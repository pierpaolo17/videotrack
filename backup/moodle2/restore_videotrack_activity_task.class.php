<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videotrack/backup/moodle2/restore_videotrack_stepslib.php');

class restore_videotrack_activity_task extends restore_activity_task {
    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new restore_videotrack_activity_structure_step('videotrack_structure', 'videotrack.xml'));
    }

    public static function define_decode_contents() {
        return [new restore_decode_content('videotrack', ['intro'], 'videotrack')];
    }

    public static function define_decode_rules() {
        return [
            new restore_decode_rule('VIDEOTRACKVIEWBYID', '/mod/videotrack/view.php?id=$1', 'course_module'),
        ];
    }
}
