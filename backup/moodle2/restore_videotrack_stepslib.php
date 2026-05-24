<?php
// This file is part of Moodle - http://moodle.org/

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
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record('videotrack', $data);
        $this->apply_activity_instance($newitemid);
        $this->set_mapping('videotrack', $oldid, $newitemid);
    }

    protected function process_videotrack_reaction($data) {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        $data->videotrackid = $this->get_new_parentid('videotrack');
        $newitemid = $DB->insert_record('videotrack_react', $data);
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
        if (!empty($data->reactionid)) {
            $oldreactionid = (int)$data->reactionid;
            $mappedreactionid = $this->get_mappingid('videotrack_react', $oldreactionid);
            if (empty($mappedreactionid)) {
                // Very defensive fallback: normally all referenced reactions are backed up.
                // If a malformed/partial backup omits one, preserve the historical relation
                // by creating a hidden placeholder instead of storing reactionid = 0.
                $this->log('mod_videotrack restore: missing reaction mapping for old reaction id ' . $oldreactionid .
                    '; creating a hidden placeholder reaction.', backup::LOG_WARNING);
                $now = time();
                $placeholder = (object)[
                    'videotrackid' => $data->videotrackid,
                    'reactionkey' => !empty($data->reactionkey) ? $data->reactionkey : ('restored_' . $oldreactionid),
                    'label' => !empty($data->reactionlabel) ? $data->reactionlabel : 'Restored reaction',
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

        // Ricrea il grade item nel gradebook del corso di destinazione.
        // Senza questa chiamata il voto non appare nel registro valutatore dopo un restore.
        if (!empty($videotrackid)) {
            $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid]);
            if ($videotrack && !empty($videotrack->grade)) {
                require_once($CFG->dirroot . '/mod/videotrack/lib.php');
                require_once($CFG->libdir . '/gradelib.php');
                // cmidnumber è necessario per grade_update; lo recuperiamo dal cm.
                $cm = get_coursemodule_from_instance('videotrack', $videotrackid,
                    $videotrack->course, false, IGNORE_MISSING);
                $videotrack->cmidnumber = $cm ? $cm->idnumber : '';
                videotrack_grade_item_update($videotrack);
            }
        }
    }
}
