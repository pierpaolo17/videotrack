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
 * CSV export configuration and formatting helpers.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_export {
    /** Instance inherits the site delimiter. */
    public const DELIMITER_INHERIT = 'inherit';

    /** RFC-style comma separator. */
    public const DELIMITER_COMMA = 'comma';

    /** Spreadsheet-friendly semicolon separator. */
    public const DELIMITER_SEMICOLON = 'semicolon';

    /** Section-sign separator. */
    public const DELIMITER_SECTION = 'section';

    /** Hash separator. */
    public const DELIMITER_HASH = 'hash';

    /** Pipe separator. */
    public const DELIMITER_PIPE = 'pipe';

    /** Optional course and activity fields. */
    private const CONTEXT_FIELDS = [
        'coursefullname',
        'courseshortname',
        'courseid',
        'instancename',
        'videolink',
    ];

    /** Optional standard Moodle user fields requested by the activity. */
    private const STANDARD_USER_FIELDS = [
        'username',
        'email',
        'city',
        'country',
        'idnumber',
        'institution',
        'department',
        'phone1',
        'phone2',
        'address',
    ];

    /**
     * Returns delimiter options stored as symbolic values.
     *
     * @param bool $includeinherit Include the per-instance inherit option.
     * @return array<string, string>
     */
    public static function delimiter_options(bool $includeinherit = false): array {
        $options = [];
        if ($includeinherit) {
            $options[self::DELIMITER_INHERIT] = get_string('setting:csvdelimiter_inherit', 'mod_videotrack');
        }
        $options[self::DELIMITER_COMMA] = get_string('setting:csvdelimiter_comma', 'mod_videotrack');
        $options[self::DELIMITER_SEMICOLON] = get_string('setting:csvdelimiter_semicolon', 'mod_videotrack');
        $options[self::DELIMITER_SECTION] = get_string('setting:csvdelimiter_section', 'mod_videotrack');
        $options[self::DELIMITER_HASH] = get_string('setting:csvdelimiter_hash', 'mod_videotrack');
        $options[self::DELIMITER_PIPE] = get_string('setting:csvdelimiter_pipe', 'mod_videotrack');
        return $options;
    }

    /**
     * Resolves the actual one-character delimiter for an activity.
     *
     * @param stdClass $videotrack Activity record.
     * @return string
     */
    public static function delimiter(stdClass $videotrack): string {
        $configured = (string)($videotrack->csvdelimiter ?? self::DELIMITER_INHERIT);
        if ($configured === '' || $configured === self::DELIMITER_INHERIT) {
            $configured = (string)get_config('mod_videotrack', 'csvdelimiter');
        }
        return match ($configured) {
            self::DELIMITER_SEMICOLON => ';',
            self::DELIMITER_SECTION => '§',
            self::DELIMITER_HASH => '#',
            self::DELIMITER_PIPE => '|',
            default => ',',
        };
    }

    /**
     * Returns all configurable export fields.
     *
     * With a context, Moodle identity-field permissions and custom-field visibility
     * are enforced. With a null context, the administrator receives the complete
     * configuration list.
     *
     * @param context|null $context Permission context, or null for site settings.
     * @return array<string, string>
     */
    public static function field_options(?context $context = null): array {
        global $DB;

        $options = [
            'coursefullname' => get_string('report:csvfield_coursefullname', 'mod_videotrack'),
            'courseshortname' => get_string('report:csvfield_courseshortname', 'mod_videotrack'),
            'courseid' => get_string('report:csvfield_courseid', 'mod_videotrack'),
            'instancename' => get_string('report:csvfield_instancename', 'mod_videotrack'),
            'videolink' => get_string('report:csvfield_videolink', 'mod_videotrack'),
        ];

        $allowedidentity = $context === null ? null : fields::get_identity_fields($context, true);
        foreach (self::STANDARD_USER_FIELDS as $field) {
            if ($allowedidentity !== null && !in_array($field, $allowedidentity, true)) {
                continue;
            }
            $options[$field] = get_string('report:csvfield_' . $field, 'mod_videotrack');
        }

        if ($context === null) {
            $customfields = $DB->get_records('user_info_field', null, 'sortorder ASC, id ASC', 'id, shortname, name');
            foreach ($customfields as $customfield) {
                $key = 'profile_field_' . \core_text::strtolower((string)$customfield->shortname);
                $options[$key] = format_string($customfield->name);
            }
        } else {
            foreach ($allowedidentity as $field) {
                if (strpos($field, 'profile_field_') !== 0) {
                    continue;
                }
                $options[$field] = fields::get_display_name($field);
            }
        }

        return $options;
    }

    /**
     * Returns the export-field choices shown in an activity form.
     *
     * Course/activity fields and all standard user fields are always shown so
     * teachers see the same configuration surface as administrators. Runtime
     * export still applies Moodle identity-field permissions; fields that are
     * not available in the current context are shown read-only by mod_form.
     *
     * @param context $context Activity or course context.
     * @return array<string, string>
     */
    public static function form_field_options(context $context): array {
        global $DB;

        $options = [
            'coursefullname' => get_string('report:csvfield_coursefullname', 'mod_videotrack'),
            'courseshortname' => get_string('report:csvfield_courseshortname', 'mod_videotrack'),
            'courseid' => get_string('report:csvfield_courseid', 'mod_videotrack'),
            'instancename' => get_string('report:csvfield_instancename', 'mod_videotrack'),
            'videolink' => get_string('report:csvfield_videolink', 'mod_videotrack'),
        ];
        foreach (self::STANDARD_USER_FIELDS as $field) {
            $options[$field] = get_string('report:csvfield_' . $field, 'mod_videotrack');
        }
        $customfields = $DB->get_records('user_info_field', null, 'sortorder ASC, id ASC', 'id, shortname, name');
        foreach ($customfields as $customfield) {
            $key = 'profile_field_' . \core_text::strtolower((string)$customfield->shortname);
            $options[$key] = format_string($customfield->name);
        }
        return $options;
    }

    /**
     * Returns the site default list of optional export fields.
     *
     * @return string[]
     */
    public static function site_default_fields(): array {
        $configured = get_config('mod_videotrack', 'csvexportfields');
        if ($configured === false || $configured === null || $configured === '') {
            return ['coursefullname', 'instancename', 'username', 'email'];
        }
        return self::normalise_field_list((string)$configured, array_keys(self::field_options(null)));
    }

    /**
     * Resolves activity fields, applying Moodle identity permissions.
     *
     * @param stdClass $videotrack Activity record.
     * @param context $context Module context.
     * @return string[]
     */
    public static function activity_fields(stdClass $videotrack, context $context): array {
        $raw = trim((string)($videotrack->csvexportfields ?? ''));
        if ($raw === 'none') {
            return [];
        }
        $selected = $raw === '' ? self::site_default_fields() : self::normalise_field_list($raw);
        return array_values(array_intersect($selected, array_keys(self::field_options($context))));
    }

    /**
     * Returns the deterministic form element name for an export field.
     *
     * @param string $field Export field key.
     * @return string
     */
    public static function form_element_name(string $field): string {
        return 'csvexportfield_' . substr(sha1($field), 0, 16);
    }

    /**
     * Aggregates submitted export-field checkboxes and removes helper fields.
     *
     * @param stdClass $data Submitted activity data.
     * @param context|null $context Context used to restrict identity fields, or null for trusted site-level processing.
     * @return void
     */
    public static function process_form_fields(stdClass $data, ?context $context = null): void {
        $selected = [];
        $allowed = array_keys(self::field_options($context));
        $formoptions = $context === null ? self::field_options(null) : self::form_field_options($context);
        foreach ($formoptions as $field => $label) {
            $elementname = self::form_element_name($field);
            if (!empty($data->{$elementname}) && in_array($field, $allowed, true)) {
                $selected[] = $field;
            }
            unset($data->{$elementname});
        }
        $data->csvexportfields = $selected ? implode(',', $selected) : 'none';
    }

    /**
     * Returns only selected user-table/custom-profile keys.
     *
     * @param string[] $selected Selected export fields.
     * @return string[]
     */
    public static function selected_user_fields(array $selected): array {
        return array_values(array_filter($selected, static function (string $field): bool {
            return in_array($field, self::STANDARD_USER_FIELDS, true) || strpos($field, 'profile_field_') === 0;
        }));
    }

    /**
     * Loads users and selected visible identity fields in one query.
     *
     * @param int[] $userids User ids.
     * @param string[] $selected Selected export fields.
     * @return array<int, stdClass>
     */
    public static function load_users(array $userids, array $selected): array {
        global $DB;

        $userids = array_values(array_unique(array_filter(array_map('intval', $userids), static function (int $id): bool {
            return $id > 0;
        })));
        if (!$userids) {
            return [];
        }

        $required = array_values(array_unique(array_merge(
            ['id'],
            fields::get_name_fields(),
            self::selected_user_fields($selected)
        )));
        $fieldapi = fields::empty()->including(...$required);
        $fieldsql = $fieldapi->get_sql('u', true, '', '', false);
        [$insql, $inparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED, 'csvuid');
        $params = array_merge($fieldsql->params, $inparams);
        return $DB->get_records_sql(
            "SELECT {$fieldsql->selects}
               FROM {user} u
                    {$fieldsql->joins}
              WHERE u.id {$insql}",
            $params
        );
    }

    /**
     * Returns localised headers preceding event/state-specific columns.
     *
     * @param string[] $selected Selected optional fields.
     * @return string[]
     */
    public static function identity_headers(array $selected): array {
        $headers = [];
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                $headers[] = self::field_label($field);
            }
        }
        $headers[] = get_string('report:userid', 'mod_videotrack');
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
     * @param string[] $selected Selected optional fields.
     * @param stdClass $course Course record.
     * @param stdClass $videotrack Activity record.
     * @param stdClass|null $user User record.
     * @param string $userlabel Mandatory formatted user label.
     * @param int $cmid Course module id, required for uploaded-video links.
     * @return array
     */
    public static function identity_values(
        array $selected,
        stdClass $course,
        stdClass $videotrack,
        ?stdClass $user,
        string $userlabel,
        int $cmid
    ): array {
        $values = [];
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                $values[] = self::field_value($field, $course, $videotrack, $user, $cmid);
            }
        }
        $values[] = $userlabel;
        foreach ($selected as $field) {
            if (in_array($field, self::CONTEXT_FIELDS, true)) {
                continue;
            }
            $values[] = self::field_value($field, $course, $videotrack, $user, $cmid);
        }
        return $values;
    }

    /**
     * Writes a UTF-8 byte-order mark for spreadsheet applications.
     *
     * The plugin stores and emits UTF-8. The BOM prevents applications such as
     * Microsoft Excel from interpreting accented text as a legacy code page.
     *
     * @param resource $handle Output stream.
     * @return void
     */
    public static function write_utf8_bom($handle): void {
        fwrite($handle, "\xEF\xBB\xBF");
    }

    /**
     * Writes a CSV row using an explicit escape parameter for PHP 8.4 compatibility.
     *
     * @param resource $handle Output stream.
     * @param array $row Values.
     * @param string $delimiter One-character delimiter.
     * @return void
     */
    public static function write_row($handle, array $row, string $delimiter): void {
        $row = array_map([self::class, 'safe_value'], $row);
        if (strlen($delimiter) === 1) {
            fputcsv($handle, $row, $delimiter, '"', '');
            return;
        }

        $encoded = array_map(static function ($value) use ($delimiter): string {
            $value = (string)$value;
            $mustquote = strpos($value, $delimiter) !== false
                || strpos($value, '"') !== false
                || strpos($value, "\r") !== false
                || strpos($value, "\n") !== false;
            $value = str_replace('"', '""', $value);
            return $mustquote ? '"' . $value . '"' : $value;
        }, $row);
        fwrite($handle, implode($delimiter, $encoded) . "\r\n");
    }

    /**
     * Protects spreadsheet consumers from formula injection.
     *
     * @param mixed $value Value to export.
     * @return mixed Sanitised scalar value.
     */
    public static function safe_value($value) {
        if (!is_string($value)) {
            return $value;
        }
        if ($value !== '' && preg_match('/^[=+\-@\t\r\n]/', ltrim($value))) {
            return "'" . $value;
        }
        return $value;
    }

    /**
     * Normalises a comma-separated field list.
     *
     * @param string $raw Raw field list.
     * @param string[]|null $allowed Optional allowed keys.
     * @return string[]
     */
    private static function normalise_field_list(string $raw, ?array $allowed = null): array {
        $fields = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        if ($allowed !== null) {
            $fields = array_values(array_intersect($fields, $allowed));
        }
        return $fields;
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
     * @return mixed
     */
    private static function field_value(
        string $field,
        stdClass $course,
        stdClass $videotrack,
        ?stdClass $user,
        int $cmid
    ) {
        switch ($field) {
            case 'coursefullname':
                return format_string($course->fullname);
            case 'courseshortname':
                return format_string($course->shortname);
            case 'courseid':
                return (int)$course->id;
            case 'instancename':
                return format_string($videotrack->name);
            case 'videolink':
                return self::video_url($videotrack, $cmid);
        }
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
