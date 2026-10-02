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
use mod_videotrack\local\reaction_write_service;
use mod_videotrack\local\tracker;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');

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
        $params = self::validate_request(compact('cmid', 'sessionid', 'reactionid', 'videotime', 'playbackrate'));
        helper::require_ajax_sesskey();
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        $course = $loaded['course'];
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];
        if (empty($videotrack->reactionsenabled)) {
            throw new \moodle_exception('reactionsdisabled', 'mod_videotrack');
        }
        $userid = (int)$USER->id;
        $reaction = self::load_active_reaction($videotrack, (int)$params['reactionid']);
        $now = time();
        $videotime = self::normalise_video_time($videotrack, (float)$params['videotime']);
        self::require_watched_position($videotrack, $userid, $params['sessionid'], $videotime);
        self::require_burst_limit($videotrack, $userid, $now);

        // Rate-limit / anti-spam. Serialise all reactions for this user/activity so
        // near-simultaneous AJAX clicks are evaluated against the same latest DB state.
        // Only one reaction of any type is kept for the same displayed video second.
        // Repeated reactions are ignored within three wall-clock seconds or within a
        // three-second window of video time.
        $reactionlockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $reactionlockkey = 'reaction:' . $videotrack->id . ':' . $userid;
        $reactionlock = $reactionlockfactory->get_lock($reactionlockkey, 10);
        if (!$reactionlock) {
            return self::ignored_reaction_response($videotrack, $userid, $reaction, $context, $videotime);
        }

        try {
            $duplicatereaction = self::has_duplicate_reaction($videotrack, $userid, $reaction, $videotime, $now);
            if ($duplicatereaction) {
                // Too close to an already-saved reaction. This is deliberately a soft ignore:
                // the UI removes its optimistic row without showing an error.
                return self::ignored_reaction_response($videotrack, $userid, $reaction, $context, $videotime);
            }

            $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid' => $course->id,
            'cmid' => $cm->id,
            'userid' => $userid,
            'videoid' => $videotrack->videoid,
            'sessionid' => $params['sessionid'],
            'reactionid' => $reaction->id,
            'reactionkey' => $reaction->reactionkey,
            'reactionlabel' => $reaction->label,
            'reactiondesc' => $reaction->description,
            'videotime' => $videotime,
            'playbackrate' => max(0.25, min(4.0, round($params['playbackrate'], 3))),
            'notetype'    => '', // Empty string marks standard reactions and distinguishes them from personal notes.
            'isdeleted' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
            ];
            $eventid = $DB->insert_record('videotrack_reactev', $record);
        } finally {
            $reactionlock->release();
        }

        $postwrite = reaction_write_service::complete($loaded, $userid, $reaction, $eventid, $videotime);
        return [
            'reactioneventid' => $eventid,
            'uniquereactions' => $postwrite['uniquereactions'],
            'iscompleted'     => $postwrite['iscompleted'],
            'reaction'        => self::export_reaction_for_client($reaction, $context, $videotime),
            'warnings'        => $postwrite['warnings'],
        ];
    }

    /**
     * Validates and normalises the public request payload.
     *
     * @param array $request Raw request values.
     * @return array
     */
    private static function validate_request(array $request): array {
        $params = self::validate_parameters(self::execute_parameters(), $request);
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
        return $params;
    }

    /**
     * Loads an active reaction belonging to the current activity.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $reactionid Reaction id.
     * @return \stdClass
     */
    private static function load_active_reaction(\stdClass $videotrack, int $reactionid): \stdClass {
        global $DB;
        return $DB->get_record('videotrack_react', [
            'id' => $reactionid,
            'videotrackid' => $videotrack->id,
            'isdeleted' => 0,
        ], '*', MUST_EXIST);
    }

    /**
     * Rounds a requested video time and clamps it to the configured duration.
     *
     * @param \stdClass $videotrack Activity record.
     * @param float $requestedtime Requested video time.
     * @return float
     */
    private static function normalise_video_time(\stdClass $videotrack, float $requestedtime): float {
        $videotime = max(0.0, round($requestedtime, 3));
        $duration = (float)($videotrack->durationseconds ?? 0);
        return $duration > 0 ? min($videotime, $duration) : $videotime;
    }

    /**
     * Requires server-validated watched evidence for the requested position.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video time.
     * @return void
     */
    private static function require_watched_position(
        \stdClass $videotrack,
        int $userid,
        string $sessionid,
        float $videotime
    ): void {
        if (!tracker::interaction_timestamp_allowed($videotrack, $userid, $sessionid, $videotime)) {
            throw new \moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }
    }

    /**
     * Enforces the cross-session reaction burst limit.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid User id.
     * @param int $now Current timestamp.
     * @return void
     */
    private static function require_burst_limit(\stdClass $videotrack, int $userid, int $now): void {
        global $DB;
        $burstcount = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :bvtid AND userid = :buid AND isdeleted = 0 " .
                "AND (notetype = '' OR notetype IS NULL) AND timecreated >= :bsince",
            [
                'bvtid'  => $videotrack->id,
                'buid'   => $userid,
                'bsince' => $now - 10,
            ]
        );
        if ($burstcount >= 10) {
            throw new \moodle_exception('error:reactionratelimit', 'mod_videotrack');
        }
    }

    /**
     * Checks both duplicate-reaction windows while the per-user lock is held.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid User id.
     * @param \stdClass $reaction Reaction definition.
     * @param float $videotime Video time.
     * @param int $now Current timestamp.
     * @return bool
     */
    private static function has_duplicate_reaction(
        \stdClass $videotrack,
        int $userid,
        \stdClass $reaction,
        float $videotime,
        int $now
    ): bool {
        global $DB;
        $displaysecond = (int)round($videotime);
        $videosecondstart = max(0.0, $displaysecond - 0.5);
        $videosecondend = $displaysecond + 0.5;
        return $DB->record_exists_select(
            'videotrack_reactev',
            'videotrackid = :vtid AND userid = :uid AND isdeleted = 0 ' .
                "AND (notetype = '' OR notetype IS NULL) " .
                'AND (' .
                    '(videotime >= :secondstart AND videotime < :secondend) OR ' .
                    '(reactionid = :reactionid AND (timecreated >= :since OR ABS(videotime - :videotime) < :window))' .
                ')',
            [
                'vtid' => $videotrack->id,
                'uid' => $userid,
                'reactionid' => $reaction->id,
                'since' => $now - 3,
                'videotime' => $videotime,
                'window' => 3.0,
                'secondstart' => $videosecondstart,
                'secondend' => $videosecondend,
            ]
        );
    }

    /**
     * Builds the successful soft-ignore response used for lock and duplicate races.
     *
     * @param \stdClass $videotrack Activity record.
     * @param int $userid User id.
     * @param \stdClass $reaction Reaction definition.
     * @param object $context Module context.
     * @param float $videotime Video time.
     * @return array
     */
    private static function ignored_reaction_response(
        \stdClass $videotrack,
        int $userid,
        \stdClass $reaction,
        object $context,
        float $videotime
    ): array {
        global $DB;
        $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
        $summary = tracker::reaction_counts($videotrack->id, $userid);
        return [
            'reactioneventid' => 0,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => !empty($state->iscompleted),
            'reaction'        => self::export_reaction_for_client($reaction, $context, $videotime),
            'warnings'        => [],
        ];
    }

    /**
     * Exports the saved reaction definition for immediate client-side rendering.
     *
     * @param \stdClass $reaction Reaction definition.
     * @param object $context Module context.
     * @param float $videotime Saved video time.
     * @return array
     */
    private static function export_reaction_for_client(
        \stdClass $reaction,
        object $context,
        float $videotime
    ): array {
        $icontype = clean_param((string)($reaction->icontype ?? 'emoji'), PARAM_ALPHA);
        if (!in_array($icontype, ['emoji', 'fa', 'file'], true)) {
            $icontype = 'emoji';
        }
        $iconvalue = (string)($reaction->iconvalue ?? '');
        return [
            'label' => (string)($reaction->label ?? ''),
            'description' => (string)($reaction->description ?? ''),
            'icontype' => $icontype,
            'iconclass' => $icontype === 'fa' ? $iconvalue : '',
            'iconsrc' => $icontype === 'file' ? videotrack_reaction_icon_url($context, $reaction) : '',
            'icontext' => $icontype === 'emoji' ? ($iconvalue !== '' ? $iconvalue : (string)($reaction->label ?? '')) : '',
            'videotime' => $videotime,
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
            'reaction' => new external_single_structure([
                'label' => new external_value(PARAM_TEXT, 'Reaction label'),
                'description' => new external_value(PARAM_TEXT, 'Reaction description'),
                'icontype' => new external_value(PARAM_ALPHA, 'Reaction icon type'),
                'iconclass' => new external_value(PARAM_NOTAGS, 'Font Awesome icon classes'),
                'iconsrc' => new external_value(PARAM_URL, 'Reaction file icon URL'),
                'icontext' => new external_value(PARAM_TEXT, 'Emoji or text icon fallback'),
                'videotime' => new external_value(PARAM_FLOAT, 'Saved video time'),
            ], 'Saved reaction data for immediate UI rendering'),
            'warnings' => new external_warnings(),
        ]);
    }
}
