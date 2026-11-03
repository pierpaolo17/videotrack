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

use mod_videotrack\local\form_forum_definition;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for course-scoped Forum choices in the activity form.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_forum_definition::class)]
final class form_forum_definition_test extends \advanced_testcase {
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

        form_forum_definition::add_elements($form, (int)$course->id, null);

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

        form_forum_definition::add_elements($form, (int)$course->id, (object)[]);

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

        form_forum_definition::add_elements($form, (int)$course->id, (object)['linkedforumid' => $saved->id]);

        $destination = $form->getElement('linkedforumid');
        $html = $destination->toHtml();
        $this->assertFalse($destination->isFrozen());
        $this->assertEmpty($form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertFalse($form->elementExists('forumnoneavailable'));
        $this->assertStringContainsString('value="' . $saved->id . '"', $html);
        $this->assertStringContainsString(get_string('forum:configuredunavailable', 'mod_videotrack'), $html);
        $this->assertStringNotContainsString('Other course private label', $html);
    }
}
