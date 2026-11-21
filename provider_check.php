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
header('Referrer-Policy: no-referrer');

$connectpersonal = optional_param('connectpersonal', false, PARAM_BOOL);
$oauthreturn = optional_param('oauthreturn', false, PARAM_BOOL);
if ($connectpersonal || $oauthreturn) {
    $connection = new \mod_videotrack\local\microsoft_user_connection();
    if ($connectpersonal) {
        $loginurl = $connection->begin();
        if ($loginurl !== null) {
            redirect($loginurl);
        }
        $connectionstatus = $connection->status();
    } else {
        $connectionstatus = $connection->complete(optional_param('connectnonce', '', PARAM_ALPHANUMEXT));
    }
    $connected = in_array($connectionstatus, ['connected', 'already_connected'], true);
    $messagekey = match ($connectionstatus) {
        'connected', 'already_connected' => 'providercheck:connected',
        'connection_expired' => 'providercheck:connectexpired',
        'connection_cancelled' => 'providercheck:connectcancelled',
        default => 'providercheck:connectfailed',
    };
    \core\notification::add(get_string($messagekey, 'mod_videotrack'), $connected ? 'success' : 'warning');
    redirect(new moodle_url('/mod/videotrack/provider_check.php'));
}

$configuration = \mod_videotrack\local\provider_connection::configuration_status('microsoft');
$userconfiguration = \mod_videotrack\local\provider_connection::user_configuration_status('microsoft');
$form = new \mod_videotrack\form\microsoft_probe_form($PAGE->url, null, 'post');
$result = null;
$providerlink = null;
$previewurl = null;
$videoreference = null;
if ($data = $form->get_data()) {
    require_sesskey();
    $data = $form->resolve_target($data);
    if ($data->operation === 'provider_link') {
        if ($data->accountcontext !== 'current_user') {
            throw new invalid_parameter_exception('A personal Microsoft account is required.');
        }
        $probe = new \mod_videotrack\local\microsoft_user_probe();
        $linkresult = $probe->video_link($data->driveid, $data->itemid);
        $result = $linkresult->diagnostic();
        $providerlink = $linkresult->url();
    } else if ($data->operation === 'provider_preview') {
        if ($data->accountcontext !== 'current_user') {
            throw new invalid_parameter_exception('A personal Microsoft account is required.');
        }
        $probe = new \mod_videotrack\local\microsoft_user_probe();
        $previewresult = $probe->video_preview($data->driveid, $data->itemid);
        $result = $previewresult->diagnostic();
        $previewurl = $previewresult->url();
    } else if ($data->operation === 'metadata') {
        $probe = match ($data->accountcontext) {
            'current_user' => new \mod_videotrack\local\microsoft_user_probe(),
            'system' => new \mod_videotrack\local\microsoft_graph_probe(),
            default => throw new invalid_parameter_exception('Invalid provider account context.'),
        };
        $result = $probe->run($data->driveid, $data->itemid);
    } else {
        throw new invalid_parameter_exception('Invalid Microsoft operation.');
    }
    $result['account_context'] = $data->accountcontext;
    $reference = \mod_videotrack\local\microsoft_video_reference::for_result(
        (int)$userconfiguration['issuerid'],
        $data,
        $result
    );
    $videoreference = $reference?->encode();
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
echo $OUTPUT->notification(get_string('providercheck:connectnotice', 'mod_videotrack'), 'info');
if ($userconfiguration['status'] === 'configuration_complete') {
    echo $OUTPUT->single_button(
        new moodle_url('/mod/videotrack/provider_check.php', ['connectpersonal' => 1]),
        get_string('providercheck:connect', 'mod_videotrack'),
        'post'
    );
}
$form->display();
if ($result !== null) {
    echo $OUTPUT->heading(get_string('providercheck:result', 'mod_videotrack'), 3);
    $success = in_array($result['status'], ['drive_accessible', 'video_accessible', 'provider_link_ready', 'preview_ready'], true);
    $messagekey = match ($result['status']) {
        'preview_ready' => 'previewready',
        'preview_authentication_unverified' => 'previewauth',
        default => $success ? 'success' : 'failure',
    };
    echo $OUTPUT->notification(
        get_string('providercheck:' . $messagekey, 'mod_videotrack'),
        $success ? 'success' : 'warning'
    );
    echo html_writer::tag('pre', s(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)));
}
if ($providerlink !== null) {
    echo $OUTPUT->notification(get_string('providercheck:linknotice', 'mod_videotrack'), 'info');
    echo html_writer::tag('a', get_string('providercheck:open', 'mod_videotrack'), [
        'href' => $providerlink,
        'target' => '_blank',
        'rel' => 'noopener noreferrer',
        'referrerpolicy' => 'no-referrer',
        'class' => 'btn btn-primary',
    ]);
}
if ($videoreference !== null) {
    echo $OUTPUT->heading(get_string('providercheck:referencetitle', 'mod_videotrack'), 3);
    echo $OUTPUT->notification(get_string('providercheck:referencenotice', 'mod_videotrack'), 'info');
    echo html_writer::tag('textarea', s($videoreference), [
        'id' => 'videotrack-microsoft-reference',
        'aria-label' => get_string('providercheck:referencetitle', 'mod_videotrack'),
        'readonly' => 'readonly',
        'rows' => 7,
        'cols' => 60,
        'class' => 'form-control mb-3',
    ]);
}
if ($previewurl !== null) {
    echo html_writer::tag('p', get_string('providercheck:previewnotice', 'mod_videotrack'), [
        'id' => 'videotrack-microsoft-preview-notice',
        'class' => 'alert alert-info',
    ]);
    echo html_writer::tag('iframe', '', [
        'id' => 'videotrack-microsoft-preview',
        'src' => $previewurl,
        'title' => get_string('providercheck:previewtitle', 'mod_videotrack'),
        'aria-describedby' => 'videotrack-microsoft-preview-notice',
        'class' => 'w-100 border-0',
        'height' => 480,
        'referrerpolicy' => 'no-referrer',
        'sandbox' => 'allow-scripts allow-same-origin allow-forms allow-popups',
        'allow' => 'fullscreen; encrypted-media',
        'allowfullscreen' => 'allowfullscreen',
    ]);
}
echo $OUTPUT->footer();
