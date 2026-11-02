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

    /**
     * Returns site-level delimiter options stored as symbolic values.
     *
     * @return array Delimiter options keyed by symbolic value.
     */
    public static function delimiter_options(): array {
        return [
            self::DELIMITER_COMMA => get_string('setting:csvdelimiter_comma', 'mod_videotrack'),
            self::DELIMITER_SEMICOLON => get_string('setting:csvdelimiter_semicolon', 'mod_videotrack'),
            self::DELIMITER_SECTION => get_string('setting:csvdelimiter_section', 'mod_videotrack'),
            self::DELIMITER_HASH => get_string('setting:csvdelimiter_hash', 'mod_videotrack'),
            self::DELIMITER_PIPE => get_string('setting:csvdelimiter_pipe', 'mod_videotrack'),
        ];
    }

    /**
     * Returns activity-level delimiter options, including site-default inheritance.
     *
     * @return array Delimiter options keyed by symbolic value.
     */
    public static function delimiter_options_with_inherit(): array {
        return [
            self::DELIMITER_INHERIT => get_string('setting:csvdelimiter_inherit', 'mod_videotrack'),
            ...self::delimiter_options(),
        ];
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
     * @return array Export-field labels keyed by field name.
     */
    public static function field_options(?context $context = null): array {
        return csv_export_fields::field_options($context);
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
        return csv_export_fields::form_field_options($context);
    }

    /**
     * Returns the site default list of optional export fields.
     *
     * @return string[]
     */
    public static function site_default_fields(): array {
        return csv_export_fields::site_default_fields();
    }

    /**
     * Resolves activity fields, applying Moodle identity permissions.
     *
     * @param stdClass $videotrack Activity record.
     * @param context $context Module context.
     * @return string[]
     */
    public static function activity_fields(stdClass $videotrack, context $context): array {
        return csv_export_fields::activity_fields($videotrack, $context);
    }

    /**
     * Returns the deterministic form element name for an export field.
     *
     * @param string $field Export field key.
     * @return string
     */
    public static function form_element_name(string $field): string {
        return csv_export_fields::form_element_name($field);
    }

    /**
     * Aggregates submitted export-field checkboxes and removes helper fields.
     *
     * @param stdClass $data Submitted activity data.
     * @param context|null $context Context used to restrict identity fields, or null for trusted site-level processing.
     * @return void
     */
    public static function process_form_fields(stdClass $data, ?context $context = null): void {
        csv_export_fields::process_form_fields($data, $context);
    }

    /**
     * Returns only selected user-table/custom-profile keys.
     *
     * @param array $selected Selected export fields.
     * @return string[]
     */
    public static function selected_user_fields(array $selected): array {
        return csv_export_fields::selected_user_fields($selected);
    }

    /**
     * Loads users and selected visible identity fields in one query.
     *
     * @param array $userids User ids.
     * @param array $selected Selected export fields.
     * @return array User records keyed by id.
     */
    public static function load_users(array $userids, array $selected): array {
        return csv_export_fields::load_users($userids, $selected);
    }

    /**
     * Returns localised headers for custom teacher-report event columns.
     *
     * @param bool $overall Whether the export is the aggregate overall format.
     * @return string[]
     */
    public static function event_headers(bool $overall): array {
        $headers = [
            get_string('report:csvcol_eventtype', 'mod_videotrack'),
            get_string('report:reaction', 'mod_videotrack'),
            get_string('report:csvcol_comment', 'mod_videotrack'),
            get_string('report:timestamp', 'mod_videotrack'),
            get_string('report:csvcol_firsttimestamp', 'mod_videotrack'),
            get_string('report:csvcol_lasttimestamp', 'mod_videotrack'),
            get_string('report:csvcol_count', 'mod_videotrack'),
        ];
        $headers[] = get_string(
            $overall ? 'report:students' : 'report:csvcol_created',
            'mod_videotrack'
        );
        return $headers;
    }

    /**
     * Returns localised headers preceding event/state-specific columns.
     *
     * @param array $selected Selected optional fields.
     * @return string[]
     */
    public static function identity_headers(array $selected): array {
        return csv_export_identity::identity_headers($selected);
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
        return csv_export_identity::identity_values(
            $selected,
            $course,
            $videotrack,
            $user,
            $userlabel,
            $cmid,
            $context
        );
    }

    /**
     * Groups note events into time clusters for the overall CSV export.
     *
     * Notes are expected in ascending video-time order. Each note is wrapped
     * in braces so the concatenated cell remains unambiguous.
     *
     * @param iterable $notes Note records with userid, notetext and videotime.
     * @param int $windowseconds Cluster window in seconds.
     * @return array Note clusters.
     */
    public static function cluster_notes(iterable $notes, int $windowseconds): array {
        $windowseconds = max(0, $windowseconds);
        $clusters = [];
        $activeindex = null;

        foreach ($notes as $note) {
            $time = (float)$note->videotime;
            $matchesactivecluster = $activeindex !== null
                && ($time - (float)$clusters[$activeindex]['anchor']) <= $windowseconds;
            if ($matchesactivecluster) {
                $clusters[$activeindex]['count']++;
                $clusters[$activeindex]['students'][(int)$note->userid] = true;
                $clusters[$activeindex]['timesum'] += $time;
                $clusters[$activeindex]['first'] = min($clusters[$activeindex]['first'], $time);
                $clusters[$activeindex]['last'] = max($clusters[$activeindex]['last'], $time);
                $clusters[$activeindex]['comment'] .= '{' . (string)$note->notetext . '}';
                continue;
            }

            $clusters[] = [
                'anchor' => $time,
                'first' => $time,
                'last' => $time,
                'count' => 1,
                'students' => [(int)$note->userid => true],
                'timesum' => $time,
                'comment' => '{' . (string)$note->notetext . '}',
            ];
            $activeindex = count($clusters) - 1;
        }

        foreach ($clusters as &$cluster) {
            $cluster['students'] = count($cluster['students']);
            $cluster['timestamp'] = $cluster['timesum'] / $cluster['count'];
            unset($cluster['timesum']);
        }
        unset($cluster);

        return $clusters;
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
}
