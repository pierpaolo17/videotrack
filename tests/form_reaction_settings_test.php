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
 * Behavioural checks for reaction fields, repeated icon controls, presets and completion locks.
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
                    form_reaction_settings::add_row_elements($form, $i, ['return_types' => FILE_INTERNAL]);
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

    /**
     * Nonconsecutive rows retain field order, cleaning types, unique picker targets and internal image options.
     */
    public function test_row_fields_preserve_order_types_picker_targets_and_file_options(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $PAGE = new \moodle_page();
        $PAGE->set_course($course);
        $PAGE->set_context(\context_course::instance($course->id));
        $form = new \MoodleQuickForm('videotrack_reaction_row_contract', 'post', '');
        $options = [
            'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
            'maxbytes' => 0, 'return_types' => FILE_INTERNAL,
        ];
        foreach ([0, 3, 29] as $i) {
            form_reaction_settings::add_row_elements($form, $i, $options);

            $elements = (new \ReflectionProperty($form, '_elements'))->getValue($form);
            $row = array_slice($elements, -10);
            $this->assertCount(10, $row);
            $this->assertSame('html', $row[0]->getType());
            $this->assertStringContainsString(get_string('reactionx', 'mod_videotrack', $i + 1), $row[0]->toHtml());
            $this->assertStringContainsString('videotrack-reaction-config-heading', $row[0]->toHtml());
            $expected = [
                'reactionid[' . $i . ']' => 'hidden',
                'reactionlabel[' . $i . ']' => 'text',
                'reactiondescription[' . $i . ']' => 'text',
                'reactionicontype[' . $i . ']' => 'select',
                'reactioniconvalue[' . $i . ']' => 'text',
                'reactioniconvalue_suggestions_' . $i => 'static',
                'reactioniconfile_' . $i => 'filemanager',
                'reactioniconfile_notice_' . $i => 'static',
                'reactionrequired[' . $i . ']' => 'advcheckbox',
            ];
            $actual = [];
            foreach (array_slice($row, 1) as $element) {
                $actual[$element->getName()] = $element->getType();
                $this->assertFalse($element->isFrozen());
            }
            $this->assertSame($expected, $actual);
            $types = (new \ReflectionProperty($form, '_types'))->getValue($form);
            $expectedtypes = [
                'reactionid' => PARAM_INT, 'reactionlabel' => PARAM_TEXT, 'reactiondescription' => PARAM_TEXT,
                'reactionicontype' => PARAM_ALPHA, 'reactioniconvalue' => PARAM_TEXT, 'reactionrequired' => PARAM_BOOL,
            ];
            foreach ($expectedtypes as $field => $type) {
                $this->assertSame($type, $types[$field . '[' . $i . ']']);
            }
            $iconvalue = $form->getElement('reactioniconvalue[' . $i . ']');
            $listid = 'videotrack-reactioniconvalue-suggestions-' . $i;
            $this->assertSame($listid, $iconvalue->getAttribute('list'));
            $this->assertSame('videotrack-icon-value-input', $iconvalue->getAttribute('class'));
            $this->assertSame(24, (int)$iconvalue->getAttribute('size'));
            $suggestions = $form->getElement('reactioniconvalue_suggestions_' . $i)->toHtml();
            $this->assertStringContainsString('<datalist id="' . $listid . '">', $suggestions);
            $this->assertStringContainsString('data-videotrack-icon-target="reactioniconvalue[' . $i . ']"', $suggestions);
            $this->assertStringContainsString('data-videotrack-icon-type-target="reactionicontype[' . $i . ']"', $suggestions);
            $this->assertStringContainsString('aria-haspopup="dialog"', $suggestions);
            $notice = $form->getElement('reactioniconfile_notice_' . $i)->toHtml();
            $this->assertStringContainsString(get_string('reactioniconfile_notice', 'mod_videotrack'), $notice);
            $file = $form->getElement('reactioniconfile_' . $i);
            $actualoptions = (new \ReflectionProperty($file, '_options'))->getValue($file);
            foreach ($options as $key => $value) {
                $this->assertSame($value, $actualoptions[$key]);
            }
            $this->assertNotEmpty($file->getHelpButton());
            $this->assertSame(0, (int)$form->getElement('reactionid[' . $i . ']')->getValue());
            $selecthtml = $form->getElement('reactionicontype[' . $i . ']')->toHtml();
            foreach (['', 'emoji', 'fa', 'file'] as $value) {
                $this->assertStringContainsString('value="' . $value . '"', $selecthtml);
            }
        }
        $this->assertFalse($form->elementExists('reactionid[1]'));
        $this->assertFalse($form->elementExists('reaction_add_fields'));
        $this->assertFalse($form->elementExists('reaction_repeats'));
    }

    /**
     * Empty, emoji, Font Awesome and file choices preserve saved values and exact per-row dependencies.
     */
    public function test_row_visibility_is_isolated_and_saved_icon_values_are_preserved(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $form = new \MoodleQuickForm('videotrack_reaction_row_visibility', 'post', '');
        $expecteddependencies = [];
        $expectedhideifs = [];
        $defaults = [];
        $iconvalues = ['', '🙂', 'fa-solid fa-star', ''];
        foreach (['', 'emoji', 'fa', 'file'] as $i => $icontype) {
            form_reaction_settings::add_row_elements($form, $i, ['return_types' => FILE_INTERNAL]);
            $iconfield = 'reactioniconvalue[' . $i . ']';
            $typefield = 'reactionicontype[' . $i . ']';
            $filefield = 'reactioniconfile_' . $i;
            $suggestions = 'reactioniconvalue_suggestions_' . $i;
            $expecteddependencies[$typefield] = [
                'eq' => ['file' => [$iconfield], '' => [$iconfield]],
                'neq' => ['file' => [$filefield]],
            ];
            $expectedhideifs[$typefield] = [
                'eq' => ['file' => [$iconfield, $suggestions], '' => [$iconfield, $suggestions]],
                'neq' => ['file' => [$filefield, 'reactioniconfile_notice_' . $i]],
            ];
            $defaults['reactionid'][$i] = 100 + $i;
            $defaults['reactionlabel'][$i] = 'Saved label ' . $i;
            $defaults['reactiondescription'][$i] = 'Saved description ' . $i;
            $defaults['reactionicontype'][$i] = $icontype;
            $defaults['reactioniconvalue'][$i] = $iconvalues[$i];
            $defaults['reactionrequired'][$i] = 1;
            $defaults[$filefield] = 200 + $i;
        }
        $form->setDefaults($defaults);
        $dependencies = (new \ReflectionProperty($form, '_dependencies'))->getValue($form);
        $hideifs = (new \ReflectionProperty($form, '_hideifs'))->getValue($form);
        $this->assertSame($expecteddependencies, $dependencies);
        $this->assertSame($expectedhideifs, $hideifs);
        foreach (['', 'emoji', 'fa', 'file'] as $i => $icontype) {
            $this->assertSame([$icontype], $form->getElement('reactionicontype[' . $i . ']')->getSelected());
            $this->assertSame(100 + $i, (int)$form->getElement('reactionid[' . $i . ']')->getValue());
            $this->assertSame('Saved label ' . $i, $form->getElement('reactionlabel[' . $i . ']')->getValue());
            $this->assertSame('Saved description ' . $i, $form->getElement('reactiondescription[' . $i . ']')->getValue());
            $this->assertSame($iconvalues[$i], $form->getElement('reactioniconvalue[' . $i . ']')->getValue());
            $this->assertSame(200 + $i, (int)$form->getElement('reactioniconfile_' . $i)->getValue());
            $this->assertSame(1, (int)$form->getElement('reactionrequired[' . $i . ']')->getValue());
            $this->assertFalse($form->getElement('reactionrequired[' . $i . ']')->isFrozen());
        }
    }

    /**
     * The activity form retains minimum/capped counts, the no-submit add button and the actual row count.
     */
    public function test_activity_form_retains_repeat_count_add_button_and_upload_options(): void {
        global $CFG, $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');
        $course = $this->getDataGenerator()->create_course();
        $PAGE = new \moodle_page();
        $PAGE->set_course($course);
        $PAGE->set_context(\context_course::instance($course->id));
        $originalpost = $_POST;
        $originalget = $_GET;
        try {
            $cases = [[1, '', 4], [4, 'Add', 5], [31, 'Add', 30]];
            foreach ($cases as [$requested, $add, $expected]) {
                unset($_GET['reaction_repeats'], $_GET['reaction_add_fields']);
                $_POST['reaction_repeats'] = $requested;
                $_POST['reaction_add_fields'] = $add;
                $quickform = new \MoodleQuickForm('videotrack_reaction_repeat_' . $expected, 'post', '');
                $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
                (new \ReflectionProperty($form, '_form'))->setValue($form, $quickform);
                (new \ReflectionProperty($form, '_instance'))->setValue($form, 0);

                (new \ReflectionMethod($form, 'add_reaction_elements'))->invoke($form);

                $count = (new \ReflectionProperty($form, 'reactionrepeatcount'))->getValue($form);
                $this->assertSame($expected, $count);
                $this->assertSame($expected, (int)$quickform->getElement('reaction_repeats')->getValue());
                $this->assertSame($expected < 30, $quickform->elementExists('reaction_add_fields'));
                $this->assertSame($expected < 30, $quickform->isNoSubmitButton('reaction_add_fields'));
                for ($i = 0; $i < $expected; $i++) {
                    $this->assertTrue($quickform->elementExists('reactionrequired[' . $i . ']'));
                    $file = $quickform->getElement('reactioniconfile_' . $i);
                    $options = (new \ReflectionProperty($file, '_options'))->getValue($file);
                    $this->assertSame(FILE_INTERNAL, $options['return_types']);
                    $this->assertSame(1, $options['maxfiles']);
                    $this->assertSame(0, $options['subdirs']);
                    $this->assertSame(['.jpg', '.jpeg', '.png', '.webp', '.gif'], $options['accepted_types']);
                }
                $this->assertFalse($quickform->elementExists('reactionid[' . $expected . ']'));
                $this->assertFalse($quickform->elementExists('reactioniconfile_' . $expected));
            }
        } finally {
            $_POST = $originalpost;
            $_GET = $originalget;
        }
    }
}
