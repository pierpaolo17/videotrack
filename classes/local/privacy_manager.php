<?php
namespace mod_videotrack\local;

defined('MOODLE_INTERNAL') || die();

use context;

/**
 * Privacy helpers for VideoTrack.
 *
 * User initiated erasure requests anonymise tracking data instead of deleting it,
 * preserving aggregate statistics while removing the link to the real user.
 */
class privacy_manager {
    /** Prefix used for anonymised browser session identifiers. */
    private const ANONYMOUS_SESSION_PREFIX = 'anon-';

    /**
     * Returns the configured retention period in seconds.
     *
     * A value of 0 means unlimited retention: data is kept until a user requests
     * erasure via Moodle privacy tools, at which point it is anonymised.
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
     * Builds a stable negative user id that cannot collide with normal Moodle users.
     *
     * @param int $userid Real user id.
     * @return int Anonymous user id.
     */
    public static function anonymous_userid(int $userid): int {
        return -1 * (100000000 + ($userid % 800000000));
    }

    /**
     * Builds a deterministic non-identifying session id.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     * @return string
     */
    private static function anonymous_sessionid(int $userid, int $cmid): string {
        return self::ANONYMOUS_SESSION_PREFIX . sha1($userid . ':' . $cmid . ':videotrack');
    }

    /**
     * Anonymises all personal tracking records for one user in one module context.
     *
     * @param context $context Moodle context.
     * @param int $userid Real user id.
     */
    public static function anonymise_user_in_context(context $context, int $userid): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE || $userid <= 0) {
            return;
        }

        $cmid = (int)$context->instanceid;
        self::anonymise_user_records($userid, $cmid);
    }

    /**
     * Anonymises one user's records for a course module.
     *
     * @param int $userid Real user id.
     * @param int $cmid Course module id.
     */
    private static function anonymise_user_records(int $userid, int $cmid): void {
        global $DB;

        $anonuserid = self::anonymous_userid($userid);
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

        $DB->execute(
            "UPDATE {videotrack_state}
                SET userid = :anonuserid
              WHERE cmid = :cmid AND userid = :userid",
            $params
        );

        $eventparams = $params + ['notetext' => $notetext];
        $DB->execute(
            "UPDATE {videotrack_reactev}
                SET userid = :anonuserid, sessionid = :sessionid,
                    notetext = CASE WHEN notetype = 'note' THEN :notetext ELSE notetext END
              WHERE cmid = :cmid AND userid = :userid",
            $eventparams
        );
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
            return ['segments' => 0, 'states' => 0, 'events' => 0];
        }

        $cutoff = time() - $retention;
        $counts = ['segments' => 0, 'states' => 0, 'events' => 0];

        $records = $DB->get_recordset_select(
            'videotrack_seg',
            'userid > 0 AND timecreated < ?',
            [$cutoff],
            '',
            'DISTINCT userid, cmid'
        );
        foreach ($records as $record) {
            self::anonymise_old_user_rows((int)$record->userid, (int)$record->cmid, $cutoff, $counts);
        }
        $records->close();

        $records = $DB->get_recordset_select(
            'videotrack_reactev',
            'userid > 0 AND timecreated < ?',
            [$cutoff],
            '',
            'DISTINCT userid, cmid'
        );
        foreach ($records as $record) {
            self::anonymise_old_user_rows((int)$record->userid, (int)$record->cmid, $cutoff, $counts);
        }
        $records->close();

        $records = $DB->get_recordset_select(
            'videotrack_state',
            'userid > 0 AND timemodified < ?',
            [$cutoff],
            '',
            'DISTINCT userid, cmid'
        );
        foreach ($records as $record) {
            self::anonymise_old_user_rows((int)$record->userid, (int)$record->cmid, $cutoff, $counts);
        }
        $records->close();

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

        $anonuserid = self::anonymous_userid($userid);
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
        $DB->execute(
            "UPDATE {videotrack_state}
                SET userid = :anonuserid
              WHERE cmid = :cmid AND userid = :userid AND timemodified < :cutoff",
            [
                'anonuserid' => $anonuserid,
                'cmid' => $cmid,
                'userid' => $userid,
                'cutoff' => $cutoff,
            ]
        );

        $counts['events'] += $DB->count_records_select(
            'videotrack_reactev',
            'cmid = ? AND userid = ? AND timecreated < ?',
            [$cmid, $userid, $cutoff]
        );
        $DB->execute(
            "UPDATE {videotrack_reactev}
                SET userid = :anonuserid, sessionid = :sessionid,
                    notetext = CASE WHEN notetype = 'note' THEN :notetext ELSE notetext END
              WHERE cmid = :cmid AND userid = :userid AND timecreated < :cutoff",
            [
                'anonuserid' => $anonuserid,
                'sessionid' => $sessionid,
                'notetext' => $notetext,
                'cmid' => $cmid,
                'userid' => $userid,
                'cutoff' => $cutoff,
            ]
        );
    }
}
