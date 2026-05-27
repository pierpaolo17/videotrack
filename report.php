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
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Formats a report user label without exposing anonymised pseudo-user ids.
 *
 * @param int $userid User id, potentially an anonymised negative pseudo-id.
 * @param array $usermap Real Moodle users keyed by id.
 * @param bool $canviewemail Whether email may be displayed.
 * @return string Safe display label.
 */
function videotrack_report_user_label(int $userid, array $usermap, bool $canviewemail): string {
    if ($userid < 0) {
        return get_string('report:anonymiseduser', 'mod_videotrack');
    }
    $user = $usermap[$userid] ?? null;
    if (!$user) {
        return '#' . $userid;
    }
    return fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : '');
}

global $DB, $USER, $CFG, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT);
$sort = optional_param('sort', 'time', PARAM_ALPHA);
$mode = optional_param('mode', 'student', PARAM_ALPHA);
$aggregation = optional_param('aggregation', 'type', PARAM_ALPHA);
$window = optional_param('window', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHA);
$useridfilter = optional_param('userid', 0, PARAM_INT);
$reactionidfilter = optional_param('reactionid', 0, PARAM_INT);
$notepage = max(0, optional_param('notepage', 0, PARAM_INT));
$notecreatedfrom = videotrack_optional_iso_date_param('notecreatedfrom');
$notecreatedto = videotrack_optional_iso_date_param('notecreatedto');
$timefromparam = optional_param('timefrom', null, PARAM_FLOAT);
$timetoparam = optional_param('timeto', null, PARAM_FLOAT);
$timefrom = $timefromparam !== null ? max(0.0, (float)$timefromparam) : null;
$timeto = $timetoparam !== null ? max(0.0, (float)$timetoparam) : null;
if ($timefrom !== null && $timeto !== null && $timeto < $timefrom) {
    [$timefrom, $timeto] = [$timeto, $timefrom];
}
$notecreatedfromts = $notecreatedfrom !== '' ? strtotime($notecreatedfrom . ' 00:00:00') : 0;
$notecreatedtots = $notecreatedto !== '' ? strtotime($notecreatedto . ' 23:59:59') : 0;
if ($notecreatedfromts === false) {
    $notecreatedfromts = 0;
}
if ($notecreatedtots === false) {
    $notecreatedtots = 0;
}
if ($notecreatedfromts && $notecreatedtots && $notecreatedtots < $notecreatedfromts) {
    [$notecreatedfromts, $notecreatedtots] = [$notecreatedtots, $notecreatedfromts];
}

$cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videotrack:viewreport', $context);

$window = $window ?: (int)$videotrack->clusterwindow;
$validwindows = [10, 15, 20, 30, 60];
if (!in_array($window, $validwindows, true)) {
    $window = 30;
}
$mode = in_array($mode, ['student', 'cumulative'], true) ? $mode : 'student';
$aggregation = in_array($aggregation, ['type', 'peak'], true) ? $aggregation : 'type';
$sort = in_array($sort, ['time', 'reaction', 'clicks'], true) ? $sort : 'time';

$sortsql = 'videotime ASC';
if ($sort === 'reaction') {
    $sortsql = 'reactionlabel ASC, videotime ASC';
}

$reactions = videotrack_get_reactions($videotrack->id);
$reactionmap = [];
foreach ($reactions as $reaction) {
    $reactionmap[(int)$reaction->id] = $reaction;
}

// $events: solo reazioni standard (esclude note personali — notetype='note').
// Le note vengono mostrate in una sezione separata più in basso.
$eventconditions = "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)";
$eventparams_named = ['vtid' => $videotrack->id];
if ($useridfilter > 0) {
    $eventconditions .= ' AND userid = :uid';
    $eventparams_named['uid'] = $useridfilter;
}
if ($reactionidfilter > 0) {
    $eventconditions .= ' AND reactionid = :rid';
    $eventparams_named['rid'] = $reactionidfilter;
}
if ($timefrom !== null) {
    $eventconditions .= ' AND videotime >= :timefrom';
    $eventparams_named['timefrom'] = $timefrom;
}
if ($timeto !== null) {
    $eventconditions .= ' AND videotime <= :timeto';
    $eventparams_named['timeto'] = $timeto;
}
// Avoid loading all reaction events into memory. Use count/distinct queries for filters
// and recordsets only where the full event stream is required (CSV/clustered report).
$eventcount = $DB->count_records_select('videotrack_reactev', $eventconditions, $eventparams_named);
$eventuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_reactev',
    'DISTINCT userid',
    $eventconditions,
    $eventparams_named
));
$geteventrecordset = static function() use ($DB, $eventconditions, $eventparams_named) {
    return $DB->get_recordset_select(
        'videotrack_reactev',
        $eventconditions,
        $eventparams_named,
        'videotime ASC',
        'id, userid, reactionid, reactionlabel, videotime'
    );
};

$stateparams = ['videotrackid' => $videotrack->id];
$stateconditions = 'videotrackid = :svtid';
$stateparams_named = ['svtid' => $videotrack->id];
if ($useridfilter > 0) {
    $stateparams['userid'] = $useridfilter;
    $stateconditions .= ' AND userid = :suid';
    $stateparams_named['suid'] = $useridfilter;
}
$statecount = $DB->count_records_select('videotrack_state', $stateconditions, $stateparams_named);
$stateuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_state',
    'DISTINCT userid',
    $stateconditions,
    $stateparams_named
));
$getstaterecordset = static function() use ($DB, $stateparams) {
    return $DB->get_recordset('videotrack_state', $stateparams, 'completionpercent DESC, uniquecoveredseconds DESC');
};

// Raccoglie gli userid delle note (potrebbero non avere state né events).
$noteuserids = [];
if (!empty($videotrack->studentnotesenabled)) {
    $noteuidparams = ['vtid' => $videotrack->id];
    $noteuidwhere  = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'";
    if ($useridfilter > 0) {
        $noteuidwhere .= ' AND userid = :uid';
        $noteuidparams['uid'] = $useridfilter;
    }
    foreach ($DB->get_fieldset_select('videotrack_reactev', 'DISTINCT userid', $noteuidwhere, $noteuidparams) as $nuid) {
        $noteuserids[] = (int)$nuid;
    }
}

// Carica tutti gli utenti necessari in una sola query invece di N chiamate a core_user::get_user().
$alluserids = array_values(array_filter(array_unique(array_merge(
    $stateuserids,
    $eventuserids,
    $noteuserids
)), static function(int $userid): bool {
    return $userid > 0;
}));
$usermap = [];
$canviewemail = false;
if ($alluserids) {
    [$insql, $inparams] = $DB->get_in_or_equal($alluserids, SQL_PARAMS_NAMED);
    // Email visibile solo a chi ha la capability viewreport E ha il diritto di vedere email.
    // Minimizzazione GDPR: per default mostra solo il nome completo.
    $canviewemail = has_capability('moodle/site:viewuseridentity', $context) &&
            in_array('email', \core_user\fields::get_identity_fields($context, false));
    // Seleziona email solo se necessario: evita di caricare dati personali superflui.
    $userfields = $canviewemail ? 'id,firstname,lastname,email,deleted' : 'id,firstname,lastname,deleted';
    foreach ($DB->get_records_select('user', "id $insql", $inparams, '', $userfields) as $u) {
        $usermap[(int)$u->id] = $u;
    }
}

$useroptions = [0 => get_string('all')];
foreach ($stateuserids as $stateuserid) {
    $user = $usermap[(int)$stateuserid] ?? null;
    if ($user) {
        $useroptions[(int)$user->id] = (fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : ''));
    }
}
foreach ($eventuserids as $eventuserid) {
    if (!isset($useroptions[(int)$eventuserid])) {
        $user = $usermap[(int)$eventuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = (fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : ''));
        }
    }
}
foreach ($noteuserids as $noteuserid) {
    if (!isset($useroptions[(int)$noteuserid])) {
        $user = $usermap[(int)$noteuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = (fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : ''));
        }
    }
}

$reactionoptions = [0 => get_string('all')];
foreach ($reactions as $reaction) {
    $reactionoptions[(int)$reaction->id] = $reaction->label;
}

// OPT-3: $baseparams definita qui una volta sola, usata da export, actions e link di navigazione.
$baseparams = [
    'id' => $cm->id,
    'mode' => $mode,
    'sort' => $sort,
    'aggregation' => $aggregation,
    'window' => $window,
    'userid' => $useridfilter,
    'reactionid' => $reactionidfilter,
    'timefrom' => $timefrom,
    'timeto' => $timeto,
    'notecreatedfrom' => $notecreatedfrom,
    'notecreatedto' => $notecreatedto,
];
$baseurl = new moodle_url('/mod/videotrack/report.php', $baseparams);
$hasvideotimefilter = ($timefrom !== null || $timeto !== null);

// OPT-1: grade_get_grades caricato una sola volta per tutte le sezioni del report.
$hasgrade  = !empty($videotrack->grade);
$cangrade = has_capability('mod/videotrack:grade', $context);
$gradeinfo = null;
if ($hasgrade && $cangrade && $alluserids) {
    require_once($CFG->libdir . '/gradelib.php');
    $gradeinfo = grade_get_grades(
        $course->id, 'mod', 'videotrack', $videotrack->id,
        array_keys($usermap)
    );
}

$clusterlimitreached = false;
$clusterize = function(iterable $events, int $windowseconds, string $aggregationmode) use ($reactionmap, $sort, &$clusterlimitreached) {
    // Events are processed in timestamp order. Keep only the latest open cluster
    // per reaction (or a single cluster for peak mode), avoiding the former O(n * clusters)
    // scan for every event.
    $clusters = [];
    $activeindex = [];
    $maxclusters = 2000;
    foreach ($events as $event) {
        $reactionid = (int)$event->reactionid;
        $time = (float)$event->videotime;
        $key = ($aggregationmode === 'peak') ? 0 : $reactionid;
        $idx = $activeindex[$key] ?? null;

        if ($idx !== null && ($time - (float)$clusters[$idx]['anchor']) <= $windowseconds) {
            $clusters[$idx]['count']++;
            $clusters[$idx]['students'][(int)$event->userid] = true;
            $clusters[$idx]['timesum'] += $time;
            $clusters[$idx]['first'] = min($clusters[$idx]['first'], $time);
            $clusters[$idx]['last'] = max($clusters[$idx]['last'], $time);
            continue;
        }

        if (count($clusters) >= $maxclusters) {
            // Safety valve for very large datasets. CSV/report remains deterministic;
            // administrators should use filters for deeper analysis on huge courses.
            $clusterlimitreached = true;
            continue;
        }
        $clusters[] = [
            'reactionid' => $reactionid,
            'reactionlabel' => format_string($event->reactionlabel, true, ['context' => $context]),
            'reaction' => $reactionmap[$reactionid] ?? null,
            'anchor' => $time,
            'first' => $time,
            'last' => $time,
            'count' => 1,
            'students' => [(int)$event->userid => true],
            'timesum' => $time,
        ];
        $activeindex[$key] = count($clusters) - 1;
    }

    foreach ($clusters as &$cluster) {
        $cluster['students'] = count($cluster['students']);
        $cluster['timestamp'] = $cluster['timesum'] / $cluster['count'];
        unset($cluster['timesum']);
    }
    unset($cluster);

    if ($aggregationmode === 'type' && $sort === 'reaction') {
        usort($clusters, static fn($a, $b) => [$a['reactionlabel'], $a['timestamp']] <=> [$b['reactionlabel'], $b['timestamp']]);
    } else if ($sort === 'clicks') {
        usort($clusters, static fn($a, $b) => $b['count'] <=> $a['count']);
    } else {
        usort($clusters, static fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
    }
    return $clusters;
};

if ($export === 'notes_csv' && !empty($videotrack->studentnotesenabled)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    $confirmnotesexport = optional_param('confirmnotesexport', 0, PARAM_BOOL);
    if (!$confirmnotesexport) {
        throw new moodle_exception('report:exportnotes_confirmrequired', 'mod_videotrack');
    }
    // S1 fix: validate useridfilter against course enrolment to prevent a teacher
    // from exporting notes of a user not enrolled in this course by manipulating
    // the GET parameter. is_enrolled() is already used for reset and grade actions.
    if ($useridfilter > 0 && !is_enrolled($context, $useridfilter, '', true)) {
        throw new moodle_exception('invaliduser', 'error');
    }
    $event = \mod_videotrack\event\notes_exported::create([
        'objectid' => (int)$videotrack->id,
        'context'  => $context,
        'userid'   => (int)$USER->id,
        'other'    => [
            'useridfilter' => (int)$useridfilter,
            'createdfrom' => (int)$notecreatedfromts,
            'createdto' => (int)$notecreatedtots,
            'emailincluded' => (bool)$canviewemail,
        ],
    ]);
    $event->trigger();
    $filename = 'videotrack_notes_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    $headers = ['user'];
    if ($canviewemail) {
        $headers[] = 'email';
    }
    $headers = array_merge($headers, ['video_timestamp', 'note', 'created']);
    fputcsv($fh, videotrack_csv_safe_row($headers));
    // Rispetta il filtro userid del report (GDPR: esporta solo chi è autorizzato vedere).
    $notecsv_where = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'";
    $notecsv_params = ['vtid' => $videotrack->id];
    if ($useridfilter > 0) {
        $notecsv_where .= ' AND userid = :uid';
        $notecsv_params['uid'] = $useridfilter;
    }
    if ($notecreatedfromts) {
        $notecsv_where .= ' AND timecreated >= :notecreatedfrom';
        $notecsv_params['notecreatedfrom'] = $notecreatedfromts;
    }
    if ($notecreatedtots) {
        $notecsv_where .= ' AND timecreated <= :notecreatedto';
        $notecsv_params['notecreatedto'] = $notecreatedtots;
    }
    $rs = $DB->get_recordset_select(
        'videotrack_reactev',
        $notecsv_where,
        $notecsv_params,
        'userid ASC, videotime ASC',
        'userid, videotime, notetext, timecreated'
    );
    foreach ($rs as $note) {
        $nu = $usermap[(int)$note->userid] ?? null;
        $row = [videotrack_report_user_label((int)$note->userid, $usermap, false)];
        if ($canviewemail) {
            $row[] = $nu ? $nu->email : '';
        }
        $row = array_merge($row, [
            round((float)$note->videotime, 3),
            $note->notetext,
            userdate((int)$note->timecreated),
        ]);
        fputcsv($fh, videotrack_csv_safe_row($row));
    }
    $rs->close();
    fclose($fh);
    exit;
}

if ($export === 'csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    $filename = 'videotrack_report_' . $cm->id . '_' . $mode . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    if ($mode === 'cumulative') {
        $eventrs = $geteventrecordset();
        $clusters = $clusterize($eventrs, $window, $aggregation);
        $eventrs->close();
        if ($clusterlimitreached) {
            // Put the warning before the data header so spreadsheet users see it immediately.
            fputcsv($fh, videotrack_csv_safe_row(['warning', get_string('report:clusterlimitreached_csv', 'mod_videotrack')]));
            if (!$hasvideotimefilter) {
                fputcsv($fh, videotrack_csv_safe_row(['warning', get_string('report:clusterlimitrequiresfilters_csv', 'mod_videotrack')]));
                fputcsv($fh, videotrack_csv_safe_row(['warning', get_string('report:clusterexportblocked_csv', 'mod_videotrack')]));
                fclose($fh);
                exit;
            }
            fputcsv($fh, []);
        }
        fputcsv($fh, videotrack_csv_safe_row(['timestamp', 'reaction', 'clicks', 'students', 'first_timestamp', 'last_timestamp']));
        foreach ($clusters as $cluster) {
            fputcsv($fh, videotrack_csv_safe_row([
                round($cluster['timestamp'], 3),
                $cluster['reactionlabel'],
                $cluster['count'],
                $cluster['students'],
                round($cluster['first'], 3),
                round($cluster['last'], 3),
            ]));
        }
    } else {
        // Usa recordset per iterare riga per riga ed evitare di caricare tutto in memoria.
        $csvheads = ['user', 'unique_seconds', 'completion_percent', 'last_position', 'completed'];
        if ($hasgrade && $cangrade) {
            $csvheads[] = 'grade';
        }
        fputcsv($fh, videotrack_csv_safe_row($csvheads));
        $rs = $DB->get_recordset('videotrack_state', $stateparams, 'completionpercent DESC');
        foreach ($rs as $state) {
            $user = $usermap[(int)$state->userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = [
                fullname($user),
                $state->uniquecoveredseconds,
                $state->completionpercent,
                $state->lastposition,
                $state->iscompleted,
            ];
            if ($hasgrade && $cangrade) {
                $row[] = $gradeinfo->items[0]->grades[(int)$state->userid]->grade ?? '';
            }
            fputcsv($fh, videotrack_csv_safe_row($row));
        }
        $rs->close();
    }
    fclose($fh);
    exit;
}


/**
 * Escapes values for CSV exports to reduce spreadsheet formula injection risk.
 *
 * @param mixed $value Value to export.
 * @return mixed Sanitised scalar value.
 */
function videotrack_csv_safe($value) {
    if (!is_string($value)) {
        return $value;
    }
    if ($value !== '' && preg_match('/^[=+\-@\t\r\n]/', ltrim($value))) {
        return "'" . $value;
    }
    return $value;
}

/**
 * Escapes all values in a CSV row.
 *
 * @param array $row CSV row.
 * @return array Sanitised CSV row.
 */
function videotrack_csv_safe_row(array $row): array {
    return array_map('videotrack_csv_safe', $row);
}

$action = optional_param('action', '', PARAM_ALPHA);
$resetaction = optional_param('resetaction', '', PARAM_ALPHA);

// Ricalcolo stati: aggiorna completionpercent e iscompleted per tutti gli utenti.
if ($action === 'recalculate') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_once(__DIR__ . '/lib.php');
    $updated = videotrack_recalculate_all_states($videotrack->id, cm_info::create($cm));
    redirect(
        new moodle_url('/mod/videotrack/report.php', $baseparams),
        get_string('report:recalculated', 'mod_videotrack', $updated),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Reset progresso singolo studente: cancella segmenti, stato e reazioni di un utente.
if ($resetaction === 'resetstudent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    require_capability('mod/videotrack:managereactions', $context);
    $resetuserid = required_param('resetuserid', PARAM_INT);
    if (!is_enrolled($context, $resetuserid, '', true)) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $resetcounts = [
        'segments' => $DB->count_records('videotrack_seg', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'states' => $DB->count_records('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'events' => $DB->count_records('videotrack_reactev', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
    ];
    $DB->delete_records('videotrack_seg',     ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    $DB->delete_records('videotrack_state',   ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    $DB->delete_records('videotrack_reactev', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    \mod_videotrack\event\student_progress_reset::create([
        'objectid' => $videotrack->id,
        'context' => $context,
        'relateduserid' => $resetuserid,
        'other' => $resetcounts,
    ])->trigger();
    // Azzera anche il voto nel gradebook se l'attività prevede valutazione.
    if (!empty($videotrack->grade)) {
        require_once($CFG->libdir . '/gradelib.php');
        grade_update('mod/videotrack', $course->id, 'mod', 'videotrack',
            $videotrack->id, 0, null, ['reset' => true, 'userid' => $resetuserid]);
    }
    // Aggiorna il completamento Moodle a INCOMPLETE per questo studente.
    $cminfo    = cm_info::create($cm);
    $completion = new completion_info($course);
    $completion->update_state($cminfo, COMPLETION_INCOMPLETE, $resetuserid);
    redirect(
        new moodle_url('/mod/videotrack/report.php', $baseparams),
        get_string('report:studentreset', 'mod_videotrack'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$PAGE->set_url('/mod/videotrack/report.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_title(format_string($videotrack->name));
$PAGE->set_heading(format_string($course->fullname));
// Gli stili del plugin sono in styles.css, incluso automaticamente da Moodle.

// B4: il blocco savegrade va eseguito PRIMA di $OUTPUT->header() per permettere il redirect.
if ($hasgrade && optional_param('savegrade', 0, PARAM_INT)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:grade', $context);
    require_once(__DIR__ . '/lib.php');
    require_once($CFG->libdir . '/gradelib.php');
    $gradeuserid = required_param('grade_userid', PARAM_INT);
    if (!is_enrolled($context, $gradeuserid, '', true)) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $gradevalue = optional_param('grade_value', '', PARAM_NOTAGS);
    if ($gradevalue !== '') {
        if (!is_numeric($gradevalue)) {
            throw new moodle_exception('invaliddata', 'error');
        }
        if ($videotrack->grade > 0) {
            $val = (float)$gradevalue;
            if ($val < 0 || $val > (float)$videotrack->grade) {
                throw new moodle_exception('invaliddata', 'error');
            }
        } else {
            $scaleid = -(int)$videotrack->grade;
            $scale   = grade_scale::fetch(['id' => $scaleid]);
            $items   = $scale ? $scale->load_items() : [];
            $val     = (int)$gradevalue;
            if ($val < 1 || $val > count($items)) {
                throw new moodle_exception('invaliddata', 'error');
            }
        }
        videotrack_set_user_grade($videotrack, $gradeuserid, (float)$gradevalue);
    } else {
        videotrack_set_user_grade($videotrack, $gradeuserid, -1);
    }
    redirect($PAGE->url,
        get_string('report:gradesaved', 'mod_videotrack'),
        null, \core\output\notification::NOTIFY_SUCCESS);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reportteacher', 'mod_videotrack'));

$recalculateform = html_writer::start_tag('form', [
    'method' => 'post',
    'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
    'class' => 'd-inline videotrack-recalculate-form',
]);
foreach ($baseparams as $name => $value) {
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => $name,
        'value' => $value,
    ]);
}
$recalculateform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
$recalculateform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'action', 'value' => 'recalculate']);
$recalculateform .= html_writer::tag('button', get_string('report:recalculate', 'mod_videotrack'), [
    'type' => 'submit',
    'class' => 'btn btn-link p-0 align-baseline',
]);
$recalculateform .= html_writer::end_tag('form');

$exportform = html_writer::start_tag('form', [
    'method' => 'post',
    'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
    'class' => 'd-inline videotrack-export-form',
]);
foreach ($baseparams as $name => $value) {
    $exportform .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => $name,
        'value' => $value,
    ]);
}
$exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
$exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'export', 'value' => 'csv']);
$exportform .= html_writer::tag('button', get_string('report:exportcsv', 'mod_videotrack'), [
    'type' => 'submit',
    'class' => 'btn btn-link p-0 align-baseline',
]);
$exportform .= html_writer::end_tag('form');

echo html_writer::div(
    html_writer::link(new moodle_url('/mod/videotrack/report.php', array_merge($baseparams, ['mode' => 'student'])), get_string('report:perstudent', 'mod_videotrack')) . ' | ' .
    html_writer::link(new moodle_url('/mod/videotrack/report.php', array_merge($baseparams, ['mode' => 'cumulative'])), get_string('report:cumulative', 'mod_videotrack')) . ' | ' .
    $exportform . ' | ' .
    $recalculateform,
    'mb-3'
);

$filterurl = new moodle_url('/mod/videotrack/report.php');
echo html_writer::start_div('videotrack-report-filters mb-3');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $filterurl->out(false)]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => $mode]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sort', 'value' => $sort]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'aggregation', 'value' => $aggregation]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'window', 'value' => $window]);
echo html_writer::div(html_writer::label(get_string('report:userid', 'mod_videotrack'), 'id_userid') . html_writer::select($useroptions, 'userid', $useridfilter, false, ['id' => 'id_userid']), 'd-inline-block');
echo html_writer::div(html_writer::label(get_string('report:reaction', 'mod_videotrack'), 'id_reactionid') . html_writer::select($reactionoptions, 'reactionid', $reactionidfilter, false, ['id' => 'id_reactionid']), 'd-inline-block mr-2');
echo html_writer::div(
    html_writer::label(get_string('report:timefrom', 'mod_videotrack'), 'id_timefrom') .
    html_writer::empty_tag('input', [
        'type' => 'number', 'step' => '1', 'min' => '0', 'name' => 'timefrom',
        'id' => 'id_timefrom', 'value' => $timefrom === null ? '' : (string)(int)$timefrom,
        'class' => 'form-control d-inline-block', 'style' => 'width:7rem',
    ]),
    'd-inline-block mr-2'
);
echo html_writer::div(
    html_writer::label(get_string('report:timeto', 'mod_videotrack'), 'id_timeto') .
    html_writer::empty_tag('input', [
        'type' => 'number', 'step' => '1', 'min' => '0', 'name' => 'timeto',
        'id' => 'id_timeto', 'value' => $timeto === null ? '' : (string)(int)$timeto,
        'class' => 'form-control d-inline-block', 'style' => 'width:7rem',
    ]),
    'd-inline-block mr-2'
);
echo html_writer::div(
    html_writer::label(get_string('report:notecreatedfrom', 'mod_videotrack'), 'id_notecreatedfrom') .
    html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedfrom',
        'id' => 'id_notecreatedfrom', 'value' => s($notecreatedfrom),
        'class' => 'form-control d-inline-block', 'style' => 'width:10rem',
    ]),
    'd-inline-block mr-2'
);
echo html_writer::div(
    html_writer::label(get_string('report:notecreatedto', 'mod_videotrack'), 'id_notecreatedto') .
    html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedto',
        'id' => 'id_notecreatedto', 'value' => s($notecreatedto),
        'class' => 'form-control d-inline-block', 'style' => 'width:10rem',
    ]),
    'd-inline-block mr-2'
);
echo html_writer::empty_tag('input', ['type' => 'submit', 'class' => 'btn btn-secondary ml-2', 'value' => get_string('filter')]);
echo html_writer::end_tag('form');
echo html_writer::end_div();

if ($mode === 'student') {
    if (!$statecount) {
        echo $OUTPUT->notification(get_string('report:noattempts', 'mod_videotrack'), 'notifymessage');
    } else {
        // $hasgrade e $gradeinfo sono già stati caricati all'inizio del file (OPT-1).
        $usergrades = [];

        $heads = [
            get_string('report:userid', 'mod_videotrack'),
            get_string('report:uniquecoveredseconds', 'mod_videotrack'),
            get_string('report:completionpercent', 'mod_videotrack'),
            get_string('report:lastposition', 'mod_videotrack'),
            get_string('report:iscompleted', 'mod_videotrack'),
        ];
        if ($hasgrade && $cangrade) {
            $heads[] = get_string('report:grade', 'mod_videotrack');
        }
        if (has_capability('mod/videotrack:managereactions', $context)) {
            $heads[] = get_string('report:actions', 'mod_videotrack');
        }

        $table = new html_table();
        $table->head = $heads;

        $staters = $getstaterecordset();
        foreach ($staters as $state) {
            $user = $usermap[(int)$state->userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = [
                (fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : '')),
                videotrack_format_seconds((float)$state->uniquecoveredseconds),
                format_float((float)$state->completionpercent, 2),
                videotrack_format_seconds((float)$state->lastposition),
                $state->iscompleted ? get_string('yes', 'mod_videotrack') : get_string('no', 'mod_videotrack'),
            ];

            if ($hasgrade && $cangrade) {
                // Legge il voto attuale per questo utente.
                $studentname = fullname($user);
                $currentgrade = $gradeinfo->items[0]->grades[(int)$state->userid]->grade ?? '';
                $gradecell = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => $PAGE->url->out(false),
                    'class'  => 'videotrack-grade-form d-inline-flex align-items-center gap-1',
                ]);
                $gradecell .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey',     'value' => sesskey()]);
                $gradecell .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'savegrade',   'value' => 1]);
                $gradecell .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'grade_userid','value' => (int)$state->userid]);

                if ($videotrack->grade > 0) {
                    // Valutazione numerica: campo numerico con max.
                    $gradecell .= html_writer::empty_tag('input', [
                        'type'        => 'number',
                        'name'        => 'grade_value',
                        'class'       => 'form-control form-control-sm',
                        'style'       => 'width:80px',
                        'min'         => 0,
                        'max'         => (int)$videotrack->grade,
                        'step'        => 'any',
                        'value'       => ($currentgrade !== '' ? format_float((float)$currentgrade, 2) : ''),
                        'placeholder' => '-',
                        'aria-label' => get_string('report:gradeinputfor', 'mod_videotrack', $studentname),
                    ]);
                    $gradecell .= html_writer::tag('small', '/ ' . (int)$videotrack->grade,
                        ['class' => 'text-muted ms-1']);
                } else {
                    // Valutazione su scala: menu a tendina.
                    $scaleid = -(int)$videotrack->grade;
                    $scale   = grade_scale::fetch(['id' => $scaleid]);
                    $items   = $scale ? $scale->load_items() : [];
                    $options = ['' => '-'];
                    foreach ($items as $k => $label) {
                        $options[$k + 1] = $label; // Moodle scala: 1-based.
                    }
                    $gradecell .= html_writer::select($options, 'grade_value',
                        ($currentgrade !== '' ? (int)$currentgrade : ''),
                        false, [
                        'class' => 'form-control form-control-sm custom-select',
                        'aria-label' => get_string('report:gradeinputfor', 'mod_videotrack', $studentname),
                    ]);
                }

                $gradecell .= html_writer::tag('button',
                    get_string('save'),
                    [
                        'type' => 'submit',
                        'class' => 'btn btn-sm btn-primary ms-1',
                        'aria-label' => get_string('report:savegradefor', 'mod_videotrack', $studentname),
                    ]
                );
                $gradecell .= html_writer::end_tag('form');

                // Indicatore visivo sufficienza se configurata.
                if (!empty($videotrack->gradepass) && $currentgrade !== '') {
                    $passed = (float)$currentgrade >= (float)$videotrack->gradepass;
                    $passlabel = get_string($passed ? 'report:gradepassed' : 'report:gradefailed', 'mod_videotrack');
                    $gradecell .= html_writer::tag('span',
                        html_writer::span($passed ? '✓' : '✗', '', ['aria-hidden' => 'true']) .
                            html_writer::span($passlabel, 'sr-only'),
                        ['class' => 'ms-1 ' . ($passed ? 'text-success' : 'text-danger'),
                         'title' => get_string('report:gradepass_hint', 'mod_videotrack', format_float((float)$videotrack->gradepass, 2))]
                    );
                }

                $row[] = $gradecell;
            }

            // Reset progress singolo studente (solo per chi ha manage capability).
            if (has_capability('mod/videotrack:managereactions', $context)) {
                $resetform = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => (new moodle_url('/mod/videotrack/report.php', $baseparams))->out(false),
                    'class' => 'd-inline videotrack-reset-student-form',
                ]);
                $resetform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
                $resetform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'resetaction', 'value' => 'resetstudent']);
                $resetform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'resetuserid', 'value' => (int)$state->userid]);
                $resetform .= html_writer::tag('button', get_string('report:resetstudent', 'mod_videotrack'), [
                    'type' => 'submit',
                    'class' => 'btn btn-sm btn-outline-danger videotrack-reset-student',
                    'data-confirm' => get_string('report:resetstudent_confirm', 'mod_videotrack'),
                ]);
                $resetform .= html_writer::end_tag('form');
                $row[] = $resetform;
            }

            $table->data[] = $row;
        }
        $staters->close();
        echo html_writer::table($table);
    }
} else {
    if (!$eventcount) {
        echo $OUTPUT->notification(get_string('report:noreactions', 'mod_videotrack'), 'notifymessage');
    } else {
        $eventrs = $geteventrecordset();
        $clusters = $clusterize($eventrs, $window, $aggregation);
        $eventrs->close();
        if ($clusterlimitreached) {
            echo $OUTPUT->notification(get_string('report:clusterlimitreached', 'mod_videotrack'), 'notifymessage');
            echo $OUTPUT->notification(get_string('report:clusterlimitreached_help', 'mod_videotrack'), 'notifymessage');
            if (!$hasvideotimefilter) {
                echo $OUTPUT->notification(get_string('report:clusterlimitrequiresfilters', 'mod_videotrack'), 'warning');
                echo $OUTPUT->notification(get_string('report:clusterdisplayblocked', 'mod_videotrack'), 'warning');
                $clusters = [];
            }
        }

        if ($clusters) {
            $topclusters = $clusters;
            usort($topclusters, static fn($a, $b) => $b['count'] <=> $a['count']);
            $topclusters = array_slice($topclusters, 0, 5);
            $items = [];
            foreach ($topclusters as $topcluster) {
                $items[] = get_string('report:topclusteritem', 'mod_videotrack', [
                    'time' => videotrack_format_seconds($topcluster['timestamp']),
                    'reaction' => s($topcluster['reactionlabel']),
                    'clicks' => (int)$topcluster['count'],
                ]);
            }
            if ($items) {
                echo html_writer::tag('p', get_string('report:topclusterssummary', 'mod_videotrack'), [
                    'class' => 'font-weight-bold mb-1',
                ]);
                echo html_writer::alist($items, ['class' => 'small']);
            }
        }

        // Heatmap SVG: mostra la distribuzione delle reazioni sul timeline del video.
        $duration = (float)($DB->get_field('videotrack', 'durationseconds', ['id' => $videotrack->id]) ?: 0);
        if ($duration > 0 && $clusters) {
            $svgw = 800; $svgh = 48; $barh = 32; $pady = 8;
            $maxcount = max(array_column($clusters, 'count'));
            // Raccoglie i colori per reazione (ciclo di palette accessibile).
            $palette = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc948','#b07aa1','#ff9da7'];
            $reactioncolors = [];
            $ci = 0;
            foreach ($reactions as $r) {
                $reactioncolors[(int)$r->id] = $palette[$ci % count($palette)];
                $ci++;
            }
            $svgtitle = s(get_string('report:heatmap_desc', 'mod_videotrack'));
            $svg  = "<svg viewBox=\"0 0 {$svgw} {$svgh}\" xmlns=\"http://www.w3.org/2000/svg\" ";
            $svg .= "role=\"img\" aria-label=\"{$svgtitle}\" aria-describedby=\"videotrack-heatmap-table\" ";
            $svg .= "style=\"width:100%;max-width:{$svgw}px;height:{$svgh}px;display:block;margin-bottom:1rem;border:1px solid #dee2e6;border-radius:4px;background:#f8f9fa\">";
            $svg .= "<title>{$svgtitle}</title>";
            $patternpaths = [
                '<path d="M0 6 L6 0" stroke="#000" stroke-width="1" opacity="0.25"/>',
                '<path d="M0 0 L6 6" stroke="#000" stroke-width="1" opacity="0.25"/>',
                '<path d="M3 0 L3 6" stroke="#000" stroke-width="1" opacity="0.25"/>',
                '<path d="M0 3 L6 3" stroke="#000" stroke-width="1" opacity="0.25"/>',
            ];
            $svg .= '<defs>';
            $patternmap = [];
            $pi = 0;
            foreach ($reactions as $r) {
                $patternid = 'videotrack-hatch-' . (int)$r->id;
                $patternmap[(int)$r->id] = $patternid;
                $svg .= '<pattern id="' . $patternid . '" width="6" height="6" patternUnits="userSpaceOnUse">' .
                    $patternpaths[$pi % count($patternpaths)] . '</pattern>';
                $pi++;
            }
            $svg .= '</defs>';
            // Barra di sfondo timeline.
            $svg .= "<rect x=\"0\" y=\"{$pady}\" width=\"{$svgw}\" height=\"{$barh}\" rx=\"3\" fill=\"#e9ecef\"/>";
            $labelled = 0;
            $labelthreshold = max(1, (int)ceil($maxcount * 0.75));
            foreach ($clusters as $cluster) {
                $x   = (int)min($svgw, max(0, (($cluster['timestamp'] / $duration) * $svgw)));
                $h   = max(2, (int)(($cluster['count'] / $maxcount) * $barh));
                $y   = $pady + $barh - $h;
                $col = $reactioncolors[(int)$cluster['reactionid']] ?? '#4e79a7';
                $tip = s($cluster['reactionlabel']) . ': ' . $cluster['count'] . ' @ ' .
                       videotrack_format_seconds($cluster['timestamp']);
                $rectx = max(0, min($svgw - 6, $x - 3));
                $svg .= "<rect x=\"{$rectx}\" y=\"{$y}\" width=\"6\" height=\"{$h}\" ";
                $svg .= "rx=\"2\" fill=\"{$col}\" opacity=\"0.85\"><title>{$tip}</title></rect>";
                $patternid = $patternmap[(int)$cluster['reactionid']] ?? '';
                if ($patternid !== '') {
                    $svg .= "<rect x=\"{$rectx}\" y=\"{$y}\" width=\"6\" height=\"{$h}\" fill=\"url(#{$patternid})\" opacity=\"0.35\"/>";
                }
                if ($cluster['count'] >= $labelthreshold && $labelled < 8) {
                    $textx = min($svgw - 24, max(2, $x + 4));
                    $texty = max(8, $y - 2);
                    $svg .= "<text x=\"{$textx}\" y=\"{$texty}\" font-size=\"10\" fill=\"#212529\">" . (int)$cluster['count'] . "</text>";
                    $labelled++;
                }
            }
            $svg .= '</svg>';
            echo html_writer::tag('p', get_string('report:heatmap_supplementary', 'mod_videotrack'), [
                'class' => 'small mb-1'
            ]);
            echo html_writer::link('#videotrack-heatmap-table', get_string('report:skiptoheatmaptable', 'mod_videotrack'), [
                'class' => 'sr-only sr-only-focusable d-block mb-2'
            ]);
            echo html_writer::tag('p', get_string('report:heatmap_desc', 'mod_videotrack') . ' ' .
                get_string('report:heatmap_textsummary', 'mod_videotrack', [
                    'clusters' => count($clusters),
                    'max' => $maxcount,
                ]), ['class' => 'text-muted small mb-1']);
            $legenditems = [];
            foreach ($reactions as $r) {
                $color = $reactioncolors[(int)$r->id] ?? '#4e79a7';
                $swatch = html_writer::span('', 'videotrack-heatmap-swatch', [
                    'aria-hidden' => 'true',
                    'style' => 'display:inline-block;width:0.9em;height:0.9em;margin-right:0.35em;border:1px solid #555;background:' . $color . ';background-image:repeating-linear-gradient(45deg,rgba(0,0,0,.25) 0,rgba(0,0,0,.25) 1px,transparent 1px,transparent 4px)',
                ]);
                $legenditems[] = html_writer::tag('li', $swatch . s($r->label), ['class' => 'list-inline-item mr-3']);
            }
            if ($legenditems) {
                echo html_writer::tag('ul', implode('', $legenditems), [
                    'class' => 'list-inline small mb-2',
                    'aria-label' => get_string('report:heatmap_legend', 'mod_videotrack'),
                ]);
            }
            echo $svg;
        }

        $table = new html_table();
        $table->attributes['id'] = 'videotrack-heatmap-table';
        $table->head = [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:clicks', 'mod_videotrack'),
            get_string('report:students', 'mod_videotrack'),
            get_string('report:replay', 'mod_videotrack'),
        ];
        foreach ($clusters as $cluster) {
            $reactionhtml = $cluster['reaction'] ? videotrack_render_reaction_icon($cluster['reaction'], $context, true) : s($cluster['reactionlabel']);
            $start = max(0, $cluster['timestamp'] - 30);
            $end = $cluster['timestamp'] + 30;
            $replayurl = new moodle_url('/mod/videotrack/view.php', ['id' => $cm->id, 'replaystart' => (int)$start, 'replayend' => (int)$end]);
            $table->data[] = [
                videotrack_format_seconds($cluster['timestamp']),
                html_writer::span($reactionhtml, 'videotrack-report-icon'),
                (int)$cluster['count'],
                (int)$cluster['students'],
                html_writer::span(html_writer::link($replayurl, get_string('report:replay', 'mod_videotrack')), 'videotrack-replay-inline'),
            ];
        }
        echo html_writer::table($table);
    }
}

// Inizializza il modulo AMD: gestisce la conferma del reset studente.
$PAGE->requires->js_call_amd('mod_videotrack/report', 'init', [[
    'confirmreset' => get_string('report:resetstudent_confirm', 'mod_videotrack'),
    'confirmrecalculate' => get_string('report:recalculate', 'mod_videotrack'),
    'labels' => [
        'confirm' => get_string('confirm', 'moodle'),
        'yes' => get_string('yes', 'moodle'),
        'cancel' => get_string('cancel', 'moodle'),
    ],
]]);

// ── Sezione note studenti (solo nella modalità per-studente, solo se le note sono abilitate) ──
if ($mode === 'student' && !empty($videotrack->studentnotesenabled)) {
    $notewhere = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'" .
        ($useridfilter > 0 ? ' AND userid = :uid' : '');
    $noteparams = array_filter(['vtid' => $videotrack->id, 'uid' => $useridfilter ?: null]);
    if ($notecreatedfromts) {
        $notewhere .= ' AND timecreated >= :notecreatedfrom';
        $noteparams['notecreatedfrom'] = $notecreatedfromts;
    }
    if ($notecreatedtots) {
        $notewhere .= ' AND timecreated <= :notecreatedto';
        $noteparams['notecreatedto'] = $notecreatedtots;
    }
    $notelimit = 100;
    $notecount = $DB->count_records_select('videotrack_reactev', $notewhere, $noteparams);
    $notes = $DB->get_records_select(
        'videotrack_reactev',
        $notewhere,
        $noteparams,
        'userid ASC, videotime ASC',
        'id, userid, videotime, notetext, timecreated',
        $notepage * $notelimit,
        $notelimit
    );

    echo html_writer::start_div('videotrack-notes-report mt-4');
    echo $OUTPUT->heading(get_string('report:notes_title', 'mod_videotrack'), 3);

    $ntable = new html_table();
    $ntable->head = [
        get_string('report:userid',    'mod_videotrack'),
        get_string('report:timestamp', 'mod_videotrack'),
        get_string('studentnote_label', 'mod_videotrack'),
        get_string('report:notedate',  'mod_videotrack'),
    ];
    $ntable->attributes['class'] = 'generaltable';
    foreach ($notes as $note) {
        $nuser = $usermap[(int)$note->userid] ?? null;
        $username = videotrack_report_user_label((int)$note->userid, $usermap, $canviewemail);
        $ntable->data[] = [
            $username,
            videotrack_format_seconds((float)$note->videotime),
            s($note->notetext),
            userdate((int)$note->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        ];
    }

    if ($notecount === 0) {
        echo $OUTPUT->notification(get_string('report:nonotes', 'mod_videotrack'), 'notifymessage');
    } else {
        $pagingurl = new moodle_url('/mod/videotrack/report.php', $baseparams + ['mode' => 'student']);
        echo $OUTPUT->paging_bar($notecount, $notepage, $notelimit, $pagingurl, 'notepage');
        echo html_writer::table($ntable);
        echo $OUTPUT->paging_bar($notecount, $notepage, $notelimit, $pagingurl, 'notepage');

        echo $OUTPUT->notification(get_string('report:exportnotes_privacywarning', 'mod_videotrack'), 'notifywarning');

        // Export CSV note via POST to avoid exposing sesskey in URLs/history.
        $notesexportform = html_writer::start_tag('form', [
            'method' => 'post',
            'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
            'class' => 'd-inline videotrack-notes-export-form',
        ]);
        foreach ($baseparams as $name => $value) {
            $notesexportform .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => $name,
                'value' => $value,
            ]);
        }
        $notesexportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
        $notesexportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'export', 'value' => 'notes_csv']);
        $notesexportform .= html_writer::start_div('form-check mt-2');
        $notesexportform .= html_writer::empty_tag('input', [
            'type' => 'checkbox',
            'name' => 'confirmnotesexport',
            'value' => 1,
            'required' => 'required',
            'class' => 'form-check-input',
            'id' => 'id_confirmnotesexport',
        ]);
        $notesexportform .= html_writer::tag('label',
            get_string('report:exportnotes_confirm', 'mod_videotrack'),
            ['class' => 'form-check-label', 'for' => 'id_confirmnotesexport']
        );
        $notesexportform .= html_writer::end_div();
        $notesexportform .= html_writer::tag('button', get_string('report:exportnotes_csv', 'mod_videotrack'), [
            'type' => 'submit',
            'class' => 'btn btn-sm btn-outline-secondary mt-2',
            'aria-label' => get_string('report:exportnotes_csv_personaldata', 'mod_videotrack'),
        ]);
        $notesexportform .= html_writer::end_tag('form');
        echo $notesexportform;
    }
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
