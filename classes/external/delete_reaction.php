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

class delete_reaction extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'reactioneventid' => new external_value(PARAM_INT, 'Reaction event ID'),
        ]);
    }

    public static function execute(int $cmid, int $reactioneventid): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'reactioneventid'));
        $cmraw  = get_coursemodule_from_id('videotrack', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);
        require_login($course, false, $cmraw);
        // cm_info::create va chiamato DOPO require_login: carica dati filtrati per utente.
        $cm = \cm_info::create($cmraw);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);
        $event = $DB->get_record('videotrack_reactev', ['id' => $params['reactioneventid'], 'userid' => $USER->id, 'videotrackid' => $videotrack->id], '*', MUST_EXIST);
        $event->isdeleted = 1;
        $event->timemodified = time();
        $DB->update_record('videotrack_reactev', $event);
        // Log dell'evento nei log di Moodle.
        $moodleevent = reaction_deleted::create([
            'objectid' => $event->id,
            'context'  => $context,
            'other'    => [
                'reactionlabel' => $event->reactionlabel,
            ],
        ]);
        $moodleevent->trigger();

        // Le note personali (notetype='note') non contribuiscono al completamento:
        // ricalcoliamo solo se si tratta di una reazione standard.
        $isnote = ($event->notetype ?? '') === 'note';
        // Legge reaction_counts UNA SOLA VOLTA dopo il soft-delete — già aggiornato.
        // Sia refresh_completion (se reazione) che il path nota la usano per la response.
        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);

        if (!$isnote) {
            // refresh_completion chiama reaction_counts internamente: accettiamo la doppia
            // query come trade-off (refresh_completion è già chiamato qui sotto).
            $state = tracker::refresh_completion($videotrack, $cm, (int)$USER->id);
            $completion = new \completion_info($course);
            $completion->update_state($cm,
                $state->iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE, (int)$USER->id);
            $iscompleted = (bool)$state->iscompleted;
        } else {
            $state = $DB->get_record('videotrack_state',
                ['videotrackid' => $videotrack->id, 'userid' => (int)$USER->id]);
            $iscompleted = !empty($state->iscompleted);
        }
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
