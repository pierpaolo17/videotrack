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
 * Course-level dashboard for authorised mod_videotrack report viewers.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Renders an exact aggregate count for an authorised course report viewer.
 *
 * @param array $summary Summary returned by analytics::count_summary().
 * @return string Rendered table-cell content.
 */
function videotrack_course_report_count_cell(array $summary): string {
    if (empty($summary['hasdata'])) {
        return '0';
    }
    return (string)(int)$summary['eventcount'];
}

/**
 * Renders an exact aggregate percentage for an authorised course report viewer.
 *
 * @param float|null $value Percentage value.
 * @param string $labelstring Language key used for the accessible label.
 * @param bool $showbar Whether to include the compact coverage bar.
 * @return string Rendered table-cell content.
 */
function videotrack_course_report_percentage_cell(
    ?float $value,
    string $labelstring,
    bool $showbar = false
): string {
    if ($value === null) {
        return html_writer::span(get_string('coursereport:notavailable', 'mod_videotrack'), 'text-muted');
    }

    $label = get_string($labelstring, 'mod_videotrack', format_float($value, 1));
    if (!$showbar) {
        return html_writer::span($label);
    }

    $barwidth = max(0, min(80, round($value * 0.8)));
    $svg = '<svg width="80" height="14" role="img" focusable="false" '
        . 'style="vertical-align:middle;margin-left:4px">'
        . '<title>' . s($label) . '</title>'
        . '<rect class="videotrack-course-avgbar-bg" x="0" y="3" width="80" height="8" rx="2"/>'
        . '<rect class="videotrack-course-avgbar-fill" x="0" y="3" width="' . $barwidth . '" '
        . 'height="8" rx="2"/>'
        . '</svg>';
    return html_writer::span($label, 'videotrack-course-avglabel') . ' ' . $svg;
}

/**
 * Renders the largest adjacent retention decrease.
 *
 * @param array|null $drop Exact drop details.
 * @return string Rendered table-cell content.
 */
function videotrack_course_report_drop_cell(?array $drop): string {
    if ($drop === null) {
        return html_writer::span(get_string('coursereport:notavailable', 'mod_videotrack'), 'text-muted');
    }
    return get_string('coursereport:main_drop', 'mod_videotrack', [
        'time' => videotrack_format_seconds((float)$drop['timestamp']),
        'drop' => format_float((float)$drop['percentagepoints'], 1),
    ]);
}

global $DB, $OUTPUT, $PAGE, $USER;

$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$context = context_course::instance($courseid);
require_capability('mod/videotrack:viewcoursereport', $context);

$PAGE->set_url(new moodle_url('/mod/videotrack/reports_course.php', ['course' => $courseid]));
$PAGE->set_context($context);
$courseshortname = format_string($course->shortname, true, ['context' => $context]);
$coursefullname = format_string($course->fullname, true, ['context' => $context]);
$PAGE->set_title($courseshortname . ': ' . get_string('coursereport:title', 'mod_videotrack'));
$PAGE->set_heading($coursefullname);

$exactminusers = \mod_videotrack\local\analytics::EXACT_REPORT_MIN_USERS;
$instances = \mod_videotrack\local\course_analytics::get_course_rows(
    $course,
    (int)$USER->id,
    $exactminusers
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursereport:title', 'mod_videotrack'));
echo html_writer::tag('p', get_string('coursereport:intro', 'mod_videotrack'), ['class' => 'text-muted']);
if (!$instances) {
    echo $OUTPUT->notification(get_string('coursereport:nodata', 'mod_videotrack'), 'notifymessage');
    echo $OUTPUT->footer();
    exit;
}

$showbookmarks = count(array_filter(
    $instances,
    static fn(stdClass $instance): bool => !empty($instance->bookmarksenabled)
)) > 0;

$table = new html_table();
$table->caption = get_string('coursereport:title', 'mod_videotrack');
$table->attributes['class'] = 'generaltable w-100';
$table->head = [
    get_string('coursereport:col_activity', 'mod_videotrack'),
    get_string('coursereport:col_source', 'mod_videotrack'),
    get_string('coursereport:col_duration', 'mod_videotrack'),
    get_string('coursereport:col_students_started', 'mod_videotrack'),
    get_string('coursereport:col_avg_percent', 'mod_videotrack'),
    get_string('coursereport:col_median_percent', 'mod_videotrack'),
    get_string('coursereport:col_completions', 'mod_videotrack'),
    get_string('coursereport:col_not_completed', 'mod_videotrack'),
    get_string('coursereport:col_main_drop', 'mod_videotrack'),
    get_string('coursereport:col_reactions', 'mod_videotrack'),
    get_string('coursereport:col_notes', 'mod_videotrack'),
];
if ($showbookmarks) {
    $table->head[] = get_string('coursereport:col_bookmarks', 'mod_videotrack');
}
$table->head[] = get_string('coursereport:col_actions', 'mod_videotrack');

foreach ($instances as $instance) {
    $formattedname = format_string($instance->name, true, ['context' => $context]);
    $activity = !empty($instance->canviewactivity)
        ? html_writer::link(new moodle_url('/mod/videotrack/view.php', ['id' => $instance->cmid]), $formattedname)
        : $formattedname;
    $report = !empty($instance->canviewreport)
        ? html_writer::link(
            new moodle_url('/mod/videotrack/report.php', ['id' => $instance->cmid]),
            get_string('reportteacher', 'mod_videotrack'),
            ['class' => 'btn btn-sm btn-outline-secondary']
        )
        : html_writer::span(get_string('coursereport:report_unavailable', 'mod_videotrack'), 'text-muted');

    $source = in_array($instance->videosource, ['youtube', 'vimeo', 'upload'], true)
        ? $instance->videosource
        : 'youtube';
    $sourcelabel = get_string('source:' . $source, 'mod_videotrack');
    $duration = (float)$instance->summary['duration'] > 0
        ? videotrack_format_seconds((float)$instance->summary['duration'])
        : get_string('coursereport:notavailable', 'mod_videotrack');
    $row = [
        $activity,
        s($sourcelabel),
        s($duration),
        videotrack_course_report_count_cell($instance->summary['started']),
        videotrack_course_report_percentage_cell(
            $instance->summary['averagepercent'],
            'coursereport:avgcoverage',
            true
        ),
        videotrack_course_report_percentage_cell(
            $instance->summary['medianpercent'],
            'coursereport:mediancoverage'
        ),
        videotrack_course_report_count_cell($instance->summary['completions']),
        videotrack_course_report_count_cell($instance->summary['noncompleted']),
        videotrack_course_report_drop_cell($instance->summary['maindrop']),
        videotrack_course_report_count_cell($instance->reactions),
        videotrack_course_report_count_cell($instance->notes),
    ];
    if ($showbookmarks) {
        $row[] = !empty($instance->bookmarksenabled)
            ? videotrack_course_report_count_cell($instance->bookmarks)
            : html_writer::span(get_string('coursereport:disabled', 'mod_videotrack'), 'text-muted');
    }
    $row[] = $report;
    $table->data[] = $row;
}

echo html_writer::table($table);
echo $OUTPUT->footer();
