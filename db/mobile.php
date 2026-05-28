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

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Mobile app support for mod_videotrack.
 *
 * The YouTube IFrame Player API requires a full browser environment and cannot
 * run inside the app's native WebView without the full Moodle JS stack.
 * We therefore declare the plugin as a "site plugin" that opens view.php in
 * the app's in-app browser, which provides a complete browser environment
 * with full JS support.
 *
 * @see https://moodledev.io/general/app/development/plugins-development-guide
 */

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_videotrack' => [
        'handlers' => [
            'videotrack' => [
                'delegate' => 'CoreCourseModuleDelegate',
                'method'   => 'mobile_course_view',
                'displaydata' => [
                    'title'   => 'pluginname',
                    'icon'    => $CFG->wwwroot . '/mod/videotrack/pix/icon.svg',
                    'class'   => '',
                ],
                'init' => '',
                // Opens the activity in the in-app browser so the YouTube
                // IFrame Player API has full browser-level JS support.
                'offlinefunctions' => [],
            ],
        ],
        'lang' => [
            ['pluginname', 'videotrack'],
        ],
    ],
];
