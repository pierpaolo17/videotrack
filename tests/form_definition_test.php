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

use mod_videotrack\local\csv_export;
use mod_videotrack\local\form_definition;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for delegated CSV, Forum and acknowledgement form groups.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_definition::class)]
final class form_definition_test extends \advanced_testcase {
    /**
     * Load the real QuickForm and the shared VideoTrack Forum queries.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        require_once($CFG->libdir . '/formslib.php');
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * New activities cannot enable posting when no compatible Forum exists.
     */
    public function test_empty_course_disables_posting_and_freezes_destination(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $form = new \MoodleQuickForm('videotrack_forum_empty', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, null);

        $this->assertSame('disabled', $form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertTrue($form->getElement('linkedforumid')->isFrozen());
        $this->assertTrue($form->elementExists('forumnoneavailable'));
        $this->assertTrue($form->elementExists('forumsubjecttemplate'));
        $this->assertTrue($form->elementExists('forumprivacyinfo'));
    }

    /**
     * Choices keep course/type/deletion filters, ordering and hidden-module labels.
     */
    public function test_choices_keep_course_scope_and_module_status(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $first = $generator->create_module('forum', ['course' => $course->id, 'type' => 'qanda', 'name' => 'Alpha']);
        $hidden = $generator->create_module('forum', [
            'course' => $course->id, 'type' => 'general', 'name' => 'Beta', 'visible' => 0,
        ]);
        $incompatible = $generator->create_module('forum', ['course' => $course->id, 'type' => 'single']);
        $other = $generator->create_module('forum', ['course' => $othercourse->id, 'type' => 'general']);
        $deleting = $generator->create_module('forum', ['course' => $course->id, 'type' => 'general']);
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $deleting->cmid]);
        $form = new \MoodleQuickForm('videotrack_forum_choices', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, (object)[]);

        $destination = $form->getElement('linkedforumid');
        $html = $destination->toHtml();
        $this->assertFalse($destination->isFrozen());
        $this->assertEmpty($form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertFalse($form->elementExists('forumnoneavailable'));
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('value="' . $first->id . '"', $html);
        $this->assertStringContainsString('value="' . $hidden->id . '"', $html);
        $this->assertStringContainsString('Beta (' . get_string('forum:statushidden', 'mod_videotrack') . ')', $html);
        foreach ([$incompatible, $other, $deleting] as $excluded) {
            $this->assertStringNotContainsString('value="' . $excluded->id . '"', $html);
        }
        $this->assertLessThan(
            strpos($html, 'value="' . $hidden->id . '"'),
            strpos($html, 'value="' . $first->id . '"')
        );
    }

    /**
     * An unavailable saved destination remains explicit without importing another course's choices.
     */
    public function test_unavailable_saved_forum_remains_an_editable_placeholder(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $saved = $generator->create_module('forum', [
            'course' => $othercourse->id, 'type' => 'general', 'name' => 'Other course private label',
        ]);
        $form = new \MoodleQuickForm('videotrack_forum_saved', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, (object)['linkedforumid' => $saved->id]);

        $destination = $form->getElement('linkedforumid');
        $html = $destination->toHtml();
        $this->assertFalse($destination->isFrozen());
        $this->assertEmpty($form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertFalse($form->elementExists('forumnoneavailable'));
        $this->assertStringContainsString('value="' . $saved->id . '"', $html);
        $this->assertStringContainsString(get_string('forum:configuredunavailable', 'mod_videotrack'), $html);
        $this->assertStringNotContainsString('Other course private label', $html);
    }

    /**
     * Acknowledgement choices and editor options retain the actual activity context.
     */
    public function test_acknowledgement_fields_keep_context_and_safe_editor_options(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $contexts = [
            \context_course::instance($course->id),
            \context_module::instance($forum->cmid),
        ];
        $timings = [
            \mod_videotrack\local\acknowledgement::TIMING_ANYTIME,
            \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END,
        ];
        foreach ($contexts as $context) {
            $form = new \MoodleQuickForm('videotrack_acknowledgement_' . $context->id, 'post', '');

            form_definition::add_acknowledgement_elements($form, $context);

            $this->assertTrue($form->elementExists('acknowledgementheader'));
            $this->assertTrue($form->elementExists('acknowledgementenabled'));
            $html = $form->getElement('acknowledgementtiming')->toHtml();
            foreach ($timings as $timing) {
                $this->assertStringContainsString('value="' . $timing . '"', $html);
            }
            $editor = $form->getElement('acknowledgement_editor');
            $options = (new \ReflectionProperty($editor, '_options'))->getValue($editor);
            $this->assertSame($context, $options['context']);
            $this->assertSame(0, $options['maxfiles']);
            $this->assertSame(0, $options['maxbytes']);
            $this->assertFalse($options['trusttext']);
            $this->assertFalse($options['noclean']);
        }
    }

    /**
     * CSV choices retain delimiter inheritance, flat field names and visible field order.
     */
    public function test_csv_fields_keep_delimiters_names_and_order(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', '');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_csv_fields', 'post', '');

        form_definition::add_csv_elements($form, $context);

        $delimiter = $form->getElement('csvdelimiter');
        $this->assertSame([csv_export::DELIMITER_INHERIT], $delimiter->getSelected());
        $html = $delimiter->toHtml();
        foreach (['inherit', 'comma', 'semicolon', 'section', 'hash', 'pipe'] as $value) {
            $this->assertStringContainsString('value="' . $value . '"', $html);
        }
        $fields = [
            'coursefullname', 'courseshortname', 'courseid', 'instancename', 'videolink',
            'username', 'email', 'city', 'country', 'idnumber', 'institution', 'department',
            'phone1', 'phone2', 'address',
        ];
        $checkboxes = $form->getElement('csvexportfieldsgroup')->getElements();
        $names = array_map(static fn($checkbox): string => $checkbox->getName(), $checkboxes);
        $this->assertSame(array_map([csv_export::class, 'form_element_name'], $fields), $names);
        $this->assertTrue($form->elementExists('csvexportheader'));
        $this->assertTrue($form->elementExists('csvexportfields_desc'));
    }

    /**
     * The actual context and hidden-identity permissions control enabled CSV checkboxes.
     */
    public function test_csv_identity_fields_respect_context_and_hidden_field_permissions(): void {
        $this->resetAfterTest();
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', 'idnumber');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack CSV identity viewer', 'videotrackcsvidentityviewer', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('moodle/site:viewuseridentity', CAP_ALLOW, $roleid, $context->id);
        assign_capability('moodle/course:viewhiddenuserfields', CAP_PREVENT, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('moodle/site:viewuseridentity', $context));
        $this->assertFalse(has_capability('moodle/site:viewuseridentity', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $form = new \MoodleQuickForm('videotrack_csv_permissions_' . $scope->id, 'post', '');
            form_definition::add_csv_elements($form, $scope);
            $checkboxes = [];
            foreach ($form->getElement('csvexportfieldsgroup')->getElements() as $checkbox) {
                $checkboxes[$checkbox->getName()] = $checkbox;
            }
            $email = $checkboxes[csv_export::form_element_name('email')];
            if ($scope->id === $context->id) {
                $this->assertEmpty($email->getAttribute('disabled'));
            } else {
                $this->assertSame('disabled', $email->getAttribute('disabled'));
            }
            foreach (['idnumber', 'phone1'] as $field) {
                $element = $checkboxes[csv_export::form_element_name($field)];
                $this->assertSame('disabled', $element->getAttribute('disabled'));
                $this->assertStringContainsString(get_string('notavailable'), $element->toHtml());
            }
            $coursefield = $checkboxes[csv_export::form_element_name('coursefullname')];
            $this->assertEmpty($coursefield->getAttribute('disabled'));
        }
    }
}
