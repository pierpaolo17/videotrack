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
            'bins' => $bins,
        ];
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
            if ($userid === 0 || $reactionid <= 0) {
                continue;
            }
            $time = max(0.0, (float)($event->videotime ?? 0));
            if (isset($active[$reactionid]) && ($time - $active[$reactionid]['anchor']) <= $windowseconds) {
                $active[$reactionid]['count']++;
                $active[$reactionid]['students'][$userid] = true;
                $active[$reactionid]['timesum'] += $time;
                $active[$reactionid]['last'] = max($active[$reactionid]['last'], $time);
                continue;
            }

            if (isset($active[$reactionid])) {
                self::append_visible_reaction_cluster($active[$reactionid], $minusers, $visible, $truncated);
            }
            $active[$reactionid] = [
                'reactionid' => $reactionid,
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
