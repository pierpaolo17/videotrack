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
     * Server credit budget is cumulative and is not replenished by same-second request frequency.
     */
    public function test_server_credit_budget_is_cumulative(): void {
        $first = tracker::advance_server_credit_budget(0, 0.0, 0.0, 100, 30, 1.0, 20.0);
        $this->assertNotNull($first);
        $this->assertSame(32.0, $first['budget']);
        $this->assertSame(20.0, $first['credited']);

        $sameSecond = tracker::advance_server_credit_budget(
            $first['lastactivity'],
            $first['budget'],
            $first['credited'],
            100,
            30,
            1.0,
            13.0
        );
        $this->assertNull($sameSecond);

        $afterFiveSeconds = tracker::advance_server_credit_budget(
            $first['lastactivity'],
            $first['budget'],
            $first['credited'],
            105,
            30,
            1.0,
            13.0
        );
        $this->assertNotNull($afterFiveSeconds);
        $this->assertSame(37.0, $afterFiveSeconds['budget']);
        $this->assertSame(33.0, $afterFiveSeconds['credited']);
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
}
