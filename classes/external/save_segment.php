<?php
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

class save_segment extends external_api {
    /** Maximum accepted duration from trusted activity configuration, in seconds. */
    private const MAX_DURATION_SECONDS = 86400;

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

    public static function execute(int $cmid, string $sessionid, float $videotimestart, float $videotimeend, int $wallclockstart,
            int $wallclockend, float $playbackrate, string $endreason, float $durationseconds = 0.0): array {
        global $DB, $USER;
        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'sessionid', 'videotimestart', 'videotimeend', 'wallclockstart', 'wallclockend', 'playbackrate', 'endreason', 'durationseconds'));
        if (\core_text::strlen($params['sessionid']) > 64) {
            throw new \invalid_parameter_exception('Invalid session ID');
        }
        $allowedendreasons = ['heartbeat', 'pause', 'seek', 'ended', 'beforeunload', 'pagehide', 'tab',
            'visibilitychange', 'reaction', 'note', 'interaction'];
        if (\core_text::strlen($params['endreason']) > 32 || !in_array($params['endreason'], $allowedendreasons, true)) {
            throw new \invalid_parameter_exception('Invalid segment end reason');
        }
        $cmraw  = get_coursemodule_from_id('videotrack', $params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);
        require_login($course, false, $cmraw);
        // cm_info::create va chiamato DOPO require_login: carica dati filtrati per utente.
        $cm = \cm_info::create($cmraw);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);

        // Non considerare attendibile né persistere durationseconds da una chiamata AJAX studente.
        // La durata inviata dal client può essere utile all'interfaccia, ma non deve
        // influenzare normalizzazione o completamento finché l'attività non dispone
        // di una durata attendibile salvata lato server.
        $knownduration = (float)($videotrack->durationseconds ?? 0);
        $normaliseduration = $knownduration > 0 ? min($knownduration, self::MAX_DURATION_SECONDS) : 0.0;
        $interval = tracker::normalise_interval((float)$params['videotimestart'], (float)$params['videotimeend'], $normaliseduration);
        if ($interval === null) {
            return [
                'accepted'             => false,
                'uniquecoveredseconds' => 0.0,
                'completionpercent'    => 0.0,
                'iscompleted'          => false,
                'intervaljson'         => '[]',
                'durationseconds'      => 0.0,
                'warnings'             => [],
            ];
        }
        $now    = time();
        // Clampa wallclock al server time (tollera 5s di clock skew client).
        $wstart = max(0, min($params['wallclockstart'], $now + 5));
        $wend   = max($wstart, min($params['wallclockend'],   $now + 5));

        // ── Validazione per l'integrità accademica lato server ───────────────────────────────
        // I wallclock inviati dal client sono conservati come dato diagnostico, ma
        // non vengono usati per decidere se accettare il segmento. La validazione
        // si basa solo sul tempo server trascorso dall'ultimo segmento della stessa
        // sessione e sull'heartbeat configurato.
        $videoduration = $interval[1] - $interval[0];
        $playbackrate  = max(0.25, min(4.0, (float)$params['playbackrate']));
        $heartbeat = (int)(get_config('mod_videotrack', 'heartbeatinterval') ?: 30);
        $heartbeat = max(5, min(300, $heartbeat));
        $lasttimecreated = $DB->get_field_sql(
            "SELECT MAX(timecreated)
               FROM {videotrack_seg}
              WHERE videotrackid = :vtid AND userid = :uid AND sessionid = :sid",
            ['vtid' => $videotrack->id, 'uid' => $USER->id, 'sid' => $params['sessionid']]
        );
        $isfirstsegment = empty($lasttimecreated);
        $serverspan = $isfirstsegment ? $heartbeat : max(0, $now - (int)$lasttimecreated);
        // B1 fix: il ternario '? 10 : 10' era dead code: entrambi i rami restituivano 10.
        // La tolleranza è 10 secondi per tutti i segmenti: sufficiente per assorbire
        // lo scarto tra browser e server senza aprire una finestra ampia nella
        // validazione dell'integrità accademica. I primi segmenti usano già
        // $serverspan = $heartbeat, quindi non richiedono una tolleranza maggiore.
        $servergrace = 10;
        $serverallowedvideo = max(2.0, ($serverspan + $servergrace) * $playbackrate);
        if ($videoduration > 2.0 && $videoduration > $serverallowedvideo) {
            // Segmento sospetto: rigettato silenziosamente senza registrare dettagli temporali comportamentali.
            return [
                'accepted'             => false,
                'uniquecoveredseconds' => 0.0,
                'completionpercent'    => 0.0,
                'iscompleted'          => false,
                'intervaljson'         => '[]',
                'durationseconds'      => 0.0,
                'warnings'             => [],
            ];
        }

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
            'playbackrate'   => $playbackrate,  // Già clampata a [0.25, 4.0] sopra.
            'endreason'      => $params['endreason'],
            'timecreated'    => $now,
        ];
        // Inserisce il segmento grezzo E aggiorna lo stato aggregato in un'unica
        // transazione atomica gestita da update_state. Se update_state fallisce,
        // il rollback rimuove anche il segmento appena inserito — nessun orfano.
        $segmentid = null;
        $state = tracker::update_state($videotrack, $cm, (int)$USER->id, $interval, $interval[1], $segment, $segmentid);

        // Logga solo azioni significative — non il heartbeat (genera troppi log).
        $loggable = ['pause', 'seek', 'ended', 'beforeunload', 'pagehide'];
        if (in_array($params['endreason'], $loggable, true)) {
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
        $completion->update_state($cm, $state->iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE, (int)$USER->id);
        return [
            'accepted'             => true,
            'uniquecoveredseconds' => (float)$state->uniquecoveredseconds,
            'completionpercent'    => (float)$state->completionpercent,
            'iscompleted'          => (bool)$state->iscompleted,
            'intervaljson'         => (string)($state->intervaljson ?? '[]'),
            'durationseconds'      => (float)($state->durationseconds ?? 0),
            'warnings'             => [],
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'accepted'             => new external_value(PARAM_BOOL,  'Whether the segment was accepted'),
            'uniquecoveredseconds' => new external_value(PARAM_FLOAT, 'Unique covered seconds'),
            'completionpercent'    => new external_value(PARAM_FLOAT, 'Computed completion percentage'),
            'iscompleted'          => new external_value(PARAM_BOOL,  'Whether completion threshold has been met'),
            'intervaljson'         => new external_value(PARAM_RAW,   'JSON array of watched intervals for the progress bar'),
            'durationseconds'      => new external_value(PARAM_FLOAT, 'Total video duration in seconds'),
            'warnings'             => new external_warnings(),
        ]);
    }
}
