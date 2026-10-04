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

/**
 * Capability-safe SQL builders for teacher-report Analytics queries.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_analytics_query {
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
     * Adds standard-reaction and optional provider filtering to an Analytics scope condition.
     *
     * Personal notes, bookmarks and deleted rows are intentionally excluded from reaction Analytics.
     *
     * @param string $scopewhere Capability-safe Analytics scope SQL fragment.
     * @param array $scopeparams Capability-safe Analytics scope named parameters.
     * @param string $providerdataid Optional provider video id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function analytics_reaction_condition(
        string $scopewhere,
        array $scopeparams,
        string $providerdataid
    ): array {
        $conditions = '(' . $scopewhere . ') AND isdeleted = 0 ' .
            "AND (notetype = '' OR notetype IS NULL)";
        $params = $scopeparams;
        if ($providerdataid !== '') {
            $conditions .= ' AND videoid = :analyticsreactionvideoid';
            $params['analyticsreactionvideoid'] = $providerdataid;
        }
        return [$conditions, $params];
    }

    /**
     * Adds bookmark and optional provider filtering to an Analytics scope condition.
     *
     * Deleted rows and non-bookmark reaction events are intentionally excluded from bookmark Analytics.
     *
     * @param string $scopewhere Capability-safe Analytics scope SQL fragment.
     * @param array $scopeparams Capability-safe Analytics scope named parameters.
     * @param string $providerdataid Optional provider video id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function analytics_bookmark_condition(
        string $scopewhere,
        array $scopeparams,
        string $providerdataid
    ): array {
        $conditions = '(' . $scopewhere . ") AND isdeleted = 0 AND notetype = 'bookmark'";
        $params = $scopeparams;
        if ($providerdataid !== '') {
            $conditions .= ' AND videoid = :analyticsbookmarkvideoid';
            $params['analyticsbookmarkvideoid'] = $providerdataid;
        }
        return [$conditions, $params];
    }

    /**
     * Adds optional provider filtering to an integrity-Analytics scope condition.
     *
     * The capability-safe scope is preserved exactly when no provider filter is requested.
     *
     * @param string $scopewhere Capability-safe Analytics scope SQL fragment.
     * @param array $scopeparams Capability-safe Analytics scope named parameters.
     * @param string $providerdataid Optional provider video id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function analytics_integrity_condition(
        string $scopewhere,
        array $scopeparams,
        string $providerdataid
    ): array {
        $conditions = $scopewhere;
        $params = $scopeparams;
        if ($providerdataid !== '') {
            $conditions = '(' . $conditions . ') AND videoid = :analyticsintegrityvideoid';
            $params['analyticsintegrityvideoid'] = $providerdataid;
        }
        return [$conditions, $params];
    }

    /**
     * Adds optional provider filtering to a state-Analytics scope condition.
     *
     * The provider constraint applies to the whole capability-safe scope, including
     * multi-activity scopes joined with OR.
     *
     * @param string $scopewhere Capability-safe Analytics scope SQL fragment.
     * @param array $scopeparams Capability-safe Analytics scope named parameters.
     * @param string $providerdataid Optional provider video id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function analytics_state_condition(
        string $scopewhere,
        array $scopeparams,
        string $providerdataid
    ): array {
        $conditions = $scopewhere;
        $params = $scopeparams;
        if ($providerdataid !== '') {
            $conditions = '(' . $conditions . ') AND videoid = :analyticsstatevideoid';
            $params['analyticsstatevideoid'] = $providerdataid;
        }
        return [$conditions, $params];
    }

    /**
     * Adds validated-segment and optional provider filtering to an Analytics scope condition.
     *
     * @param string $scopewhere Capability-safe Analytics scope SQL fragment.
     * @param array $scopeparams Capability-safe Analytics scope named parameters.
     * @param string $providerdataid Optional provider video id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function analytics_segment_condition(
        string $scopewhere,
        array $scopeparams,
        string $providerdataid
    ): array {
        $conditions = '(' . $scopewhere . ') AND servervalidated = 1';
        $params = $scopeparams;
        if ($providerdataid !== '') {
            $conditions .= ' AND videoid = :analyticssegmentvideoid';
            $params['analyticssegmentvideoid'] = $providerdataid;
        }
        return [$conditions, $params];
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
}
