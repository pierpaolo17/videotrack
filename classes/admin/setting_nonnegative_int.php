<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_videotrack\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Integer admin setting that accepts only non-negative values.
 *
 * @package    mod_videotrack
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
