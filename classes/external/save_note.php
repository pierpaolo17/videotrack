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

namespace mod_videotrack\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use mod_videotrack\local\tracker;
use mod_videotrack\event\note_saved;

/**
 * External function: save a personal timestamped note for the current student.
 *
 * Notes are stored as reaction events with notetype='note' and notetext set.
 * reactionid is set to 0 (no associated reaction definition).
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_note extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'        => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid'   => new external_value(PARAM_ALPHANUMEXT, 'Session UUID'),
            'videotime'   => new external_value(PARAM_FLOAT, 'Video timestamp in seconds'),
            'notetext'    => new external_value(PARAM_RAW_TRIM, 'Note text'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate at time of note', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Saves a personal note for the current user.
     *
     * @param int $cmid Course module id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video timestamp in seconds.
     * @param string $notetext Note text.
     * @param float $playbackrate Playback rate at note time.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        float $videotime,
        string $notetext,
        float $playbackrate = 1.0
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid',
            'sessionid',
            'videotime',
            'notetext',
            'playbackrate'
        ));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['sessionid'] = helper::validate_session_id($params['sessionid']);
        $params['videotime'] = helper::validate_bounded_float((float)$params['videotime'], 'videotime', 0.0, 86400.0);
        $params['playbackrate'] = helper::validate_bounded_float((float)$params['playbackrate'], 'playbackrate', 0.25, 4.0);

        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        helper::require_ajax_sesskey();
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];

        // Check that notes are enabled for this instance.
        if (empty($videotrack->studentnotesenabled)) {
            throw new \moodle_exception('studentnotesdisabled', 'mod_videotrack');
        }

        // Sanitize and truncate to the configured limit to prevent abuse.
        // Notes are plain text in the UI and restore path; normalise AJAX input
        // before storing it so raw HTML is never persisted in notetext.
        $notemaxlength = \videotrack_get_config_int('notemaxlength', 2000, 100, 10000);
        $rawtext = clean_param(trim($params['notetext']), PARAM_TEXT);
        $truncated = $notemaxlength > 0 && \core_text::strlen($rawtext) > $notemaxlength;
        $text = \core_text::substr($rawtext, 0, $notemaxlength);
        if ($text === '') {
            throw new \moodle_exception('invaliddata', 'error');
        }

        // Sanitize videotime: clamp to [0, durationseconds].
        // Prevent notes at negative timestamps or beyond the end of the video.
        $rawtime  = (float)$params['videotime'];
        $duration = (float)($videotrack->durationseconds ?? 0);
        $videotime = max(0.0, $duration > 0 ? min($rawtime, $duration) : $rawtime);

        if (!tracker::has_recent_playback($videotrack->id, (int)$USER->id, $params['sessionid'], $videotime)) {
            throw new \moodle_exception('error:playbackrequired', 'mod_videotrack');
        }
        if (!tracker::has_watched_videotime($videotrack->id, (int)$USER->id, $params['sessionid'], $videotime)) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }

        // Global note rate limit: max 5 notes every 10 seconds per user/activity.
        $recentnotes = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :vtid AND userid = :userid AND notetype = 'note' AND isdeleted = 0 AND timecreated >= :since",
            [
                'vtid' => $videotrack->id,
                'userid' => (int)$USER->id,
                'since' => time() - 10,
            ]
        );
        if ($recentnotes >= 5) {
            throw new \moodle_exception('error:notesratelimit', 'mod_videotrack');
        }

        $now = time();
        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid'     => $videotrack->course,
            'cmid'         => $cm->id,
            'userid'       => (int)$USER->id,
            'videoid'      => $videotrack->videoid,
            'sessionid'    => $params['sessionid'],
            'reactionid'   => 0,
            'reactionkey'  => 'note',
            'reactionlabel' => get_string('studentnote_label', 'mod_videotrack'),
            'reactiondesc' => '',
            'notetext'     => $text,
            'notetype'     => 'note',
            'videotime'    => round($videotime, 3),
            'playbackrate' => max(0.25, min(4.0, round((float)$params['playbackrate'], 3))),
            'isdeleted'    => 0,
            'timecreated'  => $now,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videotrack_reactev', $record);

        // M3 fix: use the dedicated note_saved event instead of reusing reaction_saved.
        // Distinct events allow Moodle logs and reports to differentiate between
        // reaction button clicks and personal student notes.
        $event = note_saved::create([
            'objectid' => $record->id,
            'context'  => $context,
            'userid'   => (int)$USER->id,
            'other'    => [
                'videotime' => $record->videotime,
            ],
        ]);
        $event->trigger();

        $warnings = [];
        if ($truncated) {
            $warnings[] = [
                'item' => 'note',
                'itemid' => (int)$record->id,
                'warningcode' => 'notetruncated',
                'message' => get_string('warning:notetruncated', 'mod_videotrack'),
            ];
        }

        return [
            'noteeventid' => (int)$record->id,
            'warnings'    => $warnings,
        ];
    }

    /**
     * Returns the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'noteeventid' => new external_value(PARAM_INT, 'ID of the saved note event'),
            'warnings'    => new external_warnings(),
        ]);
    }
}
