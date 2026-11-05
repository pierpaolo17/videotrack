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

use mod_videotrack\local\form_playback_settings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for site-controlled playback form defaults, permissions and rate choices.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_playback_settings::class)]
final class form_playback_settings_test extends \advanced_testcase {
    /**
     * Load the real Moodle QuickForm implementation.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        require_once($CFG->libdir . '/formslib.php');
    }

    /**
     * Missing/empty values use the original defaults, while explicit zero remains disabled.
     */
    public function test_site_defaults_preserve_missing_empty_zero_and_one(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $settings = [
            'showcontrols' => 'default_showcontrols',
            'disablekeyboard' => 'default_disablekeyboard',
            'showfullscreen' => 'default_showfullscreen',
            'allowseekforward' => 'default_allowseekforward',
            'allowseekbackward' => 'default_allowseekbackward',
            'allowplaybackratechange' => 'default_allowplaybackratechange',
        ];
        $cases = [
            [null, [1, 0, 1, 1, 1, 1]],
            ['', [1, 0, 1, 1, 1, 1]],
            ['0', [0, 0, 0, 0, 0, 0]],
            ['1', [1, 1, 1, 1, 1, 1]],
        ];
        foreach ($cases as $index => [$value, $expected]) {
            foreach ($settings as $setting) {
                if ($value === null) {
                    unset_config($setting, 'mod_videotrack');
                } else {
                    set_config($setting, $value, 'mod_videotrack');
                }
            }
            $form = new \MoodleQuickForm('videotrack_playback_defaults_' . $index, 'post', '');

            form_playback_settings::add_elements($form, $context);

            foreach (array_keys($settings) as $position => $field) {
                $element = $form->getElement($field);
                $this->assertSame($expected[$position], (int)$element->getValue());
                $this->assertFalse($element->isFrozen());
            }
            $this->assertFalse($form->elementExists('playersettings_locked_notice'));
        }
    }

    /**
     * Course permissions freeze exactly the original seven fields and retain the unlocked controls.
     */
    public function test_course_override_lock_preserves_defaults_and_unlocked_fields(): void {
        $this->resetAfterTest();
        $settings = [
            'default_showcontrols' => 0, 'default_disablekeyboard' => 1, 'default_showfullscreen' => 1,
            'default_allowseekforward' => 0, 'default_allowseekbackward' => 1, 'default_allowplaybackratechange' => 0,
            'resumeplayback' => 1, 'maxplaybackrate' => 150,
        ];
        foreach ($settings as $setting => $value) {
            set_config($setting, $value, 'mod_videotrack');
        }
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack playback settings editor', 'videotrackplaybacksettingseditor', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:overrideplayersettings', CAP_ALLOW, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', $context));
        $this->assertFalse(has_capability('mod/videotrack:overrideplayersettings', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $form = new \MoodleQuickForm('videotrack_playback_lock_' . $scope->id, 'post', '');

            form_playback_settings::add_elements($form, $scope);

            $locked = [
                'showcontrols' => 0, 'disablekeyboard' => 1, 'showfullscreen' => 1,
                'allowseekforward' => 0, 'allowseekbackward' => 1, 'allowplaybackratechange' => 0,
            ];
            foreach ($locked as $field => $value) {
                $element = $form->getElement($field);
                $this->assertSame($scope->id !== $context->id, $element->isFrozen());
                $this->assertSame($value, (int)$element->getValue());
            }
            $blocked = $form->getElement('blockedseekplaybackrate');
            $this->assertSame($scope->id !== $context->id, $blocked->isFrozen());
            $this->assertSame([50], array_map('intval', $blocked->getSelected()));
            foreach (['resumeplayback', 'maxplaybackrate'] as $field) {
                $this->assertFalse($form->getElement($field)->isFrozen());
            }
            $this->assertSame(1, (int)$form->getElement('resumeplayback')->getValue());
            $this->assertSame([150], array_map('intval', $form->getElement('maxplaybackrate')->getSelected()));
            $this->assertSame($scope->id !== $context->id, $form->elementExists('playersettings_locked_notice'));
            if ($scope->id !== $context->id) {
                $this->assertStringContainsString(
                    get_string('setting:lockedbyAdmin', 'mod_videotrack'),
                    $form->getElement('playersettings_locked_notice')->toHtml()
                );
            }
        }
    }

    /**
     * Rate choice values and saved selections retain their hundredths-based persistence contract.
     */
    public function test_rate_choices_and_saved_values_are_preserved(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('maxplaybackrate', 150, 'mod_videotrack');
        set_config('resumeplayback', 1, 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_playback_rates', 'post', '');

        form_playback_settings::add_elements($form, $context);

        $maximum = $form->getElement('maxplaybackrate');
        $blocked = $form->getElement('blockedseekplaybackrate');
        foreach ([0, 100, 125, 150, 175, 200, 300, 400] as $value) {
            $this->assertStringContainsString('value="' . $value . '"', $maximum->toHtml());
        }
        foreach ([50, 75, 100] as $value) {
            $this->assertStringContainsString('value="' . $value . '"', $blocked->toHtml());
        }
        $this->assertSame([150], array_map('intval', $maximum->getSelected()));
        $this->assertSame([50], array_map('intval', $blocked->getSelected()));
        $form->setDefaults(['maxplaybackrate' => 175, 'blockedseekplaybackrate' => 75, 'resumeplayback' => 0]);
        $this->assertSame([175], array_map('intval', $maximum->getSelected()));
        $this->assertSame([75], array_map('intval', $blocked->getSelected()));
        $this->assertSame(0, (int)$form->getElement('resumeplayback')->getValue());
    }
}
