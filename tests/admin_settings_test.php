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
use mod_videotrack\admin\setting_int_range;
use mod_videotrack\admin\setting_nonnegative_int;
use mod_videotrack\admin\setting_retention_days;

/**
 * PHPUnit coverage for custom admin setting validation.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class admin_settings_test extends advanced_testcase {
    /**
     * Load Moodle admin setting base classes before instantiating plugin settings.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        require_once($CFG->libdir . '/adminlib.php');
    }

    /**
     * Non-negative integer settings accept zero and reject unsafe values.
     *
     * @covers \mod_videotrack\admin\setting_nonnegative_int::validate
     */
    public function test_nonnegative_int_validation_accepts_zero_and_rejects_invalid_values(): void {
        $setting = new setting_nonnegative_int('mod_videotrack/testnonnegative', 'Test', 'Test', 0, PARAM_INT);

        $this->assertTrue($setting->validate('0'));
        $this->assertTrue($setting->validate('730'));
        $this->assertNotTrue($setting->validate('-1'));
        $this->assertNotTrue($setting->validate('1.5'));
        $this->assertNotTrue($setting->validate('3651'));
    }

    /**
     * Range settings enforce both configured boundaries.
     *
     * @covers \mod_videotrack\admin\setting_int_range::validate
     */
    public function test_int_range_validation_enforces_configured_bounds(): void {
        $setting = new setting_int_range('mod_videotrack/testrange', 'Test', 'Test', 100, 20, 500);

        $this->assertTrue($setting->validate('20'));
        $this->assertTrue($setting->validate('500'));
        $this->assertNotTrue($setting->validate('19'));
        $this->assertNotTrue($setting->validate('501'));
        $this->assertNotTrue($setting->validate('abc'));
    }

    /**
     * Unlimited retention cannot be saved without explicit administrator confirmation.
     *
     * @covers \mod_videotrack\admin\setting_retention_days::write_setting
     */
    public function test_unlimited_retention_requires_explicit_confirmation(): void {
        $this->resetAfterTest();

        unset($_POST['s_mod_videotrack_retentionunlimitedconfirmed']);
        set_config('retentionperioddays', 730, 'mod_videotrack');

        $setting = new setting_retention_days(
            'mod_videotrack/retentionperioddays',
            'Retention',
            'Retention',
            730,
            PARAM_INT
        );

        $result = $setting->write_setting('0');

        $this->assertSame(get_string('setting:retentionunlimitedconfirm_required', 'mod_videotrack'), $result);
        $this->assertSame('730', get_config('mod_videotrack', 'retentionperioddays'));
    }
}
