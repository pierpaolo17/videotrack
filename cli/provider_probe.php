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
 * Explicit authenticated Microsoft Graph metadata diagnostics for administrators.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');

/** @var stdClass $CFG Moodle configuration initialised by config.php. */
require_once($CFG->libdir . '/clilib.php');

$usage = <<<'USAGE'
Checks explicit Microsoft drive/video metadata using Moodle's system OAuth account.

Usage (from the Moodle root; single-quote opaque ids in Bash):
    php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID'
    php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID' --itemid='ITEM_ID'

Options:
    --driveid=<id>    Required explicit drive id. URLs and paths are rejected.
    --itemid=<id>     Optional video item id within that drive.
    -h, --help        Print this help without running the probe.

Requires an HTTPS site and a complete core Microsoft OAuth service/system account.
Supports public-cloud Graph and login.microsoftonline.com OAuth endpoints only.
Core may refresh and persist OAuth tokens. VideoTrack/demo data is not modified.
Performs a metadata GET, never a file download. Output contains redacted JSON status
and safe video metadata, without names, resource ids, tokens or download URLs.
Exit 0: drive_accessible/video_accessible. Exit 2: any other diagnostic or invalid option.
This command does not enable or validate OneDrive/SharePoint playback.
USAGE;

[$options, $unknown] = cli_get_params([
    'driveid' => '',
    'itemid' => '',
    'help' => false,
], ['h' => 'help']);
if ($unknown) {
    cli_error('Unknown option. Use --help for supported options.', 2);
}
if ($options['help']) {
    cli_writeln($usage);
    exit(0);
}
$probe = new \mod_videotrack\local\microsoft_graph_probe();
$status = $probe->run((string)$options['driveid'], (string)$options['itemid']);
cli_writeln(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
exit(in_array($status['status'], ['drive_accessible', 'video_accessible'], true) ? 0 : 2);
