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

namespace mod_videotrack\local;

use html_table;
use html_writer;
use moodle_url;

/**
 * Rendering helpers for the teacher Analytics report.
 *
 * Keeping presentation-only code out of report.php makes the request controller
 * easier to audit without changing Analytics queries or privacy semantics.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_renderer {
    /**
     * Formats a timeline interval for analytics reports.
     *
     * @param float $start Interval start.
     * @param float $end Interval end.
     * @param float $duration Video duration.
     * @return string Formatted interval.
     */
    public static function analytics_interval(float $start, float $end, float $duration): string {
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
    public static function analytics_heatmap(
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
            $interval = self::analytics_interval($bin['start'], $bin['end'], $duration);
            if (!empty($bin['suppressed'])) {
                $tooltip = get_string('report:analytics_bin_suppressed_title', 'mod_videotrack', [
                    'interval' => $interval,
                    'minusers' => $minusers,
                ]);
                $class = 'videotrack-analytics-bin videotrack-analytics-bin-suppressed';
                $attributes = ['fill' => 'url(#videotrack-analytics-suppressed-pattern)'];
            } else {
                $viewers = (int)($bin['viewers'] ?? 0);
                if (!empty($bin['retentionsuppressed'])) {
                    $tooltip = get_string('report:analytics_bin_title_privacy', 'mod_videotrack', [
                        'interval' => $interval,
                        'viewers' => $viewers,
                    ]);
                } else {
                    $tooltip = get_string('report:analytics_bin_title', 'mod_videotrack', [
                        'interval' => $interval,
                        'viewers' => $viewers,
                        'retention' => format_float((float)($bin['retention'] ?? 0), 1),
                    ]);
                }
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
    public static function analytics_methodology(
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
    public static function privacy_alert(
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
    public static function heatmap_legend(bool $showreactions, bool $hassuppressed): string {
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
    public static function analytics_download(array $formats, array $params): string {
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
    public static function reaction_clusters(array $clusters, float $duration): string {
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
    public static function reaction_summary(array $summary): string {
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
    public static function bookmark_summary(array $summary, int $minusers): string {
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
    public static function acknowledgement_summary(
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
    public static function integrity_summary(
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
    public static function analytics_retention(array $bins, float $duration): string {
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
        $hasvisibleretention = false;
        $hasprivacysuppression = false;
        foreach ($bins as $bin) {
            if (empty($bin['suppressed']) && $bin['retention'] !== null) {
                $hasvisibleretention = true;
            }
            if (!empty($bin['suppressed']) || !empty($bin['retentionsuppressed'])) {
                $hasprivacysuppression = true;
            }
        }
        $retentionprivacyhidden = !$hasvisibleretention && $hasprivacysuppression;
        $privacyhiddenmessage = $retentionprivacyhidden
            ? get_string('report:analytics_retention_privacy_hidden', 'mod_videotrack')
            : '';
        if ($retentionprivacyhidden) {
            $description .= ' ' . $privacyhiddenmessage;
        }

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

        if ($retentionprivacyhidden) {
            $svg .= html_writer::tag('text', s($privacyhiddenmessage), [
                'x' => format_float($left + ($plotwidth / 2), 3, false, true),
                'y' => format_float($top + ($plotheight / 2), 3, false, true),
                'text-anchor' => 'middle',
                'class' => 'videotrack-analytics-privacy-label',
            ]);
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
                    'interval' => self::analytics_interval($bin['start'], $bin['end'], $duration),
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
}
