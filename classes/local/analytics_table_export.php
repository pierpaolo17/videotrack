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
     * @param bool $includeeventsummaries Whether aggregate event summary columns are included.
     * @return string[] Export column headings.
     */
    public static function export_columns(
        bool $includereactions,
        bool $includeacknowledgements,
        bool $includeeventsummaries = false
    ): array {
        if (!$includeacknowledgements && !$includeeventsummaries) {
            return self::columns($includereactions);
        }

        $columns = array_merge(
            [get_string('report:analytics_export_recordtype', 'mod_videotrack')],
            self::columns($includereactions)
        );
        if ($includeeventsummaries) {
            $columns = array_merge($columns, [
                get_string('report:analytics_export_summary', 'mod_videotrack'),
                get_string('report:analytics_export_events', 'mod_videotrack'),
                get_string('report:analytics_export_students', 'mod_videotrack'),
            ]);
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
     * @return array<int, array<int, int|string>> Export rows.
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
     * @return array<int, array<int, int|float|string>> Export rows.
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
        $rows = [];
        foreach (self::rows($bins, $duration, $repeatmetricsavailable, $includereactions, $minusers) as $row) {
            $rows[] = array_merge(
                [get_string('report:analytics_export_row_viewing', 'mod_videotrack')],
                $row,
                array_fill(0, $summarycolumncount + $acknowledgementcolumncount, '')
            );
        }

        if ($includeeventsummaries) {
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
                $hasintegritydata = false;
                foreach (integrity::EVENT_TYPES as $eventtype) {
                    $item = $integritysummary[$eventtype] ?? [];
                    if (empty($item['hasdata'])) {
                        continue;
                    }
                    $hasintegritydata = true;
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
                if (!$hasintegritydata) {
                    $rows[] = self::event_summary_row(
                        get_string('report:analytics_export_row_integrity', 'mod_videotrack'),
                        get_string('integrity:nodata', 'mod_videotrack'),
                        0,
                        0,
                        $timelinecolumncount,
                        $acknowledgementcolumncount
                    );
                }
            }
        }

        if ($acknowledgementsummary !== null) {
            $suppressed = !empty($acknowledgementsummary['suppressed']);
            $progresssuppressed = !empty($acknowledgementsummary['progresssuppressed']);
            $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
            $unavailable = get_string('report:analytics_acknowledgements_unavailable', 'mod_videotrack');
            $confirmationvalue = $suppressed
                ? $hidden
                : (int)($acknowledgementsummary['confirmationcount'] ?? 0);
            $studentvalue = $suppressed
                ? $hidden
                : (int)($acknowledgementsummary['studentcount'] ?? 0);
            $secondsvalue = ($suppressed || $progresssuppressed)
                ? $hidden
                : ($acknowledgementsummary['averageviewedseconds'] === null
                    ? $unavailable
                    : round((float)$acknowledgementsummary['averageviewedseconds'], 3));
            $percentvalue = ($suppressed || $progresssuppressed)
                ? $hidden
                : ($acknowledgementsummary['averageviewedpercent'] === null
                    ? $unavailable
                    : format_float((float)$acknowledgementsummary['averageviewedpercent'], 1) . '%');
            $missingvalue = $suppressed
                ? $hidden
                : (int)($acknowledgementsummary['progressmissing'] ?? 0);
            $rows[] = array_merge(
                [get_string('report:analytics_export_row_acknowledgement', 'mod_videotrack')],
                array_fill(0, $timelinecolumncount + $summarycolumncount, ''),
                [
                    $confirmationvalue,
                    $studentvalue,
                    $secondsvalue,
                    $percentvalue,
                    $missingvalue,
                    (int)($acknowledgementsummary['enabledactivitycount'] ?? 0),
                    (int)($acknowledgementsummary['anytimeactivitycount'] ?? 0),
                    (int)($acknowledgementsummary['videoendactivitycount'] ?? 0),
                ]
            );
        }
        return $rows;
    }

    /**
     * Converts a privacy-safe count summary to export values.
     *
     * @param array $summary Summary containing eventcount, studentcount and suppression state.
     * @return array{0:int|string,1:int|string} Event and student values.
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
     * @return array<int, int|string> Export row.
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
