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
use mod_videotrack\event\bookmark_deleted;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

/**
 * Soft-deletes a private bookmark owned by the current user.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_bookmark extends external_api {
    /**
     * Returns the external function parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'cmid' => new external_value(PARAM_INT, 'Course module ID'),
            'bookmarkeventid' => new external_value(PARAM_INT, 'Private bookmark event ID'),
        ]);
    }

    /**
     * Delete a private bookmark.
     *
     * @param int $cmid Course module id.
     * @param int $bookmarkeventid Bookmark event id.
     * @return array
     */
    public static function execute(int $cmid, int $bookmarkeventid): array {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), compact('cmid', 'bookmarkeventid'));
        $params['cmid'] = helper::validate_positive_id((int)$params['cmid'], 'cmid');
        $bookmarkeventid = helper::validate_positive_id((int)$params['bookmarkeventid'], 'bookmarkeventid');
        helper::require_ajax_sesskey();
        $loaded = helper::load_and_validate_context((int)$params['cmid']);
        $videotrack = $loaded['videotrack'];
        $context = $loaded['context'];
        $bookmark = $DB->get_record('videotrack_reactev', [
            'id' => $bookmarkeventid,
            'userid' => $USER->id,
            'videotrackid' => $videotrack->id,
            'notetype' => 'bookmark',
        ], '*', MUST_EXIST);
        if (empty($bookmark->isdeleted)) {
            $bookmark->isdeleted = 1;
            $bookmark->timemodified = time();
            $DB->update_record('videotrack_reactev', $bookmark);
            bookmark_deleted::create([
                'objectid' => $bookmark->id,
                'context' => $context,
            ])->trigger();
        }
        return ['deleted' => true, 'warnings' => []];
    }

    /**
     * Returns the external function result structure.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'deleted' => new external_value(PARAM_BOOL, 'Deleted'),
            'warnings' => new external_warnings(),
        ]);
    }
}
