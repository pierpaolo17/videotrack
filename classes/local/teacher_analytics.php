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

namespace mod_videotrack\local;

use context_course;
use context_module;
use stdClass;

/**
 * Cross-course, capability-safe dashboard aggregation for report viewers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class teacher_analytics {
    /**
     * Returns courses where the user can view the VideoTrack course report.
     *
     * @param int $userid Report viewer id.
     * @return array Courses keyed by id.
     */
    public static function accessible_courses(int $userid): array {
        $courses = get_user_capability_course(
            'mod/videotrack:viewcoursereport',
            $userid,
            true,
            'fullname,visible'
        );
        $accessible = [];
        foreach ($courses as $course) {
            $courseid = (int)$course->id;
            if ($courseid === SITEID) {
                continue;
            }
            $context = context_course::instance($courseid, IGNORE_MISSING);
            if (
                !$context
                || (empty($course->visible)
                    && !has_capability('moodle/course:viewhiddencourses', $context, $userid))
            ) {
                continue;
            }
            $accessible[$courseid] = $course;
        }

        uasort($accessible, static function (stdClass $left, stdClass $right): int {
            return [\core_text::strtolower((string)$left->fullname), (int)$left->id]
                <=> [\core_text::strtolower((string)$right->fullname), (int)$right->id];
        });
        return $accessible;
    }

    /**
     * Builds all accessible dashboard rows using the course analytics service.
     *
     * @param int $userid Report viewer id.
     * @param int $minusers Privacy threshold.
     * @param int $courseid Optional course filter.
     * @param int $activityid Optional VideoTrack instance filter.
     * @param int $groupid Optional group filter for the selected course.
     * @param int $perioddays Number of days to include, or zero for all time.
     * @param int|null $now Current timestamp override for tests.
     * @return array Rows grouped by course id.
     */
    public static function dashboard_rows(
        int $userid,
        int $minusers,
        int $courseid = 0,
        int $activityid = 0,
        int $groupid = 0,
        int $perioddays = 0,
        ?int $now = null
    ): array {
        $courses = self::accessible_courses($userid);
        if ($courseid > 0) {
            $courses = isset($courses[$courseid]) ? [$courseid => $courses[$courseid]] : [];
        }

        [$timestart, $timeend] = self::period_bounds($perioddays, $now);
        $result = [];
        foreach ($courses as $course) {
            $context = context_course::instance((int)$course->id, IGNORE_MISSING);
            if (!$context || !has_capability('mod/videotrack:viewcoursereport', $context, $userid)) {
                continue;
            }
            $rows = course_analytics::get_course_rows(
                $course,
                $userid,
                $minusers,
                $activityid,
                $groupid,
                $timestart,
                $timeend
            );
            if ($rows) {
                $result[(int)$course->id] = [
                    'course' => $course,
                    'rows' => $rows,
                ];
            }
        }
        return $result;
    }

    /**
     * Returns accessible VideoTrack activities for one selected course.
     *
     * @param stdClass $course Course record.
     * @param int $userid Report viewer id.
     * @return array Activity labels keyed by instance id.
     */
    public static function activity_options(stdClass $course, int $userid): array {
        $modinfo = get_fast_modinfo($course, $userid);
        $cms = $modinfo->instances['videotrack'] ?? [];
        $coursecontext = context_course::instance((int)$course->id);
        $options = [];
        $sortnames = [];
        foreach ($cms as $cm) {
            if (!$cm->uservisible) {
                continue;
            }
            $context = context_module::instance((int)$cm->id, IGNORE_MISSING);
            if (!$context || !report_access::can_view_aggregate($context, $userid)) {
                continue;
            }
            $instanceid = (int)$cm->instance;
            $sortnames[$instanceid] = (string)$cm->name;
            $options[$instanceid] = format_string($cm->name, true, ['context' => $coursecontext]);
        }
        uksort($options, static function (int $leftid, int $rightid) use ($sortnames): int {
            return [\core_text::strtolower($sortnames[$leftid]), $leftid]
                <=> [\core_text::strtolower($sortnames[$rightid]), $rightid];
        });
        return $options;
    }

    /**
     * Returns groups the report viewer may use as a filter in one course.
     *
     * @param stdClass $course Course record.
     * @param int $userid Report viewer id.
     * @return array Group labels keyed by group id.
     */
    public static function group_options(stdClass $course, int $userid): array {
        global $CFG;

        require_once($CFG->libdir . '/grouplib.php');
        $context = context_course::instance((int)$course->id);
        $groups = has_capability('moodle/site:accessallgroups', $context, $userid)
            ? groups_get_all_groups((int)$course->id)
            : groups_get_all_groups((int)$course->id, $userid);

        $options = [];
        foreach ($groups as $group) {
            $options[(int)$group->id] = format_string($group->name, true, ['context' => $context]);
        }
        natcasesort($options);
        return $options;
    }

    /**
     * Converts a relative period into inclusive timestamp bounds.
     *
     * @param int $perioddays Number of days, or zero for all time.
     * @param int|null $now Current timestamp override.
     * @return array Start and end timestamps.
     */
    public static function period_bounds(int $perioddays, ?int $now = null): array {
        if ($perioddays <= 0) {
            return [0, 0];
        }
        $now = $now ?? time();
        return [max(0, $now - ($perioddays * DAYSECS)), $now];
    }
}
