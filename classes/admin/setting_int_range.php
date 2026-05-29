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
 * Integer admin setting with inclusive minimum and maximum bounds.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_int_range extends setting_nonnegative_int {

    /** @var int Minimum accepted value. */
    protected $min;

    /** @var int Maximum accepted value. */
    protected $max;

    /**
     * Constructor.
     *
     * @param string $name Setting name.
     * @param string $visiblename Visible name.
     * @param string $description Description.
     * @param int $defaultsetting Default value.
     * @param int $min Minimum accepted value.
     * @param int $max Maximum accepted value.
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $min, $max) {
        $this->min = (int)$min;
        $this->max = (int)$max;
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_INT);
    }

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
        $value = (int)$data;
        if ($value < $this->min || $value > $this->max) {
            return get_string('setting:intrangerequired', 'mod_videotrack',
                (object)['min' => $this->min, 'max' => $this->max]);
        }
        return true;
    }
}
