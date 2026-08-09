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

use advanced_testcase;
use mod_videotrack\local\privacy_manager;
use mod_videotrack\local\tracker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for deletion-based GDPR retention and derived-state rebuilding.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(privacy_manager::class)]
final class privacy_manager_test extends advanced_testcase {
    /**
     * Finite retention deletes expired rows and rebuilds state from retained data only.
     */
    public function test_cleanup_deletes_expired_rows_and_rebuilds_state(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $videotrack, $cm] = $this->create_test_activity(100.0);
        $user = $this->getDataGenerator()->create_user();
        $now = 2_000_000_000;
        $old = $now - (20 * DAYSECS);
        $recent = $now - (2 * DAYSECS);
        set_config('retentionperioddays', 10, 'mod_videotrack');

        $this->insert_segment($videotrack, $cm, $user->id, 'oldsegment', 0.0, 60.0, $old);
        $this->insert_segment($videotrack, $cm, $user->id, 'recentsegment', 20.0, 40.0, $recent);
        $this->insert_interaction($videotrack, $cm, $user->id, 'Old note', $old);
        $this->insert_interaction($videotrack, $cm, $user->id, 'Recent note', $recent + 10);
        $this->insert_integrity($videotrack, $cm, $user->id, 'tabhidden', $old);
        $this->insert_integrity($videotrack, $cm, $user->id, 'tabhidden', $recent + 20);
        $this->insert_acknowledgement($videotrack, $cm, $user->id, str_repeat('a', 64), $old);
        $this->insert_acknowledgement($videotrack, $cm, $user->id, str_repeat('b', 64), $recent + 30);

        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $user->id,
            'videoid' => $videotrack->videoid,
            'lastposition' => 60.0,
            'durationseconds' => 100.0,
            'serverlastactivity' => 123456,
            'serverbudgetseconds' => 90.0,
            'servercreditedseconds' => 80.0,
            'uniquecoveredseconds' => 60.0,
            'completionpercent' => 60.0,
            'intervaljson' => '[[0,60]]',
            'iscompleted' => 1,
            'timemodified' => $recent,
            'timecreated' => $old,
        ]);

        $counts = privacy_manager::delete_expired_records($now);

        $this->assertSame(1, $counts['segments']);
        $this->assertSame(1, $counts['events']);
        $this->assertSame(1, $counts['integrity']);
        $this->assertSame(1, $counts['acknowledgements']);
        $this->assertSame(1, $counts['statesrebuilt']);
        $this->assertSame(0, $counts['statesdeleted']);
        $this->assertSame(1, $counts['processed']);
        $this->assertSame(0, $counts['remaining']);
        $this->assertSame(0, $counts['completionerrors']);

        $this->assertFalse($DB->record_exists('videotrack_seg', ['requestid' => 'oldsegment']));
        $this->assertTrue($DB->record_exists('videotrack_seg', ['requestid' => 'recentsegment']));
        $this->assertFalse($DB->record_exists('videotrack_reactev', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
            'notetext' => 'Old note',
        ]));
        $this->assertTrue($DB->record_exists('videotrack_reactev', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
            'notetext' => 'Recent note',
        ]));
        $this->assertSame(1, $DB->count_records('videotrack_integrity', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
        ]));
        $this->assertSame(1, $DB->count_records('videotrack_acknowledge', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
        ]));

        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
        ], '*', MUST_EXIST);
        $this->assertEqualsWithDelta(20.0, (float)$state->uniquecoveredseconds, 0.001);
        $this->assertEqualsWithDelta(20.0, (float)$state->completionpercent, 0.001);
        $this->assertEqualsWithDelta(40.0, (float)$state->lastposition, 0.001);
        $this->assertSame([[20.0, 40.0]], tracker::decode_intervals($state->intervaljson));
        $this->assertSame(0, (int)$state->serverlastactivity);
        $this->assertEqualsWithDelta(0.0, (float)$state->serverbudgetseconds, 0.001);
        $this->assertEqualsWithDelta(0.0, (float)$state->servercreditedseconds, 0.001);
        $this->assertSame($recent, (int)$state->timecreated);
        $this->assertSame(0, (int)$state->iscompleted);
    }

    /**
     * A state with no retained completion inputs is removed after cleanup.
     */
    public function test_cleanup_removes_state_when_no_retained_inputs_remain(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $videotrack, $cm] = $this->create_test_activity(60.0);
        $user = $this->getDataGenerator()->create_user();
        $now = 2_000_000_000;
        $old = $now - (20 * DAYSECS);
        set_config('retentionperioddays', 10, 'mod_videotrack');

        $this->insert_segment($videotrack, $cm, $user->id, 'expiredonly', 0.0, 60.0, $old);
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $user->id,
            'videoid' => $videotrack->videoid,
            'lastposition' => 60.0,
            'durationseconds' => 60.0,
            'uniquecoveredseconds' => 60.0,
            'completionpercent' => 100.0,
            'intervaljson' => '[[0,60]]',
            'iscompleted' => 1,
            'timemodified' => $old,
            'timecreated' => $old,
        ]);

        $counts = privacy_manager::delete_expired_records($now);

        $this->assertSame(1, $counts['segments']);
        $this->assertSame(1, $counts['statesdeleted']);
        $this->assertFalse($DB->record_exists('videotrack_state', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
        ]));
    }

    /**
     * Cleanup preserves a playback guard that is still inside the active heartbeat window.
     */
    public function test_cleanup_preserves_recent_playback_guard(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $videotrack, $cm] = $this->create_test_activity(100.0);
        $user = $this->getDataGenerator()->create_user();
        $now = 2_000_000_000;
        $old = $now - (20 * DAYSECS);
        $recent = $now - (2 * DAYSECS);
        set_config('retentionperioddays', 10, 'mod_videotrack');
        set_config('heartbeatinterval', 30, 'mod_videotrack');

        $this->insert_segment($videotrack, $cm, $user->id, 'oldguardsegment', 0.0, 10.0, $old);
        $this->insert_segment($videotrack, $cm, $user->id, 'recentguardsegment', 10.0, 20.0, $recent);
        $lastactivity = ($now * 1000) - 1000;
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $user->id,
            'videoid' => $videotrack->videoid,
            'lastposition' => 20.0,
            'durationseconds' => 100.0,
            'serverlastactivity' => $lastactivity,
            'serverbudgetseconds' => 18.0,
            'servercreditedseconds' => 15.0,
            'uniquecoveredseconds' => 20.0,
            'completionpercent' => 20.0,
            'intervaljson' => '[[0,20]]',
            'iscompleted' => 0,
            'timemodified' => $recent,
            'timecreated' => $old,
        ]);

        $counts = privacy_manager::delete_expired_records($now);
        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
        ], '*', MUST_EXIST);

        $this->assertSame(1, $counts['segments']);
        $this->assertSame(1, $counts['statesrebuilt']);
        $this->assertSame($lastactivity, (int)$state->serverlastactivity);
        $this->assertEqualsWithDelta(18.0, (float)$state->serverbudgetseconds, 0.001);
        $this->assertEqualsWithDelta(15.0, (float)$state->servercreditedseconds, 0.001);
        $this->assertEqualsWithDelta(10.0, (float)$state->uniquecoveredseconds, 0.001);
    }

    /**
     * Privacy erasure after retention removes the remaining learner data but keeps activity files.
     */
    public function test_user_erasure_after_retention_keeps_shared_activity_files(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $videotrack, $cm] = $this->create_test_activity(100.0);
        $user = $this->getDataGenerator()->create_user();
        $now = 2_000_000_000;
        $old = $now - (20 * DAYSECS);
        $recent = $now - (2 * DAYSECS);
        set_config('retentionperioddays', 10, 'mod_videotrack');

        $this->insert_segment($videotrack, $cm, $user->id, 'eraseold', 0.0, 10.0, $old);
        $this->insert_segment($videotrack, $cm, $user->id, 'eraserecent', 10.0, 20.0, $recent);
        $this->insert_interaction($videotrack, $cm, $user->id, 'Recent private note', $recent);
        $this->insert_integrity($videotrack, $cm, $user->id, 'tabhidden', $recent);
        $this->insert_acknowledgement($videotrack, $cm, $user->id, str_repeat('d', 64), $recent);

        $context = \context_module::instance($cm->id);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_videotrack',
            'filearea' => 'posterimage',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'shared-poster.png',
        ], 'not-a-real-image');

        privacy_manager::delete_expired_records($now);
        privacy_manager::delete_user_data_in_context($context, $user->id);

        foreach (
            [
                'videotrack_seg',
                'videotrack_state',
                'videotrack_reactev',
                'videotrack_integrity',
                'videotrack_acknowledge',
            ] as $table
        ) {
            $this->assertFalse($DB->record_exists($table, [
                'cmid' => $cm->id,
                'userid' => $user->id,
            ]));
        }
        $this->assertTrue($file->get_contenthash() !== '');
        $this->assertTrue(get_file_storage()->file_exists(
            $context->id,
            'mod_videotrack',
            'posterimage',
            0,
            '/',
            'shared-poster.png'
        ));
    }

    /**
     * Unlimited retention keeps real-user rows but purges legacy pseudonymous data.
     */
    public function test_unlimited_retention_purges_only_legacy_pseudonymous_rows(): void {
        global $DB;

        $this->resetAfterTest();
        [$course, $videotrack, $cm] = $this->create_test_activity(100.0);
        $user = $this->getDataGenerator()->create_user();
        $old = 1_000_000_000;
        set_config('retentionperioddays', 0, 'mod_videotrack');
        set_config('anonymisationsalt', 'legacy-secret', 'mod_videotrack');

        $this->insert_segment($videotrack, $cm, $user->id, 'realrow', 0.0, 10.0, $old);
        $this->insert_segment($videotrack, $cm, -123456, 'legacyrow', 0.0, 10.0, $old);
        $this->insert_interaction($videotrack, $cm, -123456, 'Legacy note', $old);
        $this->insert_integrity($videotrack, $cm, -123456, 'tabhidden', $old);
        $this->insert_acknowledgement($videotrack, $cm, -123456, str_repeat('c', 64), $old);
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => -123456,
            'videoid' => $videotrack->videoid,
            'intervaljson' => '[[0,10]]',
            'timecreated' => $old,
            'timemodified' => $old,
        ]);

        $counts = privacy_manager::delete_expired_records($old + (100 * DAYSECS));

        $this->assertSame(5, $counts['legacy']);
        $this->assertSame(1, $counts['skipped']);
        $this->assertTrue($DB->record_exists('videotrack_seg', ['requestid' => 'realrow']));
        $this->assertFalse($DB->record_exists_select('videotrack_seg', 'userid < 0'));
        $this->assertFalse($DB->record_exists_select('videotrack_state', 'userid < 0'));
        $this->assertFalse($DB->record_exists_select('videotrack_reactev', 'userid < 0'));
        $this->assertFalse($DB->record_exists_select('videotrack_integrity', 'userid < 0'));
        $this->assertFalse($DB->record_exists_select('videotrack_acknowledge', 'userid < 0'));
        $this->assertFalse(get_config('mod_videotrack', 'anonymisationsalt'));
    }

    /**
     * Create a valid VideoTrack course module without depending on a plugin generator.
     *
     * @param float $duration Verified duration.
     * @return array [course, activity, cm_info]
     */
    private function create_test_activity(float $duration): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $now = time();
        $videotrackid = $DB->insert_record('videotrack', (object)[
            'course' => $course->id,
            'name' => 'Retention test activity',
            'videoid' => 'retentiontest',
            'videosource' => 'youtube',
            'durationseconds' => $duration,
            'completionpercent' => 50,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'videotrack'], MUST_EXIST);
        $DB->update_record('course_modules', (object)[
            'id' => $forum->cmid,
            'module' => $moduleid,
            'instance' => $videotrackid,
            'completion' => COMPLETION_TRACKING_NONE,
        ]);
        rebuild_course_cache($course->id, true);
        $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid], '*', MUST_EXIST);
        $cm = get_fast_modinfo($course)->get_cm($forum->cmid);
        return [$course, $videotrack, $cm];
    }

    /**
     * Insert one server-validated playback segment.
     *
     * @param \stdClass $videotrack Activity record.
     * @param \cm_info $cm Course module.
     * @param int $userid User id.
     * @param string $requestid Idempotency identifier.
     * @param float $start Segment start.
     * @param float $end Segment end.
     * @param int $timecreated Creation timestamp.
     */
    private function insert_segment(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        string $requestid,
        float $start,
        float $end,
        int $timecreated
    ): void {
        global $DB;

        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cm->id,
            'userid' => $userid,
            'videoid' => $videotrack->videoid,
            'sessionid' => 'retention-session',
            'requestid' => $requestid,
            'wallclockstart' => $timecreated,
            'wallclockend' => $timecreated + 1,
            'videotimestart' => $start,
            'videotimeend' => $end,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => $timecreated,
        ]);
    }

    /**
     * Insert one personal note event.
     *
     * @param \stdClass $videotrack Activity record.
     * @param \cm_info $cm Course module.
     * @param int $userid User id.
     * @param string $text Note text.
     * @param int $timecreated Creation timestamp.
     */
    private function insert_interaction(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        string $text,
        int $timecreated
    ): void {
        global $DB;

        $DB->insert_record('videotrack_reactev', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cm->id,
            'userid' => $userid,
            'videoid' => $videotrack->videoid,
            'sessionid' => 'retention-session',
            'reactionid' => 0,
            'reactionkey' => '',
            'reactionlabel' => '',
            'reactiondesc' => '',
            'notetext' => $text,
            'notetype' => 'note',
            'videotime' => 5.0,
            'playbackrate' => 1.0,
            'isdeleted' => 0,
            'timecreated' => $timecreated,
            'timemodified' => $timecreated,
        ]);
    }

    /**
     * Insert one integrity event.
     *
     * @param \stdClass $videotrack Activity record.
     * @param \cm_info $cm Course module.
     * @param int $userid User id.
     * @param string $eventtype Integrity event type.
     * @param int $timecreated Creation timestamp.
     */
    private function insert_integrity(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        string $eventtype,
        int $timecreated
    ): void {
        global $DB;

        $DB->insert_record('videotrack_integrity', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cm->id,
            'userid' => $userid,
            'videoid' => $videotrack->videoid,
            'sessionid' => 'retention-session',
            'eventtype' => $eventtype,
            'videotime' => 5.0,
            'timecreated' => $timecreated,
        ]);
    }

    /**
     * Insert one acknowledgement row.
     *
     * @param \stdClass $videotrack Activity record.
     * @param \cm_info $cm Course module.
     * @param int $userid User id.
     * @param string $statementhash Statement hash.
     * @param int $timeconfirmed Confirmation timestamp.
     */
    private function insert_acknowledgement(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        string $statementhash,
        int $timeconfirmed
    ): void {
        global $DB;

        $DB->insert_record('videotrack_acknowledge', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cm->id,
            'userid' => $userid,
            'statementhash' => $statementhash,
            'instanceversion' => 1,
            'viewedseconds' => 5.0,
            'viewedpercent' => 5.0,
            'timeconfirmed' => $timeconfirmed,
        ]);
    }
}
