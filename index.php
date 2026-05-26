<?php
/**
 * VideoTrack activity module.
 *
 * @package   mod_videotrack
 * @copyright 2026 SICS, Universita degli Studi della Tuscia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
