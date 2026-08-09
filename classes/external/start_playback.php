<?php
// This file is part of Moodle - https://moodle.org/.
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

/**
 * Opens a server-authoritative playback-credit window.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class start_playback extends external_api {
    /** Maximum supported video duration in seconds. */
    private const MAX_DURATION_SECONDS = 86400;

    /**
     * Defines the external parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'sessionid' => new external_value(PARAM_ALPHANUMEXT, 'Browser session ID'),
            'requestid' => new external_value(PARAM_ALPHANUMEXT, 'Idempotency request ID'),
            'videotime' => new external_value(PARAM_FLOAT, 'Current video time in seconds'),
        ]);
    }

    /**
     * Opens a playback-credit window without granting watched time.
     *
     * @param int $cmid Course module ID.
     * @param string $sessionid Browser session ID.
     * @param string $requestid Idempotency request ID.
     * @param float $videotime Current video time.
     * @return array
     */
    public static function execute(
        int $cmid,
        string $sessionid,
        string $requestid,
        float $videotime
    ): array {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact(
            'cmid',
            'sessionid',
            'requestid',
            'videotime'
        ));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $params['sessionid'] = helper::validate_session_id($params['sessionid']);
        $params['requestid'] = helper::validate_request_id($params['requestid']);
        $params['videotime'] = helper::validate_bounded_float(
            (float)$params['videotime'],
            'videotime',
            0.0,
            self::MAX_DURATION_SECONDS
        );

        helper::require_ajax_sesskey();
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        $videotrack = $loaded['videotrack'];
        $cm = $loaded['cm'];
        $duration = max(0.0, (float)($videotrack->durationseconds ?? 0));
        $videotime = $duration > 0
            ? min((float)$params['videotime'], $duration)
            : (float)$params['videotime'];

        $result = tracker::begin_playback(
            $videotrack,
            $cm,
            (int)$USER->id,
            $params['sessionid'],
            $params['requestid'],
            $videotime,
            (int)round(microtime(true) * 1000)
        );

        return [
            'accepted' => true,
            'requestreplayed' => !empty($result['requestreplayed']),
            'warnings' => [],
        ];
    }

    /**
     * Defines the external return structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'accepted' => new external_value(PARAM_BOOL, 'Whether the handshake was accepted'),
            'requestreplayed' => new external_value(PARAM_BOOL, 'Whether an earlier response was replayed'),
            'warnings' => new external_warnings(),
        ]);
    }
}
