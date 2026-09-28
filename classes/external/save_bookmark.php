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
use mod_videotrack\event\bookmark_saved;
use mod_videotrack\local\tracker;

/**
 * Saves a private named video bookmark for the current user.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_bookmark extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Session UUID'),
            'videotime' => new external_value(PARAM_FLOAT, 'Video timestamp in seconds'),
            'label' => new external_value(PARAM_RAW_TRIMMED, 'Private bookmark label'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate at bookmark time', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Save a private bookmark.
     *
     * @param int $cmid Course module id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video timestamp.
     * @param string $label Bookmark label.
     * @param float $playbackrate Playback rate.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        float $videotime,
        string $label,
        float $playbackrate = 1.0
    ): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid',
            'sessionid',
            'videotime',
            'label',
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
        if (empty($videotrack->bookmarksenabled)) {
            throw new \moodle_exception('bookmarksdisabled', 'mod_videotrack');
        }

        $label = self::normalise_label((string)$params['label']);
        $videotime = self::normalise_video_time($videotrack, (float)$params['videotime']);
        self::require_watched_position($videotrack, (int)$USER->id, (string)$params['sessionid'], $videotime);
        self::require_bookmark_rate_limit($videotrack, (int)$USER->id);

        $record = self::insert_bookmark_record(
            $videotrack,
            (int)$cm->id,
            (int)$USER->id,
            $params,
            $label,
            $videotime
        );
        self::trigger_bookmark_event($record, $context);

        return [
            'bookmarkeventid' => (int)$record->id,
            'videotime' => (float)$record->videotime,
            'label' => $label,
            'warnings' => [],
        ];
    }

    /**
     * Normalises a private bookmark label.
     *
     * @param string $label Raw bookmark label.
     * @return string Normalised label.
     */
    private static function normalise_label(string $label): string {
        $maxlength = \videotrack_get_config_int('bookmarkmaxlength', 120, 20, 255);
        $label = \core_text::substr(clean_param(trim($label), PARAM_TEXT), 0, $maxlength);
        if ($label === '') {
            throw new \moodle_exception('bookmarkempty', 'mod_videotrack');
        }

        return $label;
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
     * Requires server-validated watched evidence for the bookmark timestamp.
     *
     * A bookmark may target any position this learner already watched, including
     * after seeking backward to validated progress from an earlier session. If the
     * player flushes current progress first, a newly reached timestamp must already
     * be covered by server-validated evidence. Forward-seek permission alone is not
     * sufficient evidence.
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
        $alreadywatched = tracker::has_watched_videotime_any_session(
            (int)$videotrack->id,
            $userid,
            $videotime,
            2.0,
            $maxage
        );
        if (
            !$alreadywatched
            && !tracker::interaction_timestamp_allowed(
                $videotrack,
                $userid,
                $sessionid,
                $videotime,
                2.0,
                $maxage
            )
        ) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }
    }

    /**
     * Enforces the bookmark burst limit across all browser sessions.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid Current user id.
     */
    private static function require_bookmark_rate_limit(\stdClass $videotrack, int $userid): void {
        global $DB;

        $recent = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :vtid AND userid = :userid AND notetype = 'bookmark' " .
                'AND isdeleted = 0 AND timecreated >= :since',
            ['vtid' => $videotrack->id, 'userid' => $userid, 'since' => time() - 10]
        );
        if ($recent >= 10) {
            throw new \moodle_exception('error:bookmarksratelimit', 'mod_videotrack');
        }
    }

    /**
     * Persists one normalised private bookmark.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $cmid Course-module id.
     * @param int $userid Current user id.
     * @param array $params Validated external parameters.
     * @param string $label Normalised bookmark label.
     * @param float $videotime Normalised timestamp.
     * @return \stdClass Inserted bookmark record.
     */
    private static function insert_bookmark_record(
        \stdClass $videotrack,
        int $cmid,
        int $userid,
        array $params,
        string $label,
        float $videotime
    ): \stdClass {
        global $DB;

        $now = time();
        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cmid,
            'userid' => $userid,
            'videoid' => $videotrack->videoid,
            'sessionid' => (string)$params['sessionid'],
            'reactionid' => 0,
            'reactionkey' => 'bookmark',
            'reactionlabel' => get_string('bookmark_label', 'mod_videotrack'),
            'reactiondesc' => '',
            'notetext' => $label,
            'notetype' => 'bookmark',
            'videotime' => round($videotime, 3),
            'playbackrate' => round((float)$params['playbackrate'], 3),
            'isdeleted' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videotrack_reactev', $record);

        return $record;
    }

    /**
     * Triggers the dedicated Moodle event after persistence.
     *
     * @param \stdClass $record Inserted bookmark record.
     * @param object $context Activity context.
     */
    private static function trigger_bookmark_event(\stdClass $record, object $context): void {
        bookmark_saved::create([
            'objectid' => $record->id,
            'context' => $context,
            'other' => ['videotime' => $record->videotime],
        ])->trigger();
    }

    /**
     * Returns the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'bookmarkeventid' => new external_value(PARAM_INT, 'Saved bookmark event ID'),
            'videotime' => new external_value(PARAM_FLOAT, 'Saved video timestamp'),
            'label' => new external_value(PARAM_TEXT, 'Saved bookmark label'),
            'warnings' => new external_warnings(),
        ]);
    }
}
