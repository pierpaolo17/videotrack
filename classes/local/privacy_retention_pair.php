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

/**
 * Processes one user/activity retention pair under the canonical state lock.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class privacy_retention_pair {
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
    public static function delete_expired_pair(
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
            self::delete_expired_personal_rows($videotrackid, $userid, $cmid, $cutoff, $counts);
            tracker::invalidate_reactioncountscache($videotrackid, $userid);

            $activity = self::load_activity($videotrackid, $cmid);
            if ($activity === null) {
                $counts['statesdeleted'] += self::delete_state($videotrackid, $userid, $cmid);
            } else if (self::has_retained_state_inputs($videotrackid, $userid)) {
                [$videotrack, $cm, $course] = $activity;
                $state = self::rebuild_retained_state($videotrack, $cm, $videotrackid, $userid, $cmid, $now, $lock);
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

        self::synchronise_completion_safely($completionpayload, $videotrackid, $userid, $counts);

        $lock->release();
        return true;
    }

    /**
     * Synchronises Moodle completion after the retention transaction commits.
     *
     * The caller still holds the state lock while this best-effort step runs.
     *
     * @param array|null $completionpayload Activity, module, course and completion flag.
     * @param int $videotrackid Activity id for diagnostics.
     * @param int $userid User id.
     * @param array $counts Running counters updated on synchronisation failure.
     */
    private static function synchronise_completion_safely(
        ?array $completionpayload,
        int $videotrackid,
        int $userid,
        array &$counts
    ): void {
        if ($completionpayload === null) {
            return;
        }

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

    /**
     * Rebuilds retained aggregate state while the caller holds the state lock and transaction.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param int $cmid Course module id.
     * @param int $now Current task timestamp.
     * @param \core\lock\lock $lock Canonical state lock owned by the caller.
     * @return \stdClass Rebuilt and persisted state.
     */
    private static function rebuild_retained_state(
        \stdClass $videotrack,
        \cm_info $cm,
        int $videotrackid,
        int $userid,
        int $cmid,
        int $now,
        \core\lock\lock $lock
    ): \stdClass {
        global $DB;

        $state = tracker::rebuild_state_from_segments($videotrack, $cm, $userid, $lock);
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
        return $state;
    }

    /**
     * Deletes expired personal rows while the caller holds the state lock and transaction.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param int $cmid Course module id.
     * @param int $cutoff Retention cutoff.
     * @param array $counts Running counters updated in place.
     */
    private static function delete_expired_personal_rows(
        int $videotrackid,
        int $userid,
        int $cmid,
        int $cutoff,
        array &$counts
    ): void {
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
