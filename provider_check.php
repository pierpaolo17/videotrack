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
 * Administrator-only Microsoft connection and metadata diagnostics.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('mod_videotrack_providercheck');
require_capability('moodle/site:config', context_system::instance());
$PAGE->set_cacheable(false);

$configuration = \mod_videotrack\local\provider_connection::configuration_status('microsoft');
$userconfiguration = \mod_videotrack\local\provider_connection::user_configuration_status('microsoft');
$form = new \mod_videotrack\form\microsoft_probe_form($PAGE->url, null, 'post');
$result = null;
if ($data = $form->get_data()) {
    require_sesskey();
    $probe = match ($data->accountcontext) {
        'current_user' => new \mod_videotrack\local\microsoft_user_probe(),
        'system' => new \mod_videotrack\local\microsoft_graph_probe(),
        default => throw new invalid_parameter_exception('Invalid provider account context.'),
    };
    $result = $probe->run($data->driveid, $data->itemid);
    $result['account_context'] = $data->accountcontext;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('providercheck:title', 'mod_videotrack'));
echo $OUTPUT->notification(get_string('providercheck:notice', 'mod_videotrack'), 'info');
echo $OUTPUT->notification(get_string('providercheck:usernotice', 'mod_videotrack'), 'info');
echo html_writer::link(
    new moodle_url('/' . $CFG->admin . '/settings.php', ['section' => 'modsettingvideotrack']),
    get_string('settings')
);
echo $OUTPUT->heading(get_string('providercheck:configuration', 'mod_videotrack'), 3);
echo $OUTPUT->heading(get_string('providercheck:systemaccount', 'mod_videotrack'), 4);
echo html_writer::tag('pre', s(json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)));
echo $OUTPUT->heading(get_string('providercheck:currentuser', 'mod_videotrack'), 4);
echo html_writer::tag('pre', s(json_encode($userconfiguration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)));
$form->display();
if ($result !== null) {
    echo $OUTPUT->heading(get_string('providercheck:result', 'mod_videotrack'), 3);
    $success = in_array($result['status'], ['drive_accessible', 'video_accessible'], true);
    echo $OUTPUT->notification(
        get_string($success ? 'providercheck:success' : 'providercheck:failure', 'mod_videotrack'),
        $success ? 'success' : 'warning'
    );
    echo html_writer::tag('pre', s(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)));
}
echo $OUTPUT->footer();
