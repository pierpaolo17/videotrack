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
 * Explicit teacher repair of the pre-269 missed-heartbeat defect.
 *
 * Original rejected evidence remains unchanged. This is an opt-in administrative
 * reconstruction, not a relaxation of live session/time/seek validation.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class playback_recovery {
    /** Audit marker for derived repair rows; excluded from subsequent replay. */
    public const REASON = 'recovery269';

    /**
     * Append idempotent derived rows while the caller holds the canonical state lock.
     *
     * @param \stdClass $activity Current activity.
     * @param \cm_info $cm Canonical module.
     * @param int $userid Target learner.
     * @return int Number of appended repair rows.
     */
    public static function recover(\stdClass $activity, \cm_info $cm, int $userid): int {
        global $DB;

        $records = $DB->get_recordset('videotrack_seg', [
            'videotrackid' => $activity->id,
            'userid' => $userid,
            'courseid' => $activity->course,
            'cmid' => $cm->id,
            'videoid' => $activity->videoid,
        ], 'timecreated ASC, id ASC');
        try {
            $heartbeat = \videotrack_get_config_int('heartbeatinterval', 30, 5, 300);
            $candidates = iterator_to_array(self::candidates($records, $activity, $heartbeat), false);
        } finally {
            $records->close();
        }
        $count = 0;
        foreach ($candidates as $source) {
            $requestid = 'recovery269_' . sha1((string)$source->id);
            if (
                $DB->record_exists(
                    'videotrack_seg',
                    ['videotrackid' => $activity->id, 'userid' => $userid, 'requestid' => $requestid]
                )
            ) {
                continue;
            }
            $derived = clone $source;
            unset($derived->id);
            $derived->requestid = $requestid;
            $derived->endreason = self::REASON;
            $derived->servervalidated = 1;
            $DB->insert_record('videotrack_seg', $derived);
            $count++;
        }
        return $count;
    }

    /**
     * Replay server receipt order, returning only long, elapsed-time-bounded evidence.
     *
     * Client wallclock values never authorise recovery. A new handshake supersedes
     * every prior tab; any terminal or seek row closes the reconstruction window.
     * Even accepted short rows consume time before a longer rejected row is tested.
     *
     * @param iterable $records Raw rows in server receipt/id order.
     * @param \stdClass $activity Current duration and playback restrictions.
     * @param int $heartbeat Current heartbeat interval.
     * @return \Generator Recoverable original rows.
     */
    public static function candidates(iterable $records, \stdClass $activity, int $heartbeat): \Generator {
        $window = null;
        $frontier = 0.0;
        foreach ($records as $row) {
            if ((string)$row->endreason === self::REASON) {
                continue;
            }
            if ((string)$row->endreason === 'playstart') {
                $window = [
                    'session' => (string)$row->sessionid,
                    'time' => (int)$row->timecreated,
                    'position' => (float)$row->videotimeend,
                    'spent' => 0.0,
                ];
                continue;
            }
            $accepted = !empty($row->servervalidated);
            $samewindow = $window !== null && hash_equals($window['session'], (string)$row->sessionid);
            $recoverable = !$accepted && $samewindow
                && self::eligible($row, $activity, $heartbeat, $window, $frontier);
            if ($accepted || $recoverable) {
                $frontier = max($frontier, (float)$row->videotimeend);
                if ($samewindow) {
                    $window['spent'] += max(0.0, (float)$row->videotimeend - (float)$row->videotimestart)
                        / max(0.25, (float)$row->playbackrate);
                }
            }
            if ($recoverable) {
                yield $row;
            }
            if (
                $samewindow && in_array(
                    (string)$row->endreason,
                    ['pause', 'ended', 'beforeunload', 'pagehide', 'tab', 'visibilitychange', 'seek'],
                    true
                )
            ) {
                $window = null;
            }
        }
    }

    /**
     * Check one rejected row against a same-session server receipt window.
     *
     * @param \stdClass $row Original rejected row.
     * @param \stdClass $activity Current activity restrictions.
     * @param int $heartbeat Heartbeat interval.
     * @param array $window Server-observed playback window.
     * @param float $frontier Already accepted/recoverable watched frontier.
     * @return bool Whether explicit repair can reconstruct this row.
     */
    private static function eligible(\stdClass $row, \stdClass $activity, int $heartbeat, array $window, float $frontier): bool {
        if (!self::valid_bounds($row, $activity) || !self::valid_rate((float)$row->playbackrate, $activity)) {
            return false;
        }
        $span = (float)$row->videotimeend - (float)$row->videotimestart;
        $rate = (float)$row->playbackrate;
        $elapsed = (int)$row->timecreated - $window['time'];
        if (!self::starved_span($row, $heartbeat) || $elapsed <= 0 || $elapsed > 86400) {
            return false;
        }
        $start = (float)$row->videotimestart;
        if (
            $start < $window['position'] - 0.25 ||
                (empty($activity->allowseekforward) && $start > $frontier + 2.0)
        ) {
            return false;
        }
        // Two seconds cover integer server receipt quantisation, cumulatively per window.
        return $window['spent'] + $span / $rate <= $elapsed + 2.0;
    }

    /**
     * Check finite, non-empty timeline bounds within the configured duration.
     *
     * @param \stdClass $row Original evidence.
     * @param \stdClass $activity Current activity.
     * @return bool Safe bounds.
     */
    private static function valid_bounds(\stdClass $row, \stdClass $activity): bool {
        $start = (float)$row->videotimestart;
        $end = (float)$row->videotimeend;
        $duration = (float)($activity->durationseconds ?? 0);
        return is_finite($start) && is_finite($end) && $duration > 0 && $start >= 0 && $end > $start && $end <= $duration;
    }

    /**
     * Apply the same rate ceiling used by the live endpoint.
     *
     * @param float $rate Recorded rate.
     * @param \stdClass $activity Current activity.
     * @return bool Allowed rate.
     */
    private static function valid_rate(float $rate, \stdClass $activity): bool {
        $maxrate = (int)($activity->maxplaybackrate ?? 0) / 100;
        return is_finite($rate) && $rate >= 0.25 && $rate <= 4 && ($maxrate <= 0 || $rate <= $maxrate)
            && (!empty($activity->allowplaybackratechange) || $rate <= 1);
    }

    /**
     * Limit the repair to spans larger than the old server heartbeat cap.
     *
     * @param \stdClass $row Rejected evidence.
     * @param int $heartbeat Configured heartbeat.
     * @return bool A starvation-sized interval with a permitted close reason.
     */
    private static function starved_span(\stdClass $row, int $heartbeat): bool {
        $span = (float)$row->videotimeend - (float)$row->videotimestart;
        return $span > (max(5, min(300, $heartbeat)) + 5) * (float)$row->playbackrate
            && in_array(
                (string)$row->endreason,
                [
                    'heartbeat', 'pause', 'ended', 'tab', 'visibilitychange', 'pagehide', 'beforeunload',
                    'note', 'bookmark', 'reaction', 'interaction',
                ],
                true
            );
    }
}
