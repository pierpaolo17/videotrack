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

use invalid_parameter_exception;
use mod_videotrack\local\report_time_filter;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for teacher-report time-filter handling.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(report_time_filter::class)]
final class report_time_filter_test extends \advanced_testcase {
    /**
     * Date-only report filters reject malformed and impossible calendar values.
     */
    public function test_date_to_timestamp_rejects_invalid_values(): void {
        $this->assertSame(0, report_time_filter::date_to_timestamp(''));
        $this->assertSame(0, report_time_filter::date_to_timestamp('2026-02-30'));
        $this->assertSame(0, report_time_filter::date_to_timestamp('17-08-2026'));
        $this->assertSame(
            make_timestamp(2026, 8, 17, 0, 0, 0),
            report_time_filter::date_to_timestamp('2026-08-17')
        );
        $this->assertSame(
            make_timestamp(2026, 8, 17, 23, 59, 59),
            report_time_filter::end_date_to_timestamp('2026-08-17')
        );
        $this->assertSame(0, report_time_filter::end_date_to_timestamp('2026-02-30'));
    }

    /**
     * Structured request components retain their numeric duration semantics.
     */
    public function test_structured_time_components_are_parsed(): void {
        $method = new \ReflectionMethod(report_time_filter::class, 'structured_time');
        $this->assertSame(3723.0, $method->invoke(null, [
            'hours' => '1',
            'minutes' => '2',
            'seconds' => '3',
        ]));
    }

    /**
     * Structured component bounds remain fail-closed.
     */
    public function test_structured_time_rejects_out_of_range_components(): void {
        $method = new \ReflectionMethod(report_time_filter::class, 'structured_time');
        $this->expectException(invalid_parameter_exception::class);
        $method->invoke(null, [
            'hours' => '0',
            'minutes' => '60',
            'seconds' => '0',
        ]);
    }

    /**
     * Duration filters keep the structured hours/minutes/seconds accessibility contract.
     */
    public function test_duration_filter_preserves_structured_controls(): void {
        $markup = report_time_filter::duration_filter('timefrom', 'From', 3661.0, true);

        $this->assertStringContainsString('name="timefrom_hours"', $markup);
        $this->assertStringContainsString('value="1"', $markup);
        $this->assertStringContainsString('name="timefrom_minutes"', $markup);
        $this->assertStringContainsString('name="timefrom_seconds"', $markup);
        $this->assertStringContainsString('role="group"', $markup);
        $this->assertStringContainsString('aria-labelledby="id_timefrom_group_label"', $markup);
    }
}
