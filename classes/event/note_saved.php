<?php
/**
 * VideoTrack activity module.
 *
 * @package   mod_videotrack
 * @copyright 2026 SICS, Universita degli Studi della Tuscia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a student saves a personal timestamped note while watching the video.
 *
 * Distinct from reaction_saved (which logs reaction button clicks) so that
 * Moodle logs and reports can differentiate between reactions and notes.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class note_saved extends \core\event\base {
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack_reactev';
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
    }

    public static function get_name(): string {
        return get_string('event:note_saved', 'mod_videotrack');
    }

    public function get_description(): string {
        $videotime = $this->other['videotime'] ?? 0;
        return "The user with id '{$this->userid}' saved a personal note " .
               "at video time {$videotime}s " .
               "in the videotrack activity with course module id '{$this->contextinstanceid}'.";
    }

    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/view.php', ['id' => $this->contextinstanceid]);
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack_reactev', 'restore' => 'videotrack_reactev'];
    }
}
