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

use mod_videotrack\local\report_support;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for teacher-report request and scope helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(report_support::class)]
final class report_support_test extends \advanced_testcase {
    /**
     * Date-only report filters reject malformed and impossible calendar values.
     */
    public function test_date_to_timestamp_rejects_invalid_values(): void {
        $this->assertSame(0, report_support::date_to_timestamp(''));
        $this->assertSame(0, report_support::date_to_timestamp('2026-02-30'));
        $this->assertSame(0, report_support::date_to_timestamp('17-08-2026'));
        $this->assertSame(
            make_timestamp(2026, 8, 17, 0, 0, 0),
            report_support::date_to_timestamp('2026-08-17')
        );
        $this->assertSame(
            make_timestamp(2026, 8, 17, 23, 59, 59),
            report_support::date_to_timestamp('2026-08-17', true)
        );
    }

    /**
     * Duration filters keep the structured hours/minutes/seconds accessibility contract.
     */
    public function test_duration_filter_preserves_structured_controls(): void {
        $markup = report_support::duration_filter('timefrom', 'From', 3661.0, true);

        $this->assertStringContainsString('name="timefrom_hours"', $markup);
        $this->assertStringContainsString('value="1"', $markup);
        $this->assertStringContainsString('name="timefrom_minutes"', $markup);
        $this->assertStringContainsString('name="timefrom_seconds"', $markup);
        $this->assertStringContainsString('role="group"', $markup);
        $this->assertStringContainsString('aria-labelledby="id_timefrom_group_label"', $markup);
    }

    /**
     * User labels retain privacy behaviour when email visibility is disabled.
     */
    public function test_user_label_respects_email_visibility(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'email' => 'ada@example.invalid',
        ]);

        $withoutemail = report_support::user_label((int)$user->id, [(int)$user->id => $user], false);
        $withemail = report_support::user_label((int)$user->id, [(int)$user->id => $user], true);

        $this->assertStringContainsString('Ada', $withoutemail);
        $this->assertStringNotContainsString('ada@example.invalid', $withoutemail);
        $this->assertStringContainsString('ada@example.invalid', $withemail);
        $this->assertSame('#99', report_support::user_label(99, [], false));
    }

    /**
     * Report tabs keep learner-only and full-report visibility separated.
     */
    public function test_tabs_preserve_capability_dependent_set(): void {
        $learner = report_support::tabs(123, false, ['userid' => 7]);
        $teacher = report_support::tabs(123, true, ['userid' => 7]);

        $this->assertCount(2, $learner);
        $this->assertSame(['student', 'cumulative'], array_column($learner, 'id'));
        $this->assertCount(5, $teacher);
        $this->assertSame(
            ['student', 'cumulative', 'analytics', 'export', 'recalculate'],
            array_column($teacher, 'id')
        );
    }

    /**
     * Empty Analytics/acknowledgement scope sets must never broaden the SQL result.
     */
    public function test_empty_scope_conditions_remain_deny_all(): void {
        [$analyticssql, $analyticsparams] = report_support::analytics_scope_condition([], 'analytics', 7);
        [$acksql, $ackparams] = report_support::acknowledgement_scope_condition([], 'ack', 7);

        $this->assertSame('1 = 0', $analyticssql);
        $this->assertSame([], $analyticsparams);
        $this->assertSame('1 = 0', $acksql);
        $this->assertSame([], $ackparams);
    }
}
