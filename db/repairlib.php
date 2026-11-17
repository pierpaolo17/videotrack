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

// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalNotNeeded -- Deliberate defence-in-depth access guard.
defined('MOODLE_INTERNAL') || die();

/**
 * Remove stale or ambiguous pre-production VideoTrack grade items.
 *
 * A valid VideoTrack grade item must be the unique itemnumber 0 row for an
 * existing VideoTrack instance and exactly one matching course module in the
 * same course. Orphan rows, non-canonical item numbers, course/context
 * mismatches and duplicate canonical rows can make Moodle's standard grading
 * form fail with morethanonerecordinfetch before the activity form is rendered.
 *
 * The repair intentionally uses DML only. For duplicate canonical rows the
 * newest item is retained because Moodle's common restore step inserts it after
 * the former VideoTrack module-specific restore step. Any non-conflicting
 * grade_grades rows attached to an older duplicate are moved to the retained
 * item before the duplicate is removed.
 *
 * @return void
 */
function videotrack_repair_preproduction_gradebook_rows(): void {
    global $DB;

    $transaction = $DB->start_delegated_transaction();
    try {
        $gradeitems = $DB->get_records_select(
            'grade_items',
            'itemtype = :itemtype AND itemmodule = :itemmodule',
            ['itemtype' => 'mod', 'itemmodule' => 'videotrack'],
            'iteminstance ASC, itemnumber ASC, id ASC',
            'id, courseid, iteminstance, itemnumber'
        );
        if (!$gradeitems) {
            $transaction->allow_commit();
            return;
        }

        $instances = $DB->get_records('videotrack', null, '', 'id, course');
        $coursemodulecounts = [];
        $moduleid = (int)$DB->get_field('modules', 'id', ['name' => 'videotrack'], IGNORE_MISSING);
        if ($moduleid > 0) {
            $coursemodules = $DB->get_recordset(
                'course_modules',
                ['module' => $moduleid],
                'id ASC',
                'id, course, instance'
            );
            foreach ($coursemodules as $coursemodule) {
                $contextkey = (int)$coursemodule->instance . ':' . (int)$coursemodule->course;
                $coursemodulecounts[$contextkey] = ($coursemodulecounts[$contextkey] ?? 0) + 1;
            }
            $coursemodules->close();
        }
        $canonicalbyinstance = [];
        $deleteids = [];

        foreach ($gradeitems as $gradeitem) {
            $instanceid = (int)$gradeitem->iteminstance;
            if (
                !videotrack_repair_preproduction_grade_item_has_canonical_context(
                    $gradeitem,
                    $instances,
                    $coursemodulecounts
                )
            ) {
                $deleteids[] = (int)$gradeitem->id;
                continue;
            }

            $canonicalbyinstance[$instanceid][] = (int)$gradeitem->id;
        }

        foreach ($canonicalbyinstance as $gradeitemids) {
            if (count($gradeitemids) <= 1) {
                continue;
            }

            sort($gradeitemids, SORT_NUMERIC);
            $keepid = (int)array_pop($gradeitemids);
            foreach ($gradeitemids as $duplicateid) {
                videotrack_repair_preproduction_merge_grade_grades($keepid, (int)$duplicateid);
                $deleteids[] = (int)$duplicateid;
            }
        }

        videotrack_repair_preproduction_delete_grade_items($deleteids);
        $transaction->allow_commit();
    } catch (\Throwable $error) {
        $transaction->rollback($error);
        throw $error;
    }
}

/**
 * Verify that one grade item belongs to an existing instance and exactly one matching course module.
 *
 * @param stdClass $gradeitem Gradebook row being checked.
 * @param stdClass[] $instances VideoTrack activities keyed by instance id.
 * @param int[] $coursemodulecounts Matching course module counts keyed by instance and course.
 * @return bool Whether the item belongs to a canonical activity context.
 */
function videotrack_repair_preproduction_grade_item_has_canonical_context(
    stdClass $gradeitem,
    array $instances,
    array $coursemodulecounts
): bool {
    $instanceid = (int)$gradeitem->iteminstance;
    $validinstance = isset($instances[$instanceid]);
    $validcourse = $validinstance && (int)$gradeitem->courseid === (int)$instances[$instanceid]->course;
    $contextkey = $instanceid . ':' . (int)$gradeitem->courseid;
    $validcontext = $validcourse && ($coursemodulecounts[$contextkey] ?? 0) === 1;
    $validitemnumber = isset($gradeitem->itemnumber) && (int)$gradeitem->itemnumber === 0;

    return $validinstance && $validcourse && $validcontext && $validitemnumber;
}

/**
 * Move user grades from one duplicate grade item to the canonical item.
 *
 * If both items already contain a grade for the same user, the retained item's
 * row wins because it belongs to the grade item created later by Moodle's core
 * restore step. The older conflicting row is removed.
 *
 * @param int $keepid Canonical grade item id to retain.
 * @param int $duplicateid Duplicate grade item id to remove.
 * @return void
 */
function videotrack_repair_preproduction_merge_grade_grades(int $keepid, int $duplicateid): void {
    global $DB;

    $grades = $DB->get_records(
        'grade_grades',
        ['itemid' => $duplicateid],
        'id ASC',
        'id, userid'
    );
    foreach ($grades as $grade) {
        $existingid = $DB->get_field(
            'grade_grades',
            'id',
            ['itemid' => $keepid, 'userid' => (int)$grade->userid],
            IGNORE_MISSING
        );
        if ($existingid) {
            $DB->delete_records('grade_grades', ['id' => (int)$grade->id]);
            continue;
        }
        $DB->set_field('grade_grades', 'itemid', $keepid, ['id' => (int)$grade->id]);
    }
}

/**
 * Delete grade items and their active user-grade rows using DML only.
 *
 * @param int[] $gradeitemids Grade item ids to delete.
 * @return void
 */
function videotrack_repair_preproduction_delete_grade_items(array $gradeitemids): void {
    global $DB;

    $gradeitemids = array_values(array_unique(array_filter(array_map('intval', $gradeitemids))));
    foreach (array_chunk($gradeitemids, 500) as $chunk) {
        [$insql, $params] = $DB->get_in_or_equal($chunk, SQL_PARAMS_NAMED, 'vtgradeitemrepair');
        $DB->delete_records_select('grade_grades', "itemid {$insql}", $params);
        $DB->delete_records_select('grade_items', "id {$insql}", $params);
    }
}
