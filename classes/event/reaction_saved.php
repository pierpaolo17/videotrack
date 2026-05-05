<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a student submits a reaction while watching the video.
 */
class reaction_saved extends \core\event\base {
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack_reactev';
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
    }

    public static function get_name(): string {
        return get_string('event:reaction_saved', 'mod_videotrack');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' submitted reaction '{$this->other['reactionlabel']}' " .
               "at video time {$this->other['videotime']}s " .
               "in the videotrack activity with course module id '{$this->contextinstanceid}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/view.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack_reactev', 'restore' => 'videotrack_reactev'];
    }
}
