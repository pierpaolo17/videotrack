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
 * Post-installation steps for mod_videotrack.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/repairlib.php');

/**
 * Run post-installation cleanup.
 *
 * Fresh installs can follow failed pre-production attempts that left stale
 * grade_items rows behind even though no VideoTrack instances exist. Remove
 * those rows before Moodle can build the standard activity grading form.
 *
 * @return void
 */
function xmldb_videotrack_install(): void {
    videotrack_repair_preproduction_gradebook_rows();
}
