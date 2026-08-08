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
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_warnings;
use mod_videotrack\local\tracker;
use mod_videotrack\event\segment_saved;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

/**
 * External function that persists a watched video segment.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_segment extends external_api {
    /** Maximum accepted duration from trusted activity configuration, in seconds. */
    private const MAX_DURATION_SECONDS = 86400;

    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Browser session ID'),
            'videotimestart' => new external_value(PARAM_FLOAT, 'Video time segment start'),
            'videotimeend' => new external_value(PARAM_FLOAT, 'Video time segment end'),
            'wallclockstart' => new external_value(PARAM_INT, 'Wallclock segment start'),
            'wallclockend' => new external_value(PARAM_INT, 'Wallclock segment end'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate'),
            'endreason' => new external_value(PARAM_ALPHANUMEXT, 'End reason'),
            'durationseconds' => new external_value(PARAM_FLOAT, 'Known duration in seconds', VALUE_DEFAULT, 0),
        ]);
    }

    /**
     * Saves a watched video segment and refreshes aggregate progress.
     *
     * @param int $cmid Course module id.
     * @param string $sessionid Browser session id.
     * @param float $videotimestart Segment start time in seconds.
     * @param float $videotimeend Segment end time in seconds.
     * @param int $wallclockstart Client wallclock start timestamp.
     * @param int $wallclockend Client wallclock end timestamp.
     * @param float $playbackrate Playback rate reported by the player.
     * @param string $endreason Segment end reason.
     * @param float $durationseconds Client-known duration in seconds.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        float $videotimestart,
        float $videotimeend,
        int $wallclockstart,
        int $wallclockend,
        float $playbackrate,
        string $endreason,
        float $durationseconds = 0.0
    ): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid',
            'sessionid',
            'videotimestart',
            'videotimeend',
            'wallclockstart',
            'wallclockend',
            'playbackrate',
            'endreason',
            'durationseconds'
        ));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['sessionid'] = helper::validate_session_id($params['sessionid']);
        $params['endreason'] = helper::validate_end_reason($params['endreason']);
        $params['videotimestart'] = helper::validate_bounded_float(
            (float)$params['videotimestart'],
            'videotimestart',
            0.0,
            self::MAX_DURATION_SECONDS
        );
        $params['videotimeend'] = helper::validate_bounded_float(
            (float)$params['videotimeend'],
            'videotimeend',
            0.0,
            self::MAX_DURATION_SECONDS
        );
        $params['playbackrate'] = helper::validate_bounded_float(
            (float)$params['playbackrate'],
            'playbackrate',
            0.25,
            4.0
        );
        $params['durationseconds'] = helper::validate_bounded_float(
            (float)$params['durationseconds'],
            'durationseconds',
            0.0,
            self::MAX_DURATION_SECONDS
        );
        helper::require_ajax_sesskey();
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        $course = $loaded['course'];
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];

        // The activity duration is authoritative only when configured by the teacher.
        // Client-reported duration is diagnostic input and must never unlock completion.
        $knownduration = (float)($videotrack->durationseconds ?? 0);
        $normaliseduration = $knownduration > 0
            ? min($knownduration, self::MAX_DURATION_SECONDS)
            : 0.0;
        $interval = tracker::normalise_interval(
            (float)$params['videotimestart'],
            (float)$params['videotimeend'],
            $normaliseduration
        );
        if ($interval === null) {
            return [
                'accepted'             => false,
                'uniquecoveredseconds' => 0.0,
                'completionpercent'    => 0.0,
                'iscompleted'          => false,
                'intervaljson'         => '[]',
                'durationseconds'      => $normaliseduration,
                'savedvideotimestart' => 0.0,
                'savedvideotimeend'   => 0.0,
                'warnings'             => [],
            ];
        }

        $playbackrate = (float)$params['playbackrate'];
        $allowedspeeds = \videotrack_get_playback_speeds($videotrack);
        $rateallowed = false;
        foreach ($allowedspeeds as $allowedspeed) {
            if (abs((float)$allowedspeed - $playbackrate) <= 0.001) {
                $rateallowed = true;
                break;
            }
        }
        if (!$rateallowed) {
            throw new \invalid_parameter_exception('Playback rate is not allowed for this activity');
        }

        $now = time();
        // Client wallclock values remain diagnostic only.
        $wstart = max(0, min($params['wallclockstart'], $now + 5));
        $wend = max($wstart, min($params['wallclockend'], $now + 5));
        $heartbeat = \videotrack_get_config_int('heartbeatinterval', 30, 5, 300);

        $segment = (object)[
            'videotrackid' => $videotrack->id,
            'courseid'     => $course->id,
            'cmid'         => $cm->id,
            'userid'       => $USER->id,
            'videoid'      => $videotrack->videoid,
            'sessionid'    => $params['sessionid'],
            'wallclockstart' => $wstart,
            'wallclockend'   => $wend,
            'videotimestart' => $interval[0],
            'videotimeend'   => $interval[1],
            'playbackrate'   => $playbackrate, // Already clamped to [0.25, 4.0] above.
            'endreason'      => $params['endreason'],
            'servervalidated' => 1,
            'timecreated'    => $now,
        ];
        // Insert the raw segment and update the aggregate state in a single atomic
        // transaction managed by update_state. If update_state fails, rollback also
        // removes the inserted segment, leaving no orphan records.
        $segmentid = null;
        $state = tracker::update_state(
            $videotrack,
            $cm,
            (int)$USER->id,
            $interval,
            $interval[1],
            $segment,
            $segmentid,
            [
                'now' => $now,
                'heartbeat' => $heartbeat,
                'playbackrate' => $playbackrate,
                'allowseekforward' => !empty($videotrack->allowseekforward),
            ]
        );

        if ($segmentid === -1) {
            return [
                'accepted'             => false,
                'uniquecoveredseconds' => (float)$state->uniquecoveredseconds,
                'completionpercent'    => (float)$state->completionpercent,
                'iscompleted'          => (bool)$state->iscompleted,
                'intervaljson'         => (string)($state->intervaljson ?? '[]'),
                'durationseconds'      => (float)($state->durationseconds ?? 0),
                'savedvideotimestart' => 0.0,
                'savedvideotimeend'   => 0.0,
                'warnings'             => [[
                    'item' => 'segment',
                    'itemid' => 0,
                    'warningcode' => 'suspicioussegment',
                    'message' => get_string('warning:suspicioussegment', 'mod_videotrack'),
                ]],
            ];
        }

        if ($segmentid === 0) {
            return [
                'accepted'             => false,
                'uniquecoveredseconds' => (float)$state->uniquecoveredseconds,
                'completionpercent'    => (float)$state->completionpercent,
                'iscompleted'          => (bool)$state->iscompleted,
                'intervaljson'         => (string)($state->intervaljson ?? '[]'),
                'durationseconds'      => (float)($state->durationseconds ?? 0),
                'savedvideotimestart' => (float)$interval[0],
                'savedvideotimeend'   => (float)$interval[1],
                'warnings'             => [],
            ];
        }

        // Log only significant actions; heartbeats produce too many log entries.
        $loggable = ['pause', 'seek', 'ended', 'beforeunload', 'pagehide'];
        if ($segmentid !== null && in_array($params['endreason'], $loggable, true)) {
            $event = segment_saved::create([
                'objectid' => $segmentid,
                'context'  => $context,
                'other'    => [
                    'videotimestart' => $interval[0],
                    'videotimeend'   => $interval[1],
                    'endreason'      => $params['endreason'],
                ],
            ]);
            $event->trigger();
        }

        $completion = new \completion_info($course);
        tracker::update_moodle_completion_if_changed($completion, $cm, (bool)$state->iscompleted, (int)$USER->id);
        return [
            'accepted'             => true,
            'uniquecoveredseconds' => (float)$state->uniquecoveredseconds,
            'completionpercent'    => (float)$state->completionpercent,
            'iscompleted'          => (bool)$state->iscompleted,
            'intervaljson'         => (string)($state->intervaljson ?? '[]'),
            'durationseconds'      => (float)($state->durationseconds ?? 0),
            'savedvideotimestart'  => (float)$interval[0],
            'savedvideotimeend'    => (float)$interval[1],
            'warnings'             => [],
        ];
    }

    /**
     * Returns the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'accepted'             => new external_value(PARAM_BOOL, 'Whether the segment was accepted'),
            'uniquecoveredseconds' => new external_value(PARAM_FLOAT, 'Unique covered seconds'),
            'completionpercent'    => new external_value(PARAM_FLOAT, 'Computed completion percentage'),
            'iscompleted'          => new external_value(PARAM_BOOL, 'Whether completion threshold has been met'),
            'intervaljson'         => new external_value(PARAM_RAW, 'JSON array of watched intervals for the progress bar'),
            'durationseconds'      => new external_value(PARAM_FLOAT, 'Total video duration in seconds'),
            'savedvideotimestart' => new external_value(PARAM_FLOAT, 'Saved segment start time in seconds'),
            'savedvideotimeend'   => new external_value(PARAM_FLOAT, 'Saved segment end time in seconds'),
            'warnings'             => new external_warnings(),
        ]);
    }
}
