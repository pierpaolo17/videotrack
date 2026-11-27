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

use core\oauth2\api;

/**
 * Store an optional resource identity without activating Microsoft playback.
 *
 * Binding zero means that a restored identity needs an explicit local association.
 * No method acquires an OAuth client, requests media or grants provider access.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_activity_reference {
    /**
     * Read the current saved identity only after site and owning-course permissions are checked.
     *
     * The returned fields are trusted form context, never request-selected resource ids.
     * No OAuth client, provider request, configuration write or data repair is started.
     *
     * @param int $cmid VideoTrack course-module id, not an activity instance id.
     * @return array Current activity name and possibly empty or stale saved reference.
     */
    public static function diagnostic_context(int $cmid): array {
        global $DB;

        require_capability('moodle/site:config', \context_system::instance());
        if ($cmid <= 0) {
            throw new \invalid_parameter_exception('A VideoTrack course module is required.');
        }
        $cm = get_coursemodule_from_id('videotrack', $cmid, 0, false, MUST_EXIST);
        require_capability('moodle/course:manageactivities', \context_course::instance((int)$cm->course));
        $activity = $DB->get_record('videotrack', ['id' => $cm->instance, 'course' => $cm->course], '*', MUST_EXIST);
        return ['activityname' => (string)$activity->name, 'activityreference' => (string)($activity->microsoftreference ?? '')];
    }

    /**
     * Canonicalise a bounded stored identity; empty and null mean no reference.
     *
     * @param mixed $value Stored or submitted JSON.
     * @return string Canonical JSON or an empty string.
     */
    public static function canonical(mixed $value): string {
        if ($value === null || $value === '') {
            return '';
        }
        return self::encode(self::decode($value));
    }

    /**
     * Validate exact scalar fields before using the existing opaque-id grammar.
     *
     * @param mixed $value Untrusted JSON.
     * @return array Validated local identity, including a possibly unbound issuer.
     */
    private static function decode(mixed $value): array {
        if (!is_string($value) || strlen($value) > 4096) {
            throw new \invalid_parameter_exception('Invalid Microsoft activity reference.');
        }
        try {
            $data = json_decode($value, false, 4, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException $error) {
            throw new \invalid_parameter_exception('Invalid Microsoft activity reference.');
        }
        if (!($data instanceof \stdClass)) {
            throw new \invalid_parameter_exception('Invalid Microsoft activity reference.');
        }
        $fields = array_keys(get_object_vars($data));
        sort($fields);
        if (
            $fields !== ['driveid', 'issuerid', 'itemid', 'provider', 'version']
            || $data->version !== 1 || $data->provider !== 'microsoft'
            || !is_int($data->issuerid) || $data->issuerid < 0
            || !is_string($data->driveid) || !is_string($data->itemid) || $data->itemid === ''
        ) {
            throw new \invalid_parameter_exception('Invalid Microsoft activity reference.');
        }
        microsoft_graph::resource_url($data->driveid, $data->itemid);
        return [
            'version' => 1, 'provider' => 'microsoft', 'issuerid' => $data->issuerid,
            'driveid' => $data->driveid, 'itemid' => $data->itemid,
        ];
    }

    /**
     * Encode only fields produced by the local validators.
     *
     * @param array $identity Validated local identity.
     * @return string Stable storage representation.
     */
    private static function encode(array $identity): string {
        return json_encode($identity, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Export resource ids while discarding every local OAuth binding.
     *
     * @param mixed $value Stored identity.
     * @return array Portable backup fields, or no child for an empty reference.
     */
    public static function to_backup(mixed $value): array {
        if ($value === null || $value === '') {
            return [];
        }
        $identity = self::decode($value);
        unset($identity['issuerid']);
        return $identity;
    }

    /**
     * Restore a portable identity without guessing the destination OAuth service.
     *
     * @param array $data Scalar XML fields; XML numbers may arrive as strings.
     * @return string Canonical unbound local identity.
     */
    public static function from_backup(array $data): string {
        $fields = array_keys($data);
        sort($fields);
        if (
            $fields !== ['driveid', 'itemid', 'provider', 'version']
            || !in_array($data['version'], [1, '1'], true) || $data['provider'] !== 'microsoft'
        ) {
            throw new \invalid_parameter_exception('Invalid portable Microsoft activity reference.');
        }
        $data['version'] = 1;
        $data['issuerid'] = 0;
        return self::canonical(self::encode($data));
    }

    /**
     * Preserve sparse updates and require deliberate authorised changes or binding.
     *
     * @param \stdClass $data Activity data before its database write.
     * @param \stdClass|null $previous Existing record, or null during creation.
     */
    public static function process_form_fields(\stdClass $data, ?\stdClass $previous = null): void {
        $old = $previous->microsoftreference ?? null;
        $bind = !empty($data->microsoftreferencebind);
        if (!property_exists($data, 'microsoftreference')) {
            if ($bind) {
                throw new \invalid_parameter_exception('A Microsoft reference is required for explicit binding.');
            }
            $data->microsoftreference = $old;
            return;
        }
        $value = self::canonical($data->microsoftreference);
        if ($bind && $value === '') {
            throw new \invalid_parameter_exception('A Microsoft reference is required for explicit binding.');
        }
        if (!$bind && ($value === $old || ($value === '' && $old === null))) {
            $data->microsoftreference = $old;
            return;
        }
        $courseid = (int)($previous->course ?? $data->course ?? 0);
        if ($courseid <= 0) {
            throw new \invalid_parameter_exception('A course context is required for Microsoft reference changes.');
        }
        require_capability('moodle/course:manageactivities', \context_course::instance($courseid));
        if ($value === '') {
            $data->microsoftreference = '';
            return;
        }
        $identity = self::decode($value);
        $issuerid = self::selected_issuer();
        if ($bind) {
            $identity['issuerid'] = $issuerid;
        }
        if ($issuerid <= 0 || $identity['issuerid'] !== $issuerid) {
            throw new \invalid_parameter_exception('Microsoft activity reference service mismatch.');
        }
        $data->microsoftreference = self::encode($identity);
    }

    /**
     * Inspect the configured service locally without asking for consent or tokens.
     *
     * @return int Enabled selected Microsoft issuer, or zero if unavailable.
     */
    private static function selected_issuer(): int {
        $issuerid = (int)get_config('mod_videotrack', 'microsoftissuerid');
        if ($issuerid <= 0) {
            return 0;
        }
        try {
            $issuer = api::get_issuer($issuerid);
        } catch (\dml_missing_record_exception $error) {
            return 0;
        }
        return $issuer->get('enabled') && $issuer->get('servicetype') === 'microsoft' ? $issuerid : 0;
    }
}
