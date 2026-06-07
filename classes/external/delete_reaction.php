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
use mod_videotrack\event\reaction_deleted;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

/**
 * External function that soft-deletes a standard reaction owned by the current user.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_reaction extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'reactioneventid' => new external_value(PARAM_INT, 'Reaction event ID'),
        ]);
    }

    /**
     * Soft-deletes a standard reaction owned by the current user.
     *
     * Repeated calls for an already deleted record are idempotent and do not
     * create duplicate Moodle log events.
     *
     * @param int $cmid Course module id.
     * @param int $reactioneventid Reaction event id.
     * @return array
     */
    public static function execute(int $cmid, int $reactioneventid): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'reactioneventid'));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['reactioneventid'] = helper::validate_positive_id((int)$params['reactioneventid'], 'reactioneventid');
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        helper::require_ajax_sesskey();
        $course = $loaded['course'];
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];
        $event = $DB->get_record_select(
            'videotrack_reactev',
            'id = :id AND userid = :userid AND videotrackid = :videotrackid AND (notetype IS NULL OR notetype <> :notetype)',
            [
                'id' => $params['reactioneventid'],
                'userid' => $USER->id,
                'videotrackid' => $videotrack->id,
                'notetype' => 'note',
            ],
            '*',
            MUST_EXIST
        );

        $changed = false;
        if (empty($event->isdeleted)) {
            $event->isdeleted = 1;
            $event->timemodified = time();
            $DB->update_record('videotrack_reactev', $event);
            $changed = true;
            // Invalidate the per-request cache so subsequent reaction counts see the soft-deleted record.
            tracker::invalidate_reaction_counts_cache($videotrack->id, (int)$USER->id);
            // Log the event in Moodle logs.
            $moodleevent = reaction_deleted::create([
                'objectid' => $event->id,
                'context'  => $context,
                'other'    => [
                    'reactionlabel' => $event->reactionlabel,
                ],
            ]);
            $moodleevent->trigger();
        }

        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        if ($changed) {
            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id')));
            $state = tracker::refresh_completion($videotrack, $cm, (int)$USER->id, $summary, $requiredreactionids);
            $completion = new \completion_info($course);
            tracker::update_moodle_completion_if_changed($completion, $cm, (bool)$state->iscompleted, (int)$USER->id);
            $iscompleted = (bool)$state->iscompleted;
        } else {
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => (int)$USER->id]);
            $iscompleted = $state ? (bool)$state->iscompleted : false;
        }
        return [
            'deleted'         => true,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => $iscompleted,
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
            'deleted' => new external_value(PARAM_BOOL, 'Deleted'),
            'uniquereactions' => new external_value(PARAM_INT, 'Unique reaction count'),
            'iscompleted' => new external_value(PARAM_BOOL, 'Completion status'),
            'warnings' => new external_warnings(),
        ]);
    }
}
