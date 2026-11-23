<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack;

use mod_videotrack\local\playback_recovery;
use mod_videotrack\local\tracker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Evidence-based, explicit repair of historical missed-heartbeat rows.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(playback_recovery::class)]
#[CoversClass(tracker::class)]
final class playback_recovery_test extends \advanced_testcase {
    /**
     * Long contiguous playback can be reconstructed from a real server start.
     */
    public function test_contiguous_long_pause_has_elapsed_server_evidence(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 440, 540, 'pause')];
        $this->assertSame([2], $this->ids($rows));
    }

    /**
     * A client clock cannot manufacture elapsed server time.
     */
    public function test_forged_wallclock_does_not_recover_insufficient_server_time(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 440, 101, 'pause')];
        $rows[1]->wallclockstart = 100;
        $rows[1]->wallclockend = 100000;
        $this->assertSame([], $this->ids($rows));
    }

    /**
     * Missing handshakes remain insufficient evidence.
     */
    public function test_segment_without_handshake_is_not_promoted(): void {
        $this->assertSame([], $this->ids([$this->row(2, 0, 440, 540, 'pause')]));
    }

    /**
     * Switching tabs supersedes the old credit owner.
     */
    public function test_another_session_handshake_excludes_stale_rows(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 0, 110, 'playstart'),
            $this->row(3, 0, 440, 540, 'pause')];
        $rows[1]->sessionid = 'other';
        $this->assertSame([], $this->ids($rows));
    }

    /**
     * A hidden/end/pause boundary cannot be bridged by later rejected evidence.
     */
    public function test_terminal_boundary_requires_a_fresh_handshake(): void {
        foreach (['tab', 'pause', 'pagehide', 'ended', 'visibilitychange', 'seek'] as $reason) {
            $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 10, 110, $reason),
                $this->row(3, 10, 440, 540, 'pause')];
            $this->assertSame([], $this->ids($rows), $reason);
        }
    }

    /**
     * A second real play restores only its own subsequent elapsed-time window.
     */
    public function test_fresh_handshake_after_pause_can_recover_rewatch(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 50, 150, 'pause'),
            $this->row(3, 0, 0, 200, 'playstart'), $this->row(4, 0, 157, 357, 'tab')];
        $this->assertSame([2, 4], $this->ids($rows));
    }

    /**
     * Recovery cannot open a forbidden forward gap.
     */
    public function test_unwatched_forward_jump_remains_rejected(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 200, 440, 540, 'pause')];
        $this->assertSame([], $this->ids($rows));
    }

    /**
     * Short unrelated rejections are outside this narrowly scoped repair.
     */
    public function test_short_rejections_are_not_misclassified_as_starvation(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 20, 120, 'pause')];
        $this->assertSame([], $this->ids($rows));
    }

    /**
     * Elapsed time is spent cumulatively, not granted again to every request.
     */
    public function test_two_candidates_cannot_spend_the_same_elapsed_time_twice(): void {
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 100, 200, 'heartbeat'),
            $this->row(3, 100, 200, 201, 'pause')];
        $this->assertSame([2], $this->ids($rows));
    }

    /**
     * Current duration and rate constraints still apply to a historical reconstruction.
     */
    public function test_invalid_rate_and_over_duration_are_not_recovered(): void {
        $start = $this->row(1, 0, 0, 100, 'playstart');
        $row = $this->row(2, 0, 440, 540, 'pause');
        $row->playbackrate = 4;
        $this->assertSame([], $this->ids([$start, $row]));
        $row->playbackrate = 1;
        $row->videotimeend = 2000;
        $this->assertSame([], $this->ids([$start, $row]));
    }

    /**
     * Recalculation repairs by explicit opt-in, preserves originals and is idempotent.
     */
    public function test_explicit_recalculation_appends_repairs_without_mutating_originals(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 1172, 'completionpercent' => 100,
            'allowseekforward' => 0, 'reactionsenabled' => 0, 'completionacknowledgement' => 0,
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);
        $cm = get_fast_modinfo($course)->get_cm((int)$activity->cmid);
        $rows = [$this->row(1, 0, 0, 100, 'playstart'), $this->row(2, 0, 440, 540, 'pause')];
        foreach ($rows as $row) {
            unset($row->id);
            $row->videotrackid = $activity->id;
            $row->courseid = $course->id;
            $row->cmid = $cm->id;
            $row->userid = $user->id;
            $row->videoid = $activity->videoid;
            $DB->insert_record('videotrack_seg', $row);
        }
        $where = ['videotrackid' => $activity->id, 'userid' => $user->id];
        $originals = $DB->get_records('videotrack_seg', $where, 'id');
        $before = tracker::rebuild_state_from_segments($activity, $cm, (int)$user->id);
        $this->assertSame(0.0, (float)$before->uniquecoveredseconds);
        $logcount = $DB->count_records('logstore_standard_log');
        $repaired = tracker::recover_and_rebuild_state($activity, $cm, (int)$user->id);
        $this->assertSame(440.0, (float)$repaired->uniquecoveredseconds);
        $this->assertSame(37.54, (float)$repaired->completionpercent);
        $this->assertTrue(tracker::has_watched_videotime((int)$activity->id, (int)$user->id, 'browser', 157));
        $this->assertSame(3, $DB->count_records('videotrack_seg', $where));
        foreach ($originals as $id => $original) {
            $this->assertEquals($original, $DB->get_record('videotrack_seg', ['id' => $id], '*', MUST_EXIST));
        }
        $again = tracker::recover_and_rebuild_state($activity, $cm, (int)$user->id);
        $this->assertSame(440.0, (float)$again->uniquecoveredseconds);
        $this->assertSame(3, $DB->count_records('videotrack_seg', $where));
        $this->assertSame($logcount, $DB->count_records('logstore_standard_log'));
    }

    /**
     * Build one isolated raw-evidence row; client clock is deliberately zero.
     *
     * @param int $id Source identity.
     * @param float $start Timeline start.
     * @param float $end Timeline end.
     * @param int $time Server receipt seconds.
     * @param string $reason Close reason.
     * @return \stdClass Evidence row.
     */
    private function row(int $id, float $start, float $end, int $time, string $reason): \stdClass {
        return (object)[
            'id' => $id, 'sessionid' => 'browser', 'requestid' => md5('original-' . $id),
            'videotimestart' => $start, 'videotimeend' => $end, 'playbackrate' => 1.0,
            'timecreated' => $time, 'wallclockstart' => 0, 'wallclockend' => 0,
            'endreason' => $reason, 'servervalidated' => 0,
        ];
    }

    /**
     * Apply the pure replay policy using the same configured limits as the fixture.
     *
     * @param array $rows Server-ordered evidence rows.
     * @return array Eligible original ids.
     */
    private function ids(array $rows): array {
        $activity = (object)['durationseconds' => 1172, 'allowseekforward' => 0,
            'allowplaybackratechange' => 1, 'maxplaybackrate' => 100];
        return array_map(
            static fn($row) => $row->id,
            iterator_to_array(playback_recovery::candidates($rows, $activity, 30), false)
        );
    }
}
