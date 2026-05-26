<?php
/**
 * VideoTrack activity module.
 *
 * @package   mod_videotrack
 * @copyright 2026 SICS, Universita degli Studi della Tuscia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'mod_videotrack_save_segment' => [
        'classname' => 'mod_videotrack\\external\\save_segment',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a watched video segment.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:view',
    ],
    'mod_videotrack_save_reaction' => [
        'classname' => 'mod_videotrack\\external\\save_reaction',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Save a reaction click.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:view',
    ],
    'mod_videotrack_delete_reaction' => [
        'classname' => 'mod_videotrack\\external\\delete_reaction',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Delete a reaction click from current user.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:view',
    ],

    'mod_videotrack_delete_note' => [
        'classname' => 'mod_videotrack\\external\\delete_note',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => 'Delete a personal note from current user.',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => 'mod/videotrack:view',
    ],

    'mod_videotrack_save_note' => [
        'classname'     => 'mod_videotrack\\external\\save_note',
        'methodname'    => 'execute',
        'description'   => 'Save a personal timestamped note for the current student.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'capabilities'  => 'mod/videotrack:view',
    ],
];
