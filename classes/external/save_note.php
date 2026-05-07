<?php
namespace mod_videotrack\external;

defined('MOODLE_INTERNAL') || die();


use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use context_module;
use mod_videotrack\local\tracker;

/**
 * External function: save a personal timestamped note for the current student.
 *
 * Notes are stored as reaction events with notetype='note' and notetext set.
 * reactionid is set to 0 (no associated reaction definition).
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_note extends external_api {

    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid'        => new external_value(PARAM_INT,   'Course module ID'),
            'sessionid'   => new external_value(PARAM_ALPHANUMEXT, 'Session UUID'),
            'videotime'   => new external_value(PARAM_FLOAT, 'Video timestamp in seconds'),
            'notetext'    => new external_value(PARAM_TEXT,  'Note text (max 2000 chars)'),
            'playbackrate'=> new external_value(PARAM_FLOAT, 'Playback rate at time of note', VALUE_DEFAULT, 1.0),
        ]);
    }

    public static function execute(int $cmid, string $sessionid, float $videotime,
            string $notetext, float $playbackrate = 1.0): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid', 'sessionid', 'videotime', 'notetext', 'playbackrate'
        ));

        $cmraw  = get_coursemodule_from_id('videotrack', (int)$params['cmid'], 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);
        require_login($course, false, $cmraw);
        // cm_info::create va chiamato DOPO require_login: carica dati filtrati per utente.
        $cm      = \cm_info::create($cmraw);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);

        // Verifica che le note siano abilitate per questa istanza.
        if (empty($videotrack->studentnotesenabled)) {
            throw new \moodle_exception('studentnotesdisabled', 'mod_videotrack');
        }

        // Sanitize: tronca a 2000 caratteri per evitare abusi.
        $text = \core_text::substr(trim($params['notetext']), 0, 2000);
        if ($text === '') {
            throw new \moodle_exception('invaliddata', 'error');
        }

        // Sanitize videotime: clamp a [0, durationseconds].
        // Impedisce note a timestamp negativi o oltre la fine del video.
        $rawtime  = (float)$params['videotime'];
        $duration = (float)($videotrack->durationseconds ?? 0);
        $videotime = max(0.0, $duration > 0 ? min($rawtime, $duration) : $rawtime);

        if (!tracker::has_recent_playback($videotrack->id, (int)$USER->id, $params['sessionid'], $videotime)) {
            throw new \moodle_exception('error:playbackrequired', 'mod_videotrack');
        }

        // Global note rate limit: max 5 notes every 10 seconds per user/activity.
        $recentnotes = $DB->count_records_select(
            'videotrack_reactev',
            "videotrackid = :vtid AND userid = :userid AND notetype = 'note' AND isdeleted = 0 AND timecreated > :since",
            [
                'vtid' => $videotrack->id,
                'userid' => (int)$USER->id,
                'since' => time() - 10,
            ]
        );
        if ($recentnotes >= 5) {
            throw new \moodle_exception('error:notesratelimit', 'mod_videotrack');
        }

        $now = time();
        $record = (object)[
            'videotrackid' => $videotrack->id,
            'courseid'     => $videotrack->course,
            'cmid'         => $cm->id,
            'userid'       => (int)$USER->id,
            'videoid'      => $videotrack->videoid,
            'sessionid'    => $params['sessionid'],
            'reactionid'   => 0,
            'reactionkey'  => 'note',
            'reactionlabel'=> get_string('studentnote_label', 'mod_videotrack'),
            'reactiondesc' => '',
            'notetext'     => $text,
            'notetype'     => 'note',
            'videotime'    => round($videotime, 3),
            'playbackrate' => max(0.25, min(4.0, (float)$params['playbackrate'])),
            'isdeleted'    => 0,
            'timecreated'  => $now,
            'timemodified' => $now,
        ];
        $record->id = $DB->insert_record('videotrack_reactev', $record);

        // Trigger evento Moodle (riutilizza reaction_saved — stesso tipo di record).
        $event = \mod_videotrack\event\reaction_saved::create([
            'objectid' => $record->id,
            'context'  => $context,
            'userid'   => (int)$USER->id,
            'other'    => [
                           'reactionid'    => 0,
                           'reactionlabel' => get_string('studentnote_label', 'mod_videotrack'),
                           'videotime'     => $record->videotime,
                           'notetype'      => 'note',
                          ],
        ]);
        $event->trigger();

        return [
            'noteeventid' => (int)$record->id,
            'warnings'    => [],
        ];
    }

    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'noteeventid' => new external_value(PARAM_INT,  'ID of the saved note event'),
            'warnings'    => new external_warnings(),
        ]);
    }
}
