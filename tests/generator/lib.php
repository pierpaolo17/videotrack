<?php
// This file is part of Moodle - https://moodle.org/.
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

/**
 * VideoTrack test data generator.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generator used by PHPUnit and Behat fixtures.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videotrack_generator extends testing_module_generator {
    /**
     * Create a VideoTrack instance with deterministic test-safe defaults.
     *
     * @param array|stdClass|null $record Instance fields.
     * @param array|null $options Course-module options.
     * @return stdClass Created instance with cmid.
     */
    public function create_instance($record = null, ?array $options = null) {
        $record = (array)$record + [
            'videosource' => 'youtube',
            'youtubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'durationseconds' => 120,
            'reactionsenabled' => 0,
            'studentnotesenabled' => 0,
            'bookmarksenabled' => 0,
        ];

        return parent::create_instance($record, (array)$options);
    }
}
