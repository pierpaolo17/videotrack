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

use mod_videotrack\local\form_reaction_settings;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for general reaction fields, notice context, presets and completion locks.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_reaction_settings::class)]
final class form_reaction_settings_test extends \advanced_testcase {
    /**
     * Load the real QuickForm and shared preset APIs.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        require_once($CFG->libdir . '/formslib.php');
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * General fields retain defaults, exact dependencies and safe editor options in both form contexts.
     */
    public function test_defaults_dependencies_and_notice_editor_context_are_preserved(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('default_clusterwindow', 15, 'mod_videotrack');
        set_config('reaction_presets', '[]', 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $coursecontext = \context_course::instance($course->id);
        $contexts = [$coursecontext, \context_module::instance($forum->cmid)];
        foreach ($contexts as $context) {
            $form = new \MoodleQuickForm('videotrack_reaction_general_' . $context->id, 'post', '');

            form_reaction_settings::add_elements($form, $context, $coursecontext);

            $defaults = [
                'reactionsenabled' => 0, 'showreactionnotice' => 1, 'reactionsrequired' => 0,
                'minreactions' => 0, 'requireallreactiontypes' => 0, 'showstudentreport' => 1,
            ];
            foreach ($defaults as $field => $value) {
                $this->assertSame($value, (int)$form->getElement($field)->getValue());
            }
            $this->assertSame([15], array_map('intval', $form->getElement('clusterwindow')->getSelected()));
            $this->assertFalse($form->getElement('clusterwindow')->isFrozen());
            $this->assertTrue($form->elementExists('reactionsheader'));
            $this->assertTrue($form->elementExists('reactioncompletionguide'));
            $this->assertFalse($form->elementExists('completionsettings_locked_notice'));
            $editor = $form->getElement('reactionnotice_editor');
            $options = (new \ReflectionProperty($editor, '_options'))->getValue($editor);
            $this->assertSame($context, $options['context']);
            $this->assertSame(0, $options['maxfiles']);
            $this->assertSame(0, $options['maxbytes']);
            $this->assertFalse($options['trusttext']);
            $this->assertFalse($options['noclean']);
            $dependencies = (new \ReflectionProperty($form, '_dependencies'))->getValue($form);
            $this->assertSame([
                'reactionsenabled' => [
                    'notchecked' => [
                        '1' => [
                            'showreactionnotice', 'reactionnotice_editor', 'reactionsrequired', 'minreactions',
                            'requireallreactiontypes',
                        ],
                    ],
                ],
                'showreactionnotice' => ['notchecked' => ['1' => ['reactionnotice_editor']]],
                'reactionsrequired' => ['notchecked' => ['1' => ['minreactions']]],
            ], $dependencies);
            $hideifs = (new \ReflectionProperty($form, '_hideifs'))->getValue($form);
            $this->assertSame(['reactionsenabled' => ['notchecked' => ['1' => ['reactionconfiguration_note']]]], $hideifs);
        }
    }

    /**
     * Only configured named presets add the selector and its separate JSON carrier.
     */
    public function test_preset_selector_keeps_configuration_guard_and_saved_values(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        unset_config('default_clusterwindow', 'mod_videotrack');
        unset_config('reaction_presets', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $empty = new \MoodleQuickForm('videotrack_reaction_no_presets', 'post', '');

        form_reaction_settings::add_elements($empty, $context, $context);

        $this->assertFalse($empty->elementExists('reactionpreset'));
        $this->assertFalse($empty->elementExists('reactionpreset_json'));
        $this->assertSame([30], array_map('intval', $empty->getElement('clusterwindow')->getSelected()));
        videotrack_save_presets([
            ['key' => 'alpha', 'name' => 'Alpha', 'reactions' => []],
            ['key' => 'beta', 'name' => 'Beta', 'reactions' => []],
            ['key' => 'nameless', 'name' => '', 'reactions' => []],
        ]);
        $form = new \MoodleQuickForm('videotrack_reaction_presets', 'post', '');

        form_reaction_settings::add_elements($form, $context, $context);

        $selector = $form->getElement('reactionpreset');
        $html = $selector->toHtml();
        foreach (['', 'alpha', 'beta'] as $value) {
            $this->assertStringContainsString('value="' . $value . '"', $html);
        }
        $this->assertStringNotContainsString('value="nameless"', $html);
        $this->assertTrue($form->elementExists('reactionpreset_json'));
        $form->setDefaults(['reactionpreset' => 'beta', 'reactionpreset_json' => '{"reactions":[]}']);
        $this->assertSame(['beta'], $selector->getSelected());
        $this->assertSame('{"reactions":[]}', $form->getElement('reactionpreset_json')->getValue());
    }

    /**
     * Completion permission, independently of player permission, locks only the existing requested rows.
     */
    public function test_completion_lock_respects_course_permission_and_row_count(): void {
        $this->resetAfterTest();
        set_config('default_clusterwindow', 30, 'mod_videotrack');
        set_config('reaction_presets', '[]', 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack completion settings editor', 'videotrackcompletionsettingseditor', '');
        role_assign($roleid, $user->id, $context->id);
        role_assign($roleid, $user->id, $othercontext->id);
        assign_capability('mod/videotrack:overridecompletionsettings', CAP_ALLOW, $roleid, $context->id);
        assign_capability('mod/videotrack:overrideplayersettings', CAP_ALLOW, $roleid, $othercontext->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('mod/videotrack:overridecompletionsettings', $context));
        $this->assertFalse(has_capability('mod/videotrack:overridecompletionsettings', $othercontext));
        $this->assertFalse(has_capability('mod/videotrack:overrideplayersettings', $context));
        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            foreach ([0, 2] as $repeatcount) {
                $form = new \MoodleQuickForm('videotrack_reaction_lock_' . $scope->id . '_' . $repeatcount, 'post', '');
                form_reaction_settings::add_elements($form, $scope, $scope);
                for ($i = 0; $i < 3; $i++) {
                    $field = 'reactionrequired[' . $i . ']';
                    $form->addElement('advcheckbox', $field, 'Reaction ' . $i);
                    $form->setType($field, PARAM_BOOL);
                    $form->setDefault($field, 1);
                }
                $form->setDefaults(['reactionsrequired' => 1, 'minreactions' => 2, 'requireallreactiontypes' => 1]);

                form_reaction_settings::apply_completion_lock($form, $scope, $repeatcount);

                foreach (['clusterwindow', 'reactionsrequired', 'minreactions', 'requireallreactiontypes'] as $field) {
                    $this->assertSame($scope->id !== $context->id, $form->getElement($field)->isFrozen());
                }
                for ($i = 0; $i < 3; $i++) {
                    $element = $form->getElement('reactionrequired[' . $i . ']');
                    $this->assertSame($scope->id !== $context->id && $i < $repeatcount, $element->isFrozen());
                    $this->assertSame(1, (int)$element->getValue());
                }
                foreach (['reactionsenabled', 'showreactionnotice', 'reactionnotice_editor', 'showstudentreport'] as $field) {
                    $this->assertFalse($form->getElement($field)->isFrozen());
                }
                $this->assertSame(2, (int)$form->getElement('minreactions')->getValue());
                $this->assertSame($scope->id !== $context->id, $form->elementExists('completionsettings_locked_notice'));
            }
        }
    }
}
