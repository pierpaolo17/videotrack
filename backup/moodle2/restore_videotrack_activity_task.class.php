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

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotrack/backup/moodle2/restore_videotrack_stepslib.php');

/**
 * Defines the restore task for VideoTrack activities.
 */
class restore_videotrack_activity_task extends restore_activity_task {
    /**
     * Define restore settings for the activity.
     */
    protected function define_my_settings() {
    }

    /**
     * Define restore steps for the activity.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_videotrack_activity_structure_step('videotrack_structure', 'videotrack.xml'));
    }

    /**
     * Define content fields that need link decoding during restore.
     *
     * @return restore_decode_content[]
     */
    public static function define_decode_contents() {
        return [new restore_decode_content('videotrack', ['intro'], 'videotrack')];
    }

    /**
     * Define restore link decoding rules for VideoTrack activity URLs.
     *
     * @return restore_decode_rule[]
     */
    public static function define_decode_rules() {
        return [
            new restore_decode_rule('VIDEOTRACKVIEWBYID', '/mod/videotrack/view.php?id=$1', 'course_module'),
        ];
    }
}
