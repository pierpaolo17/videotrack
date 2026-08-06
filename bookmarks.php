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
 * Export the current user's private video bookmarks.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

$id = required_param('id', PARAM_INT);
$cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/videotrack:view', $context);
require_sesskey();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($videotrack->bookmarksenabled)) {
    throw new moodle_exception('invalidrequest', 'error');
}

\mod_videotrack\event\bookmark_exported::create([
    'objectid' => (int)$videotrack->id,
    'context' => $context,
])->trigger();

$filename = 'videotrack_bookmarks_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Disposition: attachment; filename="' . $filename . '"');
$fh = fopen('php://output', 'w');
\mod_videotrack\local\csv_export::write_utf8_bom($fh);
$delimiter = \mod_videotrack\local\csv_export::delimiter($videotrack);
\mod_videotrack\local\csv_export::write_row($fh, [
    get_string('bookmark_export_timestamp', 'mod_videotrack'),
    get_string('bookmark_export_label', 'mod_videotrack'),
    get_string('bookmark_export_created', 'mod_videotrack'),
], $delimiter);
$rs = $DB->get_recordset_select(
    'videotrack_reactev',
    "videotrackid = :vtid AND userid = :userid AND notetype = 'bookmark' AND isdeleted = 0",
    ['vtid' => $videotrack->id, 'userid' => $USER->id],
    'videotime ASC, timecreated ASC',
    'videotime, notetext, timecreated'
);
foreach ($rs as $bookmark) {
    \mod_videotrack\local\csv_export::write_row($fh, [
        videotrack_format_video_timestamp((float)$bookmark->videotime, (float)$videotrack->durationseconds),
        (string)$bookmark->notetext,
        userdate((int)$bookmark->timecreated),
    ], $delimiter);
}
$rs->close();
fclose($fh);
exit;
