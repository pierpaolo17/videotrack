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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use mod_videotrack\local\integrity;

/**
 * Stores a bounded diagnostic integrity signal for the current student.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class save_integrity_event extends external_api {
    /** Same-signal server-side debounce window in seconds. */
    private const DUPLICATE_WINDOW_SECONDS = 10;

    /**
     * Return the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Browser playback session ID'),
            'eventtype' => new external_value(PARAM_ALPHANUMEXT, 'Diagnostic signal type'),
            'videotime' => new external_value(PARAM_FLOAT, 'Current video timestamp', VALUE_DEFAULT, 0.0),
        ]);
    }

    /**
     * Store one integrity signal when recording is enabled for the activity.
     *
     * @param int $cmid Course module id.
     * @param string $sessionid Browser session id.
     * @param string $eventtype Signal type.
     * @param float $videotime Current video timestamp.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        string $eventtype,
        float $videotime = 0.0
    ): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid',
            'sessionid',
            'eventtype',
            'videotime'
        ));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['sessionid'] = helper::validate_session_id($params['sessionid']);
        $params['eventtype'] = integrity::validate_event_type($params['eventtype']);
        $params['videotime'] = helper::validate_bounded_float(
            (float)$params['videotime'],
            'videotime',
            0.0,
            86400.0
        );

        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        helper::require_ajax_sesskey();
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $context = $loaded['context'];

        if (has_capability('mod/videotrack:viewreport', $context)) {
            return ['stored' => false, 'integrityeventid' => 0, 'warnings' => []];
        }
        if (empty($videotrack->integrityindicatorsenabled)) {
            return ['stored' => false, 'integrityeventid' => 0, 'warnings' => []];
        }

        $since = time() - self::DUPLICATE_WINDOW_SECONDS;
        $duplicate = $DB->record_exists_select(
            'videotrack_integrity',
            'cmid = :cmid AND userid = :userid AND sessionid = :sessionid ' .
                'AND eventtype = :eventtype AND timecreated >= :since',
            [
                'cmid' => (int)$cm->id,
                'userid' => (int)$USER->id,
                'sessionid' => $params['sessionid'],
                'eventtype' => $params['eventtype'],
                'since' => $since,
            ]
        );
        if ($duplicate) {
            return ['stored' => false, 'integrityeventid' => 0, 'warnings' => []];
        }

        $record = (object)[
            'videotrackid' => (int)$videotrack->id,
            'courseid' => (int)$videotrack->course,
            'cmid' => (int)$cm->id,
            'userid' => (int)$USER->id,
            'videoid' => (string)$videotrack->videoid,
            'sessionid' => $params['sessionid'],
            'eventtype' => $params['eventtype'],
            'videotime' => round((float)$params['videotime'], 3),
            'timecreated' => time(),
        ];
        $record->id = $DB->insert_record('videotrack_integrity', $record);

        return [
            'stored' => true,
            'integrityeventid' => (int)$record->id,
            'warnings' => [],
        ];
    }

    /**
     * Return the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'stored' => new external_value(PARAM_BOOL, 'Whether the signal was stored'),
            'integrityeventid' => new external_value(PARAM_INT, 'Stored signal id, or zero'),
            'warnings' => new external_warnings(),
        ]);
    }
}
