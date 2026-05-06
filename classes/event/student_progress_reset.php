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
        $segments = (int)($this->other['segments'] ?? 0);
        $states   = (int)($this->other['states'] ?? 0);
        $events   = (int)($this->other['events'] ?? 0);
        return "The user with id '{$this->userid}' reset VideoTrack data for user with id " .
            "'{$this->relateduserid}' in the activity with course module id '{$this->contextinstanceid}' " .
            "(deleted {$segments} segments, {$states} state records and {$events} interaction events).";
    }

    protected function validate_data(): void {
        parent::validate_data();
        if (empty($this->relateduserid)) {
            throw new \coding_exception('The relateduserid must be set for student_progress_reset events.');
        }
        foreach (['segments', 'states', 'events'] as $key) {
            if (!isset($this->other[$key])) {
                throw new \coding_exception('The ' . $key . ' count must be set in other.');
            }
        }
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/report.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack', 'restore' => 'videotrack'];
    }
}
