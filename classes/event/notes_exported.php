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

defined('MOODLE_INTERNAL') || die();

/**
 * Fired when a teacher exports student personal notes from a VideoTrack report.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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



    protected function validate_data(): void {
        parent::validate_data();
        if (empty($this->objectid) || (int)$this->objectid <= 0) {
            throw new \coding_exception('The objectid must be the videotrack activity id.');
        }
        if (!array_key_exists('useridfilter', $this->other)) {
            throw new \coding_exception('The useridfilter value must be set in other.');
        }
        if (!array_key_exists('emailincluded', $this->other)) {
            throw new \coding_exception('The emailincluded value must be set in other.');
        }
        if (!array_key_exists('createdfrom', $this->other)) {
            throw new \coding_exception('The createdfrom value must be set in other.');
        }
        if (!array_key_exists('createdto', $this->other)) {
            throw new \coding_exception('The createdto value must be set in other.');
        }
    }

    public static function get_objectid_mapping(): array {
        return ['db' => 'videotrack', 'restore' => 'videotrack'];
    }

    public static function get_other_mapping(): array {
        return [
            'useridfilter' => ['db' => 'user', 'restore' => 'user'],
        ];
    }
}
