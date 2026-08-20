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
     * Builds the standard reaction-event report condition and named parameters.
     *
     * Personal notes and bookmarks are intentionally excluded from this event stream.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param int $reactionidfilter Optional reaction id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function reaction_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        int $reactionidfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)";
        $conditions .= " AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        if ($reactionidfilter > 0) {
            $conditions .= ' AND reactionid = :rid';
            $params['rid'] = $reactionidfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :timefrom';
            $params['timefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :timeto';
            $params['timeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the standard bookmark-event report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function bookmark_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :bookmarkvtid AND isdeleted = 0 AND notetype = 'bookmark' AND {$learnerwhere}";
        $params = ['bookmarkvtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :bookmarkuserid';
            $params['bookmarkuserid'] = $useridfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :bookmarktimefrom';
            $params['bookmarktimefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :bookmarktimeto';
            $params['bookmarktimeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the standard integrity-event report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function integrity_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :integrityvtid AND {$learnerwhere}";
        $params = ['integrityvtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :integrityuserid';
            $params['integrityuserid'] = $useridfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :integritytimefrom';
            $params['integritytimefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :integritytimeto';
            $params['integritytimeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the note-user discovery condition and named parameters.
     *
     * This condition is used only to discover learners with personal notes for report user options.
     * Note content, date filtering and export paths remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function note_user_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the personal-note event condition and named parameters.
     *
     * This condition is used by the per-student note list. Note exports remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param int $createdfrom Optional inclusive creation-time lower bound.
     * @param int $createdto Optional inclusive creation-time upper bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function note_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        int $createdfrom,
        int $createdto
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        if ($createdfrom) {
            $conditions .= ' AND timecreated >= :notecreatedfrom';
            $params['notecreatedfrom'] = $createdfrom;
        }
        if ($createdto) {
            $conditions .= ' AND timecreated <= :notecreatedto';
            $params['notecreatedto'] = $createdto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the state-row report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function state_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter
    ): array {
        $conditions = "videotrackid = :svtid AND {$learnerwhere}";
        $params = ['svtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :suid';
            $params['suid'] = $useridfilter;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the segment-user discovery condition and named parameters.
     *
     * This condition is used only to discover learners represented by validated/raw segment rows
     * when assembling the report user filter options. Segment loading and Analytics remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function segment_user_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams
    ): array {
        return [
            "videotrackid = :vtid AND {$learnerwhere}",
            ['vtid' => $videotrackid] + $learnerparams,
        ];
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

        if ($aggregationmode === 'type' && $sort === 'reaction') {
            usort(
                $clusters,
                static fn($a, $b) => [$a['reactionlabel'], $a['timestamp']] <=> [$b['reactionlabel'], $b['timestamp']]
            );
        } else if ($sort === 'clicks') {
            usort($clusters, static fn($a, $b) => $b['count'] <=> $a['count']);
        } else {
            usort($clusters, static fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }
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
