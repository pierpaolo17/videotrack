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

        $unavailable = $context === null ? [] : self::unavailable_reasons($context);
        foreach (self::STANDARD_USER_FIELDS as $field) {
            if (isset($unavailable[$field])) {
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
            foreach (self::form_field_options($context) as $field => $label) {
                if (str_starts_with($field, 'profile_field_') && !isset($unavailable[$field])) {
                    $options[$field] = $label;
                }
            }
        }

        return $options;
    }

    /**
     * Explain actual field restrictions without treating Moodle's identity display list as an admin permission.
     *
     * @param context $context Effective activity or course context.
     * @return array Field keys mapped to translatable reason keys; available fields are absent.
     */
    public static function unavailable_reasons(context $context): array {
        global $CFG;

        $identity = fields::get_identity_fields($context, true);
        $permissions = [
            'admin' => is_siteadmin(),
            'identity' => has_capability('moodle/site:viewuseridentity', $context),
            'all' => has_capability('moodle/user:viewalldetails', $context),
            'profile' => has_capability('moodle/user:viewdetails', $context),
            'email' => has_capability('moodle/course:useremail', $context),
            'hidden' => has_capability(
                $context->get_course_context(false) ? 'moodle/course:viewhiddenuserfields' : 'moodle/user:viewhiddendetails',
                $context
            ),
        ];
        $hidden = array_filter(explode(',', (string)($CFG->hiddenuserfields ?? '')));
        $reasons = [];
        foreach (self::STANDARD_USER_FIELDS as $field) {
            $reason = self::standard_field_reason($field, $identity, $permissions, $hidden);
            if ($reason !== null) {
                $reasons[$field] = $reason;
            }
        }
        return $reasons + self::profile_field_reasons($context, $permissions);
    }

    /**
     * Apply the standard-profile read rules used by Moodle's user details API.
     *
     * @param string $field Standard user field key.
     * @param array $identity Identity fields authorised by Moodle.
     * @param array $permissions Current actor permissions in the effective context.
     * @param array $hidden Site-hidden profile fields.
     * @return string|null Unavailability reason key, or null when accessible.
     */
    private static function standard_field_reason(string $field, array $identity, array $permissions, array $hidden): ?string {
        if ($permissions['admin']) {
            return null;
        }
        if (in_array($field, $hidden, true) && !$permissions['hidden']) {
            return 'report:csvfield_unavailable_hidden';
        }
        if (in_array($field, $identity, true)) {
            return null;
        }
        if ($permissions['all'] && in_array($field, ['username', 'idnumber', 'institution', 'department'], true)) {
            return null;
        }
        if ($permissions['profile'] && in_array($field, ['city', 'country'], true)) {
            return null;
        }
        if ($field === 'email' && $permissions['email']) {
            return null;
        }
        return $permissions['identity']
            ? 'report:csvfield_unavailable_configuration'
            : 'report:csvfield_unavailable_permission';
    }

    /**
     * Reuse custom-profile visibility, using the explicit course rather than the global course fallback.
     *
     * @param context $context Effective context.
     * @param array $permissions Current actor permissions.
     * @return array Unavailable profile-field keys and reason keys.
     */
    private static function profile_field_reasons(context $context, array $permissions): array {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/user/profile/lib.php');
        $coursecontext = $context->get_course_context(false);
        $visibilitycontext = $coursecontext ?: $context;
        $canviewprofile = $permissions['admin'] || $permissions['all'] || $permissions['identity'] || $permissions['profile'];
        $reasons = [];
        foreach ($DB->get_records('user_info_field', null, 'sortorder ASC, id ASC') as $field) {
            $key = 'profile_field_' . \core_text::strtolower((string)$field->shortname);
            $instance = profile_get_user_field($field->datatype, (int)$field->id, 0, $field);
            // A completely hidden field requires system permission, even if an actor has a local override.
            $fieldcontext = (int)$field->visible === PROFILE_VISIBLE_NONE ? context_system::instance() : $visibilitycontext;
            if (!$canviewprofile || !$instance->is_visible($fieldcontext)) {
                $reasons[$key] = 'report:csvfield_unavailable_profile';
            }
        }
        return $reasons;
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
     * @param context|null $context Effective field permission context.
     * @param stdClass|null $previous Stored activity, used to preserve disabled choices and inheritance.
     * @return void
     */
    public static function process_form_fields(stdClass $data, ?context $context = null, ?stdClass $previous = null): void {
        $allowed = array_keys(self::field_options($context));
        $formoptions = $context === null ? self::field_options(null) : self::form_field_options($context);
        $oldraw = trim((string)($previous->csvexportfields ?? $data->csvexportfields ?? ''));
        $oldfields = $oldraw === '' ? self::site_default_fields() : self::normalise_field_list($oldraw);
        [$selected, $submitted] = self::submitted_fields($data, array_keys($formoptions), $allowed, $oldfields);
        if (!$submitted) {
            $raw = trim((string)($data->csvexportfields ?? $previous->csvexportfields ?? ''));
            $data->csvexportfields = in_array($raw, ['', 'none'], true)
                ? $raw : implode(',', self::normalise_field_list($raw, array_keys($formoptions)));
            return;
        }
        $selected = array_values(array_unique(array_merge(array_intersect($oldfields, $selected), $selected)));
        $inherits = $oldraw === '' && !array_diff($oldfields, $selected) && !array_diff($selected, $oldfields);
        $data->csvexportfields = $inherits ? '' : ($selected ? implode(',', $selected) : 'none');
    }

    /**
     * Collect editable checkboxes while keeping an unavailable stored preference locked.
     *
     * @param stdClass $data Form data; helper properties are removed in place.
     * @param array $formfields Configurable field keys.
     * @param array $allowed Read-permitted field keys.
     * @param array $oldfields Previously selected field keys.
     * @return array{0: string[], 1: bool} Selected keys and whether checkboxes were submitted.
     */
    private static function submitted_fields(stdClass $data, array $formfields, array $allowed, array $oldfields): array {
        $selected = [];
        $submitted = false;
        foreach ($formfields as $field) {
            $elementname = self::form_element_name($field);
            $submitted = $submitted || property_exists($data, $elementname);
            if (!in_array($field, $allowed, true) && in_array($field, $oldfields, true)) {
                // A disabled checkbox is absent from POST; it must not erase the stored preference.
                $selected[] = $field;
            } else if (!empty($data->{$elementname}) && in_array($field, $allowed, true)) {
                $selected[] = $field;
            }
            unset($data->{$elementname});
        }
        return [$selected, $submitted];
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
