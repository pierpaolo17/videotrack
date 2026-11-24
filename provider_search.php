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
 * Protected personal Microsoft video discovery with no media import or proxy.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('mod_videotrack_providercheck');
require_capability('moodle/site:config', context_system::instance());
$PAGE->set_url(new moodle_url('/mod/videotrack/provider_search.php'));
$PAGE->set_cacheable(false);
header('Referrer-Policy: no-referrer');

$form = new \mod_videotrack\form\microsoft_search_form($PAGE->url, null, 'post');
$result = null;
$issuerid = (int)get_config('mod_videotrack', 'microsoftissuerid');
if ($data = $form->get_data()) {
    require_sesskey();
    $result = (new \mod_videotrack\local\microsoft_user_probe())->search_videos($data->driveid, $data->searchquery);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('providersearch:title', 'mod_videotrack'));
echo $OUTPUT->notification(get_string('providersearch:notice', 'mod_videotrack'), 'info');
echo html_writer::link(new moodle_url('/mod/videotrack/provider_check.php'), get_string('providercheck:title', 'mod_videotrack'));
$form->display();
if ($result !== null) {
    $diagnostic = $result->diagnostic();
    echo $OUTPUT->heading(get_string('providercheck:result', 'mod_videotrack'), 3);
    echo html_writer::tag('pre', s(json_encode($diagnostic, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)));
    if ($diagnostic['status'] !== 'search_complete') {
        echo $OUTPUT->notification(get_string('providercheck:failure', 'mod_videotrack'), 'warning');
    } else {
        if (!empty($diagnostic['more_results'])) {
            echo $OUTPUT->notification(get_string('providersearch:partial', 'mod_videotrack'), 'info');
        }
        if (empty($result->items())) {
            echo $OUTPUT->notification(get_string('providersearch:empty', 'mod_videotrack'), 'info');
        }
        foreach ($result->items() as $item) {
            $reference = new \mod_videotrack\local\microsoft_video_reference($issuerid, $item['driveid'], $item['itemid']);
            echo $OUTPUT->heading(s($item['name']), 4);
            echo html_writer::tag('p', s(format_time((int)$item['duration_seconds'])));
            echo html_writer::tag('textarea', s($reference->encode()), [
                'aria-label' => get_string('providercheck:referencetitle', 'mod_videotrack'),
                'readonly' => 'readonly',
                'rows' => 7,
                'cols' => 60,
                'class' => 'form-control mb-3',
            ]);
        }
    }
}
echo $OUTPUT->footer();
