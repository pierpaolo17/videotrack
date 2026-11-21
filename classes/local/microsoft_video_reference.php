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
 * Explicit reusable video identity without temporary URLs or access credentials.
 *
 * A reference names a local core OAuth service and opaque drive/video ids. It is
 * not an access grant, a connected-user identity or a stored activity source.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_video_reference {
    /** @var array Validated identity; only encode() explicitly exposes its fields. */
    private readonly array $identity;

    /**
     * Build a video reference using the same opaque-id contract as Graph requests.
     *
     * @param int $issuerid Local Moodle OAuth service id.
     * @param string $driveid Explicit opaque drive id.
     * @param string $itemid Required opaque video item id.
     */
    public function __construct(int $issuerid, string $driveid, string $itemid) {
        if ($issuerid <= 0 || $itemid === '') {
            throw new \invalid_parameter_exception('Invalid Microsoft video reference.');
        }
        microsoft_graph::resource_url($driveid, $itemid);
        $this->identity = [
            'version' => 1,
            'provider' => 'microsoft',
            'issuerid' => $issuerid,
            'driveid' => $driveid,
            'itemid' => $itemid,
        ];
    }

    /**
     * Read a small versioned identity object, refusing all additional fields.
     *
     * @param mixed $reference Untrusted administrator input; only a JSON string is accepted.
     * @return self Locally validated identity; no configuration or network access occurs.
     */
    public static function from_json(mixed $reference): self {
        if (!is_string($reference) || strlen($reference) > 4096) {
            throw new \invalid_parameter_exception('Invalid Microsoft video reference.');
        }
        try {
            $data = json_decode($reference, false, 4, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (\JsonException $error) {
            throw new \invalid_parameter_exception('Invalid Microsoft video reference.');
        }
        if (!($data instanceof \stdClass) || !self::valid_payload($data)) {
            throw new \invalid_parameter_exception('Invalid Microsoft video reference.');
        }
        return new self($data->issuerid, $data->driveid, $data->itemid);
    }

    /**
     * Require the exact identity fields and scalar types before constructing a target.
     *
     * @param \stdClass $data Decoded JSON object.
     * @return bool Supported version, provider, fields and scalar types.
     */
    private static function valid_payload(\stdClass $data): bool {
        $fields = array_keys(get_object_vars($data));
        sort($fields);
        return $fields === ['driveid', 'issuerid', 'itemid', 'provider', 'version']
            && $data->version === 1 && $data->provider === 'microsoft'
            && is_int($data->issuerid) && is_string($data->driveid) && is_string($data->itemid);
    }

    /**
     * Refuse a reference after the selected local OAuth service has changed.
     *
     * @param int $issuerid Currently selected local service id.
     */
    public function assert_issuer(int $issuerid): void {
        if ($this->identity['issuerid'] !== $issuerid) {
            throw new \invalid_parameter_exception('Microsoft reference service mismatch.');
        }
    }

    /**
     * Supply explicit ids under the current personal account, never a shared fallback.
     *
     * @return array Existing probe fields; no URL, token or new operation is supplied.
     */
    public function target(): array {
        return [
            'accountcontext' => 'current_user',
            'driveid' => $this->identity['driveid'],
            'itemid' => $this->identity['itemid'],
        ];
    }

    /**
     * Explicitly expose the reusable identity for the protected administrator response.
     *
     * @return string Canonical JSON; this is not diagnostic JSON or a playback URL.
     */
    public function encode(): string {
        return json_encode($this->identity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Offer a reference only after a successful personal video check.
     *
     * @param int $issuerid Selected local OAuth service id.
     * @param \stdClass $target Validated form target.
     * @param array $diagnostic Reduced result of the same explicit probe.
     * @return self|null Reusable identity, without copying temporary result URLs.
     */
    public static function for_result(int $issuerid, \stdClass $target, array $diagnostic): ?self {
        if ($issuerid <= 0 || ($target->accountcontext ?? '') !== 'current_user' || ($target->itemid ?? '') === '') {
            return null;
        }
        if (
            ($diagnostic['account_context'] ?? '') !== 'current_user'
            || !in_array($diagnostic['status'] ?? '', ['video_accessible', 'provider_link_ready', 'preview_ready'], true)
        ) {
            return null;
        }
        return new self($issuerid, $target->driveid, $target->itemid);
    }
}
