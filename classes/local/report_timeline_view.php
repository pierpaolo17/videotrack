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

/**
 * Timeline charts, legends and reaction clusters for teacher Analytics.
 *
 * Presentation-only services keep the request controller auditable without
 * changing Analytics queries, privacy thresholds or rendered contracts.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_timeline_view {
    /** Heatmap canvas width. */
    private const HEATMAP_WIDTH = 1000;

    /** Heatmap canvas height. */
    private const HEATMAP_HEIGHT = 92;

    /** Vertical origin of the heatmap bar. */
    private const HEATMAP_BAR_START = 24;

    /** Heatmap bar height. */
    private const HEATMAP_BAR_HEIGHT = 42;

    /** Retention canvas width. */
    private const RETENTION_WIDTH = 1000;

    /** Retention canvas height. */
    private const RETENTION_HEIGHT = 260;

    /** Retention plot left offset. */
    private const RETENTION_LEFT = 52;

    /** Retention plot right offset. */
    private const RETENTION_RIGHT = 12;

    /** Retention plot top offset. */
    private const RETENTION_TOP = 20;

    /** Retention plot bottom offset. */
    private const RETENTION_BOTTOM = 34;

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
        $visiblecounts = array_filter(array_column($bins, 'viewers'), static fn($value): bool => $value !== null);
        $maxviewers = $visiblecounts ? (float)max($visiblecounts) : 0.0;
        $title = get_string('report:analytics_heatmap_title', 'mod_videotrack');
        $description = get_string('report:analytics_heatmap_desc', 'mod_videotrack');

        $svg = html_writer::start_tag('svg', [
            'viewBox' => '0 0 ' . self::HEATMAP_WIDTH . ' ' . self::HEATMAP_HEIGHT,
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
            'y' => self::HEATMAP_BAR_START,
            'width' => self::HEATMAP_WIDTH,
            'height' => self::HEATMAP_BAR_HEIGHT,
            'class' => 'videotrack-analytics-background',
        ]);

        foreach ($bins as $bin) {
            $svg .= self::heatmap_bin_markup($bin, $duration, $maxviewers, $minusers);
        }

        foreach ($reactionclusters as $cluster) {
            $svg .= self::heatmap_reaction_marker_markup($cluster, $duration);
        }

        $svg .= html_writer::tag('text', '0', [
            'x' => 0,
            'y' => 84,
            'class' => 'videotrack-analytics-axis-label',
        ]);
        $svg .= html_writer::tag('text', s(videotrack_format_video_timestamp($duration, $duration)), [
            'x' => self::HEATMAP_WIDTH,
            'y' => 84,
            'text-anchor' => 'end',
            'class' => 'videotrack-analytics-axis-label',
        ]);
        $svg .= html_writer::end_tag('svg');
        return $svg;
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
     * Renders the retention line chart.
     *
     * @param array $bins Privacy-safe analytics bins.
     * @param float $duration Video duration.
     * @return string SVG markup.
     */
    public static function analytics_retention(array $bins, float $duration): string {
        $plotwidth = self::RETENTION_WIDTH - self::RETENTION_LEFT - self::RETENTION_RIGHT;
        $plotheight = self::RETENTION_HEIGHT - self::RETENTION_TOP - self::RETENTION_BOTTOM;
        $title = get_string('report:analytics_retention_title', 'mod_videotrack');
        $description = get_string('report:analytics_retention_desc', 'mod_videotrack');
        [$retentionprivacyhidden, $privacyhiddenmessage] = self::retention_privacy_state($bins);
        if ($retentionprivacyhidden) {
            $description .= ' ' . $privacyhiddenmessage;
        }

        $svg = html_writer::start_tag('svg', [
            'viewBox' => '0 0 ' . self::RETENTION_WIDTH . ' ' . self::RETENTION_HEIGHT,
            'xmlns' => 'http://www.w3.org/2000/svg',
            'role' => 'img',
            'aria-labelledby' => 'videotrack-analytics-retention-title videotrack-analytics-retention-desc',
            'aria-describedby' => 'videotrack-analytics-table',
            'class' => 'videotrack-analytics-svg videotrack-analytics-retention',
        ]);
        $svg .= html_writer::tag('title', s($title), ['id' => 'videotrack-analytics-retention-title']);
        $svg .= html_writer::tag('desc', s($description), ['id' => 'videotrack-analytics-retention-desc']);
        $svg .= self::retention_grid_markup($plotwidth, $plotheight);

        if ($retentionprivacyhidden) {
            $svg .= html_writer::tag('text', s($privacyhiddenmessage), [
                'x' => format_float(self::RETENTION_LEFT + ($plotwidth / 2), 3, false, true),
                'y' => format_float(self::RETENTION_TOP + ($plotheight / 2), 3, false, true),
                'text-anchor' => 'middle',
                'class' => 'videotrack-analytics-privacy-label',
            ]);
        }
        $paths = self::retention_paths($bins, $duration, $plotwidth, $plotheight);
        $svg .= self::retention_paths_markup($paths, $duration);

        $svg .= html_writer::tag('text', '0', [
            'x' => self::RETENTION_LEFT,
            'y' => self::RETENTION_HEIGHT - 8,
            'class' => 'videotrack-analytics-axis-label',
        ]);
        $svg .= html_writer::tag('text', s(videotrack_format_video_timestamp($duration, $duration)), [
            'x' => self::RETENTION_LEFT + $plotwidth,
            'y' => self::RETENTION_HEIGHT - 8,
            'text-anchor' => 'end',
            'class' => 'videotrack-analytics-axis-label',
        ]);
        $svg .= html_writer::end_tag('svg');
        return $svg;
    }

    /**
     * Renders one heatmap interval.
     *
     * @param array $bin Privacy-safe analytics bin.
     * @param float $duration Video duration.
     * @param float $maxviewers Maximum visible viewer count.
     * @param int $minusers Privacy threshold.
     * @return string SVG rectangle markup.
     */
    private static function heatmap_bin_markup(
        array $bin,
        float $duration,
        float $maxviewers,
        int $minusers
    ): string {
        $x = $duration > 0 ? ($bin['start'] / $duration) * self::HEATMAP_WIDTH : 0;
        $binwidth = $duration > 0
            ? (($bin['end'] - $bin['start']) / $duration) * self::HEATMAP_WIDTH
            : 0;
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
            $tooltip = !empty($bin['retentionsuppressed'])
                ? get_string('report:analytics_bin_title_privacy', 'mod_videotrack', [
                    'interval' => $interval,
                    'viewers' => $viewers,
                ])
                : get_string('report:analytics_bin_title', 'mod_videotrack', [
                    'interval' => $interval,
                    'viewers' => $viewers,
                    'retention' => format_float((float)($bin['retention'] ?? 0), 1),
                ]);
            $class = 'videotrack-analytics-bin';
            $opacity = $maxviewers > 0 ? max(0.08, $viewers / $maxviewers) : 0.08;
            $attributes = ['opacity' => format_float($opacity, 3, false, true)];
        }

        return html_writer::tag('rect', html_writer::tag('title', s($tooltip)), array_merge($attributes, [
            'x' => format_float($x, 3, false, true),
            'y' => self::HEATMAP_BAR_START,
            'width' => max(0.5, (float)format_float($binwidth, 3, false, true)),
            'height' => self::HEATMAP_BAR_HEIGHT,
            'class' => $class,
        ]));
    }

    /**
     * Renders one reaction marker over the heatmap.
     *
     * @param array $cluster Privacy-safe reaction cluster.
     * @param float $duration Video duration.
     * @return string SVG marker markup.
     */
    private static function heatmap_reaction_marker_markup(array $cluster, float $duration): string {
        $x = $duration > 0 ? ($cluster['timestamp'] / $duration) * self::HEATMAP_WIDTH : 0;
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
            'y2' => self::HEATMAP_BAR_START + self::HEATMAP_BAR_HEIGHT + 7,
            'class' => 'videotrack-analytics-reaction-line',
        ]);
        $marker .= html_writer::empty_tag('circle', [
            'cx' => format_float($x, 3, false, true),
            'cy' => 12,
            'r' => 5,
            'class' => 'videotrack-analytics-reaction-marker',
        ]);
        return html_writer::tag('g', $marker, ['class' => 'videotrack-analytics-reaction-cluster']);
    }

    /**
     * Determines whether privacy rules hide the complete retention series.
     *
     * @param array $bins Privacy-safe analytics bins.
     * @return array{0: bool, 1: string} Hidden state and localised explanation.
     */
    private static function retention_privacy_state(array $bins): array {
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

        $privacyhidden = !$hasvisibleretention && $hasprivacysuppression;
        $message = $privacyhidden
            ? get_string('report:analytics_retention_privacy_hidden', 'mod_videotrack')
            : '';
        return [$privacyhidden, $message];
    }

    /**
     * Renders the retention percentage grid.
     *
     * @param int $plotwidth Plot width.
     * @param int $plotheight Plot height.
     * @return string SVG grid markup.
     */
    private static function retention_grid_markup(int $plotwidth, int $plotheight): string {
        $markup = '';
        foreach ([0, 25, 50, 75, 100] as $percentage) {
            $y = self::RETENTION_TOP + $plotheight - (($percentage / 100) * $plotheight);
            $markup .= html_writer::empty_tag('line', [
                'x1' => self::RETENTION_LEFT,
                'x2' => self::RETENTION_LEFT + $plotwidth,
                'y1' => format_float($y, 3, false, true),
                'y2' => format_float($y, 3, false, true),
                'class' => 'videotrack-analytics-gridline',
            ]);
            $markup .= html_writer::tag('text', $percentage . '%', [
                'x' => self::RETENTION_LEFT - 8,
                'y' => format_float($y + 4, 3, false, true),
                'text-anchor' => 'end',
                'class' => 'videotrack-analytics-axis-label',
            ]);
        }
        return $markup;
    }

    /**
     * Groups visible retention points into contiguous paths.
     *
     * @param array $bins Privacy-safe analytics bins.
     * @param float $duration Video duration.
     * @param int $plotwidth Plot width.
     * @param int $plotheight Plot height.
     * @return array Contiguous paths of coordinates and source bins.
     */
    private static function retention_paths(
        array $bins,
        float $duration,
        int $plotwidth,
        int $plotheight
    ): array {
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
            $x = self::RETENTION_LEFT + ($duration > 0 ? ($midpoint / $duration) * $plotwidth : 0);
            $y = self::RETENTION_TOP + $plotheight - (((float)$bin['retention'] / 100) * $plotheight);
            $currentpath[] = [$x, $y, $bin];
        }
        if ($currentpath) {
            $paths[] = $currentpath;
        }
        return $paths;
    }

    /**
     * Renders visible retention paths and point tooltips.
     *
     * @param array $paths Contiguous paths from retention_paths().
     * @param float $duration Video duration.
     * @return string SVG path and point markup.
     */
    private static function retention_paths_markup(array $paths, float $duration): string {
        $markup = '';
        foreach ($paths as $path) {
            $points = array_map(static function (array $point): string {
                return format_float($point[0], 3, false, true) . ',' . format_float($point[1], 3, false, true);
            }, $path);
            if (count($points) > 1) {
                $markup .= html_writer::empty_tag('polyline', [
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
                $markup .= html_writer::tag('circle', html_writer::tag('title', s($tooltip)), [
                    'cx' => format_float($x, 3, false, true),
                    'cy' => format_float($y, 3, false, true),
                    'r' => 3.5,
                    'class' => 'videotrack-analytics-retention-point',
                ]);
            }
        }
        return $markup;
    }
}
