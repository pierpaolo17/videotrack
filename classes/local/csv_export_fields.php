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
use context_system;
use core_user\fields;
use stdClass;

/**
 * Resolves configured and context-permitted CSV export fields.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_export_fields {
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
     * Returns all configurable export fields.
     *
     * With a context, Moodle identity-field permissions and custom-field visibility
     * are enforced. With a null context, the administrator receives the complete
     * configuration list.
     *
     * @param context|null $context Permission context, or null for site settings.
     * @return array Export-field labels keyed by field name.
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
            $formatcontext = context_system::instance();
            $customfields = $DB->get_records('user_info_field', null, 'sortorder ASC, id ASC', 'id, shortname, name');
            foreach ($customfields as $customfield) {
                $key = 'profile_field_' . \core_text::strtolower((string)$customfield->shortname);
                $options[$key] = format_string($customfield->name, true, ['context' => $formatcontext]);
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
     * @return array Form field labels keyed by field name.
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
            $options[$key] = format_string($customfield->name, true, ['context' => $context]);
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
        foreach (array_keys($formoptions) as $field) {
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
     * @param array $selected Selected export fields.
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
     * @param array $userids User ids.
     * @param array $selected Selected export fields.
     * @return array User records keyed by id.
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
     * Normalises a comma-separated field list.
     *
     * @param string $raw Raw field list.
     * @param array|null $allowed Optional allowed keys.
     * @return string[]
     */
    private static function normalise_field_list(string $raw, ?array $allowed = null): array {
        $fields = array_values(array_unique(array_filter(array_map('trim', explode(',', $raw)))));
        if ($allowed !== null) {
            $fields = array_values(array_intersect($fields, $allowed));
        }
        return $fields;
    }
}
