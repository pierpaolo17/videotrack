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
        // Reactions are valid during playback and while paused. The timestamp must
        // still belong to server-validated viewing data, so pausing cannot be used
        // to react at an unwatched point of the video.
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

        // Rate-limit / anti-spam. Serialise all reactions for this user/activity so
        // near-simultaneous AJAX clicks are evaluated against the same latest DB state.
        // Only one reaction of any type is kept for the same displayed video second.
        // Repeated reactions are ignored within three wall-clock seconds or within a
        // three-second window of video time.
        $reactionlockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $reactionlockkey = 'reaction:' . $videotrack->id . ':' . (int)$USER->id;
        $reactionlock = $reactionlockfactory->get_lock($reactionlockkey, 10);
        if (!$reactionlock) {
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $USER->id]);
            $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
            return [
                'reactioneventid' => 0,
                'uniquereactions' => $summary['uniquecount'],
                'iscompleted'     => !empty($state->iscompleted),
                'reaction'        => self::export_reaction_for_client($reaction, $context, $videotime),
                'warnings'        => [],
            ];
        }

        try {
            $displaysecond = (int)round($videotime);
            $videosecondstart = max(0.0, $displaysecond - 0.5);
            $videosecondend = $displaysecond + 0.5;
            $duplicatereaction = $DB->record_exists_select(
                'videotrack_reactev',
                'videotrackid = :vtid AND userid = :uid AND isdeleted = 0 ' .
                    "AND (notetype = '' OR notetype IS NULL) " .
                    'AND (' .
                        '(videotime >= :secondstart AND videotime < :secondend) OR ' .
                        '(reactionid = :reactionid AND (timecreated >= :since OR ABS(videotime - :videotime) < :window))' .
                    ')',
                [
                    'vtid' => $videotrack->id,
                    'uid' => $USER->id,
                    'reactionid' => $reaction->id,
                    'since' => $now - 3,
                    'videotime' => $videotime,
                    'window' => 3.0,
                    'secondstart' => $videosecondstart,
                    'secondend' => $videosecondend,
                ]
            );
            if ($duplicatereaction) {
                // Too close to an already-saved reaction. This is deliberately a soft ignore:
                // the UI removes its optimistic row without showing an error.
                $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $USER->id]);
                $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
                return [
                    'reactioneventid' => 0,
                    'uniquereactions' => $summary['uniquecount'],
                    'iscompleted'     => !empty($state->iscompleted),
                    'reaction'        => self::export_reaction_for_client($reaction, $context, $videotime),
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
            'notetype'    => '', // Empty string marks standard reactions and distinguishes them from personal notes.
            'isdeleted' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
            ];
            $eventid = $DB->insert_record('videotrack_reactev', $record);
        } finally {
            $reactionlock->release();
        }

        // O1: invalidate per-request cache so subsequent reaction_counts() calls
        // within this request see the newly inserted record.
        tracker::invalidate_reactioncountscache($videotrack->id, (int)$USER->id);
        $warnings = [];

        // Log the event in Moodle logs. This is useful but must not turn an
        // already-saved reaction into a failed AJAX response: otherwise the UI
        // shows an error even though the record appears after page refresh.
        try {
            $event = reaction_saved::create([
                'objectid' => $eventid,
                'context'  => $context,
                'other'    => [
                    'reactionlabel' => $reaction->label,
                    'videotime'     => $videotime,
                ],
            ]);
            $event->trigger();
        } catch (\Throwable $e) {
            debugging('VideoTrack reaction event trigger failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $warnings[] = [
                'item' => 'reaction',
                'itemid' => (int)$eventid,
                'warningcode' => 'eventtriggerfailed',
                'message' => 'Reaction saved, but the Moodle log event could not be triggered.',
            ];
        }

        // Read reaction counts once after insert, then pass the same summary to
        // refresh_completion() so this request does not repeat the aggregate query.
        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $USER->id]);
        try {
            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id')));
            $state = tracker::refresh_completion($videotrack, $cm, (int)$USER->id, $summary, $requiredreactionids);
            $completion = new \completion_info($course);
            tracker::update_moodle_completion_if_changed($completion, $cm, (bool)$state->iscompleted, (int)$USER->id);
        } catch (\Throwable $e) {
            debugging('VideoTrack reaction completion refresh failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            $warnings[] = [
                'item' => 'reaction',
                'itemid' => (int)$eventid,
                'warningcode' => 'completionrefreshfailed',
                'message' => 'Reaction saved, but completion could not be refreshed immediately.',
            ];
        }
        return [
            'reactioneventid' => $eventid,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => !empty($state->iscompleted),
            'reaction'        => self::export_reaction_for_client($reaction, $context, $videotime),
            'warnings'        => $warnings,
        ];
    }


    /**
     * Exports the saved reaction definition for immediate client-side rendering.
     *
     * @param \stdClass $reaction Reaction definition.
     * @param \context_module $context Module context.
     * @param float $videotime Saved video time.
     * @return array
     */
    private static function export_reaction_for_client(
        \stdClass $reaction,
        \context_module $context,
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
            'iconhtml' => '',
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
                'iconhtml' => new external_value(PARAM_RAW, 'Sanitised reaction icon HTML'),
                'videotime' => new external_value(PARAM_FLOAT, 'Saved video time'),
            ], 'Saved reaction data for immediate UI rendering'),
            'warnings' => new external_warnings(),
        ]);
    }
}
