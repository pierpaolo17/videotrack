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

namespace mod_videotrack\form;

use mod_videotrack\local\microsoft_activity_reference;

/**
 * Optional resource preparation kept separate from the active video source.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_activity_reference_form {
    /**
     * Add a collapsed metadata group without adding a player source option.
     *
     * @param \MoodleQuickForm $form Activity settings form.
     */
    public static function add_elements(\MoodleQuickForm $form): void {
        $form->addElement('header', 'microsoftreferenceheader', get_string('microsoftreferenceheader', 'mod_videotrack'));
        $form->addElement('textarea', 'microsoftreference', get_string('microsoftreference', 'mod_videotrack'), [
            'rows' => 5, 'cols' => 70, 'maxlength' => 4096,
        ]);
        $form->setType('microsoftreference', PARAM_RAW_TRIMMED);
        $form->setDefault('microsoftreference', '');
        $form->addHelpButton('microsoftreference', 'microsoftreference', 'mod_videotrack');
        $form->addElement('advcheckbox', 'microsoftreferencebind', get_string('microsoftreferencebind', 'mod_videotrack'));
        $form->setType('microsoftreferencebind', PARAM_BOOL);
        $form->setDefault('microsoftreferencebind', 0);
        $form->addHelpButton('microsoftreferencebind', 'microsoftreferencebind', 'mod_videotrack');
        $form->setExpanded('microsoftreferenceheader', false);
    }

    /**
     * Report local field errors using the same validator as the save callbacks.
     *
     * @param array $data Submitted activity data.
     * @param \stdClass|null $previous Existing activity.
     * @return array Field errors without private input or provider contents.
     */
    public static function errors(array $data, ?\stdClass $previous): array {
        try {
            microsoft_activity_reference::process_form_fields((object)$data, $previous);
        } catch (\invalid_parameter_exception $error) {
            return ['microsoftreference' => get_string('microsoftreference:invalid', 'mod_videotrack')];
        }
        return [];
    }
}
