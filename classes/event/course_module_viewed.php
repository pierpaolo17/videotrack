<?php
namespace mod_videotrack\event;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class course_module_viewed extends \core\event\course_module_viewed {
    protected function init() {
        $this->data['objecttable'] = 'videotrack';
        parent::init();
    }
}
