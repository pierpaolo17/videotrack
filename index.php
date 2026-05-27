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

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_login($course);

$PAGE->set_url('/mod/videotrack/index.php', ['id' => $id]);
$PAGE->set_title(format_string($course->fullname));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_videotrack'));

if (!$instances = get_all_instances_in_course('videotrack', $course)) {
    echo $OUTPUT->notification(get_string('nonewmodules', 'moodle'), 'notifyproblem');
    echo $OUTPUT->footer();
    die;
}

$table = new html_table();
$table->head = [get_string('name')];
foreach ($instances as $instance) {
    $table->data[] = [html_writer::link(new moodle_url('/mod/videotrack/view.php', ['id' => $instance->coursemodule]), format_string($instance->name))];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
