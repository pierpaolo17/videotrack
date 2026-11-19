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
 * Bootstrap the administrator Microsoft metadata form.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotrack\form;

use mod_videotrack\local\microsoft_graph;

defined('MOODLE_INTERNAL') || die();

/** @var \stdClass $videotrackconfig */
$videotrackconfig = $GLOBALS['CFG'];
require_once($videotrackconfig->libdir . '/formslib.php');

/**
 * Explicit administrator submission for Microsoft metadata diagnostics.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_probe_form extends \moodleform {
    /**
     * Define identifiers without silently stripping invalid target characters.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement(
            'select',
            'operation',
            get_string('providercheck:operation', 'mod_videotrack'),
            [
                'metadata' => get_string('providercheck:metadata', 'mod_videotrack'),
                'provider_link' => get_string('providercheck:providerlink', 'mod_videotrack'),
            ]
        );
        $form->setDefault('operation', 'metadata');
        $form->setType('operation', PARAM_RAW);
        $form->addElement(
            'select',
            'accountcontext',
            get_string('providercheck:accountcontext', 'mod_videotrack'),
            [
                'system' => get_string('providercheck:systemaccount', 'mod_videotrack'),
                'current_user' => get_string('providercheck:currentuser', 'mod_videotrack'),
            ]
        );
        $form->setDefault('accountcontext', 'system');
        $form->setType('accountcontext', PARAM_RAW);
        foreach (['driveid', 'itemid'] as $field) {
            $form->addElement('text', $field, get_string('providercheck:' . $field, 'mod_videotrack'), ['maxlength' => 256]);
            $form->setType($field, PARAM_RAW_TRIMMED);
        }
        $this->add_action_buttons(false, get_string('providercheck:submit', 'mod_videotrack'));
    }

    /**
     * Reject paths, URLs and oversized ids before any OAuth or network operation.
     *
     * @param array $data Submitted form fields.
     * @param array $files Submitted files, unused by this form.
     * @return array Field errors; an empty item id requests drive metadata only.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $operation = $data['operation'] ?? 'metadata';
        if (!in_array($operation, ['metadata', 'provider_link'], true)) {
            $errors['operation'] = get_string('invaliddata', 'error');
        }
        if (!in_array($data['accountcontext'] ?? 'system', ['system', 'current_user'], true)) {
            $errors['accountcontext'] = get_string('invaliddata', 'error');
        }
        foreach (['driveid', 'itemid'] as $field) {
            $value = (string)($data[$field] ?? '');
            if ($field === 'itemid' && $value === '') {
                continue;
            }
            try {
                microsoft_graph::resource_url($value);
            } catch (\invalid_parameter_exception $error) {
                $errors[$field] = get_string('providercheck:invalidid', 'mod_videotrack');
            }
        }
        if ($operation === 'provider_link') {
            if (($data['accountcontext'] ?? 'system') !== 'current_user') {
                $errors['accountcontext'] = get_string('providercheck:personalrequired', 'mod_videotrack');
            }
            if (($data['itemid'] ?? '') === '') {
                $errors['itemid'] = get_string('providercheck:itemrequired', 'mod_videotrack');
            }
        }
        return $errors;
    }
}
