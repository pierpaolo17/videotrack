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
 * Personal cross-course dashboard for VideoTrack report viewers.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

use mod_videotrack\local\teacher_analytics;

global $OUTPUT, $PAGE, $USER;

require_login();
$courseid = optional_param('course', 0, PARAM_INT);
$activityid = optional_param('activity', 0, PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);
$perioddays = optional_param('period', 0, PARAM_INT);
$allowedperiods = [0, 7, 30, 90];
if (!in_array($perioddays, $allowedperiods, true)) {
    $perioddays = 0;
}

$courses = teacher_analytics::accessible_courses((int)$USER->id);
if (!$courses) {
    throw new required_capability_exception(
        context_system::instance(),
        'mod/videotrack:viewcoursereport',
        'nopermissions',
        ''
    );
}
if ($courseid > 0 && !isset($courses[$courseid])) {
    throw new moodle_exception('invalidcourse');
}

$selectedcourse = $courseid > 0 ? $courses[$courseid] : null;
$activityoptions = $selectedcourse ? teacher_analytics::activity_options($selectedcourse, (int)$USER->id) : [];
$groupoptions = $selectedcourse ? teacher_analytics::group_options($selectedcourse, (int)$USER->id) : [];
if ($activityid > 0 && !isset($activityoptions[$activityid])) {
    $activityid = 0;
}
if ($groupid > 0 && !isset($groupoptions[$groupid])) {
    $groupid = 0;
}

$params = array_filter([
    'course' => $courseid,
    'activity' => $activityid,
    'group' => $groupid,
    'period' => $perioddays,
]);
$PAGE->set_url(new moodle_url('/mod/videotrack/reports_teacher.php', $params));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('teacherdashboard:title', 'mod_videotrack'));
$PAGE->set_heading(get_string('teacherdashboard:title', 'mod_videotrack'));

$minusers = videotrack_get_config_int('analyticsminusers', 5, 2, 50);
$dashboard = teacher_analytics::dashboard_rows(
    (int)$USER->id,
    $minusers,
    $courseid,
    $activityid,
    $groupid,
    $perioddays
);

$courseoptions = [0 => get_string('teacherdashboard:allcourses', 'mod_videotrack')];
foreach ($courses as $course) {
    $coursecontext = context_course::instance((int)$course->id);
    $courseoptions[(int)$course->id] = format_string($course->fullname, true, ['context' => $coursecontext]);
}
$activityoptions = [0 => get_string('teacherdashboard:allactivities', 'mod_videotrack')] + $activityoptions;
$groupoptions = [0 => get_string('teacherdashboard:allgroups', 'mod_videotrack')] + $groupoptions;
$periodoptions = [
    0 => get_string('teacherdashboard:alltime', 'mod_videotrack'),
    7 => get_string('teacherdashboard:lastdays', 'mod_videotrack', 7),
    30 => get_string('teacherdashboard:lastdays', 'mod_videotrack', 30),
    90 => get_string('teacherdashboard:lastdays', 'mod_videotrack', 90),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('teacherdashboard:title', 'mod_videotrack'));
echo html_writer::tag('p', get_string('teacherdashboard:intro', 'mod_videotrack'), ['class' => 'text-muted']);
echo $OUTPUT->notification(
    get_string('coursereport:privacy_notice', 'mod_videotrack', $minusers),
    'info'
);

$filterurl = new moodle_url('/mod/videotrack/reports_teacher.php');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $filterurl->out_omit_querystring(), 'class' => 'mb-4']);
echo html_writer::start_div('row g-3 align-items-end');
$filters = [
    ['course', 'teacherdashboard:filter_course', $courseoptions, $courseid],
    ['activity', 'teacherdashboard:filter_activity', $activityoptions, $activityid],
    ['group', 'teacherdashboard:filter_group', $groupoptions, $groupid],
    ['period', 'teacherdashboard:filter_period', $periodoptions, $perioddays],
];
foreach ($filters as [$name, $labelkey, $options, $selected]) {
    echo html_writer::start_div('col-md-3');
    echo html_writer::label(get_string($labelkey, 'mod_videotrack'), $name, false, ['class' => 'form-label']);
    echo html_writer::select($options, $name, $selected, false, ['id' => $name, 'class' => 'form-select']);
    echo html_writer::end_div();
}
echo html_writer::start_div('col-12');
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'class' => 'btn btn-primary',
    'value' => get_string('filter'),
]);
echo ' ' . html_writer::link(
    new moodle_url('/mod/videotrack/reports_teacher.php'),
    get_string('reset'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_div();
echo html_writer::end_div();
echo html_writer::end_tag('form');

if (!$dashboard) {
    echo $OUTPUT->notification(get_string('teacherdashboard:nodata', 'mod_videotrack'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

foreach ($dashboard as $courseblock) {
    $course = $courseblock['course'];
    $context = context_course::instance((int)$course->id);
    $coursename = format_string($course->fullname, true, ['context' => $context]);
    echo $OUTPUT->heading($coursename, 3);
    echo html_writer::link(
        new moodle_url('/mod/videotrack/reports_course.php', ['course' => $course->id]),
        get_string('teacherdashboard:opencourse', 'mod_videotrack'),
        ['class' => 'btn btn-sm btn-outline-secondary mb-2']
    );

    $table = new html_table();
    $table->caption = get_string('teacherdashboard:coursecaption', 'mod_videotrack', $coursename);
    $table->attributes['class'] = 'generaltable w-100';
    $table->head = [
        get_string('coursereport:col_activity', 'mod_videotrack'),
        get_string('coursereport:col_students_started', 'mod_videotrack'),
        get_string('coursereport:col_avg_percent', 'mod_videotrack'),
        get_string('coursereport:col_median_percent', 'mod_videotrack'),
        get_string('coursereport:col_completions', 'mod_videotrack'),
        get_string('coursereport:col_not_completed', 'mod_videotrack'),
        get_string('coursereport:col_reactions', 'mod_videotrack'),
        get_string('coursereport:col_notes', 'mod_videotrack'),
        get_string('coursereport:col_actions', 'mod_videotrack'),
    ];
    foreach ($courseblock['rows'] as $row) {
        $suppressed = !empty($row->summary['datasetsuppressed']);
        $privacy = get_string('coursereport:privacy_suppressed', 'mod_videotrack', $minusers);
        $countcell = static function (array $summary) use ($privacy): string {
            if (empty($summary['hasdata'])) {
                return '0';
            }
            return !empty($summary['suppressed']) ? $privacy : (string)(int)$summary['eventcount'];
        };
        $percentagecell = static function (?float $value) use ($suppressed, $privacy): string {
            if ($suppressed) {
                return $privacy;
            }
            return $value === null ? '-' : format_float($value, 1) . '%';
        };
        $activityname = format_string($row->name, true, ['context' => $context]);
        $activity = !empty($row->canviewactivity)
            ? html_writer::link(new moodle_url('/mod/videotrack/view.php', ['id' => $row->cmid]), $activityname)
            : $activityname;
        $report = !empty($row->canviewreport)
            ? html_writer::link(
                new moodle_url('/mod/videotrack/report.php', ['id' => $row->cmid]),
                get_string('reportteacher', 'mod_videotrack')
            )
            : '-';
        $table->data[] = [
            $activity,
            $countcell($row->summary['started']),
            $percentagecell($row->summary['averagepercent']),
            $percentagecell($row->summary['medianpercent']),
            $countcell($row->summary['completions']),
            $countcell($row->summary['noncompleted']),
            $countcell($row->reactions),
            $countcell($row->notes),
            $report,
        ];
    }
    echo html_writer::table($table);
}

echo $OUTPUT->footer();
