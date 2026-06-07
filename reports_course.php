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
 * Course-level report for mod_videotrack.
 *
 * Shows an aggregated overview of all VideoTrack activities in the course:
 * for each activity, number of students who have started, average coverage
 * percentage, and number of completions. Accessible from the course reports
 * navigation node added by videotrack_extend_navigation_course().
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

global $DB, $PAGE, $OUTPUT;

$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($courseid);

// This aggregated report is course-wide, so access is checked with a dedicated CONTEXT_COURSE capability.
// Per-activity reports continue to use CONTEXT_MODULE in report.php.
require_capability('mod/videotrack:viewcoursereport', $context);

$PAGE->set_url(new moodle_url('/mod/videotrack/reports_course.php', ['course' => $courseid]));
$PAGE->set_context($context);
$courseshortname = format_string($course->shortname, true, ['context' => $context]);
$coursefullname = format_string($course->fullname, true, ['context' => $context]);
$PAGE->set_title($courseshortname . ': ' . get_string('coursereport:title', 'mod_videotrack'));
$PAGE->set_heading($coursefullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursereport:title', 'mod_videotrack'));
echo html_writer::tag(
    'p',
    get_string('coursereport:intro', 'mod_videotrack'),
    ['class' => 'text-muted']
);

// Fetch all videotrack instances in this course.
// Compatible with MySQL ONLY_FULL_GROUP_BY: group only by primary keys (vt.id, cm.id).
// Non-aggregated columns are read from the instance recordset loaded below.
$sql = "
    SELECT vt.id,
           cm.id                            AS cmid,
           COALESCE(vs.students_started, 0) AS students_started,
           COALESCE(vs.avg_percent, 0)      AS avg_percent,
           COALESCE(vs.completions, 0)      AS completions,
           COALESCE(vr.total_reactions, 0)  AS total_reactions
      FROM {videotrack} vt
      JOIN {course_modules} cm ON cm.instance = vt.id
      JOIN {modules} m ON m.id = cm.module AND m.name = 'videotrack'
                        AND cm.deletioninprogress = 0
 LEFT JOIN (
           SELECT vs2.videotrackid,
                  COUNT(DISTINCT vs2.userid) AS students_started,
                  ROUND(AVG(vs2.completionpercent), 1) AS avg_percent,
                  SUM(CASE WHEN vs2.iscompleted <> 0 THEN 1 ELSE 0 END) AS completions
             FROM {videotrack_state} vs2
             JOIN {videotrack} vt2 ON vt2.id = vs2.videotrackid AND vt2.course = :courseid3
         GROUP BY vs2.videotrackid
           ) vs ON vs.videotrackid = vt.id
 LEFT JOIN (
           SELECT vr2.videotrackid,
                  COUNT(vr2.id) AS total_reactions
             FROM {videotrack_reactev} vr2
             JOIN {videotrack} vt3 ON vt3.id = vr2.videotrackid AND vt3.course = :courseid4
            WHERE vr2.isdeleted = 0
              AND (vr2.notetype IS NULL OR vr2.notetype = '')
         GROUP BY vr2.videotrackid
           ) vr ON vr.videotrackid = vt.id
     WHERE vt.course = :courseid AND cm.course = :courseid2
";
$aggrows = $DB->get_records_sql($sql, [
    'courseid' => $courseid,
    'courseid2' => $courseid,
    'courseid3' => $courseid,
    'courseid4' => $courseid,
]);

$instances = [];
$modinfo = get_fast_modinfo($course);
if (empty($aggrows)) {
    echo $OUTPUT->notification(get_string('coursereport:nodata', 'mod_videotrack'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

// Load complete instance records to get name, videosource, and durationseconds.
$vtrecords = $DB->get_records(
    'videotrack',
    ['course' => $courseid],
    'name ASC',
    'id,name,videosource,durationseconds'
);

foreach ($vtrecords as $vt) {
    if (!isset($aggrows[$vt->id])) {
        continue;
    }

    $row = $aggrows[$vt->id];
    if (empty($modinfo->cms[$row->cmid]) || !$modinfo->cms[$row->cmid]->uservisible) {
        continue;
    }

    $row->name = $vt->name;
    $row->videosource = $vt->videosource;
    $row->durationseconds = $vt->durationseconds;
    $instances[$vt->id] = $row;
}

if (empty($instances)) {
    echo $OUTPUT->notification(get_string('coursereport:nodata', 'mod_videotrack'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->caption = get_string('coursereport:title', 'mod_videotrack');
$table->attributes['class'] = 'generaltable w-100';
$table->head = [
    get_string('coursereport:col_activity', 'mod_videotrack'),
    get_string('coursereport:col_source', 'mod_videotrack'),
    get_string('coursereport:col_duration', 'mod_videotrack'),
    get_string('coursereport:col_students_started', 'mod_videotrack'),
    get_string('coursereport:col_avg_percent', 'mod_videotrack'),
    get_string('coursereport:col_completions', 'mod_videotrack'),
    get_string('coursereport:col_reactions', 'mod_videotrack'),
    get_string('coursereport:col_actions', 'mod_videotrack'),
];

foreach ($instances as $inst) {
    $link = html_writer::link(
        new moodle_url('/mod/videotrack/view.php', ['id' => $inst->cmid]),
        format_string($inst->name, true, ['context' => $context])
    );
    $report = html_writer::link(
        new moodle_url('/mod/videotrack/report.php', ['id' => $inst->cmid]),
        get_string('reportteacher', 'mod_videotrack'),
        ['class' => 'btn btn-sm btn-outline-secondary']
    );

    $src = in_array($inst->videosource, ['youtube', 'vimeo', 'upload'], true) ? $inst->videosource : 'youtube';
    $sourcelabel = get_string('source:' . $src, 'mod_videotrack');
    $duration = $inst->durationseconds > 0
        ? videotrack_format_seconds((float)$inst->durationseconds)
        : '—';

    // Mini bar showing average coverage.
    $pct = (float)($inst->avg_percent ?? 0);
    $barw = max(0, min(100, $pct));
    $avglabel = get_string('coursereport:avgcoverage', 'mod_videotrack', format_float($pct, 1));
    $barsvg = '<svg width="80" height="14" role="img" '
        . 'focusable="false" style="vertical-align:middle;margin-left:4px">'
        . '<title>' . s($avglabel) . '</title>'
        . '<rect class="videotrack-course-avgbar-bg" x="0" y="3" width="80" height="8" rx="2"/>'
        . '<rect class="videotrack-course-avgbar-fill" x="0" y="3" width="' . round($barw * 0.8) . '" '
        . 'height="8" rx="2"/>'
        . '</svg>';
    $avgcell = html_writer::span($avglabel, 'videotrack-course-avglabel') . ' ' . $barsvg;

    $table->data[] = [
        $link,
        s($sourcelabel),
        s($duration),
        (int)($inst->students_started ?? 0),
        $avgcell,
        (int)($inst->completions ?? 0),
        (int)($inst->total_reactions ?? 0),
        $report,
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
