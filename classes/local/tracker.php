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

    /** Maximum cumulative provider/server clock drift accepted by the playback ledger. */
    private const SERVER_CREDIT_TOLERANCE_SECONDS = 1.0;

    /** Maximum instrumentation gap tolerated at the beginning of the first watched interval. */
    private const PLAYBACK_START_TOLERANCE_SECONDS = 0.25;

    /** Maximum provider-timeline gap tolerated when a validated segment ends naturally. */
    private const NATURAL_END_TOLERANCE_SECONDS = 1.25;

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
     * Recover a tiny instrumentation gap at the beginning of watched coverage.
     *
     * YouTube and HTML5 start the server playback handshake before the first
     * persisted segment is opened. A short AJAX round trip can therefore make a
     * genuine watch-from-zero interval begin a few milliseconds after 0. This
     * bounded correction prevents that transport delay from making 100% coverage
     * mathematically impossible while never bridging a meaningful unwatched gap.
     *
     * @param array $intervals Already merged intervals in timeline order.
     * @param float $duration Configured video duration.
     * @return array Boundary-normalised intervals.
     */
    public static function normalise_start_boundary(array $intervals, float $duration): array {
        if (!$intervals || $duration <= 0) {
            return $intervals;
        }
        $start = (float)$intervals[0][0];
        if ($start > 0.0 && $start <= self::PLAYBACK_START_TOLERANCE_SECONDS) {
            $intervals[0][0] = 0.0;
        }
        return $intervals;
    }

    /**
     * Recover a bounded provider timeline discrepancy at a natural video end.
     *
     * Some providers can emit their natural ended state while getCurrentTime()
     * remains slightly below the configured/provider duration. Only an accepted
     * segment explicitly closed as "ended" may receive this correction. Pauses,
     * seeks and other segment reasons never gain terminal coverage.
     *
     * @param array $interval Normalised [start, end] interval.
     * @param float $duration Configured video duration.
     * @param string $endreason Segment end reason.
     * @return array Boundary-normalised interval.
     */
    public static function normalise_natural_end(array $interval, float $duration, string $endreason): array {
        if ($duration <= 0 || $endreason !== 'ended') {
            return $interval;
        }
        $gap = $duration - (float)$interval[1];
        if ($gap > 0.0 && $gap <= self::NATURAL_END_TOLERANCE_SECONDS) {
            $interval[1] = round($duration, 3);
        }
        return $interval;
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

        $samesessionselect = 'videotrackid = :vtid AND userid = :uid AND sessionid = :sid AND servervalidated = 1
             AND :vt1 >= (videotimestart - :tol1)
             AND :vt2 <= (videotimeend + :tol2)';
        if ($DB->record_exists_select('videotrack_seg', $samesessionselect, $params)) {
            return true;
        }

        if ((int) get_config('mod_videotrack', 'strictsessionvalidation')) {
            return false;
        }

        return self::has_watched_videotime_any_session(
            $videotrackid,
            $userid,
            $videotime,
            $timetolerance,
            $maxageseconds
        );
    }

    /**
     * Checks whether a timestamp was watched in any validated session for one user.
     *
     * This is used by server-side actions that do not carry a player session id,
     * such as the Forum composer. Raw validated segments are preferred, with the
     * canonical aggregate state used as a fallback after segment compaction.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param float $videotime Video timestamp in seconds.
     * @param float $timetolerance Small tolerance for heartbeat/network delay.
     * @param int $maxageseconds Maximum evidence age in seconds, or zero for no limit.
     * @return bool Whether the timestamp is inside previously watched progress.
     */
    public static function has_watched_videotime_any_session(
        int $videotrackid,
        int $userid,
        float $videotime,
        float $timetolerance = 2.0,
        int $maxageseconds = 0
    ): bool {
        global $DB;

        $vt = max(0.0, $videotime);
        $tol = max(0.5, $timetolerance);

        // UX-friendly fallback: after refreshes or browser changes, allow notes and
        // reactions for timestamps already watched by the same user in this activity.
        // This still rejects unwatched positions because the timestamp must fall
        // inside a recorded segment. A configurable age limit prevents very old
        // playback from authorising new interactions indefinitely.
        $fallbackselect = 'videotrackid = :vtid AND userid = :uid AND servervalidated = 1
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

        if ($DB->record_exists_select('videotrack_seg', $fallbackselect, $fallbackparams)) {
            return true;
        }

        // Raw segments may have been compacted or removed while the canonical
        // aggregate state still retains the user's watched intervals. Use that
        // state as a final, privacy-safe validation source when strict same-session
        // validation is disabled.
        $state = $DB->get_record(
            'videotrack_state',
            ['videotrackid' => $videotrackid, 'userid' => $userid],
            'intervaljson, timemodified'
        );
        if (!$state) {
            return false;
        }
        if ($maxageseconds > 0 && (int)$state->timemodified < time() - $maxageseconds) {
            return false;
        }
        foreach (self::decode_intervals($state->intervaljson ?? null) as [$start, $end]) {
            if ($vt >= ($start - $tol) && $vt <= ($end + $tol)) {
                return true;
            }
        }
        return false;
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
            $checks[] = !empty($state) && (float)$state->completionpercent >= (float)$videotrack->completionpercent;
        }

        $reactionchecks = [];
        if (!empty($videotrack->reactionsenabled)) {
            if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
                $reactionchecks[] = $reactionsummary['uniquecount'] >= (int)$videotrack->minreactions;
            }
            foreach ($requiredreactionids as $reactionid) {
                $reactionchecks[] = in_array((int)$reactionid, $reactionsummary['uniqueids'], true);
            }
            if (!empty($videotrack->requireallreactiontypes)) {
                global $DB;
                $allreactionids = array_map('intval', array_keys((array)$DB->get_records_menu('videotrack_react', [
                    'videotrackid' => $videotrack->id,
                    'isdeleted' => 0,
                ], '', 'id,id')));
                if ($allreactionids) {
                    $matchingids = array_intersect($allreactionids, array_map('intval', $reactionsummary['uniqueids']));
                    $reactionchecks[] = count($matchingids) === count($allreactionids);
                }
            }
        }
        if ($reactionchecks) {
            $reactionlogic = $videotrack->completionlogic ?? 'and';
            if ($reactionlogic === 'or') {
                $checks[] = in_array(true, $reactionchecks, true);
            } else {
                $checks[] = !in_array(false, $reactionchecks, true);
            }
        }

        if (!empty($videotrack->completionacknowledgement) && acknowledgement::is_enabled($videotrack)) {
            $userid = !empty($state->userid) ? (int)$state->userid : 0;
            $checks[] = $userid > 0 && acknowledgement::current_record($videotrack, $userid) !== null;
        }
        if (!$checks) {
            // Returning false here is intentional: when no custom completion
            // rules are enabled, Moodle falls back to FEATURE_COMPLETION_TRACKS_VIEWS
            // and treats the page visit as the completion condition at framework level.
            return false;
        }
        $completionlogic = $videotrack->completionlogic ?? 'and';
        if ($completionlogic === 'or') {
            return in_array(true, $checks, true);
        }
        return !in_array(false, $checks, true);
    }

    /**
     * Checks whether an interaction timestamp is valid for the current playback policy.
     *
     * Previously watched timestamps are always accepted. When forward seeking is
     * enabled, a newly reached timestamp may not yet have a validated segment at the
     * instant an interaction is saved. In that case, require recent server-side
     * playback evidence from the same browser session before accepting the timestamp.
     * This preserves anti-forgery protection while avoiding false negatives after a
     * legitimate forward seek.
     *
     * @param stdClass $videotrack Activity instance.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Requested interaction timestamp.
     * @param float $timetolerance Watched-time tolerance in seconds.
     * @param int $maxageseconds Maximum fallback age for persisted watched evidence.
     * @return bool Whether the interaction timestamp is allowed.
     */
    public static function interaction_timestamp_allowed(
        \stdClass $videotrack,
        int $userid,
        string $sessionid,
        float $videotime,
        float $timetolerance = 2.0,
        int $maxageseconds = 0
    ): bool {
        global $DB;

        if (
            self::has_watched_videotime(
                (int)$videotrack->id,
                $userid,
                $sessionid,
                $videotime,
                $timetolerance,
                $maxageseconds
            )
        ) {
            return true;
        }
        if (empty($videotrack->allowseekforward)) {
            return false;
        }

        $heartbeat = \videotrack_get_config_int('heartbeatinterval', 30, 5, 300);
        $recentwindow = min(610, max(30, ($heartbeat * 2) + 10));
        return $DB->record_exists_select(
            'videotrack_seg',
            'videotrackid = :vtid AND userid = :uid AND sessionid = :sid AND timecreated >= :since '
                . "AND (servervalidated = 1 OR endreason = 'playstart')",
            [
                'vtid' => (int)$videotrack->id,
                'uid' => $userid,
                'sid' => $sessionid,
                'since' => time() - $recentwindow,
            ]
        );
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
            'serverlastactivity'    => 0,
            'serverplaybacksessionid' => '',
            'serverbudgetseconds'   => 0.0,
            'servercreditedseconds' => 0.0,
            'uniquecoveredseconds' => 0,
            'completionpercent'    => 0,
            'intervaljson'         => '[]',
            'iscompleted'          => 0,
            'timemodified'         => time(),
            'timecreated'          => time(),
        ];
    }

    /**
     * Advances the cumulative server-time playback budget for one candidate segment.
     *
     * A playback handshake establishes the initial millisecond timestamp without
     * granting watched time. Later requests earn video-time credit only from real
     * elapsed server time, capped to one heartbeat window plus a small network margin.
     *
     * @param int $lastactivity Last playback handshake/request time in server milliseconds.
     * @param float $budget Existing server video-time budget.
     * @param float $credited Existing credited raw video seconds.
     * @param int $nowmilliseconds Current server time in milliseconds.
     * @param int $heartbeat Configured heartbeat interval.
     * @param float $playbackrate Validated effective playback rate.
     * @param float $candidate Candidate raw segment duration.
     * @return array Updated guard values including an accepted flag.
     */
    public static function advance_server_credit_budget(
        int $lastactivity,
        float $budget,
        float $credited,
        int $nowmilliseconds,
        int $heartbeat,
        float $playbackrate,
        float $candidate
    ): array {
        $heartbeat = max(5, min(300, $heartbeat));
        $playbackrate = max(0.25, min(4.0, $playbackrate));
        $candidate = max(0.0, $candidate);
        $budget = max(0.0, $budget);
        $credited = max(0.0, $credited);
        $nowmilliseconds = max(1, $nowmilliseconds);

        if ($lastactivity <= 0) {
            // A segment request cannot implicitly open a playback window. Keep any
            // existing tolerance debt, discard positive headroom and require the
            // explicit start_playback handshake before elapsed time can be earned.
            return [
                'accepted' => false,
                'lastactivity' => 0,
                'budget' => round(min($budget, $credited), 3),
                'credited' => round($credited, 3),
            ];
        }

        $elapsedseconds = max(0.0, ($nowmilliseconds - $lastactivity) / 1000);
        $earned = min($elapsedseconds, $heartbeat + 5) * $playbackrate;
        $updatedbudget = $budget + $earned;
        if (($credited + $candidate) >= ($updatedbudget + self::SERVER_CREDIT_TOLERANCE_SECONDS)) {
            // Reject the candidate and discard positive headroom without converting
            // previously tolerated drift into reusable credit. When credited exceeds
            // the budget, the difference remains a cumulative debt across requests.
            return [
                'accepted' => false,
                'lastactivity' => $nowmilliseconds,
                'budget' => round(min($updatedbudget, $credited), 3),
                'credited' => round($credited, 3),
            ];
        }

        return [
            'accepted' => true,
            'lastactivity' => $nowmilliseconds,
            'budget' => round($updatedbudget, 3),
            'credited' => round($credited + $candidate, 3),
        ];
    }

    /**
     * Compare an existing ledger row with a retried request payload.
     *
     * @param stdClass $existing Persisted request row.
     * @param stdClass $candidate Candidate request row.
     * @return bool Whether both rows represent the same browser request.
     */
    private static function same_segment_request(\stdClass $existing, \stdClass $candidate): bool {
        return (string)$existing->sessionid === (string)$candidate->sessionid
            && (string)$existing->endreason === (string)$candidate->endreason
            && abs((float)$existing->videotimestart - (float)$candidate->videotimestart) <= 0.001
            && abs((float)$existing->videotimeend - (float)$candidate->videotimeend) <= 0.001
            && abs((float)$existing->playbackrate - (float)$candidate->playbackrate) <= 0.001;
    }

    /**
     * Establish a playback-credit window without granting watched time.
     *
     * @param stdClass $videotrack Activity instance.
     * @param cm_info $cm Course module.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param string $requestid Idempotency request id.
     * @param float $videotime Current provider time.
     * @param int $nowmilliseconds Current server time in milliseconds.
     * @return array State and retry information.
     */
    public static function begin_playback(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        string $sessionid,
        string $requestid,
        float $videotime,
        int $nowmilliseconds
    ): array {
        global $DB;

        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lock = $lockfactory->get_lock('state:' . $videotrack->id . ':' . $userid, 10);
        if (!$lock) {
            throw new \moodle_exception('locktimeout', 'error');
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

            $existing = $DB->get_record('videotrack_seg', [
                'videotrackid' => $videotrack->id,
                'userid' => $userid,
                'requestid' => $requestid,
            ]);
            $handshake = (object)[
                'videotrackid' => $videotrack->id,
                'courseid' => $videotrack->course,
                'cmid' => $cm->id,
                'userid' => $userid,
                'videoid' => $videotrack->videoid,
                'sessionid' => $sessionid,
                'requestid' => $requestid,
                'wallclockstart' => (int)floor($nowmilliseconds / 1000),
                'wallclockend' => (int)floor($nowmilliseconds / 1000),
                'videotimestart' => round(max(0.0, $videotime), 3),
                'videotimeend' => round(max(0.0, $videotime), 3),
                'playbackrate' => 1.0,
                'endreason' => 'playstart',
                'servervalidated' => 0,
                'timecreated' => time(),
            ];

            $requestreplayed = false;
            if ($existing) {
                if (!self::same_segment_request($existing, $handshake)) {
                    throw new \invalid_parameter_exception('Request identifier was reused with different playback data');
                }
                $requestreplayed = true;
            } else {
                $DB->insert_record('videotrack_seg', $handshake);
                $state->serverlastactivity = $nowmilliseconds;
                $state->serverplaybacksessionid = $sessionid;
                $state->serverbudgetseconds = min(
                    (float)($state->serverbudgetseconds ?? 0.0),
                    (float)($state->servercreditedseconds ?? 0.0)
                );
                $state->timemodified = time();
                if (!empty($state->id)) {
                    $DB->update_record('videotrack_state', $state);
                } else {
                    $state->id = $DB->insert_record('videotrack_state', $state);
                }
            }

            $transaction->allow_commit();
            $lock->release();
            $lock = null;
            return [
                'state' => $state,
                'requestreplayed' => $requestreplayed,
            ];
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            throw $e;
        }
    }

    /**
     * Checks a candidate interval against the server-known forward-seek frontier.
     *
     * @param stdClass $state Current state record.
     * @param array $interval Normalised [start, end] interval.
     * @param bool $allowseekforward Whether forward seeking is enabled.
     * @param float $tolerance Small tolerance for timer/provider drift.
     * @return bool True when the interval is allowed.
     */
    public static function forward_interval_allowed(
        \stdClass $state,
        array $interval,
        bool $allowseekforward,
        float $tolerance = 2.0
    ): bool {
        if ($allowseekforward) {
            return true;
        }
        $furthest = max(0.0, (float)($state->lastposition ?? 0));
        foreach (self::decode_intervals((string)($state->intervaljson ?? '[]')) as $watched) {
            $furthest = max($furthest, (float)$watched[1]);
        }
        return (float)$interval[0] <= ($furthest + max(0.5, $tolerance));
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
     * @param int|null  &$segmentid   Set to inserted id; -1 means server guard rejected the segment.
     * @param array|null $guard Optional server-credit guard values from save_segment.
     * @param bool|null &$requestreplayed Set to true when an existing idempotent result is reused.
     * @return stdClass Updated state.
     */
    public static function update_state(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        array $interval,
        float $lastposition,
        ?\stdClass $segment = null,
        ?int &$segmentid = null,
        ?array $guard = null,
        ?bool &$requestreplayed = null
    ): \stdClass {
        global $DB;

        $requestreplayed = false;
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lock = $lockfactory->get_lock('state:' . $videotrack->id . ':' . $userid, 10);
        if (!$lock) {
            if ($segment !== null) {
                $segmentid = 0;
            }
            return self::current_state_snapshot($videotrack, $cm, $userid);
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

            if ($segment !== null) {
                $existing = $DB->get_record('videotrack_seg', [
                    'videotrackid' => $videotrack->id,
                    'userid' => $userid,
                    'requestid' => (string)$segment->requestid,
                ]);
                if ($existing) {
                    if (!self::same_segment_request($existing, $segment) || $existing->endreason === 'playstart') {
                        throw new \invalid_parameter_exception('Request identifier was reused with different segment data');
                    }
                    $requestreplayed = true;
                    $segmentid = !empty($existing->servervalidated) ? (int)$existing->id : -1;
                    $transaction->allow_commit();
                    $lock->release();
                    return $state;
                }
            }

            if ($segment !== null && $guard !== null) {
                $nowmilliseconds = (int)($guard['nowmilliseconds'] ?? round(microtime(true) * 1000));
                $activesessionid = (string)($state->serverplaybacksessionid ?? '');
                $segmentsessionid = (string)($segment->sessionid ?? '');
                if ($activesessionid === '' || !hash_equals($activesessionid, $segmentsessionid)) {
                    // Credit belongs only to the browser session that opened the current
                    // playback window. A stale/cross-tab request is retained for audit but
                    // must not consume, reset, or steal another session's server budget.
                    $segment->servervalidated = 0;
                    $DB->insert_record('videotrack_seg', $segment);
                    $segmentid = -1;
                    $transaction->allow_commit();
                    $lock->release();
                    return $state;
                }

                $forwardallowed = self::forward_interval_allowed(
                    $state,
                    $interval,
                    !empty($guard['allowseekforward'])
                );
                if (!$forwardallowed) {
                    // An illegal forward jump must not consume candidate seconds or
                    // preserve elapsed headroom. Start any later earning from this
                    // rejection point while retaining cumulative tolerance debt.
                    $state->serverlastactivity = $nowmilliseconds;
                    $state->serverbudgetseconds = min(
                        (float)($state->serverbudgetseconds ?? 0.0),
                        (float)($state->servercreditedseconds ?? 0.0)
                    );
                    $state->timemodified = time();
                    $segment->servervalidated = 0;
                    $segmentid = $DB->insert_record('videotrack_seg', $segment);
                    if (!empty($state->id)) {
                        $DB->update_record('videotrack_state', $state);
                    } else {
                        $state->id = $DB->insert_record('videotrack_state', $state);
                    }
                    $segmentid = -1;
                    $transaction->allow_commit();
                    $lock->release();
                    return $state;
                }

                $budgetstate = self::advance_server_credit_budget(
                    (int)($state->serverlastactivity ?? 0),
                    (float)($state->serverbudgetseconds ?? 0),
                    (float)($state->servercreditedseconds ?? 0),
                    $nowmilliseconds,
                    (int)($guard['heartbeat'] ?? 30),
                    (float)($guard['playbackrate'] ?? 1.0),
                    max(0.0, (float)$segment->videotimeend - (float)$segment->videotimestart)
                );
                $state->serverlastactivity = $budgetstate['lastactivity'];
                $state->serverbudgetseconds = $budgetstate['budget'];
                $state->servercreditedseconds = $budgetstate['credited'];

                if (!$budgetstate['accepted']) {
                    $state->timemodified = time();
                    $segment->servervalidated = 0;
                    $segmentid = $DB->insert_record('videotrack_seg', $segment);
                    if (!empty($state->id)) {
                        $DB->update_record('videotrack_state', $state);
                    } else {
                        $state->id = $DB->insert_record('videotrack_state', $state);
                    }
                    $segmentid = -1;
                    $transaction->allow_commit();
                    $lock->release();
                    return $state;
                }
                $segment->servervalidated = 1;
                if (in_array((string)$segment->endreason, [
                    'pause',
                    'ended',
                    'beforeunload',
                    'pagehide',
                    'tab',
                    'visibilitychange',
                ], true)) {
                    // Terminal/lifecycle closes require a new explicit playback handshake.
                    // This also prevents a hidden/background tab from spending stale credit.
                    $state->serverlastactivity = 0;
                    $state->serverplaybacksessionid = '';
                    $state->serverbudgetseconds = min(
                        (float)($state->serverbudgetseconds ?? 0.0),
                        (float)($state->servercreditedseconds ?? 0.0)
                    );
                }
            }

            if ($segment !== null) {
                $segmentid = $DB->insert_record('videotrack_seg', $segment);
            }

            $duration = max(0.0, (float)$videotrack->durationseconds);
            $coverageinterval = self::normalise_natural_end(
                $interval,
                $duration,
                (string)($segment->endreason ?? '')
            );
            if ($coverageinterval[1] > $lastposition) {
                $lastposition = $coverageinterval[1];
            }
            $storedintervals = self::decode_intervals($state->intervaljson);
            $mergedintervals = self::merge_intervals(array_merge($storedintervals, [$coverageinterval]));
            $mergedintervals = self::normalise_start_boundary($mergedintervals, $duration);
            $covered = self::covered_seconds($mergedintervals);
            $intervals = self::cap_intervals($mergedintervals);

            if (count($mergedintervals) > self::MAX_INTERVALS || count($storedintervals) >= self::MAX_INTERVALS) {
                $segments = $DB->get_recordset('videotrack_seg', [
                    'videotrackid' => $videotrack->id,
                    'userid' => $userid,
                    'servervalidated' => 1,
                ], 'timecreated ASC, id ASC', 'id, videotimestart, videotimeend, endreason, timecreated');
                $aggregate = self::aggregate_segments(
                    $segments,
                    max(0.0, (float)$videotrack->durationseconds)
                );
                $segments->close();
                $intervals = $aggregate['intervals'];
                $covered = $aggregate['coveredseconds'];
            }

            $covered = max((float)($state->uniquecoveredseconds ?? 0.0), $covered);
            $percent = $duration > 0 ? min(100.0, round(($covered / $duration) * 100, 2)) : 0.0;
            $requiredreactionids = !empty($videotrack->reactionsenabled)
                ? completion_config::required_reaction_ids((int)$videotrack->id)
                : [];
            $reactionsummary = self::reaction_counts($videotrack->id, $userid);

            if ($lastposition > 2.0) {
                $state->lastposition = $lastposition;
            }
            $state->durationseconds = $duration;
            $state->uniquecoveredseconds = $covered;
            $state->completionpercent = $percent;
            $state->intervaljson = self::encode_intervals($intervals);
            $wascompleted = !empty($state->id) ? (int)($state->iscompleted ?? 0) : 0;
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

            if (!$wascompleted && $state->iscompleted) {
                $event = \mod_videotrack\event\activity_completed::create([
                    'objectid' => $state->id,
                    'context' => \context_module::instance($cm->id),
                    'userid' => $userid,
                    'other' => [
                        'completionpercent' => $state->completionpercent,
                        'uniquecoveredseconds' => $state->uniquecoveredseconds,
                    ],
                ]);
                $event->trigger();
            }
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
            $normalised = self::normalise_natural_end(
                $normalised,
                $duration,
                (string)($segment->endreason ?? '')
            );
            $intervals[] = $normalised;
            $timecreated = (int)($segment->timecreated ?? 0);
            $segmentid = (int)($segment->id ?? 0);
            if ($timecreated > $latesttime || ($timecreated === $latesttime && $segmentid > $latestid)) {
                $latesttime = $timecreated;
                $latestid = $segmentid;
                $lastposition = $normalised[1];
            }
        }
        $mergedintervals = self::merge_intervals($intervals);
        $mergedintervals = self::normalise_start_boundary($mergedintervals, $duration);
        return [
            'intervals' => self::cap_intervals($mergedintervals),
            'coveredseconds' => self::covered_seconds($mergedintervals),
            'lastposition' => round($lastposition, 3),
        ];
    }

    /**
     * Rebuilds one user's aggregate state from raw segment rows and reaction events.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $userid User id.
     * @param bool $lockheld Whether the caller already holds the canonical state lock.
     * @return \stdClass|null Rebuilt state, or null when the state lock is busy.
     */
    public static function rebuild_state_from_segments(
        \stdClass $videotrack,
        \cm_info $cm,
        int $userid,
        bool $lockheld = false
    ): ?\stdClass {
        global $DB;

        $lock = null;
        if (!$lockheld) {
            $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
            $lock = $lockfactory->get_lock('state:' . $videotrack->id . ':' . $userid, 10);
            if (!$lock) {
                return null;
            }
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

            $configuredduration = max(0.0, (float)($videotrack->durationseconds ?? 0));
            // Only the teacher-configured activity duration is authoritative for
            // percentages and completion. Historical client/state maxima cannot
            // promote an unknown duration into a trusted completion denominator.
            $duration = $configuredduration;
            $segments = $DB->get_recordset('videotrack_seg', [
                'videotrackid' => $videotrack->id,
                'userid' => $userid,
                'servervalidated' => 1,
            ], 'timecreated ASC, id ASC', 'id, videotimestart, videotimeend, endreason, timecreated');
            $aggregate = self::aggregate_segments($segments, $duration);
            $segments->close();

            $state->courseid = $videotrack->course;
            $state->cmid = $cm->id;
            $state->videoid = $videotrack->videoid;
            $state->lastposition = $aggregate['lastposition'];
            $state->durationseconds = $duration;
            // Recalculation rebuilds progress from trusted raw segments but must not
            // mint a fresh playback budget for an active learner session. Existing
            // server guard values therefore remain unchanged.
            $state->uniquecoveredseconds = $aggregate['coveredseconds'];
            $state->completionpercent = $duration > 0
                ? min(100.0, round(($aggregate['coveredseconds'] / $duration) * 100, 2))
                : 0.0;
            $state->intervaljson = self::encode_intervals($aggregate['intervals']);

            $requiredreactionids = !empty($videotrack->reactionsenabled)
                ? completion_config::required_reaction_ids((int)$videotrack->id)
                : [];
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
            if ($lock) {
                $lock->release();
                $lock = null;
            }
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
                $requiredreactionids = !empty($videotrack->reactionsenabled)
                    ? completion_config::required_reaction_ids((int)$videotrack->id)
                    : [];
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
