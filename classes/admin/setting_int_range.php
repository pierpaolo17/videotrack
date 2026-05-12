<?php
// This file is part of Moodle - http://moodle.org/

namespace mod_videotrack\admin;

defined('MOODLE_INTERNAL') || die();

/**
 * Integer admin setting with inclusive minimum and maximum bounds.
 *
 * @package    mod_videotrack
 */
class setting_int_range extends \admin_setting_configtext {

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
            return get_string('setting:intrangerequired', 'mod_videotrack',
                (object)['min' => $this->min, 'max' => $this->max]);
        }
        $data = trim((string)$data);
        if (!preg_match('/^\d+$/', $data)) {
            return get_string('setting:intrangerequired', 'mod_videotrack',
                (object)['min' => $this->min, 'max' => $this->max]);
        }
        $value = (int)$data;
        if ($value < $this->min || $value > $this->max) {
            return get_string('setting:intrangerequired', 'mod_videotrack',
                (object)['min' => $this->min, 'max' => $this->max]);
        }
        return true;
    }
}
