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

    /**
     * Rebuild derived state after all common activity restore steps have completed.
     *
     * This hook runs after Moodle restores course-module completion. Rebuilding here
     * prevents a completion row from the backup from reintroducing evidence that the
     * destination retention policy discarded.
     */
    public function after_restore() {
        global $CFG, $DB;

        if (!$this->get_setting_value('userinfo')) {
            return;
        }

        $videotrackid = (int)$this->get_activityid();
        $cmid = (int)$this->get_moduleid();
        if ($videotrackid <= 0 || $cmid <= 0) {
            return;
        }

        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid]);
        if (!$videotrack) {
            return;
        }

        $course = get_course((int)$videotrack->course);
        $cm = get_fast_modinfo($course)->get_cm($cmid);
        videotrack_recalculate_all_states($videotrackid, $cm);

        if ((int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC) {
            return;
        }

        if (!\mod_videotrack\local\completion_config::has_custom_rules($videotrack)) {
            return;
        }

        $completion = new completion_info($course);
        $userids = $DB->get_fieldset_select(
            'course_modules_completion',
            'DISTINCT userid',
            'coursemoduleid = :cmid AND userid > 0',
            ['cmid' => $cmid]
        );
        foreach ($userids as $userid) {
            $userid = (int)$userid;
            if (
                $DB->record_exists('videotrack_state', [
                    'videotrackid' => $videotrackid,
                    'userid' => $userid,
                ])
            ) {
                continue;
            }
            \mod_videotrack\local\tracker::update_moodle_completion_if_changed(
                $completion,
                $cm,
                false,
                $userid
            );
        }
    }
}
