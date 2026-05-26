<?php
namespace mod_videotrack\task;

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotrack\local\privacy_manager;

/**
 * Scheduled GDPR retention cleanup.
 */
class cleanup_task extends \core\task\scheduled_task {
    /**
     * Returns the task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task:cleanup', 'mod_videotrack');
    }

    /**
     * Executes the retention task.
     */
    public function execute(): void {
        $counts = privacy_manager::anonymise_expired_records();
        if (!empty($counts['skipped'])) {
            mtrace('VideoTrack GDPR retention: unlimited retention configured; no records anonymised.');
            return;
        }

        $message = 'VideoTrack GDPR retention: anonymised ' .
            $counts['segments'] . ' segments, ' .
            $counts['states'] . ' states, ' .
            $counts['events'] . ' reaction/note events across ' .
            $counts['processed'] . ' user/activity pairs.';
        if (!empty($counts['remaining'])) {
            $message .= ' More records remain and will be processed by a later run.';
        }
        mtrace($message);
    }
}
