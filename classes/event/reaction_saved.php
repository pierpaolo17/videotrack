<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a student submits a reaction while watching the video.
 *
 * @package    mod_videotrack
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        $videotime = $this->other['videotime'] ?? 0;
        $reactionlabel = $this->other['reactionlabel'] ??
            get_string('unknownreaction', 'mod_videotrack');

        return "The user with id '{$this->userid}' submitted reaction '{$reactionlabel}' " .
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
