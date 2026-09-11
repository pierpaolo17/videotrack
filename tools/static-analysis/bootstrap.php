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
 * Moodle-aware bootstrap shared by PHPStan and Psalm.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This standalone bootstrap must resolve Moodle before MOODLE_INTERNAL exists.
// phpcs:disable moodle.Files.MoodleInternal.MoodleInternalGlobalState

$pluginroot = dirname(__DIR__, 2);
$candidates = [];
$environmentroot = getenv('MOODLE_ROOT');
if (is_string($environmentroot) && $environmentroot !== '') {
    $candidates[] = $environmentroot;
}
$candidates[] = dirname($pluginroot, 2);
$candidates[] = dirname($pluginroot, 3);

$moodleconfig = '';
foreach (array_unique($candidates) as $candidate) {
    $config = rtrim($candidate, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config.php';
    if (is_file($config)) {
        $moodleconfig = $config;
        break;
    }
}
if ($moodleconfig === '') {
    throw new RuntimeException('No installed Moodle config.php could be resolved for static analysis.');
}

defined('CLI_SCRIPT') || define('CLI_SCRIPT', true);
require_once($moodleconfig);

// Static analysers do not follow every variable-based require used by legacy Moodle entry points. Load the stable
// parent APIs required by the production files in this analysis scope so both supported Moodle layouts expose the
// same class graph.
$legacyfiles = [
    $CFG->libdir . '/upgradelib.php',
    $CFG->libdir . '/clilib.php',
    $CFG->libdir . '/adminlib.php',
    $CFG->libdir . '/formslib.php',
    $CFG->dirroot . '/group/lib.php',
    $CFG->dirroot . '/mod/forum/externallib.php',
    $CFG->dirroot . '/course/moodleform_mod.php',
    $CFG->dirroot . '/backup/util/includes/backup_includes.php',
    $CFG->dirroot . '/backup/util/includes/restore_includes.php',
    $CFG->dirroot . '/backup/moodle2/backup_stepslib.php',
    $CFG->dirroot . '/backup/moodle2/backup_activity_task.class.php',
    $CFG->dirroot . '/backup/moodle2/restore_stepslib.php',
    $CFG->dirroot . '/backup/moodle2/restore_activity_task.class.php',
];
foreach ($legacyfiles as $legacyfile) {
    require_once($legacyfile);
}

// phpcs:enable moodle.Files.MoodleInternal.MoodleInternalGlobalState
