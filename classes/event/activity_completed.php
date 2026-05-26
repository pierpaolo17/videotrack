<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Fired the first time a student's state transitions from incomplete to complete.
 * Allows other Moodle systems (badges, notifications, reports) to react.
 */
class activity_completed extends \core\event\base {
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack_state';
        $this->data['crud']        = 'u';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
    }

    public static function get_name(): string {
        return get_string('event:activity_completed', 'mod_videotrack');
    }

    public function get_description(): string {
        return "The user with id '{$this->userid}' completed the videotrack activity " .
               "with course module id '{$this->contextinstanceid}' " .
               "(coverage: {$this->other['completionpercent']}%, " .
               "unique seconds: {$this->other['uniquecoveredseconds']}).";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/view.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack_state', 'restore' => 'videotrack_state'];
    }
}
