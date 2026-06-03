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

global $DB, $OUTPUT, $PAGE;

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($course->id);
$PAGE->set_url('/mod/videotrack/index.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(format_string($course->fullname, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => $context]));

$event = \core\event\course_module_instance_list_viewed::create([
    'context' => $context,
    'other' => [
        'modulename' => 'videotrack',
    ],
]);
$event->add_record_snapshot('course', $course);
$event->trigger();

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_videotrack'));

if (!$instances = get_all_instances_in_course('videotrack', $course)) {
    echo $OUTPUT->notification(get_string('nonewmodules', 'moodle'), 'notifyproblem');
    echo $OUTPUT->footer();
    die;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';
if ($usesections) {
    $table->head = [get_string('sectionname', 'format_' . $course->format), get_string('name')];
    $table->align = ['left', 'left'];
} else {
    $table->head = [get_string('name')];
    $table->align = ['left'];
}

$currentsection = null;
foreach ($instances as $instance) {
    $instancecontext = context_module::instance($instance->coursemodule);
    $instancename = format_string($instance->name, true, ['context' => $instancecontext]);
    $linkattributes = [];
    if (!$instance->visible) {
        $linkattributes['class'] = 'dimmed';
        $linkattributes['aria-label'] = get_string('hiddeninstancelabel', 'mod_videotrack', $instancename);
    }
    $link = html_writer::link(
        new moodle_url('/mod/videotrack/view.php', ['id' => $instance->coursemodule]),
        $instancename,
        $linkattributes
    );

    if ($usesections) {
        $printsection = '';
        if ($instance->section !== $currentsection) {
            if ($instance->section) {
                $printsection = get_section_name($course, $instance->section);
            }
            $currentsection = $instance->section;
        }
        $table->data[] = [$printsection, $link];
    } else {
        $table->data[] = [$link];
    }
}

echo html_writer::table($table);
echo $OUTPUT->footer();
