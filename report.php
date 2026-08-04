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

/**
 * Converts an ISO date-only parameter to a timestamp in the user's timezone.
 *
 * @param string $date Date in YYYY-MM-DD format.
 * @param bool $endofday Whether to use the last second of the day.
 * @return int Timestamp, or 0 when the value is empty or invalid.
 */
function videotrack_report_date_to_timestamp(string $date, bool $endofday = false): int {
    if ($date === '' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
        return 0;
    }
    $year = (int)$matches[1];
    $month = (int)$matches[2];
    $day = (int)$matches[3];
    if (!checkdate($month, $day, $year)) {
        return 0;
    }
    return make_timestamp($year, $month, $day, $endofday ? 23 : 0, $endofday ? 59 : 0, $endofday ? 59 : 0);
}

/**
 * Reads an optional video-time filter.
 *
 * The report form submits numeric hour/minute/second controls. Legacy MM:SS and
 * HH:MM:SS links remain supported for backwards compatibility.
 *
 * @param string $name Parameter name.
 * @return float|null Non-negative float value, or null when unset/empty.
 */
function videotrack_report_optional_time_param(string $name): ?float {
    $componentnames = [
        'hours' => $name . '_hours',
        'minutes' => $name . '_minutes',
        'seconds' => $name . '_seconds',
    ];
    $rawparts = [];
    foreach ($componentnames as $part => $componentname) {
        $rawparts[$part] = optional_param($componentname, null, PARAM_RAW_TRIMMED);
    }
    $hascomponents = count(array_filter($rawparts, static function ($value): bool {
        return $value !== null;
    })) > 0;
    if ($hascomponents) {
        if (
            ($rawparts['hours'] === '' || $rawparts['hours'] === null)
            && ($rawparts['minutes'] === '' || $rawparts['minutes'] === null)
            && ($rawparts['seconds'] === '' || $rawparts['seconds'] === null)
        ) {
            return null;
        }
        foreach ($rawparts as $rawpart) {
            if ($rawpart !== '' && $rawpart !== null && !preg_match('/^\d+$/', $rawpart)) {
                throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
            }
        }
        $hours = ($rawparts['hours'] === '' || $rawparts['hours'] === null) ? 0 : (int)$rawparts['hours'];
        $minutes = ($rawparts['minutes'] === '' || $rawparts['minutes'] === null) ? 0 : (int)$rawparts['minutes'];
        $seconds = ($rawparts['seconds'] === '' || $rawparts['seconds'] === null) ? 0 : (int)$rawparts['seconds'];
        if ($minutes > 59 || $seconds > 59) {
            throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
        }
        return (float)(($hours * HOURSECS) + ($minutes * MINSECS) + $seconds);
    }

    $rawvalue = optional_param($name, '', PARAM_RAW_TRIMMED);
    $parsed = videotrack_parse_report_timestamp($rawvalue);
    if ($rawvalue !== '' && $parsed === null) {
        throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
    }
    return $parsed;
}

/**
 * Renders a structured duration filter using number inputs.
 *
 * @param string $name Base parameter name.
 * @param string $label Visible field label.
 * @param float|null $value Current value in seconds.
 * @param bool $showhours Whether to render the hours control.
 * @return string HTML fragment.
 */
function videotrack_report_duration_filter(string $name, string $label, ?float $value, bool $showhours): string {
    $totalseconds = $value === null ? null : max(0, (int)round($value));
    $hours = $totalseconds === null ? '' : (string)floor($totalseconds / HOURSECS);
    $minutes = $totalseconds === null ? '' : (string)floor(($totalseconds % HOURSECS) / MINSECS);
    $seconds = $totalseconds === null ? '' : (string)($totalseconds % MINSECS);
    if (!$showhours && $totalseconds !== null) {
        $minutes = (string)floor($totalseconds / MINSECS);
    }

    $groupid = 'id_' . $name . '_group';
    $html = html_writer::span($label, 'mr-1', ['id' => $groupid . '_label']);
    $attributes = [
        'type' => 'number',
        'min' => 0,
        'step' => 1,
        'inputmode' => 'numeric',
        'autocomplete' => 'off',
        'class' => 'form-control form-control-sm videotrack-time-part',
        'style' => 'width:4.5rem',
    ];
    if ($showhours) {
        $html .= html_writer::empty_tag('input', array_merge($attributes, [
            'name' => $name . '_hours',
            'id' => 'id_' . $name . '_hours',
            'value' => $hours,
            'aria-label' => get_string('hours'),
        ]));
        $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
    }
    $html .= html_writer::empty_tag('input', array_merge($attributes, [
        'name' => $name . '_minutes',
        'id' => 'id_' . $name . '_minutes',
        'value' => $minutes,
        'max' => 59,
        'aria-label' => get_string('minutes'),
    ]));
    $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
    $html .= html_writer::empty_tag('input', array_merge($attributes, [
        'name' => $name . '_seconds',
        'id' => 'id_' . $name . '_seconds',
        'value' => $seconds,
        'max' => 59,
        'aria-label' => get_string('seconds'),
    ]));
    return html_writer::div($html, 'd-inline-flex align-items-center mr-3 mb-2', [
        'id' => $groupid,
        'role' => 'group',
        'aria-labelledby' => $groupid . '_label',
    ]);
}

global $DB, $USER, $CFG, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT);
$sort = optional_param('sort', 'time', PARAM_ALPHA);
$mode = optional_param('mode', 'student', PARAM_ALPHA);
$aggregation = optional_param('aggregation', 'type', PARAM_ALPHA);
$window = optional_param('window', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHANUMEXT);
$csvuserid = optional_param('csvuserid', 0, PARAM_INT);
$csvincludereactions = optional_param(
    'csvincludereactions',
    $_SERVER['REQUEST_METHOD'] === 'POST' ? 0 : 1,
    PARAM_BOOL
);
$csvincludenotes = optional_param('csvincludenotes', 0, PARAM_BOOL);
$csvformat = optional_param('csvformat', 'detailed', PARAM_ALPHA);
$recalculateuserid = optional_param('recalculateuserid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$resetaction = optional_param('resetaction', '', PARAM_ALPHA);
$useridfilter = optional_param('userid', 0, PARAM_INT);
$reactionidfilter = optional_param('reactionid', 0, PARAM_INT);
$notepage = max(0, optional_param('notepage', 0, PARAM_INT));
$notecreatedfrom = videotrack_optional_iso_date_param('notecreatedfrom');
$notecreatedto = videotrack_optional_iso_date_param('notecreatedto');
$timefrom = videotrack_report_optional_time_param('timefrom');
$timeto = videotrack_report_optional_time_param('timeto');
if ($timefrom !== null && $timeto !== null && $timeto < $timefrom) {
    [$timefrom, $timeto] = [$timeto, $timefrom];
}
$notecreatedfromts = videotrack_report_date_to_timestamp($notecreatedfrom);
$notecreatedtots = videotrack_report_date_to_timestamp($notecreatedto, true);
if ($notecreatedfromts && $notecreatedtots && $notecreatedtots < $notecreatedfromts) {
    [$notecreatedfromts, $notecreatedtots] = [$notecreatedtots, $notecreatedfromts];
}

$cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
$csvincludenotes = !empty($videotrack->studentnotesenabled) && $csvincludenotes;
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$canviewfullreport = has_capability('mod/videotrack:viewreport', $context);
$canviewownreport = has_capability('mod/videotrack:viewownreport', $context);

$window = $window ?: (int)$videotrack->clusterwindow;
$validwindows = [10, 15, 20, 30, 60];
if (!in_array($window, $validwindows, true)) {
    $window = 30;
}
$mode = in_array($mode, ['student', 'cumulative', 'export', 'recalculate'], true) ? $mode : 'student';
if (!$canviewfullreport) {
    require_capability('mod/videotrack:viewownreport', $context);
    if ($mode !== 'student' || ($export !== '' && $export !== 'csv') || $action !== '' || $resetaction !== '') {
        require_capability('mod/videotrack:viewreport', $context);
    }
    // Users with only the own-report capability may see only their own student report.
    $mode = 'student';
    $useridfilter = (int)$USER->id;
}
$aggregation = in_array($aggregation, ['type', 'peak'], true) ? $aggregation : 'type';
$sort = in_array($sort, ['time', 'reaction', 'clicks'], true) ? $sort : 'time';
$csvformat = in_array($csvformat, ['detailed', 'summary', 'overall'], true) ? $csvformat : 'detailed';

$sortsql = 'videotime ASC';
if ($sort === 'reaction') {
    $sortsql = 'reactionlabel ASC, videotime ASC';
}

$reactions = videotrack_get_reactions($videotrack->id);
$reactionmap = [];
foreach ($reactions as $reaction) {
    $reactionmap[(int)$reaction->id] = $reaction;
}

// Standard reaction events: standard reactions only (excludes personal notes, notetype='note').
// Notes are shown in a separate section further below.
$eventconditions = "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)";
$eventparamsnamed = ['vtid' => $videotrack->id];
if ($useridfilter > 0) {
    $eventconditions .= ' AND userid = :uid';
    $eventparamsnamed['uid'] = $useridfilter;
}
if ($reactionidfilter > 0) {
    $eventconditions .= ' AND reactionid = :rid';
    $eventparamsnamed['rid'] = $reactionidfilter;
}
if ($timefrom !== null) {
    $eventconditions .= ' AND videotime >= :timefrom';
    $eventparamsnamed['timefrom'] = $timefrom;
}
if ($timeto !== null) {
    $eventconditions .= ' AND videotime <= :timeto';
    $eventparamsnamed['timeto'] = $timeto;
}
// Avoid loading all reaction events into memory. Use count/distinct queries for filters
// and recordsets only where the full event stream is required for CSV or the clustered report.
$eventcount = $DB->count_records_select('videotrack_reactev', $eventconditions, $eventparamsnamed);
$eventuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_reactev',
    'DISTINCT userid',
    $eventconditions,
    $eventparamsnamed
));
$geteventrecordset = static function () use ($DB, $eventconditions, $eventparamsnamed) {
    return $DB->get_recordset_select(
        'videotrack_reactev',
        $eventconditions,
        $eventparamsnamed,
        'videotime ASC',
        'id, userid, reactionid, reactionlabel, videotime'
    );
};

$stateparams = ['videotrackid' => $videotrack->id];
$stateconditions = 'videotrackid = :svtid';
$stateparamsnamed = ['svtid' => $videotrack->id];
if ($useridfilter > 0) {
    $stateparams['userid'] = $useridfilter;
    $stateconditions .= ' AND userid = :suid';
    $stateparamsnamed['suid'] = $useridfilter;
}
$statecount = $DB->count_records_select('videotrack_state', $stateconditions, $stateparamsnamed);
$stateuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_state',
    'DISTINCT userid',
    $stateconditions,
    $stateparamsnamed
));
$segmentuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_seg',
    'DISTINCT userid',
    'videotrackid = :vtid',
    ['vtid' => $videotrack->id]
));
$getstaterecordset = static function () use ($DB, $stateparams) {
    return $DB->get_recordset('videotrack_state', $stateparams, 'completionpercent DESC, uniquecoveredseconds DESC');
};

// Collect note user ids (they may have neither state nor events).
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

// Load all required users in a single query instead of N core_user::get_user() calls.
$alluserids = array_values(array_filter(array_unique(array_merge(
    $stateuserids,
    $segmentuserids,
    $eventuserids,
    $noteuserids
)), static function (int $userid): bool {
    return $userid > 0;
}));
$usermap = [];
$canviewemail = false;
if ($alluserids) {
    [$insql, $inparams] = $DB->get_in_or_equal($alluserids, SQL_PARAMS_NAMED);
    // Email is visible only to users with the viewreport capability and permission to see email addresses.
    // GDPR minimisation: by default show only the full name.
    $canviewemail = has_capability('moodle/site:viewuseridentity', $context) &&
            in_array('email', \core_user\fields::get_identity_fields($context, false));
    // Select all Moodle name fields required by fullname(). Email is loaded only when permitted.
    $userfields = array_unique(array_merge(
        ['id', 'deleted'],
        \core_user\fields::get_name_fields(),
        $canviewemail ? ['email'] : []
    ));
    foreach ($DB->get_records_select('user', "id $insql", $inparams, '', implode(',', $userfields)) as $u) {
        $usermap[(int)$u->id] = $u;
    }
}

$useroptions = [0 => get_string('all')];
foreach ($stateuserids as $stateuserid) {
    $user = $usermap[(int)$stateuserid] ?? null;
    if ($user) {
        $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
    }
}
foreach ($segmentuserids as $segmentuserid) {
    if (!isset($useroptions[(int)$segmentuserid])) {
        $user = $usermap[(int)$segmentuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}
foreach ($eventuserids as $eventuserid) {
    if (!isset($useroptions[(int)$eventuserid])) {
        $user = $usermap[(int)$eventuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}
foreach ($noteuserids as $noteuserid) {
    if (!isset($useroptions[(int)$noteuserid])) {
        $user = $usermap[(int)$noteuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}

$reactionoptions = [0 => get_string('all')];
foreach ($reactions as $reaction) {
    $reactionoptions[(int)$reaction->id] = $reaction->label;
}

// Define $baseparams once for exports, actions and navigation links.
$baseparams = [
    'id' => $cm->id,
    'mode' => $mode,
    'sort' => $sort,
    'aggregation' => $aggregation,
    'window' => $window,
    'userid' => $useridfilter,
    'reactionid' => $reactionidfilter,
    'timefrom' => $timefrom === null ? '' : videotrack_format_video_timestamp(
        $timefrom,
        (float)$videotrack->durationseconds
    ),
    'timeto' => $timeto === null ? '' : videotrack_format_video_timestamp(
        $timeto,
        (float)$videotrack->durationseconds
    ),
    'notecreatedfrom' => $notecreatedfrom,
    'notecreatedto' => $notecreatedto,
];
$baseurl = new moodle_url('/mod/videotrack/report.php', $baseparams);
$hasvideotimefilter = ($timefrom !== null || $timeto !== null);

// Load grade_get_grades once for all report sections.
$hasgrade  = !empty($videotrack->grade);
$cangrade = has_capability('mod/videotrack:grade', $context);
$gradeinfo = null;
if ($hasgrade && $cangrade && $alluserids) {
    require_once($CFG->libdir . '/gradelib.php');
    $gradeinfo = grade_get_grades(
        $course->id,
        'mod',
        'videotrack',
        $videotrack->id,
        array_keys($usermap)
    );
}

$clusterlimitreached = false;
$clusterize = function (
    iterable $events,
    int $windowseconds,
    string $aggregationmode
) use (
    $reactionmap,
    $sort,
    $context,
    &$clusterlimitreached
) {
    // Events are processed in timestamp order. Keep only the latest open cluster
    // per reaction (or a single cluster for peak mode), avoiding the former O(n * clusters).
    // scan for every event.
    $clusters = [];
    $activeindex = [];
    $maxclusters = videotrack_get_config_int('reportclusterlimit', 2000, 500, 10000);
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

$csvdelimiter = \mod_videotrack\local\csv_export::delimiter($videotrack);
$csvfields = \mod_videotrack\local\csv_export::activity_fields($videotrack, $context);
$csvusermap = \mod_videotrack\local\csv_export::load_users($alluserids, $csvfields);
$videoduration = (float)$videotrack->durationseconds;

if ($export === 'custom_csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    if (!$csvincludereactions && !$csvincludenotes) {
        throw new moodle_exception('report:csvexport_selectcontent', 'mod_videotrack');
    }
    if ($csvformat === 'overall') {
        $csvuserid = 0;
    }
    if ($csvuserid > 0) {
        if (!is_enrolled($context, $csvuserid, '', true)) {
            throw new moodle_exception('invaliduser', 'error');
        }
        $exportuserids = [$csvuserid];
    } else {
        $exportuserids = array_map('intval', $DB->get_fieldset_select(
            'videotrack_reactev',
            'DISTINCT userid',
            'videotrackid = :vtid AND isdeleted = 0',
            ['vtid' => $videotrack->id]
        ));
    }
    $exportuserids = array_values(array_filter(array_unique($exportuserids), static function (int $userid): bool {
        return $userid > 0;
    }));
    $exportusermap = \mod_videotrack\local\csv_export::load_users($exportuserids, $csvfields);

    \mod_videotrack\event\report_exported::create([
        'objectid' => (int)$videotrack->id,
        'context' => $context,
        'userid' => (int)$USER->id,
        'other' => [
            'exporttype' => 'custom_csv_' . $csvformat,
            'fieldcount' => count($csvfields),
        ],
    ])->trigger();

    $filename = 'videotrack_export_' . $cm->id . '_' . $csvformat . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);

    $eventheaders = [
        get_string('report:csvcol_eventtype', 'mod_videotrack'),
        get_string('report:reaction', 'mod_videotrack'),
        get_string('report:csvcol_comment', 'mod_videotrack'),
        get_string('report:timestamp', 'mod_videotrack'),
        get_string('report:csvcol_firsttimestamp', 'mod_videotrack'),
        get_string('report:csvcol_lasttimestamp', 'mod_videotrack'),
        get_string('report:csvcol_count', 'mod_videotrack'),
        get_string('report:csvcol_created', 'mod_videotrack'),
    ];
    if ($csvformat === 'overall') {
        $eventheaders[] = get_string('report:students', 'mod_videotrack');
    }
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        $eventheaders
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);

    $writeeventrow = static function (
        int $userid,
        string $eventtype,
        string $reactionlabel,
        string $comment,
        float $timestamp,
        float $firsttimestamp,
        float $lasttimestamp,
        int $count,
        string $created,
        int $studentcount = 1
    ) use (
        $fh,
        $csvdelimiter,
        $csvfields,
        $course,
        $videotrack,
        $exportusermap,
        $cm,
        $videoduration,
        $csvformat
    ): void {
        $user = $userid > 0 ? ($exportusermap[$userid] ?? null) : null;
        if ($userid > 0 && !$user) {
            return;
        }
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            $userid > 0 ? videotrack_report_user_label($userid, $exportusermap, false) : '',
            (int)$cm->id
        );
        $row = array_merge($row, [
            $eventtype,
            $reactionlabel,
            $comment,
            videotrack_format_video_timestamp($timestamp, $videoduration),
            videotrack_format_video_timestamp($firsttimestamp, $videoduration),
            videotrack_format_video_timestamp($lasttimestamp, $videoduration),
            $count,
            $created,
        ]);
        if ($csvformat === 'overall') {
            $row[] = $studentcount;
        }
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
    };

    $scopewhere = 'videotrackid = :vtid AND isdeleted = 0';
    $scopeparams = ['vtid' => $videotrack->id];
    if ($csvuserid > 0 && $csvformat !== 'overall') {
        $scopewhere .= ' AND userid = :exportuserid';
        $scopeparams['exportuserid'] = $csvuserid;
    }

    if ($csvformat === 'detailed') {
        $typeconditions = [];
        if ($csvincludereactions) {
            $typeconditions[] = "(notetype = '' OR notetype IS NULL)";
        }
        if ($csvincludenotes && !empty($videotrack->studentnotesenabled)) {
            $typeconditions[] = "notetype = 'note'";
        }
        if ($typeconditions) {
            $rs = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . ' AND (' . implode(' OR ', $typeconditions) . ')',
                $scopeparams,
                'userid ASC, videotime ASC, timecreated ASC',
                'userid, reactionlabel, notetext, notetype, videotime, timecreated'
            );
            foreach ($rs as $record) {
                $isnote = $record->notetype === 'note';
                $writeeventrow(
                    (int)$record->userid,
                    get_string($isnote ? 'report:eventtype_note' : 'report:eventtype_reaction', 'mod_videotrack'),
                    $isnote ? '' : format_string($record->reactionlabel, true, ['context' => $context]),
                    $isnote ? (string)$record->notetext : '',
                    (float)$record->videotime,
                    (float)$record->videotime,
                    (float)$record->videotime,
                    1,
                    userdate((int)$record->timecreated)
                );
            }
            $rs->close();
        }
    } else {
        if ($csvincludereactions) {
            $reactionrs = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . " AND (notetype = '' OR notetype IS NULL)",
                $scopeparams,
                $csvformat === 'overall' ? 'videotime ASC' : 'userid ASC, videotime ASC',
                'userid, reactionid, reactionlabel, videotime'
            );
            if ($csvformat === 'overall') {
                foreach ($clusterize($reactionrs, $window, 'type') as $cluster) {
                    $writeeventrow(
                        0,
                        get_string('report:eventtype_reaction', 'mod_videotrack'),
                        (string)$cluster['reactionlabel'],
                        '',
                        (float)$cluster['timestamp'],
                        (float)$cluster['first'],
                        (float)$cluster['last'],
                        (int)$cluster['count'],
                        '',
                        (int)$cluster['students']
                    );
                }
            } else {
                $currentuserid = 0;
                $userevents = [];
                $flushclusters = static function () use (
                    &$currentuserid,
                    &$userevents,
                    $clusterize,
                    $window,
                    $writeeventrow
                ): void {
                    if ($currentuserid <= 0 || !$userevents) {
                        return;
                    }
                    foreach ($clusterize($userevents, $window, 'type') as $cluster) {
                        $writeeventrow(
                            $currentuserid,
                            get_string('report:eventtype_reaction', 'mod_videotrack'),
                            (string)$cluster['reactionlabel'],
                            '',
                            (float)$cluster['timestamp'],
                            (float)$cluster['first'],
                            (float)$cluster['last'],
                            (int)$cluster['count'],
                            ''
                        );
                    }
                    $userevents = [];
                };
                foreach ($reactionrs as $reactionevent) {
                    $userid = (int)$reactionevent->userid;
                    if ($currentuserid !== 0 && $userid !== $currentuserid) {
                        $flushclusters();
                    }
                    $currentuserid = $userid;
                    $userevents[] = $reactionevent;
                }
                $flushclusters();
            }
            $reactionrs->close();
        }
        if ($csvincludenotes && !empty($videotrack->studentnotesenabled)) {
            $noters = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . " AND notetype = 'note'",
                $scopeparams,
                'userid ASC, videotime ASC, timecreated ASC',
                'userid, notetext, videotime, timecreated'
            );
            foreach ($noters as $note) {
                $writeeventrow(
                    (int)$note->userid,
                    get_string('report:eventtype_note', 'mod_videotrack'),
                    '',
                    (string)$note->notetext,
                    (float)$note->videotime,
                    (float)$note->videotime,
                    (float)$note->videotime,
                    1,
                    userdate((int)$note->timecreated)
                );
            }
            $noters->close();
        }
    }

    fclose($fh);
    exit;
}

if ($export === 'events_csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    if (!optional_param('confirmeventsexport', 0, PARAM_BOOL)) {
        throw new moodle_exception('report:exportallevents_confirmrequired', 'mod_videotrack');
    }

    $alleventuserids = array_map('intval', $DB->get_fieldset_select(
        'videotrack_reactev',
        'DISTINCT userid',
        'videotrackid = :vtid AND isdeleted = 0',
        ['vtid' => $videotrack->id]
    ));
    $alleventusermap = \mod_videotrack\local\csv_export::load_users($alleventuserids, $csvfields);
    $event = \mod_videotrack\event\report_exported::create([
        'objectid' => (int)$videotrack->id,
        'context' => $context,
        'userid' => (int)$USER->id,
        'other' => [
            'exporttype' => 'events_csv',
            'fieldcount' => count($csvfields),
        ],
    ]);
    $event->trigger();

    $filename = 'videotrack_events_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        [
            get_string('report:csvcol_eventtype', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:csvcol_comment', 'mod_videotrack'),
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:csvcol_created', 'mod_videotrack'),
        ]
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);

    $rs = $DB->get_recordset_select(
        'videotrack_reactev',
        'videotrackid = :vtid AND isdeleted = 0',
        ['vtid' => $videotrack->id],
        'userid ASC, videotime ASC, timecreated ASC',
        'userid, reactionlabel, notetext, notetype, videotime, timecreated'
    );
    foreach ($rs as $record) {
        $userid = (int)$record->userid;
        $user = $alleventusermap[$userid] ?? null;
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            videotrack_report_user_label($userid, $alleventusermap, false),
            (int)$cm->id
        );
        $isnote = $record->notetype === 'note';
        $row = array_merge($row, [
            get_string($isnote ? 'report:eventtype_note' : 'report:eventtype_reaction', 'mod_videotrack'),
            $isnote ? '' : format_string($record->reactionlabel, true, ['context' => $context]),
            $isnote ? (string)$record->notetext : '',
            videotrack_format_video_timestamp((float)$record->videotime, $videoduration),
            userdate((int)$record->timecreated),
        ]);
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
    }
    $rs->close();
    fclose($fh);
    exit;
}

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
            'emailincluded' => in_array('email', $csvfields, true),
        ],
    ]);
    $event->trigger();
    $filename = 'videotrack_notes_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:csvcol_comment', 'mod_videotrack'),
            get_string('report:csvcol_created', 'mod_videotrack'),
        ]
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);
    $notecsvwhere = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'";
    $notecsvparams = ['vtid' => $videotrack->id];
    if ($useridfilter > 0) {
        $notecsvwhere .= ' AND userid = :uid';
        $notecsvparams['uid'] = $useridfilter;
    }
    if ($notecreatedfromts) {
        $notecsvwhere .= ' AND timecreated >= :notecreatedfrom';
        $notecsvparams['notecreatedfrom'] = $notecreatedfromts;
    }
    if ($notecreatedtots) {
        $notecsvwhere .= ' AND timecreated <= :notecreatedto';
        $notecsvparams['notecreatedto'] = $notecreatedtots;
    }
    $rs = $DB->get_recordset_select(
        'videotrack_reactev',
        $notecsvwhere,
        $notecsvparams,
        'userid ASC, videotime ASC',
        'userid, videotime, notetext, timecreated'
    );
    foreach ($rs as $note) {
        $userid = (int)$note->userid;
        $user = $csvusermap[$userid] ?? null;
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            videotrack_report_user_label($userid, $csvusermap, false),
            (int)$cm->id
        );
        $row = array_merge($row, [
            videotrack_format_video_timestamp((float)$note->videotime, $videoduration),
            $note->notetext,
            userdate((int)$note->timecreated),
        ]);
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
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
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    if ($mode === 'cumulative') {
        $eventrs = $geteventrecordset();
        $clusters = $clusterize($eventrs, $window, $aggregation);
        $eventrs->close();
        if ($clusterlimitreached) {
            $warninglabel = get_string('report:csvcol_warning', 'mod_videotrack');
            \mod_videotrack\local\csv_export::write_row(
                $fh,
                [$warninglabel, get_string('report:clusterlimitreached_csv', 'mod_videotrack')],
                $csvdelimiter
            );
            if (!$hasvideotimefilter) {
                \mod_videotrack\local\csv_export::write_row(
                    $fh,
                    [$warninglabel, get_string('report:clusterlimitrequiresfilters_csv', 'mod_videotrack')],
                    $csvdelimiter
                );
                \mod_videotrack\local\csv_export::write_row(
                    $fh,
                    [$warninglabel, get_string('report:clusterexportblocked_csv', 'mod_videotrack')],
                    $csvdelimiter
                );
                fclose($fh);
                exit;
            }
            \mod_videotrack\local\csv_export::write_row($fh, [], $csvdelimiter);
        }
        \mod_videotrack\local\csv_export::write_row($fh, [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:clicks', 'mod_videotrack'),
            get_string('report:students', 'mod_videotrack'),
            get_string('report:csvcol_firsttimestamp', 'mod_videotrack'),
            get_string('report:csvcol_lasttimestamp', 'mod_videotrack'),
        ], $csvdelimiter);
        foreach ($clusters as $cluster) {
            \mod_videotrack\local\csv_export::write_row($fh, [
                videotrack_format_video_timestamp((float)$cluster['timestamp'], $videoduration),
                $cluster['reactionlabel'],
                $cluster['count'],
                $cluster['students'],
                videotrack_format_video_timestamp((float)$cluster['first'], $videoduration),
                videotrack_format_video_timestamp((float)$cluster['last'], $videoduration),
            ], $csvdelimiter);
        }
    } else {
        $csvheads = array_merge(
            \mod_videotrack\local\csv_export::identity_headers($csvfields),
            [
                get_string('report:csvcol_uniquecoveredtime', 'mod_videotrack'),
                get_string('report:completionpercent', 'mod_videotrack'),
                get_string('report:lastposition', 'mod_videotrack'),
                get_string('report:iscompleted', 'mod_videotrack'),
            ]
        );
        if ($hasgrade && $cangrade) {
            $csvheads[] = get_string('report:grade', 'mod_videotrack');
        }
        \mod_videotrack\local\csv_export::write_row($fh, $csvheads, $csvdelimiter);
        $rs = $DB->get_recordset('videotrack_state', $stateparams, 'completionpercent DESC');
        foreach ($rs as $state) {
            $userid = (int)$state->userid;
            $user = $csvusermap[$userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = \mod_videotrack\local\csv_export::identity_values(
                $csvfields,
                $course,
                $videotrack,
                $user,
                videotrack_report_user_label($userid, $csvusermap, false),
                (int)$cm->id
            );
            $row = array_merge($row, [
                videotrack_format_video_timestamp((float)$state->uniquecoveredseconds, $videoduration),
                format_float((float)$state->completionpercent, 2),
                videotrack_format_video_timestamp((float)$state->lastposition, $videoduration),
                get_string($state->iscompleted ? 'yes' : 'no', 'mod_videotrack'),
            ]);
            if ($hasgrade && $cangrade) {
                $row[] = $gradeinfo->items[0]->grades[$userid]->grade ?? '';
            }
            \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
        }
        $rs->close();
    }
    fclose($fh);
    exit;
}

// Recalculate aggregate completion state for all tracked users or one selected user.
if ($action === 'recalculate') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_once(__DIR__ . '/lib.php');
    if ($recalculateuserid > 0 && !in_array($recalculateuserid, $alluserids, true)) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $updated = videotrack_recalculate_all_states(
        $videotrack->id,
        cm_info::create($cm),
        $recalculateuserid
    );
    redirect(
        new moodle_url('/mod/videotrack/report.php', $baseparams),
        get_string('report:recalculated', 'mod_videotrack', $updated),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Reset one student's progress: delete that user's segments, state and reactions.
if ($resetaction === 'resetstudent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    $resetuserid = required_param('resetuserid', PARAM_INT);
    require_capability('mod/videotrack:viewreport', $context);
    if ($resetuserid <= 0 || !is_enrolled($context, $resetuserid, '', true)) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $resetcounts = [
        'segments' => $DB->count_records('videotrack_seg', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'states' => $DB->count_records('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'events' => $DB->count_records('videotrack_reactev', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
    ];
    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('videotrack_seg', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    $DB->delete_records('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    $DB->delete_records('videotrack_reactev', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]);
    $transaction->allow_commit();
    \mod_videotrack\event\student_progress_reset::create([
        'objectid' => $videotrack->id,
        'context' => $context,
        'relateduserid' => $resetuserid,
        'other' => $resetcounts,
    ])->trigger();
    // Also reset the gradebook grade when the activity uses grading.
    if (!empty($videotrack->grade)) {
        require_once($CFG->libdir . '/gradelib.php');
        grade_update(
            'mod/videotrack',
            $course->id,
            'mod',
            'videotrack',
            $videotrack->id,
            0,
            null,
            ['reset' => true, 'userid' => $resetuserid]
        );
    }
    // Update Moodle completion to INCOMPLETE for this student.
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
$PAGE->set_title(format_string($videotrack->name, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => context_course::instance($course->id)]));
// Plugin styles are in styles.css and are loaded automatically by Moodle.

// The savegrade block must run before $OUTPUT->header() to allow redirect responses.
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
        videotrack_set_user_grade($videotrack, $gradeuserid, (float)$val);
    } else {
        videotrack_set_user_grade($videotrack, $gradeuserid, -1);
    }
    redirect(
        $PAGE->url,
        get_string('report:gradesaved', 'mod_videotrack'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Initialise the AMD module that handles the student reset confirmation.
$PAGE->requires->js_call_amd('mod_videotrack/report', 'init', [[
    'confirmreset' => get_string('report:resetstudent_confirm', 'mod_videotrack'),
    'confirmrecalculate' => get_string('report:recalculate_confirm', 'mod_videotrack'),
    'labels' => [
        'confirm' => get_string('confirm', 'moodle'),
        'yes' => get_string('yes', 'moodle'),
        'cancel' => get_string('cancel', 'moodle'),
    ],
]]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reportteacher', 'mod_videotrack'));

$tabs = [
    new tabobject(
        'student',
        new moodle_url('/mod/videotrack/report.php', array_merge($baseparams, ['mode' => 'student'])),
        get_string('report:perstudent', 'mod_videotrack')
    ),
    new tabobject(
        'cumulative',
        new moodle_url('/mod/videotrack/report.php', array_merge($baseparams, ['mode' => 'cumulative'])),
        get_string('report:cumulative', 'mod_videotrack')
    ),
];
if ($canviewfullreport) {
    $tabs[] = new tabobject(
        'export',
        new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'export']),
        get_string('report:csvexport_tab', 'mod_videotrack')
    );
    $tabs[] = new tabobject(
        'recalculate',
        new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'recalculate']),
        get_string('report:recalculate_tab', 'mod_videotrack')
    );
}
echo $OUTPUT->tabtree($tabs, $mode);

if ($mode === 'recalculate') {
    echo $OUTPUT->heading(get_string('report:recalculate_heading', 'mod_videotrack'), 3);
    echo html_writer::div(
        get_string('report:recalculate_description', 'mod_videotrack'),
        'alert alert-info'
    );
    $recalculateoptions = $useroptions;
    $recalculateoptions[0] = get_string('report:recalculate_all', 'mod_videotrack');
    $recalculateform = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-recalculate-form',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'id', 'value' => $cm->id,
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'mode', 'value' => 'recalculate',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'action', 'value' => 'recalculate',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey(),
    ]);
    $recalculateform .= html_writer::start_div('form-group');
    $recalculateform .= html_writer::label(
        get_string('report:recalculate_users', 'mod_videotrack'),
        'id_recalculateuserid',
        false,
        ['class' => 'd-block']
    );
    $recalculateform .= html_writer::select(
        $recalculateoptions,
        'recalculateuserid',
        $recalculateuserid,
        false,
        ['id' => 'id_recalculateuserid', 'class' => 'custom-select']
    );
    $recalculateform .= html_writer::end_div();
    $recalculateform .= html_writer::tag(
        'button',
        get_string('report:recalculate_submit', 'mod_videotrack'),
        ['type' => 'submit', 'class' => 'btn btn-primary']
    );
    $recalculateform .= html_writer::end_tag('form');
    echo $recalculateform;
    echo $OUTPUT->footer();
    exit;
}

if ($mode === 'export') {
    echo $OUTPUT->heading(get_string('report:csvexport_heading', 'mod_videotrack'), 3);
    echo html_writer::div(
        get_string('report:csvexport_description', 'mod_videotrack'),
        'alert alert-info'
    );

    $exportform = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-custom-csv-export',
    ]);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => 'export']);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'export', 'value' => 'custom_csv']);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    $exportuseroptions = $useroptions;
    $exportuseroptions[0] = get_string('report:csvexport_scope_all', 'mod_videotrack');
    $exportform .= html_writer::start_div('form-group');
    $exportform .= html_writer::label(
        get_string('report:csvexport_users', 'mod_videotrack'),
        'id_csvuserid',
        false,
        ['class' => 'd-block']
    );
    $exportform .= html_writer::select(
        $exportuseroptions,
        'csvuserid',
        $csvuserid,
        false,
        ['id' => 'id_csvuserid', 'class' => 'custom-select']
    );
    $exportform .= html_writer::end_div();

    $exportform .= html_writer::start_tag('fieldset', ['class' => 'form-group']);
    $exportform .= html_writer::tag('legend', get_string('report:csvexport_content', 'mod_videotrack'), [
        'class' => 'col-form-label pt-0',
    ]);
    $exportform .= html_writer::start_div('form-check');
    $exportform .= html_writer::empty_tag('input', [
        'type' => 'checkbox', 'name' => 'csvincludereactions', 'value' => 1,
        'id' => 'id_csvincludereactions', 'class' => 'form-check-input',
        'checked' => 'checked',
    ]);
    $exportform .= html_writer::label(
        get_string('report:csvexport_reactions', 'mod_videotrack'),
        'id_csvincludereactions',
        false,
        ['class' => 'form-check-label']
    );
    $exportform .= html_writer::end_div();
    if (!empty($videotrack->studentnotesenabled)) {
        $exportform .= html_writer::start_div('form-check');
        $exportform .= html_writer::empty_tag('input', [
            'type' => 'checkbox', 'name' => 'csvincludenotes', 'value' => 1,
            'id' => 'id_csvincludenotes', 'class' => 'form-check-input',
        ]);
        $exportform .= html_writer::label(
            get_string('report:csvexport_notes', 'mod_videotrack'),
            'id_csvincludenotes',
            false,
            ['class' => 'form-check-label']
        );
        $exportform .= html_writer::end_div();
    }
    $exportform .= html_writer::end_tag('fieldset');

    $exportform .= html_writer::start_div('form-group');
    $exportform .= html_writer::label(
        get_string('report:csvexport_format', 'mod_videotrack'),
        'id_csvformat',
        false,
        ['class' => 'd-block']
    );
    $exportform .= html_writer::select([
        'detailed' => get_string('report:csvexport_detailed', 'mod_videotrack'),
        'summary' => get_string('report:csvexport_summary', 'mod_videotrack'),
        'overall' => get_string('report:csvexport_overall', 'mod_videotrack'),
    ], 'csvformat', $csvformat, false, ['id' => 'id_csvformat', 'class' => 'custom-select']);
    $exportform .= html_writer::div(
        get_string('report:csvexport_format_help', 'mod_videotrack', $window),
        'form-text text-muted'
    );
    $exportform .= html_writer::end_div();

    $exportform .= html_writer::div(
        get_string('report:csvexport_privacywarning', 'mod_videotrack'),
        'alert alert-warning',
        ['id' => 'videotrack-csv-export-warning']
    );
    $exportform .= html_writer::tag('button', get_string('report:csvexport_download', 'mod_videotrack'), [
        'type' => 'submit',
        'class' => 'btn btn-primary',
    ]);
    $exportform .= html_writer::end_tag('form');
    echo $exportform;
    echo $OUTPUT->footer();
    exit;
}

$filterurl = new moodle_url('/mod/videotrack/report.php');
echo html_writer::start_div('videotrack-report-filters mb-3');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $filterurl->out(false), 'class' => 'form-inline']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => $mode]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sort', 'value' => $sort]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'aggregation', 'value' => $aggregation]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'window', 'value' => $window]);
$userfilter = html_writer::label(
    get_string('report:userid', 'mod_videotrack'),
    'id_userid',
    false,
    ['class' => 'mr-1']
) . html_writer::select(
    $useroptions,
    'userid',
    $useridfilter,
    false,
    ['id' => 'id_userid', 'class' => 'custom-select']
);
$reactionfilter = html_writer::label(
    get_string('report:reaction', 'mod_videotrack'),
    'id_reactionid',
    false,
    ['class' => 'mr-1']
) . html_writer::select(
    $reactionoptions,
    'reactionid',
    $reactionidfilter,
    false,
    ['id' => 'id_reactionid', 'class' => 'custom-select']
);
echo html_writer::div($userfilter, 'd-inline-flex align-items-center mr-3 mb-2');
echo html_writer::div($reactionfilter, 'd-inline-flex align-items-center mr-3 mb-2');
$reportduration = (float)$videotrack->durationseconds;
$showtimehours = $reportduration <= 0 || max(
    $reportduration,
    $timefrom ?? 0,
    $timeto ?? 0
) >= HOURSECS;
echo videotrack_report_duration_filter(
    'timefrom',
    get_string('report:timefrom', 'mod_videotrack'),
    $timefrom,
    $showtimehours
);
echo videotrack_report_duration_filter(
    'timeto',
    get_string('report:timeto', 'mod_videotrack'),
    $timeto,
    $showtimehours
);
echo html_writer::div(
    html_writer::label(
        get_string('report:notecreatedfrom', 'mod_videotrack'),
        'id_notecreatedfrom',
        false,
        ['class' => 'mr-1']
    ) . html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedfrom',
        'id' => 'id_notecreatedfrom', 'value' => s($notecreatedfrom),
        'class' => 'form-control', 'style' => 'width:10rem',
    ]),
    'd-inline-flex align-items-center mr-3 mb-2'
);
echo html_writer::div(
    html_writer::label(
        get_string('report:notecreatedto', 'mod_videotrack'),
        'id_notecreatedto',
        false,
        ['class' => 'mr-1']
    ) . html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedto',
        'id' => 'id_notecreatedto', 'value' => s($notecreatedto),
        'class' => 'form-control', 'style' => 'width:10rem',
    ]),
    'd-inline-flex align-items-center mr-3 mb-2'
);
echo html_writer::empty_tag('input', [
    'type' => 'submit', 'class' => 'btn btn-secondary mr-2 mb-2', 'value' => get_string('filter'),
]);
$resetfilterurl = new moodle_url('/mod/videotrack/report.php', [
    'id' => $cm->id,
    'mode' => $mode,
    'sort' => $sort,
    'aggregation' => $aggregation,
    'window' => $window,
]);
echo html_writer::link(
    $resetfilterurl,
    get_string('report:resetfilters', 'mod_videotrack'),
    ['class' => 'btn btn-outline-secondary mb-2']
);
echo html_writer::end_tag('form');
echo html_writer::end_div();

if ($mode === 'student') {
    if (!$statecount) {
        echo $OUTPUT->notification(get_string('report:noattempts', 'mod_videotrack'), 'notifymessage');
    } else {
        // The $hasgrade and $gradeinfo variables were already loaded at the start of the file.
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
        $table->caption = get_string('report:perstudent', 'mod_videotrack');
        $table->head = $heads;

        $staters = $getstaterecordset();
        foreach ($staters as $state) {
            $user = $usermap[(int)$state->userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = [
                videotrack_report_user_label((int)$state->userid, $usermap, $canviewemail),
                videotrack_format_seconds((float)$state->uniquecoveredseconds),
                format_float((float)$state->completionpercent, 2),
                videotrack_format_seconds((float)$state->lastposition),
                $state->iscompleted ? get_string('yes', 'mod_videotrack') : get_string('no', 'mod_videotrack'),
            ];

            if ($hasgrade && $cangrade) {
                // Read the current grade for this user.
                $studentname = videotrack_report_user_label((int)$state->userid, $usermap, false);
                $currentgrade = $gradeinfo->items[0]->grades[(int)$state->userid]->grade ?? '';
                $gradecell = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => $PAGE->url->out(false),
                    'class'  => 'videotrack-grade-form d-inline-flex align-items-center gap-1',
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'savegrade',
                    'value' => 1,
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'grade_userid',
                    'value' => (int)$state->userid,
                ]);

                if ($videotrack->grade > 0) {
                    // Numeric grading: number input constrained by the activity maximum.
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
                    $gradecell .= html_writer::tag(
                        'small',
                        '/ ' . (int)$videotrack->grade,
                        ['class' => 'text-muted ms-1']
                    );
                } else {
                    // Scale grading: select menu with Moodle one-based scale values.
                    $scaleid = -(int)$videotrack->grade;
                    $scale   = grade_scale::fetch(['id' => $scaleid]);
                    $items   = $scale ? $scale->load_items() : [];
                    $options = ['' => '-'];
                    foreach ($items as $k => $label) {
                        $options[$k + 1] = $label; // Moodle scale value: one-based.
                    }
                    $gradecell .= html_writer::select(
                        $options,
                        'grade_value',
                        ($currentgrade !== '' ? (int)$currentgrade : ''),
                        false,
                        [
                            'class' => 'form-control form-control-sm custom-select',
                            'aria-label' => get_string('report:gradeinputfor', 'mod_videotrack', $studentname),
                        ]
                    );
                }

                $gradecell .= html_writer::tag(
                    'button',
                    get_string('save'),
                    [
                        'type' => 'submit',
                        'class' => 'btn btn-sm btn-primary ms-1',
                        'aria-label' => get_string('report:savegradefor', 'mod_videotrack', $studentname),
                    ]
                );
                $gradecell .= html_writer::end_tag('form');

                // Visual pass-grade indicator when configured.
                if (!empty($videotrack->gradepass) && $currentgrade !== '') {
                    $passed = (float)$currentgrade >= (float)$videotrack->gradepass;
                    $passlabel = get_string($passed ? 'report:gradepassed' : 'report:gradefailed', 'mod_videotrack');
                    $gradecell .= html_writer::tag(
                        'span',
                        html_writer::span($passed ? '✓' : '✗', '', ['aria-hidden' => 'true']) .
                            html_writer::span($passlabel, 'sr-only'),
                        [
                            'class' => 'ms-1 ' . ($passed ? 'text-success' : 'text-danger'),
                            'title' => get_string(
                                'report:gradepass_hint',
                                'mod_videotrack',
                                format_float((float)$videotrack->gradepass, 2)
                            ),
                        ]
                    );
                }

                $row[] = $gradecell;
            }

            // Reset one student's progress (only for users with the manage capability).
            if (has_capability('mod/videotrack:managereactions', $context)) {
                $resetform = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => (new moodle_url('/mod/videotrack/report.php', $baseparams))->out(false),
                    'class' => 'd-inline videotrack-reset-student-form',
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'resetaction',
                    'value' => 'resetstudent',
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'resetuserid',
                    'value' => (int)$state->userid,
                ]);
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

        // Heatmap SVG: show reaction distribution on the video timeline.
        $duration = (float)($DB->get_field('videotrack', 'durationseconds', ['id' => $videotrack->id]) ?: 0);
        if ($duration > 0 && $clusters) {
            $svgw = 800;
            $svgh = 48;
            $barh = 32;
            $pady = 8;
            $maxcount = max(array_column($clusters, 'count'));
            // Collect per-reaction colours using an accessible rotating palette.
            $palette = [
                '#4e79a7',
                '#f28e2b',
                '#e15759',
                '#76b7b2',
                '#59a14f',
                '#edc948',
                '#b07aa1',
                '#ff9da7',
            ];
            $reactioncolors = [];
            $ci = 0;
            foreach ($reactions as $r) {
                $reactioncolors[(int)$r->id] = $palette[$ci % count($palette)];
                $ci++;
            }
            $svgtitle = s(get_string('report:heatmap_desc', 'mod_videotrack'));
            $svgattributes = [
                'viewBox' => "0 0 {$svgw} {$svgh}",
                'xmlns' => 'http://www.w3.org/2000/svg',
                'role' => 'img',
                'aria-label' => $svgtitle,
                'aria-describedby' => 'videotrack-heatmap-table',
                'class' => 'videotrack-heatmap-svg',
            ];
            $svg = html_writer::start_tag('svg', $svgattributes);
            $svg .= html_writer::tag('title', $svgtitle);
            $patternpaths = [
                html_writer::empty_tag('path', [
                    'd' => 'M0 6 L6 0',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M0 0 L6 6',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M3 0 L3 6',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M0 3 L6 3',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
            ];
            $svg .= html_writer::start_tag('defs');
            $patternmap = [];
            $pi = 0;
            foreach ($reactions as $r) {
                $reactionid = (int)$r->id;
                $patternid = 'videotrack-hatch-' . $reactionid;
                $patternmap[$reactionid] = $patternid;
                $svg .= html_writer::tag('pattern', $patternpaths[$pi % count($patternpaths)], [
                    'id' => $patternid,
                    'width' => '6',
                    'height' => '6',
                    'patternUnits' => 'userSpaceOnUse',
                ]);
                $pi++;
            }
            $svg .= html_writer::end_tag('defs');
            // Timeline background bar.
            $svg .= html_writer::empty_tag('rect', [
                'x' => '0',
                'y' => $pady,
                'width' => $svgw,
                'height' => $barh,
                'rx' => '3',
                'fill' => '#e9ecef',
            ]);
            $labelled = 0;
            $labelthreshold = max(1, (int)ceil($maxcount * 0.75));
            foreach ($clusters as $cluster) {
                $x = (int)min($svgw, max(0, (($cluster['timestamp'] / $duration) * $svgw)));
                $h = max(2, (int)(($cluster['count'] / $maxcount) * $barh));
                $y = $pady + $barh - $h;
                $col = $reactioncolors[(int)$cluster['reactionid']] ?? '#4e79a7';
                $tip = s($cluster['reactionlabel']) . ': ' . $cluster['count'] . ' @ ' .
                    videotrack_format_seconds($cluster['timestamp']);
                $rectx = max(0, min($svgw - 6, $x - 3));
                $svg .= html_writer::tag('rect', html_writer::tag('title', $tip), [
                    'x' => $rectx,
                    'y' => $y,
                    'width' => '6',
                    'height' => $h,
                    'rx' => '2',
                    'fill' => $col,
                    'opacity' => '0.85',
                ]);
                $patternid = $patternmap[(int)$cluster['reactionid']] ?? '';
                if ($patternid !== '') {
                    $svg .= html_writer::empty_tag('rect', [
                        'x' => $rectx,
                        'y' => $y,
                        'width' => '6',
                        'height' => $h,
                        'fill' => "url(#{$patternid})",
                        'opacity' => '0.35',
                    ]);
                }
                if ($cluster['count'] >= $labelthreshold && $labelled < 8) {
                    $textx = min($svgw - 24, max(2, $x + 4));
                    $texty = max(8, $y - 2);
                    $svg .= html_writer::tag('text', (string)(int)$cluster['count'], [
                        'x' => $textx,
                        'y' => $texty,
                        'font-size' => '10',
                        'fill' => '#212529',
                    ]);
                    $labelled++;
                }
            }
            $svg .= html_writer::end_tag('svg');
            echo html_writer::tag('p', get_string('report:heatmap_supplementary', 'mod_videotrack'), [
                'class' => 'small mb-1',
            ]);
            echo html_writer::link('#videotrack-heatmap-table', get_string('report:skiptoheatmaptable', 'mod_videotrack'), [
                'class' => 'sr-only sr-only-focusable d-block mb-2',
            ]);
            echo html_writer::tag('p', get_string('report:heatmap_desc', 'mod_videotrack') . ' ' .
                get_string('report:heatmap_textsummary', 'mod_videotrack', [
                    'clusters' => count($clusters),
                    'max' => $maxcount,
                ]), ['class' => 'text-muted small mb-1']);
            $legenditems = [];
            $legendindex = 0;
            foreach ($reactions as $r) {
                $color = $reactioncolors[(int)$r->id] ?? '#4e79a7';
                $patternstyle = $patternstyles[$legendindex % count($patternstyles)];
                $swatchstyle = implode(';', [
                    'background-color:' . $color,
                    'background-image:' . $patternstyle,
                ]);
                $swatch = html_writer::span('', 'videotrack-heatmap-swatch', [
                    'aria-hidden' => 'true',
                    'style' => $swatchstyle,
                ]);
                $legenditems[] = html_writer::tag('li', $swatch . s($r->label), ['class' => 'list-inline-item mr-3']);
                $legendindex++;
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
        $table->caption = get_string('report:heatmap_textsummary', 'mod_videotrack', [
            'clusters' => count($clusters),
            'max' => $clusters ? max(array_column($clusters, 'count')) : 0,
        ]);
        $table->head = [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:clicks', 'mod_videotrack'),
            get_string('report:students', 'mod_videotrack'),
            get_string('report:replay', 'mod_videotrack'),
        ];
        $replayoffset = $window;
        $replaynotice = get_string('report:replayoffsetnotice', 'mod_videotrack', $replayoffset);
        echo $OUTPUT->notification($replaynotice, 'info', false);
        foreach ($clusters as $cluster) {
            $reactionhtml = $cluster['reaction']
                ? videotrack_render_reaction_icon($cluster['reaction'], $context, true)
                : s($cluster['reactionlabel']);
            $replaytimestamp = max(0, (int)round($cluster['timestamp']));
            $start = max(0, $replaytimestamp - $replayoffset);
            $end = $replaytimestamp + $replayoffset;
            $replayurl = new moodle_url('/mod/videotrack/view.php', [
                'id' => $cm->id,
                'replaystart' => $start,
                'replayend' => $end,
            ]);
            $table->data[] = [
                videotrack_format_seconds($replaytimestamp),
                html_writer::span($reactionhtml, 'videotrack-report-icon'),
                (int)$cluster['count'],
                (int)$cluster['students'],
                html_writer::span(
                    html_writer::link($replayurl, get_string('report:replay', 'mod_videotrack')),
                    'videotrack-replay-inline'
                ),
            ];
        }
        echo html_writer::table($table);
    }
}

// Student notes section: per-student mode only, and only when notes are enabled.
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
    $notelimit = videotrack_get_config_int('reportnotespagesize', 100, 20, 500);
    $notecount = $DB->count_records_select('videotrack_reactev', $notewhere, $noteparams);
    if ($notecount > 0) {
        $notepage = min($notepage, (int)floor(($notecount - 1) / $notelimit));
    }
    $notes = $DB->get_records_select(
        'videotrack_reactev',
        $notewhere,
        $noteparams,
        'userid ASC, videotime ASC, id ASC',
        'id, userid, videotime, notetext, timecreated',
        $notepage * $notelimit,
        $notelimit
    );

    echo html_writer::start_div('videotrack-notes-report mt-4');
    echo $OUTPUT->heading(get_string('report:notes_title', 'mod_videotrack'), 3);

    $ntable = new html_table();
    $ntable->head = [
        get_string('report:userid', 'mod_videotrack'),
        get_string('report:timestamp', 'mod_videotrack'),
        get_string('studentnote_label', 'mod_videotrack'),
        get_string('report:notedate', 'mod_videotrack'),
    ];
    $ntable->attributes['class'] = 'generaltable';
    $ntable->caption = get_string('report:notes_title', 'mod_videotrack');
    foreach ($notes as $note) {
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

        echo html_writer::link(
            new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'export']),
            get_string('report:csvexport_tab', 'mod_videotrack'),
            ['class' => 'btn btn-sm btn-outline-secondary mt-2']
        );
    }
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
