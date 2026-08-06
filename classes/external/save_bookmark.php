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
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid', 'sessionid', 'videotime', 'label', 'playbackrate'
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
        if (empty($videotrack->bookmarksenabled)) {
            throw new \moodle_exception('bookmarksdisabled', 'mod_videotrack');
        }

        $maxlength = \videotrack_get_config_int('bookmarkmaxlength', 120, 20, 255);
        $label = \core_text::substr(clean_param(trim($params['label']), PARAM_TEXT), 0, $maxlength);
        if ($label === '') {
            throw new \moodle_exception('bookmarkempty', 'mod_videotrack');
        }

        $duration = (float)($videotrack->durationseconds ?? 0);
        $videotime = max(0.0, $duration > 0 ? min((float)$params['videotime'], $duration) : (float)$params['videotime']);
        $fallbackdays = \videotrack_get_config_int('validationfallbackdays', 30, 0, 3650);
        $maxage = $fallbackdays > 0 ? $fallbackdays * DAYSECS : 0;
        if (!tracker::has_watched_videotime(
            $videotrack->id,
            (int)$USER->id,
            $params['sessionid'],
            $videotime,
            2.0,
            $maxage
        )) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }

        $recent = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :vtid AND userid = :userid AND notetype = 'bookmark' " .
                'AND isdeleted = 0 AND timecreated >= :since',
            ['vtid' => $videotrack->id, 'userid' => (int)$USER->id, 'since' => time() - 10]
        );
        if ($recent >= 10) {
            throw new \moodle_exception('error:bookmarksratelimit', 'mod_videotrack');
        }

        $now = time();
        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $videotrack->course,
            'cmid' => $cm->id,
            'userid' => (int)$USER->id,
            'videoid' => $videotrack->videoid,
            'sessionid' => $params['sessionid'],
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
        bookmark_saved::create([
            'objectid' => $record->id,
            'context' => $context,
            'other' => ['videotime' => $record->videotime],
        ])->trigger();

        return [
            'bookmarkeventid' => (int)$record->id,
            'videotime' => (float)$record->videotime,
            'label' => $label,
            'warnings' => [],
        ];
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
