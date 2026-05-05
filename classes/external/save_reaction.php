<?php
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

class save_reaction extends external_api {
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Browser session ID'),
            'reactionid' => new external_value(PARAM_INT, 'Reaction ID'),
            'videotime' => new external_value(PARAM_FLOAT, 'Video time'),
            'playbackrate' => new external_value(PARAM_FLOAT, 'Playback rate', VALUE_DEFAULT, 1.0),
        ]);
    }

    public static function execute(int $cmid, string $sessionid, int $reactionid, float $videotime, float $playbackrate = 1.0): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'sessionid', 'reactionid', 'videotime', 'playbackrate'));
        $cmraw  = get_coursemodule_from_id('videotrack', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);
        // require_login PRIMA di qualsiasi query su dati del plugin (pattern Moodle).
        require_login($course, false, $cmraw);
        // cm_info::create va chiamato DOPO require_login: carica dati filtrati per utente.
        $cm = \cm_info::create($cmraw);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);
        // Legge la reazione DOPO l'autenticazione e accetta solo reazioni attive.
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

        // Global anti-spam throttle: even if different reaction types are clicked,
        // a browser session must not be able to create an excessive burst of reaction
        // events while a single playback segment is still valid.
        $burstcount = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :bvtid AND userid = :buid AND sessionid = :bsid AND isdeleted = 0 " .
                "AND (notetype = '' OR notetype IS NULL) AND timecreated >= :bsince",
            [
                'bvtid'  => $videotrack->id,
                'buid'   => $USER->id,
                'bsid'   => $params['sessionid'],
                'bsince' => $now - 10,
            ]
        );
        if ($burstcount >= 10) {
            throw new \moodle_exception('error:reactionratelimit', 'mod_videotrack');
        }

        // Throttle anti-spam: rifiuta se esiste già una reazione identica (stesso utente,
        // stessa reazione) negli ultimi 3 secondi di orologio. Impedisce l'accumulo illimitato
        // di eventi dovuto ad automazioni o doppi click rapidi.
        $recentcount = $DB->count_records_select(
            'videotrack_reactev',
            'videotrackid = :vtid AND userid = :uid AND reactionid = :rid AND isdeleted = 0 AND timecreated >= :since',
            [
                'vtid'  => $videotrack->id,
                'uid'   => $USER->id,
                'rid'   => $reaction->id,
                'since' => $now - 3,
            ]
        );
        if ($recentcount > 0) {
            // Reazione duplicata: restituisce lo stato corrente senza salvare.
            // Non serve chiamare reaction_counts: il client non ha salvato nulla,
            // quindi il conteggio non è cambiato.
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
            'notetype'    => '',   // '' per reazioni standard (distingue da 'note' per le note personali).
            'isdeleted' => 0,
            'timecreated' => $now,
            'timemodified' => $now,
        ];
        $eventid = $DB->insert_record('videotrack_reactev', $record);
        // Log dell'evento nei log di Moodle.
        $event = reaction_saved::create([
            'objectid' => $eventid,
            'context'  => $context,
            'other'    => [
                'reactionlabel' => $reaction->label,
                'videotime'     => $videotime,
            ],
        ]);
        $event->trigger();
        // Legge reaction_counts UNA SOLA VOLTA subito dopo l'insert, poi chiama
        // refresh_completion. Evita la chiamata tripla: una qui, una in refresh_completion
        // internamente, e un'altra esplicita dopo.
        $summary = tracker::reaction_counts($videotrack->id, (int)$USER->id);
        $state   = tracker::refresh_completion($videotrack, $cm, (int)$USER->id);
        $completion = new \completion_info($course);
        $completion->update_state($cm, $state->iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE, (int)$USER->id);
        return [
            'reactioneventid' => $eventid,
            'uniquereactions' => $summary['uniquecount'],
            'iscompleted'     => (bool)$state->iscompleted,
            'warnings'        => [],
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'reactioneventid' => new external_value(PARAM_INT, 'Reaction event ID'),
            'uniquereactions' => new external_value(PARAM_INT, 'Unique reaction count'),
            'iscompleted' => new external_value(PARAM_BOOL, 'Completion status'),
            'warnings' => new external_warnings(),
        ]);
    }
}
