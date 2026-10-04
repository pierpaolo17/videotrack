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

use context;
use moodle_url;
use tabobject;

/**
 * Presentation and aggregation helpers for the teacher report controller.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_support {
    /**
     * Formats a report user label.
     *
     * @param int $userid Moodle user id.
     * @param array $usermap Real Moodle users keyed by id.
     * @param bool $canviewemail Whether email may be displayed.
     * @return string Safe display label.
     */
    public static function user_label(int $userid, array $usermap, bool $canviewemail): string {
        if ($userid <= 0) {
            return get_string('unknownuser');
        }
        $user = $usermap[$userid] ?? null;
        if (!$user) {
            return '#' . $userid;
        }
        return fullname($user) . ($canviewemail ? ' (' . s($user->email) . ')' : '');
    }

    /**
     * Determines whether state-derived Analytics should replace raw segment Analytics.
     *
     * State data wins when it covers more viewers, or when viewer counts are equal and
     * its unique watched duration exceeds the raw result by more than the existing epsilon.
     *
     * @param array $rawanalytics Analytics built from validated segments.
     * @param array $stateanalytics Analytics rebuilt from aggregate state rows.
     * @return bool True when the state fallback should be used.
     */
    public static function analytics_prefers_state_fallback(array $rawanalytics, array $stateanalytics): bool {
        return (int)$stateanalytics['viewers'] > (int)$rawanalytics['viewers']
            || (
                (int)$stateanalytics['viewers'] === (int)$rawanalytics['viewers']
                && (float)$stateanalytics['uniqueseconds'] > (float)$rawanalytics['uniqueseconds'] + 0.001
            );
    }

    /**
     * Builds the deterministic Analytics highlight lists from privacy-processed bins.
     *
     * @param array $bins Privacy-processed Analytics bins.
     * @param bool $repeatmetricsavailable Whether repeat metrics are available for the selected data.
     * @return array Tuple containing top-watched bins, top-replayed bins and largest viewer drops.
     */
    public static function analytics_highlights(array $bins, bool $repeatmetricsavailable): array {
        $visiblebins = array_values(array_filter($bins, static function (array $bin): bool {
            return empty($bin['suppressed']) && $bin['viewers'] !== null && (int)$bin['viewers'] > 0;
        }));

        $topwatched = $visiblebins;
        usort($topwatched, static function (array $a, array $b): int {
            return [$b['viewers'], $b['uniqueseconds'], -$b['start']] <=>
                [$a['viewers'], $a['uniqueseconds'], -$a['start']];
        });
        $topwatched = array_slice($topwatched, 0, 5);

        $topreplayed = $repeatmetricsavailable ? array_values(array_filter(
            $visiblebins,
            static function (array $bin): bool {
                return $bin['repeatseconds'] !== null && (float)$bin['repeatseconds'] > 0;
            }
        )) : [];
        usort($topreplayed, static function (array $a, array $b): int {
            return [$b['repeatseconds'], $b['repeatviewers'], -$b['start']] <=>
                [$a['repeatseconds'], $a['repeatviewers'], -$a['start']];
        });
        $topreplayed = array_slice($topreplayed, 0, 5);

        $drops = report_aggregation::analytics_viewer_drops($bins);
        usort($drops, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
        $drops = array_slice($drops, 0, 5);

        return [$topwatched, $topreplayed, $drops];
    }

    /**
     * Counts acknowledgement Analytics instances by confirmation timing.
     *
     * The caller already supplies acknowledgement-enabled instances. Invalid or missing
     * timing values keep the canonical acknowledgement fallback to anytime.
     *
     * @param array $instances Acknowledgement-enabled Analytics activity instances.
     * @return array Tuple containing anytime count and video-end count.
     */
    public static function analytics_acknowledgement_timing_counts(array $instances): array {
        $anytimecount = 0;
        $videoendcount = 0;
        foreach ($instances as $instance) {
            if (acknowledgement::requires_video_end($instance)) {
                $videoendcount++;
            } else {
                $anytimecount++;
            }
        }

        return [$anytimecount, $videoendcount];
    }

    /**
     * Builds the report user filter options in source-priority order.
     *
     * @param array $useridgroups Ordered groups of Moodle user ids.
     * @param array $usermap Real Moodle users keyed by id.
     * @param bool $canviewemail Whether email may be displayed.
     * @return array User filter options keyed by Moodle user id.
     */
    public static function user_options(array $useridgroups, array $usermap, bool $canviewemail): array {
        $options = [0 => get_string('all')];
        foreach ($useridgroups as $userids) {
            foreach ($userids as $userid) {
                $userid = (int)$userid;
                if ($userid <= 0 || isset($options[$userid])) {
                    continue;
                }
                if (!isset($usermap[$userid])) {
                    continue;
                }
                $options[$userid] = self::user_label($userid, $usermap, $canviewemail);
            }
        }
        return $options;
    }

    /**
     * Clusters standard reaction events using the report window and sort policy.
     *
     * Events must arrive in ascending video-time order, matching the controller recordsets.
     * The bounded cluster limit preserves the report safety valve without loading additional data.
     *
     * @param iterable $events Standard reaction events.
     * @param int $windowseconds Cluster window in seconds.
     * @param string $aggregationmode Aggregation mode: type or peak.
     * @param array $reactionmap Reaction definitions keyed by id.
     * @param string $sort Report sort mode.
     * @param context $context Formatting context for reaction labels.
     * @param bool $limitreached Shared flag set when the configured cluster cap is reached.
     * @return array Cluster rows in report order.
     */
    public static function cluster_reaction_events(
        iterable $events,
        int $windowseconds,
        string $aggregationmode,
        array $reactionmap,
        string $sort,
        context $context,
        bool &$limitreached
    ): array {
        // Keep only the latest open cluster per reaction (or one cluster for peak mode),
        // avoiding the former O(n * clusters) scan for every event.
        $clusters = [];
        $activeindex = [];
        $maxclusters = videotrack_get_config_int('reportclusterlimit', 2000, 500, 10000);
        foreach ($events as $event) {
            $reactionid = (int)$event->reactionid;
            $time = (float)$event->videotime;
            $key = ($aggregationmode === 'peak') ? 0 : $reactionid;
            $idx = $activeindex[$key] ?? null;

            if ($idx !== null && ($time - (float)$clusters[$idx]['anchor']) <= $windowseconds) {
                $clusters[$idx]['count']++;
                $clusters[$idx]['students'][(int)$event->userid] = true;
                $clusters[$idx]['timesum'] += $time;
                $clusters[$idx]['first'] = min($clusters[$idx]['first'], $time);
                $clusters[$idx]['last'] = max($clusters[$idx]['last'], $time);
                continue;
            }

            if (count($clusters) >= $maxclusters) {
                $limitreached = true;
                continue;
            }
            $clusters[] = [
                'reactionid' => $reactionid,
                'reactionlabel' => format_string($event->reactionlabel, true, ['context' => $context]),
                'reaction' => $reactionmap[$reactionid] ?? null,
                'anchor' => $time,
                'first' => $time,
                'last' => $time,
                'count' => 1,
                'students' => [(int)$event->userid => true],
                'timesum' => $time,
            ];
            $activeindex[$key] = count($clusters) - 1;
        }

        foreach ($clusters as &$cluster) {
            $cluster['students'] = count($cluster['students']);
            $cluster['timestamp'] = $cluster['timesum'] / $cluster['count'];
            unset($cluster['timesum']);
        }
        unset($cluster);

        report_aggregation::sort_reaction_clusters($clusters, $aggregationmode, $sort);
        return $clusters;
    }

    /**
     * Builds the report tab set.
     *
     * @param int $cmid Course module id.
     * @param bool $canviewstudentreport Whether a student/individual report tab may be shown.
     * @param bool $canviewaggregatereport Whether cumulative and Analytics tabs may be shown.
     * @param bool $canexportindividualreport Whether the detailed export tab may be shown.
     * @param bool $canrecalculate Whether the maintenance/recalculation tab may be shown.
     * @param array $baseparams Existing report filter parameters.
     * @return array Report tabs.
     */
    public static function tabs(
        int $cmid,
        bool $canviewstudentreport,
        bool $canviewaggregatereport,
        bool $canexportindividualreport,
        bool $canrecalculate,
        array $baseparams = []
    ): array {
        $tabs = [];
        if ($canviewstudentreport) {
            $studentparams = array_merge($baseparams, ['id' => $cmid, 'mode' => 'student']);
            $tabs[] = new tabobject(
                'student',
                new moodle_url('/mod/videotrack/report.php', $studentparams),
                get_string('report:perstudent', 'mod_videotrack')
            );
        }
        if ($canviewaggregatereport) {
            $cumulativeparams = array_merge($baseparams, ['id' => $cmid, 'mode' => 'cumulative']);
            $tabs[] = new tabobject(
                'cumulative',
                new moodle_url('/mod/videotrack/report.php', $cumulativeparams),
                get_string('report:cumulative', 'mod_videotrack')
            );
            $tabs[] = new tabobject(
                'analytics',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'analytics']),
                get_string('report:analytics_tab', 'mod_videotrack')
            );
        }
        if ($canexportindividualreport) {
            $tabs[] = new tabobject(
                'export',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'export']),
                get_string('report:csvexport_tab', 'mod_videotrack')
            );
        }
        if ($canrecalculate) {
            $tabs[] = new tabobject(
                'recalculate',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'recalculate']),
                get_string('report:recalculate_tab', 'mod_videotrack')
            );
        }
        return $tabs;
    }
}
