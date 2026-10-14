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
 * Selects visible activity scopes and batched records for the course dashboard.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class course_analytics_loader {
    /** Maximum activity scopes combined into one aggregate query. */
    private const SCOPE_BATCH_SIZE = 20;

    /**
     * Finds visible activities and their capability-filtered learner SQL scopes.
     *
     * @param stdClass $course Course record.
     * @param int $viewerid Dashboard viewer id.
     * @param int $activityid Optional activity filter.
     * @param int $groupid Optional accessible group filter.
     * @return array Scopes keyed by activity id.
     */
    public static function scopes(stdClass $course, int $viewerid, int $activityid, int $groupid): array {
        global $CFG, $DB;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');

        $instances = self::course_instances($course, $activityid);
        if (!$instances) {
            return [];
        }

        $modinfo = get_fast_modinfo($course, $viewerid);
        $coursehasgroups = $DB->record_exists('groups', ['courseid' => (int)$course->id]);
        $scopes = [];
        foreach ($instances as $instance) {
            $cmid = (int)$instance->cmid;
            if (empty($modinfo->cms[$cmid]) || !$modinfo->cms[$cmid]->uservisible) {
                continue;
            }

            $context = context_module::instance($cmid, IGNORE_MISSING);
            if (!$context) {
                continue;
            }

            $groupids = analytics_scope::accessible_group_ids($instance, $viewerid, $coursehasgroups);
            if ($groupid > 0) {
                if (is_array($groupids) && !in_array($groupid, $groupids, true)) {
                    continue;
                }
                $groupids = [$groupid];
            }
            [$learnersql, $learnerparams] = learner_scope::sql_for_group_ids(
                $context,
                $groupids,
                'userid',
                'course' . (int)$instance->id
            );
            [$segmentlearnersql, $segmentlearnerparams] = learner_scope::sql_for_group_ids(
                $context,
                $groupids,
                'seg.userid',
                'segmentcourse' . (int)$instance->id
            );
            $scopes[(int)$instance->id] = [
                'instance' => $instance,
                'context' => $context,
                'learnersql' => $learnersql,
                'learnerparams' => $learnerparams,
                'segmentlearnersql' => $segmentlearnersql,
                'segmentlearnerparams' => $segmentlearnerparams,
            ];
        }
        return $scopes;
    }

    /**
     * Reads course activities before per-module visibility and learner-scope checks.
     *
     * @param stdClass $course Course record.
     * @param int $activityid Optional activity filter.
     * @return array Matching activity records.
     */
    private static function course_instances(stdClass $course, int $activityid): array {
        global $DB;

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
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Loads aggregate all-time state rows for prepared activity scopes.
     *
     * @param array $scopes Prepared activity scopes keyed by VideoTrack id.
     * @return array State records grouped by VideoTrack id.
     */
    public static function load_states_for_scopes(array $scopes): array {
        global $DB;

        $states = array_fill_keys(array_map('intval', array_keys($scopes)), []);
        foreach (array_chunk($scopes, self::SCOPE_BATCH_SIZE, true) as $batch) {
            $selects = [];
            $params = [];
            foreach ($batch as $videotrackid => $scope) {
                $suffix = (string)(int)$videotrackid;
                $selects[] = "SELECT id, videotrackid, userid, completionpercent, iscompleted, intervaljson, durationseconds
                                FROM {videotrack_state}
                               WHERE videotrackid = :statevideotrackid{$suffix}
                                 AND {$scope['learnersql']}";
                $params += ['statevideotrackid' . $suffix => (int)$videotrackid] + $scope['learnerparams'];
            }
            if (!$selects) {
                continue;
            }
            foreach ($DB->get_records_sql(implode("\nUNION ALL\n", $selects), $params) as $state) {
                $videotrackid = (int)$state->videotrackid;
                if (isset($states[$videotrackid])) {
                    $states[$videotrackid][] = $state;
                }
            }
        }
        return $states;
    }

    /**
     * Loads server-validated period segments for prepared activity scopes.
     *
     * @param array $scopes Prepared activity scopes keyed by VideoTrack id.
     * @param int $timestart Optional inclusive segment creation start time.
     * @param int $timeend Optional inclusive segment creation end time.
     * @return array Segment records grouped by VideoTrack id.
     */
    public static function load_period_segments_for_scopes(
        array $scopes,
        int $timestart,
        int $timeend
    ): array {
        global $DB;

        $segments = array_fill_keys(array_map('intval', array_keys($scopes)), []);
        foreach (array_chunk($scopes, self::SCOPE_BATCH_SIZE, true) as $batch) {
            $selects = [];
            $params = [];
            foreach ($batch as $videotrackid => $scope) {
                $suffix = (string)(int)$videotrackid;
                $branchparams = ['segmentvideotrackid' . $suffix => (int)$videotrackid]
                    + $scope['segmentlearnerparams'];
                $timecondition = '';
                if ($timestart > 0) {
                    $timecondition .= ' AND seg.timecreated >= :segmenttimestart' . $suffix;
                    $branchparams['segmenttimestart' . $suffix] = $timestart;
                }
                if ($timeend > 0) {
                    $timecondition .= ' AND seg.timecreated <= :segmenttimeend' . $suffix;
                    $branchparams['segmenttimeend' . $suffix] = $timeend;
                }
                $selects[] = "SELECT seg.id, seg.videotrackid, seg.userid, seg.videotimestart, seg.videotimeend,
                                     seg.servervalidated, seg.timecreated, COALESCE(st.iscompleted, 0) AS iscompleted
                                FROM {videotrack_seg} seg
                           LEFT JOIN {videotrack_state} st
                                  ON st.videotrackid = seg.videotrackid
                                 AND st.userid = seg.userid
                               WHERE seg.videotrackid = :segmentvideotrackid{$suffix}
                                 AND seg.servervalidated = 1
                                 AND {$scope['segmentlearnersql']}{$timecondition}";
                $params += $branchparams;
            }
            if (!$selects) {
                continue;
            }
            foreach ($DB->get_records_sql(implode("\nUNION ALL\n", $selects), $params) as $segment) {
                $videotrackid = (int)$segment->videotrackid;
                if (isset($segments[$videotrackid])) {
                    $segments[$videotrackid][] = $segment;
                }
            }
        }
        return $segments;
    }

    /**
     * Loads privacy-safe reaction, note and bookmark counts for prepared activity scopes.
     *
     * Scopes are chunked so the course dashboard performs one aggregate event query per
     * batch instead of three event queries for every activity. Each learner subquery has
     * already received a unique parameter prefix while the scopes were prepared.
     *
     * @param array $scopes Prepared activity scopes keyed by VideoTrack id.
     * @param int $minusers Privacy threshold.
     * @param int $timestart Optional inclusive event creation start time.
     * @param int $timeend Optional inclusive event creation end time.
     * @return array Event summaries keyed by VideoTrack id and event type.
     */
    public static function load_event_summaries_for_scopes(
        array $scopes,
        int $minusers,
        int $timestart,
        int $timeend
    ): array {
        global $DB;

        $summaries = [];
        foreach (array_keys($scopes) as $videotrackid) {
            $summaries[(int)$videotrackid] = [
                'reactions' => analytics::count_summary(0, 0, $minusers),
                'notes' => analytics::count_summary(0, 0, $minusers),
                'bookmarks' => analytics::count_summary(0, 0, $minusers),
            ];
        }

        foreach (array_chunk($scopes, self::SCOPE_BATCH_SIZE, true) as $batch) {
            $selects = [];
            $params = [];
            foreach ($batch as $videotrackid => $scope) {
                $suffix = (string)(int)$videotrackid;
                $branchparams = ['eventvideotrackid' . $suffix => (int)$videotrackid]
                    + $scope['learnerparams'];
                $timecondition = '';
                if ($timestart > 0) {
                    $timecondition .= ' AND timecreated >= :eventtimestart' . $suffix;
                    $branchparams['eventtimestart' . $suffix] = $timestart;
                }
                if ($timeend > 0) {
                    $timecondition .= ' AND timecreated <= :eventtimeend' . $suffix;
                    $branchparams['eventtimeend' . $suffix] = $timeend;
                }
                $selects[] = "SELECT MIN(id) AS id, videotrackid, notetype,
                                     COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS usercount
                                FROM {videotrack_reactev}
                               WHERE videotrackid = :eventvideotrackid{$suffix}
                                 AND isdeleted = 0
                                 AND (notetype IS NULL OR notetype = '' OR notetype = 'note' OR notetype = 'bookmark')
                                 AND {$scope['learnersql']}{$timecondition}
                            GROUP BY videotrackid, notetype";
                $params += $branchparams;
            }

            if (!$selects) {
                continue;
            }
            $records = $DB->get_records_sql(implode("
UNION ALL
", $selects), $params);
            foreach ($records as $record) {
                $videotrackid = (int)$record->videotrackid;
                if (!isset($summaries[$videotrackid])) {
                    continue;
                }
                $type = (string)($record->notetype ?? '');
                $key = match ($type) {
                    'note' => 'notes',
                    'bookmark' => 'bookmarks',
                    default => 'reactions',
                };
                $summaries[$videotrackid][$key] = analytics::count_summary(
                    (int)($record->eventcount ?? 0),
                    (int)($record->usercount ?? 0),
                    $minusers
                );
            }
        }
        return $summaries;
    }
}
