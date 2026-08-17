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
use mod_videotrack\local\analytics;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for server-side analytics aggregation.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(analytics::class)]
final class analytics_test extends advanced_testcase {
    /**
     * Granularity uses supported values and caps the number of bins.
     */
    public function test_bin_size_is_normalised_for_duration(): void {
        $this->assertSame(10, analytics::normalise_bin_size(0, 600));
        $this->assertSame(30, analytics::normalise_bin_size(17, 1800));
        $this->assertSame(600, analytics::normalise_bin_size(10, 300000));
    }

    /**
     * Unique coverage and replay time are separated per user and bin.
     */
    public function test_build_separates_unique_and_repeated_viewing(): void {
        $segments = [
            (object)['userid' => 1, 'videotimestart' => 0, 'videotimeend' => 15],
            (object)['userid' => 1, 'videotimestart' => 5, 'videotimeend' => 12],
            (object)['userid' => 1, 'videotimestart' => 20, 'videotimeend' => 30],
            (object)['userid' => 2, 'videotimestart' => 0, 'videotimeend' => 10],
            (object)['userid' => 2, 'videotimestart' => 10, 'videotimeend' => 20],
        ];

        $result = analytics::build($segments, 40, 10);

        $this->assertSame(2, $result['viewers']);
        $this->assertSame(25.0, $result['bins'][0]['rawseconds']);
        $this->assertSame(20.0, $result['bins'][0]['uniqueseconds']);
        $this->assertSame(5.0, $result['bins'][0]['repeatseconds']);
        $this->assertSame(2, $result['bins'][0]['viewers']);
        $this->assertSame(1, $result['bins'][0]['repeatviewers']);
        $this->assertSame(17.0, $result['bins'][1]['rawseconds']);
        $this->assertSame(15.0, $result['bins'][1]['uniqueseconds']);
        $this->assertSame(2.0, $result['bins'][1]['repeatseconds']);
        $this->assertSame(1, $result['bins'][2]['viewers']);
        $this->assertSame(50.0, $result['bins'][2]['retention']);
    }

    /**
     * Authorised exact reporting can expose aggregates from a single contributing user.
     */
    public function test_exact_reporting_threshold_preserves_single_user_aggregates(): void {
        $viewing = analytics::apply_privacy_threshold([
            'viewers' => 1,
            'repeatmetricsavailable' => true,
            'bins' => [[
                'start' => 0.0,
                'end' => 10.0,
                'viewers' => 1,
                'repeatviewers' => 1,
                'rawseconds' => 12.0,
                'uniqueseconds' => 10.0,
                'repeatseconds' => 2.0,
                'retention' => 100.0,
                'suppressed' => false,
                'repeatsuppressed' => false,
            ]],
        ], analytics::EXACT_REPORT_MIN_USERS);
        $this->assertFalse($viewing['datasetsuppressed']);
        $this->assertFalse($viewing['totalsuppressed']);
        $this->assertSame(1, $viewing['bins'][0]['viewers']);
        $this->assertSame(1, $viewing['bins'][0]['repeatviewers']);
        $this->assertSame(10.0, $viewing['bins'][0]['uniqueseconds']);
        $this->assertSame(2.0, $viewing['bins'][0]['repeatseconds']);

        $summary = analytics::reaction_summary(3, 1, analytics::EXACT_REPORT_MIN_USERS);
        $this->assertFalse($summary['suppressed']);
        $this->assertSame(3, $summary['eventcount']);
        $this->assertSame(1, $summary['studentcount']);

        $countsummary = analytics::count_summary(2, 1, analytics::EXACT_REPORT_MIN_USERS);
        $this->assertFalse($countsummary['suppressed']);
        $this->assertSame(2, $countsummary['eventcount']);
        $this->assertSame(1, $countsummary['studentcount']);

        $clusters = analytics::cluster_reactions([(object)[
            'userid' => 7,
            'reactionid' => 10,
            'reactionlabel' => 'Question',
            'videotime' => 12.0,
        ]], 10, analytics::EXACT_REPORT_MIN_USERS);
        $this->assertCount(1, $clusters['clusters']);
        $this->assertSame(1, $clusters['clusters'][0]['students']);
    }

    /**
     * Small datasets and small positive bins are hidden by the privacy threshold.
     */
    public function test_privacy_threshold_masks_small_values(): void {
        $small = analytics::apply_privacy_threshold([
            'viewers' => 2,
            'bins' => [['viewers' => 2]],
        ], 5);
        $this->assertTrue($small['datasetsuppressed']);
        $this->assertTrue($small['totalsuppressed']);
        $this->assertSame([], $small['bins']);

        $result = analytics::apply_privacy_threshold([
            'viewers' => 6,
            'bins' => [
                [
                    'viewers' => 6,
                    'repeatviewers' => 3,
                    'rawseconds' => 50.0,
                    'uniqueseconds' => 45.0,
                    'repeatseconds' => 5.0,
                    'retention' => 100.0,
                    'suppressed' => false,
                    'repeatsuppressed' => false,
                ],
                [
                    'viewers' => 2,
                    'repeatviewers' => 1,
                    'rawseconds' => 10.0,
                    'uniqueseconds' => 8.0,
                    'repeatseconds' => 2.0,
                    'retention' => 33.33,
                    'suppressed' => false,
                    'repeatsuppressed' => false,
                ],
            ],
        ], 5);

        $this->assertFalse($result['datasetsuppressed']);
        $this->assertTrue($result['totalsuppressed']);
        $this->assertFalse($result['bins'][0]['suppressed']);
        $this->assertTrue($result['bins'][0]['repeatsuppressed']);
        $this->assertTrue($result['bins'][0]['retentionsuppressed']);
        $this->assertNull($result['bins'][0]['retention']);
        $this->assertNull($result['bins'][0]['repeatviewers']);
        $this->assertNull($result['bins'][0]['repeatseconds']);
        $this->assertTrue($result['bins'][1]['suppressed']);
        $this->assertNull($result['bins'][1]['viewers']);
        $this->assertNull($result['bins'][1]['repeatseconds']);
    }

    /**
     * Empty timeline intervals remain visible as zero and do not disclose a small subgroup.
     */
    public function test_privacy_threshold_keeps_zero_intervals_visible(): void {
        $result = analytics::apply_privacy_threshold([
            'viewers' => 5,
            'bins' => [
                [
                    'viewers' => 0,
                    'repeatviewers' => 0,
                    'rawseconds' => 0.0,
                    'uniqueseconds' => 0.0,
                    'repeatseconds' => 0.0,
                    'retention' => 0.0,
                    'suppressed' => false,
                    'repeatsuppressed' => false,
                ],
            ],
        ], 5);

        $this->assertFalse($result['datasetsuppressed']);
        $this->assertFalse($result['totalsuppressed']);
        $this->assertFalse($result['bins'][0]['suppressed']);
        $this->assertFalse($result['bins'][0]['repeatsuppressed']);
        $this->assertFalse($result['bins'][0]['retentionsuppressed']);
        $this->assertSame(0, $result['bins'][0]['viewers']);
        $this->assertSame(0.0, $result['bins'][0]['repeatseconds']);
    }

    /**
     * A small replay subgroup does not hide the total viewer denominator.
     */
    public function test_privacy_threshold_keeps_total_when_only_replays_are_suppressed(): void {
        $result = analytics::apply_privacy_threshold([
            'viewers' => 6,
            'bins' => [[
                'viewers' => 6,
                'repeatviewers' => 2,
                'rawseconds' => 62.0,
                'uniqueseconds' => 60.0,
                'repeatseconds' => 2.0,
                'retention' => 100.0,
                'suppressed' => false,
                'repeatsuppressed' => false,
            ]],
        ], 5);

        $this->assertFalse($result['totalsuppressed']);
        $this->assertTrue($result['bins'][0]['repeatsuppressed']);
        $this->assertFalse($result['bins'][0]['retentionsuppressed']);
        $this->assertSame(100.0, $result['bins'][0]['retention']);
    }

    /**
     * The reaction overlay reports when its privacy-safe cluster limit is reached.
     */
    public function test_reaction_cluster_limit_is_reported(): void {
        $events = [];
        for ($index = 0; $index <= analytics::MAX_REACTION_CLUSTERS; $index++) {
            $time = $index * 2;
            $events[] = (object)[
                'userid' => 1,
                'reactionid' => 10,
                'reactionlabel' => 'Question',
                'videotime' => $time,
            ];
            $events[] = (object)[
                'userid' => 2,
                'reactionid' => 10,
                'reactionlabel' => 'Question',
                'videotime' => $time + 0.1,
            ];
        }

        $result = analytics::cluster_reactions($events, 1, 2);

        $this->assertTrue($result['truncated']);
        $this->assertCount(analytics::MAX_REACTION_CLUSTERS, $result['clusters']);
    }

    /**
     * Reaction clusters are visible only when enough distinct students contribute.
     */
    public function test_reaction_clusters_apply_student_threshold(): void {
        $events = [
            (object)['userid' => 0, 'reactionid' => 10, 'reactionlabel' => 'Question', 'videotime' => 9],
            (object)['userid' => 1, 'reactionid' => 10, 'reactionlabel' => 'Question', 'videotime' => 10],
            (object)['userid' => 2, 'reactionid' => 10, 'reactionlabel' => 'Question', 'videotime' => 12],
            (object)['userid' => 3, 'reactionid' => 10, 'reactionlabel' => 'Question', 'videotime' => 13],
            (object)['userid' => 1, 'reactionid' => 20, 'reactionlabel' => 'Important', 'videotime' => 40],
            (object)['userid' => 2, 'reactionid' => 20, 'reactionlabel' => 'Important', 'videotime' => 42],
        ];

        $result = analytics::cluster_reactions($events, 10, 3);
        $clusters = $result['clusters'];

        $this->assertFalse($result['truncated']);
        $this->assertCount(1, $clusters);
        $this->assertSame(10, $clusters[0]['reactionid']);
        $this->assertSame(3, $clusters[0]['students']);
        $this->assertSame(3, $clusters[0]['count']);
        $this->assertEqualsWithDelta(11.667, $clusters[0]['timestamp'], 0.001);
    }

    /**
     * Cross-course clusters use the saved reaction key instead of local database ids.
     */
    public function test_reaction_clusters_use_stable_reaction_keys(): void {
        $events = [
            (object)[
                'userid' => 1,
                'reactionid' => 10,
                'reactionkey' => 'question',
                'reactionlabel' => 'Question',
                'videotime' => 10,
            ],
            (object)[
                'userid' => 2,
                'reactionid' => 99,
                'reactionkey' => 'question',
                'reactionlabel' => 'Question',
                'videotime' => 12,
            ],
            (object)[
                'userid' => 3,
                'reactionid' => 10,
                'reactionkey' => 'important',
                'reactionlabel' => 'Important',
                'videotime' => 11,
            ],
        ];

        $result = analytics::cluster_reactions($events, 10, 2);

        $this->assertCount(1, $result['clusters']);
        $this->assertSame('question', $result['clusters'][0]['reactionkey']);
        $this->assertSame(2, $result['clusters'][0]['students']);

        $legacy = analytics::cluster_reactions([
            (object)[
                'videotrackid' => 1,
                'userid' => 1,
                'reactionid' => 5,
                'reactionlabel' => 'Legacy A',
                'videotime' => 20,
            ],
            (object)[
                'videotrackid' => 2,
                'userid' => 2,
                'reactionid' => 5,
                'reactionlabel' => 'Legacy B',
                'videotime' => 21,
            ],
        ], 10, 2);
        $this->assertSame([], $legacy['clusters']);
    }

    /**
     * Privacy-safe reaction clusters remain computable when viewing data is suppressed.
     */
    public function test_reaction_privacy_is_independent_from_viewing_privacy(): void {
        $viewing = analytics::apply_privacy_threshold([
            'viewers' => 1,
            'bins' => [['viewers' => 1]],
        ], 2);
        $reactions = analytics::cluster_reactions([
            (object)[
                'userid' => 1,
                'reactionid' => 10,
                'reactionlabel' => 'Question',
                'videotime' => 20,
            ],
            (object)[
                'userid' => 2,
                'reactionid' => 10,
                'reactionlabel' => 'Question',
                'videotime' => 22,
            ],
        ], 10, 2);

        $this->assertTrue($viewing['datasetsuppressed']);
        $this->assertCount(1, $reactions['clusters']);
        $this->assertSame(2, $reactions['clusters'][0]['students']);
    }

    /**
     * Course groups do not restrict analytics when the activity uses no groups.
     */
    public function test_group_scope_restriction_uses_effective_activity_mode(): void {
        $this->assertFalse(analytics::restrict_to_own_groups(NOGROUPS, false));
        $this->assertFalse(analytics::restrict_to_own_groups(VISIBLEGROUPS, false));
        $this->assertTrue(analytics::restrict_to_own_groups(SEPARATEGROUPS, false));
        $this->assertFalse(analytics::restrict_to_own_groups(SEPARATEGROUPS, true));
    }

    /**
     * Overall reaction counts are exposed only after the distinct-user threshold.
     */
    public function test_reaction_summary_masks_small_populations(): void {
        $hidden = analytics::reaction_summary(4, 1, 2);
        $this->assertTrue($hidden['hasdata']);
        $this->assertTrue($hidden['suppressed']);
        $this->assertNull($hidden['eventcount']);
        $this->assertNull($hidden['studentcount']);

        $visible = analytics::reaction_summary(5, 2, 2);
        $this->assertTrue($visible['hasdata']);
        $this->assertFalse($visible['suppressed']);
        $this->assertSame(5, $visible['eventcount']);
        $this->assertSame(2, $visible['studentcount']);

        $empty = analytics::reaction_summary(0, 0, 2);
        $this->assertFalse($empty['hasdata']);
        $this->assertFalse($empty['suppressed']);
    }

    /**
     * Aggregate state intervals recover unique-view analytics without raw replay data.
     */
    public function test_build_from_states_recovers_unique_viewers(): void {
        $states = [
            (object)['userid' => 1, 'intervaljson' => '[[0,15],[20,30]]'],
            (object)['userid' => 2, 'intervaljson' => '[[0,20]]'],
            (object)['userid' => 0, 'intervaljson' => '[[0,40]]'],
        ];

        $result = analytics::build_from_states($states, 40, 10);

        $this->assertSame(2, $result['viewers']);
        $this->assertFalse($result['repeatmetricsavailable']);
        $this->assertSame(2, $result['bins'][0]['viewers']);
        $this->assertSame(20.0, $result['bins'][0]['uniqueseconds']);
        $this->assertNull($result['bins'][0]['repeatseconds']);
        $this->assertNull($result['repeatseconds']);
    }

    /**
     * Analytics can recover duration from aggregate state when the instance field is empty.
     */
    public function test_resolve_duration_uses_best_persisted_source(): void {
        $this->assertSame(120.0, analytics::resolve_duration(120.0, 140.0, 150.0));
        $this->assertSame(90.0, analytics::resolve_duration(0.0, 90.0, 80.0));
        $this->assertSame(80.0, analytics::resolve_duration(0.0, 0.0, 80.0));
        $this->assertSame(0.0, analytics::resolve_duration(-1.0, -2.0, -3.0));
    }
}
