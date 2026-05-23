<?php
namespace mod_videotrack\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/videotrack/lib.php');

use core_external\external_api;

/**
 * Shared helpers for VideoTrack external AJAX endpoints.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper extends external_api {
    /**
     * Loads the activity and validates login, context and view capability.
     *
     * @param int $cmid Course module id.
     * @return array{course: \stdClass, videotrack: \stdClass, cm: \cm_info, context: \context_module}
     */
    public static function load_and_validate_context(int $cmid): array {
        global $DB;

        $cmraw = get_coursemodule_from_id('videotrack', $cmid, 0, false, MUST_EXIST);
        $course = get_course($cmraw->course);
        $videotrack = $DB->get_record('videotrack', ['id' => $cmraw->instance], '*', MUST_EXIST);

        require_login($course, false, $cmraw);
        $cm = \cm_info::create($cmraw);
        $context = \context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/videotrack:view', $context);

        return [
            'course' => $course,
            'videotrack' => $videotrack,
            'cm' => $cm,
            'context' => $context,
        ];
    }
}
