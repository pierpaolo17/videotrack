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

namespace mod_videotrack\local;

/**
 * Tracking and completion helper methods for VideoTrack.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tracker {
    /**
     * Maximum number of merged intervals retained in intervaljson.
     *
     * Prevents unbounded field growth for users who watch many short,
     * non-overlapping fragments.
     */
    public const MAX_INTERVALS = 500;

    /**
     * Grace window in seconds for the OR branch of has_recent_playback().
     * Covers high-latency environments where the segment end timestamp may
     * lag actual playback end by up to this many seconds. Must be >= the
     * default $timetolerance parameter (8.0) to be meaningful.
     */
    public const PLAYBACK_GRACE_SECONDS = 12.0;

    /** @var array Per-request cache for reaction_counts(). Keyed by "videotrackid:userid". */
    private static $reactioncountscache = [];

    /**
     * Returns the current persisted state, or a safe in-memory default when no
     * state row exists yet. Used as a non-fatal fallback when the per-user state
     * lock is temporarily contended.
     *
     * @param stdClass $videotrack Activity instance.
     * @param cm_info $cm Course module info.
     * @param int $userid User id.
     * @return stdClass
     */
    private static function current_state_snapshot(\stdClass $videotrack, \cm_info $cm, int $userid): \stdClass {
        global $DB;

        $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
        if ($state) {
            return $state;
        }

        return self::create_default_state($videotrack, $cm, $userid);
    }

    /**
     * Normalises a playback interval and rejects empty or invalid ranges.
     *
     * @param float $start Interval start in seconds.
     * @param float $end Interval end in seconds.
     * @param float $duration Optional video duration used to clamp bounds.
     * @return array|null Normalised [start, end] interval, or null when invalid.
     */
    public static function normalise_interval(float $start, float $end, float $duration = 0.0): ?array {
        if ($duration > 0) {
            $start = max(0.0, min($start, $duration));
            $end = max(0.0, min($end, $duration));
        } else {
            $start = max(0.0, $start);
            $end = max(0.0, $end);
        }
        if ($end <= $start) {
            return null;
        }
        return [round($start, 3), round($end, 3)];
    }

    /**
     * Decodes a JSON list of intervals into safe normalised intervals.
     *
     * @param string|null $json Encoded interval JSON.
     * @return array Normalised interval list.
     */
    public static function decode_intervals(?string $json): array {
        if (empty($json)) {
            return [];
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $intervals = [];
        foreach ($data as $interval) {
            if (!is_array($interval) || count($interval) < 2 || !is_numeric($interval[0]) || !is_numeric($interval[1])) {
                continue;
            }
            $start = (float) $interval[0];
            $end = (float) $interval[1];
            if (!is_finite($start) || !is_finite($end)) {
                continue;
            }
            $normalised = self::normalise_interval($start, $end);
            if ($normalised !== null) {
                $intervals[] = $normalised;
            }
        }
        return $intervals;
    }

    /**
     * Encodes intervals for persistence in videotrack_state.intervaljson.
     *
     * @param array $intervals Interval list.
     * @return string Encoded JSON representation.
     */
    public static function encode_intervals(array $intervals): string {
        return json_encode(array_values($intervals));
    }

    /**
     * Merges overlapping intervals while preserving watched coverage.
     *
     * @param array $intervals Interval list.
     * @return array Merged interval list.
     */
    public static function merge_intervals(array $intervals): array {
        if (!$intervals) {
            return [];
        }
        usort($intervals, static function ($a, $b) {
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
        return $merged;
    }

    /**
     * If the number of intervals exceeds MAX_INTERVALS, keep the longest
     * intervals and discard smaller fragments. This avoids inventing watched
     * coverage by merging unseen gaps, at the cost of controlled precision loss
     * in extreme cases.
     *
     * @param array $intervals Interval list.
     * @return array Capped interval list.
     */
    public static function cap_intervals(array $intervals): array {
        if (count($intervals) <= self::MAX_INTERVALS) {
            return $intervals;
        }
        return self::simplify_intervals($intervals, self::MAX_INTERVALS);
    }

    /**
     * Reduce the interval array to the target count without merging unseen gaps.
     *
     * The previous approach merged pairs with the smallest gap, which made the
     * unseen gap part of the resulting interval and artificially increased
     * uniquecoveredseconds.
     *
     * The safe approach is to truncate the array after sorting by interval length.
     * Short marginal fragments are discarded, but total watched coverage is never
     * overestimated. Precision is lost only in rare cases with more than 500
     * distinct intervals.
     *
     * @param array $intervals Array of already-merged [start, end] intervals.
     * @param int $target Maximum number of intervals to keep.
     * @return array
     */
    public static function simplify_intervals(array $intervals, int $target): array {
        $n = count($intervals);
        if ($n <= $target) {
            return $intervals;
        }
        // Sort by descending length and keep the longest $target intervals.
        usort($intervals, static function ($a, $b) {
            return ($b[1] - $b[0]) <=> ($a[1] - $a[0]);
        });
        $kept = array_slice($intervals, 0, $target);
        // Re-sort by timeline position for deterministic output.
        usort($kept, static function ($a, $b) {
            return $a[0] <=> $b[0];
        });
        return $kept;
    }

    /**
     * Calculates the total covered seconds represented by interval ranges.
     *
     * @param array $intervals Interval list.
     * @return float Covered seconds rounded to milliseconds.
     */
    public static function covered_seconds(array $intervals): float {
        $total = 0.0;
        foreach ($intervals as $interval) {
            $total += max(0.0, $interval[1] - $interval[0]);
        }
        return round($total, 3);
    }

    /**
     * Returns cached reaction counters for one user/activity pair.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @return array Reaction summary with event count, unique count and ids.
     */
    public static function reaction_counts(int $videotrackid, int $userid): array {
        global $DB;
        // O1: per-request cache to avoid repeated identical DB queries within the same
        // HTTP request (e.g. save_reaction calls this once, then refresh_completion
        // calls it again internally). Invalidated via invalidate_reactioncountscache()
        // after any insert/delete on videotrack_reactev.
        // Uses a static class property (not a method-local static) so that
        // invalidate_reactioncountscache() can reliably clear the same variable.
        $key = $videotrackid . ':' . $userid;
        if (isset(self::$reactioncountscache[$key])) {
            return self::$reactioncountscache[$key];
        }
        $p = ['vtid' => $videotrackid, 'uid' => $userid];
        $where = "videotrackid = :vtid AND userid = :uid AND isdeleted = 0
                  AND reactionid > 0 AND (notetype = '' OR notetype IS NULL)";
        // Use two separate queries to avoid GROUP_CONCAT truncation on MySQL.
        $row = $DB->get_record_sql(
            "SELECT COUNT(*) AS eventcount, COUNT(DISTINCT reactionid) AS uniquecount
               FROM {videotrack_reactev} WHERE $where",
            $p
        );
        $ids = $DB->get_fieldset_sql(
            "SELECT DISTINCT reactionid FROM {videotrack_reactev} WHERE $where ORDER BY reactionid",
            $p
        );
        $result = [
            'eventcount' => (int) ($row->eventcount ?? 0),
            'uniquecount' => (int) ($row->uniquecount ?? 0),
            'uniqueids' => array_map('intval', $ids),
        ];
        self::$reactioncountscache[$key] = $result;
        return $result;
    }

    /**
     * Invalidates the per-request cache for reaction_counts.
     * Must be called after any insert or soft-delete on videotrack_reactev
     * to ensure subsequent calls within the same request see fresh data.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     */
    public static function invalidate_reactioncountscache(int $videotrackid, int $userid): void {
        $key = $videotrackid . ':' . $userid;
        unset(self::$reactioncountscache[$key]);
    }

    /**
     * Returns true when a reaction or note is backed by a recent playback heartbeat.
     *
     * The browser UI hides these controls outside PLAYING, but this server-side check
     * prevents direct AJAX calls from creating reactions/notes at arbitrary timestamps.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video timestamp in seconds.
     * @param int $recentseconds Recent playback window in seconds.
     * @param float $timetolerance Timestamp tolerance in seconds.
     * @return bool Whether recent playback authorises the action.
     */
    public static function has_recent_playback(
        int $videotrackid,
        int $userid,
        string $sessionid,
        float $videotime,
        int $recentseconds = 20,
        float $timetolerance = 8.0
    ): bool {
        global $DB;
        // Vtstart/vtend intentionally carry the same value, and tolstart/tolend do the same.
        // Distinct placeholders avoid driver issues with reusing the same named
        // parameter more than once in a query.
        $vt = max(0.0, $videotime);
        $tol = max(1.0, $timetolerance);
        $since = time() - max(5, $recentseconds);
        // S1 fix: replace the magic number 12.0 with a named constant.
        // This grace window covers high-latency environments where the segment
        // end timestamp may lag the actual end of playback by up to 12 seconds
        // (e.g. slow mobile connections or deferred heartbeat delivery).
        // The value is intentionally larger than $timetolerance to accept
        // reactions/notes triggered just after a segment has nominally ended.
        $graceseconds = max($tol, self::PLAYBACK_GRACE_SECONDS);
        $params = [
            'vtid'  => $videotrackid,
            'uid'   => $userid,
            'sid'   => $sessionid,
            'since' => $since,
            'vt'    => $vt,
            'vt2'   => $vt,
            'tol1'  => $tol,
            'tol2'  => $tol,
            'tolend' => $graceseconds,
        ];

        // A single query covers both the strict interval match and the grace
        // period used for clicks immediately after PLAYING/seek in high-latency
        // environments. This avoids duplicate DB checks for each reaction/note.
        $select = 'videotrackid = :vtid AND userid = :uid AND sessionid = :sid AND timecreated >= :since
             AND ((:vt >= (videotimestart - :tol1) AND :vt2 <= (videotimeend + :tol2))
                  OR ABS(videotimeend - :vt3) <= :tolend)';
        if ($DB->record_exists_select('videotrack_seg', $select, $params + ['vt3' => $vt])) {
            return true;
        }

        if ((int) get_config('mod_videotrack', 'strictsessionvalidation')) {
            return false;
        }

        // UX-friendly fallback: after refreshes, browser changes or a longer pause,
        // accept playback for the same user/activity even when the browser session id
        // changed or the heartbeat is no longer recent. The timestamp must still be
        // inside a watched interval, so direct calls cannot create notes/reactions on
        // unwatched positions.
        if (
            $DB->record_exists_select(
                'videotrack_seg',
                'videotrackid = :vtid AND userid = :uid AND timecreated >= :since
                 AND ((:vt >= (videotimestart - :tol1) AND :vt2 <= (videotimeend + :tol2))
                      OR ABS(videotimeend - :vt3) <= :tolend)',
                [
                    'vtid' => $videotrackid,
                    'uid' => $userid,
                    'since' => $since,
                    'vt' => $vt,
                    'vt2' => $vt,
                    'vt3' => $vt,
                    'tol1' => $tol,
                    'tol2' => $tol,
                    'tolend' => $graceseconds,
                ]
            )
        ) {
            return true;
        }

        // Global helper from locallib.php; the leading backslash selects the global namespace.
        $fallbackdays = \videotrack_get_config_int('validationfallbackdays', 30, 0, 3650);
        $maxage = $fallbackdays > 0 ? $fallbackdays * DAYSECS : 0;

        return self::has_watched_videotime($videotrackid, $userid, $sessionid, $videotime, 2.0, $maxage);
    }

    /**
     * Returns true when the requested video time is inside a watched segment.
     *
     * This check is used for notes and reactions: the target timestamp must fall
     * inside a recorded watched segment. By default it accepts previous browser
     * sessions for the same user/activity to avoid false negatives after refreshes;
     * administrators can enable strict same-session validation in plugin settings.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video timestamp in seconds.
     * @param float $timetolerance Small tolerance for heartbeat/network delay.
     * @param int $maxageseconds Maximum fallback age in seconds, or zero for no limit.
     * @return bool Whether the timestamp is inside a watched segment.
     */
    public static function has_watched_videotime(
        int $videotrackid,
        int $userid,
        string $sessionid,
        float $videotime,
        float $timetolerance = 2.0,
        int $maxageseconds = 0
    ): bool {
        global $DB;

        $vt = max(0.0, $videotime);
        $tol = max(0.5, $timetolerance);
        $params = [
            'vtid' => $videotrackid,
            'uid' => $userid,
            'sid' => $sessionid,
            'vt1' => $vt,
            'vt2' => $vt,
            'tol1' => $tol,
            'tol2' => $tol,
        ];

        $samesessionselect = 'videotrackid = :vtid AND userid = :uid AND sessionid = :sid
             AND :vt1 >= (videotimestart - :tol1)
             AND :vt2 <= (videotimeend + :tol2)';
        if ($DB->record_exists_select('videotrack_seg', $samesessionselect, $params)) {
            return true;
        }

        if ((int) get_config('mod_videotrack', 'strictsessionvalidation')) {
            return false;
        }

        // UX-friendly fallback: after refreshes or browser changes, allow notes and
        // reactions for timestamps already watched by the same user in this activity.
        // This still rejects unwatched positions because the timestamp must fall
        // inside a recorded segment. A configurable age limit prevents very old
        // playback from authorising new interactions indefinitely.
        $fallbackselect = 'videotrackid = :vtid AND userid = :uid
             AND :vt1 >= (videotimestart - :tol1)
             AND :vt2 <= (videotimeend + :tol2)';
        $fallbackparams = [
            'vtid' => $videotrackid,
            'uid' => $userid,
            'vt1' => $vt,
            'vt2' => $vt,
            'tol1' => $tol,
            'tol2' => $tol,
        ];
        if ($maxageseconds > 0) {
            $fallbackselect .= ' AND timecreated >= :fallbacksince';
            $fallbackparams['fallbacksince'] = time() - $maxageseconds;
        }

        return $DB->record_exists_select('videotrack_seg', $fallbackselect, $fallbackparams);
    }

    /**
     * Evaluates the custom VideoTrack completion rules.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \stdClass|null $state Persisted user state.
     * @param array $reactionsummary Reaction summary.
     * @param array $requiredreactionids Required reaction ids.
     * @return bool Whether custom completion rules are satisfied.
     */
    public static function completion_satisfied(
        \stdClass $videotrack,
        ?\stdClass $state,
        array $reactionsummary,
        array $requiredreactionids
    ): bool {
        $checks = [];
        if (!empty($videotrack->completionpercent)) {
            $checks[] = !empty($state) && (float)$state->completionpercent >= (float) $videotrack->completionpercent;
        }
        if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
            $checks[] = $reactionsummary['uniquecount'] >= (int) $videotrack->minreactions;
        }
        foreach ($requiredreactionids as $reactionid) {
            $checks[] = in_array((int) $reactionid, $reactionsummary['uniqueids'], true);
        }
        if (!empty($videotrack->requireallreactiontypes)) {
            global $DB;
            $allreactionids = array_map('intval', array_keys((array) $DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'isdeleted' => 0,
            ], '', 'id,id')));
            if ($allreactionids) {
                $matchingids = array_intersect($allreactionids, array_map('intval', $reactionsummary['uniqueids']));
                $checks[] = count($matchingids) === count($allreactionids);
            }
        }
        if (!$checks) {
            // Returning false here is intentional: when no custom completion
            // rules are enabled, Moodle falls back to FEATURE_COMPLETION_TRACKS_VIEWS
            // and treats the page visit as the completion condition at framework level.
            return false;
        }
        $logic = $videotrack->completionlogic ?? 'and';
        if ($logic === 'or') {
            return in_array(true, $checks, true);
        }
        foreach ($checks as $check) {
            if (!$check) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates the default aggregate state record for a user/activity pair.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $userid User id.
     * @return \stdClass Unsaved default state record.
     */
    private static function create_default_state(\stdClass $videotrack, \cm_info $cm, int $userid): \stdClass {
        return (object)[
            'videotrackid'         => $videotrack->id,
            'courseid'             => $videotrack->course,
            'cmid'                 => $cm->id,
            'userid'               => $userid,
            'videoid'              => $videotrack->videoid,
            'lastposition'         => 0,
            'durationseconds'      => (float) ($videotrack->durationseconds ?? 0),
            'uniquecoveredseconds' => 0,
            'completionpercent'    => 0,
            'intervaljson'         => '[]',
            'iscompleted'          => 0,
            'timemodified'         => time(),
            'timecreated'          => time(),
        ];
    }

    /**
     * Update the aggregated viewing state for one user.
     *
     * Inserts the raw segment and updates videotrack_state in a single atomic
     * transaction: if anything fails, no orphan segment remains in the database.
     *
     * @param stdClass  $videotrack   Activity instance.
     * @param cm_info   $cm           Course module.
     * @param int       $userid       User id.
     * @param array     $interval     Normalised [start, end] interval.
     * @param float     $lastposition Resume position.
     * @param stdClass|null $segment  Segment record to insert, or null when none is needed.
     * @param int|null  &$segmentid   Set to the inserted segment id.
     * @return stdClass               Updated state.
     */
    public static function update_state(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        array $interval,
        float $lastposition,
        ?\stdClass $segment = null,
        ?int &$segmentid = null
    ): \stdClass {
        global $DB;

        // Serialise concurrent updates to the same user/activity state record.
        // The transaction protects atomicity; the lock prevents concurrent inserts
        // on the unique videotrack_state record when heartbeat and pagehide arrive
        // almost simultaneously.
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lockkey = 'state:' . $videotrack->id . ':' . $userid;
        $lock = $lockfactory->get_lock($lockkey, 10);
        if (!$lock) {
            // Under very high concurrency another request is already updating
            // the same aggregate row. Avoid surfacing a lock timeout to the
            // student; return the last committed state and let the next
            // heartbeat/pagehide retry the write.
            if ($segment !== null) {
                $segmentid = 0;
            }
            return self::current_state_snapshot($videotrack, $cm, $userid);
        }

        // Use a transaction to serialise concurrent writes, for example heartbeat plus pagehide.
        $transaction = $DB->start_delegated_transaction();
        $state = null;
        try {
            // Insert the raw segment inside the transaction so it remains atomic with update_state.
            if ($segment !== null) {
                $segmentid = $DB->insert_record('videotrack_seg', $segment);
            }
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
            if (!$state) {
                $state = self::create_default_state($videotrack, $cm, $userid);
            }
            $intervals = self::decode_intervals($state->intervaljson);
            $intervals[] = $interval;
            $intervals = self::merge_intervals($intervals);
            $intervals = self::cap_intervals($intervals);
            $covered = self::covered_seconds($intervals);
            $duration = max((float) $videotrack->durationseconds, (float) $state->durationseconds);
            $percent = $duration > 0 ? min(100.0, round(($covered / $duration) * 100, 2)) : 0.0;

            $requiredreactionids = array_keys(array_filter((array) $DB->get_records_menu('videotrack_react', [
                'videotrackid'          => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted'             => 0,
            ], '', 'id,id')));
            $reactionsummary = self::reaction_counts($videotrack->id, $userid);

            // Lastposition is the end of the current segment for automatic resume.
            // Use the current value only when it is greater than 2 seconds to avoid
            // resuming from negligible positions. Do not use the historical max():
            // resume should point to where the user stopped watching, not the furthest point reached.
            if ($lastposition > 2.0) {
                $state->lastposition = $lastposition;
            }
            $state->durationseconds      = $duration;
            $state->uniquecoveredseconds = $covered;
            $state->completionpercent    = $percent;
            $state->intervaljson         = self::encode_intervals($intervals);
            $wascompleted = !empty($state->id) ? (int) ($state->iscompleted ?? 0) : 0;
            $state->iscompleted = self::completion_satisfied(
                $videotrack,
                $state,
                $reactionsummary,
                $requiredreactionids
            ) ? 1 : 0;
            $state->timemodified = time();

            if (!empty($state->id)) {
                $DB->update_record('videotrack_state', $state);
            } else {
                $state->id = $DB->insert_record('videotrack_state', $state);
            }
            $transaction->allow_commit();
            $lock->release();
            $lock = null;

            // Emit activity_completed on the first 0-to-1 transition.
            // This is outside the transaction because the event is not critical data.
            if (!$wascompleted && $state->iscompleted) {
                $completedevent = \mod_videotrack\event\activity_completed::create([
                    'objectid' => $state->id,
                    'context'  => \context_module::instance($cm->id),
                    'userid'   => $userid,
                    'other'    => [
                        'completionpercent'    => $state->completionpercent,
                        'uniquecoveredseconds' => $state->uniquecoveredseconds,
                    ],
                ]);
                $completedevent->trigger();
            }
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            // Rollback() already rethrows in Moodle, but rethrow explicitly to ensure
            // future framework changes never return a silent null state to callers.
            throw $e;
        }
        return $state;
    }

    /**
     * Synchronises Moodle completion only when the persisted completion state
     * differs from the computed VideoTrack state. This avoids redundant writes
     * on every heartbeat/reaction while preserving normal completion semantics.
     *
     * @param \completion_info $completion Course completion helper.
     * @param \cm_info $cm Course module info.
     * @param bool $iscompleted Computed VideoTrack completion state.
     * @param int $userid User id.
     */
    public static function update_moodle_completion_if_changed(
        \completion_info $completion,
        \cm_info $cm,
        bool $iscompleted,
        int $userid
    ): void {
        $target = $iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        $current = $completion->get_data($cm, false, $userid);
        $currentstate = isset($current->completionstate) ? (int) $current->completionstate : COMPLETION_INCOMPLETE;

        $completestates = [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL];
        $currentlycomplete = in_array($currentstate, $completestates, true);
        if (($iscompleted && !$currentlycomplete) || (!$iscompleted && $currentstate !== COMPLETION_INCOMPLETE)) {
            $completion->update_state($cm, $target, $userid);
        }
    }

    /**
     * Aggregates persisted raw segments without trusting an existing state snapshot.
     *
     * @param iterable $segments Segment records containing videotimestart and videotimeend.
     * @param float $duration Video duration used to clamp segment bounds.
     * @return array Aggregate values: intervals, coveredseconds and lastposition.
     */
    public static function aggregate_segments(iterable $segments, float $duration): array {
        $intervals = [];
        $lastposition = 0.0;
        $latesttime = -1;
        $latestid = -1;
        foreach ($segments as $segment) {
            $normalised = self::normalise_interval(
                (float)$segment->videotimestart,
                (float)$segment->videotimeend,
                $duration
            );
            if ($normalised === null) {
                continue;
            }
            $intervals[] = $normalised;
            $timecreated = (int)($segment->timecreated ?? 0);
            $segmentid = (int)($segment->id ?? 0);
            if ($timecreated > $latesttime || ($timecreated === $latesttime && $segmentid > $latestid)) {
                $latesttime = $timecreated;
                $latestid = $segmentid;
                $lastposition = $normalised[1];
            }
        }
        $intervals = self::cap_intervals(self::merge_intervals($intervals));
        return [
            'intervals' => $intervals,
            'coveredseconds' => self::covered_seconds($intervals),
            'lastposition' => round($lastposition, 3),
        ];
    }

    /**
     * Rebuilds one user's aggregate state from raw segment rows and reaction events.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $userid User id.
     * @return \stdClass|null Rebuilt state, or null when the state lock is busy.
     */
    public static function rebuild_state_from_segments(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid
    ): ?\stdClass {
        global $DB;

        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lock = $lockfactory->get_lock('state:' . $videotrack->id . ':' . $userid, 10);
        if (!$lock) {
            return null;
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $state = $DB->get_record('videotrack_state', [
                'videotrackid' => $videotrack->id,
                'userid' => $userid,
            ]);
            if (!$state) {
                $state = self::create_default_state($videotrack, $cm, $userid);
            }

            $maxsegmentend = (float)$DB->get_field_sql(
                'SELECT COALESCE(MAX(videotimeend), 0)
                   FROM {videotrack_seg}
                  WHERE videotrackid = :vtid AND userid = :userid',
                ['vtid' => $videotrack->id, 'userid' => $userid]
            );
            $configuredduration = max(0.0, (float)($videotrack->durationseconds ?? 0));
            $duration = $configuredduration > 0
                ? $configuredduration
                : max(0.0, (float)($state->durationseconds ?? 0), $maxsegmentend);
            $segments = $DB->get_recordset('videotrack_seg', [
                'videotrackid' => $videotrack->id,
                'userid' => $userid,
            ], 'timecreated ASC, id ASC', 'id, videotimestart, videotimeend, timecreated');
            $aggregate = self::aggregate_segments($segments, $duration);
            $segments->close();

            $state->courseid = $videotrack->course;
            $state->cmid = $cm->id;
            $state->videoid = $videotrack->videoid;
            $state->lastposition = $aggregate['lastposition'];
            $state->durationseconds = $duration;
            $state->uniquecoveredseconds = $aggregate['coveredseconds'];
            $state->completionpercent = $duration > 0
                ? min(100.0, round(($aggregate['coveredseconds'] / $duration) * 100, 2))
                : 0.0;
            $state->intervaljson = self::encode_intervals($aggregate['intervals']);

            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id')));
            $state->iscompleted = self::completion_satisfied(
                $videotrack,
                $state,
                self::reaction_counts($videotrack->id, $userid),
                $requiredreactionids
            ) ? 1 : 0;
            $state->timemodified = time();

            if (!empty($state->id)) {
                $DB->update_record('videotrack_state', $state);
            } else {
                $state->id = $DB->insert_record('videotrack_state', $state);
            }
            $transaction->allow_commit();
            $lock->release();
            $lock = null;
            return $state;
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Recomputes and persists completion state for one user/activity pair.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $userid User id.
     * @param array|null $reactionsummary Optional precomputed reaction summary.
     * @param array|null $requiredreactionids Optional required reaction ids.
     * @return \stdClass Updated state.
     */
    public static function refresh_completion(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        ?array $reactionsummary = null,
        ?array $requiredreactionids = null
    ): \stdClass {
        global $DB;

        // Use the same lock as update_state(): refresh_completion can be called
        // by endpoints other than save_segment and must avoid concurrent inserts
        // on the unique videotrack_state record.
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lockkey = 'state:' . $videotrack->id . ':' . $userid;
        $lock = $lockfactory->get_lock($lockkey, 10);
        if (!$lock) {
            // Non-fatal fallback: completion will be refreshed by the next
            // successful state/reaction update. This prevents transient lock
            // contention from becoming a visible AJAX error for students.
            return self::current_state_snapshot($videotrack, $cm, $userid);
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
            if (!$state) {
                $state = self::create_default_state($videotrack, $cm, $userid);
                $state->id = $DB->insert_record('videotrack_state', $state);
            }

            if ($requiredreactionids === null) {
                $requiredreactionids = array_keys(array_filter((array) $DB->get_records_menu('videotrack_react', [
                    'videotrackid' => $videotrack->id,
                    'requiredforcompletion' => 1,
                    'isdeleted' => 0,
                ], '', 'id,id')));
            }
            if ($reactionsummary === null) {
                $reactionsummary = self::reaction_counts($videotrack->id, $userid);
            }
            $state->iscompleted = self::completion_satisfied(
                $videotrack,
                $state,
                $reactionsummary,
                $requiredreactionids
            ) ? 1 : 0;
            $state->timemodified = time();
            $DB->update_record('videotrack_state', $state);

            $transaction->allow_commit();
            $lock->release();
            $lock = null;
            return $state;
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            throw $e;
        }
    }
}
