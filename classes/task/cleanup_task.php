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

/**
 * Scheduled GDPR retention cleanup task.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotrack\task;

defined('MOODLE_INTERNAL') || die();

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
        try {
            $counts = privacy_manager::anonymise_expired_records();
        } catch (\Throwable $e) {
            mtrace('VideoTrack GDPR retention cleanup failed: ' . $e->getMessage());
            throw $e;
        }

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
