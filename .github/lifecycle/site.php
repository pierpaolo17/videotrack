<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Repository-only assertions for disposable CI lifecycle sites.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
define('IGNORE_COMPONENT_CACHE', true);

$workspace = realpath((string)getenv('GITHUB_WORKSPACE'));
$root = realpath((string)getenv('MOODLE_ROOT'));
if (getenv('GITHUB_ACTIONS') !== 'true' || !$workspace || $root !== $workspace . '/moodle') {
    fwrite(STDERR, "This helper requires the disposable Moodle GitHub Actions workspace.\n");
    exit(2);
}
require($root . '/config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/ddllib.php');

/** @var int Number of successful assertions in this invocation. */
$checks = 0;

/**
 * Fail immediately so a preservation error cannot be followed by uninstall.
 *
 * @param bool $condition Required condition.
 * @param string $message Public assertion label, containing no credentials.
 */
function videotrack_ci_check(bool $condition, string $message): void {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
    mtrace('[PASS] ' . $message);
}

/**
 * Read canonical ordered rows without driver-specific scalar types or column order.
 *
 * @param string $table Moodle table name.
 * @param array $conditions Exact DML conditions.
 * @param string $fields Selected fields, including id for ordering.
 * @return array Canonical rows.
 */
function videotrack_ci_rows(string $table, array $conditions, string $fields = '*'): array {
    global $DB;
    $rows = [];
    foreach ($DB->get_records($table, $conditions, 'id', $fields) as $record) {
        $row = array_map(static fn($value) => $value === null ? null : (string)$value, (array)$record);
        ksort($row);
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Snapshot unrelated fixture data which a plugin uninstall must preserve.
 *
 * @param array $proof Fixture identifiers.
 * @return array Neighbouring activity and surviving core identities.
 */
function videotrack_ci_related(array $proof): array {
    return [
        'forum' => videotrack_ci_rows('forum', ['id' => $proof['forumid']]),
        'cm' => videotrack_ci_rows('course_modules', ['id' => $proof['forumcmid']]),
        'files' => videotrack_ci_rows('files', ['contextid' => $proof['forumcontextid']]),
        'course' => videotrack_ci_rows('course', ['id' => $proof['courseid']], 'id, shortname'),
        'user' => videotrack_ci_rows('user', ['id' => $proof['userid']], 'id, username, deleted'),
    ];
}

/**
 * Capture all plugin families, active grades, completion and actual shared file bytes.
 *
 * @param array $proof Fixture identifiers.
 * @return array Snapshot suitable for equality before and after upgrade.
 */
function videotrack_ci_snapshot(array $proof): array {
    $tables = ['videotrack' => videotrack_ci_rows('videotrack', ['id' => $proof['activityid']])];
    foreach (['seg', 'state', 'react', 'reactev', 'integrity', 'acknowledge'] as $suffix) {
        $tables['videotrack_' . $suffix] = videotrack_ci_rows('videotrack_' . $suffix, [
            'videotrackid' => $proof['activityid'],
        ]);
    }
    foreach (['videocontent', 'intro', 'subtitles', 'transcripts', 'chapters', 'posterimage', 'reactionicon'] as $area) {
        $files = get_file_storage()->get_area_files($proof['contextid'], 'mod_videotrack', $area, false, 'id', false);
        videotrack_ci_check(count($files) === 1, 'Fixture has one shared file in ' . $area);
        foreach ($files as $file) {
            videotrack_ci_check(sha1($file->get_content()) === $file->get_contenthash(), 'Shared file content matches in ' . $area);
        }
    }
    return [
        'tables' => $tables,
        'cm' => videotrack_ci_rows('course_modules', ['id' => $proof['cmid']]),
        'items' => videotrack_ci_rows('grade_items', ['id' => $proof['itemid']]),
        'grades' => videotrack_ci_rows('grade_grades', ['itemid' => $proof['itemid']]),
        'completion' => videotrack_ci_rows('course_modules_completion', ['coursemoduleid' => $proof['cmid']]),
        'files' => videotrack_ci_rows('files', ['contextid' => $proof['contextid']]),
        'config' => array_map(static fn($key) => (string)get_config('mod_videotrack', $key), [
            'retentionperioddays', 'analyticsminusers', 'lifecycleproof',
        ]),
        'related' => videotrack_ci_related($proof),
    ];
}

/**
 * Populate the installed real baseline through Moodle generators and explicit learner rows.
 *
 * @param string $label Distinct lineage label.
 * @param int $expectedversion Immutable baseline's actual version code.
 * @return array Proof and baseline snapshot.
 */
function videotrack_ci_seed(string $label, int $expectedversion): array {
    global $CFG, $DB;
    videotrack_ci_check((int)get_config('mod_videotrack', 'version') === $expectedversion, 'Real baseline version installed');
    require_once($CFG->libdir . '/testing/generator/lib.php');
    require_once($CFG->libdir . '/gradelib.php');
    \core\session\manager::set_user(get_admin());
    set_config('enablecompletion', 1);
    $CFG->enablecompletion = 1;
    set_config('retentionperioddays', 37, 'mod_videotrack');
    set_config('analyticsminusers', 3, 'mod_videotrack');
    set_config('lifecycleproof', $label, 'mod_videotrack');
    $generator = new testing_data_generator();
    $suffix = $label . '_' . bin2hex(random_bytes(6));
    $course = $generator->create_course(['shortname' => 'vt_' . $suffix, 'enablecompletion' => 1]);
    $user = $generator->create_user(['username' => 'vt_' . $suffix, 'password' => bin2hex(random_bytes(24))]);
    $generator->enrol_user($user->id, $course->id, 'student');
    $forum = $generator->create_module('forum', ['course' => $course->id, 'name' => 'Preserved neighbour']);
    $settings = [
        'course' => $course->id, 'name' => 'VideoTrack lifecycle fixture', 'behathtml5fixture' => 1,
        'behatemptyreactions' => 1, 'durationseconds' => 100, 'grade' => 100,
        'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionpercentenabled' => 1, 'completionpercent' => 50,
        'reactionsenabled' => 1, 'studentnotesenabled' => 1, 'bookmarksenabled' => 1,
        'forumpostingenabled' => 1, 'linkedforumid' => $forum->id, 'randomfocuspauses' => 1,
    ];
    if ($DB->get_manager()->field_exists('videotrack', 'randompauseminseconds')) {
        $settings['randompauseminseconds'] = 120;
        $settings['randompausemaxseconds'] = 600;
    }
    $activity = $generator->create_module('videotrack', $settings);
    $record = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
    $context = \core\context\module::instance($activity->cmid);
    $forumcontext = \core\context\module::instance($forum->cmid);
    $now = time();
    $ids = ['videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid, 'userid' => $user->id];
    $common = $ids + ['videoid' => $record->videoid];
    $reactionid = $DB->insert_record('videotrack_react', (object)[
        'videotrackid' => $activity->id, 'reactionkey' => 'lifecycle', 'label' => 'Lifecycle reaction',
        'iconvalue' => 'icon.png', 'icontype' => 'file', 'timecreated' => $now, 'timemodified' => $now,
    ]);
    $DB->insert_record('videotrack_seg', (object)($common + [
        'sessionid' => $suffix, 'requestid' => $suffix, 'wallclockstart' => ($now - 80) * 1000,
        'wallclockend' => $now * 1000, 'videotimestart' => 0, 'videotimeend' => 80,
        'playbackrate' => 1, 'servervalidated' => 1, 'timecreated' => $now,
    ]));
    $DB->insert_record('videotrack_state', (object)($common + [
        'serverplaybacksessionid' => '', 'durationseconds' => 100, 'lastposition' => 80,
        'uniquecoveredseconds' => 80, 'completionpercent' => 80, 'intervaljson' => '[[0,80]]',
        'iscompleted' => 1, 'timecreated' => $now, 'timemodified' => $now,
    ]));
    foreach (['reaction', 'note', 'bookmark'] as $type) {
        $DB->insert_record('videotrack_reactev', (object)($common + [
            'sessionid' => $suffix, 'reactionid' => $type === 'reaction' ? $reactionid : 0,
            'reactionkey' => $type === 'reaction' ? 'lifecycle' : '', 'reactionlabel' => 'Lifecycle reaction',
            'notetype' => $type === 'reaction' ? '' : $type, 'notetext' => 'Private fixture ' . $type,
            'videotime' => 5, 'playbackrate' => 1, 'timecreated' => $now, 'timemodified' => $now,
        ]));
    }
    $DB->insert_record('videotrack_integrity', (object)($common + [
        'sessionid' => $suffix, 'eventtype' => 'tabhidden', 'videotime' => 5, 'timecreated' => $now,
    ]));
    $DB->insert_record('videotrack_acknowledge', (object)($ids + [
        'statementhash' => hash('sha256', $suffix), 'instanceversion' => 1,
        'viewedseconds' => 80, 'viewedpercent' => 80, 'timeconfirmed' => $now,
    ]));
    $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jrS8AAAAASUVORK5CYII=');
    $vtt = "WEBVTT\n\n00:00.000 --> 00:01.000\nLifecycle\n";
    foreach (
        [
            ['intro', 0, 'shared.txt', 'Shared content'],
            ['subtitles', 0, 'captions_it.vtt', $vtt],
            ['transcripts', 0, 'transcript_it.vtt', $vtt],
            ['chapters', 0, 'chapters_it.vtt', $vtt],
            ['posterimage', 0, 'poster.png', $image],
            ['reactionicon', $reactionid, 'icon.png', $image],
        ] as $file
    ) {
        get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_videotrack', 'filearea' => $file[0],
            'itemid' => $file[1], 'filepath' => '/', 'filename' => $file[2],
        ], $file[3]);
    }
    get_file_storage()->create_file_from_string([
        'contextid' => $forumcontext->id, 'component' => 'mod_forum', 'filearea' => 'intro',
        'itemid' => 0, 'filepath' => '/', 'filename' => 'preserved.txt',
    ], 'Unrelated file');
    $status = grade_update('mod/videotrack', $course->id, 'mod', 'videotrack', $activity->id, 0, (object)[
        'userid' => $user->id, 'rawgrade' => 80,
    ]);
    videotrack_ci_check($status === GRADE_UPDATE_OK, 'Real Moodle grade update succeeded');
    $completion = (object)[
        'coursemoduleid' => $activity->cmid, 'userid' => $user->id,
        'completionstate' => COMPLETION_COMPLETE, 'timemodified' => $now,
    ];
    $completionid = $DB->get_field('course_modules_completion', 'id', [
        'coursemoduleid' => $activity->cmid, 'userid' => $user->id,
    ]);
    if ($completionid) {
        $completion->id = $completionid;
        $DB->update_record('course_modules_completion', $completion);
    } else {
        $DB->insert_record('course_modules_completion', $completion);
    }
    $itemid = $DB->get_field('grade_items', 'id', [
        'itemmodule' => 'videotrack', 'iteminstance' => $activity->id, 'itemnumber' => 0,
    ], MUST_EXIST);
    $proof = [
        'label' => $label, 'sourceversion' => $expectedversion, 'courseid' => (int)$course->id,
        'activityid' => (int)$activity->id, 'cmid' => (int)$activity->cmid, 'contextid' => (int)$context->id,
        'moduleid' => (int)$DB->get_field('modules', 'id', ['name' => 'videotrack'], MUST_EXIST),
        'userid' => (int)$user->id, 'itemid' => (int)$itemid, 'forumid' => (int)$forum->id,
        'forumcmid' => (int)$forum->cmid, 'forumcontextid' => (int)$forumcontext->id,
        'functions' => array_keys($DB->get_records('external_functions', ['component' => 'mod_videotrack'], '', 'name')),
    ];
    $proof['snapshot'] = videotrack_ci_snapshot($proof);
    foreach ($proof['snapshot']['tables'] as $table => $rows) {
        videotrack_ci_check(count($rows) > 0, 'Populated fixture table ' . $table);
    }
    videotrack_ci_check(count($proof['snapshot']['grades']) === 1, 'Fixture has an active learner grade');
    videotrack_ci_check(count($proof['snapshot']['completion']) === 1, 'Fixture has native completion');
    videotrack_ci_check(count($proof['functions']) === 9, 'Baseline registers nine AJAX methods');
    return $proof;
}

/**
 * Check operational uninstall residues while preserving legitimate core audit/history records.
 *
 * @param array $proof Fixture identifiers and baseline data.
 */
function videotrack_ci_residue(array $proof): void {
    global $DB;
    $tables = array_filter($DB->get_tables(), static fn($name) => preg_match('/^videotrack(?:_|$)/', $name));
    videotrack_ci_check(!$tables, 'No VideoTrack tables remain');
    foreach (
        [
            ['modules', ['name' => 'videotrack']], ['course_modules', ['module' => $proof['moduleid']]],
            ['context', ['id' => $proof['contextid']]], ['files', ['component' => 'mod_videotrack']],
            ['grade_items', ['itemmodule' => 'videotrack']], ['grade_grades', ['itemid' => $proof['itemid']]],
            ['course_modules_completion', ['coursemoduleid' => $proof['cmid']]],
            ['config_plugins', ['plugin' => 'mod_videotrack']], ['config_plugins', ['plugin' => 'videotrack']],
            ['capabilities', ['component' => 'mod_videotrack']],
            ['external_functions', ['component' => 'mod_videotrack']], ['external_services', ['component' => 'mod_videotrack']],
            ['task_scheduled', ['component' => 'mod_videotrack']], ['task_adhoc', ['component' => 'mod_videotrack']],
        ] as [$table, $conditions]
    ) {
        videotrack_ci_check(!$DB->record_exists($table, $conditions), 'No operational residue in ' . $table);
    }
    $like = $DB->sql_like('capability', ':pattern');
    videotrack_ci_check(!$DB->record_exists_select('role_capabilities', $like, [
        'pattern' => 'mod/videotrack:%',
    ]), 'No VideoTrack role grants remain');
    [$insql, $params] = $DB->get_in_or_equal($proof['functions'], SQL_PARAMS_NAMED);
    videotrack_ci_check(
        !$DB->record_exists_select('external_services_functions', 'functionname ' . $insql, $params),
        'No service bindings to former AJAX methods remain'
    );
    videotrack_ci_check(
        videotrack_ci_related($proof) === $proof['snapshot']['related'],
        'Uninstall preserves neighbouring Forum/files, learner and course identity'
    );
}

try {
    [$options, $unknown] = cli_get_params(['mode' => '', 'manifest' => '', 'label' => '', 'expected' => 0], []);
    videotrack_ci_check(!$unknown, 'Only recognised lifecycle options supplied');
    videotrack_ci_check(!PHPUNIT_TEST && !defined('BEHAT_SITE_RUNNING'), 'Normal CI database, outside PHPUnit and Behat');
    foreach (['phpunit_prefix', 'behat_prefix'] as $prefix) {
        videotrack_ci_check(empty($CFG->$prefix) || $DB->get_prefix() !== $CFG->$prefix, 'Database is not ' . $prefix);
    }
    videotrack_ci_check(str_starts_with((string)realpath($CFG->dataroot), $workspace . '/'), 'Dataroot belongs to CI job');
    videotrack_ci_check(get_site()->shortname === 'videotrack-ci', 'Disposable validator site identity matches');
    $identity = hash('sha256', $CFG->dbname . ':' . $DB->get_prefix() . ':' . realpath($CFG->dataroot));
    $mode = $options['mode'];
    $manifest = $options['manifest'];
    if ($mode === 'guard') {
        videotrack_ci_check($DB->count_records('videotrack') === 0, 'Initial CI site has no VideoTrack learner activities');
    } else {
        videotrack_ci_check(str_starts_with($manifest, $workspace . '/ci-reports/lifecycle/'), 'Proof path belongs to CI reports');
        if ($mode === 'seed') {
            videotrack_ci_check(!file_exists($manifest), 'Baseline proof is written once');
            $proof = videotrack_ci_seed((string)$options['label'], (int)$options['expected']);
            $proof['identity'] = $identity;
            videotrack_ci_check(
                file_put_contents($manifest, json_encode($proof, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) !== false,
                'Baseline proof saved'
            );
        } else {
            $proof = json_decode(file_get_contents($manifest), true, 512, JSON_THROW_ON_ERROR);
            videotrack_ci_check($proof['identity'] === $identity, 'Proof belongs to the same disposable database');
            if ($mode === 'partial') {
                videotrack_ci_check(
                    (int)get_config('mod_videotrack', 'version') === 2026110507,
                    'Partial DDL keeps actual old version'
                );
                $dbman = $DB->get_manager();
                videotrack_ci_check(
                    !$dbman->field_exists('videotrack', 'randompauseminseconds'),
                    'First migration field is absent'
                );
                $dbman->add_field(new xmldb_table('videotrack'), new xmldb_field(
                    'randompauseminseconds',
                    XMLDB_TYPE_INTEGER,
                    '4',
                    null,
                    XMLDB_NOTNULL,
                    null,
                    '0',
                    'randomfocuspauses'
                ));
                videotrack_ci_check(!$dbman->field_exists('videotrack', 'randompausemaxseconds'), 'Second field remains absent');
                videotrack_ci_check(
                    (int)get_config('mod_videotrack', 'version') === 2026110507,
                    'Installed version was not rewritten'
                );
            } else if ($mode === 'verify') {
                $plugin = new stdClass();
                require($CFG->dirroot . '/mod/videotrack/version.php');
                videotrack_ci_check(
                    (int)get_config('mod_videotrack', 'version') === (int)$plugin->version
                        && (int)$plugin->version > $proof['sourceversion'],
                    'Target upgrade is installed and newer than baseline'
                );
                $expected = $proof['snapshot'];
                $row = &$expected['tables']['videotrack'][0];
                foreach (['randompauseminseconds', 'randompausemaxseconds'] as $field) {
                    if (!array_key_exists($field, $row)) {
                        $row[$field] = '0';
                    }
                }
                ksort($row);
                unset($row);
                $actual = videotrack_ci_snapshot($proof);
                foreach ($expected as $area => $data) {
                    videotrack_ci_check($actual[$area] === $data, 'Upgrade preserves ' . $area . ' and expected field defaults');
                }
                mtrace('Preserved snapshot SHA-256: ' . hash('sha256', json_encode($actual, JSON_THROW_ON_ERROR)));
            } else if ($mode === 'residue') {
                videotrack_ci_residue($proof);
            } else {
                throw new RuntimeException('Unknown lifecycle mode');
            }
        }
    }
    mtrace(json_encode(['mode' => $mode, 'pass' => $checks, 'fail' => 0], JSON_THROW_ON_ERROR));
} catch (Throwable $e) {
    fwrite(STDERR, '[FAIL] Lifecycle: ' . $e->getMessage() . "\n");
    exit(1);
}
