<?php
namespace mod_videotrack\local;

defined('MOODLE_INTERNAL') || die();

use context;

/**
 * Privacy helpers for VideoTrack.
 *
 * User initiated erasure requests delete personal tracking data.
 * Retention cleanup may still anonymise old records when configured by admins.
 */
class privacy_manager {
    /** Prefix used for anonymised browser session identifiers. */
    private const ANONYMOUS_SESSION_PREFIX = 'anon-';

    /** Name of the local secret used to make anonymised identifiers non-reversible. */
    private const ANONYMISATION_SALT_CONFIG = 'anonymisationsalt';

    /** Maximum number of user/activity pairs processed by one retention task run. */
    private const RETENTION_BATCH_LIMIT = 500;

    /**
     * Returns the configured retention period in seconds.
     *
     * A value of 0 means unlimited retention: data is kept until a user requests
     * erasure via Moodle privacy tools, at which point it is deleted.
     *
     * @return int
     */
    public static function retention_period_seconds(): int {
        $days = (int)get_config('mod_videotrack', 'retentionperioddays');
        if ($days <= 0) {
            return 0;
        }
        return $days * DAYSECS;
    }

    /**
     * Returns the local anonymisation salt, creating it on first use.
     *
     * @return string
     */
    private static function anonymisation_salt(): string {
        $salt = (string)get_config('mod_videotrack', self::ANONYMISATION_SALT_CONFIG);
        if ($salt !== '') {
            return $salt;
        }

        $factory = \core\lock\lock_config::get_lock_factory('mod_videotrack_anonymisation');
        $lock = $factory->get_lock('salt', 10);
        if (!$lock) {
            // Do not create a salt without the lock: two concurrent requests could
            // generate different salts and make anonymised identifiers inconsistent.
            $salt = (string)get_config('mod_videotrack', self::ANONYMISATION_SALT_CONFIG);
            if ($salt !== '') {
                return $salt;
            }
            throw new \moodle_exception('locktimeout', 'error');
        }
        try {
            // Re-read after acquiring the lock: another request may have created it.
            $salt = (string)get_config('mod_videotrack', self::ANONYMISATION_SALT_CONFIG);
            if ($salt !== '') {
                return $salt;
            }

            try {
                $salt = bin2hex(random_bytes(32));
            } catch (\Throwable $e) {
                if (function_exists('random_string')) {
                    $salt = random_string(64);
                } else {
                    debugging(
                        'mod_videotrack: CSPRNG unavailable; anonymisation salt generated with a weak fallback.',
                        DEBUG_NORMAL
                    );
                    $salt = hash('sha256', uniqid('', true) . ':' . microtime(true));
                }
            }
            set_config(self::ANONYMISATION_SALT_CONFIG, $salt, 'mod_videotrack');
            return $salt;
        } finally {
            if ($lock) {
                $lock->release();
            }
        }
    }

    /**
     * Builds a stable negative user id that cannot collide with normal Moodle users.
     *
     * The mapping is salted at site level. It is a deterministic pseudonymous key
     * used only to preserve aggregate analytics after erasure requests.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id. Scopes the pseudonymous id to one activity.
     * @return int Anonymous user id.
     */
    public static function anonymous_userid(int $userid, int $cmid): int {
        $hash = hash('sha256', self::anonymisation_salt() . ':' . $userid . ':' . $cmid . ':userid');
        // Use a wide signed-int safe range to make collisions negligible even on large sites.
        $bucket = hexdec(substr($hash, 0, 15)) % 2000000000;
        return -1 * (100000000 + $bucket);
    }

    /**
     * Builds a deterministic non-identifying session id.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     * @return string
     */
    private static function anonymous_sessionid(int $userid, int $cmid): string {
        $hash = hash('sha256', self::anonymisation_salt() . ':' . $userid . ':' . $cmid . ':sessionid');
        return self::ANONYMOUS_SESSION_PREFIX . substr($hash, 0, 59);
    }


    /**
     * Permanently deletes all personal tracking records for one user in one module context.
     *
     * This is used for Moodle Privacy API erasure requests (GDPR Art. 17). Unlike
     * retention cleanup, it does not preserve aggregate rows with pseudonymous ids.
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
            $DB->delete_records('videotrack_seg', ['cmid' => $cmid, 'userid' => $userid]);
            $DB->delete_records('videotrack_state', ['cmid' => $cmid, 'userid' => $userid]);
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
            $DB->delete_records('videotrack_seg', ['cmid' => $cmid]);
            $DB->delete_records('videotrack_state', ['cmid' => $cmid]);
            $transaction->allow_commit();
        } catch (\Throwable $e) {
            $transaction->rollback($e);
            throw $e;
        }

        // Context-level erasure removes shared plugin files as well (for example
        // teacher-uploaded reaction icons, poster images, subtitles and uploaded videos).
        // Per-user erasure intentionally does not delete these shared activity files.
        // File operations are outside the delegated transaction because Moodle file
        // storage is not rolled back together with database writes.
        get_file_storage()->delete_area_files($context->id, 'mod_videotrack');
    }

    /**
     * Anonymises all personal tracking records for one user in one module context.
     *
     * @param context $context Moodle context.
     * @param int $userid Real user id.
     */
    public static function anonymise_user_in_context(context $context, int $userid): void {
        if ($context->contextlevel != CONTEXT_MODULE || $userid <= 0) {
            return;
        }

        self::anonymise_user_records($userid, (int)$context->instanceid);
    }

    /**
     * Anonymises all real users' tracking records in one module context.
     *
     * This is used by Moodle privacy erasure for a whole activity context. The
     * plugin preserves aggregate analytics but removes the link to real users.
     *
     * @param context $context Moodle context.
     */
    public static function anonymise_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cmid = (int)$context->instanceid;
        $sql = "SELECT DISTINCT userid
                  FROM {videotrack_seg}
                 WHERE cmid = :segcmid AND userid > 0
                 UNION
                SELECT DISTINCT userid
                  FROM {videotrack_state}
                 WHERE cmid = :statecmid AND userid > 0
                 UNION
                SELECT DISTINCT userid
                  FROM {videotrack_reactev}
                 WHERE cmid = :eventcmid AND userid > 0";
        $records = $DB->get_recordset_sql($sql, [
            'segcmid' => $cmid,
            'statecmid' => $cmid,
            'eventcmid' => $cmid,
        ]);
        $userids = [];
        foreach ($records as $record) {
            $userids[(int)$record->userid] = true;
        }
        $records->close();

        foreach (array_keys($userids) as $userid) {
            self::anonymise_user_records((int)$userid, $cmid);
        }
    }

    /**
     * Anonymises one user's records for a course module.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     */
    private static function anonymise_user_records(int $userid, int $cmid): void {
        global $DB;

        if ($userid <= 0) {
            return;
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $anonuserid = self::anonymous_userid($userid, $cmid);
            $sessionid = self::anonymous_sessionid($userid, $cmid);
            $notetext = get_string('privacy:anonymised', 'mod_videotrack');

            $params = [
                'anonuserid' => $anonuserid,
                'sessionid' => $sessionid,
                'cmid' => $cmid,
                'userid' => $userid,
            ];

            $DB->execute(
                "UPDATE {videotrack_seg}
                    SET userid = :anonuserid, sessionid = :sessionid
                  WHERE cmid = :cmid AND userid = :userid",
                $params
            );

            self::anonymise_state_rows($userid, $cmid);

            $eventparams = $params + [
                'notetext' => $notetext,
                'reactionlabel' => get_string('privacy:anonymisedreaction', 'mod_videotrack'),
            ];
            $DB->execute(
                "UPDATE {videotrack_reactev}
                    SET userid = :anonuserid, sessionid = :sessionid,
                        videotime = 0, playbackrate = 1, reactionlabel = :reactionlabel, reactiondesc = '',
                        notetext = CASE WHEN notetype = 'note' THEN :notetext ELSE notetext END
                  WHERE cmid = :cmid AND userid = :userid",
                $eventparams
            );

            $transaction->allow_commit();
        } catch (\Throwable $e) {
            debugging('mod_videotrack anonymisation failed for cmid ' . $cmid . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
            $transaction->rollback($e);
            // rollback() already rethrows in Moodle; keep an explicit throw for
            // clarity and for future compatibility with transaction handling.
            throw $e;
        }
    }

    /**
     * Anonymises old records according to the configured retention period.
     *
     * This is intentionally anonymisation, not deletion: aggregate analytics remain
     * available while the relation to the real user and personal notes are removed.
     * If retention is 0, nothing is changed.
     *
     * @return array Counts grouped by table.
     */
    public static function anonymise_expired_records(): array {
        global $DB;

        $retention = self::retention_period_seconds();
        if ($retention <= 0) {
            return ['segments' => 0, 'states' => 0, 'events' => 0, 'skipped' => 1, 'processed' => 0, 'remaining' => 0];
        }

        $cutoff = time() - $retention;
        $counts = ['segments' => 0, 'states' => 0, 'events' => 0, 'skipped' => 0, 'processed' => 0, 'remaining' => 0];

        $sql = "SELECT DISTINCT userid, cmid
                  FROM {videotrack_seg}
                 WHERE userid > 0 AND timecreated < :segcutoff
                 UNION
                SELECT DISTINCT userid, cmid
                  FROM {videotrack_reactev}
                 WHERE userid > 0 AND timecreated < :eventcutoff
                 UNION
                SELECT DISTINCT userid, cmid
                  FROM {videotrack_state}
                 WHERE userid > 0 AND timemodified < :statecutoff";
        $params = [
            'segcutoff' => $cutoff,
            'eventcutoff' => $cutoff,
            'statecutoff' => $cutoff,
        ];

        $records = $DB->get_recordset_sql($sql, $params, 0, self::RETENTION_BATCH_LIMIT + 1);
        $pairs = [];
        foreach ($records as $record) {
            if (count($pairs) >= self::RETENTION_BATCH_LIMIT) {
                $counts['remaining'] = 1;
                break;
            }
            $key = (int)$record->userid . ':' . (int)$record->cmid;
            $pairs[$key] = [(int)$record->userid, (int)$record->cmid];
        }
        $records->close();

        foreach ($pairs as $pair) {
            self::anonymise_old_user_rows($pair[0], $pair[1], $cutoff, $counts);
            $counts['processed']++;
        }

        return $counts;
    }

    /**
     * Anonymises old rows for one user/module pair.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     * @param int $cutoff Unix timestamp.
     * @param array $counts Running counts.
     */
    private static function anonymise_old_user_rows(int $userid, int $cmid, int $cutoff, array &$counts): void {
        global $DB;

        if ($userid <= 0) {
            return;
        }

        $transaction = $DB->start_delegated_transaction();
        $anonuserid = self::anonymous_userid($userid, $cmid);
        $sessionid = self::anonymous_sessionid($userid, $cmid);
        $notetext = get_string('privacy:anonymised', 'mod_videotrack');

        $counts['segments'] += $DB->count_records_select(
            'videotrack_seg',
            'cmid = ? AND userid = ? AND timecreated < ?',
            [$cmid, $userid, $cutoff]
        );
        $DB->execute(
            "UPDATE {videotrack_seg}
                SET userid = :anonuserid, sessionid = :sessionid
              WHERE cmid = :cmid AND userid = :userid AND timecreated < :cutoff",
            [
                'anonuserid' => $anonuserid,
                'sessionid' => $sessionid,
                'cmid' => $cmid,
                'userid' => $userid,
                'cutoff' => $cutoff,
            ]
        );

        $counts['states'] += $DB->count_records_select(
            'videotrack_state',
            'cmid = ? AND userid = ? AND timemodified < ?',
            [$cmid, $userid, $cutoff]
        );
        self::anonymise_state_rows($userid, $cmid, $cutoff);

        $counts['events'] += $DB->count_records_select(
            'videotrack_reactev',
            'cmid = ? AND userid = ? AND timecreated < ?',
            [$cmid, $userid, $cutoff]
        );
        $DB->execute(
            "UPDATE {videotrack_reactev}
                SET userid = :anonuserid, sessionid = :sessionid,
                    videotime = 0, playbackrate = 1, reactionlabel = :reactionlabel, reactiondesc = '',
                    notetext = CASE WHEN notetype = 'note' THEN :notetext ELSE notetext END
              WHERE cmid = :cmid AND userid = :userid AND timecreated < :cutoff",
            [
                'anonuserid' => $anonuserid,
                'sessionid' => $sessionid,
                'notetext' => $notetext,
                'reactionlabel' => get_string('privacy:anonymisedreaction', 'mod_videotrack'),
                'cmid' => $cmid,
                'userid' => $userid,
                'cutoff' => $cutoff,
            ]
        );

        $transaction->allow_commit();
    }

    /**
     * Anonymises state rows and safely merges with an existing anonymous state row.
     *
     * The state table has a unique index on (videotrackid, userid). If the same
     * user is anonymised more than once, or if partial retention already created an
     * anonymous row, a plain UPDATE can violate that index. This method merges the
     * real row into the anonymous row before deleting only the now-duplicate state
     * row.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     * @param int|null $cutoff Optional timemodified cutoff for retention task.
     */
    private static function anonymise_state_rows(int $userid, int $cmid, ?int $cutoff = null): void {
        global $DB;

        $select = 'cmid = :cmid AND userid = :userid';
        $params = ['cmid' => $cmid, 'userid' => $userid];
        if ($cutoff !== null) {
            $select .= ' AND timemodified < :cutoff';
            $params['cutoff'] = $cutoff;
        }

        $records = $DB->get_records_select('videotrack_state', $select, $params);
        foreach ($records as $record) {
            self::anonymise_one_state_row($record);
        }
    }

    /**
     * Anonymises a single state row, merging on unique-key collision.
     *
     * @param \stdClass $record Existing real-user state row.
     */
    private static function anonymise_one_state_row(\stdClass $record): void {
        global $DB;

        $anonuserid = self::anonymous_userid((int)$record->userid, (int)$record->cmid);
        $existing = $DB->get_record('videotrack_state', [
            'videotrackid' => $record->videotrackid,
            'userid' => $anonuserid,
        ]);

        if (!$existing) {
            $DB->set_field('videotrack_state', 'userid', $anonuserid, ['id' => $record->id]);
            return;
        }

        $existing->lastposition = max((float)$existing->lastposition, (float)$record->lastposition);
        $existing->durationseconds = max((float)$existing->durationseconds, (float)$record->durationseconds);
        $existing->uniquecoveredseconds = max((float)$existing->uniquecoveredseconds, (float)$record->uniquecoveredseconds);
        $existing->completionpercent = max((float)$existing->completionpercent, (float)$record->completionpercent);
        $existing->iscompleted = !empty($existing->iscompleted) || !empty($record->iscompleted) ? 1 : 0;
        $existing->timecreated = min((int)$existing->timecreated, (int)$record->timecreated);
        $existing->timemodified = max((int)$existing->timemodified, (int)$record->timemodified);
        $existing->intervaljson = self::merge_interval_json((string)$existing->intervaljson, (string)$record->intervaljson);

        $DB->update_record('videotrack_state', $existing);
        $DB->delete_records('videotrack_state', ['id' => $record->id]);
    }

    /**
     * Merges two JSON interval lists.
     *
     * @param string $left First JSON interval list.
     * @param string $right Second JSON interval list.
     * @return string Merged JSON interval list.
     */
    private static function merge_interval_json(string $left, string $right): string {
        $intervals = [];
        foreach ([$left, $right] as $json) {
            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                continue;
            }
            foreach ($decoded as $interval) {
                if (!is_array($interval) || count($interval) < 2) {
                    continue;
                }
                $start = (float)$interval[0];
                $end = (float)$interval[1];
                if ($end > $start) {
                    $intervals[] = [$start, $end];
                }
            }
        }

        if (!$intervals) {
            return '[]';
        }

        usort($intervals, static function(array $a, array $b): int {
            return $a[0] <=> $b[0];
        });

        $merged = [];
        foreach ($intervals as $interval) {
            if (!$merged || $interval[0] > $merged[count($merged) - 1][1]) {
                $merged[] = $interval;
                continue;
            }
            $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $interval[1]);
        }

        $json = json_encode($merged);
        return $json === false ? '[]' : $json;
    }
}
