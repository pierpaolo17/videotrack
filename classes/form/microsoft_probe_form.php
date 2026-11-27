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
use mod_videotrack\local\microsoft_video_reference;

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
                'provider_preview' => get_string('providercheck:preview', 'mod_videotrack'),
            ]
        );
        $form->setDefault('operation', 'metadata');
        $form->setType('operation', PARAM_RAW);
        if ($this->bound_reference() !== null) {
            $form->addElement(
                'static',
                'activitytarget',
                get_string('providercheck:activitytarget', 'mod_videotrack'),
                s((string)($this->_customdata['activityname'] ?? ''))
            );
            $form->addElement('static', 'activitynotice', '', get_string('providercheck:activitynotice', 'mod_videotrack'));
            $this->add_action_buttons(false, get_string('providercheck:submit', 'mod_videotrack'));
            return;
        }
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
        $form->addElement('select', 'targetmode', get_string('providercheck:targetmode', 'mod_videotrack'), [
            'manual' => get_string('providercheck:targetids', 'mod_videotrack'),
            'reference' => get_string('providercheck:targetreference', 'mod_videotrack'),
        ]);
        $form->setDefault('targetmode', 'manual');
        $form->setType('targetmode', PARAM_RAW);
        foreach (['driveid', 'itemid'] as $field) {
            $form->addElement('text', $field, get_string('providercheck:' . $field, 'mod_videotrack'), ['maxlength' => 256]);
            $form->setType($field, PARAM_RAW_TRIMMED);
            $form->hideIf($field, 'targetmode', 'neq', 'manual');
        }
        $form->addElement('textarea', 'reference', get_string('providercheck:reference', 'mod_videotrack'), [
            'rows' => 7,
            'cols' => 60,
            'maxlength' => 4096,
        ]);
        $form->setType('reference', PARAM_RAW);
        $form->hideIf('reference', 'targetmode', 'neq', 'reference');
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
        if (!in_array($operation, ['metadata', 'provider_link', 'provider_preview'], true)) {
            $errors['operation'] = get_string('invaliddata', 'error');
        }
        $bound = $this->bound_reference();
        if ($bound !== null) {
            if ($this->reference_errors(['reference' => $bound, 'accountcontext' => 'current_user'])) {
                $errors['operation'] = get_string('microsoftreference:invalid', 'mod_videotrack');
            }
            return $errors;
        }
        if (!in_array($data['accountcontext'] ?? 'system', ['system', 'current_user'], true)) {
            $errors['accountcontext'] = get_string('invaliddata', 'error');
        }
        $mode = $data['targetmode'] ?? 'manual';
        if ($mode === 'reference') {
            return array_replace($errors, $this->reference_errors($data));
        }
        if ($mode !== 'manual') {
            $errors['targetmode'] = get_string('invaliddata', 'error');
            return $errors;
        }
        $errors = array_replace($errors, self::identifier_errors($data));
        if (in_array($operation, ['provider_link', 'provider_preview'], true)) {
            $errors = array_replace($errors, $this->provider_link_errors($data));
        }
        return $errors;
    }

    /**
     * Retain manual id validation independently of reference input.
     *
     * @param array $data Submitted manual fields.
     * @return array Field errors, preserving the original id error order.
     */
    private static function identifier_errors(array $data): array {
        $errors = [];
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
        return $errors;
    }

    /**
     * Require a valid reference, the selected local service and a personal account.
     *
     * @param array $data Submitted reference fields.
     * @return array Local field errors, before any OAuth or Graph call.
     */
    private function reference_errors(array $data): array {
        $errors = [];
        if (($data['accountcontext'] ?? 'system') !== 'current_user') {
            $errors['accountcontext'] = get_string('providercheck:personalrequired', 'mod_videotrack');
        }
        try {
            $reference = microsoft_video_reference::from_json($data['reference'] ?? null);
        } catch (\invalid_parameter_exception $error) {
            $errors['reference'] = get_string('providercheck:referenceinvalid', 'mod_videotrack');
            return $errors;
        }
        try {
            $reference->assert_issuer((int)get_config('mod_videotrack', 'microsoftissuerid'));
        } catch (\invalid_parameter_exception $error) {
            $errors['reference'] = get_string('providercheck:referenceissuer', 'mod_videotrack');
        }
        return $errors;
    }

    /**
     * Resolve validated reference input after the controller's sesskey check.
     *
     * @param \stdClass $data Validated POST fields.
     * @return \stdClass Probe fields with the same explicit operation and personal identity.
     */
    public function resolve_target(\stdClass $data): \stdClass {
        $bound = $this->bound_reference();
        if ($bound === null && ($data->targetmode ?? 'manual') === 'manual') {
            return $data;
        }
        if ($bound === null && ($data->targetmode !== 'reference' || ($data->accountcontext ?? '') !== 'current_user')) {
            throw new \invalid_parameter_exception('Invalid Microsoft reference target.');
        }
        $reference = microsoft_video_reference::from_json($bound ?? $data->reference ?? null);
        $reference->assert_issuer((int)get_config('mod_videotrack', 'microsoftissuerid'));
        $target = $bound === null ? clone $data : (object)['operation' => $data->operation, 'targetmode' => 'reference'];
        foreach ($reference->target() as $field => $value) {
            $target->{$field} = $value;
        }
        return $target;
    }

    /**
     * Distinguish a server-bound saved reference (including empty) from the ordinary editable diagnostic.
     *
     * @return string|null Saved identity supplied by the authorised controller, or null for a general probe.
     */
    private function bound_reference(): ?string {
        return is_array($this->_customdata) && array_key_exists('activityreference', $this->_customdata)
            ? (string)$this->_customdata['activityreference'] : null;
    }

    /**
     * Require a personal account and video item for page or preview actions.
     *
     * @param array $data Submitted form fields.
     * @return array Field errors specific to opening a Microsoft page.
     */
    private function provider_link_errors(array $data): array {
        $errors = [];
        if (($data['accountcontext'] ?? 'system') !== 'current_user') {
            $errors['accountcontext'] = get_string('providercheck:personalrequired', 'mod_videotrack');
        }
        if (($data['itemid'] ?? '') === '') {
            $key = ($data['operation'] ?? '') === 'provider_preview' ? 'previewitemrequired' : 'itemrequired';
            $errors['itemid'] = get_string('providercheck:' . $key, 'mod_videotrack');
        }
        return $errors;
    }
}
