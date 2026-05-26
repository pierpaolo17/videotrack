<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a teacher exports student personal notes from a VideoTrack report.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notes_exported extends \core\event\base {
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack';
        $this->data['crud']        = 'r';
        $this->data['edulevel']    = self::LEVEL_TEACHING;
    }

    public static function get_name(): string {
        return get_string('event:notes_exported', 'mod_videotrack');
    }

    public function get_description(): string {
        $useridfilter = $this->other['useridfilter'] ?? 0;
        return "The user with id '{$this->userid}' exported personal notes " .
            "from the videotrack activity with course module id '{$this->contextinstanceid}' " .
            "using user filter '{$useridfilter}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/report.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack', 'restore' => 'videotrack'];
    }
}
