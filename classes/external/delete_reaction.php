<?php
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
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $event = $DB->get_record_select('videotrack_reactev',
            'id = :id AND userid = :userid AND videotrackid = :videotrackid AND (notetype IS NULL OR notetype <> :notetype)',
            [
                'id' => $params['reactioneventid'],
                'userid' => $USER->id,
                'videotrackid' => $videotrack->id,
                'notetype' => 'note',
            ], '*', MUST_EXIST);

        if (empty($event->isdeleted)) {
            $event->isdeleted = 1;
            $event->timemodified = time();
            $DB->update_record('videotrack_reactev', $event);
            // O1: invalidate per-request cache so subsequent reaction_counts() calls
            // within this request see the updated (soft-deleted) record.
            tracker::invalidate_reaction_counts_cache($videotrack->id, (int)$USER->id);
            // Log dell'evento nei log di Moodle.
            $moodleevent = reaction_deleted::create([
                'objectid' => $event->id,
                'context'  => $context,
                'other'    => [
                    'reactionlabel' => $event->reactionlabel,
                ],
            ]);
            $moodleevent->trigger();
        }

        // B5 fix: reaction_counts is called once here, before refresh_completion.
        // refresh_completion calls it internally too, but we need the count for the
        // response. Calling it before avoids a third redundant call after the block.
        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        $state = tracker::refresh_completion($videotrack, $cm, (int)$USER->id);
        $completion = new \completion_info($course);
        $completion->update_state($cm,
            $state->iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE, (int)$USER->id);
        $iscompleted = (bool)$state->iscompleted;
        return [
            'deleted'         => true,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => $iscompleted,
            'warnings'        => [],
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'deleted' => new external_value(PARAM_BOOL, 'Deleted'),
            'uniquereactions' => new external_value(PARAM_INT, 'Unique reaction count'),
            'iscompleted' => new external_value(PARAM_BOOL, 'Completion status'),
            'warnings' => new external_warnings(),
        ]);
    }
}
