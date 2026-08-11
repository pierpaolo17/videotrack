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
 * Compose a Forum discussion linked to a VideoTrack timestamp.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');
require_once($CFG->libdir . '/formslib.php');

$id = required_param('id', PARAM_INT);
$time = required_param('time', PARAM_INT);
$sessionid = optional_param('sessionid', '', PARAM_ALPHANUMEXT);
$cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/videotrack:view', $context);

$duration = max(0.0, (float)($videotrack->durationseconds ?? 0));
$time = max(0, $time);
if ($duration > 0) {
    $time = min($time, (int)round($duration));
}
$timestamp = videotrack_format_video_timestamp((float)$time, $duration);
$replayurl = videotrack_build_replay_url(
    (int)$cm->id,
    (float)$time,
    (int)($videotrack->clusterwindow ?? 30),
    $duration
);

$pageparams = ['id' => $cm->id, 'time' => $time];
if ($sessionid !== '') {
    $pageparams['sessionid'] = $sessionid;
}
$PAGE->set_url('/mod/videotrack/forum_post.php', $pageparams);
$PAGE->set_context($context);
$PAGE->set_title(get_string('forum:composetitle', 'mod_videotrack'));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => context_course::instance($course->id)]));
$PAGE->set_pagelayout('standard');

try {
    $destination = \mod_videotrack\local\forum_bridge::resolve_destination($videotrack, $course);
} catch (moodle_exception) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('forum:composetitle', 'mod_videotrack'));
    echo $OUTPUT->notification(get_string('forum:destinationunavailable', 'mod_videotrack'), 'notifyproblem');
    echo html_writer::link(
        $replayurl,
        get_string('forum:returntovideo', 'mod_videotrack'),
        ['class' => 'btn btn-secondary']
    );
    echo $OUTPUT->footer();
    exit;
}
\mod_videotrack\local\forum_bridge::validate_timestamp_access(
    $videotrack,
    $context,
    (int)$USER->id,
    (float)$time,
    $sessionid
);

$forumname = format_string($destination['forum']->name, true, ['context' => $destination['context']]);
$form = new \mod_videotrack\form\forum_post_form(null, [
    'forumname' => $forumname,
    'timestamp' => $timestamp,
    'groupoptions' => $destination['groupoptions'],
    'context' => $destination['context'],
    'cansubscribe' => $destination['cansubscribe'],
]);
$defaultmessage = html_writer::tag(
    'p',
    html_writer::link($replayurl, get_string('forum:replaylink', 'mod_videotrack', $timestamp))
) . html_writer::tag('p', '&nbsp;');
if (!$form->is_submitted()) {
    $form->set_data((object)[
        'id' => $cm->id,
        'time' => $time,
        'sessionid' => $sessionid,
        'groupid' => (int)array_key_first($destination['groupoptions']),
        'subject' => videotrack_build_forum_subject($videotrack, $timestamp),
        'message_editor' => [
            'text' => $defaultmessage,
            'format' => FORMAT_HTML,
            'itemid' => 0,
        ],
        'subscribe' => (int)$destination['defaultsubscribe'],
    ]);
}

if ($form->is_cancelled()) {
    redirect($replayurl);
}
if ($data = $form->get_data()) {
    try {
        $discussionid = \mod_videotrack\local\forum_bridge::create_discussion(
            $videotrack,
            $course,
            (string)$data->subject,
            (string)$data->message_editor['text'],
            (int)$data->groupid,
            !empty($data->subscribe)
        );
        redirect(
            new moodle_url('/mod/forum/discuss.php', ['d' => $discussionid]),
            get_string('forum:publishsuccess', 'mod_videotrack'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } catch (moodle_exception) {
        $form->set_data($data);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('forum:composetitle', 'mod_videotrack'));
        echo $OUTPUT->notification(get_string('forum:publishfailed', 'mod_videotrack'), 'notifyproblem');
        $form->display();
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('forum:composetitle', 'mod_videotrack'));
echo $OUTPUT->notification(
    get_string('forum:composerprivacy', 'mod_videotrack'),
    \core\output\notification::NOTIFY_INFO
);
$form->display();
echo $OUTPUT->footer();
