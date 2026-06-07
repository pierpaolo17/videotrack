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

/**
 * Restores VideoTrack activity data and related user records.
 */
class restore_videotrack_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the restore structure for activity, reaction, segment, state and event records.
     *
     * @return restore_path_element[]
     */
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

    /**
     * Restore the main VideoTrack activity record.
     *
     * @param array|stdClass $data Restored backup data.
     */
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

    /**
     * Restore a configured reaction.
     *
     * @param array|stdClass $data Restored backup data.
     */
    protected function process_videotrack_reaction($data) {
        global $DB;
        $data = (object)$data;
        $oldid = (int)$data->id;
        $icontype = isset($data->icontype) ? clean_param($data->icontype, PARAM_ALPHANUMEXT) : 'emoji';
        if (!in_array($icontype, ['emoji', 'file', 'fa', 'text'], true)) {
            $icontype = 'emoji';
        }
        $record = (object)[
            'videotrackid' => $this->get_new_parentid('videotrack'),
            'reactionkey' => isset($data->reactionkey) ? clean_param($data->reactionkey, PARAM_ALPHANUMEXT) : '',
            'label' => isset($data->label) ? clean_param($data->label, PARAM_TEXT) : '',
            'description' => isset($data->description) ? clean_param($data->description, PARAM_TEXT) : '',
            'icontype' => $icontype,
            'iconvalue' => isset($data->iconvalue) ? clean_param($data->iconvalue, PARAM_TEXT) : '',
            'requiredforcompletion' => empty($data->requiredforcompletion) ? 0 : 1,
            'sortorder' => isset($data->sortorder) ? (int)$data->sortorder : 0,
            'isdeleted' => empty($data->isdeleted) ? 0 : 1,
            'timecreated' => isset($data->timecreated) ? (int)$data->timecreated : time(),
            'timemodified' => isset($data->timemodified) ? (int)$data->timemodified : time(),
        ];
        $newitemid = $DB->insert_record('videotrack_react', $record);
        $this->set_mapping('videotrack_react', $oldid, $newitemid, true);
    }

    /**
     * Restore one user playback segment.
     *
     * @param array|stdClass $data Restored backup data.
     */
    protected function process_videotrack_segment($data) {
        global $DB;
        $data = (object)$data;
        $oldid = isset($data->id) ? (int)$data->id : 0;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = $this->get_restored_cmid();
        if (!empty($data->userid) && (int)$data->userid > 0) {
            $mappeduserid = $this->get_mappingid('user', $data->userid);
            if (empty($mappeduserid)) {
                return;
            }
            $data->userid = $mappeduserid;
        }
        // Negative user ids are anonymised aggregate records: preserve them as non-user data.
        $record = (object)[
            'videotrackid' => (int)$data->videotrackid,
            'courseid' => (int)$data->courseid,
            'cmid' => (int)$data->cmid,
            'userid' => isset($data->userid) ? (int)$data->userid : 0,
            'videoid' => isset($data->videoid) ? clean_param($data->videoid, PARAM_ALPHANUMEXT) : '',
            'sessionid' => isset($data->sessionid) ? clean_param($data->sessionid, PARAM_ALPHANUMEXT) : '',
            'wallclockstart' => isset($data->wallclockstart) ? (int)$data->wallclockstart : 0,
            'wallclockend' => isset($data->wallclockend) ? (int)$data->wallclockend : 0,
            'videotimestart' => isset($data->videotimestart) ? (float)$data->videotimestart : 0.0,
            'videotimeend' => isset($data->videotimeend) ? (float)$data->videotimeend : 0.0,
            'playbackrate' => isset($data->playbackrate) ? (float)$data->playbackrate : 1.0,
            'endreason' => isset($data->endreason) ? clean_param($data->endreason, PARAM_ALPHANUMEXT) : 'unknown',
            'timecreated' => isset($data->timecreated) ? (int)$data->timecreated : time(),
        ];
        $newitemid = $DB->insert_record('videotrack_seg', $record);
        if ($oldid > 0) {
            $this->set_mapping('videotrack_seg', $oldid, $newitemid, true);
        }
    }

    /**
     * Restore one persisted user playback state.
     *
     * @param array|stdClass $data Restored backup data.
     */
    protected function process_videotrack_state($data) {
        global $DB;
        $data = (object)$data;
        $oldid = isset($data->id) ? (int)$data->id : 0;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = $this->get_restored_cmid();
        if (!empty($data->userid) && (int)$data->userid > 0) {
            $mappeduserid = $this->get_mappingid('user', $data->userid);
            if (empty($mappeduserid)) {
                return;
            }
            $data->userid = $mappeduserid;
        }
        // Negative user ids are anonymised aggregate records: preserve them as non-user data.
        $record = (object)[
            'videotrackid' => (int)$data->videotrackid,
            'courseid' => (int)$data->courseid,
            'cmid' => (int)$data->cmid,
            'userid' => isset($data->userid) ? (int)$data->userid : 0,
            'videoid' => isset($data->videoid) ? clean_param($data->videoid, PARAM_ALPHANUMEXT) : '',
            'lastposition' => isset($data->lastposition) ? (float)$data->lastposition : 0.0,
            'durationseconds' => isset($data->durationseconds) ? (float)$data->durationseconds : 0.0,
            'uniquecoveredseconds' => isset($data->uniquecoveredseconds) ? (float)$data->uniquecoveredseconds : 0.0,
            'completionpercent' => isset($data->completionpercent) ? (float)$data->completionpercent : 0.0,
            'intervaljson' => self::normalise_interval_json($data->intervaljson ?? null),
            'iscompleted' => empty($data->iscompleted) ? 0 : 1,
            'timemodified' => isset($data->timemodified) ? (int)$data->timemodified : time(),
            'timecreated' => isset($data->timecreated) ? (int)$data->timecreated : time(),
        ];
        $newitemid = $DB->insert_record('videotrack_state', $record);
        if ($oldid > 0) {
            $this->set_mapping('videotrack_state', $oldid, $newitemid, true);
        }
    }

    /**
     * Restore one user reaction or note event.
     *
     * @param array|stdClass $data Restored backup data.
     */
    protected function process_videotrack_reactionevent($data) {
        global $DB;
        $data = (object)$data;
        $oldid = isset($data->id) ? (int)$data->id : 0;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $data->courseid = $this->get_courseid();
        $data->cmid = $this->get_restored_cmid();
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
                    'reactionkey' => !empty($data->reactionkey)
                        ? clean_param($data->reactionkey, PARAM_ALPHANUMEXT)
                        : ('restored_' . $oldreactionid),
                    'label' => !empty($data->reactionlabel)
                        ? clean_param($data->reactionlabel, PARAM_TEXT)
                        : get_string('restore_placeholder_reaction', 'mod_videotrack'),
                    'description' => !empty($data->reactiondesc)
                        ? clean_param($data->reactiondesc, PARAM_TEXT)
                        : '',
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
        $record = (object)[
            'videotrackid' => (int)$data->videotrackid,
            'courseid' => (int)$data->courseid,
            'cmid' => (int)$data->cmid,
            'userid' => isset($data->userid) ? (int)$data->userid : 0,
            'videoid' => isset($data->videoid) ? clean_param($data->videoid, PARAM_ALPHANUMEXT) : '',
            'sessionid' => isset($data->sessionid) ? clean_param($data->sessionid, PARAM_ALPHANUMEXT) : '',
            'reactionid' => isset($data->reactionid) ? (int)$data->reactionid : 0,
            'reactionkey' => isset($data->reactionkey) ? clean_param($data->reactionkey, PARAM_ALPHANUMEXT) : '',
            'reactionlabel' => isset($data->reactionlabel) ? clean_param($data->reactionlabel, PARAM_TEXT) : '',
            'reactiondesc' => isset($data->reactiondesc) ? clean_param($data->reactiondesc, PARAM_TEXT) : '',
            'notetext' => isset($data->notetext) ? clean_param($data->notetext, PARAM_TEXT) : '',
            'notetype' => isset($data->notetype) ? clean_param($data->notetype, PARAM_ALPHANUMEXT) : '',
            'videotime' => isset($data->videotime) ? (float)$data->videotime : 0.0,
            'playbackrate' => isset($data->playbackrate) ? (float)$data->playbackrate : 1.0,
            'isdeleted' => empty($data->isdeleted) ? 0 : 1,
            'timecreated' => isset($data->timecreated) ? (int)$data->timecreated : time(),
            'timemodified' => isset($data->timemodified) ? (int)$data->timemodified : time(),
        ];
        $newitemid = $DB->insert_record('videotrack_reactev', $record);
        if ($oldid > 0) {
            $this->set_mapping('videotrack_reactev', $oldid, $newitemid, true);
        }
        if ($transaction !== null) {
            $transaction->allow_commit();
        }
    }

    /**
     * Normalise restored interval JSON before storing it again.
     *
     * Backup data is trusted only structurally. This keeps the Moodle restore
     * path aligned with the runtime tracker normalisation and prevents invalid
     * or unbounded JSON from being persisted in videotrack_state.
     *
     * @param mixed $json Raw value from the backup file.
     * @return string Canonical JSON encoded interval list.
     */
    private static function normalise_interval_json($json): string {
        $intervals = \mod_videotrack\local\tracker::decode_intervals(is_string($json) ? $json : '');
        $intervals = \mod_videotrack\local\tracker::merge_intervals($intervals);
        return \mod_videotrack\local\tracker::encode_intervals($intervals);
    }

    /**
     * Return the new course module id created by the restore task.
     *
     * @return int
     */
    protected function get_restored_cmid(): int {
        return (int)$this->task->get_moduleid();
    }

    /**
     * Restore related files and recreate the grade item after records are restored.
     */
    protected function after_execute() {
        global $DB, $CFG;

        $this->add_related_files('mod_videotrack', 'intro', null);
        $this->add_related_files('mod_videotrack', 'reactionicon', 'videotrack_react');
        $this->add_related_files('mod_videotrack', 'videocontent', null);
        $this->add_related_files('mod_videotrack', 'subtitles', null);
        $this->add_related_files('mod_videotrack', 'posterimage', null);

        $videotrackid = $this->get_new_parentid('videotrack');

        // Recreate the grade item in the destination course gradebook.
        // Without this call, the grade does not appear in the grader report after restore.
        if (!empty($videotrackid)) {
            $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid]);
            if ($videotrack && !empty($videotrack->grade)) {
                require_once($CFG->dirroot . '/mod/videotrack/lib.php');
                require_once($CFG->libdir . '/gradelib.php');
                // Cmidnumber is required by grade_update; retrieve it from the course module.
                $cm = get_coursemodule_from_instance(
                    'videotrack',
                    $videotrackid,
                    $videotrack->course,
                    false,
                    IGNORE_MISSING
                );
                $videotrack->cmidnumber = $cm ? $cm->idnumber : '';
                videotrack_grade_item_update($videotrack);
            }
        }
    }
}
