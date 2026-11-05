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

use mod_videotrack\external\save_segment;
use mod_videotrack\local\tracker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Persisted tracking effects at the maintainability exit boundary.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(tracker::class)]
#[CoversClass(save_segment::class)]
final class tracking_effects_test extends \advanced_testcase {
    /**
     * Replaying a budget rejection cannot earn time or turn the rejected request into accepted evidence.
     */
    public function test_budget_rejection_retry_preserves_state_and_audit_row(): void {
        global $DB;

        $this->resetAfterTest();
        $fixture = $this->fixture();
        $segment = $this->segment($fixture, md5('budget-rejection'), [0.0, 20.0]);
        [$state, $id, $replayed] = $this->persist($fixture, clone $segment, 101000);

        $this->assertSame(-1, $id);
        $this->assertFalse($replayed);
        $this->assertSame(0.0, (float)$state->uniquecoveredseconds);
        $this->assertSame(0.0, (float)$state->serverbudgetseconds);
        $this->assertSame(0.0, (float)$state->servercreditedseconds);
        $this->assertSame(101000, (int)$state->serverlastactivity);
        $conditions = ['videotrackid' => $fixture['activity']->id, 'userid' => $fixture['user']->id];
        $before = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
        [$retried, $retryid, $replayed] = $this->persist($fixture, clone $segment, 200000);

        $this->assertSame(-1, $retryid);
        $this->assertTrue($replayed);
        $this->assertEquals($before, $retried);
        $this->assertEquals($before, $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST));
        $raw = $DB->get_records('videotrack_seg', $conditions + ['requestid' => $segment->requestid]);
        $this->assertCount(1, $raw);
        $rawrecord = reset($raw);
        $this->assertSame(0, (int)$rawrecord->servervalidated);
        $this->assertSame(20.0, (float)$rawrecord->videotimeend);

        $recovery = $this->segment($fixture, md5('budget-recovery'), [0.0, 1.0]);
        [$state, $id, $replayed] = $this->persist($fixture, $recovery, 102000);
        $this->assertGreaterThan(0, $id);
        $this->assertFalse($replayed);
        $this->assertSame(1.0, (float)$state->uniquecoveredseconds);
        $this->assertSame(1.0, (float)$state->serverbudgetseconds);
        $this->assertSame(1.0, (float)$state->servercreditedseconds);
        $this->assertSame(3, $DB->count_records('videotrack_seg', $conditions));
    }

    /**
     * An illegal forward jump retains raw evidence while only subsequent legitimate playback earns coverage.
     */
    public function test_forward_rejection_keeps_frontier_and_does_not_replay_its_budget_reset(): void {
        global $DB;

        $this->resetAfterTest();
        $fixture = $this->fixture();
        $first = $this->segment($fixture, md5('frontier-first'), [0.0, 5.0]);
        $this->persist($fixture, $first, 105000, false);
        $jump = $this->segment($fixture, md5('frontier-jump'), [50.0, 55.0]);
        [$state, $id, $replayed] = $this->persist($fixture, clone $jump, 110000, false);

        $this->assertSame(-1, $id);
        $this->assertFalse($replayed);
        $this->assertSame(5.0, (float)$state->lastposition);
        $this->assertSame(5.0, (float)$state->uniquecoveredseconds);
        $this->assertSame([[0.0, 5.0]], tracker::decode_intervals($state->intervaljson));
        $this->assertSame(5.0, (float)$state->serverbudgetseconds);
        $this->assertSame(5.0, (float)$state->servercreditedseconds);
        $this->assertSame(110000, (int)$state->serverlastactivity);
        $next = $this->segment($fixture, md5('frontier-next'), [5.0, 10.0]);
        [$state, $id] = $this->persist($fixture, $next, 115000, false);
        $this->assertGreaterThan(0, $id);
        $this->assertSame(10.0, (float)$state->uniquecoveredseconds);
        $this->assertSame(10.0, (float)$state->lastposition);
        $conditions = ['videotrackid' => $fixture['activity']->id, 'userid' => $fixture['user']->id];
        $before = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
        [$state, $id, $replayed] = $this->persist($fixture, clone $jump, 999000, false);
        $this->assertSame(-1, $id);
        $this->assertTrue($replayed);
        $this->assertEquals($before, $state);
        $this->assertEquals($before, $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST));
        $raw = $DB->get_record('videotrack_seg', $conditions + ['requestid' => $jump->requestid], '*', MUST_EXIST);
        $this->assertSame(0, (int)$raw->servervalidated);
        $this->assertSame(50.0, (float)$raw->videotimestart);
        $this->assertSame(55.0, (float)$raw->videotimeend);
        $this->assertSame(4, $DB->count_records('videotrack_seg', $conditions));
    }

    /**
     * The live interval-cap path rebuilds exact coverage from validated ledger rows and ignores rejected evidence.
     */
    public function test_live_interval_cap_rebuild_preserves_exact_validated_coverage(): void {
        global $DB;

        $this->resetAfterTest();
        $fixture = $this->fixture(2000.0);
        $conditions = ['videotrackid' => $fixture['activity']->id, 'userid' => $fixture['user']->id];
        $intervals = [];
        $segments = [];
        for ($i = 0; $i < tracker::MAX_INTERVALS; $i++) {
            $interval = [(float)($i * 3), (float)($i * 3 + 1)];
            $intervals[] = $interval;
            $segments[] = $this->segment($fixture, md5('cap-' . $i), $interval);
        }
        $DB->insert_records('videotrack_seg', $segments);
        $rejected = $this->segment($fixture, md5('cap-rejected'), [0.0, 2000.0]);
        $rejected->servervalidated = 0;
        $DB->insert_record('videotrack_seg', $rejected);
        $state = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
        $state->intervaljson = tracker::encode_intervals($intervals);
        $state->uniquecoveredseconds = (float)tracker::MAX_INTERVALS;
        $state->completionpercent = 25.0;
        $state->lastposition = (float)(tracker::MAX_INTERVALS * 3 - 2);
        $DB->update_record('videotrack_state', $state);
        $newinterval = [(float)(tracker::MAX_INTERVALS * 3), (float)(tracker::MAX_INTERVALS * 3 + 1)];
        $segment = $this->segment($fixture, md5('cap-new'), $newinterval);

        [$state, $id, $replayed] = $this->persist($fixture, clone $segment, 101000);

        $this->assertGreaterThan(0, $id);
        $this->assertFalse($replayed);
        $this->assertSame((float)(tracker::MAX_INTERVALS + 1), (float)$state->uniquecoveredseconds);
        $this->assertSame(25.05, (float)$state->completionpercent);
        $this->assertSame($newinterval[1], (float)$state->lastposition);
        $this->assertCount(tracker::MAX_INTERVALS, tracker::decode_intervals($state->intervaljson));
        $this->assertSame(tracker::MAX_INTERVALS + 1, $DB->count_records('videotrack_seg', $conditions + ['servervalidated' => 1]));
        $before = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
        [$retried, $retryid, $replayed] = $this->persist($fixture, clone $segment, 201000);
        $this->assertTrue($replayed);
        $this->assertSame($id, $retryid);
        $this->assertEquals($before, $retried);
        $this->assertEquals($before, $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST));
        $this->assertSame(1, $DB->count_records('videotrack_seg', $conditions + ['requestid' => $segment->requestid]));
    }

    /**
     * A real authenticated endpoint retry repeats the response without duplicating completion, events or ledger rows.
     */
    public function test_external_completion_retry_keeps_persisted_effects_and_events_once(): void {
        global $DB;

        $this->resetAfterTest();
        $fixture = $this->fixture(10.0);
        $originalpost = $_POST;
        $_POST['sesskey'] = sesskey();
        $sink = $this->redirectEvents();
        try {
            $arguments = [
                (int)$fixture['cm']->id, $fixture['sessionid'], md5('external-end'),
                0.0, 10.0, 100, 110, 1.0, 'ended', 10.0,
            ];
            $first = save_segment::execute(...$arguments);
            $this->assertTrue($first['accepted']);
            $this->assertFalse($first['requestreplayed']);
            $this->assertTrue($first['iscompleted']);
            $this->assertSame(10.0, $first['uniquecoveredseconds']);
            $this->assertSame(100.0, $first['completionpercent']);
            $conditions = ['videotrackid' => $fixture['activity']->id, 'userid' => $fixture['user']->id];
            $state = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
            $nativeconditions = ['coursemoduleid' => $fixture['cm']->id, 'userid' => $fixture['user']->id];
            $native = $DB->get_record('course_modules_completion', $nativeconditions, '*', MUST_EXIST);
            $this->assertSame(COMPLETION_COMPLETE, (int)$native->completionstate);
            $this->assertSame('', (string)$state->serverplaybacksessionid);
            $this->assertSame(0, (int)$state->serverlastactivity);
            $events = $this->significant_events($sink->get_events());
            $this->assertSame([
                \mod_videotrack\event\activity_completed::class => 1,
                \mod_videotrack\event\segment_saved::class => 1,
            ], $events);

            $retry = save_segment::execute(...$arguments);

            $this->assertTrue($retry['requestreplayed']);
            $first['requestreplayed'] = true;
            $this->assertSame($first, $retry);
            $this->assertEquals($state, $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST));
            $this->assertEquals($native, $DB->get_record('course_modules_completion', $nativeconditions, '*', MUST_EXIST));
            $this->assertSame(2, $DB->count_records('videotrack_seg', $conditions));
            $this->assertSame($events, $this->significant_events($sink->get_events()));
        } finally {
            $sink->close();
            $_POST = $originalpost;
        }
    }

    /**
     * Build a real enrolled learner, automatic-completion activity and explicit server playback window.
     *
     * @param float $duration Authoritative teacher-configured duration.
     * @return array Activity, course module, learner and browser session.
     */
    private function fixture(float $duration = 100.0): array {
        $this->setAdminUser();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $activity = $generator->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => $duration, 'completionpercent' => 100,
            'allowseekforward' => 0,
            'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionview' => 0,
            'reactionsenabled' => 0, 'reactionsrequired' => 0, 'minreactions' => 0,
            'requireallreactiontypes' => 0, 'completionacknowledgement' => 0, 'completionlogic' => 'and',
        ]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);
        $cm = get_fast_modinfo($course)->get_cm((int)$activity->cmid);
        $sessionid = md5('tracking-effects-session');
        tracker::begin_playback($activity, $cm, (int)$user->id, $sessionid, md5('tracking-effects-start'), 0.0, 100000);
        return ['activity' => $activity, 'cm' => $cm, 'user' => $user, 'sessionid' => $sessionid];
    }

    /**
     * Build one raw request using the fixture's actual activity and learner identifiers.
     *
     * @param array $fixture Real activity/learner fixture.
     * @param string $requestid Stable idempotency key.
     * @param array $interval Start and end video times.
     * @return \stdClass Candidate ledger row.
     */
    private function segment(array $fixture, string $requestid, array $interval): \stdClass {
        return (object)[
            'videotrackid' => $fixture['activity']->id, 'courseid' => $fixture['activity']->course,
            'cmid' => $fixture['cm']->id, 'userid' => $fixture['user']->id, 'videoid' => $fixture['activity']->videoid,
            'sessionid' => $fixture['sessionid'], 'requestid' => $requestid,
            'wallclockstart' => 100, 'wallclockend' => 105, 'videotimestart' => $interval[0], 'videotimeend' => $interval[1],
            'playbackrate' => 1.0, 'endreason' => 'heartbeat', 'servervalidated' => 1, 'timecreated' => 105,
        ];
    }

    /**
     * Invoke the real atomic tracker write with a deterministic server clock.
     *
     * @param array $fixture Real activity/learner fixture.
     * @param \stdClass $segment Candidate ledger row.
     * @param int $nowmilliseconds Deterministic server time.
     * @param bool $allowseekforward Effective forward-seek policy for this case.
     * @return array Persisted state, segment result and replay flag.
     */
    private function persist(array $fixture, \stdClass $segment, int $nowmilliseconds, bool $allowseekforward = true): array {
        $id = null;
        $replayed = false;
        $guard = [
            'nowmilliseconds' => $nowmilliseconds, 'heartbeat' => 30,
            'playbackrate' => 1.0, 'allowseekforward' => $allowseekforward,
        ];
        $state = tracker::update_state(
            $fixture['activity'], $fixture['cm'], (int)$fixture['user']->id,
            [(float)$segment->videotimestart, (float)$segment->videotimeend], (float)$segment->videotimeend,
            $segment, $id, $guard, $replayed
        );
        return [$state, $id, $replayed];
    }

    /**
     * Count only VideoTrack completion and significant segment events, excluding unrelated core events.
     *
     * @param array $events Events captured by Moodle's real event sink.
     * @return array Counts keyed by canonical event class.
     */
    private function significant_events(array $events): array {
        $counts = [];
        foreach ($events as $event) {
            if (
                $event instanceof \mod_videotrack\event\activity_completed
                || $event instanceof \mod_videotrack\event\segment_saved
            ) {
                $name = get_class($event);
                $counts[$name] = ($counts[$name] ?? 0) + 1;
            }
        }
        ksort($counts);
        return $counts;
    }
}
