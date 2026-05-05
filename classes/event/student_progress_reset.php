<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a teacher resets a student's VideoTrack data for an activity.
 */
class student_progress_reset extends \core\event\base {
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack';
        $this->data['crud']        = 'd';
        $this->data['edulevel']    = self::LEVEL_TEACHING;
    }

    public static function get_name(): string {
        return get_string('event:student_progress_reset', 'mod_videotrack');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' reset VideoTrack data for user with id " .
            "'{$this->relateduserid}' in the activity with course module id '{$this->contextinstanceid}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/report.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack', 'restore' => 'videotrack'];
    }
}
