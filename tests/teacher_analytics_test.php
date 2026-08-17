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

use mod_videotrack\local\analytics;
use mod_videotrack\local\teacher_analytics;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for cross-course dashboard helpers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(teacher_analytics::class)]
final class teacher_analytics_test extends \advanced_testcase {
    /**
     * Relative periods produce stable inclusive timestamp bounds.
     */
    public function test_period_bounds(): void {
        $now = 2000000000;
        $this->assertSame([0, 0], teacher_analytics::period_bounds(0, $now));
        $this->assertSame([$now - (7 * DAYSECS), $now], teacher_analytics::period_bounds(7, $now));
        $this->assertSame([0, 0], teacher_analytics::period_bounds(-1, $now));
    }

    /**
     * The teacher dashboard exposes an exact one-learner aggregate when requested.
     */
    public function test_dashboard_rows_expose_single_learner_with_exact_threshold(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_user();
        $student = $generator->create_user();
        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $generator->enrol_user($teacher->id, $course->id, $teacherroleid);
        $generator->enrol_user($student->id, $course->id, $studentroleid);
        $videotrackgenerator = $generator->get_plugin_generator('mod_videotrack');
        $videotrack = $videotrackgenerator->create_instance([
            'course' => $course->id,
            'durationseconds' => 100,
        ]);

        $now = time();
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $videotrack->cmid,
            'userid' => $student->id,
            'videoid' => 'teacher-dashboard-test',
            'lastposition' => 40.0,
            'durationseconds' => 100.0,
            'uniquecoveredseconds' => 40.0,
            'completionpercent' => 40.0,
            'intervaljson' => '[[0,40]]',
            'iscompleted' => 0,
            'timemodified' => $now,
            'timecreated' => $now,
        ]);

        $courses = teacher_analytics::accessible_courses($teacher->id);
        $this->assertArrayHasKey($course->id, $courses);
        $this->assertSame($course->fullname, $courses[$course->id]->fullname);
        $this->assertSame((int)$course->visible, (int)$courses[$course->id]->visible);

        $dashboard = teacher_analytics::dashboard_rows(
            $teacher->id,
            analytics::EXACT_REPORT_MIN_USERS,
            $course->id,
            $videotrack->id
        );

        $this->assertArrayHasKey($course->id, $dashboard);
        $this->assertArrayHasKey($videotrack->id, $dashboard[$course->id]['rows']);
        $row = $dashboard[$course->id]['rows'][$videotrack->id];
        $this->assertFalse($row->summary['datasetsuppressed']);
        $this->assertSame(1, $row->summary['started']['eventcount']);
        $this->assertSame(40.0, $row->summary['averagepercent']);
        $this->assertSame(40.0, $row->summary['medianpercent']);
    }

    /**
     * The teacher dashboard controller requests exact aggregates and has no privacy masking UI.
     */
    public function test_teacher_dashboard_controller_requests_exact_aggregates(): void {
        $source = file_get_contents(__DIR__ . '/../reports_teacher.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('analytics::EXACT_REPORT_MIN_USERS', $source);
        $this->assertStringContainsString("'mod/videotrack:viewcoursereport'", $source);
        $this->assertStringNotContainsString("videotrack_get_config_int('analyticsminusers'", $source);
        $this->assertStringNotContainsString("get_string('coursereport:privacy_notice'", $source);
        $this->assertStringNotContainsString("get_string('coursereport:privacy_suppressed'", $source);
        $this->assertStringNotContainsString("['datasetsuppressed']", $source);
    }
}
