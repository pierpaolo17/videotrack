#!/usr/bin/env node
// Static privacy-provider review for mod_videotrack.
// This intentionally avoids bootstrapping Moodle. It checks that the Privacy API
// provider, language metadata and plugin-owned tables stay aligned during the
// release preparation phase.

'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const providerPath = path.join(root, 'classes', 'privacy', 'provider.php');
const privacyManagerPath = path.join(root, 'classes', 'local', 'privacy_manager.php');
const installXmlPath = path.join(root, 'db', 'install.xml');
const englishLangPath = path.join(root, 'lang', 'en', 'videotrack.php');

const provider = fs.readFileSync(providerPath, 'utf8');
const privacyManager = fs.readFileSync(privacyManagerPath, 'utf8');
const installXml = fs.readFileSync(installXmlPath, 'utf8');
const lang = fs.readFileSync(englishLangPath, 'utf8');

function contains(source, needle, context) {
    assert.ok(source.includes(needle), `${context}: missing ${needle}`);
}

function containsAll(source, needles, context) {
    needles.forEach((needle) => contains(source, needle, context));
}

function assertOrdered(source, first, second, context) {
    const firstIndex = source.indexOf(first);
    const secondIndex = source.indexOf(second);
    assert.ok(firstIndex !== -1, `${context}: missing first marker ${first}`);
    assert.ok(secondIndex !== -1, `${context}: missing second marker ${second}`);
    assert.ok(firstIndex < secondIndex, `${context}: ${first} should appear before ${second}`);
}

const personalTables = [
    'videotrack_seg',
    'videotrack_state',
    'videotrack_reactev',
];

containsAll(provider, [
    'namespace mod_videotrack\\privacy;',
    'class provider implements',
    '\\core_privacy\\local\\metadata\\provider',
    '\\core_privacy\\local\\request\\plugin\\provider',
    '\\core_privacy\\local\\request\\core_userlist_provider',
], 'privacy provider interface coverage');

personalTables.forEach((table) => {
    contains(installXml, `<TABLE NAME="${table}"`, `install.xml table ${table}`);
    contains(provider, `add_database_table('${table}'`, `metadata declaration for ${table}`);
    contains(provider, `{${table}}`, `provider SQL coverage for ${table}`);
    contains(lang, `privacy:metadata:${table}`, `metadata language string for ${table}`);
});

containsAll(provider, [
    "add_external_location_link('youtube'",
    "add_external_location_link('vimeo'",
], 'external location metadata');

containsAll(lang, [
    "privacy:metadata:youtube",
    "privacy:metadata:youtube:videoid",
    "privacy:metadata:youtube:url",
    "privacy:metadata:vimeo",
    "privacy:metadata:vimeo:videoid",
    "privacy:metadata:vimeo:url",
], 'external location language strings');

containsAll(provider, [
    'public static function get_contexts_for_userid(int $userid): contextlist',
    'public static function get_users_in_context(userlist $userlist): void',
    'public static function export_user_data(approved_contextlist $contextlist): void',
    'public static function delete_data_for_all_users_in_context(context $context): void',
    'public static function delete_data_for_user(approved_contextlist $contextlist): void',
    'public static function delete_data_for_users(approved_userlist $userlist): void',
], 'Privacy API method coverage');

containsAll(provider, [
    'SELECT cmid FROM {videotrack_state} WHERE userid = :userid1',
    'SELECT cmid FROM {videotrack_seg} WHERE userid = :userid2',
    'SELECT cmid FROM {videotrack_reactev} WHERE userid = :userid3',
    'SELECT userid FROM {videotrack_state} WHERE cmid = :cmid1 AND userid > 0',
    'SELECT userid FROM {videotrack_seg} WHERE cmid = :cmid2 AND userid > 0',
    'SELECT userid FROM {videotrack_reactev} WHERE cmid = :cmid3 AND userid > 0',
], 'context and user-list SQL coverage');

containsAll(provider, [
    "$DB->get_record('videotrack_state'",
    "$DB->get_recordset('videotrack_seg'",
    "$DB->get_recordset('videotrack_reactev'",
    "get_string('privacy:state', 'mod_videotrack')",
    "get_string('privacy:segmentschunk', 'mod_videotrack'",
    "get_string('privacy:reactionsactivechunk', 'mod_videotrack'",
    "get_string('privacy:notesactivechunk', 'mod_videotrack'",
], 'export path coverage');

containsAll(provider, [
    'transform::datetime($state->timemodified)',
    'transform::datetime($state->timecreated)',
    'transform::datetime($segment->timecreated)',
    'transform::datetime($reactionevent->timecreated)',
    'transform::yesno((bool)$state->iscompleted)',
    'transform::yesno($isdeleted)',
], 'export transform coverage');

containsAll(provider, [
    'unset($state->id, $state->videotrackid, $state->courseid',
    'unset($segment->id, $segment->videotrackid, $segment->courseid',
    'unset($reactionevent->id, $reactionevent->videotrackid, $reactionevent->courseid',
], 'export internal identifier cleanup');

containsAll(privacyManager, [
    'class privacy_manager',
    'delete_all_user_data_in_context(context $context): void',
    'delete_user_data_in_context(context $context, int $userid): void',
    "delete_records('videotrack_seg'",
    "delete_records('videotrack_state'",
    "delete_records('videotrack_reactev'",
    "delete_area_files($context->id, 'mod_videotrack'",
], 'privacy manager erasure coverage');

containsAll(provider, [
    'privacy_manager::delete_all_user_data_in_context($context)',
    'privacy_manager::delete_user_data_in_context($context, (int)$userid)',
], 'provider delegates erasure to privacy manager');

assertOrdered(provider,
    'public static function delete_data_for_user(approved_contextlist $contextlist): void',
    'protected static function delete_records_for_users_in_context(context $context, array $userids): void',
    'single-user erasure delegates to shared helper'
);

console.log('privacy_static_test: OK');
