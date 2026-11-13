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
 * Read-only local OAuth configuration diagnostics for future media providers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);
require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

$usage = <<<'USAGE'
Inspects local VideoTrack provider configuration without contacting any provider.

Usage:
    php mod/videotrack/cli/provider_status.php [--provider=microsoft] [--strict]

Options:
    --provider=<name>    Provider configuration to inspect. Currently: microsoft.
    --strict             Exit with code 2 unless local configuration is complete.
    -h, --help            Print this help.

Output is always JSON. No credentials, tokens or account identifiers are printed.
Configuration completeness does not validate tokens, permissions or remote files.
This foundation does not enable OneDrive/SharePoint playback in the activity form.
USAGE;

[$options, $unknown] = cli_get_params([
    'provider' => 'microsoft',
    'strict' => false,
    'help' => false,
], ['h' => 'help']);
if ($unknown) {
    cli_error('Unknown option(s): ' . implode(', ', $unknown), 2);
}
if ($options['help']) {
    cli_writeln($usage);
    exit(0);
}
try {
    $status = \mod_videotrack\local\provider_connection::configuration_status((string)$options['provider']);
} catch (invalid_parameter_exception $error) {
    cli_error($error->getMessage(), 2);
}
cli_writeln(json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
exit($options['strict'] && $status['status'] !== 'configuration_complete' ? 2 : 0);
