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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use mod_videotrack\local\tracker;
use mod_videotrack\event\reaction_saved;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

/**
 * External function that stores a standard reaction for the current user.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_reaction extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Browser session ID'),
            'reactionid' => new external_value(PARAM_INT, 'Reaction ID'),
            'videotime' => new external_value(PARAM_FLOAT, 'Video time'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate', VALUE_DEFAULT, 1.0),
        ]);
    }

    /**
     * Saves a configured reaction for the current user.
     *
     * @param int $cmid Course module id.
     * @param string $sessionid Browser session id.
     * @param int $reactionid Reaction id.
     * @param float $videotime Video timestamp in seconds.
     * @param float $playbackrate Playback rate at reaction time.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        int $reactionid,
        float $videotime,
        float $playbackrate = 1.0
    ): array {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::execute_parameters(),
            compact('cmid', 'sessionid', 'reactionid', 'videotime', 'playbackrate')
        );
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['reactionid'] = helper::validate_positive_id((int)$params['reactionid'], 'reactionid');
        $params['sessionid'] = helper::validate_session_id($params['sessionid']);
        $params['videotime'] = helper::validate_bounded_float(
            (float)$params['videotime'],
            'videotime',
            0.0,
            86400.0
        );
        $params['playbackrate'] = helper::validate_bounded_float(
            (float)$params['playbackrate'],
            'playbackrate',
            0.25,
            4.0
        );
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        helper::require_ajax_sesskey();
        $course = $loaded['course'];
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];
        if (empty($videotrack->reactionsenabled)) {
            throw new \moodle_exception('reactionsdisabled', 'mod_videotrack');
        }
        // Read the reaction after authentication and accept active reactions only.
        $reaction = $DB->get_record('videotrack_react', [
            'id' => $params['reactionid'],
            'videotrackid' => $videotrack->id,
            'isdeleted' => 0,
        ], '*', MUST_EXIST);
        $now = time();
        $videotime = max(0.0, round((float)$params['videotime'], 3));
        $duration = (float)($videotrack->durationseconds ?? 0);
        if ($duration > 0) {
            $videotime = min($videotime, $duration);
        }
        if (!tracker::has_recent_playback($videotrack->id, (int)$USER->id, $params['sessionid'], $videotime)) {
            throw new \moodle_exception('error:playbackrequired', 'mod_videotrack');
        }
        if (!tracker::has_watched_videotime($videotrack->id, (int)$USER->id, $params['sessionid'], $videotime)) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }

        // Global anti-spam throttle: limits reaction bursts per user regardless of
        // session ID. Filtering by sessionid allowed an attacker to bypass the limit
        // by rotating session IDs on each AJAX request (B3 fix).
        $burstcount = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :bvtid AND userid = :buid AND isdeleted = 0 " .
                "AND (notetype = '' OR notetype IS NULL) AND timecreated >= :bsince",
            [
                'bvtid'  => $videotrack->id,
                'buid'   => $USER->id,
                'bsince' => $now - 10,
            ]
        );
        if ($burstcount >= 10) {
            throw new \moodle_exception('error:reactionratelimit', 'mod_videotrack');
        }

        // Rate-limit / anti-spam: reject if an identical reaction already exists for this user
        // and reaction in the previous 3 seconds. This prevents unlimited event
        // accumulation caused by automation or rapid double clicks.
        $recentcount = $DB->count_records_select(
            'videotrack_reactev',
            'videotrackid = :vtid AND userid = :uid AND reactionid = :rid AND isdeleted = 0 ' .
                "AND (notetype = '' OR notetype IS NULL) AND timecreated >= :since",
            [
                'vtid'  => $videotrack->id,
                'uid'   => $USER->id,
                'rid'   => $reaction->id,
                'since' => $now - 3,
            ]
        );
        if ($recentcount > 0) {
            // Duplicate reaction: return the current state without saving.
            // No new reaction was persisted, so the reaction count has not changed.
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $USER->id]);
            $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
            return [
                'reactioneventid' => 0,
                'uniquereactions' => $summary['uniquecount'],
                'iscompleted'     => !empty($state->iscompleted),
                'warnings'        => [],
            ];
        }

        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $USER->id,
            'videoid' => $videotrack->videoid,
            'sessionid' => $params['sessionid'],
            'reactionid' => $reaction->id,
            'reactionkey' => $reaction->reactionkey,
            'reactionlabel' => $reaction->label,
            'reactiondesc' => $reaction->description,
            'videotime' => $videotime,
            'playbackrate' => max(0.25, min(4.0, round($params['playbackrate'], 3))),
            'notetype'    => '',   // Empty string marks standard reactions and distinguishes them from personal notes.
            'isdeleted' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $eventid = $DB->insert_record('videotrack_reactev', $record);
        // O1: invalidate per-request cache so subsequent reaction_counts() calls
        // within this request see the newly inserted record.
        tracker::invalidate_reaction_counts_cache($videotrack->id, (int)$USER->id);
        // Log the event in Moodle logs.
        $event = reaction_saved::create([
            'objectid' => $eventid,
            'context'  => $context,
            'other'    => [
                'reactionlabel' => $reaction->label,
                'videotime'     => $videotime,
            ],
        ]);
        $event->trigger();
        // Read reaction counts once after insert, then pass the same summary to
        // refresh_completion() so this request does not repeat the aggregate query.
        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
            'videotrackid' => $videotrack->id,
            'requiredforcompletion' => 1,
            'isdeleted' => 0,
        ], '', 'id,id')));
        $state = tracker::refresh_completion($videotrack, $cm, (int)$USER->id, $summary, $requiredreactionids);
        $completion = new \completion_info($course);
        tracker::update_moodle_completion_if_changed($completion, $cm, (bool)$state->iscompleted, (int)$USER->id);
        return [
            'reactioneventid' => $eventid,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => (bool)$state->iscompleted,
            'warnings'        => [],
        ];
    }

    /**
     * Returns the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'reactioneventid' => new external_value(PARAM_INT, 'Reaction event ID'),
            'uniquereactions' => new external_value(PARAM_INT, 'Unique reaction count'),
            'iscompleted' => new external_value(PARAM_BOOL, 'Completion status'),
            'warnings' => new external_warnings(),
        ]);
    }
}
