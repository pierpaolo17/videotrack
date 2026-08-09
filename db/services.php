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


defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_videotrack_save_integrity_event' => [
        'classname' => 'mod_videotrack\\external\\save_integrity_event',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a privacy-safe diagnostic integrity signal.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_start_playback' => [
        'classname' => 'mod_videotrack\\external\\start_playback',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Start a server-authoritative playback credit window.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_save_segment' => [
        'classname' => 'mod_videotrack\\external\\save_segment',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a watched video segment.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],
    'mod_videotrack_save_reaction' => [
        'classname' => 'mod_videotrack\\external\\save_reaction',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a reaction click.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],
    'mod_videotrack_delete_reaction' => [
        'classname' => 'mod_videotrack\\external\\delete_reaction',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Delete a reaction click from current user.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_delete_note' => [
        'classname' => 'mod_videotrack\\external\\delete_note',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Delete a personal note from current user.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_save_bookmark' => [
        'classname' => 'mod_videotrack\\external\\save_bookmark',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a private named bookmark for the current student.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_delete_bookmark' => [
        'classname' => 'mod_videotrack\\external\\delete_bookmark',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Delete a private bookmark owned by the current student.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:participate',
    ],

    'mod_videotrack_save_note' => [
        'classname'     => 'mod_videotrack\\external\\save_note',
        'methodname'    => 'execute',
        'classpath'     => '',
        'description'   => 'Save a personal timestamped note for the current student.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'mod/videotrack:participate',
    ],
];
