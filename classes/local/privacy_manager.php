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

namespace mod_videotrack\local;

use context;

/**
 * Privacy and retention helpers for VideoTrack.
 *
 * User erasure and automated retention both delete expired personal rows. The
 * aggregate state is derived data and is rebuilt only from retained,
 * server-validated segments and retained completion inputs.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class privacy_manager {
    /** Maximum number of user/activity pairs processed by one retention task run. */
    private const RETENTION_BATCH_LIMIT = 500;

    /** Legacy config key used by the removed deterministic pseudonymisation model. */
    private const LEGACY_ANONYMISATION_SALT_CONFIG = 'anonymisationsalt';

    /**
     * Returns the configured retention period in seconds.
     *
     * A value of 0 means unlimited retention: data is kept until a user requests
     * erasure via Moodle privacy tools, at which point it is deleted.
     *
     * @return int
     */
    public static function retention_period_seconds(): int {
        $configured = get_config('mod_videotrack', 'retentionperioddays');
        if ($configured === false || $configured === null || $configured === '') {
            $days = 730;
        } else {
            $days = (int)$configured;
        }
        if ($days <= 0) {
            return 0;
        }
        return $days * DAYSECS;
    }

    /**
     * Returns the current retention cutoff timestamp.
     *
     * A zero cutoff means age-based cleanup is disabled.
     *
     * @param int|null $now Optional deterministic clock for tests.
     * @return int
     */
    public static function retention_cutoff_timestamp(?int $now = null): int {
        $retention = self::retention_period_seconds();
        if ($retention <= 0) {
            return 0;
        }
        return max(0, ($now ?? time()) - $retention);
    }

    /**
     * Returns whether a timestamp is inside the currently retained window.
     *
     * @param int $timestamp Record timestamp.
     * @param int|null $now Optional deterministic clock for tests.
     * @return bool
     */
    public static function timestamp_is_retained(int $timestamp, ?int $now = null): bool {
        $cutoff = self::retention_cutoff_timestamp($now);
        return $cutoff === 0 || $timestamp >= $cutoff;
    }

    /**
     * Permanently deletes all personal tracking records for one user in one module context.
     *
     * @param context $context Moodle context.
     * @param int $userid Real user id.
     */
    public static function delete_user_data_in_context(context $context, int $userid): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE || $userid <= 0) {
            return;
        }

        $cmid = (int)$context->instanceid;
        $transaction = $DB->start_delegated_transaction();
        try {
            $DB->delete_records('videotrack_reactev', ['cmid' => $cmid, 'userid' => $userid]);
            $DB->delete_records('videotrack_integrity', ['cmid' => $cmid, 'userid' => $userid]);
            $DB->delete_records('videotrack_seg', ['cmid' => $cmid, 'userid' => $userid]);
            $DB->delete_records('videotrack_state', ['cmid' => $cmid, 'userid' => $userid]);
            $DB->delete_records('videotrack_acknowledge', ['cmid' => $cmid, 'userid' => $userid]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Permanently deletes all personal tracking records in one module context.
     *
     * @param context $context Moodle context.
     */
    public static function delete_all_user_data_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cmid = (int)$context->instanceid;
        $transaction = $DB->start_delegated_transaction();
        try {
            $DB->delete_records('videotrack_reactev', ['cmid' => $cmid]);
            $DB->delete_records('videotrack_integrity', ['cmid' => $cmid]);
            $DB->delete_records('videotrack_seg', ['cmid' => $cmid]);
            $DB->delete_records('videotrack_state', ['cmid' => $cmid]);
            $DB->delete_records('videotrack_acknowledge', ['cmid' => $cmid]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }

        // Activity configuration files (source video, poster, captions and icons)
        // are not learner records. They remain attached to the activity and are
        // removed by the normal activity-deletion lifecycle, not by a user-data
        // erasure request.
    }

    /**
     * Deletes expired personal rows and rebuilds derived state from retained data.
     *
     * The task intentionally does not keep pseudonymous copies. Analytics after
     * cleanup therefore represent only the configured retention window.
     *
     * @param int|null $now Optional deterministic clock for tests.
     * @return array Counts grouped by operation.
     */
    public static function delete_expired_records(?int $now = null): array {
        global $DB;

        $now = $now ?? time();
        $counts = [
            'segments' => 0,
            'events' => 0,
            'integrity' => 0,
            'acknowledgements' => 0,
            'statesrebuilt' => 0,
            'statesdeleted' => 0,
            'legacy' => self::delete_legacy_pseudonymous_records(),
            'processed' => 0,
            'remaining' => 0,
            'completionerrors' => 0,
            'skipped' => 0,
        ];

        $cutoff = self::retention_cutoff_timestamp($now);
        if ($cutoff <= 0) {
            $counts['skipped'] = 1;
            return $counts;
        }

        $sql = "SELECT DISTINCT userid, cmid, videotrackid
                  FROM {videotrack_seg}
                 WHERE userid > 0 AND timecreated < :segcutoff
                 UNION
                SELECT DISTINCT userid, cmid, videotrackid
                  FROM {videotrack_reactev}
                 WHERE userid > 0 AND timecreated < :eventcutoff
                 UNION
                SELECT DISTINCT userid, cmid, videotrackid
                  FROM {videotrack_state}
                 WHERE userid > 0 AND timecreated < :statecutoff
                 UNION
                SELECT DISTINCT userid, cmid, videotrackid
                  FROM {videotrack_integrity}
                 WHERE userid > 0 AND timecreated < :integritycutoff
                 UNION
                SELECT DISTINCT userid, cmid, videotrackid
                  FROM {videotrack_acknowledge}
                 WHERE userid > 0 AND timeconfirmed < :ackcutoff";
        $params = [
            'segcutoff' => $cutoff,
            'eventcutoff' => $cutoff,
            'statecutoff' => $cutoff,
            'integritycutoff' => $cutoff,
            'ackcutoff' => $cutoff,
        ];

        $records = $DB->get_recordset_sql($sql, $params, 0, self::RETENTION_BATCH_LIMIT + 1);
        $pairs = [];
        foreach ($records as $record) {
            if (count($pairs) >= self::RETENTION_BATCH_LIMIT) {
                $counts['remaining'] = 1;
                break;
            }
            $key = implode(':', [
                (int)$record->videotrackid,
                (int)$record->userid,
                (int)$record->cmid,
            ]);
            $pairs[$key] = [
                (int)$record->videotrackid,
                (int)$record->userid,
                (int)$record->cmid,
            ];
        }
        $records->close();

        foreach ($pairs as [$videotrackid, $userid, $cmid]) {
            if (privacy_retention_pair::delete_expired_pair($videotrackid, $userid, $cmid, $cutoff, $now, $counts)) {
                $counts['processed']++;
            } else {
                $counts['remaining'] = 1;
            }
        }

        return $counts;
    }

    /**
     * Deletes legacy negative-user rows created by releases before 1.6.33.
     *
     * @return int Number of deleted rows.
     */
    private static function delete_legacy_pseudonymous_records(): int {
        global $DB;

        $count = 0;
        $transaction = $DB->start_delegated_transaction();
        try {
            foreach (
                [
                    'videotrack_seg',
                    'videotrack_state',
                    'videotrack_reactev',
                    'videotrack_integrity',
                    'videotrack_acknowledge',
                ] as $table
            ) {
                $count += $DB->count_records_select($table, 'userid < 0');
                $DB->delete_records_select($table, 'userid < 0');
            }
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }

        unset_config(self::LEGACY_ANONYMISATION_SALT_CONFIG, 'mod_videotrack');
        return $count;
    }
}
