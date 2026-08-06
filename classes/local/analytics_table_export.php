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

            $row = [
                $interval,
                (int)$bin['viewers'],
                format_float((float)$bin['retention'], 1) . '%',
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
}
