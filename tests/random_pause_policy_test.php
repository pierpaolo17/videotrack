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

use mod_videotrack\local\form_validation;
use mod_videotrack\local\random_pause_policy;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Attention policy boundaries, persistence and real activity backup/restore.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(random_pause_policy::class)]
#[CoversClass(form_validation::class)]
final class random_pause_policy_test extends \advanced_testcase {
    /**
     * Saved duration controls automatic bounds, rounding and short/long video limits.
     */
    public function test_automatic_thirds_use_saved_duration_with_operational_limits(): void {
        $cases = [
            [30, 60, 60],
            [90, 60, 60],
            [123.456, 60, 82],
            [900, 300, 600],
            [1800, 600, 1200],
            [3600, 1200, 2400],
            [86400, 7200, 7200],
        ];
        foreach ($cases as [$duration, $low, $high]) {
            $activity = (object)['durationseconds' => $duration];
            $this->assertSame(['min' => $low, 'max' => $high], random_pause_policy::activity_bounds($activity));
            $this->assertSame($duration, $activity->durationseconds);
        }
    }

    /**
     * Unknown or corrupt duration retains the bounded site fallback.
     */
    public function test_zero_or_invalid_duration_uses_site_fallback(): void {
        $this->resetAfterTest();
        set_config('randompauseminseconds', 120, 'mod_videotrack');
        set_config('randompausemaxseconds', 900, 'mod_videotrack');
        foreach ([0, -1, INF, NAN] as $duration) {
            $this->assertSame(['min' => 120, 'max' => 900], random_pause_policy::activity_bounds(
                (object)['durationseconds' => $duration]
            ));
        }
    }

    /**
     * Independent overrides retain their meaning when they cross an automatic bound.
     */
    public function test_independent_and_fixed_overrides_take_precedence(): void {
        $cases = [
            [0, 0, 300, 600],
            [900, 0, 900, 900],
            [0, 120, 120, 120],
            [120, 600, 120, 600],
            [300, 300, 300, 300],
        ];
        foreach ($cases as [$minimum, $maximum, $low, $high]) {
            $this->assertSame(['min' => $low, 'max' => $high], random_pause_policy::activity_bounds((object)[
                'durationseconds' => 900,
                'randompauseminseconds' => $minimum,
                'randompausemaxseconds' => $maximum,
            ]));
        }
    }

    /**
     * Raw form input cannot silently truncate a fractional, negative or malformed interval.
     */
    public function test_raw_overrides_reject_invalid_input(): void {
        foreach ([null, '', ' ', 0, '0'] as $value) {
            $this->assertSame(0, random_pause_policy::parse_override($value));
        }
        foreach ([60, ' 60 ', '007200'] as $value) {
            $this->assertSame((int)$value, random_pause_policy::parse_override($value));
        }
        foreach ([-1, 1, 59, 7201, '60.5', '60.0', '1e3', '60x', [], true, INF] as $value) {
            $this->assertNull(random_pause_policy::parse_override($value));
        }
    }

    /**
     * The real form validation reports invalid fields and inverted explicit values.
     */
    public function test_form_validation_preserves_optional_bounds_and_reports_errors(): void {
        $this->assertSame([], random_pause_policy::form_errors([]));
        $this->assertSame([], random_pause_policy::form_errors(['randompauseminseconds' => '900']));
        $this->assertSame([], random_pause_policy::form_errors(['randompausemaxseconds' => '60']));
        $this->assertSame(['randompauseminseconds', 'randompausemaxseconds'], array_keys(
            random_pause_policy::form_errors(['randompauseminseconds' => '60.5', 'randompausemaxseconds' => -1])
        ));
        $this->assertSame(['randompausemaxseconds'], array_keys(random_pause_policy::form_errors([
            'randompauseminseconds' => 600, 'randompausemaxseconds' => 120,
        ])));
        $this->assertArrayHasKey('randompauseminseconds', form_validation::scalar_settings_errors([
            'randompauseminseconds' => '60.5',
        ], ''));
    }

    /**
     * Import normalisation repairs invalid values but keeps automatic values as zero.
     */
    public function test_import_normalisation_preserves_automatic_and_orders_manual_values(): void {
        foreach ([[null, null, 0, 0], ['60.5', 99999, 0, 0], [600, 120, 120, 600]] as $case) {
            [$minimum, $maximum, $low, $high] = $case;
            $activity = (object)['randompauseminseconds' => $minimum, 'randompausemaxseconds' => $maximum];
            random_pause_policy::normalise_overrides($activity);
            $this->assertSame($low, $activity->randompauseminseconds);
            $this->assertSame($high, $activity->randompausemaxseconds);
        }
    }

    /**
     * Actual module create/update persist overrides without changing duration or completion settings.
     */
    public function test_activity_create_and_update_persist_policy_without_changing_duration(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 900, 'completionpercent' => 50,
            'randomfocuspauses' => 1, 'randompauseminseconds' => '120', 'randompausemaxseconds' => '600',
        ]);
        $data = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->assertSame(['min' => 120, 'max' => 600], random_pause_policy::activity_bounds($data));
        $data->instance = $activity->id;
        $data->coursemodule = $activity->cmid;
        $data->randomfocuspauses = 0;
        $data->randompauseminseconds = '';
        $data->randompausemaxseconds = '600';
        $this->assertTrue(\videotrack_update_instance($data));
        $stored = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->assertSame(0, (int)$stored->randompauseminseconds);
        $this->assertSame(600, (int)$stored->randompausemaxseconds);
        $this->assertSame(0, (int)$stored->randomfocuspauses);
        $this->assertEquals(900, $stored->durationseconds);
        $this->assertSame(50, (int)$stored->completionpercent);
    }

    /**
     * Moodle's actual duplication runs the backup/restore plan and retains manual and automatic settings.
     */
    public function test_activity_duplication_preserves_manual_and_automatic_policy(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/lib.php');
        $course = $this->getDataGenerator()->create_course();
        foreach ([[120, 600], [0, 0]] as [$minimum, $maximum]) {
            $activity = $this->getDataGenerator()->create_module('videotrack', [
                'course' => $course->id, 'durationseconds' => 900, 'randomfocuspauses' => 1,
                'randompauseminseconds' => $minimum, 'randompausemaxseconds' => $maximum,
            ]);
            $cm = get_fast_modinfo($course)->get_cm($activity->cmid);
            if (method_exists(\core_courseformat\local\cmactions::class, 'duplicate')) {
                $copy = \core_courseformat\formatactions::cm((int)$course->id)->duplicate((int)$cm->id);
            } else {
                $copy = \duplicate_module($course, $cm);
            }
            $this->assertNotNull($copy);
            $stored = $DB->get_record('videotrack', ['id' => $copy->instance], '*', MUST_EXIST);
            $this->assertSame($minimum, (int)$stored->randompauseminseconds);
            $this->assertSame($maximum, (int)$stored->randompausemaxseconds);
            $this->assertSame(1, (int)$stored->randomfocuspauses);
            $this->assertEquals(900, $stored->durationseconds);
        }
    }
    /**
     * The attention fixture must authorise half speed through the real activity save path.
     */
    public function test_attention_fixture_half_speed_requires_an_explicit_allowed_rate(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('playbackspeeds', '0.75,1', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $settings = [
            'course' => $course->id,
            'durationseconds' => 1800,
            'allowplaybackratechange' => 1,
            'allowseekforward' => 1,
            'maxplaybackrate' => 100,
            'randomfocuspauses' => 1,
            'randompauseminseconds' => 60,
            'randompausemaxseconds' => 60,
        ];
        $unselected = $this->getDataGenerator()->create_module('videotrack', $settings);
        $unselected = $DB->get_record('videotrack', ['id' => $unselected->id], '*', MUST_EXIST);
        $this->assertNotContains(0.5, \videotrack_get_tracking_playback_speeds($unselected));
        $selected = $this->getDataGenerator()->create_module('videotrack', $settings + [
            'playbackspeed_0.5' => '0.5',
            'playbackspeed_1' => '1',
        ]);
        $selected = $DB->get_record('videotrack', ['id' => $selected->id], '*', MUST_EXIST);
        $this->assertContains(0.5, \videotrack_get_tracking_playback_speeds($selected));
        $this->assertSame(['min' => 60, 'max' => 60], random_pause_policy::activity_bounds($selected));
    }
}
