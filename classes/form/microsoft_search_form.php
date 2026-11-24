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
 * Form bootstrap for protected personal Microsoft video search.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotrack\form;

use mod_videotrack\local\microsoft_graph;
use mod_videotrack\local\microsoft_graph_search;

defined('MOODLE_INTERNAL') || die();

/** @var \stdClass $videotrackconfig */
$videotrackconfig = $GLOBALS['CFG'];
require_once($videotrackconfig->libdir . '/formslib.php');

/**
 * Explicit personal metadata search; normal GET never submits a query.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_search_form extends \moodleform {
    /**
     * Ask for an explicit drive and bounded query rather than a remote URL.
     */
    public function definition(): void {
        $form = $this->_form;
        $form->addElement('text', 'driveid', get_string('providercheck:driveid', 'mod_videotrack'), ['maxlength' => 256]);
        $form->setType('driveid', PARAM_RAW_TRIMMED);
        $form->addElement('text', 'searchquery', get_string('providersearch:query', 'mod_videotrack'), ['maxlength' => 100]);
        $form->setType('searchquery', PARAM_RAW_TRIMMED);
        $this->add_action_buttons(false, get_string('providersearch:submit', 'mod_videotrack'));
    }

    /**
     * Reject invalid inputs locally before OAuth or Graph can run.
     *
     * @param array $data Submitted fields.
     * @param array $files Submitted files, unused.
     * @return array Local field errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $driveid = $data['driveid'] ?? '';
        $query = $data['searchquery'] ?? '';
        try {
            if (!is_string($driveid)) {
                throw new \invalid_parameter_exception('Invalid drive.');
            }
            microsoft_graph::resource_url($driveid);
        } catch (\invalid_parameter_exception $error) {
            $errors['driveid'] = get_string('providercheck:invalidid', 'mod_videotrack');
        }
        try {
            if (!is_string($query)) {
                throw new \invalid_parameter_exception('Invalid query.');
            }
            microsoft_graph_search::resource_url('drive', $query);
        } catch (\invalid_parameter_exception $error) {
            $errors['searchquery'] = get_string('providersearch:invalidquery', 'mod_videotrack');
        }
        return $errors;
    }
}
