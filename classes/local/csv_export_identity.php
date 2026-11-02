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
use core_user\fields;
use stdClass;

/**
 * Formats CSV identity headers, values and video links.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_export_identity {
    /** Optional course and activity fields. */
    private const CONTEXT_FIELDS = [
        'coursefullname',
        'courseshortname',
        'courseid',
        'instancename',
        'videolink',
    ];

    /**
     * Returns localised headers preceding event/state-specific columns.
     *
     * @param array $selected Selected optional fields.
     * @return string[]
     */
    public static function identity_headers(array $selected): array {
        $headers = [];
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                $headers[] = self::field_label($field);
            }
        }
        $headers[] = get_string('lastname');
        $headers[] = get_string('firstname');
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                continue;
            }
            $headers[] = self::field_label($field);
        }
        return $headers;
    }

    /**
     * Returns values matching identity_headers().
     *
     * @param array $selected Selected optional fields.
     * @param stdClass $course Course record.
     * @param stdClass $videotrack Activity record.
     * @param stdClass|null $user User record.
     * @param string $userlabel Fallback label when the user record is unavailable.
     * @param int $cmid Course module id, required for uploaded-video links.
     * @param context $context Module context used for formatted course/activity strings.
     * @return array
     */
    public static function identity_values(
        array $selected,
        stdClass $course,
        stdClass $videotrack,
        ?stdClass $user,
        string $userlabel,
        int $cmid,
        context $context
    ): array {
        $values = [];
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                $values[] = self::field_value($field, $course, $videotrack, $user, $cmid, $context);
            }
        }
        if ($user) {
            $values[] = trim((string)($user->lastname ?? ''));
            $values[] = trim((string)($user->firstname ?? ''));
        } else {
            $values[] = '';
            $values[] = $userlabel;
        }
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                continue;
            }
            $values[] = self::field_value($field, $course, $videotrack, $user, $cmid, $context);
        }
        return $values;
    }

    /**
     * Returns a field label.
     *
     * @param string $field Field key.
     * @return string
     */
    private static function field_label(string $field): string {
        if (strpos($field, 'profile_field_') === 0) {
            return fields::get_display_name($field);
        }
        return get_string('report:csvfield_' . $field, 'mod_videotrack');
    }

    /**
     * Returns one configured field value.
     *
     * @param string $field Field key.
     * @param stdClass $course Course record.
     * @param stdClass $videotrack Activity record.
     * @param stdClass|null $user User record.
     * @param int $cmid Course module id.
     * @param context $context Module context used by Moodle text filters.
     * @return mixed
     */
    private static function field_value(
        string $field,
        stdClass $course,
        stdClass $videotrack,
        ?stdClass $user,
        int $cmid,
        context $context
    ) {
        switch ($field) {
            case 'coursefullname':
                return format_string($course->fullname, true, ['context' => $context]);
            case 'courseshortname':
                return format_string($course->shortname, true, ['context' => $context]);
            case 'courseid':
                return (int)$course->id;
            case 'instancename':
                return format_string($videotrack->name, true, ['context' => $context]);
            case 'videolink':
                return self::video_url($videotrack, $cmid);
        }
        return self::user_field_value($field, $user);
    }

    /**
     * Returns a configured user field with its existing country-name formatting.
     *
     * @param string $field Field key.
     * @param stdClass|null $user User record, if available.
     * @return string Sanitised value for a user field.
     */
    private static function user_field_value(string $field, ?stdClass $user): string {
        if (!$user || !property_exists($user, $field)) {
            return '';
        }
        $value = trim(strip_tags((string)$user->{$field}));
        if ($field === 'country' && $value !== '' && get_string_manager()->string_exists($value, 'countries')) {
            return get_string($value, 'countries');
        }
        return $value;
    }

    /**
     * Returns the configured video URL for CSV exports.
     *
     * @param stdClass $videotrack Activity record.
     * @param int $cmid Course module id.
     * @return string
     */
    private static function video_url(stdClass $videotrack, int $cmid): string {
        $source = (string)($videotrack->videosource ?? 'youtube');
        if ($source === 'upload') {
            $context = \context_module::instance($cmid);
            $files = get_file_storage()->get_area_files(
                $context->id,
                'mod_videotrack',
                'videocontent',
                0,
                'id',
                false
            );
            if (!$files) {
                return '';
            }
            $file = reset($files);
            return \moodle_url::make_pluginfile_url(
                $context->id,
                'mod_videotrack',
                'videocontent',
                0,
                $file->get_filepath(),
                $file->get_filename()
            )->out(false);
        }
        return trim((string)($videotrack->videourl ?? $videotrack->youtubeurl ?? ''));
    }
}
