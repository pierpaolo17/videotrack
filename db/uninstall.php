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

/**
 * VideoTrack uninstall hook.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Remove VideoTrack gradebook data before core deletes module contexts.
 *
 * Moodle invokes this hook before mod plugin cleanup removes course_modules,
 * module contexts and the modules registry row. Grade items with one valid
 * activity context therefore use the Grade API, preserving its normal file,
 * history and regrade behaviour. Already malformed rows cannot safely resolve
 * a module context and are removed through the existing DML-only repair helper.
 *
 * @return bool Always true after successful cleanup.
 */
function xmldb_videotrack_uninstall(): bool {
    global $CFG, $DB;

    require_once($CFG->libdir . '/gradelib.php');
    require_once(__DIR__ . '/repairlib.php');

    $gradeitems = $DB->get_records('grade_items', [
        'itemtype' => 'mod',
        'itemmodule' => 'videotrack',
    ], 'id ASC');
    if (!$gradeitems) {
        return true;
    }

    $dbman = $DB->get_manager();
    $hasactivitytable = $dbman->table_exists('videotrack');
    $moduleid = (int)$DB->get_field('modules', 'id', ['name' => 'videotrack'], IGNORE_MISSING);
    $fallbackids = [];
    $fallbackcourses = [];

    foreach ($gradeitems as $gradeitem) {
        $instancecourse = false;
        if ($hasactivitytable) {
            $instancecourse = $DB->get_field(
                'videotrack',
                'course',
                ['id' => (int)$gradeitem->iteminstance],
                IGNORE_MISSING
            );
        }

        $coursemodulecount = 0;
        if ($moduleid > 0 && $instancecourse !== false) {
            $coursemodulecount = $DB->count_records('course_modules', [
                'module' => $moduleid,
                'instance' => (int)$gradeitem->iteminstance,
                'course' => (int)$gradeitem->courseid,
            ]);
        }

        $hasvalidcontext = $instancecourse !== false
            && (int)$instancecourse === (int)$gradeitem->courseid
            && $coursemodulecount === 1;
        if (!$hasvalidcontext) {
            $fallbackids[] = (int)$gradeitem->id;
            $fallbackcourses[] = (int)$gradeitem->courseid;
            continue;
        }

        $gradeitemobject = new grade_item($gradeitem, false);
        if (!$gradeitemobject->delete('moduninstall')) {
            $fallbackids[] = (int)$gradeitem->id;
            $fallbackcourses[] = (int)$gradeitem->courseid;
        }
    }

    videotrack_repair_preproduction_delete_grade_items($fallbackids);
    foreach (array_unique(array_filter($fallbackcourses)) as $courseid) {
        if ($DB->record_exists('course', ['id' => (int)$courseid])) {
            grade_force_full_regrading((int)$courseid);
        }
    }

    return true;
}
