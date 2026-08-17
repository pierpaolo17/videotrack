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
use html_writer;
use invalid_parameter_exception;
use moodle_url;
use tabobject;

/**
 * Request, filter and scope helpers for the teacher report controller.
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
     * Converts an ISO date-only parameter to a timestamp in the user's timezone.
     *
     * @param string $date Date in YYYY-MM-DD format.
     * @param bool $endofday Whether to use the last second of the day.
     * @return int Timestamp, or 0 when the value is empty or invalid.
     */
    public static function date_to_timestamp(string $date, bool $endofday = false): int {
        if ($date === '' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return 0;
        }
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        if (!checkdate($month, $day, $year)) {
            return 0;
        }
        return make_timestamp($year, $month, $day, $endofday ? 23 : 0, $endofday ? 59 : 0, $endofday ? 59 : 0);
    }

    /**
     * Reads an optional video-time filter.
     *
     * The report form submits numeric hour/minute/second controls. Legacy MM:SS and
     * HH:MM:SS links remain supported for backwards compatibility.
     *
     * @param string $name Parameter name.
     * @return float|null Non-negative float value, or null when unset/empty.
     */
    public static function optional_time_param(string $name): ?float {
        $componentnames = [
            'hours' => $name . '_hours',
            'minutes' => $name . '_minutes',
            'seconds' => $name . '_seconds',
        ];
        $rawparts = [];
        foreach ($componentnames as $part => $componentname) {
            $rawparts[$part] = optional_param($componentname, null, PARAM_RAW_TRIMMED);
        }
        $hascomponents = count(array_filter($rawparts, static function ($value): bool {
            return $value !== null;
        })) > 0;
        if ($hascomponents) {
            if (
                ($rawparts['hours'] === '' || $rawparts['hours'] === null)
                && ($rawparts['minutes'] === '' || $rawparts['minutes'] === null)
                && ($rawparts['seconds'] === '' || $rawparts['seconds'] === null)
            ) {
                return null;
            }
            foreach ($rawparts as $rawpart) {
                if ($rawpart !== '' && $rawpart !== null && !preg_match('/^\d+$/', $rawpart)) {
                    throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
                }
            }
            $hours = ($rawparts['hours'] === '' || $rawparts['hours'] === null) ? 0 : (int)$rawparts['hours'];
            $minutes = ($rawparts['minutes'] === '' || $rawparts['minutes'] === null) ? 0 : (int)$rawparts['minutes'];
            $seconds = ($rawparts['seconds'] === '' || $rawparts['seconds'] === null) ? 0 : (int)$rawparts['seconds'];
            if ($minutes > 59 || $seconds > 59) {
                throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
            }
            return (float)(($hours * HOURSECS) + ($minutes * MINSECS) + $seconds);
        }

        $rawvalue = optional_param($name, '', PARAM_RAW_TRIMMED);
        $parsed = videotrack_parse_report_timestamp($rawvalue);
        if ($rawvalue !== '' && $parsed === null) {
            throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
        }
        return $parsed;
    }

    /**
     * Renders a structured duration filter using number inputs.
     *
     * @param string $name Base parameter name.
     * @param string $label Visible field label.
     * @param float|null $value Current value in seconds.
     * @param bool $showhours Whether to render the hours control.
     * @return string HTML fragment.
     */
    public static function duration_filter(string $name, string $label, ?float $value, bool $showhours): string {
        $totalseconds = $value === null ? null : max(0, (int)round($value));
        $hours = $totalseconds === null ? '' : (string)floor($totalseconds / HOURSECS);
        $minutes = $totalseconds === null ? '' : (string)floor(($totalseconds % HOURSECS) / MINSECS);
        $seconds = $totalseconds === null ? '' : (string)($totalseconds % MINSECS);
        if (!$showhours && $totalseconds !== null) {
            $minutes = (string)floor($totalseconds / MINSECS);
        }

        $groupid = 'id_' . $name . '_group';
        $html = html_writer::span($label, 'mr-1', ['id' => $groupid . '_label']);
        $attributes = [
            'type' => 'number',
            'min' => 0,
            'step' => 1,
            'inputmode' => 'numeric',
            'autocomplete' => 'off',
            'class' => 'form-control form-control-sm videotrack-time-part',
            'style' => 'width:4.5rem',
        ];
        if ($showhours) {
            $html .= html_writer::empty_tag('input', array_merge($attributes, [
                'name' => $name . '_hours',
                'id' => 'id_' . $name . '_hours',
                'value' => $hours,
                'aria-label' => get_string('hours'),
            ]));
            $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
        }
        $html .= html_writer::empty_tag('input', array_merge($attributes, [
            'name' => $name . '_minutes',
            'id' => 'id_' . $name . '_minutes',
            'value' => $minutes,
            'max' => 59,
            'aria-label' => get_string('minutes'),
        ]));
        $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
        $html .= html_writer::empty_tag('input', array_merge($attributes, [
            'name' => $name . '_seconds',
            'id' => 'id_' . $name . '_seconds',
            'value' => $seconds,
            'max' => 59,
            'aria-label' => get_string('seconds'),
        ]));
        return html_writer::div($html, 'd-inline-flex align-items-center mr-3 mb-2', [
            'id' => $groupid,
            'role' => 'group',
            'aria-labelledby' => $groupid . '_label',
        ]);
    }

    /**
     * Builds a capability-safe SQL condition for one or more Analytics activities.
     *
     * Each scope record must expose an id and an analyticsgroupids property. A null
     * group list means all canonical learners allowed by the activity context; an empty list
     * excludes the activity; a populated list further restricts learners to those groups.
     *
     * @param array $scopes Analytics activity scope records.
     * @param string $prefix Unique parameter prefix.
     * @param int $viewerid Report viewer id.
     * @return array SQL condition and named parameters.
     */
    public static function analytics_scope_condition(array $scopes, string $prefix, int $viewerid): array {
        global $DB;

        $clauses = [];
        $params = [];
        $index = 0;
        foreach ($scopes as $scope) {
            $groupids = $scope->analyticsgroupids ?? null;
            if (is_array($groupids) && !$groupids) {
                continue;
            }
            $vtparam = $prefix . 'vt' . $index;
            $clause = 'videotrackid = :' . $vtparam;
            $params[$vtparam] = (int)$scope->id;
            $scopecontext = context_module::instance((int)$scope->cmid, MUST_EXIST);
            $scopecm = (object)[
                'id' => (int)$scope->cmid,
                'groupmode' => (int)$scope->groupmode,
                'groupingid' => (int)$scope->groupingid,
            ];
            $scopecourse = (object)[
                'id' => (int)$scope->course,
                'groupmode' => (int)$scope->coursegroupmode,
                'groupmodeforce' => (int)$scope->groupmodeforce,
            ];
            [$learnersql, $learnerparams] = learner_scope::sql(
                $scopecontext,
                $scopecm,
                $scopecourse,
                $viewerid,
                'userid',
                $prefix . 'learner' . $index
            );
            $clause .= ' AND ' . $learnersql;
            $params = array_merge($params, $learnerparams);
            if (is_array($groupids)) {
                [$groupsql, $groupparams] = $DB->get_in_or_equal(
                    array_map('intval', $groupids),
                    SQL_PARAMS_NAMED,
                    $prefix . 'group' . $index
                );
                $clause .= " AND userid IN (
                    SELECT scopegm.userid
                      FROM {groups_members} scopegm
                     WHERE scopegm.groupid {$groupsql}
                )";
                $params = array_merge($params, $groupparams);
            }
            $clauses[] = '(' . $clause . ')';
            $index++;
        }

        return [$clauses ? implode(' OR ', $clauses) : '1 = 0', $params];
    }

    /**
     * Builds a capability-safe SQL condition for current acknowledgement versions.
     *
     * Each enabled activity contributes its own statement hash. Group restrictions
     * mirror the viewing Analytics scope so cross-course results cannot include
     * confirmations outside the teacher's accessible groups.
     *
     * @param array $scopes Analytics activity scope records.
     * @param string $prefix Unique parameter prefix.
     * @param int $viewerid Report viewer id.
     * @return array SQL condition and named parameters.
     */
    public static function acknowledgement_scope_condition(array $scopes, string $prefix, int $viewerid): array {
        global $DB;

        $clauses = [];
        $params = [];
        $index = 0;
        foreach ($scopes as $scope) {
            if (!acknowledgement::is_enabled($scope)) {
                continue;
            }
            $groupids = $scope->analyticsgroupids ?? null;
            if (is_array($groupids) && !$groupids) {
                continue;
            }
            $vtparam = $prefix . 'vt' . $index;
            $hashparam = $prefix . 'hash' . $index;
            $clause = 'videotrackid = :' . $vtparam . ' AND statementhash = :' . $hashparam;
            $params[$vtparam] = (int)$scope->id;
            $params[$hashparam] = acknowledgement::statement_hash($scope);
            $scopecontext = context_module::instance((int)$scope->cmid, MUST_EXIST);
            $scopecm = (object)[
                'id' => (int)$scope->cmid,
                'groupmode' => (int)$scope->groupmode,
                'groupingid' => (int)$scope->groupingid,
            ];
            $scopecourse = (object)[
                'id' => (int)$scope->course,
                'groupmode' => (int)$scope->coursegroupmode,
                'groupmodeforce' => (int)$scope->groupmodeforce,
            ];
            [$learnersql, $learnerparams] = learner_scope::sql(
                $scopecontext,
                $scopecm,
                $scopecourse,
                $viewerid,
                'userid',
                $prefix . 'learner' . $index
            );
            $clause .= ' AND ' . $learnersql;
            $params = array_merge($params, $learnerparams);
            if (is_array($groupids)) {
                [$groupsql, $groupparams] = $DB->get_in_or_equal(
                    array_map('intval', $groupids),
                    SQL_PARAMS_NAMED,
                    $prefix . 'group' . $index
                );
                $clause .= " AND userid IN (
                    SELECT ackgm.userid
                      FROM {groups_members} ackgm
                     WHERE ackgm.groupid {$groupsql}
                )";
                $params = array_merge($params, $groupparams);
            }
            $clauses[] = '(' . $clause . ')';
            $index++;
        }

        return [$clauses ? implode(' OR ', $clauses) : '1 = 0', $params];
    }

    /**
     * Builds the report tab set.
     *
     * @param int $cmid Course module id.
     * @param bool $canviewfullreport Whether the current user may view teacher reports.
     * @param array $baseparams Existing report filter parameters.
     * @return array Report tabs.
     */
    public static function tabs(int $cmid, bool $canviewfullreport, array $baseparams = []): array {
        $studentparams = array_merge($baseparams, ['id' => $cmid, 'mode' => 'student']);
        $cumulativeparams = array_merge($baseparams, ['id' => $cmid, 'mode' => 'cumulative']);
        $tabs = [
            new tabobject(
                'student',
                new moodle_url('/mod/videotrack/report.php', $studentparams),
                get_string('report:perstudent', 'mod_videotrack')
            ),
            new tabobject(
                'cumulative',
                new moodle_url('/mod/videotrack/report.php', $cumulativeparams),
                get_string('report:cumulative', 'mod_videotrack')
            ),
        ];
        if ($canviewfullreport) {
            $tabs[] = new tabobject(
                'analytics',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'analytics']),
                get_string('report:analytics_tab', 'mod_videotrack')
            );
            $tabs[] = new tabobject(
                'export',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'export']),
                get_string('report:csvexport_tab', 'mod_videotrack')
            );
            $tabs[] = new tabobject(
                'recalculate',
                new moodle_url('/mod/videotrack/report.php', ['id' => $cmid, 'mode' => 'recalculate']),
                get_string('report:recalculate_tab', 'mod_videotrack')
            );
        }
        return $tabs;
    }
}
