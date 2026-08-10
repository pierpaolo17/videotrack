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
use mod_videotrack\local\learner_scope;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for the canonical learner participation contract.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(learner_scope::class)]
final class learner_scope_test extends advanced_testcase {
    /**
     * Participation remains independent from report access for dual-role users.
     */
    public function test_can_participate_is_independent_from_report_access(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);

        $dualrole = $generator->create_user();
        $generator->enrol_user($dualrole->id, $course->id, $studentroleid);
        role_assign($teacherroleid, $dualrole->id, \context_course::instance($course->id)->id);

        $teacher = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, $teacherroleid);

        $this->assertTrue(learner_scope::can_participate($context, $dualrole->id));
        $this->assertTrue(has_capability('mod/videotrack:viewreport', $context, $dualrole->id));
        $this->assertFalse(learner_scope::can_participate($context, $teacher->id));
        $this->assertTrue(has_capability('mod/videotrack:viewreport', $context, $teacher->id));
    }

    /**
     * Learner-facing entry points reuse the canonical participation helper.
     */
    public function test_participation_entrypoints_reuse_canonical_helper(): void {
        foreach ([
            __DIR__ . '/../view.php',
            __DIR__ . '/../bookmarks.php',
            __DIR__ . '/../classes/external/helper.php',
        ] as $path) {
            $source = file_get_contents($path);
            $this->assertIsString($source);
            $this->assertStringContainsString('learner_scope::can_participate($context)', $source);
        }
    }
}
