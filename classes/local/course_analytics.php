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

use context_module;
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
        global $CFG, $DB;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');

        $sql = "SELECT vt.id, vt.course, vt.name, vt.videosource, vt.durationseconds,
                       vt.bookmarksenabled, cm.id AS cmid, cm.groupmode, cm.groupingid,
                       c.groupmode AS coursegroupmode, c.groupmodeforce
                  FROM {videotrack} vt
                  JOIN {course_modules} cm ON cm.instance = vt.id
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {course} c ON c.id = vt.course
                 WHERE vt.course = :courseid
                   AND cm.course = :cmcourseid
                   AND cm.deletioninprogress = 0";
        $params = [
            'modname' => 'videotrack',
            'courseid' => (int)$course->id,
            'cmcourseid' => (int)$course->id,
        ];
        if ($activityid > 0) {
            $sql .= " AND vt.id = :activityid";
            $params['activityid'] = $activityid;
        }
        $instances = $DB->get_records_sql($sql, $params);
        if (!$instances) {
            return [];
        }

        $modinfo = get_fast_modinfo($course, $viewerid);
        $rows = [];
        foreach ($instances as $instance) {
            $cmid = (int)$instance->cmid;
            if (empty($modinfo->cms[$cmid]) || !$modinfo->cms[$cmid]->uservisible) {
                continue;
            }

            $context = context_module::instance($cmid, IGNORE_MISSING);
            if (!$context) {
                continue;
            }

            $groupids = analytics_scope::accessible_group_ids($instance, $viewerid);
            if ($groupid > 0) {
                if (is_array($groupids) && !in_array($groupid, $groupids, true)) {
                    continue;
                }
                $groupids = [$groupid];
            }
            [$learnersql, $learnerparams] = learner_scope::sql_for_group_ids($context, $groupids);
            if ($timestart > 0 || $timeend > 0) {
                $segments = self::load_period_segments(
                    (int)$instance->id,
                    $learnersql,
                    $learnerparams,
                    $timestart,
                    $timeend
                );
                $completionflags = self::load_current_completion_flags(
                    (int)$instance->id,
                    array_values(array_unique(array_map(
                        static fn(stdClass $segment): int => (int)$segment->userid,
                        $segments
                    )))
                );
                $summary = self::summarise_period_segments(
                    $segments,
                    $completionflags,
                    (float)$instance->durationseconds,
                    $minusers,
                    $timestart,
                    $timeend
                );
            } else {
                $states = self::load_states(
                    (int)$instance->id,
                    $learnersql,
                    $learnerparams
                );
                $summary = self::summarise_states(
                    $states,
                    (float)$instance->durationseconds,
                    $minusers
                );
            }

            $row = clone $instance;
            $row->summary = $summary;
            $row->reactions = self::load_event_summary(
                (int)$instance->id,
                '',
                $learnersql,
                $learnerparams,
                $minusers,
                $timestart,
                $timeend
            );
            $row->notes = self::load_event_summary(
                (int)$instance->id,
                'note',
                $learnersql,
                $learnerparams,
                $minusers,
                $timestart,
                $timeend
            );
            $row->bookmarks = !empty($instance->bookmarksenabled)
                ? self::load_event_summary(
                    (int)$instance->id,
                    'bookmark',
                    $learnersql,
                    $learnerparams,
                    $minusers,
                    $timestart,
                    $timeend
                )
                : analytics::count_summary(0, 0, $minusers);
            $row->canviewactivity = has_capability('mod/videotrack:view', $context, $viewerid);
            $row->canviewreport = has_capability('mod/videotrack:viewreport', $context, $viewerid);
            $rows[(int)$instance->id] = $row;
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
        $minusers = max(2, $minusers);
        $statesbyuser = [];
        $percentages = [];
        $completions = 0;
        $stateduration = 0.0;

        foreach ($states as $state) {
            $userid = (int)($state->userid ?? 0);
            if ($userid <= 0) {
                continue;
            }
            $statesbyuser[$userid] = $state;
        }

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
     * Loads aggregate all-time state rows for one activity and learner scope.
     *
     * @param int $videotrackid Activity instance id.
     * @param string $learnersql Learner SQL condition.
     * @param array $learnerparams Learner SQL parameters.
     * @return array State records.
     */
    private static function load_states(
        int $videotrackid,
        string $learnersql,
        array $learnerparams
    ): array {
        global $DB;

        $params = ['statevideotrackid' => $videotrackid] + $learnerparams;
        $sql = "SELECT id, userid, completionpercent, iscompleted, intervaljson, durationseconds
                  FROM {videotrack_state}
                 WHERE videotrackid = :statevideotrackid
                   AND {$learnersql}
              ORDER BY userid ASC, id ASC";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Loads server-validated playback segments created inside the requested period.
     *
     * @param int $videotrackid Activity instance id.
     * @param string $learnersql Learner SQL condition.
     * @param array $learnerparams Learner SQL parameters.
     * @param int $timestart Optional inclusive segment creation start time.
     * @param int $timeend Optional inclusive segment creation end time.
     * @return array Segment records.
     */
    private static function load_period_segments(
        int $videotrackid,
        string $learnersql,
        array $learnerparams,
        int $timestart,
        int $timeend
    ): array {
        global $DB;

        $params = ['segmentvideotrackid' => $videotrackid] + $learnerparams;
        $timecondition = '';
        if ($timestart > 0) {
            $timecondition .= ' AND timecreated >= :segmenttimestart';
            $params['segmenttimestart'] = $timestart;
        }
        if ($timeend > 0) {
            $timecondition .= ' AND timecreated <= :segmenttimeend';
            $params['segmenttimeend'] = $timeend;
        }
        $sql = "SELECT id, userid, videotimestart, videotimeend, servervalidated, timecreated
                  FROM {videotrack_seg}
                 WHERE videotrackid = :segmentvideotrackid
                   AND servervalidated = 1
                   AND {$learnersql}{$timecondition}
              ORDER BY userid ASC, timecreated ASC, id ASC";
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Loads current completion flags for learners active inside a period.
     *
     * @param int $videotrackid Activity instance id.
     * @param array $userids Learner ids observed in period segments.
     * @return array Completion flags keyed by user id.
     */
    private static function load_current_completion_flags(int $videotrackid, array $userids): array {
        global $DB;

        $userids = array_values(array_unique(array_filter(array_map('intval', $userids))));
        if (!$userids) {
            return [];
        }
        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'perioduser');
        $params = ['completionvideotrackid' => $videotrackid] + $inparams;
        $records = $DB->get_records_select(
            'videotrack_state',
            "videotrackid = :completionvideotrackid AND userid {$insql}",
            $params,
            '',
            'userid,iscompleted'
        );

        $flags = [];
        foreach ($records as $record) {
            $flags[(int)$record->userid] = !empty($record->iscompleted);
        }
        return $flags;
    }

    /**
     * Loads a privacy-safe reaction, note or bookmark count for one activity.
     *
     * @param int $videotrackid Activity instance id.
     * @param string $notetype Empty for reactions, or the personal event type to count.
     * @param string $learnersql Learner SQL condition.
     * @param array $learnerparams Learner SQL parameters.
     * @param int $minusers Privacy threshold.
     * @param int $timestart Optional inclusive event creation start time.
     * @param int $timeend Optional inclusive event creation end time.
     * @return array Privacy-safe count summary.
     */
    private static function load_event_summary(
        int $videotrackid,
        string $notetype,
        string $learnersql,
        array $learnerparams,
        int $minusers,
        int $timestart,
        int $timeend
    ): array {
        global $DB;

        $typecondition = $notetype !== ''
            ? 'notetype = :eventnotetype'
            : "(notetype IS NULL OR notetype = '')";
        $params = ['eventvideotrackid' => $videotrackid] + $learnerparams;
        if ($notetype !== '') {
            $params['eventnotetype'] = $notetype;
        }
        $timecondition = '';
        if ($timestart > 0) {
            $timecondition .= ' AND timecreated >= :eventtimestart';
            $params['eventtimestart'] = $timestart;
        }
        if ($timeend > 0) {
            $timecondition .= ' AND timecreated <= :eventtimeend';
            $params['eventtimeend'] = $timeend;
        }
        $record = $DB->get_record_sql(
            "SELECT COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS usercount
               FROM {videotrack_reactev}
              WHERE videotrackid = :eventvideotrackid
                AND isdeleted = 0
                AND {$typecondition}
                AND {$learnersql}{$timecondition}",
            $params
        );

        return analytics::count_summary(
            (int)($record->eventcount ?? 0),
            (int)($record->usercount ?? 0),
            $minusers
        );
    }
}
