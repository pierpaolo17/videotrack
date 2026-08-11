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
use mod_videotrack\local\tracker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for pure tracking interval helpers.
 *
 * These tests focus on deterministic helpers used by segment tracking and
 * completion calculations. They intentionally avoid database writes so they
 * remain a low-risk baseline before broader integration tests are added.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(tracker::class)]
final class tracker_test extends advanced_testcase {
    /**
     * Load tracker class under test.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../classes/local/tracker.php');
    }

    /**
     * Interval normalisation clamps against video duration and rejects empty ranges.
     */
    public function test_normalise_interval_clamps_and_rejects_empty_ranges(): void {
        $this->assertSame([0.0, 10.0], tracker::normalise_interval(-5.0, 10.0));
        $this->assertSame([5.0, 20.0], tracker::normalise_interval(5.0, 25.0, 20.0));
        $this->assertSame([1.235, 3.457], tracker::normalise_interval(1.23456, 3.45678));
        $this->assertNull(tracker::normalise_interval(10.0, 10.0));
        $this->assertNull(tracker::normalise_interval(15.0, 10.0));
    }

    /**
     * Invalid decoded interval data is ignored before it can affect completion.
     */
    public function test_decode_intervals_filters_invalid_ranges(): void {
        $json = json_encode([
            [0, 10],
            ['bad', 20],
            [30, 20],
            [40, 50, 'extra'],
            'not-an-interval',
        ]);

        $this->assertSame([[0.0, 10.0], [40.0, 50.0]], tracker::decode_intervals($json));
        $this->assertSame([], tracker::decode_intervals(null));
        $this->assertSame([], tracker::decode_intervals('not json'));
    }

    /**
     * Overlapping and adjacent intervals are merged deterministically.
     */
    public function test_merge_intervals_and_covered_seconds_are_deterministic(): void {
        $merged = tracker::merge_intervals([
            [10.0, 20.0],
            [0.0, 5.0],
            [4.0, 8.0],
            [20.0, 25.0],
        ]);

        $this->assertSame([[0.0, 8.0], [10.0, 25.0]], $merged);
        $this->assertSame(23.0, tracker::covered_seconds($merged));
    }

    /**
     * Simplification keeps the longest fragments without merging unseen gaps.
     */
    public function test_simplify_intervals_never_overestimates_coverage(): void {
        $intervals = [
            [0.0, 5.0],
            [10.0, 30.0],
            [40.0, 41.0],
            [50.0, 65.0],
        ];

        $simplified = tracker::simplify_intervals($intervals, 2);

        $this->assertSame([[10.0, 30.0], [50.0, 65.0]], $simplified);
        $this->assertLessThanOrEqual(tracker::covered_seconds($intervals), tracker::covered_seconds($simplified));
    }

    /**
     * Raw segment aggregation rebuilds coverage and the latest resume position.
     */
    public function test_aggregate_segments_rebuilds_state_values(): void {
        $segments = [
            (object)[
                'id' => 1,
                'videotimestart' => 0,
                'videotimeend' => 10,
                'timecreated' => 100,
            ],
            (object)[
                'id' => 2,
                'videotimestart' => 8,
                'videotimeend' => 20,
                'timecreated' => 110,
            ],
            (object)[
                'id' => 3,
                'videotimestart' => 90,
                'videotimeend' => 120,
                'timecreated' => 120,
            ],
            (object)[
                'id' => 4,
                'videotimestart' => 80,
                'videotimeend' => 70,
                'timecreated' => 130,
            ],
        ];

        $aggregate = tracker::aggregate_segments($segments, 100.0);

        $this->assertSame([[0.0, 20.0], [90.0, 100.0]], $aggregate['intervals']);
        $this->assertSame(30.0, $aggregate['coveredseconds']);
        $this->assertSame(100.0, $aggregate['lastposition']);
    }

    /**
     * The interval cap limits pathological data while preserving timeline order.
     */
    public function test_cap_intervals_limits_count_and_preserves_order(): void {
        $intervals = [];
        for ($i = 0; $i < tracker::MAX_INTERVALS + 5; $i++) {
            $intervals[] = [(float)($i * 2), (float)($i * 2 + 1)];
        }

        $capped = tracker::cap_intervals($intervals);

        $this->assertCount(tracker::MAX_INTERVALS, $capped);
        $previousstart = -1.0;
        foreach ($capped as $interval) {
            $this->assertGreaterThan($previousstart, $interval[0]);
            $previousstart = $interval[0];
        }
    }

    /**
     * Stored interval compaction never reduces exact covered seconds.
     */
    public function test_aggregate_segments_keeps_exact_coverage_after_interval_cap(): void {
        $segments = [];
        for ($i = 0; $i < tracker::MAX_INTERVALS + 5; $i++) {
            $segments[] = (object)[
                'id' => $i + 1,
                'videotimestart' => (float)($i * 2),
                'videotimeend' => (float)($i * 2 + 1),
                'timecreated' => $i + 1,
            ];
        }

        $aggregate = tracker::aggregate_segments($segments, 0.0);

        $this->assertCount(tracker::MAX_INTERVALS, $aggregate['intervals']);
        $this->assertSame((float)(tracker::MAX_INTERVALS + 5), $aggregate['coveredseconds']);
    }

    /**
     * Server credit budget is cumulative and is not replenished by same-second request frequency.
     */
    public function test_server_credit_budget_requires_elapsed_server_time(): void {
        $withouthandshake = tracker::advance_server_credit_budget(0, 0.0, 0.0, 100000, 30, 1.0, 1.0);
        $this->assertFalse($withouthandshake['accepted']);
        $this->assertSame(0.0, $withouthandshake['budget']);
        $this->assertSame(0.0, $withouthandshake['credited']);

        $aftertwentyseconds = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 120000, 30, 1.0, 20.0);
        $this->assertTrue($aftertwentyseconds['accepted']);
        $this->assertSame(20.0, $aftertwentyseconds['budget']);
        $this->assertSame(20.0, $aftertwentyseconds['credited']);

        $samemillisecond = tracker::advance_server_credit_budget(
            $aftertwentyseconds['lastactivity'],
            $aftertwentyseconds['budget'],
            $aftertwentyseconds['credited'],
            120000,
            30,
            1.0,
            1.0
        );
        $this->assertFalse($samemillisecond['accepted']);
        $this->assertSame(20.0, $samemillisecond['budget']);

        $afterfiveseconds = tracker::advance_server_credit_budget(120000, 20.0, 20.0, 125000, 30, 1.0, 5.0);
        $this->assertTrue($afterfiveseconds['accepted']);
        $this->assertSame(25.0, $afterfiveseconds['budget']);
        $this->assertSame(25.0, $afterfiveseconds['credited']);
    }

    /**
     * The bounded clock-drift tolerance cannot be converted into repeatable credit.
     */
    public function test_server_credit_tolerance_is_cumulative_and_not_reusable(): void {
        $initialdrift = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 100000, 30, 1.0, 0.5);
        $this->assertTrue($initialdrift['accepted']);
        $this->assertSame(0.0, $initialdrift['budget']);
        $this->assertSame(0.5, $initialdrift['credited']);

        $samemillisecond = tracker::advance_server_credit_budget(100000, 0.0, 0.5, 100000, 30, 1.0, 0.5);
        $this->assertFalse($samemillisecond['accepted']);
        $this->assertSame(0.0, $samemillisecond['budget']);
        $this->assertSame(0.5, $samemillisecond['credited']);

        $repeated = tracker::advance_server_credit_budget(100000, 0.0, 0.5, 100000, 30, 1.0, 0.5);
        $this->assertFalse($repeated['accepted']);
        $this->assertSame(0.0, $repeated['budget']);

        $afteronesecond = tracker::advance_server_credit_budget(100000, 0.0, 0.5, 101000, 30, 1.0, 0.5);
        $this->assertTrue($afteronesecond['accepted']);
        $this->assertSame(1.0, $afteronesecond['credited']);
    }

    /**
     * Playback-rate credit is bounded by real elapsed server time.
     */
    public function test_server_credit_budget_scales_only_with_validated_rate(): void {
        $accepted = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 102000, 30, 4.0, 8.0);
        $this->assertTrue($accepted['accepted']);
        $this->assertSame(8.0, $accepted['credited']);

        $rateclockdrift = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 102000, 30, 4.0, 8.5);
        $this->assertTrue($rateclockdrift['accepted']);
        $this->assertSame(8.5, $rateclockdrift['credited']);

        $rateexcess = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 102000, 30, 4.0, 9.0);
        $this->assertFalse($rateexcess['accepted']);
        $this->assertSame(0.0, $rateexcess['credited']);

        $roundingdrift = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 102000, 30, 1.0, 2.5);
        $this->assertTrue($roundingdrift['accepted']);
        $this->assertSame(2.5, $roundingdrift['credited']);

        $excessivedrift = tracker::advance_server_credit_budget(100000, 0.0, 0.0, 102000, 30, 1.0, 3.1);
        $this->assertFalse($excessivedrift['accepted']);
    }

    /**
     * Disabled forward seeking rejects direct jumps beyond the watched frontier.
     */
    public function test_forward_interval_guard_rejects_unwatched_jump(): void {
        $state = (object)[
            'lastposition' => 20.0,
            'intervaljson' => '[[0,20]]',
        ];
        $this->assertTrue(tracker::forward_interval_allowed($state, [20.5, 25.0], false));
        $this->assertFalse(tracker::forward_interval_allowed($state, [60.0, 61.0], false));
        $this->assertTrue(tracker::forward_interval_allowed($state, [60.0, 61.0], true));
    }

    /**
     * Legacy or restored unvalidated raw segments cannot authorise new interactions.
     */
    public function test_watched_time_validation_ignores_unvalidated_raw_segments(): void {
        global $DB;

        $this->resetAfterTest();
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => 998,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => 123,
            'videoid' => 'test-video',
            'sessionid' => 'legacy-session',
            'requestid' => str_repeat('a', 32),
            'wallclockstart' => time() - 5,
            'wallclockend' => time(),
            'videotimestart' => 0,
            'videotimeend' => 60,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 0,
            'timecreated' => time(),
        ]);

        set_config('strictsessionvalidation', 0, 'mod_videotrack');
        $this->assertFalse(tracker::has_watched_videotime(998, 123, 'legacy-session', 30.0));
    }

    /**
     * Retrying an accepted segment is idempotent and does not inflate coverage.
     */
    public function test_segment_request_retry_reuses_persisted_result(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $cm = get_fast_modinfo($course)->get_cm($forum->cmid);
        $user = $generator->create_user();
        $videotrack = (object)[
            'id' => 990032,
            'course' => $course->id,
            'videoid' => 'ledger-test',
            'durationseconds' => 60.0,
            'allowseekforward' => 1,
            'completionpercent' => 0,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
            'completionacknowledgement' => 0,
            'completionlogic' => 'and',
        ];
        $sessionid = str_repeat('b', 32);
        tracker::begin_playback(
            $videotrack,
            $cm,
            $user->id,
            $sessionid,
            str_repeat('c', 32),
            0.0,
            100000
        );
        $segment = (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $user->id,
            'videoid' => $videotrack->videoid,
            'sessionid' => $sessionid,
            'requestid' => str_repeat('d', 32),
            'wallclockstart' => 100,
            'wallclockend' => 105,
            'videotimestart' => 0.0,
            'videotimeend' => 5.0,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => 105,
        ];
        $segmentid = null;
        $replayed = false;
        $state = tracker::update_state(
            $videotrack,
            $cm,
            $user->id,
            [0.0, 5.0],
            5.0,
            clone $segment,
            $segmentid,
            [
                'nowmilliseconds' => 105000,
                'heartbeat' => 30,
                'playbackrate' => 1.0,
                'allowseekforward' => true,
            ],
            $replayed
        );
        $this->assertFalse($replayed);
        $this->assertGreaterThan(0, $segmentid);
        $this->assertSame(5.0, (float)$state->uniquecoveredseconds);

        $retriedid = null;
        $retried = false;
        $retriedstate = tracker::update_state(
            $videotrack,
            $cm,
            $user->id,
            [0.0, 5.0],
            5.0,
            clone $segment,
            $retriedid,
            [
                'nowmilliseconds' => 106000,
                'heartbeat' => 30,
                'playbackrate' => 1.0,
                'allowseekforward' => true,
            ],
            $retried
        );

        $this->assertTrue($retried);
        $this->assertSame($segmentid, $retriedid);
        $this->assertSame(5.0, (float)$retriedstate->uniquecoveredseconds);
        $this->assertSame(1, $DB->count_records('videotrack_seg', [
            'videotrackid' => $videotrack->id,
            'userid' => $user->id,
            'requestid' => $segment->requestid,
        ]));
    }

    /**
     * Watched-time validation falls back to persisted aggregate intervals.
     */
    public function test_watched_time_validation_uses_aggregate_state_fallback(): void {
        global $DB;

        $this->resetAfterTest();
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => 999,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => 123,
            'videoid' => 'test-video',
            'lastposition' => 25.0,
            'durationseconds' => 60.0,
            'uniquecoveredseconds' => 20.0,
            'completionpercent' => 33.33,
            'intervaljson' => '[[10,30]]',
            'iscompleted' => 0,
            'timemodified' => time(),
            'timecreated' => time(),
        ]);

        set_config('strictsessionvalidation', 0, 'mod_videotrack');
        $this->assertTrue(tracker::has_watched_videotime(999, 123, 'different-session', 20.0));
        $this->assertFalse(tracker::has_watched_videotime(999, 123, 'different-session', 50.0));
    }

    /**
     * Sessionless server actions can validate watched progress across browser sessions.
     */
    public function test_any_session_watched_time_validation_uses_validated_segments(): void {
        global $DB;

        $this->resetAfterTest();
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => 1001,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => 123,
            'videoid' => 'forum-video',
            'sessionid' => 'first-session',
            'requestid' => str_repeat('e', 32),
            'wallclockstart' => time() - 5,
            'wallclockend' => time(),
            'videotimestart' => 10.0,
            'videotimeend' => 30.0,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => time(),
        ]);

        set_config('strictsessionvalidation', 1, 'mod_videotrack');
        $this->assertFalse(tracker::has_watched_videotime(1001, 123, 'other-session', 20.0));
        $this->assertTrue(tracker::has_watched_videotime_any_session(1001, 123, 20.0));
        $this->assertFalse(tracker::has_watched_videotime_any_session(1001, 123, 40.0));
    }

    /**
     * Allowed forward seeking can authorise an immediate interaction from a recent same-session playback window.
     */
    public function test_allowed_forward_seek_accepts_recent_session_interaction_timestamp(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('heartbeatinterval', 30, 'mod_videotrack');
        set_config('strictsessionvalidation', 1, 'mod_videotrack');
        $sessionid = str_repeat('f', 32);
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => 1002,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => 123,
            'videoid' => 'seek-enabled-video',
            'sessionid' => $sessionid,
            'requestid' => str_repeat('g', 32),
            'wallclockstart' => time(),
            'wallclockend' => time(),
            'videotimestart' => 10.0,
            'videotimeend' => 10.0,
            'playbackrate' => 1.0,
            'endreason' => 'playstart',
            'servervalidated' => 0,
            'timecreated' => time(),
        ]);
        $instance = (object)['id' => 1002, 'allowseekforward' => 1];

        $this->assertTrue(tracker::interaction_timestamp_allowed($instance, 123, $sessionid, 60.0));
        $instance->allowseekforward = 0;
        $this->assertFalse(tracker::interaction_timestamp_allowed($instance, 123, $sessionid, 60.0));
    }

    /**
     * Forward-seek interaction relaxation requires recent evidence from the same session.
     */
    public function test_allowed_forward_seek_rejects_stale_or_different_session_interaction(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('heartbeatinterval', 30, 'mod_videotrack');
        set_config('strictsessionvalidation', 1, 'mod_videotrack');
        $sessionid = str_repeat('h', 32);
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => 1003,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => 123,
            'videoid' => 'stale-seek-video',
            'sessionid' => $sessionid,
            'requestid' => str_repeat('i', 32),
            'wallclockstart' => time() - 200,
            'wallclockend' => time() - 190,
            'videotimestart' => 10.0,
            'videotimeend' => 20.0,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => time() - 190,
        ]);
        $instance = (object)['id' => 1003, 'allowseekforward' => 1];

        $this->assertFalse(tracker::interaction_timestamp_allowed($instance, 123, $sessionid, 60.0));
        $this->assertFalse(tracker::interaction_timestamp_allowed(
            $instance,
            123,
            str_repeat('j', 32),
            60.0
        ));
    }
}
