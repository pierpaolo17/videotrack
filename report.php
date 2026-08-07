<?php
// This file is part of Moodle - https://moodle.org/
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
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Formats a report user label without exposing anonymised pseudo-user ids.
 *
 * @param int $userid User id, potentially an anonymised negative pseudo-id.
 * @param array $usermap Real Moodle users keyed by id.
 * @param bool $canviewemail Whether email may be displayed.
 * @return string Safe display label.
 */
function videotrack_report_user_label(int $userid, array $usermap, bool $canviewemail): string {
    if ($userid < 0) {
        return get_string('report:anonymiseduser', 'mod_videotrack');
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
function videotrack_report_date_to_timestamp(string $date, bool $endofday = false): int {
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
function videotrack_report_optional_time_param(string $name): ?float {
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
function videotrack_report_duration_filter(string $name, string $label, ?float $value, bool $showhours): string {
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
function videotrack_report_analytics_scope_condition(array $scopes, string $prefix, int $viewerid): array {
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
        [$learnersql, $learnerparams] = \mod_videotrack\local\learner_scope::sql(
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
function videotrack_report_acknowledgement_scope_condition(array $scopes, string $prefix, int $viewerid): array {
    global $DB;

    $clauses = [];
    $params = [];
    $index = 0;
    foreach ($scopes as $scope) {
        if (!\mod_videotrack\local\acknowledgement::is_enabled($scope)) {
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
        $params[$hashparam] = \mod_videotrack\local\acknowledgement::statement_hash($scope);
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
        [$learnersql, $learnerparams] = \mod_videotrack\local\learner_scope::sql(
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
function videotrack_report_tabs(int $cmid, bool $canviewfullreport, array $baseparams = []): array {
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

/**
 * Formats a timeline interval for analytics reports.
 *
 * @param float $start Interval start.
 * @param float $end Interval end.
 * @param float $duration Video duration.
 * @return string Formatted interval.
 */
function videotrack_report_analytics_interval(float $start, float $end, float $duration): string {
    return videotrack_format_video_timestamp($start, $duration) . '–' .
        videotrack_format_video_timestamp($end, $duration);
}

/**
 * Renders the unique-view heatmap with optional reaction-cluster markers.
 *
 * @param array $bins Privacy-safe analytics bins.
 * @param float $duration Video duration.
 * @param array $reactionclusters Visible reaction clusters.
 * @param int $minusers Privacy threshold.
 * @return string SVG markup.
 */
function videotrack_report_render_analytics_heatmap(
    array $bins,
    float $duration,
    array $reactionclusters,
    int $minusers
): string {
    $width = 1000;
    $height = 92;
    $barstart = 24;
    $barheight = 42;
    $visiblecounts = array_filter(array_column($bins, 'viewers'), static fn($value): bool => $value !== null);
    $maxviewers = $visiblecounts ? max($visiblecounts) : 0;
    $title = get_string('report:analytics_heatmap_title', 'mod_videotrack');
    $description = get_string('report:analytics_heatmap_desc', 'mod_videotrack');

    $svg = html_writer::start_tag('svg', [
        'viewBox' => "0 0 {$width} {$height}",
        'xmlns' => 'http://www.w3.org/2000/svg',
        'role' => 'img',
        'aria-labelledby' => 'videotrack-analytics-heatmap-title videotrack-analytics-heatmap-desc',
        'aria-describedby' => 'videotrack-analytics-table',
        'class' => 'videotrack-analytics-svg videotrack-analytics-heatmap',
    ]);
    $svg .= html_writer::tag('title', s($title), ['id' => 'videotrack-analytics-heatmap-title']);
    $svg .= html_writer::tag('desc', s($description), ['id' => 'videotrack-analytics-heatmap-desc']);
    $svg .= html_writer::start_tag('defs');
    $svg .= html_writer::tag('pattern', html_writer::empty_tag('path', [
        'd' => 'M0 8 L8 0 M-2 2 L2 -2 M6 10 L10 6',
        'class' => 'videotrack-analytics-suppressed-line',
    ]), [
        'id' => 'videotrack-analytics-suppressed-pattern',
        'width' => 8,
        'height' => 8,
        'patternUnits' => 'userSpaceOnUse',
    ]);
    $svg .= html_writer::end_tag('defs');
    $svg .= html_writer::empty_tag('rect', [
        'x' => 0,
        'y' => $barstart,
        'width' => $width,
        'height' => $barheight,
        'class' => 'videotrack-analytics-background',
    ]);

    foreach ($bins as $bin) {
        $x = $duration > 0 ? ($bin['start'] / $duration) * $width : 0;
        $binwidth = $duration > 0 ? (($bin['end'] - $bin['start']) / $duration) * $width : 0;
        $interval = videotrack_report_analytics_interval($bin['start'], $bin['end'], $duration);
        if (!empty($bin['suppressed'])) {
            $tooltip = get_string('report:analytics_bin_suppressed_title', 'mod_videotrack', [
                'interval' => $interval,
                'minusers' => $minusers,
            ]);
            $class = 'videotrack-analytics-bin videotrack-analytics-bin-suppressed';
            $attributes = ['fill' => 'url(#videotrack-analytics-suppressed-pattern)'];
        } else {
            $viewers = (int)($bin['viewers'] ?? 0);
            $tooltip = get_string('report:analytics_bin_title', 'mod_videotrack', [
                'interval' => $interval,
                'viewers' => $viewers,
                'retention' => format_float((float)($bin['retention'] ?? 0), 1),
            ]);
            $class = 'videotrack-analytics-bin';
            $opacity = $maxviewers > 0 ? max(0.08, $viewers / $maxviewers) : 0.08;
            $attributes = ['opacity' => format_float($opacity, 3, false, true)];
        }
        $svg .= html_writer::tag('rect', html_writer::tag('title', s($tooltip)), array_merge($attributes, [
            'x' => format_float($x, 3, false, true),
            'y' => $barstart,
            'width' => max(0.5, (float)format_float($binwidth, 3, false, true)),
            'height' => $barheight,
            'class' => $class,
        ]));
    }

    foreach ($reactionclusters as $cluster) {
        $x = $duration > 0 ? ($cluster['timestamp'] / $duration) * $width : 0;
        $tooltip = get_string('report:analytics_reactionmarker', 'mod_videotrack', [
            'reaction' => format_string($cluster['reactionlabel'], true, ['escape' => false]),
            'count' => (int)$cluster['count'],
            'students' => (int)$cluster['students'],
            'time' => videotrack_format_video_timestamp((float)$cluster['timestamp'], $duration),
        ]);
        $marker = html_writer::tag('title', s($tooltip));
        $marker .= html_writer::empty_tag('line', [
            'x1' => format_float($x, 3, false, true),
            'x2' => format_float($x, 3, false, true),
            'y1' => 14,
            'y2' => $barstart + $barheight + 7,
            'class' => 'videotrack-analytics-reaction-line',
        ]);
        $marker .= html_writer::empty_tag('circle', [
            'cx' => format_float($x, 3, false, true),
            'cy' => 12,
            'r' => 5,
            'class' => 'videotrack-analytics-reaction-marker',
        ]);
        $svg .= html_writer::tag('g', $marker, ['class' => 'videotrack-analytics-reaction-cluster']);
    }

    $svg .= html_writer::tag('text', '0', [
        'x' => 0,
        'y' => 84,
        'class' => 'videotrack-analytics-axis-label',
    ]);
    $svg .= html_writer::tag('text', s(videotrack_format_video_timestamp($duration, $duration)), [
        'x' => $width,
        'y' => 84,
        'text-anchor' => 'end',
        'class' => 'videotrack-analytics-axis-label',
    ]);
    $svg .= html_writer::end_tag('svg');
    return $svg;
}

/**
 * Renders the expandable explanation of analytics calculations and privacy.
 *
 * @param int $minusers Privacy threshold.
 * @param bool $haspartialmasking Whether some interval values are masked.
 * @param bool $showbookmarks Whether bookmark aggregates are available.
 * @param bool $showintegrity Whether diagnostic integrity indicators are available.
 * @param bool $showacknowledgements Whether acknowledgement aggregates are available.
 * @return string Accessible details markup.
 */
function videotrack_report_render_analytics_methodology(
    int $minusers,
    bool $haspartialmasking,
    bool $showbookmarks,
    bool $showintegrity,
    bool $showacknowledgements
): string {
    $items = [
        get_string('report:analytics_method_unique', 'mod_videotrack'),
        get_string('report:analytics_method_retention', 'mod_videotrack'),
        get_string('report:analytics_method_heatmap', 'mod_videotrack'),
        get_string('report:analytics_method_reactions', 'mod_videotrack'),
    ];
    if ($showbookmarks) {
        $items[] = get_string('report:analytics_method_bookmarks', 'mod_videotrack');
    }
    if ($showintegrity) {
        $items[] = get_string('integrity:methodology', 'mod_videotrack');
    }
    if ($showacknowledgements) {
        $items[] = get_string('report:analytics_method_acknowledgements', 'mod_videotrack');
    }
    $content = html_writer::tag(
        'p',
        get_string('report:analytics_method_intro', 'mod_videotrack'),
        ['class' => 'mb-2']
    );
    $content .= html_writer::alist($items, ['class' => 'mb-2']);
    $content .= html_writer::tag(
        'p',
        get_string('report:analytics_method_privacy', 'mod_videotrack', $minusers),
        ['class' => 'mb-0']
    );
    if ($haspartialmasking) {
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_method_partial', 'mod_videotrack'),
            ['class' => 'mt-2 mb-0']
        );
    }

    return html_writer::tag(
        'details',
        html_writer::tag(
            'summary',
            get_string('report:analytics_method_toggle', 'mod_videotrack'),
            ['class' => 'btn btn-secondary btn-sm']
        ) . html_writer::div($content, 'videotrack-analytics-method-content'),
        ['class' => 'videotrack-analytics-method mb-3']
    );
}

/**
 * Renders one privacy warning only when a dataset cannot be displayed.
 *
 * @param bool $viewingsuppressed Whether viewing analytics are hidden.
 * @param bool $reactionssuppressed Whether reaction totals are hidden.
 * @param int $minusers Privacy threshold.
 * @return string Warning notification or an empty string.
 */
function videotrack_report_render_privacy_alert(
    bool $viewingsuppressed,
    bool $reactionssuppressed,
    int $minusers
): string {
    global $OUTPUT;

    if (!$viewingsuppressed && !$reactionssuppressed) {
        return '';
    }
    if ($viewingsuppressed && $reactionssuppressed) {
        $stringkey = 'report:analytics_privacy_unavailable_both';
    } else if ($viewingsuppressed) {
        $stringkey = 'report:analytics_privacy_unavailable_viewing';
    } else {
        $stringkey = 'report:analytics_privacy_unavailable_reactions';
    }
    return $OUTPUT->notification(get_string($stringkey, 'mod_videotrack', $minusers), 'warning');
}

/**
 * Renders a legend explaining heatmap intervals, intensity and markers.
 *
 * @param bool $showreactions Whether reaction markers are shown.
 * @param bool $hassuppressed Whether privacy-patterned intervals are present.
 * @return string Legend markup.
 */
function videotrack_report_render_heatmap_legend(bool $showreactions, bool $hassuppressed): string {
    $items = [
        html_writer::span('', 'videotrack-analytics-legend-swatch videotrack-analytics-legend-bar', [
            'aria-hidden' => 'true',
        ]) . html_writer::span(get_string('report:analytics_heatmap_legend_interval', 'mod_videotrack')),
        html_writer::span('', 'videotrack-analytics-legend-swatch videotrack-analytics-legend-low', [
            'aria-hidden' => 'true',
        ]) . html_writer::span(get_string('report:analytics_heatmap_legend_low', 'mod_videotrack')),
        html_writer::span('', 'videotrack-analytics-legend-swatch videotrack-analytics-legend-high', [
            'aria-hidden' => 'true',
        ]) . html_writer::span(get_string('report:analytics_heatmap_legend_high', 'mod_videotrack')),
    ];
    if ($hassuppressed) {
        $items[] = html_writer::span(
            '',
            'videotrack-analytics-legend-swatch videotrack-analytics-legend-suppressed',
            ['aria-hidden' => 'true']
        ) . html_writer::span(get_string('report:analytics_heatmap_legend_suppressed', 'mod_videotrack'));
    }
    if ($showreactions) {
        $items[] = html_writer::span(
            '',
            'videotrack-analytics-legend-swatch videotrack-analytics-legend-reaction',
            ['aria-hidden' => 'true']
        ) . html_writer::span(get_string('report:analytics_heatmap_legend_reaction', 'mod_videotrack'));
    }

    $content = '';
    foreach ($items as $item) {
        $content .= html_writer::tag('li', $item, ['class' => 'videotrack-analytics-legend-item']);
    }
    return html_writer::div(
        html_writer::tag('strong', get_string('report:analytics_heatmap_legend', 'mod_videotrack')) .
            html_writer::tag('ul', $content, ['class' => 'videotrack-analytics-legend-list']),
        'videotrack-analytics-legend'
    );
}

/**
 * Renders the analytics table download selector.
 *
 * @param string[] $formats Enabled data formats.
 * @param array $params Current analytics filter parameters.
 * @return string Download form or an empty string.
 */
function videotrack_report_render_analytics_download(array $formats, array $params): string {
    if (!$formats) {
        return '';
    }

    $options = [];
    foreach ($formats as $format) {
        $options[$format] = get_string('dataformat', 'dataformat_' . $format);
    }
    $form = html_writer::start_tag('form', [
        'method' => 'get',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-analytics-download-form d-flex flex-wrap align-items-end mb-2',
    ]);
    foreach ($params as $name => $value) {
        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => $name,
            'value' => $value,
        ]);
    }
    $form .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'sesskey',
        'value' => sesskey(),
    ]);
    $form .= html_writer::start_div('form-group mb-0 mr-2');
    $form .= html_writer::label(
        get_string('report:analytics_download_label', 'mod_videotrack'),
        'id_analyticsformat',
        false,
        ['class' => 'd-block']
    );
    $form .= html_writer::select($options, 'analyticsformat', '', false, [
        'id' => 'id_analyticsformat',
        'class' => 'custom-select',
    ]);
    $form .= html_writer::end_div();
    $form .= html_writer::tag('button', get_string('download'), [
        'type' => 'submit',
        'class' => 'btn btn-secondary',
    ]);
    $form .= html_writer::end_tag('form');
    return $form;
}

/**
 * Render privacy-safe reaction clusters independently from viewing analytics.
 *
 * Reaction clusters already satisfy the configured distinct-user threshold.
 * Keeping this table independent prevents a small or missing segment dataset
 * from hiding otherwise valid aggregate reaction information.
 *
 * @param array $clusters Privacy-safe reaction clusters.
 * @param float $duration Video duration in seconds.
 * @return string Accessible cluster table HTML.
 */
function videotrack_report_render_reaction_clusters(array $clusters, float $duration): string {
    global $OUTPUT;

    if (!$clusters) {
        return '';
    }

    $table = new html_table();
    $table->caption = get_string('report:analytics_reactionclusters_caption', 'mod_videotrack');
    $table->head = [
        get_string('report:analytics_reaction_time', 'mod_videotrack'),
        get_string('report:analytics_reaction_type', 'mod_videotrack'),
        get_string('report:analytics_reaction_events', 'mod_videotrack'),
        get_string('report:analytics_reaction_students', 'mod_videotrack'),
    ];
    foreach ($clusters as $cluster) {
        $timestamp = max(0.0, (float)($cluster['timestamp'] ?? 0));
        $table->data[] = [
            videotrack_format_video_timestamp($timestamp, $duration),
            format_string((string)($cluster['reactionlabel'] ?? '')),
            (int)($cluster['count'] ?? 0),
            (int)($cluster['students'] ?? 0),
        ];
    }

    return $OUTPUT->heading(get_string('report:analytics_reactionclusters_title', 'mod_videotrack'), 4) .
        html_writer::table($table);
}

/**
 * Renders a privacy-safe overall reaction summary.
 *
 * @param array $summary Event and distinct-student counts plus suppression state.
 * @return string Plain summary, or an empty string when values are unavailable.
 */
function videotrack_report_render_reaction_summary(array $summary): string {
    if (empty($summary['hasdata']) || !empty($summary['suppressed'])) {
        return '';
    }
    $eventcount = (int)($summary['eventcount'] ?? 0);
    if ($eventcount <= 0) {
        return '';
    }

    $events = get_string('report:analytics_reactions_detected', 'mod_videotrack') . ' ' .
        html_writer::tag('strong', (string)$eventcount);
    $students = get_string('report:analytics_students_involved', 'mod_videotrack') . ' ' .
        html_writer::tag('strong', (string)(int)($summary['studentcount'] ?? 0));
    return html_writer::div($events . html_writer::empty_tag('br') . $students, 'mb-3');
}

/**
 * Renders a privacy-safe bookmark usage summary without exposing labels or timestamps.
 *
 * @param array $summary Event and distinct-student counts plus suppression state.
 * @param int $minusers Privacy threshold.
 * @return string Summary or privacy warning.
 */
function videotrack_report_render_bookmark_summary(array $summary, int $minusers): string {
    global $OUTPUT;

    $hasdata = !empty($summary['hasdata']);
    $suppressed = $hasdata && !empty($summary['suppressed']);
    $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
    $eventvalue = $suppressed ? $hidden : (string)(int)($summary['eventcount'] ?? 0);
    $studentvalue = $suppressed ? $hidden : (string)(int)($summary['studentcount'] ?? 0);

    $cards = [
        [get_string('report:analytics_bookmarks_saved', 'mod_videotrack'), $eventvalue],
        [get_string('report:analytics_bookmark_students', 'mod_videotrack'), $studentvalue],
    ];
    $content = html_writer::tag(
        'h4',
        get_string('report:analytics_bookmarks_title', 'mod_videotrack'),
        ['id' => 'videotrack-analytics-bookmarks-title']
    );
    $content .= html_writer::start_div('videotrack-analytics-summary');
    foreach ($cards as [$label, $value]) {
        $content .= html_writer::div(
            html_writer::div(s($value), 'videotrack-analytics-summary-value') .
                html_writer::div(s($label), 'videotrack-analytics-summary-label'),
            'videotrack-analytics-summary-card'
        );
    }
    $content .= html_writer::end_div();
    $content .= html_writer::tag(
        'p',
        get_string('report:analytics_bookmarks_private', 'mod_videotrack'),
        ['class' => 'text-muted small mb-2']
    );

    if (!$hasdata) {
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_bookmarks_none', 'mod_videotrack'),
            ['class' => 'text-muted mb-0']
        );
    } else if ($suppressed) {
        $content .= $OUTPUT->notification(
            get_string('report:analytics_bookmarks_suppressed', 'mod_videotrack', $minusers),
            'warning'
        );
    }

    return html_writer::tag(
        'section',
        $content,
        [
            'class' => 'videotrack-analytics-bookmarks mb-4',
            'aria-labelledby' => 'videotrack-analytics-bookmarks-title',
        ]
    );
}

/**
 * Renders privacy-safe acknowledgement Analytics.
 *
 * @param array $summary Confirmation counts and progress averages.
 * @param int $minusers Privacy threshold.
 * @param int $enabledactivitycount Number of activities with acknowledgement enabled.
 * @param int $anytimeactivitycount Number using the anytime policy.
 * @param int $videoendactivitycount Number requiring the final video second.
 * @return string Summary section.
 */
function videotrack_report_render_acknowledgement_summary(
    array $summary,
    int $minusers,
    int $enabledactivitycount,
    int $anytimeactivitycount,
    int $videoendactivitycount
): string {
    global $OUTPUT;

    $hasdata = !empty($summary['hasdata']);
    $suppressed = $hasdata && !empty($summary['suppressed']);
    $progresssuppressed = $hasdata && !empty($summary['progresssuppressed']);
    $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
    $unavailable = get_string('report:analytics_acknowledgements_unavailable', 'mod_videotrack');
    $confirmationvalue = $suppressed ? $hidden : (string)(int)($summary['confirmationcount'] ?? 0);
    $studentvalue = $suppressed ? $hidden : (string)(int)($summary['studentcount'] ?? 0);
    $secondsvalue = ($suppressed || $progresssuppressed)
        ? $hidden
        : ($summary['averageviewedseconds'] === null
            ? $unavailable
            : videotrack_format_seconds((float)$summary['averageviewedseconds']));
    $percentvalue = ($suppressed || $progresssuppressed)
        ? $hidden
        : ($summary['averageviewedpercent'] === null
            ? $unavailable
            : format_float((float)$summary['averageviewedpercent'], 1) . '%');

    $cards = [
        [get_string('report:analytics_acknowledgements_confirmations', 'mod_videotrack'), $confirmationvalue],
        [get_string('report:analytics_acknowledgements_students', 'mod_videotrack'), $studentvalue],
        [get_string('report:analytics_acknowledgements_average_seconds', 'mod_videotrack'), $secondsvalue],
        [get_string('report:analytics_acknowledgements_average_percent', 'mod_videotrack'), $percentvalue],
    ];
    $content = html_writer::tag(
        'h4',
        get_string('report:analytics_acknowledgements_title', 'mod_videotrack'),
        ['id' => 'videotrack-analytics-acknowledgements-title']
    );
    $content .= html_writer::tag(
        'p',
        get_string('report:analytics_acknowledgements_scope', 'mod_videotrack', [
            'activities' => $enabledactivitycount,
            'anytime' => $anytimeactivitycount,
            'videoend' => $videoendactivitycount,
        ]),
        ['class' => 'text-muted small']
    );
    $content .= html_writer::start_div('videotrack-analytics-summary');
    foreach ($cards as [$label, $value]) {
        $content .= html_writer::div(
            html_writer::div(s($value), 'videotrack-analytics-summary-value') .
                html_writer::div(s($label), 'videotrack-analytics-summary-label'),
            'videotrack-analytics-summary-card'
        );
    }
    $content .= html_writer::end_div();
    $content .= html_writer::tag(
        'p',
        get_string('report:analytics_acknowledgements_private', 'mod_videotrack'),
        ['class' => 'text-muted small mb-2']
    );

    if (!$hasdata) {
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_acknowledgements_none', 'mod_videotrack'),
            ['class' => 'text-muted mb-0']
        );
    } else if ($suppressed) {
        $content .= $OUTPUT->notification(
            get_string('report:analytics_acknowledgements_suppressed', 'mod_videotrack', $minusers),
            'warning'
        );
    } else if ($progresssuppressed) {
        $content .= $OUTPUT->notification(
            get_string('report:analytics_acknowledgements_progress_suppressed', 'mod_videotrack', $minusers),
            'warning'
        );
    }
    if (!$suppressed && (int)($summary['progressmissing'] ?? 0) > 0) {
        $content .= html_writer::tag(
            'p',
            get_string(
                'report:analytics_acknowledgements_legacy',
                'mod_videotrack',
                (int)$summary['progressmissing']
            ),
            ['class' => 'text-muted small mb-0']
        );
    }

    return html_writer::tag('section', $content, [
        'class' => 'videotrack-analytics-acknowledgements mb-4',
        'aria-labelledby' => 'videotrack-analytics-acknowledgements-title',
    ]);
}

/**
 * Renders privacy-safe diagnostic integrity indicators.
 *
 * The values are signals to review in context, never proof of misconduct.
 *
 * @param array $summary Per-event-type counts and suppression state.
 * @param int $minusers Privacy threshold.
 * @param bool $recordingenabled Whether signal recording is enabled in the selected scope.
 * @param bool $focuscontrolsenabled Whether at least one focus control is enabled in the scope.
 * @param int $enabledactivitycount Number of activities with signal recording enabled.
 * @return string Summary section.
 */
function videotrack_report_render_integrity_summary(
    array $summary,
    int $minusers,
    bool $recordingenabled = true,
    bool $focuscontrolsenabled = false,
    int $enabledactivitycount = 1
): string {
    global $OUTPUT;

    $rows = [];
    $hassuppressed = false;
    foreach (\mod_videotrack\local\integrity::EVENT_TYPES as $eventtype) {
        $item = $summary[$eventtype] ?? [];
        if (empty($item['hasdata'])) {
            continue;
        }
        $suppressed = !empty($item['suppressed']);
        $hassuppressed = $hassuppressed || $suppressed;
        $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
        $rows[] = [
            get_string(\mod_videotrack\local\integrity::label_string($eventtype), 'mod_videotrack'),
            $suppressed ? $hidden : (string)(int)($item['eventcount'] ?? 0),
            $suppressed ? $hidden : (string)(int)($item['studentcount'] ?? 0),
        ];
    }

    $content = html_writer::tag(
        'h4',
        get_string('integrity:reporttitle', 'mod_videotrack'),
        ['id' => 'videotrack-integrity-summary-title']
    );
    $content .= html_writer::tag(
        'p',
        get_string('integrity:reportintro', 'mod_videotrack'),
        ['class' => 'text-muted small']
    );

    if (!$recordingenabled) {
        $message = $focuscontrolsenabled
            ? get_string('integrity:analytics_recording_disabled_controls', 'mod_videotrack')
            : get_string('integrity:analytics_disabled', 'mod_videotrack');
        $content .= $OUTPUT->notification($message, $focuscontrolsenabled ? 'warning' : 'info');
        return html_writer::tag('section', $content, [
            'class' => 'videotrack-integrity-summary mb-4',
            'aria-labelledby' => 'videotrack-integrity-summary-title',
        ]);
    }

    $content .= html_writer::tag(
        'p',
        get_string('integrity:analytics_enabled', 'mod_videotrack', max(1, $enabledactivitycount)),
        ['class' => 'small font-weight-bold']
    );

    if (!$rows) {
        $content .= html_writer::tag(
            'p',
            get_string('integrity:nodata', 'mod_videotrack'),
            ['class' => 'text-muted mb-0']
        );
    } else {
        $table = new html_table();
        $table->caption = get_string('integrity:reporttitle', 'mod_videotrack');
        $table->head = [
            get_string('integrity:signal', 'mod_videotrack'),
            get_string('integrity:events', 'mod_videotrack'),
            get_string('integrity:students', 'mod_videotrack'),
        ];
        $table->data = $rows;
        $content .= html_writer::table($table);
        if ($hassuppressed) {
            $content .= $OUTPUT->notification(
                get_string('integrity:suppressed', 'mod_videotrack', $minusers),
                'warning'
            );
        }
    }

    return html_writer::tag('section', $content, [
        'class' => 'videotrack-integrity-summary mb-4',
        'aria-labelledby' => 'videotrack-integrity-summary-title',
    ]);
}

/**
 * Renders the retention line chart.
 *
 * @param array $bins Privacy-safe analytics bins.
 * @param float $duration Video duration.
 * @return string SVG markup.
 */
function videotrack_report_render_analytics_retention(array $bins, float $duration): string {
    $width = 1000;
    $height = 260;
    $left = 52;
    $right = 12;
    $top = 20;
    $bottom = 34;
    $plotwidth = $width - $left - $right;
    $plotheight = $height - $top - $bottom;
    $title = get_string('report:analytics_retention_title', 'mod_videotrack');
    $description = get_string('report:analytics_retention_desc', 'mod_videotrack');

    $svg = html_writer::start_tag('svg', [
        'viewBox' => "0 0 {$width} {$height}",
        'xmlns' => 'http://www.w3.org/2000/svg',
        'role' => 'img',
        'aria-labelledby' => 'videotrack-analytics-retention-title videotrack-analytics-retention-desc',
        'aria-describedby' => 'videotrack-analytics-table',
        'class' => 'videotrack-analytics-svg videotrack-analytics-retention',
    ]);
    $svg .= html_writer::tag('title', s($title), ['id' => 'videotrack-analytics-retention-title']);
    $svg .= html_writer::tag('desc', s($description), ['id' => 'videotrack-analytics-retention-desc']);

    foreach ([0, 25, 50, 75, 100] as $percentage) {
        $y = $top + $plotheight - (($percentage / 100) * $plotheight);
        $svg .= html_writer::empty_tag('line', [
            'x1' => $left,
            'x2' => $left + $plotwidth,
            'y1' => format_float($y, 3, false, true),
            'y2' => format_float($y, 3, false, true),
            'class' => 'videotrack-analytics-gridline',
        ]);
        $svg .= html_writer::tag('text', $percentage . '%', [
            'x' => $left - 8,
            'y' => format_float($y + 4, 3, false, true),
            'text-anchor' => 'end',
            'class' => 'videotrack-analytics-axis-label',
        ]);
    }

    $paths = [];
    $currentpath = [];
    foreach ($bins as $bin) {
        if (!empty($bin['suppressed']) || $bin['retention'] === null) {
            if ($currentpath) {
                $paths[] = $currentpath;
                $currentpath = [];
            }
            continue;
        }
        $midpoint = ($bin['start'] + $bin['end']) / 2;
        $x = $left + ($duration > 0 ? ($midpoint / $duration) * $plotwidth : 0);
        $y = $top + $plotheight - (((float)$bin['retention'] / 100) * $plotheight);
        $currentpath[] = [$x, $y, $bin];
    }
    if ($currentpath) {
        $paths[] = $currentpath;
    }

    foreach ($paths as $path) {
        $points = array_map(static function (array $point): string {
            return format_float($point[0], 3, false, true) . ',' . format_float($point[1], 3, false, true);
        }, $path);
        if (count($points) > 1) {
            $svg .= html_writer::empty_tag('polyline', [
                'points' => implode(' ', $points),
                'class' => 'videotrack-analytics-retention-line',
            ]);
        }
        foreach ($path as [$x, $y, $bin]) {
            $tooltip = get_string('report:analytics_retention_point', 'mod_videotrack', [
                'interval' => videotrack_report_analytics_interval($bin['start'], $bin['end'], $duration),
                'retention' => format_float((float)$bin['retention'], 1),
                'viewers' => (int)$bin['viewers'],
            ]);
            $svg .= html_writer::tag('circle', html_writer::tag('title', s($tooltip)), [
                'cx' => format_float($x, 3, false, true),
                'cy' => format_float($y, 3, false, true),
                'r' => 3.5,
                'class' => 'videotrack-analytics-retention-point',
            ]);
        }
    }

    $svg .= html_writer::tag('text', '0', [
        'x' => $left,
        'y' => $height - 8,
        'class' => 'videotrack-analytics-axis-label',
    ]);
    $svg .= html_writer::tag('text', s(videotrack_format_video_timestamp($duration, $duration)), [
        'x' => $left + $plotwidth,
        'y' => $height - 8,
        'text-anchor' => 'end',
        'class' => 'videotrack-analytics-axis-label',
    ]);
    $svg .= html_writer::end_tag('svg');
    return $svg;
}

global $DB, $USER, $CFG, $PAGE, $OUTPUT;

$id = required_param('id', PARAM_INT);
$sort = optional_param('sort', 'time', PARAM_ALPHA);
$mode = optional_param('mode', 'student', PARAM_ALPHA);
$aggregation = optional_param('aggregation', 'type', PARAM_ALPHA);
$window = optional_param('window', 0, PARAM_INT);
$export = optional_param('export', '', PARAM_ALPHANUMEXT);
$csvuserid = optional_param('csvuserid', 0, PARAM_INT);
$csvincludereactions = optional_param(
    'csvincludereactions',
    $_SERVER['REQUEST_METHOD'] === 'POST' ? 0 : 1,
    PARAM_BOOL
);
$csvincludenotes = optional_param('csvincludenotes', 0, PARAM_BOOL);
$csvformat = optional_param('csvformat', 'detailed', PARAM_ALPHA);
$analyticsbinsize = optional_param('analyticsbinsize', 0, PARAM_INT);
$analyticsgroupid = optional_param('analyticsgroupid', 0, PARAM_INT);
$analyticsshowreactions = optional_param('analyticsshowreactions', 1, PARAM_BOOL);
$analyticsformat = optional_param('analyticsformat', '', PARAM_ALPHA);
$analyticsallcourses = optional_param('analyticsallcourses', 0, PARAM_BOOL);
$recalculateuserid = optional_param('recalculateuserid', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$resetaction = optional_param('resetaction', '', PARAM_ALPHA);
$useridfilter = optional_param('userid', 0, PARAM_INT);
$reactionidfilter = optional_param('reactionid', 0, PARAM_INT);
$notepage = max(0, optional_param('notepage', 0, PARAM_INT));
$notecreatedfrom = videotrack_optional_iso_date_param('notecreatedfrom');
$notecreatedto = videotrack_optional_iso_date_param('notecreatedto');
$timefrom = videotrack_report_optional_time_param('timefrom');
$timeto = videotrack_report_optional_time_param('timeto');
if ($timefrom !== null && $timeto !== null && $timeto < $timefrom) {
    [$timefrom, $timeto] = [$timeto, $timefrom];
}
$notecreatedfromts = videotrack_report_date_to_timestamp($notecreatedfrom);
$notecreatedtots = videotrack_report_date_to_timestamp($notecreatedto, true);
if ($notecreatedfromts && $notecreatedtots && $notecreatedtots < $notecreatedfromts) {
    [$notecreatedfromts, $notecreatedtots] = [$notecreatedtots, $notecreatedfromts];
}

$cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
$csvincludenotes = !empty($videotrack->studentnotesenabled) && $csvincludenotes;
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$canviewfullreport = has_capability('mod/videotrack:viewreport', $context);
$canviewownreport = has_capability('mod/videotrack:viewownreport', $context);
[$learnerwhere, $learnerparams] = \mod_videotrack\local\learner_scope::sql(
    $context,
    $cm,
    $course,
    (int)$USER->id
);

$window = $window ?: (int)$videotrack->clusterwindow;
$validwindows = [10, 15, 20, 30, 60];
if (!in_array($window, $validwindows, true)) {
    $window = 30;
}
$mode = in_array($mode, ['student', 'cumulative', 'analytics', 'export', 'recalculate'], true) ? $mode : 'student';
if (!$canviewfullreport) {
    require_capability('mod/videotrack:viewownreport', $context);
    if ($mode !== 'student' || ($export !== '' && $export !== 'csv') || $action !== '' || $resetaction !== '') {
        require_capability('mod/videotrack:viewreport', $context);
    }
    // Users with only the own-report capability may see only their own student report.
    $mode = 'student';
    $useridfilter = (int)$USER->id;
}
$aggregation = in_array($aggregation, ['type', 'peak'], true) ? $aggregation : 'type';
$sort = in_array($sort, ['time', 'reaction', 'clicks'], true) ? $sort : 'time';
$csvformat = in_array($csvformat, ['detailed', 'summary', 'overall'], true) ? $csvformat : 'detailed';

if (
    $useridfilter > 0
    && !\mod_videotrack\local\learner_scope::user_is_visible(
        $context,
        $cm,
        $course,
        (int)$USER->id,
        $useridfilter
    )
) {
    throw new moodle_exception('invaliduser', 'error');
}

if ($mode === 'analytics') {
    require_capability('mod/videotrack:viewreport', $context);
    require_once($CFG->libdir . '/grouplib.php');

    $coursecontext = context_course::instance($course->id);
    $currentanalyticsinstance = clone $videotrack;
    $currentanalyticsinstance->cmid = (int)$cm->id;
    $currentanalyticsinstance->groupmode = (int)$cm->groupmode;
    $currentanalyticsinstance->groupingid = (int)$cm->groupingid;
    $currentanalyticsinstance->coursefullname = (string)$course->fullname;
    $currentanalyticsinstance->coursegroupmode = (int)$course->groupmode;
    $currentanalyticsinstance->groupmodeforce = (int)$course->groupmodeforce;
    $currentanalyticsinstance->contextid = (int)$context->id;

    if ($analyticsallcourses) {
        $analyticsinstances = \mod_videotrack\local\analytics_scope::matching_accessible_instances(
            $videotrack,
            $cm,
            (int)$USER->id
        );
        if (!$analyticsinstances) {
            $analyticsinstances = [(int)$videotrack->id => $currentanalyticsinstance];
        }
        $analyticsgroupid = 0;
    } else {
        $analyticsinstances = [(int)$videotrack->id => $currentanalyticsinstance];
    }

    $providerdataid = in_array((string)$videotrack->videosource, ['youtube', 'vimeo'], true)
        ? trim((string)$videotrack->videoid)
        : '';

    $groupoptions = [0 => get_string('report:analytics_allusers', 'mod_videotrack')];
    if (!$analyticsallcourses) {
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context);
        $activitygroupmode = groups_get_activity_groupmode($cm, $course);
        $restricttoowngroups = \mod_videotrack\local\analytics::restrict_to_own_groups(
            $activitygroupmode,
            $canaccessallgroups
        );
        $groupuserid = $restricttoowngroups ? (int)$USER->id : 0;
        $groups = groups_get_all_groups($course->id, $groupuserid, (int)$cm->groupingid, 'g.id,g.name');
        foreach ($groups as $group) {
            $groupoptions[(int)$group->id] = format_string($group->name, true, ['context' => $coursecontext]);
        }
        if ($analyticsgroupid > 0 && !isset($groupoptions[$analyticsgroupid])) {
            throw new invalid_parameter_exception(get_string('report:analytics_invalidgroup', 'mod_videotrack'));
        }
    }

    foreach ($analyticsinstances as $scopeinstance) {
        if (!$analyticsallcourses && (int)$scopeinstance->id === (int)$videotrack->id && $analyticsgroupid > 0) {
            $scopeinstance->analyticsgroupids = [$analyticsgroupid];
            continue;
        }
        $scopeinstance->analyticsgroupids = \mod_videotrack\local\analytics_scope::accessible_group_ids(
            $scopeinstance,
            (int)$USER->id
        );
    }

    // Analytics duration is teacher-authoritative. Client/state/segment values never extend it.
    $duration = 0.0;
    foreach ($analyticsinstances as $scopeinstance) {
        $duration = max($duration, (float)$scopeinstance->durationseconds);
    }
    $analyticsbinsize = \mod_videotrack\local\analytics::normalise_bin_size($analyticsbinsize, $duration);
    $minusers = videotrack_get_config_int('analyticsminusers', 5, 2, 50);

    [$analyticsscopewhere, $segmentparams] = videotrack_report_analytics_scope_condition(
        $analyticsinstances,
        'analyticssegment',
        (int)$USER->id
    );
    $statewhere = $analyticsscopewhere;
    $stateparams = $segmentparams;
    $segmentwhere = '(' . $analyticsscopewhere . ') AND servervalidated = 1';
    if ($providerdataid !== '') {
        $segmentwhere .= ' AND videoid = :analyticssegmentvideoid';
        $segmentparams['analyticssegmentvideoid'] = $providerdataid;
        $statewhere .= ' AND videoid = :analyticsstatevideoid';
        $stateparams['analyticsstatevideoid'] = $providerdataid;
    }
    $segmentrs = $DB->get_recordset_select(
        'videotrack_seg',
        $segmentwhere,
        $segmentparams,
        'userid ASC, id ASC',
        'id, userid, videotimestart, videotimeend'
    );
    try {
        $rawanalytics = \mod_videotrack\local\analytics::build($segmentrs, $duration, $analyticsbinsize);
    } finally {
        $segmentrs->close();
    }

    // Aggregate states recover unique watched coverage when raw segments are incomplete.
    // Multiple states for the same Moodle user are merged across accessible courses.
    $staters = $DB->get_recordset_select(
        'videotrack_state',
        $statewhere,
        $stateparams,
        'userid ASC, id ASC',
        'id, userid, intervaljson'
    );
    try {
        $stateanalytics = \mod_videotrack\local\analytics::build_from_states(
            $staters,
            $duration,
            $analyticsbinsize
        );
    } finally {
        $staters->close();
    }
    $analyticsstatefallback = (int)$stateanalytics['viewers'] > (int)$rawanalytics['viewers']
        || (
            (int)$stateanalytics['viewers'] === (int)$rawanalytics['viewers']
            && (float)$stateanalytics['uniqueseconds'] > (float)$rawanalytics['uniqueseconds'] + 0.001
        );
    $analytics = $analyticsstatefallback ? $stateanalytics : $rawanalytics;
    $analytics = \mod_videotrack\local\analytics::apply_privacy_threshold($analytics, $minusers);

    $reactionclusters = [];
    $reactionclusterstruncated = false;
    $reactionsummary = [
        'hasdata' => false,
        'eventcount' => 0,
        'studentcount' => 0,
        'suppressed' => false,
    ];
    $reactionanalyticsenabled = count(array_filter(
        $analyticsinstances,
        static fn(stdClass $scopeinstance): bool => !empty($scopeinstance->reactionsenabled)
    )) > 0;
    $showreactionanalytics = $reactionanalyticsenabled && $analyticsshowreactions;
    if ($reactionanalyticsenabled) {
        [$reactionwhere, $reactionparams] = videotrack_report_analytics_scope_condition(
            $analyticsinstances,
            'analyticsreaction',
            (int)$USER->id
        );
        $reactionwhere = '(' . $reactionwhere . ') AND isdeleted = 0 '
            . "AND (notetype = '' OR notetype IS NULL)";
        if ($providerdataid !== '') {
            $reactionwhere .= ' AND videoid = :analyticsreactionvideoid';
            $reactionparams['analyticsreactionvideoid'] = $providerdataid;
        }
        $reactionsummaryrecord = $DB->get_record_sql(
            "SELECT COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS studentcount
               FROM {videotrack_reactev}
              WHERE {$reactionwhere}",
            $reactionparams
        );
        $reactionsummary = \mod_videotrack\local\analytics::reaction_summary(
            (int)($reactionsummaryrecord->eventcount ?? 0),
            (int)($reactionsummaryrecord->studentcount ?? 0),
            $minusers
        );

        if ($analyticsshowreactions) {
            $reactionrs = $DB->get_recordset_select(
                'videotrack_reactev',
                $reactionwhere,
                $reactionparams,
                'videotime ASC, id ASC',
                'id, videotrackid, userid, reactionid, reactionkey, reactionlabel, videotime'
            );
            try {
                $reactionresult = \mod_videotrack\local\analytics::cluster_reactions(
                    $reactionrs,
                    max(1, (int)$videotrack->clusterwindow),
                    $minusers
                );
                $reactionclusters = $reactionresult['clusters'];
                $reactionclusterstruncated = $reactionresult['truncated'];
            } finally {
                $reactionrs->close();
            }
        }
    }

    $bookmarksummary = [
        'hasdata' => false,
        'eventcount' => 0,
        'studentcount' => 0,
        'suppressed' => false,
    ];
    $bookmarkinstances = array_filter(
        $analyticsinstances,
        static fn(stdClass $scopeinstance): bool => !empty($scopeinstance->bookmarksenabled)
    );
    $bookmarkanalyticsenabled = !empty($bookmarkinstances);
    if ($bookmarkanalyticsenabled) {
        [$bookmarkwhere, $bookmarkparams] = videotrack_report_analytics_scope_condition(
            $bookmarkinstances,
            'analyticsbookmark',
            (int)$USER->id
        );
        $bookmarkwhere = '(' . $bookmarkwhere . ") AND isdeleted = 0 AND notetype = 'bookmark'";
        if ($providerdataid !== '') {
            $bookmarkwhere .= ' AND videoid = :analyticsbookmarkvideoid';
            $bookmarkparams['analyticsbookmarkvideoid'] = $providerdataid;
        }
        $bookmarksummaryrecord = $DB->get_record_sql(
            "SELECT COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS studentcount
               FROM {videotrack_reactev}
              WHERE {$bookmarkwhere}",
            $bookmarkparams
        );
        $bookmarksummary = \mod_videotrack\local\analytics::count_summary(
            (int)($bookmarksummaryrecord->eventcount ?? 0),
            (int)($bookmarksummaryrecord->studentcount ?? 0),
            $minusers
        );
    }

    $acknowledgementsummary = \mod_videotrack\local\acknowledgement::analytics_summary([], $minusers);
    $acknowledgementinstances = array_filter(
        $analyticsinstances,
        static fn(stdClass $scopeinstance): bool =>
            \mod_videotrack\local\acknowledgement::is_enabled($scopeinstance)
    );
    $acknowledgementanalyticsenabled = !empty($acknowledgementinstances);
    $acknowledgementanytimecount = 0;
    $acknowledgementvideoendcount = 0;
    foreach ($acknowledgementinstances as $acknowledgementinstance) {
        if (\mod_videotrack\local\acknowledgement::requires_video_end($acknowledgementinstance)) {
            $acknowledgementvideoendcount++;
        } else {
            $acknowledgementanytimecount++;
        }
    }
    if ($acknowledgementanalyticsenabled) {
        [$acknowledgementwhere, $acknowledgementparams] =
            videotrack_report_acknowledgement_scope_condition(
                $acknowledgementinstances,
                'analyticsacknowledgement',
                (int)$USER->id
            );
        $acknowledgementrs = $DB->get_recordset_select(
            'videotrack_acknowledge',
            $acknowledgementwhere,
            $acknowledgementparams,
            'id ASC',
            'id, videotrackid, userid, viewedseconds, viewedpercent, timeconfirmed'
        );
        try {
            $acknowledgementsummary = \mod_videotrack\local\acknowledgement::analytics_summary(
                $acknowledgementrs,
                $minusers
            );
        } finally {
            $acknowledgementrs->close();
        }
    }

    $acknowledgementsummary['enabledactivitycount'] = count($acknowledgementinstances);
    $acknowledgementsummary['anytimeactivitycount'] = $acknowledgementanytimecount;
    $acknowledgementsummary['videoendactivitycount'] = $acknowledgementvideoendcount;

    $integritysummary = \mod_videotrack\local\integrity::summarise([], $minusers);
    $integrityinstances = array_filter(
        $analyticsinstances,
        static fn(stdClass $scopeinstance): bool => !empty($scopeinstance->integrityindicatorsenabled)
    );
    $integrityanalyticsenabled = !empty($integrityinstances);
    $integrityfocusinstances = array_filter(
        $analyticsinstances,
        static fn(stdClass $scopeinstance): bool => !empty($scopeinstance->pauseonfocusloss)
            || !empty($scopeinstance->preventpictureinpicture)
            || !empty($scopeinstance->randomfocuspauses)
    );
    $integrityfocuscontrolsenabled = !empty($integrityfocusinstances);
    if ($integrityanalyticsenabled) {
        [$integritywhere, $integrityparams] = videotrack_report_analytics_scope_condition(
            $integrityinstances,
            'analyticsintegrity',
            (int)$USER->id
        );
        if ($providerdataid !== '') {
            $integritywhere = '(' . $integritywhere . ') AND videoid = :analyticsintegrityvideoid';
            $integrityparams['analyticsintegrityvideoid'] = $providerdataid;
        }
        $integrityrows = $DB->get_records_sql(
            "SELECT eventtype, COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS studentcount
               FROM {videotrack_integrity}
              WHERE {$integritywhere}
           GROUP BY eventtype",
            $integrityparams
        );
        $integritysummary = \mod_videotrack\local\integrity::summarise($integrityrows, $minusers);
    }

    $reactionbybin = [];
    foreach ($reactionclusters as $cluster) {
        $binindex = min(
            max(0, count($analytics['bins']) - 1),
            max(0, (int)floor($cluster['timestamp'] / max(1, $analytics['binsize'])))
        );
        if (!isset($reactionbybin[$binindex])) {
            $reactionbybin[$binindex] = ['clusters' => 0, 'events' => 0];
        }
        $reactionbybin[$binindex]['clusters']++;
        $reactionbybin[$binindex]['events'] += (int)$cluster['count'];
    }
    foreach ($analytics['bins'] as $binindex => &$bin) {
        $bin['reactionclusters'] = $reactionbybin[$binindex]['clusters'] ?? 0;
        $bin['reactionevents'] = $reactionbybin[$binindex]['events'] ?? 0;
    }
    unset($bin);

    $hasmaskedbins = count(array_filter($analytics['bins'], static function (array $bin): bool {
        return !empty($bin['suppressed']);
    })) > 0;
    $repeatmetricsavailable = !empty($analytics['repeatmetricsavailable']);
    $hasmaskedrepeats = $repeatmetricsavailable && count(array_filter(
        $analytics['bins'],
        static function (array $bin): bool {
            return !empty($bin['repeatsuppressed']);
        }
    )) > 0;
    $viewingprivacysuppressed = !empty($analytics['datasetsuppressed']);
    $reactionprivacysuppressed = !empty($reactionsummary['hasdata']) && !empty($reactionsummary['suppressed']);
    $analyticsformats = \mod_videotrack\local\analytics_table_export::enabled_formats();

    if ($analyticsformat !== '') {
        require_sesskey();
        $viewingexportavailable = $duration > 0
            && (int)$analytics['viewers'] > 0
            && !$viewingprivacysuppressed;
        if (
            !in_array($analyticsformat, $analyticsformats, true)
            || (!$viewingexportavailable && !$acknowledgementanalyticsenabled)
        ) {
            throw new moodle_exception('report:analytics_export_unavailable', 'mod_videotrack');
        }
        $exportcolumns = \mod_videotrack\local\analytics_table_export::export_columns(
            $showreactionanalytics,
            $acknowledgementanalyticsenabled
        );
        $exportrows = \mod_videotrack\local\analytics_table_export::export_rows(
            $viewingexportavailable ? $analytics['bins'] : [],
            $duration,
            $repeatmetricsavailable,
            $showreactionanalytics,
            $minusers,
            $acknowledgementanalyticsenabled ? $acknowledgementsummary : null
        );
        \mod_videotrack\event\report_exported::create([
            'context' => $context,
            'objectid' => (int)$videotrack->id,
            'other' => [
                'exporttype' => 'analytics_' . $analyticsformat,
                'fieldcount' => count($exportcolumns),
            ],
        ])->trigger();
        $filename = clean_filename('videotrack-analytics-' . format_string(
            $videotrack->name,
            true,
            ['context' => $context]
        ));
        \core\dataformat::download_data($filename, $analyticsformat, $exportcolumns, $exportrows);
        exit;
    }

    $analyticsactivitycount = count($analyticsinstances);
    $analyticscoursecount = count(array_unique(array_map(
        static fn(stdClass $scopeinstance): int => (int)$scopeinstance->course,
        $analyticsinstances
    )));
    $PAGE->set_url('/mod/videotrack/report.php', [
        'id' => $cm->id,
        'mode' => 'analytics',
        'analyticsbinsize' => $analyticsbinsize,
        'analyticsgroupid' => $analyticsgroupid,
        'analyticsshowreactions' => $analyticsshowreactions,
        'analyticsallcourses' => $analyticsallcourses,
    ]);
    $PAGE->set_context($context);
    $PAGE->set_title(format_string($videotrack->name, true, ['context' => $context]));
    $PAGE->set_heading(format_string($course->fullname, true, ['context' => $coursecontext]));

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('reportteacher', 'mod_videotrack'));
    echo $OUTPUT->tabtree(videotrack_report_tabs($cm->id, true), $mode);
    echo $OUTPUT->heading(get_string('report:analytics_heading', 'mod_videotrack'), 3);

    $filterform = html_writer::start_tag('form', [
        'method' => 'get',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-analytics-filters mb-3',
    ]);
    $filterform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    $filterform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => 'analytics']);
    $filterform .= html_writer::start_div('form-group mr-3 mb-2');
    $filterform .= html_writer::label(
        get_string('report:analytics_binsize', 'mod_videotrack'),
        'id_analyticsbinsize',
        false,
        ['class' => 'd-block']
    );
    $binsizeoptions = [];
    foreach (\mod_videotrack\local\analytics::BIN_SIZES as $option) {
        $binsizeoptions[$option] = get_string('report:analytics_binsize_option', 'mod_videotrack', $option);
    }
    if (!isset($binsizeoptions[$analyticsbinsize])) {
        $binsizeoptions[$analyticsbinsize] = get_string(
            'report:analytics_binsize_auto',
            'mod_videotrack',
            $analyticsbinsize
        );
        ksort($binsizeoptions);
    }
    $filterform .= html_writer::select(
        $binsizeoptions,
        'analyticsbinsize',
        $analyticsbinsize,
        false,
        ['id' => 'id_analyticsbinsize', 'class' => 'custom-select']
    );
    $filterform .= html_writer::end_div();

    $filterform .= html_writer::start_div('form-group mr-3 mb-2 align-self-end');
    $filterform .= html_writer::empty_tag('input', [
        'type' => 'hidden',
        'name' => 'analyticsallcourses',
        'value' => 0,
    ]);
    $allcoursesattributes = [
        'type' => 'checkbox',
        'name' => 'analyticsallcourses',
        'id' => 'id_analyticsallcourses',
        'value' => 1,
        'class' => 'form-check-input',
        'aria-describedby' => 'id_analyticsallcourses_help',
    ];
    if ($analyticsallcourses) {
        $allcoursesattributes['checked'] = 'checked';
    }
    $filterform .= html_writer::start_div('form-check');
    $filterform .= html_writer::empty_tag('input', $allcoursesattributes);
    $filterform .= html_writer::label(
        get_string('report:analytics_allcourses', 'mod_videotrack'),
        'id_analyticsallcourses',
        false,
        ['class' => 'form-check-label']
    );
    $filterform .= html_writer::end_div();
    $filterform .= html_writer::div(
        get_string('report:analytics_allcourses_help', 'mod_videotrack'),
        'form-text text-muted small',
        ['id' => 'id_analyticsallcourses_help']
    );
    $filterform .= html_writer::end_div();

    if (!$analyticsallcourses && count($groupoptions) > 1) {
        $filterform .= html_writer::start_div('form-group mr-3 mb-2');
        $filterform .= html_writer::label(
            get_string('report:analytics_group', 'mod_videotrack'),
            'id_analyticsgroupid',
            false,
            ['class' => 'd-block']
        );
        $filterform .= html_writer::select(
            $groupoptions,
            'analyticsgroupid',
            $analyticsgroupid,
            false,
            ['id' => 'id_analyticsgroupid', 'class' => 'custom-select']
        );
        $filterform .= html_writer::end_div();
    }

    if ($reactionanalyticsenabled) {
        $filterform .= html_writer::start_div('form-check mr-3 mb-2 align-self-end');
        $filterform .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'analyticsshowreactions',
            'value' => 0,
        ]);
        $reactioncheckboxattributes = [
            'type' => 'checkbox',
            'name' => 'analyticsshowreactions',
            'id' => 'id_analyticsshowreactions',
            'value' => 1,
            'class' => 'form-check-input',
        ];
        if ($analyticsshowreactions) {
            $reactioncheckboxattributes['checked'] = 'checked';
        }
        $filterform .= html_writer::empty_tag('input', $reactioncheckboxattributes);
        $filterform .= html_writer::label(
            get_string('report:analytics_showreactions', 'mod_videotrack'),
            'id_analyticsshowreactions',
            false,
            ['class' => 'form-check-label']
        );
        $filterform .= html_writer::end_div();
    }
    $filterform .= html_writer::tag('button', get_string('report:analytics_apply', 'mod_videotrack'), [
        'type' => 'submit',
        'class' => 'btn btn-primary mb-2 align-self-end',
    ]);
    $filterform .= html_writer::end_tag('form');
    echo $filterform;

    if ($analyticsallcourses) {
        $scopemessage = $analyticsactivitycount > 1
            ? get_string('report:analytics_allcourses_scope', 'mod_videotrack', [
                'activities' => $analyticsactivitycount,
                'courses' => $analyticscoursecount,
            ])
            : get_string('report:analytics_allcourses_single', 'mod_videotrack');
        echo html_writer::div($scopemessage, 'alert alert-info small');
        echo html_writer::div(
            get_string('report:analytics_allcourses_groups', 'mod_videotrack'),
            'alert alert-light small'
        );
    }

    echo videotrack_report_render_analytics_methodology(
        $minusers,
        $hasmaskedbins || $hasmaskedrepeats,
        $bookmarkanalyticsenabled,
        $integrityanalyticsenabled || $integrityfocuscontrolsenabled,
        $acknowledgementanalyticsenabled
    );
    if ($analyticsstatefallback) {
        echo $OUTPUT->notification(
            get_string('report:analytics_statefallback', 'mod_videotrack'),
            'info'
        );
    }
    if ($reactionclusterstruncated) {
        echo $OUTPUT->notification(
            get_string(
                'report:analytics_reactionlimit',
                'mod_videotrack',
                \mod_videotrack\local\analytics::MAX_REACTION_CLUSTERS
            ),
            'warning'
        );
    }

    echo videotrack_report_render_privacy_alert(
        $viewingprivacysuppressed,
        $reactionprivacysuppressed,
        $minusers
    );
    echo videotrack_report_render_reaction_summary($reactionsummary);
    if ($bookmarkanalyticsenabled) {
        echo videotrack_report_render_bookmark_summary($bookmarksummary, $minusers);
    }
    if ($acknowledgementanalyticsenabled) {
        echo videotrack_report_render_acknowledgement_summary(
            $acknowledgementsummary,
            $minusers,
            count($acknowledgementinstances),
            $acknowledgementanytimecount,
            $acknowledgementvideoendcount
        );
    }
    echo videotrack_report_render_integrity_summary(
        $integritysummary,
        $minusers,
        $integrityanalyticsenabled,
        $integrityfocuscontrolsenabled,
        count($integrityinstances)
    );
    if (
        $analyticsshowreactions
        && !empty($reactionsummary['hasdata'])
        && !$reactionsummary['suppressed']
        && !$reactionclusters
    ) {
        echo $OUTPUT->notification(
            get_string('report:analytics_reactionclusters_none', 'mod_videotrack'),
            'info'
        );
    }

    $downloadparams = [
        'id' => $cm->id,
        'mode' => 'analytics',
        'analyticsbinsize' => $analyticsbinsize,
        'analyticsgroupid' => $analyticsgroupid,
        'analyticsshowreactions' => $analyticsshowreactions,
        'analyticsallcourses' => $analyticsallcourses,
    ];
    if (
        $acknowledgementanalyticsenabled
        || ($duration > 0 && (int)$analytics['viewers'] > 0 && !$viewingprivacysuppressed)
    ) {
        echo videotrack_report_render_analytics_download($analyticsformats, $downloadparams);
    }

    if ($duration <= 0) {
        echo $OUTPUT->notification(get_string('report:analytics_noduration', 'mod_videotrack'), 'warning');
        echo $OUTPUT->footer();
        exit;
    }
    if ((int)$analytics['viewers'] === 0) {
        $haseventsummary = !empty($reactionsummary['hasdata']) || !empty($bookmarksummary['hasdata'])
            || $acknowledgementanalyticsenabled
            || count(array_filter($integritysummary, static fn(array $item): bool => !empty($item['hasdata']))) > 0;
        if (!$reactionclusters && !$haseventsummary) {
            echo $OUTPUT->notification(get_string('report:analytics_nodata', 'mod_videotrack'), 'notifymessage');
            echo $OUTPUT->footer();
            exit;
        }
        echo $OUTPUT->notification(
            get_string('report:analytics_noviewingdata_events', 'mod_videotrack'),
            'info'
        );
        echo videotrack_report_render_reaction_clusters($reactionclusters, $duration);
        echo $OUTPUT->footer();
        exit;
    }
    if ($viewingprivacysuppressed) {
        if ($reactionclusters) {
            echo videotrack_report_render_reaction_clusters($reactionclusters, $duration);
        }
        echo $OUTPUT->footer();
        exit;
    }

    $visiblebins = array_values(array_filter($analytics['bins'], static function (array $bin): bool {
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

    $drops = [];
    $previousbin = null;
    foreach ($analytics['bins'] as $bin) {
        if (!empty($bin['suppressed']) || $bin['viewers'] === null) {
            $previousbin = null;
            continue;
        }
        if ($previousbin !== null && (int)$previousbin['viewers'] > (int)$bin['viewers']) {
            $drops[] = [
                'from' => $previousbin,
                'to' => $bin,
                'count' => (int)$previousbin['viewers'] - (int)$bin['viewers'],
            ];
        }
        $previousbin = $bin;
    }
    usort($drops, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);
    $drops = array_slice($drops, 0, 5);

    $peakinterval = $topwatched
        ? videotrack_report_analytics_interval($topwatched[0]['start'], $topwatched[0]['end'], $duration)
        : get_string('report:analytics_none', 'mod_videotrack');
    $privacyhidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
    $summarycards = [
        [get_string('report:analytics_totalviewers', 'mod_videotrack'), (string)(int)$analytics['viewers']],
        [
            get_string('report:analytics_uniquetime', 'mod_videotrack'),
            $hasmaskedbins ? $privacyhidden : videotrack_format_seconds($analytics['uniqueseconds']),
        ],
        [
            get_string('report:analytics_repeattime', 'mod_videotrack'),
            !$repeatmetricsavailable
                ? get_string('report:analytics_repeat_unavailable', 'mod_videotrack')
                : (($hasmaskedbins || $hasmaskedrepeats)
                    ? $privacyhidden
                    : videotrack_format_seconds($analytics['repeatseconds'])),
        ],
        [get_string('report:analytics_peakinterval', 'mod_videotrack'), $peakinterval],
    ];
    echo html_writer::start_div('videotrack-analytics-summary');
    foreach ($summarycards as [$label, $value]) {
        echo html_writer::div(
            html_writer::div(s($value), 'videotrack-analytics-summary-value') .
                html_writer::div(s($label), 'videotrack-analytics-summary-label'),
            'videotrack-analytics-summary-card'
        );
    }
    echo html_writer::end_div();

    echo $OUTPUT->heading(get_string('report:analytics_heatmap_title', 'mod_videotrack'), 4);
    echo videotrack_report_render_analytics_heatmap(
        $analytics['bins'],
        $duration,
        $reactionclusters,
        $minusers
    );
    echo videotrack_report_render_heatmap_legend(
        $showreactionanalytics && !empty($reactionclusters),
        $hasmaskedbins
    );
    echo videotrack_report_render_reaction_clusters($reactionclusters, $duration);
    echo $OUTPUT->heading(get_string('report:analytics_retention_title', 'mod_videotrack'), 4);
    echo videotrack_report_render_analytics_retention($analytics['bins'], $duration);

    $lists = [
        [get_string('report:analytics_topwatched', 'mod_videotrack'), $topwatched, 'watched'],
        [get_string('report:analytics_topreplayed', 'mod_videotrack'), $topreplayed, 'replayed'],
        [get_string('report:analytics_largestdrops', 'mod_videotrack'), $drops, 'drops'],
    ];
    echo html_writer::start_div('videotrack-analytics-highlights');
    foreach ($lists as [$heading, $items, $listtype]) {
        $listitems = [];
        foreach ($items as $item) {
            if ($listtype === 'watched') {
                $listitems[] = get_string('report:analytics_topwatched_item', 'mod_videotrack', [
                    'interval' => videotrack_report_analytics_interval($item['start'], $item['end'], $duration),
                    'viewers' => (int)$item['viewers'],
                ]);
            } else if ($listtype === 'replayed') {
                $listitems[] = get_string('report:analytics_topreplayed_item', 'mod_videotrack', [
                    'interval' => videotrack_report_analytics_interval($item['start'], $item['end'], $duration),
                    'time' => videotrack_format_seconds((float)$item['repeatseconds']),
                ]);
            } else {
                $listitems[] = get_string('report:analytics_drop_item', 'mod_videotrack', [
                    'from' => videotrack_report_analytics_interval(
                        $item['from']['start'],
                        $item['from']['end'],
                        $duration
                    ),
                    'to' => videotrack_report_analytics_interval(
                        $item['to']['start'],
                        $item['to']['end'],
                        $duration
                    ),
                    'count' => (int)$item['count'],
                ]);
            }
        }
        $content = $listitems
            ? html_writer::alist($listitems, ['class' => 'mb-0'])
            : html_writer::div(get_string('report:analytics_none', 'mod_videotrack'), 'text-muted');
        echo html_writer::div(
            html_writer::tag('h5', s($heading)) . $content,
            'videotrack-analytics-highlight-card'
        );
    }
    echo html_writer::end_div();

    $table = new html_table();
    $table->attributes['id'] = 'videotrack-analytics-table';
    $table->caption = get_string('report:analytics_tablecaption', 'mod_videotrack');
    $table->head = \mod_videotrack\local\analytics_table_export::columns($showreactionanalytics);
    $table->data = \mod_videotrack\local\analytics_table_export::rows(
        $analytics['bins'],
        $duration,
        $repeatmetricsavailable,
        $showreactionanalytics,
        $minusers
    );
    echo html_writer::table($table);
    echo $OUTPUT->footer();
    exit;
}

$sortsql = 'videotime ASC';
if ($sort === 'reaction') {
    $sortsql = 'reactionlabel ASC, videotime ASC';
}

$reactions = videotrack_get_reactions($videotrack->id);
$reactionmap = [];
foreach ($reactions as $reaction) {
    $reactionmap[(int)$reaction->id] = $reaction;
}

// Standard reaction events only. Personal notes and bookmarks are handled separately.
$eventconditions = "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL) AND {$learnerwhere}";
$eventparamsnamed = ['vtid' => $videotrack->id] + $learnerparams;
if ($useridfilter > 0) {
    $eventconditions .= ' AND userid = :uid';
    $eventparamsnamed['uid'] = $useridfilter;
}
if ($reactionidfilter > 0) {
    $eventconditions .= ' AND reactionid = :rid';
    $eventparamsnamed['rid'] = $reactionidfilter;
}
if ($timefrom !== null) {
    $eventconditions .= ' AND videotime >= :timefrom';
    $eventparamsnamed['timefrom'] = $timefrom;
}
if ($timeto !== null) {
    $eventconditions .= ' AND videotime <= :timeto';
    $eventparamsnamed['timeto'] = $timeto;
}
// Avoid loading all reaction events into memory. Use count/distinct queries for filters
// and recordsets only where the full event stream is required for CSV or the clustered report.
$eventcount = $DB->count_records_select('videotrack_reactev', $eventconditions, $eventparamsnamed);
$eventuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_reactev',
    'DISTINCT userid',
    $eventconditions,
    $eventparamsnamed
));
$geteventrecordset = static function () use ($DB, $eventconditions, $eventparamsnamed) {
    return $DB->get_recordset_select(
        'videotrack_reactev',
        $eventconditions,
        $eventparamsnamed,
        'videotime ASC',
        'id, userid, reactionid, reactionlabel, videotime'
    );
};

$bookmarkcounts = [];
$bookmarkuserids = [];
$reportbookmarksummary = [
    'hasdata' => false,
    'eventcount' => 0,
    'studentcount' => 0,
    'suppressed' => false,
];
if (!empty($videotrack->bookmarksenabled)) {
    $bookmarkconditions = "videotrackid = :bookmarkvtid AND isdeleted = 0 AND notetype = 'bookmark' AND {$learnerwhere}";
    $bookmarkparams = ['bookmarkvtid' => $videotrack->id] + $learnerparams;
    if ($useridfilter > 0) {
        $bookmarkconditions .= ' AND userid = :bookmarkuserid';
        $bookmarkparams['bookmarkuserid'] = $useridfilter;
    }
    if ($timefrom !== null) {
        $bookmarkconditions .= ' AND videotime >= :bookmarktimefrom';
        $bookmarkparams['bookmarktimefrom'] = $timefrom;
    }
    if ($timeto !== null) {
        $bookmarkconditions .= ' AND videotime <= :bookmarktimeto';
        $bookmarkparams['bookmarktimeto'] = $timeto;
    }
    $bookmarkrecords = $DB->get_records_sql(
        "SELECT userid, COUNT(id) AS eventcount
           FROM {videotrack_reactev}
          WHERE {$bookmarkconditions}
       GROUP BY userid",
        $bookmarkparams
    );
    $bookmarkeventcount = 0;
    foreach ($bookmarkrecords as $bookmarkrecord) {
        $bookmarkuserid = (int)$bookmarkrecord->userid;
        $bookmarkcounts[$bookmarkuserid] = (int)$bookmarkrecord->eventcount;
        $bookmarkuserids[] = $bookmarkuserid;
        $bookmarkeventcount += (int)$bookmarkrecord->eventcount;
    }
    $reportbookmarksummary = \mod_videotrack\local\analytics::count_summary(
        $bookmarkeventcount,
        count($bookmarkuserids),
        videotrack_get_config_int('analyticsminusers', 5, 2, 50)
    );
}

$integritycounts = [];
$integrityuserids = [];
$reportintegritysummary = \mod_videotrack\local\integrity::summarise(
    [],
    videotrack_get_config_int('analyticsminusers', 5, 2, 50)
);
if (!empty($videotrack->integrityindicatorsenabled)) {
    $integrityconditions = "videotrackid = :integrityvtid AND {$learnerwhere}";
    $integrityparams = ['integrityvtid' => $videotrack->id] + $learnerparams;
    if ($useridfilter > 0) {
        $integrityconditions .= ' AND userid = :integrityuserid';
        $integrityparams['integrityuserid'] = $useridfilter;
    }
    if ($timefrom !== null) {
        $integrityconditions .= ' AND videotime >= :integritytimefrom';
        $integrityparams['integritytimefrom'] = $timefrom;
    }
    if ($timeto !== null) {
        $integrityconditions .= ' AND videotime <= :integritytimeto';
        $integrityparams['integritytimeto'] = $timeto;
    }
    $integritycountrows = $DB->get_records_sql(
        "SELECT userid, COUNT(id) AS eventcount
           FROM {videotrack_integrity}
          WHERE {$integrityconditions}
       GROUP BY userid",
        $integrityparams
    );
    foreach ($integritycountrows as $integritycountrow) {
        $integrityuserid = (int)$integritycountrow->userid;
        $integritycounts[$integrityuserid] = (int)$integritycountrow->eventcount;
        $integrityuserids[] = $integrityuserid;
    }
    $integritytyperows = $DB->get_records_sql(
        "SELECT eventtype, COUNT(id) AS eventcount, COUNT(DISTINCT userid) AS studentcount
           FROM {videotrack_integrity}
          WHERE {$integrityconditions}
       GROUP BY eventtype",
        $integrityparams
    );
    $reportintegritysummary = \mod_videotrack\local\integrity::summarise(
        $integritytyperows,
        videotrack_get_config_int('analyticsminusers', 5, 2, 50)
    );
}

$acknowledgementrecords = [];
$acknowledgementuserids = [];
if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
    foreach (\mod_videotrack\local\acknowledgement::current_records($videotrack) as $record) {
        $ackuserid = (int)$record->userid;
        if (
            !\mod_videotrack\local\learner_scope::user_is_visible(
                $context,
                $cm,
                $course,
                (int)$USER->id,
                $ackuserid
            )
        ) {
            continue;
        }
        $acknowledgementrecords[$ackuserid] = $record;
        $acknowledgementuserids[] = $ackuserid;
    }
}

$stateconditions = "videotrackid = :svtid AND {$learnerwhere}";
$stateparamsnamed = ['svtid' => $videotrack->id] + $learnerparams;
if ($useridfilter > 0) {
    $stateconditions .= ' AND userid = :suid';
    $stateparamsnamed['suid'] = $useridfilter;
}
$statecount = $DB->count_records_select('videotrack_state', $stateconditions, $stateparamsnamed);
$stateuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_state',
    'DISTINCT userid',
    $stateconditions,
    $stateparamsnamed
));
$segmentuserids = array_map('intval', $DB->get_fieldset_select(
    'videotrack_seg',
    'DISTINCT userid',
    "videotrackid = :vtid AND {$learnerwhere}",
    ['vtid' => $videotrack->id] + $learnerparams
));
$getstaterecordset = static function () use ($DB, $stateconditions, $stateparamsnamed) {
    return $DB->get_recordset_select(
        'videotrack_state',
        $stateconditions,
        $stateparamsnamed,
        'completionpercent DESC, uniquecoveredseconds DESC'
    );
};

// Collect note user ids (they may have neither state nor events).
$noteuserids = [];
if (!empty($videotrack->studentnotesenabled)) {
    $noteuidparams = ['vtid' => $videotrack->id] + $learnerparams;
    $noteuidwhere  = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
    if ($useridfilter > 0) {
        $noteuidwhere .= ' AND userid = :uid';
        $noteuidparams['uid'] = $useridfilter;
    }
    foreach ($DB->get_fieldset_select('videotrack_reactev', 'DISTINCT userid', $noteuidwhere, $noteuidparams) as $nuid) {
        $noteuserids[] = (int)$nuid;
    }
}

// Load all required users in a single query instead of N core_user::get_user() calls.
$alluserids = array_values(array_filter(array_unique(array_merge(
    $stateuserids,
    $segmentuserids,
    $eventuserids,
    $noteuserids,
    $bookmarkuserids,
    $integrityuserids,
    $acknowledgementuserids
)), static function (int $userid): bool {
    return $userid > 0;
}));
$usermap = [];
$canviewemail = false;
if ($alluserids) {
    [$insql, $inparams] = $DB->get_in_or_equal($alluserids, SQL_PARAMS_NAMED);
    // Email is visible only to users with the viewreport capability and permission to see email addresses.
    // GDPR minimisation: by default show only the full name.
    $canviewemail = has_capability('moodle/site:viewuseridentity', $context) &&
            in_array('email', \core_user\fields::get_identity_fields($context, false));
    // Select all Moodle name fields required by fullname(). Email is loaded only when permitted.
    $userfields = array_unique(array_merge(
        ['id', 'deleted'],
        \core_user\fields::get_name_fields(),
        $canviewemail ? ['email'] : []
    ));
    foreach ($DB->get_records_select('user', "id $insql", $inparams, '', implode(',', $userfields)) as $u) {
        $usermap[(int)$u->id] = $u;
    }
}

$useroptions = [0 => get_string('all')];
foreach ($stateuserids as $stateuserid) {
    $user = $usermap[(int)$stateuserid] ?? null;
    if ($user) {
        $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
    }
}
foreach ($segmentuserids as $segmentuserid) {
    if (!isset($useroptions[(int)$segmentuserid])) {
        $user = $usermap[(int)$segmentuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}
foreach ($eventuserids as $eventuserid) {
    if (!isset($useroptions[(int)$eventuserid])) {
        $user = $usermap[(int)$eventuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}
foreach ($noteuserids as $noteuserid) {
    if (!isset($useroptions[(int)$noteuserid])) {
        $user = $usermap[(int)$noteuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label((int)$user->id, $usermap, $canviewemail);
        }
    }
}

foreach ($bookmarkuserids as $bookmarkuserid) {
    if (!isset($useroptions[(int)$bookmarkuserid])) {
        $user = $usermap[(int)$bookmarkuserid] ?? null;
        if ($user) {
            $useroptions[(int)$user->id] = videotrack_report_user_label(
                (int)$user->id,
                $usermap,
                $canviewemail
            );
        }
    }
}

foreach ($acknowledgementuserids as $acknowledgementuserid) {
    if (!isset($useroptions[$acknowledgementuserid])) {
        $user = $usermap[$acknowledgementuserid] ?? null;
        if ($user) {
            $useroptions[$acknowledgementuserid] = videotrack_report_user_label(
                $acknowledgementuserid,
                $usermap,
                $canviewemail
            );
        }
    }
}

$reactionoptions = [0 => get_string('all')];
foreach ($reactions as $reaction) {
    $reactionoptions[(int)$reaction->id] = $reaction->label;
}

// Define $baseparams once for exports, actions and navigation links.
$baseparams = [
    'id' => $cm->id,
    'mode' => $mode,
    'sort' => $sort,
    'aggregation' => $aggregation,
    'window' => $window,
    'userid' => $useridfilter,
    'reactionid' => $reactionidfilter,
    'timefrom' => $timefrom === null ? '' : videotrack_format_video_timestamp(
        $timefrom,
        (float)$videotrack->durationseconds
    ),
    'timeto' => $timeto === null ? '' : videotrack_format_video_timestamp(
        $timeto,
        (float)$videotrack->durationseconds
    ),
    'notecreatedfrom' => $notecreatedfrom,
    'notecreatedto' => $notecreatedto,
];
$baseurl = new moodle_url('/mod/videotrack/report.php', $baseparams);
$hasvideotimefilter = ($timefrom !== null || $timeto !== null);

// Load grade_get_grades once for all report sections.
$hasgrade  = !empty($videotrack->grade);
$cangrade = has_capability('mod/videotrack:grade', $context);
$gradeinfo = null;
if ($hasgrade && $cangrade && $alluserids) {
    require_once($CFG->libdir . '/gradelib.php');
    $gradeinfo = grade_get_grades(
        $course->id,
        'mod',
        'videotrack',
        $videotrack->id,
        array_keys($usermap)
    );
}

$clusterlimitreached = false;
$clusterize = function (
    iterable $events,
    int $windowseconds,
    string $aggregationmode
) use (
    $reactionmap,
    $sort,
    $context,
    &$clusterlimitreached
) {
    // Events are processed in timestamp order. Keep only the latest open cluster
    // per reaction (or a single cluster for peak mode), avoiding the former O(n * clusters).
    // scan for every event.
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
            // Safety valve for very large datasets. CSV/report remains deterministic;
            // administrators should use filters for deeper analysis on huge courses.
            $clusterlimitreached = true;
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
        usort($clusters, static fn($a, $b) => [$a['reactionlabel'], $a['timestamp']] <=> [$b['reactionlabel'], $b['timestamp']]);
    } else if ($sort === 'clicks') {
        usort($clusters, static fn($a, $b) => $b['count'] <=> $a['count']);
    } else {
        usort($clusters, static fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
    }
    return $clusters;
};

$csvdelimiter = \mod_videotrack\local\csv_export::delimiter($videotrack);
$csvfields = \mod_videotrack\local\csv_export::activity_fields($videotrack, $context);
$csvusermap = \mod_videotrack\local\csv_export::load_users($alluserids, $csvfields);
$videoduration = (float)$videotrack->durationseconds;

if ($export === 'custom_csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    if (!$csvincludereactions && !$csvincludenotes) {
        throw new moodle_exception('report:csvexport_selectcontent', 'mod_videotrack');
    }
    if ($csvformat === 'overall') {
        $csvuserid = 0;
    }
    if ($csvuserid > 0) {
        if (
            !\mod_videotrack\local\learner_scope::user_is_visible(
                $context,
                $cm,
                $course,
                (int)$USER->id,
                $csvuserid
            )
        ) {
            throw new moodle_exception('invaliduser', 'error');
        }
        $exportuserids = [$csvuserid];
    } else {
        $exportuserids = array_map('intval', $DB->get_fieldset_select(
            'videotrack_reactev',
            'DISTINCT userid',
            "videotrackid = :vtid AND isdeleted = 0 AND {$learnerwhere}",
            ['vtid' => $videotrack->id] + $learnerparams
        ));
    }
    $exportuserids = array_values(array_intersect(
        array_filter(array_unique($exportuserids), static function (int $userid): bool {
            return $userid > 0;
        }),
        $alluserids
    ));
    $exportusermap = \mod_videotrack\local\csv_export::load_users($exportuserids, $csvfields);

    \mod_videotrack\event\report_exported::create([
        'objectid' => (int)$videotrack->id,
        'context' => $context,
        'userid' => (int)$USER->id,
        'other' => [
            'exporttype' => 'custom_csv_' . $csvformat,
            'fieldcount' => count($csvfields),
        ],
    ])->trigger();

    $filename = 'videotrack_export_' . $cm->id . '_' . $csvformat . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);

    $eventheaders = [
        get_string('report:csvcol_eventtype', 'mod_videotrack'),
        get_string('report:reaction', 'mod_videotrack'),
        get_string('report:csvcol_comment', 'mod_videotrack'),
        get_string('report:timestamp', 'mod_videotrack'),
        get_string('report:csvcol_firsttimestamp', 'mod_videotrack'),
        get_string('report:csvcol_lasttimestamp', 'mod_videotrack'),
        get_string('report:csvcol_count', 'mod_videotrack'),
    ];
    if ($csvformat === 'overall') {
        $eventheaders[] = get_string('report:students', 'mod_videotrack');
    } else {
        $eventheaders[] = get_string('report:csvcol_created', 'mod_videotrack');
    }
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        $eventheaders
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);

    $writeeventrow = static function (
        int $userid,
        string $eventtype,
        string $reactionlabel,
        string $comment,
        float $timestamp,
        float $firsttimestamp,
        float $lasttimestamp,
        int $count,
        string $created,
        int $studentcount = 1
    ) use (
        $fh,
        $csvdelimiter,
        $csvfields,
        $course,
        $videotrack,
        $exportusermap,
        $cm,
        $videoduration,
        $csvformat
    ): void {
        $user = $userid > 0 ? ($exportusermap[$userid] ?? null) : null;
        if ($userid > 0 && !$user) {
            return;
        }
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            $userid > 0 ? videotrack_report_user_label($userid, $exportusermap, false) : '',
            (int)$cm->id
        );
        $row = array_merge($row, [
            $eventtype,
            $reactionlabel,
            $comment,
            videotrack_format_video_timestamp($timestamp, $videoduration),
            videotrack_format_video_timestamp($firsttimestamp, $videoduration),
            videotrack_format_video_timestamp($lasttimestamp, $videoduration),
            $count,
        ]);
        if ($csvformat === 'overall') {
            $row[] = $studentcount;
        } else {
            $row[] = $created;
        }
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
    };

    $scopewhere = "videotrackid = :vtid AND isdeleted = 0 AND {$learnerwhere}";
    $scopeparams = ['vtid' => $videotrack->id] + $learnerparams;
    if ($csvuserid > 0 && $csvformat !== 'overall') {
        $scopewhere .= ' AND userid = :exportuserid';
        $scopeparams['exportuserid'] = $csvuserid;
    }

    if ($csvformat === 'detailed') {
        $typeconditions = [];
        if ($csvincludereactions) {
            $typeconditions[] = "(notetype = '' OR notetype IS NULL)";
        }
        if ($csvincludenotes && !empty($videotrack->studentnotesenabled)) {
            $typeconditions[] = "notetype = 'note'";
        }
        if ($typeconditions) {
            $rs = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . ' AND (' . implode(' OR ', $typeconditions) . ')',
                $scopeparams,
                'userid ASC, videotime ASC, timecreated ASC',
                'userid, reactionlabel, notetext, notetype, videotime, timecreated'
            );
            foreach ($rs as $record) {
                $isnote = $record->notetype === 'note';
                $writeeventrow(
                    (int)$record->userid,
                    get_string($isnote ? 'report:eventtype_note' : 'report:eventtype_reaction', 'mod_videotrack'),
                    $isnote ? '' : format_string($record->reactionlabel, true, ['context' => $context]),
                    $isnote ? (string)$record->notetext : '',
                    (float)$record->videotime,
                    (float)$record->videotime,
                    (float)$record->videotime,
                    1,
                    userdate((int)$record->timecreated)
                );
            }
            $rs->close();
        }
    } else {
        if ($csvincludereactions) {
            $reactionrs = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . " AND (notetype = '' OR notetype IS NULL)",
                $scopeparams,
                $csvformat === 'overall' ? 'videotime ASC' : 'userid ASC, videotime ASC',
                'userid, reactionid, reactionlabel, videotime'
            );
            if ($csvformat === 'overall') {
                foreach ($clusterize($reactionrs, $window, 'type') as $cluster) {
                    $writeeventrow(
                        0,
                        get_string('report:eventtype_reaction', 'mod_videotrack'),
                        (string)$cluster['reactionlabel'],
                        '',
                        (float)$cluster['timestamp'],
                        (float)$cluster['first'],
                        (float)$cluster['last'],
                        (int)$cluster['count'],
                        '',
                        (int)$cluster['students']
                    );
                }
            } else {
                $currentuserid = 0;
                $userevents = [];
                $flushclusters = static function () use (
                    &$currentuserid,
                    &$userevents,
                    $clusterize,
                    $window,
                    $writeeventrow
                ): void {
                    if ($currentuserid <= 0 || !$userevents) {
                        return;
                    }
                    foreach ($clusterize($userevents, $window, 'type') as $cluster) {
                        $writeeventrow(
                            $currentuserid,
                            get_string('report:eventtype_reaction', 'mod_videotrack'),
                            (string)$cluster['reactionlabel'],
                            '',
                            (float)$cluster['timestamp'],
                            (float)$cluster['first'],
                            (float)$cluster['last'],
                            (int)$cluster['count'],
                            ''
                        );
                    }
                    $userevents = [];
                };
                foreach ($reactionrs as $reactionevent) {
                    $userid = (int)$reactionevent->userid;
                    if ($currentuserid !== 0 && $userid !== $currentuserid) {
                        $flushclusters();
                    }
                    $currentuserid = $userid;
                    $userevents[] = $reactionevent;
                }
                $flushclusters();
            }
            $reactionrs->close();
        }
        if ($csvincludenotes && !empty($videotrack->studentnotesenabled)) {
            $noters = $DB->get_recordset_select(
                'videotrack_reactev',
                $scopewhere . " AND notetype = 'note'",
                $scopeparams,
                $csvformat === 'overall'
                    ? 'videotime ASC, timecreated ASC'
                    : 'userid ASC, videotime ASC, timecreated ASC',
                'userid, notetext, videotime, timecreated'
            );
            if ($csvformat === 'overall') {
                foreach (\mod_videotrack\local\csv_export::cluster_notes($noters, $window) as $cluster) {
                    $writeeventrow(
                        0,
                        get_string('report:eventtype_note', 'mod_videotrack'),
                        '',
                        (string)$cluster['comment'],
                        (float)$cluster['timestamp'],
                        (float)$cluster['first'],
                        (float)$cluster['last'],
                        (int)$cluster['count'],
                        '',
                        (int)$cluster['students']
                    );
                }
            } else {
                foreach ($noters as $note) {
                    $writeeventrow(
                        (int)$note->userid,
                        get_string('report:eventtype_note', 'mod_videotrack'),
                        '',
                        (string)$note->notetext,
                        (float)$note->videotime,
                        (float)$note->videotime,
                        (float)$note->videotime,
                        1,
                        userdate((int)$note->timecreated)
                    );
                }
            }
            $noters->close();
        }
    }

    fclose($fh);
    exit;
}

if ($export === 'events_csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    if (!optional_param('confirmeventsexport', 0, PARAM_BOOL)) {
        throw new moodle_exception('report:exportallevents_confirmrequired', 'mod_videotrack');
    }

    $alleventuserids = array_map('intval', $DB->get_fieldset_select(
        'videotrack_reactev',
        'DISTINCT userid',
        "videotrackid = :vtid AND isdeleted = 0 AND {$learnerwhere}",
        ['vtid' => $videotrack->id] + $learnerparams
    ));
    $alleventusermap = \mod_videotrack\local\csv_export::load_users($alleventuserids, $csvfields);
    $event = \mod_videotrack\event\report_exported::create([
        'objectid' => (int)$videotrack->id,
        'context' => $context,
        'userid' => (int)$USER->id,
        'other' => [
            'exporttype' => 'events_csv',
            'fieldcount' => count($csvfields),
        ],
    ]);
    $event->trigger();

    $filename = 'videotrack_events_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        [
            get_string('report:csvcol_eventtype', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:csvcol_comment', 'mod_videotrack'),
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:csvcol_created', 'mod_videotrack'),
        ]
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);

    $rs = $DB->get_recordset_select(
        'videotrack_reactev',
        "videotrackid = :vtid AND isdeleted = 0 AND {$learnerwhere} " .
            "AND (notetype = '' OR notetype IS NULL OR notetype = 'note')",
        ['vtid' => $videotrack->id] + $learnerparams,
        'userid ASC, videotime ASC, timecreated ASC',
        'userid, reactionlabel, notetext, notetype, videotime, timecreated'
    );
    foreach ($rs as $record) {
        $userid = (int)$record->userid;
        $user = $alleventusermap[$userid] ?? null;
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            videotrack_report_user_label($userid, $alleventusermap, false),
            (int)$cm->id
        );
        $isnote = $record->notetype === 'note';
        $row = array_merge($row, [
            get_string($isnote ? 'report:eventtype_note' : 'report:eventtype_reaction', 'mod_videotrack'),
            $isnote ? '' : format_string($record->reactionlabel, true, ['context' => $context]),
            $isnote ? (string)$record->notetext : '',
            videotrack_format_video_timestamp((float)$record->videotime, $videoduration),
            userdate((int)$record->timecreated),
        ]);
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
    }
    $rs->close();
    fclose($fh);
    exit;
}

if ($export === 'notes_csv' && !empty($videotrack->studentnotesenabled)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:viewreport', $context);
    $confirmnotesexport = optional_param('confirmnotesexport', 0, PARAM_BOOL);
    if (!$confirmnotesexport) {
        throw new moodle_exception('report:exportnotes_confirmrequired', 'mod_videotrack');
    }
    if (
        $useridfilter > 0
        && !\mod_videotrack\local\learner_scope::user_is_visible(
            $context,
            $cm,
            $course,
            (int)$USER->id,
            $useridfilter
        )
    ) {
        throw new moodle_exception('invaliduser', 'error');
    }
    $event = \mod_videotrack\event\notes_exported::create([
        'objectid' => (int)$videotrack->id,
        'context'  => $context,
        'userid'   => (int)$USER->id,
        'other'    => [
            'useridfilter' => (int)$useridfilter,
            'createdfrom' => (int)$notecreatedfromts,
            'createdto' => (int)$notecreatedtots,
            'emailincluded' => in_array('email', $csvfields, true),
        ],
    ]);
    $event->trigger();
    $filename = 'videotrack_notes_' . $cm->id . '_' . gmdate('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    $headers = array_merge(
        \mod_videotrack\local\csv_export::identity_headers($csvfields),
        [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:csvcol_comment', 'mod_videotrack'),
            get_string('report:csvcol_created', 'mod_videotrack'),
        ]
    );
    \mod_videotrack\local\csv_export::write_row($fh, $headers, $csvdelimiter);
    $notecsvwhere = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
    $notecsvparams = ['vtid' => $videotrack->id] + $learnerparams;
    if ($useridfilter > 0) {
        $notecsvwhere .= ' AND userid = :uid';
        $notecsvparams['uid'] = $useridfilter;
    }
    if ($notecreatedfromts) {
        $notecsvwhere .= ' AND timecreated >= :notecreatedfrom';
        $notecsvparams['notecreatedfrom'] = $notecreatedfromts;
    }
    if ($notecreatedtots) {
        $notecsvwhere .= ' AND timecreated <= :notecreatedto';
        $notecsvparams['notecreatedto'] = $notecreatedtots;
    }
    $rs = $DB->get_recordset_select(
        'videotrack_reactev',
        $notecsvwhere,
        $notecsvparams,
        'userid ASC, videotime ASC',
        'userid, videotime, notetext, timecreated'
    );
    foreach ($rs as $note) {
        $userid = (int)$note->userid;
        $user = $csvusermap[$userid] ?? null;
        $row = \mod_videotrack\local\csv_export::identity_values(
            $csvfields,
            $course,
            $videotrack,
            $user,
            videotrack_report_user_label($userid, $csvusermap, false),
            (int)$cm->id
        );
        $row = array_merge($row, [
            videotrack_format_video_timestamp((float)$note->videotime, $videoduration),
            $note->notetext,
            userdate((int)$note->timecreated),
        ]);
        \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
    }
    $rs->close();
    fclose($fh);
    exit;
}

if ($export === 'csv') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    $filename = 'videotrack_report_' . $cm->id . '_' . $mode . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $fh = fopen('php://output', 'w');
    \mod_videotrack\local\csv_export::write_utf8_bom($fh);
    if ($mode === 'cumulative') {
        $eventrs = $geteventrecordset();
        $clusters = $clusterize($eventrs, $window, $aggregation);
        $eventrs->close();
        if ($clusterlimitreached) {
            $warninglabel = get_string('report:csvcol_warning', 'mod_videotrack');
            \mod_videotrack\local\csv_export::write_row(
                $fh,
                [$warninglabel, get_string('report:clusterlimitreached_csv', 'mod_videotrack')],
                $csvdelimiter
            );
            if (!$hasvideotimefilter) {
                \mod_videotrack\local\csv_export::write_row(
                    $fh,
                    [$warninglabel, get_string('report:clusterlimitrequiresfilters_csv', 'mod_videotrack')],
                    $csvdelimiter
                );
                \mod_videotrack\local\csv_export::write_row(
                    $fh,
                    [$warninglabel, get_string('report:clusterexportblocked_csv', 'mod_videotrack')],
                    $csvdelimiter
                );
                fclose($fh);
                exit;
            }
            \mod_videotrack\local\csv_export::write_row($fh, [], $csvdelimiter);
        }
        \mod_videotrack\local\csv_export::write_row($fh, [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:clicks', 'mod_videotrack'),
            get_string('report:students', 'mod_videotrack'),
            get_string('report:csvcol_firsttimestamp', 'mod_videotrack'),
            get_string('report:csvcol_lasttimestamp', 'mod_videotrack'),
        ], $csvdelimiter);
        foreach ($clusters as $cluster) {
            \mod_videotrack\local\csv_export::write_row($fh, [
                videotrack_format_video_timestamp((float)$cluster['timestamp'], $videoduration),
                $cluster['reactionlabel'],
                $cluster['count'],
                $cluster['students'],
                videotrack_format_video_timestamp((float)$cluster['first'], $videoduration),
                videotrack_format_video_timestamp((float)$cluster['last'], $videoduration),
            ], $csvdelimiter);
        }
    } else {
        $csvheads = array_merge(
            \mod_videotrack\local\csv_export::identity_headers($csvfields),
            [
                get_string('report:csvcol_uniquecoveredtime', 'mod_videotrack'),
                get_string('report:completionpercent', 'mod_videotrack'),
                get_string('report:lastposition', 'mod_videotrack'),
                get_string('report:iscompleted', 'mod_videotrack'),
            ]
        );
        if (!empty($videotrack->bookmarksenabled)) {
            $csvheads[] = get_string('report:bookmarks_count', 'mod_videotrack');
        }
        if (!empty($videotrack->integrityindicatorsenabled)) {
            $csvheads[] = get_string('report:integrity_count', 'mod_videotrack');
        }
        if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
            $csvheads[] = get_string('report:acknowledgement_status', 'mod_videotrack');
            $csvheads[] = get_string('report:acknowledgement_date', 'mod_videotrack');
            $csvheads[] = get_string('report:acknowledgement_viewedseconds', 'mod_videotrack');
            $csvheads[] = get_string('report:acknowledgement_viewedpercent', 'mod_videotrack');
        }
        if ($hasgrade && $cangrade) {
            $csvheads[] = get_string('report:grade', 'mod_videotrack');
        }
        \mod_videotrack\local\csv_export::write_row($fh, $csvheads, $csvdelimiter);
        $rs = $DB->get_recordset_select(
            'videotrack_state',
            $stateconditions,
            $stateparamsnamed,
            'completionpercent DESC'
        );
        foreach ($rs as $state) {
            $userid = (int)$state->userid;
            $user = $csvusermap[$userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = \mod_videotrack\local\csv_export::identity_values(
                $csvfields,
                $course,
                $videotrack,
                $user,
                videotrack_report_user_label($userid, $csvusermap, false),
                (int)$cm->id
            );
            $row = array_merge($row, [
                videotrack_format_video_timestamp((float)$state->uniquecoveredseconds, $videoduration),
                format_float((float)$state->completionpercent, 2),
                videotrack_format_video_timestamp((float)$state->lastposition, $videoduration),
                get_string($state->iscompleted ? 'yes' : 'no', 'mod_videotrack'),
            ]);
            if (!empty($videotrack->bookmarksenabled)) {
                $row[] = (int)($bookmarkcounts[$userid] ?? 0);
            }
            if (!empty($videotrack->integrityindicatorsenabled)) {
                $row[] = (int)($integritycounts[$userid] ?? 0);
            }
            if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
                $ackrecord = $acknowledgementrecords[$userid] ?? null;
                $row[] = get_string($ackrecord ? 'yes' : 'no', 'mod_videotrack');
                $row[] = $ackrecord
                    ? userdate((int)$ackrecord->timeconfirmed, get_string('strftimedatetimeshort', 'langconfig'))
                    : '';
                $hasackprogress = $ackrecord
                    && $ackrecord->viewedseconds !== null
                    && $ackrecord->viewedpercent !== null;
                $row[] = $hasackprogress
                    ? format_float((float)$ackrecord->viewedseconds, 3)
                    : ($ackrecord ? get_string('report:acknowledgement_progressunavailable', 'mod_videotrack') : '');
                $row[] = $hasackprogress ? format_float((float)$ackrecord->viewedpercent, 2) : '';
            }
            if ($hasgrade && $cangrade) {
                $row[] = $gradeinfo->items[0]->grades[$userid]->grade ?? '';
            }
            \mod_videotrack\local\csv_export::write_row($fh, $row, $csvdelimiter);
        }
        $rs->close();
    }
    fclose($fh);
    exit;
}

// Recalculate aggregate completion state for all tracked users or one selected user.
if ($action === 'recalculate') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_once(__DIR__ . '/lib.php');
    if ($recalculateuserid > 0 && !in_array($recalculateuserid, $alluserids, true)) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $updated = 0;
    $cminfo = cm_info::create($cm);
    if ($recalculateuserid > 0) {
        $updated = videotrack_recalculate_all_states($videotrack->id, $cminfo, $recalculateuserid);
    } else {
        foreach ($alluserids as $learnerid) {
            $updated += videotrack_recalculate_all_states($videotrack->id, $cminfo, (int)$learnerid);
        }
    }
    redirect(
        new moodle_url('/mod/videotrack/report.php', $baseparams),
        get_string('report:recalculated', 'mod_videotrack', $updated),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Reset one student's plugin-owned data for this activity.
if ($resetaction === 'resetstudent' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();
    $resetuserid = required_param('resetuserid', PARAM_INT);
    require_capability('mod/videotrack:viewreport', $context);
    if (
        $resetuserid <= 0
        || !\mod_videotrack\local\learner_scope::user_is_visible(
            $context,
            $cm,
            $course,
            (int)$USER->id,
            $resetuserid
        )
    ) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $resetcounts = [
        'segments' => $DB->count_records('videotrack_seg', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'states' => $DB->count_records('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'events' => $DB->count_records('videotrack_reactev', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'integrity' => $DB->count_records('videotrack_integrity', ['videotrackid' => $videotrack->id, 'userid' => $resetuserid]),
        'acknowledgements' => $DB->count_records('videotrack_acknowledge', [
            'videotrackid' => $videotrack->id,
            'userid' => $resetuserid,
        ]),
    ];
    require_once(__DIR__ . '/lib.php');
    $transaction = $DB->start_delegated_transaction();
    videotrack_delete_user_progress($videotrack, $resetuserid);
    $transaction->allow_commit();
    \mod_videotrack\event\student_progress_reset::create([
        'objectid' => $videotrack->id,
        'context' => $context,
        'relateduserid' => $resetuserid,
        'other' => $resetcounts,
    ])->trigger();
    // Also reset the gradebook grade when the activity uses grading.
    if (!empty($videotrack->grade)) {
        require_once($CFG->libdir . '/gradelib.php');
        grade_update(
            'mod/videotrack',
            $course->id,
            'mod',
            'videotrack',
            $videotrack->id,
            0,
            null,
            ['reset' => true, 'userid' => $resetuserid]
        );
    }
    // Update Moodle completion to INCOMPLETE for this student.
    $cminfo    = cm_info::create($cm);
    $completion = new completion_info($course);
    $completion->update_state($cminfo, COMPLETION_INCOMPLETE, $resetuserid);
    redirect(
        new moodle_url('/mod/videotrack/report.php', $baseparams),
        get_string('report:studentreset', 'mod_videotrack'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$PAGE->set_url('/mod/videotrack/report.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_title(format_string($videotrack->name, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => context_course::instance($course->id)]));
// Plugin styles are in styles.css and are loaded automatically by Moodle.

// The savegrade block must run before $OUTPUT->header() to allow redirect responses.
if ($hasgrade && optional_param('savegrade', 0, PARAM_INT)) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new moodle_exception('invalidrequest', 'error');
    }
    require_sesskey();
    require_capability('mod/videotrack:grade', $context);
    require_once(__DIR__ . '/lib.php');
    require_once($CFG->libdir . '/gradelib.php');
    $gradeuserid = required_param('grade_userid', PARAM_INT);
    if (
        !\mod_videotrack\local\learner_scope::user_is_visible(
            $context,
            $cm,
            $course,
            (int)$USER->id,
            $gradeuserid
        )
    ) {
        throw new moodle_exception('invaliduserid', 'error');
    }
    $gradevalue = optional_param('grade_value', '', PARAM_NOTAGS);
    if ($gradevalue !== '') {
        if (!is_numeric($gradevalue)) {
            throw new moodle_exception('invaliddata', 'error');
        }
        if ($videotrack->grade > 0) {
            $val = (float)$gradevalue;
            if ($val < 0 || $val > (float)$videotrack->grade) {
                throw new moodle_exception('invaliddata', 'error');
            }
        } else {
            $scaleid = -(int)$videotrack->grade;
            $scale   = grade_scale::fetch(['id' => $scaleid]);
            $items   = $scale ? $scale->load_items() : [];
            $val     = (int)$gradevalue;
            if ($val < 1 || $val > count($items)) {
                throw new moodle_exception('invaliddata', 'error');
            }
        }
        videotrack_set_user_grade($videotrack, $gradeuserid, (float)$val);
    } else {
        videotrack_set_user_grade($videotrack, $gradeuserid, -1);
    }
    redirect(
        $PAGE->url,
        get_string('report:gradesaved', 'mod_videotrack'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Initialise the AMD module that handles the student reset confirmation.
$PAGE->requires->js_call_amd('mod_videotrack/report', 'init', [[
    'confirmreset' => get_string('report:resetstudent_confirm', 'mod_videotrack'),
    'confirmrecalculate' => get_string('report:recalculate_confirm', 'mod_videotrack'),
    'labels' => [
        'confirm' => get_string('confirm', 'moodle'),
        'yes' => get_string('yes', 'moodle'),
        'cancel' => get_string('cancel', 'moodle'),
    ],
]]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reportteacher', 'mod_videotrack'));

$tabs = videotrack_report_tabs($cm->id, $canviewfullreport, $baseparams);
echo $OUTPUT->tabtree($tabs, $mode);

if ($mode === 'recalculate') {
    echo $OUTPUT->heading(get_string('report:recalculate_heading', 'mod_videotrack'), 3);
    echo html_writer::div(
        get_string('report:recalculate_description', 'mod_videotrack'),
        'alert alert-info'
    );
    $recalculateoptions = $useroptions;
    $recalculateoptions[0] = get_string('report:recalculate_all', 'mod_videotrack');
    $recalculateform = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-recalculate-form',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'id', 'value' => $cm->id,
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'mode', 'value' => 'recalculate',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'action', 'value' => 'recalculate',
    ]);
    $recalculateform .= html_writer::empty_tag('input', [
        'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey(),
    ]);
    $recalculateform .= html_writer::start_div('form-group');
    $recalculateform .= html_writer::label(
        get_string('report:recalculate_users', 'mod_videotrack'),
        'id_recalculateuserid',
        false,
        ['class' => 'd-block']
    );
    $recalculateform .= html_writer::select(
        $recalculateoptions,
        'recalculateuserid',
        $recalculateuserid,
        false,
        ['id' => 'id_recalculateuserid', 'class' => 'custom-select']
    );
    $recalculateform .= html_writer::end_div();
    $recalculateform .= html_writer::tag(
        'button',
        get_string('report:recalculate_submit', 'mod_videotrack'),
        ['type' => 'submit', 'class' => 'btn btn-primary']
    );
    $recalculateform .= html_writer::end_tag('form');
    echo $recalculateform;
    echo $OUTPUT->footer();
    exit;
}

if ($mode === 'export') {
    echo $OUTPUT->heading(get_string('report:csvexport_heading', 'mod_videotrack'), 3);
    echo html_writer::div(
        get_string('report:csvexport_description', 'mod_videotrack'),
        'alert alert-info'
    );

    $exportform = html_writer::start_tag('form', [
        'method' => 'post',
        'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
        'class' => 'videotrack-custom-csv-export',
    ]);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => 'export']);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'export', 'value' => 'custom_csv']);
    $exportform .= html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);

    $exportuseroptions = $useroptions;
    $exportuseroptions[0] = get_string('report:csvexport_scope_all', 'mod_videotrack');
    $exportform .= html_writer::start_div('form-group');
    $exportform .= html_writer::label(
        get_string('report:csvexport_users', 'mod_videotrack'),
        'id_csvuserid',
        false,
        ['class' => 'd-block']
    );
    $exportform .= html_writer::select(
        $exportuseroptions,
        'csvuserid',
        $csvuserid,
        false,
        ['id' => 'id_csvuserid', 'class' => 'custom-select']
    );
    $exportform .= html_writer::end_div();

    $exportform .= html_writer::start_tag('fieldset', ['class' => 'form-group']);
    $exportform .= html_writer::tag('legend', get_string('report:csvexport_content', 'mod_videotrack'), [
        'class' => 'col-form-label pt-0',
    ]);
    $exportform .= html_writer::start_div('form-check');
    $exportform .= html_writer::empty_tag('input', [
        'type' => 'checkbox', 'name' => 'csvincludereactions', 'value' => 1,
        'id' => 'id_csvincludereactions', 'class' => 'form-check-input',
        'checked' => 'checked',
    ]);
    $exportform .= html_writer::label(
        get_string('report:csvexport_reactions', 'mod_videotrack'),
        'id_csvincludereactions',
        false,
        ['class' => 'form-check-label']
    );
    $exportform .= html_writer::end_div();
    if (!empty($videotrack->studentnotesenabled)) {
        $exportform .= html_writer::start_div('form-check');
        $exportform .= html_writer::empty_tag('input', [
            'type' => 'hidden', 'name' => 'csvincludenotes', 'value' => 0,
        ]);
        $exportform .= html_writer::empty_tag('input', [
            'type' => 'checkbox', 'name' => 'csvincludenotes', 'value' => 1,
            'id' => 'id_csvincludenotes', 'class' => 'form-check-input',
            'checked' => 'checked',
        ]);
        $exportform .= html_writer::label(
            get_string('report:csvexport_notes', 'mod_videotrack'),
            'id_csvincludenotes',
            false,
            ['class' => 'form-check-label']
        );
        $exportform .= html_writer::end_div();
    }
    $exportform .= html_writer::end_tag('fieldset');

    $exportform .= html_writer::start_div('form-group');
    $exportform .= html_writer::label(
        get_string('report:csvexport_format', 'mod_videotrack'),
        'id_csvformat',
        false,
        ['class' => 'd-block']
    );
    $exportform .= html_writer::select([
        'detailed' => get_string('report:csvexport_detailed', 'mod_videotrack'),
        'summary' => get_string('report:csvexport_summary', 'mod_videotrack'),
        'overall' => get_string('report:csvexport_overall', 'mod_videotrack'),
    ], 'csvformat', $csvformat, false, ['id' => 'id_csvformat', 'class' => 'custom-select']);
    $exportform .= html_writer::div(
        get_string('report:csvexport_format_help', 'mod_videotrack', $window),
        'form-text text-muted'
    );
    $exportform .= html_writer::end_div();

    $exportform .= html_writer::div(
        get_string('report:csvexport_privacywarning', 'mod_videotrack'),
        'alert alert-warning',
        ['id' => 'videotrack-csv-export-warning']
    );
    $exportform .= html_writer::tag('button', get_string('report:csvexport_download', 'mod_videotrack'), [
        'type' => 'submit',
        'class' => 'btn btn-primary',
    ]);
    $exportform .= html_writer::end_tag('form');
    echo $exportform;
    echo $OUTPUT->footer();
    exit;
}

$filterurl = new moodle_url('/mod/videotrack/report.php');
echo html_writer::start_div('videotrack-report-filters mb-3');
echo html_writer::start_tag('form', ['method' => 'get', 'action' => $filterurl->out(false), 'class' => 'form-inline']);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'mode', 'value' => $mode]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sort', 'value' => $sort]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'aggregation', 'value' => $aggregation]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'window', 'value' => $window]);
$userfilter = html_writer::label(
    get_string('report:userid', 'mod_videotrack'),
    'id_userid',
    false,
    ['class' => 'mr-1']
) . html_writer::select(
    $useroptions,
    'userid',
    $useridfilter,
    false,
    ['id' => 'id_userid', 'class' => 'custom-select']
);
$reactionfilter = html_writer::label(
    get_string('report:reaction', 'mod_videotrack'),
    'id_reactionid',
    false,
    ['class' => 'mr-1']
) . html_writer::select(
    $reactionoptions,
    'reactionid',
    $reactionidfilter,
    false,
    ['id' => 'id_reactionid', 'class' => 'custom-select']
);
echo html_writer::div($userfilter, 'd-inline-flex align-items-center mr-3 mb-2');
echo html_writer::div($reactionfilter, 'd-inline-flex align-items-center mr-3 mb-2');
$reportduration = (float)$videotrack->durationseconds;
$showtimehours = $reportduration <= 0 || max(
    $reportduration,
    $timefrom ?? 0,
    $timeto ?? 0
) >= HOURSECS;
echo videotrack_report_duration_filter(
    'timefrom',
    get_string('report:timefrom', 'mod_videotrack'),
    $timefrom,
    $showtimehours
);
echo videotrack_report_duration_filter(
    'timeto',
    get_string('report:timeto', 'mod_videotrack'),
    $timeto,
    $showtimehours
);
echo html_writer::div(
    html_writer::label(
        get_string('report:notecreatedfrom', 'mod_videotrack'),
        'id_notecreatedfrom',
        false,
        ['class' => 'mr-1']
    ) . html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedfrom',
        'id' => 'id_notecreatedfrom', 'value' => s($notecreatedfrom),
        'class' => 'form-control', 'style' => 'width:10rem',
    ]),
    'd-inline-flex align-items-center mr-3 mb-2'
);
echo html_writer::div(
    html_writer::label(
        get_string('report:notecreatedto', 'mod_videotrack'),
        'id_notecreatedto',
        false,
        ['class' => 'mr-1']
    ) . html_writer::empty_tag('input', [
        'type' => 'date', 'name' => 'notecreatedto',
        'id' => 'id_notecreatedto', 'value' => s($notecreatedto),
        'class' => 'form-control', 'style' => 'width:10rem',
    ]),
    'd-inline-flex align-items-center mr-3 mb-2'
);
echo html_writer::empty_tag('input', [
    'type' => 'submit', 'class' => 'btn btn-secondary mr-2 mb-2', 'value' => get_string('filter'),
]);
$resetfilterurl = new moodle_url('/mod/videotrack/report.php', [
    'id' => $cm->id,
    'mode' => $mode,
    'sort' => $sort,
    'aggregation' => $aggregation,
    'window' => $window,
]);
echo html_writer::link(
    $resetfilterurl,
    get_string('report:resetfilters', 'mod_videotrack'),
    ['class' => 'btn btn-outline-secondary mb-2']
);
echo html_writer::end_tag('form');
echo html_writer::end_div();

if ($mode === 'student') {
    if (!$statecount) {
        echo $OUTPUT->notification(get_string('report:noattempts', 'mod_videotrack'), 'notifymessage');
    } else {
        // The $hasgrade and $gradeinfo variables were already loaded at the start of the file.
        $usergrades = [];

        $heads = [
            get_string('report:userid', 'mod_videotrack'),
            get_string('report:uniquecoveredseconds', 'mod_videotrack'),
            get_string('report:completionpercent', 'mod_videotrack'),
            get_string('report:lastposition', 'mod_videotrack'),
            get_string('report:iscompleted', 'mod_videotrack'),
        ];
        if (!empty($videotrack->bookmarksenabled)) {
            $heads[] = get_string('report:bookmarks_count', 'mod_videotrack');
        }
        if (!empty($videotrack->integrityindicatorsenabled)) {
            $heads[] = get_string('report:integrity_count', 'mod_videotrack');
        }
        if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
            $heads[] = get_string('report:acknowledgement_status', 'mod_videotrack');
            $heads[] = get_string('report:acknowledgement_date', 'mod_videotrack');
            $heads[] = get_string('report:acknowledgement_viewedseconds', 'mod_videotrack');
            $heads[] = get_string('report:acknowledgement_viewedpercent', 'mod_videotrack');
        }
        if ($hasgrade && $cangrade) {
            $heads[] = get_string('report:grade', 'mod_videotrack');
        }
        if (has_capability('mod/videotrack:managereactions', $context)) {
            $heads[] = get_string('report:actions', 'mod_videotrack');
        }

        $table = new html_table();
        $table->caption = get_string('report:perstudent', 'mod_videotrack');
        $table->head = $heads;

        $staters = $getstaterecordset();
        foreach ($staters as $state) {
            $user = $usermap[(int)$state->userid] ?? null;
            if (!$user) {
                continue;
            }
            $row = [
                videotrack_report_user_label((int)$state->userid, $usermap, $canviewemail),
                videotrack_format_seconds((float)$state->uniquecoveredseconds),
                format_float((float)$state->completionpercent, 2),
                videotrack_format_seconds((float)$state->lastposition),
                $state->iscompleted ? get_string('yes', 'mod_videotrack') : get_string('no', 'mod_videotrack'),
            ];

            if (!empty($videotrack->bookmarksenabled)) {
                $row[] = (string)($bookmarkcounts[(int)$state->userid] ?? 0);
            }
            if (!empty($videotrack->integrityindicatorsenabled)) {
                $row[] = (string)($integritycounts[(int)$state->userid] ?? 0);
            }
            if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
                $ackrecord = $acknowledgementrecords[(int)$state->userid] ?? null;
                $row[] = $ackrecord
                    ? get_string('yes', 'mod_videotrack')
                    : get_string('acknowledgement:reportpending', 'mod_videotrack');
                $row[] = $ackrecord
                    ? userdate(
                        (int)$ackrecord->timeconfirmed,
                        get_string('strftimedatetimeshort', 'langconfig')
                    )
                    : '';
                $hasackprogress = $ackrecord
                    && $ackrecord->viewedseconds !== null
                    && $ackrecord->viewedpercent !== null;
                $row[] = $hasackprogress
                    ? videotrack_format_seconds((float)$ackrecord->viewedseconds)
                    : ($ackrecord ? get_string('report:acknowledgement_progressunavailable', 'mod_videotrack') : '');
                $row[] = $hasackprogress
                    ? format_float((float)$ackrecord->viewedpercent, 2) . '%'
                    : '';
            }

            if ($hasgrade && $cangrade) {
                // Read the current grade for this user.
                $studentname = videotrack_report_user_label((int)$state->userid, $usermap, false);
                $currentgrade = $gradeinfo->items[0]->grades[(int)$state->userid]->grade ?? '';
                $gradecell = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => $PAGE->url->out(false),
                    'class'  => 'videotrack-grade-form d-inline-flex align-items-center gap-1',
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'savegrade',
                    'value' => 1,
                ]);
                $gradecell .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'grade_userid',
                    'value' => (int)$state->userid,
                ]);

                if ($videotrack->grade > 0) {
                    // Numeric grading: number input constrained by the activity maximum.
                    $gradecell .= html_writer::empty_tag('input', [
                        'type'        => 'number',
                        'name'        => 'grade_value',
                        'class'       => 'form-control form-control-sm',
                        'style'       => 'width:80px',
                        'min'         => 0,
                        'max'         => (int)$videotrack->grade,
                        'step'        => 'any',
                        'value'       => ($currentgrade !== '' ? format_float((float)$currentgrade, 2) : ''),
                        'placeholder' => '-',
                        'aria-label' => get_string('report:gradeinputfor', 'mod_videotrack', $studentname),
                    ]);
                    $gradecell .= html_writer::tag(
                        'small',
                        '/ ' . (int)$videotrack->grade,
                        ['class' => 'text-muted ms-1']
                    );
                } else {
                    // Scale grading: select menu with Moodle one-based scale values.
                    $scaleid = -(int)$videotrack->grade;
                    $scale   = grade_scale::fetch(['id' => $scaleid]);
                    $items   = $scale ? $scale->load_items() : [];
                    $options = ['' => '-'];
                    foreach ($items as $k => $label) {
                        $options[$k + 1] = $label; // Moodle scale value: one-based.
                    }
                    $gradecell .= html_writer::select(
                        $options,
                        'grade_value',
                        ($currentgrade !== '' ? (int)$currentgrade : ''),
                        false,
                        [
                            'class' => 'form-control form-control-sm custom-select',
                            'aria-label' => get_string('report:gradeinputfor', 'mod_videotrack', $studentname),
                        ]
                    );
                }

                $gradecell .= html_writer::tag(
                    'button',
                    get_string('save'),
                    [
                        'type' => 'submit',
                        'class' => 'btn btn-sm btn-primary ms-1',
                        'aria-label' => get_string('report:savegradefor', 'mod_videotrack', $studentname),
                    ]
                );
                $gradecell .= html_writer::end_tag('form');

                // Visual pass-grade indicator when configured.
                if (!empty($videotrack->gradepass) && $currentgrade !== '') {
                    $passed = (float)$currentgrade >= (float)$videotrack->gradepass;
                    $passlabel = get_string($passed ? 'report:gradepassed' : 'report:gradefailed', 'mod_videotrack');
                    $gradecell .= html_writer::tag(
                        'span',
                        html_writer::span($passed ? '✓' : '✗', '', ['aria-hidden' => 'true']) .
                            html_writer::span($passlabel, 'sr-only'),
                        [
                            'class' => 'ms-1 ' . ($passed ? 'text-success' : 'text-danger'),
                            'title' => get_string(
                                'report:gradepass_hint',
                                'mod_videotrack',
                                format_float((float)$videotrack->gradepass, 2)
                            ),
                        ]
                    );
                }

                $row[] = $gradecell;
            }

            // Reset one student's progress (only for users with the manage capability).
            if (has_capability('mod/videotrack:managereactions', $context)) {
                $resetform = html_writer::start_tag('form', [
                    'method' => 'post',
                    'action' => (new moodle_url('/mod/videotrack/report.php', $baseparams))->out(false),
                    'class' => 'd-inline videotrack-reset-student-form',
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'sesskey',
                    'value' => sesskey(),
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'resetaction',
                    'value' => 'resetstudent',
                ]);
                $resetform .= html_writer::empty_tag('input', [
                    'type' => 'hidden',
                    'name' => 'resetuserid',
                    'value' => (int)$state->userid,
                ]);
                $resetform .= html_writer::tag('button', get_string('report:resetstudent', 'mod_videotrack'), [
                    'type' => 'submit',
                    'class' => 'btn btn-sm btn-outline-danger videotrack-reset-student',
                    'data-confirm' => get_string('report:resetstudent_confirm', 'mod_videotrack'),
                ]);
                $resetform .= html_writer::end_tag('form');
                $row[] = $resetform;
            }

            $table->data[] = $row;
        }
        $staters->close();
        echo html_writer::table($table);
    }
} else {
    if (!empty($videotrack->bookmarksenabled)) {
        echo videotrack_report_render_bookmark_summary(
            $reportbookmarksummary,
            videotrack_get_config_int('analyticsminusers', 5, 2, 50)
        );
    }
    if (!empty($videotrack->integrityindicatorsenabled)) {
        echo videotrack_report_render_integrity_summary(
            $reportintegritysummary,
            videotrack_get_config_int('analyticsminusers', 5, 2, 50)
        );
    }
    if (\mod_videotrack\local\acknowledgement::is_enabled($videotrack)) {
        echo html_writer::div(
            get_string('report:acknowledgement_summary', 'mod_videotrack', count($acknowledgementrecords)),
            'alert alert-light'
        );
    }
    if (!$eventcount) {
        echo $OUTPUT->notification(get_string('report:noreactions', 'mod_videotrack'), 'notifymessage');
    } else {
        $eventrs = $geteventrecordset();
        $clusters = $clusterize($eventrs, $window, $aggregation);
        $eventrs->close();
        if ($clusterlimitreached) {
            echo $OUTPUT->notification(get_string('report:clusterlimitreached', 'mod_videotrack'), 'notifymessage');
            echo $OUTPUT->notification(get_string('report:clusterlimitreached_help', 'mod_videotrack'), 'notifymessage');
            if (!$hasvideotimefilter) {
                echo $OUTPUT->notification(get_string('report:clusterlimitrequiresfilters', 'mod_videotrack'), 'warning');
                echo $OUTPUT->notification(get_string('report:clusterdisplayblocked', 'mod_videotrack'), 'warning');
                $clusters = [];
            }
        }

        if ($clusters) {
            $topclusters = $clusters;
            usort($topclusters, static fn($a, $b) => $b['count'] <=> $a['count']);
            $topclusters = array_slice($topclusters, 0, 5);
            $items = [];
            foreach ($topclusters as $topcluster) {
                $items[] = get_string('report:topclusteritem', 'mod_videotrack', [
                    'time' => videotrack_format_seconds($topcluster['timestamp']),
                    'reaction' => s($topcluster['reactionlabel']),
                    'clicks' => (int)$topcluster['count'],
                ]);
            }
            if ($items) {
                echo html_writer::tag('p', get_string('report:topclusterssummary', 'mod_videotrack'), [
                    'class' => 'font-weight-bold mb-1',
                ]);
                echo html_writer::alist($items, ['class' => 'small']);
            }
        }

        // Heatmap SVG: show reaction distribution on the video timeline.
        $duration = (float)($DB->get_field('videotrack', 'durationseconds', ['id' => $videotrack->id]) ?: 0);
        if ($duration > 0 && $clusters) {
            $svgw = 800;
            $svgh = 48;
            $barh = 32;
            $pady = 8;
            $maxcount = max(array_column($clusters, 'count'));
            // Collect per-reaction colours using an accessible rotating palette.
            $palette = [
                '#4e79a7',
                '#f28e2b',
                '#e15759',
                '#76b7b2',
                '#59a14f',
                '#edc948',
                '#b07aa1',
                '#ff9da7',
            ];
            $reactioncolors = [];
            $ci = 0;
            foreach ($reactions as $r) {
                $reactioncolors[(int)$r->id] = $palette[$ci % count($palette)];
                $ci++;
            }
            $svgtitle = s(get_string('report:heatmap_desc', 'mod_videotrack'));
            $svgattributes = [
                'viewBox' => "0 0 {$svgw} {$svgh}",
                'xmlns' => 'http://www.w3.org/2000/svg',
                'role' => 'img',
                'aria-label' => $svgtitle,
                'aria-describedby' => 'videotrack-heatmap-table',
                'class' => 'videotrack-heatmap-svg',
            ];
            $svg = html_writer::start_tag('svg', $svgattributes);
            $svg .= html_writer::tag('title', $svgtitle);
            $patternpaths = [
                html_writer::empty_tag('path', [
                    'd' => 'M0 6 L6 0',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M0 0 L6 6',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M3 0 L3 6',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
                html_writer::empty_tag('path', [
                    'd' => 'M0 3 L6 3',
                    'stroke' => '#000',
                    'stroke-width' => '1',
                    'opacity' => '0.25',
                ]),
            ];
            $svg .= html_writer::start_tag('defs');
            $patternmap = [];
            $pi = 0;
            foreach ($reactions as $r) {
                $reactionid = (int)$r->id;
                $patternid = 'videotrack-hatch-' . $reactionid;
                $patternmap[$reactionid] = $patternid;
                $svg .= html_writer::tag('pattern', $patternpaths[$pi % count($patternpaths)], [
                    'id' => $patternid,
                    'width' => '6',
                    'height' => '6',
                    'patternUnits' => 'userSpaceOnUse',
                ]);
                $pi++;
            }
            $svg .= html_writer::end_tag('defs');
            // Timeline background bar.
            $svg .= html_writer::empty_tag('rect', [
                'x' => '0',
                'y' => $pady,
                'width' => $svgw,
                'height' => $barh,
                'rx' => '3',
                'fill' => '#e9ecef',
            ]);
            $labelled = 0;
            $labelthreshold = max(1, (int)ceil($maxcount * 0.75));
            foreach ($clusters as $cluster) {
                $x = (int)min($svgw, max(0, (($cluster['timestamp'] / $duration) * $svgw)));
                $h = max(2, (int)(($cluster['count'] / $maxcount) * $barh));
                $y = $pady + $barh - $h;
                $col = $reactioncolors[(int)$cluster['reactionid']] ?? '#4e79a7';
                $tip = s($cluster['reactionlabel']) . ': ' . $cluster['count'] . ' @ ' .
                    videotrack_format_seconds($cluster['timestamp']);
                $rectx = max(0, min($svgw - 6, $x - 3));
                $svg .= html_writer::tag('rect', html_writer::tag('title', $tip), [
                    'x' => $rectx,
                    'y' => $y,
                    'width' => '6',
                    'height' => $h,
                    'rx' => '2',
                    'fill' => $col,
                    'opacity' => '0.85',
                ]);
                $patternid = $patternmap[(int)$cluster['reactionid']] ?? '';
                if ($patternid !== '') {
                    $svg .= html_writer::empty_tag('rect', [
                        'x' => $rectx,
                        'y' => $y,
                        'width' => '6',
                        'height' => $h,
                        'fill' => "url(#{$patternid})",
                        'opacity' => '0.35',
                    ]);
                }
                if ($cluster['count'] >= $labelthreshold && $labelled < 8) {
                    $textx = min($svgw - 24, max(2, $x + 4));
                    $texty = max(8, $y - 2);
                    $svg .= html_writer::tag('text', (string)(int)$cluster['count'], [
                        'x' => $textx,
                        'y' => $texty,
                        'font-size' => '10',
                        'fill' => '#212529',
                    ]);
                    $labelled++;
                }
            }
            $svg .= html_writer::end_tag('svg');
            echo html_writer::tag('p', get_string('report:heatmap_supplementary', 'mod_videotrack'), [
                'class' => 'small mb-1',
            ]);
            echo html_writer::link('#videotrack-heatmap-table', get_string('report:skiptoheatmaptable', 'mod_videotrack'), [
                'class' => 'sr-only sr-only-focusable d-block mb-2',
            ]);
            echo html_writer::tag('p', get_string('report:heatmap_desc', 'mod_videotrack') . ' ' .
                get_string('report:heatmap_textsummary', 'mod_videotrack', [
                    'clusters' => count($clusters),
                    'max' => $maxcount,
                ]), ['class' => 'text-muted small mb-1']);
            $legenditems = [];
            $legendindex = 0;
            foreach ($reactions as $r) {
                $color = $reactioncolors[(int)$r->id] ?? '#4e79a7';
                $patternstyle = $patternstyles[$legendindex % count($patternstyles)];
                $swatchstyle = implode(';', [
                    'background-color:' . $color,
                    'background-image:' . $patternstyle,
                ]);
                $swatch = html_writer::span('', 'videotrack-heatmap-swatch', [
                    'aria-hidden' => 'true',
                    'style' => $swatchstyle,
                ]);
                $legenditems[] = html_writer::tag('li', $swatch . s($r->label), ['class' => 'list-inline-item mr-3']);
                $legendindex++;
            }
            if ($legenditems) {
                echo html_writer::tag('ul', implode('', $legenditems), [
                    'class' => 'list-inline small mb-2',
                    'aria-label' => get_string('report:heatmap_legend', 'mod_videotrack'),
                ]);
            }
            echo $svg;
        }

        $table = new html_table();
        $table->attributes['id'] = 'videotrack-heatmap-table';
        $table->caption = get_string('report:heatmap_textsummary', 'mod_videotrack', [
            'clusters' => count($clusters),
            'max' => $clusters ? max(array_column($clusters, 'count')) : 0,
        ]);
        $table->head = [
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:clicks', 'mod_videotrack'),
            get_string('report:students', 'mod_videotrack'),
            get_string('report:replay', 'mod_videotrack'),
        ];
        $replayoffset = $window;
        $replaynotice = get_string('report:replayoffsetnotice', 'mod_videotrack', $replayoffset);
        echo $OUTPUT->notification($replaynotice, 'info', false);
        foreach ($clusters as $cluster) {
            $reactionhtml = $cluster['reaction']
                ? videotrack_render_reaction_icon($cluster['reaction'], $context, true)
                : s($cluster['reactionlabel']);
            $replaytimestamp = max(0, (int)round($cluster['timestamp']));
            $start = max(0, $replaytimestamp - $replayoffset);
            $end = $replaytimestamp + $replayoffset;
            $replayurl = new moodle_url('/mod/videotrack/view.php', [
                'id' => $cm->id,
                'replaystart' => $start,
                'replayend' => $end,
            ]);
            $table->data[] = [
                videotrack_format_seconds($replaytimestamp),
                html_writer::span($reactionhtml, 'videotrack-report-icon'),
                (int)$cluster['count'],
                (int)$cluster['students'],
                html_writer::span(
                    html_writer::link($replayurl, get_string('report:replay', 'mod_videotrack')),
                    'videotrack-replay-inline'
                ),
            ];
        }
        echo html_writer::table($table);
    }
}

// Student notes section: per-student mode only, and only when notes are enabled.
if ($mode === 'student' && !empty($videotrack->studentnotesenabled)) {
    $notewhere = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}" .
        ($useridfilter > 0 ? ' AND userid = :uid' : '');
    $noteparams = ['vtid' => $videotrack->id] + $learnerparams;
    if ($useridfilter > 0) {
        $noteparams['uid'] = $useridfilter;
    }
    if ($notecreatedfromts) {
        $notewhere .= ' AND timecreated >= :notecreatedfrom';
        $noteparams['notecreatedfrom'] = $notecreatedfromts;
    }
    if ($notecreatedtots) {
        $notewhere .= ' AND timecreated <= :notecreatedto';
        $noteparams['notecreatedto'] = $notecreatedtots;
    }
    $notelimit = videotrack_get_config_int('reportnotespagesize', 100, 20, 500);
    $notecount = $DB->count_records_select('videotrack_reactev', $notewhere, $noteparams);
    if ($notecount > 0) {
        $notepage = min($notepage, (int)floor(($notecount - 1) / $notelimit));
    }
    $notes = $DB->get_records_select(
        'videotrack_reactev',
        $notewhere,
        $noteparams,
        'userid ASC, videotime ASC, id ASC',
        'id, userid, videotime, notetext, timecreated',
        $notepage * $notelimit,
        $notelimit
    );

    echo html_writer::start_div('videotrack-notes-report mt-4');
    echo $OUTPUT->heading(get_string('report:notes_title', 'mod_videotrack'), 3);

    $ntable = new html_table();
    $ntable->head = [
        get_string('report:userid', 'mod_videotrack'),
        get_string('report:timestamp', 'mod_videotrack'),
        get_string('studentnote_label', 'mod_videotrack'),
        get_string('report:notedate', 'mod_videotrack'),
    ];
    $ntable->attributes['class'] = 'generaltable';
    $ntable->caption = get_string('report:notes_title', 'mod_videotrack');
    foreach ($notes as $note) {
        $username = videotrack_report_user_label((int)$note->userid, $usermap, $canviewemail);
        $ntable->data[] = [
            $username,
            videotrack_format_seconds((float)$note->videotime),
            s($note->notetext),
            userdate((int)$note->timecreated, get_string('strftimedatetimeshort', 'langconfig')),
        ];
    }

    if ($notecount === 0) {
        echo $OUTPUT->notification(get_string('report:nonotes', 'mod_videotrack'), 'notifymessage');
    } else {
        $pagingurl = new moodle_url('/mod/videotrack/report.php', $baseparams + ['mode' => 'student']);
        echo $OUTPUT->paging_bar($notecount, $notepage, $notelimit, $pagingurl, 'notepage');
        echo html_writer::table($ntable);
        echo $OUTPUT->paging_bar($notecount, $notepage, $notelimit, $pagingurl, 'notepage');

        echo html_writer::link(
            new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'export']),
            get_string('report:csvexport_tab', 'mod_videotrack'),
            ['class' => 'btn btn-sm btn-outline-secondary mt-2']
        );
    }
    echo html_writer::end_div();
}

echo $OUTPUT->footer();
