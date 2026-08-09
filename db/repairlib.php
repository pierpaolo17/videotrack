<?php
// This file is part of Moodle - https://moodle.org/
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
 * Database-only repair helpers used by install and upgrade steps.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Remove stale or ambiguous pre-production VideoTrack grade items.
 *
 * A valid VideoTrack grade item must be the unique itemnumber 0 row for an
 * existing VideoTrack instance in the same course. Orphan rows, non-canonical
 * item numbers, course mismatches and duplicate canonical rows can make
 * Moodle's standard grading form fail with morethanonerecordinfetch before the
 * activity form is rendered.
 *
 * The repair intentionally uses DML only. A single valid canonical grade item
 * is preserved. When duplicate canonical rows exist, all ambiguous rows for
 * that instance are removed so the normal VideoTrack save path can recreate
 * exactly one canonical item.
 *
 * @return void
 */
function videotrack_repair_preproduction_gradebook_rows(): void {
    global $DB;

    $gradeitems = $DB->get_records_select(
        'grade_items',
        'itemtype = :itemtype AND itemmodule = :itemmodule',
        ['itemtype' => 'mod', 'itemmodule' => 'videotrack'],
        'iteminstance ASC, itemnumber ASC, id ASC',
        'id, courseid, iteminstance, itemnumber'
    );
    if (!$gradeitems) {
        return;
    }

    $instances = $DB->get_records('videotrack', null, '', 'id, course');
    $canonicalbyinstance = [];
    $deleteids = [];

    foreach ($gradeitems as $gradeitem) {
        $instanceid = (int)$gradeitem->iteminstance;
        $validinstance = isset($instances[$instanceid]);
        $validcourse = $validinstance && (int)$gradeitem->courseid === (int)$instances[$instanceid]->course;
        $validitemnumber = isset($gradeitem->itemnumber) && (int)$gradeitem->itemnumber === 0;

        if (!$validinstance || !$validcourse || !$validitemnumber) {
            $deleteids[] = (int)$gradeitem->id;
            continue;
        }

        $canonicalbyinstance[$instanceid][] = (int)$gradeitem->id;
    }

    foreach ($canonicalbyinstance as $gradeitemids) {
        if (count($gradeitemids) > 1) {
            foreach ($gradeitemids as $gradeitemid) {
                $deleteids[] = $gradeitemid;
            }
        }
    }

    $deleteids = array_values(array_unique($deleteids));
    foreach (array_chunk($deleteids, 500) as $chunk) {
        [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'vtgradeitemrepair');
        $DB->delete_records_select('grade_grades', "itemid {$insql}", $params);
        $DB->delete_records_select('grade_items', "id {$insql}", $params);
    }
}
