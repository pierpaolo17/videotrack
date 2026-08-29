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
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Gradebook restore and duplicate-item regression tests.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class gradebook_restore_contract_test extends advanced_testcase {
    /**
     * Module-specific restore must leave grade-item creation to Moodle core.
     */
    public function test_restore_step_does_not_create_grade_item_before_core_grade_restore(): void {
        global $CFG;

        $source = file_get_contents(
            $CFG->dirroot . '/mod/videotrack/backup/moodle2/restore_videotrack_stepslib.php'
        );
        $this->assertIsString($source);
        $this->assertStringNotContainsString('videotrack_grade_item_update(', $source);
        $this->assertStringContainsString('restore_activity_grades_structure_step', $source);
    }

    /**
     * Duplicate canonical items are collapsed without discarding a non-conflicting user grade.
     */
    public function test_gradebook_repair_keeps_one_item_and_moves_user_grades(): void {
        global $CFG, $DB;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/mod/videotrack/db/repairlib.php');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $videotrack = $this->getDataGenerator()->create_module('videotrack', [
            'course' => (int)$course->id,
            'name' => 'Duplicate grade item repair',
            'grade' => 100,
            'gradepass' => 50,
        ]);
        $videotrackid = (int)$videotrack->id;
        $now = time();
        $original = $DB->get_record('grade_items', [
            'courseid' => (int)$course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => $videotrackid,
            'itemnumber' => 0,
        ], '*', MUST_EXIST);
        $originalid = (int)$original->id;
        $DB->insert_record('grade_grades', (object)[
            'itemid' => $originalid,
            'userid' => (int)$student->id,
            'rawgrade' => 80,
            'rawgrademax' => 100,
            'rawgrademin' => 0,
            'finalgrade' => 80,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $duplicate = clone $original;
        unset($duplicate->id);
        $duplicateid = (int)$DB->insert_record('grade_items', $duplicate);
        $this->assertGreaterThan((int)$original->id, $duplicateid);

        videotrack_repair_preproduction_gradebook_rows();

        $items = $DB->get_records('grade_items', [
            'courseid' => (int)$course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => $videotrackid,
            'itemnumber' => 0,
        ]);
        $this->assertCount(1, $items);
        $kept = reset($items);
        $this->assertSame($duplicateid, (int)$kept->id);

        $grades = $DB->get_records('grade_grades', ['itemid' => $duplicateid]);
        $this->assertCount(1, $grades);
        $grade = reset($grades);
        $this->assertSame((int)$student->id, (int)$grade->userid);
        $this->assertEquals(80.0, (float)$grade->rawgrade);
        $this->assertFalse($DB->record_exists('grade_grades', ['itemid' => $originalid]));
    }

    /**
     * Gradebook repair must remove an item whose activity has no course module.
     */
    public function test_gradebook_repair_removes_missing_course_module_context(): void {
        global $CFG, $DB;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/mod/videotrack/db/repairlib.php');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $videotrackid = (int)$DB->insert_record('videotrack', (object)[
            'course' => (int)$course->id,
            'name' => 'Missing course module',
            'grade' => 100,
            'gradepass' => 50,
        ]);
        $now = time();
        $gradeitemid = (int)$DB->insert_record('grade_items', (object)[
            'courseid' => (int)$course->id,
            'itemname' => 'Missing course module',
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => $videotrackid,
            'itemnumber' => 0,
            'grademax' => 100,
            'grademin' => 0,
            'gradepass' => 50,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $DB->insert_record('grade_grades', (object)[
            'itemid' => $gradeitemid,
            'userid' => (int)$student->id,
            'rawgrade' => 75,
            'rawgrademax' => 100,
            'rawgrademin' => 0,
            'finalgrade' => 75,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        videotrack_repair_preproduction_gradebook_rows();

        $this->assertFalse($DB->record_exists('grade_items', ['id' => $gradeitemid]));
        $this->assertFalse($DB->record_exists('grade_grades', ['itemid' => $gradeitemid]));
        $this->assertTrue($DB->record_exists('videotrack', ['id' => $videotrackid]));
    }

    /**
     * The custom uninstall hook must clear grades before core removes contexts.
     */
    public function test_uninstall_hook_cleans_valid_and_orphan_grade_items_first(): void {
        global $CFG, $DB;

        $this->resetAfterTest(true);
        require_once($CFG->dirroot . '/mod/videotrack/db/uninstall.php');

        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $videotrack = $this->getDataGenerator()->create_module('videotrack', [
            'course' => (int)$course->id,
            'name' => 'Uninstall grade cleanup',
            'grade' => 100,
        ]);
        $validitem = $DB->get_record('grade_items', [
            'courseid' => (int)$course->id,
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => (int)$videotrack->id,
            'itemnumber' => 0,
        ], '*', MUST_EXIST);
        $now = time();
        $DB->insert_record('grade_grades', (object)[
            'itemid' => (int)$validitem->id,
            'userid' => (int)$student->id,
            'rawgrade' => 90,
            'rawgrademax' => 100,
            'rawgrademin' => 0,
            'finalgrade' => 90,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $orphanitemid = (int)$DB->insert_record('grade_items', (object)[
            'courseid' => (int)$course->id,
            'itemname' => 'Orphan uninstall residue',
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => 987654321,
            'itemnumber' => 0,
            'grademax' => 100,
            'grademin' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
        $DB->insert_record('grade_grades', (object)[
            'itemid' => $orphanitemid,
            'userid' => (int)$student->id,
            'rawgrade' => 60,
            'rawgrademax' => 100,
            'rawgrademin' => 0,
            'finalgrade' => 60,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);

        $this->assertTrue(\xmldb_videotrack_uninstall());

        $this->assertFalse($DB->record_exists('grade_items', [
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
        ]));
        $this->assertFalse($DB->record_exists('grade_grades', ['itemid' => (int)$validitem->id]));
        $this->assertFalse($DB->record_exists('grade_grades', ['itemid' => $orphanitemid]));
        $this->assertTrue($DB->record_exists('videotrack', ['id' => (int)$videotrack->id]));
        $this->assertTrue($DB->record_exists('course_modules', ['id' => (int)$videotrack->cmid]));
        $this->assertTrue($DB->record_exists('modules', ['name' => 'videotrack']));
    }
}
