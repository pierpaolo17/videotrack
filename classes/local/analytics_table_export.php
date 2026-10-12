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

/**
 * Builds privacy-safe rows for the accessible analytics table export.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics_table_export {
    /** Data formats intentionally exposed by the analytics report. */
    public const SUPPORTED_FORMATS = ['csv', 'excel', 'ods'];

    /**
     * Returns supported formats that are enabled in the current Moodle site.
     *
     * @return string[] Enabled format names.
     */
    public static function enabled_formats(): array {
        $enabled = \core\plugininfo\dataformat::get_enabled_plugins() ?? [];
        return array_values(array_intersect(self::SUPPORTED_FORMATS, array_keys($enabled)));
    }

    /**
     * Returns localised export column headings.
     *
     * @param bool $includereactions Whether the reaction-cluster column is included.
     * @return string[] Column headings.
     */
    public static function columns(bool $includereactions): array {
        $columns = [
            get_string('report:analytics_interval', 'mod_videotrack'),
            get_string('report:analytics_uniqueviewers', 'mod_videotrack'),
            get_string('report:analytics_retention', 'mod_videotrack'),
            get_string('report:analytics_uniquetime', 'mod_videotrack'),
            get_string('report:analytics_repeattime', 'mod_videotrack'),
            get_string('report:analytics_repeatviewers', 'mod_videotrack'),
        ];
        if ($includereactions) {
            $columns[] = get_string('report:analytics_reactionclusters', 'mod_videotrack');
        }
        return $columns;
    }

    /**
     * Returns columns for the combined Analytics download.
     *
     * The accessible HTML table remains timeline-specific. Downloads add a row
     * type plus optional aggregate columns so privacy-safe reaction, bookmark,
     * integrity and acknowledgement summaries can share one export.
     *
     * @param bool $includereactions Whether the reaction-cluster column is included.
     * @param bool $includeacknowledgements Whether acknowledgement summary columns are included.
     * @return string[] Export column headings.
     */
    public static function export_columns(
        bool $includereactions,
        bool $includeacknowledgements
    ): array {
        return self::build_export_columns($includereactions, $includeacknowledgements, []);
    }

    /**
     * Returns combined Analytics download columns including aggregate event summaries.
     *
     * @param bool $includereactions Whether the reaction-cluster column is included.
     * @param bool $includeacknowledgements Whether acknowledgement summary columns are included.
     * @return string[] Export column headings.
     */
    public static function export_columns_with_event_summaries(
        bool $includereactions,
        bool $includeacknowledgements
    ): array {
        return self::build_export_columns(
            $includereactions,
            $includeacknowledgements,
            [
                get_string('report:analytics_export_summary', 'mod_videotrack'),
                get_string('report:analytics_export_events', 'mod_videotrack'),
                get_string('report:analytics_export_students', 'mod_videotrack'),
            ]
        );
    }

    /**
     * Builds the shared combined Analytics download heading set.
     *
     * @param bool $includereactions Whether the reaction-cluster column is included.
     * @param bool $includeacknowledgements Whether acknowledgement summary columns are included.
     * @param string[] $eventsummarycolumns Aggregate event summary headings, or an empty list.
     * @return string[] Export column headings.
     */
    private static function build_export_columns(
        bool $includereactions,
        bool $includeacknowledgements,
        array $eventsummarycolumns
    ): array {
        if (!$includeacknowledgements && !$eventsummarycolumns) {
            return self::columns($includereactions);
        }

        $columns = array_merge(
            [get_string('report:analytics_export_recordtype', 'mod_videotrack')],
            self::columns($includereactions)
        );
        if ($eventsummarycolumns) {
            $columns = array_merge($columns, $eventsummarycolumns);
        }
        if ($includeacknowledgements) {
            $columns = array_merge($columns, [
                get_string('report:analytics_acknowledgements_confirmations', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_students', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_average_seconds', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_average_percent', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_progressmissing', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_enabledactivities', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_anytimeactivities', 'mod_videotrack'),
                get_string('report:analytics_acknowledgements_videoendactivities', 'mod_videotrack'),
            ]);
        }
        return $columns;
    }

    /**
     * Builds rows equivalent to the accessible HTML table.
     *
     * Privacy-masked values remain masked in every exported format. The method
     * receives only the already aggregated bins and never accesses user records.
     *
     * @param array $bins Privacy-safe analytics bins.
     * @param float $duration Video duration.
     * @param bool $repeatmetricsavailable Whether replay metrics are available.
     * @param bool $includereactions Whether the reaction-cluster column is included.
     * @param int $minusers Privacy threshold.
     * @return array Export rows.
     */
    public static function rows(
        array $bins,
        float $duration,
        bool $repeatmetricsavailable,
        bool $includereactions,
        int $minusers
    ): array {
        $rows = [];
        foreach ($bins as $bin) {
            $interval = \videotrack_format_video_timestamp((float)$bin['start'], $duration) . '–' .
                \videotrack_format_video_timestamp((float)$bin['end'], $duration);
            if (!empty($bin['suppressed'])) {
                $row = [
                    $interval,
                    get_string('report:analytics_suppressed_value', 'mod_videotrack', $minusers),
                    '',
                    '',
                    '',
                    '',
                ];
                if ($includereactions) {
                    $row[] = '';
                }
                $rows[] = $row;
                continue;
            }

            if (!$repeatmetricsavailable) {
                $repeatseconds = get_string('report:analytics_repeat_unavailable', 'mod_videotrack');
                $repeatviewers = '';
            } else if (!empty($bin['repeatsuppressed'])) {
                $repeatseconds = '';
                $repeatviewers = get_string('report:analytics_suppressed_value', 'mod_videotrack', $minusers);
            } else {
                $repeatseconds = \videotrack_format_seconds((float)$bin['repeatseconds']);
                $repeatviewers = (int)$bin['repeatviewers'];
            }

            $retention = !empty($bin['retentionsuppressed'])
                ? get_string('report:analytics_notavailable_privacy', 'mod_videotrack')
                : format_float((float)$bin['retention'], 1) . '%';
            $row = [
                $interval,
                (int)$bin['viewers'],
                $retention,
                \videotrack_format_seconds((float)$bin['uniqueseconds']),
                $repeatseconds,
                $repeatviewers,
            ];
            if ($includereactions) {
                $row[] = (int)($bin['reactionclusters'] ?? 0) > 0
                    ? get_string('report:analytics_reactions_cell', 'mod_videotrack', [
                        'clusters' => (int)$bin['reactionclusters'],
                        'events' => (int)($bin['reactionevents'] ?? 0),
                    ])
                    : 0;
            }
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Builds a combined timeline and privacy-safe aggregate export.
     *
     * @param array $bins Privacy-safe analytics bins.
     * @param float $duration Video duration.
     * @param bool $repeatmetricsavailable Whether replay metrics are available.
     * @param bool $includereactions Whether reaction clusters are included in timeline rows.
     * @param int $minusers Privacy threshold.
     * @param array|null $acknowledgementsummary Privacy-safe acknowledgement summary.
     * @param array|null $reactionsummary Privacy-safe overall reaction summary.
     * @param array|null $bookmarksummary Privacy-safe bookmark summary.
     * @param array|null $integritysummary Privacy-safe per-signal integrity summary.
     * @return array Export rows.
     */
    public static function export_rows(
        array $bins,
        float $duration,
        bool $repeatmetricsavailable,
        bool $includereactions,
        int $minusers,
        ?array $acknowledgementsummary,
        ?array $reactionsummary = null,
        ?array $bookmarksummary = null,
        ?array $integritysummary = null
    ): array {
        $includeeventsummaries = $reactionsummary !== null
            || $bookmarksummary !== null
            || $integritysummary !== null;
        if ($acknowledgementsummary === null && !$includeeventsummaries) {
            return self::rows($bins, $duration, $repeatmetricsavailable, $includereactions, $minusers);
        }

        $timelinecolumncount = count(self::columns($includereactions));
        $summarycolumncount = $includeeventsummaries ? 3 : 0;
        $acknowledgementcolumncount = $acknowledgementsummary !== null ? 8 : 0;
        $rows = self::viewing_export_rows(
            self::rows($bins, $duration, $repeatmetricsavailable, $includereactions, $minusers),
            $summarycolumncount,
            $acknowledgementcolumncount
        );

        if ($includeeventsummaries) {
            $rows = array_merge($rows, self::event_summary_rows(
                $reactionsummary,
                $bookmarksummary,
                $integritysummary,
                $timelinecolumncount,
                $acknowledgementcolumncount
            ));
        }

        if ($acknowledgementsummary !== null) {
            $rows[] = self::acknowledgement_summary_row(
                $acknowledgementsummary,
                $timelinecolumncount,
                $summarycolumncount
            );
        }
        return $rows;
    }

    /**
     * Prefixes timeline rows and pads them to the selected combined export shape.
     *
     * @param array $timelinerows Privacy-safe timeline rows.
     * @param int $summarycolumncount Number of event-summary columns.
     * @param int $acknowledgementcolumncount Number of acknowledgement columns.
     * @return array Combined export rows.
     */
    private static function viewing_export_rows(
        array $timelinerows,
        int $summarycolumncount,
        int $acknowledgementcolumncount
    ): array {
        $rows = [];
        foreach ($timelinerows as $row) {
            $rows[] = array_merge(
                [get_string('report:analytics_export_row_viewing', 'mod_videotrack')],
                $row,
                array_fill(0, $summarycolumncount + $acknowledgementcolumncount, '')
            );
        }
        return $rows;
    }

    /**
     * Builds reaction, bookmark and integrity summary rows in display order.
     *
     * @param array|null $reactionsummary Privacy-safe overall reaction summary.
     * @param array|null $bookmarksummary Privacy-safe bookmark summary.
     * @param array|null $integritysummary Privacy-safe per-signal integrity summary.
     * @param int $timelinecolumncount Number of timeline columns to pad.
     * @param int $acknowledgementcolumncount Number of acknowledgement columns to pad.
     * @return array Event-summary rows.
     */
    private static function event_summary_rows(
        ?array $reactionsummary,
        ?array $bookmarksummary,
        ?array $integritysummary,
        int $timelinecolumncount,
        int $acknowledgementcolumncount
    ): array {
        $rows = [];
        if ($reactionsummary !== null && !empty($reactionsummary['hasdata'])) {
            [$eventvalue, $studentvalue] = self::count_summary_values($reactionsummary);
            $rows[] = self::event_summary_row(
                get_string('report:analytics_export_row_reactions', 'mod_videotrack'),
                get_string('report:analytics_reactions_detected', 'mod_videotrack'),
                $eventvalue,
                $studentvalue,
                $timelinecolumncount,
                $acknowledgementcolumncount
            );
        }

        if ($bookmarksummary !== null) {
            [$eventvalue, $studentvalue] = self::count_summary_values($bookmarksummary);
            $rows[] = self::event_summary_row(
                get_string('report:analytics_export_row_bookmarks', 'mod_videotrack'),
                get_string('report:analytics_bookmarks_title', 'mod_videotrack'),
                $eventvalue,
                $studentvalue,
                $timelinecolumncount,
                $acknowledgementcolumncount
            );
        }

        if ($integritysummary !== null) {
            $rows = array_merge($rows, self::integrity_summary_rows(
                $integritysummary,
                $timelinecolumncount,
                $acknowledgementcolumncount
            ));
        }
        return $rows;
    }

    /**
     * Builds integrity-event rows or the explicit no-data row.
     *
     * @param array $integritysummary Privacy-safe per-signal integrity summary.
     * @param int $timelinecolumncount Number of timeline columns to pad.
     * @param int $acknowledgementcolumncount Number of acknowledgement columns to pad.
     * @return array Integrity summary rows.
     */
    private static function integrity_summary_rows(
        array $integritysummary,
        int $timelinecolumncount,
        int $acknowledgementcolumncount
    ): array {
        $rows = [];
        foreach (integrity::EVENT_TYPES as $eventtype) {
            $item = $integritysummary[$eventtype] ?? [];
            if (empty($item['hasdata'])) {
                continue;
            }
            [$eventvalue, $studentvalue] = self::count_summary_values($item);
            $rows[] = self::event_summary_row(
                get_string('report:analytics_export_row_integrity', 'mod_videotrack'),
                get_string(integrity::label_string($eventtype), 'mod_videotrack'),
                $eventvalue,
                $studentvalue,
                $timelinecolumncount,
                $acknowledgementcolumncount
            );
        }

        if (!$rows) {
            $rows[] = self::event_summary_row(
                get_string('report:analytics_export_row_integrity', 'mod_videotrack'),
                get_string('integrity:nodata', 'mod_videotrack'),
                0,
                0,
                $timelinecolumncount,
                $acknowledgementcolumncount
            );
        }
        return $rows;
    }

    /**
     * Builds the acknowledgement row with privacy-safe values and column padding.
     *
     * @param array $summary Privacy-safe acknowledgement summary.
     * @param int $timelinecolumncount Number of timeline columns to pad.
     * @param int $summarycolumncount Number of event-summary columns to pad.
     * @return array Acknowledgement summary row.
     */
    private static function acknowledgement_summary_row(
        array $summary,
        int $timelinecolumncount,
        int $summarycolumncount
    ): array {
        [$confirmationvalue, $studentvalue, $secondsvalue, $percentvalue, $missingvalue] =
            self::acknowledgement_summary_values($summary);

        return array_merge(
            [get_string('report:analytics_export_row_acknowledgement', 'mod_videotrack')],
            array_fill(0, $timelinecolumncount + $summarycolumncount, ''),
            [
                $confirmationvalue,
                $studentvalue,
                $secondsvalue,
                $percentvalue,
                $missingvalue,
                (int)($summary['enabledactivitycount'] ?? 0),
                (int)($summary['anytimeactivitycount'] ?? 0),
                (int)($summary['videoendactivitycount'] ?? 0),
            ]
        );
    }

    /**
     * Resolves acknowledgement values under full and progress-only suppression.
     *
     * @param array $summary Privacy-safe acknowledgement summary.
     * @return array Confirmation, student, seconds, percentage and missing-progress values.
     */
    private static function acknowledgement_summary_values(array $summary): array {
        $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
        if (!empty($summary['suppressed'])) {
            return [$hidden, $hidden, $hidden, $hidden, $hidden];
        }

        $confirmationvalue = (int)($summary['confirmationcount'] ?? 0);
        $studentvalue = (int)($summary['studentcount'] ?? 0);
        $missingvalue = (int)($summary['progressmissing'] ?? 0);
        if (!empty($summary['progresssuppressed'])) {
            return [$confirmationvalue, $studentvalue, $hidden, $hidden, $missingvalue];
        }

        $unavailable = get_string('report:analytics_acknowledgements_unavailable', 'mod_videotrack');
        $secondsvalue = $summary['averageviewedseconds'] === null
            ? $unavailable
            : round((float)$summary['averageviewedseconds'], 3);
        $percentvalue = $summary['averageviewedpercent'] === null
            ? $unavailable
            : format_float((float)$summary['averageviewedpercent'], 1) . '%';
        return [$confirmationvalue, $studentvalue, $secondsvalue, $percentvalue, $missingvalue];
    }

    /**
     * Converts a privacy-safe count summary to export values.
     *
     * @param array $summary Summary containing eventcount, studentcount and suppression state.
     * @return array Event and student values.
     */
    private static function count_summary_values(array $summary): array {
        if (!empty($summary['hasdata']) && !empty($summary['suppressed'])) {
            $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
            return [$hidden, $hidden];
        }
        return [
            (int)($summary['eventcount'] ?? 0),
            (int)($summary['studentcount'] ?? 0),
        ];
    }

    /**
     * Builds one aggregate event-summary row aligned with the combined export columns.
     *
     * @param string $recordtype Localised record type.
     * @param string $label Localised summary label.
     * @param int|string $eventvalue Privacy-safe event value.
     * @param int|string $studentvalue Privacy-safe student value.
     * @param int $timelinecolumncount Number of timeline columns to pad.
     * @param int $acknowledgementcolumncount Number of acknowledgement columns to pad.
     * @return array Export row.
     */
    private static function event_summary_row(
        string $recordtype,
        string $label,
        int|string $eventvalue,
        int|string $studentvalue,
        int $timelinecolumncount,
        int $acknowledgementcolumncount
    ): array {
        return array_merge(
            [$recordtype],
            array_fill(0, $timelinecolumncount, ''),
            [$label, $eventvalue, $studentvalue],
            array_fill(0, $acknowledgementcolumncount, '')
        );
    }
}
