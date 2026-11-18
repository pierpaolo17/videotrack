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
 * Site-wide availability policy for implemented video sources.
 *
 * Missing settings preserve existing installations. Unknown and future sources
 * stay unavailable, independently of OAuth diagnostics or repository plugins.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class video_source_policy {
    /** Implemented source keys in the activity form's stable display order. */
    public const SOURCES = ['youtube', 'vimeo', 'upload'];

    /**
     * Check a source against the current site configuration.
     *
     * @param string $source Stored activity source.
     * @return bool True for an implemented source enabled by the administrator.
     */
    public static function is_enabled(string $source): bool {
        if (!in_array($source, self::SOURCES, true)) {
            return false;
        }
        $value = get_config('mod_videotrack', 'enable' . $source);
        return $value === false || (string)$value === '1';
    }

    /**
     * Reject disabled or unsupported sources before playback or persistence.
     *
     * @param string $source Requested activity source.
     */
    public static function require_enabled(string $source): void {
        if (!in_array($source, self::SOURCES, true)) {
            throw new \moodle_exception('invalidvideosource', 'mod_videotrack');
        }
        if (!self::is_enabled($source)) {
            throw new \moodle_exception(
                'error:videosourcedisabled',
                'mod_videotrack',
                '',
                get_string('source:' . $source, 'mod_videotrack')
            );
        }
    }

    /**
     * List enabled options, preserving an existing disabled source when editing.
     *
     * Preserving its value avoids silently replacing an activity's media. It
     * remains invalid on submit until the teacher chooses an enabled source.
     *
     * @param string $current Existing activity source, or empty for creation.
     * @return array Source keys and translated labels; empty key is a placeholder.
     */
    public static function options(string $current = ''): array {
        $options = [];
        foreach (self::SOURCES as $source) {
            $label = get_string('source:' . $source, 'mod_videotrack');
            if (self::is_enabled($source)) {
                $options[$source] = $label;
            } else if ($source === $current) {
                $options[$source] = get_string('source:disabledoption', 'mod_videotrack', $label);
            }
        }
        return $options ?: ['' => get_string('source:noneenabled', 'mod_videotrack')];
    }

    /**
     * Count affected activities once for the administrator settings page.
     *
     * @return array Counts by supported source; zero before plugin installation.
     */
    public static function usage_counts(): array {
        global $DB;

        $counts = array_fill_keys(self::SOURCES, 0);
        if (
            get_config('mod_videotrack', 'version') === false
            || !in_array('videotrack', $DB->get_tables(), true)
        ) {
            return $counts;
        }
        $rows = $DB->get_records_sql(
            "SELECT videosource, COUNT(1) AS activitycount
               FROM {videotrack}
           GROUP BY videosource"
        );
        foreach ($rows as $source => $row) {
            if (array_key_exists($source, $counts)) {
                $counts[$source] = (int)$row->activitycount;
            }
        }
        return $counts;
    }
}
