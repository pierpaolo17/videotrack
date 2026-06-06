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

namespace mod_videotrack\event;

/**
 * Fired when a student submits a reaction while watching the video.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reaction_saved extends \core\event\base {
    /**
     * Initialise event metadata.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack_reactev';
        $this->data['crud']        = 'c';
        $this->data['edulevel']    = self::LEVEL_PARTICIPATING;
    }

    /**
     * Return the event display name.
     *
     * @return string Event name.
     */
    public static function get_name(): string {
        return get_string('event:reaction_saved', 'mod_videotrack');
    }

    /**
     * Return a human-readable event description.
     *
     * @return string Event description.
     */
    public function get_description(): string {
        $videotime = $this->other['videotime'] ?? 0;
        $reactionlabel = $this->other['reactionlabel'] ??
            get_string('unknownreaction', 'mod_videotrack');

        return "The user with id '{$this->userid}' submitted reaction '{$reactionlabel}' " .
               "at video time {$videotime}s " .
               "in the videotrack activity with course module id '{$this->contextinstanceid}'.";
    }

    /**
     * Return the URL associated with this event.
     *
     * @return \moodle_url Event URL.
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/view.php', ['id' => $this->contextinstanceid]);
    }

    /**
     * Return object id mapping information for backup and restore.
     *
     * @return array Mapping information.
     */
    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack_reactev', 'restore' => 'videotrack_reactev'];
    }
}
