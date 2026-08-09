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
use mod_videotrack\local\course_analytics;
use mod_videotrack\local\learner_scope;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for privacy-safe course dashboard calculations.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(course_analytics::class)]
#[CoversClass(learner_scope::class)]
final class course_analytics_test extends advanced_testcase {
    /**
     * Median supports odd, even and empty datasets.
     */
    public function test_median_handles_common_dataset_shapes(): void {
        $this->assertSame(30.0, course_analytics::median([10, 50, 30]));
        $this->assertSame(25.0, course_analytics::median([10, 20, 30, 40]));
        $this->assertNull(course_analytics::median([]));
    }

    /**
     * Course summaries expose mean, median, completion and the main visible drop.
     */
    public function test_state_summary_reuses_timeline_analytics(): void {
        $states = [
            $this->state(1, 100, true, '[[0,40]]', 40),
            $this->state(2, 100, true, '[[0,40]]', 40),
            $this->state(3, 50, false, '[[0,20]]', 40),
            $this->state(4, 50, false, '[[0,20]]', 40),
            $this->state(5, 25, false, '[[0,10]]', 40),
        ];

        $summary = course_analytics::summarise_states($states, 40, 2);

        $this->assertFalse($summary['datasetsuppressed']);
        $this->assertSame(5, $summary['started']['eventcount']);
        $this->assertSame(65.0, $summary['averagepercent']);
        $this->assertSame(50.0, $summary['medianpercent']);
        $this->assertSame(2, $summary['completions']['eventcount']);
        $this->assertSame(3, $summary['noncompleted']['eventcount']);
        $this->assertSame(20.0, $summary['maindrop']['timestamp']);
        $this->assertSame(40.0, $summary['maindrop']['percentagepoints']);
    }

    /**
     * A suppressed positive timeline bin hides the retention denominator and main drop.
     */
    public function test_state_summary_hides_drop_when_retention_denominator_is_suppressed(): void {
        $states = [
            $this->state(1, 100, true, '[[0,40]]', 40),
            $this->state(2, 75, true, '[[0,30]]', 40),
            $this->state(3, 50, false, '[[0,20]]', 40),
            $this->state(4, 50, false, '[[0,20]]', 40),
            $this->state(5, 25, false, '[[0,10]]', 40),
        ];

        $summary = course_analytics::summarise_states($states, 40, 2);

        $this->assertFalse($summary['datasetsuppressed']);
        $this->assertNull($summary['maindrop']);
    }

    /**
     * A small activity population does not expose exact dashboard values.
     */
    public function test_state_summary_masks_small_activity_population(): void {
        $states = [
            $this->state(1, 100, true, '[[0,40]]', 40),
            $this->state(2, 50, false, '[[0,20]]', 40),
        ];

        $summary = course_analytics::summarise_states($states, 40, 5);

        $this->assertTrue($summary['datasetsuppressed']);
        $this->assertTrue($summary['started']['suppressed']);
        $this->assertNull($summary['started']['eventcount']);
        $this->assertNull($summary['averagepercent']);
        $this->assertNull($summary['medianpercent']);
        $this->assertTrue($summary['completions']['suppressed']);
        $this->assertTrue($summary['noncompleted']['suppressed']);
        $this->assertNull($summary['maindrop']);
    }

    /**
     * Completion subgroups remain masked even when the total population is visible.
     */
    public function test_state_summary_masks_small_completion_subgroups(): void {
        $states = [
            $this->state(1, 100, true, '[[0,40]]', 40),
            $this->state(2, 75, false, '[[0,30]]', 40),
            $this->state(3, 50, false, '[[0,20]]', 40),
            $this->state(4, 50, false, '[[0,20]]', 40),
            $this->state(5, 25, false, '[[0,10]]', 40),
        ];

        $summary = course_analytics::summarise_states($states, 40, 5);

        $this->assertFalse($summary['datasetsuppressed']);
        $this->assertSame(5, $summary['started']['eventcount']);
        $this->assertSame(60.0, $summary['averagepercent']);
        $this->assertTrue($summary['completions']['suppressed']);
        $this->assertTrue($summary['noncompleted']['suppressed']);
    }

    /**
     * Suppressed timeline bins are excluded from main-drop calculations.
     */
    public function test_largest_drop_ignores_suppressed_bins(): void {
        $drop = course_analytics::largest_adjacent_drop([
            ['start' => 0.0, 'retention' => 100.0, 'suppressed' => false],
            ['start' => 10.0, 'retention' => null, 'suppressed' => true],
            ['start' => 20.0, 'retention' => 60.0, 'suppressed' => false],
            ['start' => 30.0, 'retention' => 20.0, 'suppressed' => false],
        ]);

        $this->assertSame(30.0, $drop['timestamp']);
        $this->assertSame(40.0, $drop['percentagepoints']);
    }

    /**
     * Explicit participation remains available to a learner who also has report access.
     */
    public function test_participation_scope_is_independent_from_report_access(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        role_assign($teacherroleid, $user->id, \context_course::instance($course->id)->id);
        $forum = $generator->create_module('forum', [
            'course' => $course->id,
            'type' => 'general',
        ]);
        $cm = get_coursemodule_from_id('forum', $forum->cmid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);

        $this->assertTrue(has_capability('mod/videotrack:viewreport', $context, $user->id));
        $this->assertTrue(has_capability('mod/videotrack:participate', $context, $user->id, false));
        $this->assertTrue(learner_scope::user_is_visible(
            $context,
            $cm,
            $course,
            $user->id,
            $user->id
        ));

        $teacher = $generator->create_user();
        $generator->enrol_user($teacher->id, $course->id, $teacherroleid);
        $this->assertFalse(has_capability('mod/videotrack:participate', $context, $teacher->id, false));
    }

    /**
     * Creates one aggregate state fixture.
     *
     * @param int $userid User id.
     * @param float $percent Completion percentage.
     * @param bool $completed Completion state.
     * @param string $intervaljson Encoded watched intervals.
     * @param float $duration Video duration.
     * @return \stdClass State fixture.
     */
    private function state(
        int $userid,
        float $percent,
        bool $completed,
        string $intervaljson,
        float $duration
    ): \stdClass {
        return (object)[
            'userid' => $userid,
            'completionpercent' => $percent,
            'iscompleted' => $completed ? 1 : 0,
            'intervaljson' => $intervaljson,
            'durationseconds' => $duration,
        ];
    }
}
