#!/usr/bin/env node
// Static smoke tests for the Moodle backup/restore implementation of mod_videotrack.
// These tests intentionally avoid bootstrapping Moodle: they verify that the XML
// structure, user-data paths, id mappings and related files stay aligned.

'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const backupPath = path.join(root, 'backup', 'moodle2', 'backup_videotrack_stepslib.php');
const restorePath = path.join(root, 'backup', 'moodle2', 'restore_videotrack_stepslib.php');

const backup = fs.readFileSync(backupPath, 'utf8');
const restore = fs.readFileSync(restorePath, 'utf8');

function contains(source, needle, message) {
    assert.ok(source.includes(needle), message || `Missing expected snippet: ${needle}`);
}

function containsAll(source, needles, context) {
    needles.forEach((needle) => contains(source, needle, `${context}: ${needle}`));
}

function assertOrdered(source, first, second, context) {
    const firstIndex = source.indexOf(first);
    const secondIndex = source.indexOf(second);
    assert.ok(firstIndex !== -1, `${context}: missing first marker ${first}`);
    assert.ok(secondIndex !== -1, `${context}: missing second marker ${second}`);
    assert.ok(firstIndex < secondIndex, `${context}: ${first} should appear before ${second}`);
}

containsAll(backup, [
    "set_source_table('videotrack', ['id' => backup::VAR_ACTIVITYID])",
    "set_source_table('videotrack_react', ['videotrackid' => backup::VAR_PARENTID])",
    "set_source_table('videotrack_seg', ['videotrackid' => backup::VAR_PARENTID])",
    "set_source_table('videotrack_state', ['videotrackid' => backup::VAR_PARENTID])",
    "set_source_table('videotrack_reactev', ['videotrackid' => backup::VAR_PARENTID])",
], 'backup source tables');

containsAll(backup, [
    "$segment->annotate_ids('user', 'userid')",
    "$state->annotate_ids('user', 'userid')",
    "$reactionevent->annotate_ids('user', 'userid')",
], 'backup user id annotations');

containsAll(backup, [
    "$videotrack->annotate_files('mod_videotrack', 'intro',        null)",
    "$videotrack->annotate_files('mod_videotrack', 'videocontent', null)",
    "$videotrack->annotate_files('mod_videotrack', 'subtitles',    null)",
    "$videotrack->annotate_files('mod_videotrack', 'posterimage',  null)",
    "$reaction->annotate_files('mod_videotrack',   'reactionicon', 'id')",
], 'backup file annotations');

containsAll(restore, [
    "new restore_path_element('videotrack', '/activity/videotrack')",
    "new restore_path_element('videotrack_reaction', '/activity/videotrack/reactions/reaction')",
    "new restore_path_element('videotrack_segment', '/activity/videotrack/segments/segment')",
    "new restore_path_element('videotrack_state', '/activity/videotrack/states/state')",
    "new restore_path_element('videotrack_reactionevent', '/activity/videotrack/reactionevents/reactionevent')",
], 'restore paths');

containsAll(restore, [
    "$this->set_mapping('videotrack', $oldid, $newitemid)",
    "$this->set_mapping('videotrack_react', $oldid, $newitemid, true)",
    "$this->get_mappingid('user', $data->userid)",
    "$this->get_mappingid('videotrack_react', $oldreactionid)",
], 'restore mappings');

containsAll(restore, [
    "$this->add_related_files('mod_videotrack', 'intro',        null)",
    "$this->add_related_files('mod_videotrack', 'reactionicon', 'videotrack_react')",
    "$this->add_related_files('mod_videotrack', 'videocontent', null)",
    "$this->add_related_files('mod_videotrack', 'subtitles',    null)",
    "$this->add_related_files('mod_videotrack', 'posterimage',  null)",
], 'restore related files');

containsAll(restore, [
    "$DB->set_field('videotrack_seg',    'cmid', $cmid, ['videotrackid' => $videotrackid])",
    "$DB->set_field('videotrack_state',  'cmid', $cmid, ['videotrackid' => $videotrackid])",
    "$DB->set_field('videotrack_reactev','cmid', $cmid, ['videotrackid' => $videotrackid])",
    'videotrack_grade_item_update($videotrack)',
], 'restore after_execute fixups');

assertOrdered(restore, "$this->add_related_files('mod_videotrack', 'reactionicon', 'videotrack_react')", "$DB->set_field('videotrack_seg'", 'after_execute order');

console.log('backup_restore_static_test: OK');
