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
     * Small datasets and small positive bins are hidden by the privacy threshold.
     */
    public function test_privacy_threshold_masks_small_values(): void {
        $small = analytics::apply_privacy_threshold([
            'viewers' => 2,
            'bins' => [['viewers' => 2]],
        ], 5);
        $this->assertTrue($small['datasetsuppressed']);
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
        $this->assertFalse($result['bins'][0]['suppressed']);
        $this->assertTrue($result['bins'][0]['repeatsuppressed']);
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
        $this->assertFalse($result['bins'][0]['suppressed']);
        $this->assertFalse($result['bins'][0]['repeatsuppressed']);
        $this->assertSame(0, $result['bins'][0]['viewers']);
        $this->assertSame(0.0, $result['bins'][0]['repeatseconds']);
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
}
