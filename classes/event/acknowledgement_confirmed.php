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
 * Fired when a learner confirms the current acknowledgement statement.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class acknowledgement_confirmed extends \core\event\base {
    /**
     * Initialise event metadata.
     */
    protected function init(): void {
        $this->data['objecttable'] = 'videotrack_acknowledge';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
    }

    /**
     * Return the event display name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event:acknowledgement_confirmed', 'mod_videotrack');
    }

    /**
     * Return a human-readable event description.
     *
     * @return string
     */
    public function get_description(): string {
        return "The user with id '{$this->userid}' confirmed the current acknowledgement statement " .
            "for the videotrack activity with course module id '{$this->contextinstanceid}'.";
    }

    /**
     * Return the URL associated with this event.
     *
     * @return \moodle_url
     */
    public function get_url(): \moodle_url {
        return new \moodle_url('/mod/videotrack/view.php', ['id' => $this->contextinstanceid]);
    }

    /**
     * Return object id mapping information for backup and restore.
     *
     * @return array
     */
    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack_acknowledge', 'restore' => 'videotrack_acknowledge'];
    }
}
