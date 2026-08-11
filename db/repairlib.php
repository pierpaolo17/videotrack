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

/**
 * Remove stale or ambiguous pre-production VideoTrack grade items.
 *
 * A valid VideoTrack grade item must be the unique itemnumber 0 row for an
 * existing VideoTrack instance in the same course. Orphan rows, non-canonical
 * item numbers, course mismatches and duplicate canonical rows can make
 * Moodle's standard grading form fail with morethanonerecordinfetch before the
 * activity form is rendered.
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

/**
 * Reconcile an interrupted pre-production database with the current install.xml schema.
 *
 * The obsolete videotrack_progress table belongs to an abandoned development lineage and
 * is removed when present. Current tables are repaired additively: missing tables, fields
 * and indexes are created, while existing schema objects and activity configuration are
 * preserved. The helper intentionally uses XMLDB/DML only so it is safe during upgrade.
 *
 * @return void
 */
function videotrack_repair_preproduction_schema(): void {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    $legacytable = new xmldb_table('videotrack_progress');
    if ($dbman->table_exists($legacytable)) {
        $dbman->drop_table($legacytable);
    }

    $xmldbfile = new xmldb_file($CFG->dirroot . '/mod/videotrack/db/install.xml');
    $loaded = $xmldbfile->loadXMLStructure();
    $structure = $xmldbfile->getStructure();
    if (!$loaded || !$xmldbfile->isLoaded() || !$structure) {
        throw new ddl_exception('ddlxmlfileerror', null, 'Unable to load mod_videotrack install.xml');
    }

    foreach ($structure->getTables() as $table) {
        $tablename = $table->getName();
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
            continue;
        }

        $idfield = $table->getField('id');
        if ($idfield && !$dbman->field_exists($table, $idfield)) {
            if ($DB->count_records($tablename) > 0) {
                throw new ddl_exception(
                    'ddlunknownerror',
                    null,
                    'Cannot repair non-empty table ' . $tablename . ' without its id field'
                );
            }
            $dbman->drop_table($table);
            $dbman->create_table($table);
            continue;
        }

        foreach ($table->getFields() as $field) {
            if (!$dbman->field_exists($table, $field)) {
                videotrack_repair_preproduction_add_field($table, $field);
            }
        }

        if ($tablename === 'videotrack_seg') {
            $columns = $DB->get_columns($tablename, false);
            if (isset($columns['requestid']) && !$columns['requestid']->not_null) {
                videotrack_repair_preproduction_requestids();
            }
        }

        foreach ($table->getIndexes() as $index) {
            if ($dbman->index_exists($table, $index)) {
                continue;
            }
            videotrack_repair_preproduction_unique_index_data($table, $index);
            $dbman->add_index($table, $index);
        }
    }
}

/**
 * Add one missing field while preserving rows in partially created tables.
 *
 * @param xmldb_table $table Expected table definition.
 * @param xmldb_field $field Expected field definition.
 * @return void
 */
function videotrack_repair_preproduction_add_field(xmldb_table $table, xmldb_field $field): void {
    global $DB;

    $dbman = $DB->get_manager();
    $tablename = $table->getName();
    $hasrows = $DB->count_records($tablename) > 0;
    $needsbackfill = $hasrows && $field->getNotNull() && $field->getDefault() === null;

    if (!$needsbackfill) {
        $dbman->add_field($table, $field);
        return;
    }

    $temporaryfield = clone $field;
    $temporaryfield->setNotNull(false);
    $temporaryfield->setDefault(null);
    $dbman->add_field($table, $temporaryfield);

    if ($tablename === 'videotrack_seg' && $field->getName() === 'requestid') {
        videotrack_repair_preproduction_requestids();
    } else {
        $stringtypes = [XMLDB_TYPE_CHAR, XMLDB_TYPE_TEXT];
        $fallback = in_array($field->getType(), $stringtypes, true) ? '' : 0;
        $DB->set_field($tablename, $field->getName(), $fallback);
    }

    $dbman->change_field_notnull($table, $field);
}

/**
 * Prepare data before creating a missing unique index.
 *
 * @param xmldb_table $table Expected table definition.
 * @param xmldb_index $index Expected index definition.
 * @return void
 */
function videotrack_repair_preproduction_unique_index_data(xmldb_table $table, xmldb_index $index): void {
    if (!$index->getUnique()) {
        return;
    }

    if ($table->getName() === 'videotrack_seg' && $index->getName() === 'vt_user_request_uix') {
        videotrack_repair_preproduction_requestids();
        return;
    }

    videotrack_repair_preproduction_deduplicate($table->getName(), $index->getFields());
}

/**
 * Give every existing segment a deterministic unique recovery request id.
 *
 * This is only used when the current unique request index is missing, or while adding
 * the requestid field to a partial pre-production schema.
 *
 * @return void
 */
function videotrack_repair_preproduction_requestids(): void {
    global $DB;

    $segments = $DB->get_recordset('videotrack_seg', null, 'id ASC', 'id, videotrackid, userid');
    foreach ($segments as $segment) {
        $requestid = 'recovery' . substr(hash('sha256', implode(':', [
            (int)$segment->id,
            (int)$segment->videotrackid,
            (int)$segment->userid,
        ])), 0, 56);
        $DB->set_field('videotrack_seg', 'requestid', $requestid, ['id' => (int)$segment->id]);
    }
    $segments->close();

    $columns = $DB->get_columns('videotrack_seg', false);
    if (isset($columns['requestid']) && !$columns['requestid']->not_null) {
        $table = new xmldb_table('videotrack_seg');
        $field = new xmldb_field(
            'requestid',
            XMLDB_TYPE_CHAR,
            '64',
            null,
            XMLDB_NOTNULL,
            null,
            null,
            'sessionid'
        );
        $DB->get_manager()->change_field_notnull($table, $field);
    }
}

/**
 * Remove duplicate rows that would prevent creation of a required unique index.
 *
 * The first row by id is retained. This applies only when the index itself is missing,
 * so a healthy 1.6.38 schema is not modified.
 *
 * @param string $tablename Table name without Moodle prefix.
 * @param array $fieldnames Fields in the unique index.
 * @return void
 */
function videotrack_repair_preproduction_deduplicate(string $tablename, array $fieldnames): void {
    global $DB;

    $selectfields = array_merge(['id'], $fieldnames);
    $orderfields = array_merge($fieldnames, ['id']);
    $records = $DB->get_recordset(
        $tablename,
        null,
        implode(', ', $orderfields),
        implode(', ', $selectfields)
    );

    $seen = [];
    $deleteids = [];
    foreach ($records as $record) {
        $values = [];
        foreach ($fieldnames as $fieldname) {
            $values[] = $record->{$fieldname};
        }
        $key = serialize($values);
        if (isset($seen[$key])) {
            $deleteids[] = (int)$record->id;
        } else {
            $seen[$key] = true;
        }
    }
    $records->close();

    foreach (array_chunk($deleteids, 500) as $chunk) {
        $DB->delete_records_list($tablename, 'id', $chunk);
    }
}
