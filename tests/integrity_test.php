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
use mod_videotrack\local\integrity;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for privacy-safe integrity-indicator helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(integrity::class)]
final class integrity_test extends advanced_testcase {
    /**
     * Supported event types are accepted and unknown values are rejected.
     */
    public function test_event_type_validation_is_allowlist_based(): void {
        foreach (integrity::EVENT_TYPES as $eventtype) {
            $this->assertSame($eventtype, integrity::validate_event_type($eventtype));
        }

        $this->expectException(\invalid_parameter_exception::class);
        integrity::validate_event_type('customsignal');
    }

    /**
     * Small contributing groups are hidden independently for every signal type.
     */
    public function test_summary_applies_distinct_user_privacy_threshold(): void {
        $summary = integrity::summarise([
            (object)['eventtype' => 'tabhidden', 'eventcount' => 12, 'studentcount' => 6],
            (object)['eventtype' => 'pipattempt', 'eventcount' => 3, 'studentcount' => 2],
        ], 5);

        $this->assertSame(12, $summary['tabhidden']['eventcount']);
        $this->assertSame(6, $summary['tabhidden']['studentcount']);
        $this->assertFalse($summary['tabhidden']['suppressed']);
        $this->assertNull($summary['pipattempt']['eventcount']);
        $this->assertNull($summary['pipattempt']['studentcount']);
        $this->assertTrue($summary['pipattempt']['suppressed']);
        $this->assertFalse($summary['windowblur']['hasdata']);
    }

    /**
     * Random attention pauses always remain inside the configured exclusive bounds.
     */
    public function test_random_pause_bounds_match_the_feature_contract(): void {
        $this->assertGreaterThan(300, integrity::RANDOM_PAUSE_MIN_SECONDS);
        $this->assertLessThan(1800, integrity::RANDOM_PAUSE_MAX_SECONDS);
        $this->assertLessThanOrEqual(
            integrity::RANDOM_PAUSE_MAX_SECONDS,
            integrity::RANDOM_PAUSE_MIN_SECONDS
        );
    }
}
