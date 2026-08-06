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

use context_module;
use moodle_exception;
use stdClass;

/**
 * Versioned learner acknowledgement helpers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class acknowledgement {
    /**
     * Return whether the current activity contains an enabled acknowledgement statement.
     *
     * @param stdClass $instance Activity instance.
     * @return bool
     */
    public static function is_enabled(stdClass $instance): bool {
        return !empty($instance->acknowledgementenabled)
            && self::has_visible_text((string)($instance->acknowledgementtext ?? ''));
    }

    /**
     * Return whether formatted content contains visible non-spacing text.
     *
     * @param string $text Formatted statement text.
     * @return bool
     */
    public static function has_visible_text(string $text): bool {
        $plain = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/[\p{Z}\s]+/u', '', $plain);
        return is_string($plain) && $plain !== '';
    }

    /**
     * Build the stable hash used to identify the current statement version.
     *
     * @param stdClass $instance Activity instance.
     * @return string SHA-256 hash.
     */
    public static function statement_hash(stdClass $instance): string {
        $format = (int)($instance->acknowledgementformat ?? FORMAT_HTML);
        $text = trim((string)($instance->acknowledgementtext ?? ''));
        return hash('sha256', $format . "\n" . $text);
    }

    /**
     * Return the current confirmation record for a user, when present.
     *
     * @param stdClass $instance Activity instance.
     * @param int $userid User id.
     * @return stdClass|null
     */
    public static function current_record(stdClass $instance, int $userid): ?stdClass {
        global $DB;

        if (!self::is_enabled($instance) || $userid <= 0) {
            return null;
        }
        $record = $DB->get_record('videotrack_acknowledge', [
            'videotrackid' => (int)$instance->id,
            'userid' => $userid,
            'statementhash' => self::statement_hash($instance),
        ]);
        return $record ?: null;
    }

    /**
     * Record the user's explicit confirmation of the current statement.
     *
     * Repeated submissions are idempotent and return the existing record.
     *
     * @param stdClass $instance Activity instance.
     * @param int $cmid Course module id.
     * @param int $userid User id.
     * @return stdClass Confirmation record.
     */
    public static function confirm(stdClass $instance, int $cmid, int $userid): stdClass {
        global $DB;

        if (!self::is_enabled($instance) || $userid <= 0 || $cmid <= 0) {
            throw new moodle_exception('acknowledgement:unavailable', 'mod_videotrack');
        }
        $existing = self::current_record($instance, $userid);
        if ($existing) {
            return $existing;
        }

        $now = time();
        $record = (object)[
            'videotrackid' => (int)$instance->id,
            'courseid' => (int)$instance->course,
            'cmid' => $cmid,
            'userid' => $userid,
            'statementhash' => self::statement_hash($instance),
            'instanceversion' => (int)($instance->timemodified ?? 0),
            'timeconfirmed' => $now,
        ];
        try {
            $record->id = $DB->insert_record('videotrack_acknowledge', $record);
        } catch (\dml_write_exception $exception) {
            // A parallel submission may have inserted the same unique record first.
            $existing = self::current_record($instance, $userid);
            if (!$existing) {
                throw $exception;
            }
            return $existing;
        }

        $event = \mod_videotrack\event\acknowledgement_confirmed::create([
            'objectid' => (int)$record->id,
            'context' => context_module::instance($cmid),
            'userid' => $userid,
        ]);
        $event->add_record_snapshot('videotrack_acknowledge', $record);
        $event->trigger();

        return $record;
    }

    /**
     * Return current confirmations keyed by user id.
     *
     * @param stdClass $instance Activity instance.
     * @param int[] $userids Optional user restriction.
     * @return stdClass[]
     */
    public static function current_records(stdClass $instance, array $userids = []): array {
        global $DB;

        if (!self::is_enabled($instance)) {
            return [];
        }
        $params = [
            'videotrackid' => (int)$instance->id,
            'statementhash' => self::statement_hash($instance),
        ];
        $where = 'videotrackid = :videotrackid AND statementhash = :statementhash';
        if ($userids) {
            [$usersql, $userparams] = $DB->get_in_or_equal(
                array_map('intval', $userids),
                SQL_PARAMS_NAMED,
                'ackuser'
            );
            $where .= " AND userid {$usersql}";
            $params = array_merge($params, $userparams);
        }
        return $DB->get_records_select('videotrack_acknowledge', $where, $params, 'timeconfirmed ASC', '*');
    }
}
