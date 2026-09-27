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

require_once(__DIR__ . '/../../lib.php');

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
            'notetext'    => new external_value(PARAM_RAW_TRIMMED, 'Note text'),
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
        global $USER;

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

        helper::require_ajax_sesskey();
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];

        // Check that notes are enabled for this instance.
        if (empty($videotrack->studentnotesenabled)) {
            throw new \moodle_exception('studentnotesdisabled', 'mod_videotrack');
        }

        [$text, $truncated] = self::normalise_note_text($params['notetext']);
        $videotime = self::normalise_video_time($videotrack, (float)$params['videotime']);
        self::require_watched_position($videotrack, (int)$USER->id, $params['sessionid'], $videotime);
        self::require_note_rate_limit($videotrack, (int)$USER->id);

        $record = self::insert_note_record($videotrack, (int)$cm->id, (int)$USER->id, $params, $text, $videotime);
        $warnings = self::collect_warnings($record, $context, (int)$USER->id, $truncated);

        return [
            'noteeventid' => (int)$record->id,
            'warnings'    => $warnings,
        ];
    }

    /**
     * Normalises plain-text note content and applies the configured storage bound.
     *
     * @param string $notetext Raw note text.
     * @return array{0: string, 1: bool} Normalised text followed by its truncation flag.
     */
    private static function normalise_note_text(string $notetext): array {
        $notemaxlength = \videotrack_get_config_int('notemaxlength', 2000, 100, 10000);
        $rawtext = clean_param(trim($notetext), PARAM_TEXT);
        $truncated = $notemaxlength > 0 && \core_text::strlen($rawtext) > $notemaxlength;
        $text = \core_text::substr($rawtext, 0, $notemaxlength);
        if ($text === '') {
            throw new \moodle_exception('invaliddata', 'error');
        }

        return [$text, $truncated];
    }

    /**
     * Clamps the requested timestamp to the known media duration.
     *
     * @param \stdClass $videotrack Activity record.
     * @param float $rawtime Requested timestamp.
     * @return float Normalised timestamp.
     */
    private static function normalise_video_time(\stdClass $videotrack, float $rawtime): float {
        $duration = (float)($videotrack->durationseconds ?? 0);
        return max(0.0, $duration > 0 ? min($rawtime, $duration) : $rawtime);
    }

    /**
     * Requires server-validated watched evidence for the note timestamp.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid Current user id.
     * @param string $sessionid Browser playback session id.
     * @param float $videotime Normalised timestamp.
     */
    private static function require_watched_position(
        \stdClass $videotrack,
        int $userid,
        string $sessionid,
        float $videotime
    ): void {
        $fallbackdays = \videotrack_get_config_int('validationfallbackdays', 30, 0, 3650);
        $maxage = $fallbackdays > 0 ? $fallbackdays * DAYSECS : 0;
        if (!tracker::interaction_timestamp_allowed($videotrack, $userid, $sessionid, $videotime, 2.0, $maxage)) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }
    }

    /**
     * Enforces the global note burst limit for one user and activity.
     *
     * The predicate deliberately does not include sessionid, so opening multiple
     * browser sessions cannot bypass the maximum of five notes in ten seconds.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid Current user id.
     */
    private static function require_note_rate_limit(\stdClass $videotrack, int $userid): void {
        global $DB;

        $recentnotes = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :vtid AND userid = :userid AND notetype = 'note' AND isdeleted = 0 AND timecreated >= :since",
            [
                'vtid' => $videotrack->id,
                'userid' => $userid,
                'since' => time() - 10,
            ]
        );
        if ($recentnotes >= 5) {
            throw new \moodle_exception('error:notesratelimit', 'mod_videotrack');
        }
    }

    /**
     * Persists one normalised personal note.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $cmid Course-module id.
     * @param int $userid Current user id.
     * @param array $params Validated external parameters.
     * @param string $text Normalised note text.
     * @param float $videotime Normalised timestamp.
     * @return \stdClass Inserted note record.
     */
    private static function insert_note_record(
        \stdClass $videotrack,
        int $cmid,
        int $userid,
        array $params,
        string $text,
        float $videotime
    ): \stdClass {
        global $DB;

        $now = time();
        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid'     => $videotrack->course,
            'cmid'         => $cmid,
            'userid'       => $userid,
            'videoid'      => $videotrack->videoid,
            'sessionid'    => (string)$params['sessionid'],
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

        return $record;
    }

    /**
     * Triggers the dedicated Moodle event and builds non-fatal client warnings.
     *
     * @param \stdClass $record Inserted note record.
     * @param object $context Activity context.
     * @param int $userid Current user id.
     * @param bool $truncated Whether the submitted note exceeded the configured bound.
     * @return array External-function warnings.
     */
    private static function collect_warnings(
        \stdClass $record,
        object $context,
        int $userid,
        bool $truncated
    ): array {
        $warnings = [];
        try {
            $event = note_saved::create([
                'objectid' => $record->id,
                'context'  => $context,
                'userid'   => $userid,
                'other'    => [
                    'videotime' => $record->videotime,
                ],
            ]);
            $event->trigger();
        } catch (\Throwable $e) {
            debugging('VideoTrack note event trigger failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $warnings[] = [
                'item' => 'note',
                'itemid' => (int)$record->id,
                'warningcode' => 'eventtriggerfailed',
                'message' => get_string('warning:noteeventtriggerfailed', 'mod_videotrack'),
            ];
        }

        if ($truncated) {
            $warnings[] = [
                'item' => 'note',
                'itemid' => (int)$record->id,
                'warningcode' => 'notetruncated',
                'message' => get_string('warning:notetruncated', 'mod_videotrack'),
            ];
        }

        return $warnings;
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
