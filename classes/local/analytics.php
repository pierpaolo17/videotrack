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
 * Server-side aggregation for instance analytics.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics {
    /** Maximum number of timeline bins rendered by the report. */
    public const MAX_BINS = 720;

    /** Maximum number of reaction clusters retained for the optional overlay. */
    public const MAX_REACTION_CLUSTERS = 2000;

    /** Supported timeline granularities in seconds. */
    public const BIN_SIZES = [10, 15, 30, 60, 120, 300];

    /**
     * Resolves the best known duration from instance, aggregate state and segments.
     *
     * The activity field is canonical when available. Older runtime data may have
     * stored the provider duration only in per-user state, so analytics falls back
     * to that value before using the furthest recorded segment endpoint.
     *
     * @param float $instanceduration Duration stored on the activity instance.
     * @param float $stateduration Maximum duration stored in aggregate user states.
     * @param float $segmentend Furthest recorded segment endpoint.
     * @return float Best known non-negative duration.
     */
    public static function resolve_duration(
        float $instanceduration,
        float $stateduration,
        float $segmentend
    ): float {
        $instanceduration = max(0.0, $instanceduration);
        if ($instanceduration > 0) {
            return $instanceduration;
        }
        return max(0.0, $stateduration, $segmentend);
    }

    /**
     * Chooses a useful default granularity for a video duration.
     *
     * @param float $duration Video duration in seconds.
     * @return int Bin size in seconds.
     */
    public static function default_bin_size(float $duration): int {
        if ($duration <= 20 * MINSECS) {
            return 10;
        }
        if ($duration <= HOURSECS) {
            return 30;
        }
        if ($duration <= 2 * HOURSECS) {
            return 60;
        }
        if ($duration <= 4 * HOURSECS) {
            return 120;
        }
        return 300;
    }

    /**
     * Determines whether analytics must be restricted to the viewer's own groups.
     *
     * Course groups must not restrict an activity configured with no groups. In
     * visible-groups mode all visible course groups may be selected; only separate
     * groups without access-all-groups requires the own-group restriction.
     *
     * @param int $groupmode Effective activity group mode.
     * @param bool $canaccessallgroups Whether the viewer may access all groups.
     * @return bool Whether queries must be limited to the viewer's own groups.
     */
    public static function restrict_to_own_groups(int $groupmode, bool $canaccessallgroups): bool {
        return !$canaccessallgroups && $groupmode === SEPARATEGROUPS;
    }

    /**
     * Validates a requested bin size and automatically enforces MAX_BINS.
     *
     * @param int $requested Requested bin size, or zero for the default.
     * @param float $duration Video duration in seconds.
     * @return int Normalised bin size.
     */
    public static function normalise_bin_size(int $requested, float $duration): int {
        $binsize = in_array($requested, self::BIN_SIZES, true)
            ? $requested
            : self::default_bin_size($duration);

        foreach (self::BIN_SIZES as $candidate) {
            if ($candidate < $binsize) {
                continue;
            }
            if ($duration <= 0 || (int)ceil($duration / $candidate) <= self::MAX_BINS) {
                return $candidate;
            }
        }
        $minimum = (int)ceil($duration / self::MAX_BINS);
        return max(max(self::BIN_SIZES), (int)(ceil($minimum / 300) * 300));
    }

    /**
     * Builds unique-view and replay metrics from raw segment records.
     *
     * Input records must be ordered by userid so only one user's raw intervals
     * are retained in memory at a time.
     *
     * @param iterable $segments Segment records with userid, videotimestart and videotimeend.
     * @param float $duration Video duration in seconds.
     * @param int $binsize Timeline bin size in seconds.
     * @return array Aggregated analytics values.
     */
    public static function build(iterable $segments, float $duration, int $binsize): array {
        $duration = max(0.0, $duration);
        $binsize = self::normalise_bin_size($binsize, $duration);
        $bincount = $duration > 0 ? (int)ceil($duration / $binsize) : 0;
        $bins = [];
        for ($index = 0; $index < $bincount; $index++) {
            $start = $index * $binsize;
            $bins[$index] = [
                'index' => $index,
                'start' => (float)$start,
                'end' => (float)min($duration, $start + $binsize),
                'viewers' => 0,
                'repeatviewers' => 0,
                'rawseconds' => 0.0,
                'uniqueseconds' => 0.0,
                'repeatseconds' => 0.0,
                'retention' => 0.0,
                'suppressed' => false,
                'repeatsuppressed' => false,
            ];
        }

        if ($bincount === 0) {
            return [
                'duration' => $duration,
                'binsize' => $binsize,
                'viewers' => 0,
                'rawseconds' => 0.0,
                'uniqueseconds' => 0.0,
                'repeatseconds' => 0.0,
                'repeatmetricsavailable' => true,
                'bins' => [],
            ];
        }

        $currentuserid = null;
        $userintervals = [];
        $viewers = 0;
        foreach ($segments as $segment) {
            $userid = (int)($segment->userid ?? 0);
            if ($userid === 0) {
                continue;
            }
            if ($currentuserid !== null && $userid !== $currentuserid) {
                if (self::add_user_intervals($userintervals, $bins, $binsize, $duration)) {
                    $viewers++;
                }
                $userintervals = [];
            }
            $currentuserid = $userid;
            $interval = tracker::normalise_interval(
                (float)($segment->videotimestart ?? 0),
                (float)($segment->videotimeend ?? 0),
                $duration
            );
            if ($interval !== null) {
                $userintervals[] = $interval;
            }
        }
        if ($currentuserid !== null && self::add_user_intervals($userintervals, $bins, $binsize, $duration)) {
            $viewers++;
        }

        $rawseconds = 0.0;
        $uniqueseconds = 0.0;
        foreach ($bins as &$bin) {
            $bin['repeatseconds'] = max(0.0, $bin['rawseconds'] - $bin['uniqueseconds']);
            $bin['retention'] = $viewers > 0 ? round(($bin['viewers'] / $viewers) * 100, 2) : 0.0;
            $bin['rawseconds'] = round($bin['rawseconds'], 3);
            $bin['uniqueseconds'] = round($bin['uniqueseconds'], 3);
            $bin['repeatseconds'] = round($bin['repeatseconds'], 3);
            $rawseconds += $bin['rawseconds'];
            $uniqueseconds += $bin['uniqueseconds'];
        }
        unset($bin);

        return [
            'duration' => $duration,
            'binsize' => $binsize,
            'viewers' => $viewers,
            'rawseconds' => round($rawseconds, 3),
            'uniqueseconds' => round($uniqueseconds, 3),
            'repeatseconds' => round(max(0.0, $rawseconds - $uniqueseconds), 3),
            'repeatmetricsavailable' => true,
            'bins' => $bins,
        ];
    }

    /**
     * Builds unique-view analytics from canonical aggregate state records.
     *
     * This fallback is used when raw segment retention or compaction leaves fewer
     * usable segment users than the persisted per-user state. Replay metrics cannot
     * be reconstructed from merged state intervals and are marked unavailable.
     *
     * @param iterable $states State records with userid and intervaljson.
     * @param float $duration Video duration in seconds.
     * @param int $binsize Timeline bin size in seconds.
     * @return array Aggregated analytics values.
     */
    public static function build_from_states(iterable $states, float $duration, int $binsize): array {
        $segments = (static function () use ($states): \Generator {
            foreach ($states as $state) {
                $userid = (int)($state->userid ?? 0);
                if ($userid <= 0) {
                    continue;
                }
                foreach (tracker::decode_intervals($state->intervaljson ?? null) as [$start, $end]) {
                    yield (object)[
                        'userid' => $userid,
                        'videotimestart' => $start,
                        'videotimeend' => $end,
                    ];
                }
            }
        })();

        $result = self::build($segments, $duration, $binsize);
        $result['repeatmetricsavailable'] = false;
        foreach ($result['bins'] as &$bin) {
            $bin['repeatviewers'] = null;
            $bin['rawseconds'] = null;
            $bin['repeatseconds'] = null;
            $bin['repeatsuppressed'] = false;
        }
        unset($bin);
        $result['rawseconds'] = null;
        $result['repeatseconds'] = null;
        return $result;
    }

    /**
     * Masks small datasets and bins according to the configured privacy threshold.
     *
     * @param array $result Result returned by build().
     * @param int $minusers Minimum number of users required for exact values.
     * @return array Privacy-safe result.
     */
    public static function apply_privacy_threshold(array $result, int $minusers): array {
        $minusers = max(2, $minusers);
        $result['minusers'] = $minusers;
        $result['datasetsuppressed'] = (int)($result['viewers'] ?? 0) < $minusers;
        if ($result['datasetsuppressed']) {
            $result['bins'] = [];
            return $result;
        }

        foreach ($result['bins'] as &$bin) {
            $viewers = (int)$bin['viewers'];
            if ($viewers > 0 && $viewers < $minusers) {
                $bin['suppressed'] = true;
                $bin['viewers'] = null;
                $bin['repeatviewers'] = null;
                $bin['rawseconds'] = null;
                $bin['uniqueseconds'] = null;
                $bin['repeatseconds'] = null;
                $bin['retention'] = null;
                continue;
            }
            $repeatviewers = (int)$bin['repeatviewers'];
            if ($repeatviewers > 0 && $repeatviewers < $minusers) {
                $bin['repeatsuppressed'] = true;
                $bin['repeatviewers'] = null;
                $bin['rawseconds'] = null;
                $bin['repeatseconds'] = null;
            }
        }
        unset($bin);
        return $result;
    }

    /**
     * Applies a distinct-user privacy threshold to an aggregate event count.
     *
     * Exact counts are removed when the contributing population is below the
     * threshold, while a boolean still allows the UI to state that data exists.
     *
     * @param int $eventcount Number of events in scope.
     * @param int $usercount Number of distinct contributing users in scope.
     * @param int $minusers Minimum distinct users required for exact values.
     * @return array Privacy-safe summary.
     */
    public static function count_summary(int $eventcount, int $usercount, int $minusers): array {
        $eventcount = max(0, $eventcount);
        $usercount = max(0, $usercount);
        $minusers = max(2, $minusers);
        $hasdata = $eventcount > 0;
        $suppressed = $hasdata && $usercount < $minusers;

        return [
            'hasdata' => $hasdata,
            'eventcount' => $suppressed ? null : $eventcount,
            'studentcount' => $suppressed ? null : $usercount,
            'suppressed' => $suppressed,
        ];
    }

    /**
     * Applies the distinct-user privacy threshold to an overall reaction summary.
     *
     * @param int $eventcount Number of reaction events in scope.
     * @param int $studentcount Number of distinct reacting students in scope.
     * @param int $minusers Minimum distinct students required for exact values.
     * @return array Privacy-safe summary.
     */
    public static function reaction_summary(int $eventcount, int $studentcount, int $minusers): array {
        return self::count_summary($eventcount, $studentcount, $minusers);
    }

    /**
     * Clusters reaction events for an optional privacy-safe timeline overlay.
     *
     * @param iterable $events Reaction events ordered by videotime.
     * @param int $windowseconds Cluster window in seconds.
     * @param int $minusers Minimum distinct students required for a visible cluster.
     * @return array Visible clusters and truncation state.
     */
    public static function cluster_reactions(iterable $events, int $windowseconds, int $minusers): array {
        $windowseconds = max(1, $windowseconds);
        $minusers = max(2, $minusers);
        $active = [];
        $visible = [];
        $truncated = false;

        foreach ($events as $event) {
            $userid = (int)($event->userid ?? 0);
            $reactionid = (int)($event->reactionid ?? 0);
            $reactionkey = trim((string)($event->reactionkey ?? ''));
            $videotrackid = (int)($event->videotrackid ?? 0);
            if ($userid === 0 || ($reactionid <= 0 && $reactionkey === '')) {
                continue;
            }
            $clusterkey = $reactionkey !== ''
                ? 'key:' . $reactionkey
                : ($videotrackid > 0 ? 'activity:' . $videotrackid . ':id:' . $reactionid : 'id:' . $reactionid);
            $time = max(0.0, (float)($event->videotime ?? 0));
            if (isset($active[$clusterkey]) && ($time - $active[$clusterkey]['anchor']) <= $windowseconds) {
                $active[$clusterkey]['count']++;
                $active[$clusterkey]['students'][$userid] = true;
                $active[$clusterkey]['timesum'] += $time;
                $active[$clusterkey]['last'] = max($active[$clusterkey]['last'], $time);
                continue;
            }

            if (isset($active[$clusterkey])) {
                self::append_visible_reaction_cluster($active[$clusterkey], $minusers, $visible, $truncated);
            }
            $active[$clusterkey] = [
                'reactionid' => $reactionid,
                'reactionkey' => $reactionkey,
                'reactionlabel' => (string)($event->reactionlabel ?? ''),
                'anchor' => $time,
                'first' => $time,
                'last' => $time,
                'count' => 1,
                'students' => [$userid => true],
                'timesum' => $time,
            ];
        }

        foreach ($active as $cluster) {
            self::append_visible_reaction_cluster($cluster, $minusers, $visible, $truncated);
        }
        usort($visible, static fn(array $a, array $b): int => $a['timestamp'] <=> $b['timestamp']);
        return [
            'clusters' => $visible,
            'truncated' => $truncated,
        ];
    }

    /**
     * Finalises one reaction cluster and retains it when it is privacy-safe.
     *
     * @param array $cluster Active reaction cluster.
     * @param int $minusers Minimum distinct users required.
     * @param array $visible Visible clusters, modified in place.
     * @param bool $truncated Whether additional visible clusters were omitted.
     */
    private static function append_visible_reaction_cluster(
        array $cluster,
        int $minusers,
        array &$visible,
        bool &$truncated
    ): void {
        $studentcount = count($cluster['students']);
        if ($studentcount < $minusers) {
            return;
        }
        if (count($visible) >= self::MAX_REACTION_CLUSTERS) {
            $truncated = true;
            return;
        }
        $cluster['students'] = $studentcount;
        $cluster['timestamp'] = round($cluster['timesum'] / $cluster['count'], 3);
        unset($cluster['timesum'], $cluster['anchor']);
        $visible[] = $cluster;
    }

    /**
     * Adds one user's raw and merged intervals to global bins.
     *
     * @param array $intervals Raw intervals for one user.
     * @param array $bins Global bins, modified in place.
     * @param int $binsize Bin size in seconds.
     * @param float $duration Video duration in seconds.
     * @return bool True when the user had at least one valid interval.
     */
    private static function add_user_intervals(array $intervals, array &$bins, int $binsize, float $duration): bool {
        if (!$intervals) {
            return false;
        }
        $rawbybin = [];
        foreach ($intervals as $interval) {
            self::add_interval_to_map($interval, $rawbybin, $binsize, $duration, count($bins));
        }

        $uniquebybin = [];
        foreach (tracker::merge_intervals($intervals) as $interval) {
            self::add_interval_to_map($interval, $uniquebybin, $binsize, $duration, count($bins));
        }

        foreach ($rawbybin as $index => $seconds) {
            $bins[$index]['rawseconds'] += $seconds;
        }
        foreach ($uniquebybin as $index => $seconds) {
            $bins[$index]['uniqueseconds'] += $seconds;
            $bins[$index]['viewers']++;
            if (($rawbybin[$index] ?? 0.0) > $seconds + 0.001) {
                $bins[$index]['repeatviewers']++;
            }
        }
        return true;
    }

    /**
     * Distributes one interval across timeline bins.
     *
     * @param array $interval Normalised [start, end] interval.
     * @param array $map Sparse bin map, modified in place.
     * @param int $binsize Bin size in seconds.
     * @param float $duration Video duration in seconds.
     * @param int $bincount Total number of bins.
     */
    private static function add_interval_to_map(
        array $interval,
        array &$map,
        int $binsize,
        float $duration,
        int $bincount
    ): void {
        [$start, $end] = $interval;
        $first = max(0, (int)floor($start / $binsize));
        $last = min($bincount - 1, max($first, (int)ceil($end / $binsize) - 1));
        for ($index = $first; $index <= $last; $index++) {
            $binstart = $index * $binsize;
            $binend = min($duration, $binstart + $binsize);
            $overlap = max(0.0, min($end, $binend) - max($start, $binstart));
            if ($overlap > 0) {
                $map[$index] = ($map[$index] ?? 0.0) + $overlap;
            }
        }
    }
}
