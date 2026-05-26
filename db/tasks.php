<?php
/**
 * VideoTrack activity module.
 *
 * @package   mod_videotrack
 * @copyright 2026 SICS, Universita degli Studi della Tuscia
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'mod_videotrack\\task\\cleanup_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '3',
        'day' => '*',
        'dayofweek' => '*',
        'month' => '*',
    ],
];
