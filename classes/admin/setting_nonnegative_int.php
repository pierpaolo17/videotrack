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

namespace mod_videotrack\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Integer admin setting that accepts only non-negative values.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_nonnegative_int extends \admin_setting_configtext {

    /**
     * Validate the setting value.
     *
     * @param string $data Submitted value.
     * @return true|string True when valid, otherwise an error message.
     */
    public function validate($data) {
        if (!is_string($data) && !is_int($data)) {
            return get_string('setting:nonnegativeintrequired', 'mod_videotrack');
        }
        $data = trim((string)$data);
        if (!preg_match('/^\d+$/', $data)) {
            return get_string('setting:nonnegativeintrequired', 'mod_videotrack');
        }
        return true;
    }
}
