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
     * Learners are identified through capabilities rather than role names. Active
     * enrolled users must be able to view the activity and must not be able to view
     * its full report. This excludes teachers and managers who generated test data.
     *
     * @param stdClass $course Course record.
     * @param int $viewerid User viewing the dashboard.
     * @param int $minusers Privacy threshold for aggregate values.
     * @return array Dashboard rows keyed by VideoTrack instance id.
     */
    public static function get_course_rows(stdClass $course, int $viewerid, int $minusers): array {
        global $CFG, $DB;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');

        $sql = "SELECT vt.id, vt.course, vt.name, vt.videosource, vt.durationseconds,
                       cm.id AS cmid, cm.groupmode, cm.groupingid,
                       c.groupmode AS coursegroupmode, c.groupmodeforce
                  FROM {videotrack} vt
                  JOIN {course_modules} cm ON cm.instance = vt.id
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {course} c ON c.id = vt.course
                 WHERE vt.course = :courseid
                   AND cm.course = :cmcourseid
                   AND cm.deletioninprogress = 0";
        $instances = $DB->get_records_sql($sql, [
            'modname' => 'videotrack',
            'courseid' => (int)$course->id,
            'cmcourseid' => (int)$course->id,
        ]);
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
            [$learnersql, $learnerparams] = self::learner_scope_sql($context, $groupids);
            $states = self::load_states((int)$instance->id, $learnersql, $learnerparams);
            $summary = self::summarise_states(
                $states,
                (float)$instance->durationseconds,
                $minusers
            );

            $row = clone $instance;
            $row->summary = $summary;
            $row->reactions = self::load_event_summary(
                (int)$instance->id,
                false,
                $learnersql,
                $learnerparams,
                $minusers
            );
            $row->notes = self::load_event_summary(
                (int)$instance->id,
                true,
                $learnersql,
                $learnerparams,
                $minusers
            );
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
     * Builds the SQL condition for active enrolled learners in the permitted groups.
     *
     * @param context_module $context Activity context.
     * @param array|null $groupids Null for all users, empty for no permitted users.
     * @return array SQL condition and named parameters.
     */
    private static function learner_scope_sql(context_module $context, ?array $groupids): array {
        if (is_array($groupids) && !$groupids) {
            return ['1 = 0', []];
        }

        [$viewsql, $viewparams] = get_enrolled_sql(
            $context,
            'mod/videotrack:view',
            $groupids ?? 0,
            true
        );
        [$reportsql, $reportparams] = get_enrolled_sql(
            $context,
            'mod/videotrack:viewreport',
            0,
            true
        );

        return [
            "userid IN ({$viewsql}) AND userid NOT IN ({$reportsql})",
            array_merge($viewparams, $reportparams),
        ];
    }

    /**
     * Loads aggregate state rows for one activity and learner scope.
     *
     * @param int $videotrackid Activity instance id.
     * @param string $learnersql Learner SQL condition.
     * @param array $learnerparams Learner SQL parameters.
     * @return array State records.
     */
    private static function load_states(int $videotrackid, string $learnersql, array $learnerparams): array {
        global $DB;

        $sql = "SELECT id, userid, completionpercent, iscompleted, intervaljson, durationseconds
                  FROM {videotrack_state}
                 WHERE videotrackid = :statevideotrackid
                   AND {$learnersql}
              ORDER BY userid ASC, id ASC";
        return $DB->get_records_sql($sql, ['statevideotrackid' => $videotrackid] + $learnerparams);
    }

    /**
     * Loads a privacy-safe reaction or note count for one activity.
     *
     * @param int $videotrackid Activity instance id.
     * @param bool $notes True for personal notes, false for reactions.
     * @param string $learnersql Learner SQL condition.
     * @param array $learnerparams Learner SQL parameters.
     * @param int $minusers Privacy threshold.
     * @return array Privacy-safe count summary.
     */
    private static function load_event_summary(
        int $videotrackid,
        bool $notes,
        string $learnersql,
        array $learnerparams,
        int $minusers
    ): array {
        global $DB;

        $typecondition = $notes
            ? 'notetype = :eventnotetype'
            : "(notetype IS NULL OR notetype = '')";
        $params = ['eventvideotrackid' => $videotrackid] + $learnerparams;
        if ($notes) {
            $params['eventnotetype'] = 'note';
        }
        $record = $DB->get_record_sql(
            "SELECT COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS usercount
               FROM {videotrack_reactev}
              WHERE videotrackid = :eventvideotrackid
                AND isdeleted = 0
                AND {$typecondition}
                AND {$learnersql}",
            $params
        );

        return analytics::count_summary(
            (int)($record->eventcount ?? 0),
            (int)($record->usercount ?? 0),
            $minusers
        );
    }
}
