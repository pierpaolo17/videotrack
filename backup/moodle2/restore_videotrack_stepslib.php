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

class restore_videotrack_activity_structure_step extends restore_activity_structure_step {
    protected function define_structure() {
        $paths = [
            new restore_path_element('videotrack', '/activity/videotrack'),
            new restore_path_element('videotrack_reaction', '/activity/videotrack/reactions/reaction'),
        ];

        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('videotrack_segment', '/activity/videotrack/segments/segment');
            $paths[] = new restore_path_element('videotrack_state', '/activity/videotrack/states/state');
            $paths[] = new restore_path_element('videotrack_reactionevent', '/activity/videotrack/reactionevents/reactionevent');
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_videotrack($data) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('videotrack', videotrack_whitelist_record($data));
        $this->apply_activity_instance($newitemid);
        $this->set_mapping('videotrack', $oldid, $newitemid);
    }

    protected function process_videotrack_reaction($data) {
        global $DB;
        $data = (object)$data;
        $oldid = (int)$data->id;
        $record = (object)[
            'videotrackid' => $this->get_new_parentid('videotrack'),
            'label' => isset($data->label) ? clean_param($data->label, PARAM_TEXT) : '',
            'emoji' => isset($data->emoji) ? clean_param($data->emoji, PARAM_TEXT) : '',
            'sortorder' => isset($data->sortorder) ? (int)$data->sortorder : 0,
            'isdeleted' => empty($data->isdeleted) ? 0 : 1,
            'timecreated' => isset($data->timecreated) ? (int)$data->timecreated : time(),
            'timemodified' => isset($data->timemodified) ? (int)$data->timemodified : time(),
        ];
        $newitemid = $DB->insert_record('videotrack_react', $record);
        $this->set_mapping('videotrack_react', $oldid, $newitemid, true);
    }

    protected function process_videotrack_segment($data) {
        global $DB;
        $data = (object)$data;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = 0;
        if (!empty($data->userid) && (int)$data->userid > 0) {
            $mappeduserid = $this->get_mappingid('user', $data->userid);
            if (empty($mappeduserid)) {
                return;
            }
            $data->userid = $mappeduserid;
        }
        // Negative user ids are anonymised aggregate records: preserve them as non-user data.
        $DB->insert_record('videotrack_seg', $data);
    }

    protected function process_videotrack_state($data) {
        global $DB;
        $data = (object)$data;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = 0;
        if (!empty($data->userid) && (int)$data->userid > 0) {
            $mappeduserid = $this->get_mappingid('user', $data->userid);
            if (empty($mappeduserid)) {
                return;
            }
            $data->userid = $mappeduserid;
        }
        // Negative user ids are anonymised aggregate records: preserve them as non-user data.
        $DB->insert_record('videotrack_state', $data);
    }

    protected function process_videotrack_reactionevent($data) {
        global $DB;
        $data = (object)$data;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = 0;
        if (!empty($data->userid) && (int)$data->userid > 0) {
            $mappeduserid = $this->get_mappingid('user', $data->userid);
            if (empty($mappeduserid)) {
                return;
            }
            $data->userid = $mappeduserid;
        }
        // Negative user ids are anonymised aggregate records: preserve them as non-user data.
        $transaction = null;
        if (!empty($data->reactionid)) {
            $oldreactionid = (int)$data->reactionid;
            $mappedreactionid = $this->get_mappingid('videotrack_react', $oldreactionid);
            if (empty($mappedreactionid)) {
                $transaction = $DB->start_delegated_transaction();
                // Very defensive fallback: normally all referenced reactions are backed up.
                // If a malformed/partial backup omits one, preserve the historical relation
                // by creating a hidden placeholder instead of storing reactionid = 0.
                $this->log(get_string('restore_missing_reaction_mapping', 'mod_videotrack', $oldreactionid), backup::LOG_WARNING);
                $now = time();
                $placeholder = (object)[
                    'videotrackid' => $data->videotrackid,
                    'reactionkey' => !empty($data->reactionkey) ? $data->reactionkey : ('restored_' . $oldreactionid),
                    'label' => !empty($data->reactionlabel)
                        ? $data->reactionlabel
                        : get_string('restore_placeholder_reaction', 'mod_videotrack'),
                    'description' => $data->reactiondesc ?? '',
                    'icontype' => 'emoji',
                    'iconvalue' => '',
                    'requiredforcompletion' => 0,
                    'sortorder' => 9999,
                    'isdeleted' => 1,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ];
                $mappedreactionid = $DB->insert_record('videotrack_react', $placeholder);
                $this->set_mapping('videotrack_react', $oldreactionid, $mappedreactionid, true);
            }
            $data->reactionid = $mappedreactionid;
        }
        $DB->insert_record('videotrack_reactev', $data);
        if ($transaction !== null) {
            $transaction->allow_commit();
        }
    }

    protected function after_execute() {
        global $DB, $CFG;

        $this->add_related_files('mod_videotrack', 'intro',        null);
        $this->add_related_files('mod_videotrack', 'reactionicon', 'videotrack_react');
        $this->add_related_files('mod_videotrack', 'videocontent', null);
        $this->add_related_files('mod_videotrack', 'subtitles',    null);
        $this->add_related_files('mod_videotrack', 'posterimage',  null);

        $cmid         = $this->task->get_moduleid();
        $videotrackid = $this->get_new_parentid('videotrack');

        if (!empty($videotrackid) && !empty($cmid)) {
            $DB->set_field('videotrack_seg',    'cmid', $cmid, ['videotrackid' => $videotrackid]);
            $DB->set_field('videotrack_state',  'cmid', $cmid, ['videotrackid' => $videotrackid]);
            $DB->set_field('videotrack_reactev','cmid', $cmid, ['videotrackid' => $videotrackid]);
        }

        // Recreate the grade item in the destination course gradebook.
        // Without this call, the grade does not appear in the grader report after restore.
        if (!empty($videotrackid)) {
            $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid]);
            if ($videotrack && !empty($videotrack->grade)) {
                require_once($CFG->dirroot . '/mod/videotrack/lib.php');
                require_once($CFG->libdir . '/gradelib.php');
                // cmidnumber is required by grade_update; retrieve it from the course module.
                $cm = get_coursemodule_from_instance('videotrack', $videotrackid,
                    $videotrack->course, false, IGNORE_MISSING);
                $videotrack->cmidnumber = $cm ? $cm->idnumber : '';
                videotrack_grade_item_update($videotrack);
            }
        }
    }
}
