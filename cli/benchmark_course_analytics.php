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

/**
 * Read-only benchmark for course-level VideoTrack analytics.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');


$usage = <<<'USAGE'
Benchmarks VideoTrack course analytics without modifying Moodle data.

Usage:
    php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> [options]

Required:
    --courseid=<id>      Course containing VideoTrack activities.
    --userid=<id>        Viewer used for capability/group visibility checks.

Options:
    --activityid=<id>    Single VideoTrack instance used by *_single scenarios. If omitted,
                         prefer the activity with the most state rows, then the lowest id.
    --minusers=<n>       Analytics privacy threshold. Default: 2.
    --groupid=<id>       Optional group filter. Default: 0 (all accessible groups).
    --runs=<n>           Runs per scenario. Default: 5; allowed 1-50.
    --perioddays=<n>     Period used by period_* scenarios. Default: 7; allowed 1-3650.
    -h, --help           Print this help.

Output:
    Pretty-printed JSON containing per-run reads, queries, DB time, wall time, medians and
    a scaling comparison between one activity and all configured activities.

Notes:
    The benchmark is read-only and intended for local/staging diagnostics. Results depend on
    dataset density, DB caches, Moodle caches and host load; keep the raw JSON with release and
    environment evidence instead of treating one timing threshold as universal.
USAGE;

list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'courseid' => null,
    'userid' => null,
    'activityid' => 0,
    'minusers' => 2,
    'groupid' => 0,
    'runs' => 5,
    'perioddays' => 7,
], [
    'h' => 'help',
]);

if ($unrecognised) {
    cli_error('Unknown option(s): ' . implode(', ', $unrecognised), 2);
}
if ($options['help']) {
    cli_writeln($usage);
    exit(0);
}

$courseid = (int)$options['courseid'];
$userid = (int)$options['userid'];
$activityid = max(0, (int)$options['activityid']);
$minusers = max(2, (int)$options['minusers']);
$groupid = max(0, (int)$options['groupid']);
$runs = (int)$options['runs'];
$perioddays = (int)$options['perioddays'];

if ($courseid <= 0 || $userid <= 0) {
    cli_error('--courseid and --userid are required positive integers.', 2);
}
if ($runs < 1 || $runs > 50) {
    cli_error('--runs must be between 1 and 50.', 2);
}
if ($perioddays < 1 || $perioddays > 3650) {
    cli_error('--perioddays must be between 1 and 3650.', 2);
}

$course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
$user = $DB->get_record('user', ['id' => $userid, 'deleted' => 0], 'id,username', MUST_EXIST);
$configuredactivitycount = $DB->count_records('videotrack', ['course' => $courseid]);
if ($configuredactivitycount <= 0) {
    cli_error('The selected course has no VideoTrack activities.', 3);
}

if ($activityid > 0) {
    if (!$DB->record_exists('videotrack', ['id' => $activityid, 'course' => $courseid])) {
        cli_error('--activityid does not belong to the selected course.', 2);
    }
} else {
    $sql = "SELECT vt.id, COUNT(st.id) AS statecount
              FROM {videotrack} vt
         LEFT JOIN {videotrack_state} st ON st.videotrackid = vt.id
             WHERE vt.course = :courseid
          GROUP BY vt.id
          ORDER BY statecount DESC, vt.id ASC";
    $candidate = $DB->get_record_sql($sql, ['courseid' => $courseid], IGNORE_MULTIPLE);
    if (!$candidate) {
        cli_error('Unable to select a VideoTrack activity for the single-activity scenarios.', 3);
    }
    $activityid = (int)$candidate->id;
}

$median = static function (array $values): float {
    sort($values, SORT_NUMERIC);
    $count = count($values);
    $middle = intdiv($count, 2);
    if ($count % 2 === 1) {
        return (float)$values[$middle];
    }
    return ((float)$values[$middle - 1] + (float)$values[$middle]) / 2;
};

$summarise = static function (array $runsdata) use ($median): array {
    $metrics = ['reads', 'queries', 'db_ms', 'wall_ms'];
    $summary = [];
    foreach ($metrics as $metric) {
        $values = array_column($runsdata, $metric);
        $summary[$metric] = [
            'min' => min($values),
            'median' => $median($values),
            'max' => max($values),
        ];
        if (str_ends_with($metric, '_ms')) {
            foreach ($summary[$metric] as $key => $value) {
                $summary[$metric][$key] = round((float)$value, 3);
            }
        }
    }
    return $summary;
};

$runscenario = static function (
    string $name,
    int $scenarioactivityid,
    int $timestart,
    int $timeend
) use ($DB, $course, $userid, $minusers, $groupid, $runs, $summarise): array {
    $runsdata = [];
    $rowcount = null;
    for ($run = 0; $run < $runs; $run++) {
        $readsstart = $DB->perf_get_reads();
        $queriesstart = $DB->perf_get_queries();
        $dbtimestart = $DB->perf_get_queries_time();
        $wallstart = microtime(true);

        $rows = \mod_videotrack\local\course_analytics::get_course_rows(
            $course,
            $userid,
            $minusers,
            $scenarioactivityid,
            $groupid,
            $timestart,
            $timeend
        );

        $wallms = (microtime(true) - $wallstart) * 1000;
        $dbms = ($DB->perf_get_queries_time() - $dbtimestart) * 1000;
        $reads = $DB->perf_get_reads() - $readsstart;
        $queries = $DB->perf_get_queries() - $queriesstart;
        $rowcount = count($rows);
        $runsdata[] = [
            'rows' => $rowcount,
            'reads' => $reads,
            'queries' => $queries,
            'db_ms' => round($dbms, 3),
            'wall_ms' => round($wallms, 3),
        ];
    }

    return [
        'name' => $name,
        'activityid' => $scenarioactivityid,
        'rows' => $rowcount,
        'summary' => $summarise($runsdata),
        'runs' => $runsdata,
    ];
};

$now = time();
$periodstart = $now - ($perioddays * DAYSECS);
$scenarios = [
    $runscenario('all_time_single', $activityid, 0, 0),
    $runscenario('all_time_all', 0, 0, 0),
    $runscenario('period_single', $activityid, $periodstart, $now),
    $runscenario('period_all', 0, $periodstart, $now),
];

$byname = [];
foreach ($scenarios as $scenario) {
    $byname[$scenario['name']] = $scenario;
}

$scaling = [];
foreach (['all_time' => ['all_time_single', 'all_time_all'], 'period' => ['period_single', 'period_all']] as $key => $names) {
    $singlemedian = (int)$byname[$names[0]]['summary']['reads']['median'];
    $allmedian = (int)$byname[$names[1]]['summary']['reads']['median'];
    $naive = $singlemedian * $configuredactivitycount;
    $scaling[$names[1]] = [
        'single_median_reads' => $singlemedian,
        'all_median_reads' => $allmedian,
        'naive_single_x_activity_count_reads' => $naive,
        'all_vs_naive_ratio' => $naive > 0 ? round($allmedian / $naive, 4) : null,
    ];
}

$report = [
    'benchmark' => 'mod_videotrack course_analytics',
    'moodle_release' => $CFG->release,
    'php_version' => PHP_VERSION,
    'db_family' => $DB->get_dbfamily(),
    'courseid' => $courseid,
    'userid' => $userid,
    'username' => $user->username,
    'configured_activity_count' => $configuredactivitycount,
    'single_activityid' => $activityid,
    'minusers' => $minusers,
    'groupid' => $groupid,
    'runs_per_scenario' => $runs,
    'perioddays' => $perioddays,
    'scenarios' => $scenarios,
    'scaling' => $scaling,
];

cli_writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
exit(0);
