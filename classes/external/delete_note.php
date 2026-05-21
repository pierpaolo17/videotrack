<?php
namespace mod_videotrack\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use mod_videotrack\local\tracker;
use mod_videotrack\event\note_deleted;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

/**
 * External function that soft-deletes a personal note owned by the current user.
 *
 * @package    mod_videotrack
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_note extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'reactioneventid' => new external_value(PARAM_INT, 'Personal note event ID'),
        ]);
    }

    /**
     * Soft-deletes a personal note owned by the current user.
     *
     * The method is intentionally separate from delete_reaction so AJAX service
     * names and privacy semantics remain explicit. Only records with
     * notetype='note' can be deleted through this endpoint. Repeated calls for
     * an already deleted note are idempotent and do not duplicate Moodle logs.
     *
     * @param int $cmid Course module id.
     * @param int $reactioneventid Note event id.
     * @return array
     */
    public static function execute(int $cmid, int $reactioneventid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'reactioneventid'));
        $cmraw = get_coursemodule_from_id('videotrack', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);

        require_login($course, false, $cmraw);
        $cm = \cm_info::create($cmraw);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);

        $event = $DB->get_record('videotrack_reactev', [
            'id' => $params['reactioneventid'],
            'userid' => $USER->id,
            'videotrackid' => $videotrack->id,
            'notetype' => 'note',
        ], '*', MUST_EXIST);

        if (empty($event->isdeleted)) {
            $event->isdeleted = 1;
            $event->timemodified = time();
            $DB->update_record('videotrack_reactev', $event);
            tracker::invalidate_reaction_counts_cache($videotrack->id, (int)$USER->id);

            $moodleevent = note_deleted::create([
                'objectid' => $event->id,
                'context' => $context,
                'other' => [
                    'reactionlabel' => $event->reactionlabel ?: get_string('studentnotes_title', 'mod_videotrack'),
                ],
            ]);
            $moodleevent->trigger();
        }

        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => $videotrack->id,
            'userid' => (int)$USER->id,
        ]);

        return [
            'deleted' => true,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted' => !empty($state->iscompleted),
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
            'deleted' => new external_value(PARAM_BOOL, 'Deleted'),
            'uniquereactions' => new external_value(PARAM_INT, 'Unique reaction count'),
            'iscompleted' => new external_value(PARAM_BOOL, 'Completion status'),
            'warnings' => new external_warnings(),
        ]);
    }
}
