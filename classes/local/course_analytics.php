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

use stdClass;

/**
 * Privacy-safe aggregation for the course-level VideoTrack dashboard.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_analytics {
    /**
     * Builds one dashboard row for every visible VideoTrack activity in a course.
     *
     * Learners are identified through the explicit participation capability rather
     * than role names or the absence of report access. This supports custom and
     * dual-role learners without including ordinary teacher/manager previews.
     *
     * @param stdClass $course Course record.
     * @param int $viewerid User viewing the dashboard.
     * @param int $minusers Privacy threshold for aggregate values.
     * @param int $activityid Optional VideoTrack instance filter.
     * @param int $groupid Optional accessible group filter.
     * @param int $timestart Optional inclusive state/event start timestamp.
     * @param int $timeend Optional inclusive state/event end timestamp.
     * @return array Dashboard rows keyed by VideoTrack instance id.
     */
    public static function get_course_rows(
        stdClass $course,
        int $viewerid,
        int $minusers,
        int $activityid = 0,
        int $groupid = 0,
        int $timestart = 0,
        int $timeend = 0
    ): array {
        $scopes = course_analytics_loader::scopes($course, $viewerid, $activityid, $groupid);
        if (!$scopes) {
            return [];
        }

        $eventsummaries = course_analytics_loader::load_event_summaries_for_scopes(
            $scopes,
            $minusers,
            $timestart,
            $timeend
        );
        $period = $timestart > 0 || $timeend > 0;
        $statesbyactivity = $period ? [] : course_analytics_loader::load_states_for_scopes($scopes);
        $segmentsbyactivity = $period
            ? course_analytics_loader::load_period_segments_for_scopes($scopes, $timestart, $timeend)
            : [];

        $rows = [];
        foreach ($scopes as $videotrackid => $scope) {
            $instance = $scope['instance'];
            $context = $scope['context'];
            if ($period) {
                $segments = $segmentsbyactivity[$videotrackid] ?? [];
                $completionflags = [];
                foreach ($segments as $segment) {
                    $completionflags[(int)$segment->userid] = !empty($segment->iscompleted);
                }
                $summary = self::summarise_period_segments(
                    $segments,
                    $completionflags,
                    (float)$instance->durationseconds,
                    $minusers,
                    $timestart,
                    $timeend
                );
            } else {
                $summary = self::summarise_states(
                    $statesbyactivity[$videotrackid] ?? [],
                    (float)$instance->durationseconds,
                    $minusers
                );
            }

            $row = clone $instance;
            $row->summary = $summary;
            $row->reactions = $eventsummaries[$videotrackid]['reactions'];
            $row->notes = $eventsummaries[$videotrackid]['notes'];
            $row->bookmarks = !empty($instance->bookmarksenabled)
                ? $eventsummaries[$videotrackid]['bookmarks']
                : analytics::count_summary(0, 0, $minusers);
            $row->canviewactivity = has_capability('mod/videotrack:view', $context, $viewerid);
            $row->canviewreport = report_access::can_view_aggregate($context, $viewerid);
            $rows[$videotrackid] = $row;
        }

        uasort($rows, static function (stdClass $left, stdClass $right): int {
            return [\core_text::strtolower((string)$left->name), (int)$left->id]
                <=> [\core_text::strtolower((string)$right->name), (int)$right->id];
        });
        return $rows;
    }

    /**
     * Summarises state rows using the same timeline analytics service as report.php.
     *
     * @param iterable $states State records for capability-filtered learners.
     * @param float $instanceduration Duration configured on the activity.
     * @param int $minusers Privacy threshold for aggregate values.
     * @return array Privacy-safe completion and retention summary.
     */
    public static function summarise_states(iterable $states, float $instanceduration, int $minusers): array {
        $minusers = max(analytics::EXACT_REPORT_MIN_USERS, $minusers);
        $statesbyuser = self::latest_states_by_user($states);
        $percentages = [];
        $completions = 0;
        $stateduration = 0.0;

        foreach ($statesbyuser as $state) {
            $percentages[] = max(0.0, min(100.0, (float)($state->completionpercent ?? 0)));
            $completions += empty($state->iscompleted) ? 0 : 1;
            $stateduration = max($stateduration, (float)($state->durationseconds ?? 0));
        }

        $started = count($statesbyuser);
        $noncompleted = max(0, $started - $completions);
        $duration = analytics::resolve_duration($instanceduration, $stateduration, 0.0);
        $timeline = analytics::build_from_states(array_values($statesbyuser), $duration, 0);
        $timeline = analytics::apply_privacy_threshold($timeline, $minusers);
        $datasetsuppressed = $started > 0 && $started < $minusers;

        return [
            'started' => analytics::count_summary($started, $started, $minusers),
            'averagepercent' => $started > 0 && !$datasetsuppressed
                ? round(array_sum($percentages) / $started, 1)
                : null,
            'medianpercent' => $started > 0 && !$datasetsuppressed
                ? round((float)self::median($percentages), 1)
                : null,
            'completions' => analytics::count_summary($completions, $completions, $minusers),
            'noncompleted' => analytics::count_summary($noncompleted, $noncompleted, $minusers),
            'maindrop' => self::largest_adjacent_drop($timeline['bins']),
            'duration' => $duration,
            'datasetsuppressed' => $datasetsuppressed,
            'minusers' => $minusers,
        ];
    }

    /**
     * Keeps the last state per valid learner id, preserving input order.
     *
     * @param iterable $states State records for capability-filtered learners.
     * @return array State records keyed by learner id.
     */
    private static function latest_states_by_user(iterable $states): array {
        $statesbyuser = [];
        foreach ($states as $state) {
            $userid = (int)($state->userid ?? 0);
            if ($userid <= 0) {
                continue;
            }
            $statesbyuser[$userid] = $state;
        }
        return $statesbyuser;
    }

    /**
     * Calculates the median of a numeric list.
     *
     * @param array $values Numeric values.
     * @return float|null Median, or null for an empty list.
     */
    public static function median(array $values): ?float {
        if (!$values) {
            return null;
        }
        $values = array_map('floatval', $values);
        sort($values, SORT_NUMERIC);
        $count = count($values);
        $middle = intdiv($count, 2);
        if ($count % 2 !== 0) {
            return $values[$middle];
        }
        return ($values[$middle - 1] + $values[$middle]) / 2;
    }

    /**
     * Finds the largest privacy-safe retention decrease between adjacent bins.
     *
     * @param array $bins Timeline bins after apply_privacy_threshold().
     * @return array|null Drop details, or null when no positive visible drop exists.
     */
    public static function largest_adjacent_drop(array $bins): ?array {
        $largest = null;
        for ($index = 1, $count = count($bins); $index < $count; $index++) {
            $previous = $bins[$index - 1];
            $current = $bins[$index];
            if (
                !empty($previous['suppressed'])
                || !empty($current['suppressed'])
                || $previous['retention'] === null
                || $current['retention'] === null
            ) {
                continue;
            }
            $drop = (float)$previous['retention'] - (float)$current['retention'];
            if ($drop <= 0 || ($largest !== null && $drop <= $largest['percentagepoints'])) {
                continue;
            }
            $largest = [
                'timestamp' => (float)$current['start'],
                'percentagepoints' => round($drop, 2),
                'fromretention' => (float)$previous['retention'],
                'toretention' => (float)$current['retention'],
            ];
        }
        return $largest;
    }

    /**
     * Summarises server-validated playback segments created inside one time period.
     *
     * Viewing percentages and retention are rebuilt exclusively from segment rows
     * whose timecreated belongs to the requested period. Completion is intentionally
     * a current snapshot for the learners active in that period because VideoTrack
     * does not persist a historical completion timestamp.
     *
     * @param iterable $segments Candidate segment rows.
     * @param array $completionflags Current completion flags keyed by user id.
     * @param float $duration Activity duration.
     * @param int $minusers Privacy threshold.
     * @param int $timestart Inclusive period start timestamp.
     * @param int $timeend Inclusive period end timestamp.
     * @return array Privacy-safe period summary.
     */
    public static function summarise_period_segments(
        iterable $segments,
        array $completionflags,
        float $duration,
        int $minusers,
        int $timestart,
        int $timeend
    ): array {
        $byuser = self::segments_by_user_in_period($segments, $timestart, $timeend);

        $states = [];
        foreach ($byuser as $userid => $usersegments) {
            $aggregate = tracker::aggregate_segments($usersegments, $duration);
            $covered = (float)$aggregate['coveredseconds'];
            $percent = $duration > 0
                ? min(100.0, round(($covered / $duration) * 100, 2))
                : 0.0;
            $states[] = (object)[
                'userid' => $userid,
                'completionpercent' => $percent,
                'iscompleted' => !empty($completionflags[$userid]) ? 1 : 0,
                'intervaljson' => tracker::encode_intervals($aggregate['intervals']),
                'durationseconds' => $duration,
            ];
        }

        return self::summarise_states($states, $duration, $minusers);
    }

    /**
     * Groups only server-validated segments whose creation time is in the inclusive period.
     *
     * @param iterable $segments Candidate segment rows.
     * @param int $timestart Inclusive period start timestamp.
     * @param int $timeend Inclusive period end timestamp.
     * @return array Segment records grouped by valid learner id.
     */
    private static function segments_by_user_in_period(iterable $segments, int $timestart, int $timeend): array {
        $byuser = [];
        foreach ($segments as $segment) {
            if (empty($segment->servervalidated)) {
                continue;
            }
            $timecreated = (int)($segment->timecreated ?? 0);
            if (($timestart > 0 && $timecreated < $timestart) || ($timeend > 0 && $timecreated > $timeend)) {
                continue;
            }
            $userid = (int)($segment->userid ?? 0);
            if ($userid <= 0) {
                continue;
            }
            $byuser[$userid][] = $segment;
        }
        return $byuser;
    }
}
