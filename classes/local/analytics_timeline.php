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
 * Builds video timelines from segments and retained state intervals.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics_timeline {
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
     * Validates a requested bin size and automatically enforces MAX_BINS.
     *
     * @param int $requested Requested bin size, or zero for the default.
     * @param float $duration Video duration in seconds.
     * @return int Normalised bin size.
     */
    public static function normalise_bin_size(int $requested, float $duration): int {
        $binsize = in_array($requested, analytics::BIN_SIZES, true)
            ? $requested
            : self::default_bin_size($duration);

        foreach (analytics::BIN_SIZES as $candidate) {
            if ($candidate < $binsize) {
                continue;
            }
            if ($duration <= 0 || (int)ceil($duration / $candidate) <= analytics::MAX_BINS) {
                return $candidate;
            }
        }
        $minimum = (int)ceil($duration / analytics::MAX_BINS);
        return max(max(analytics::BIN_SIZES), (int)(ceil($minimum / 300) * 300));
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
                'retentionsuppressed' => false,
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
                'totalsuppressed' => false,
                'bins' => [],
            ];
        }

        $viewers = self::accumulate_segments($segments, $bins, $binsize, $duration);

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
            'totalsuppressed' => false,
            'bins' => $bins,
        ];
    }

    /**
     * Streams consecutive user intervals into bins, flushing the final user too.
     *
     * @param iterable $segments Segment records ordered by userid.
     * @param array $bins Global bins, modified in place.
     * @param int $binsize Bin size in seconds.
     * @param float $duration Video duration in seconds.
     * @return int Number of users with at least one valid interval.
     */
    private static function accumulate_segments(iterable $segments, array &$bins, int $binsize, float $duration): int {
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
        return $viewers;
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
