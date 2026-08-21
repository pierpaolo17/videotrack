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
            if (self::delete_expired_pair($videotrackid, $userid, $cmid, $cutoff, $now, $counts)) {
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

    /**
     * Deletes expired rows and rebuilds one user's derived state atomically.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param int $cmid Course module id.
     * @param int $cutoff Retention cutoff.
     * @param int $now Current task timestamp.
     * @param array $counts Running counters.
     * @return bool Whether the pair was processed.
     */
    private static function delete_expired_pair(
        int $videotrackid,
        int $userid,
        int $cmid,
        int $cutoff,
        int $now,
        array &$counts
    ): bool {
        global $DB;

        if ($videotrackid <= 0 || $userid <= 0 || $cmid <= 0) {
            return true;
        }

        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lock = $lockfactory->get_lock('state:' . $videotrackid . ':' . $userid, 10);
        if (!$lock) {
            return false;
        }

        $completionpayload = null;
        $transaction = $DB->start_delegated_transaction();
        try {
            $counts['segments'] += self::count_and_delete(
                'videotrack_seg',
                'videotrackid = ? AND userid = ? AND cmid = ? AND timecreated < ?',
                [$videotrackid, $userid, $cmid, $cutoff]
            );
            $counts['events'] += self::count_and_delete(
                'videotrack_reactev',
                'videotrackid = ? AND userid = ? AND cmid = ? AND timecreated < ?',
                [$videotrackid, $userid, $cmid, $cutoff]
            );
            $counts['integrity'] += self::count_and_delete(
                'videotrack_integrity',
                'videotrackid = ? AND userid = ? AND cmid = ? AND timecreated < ?',
                [$videotrackid, $userid, $cmid, $cutoff]
            );
            $counts['acknowledgements'] += self::count_and_delete(
                'videotrack_acknowledge',
                'videotrackid = ? AND userid = ? AND cmid = ? AND timeconfirmed < ?',
                [$videotrackid, $userid, $cmid, $cutoff]
            );
            tracker::invalidate_reactioncountscache($videotrackid, $userid);

            $activity = self::load_activity($videotrackid, $cmid);
            if ($activity === null) {
                $counts['statesdeleted'] += self::delete_state($videotrackid, $userid, $cmid);
            } else if (self::has_retained_state_inputs($videotrackid, $userid)) {
                [$videotrack, $cm, $course] = $activity;
                $state = tracker::rebuild_state_from_segments($videotrack, $cm, $userid, true);
                if ($state === null) {
                    throw new \coding_exception('State rebuild unexpectedly failed while the retention lock was held.');
                }

                if (!self::server_guard_is_recent($state, $now)) {
                    $state->serverlastactivity = 0;
                    $state->serverplaybacksessionid = '';
                    $state->serverbudgetseconds = 0.0;
                    $state->servercreditedseconds = 0.0;
                }
                $state->timecreated = self::earliest_retained_timestamp(
                    $videotrackid,
                    $userid,
                    $cmid,
                    $now
                );
                $state->timemodified = $now;
                $DB->update_record('videotrack_state', $state);
                $counts['statesrebuilt']++;
                $completionpayload = [$videotrack, $cm, $course, !empty($state->iscompleted)];
            } else {
                $counts['statesdeleted'] += self::delete_state($videotrackid, $userid, $cmid);
                [$videotrack, $cm, $course] = $activity;
                $completionpayload = [$videotrack, $cm, $course, false];
            }

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            try {
                $transaction->rollback($e);
            } finally {
                $lock->release();
            }
            throw $e;
        }

        if ($completionpayload !== null) {
            try {
                [$videotrack, $cm, $course, $iscompleted] = $completionpayload;
                self::synchronise_completion($videotrack, $cm, $course, $iscompleted, $userid);
            } catch (\Throwable $e) {
                $counts['completionerrors']++;
                debugging(
                    'VideoTrack retention could not synchronise completion for activity ' .
                    $videotrackid . ', user ' . $userid . ' (' . get_class($e) . ')',
                    DEBUG_DEVELOPER
                );
            }
        }

        $lock->release();
        return true;
    }

    /**
     * Returns whether the runtime playback guard belongs to a currently active window.
     *
     * Retention must clear stale credit after rebuilding historical state, but it
     * should not interrupt a learner whose current playback request is still inside
     * one bounded heartbeat window.
     *
     * @param \stdClass $state Rebuilt state record.
     * @param int $now Current task timestamp.
     * @return bool
     */
    private static function server_guard_is_recent(\stdClass $state, int $now): bool {
        $lastactivity = (int)($state->serverlastactivity ?? 0);
        $sessionid = (string)($state->serverplaybacksessionid ?? '');
        if ($lastactivity <= 0 || $sessionid === '') {
            return false;
        }

        $configured = get_config('mod_videotrack', 'heartbeatinterval');
        $heartbeat = ($configured === false || $configured === null || $configured === '')
            ? 30
            : (int)$configured;
        $heartbeat = max(5, min(300, $heartbeat));
        $oldestaccepted = max(0, ($now - $heartbeat - 10) * 1000);
        return $lastactivity >= $oldestaccepted;
    }

    /**
     * Counts and deletes matching records.
     *
     * @param string $table Table name.
     * @param string $select DML select clause.
     * @param array $params DML parameters.
     * @return int Deleted row count.
     */
    private static function count_and_delete(string $table, string $select, array $params): int {
        global $DB;

        $count = $DB->count_records_select($table, $select, $params);
        if ($count > 0) {
            $DB->delete_records_select($table, $select, $params);
        }
        return $count;
    }

    /**
     * Loads the activity, cm_info and course required to rebuild state.
     *
     * @param int $videotrackid Activity id.
     * @param int $cmid Course module id.
     * @return array|null [activity, cm_info, course] or null when inconsistent.
     */
    private static function load_activity(int $videotrackid, int $cmid): ?array {
        global $DB;

        $cmrecord = get_coursemodule_from_id('videotrack', $cmid, 0, false, IGNORE_MISSING);
        if (!$cmrecord || (int)$cmrecord->instance !== $videotrackid) {
            return null;
        }
        $videotrack = $DB->get_record('videotrack', [
            'id' => $videotrackid,
            'course' => (int)$cmrecord->course,
        ]);
        if (!$videotrack) {
            return null;
        }
        $course = get_course((int)$cmrecord->course);
        $cm = get_fast_modinfo($course)->get_cm($cmid);
        return [$videotrack, $cm, $course];
    }

    /**
     * Returns whether retained rows can contribute to aggregate state/completion.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @return bool
     */
    private static function has_retained_state_inputs(int $videotrackid, int $userid): bool {
        global $DB;

        if (
            $DB->record_exists('videotrack_seg', [
                'videotrackid' => $videotrackid,
                'userid' => $userid,
                'servervalidated' => 1,
            ])
        ) {
            return true;
        }
        if (
            $DB->record_exists_select(
                'videotrack_reactev',
                "videotrackid = :videotrackid AND userid = :userid AND isdeleted = 0
                      AND reactionid > 0 AND (notetype = '' OR notetype IS NULL)",
                ['videotrackid' => $videotrackid, 'userid' => $userid]
            )
        ) {
            return true;
        }
        return $DB->record_exists('videotrack_acknowledge', [
            'videotrackid' => $videotrackid,
            'userid' => $userid,
        ]);
    }

    /**
     * Returns the earliest retained timestamp across all personal data families.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param int $cmid Course module id.
     * @param int $fallback Fallback timestamp.
     * @return int
     */
    private static function earliest_retained_timestamp(
        int $videotrackid,
        int $userid,
        int $cmid,
        int $fallback
    ): int {
        global $DB;

        $timestamps = [];
        foreach (
            [
                ['videotrack_seg', 'timecreated'],
                ['videotrack_reactev', 'timecreated'],
                ['videotrack_integrity', 'timecreated'],
                ['videotrack_acknowledge', 'timeconfirmed'],
            ] as [$table, $field]
        ) {
            $value = $DB->get_field_sql(
                "SELECT MIN({$field})
                   FROM {{$table}}
                  WHERE videotrackid = :videotrackid AND userid = :userid AND cmid = :cmid",
                [
                    'videotrackid' => $videotrackid,
                    'userid' => $userid,
                    'cmid' => $cmid,
                ]
            );
            if ($value !== false && (int)$value > 0) {
                $timestamps[] = (int)$value;
            }
        }
        return $timestamps ? min($timestamps) : $fallback;
    }

    /**
     * Deletes one derived state row.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param int $cmid Course module id.
     * @return int Number of deleted rows.
     */
    private static function delete_state(int $videotrackid, int $userid, int $cmid): int {
        global $DB;

        $params = [
            'videotrackid' => $videotrackid,
            'userid' => $userid,
            'cmid' => $cmid,
        ];
        $count = $DB->count_records('videotrack_state', $params);
        if ($count > 0) {
            $DB->delete_records('videotrack_state', $params);
        }
        return $count;
    }

    /**
     * Synchronises custom Moodle completion after retention changed its inputs.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param \stdClass $course Course record.
     * @param bool $iscompleted Rebuilt VideoTrack completion state.
     * @param int $userid User id.
     */
    private static function synchronise_completion(
        \stdClass $videotrack,
        \cm_info $cm,
        \stdClass $course,
        bool $iscompleted,
        int $userid
    ): void {
        if ((int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC) {
            return;
        }
        if (!completion_config::has_custom_rules($videotrack)) {
            return;
        }

        $completion = new \completion_info($course);
        tracker::update_moodle_completion_if_changed($completion, $cm, $iscompleted, $userid);
    }
}
