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

namespace mod_videotrack\local;

use mod_videotrack\event\reaction_saved;

// Keep an explicit direct-access guard on this write-side service.
// phpcs:ignore moodle.Files.MoodleInternal.MoodleInternalNotNeeded
defined('MOODLE_INTERNAL') || die();

/**
 * Completes the non-transactional side effects of a persisted reaction.
 *
 * Event and completion failures become bounded response warnings because the
 * canonical reaction row has already been committed when this service runs.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reaction_write_service {
    /**
     * Invalidates cached counts, emits the event and refreshes completion.
     *
     * @param array $loaded Validated course, activity, module and context records.
     * @param int $userid User id.
     * @param \stdClass $reaction Reaction definition.
     * @param int $eventid Saved reaction event id.
     * @param float $videotime Saved video time.
     * @return array Response fields produced by post-write processing.
     */
    public static function complete(
        array $loaded,
        int $userid,
        \stdClass $reaction,
        int $eventid,
        float $videotime
    ): array {
        global $DB;
        $course = $loaded['course'];
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];

        // O1: invalidate per-request cache so subsequent reaction_counts() calls
        // within this request see the newly inserted record.
        tracker::invalidate_reactioncountscache($videotrack->id, $userid);
        $warnings = self::trigger_event($context, $reaction, $eventid, $videotime);

        // Read reaction counts once after insert, then pass the same summary to
        // refresh_completion() so this request does not repeat the aggregate query.
        $summary = tracker::reaction_counts($videotrack->id, $userid);
        $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
        try {
            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id')));
            $state = tracker::refresh_completion($videotrack, $cm, $userid, $summary, $requiredreactionids);
            $completion = new \completion_info($course);
            tracker::update_moodle_completion_if_changed($completion, $cm, (bool)$state->iscompleted, $userid);
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
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted' => !empty($state->iscompleted),
            'warnings' => $warnings,
        ];
    }

    /**
     * Emits the Moodle reaction event without failing an already-persisted write.
     *
     * @param object $context Module context.
     * @param \stdClass $reaction Reaction definition.
     * @param int $eventid Saved reaction event id.
     * @param float $videotime Saved video time.
     * @return array Empty on success or one bounded warning on failure.
     */
    private static function trigger_event(
        object $context,
        \stdClass $reaction,
        int $eventid,
        float $videotime
    ): array {
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
            return [];
        } catch (\Throwable $e) {
            debugging('VideoTrack reaction event trigger failed: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [[
                'item' => 'reaction',
                'itemid' => (int)$eventid,
                'warningcode' => 'eventtriggerfailed',
                'message' => 'Reaction saved, but the Moodle log event could not be triggered.',
            ]];
        }
    }
}
