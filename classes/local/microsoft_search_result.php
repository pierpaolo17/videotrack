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
 * Separate private search candidates from serialisable diagnostic metadata.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_search_result implements \JsonSerializable {
    /** @var array Allowlisted diagnostic fields without resource names, ids or query text. */
    private readonly array $diagnostic;

    /** @var array Private normalised rows, never included in JSON serialisation. */
    private readonly array $items;

    /**
     * Keep diagnostics and trusted normalised candidate rows separate.
     *
     * @param array $diagnostic Internal reduced transport/OAuth status.
     * @param array $items Rows already validated by from_response().
     */
    public function __construct(array $diagnostic, array $items = []) {
        $diagnostic['playback_implemented'] = false;
        $this->diagnostic = array_intersect_key($diagnostic, array_flip([
            'provider', 'account_context', 'phase', 'status', 'graph_attempted',
            'oauth_refresh_possible', 'playback_implemented', 'http_status', 'returned_count', 'more_results',
        ]));
        $this->items = $items;
    }

    /**
     * Return redacted diagnostic fields only.
     *
     * @return array No names, ids, query, URLs or credentials.
     */
    public function diagnostic(): array {
        return $this->diagnostic;
    }

    /**
     * Return private candidates for the authorised administrator response.
     *
     * @return array Normalised video rows from one bounded provider page.
     */
    public function items(): array {
        return $this->items;
    }

    /**
     * Exclude private candidate rows from ordinary object JSON.
     *
     * @return array The same safe diagnostic contract.
     */
    public function jsonSerialize(): array {
        return $this->diagnostic;
    }

    /**
     * Parse one bounded metadata page, ignoring all provider continuation URLs.
     *
     * @param string|false $body Core curl response body.
     * @param string $driveid Expected explicit drive id.
     * @param array $diagnostic Reduced transport status.
     * @return self Private video candidates, never proof of fresh playback access.
     * @throws \JsonException If the provider JSON is malformed or too deeply nested.
     */
    public static function from_response(string|false $body, string $driveid, array $diagnostic): self {
        if (!is_string($body) || strlen($body) > 1048576) {
            return new self($diagnostic);
        }
        $data = json_decode($body, false, 16, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        if (!($data instanceof \stdClass) || !is_array($data->value ?? null)) {
            return new self($diagnostic);
        }
        if (count($data->value) > 20) {
            return new self($diagnostic);
        }
        $items = self::video_rows($data->value, $driveid);
        $diagnostic['status'] = 'search_complete';
        $diagnostic['returned_count'] = count($items);
        $diagnostic['more_results'] = is_string($data->{'@odata.nextLink'} ?? null) && $data->{'@odata.nextLink'} !== '';
        return new self($diagnostic, array_values($items));
    }

    /**
     * Retain unique video identities from the expected drive only.
     *
     * @param array $rows At most20provider entries.
     * @param string $driveid Expected drive.
     * @return array Rows indexed by opaque item id to avoid duplicate choices.
     */
    private static function video_rows(array $rows, string $driveid): array {
        $items = [];
        foreach ($rows as $row) {
            if (!($row instanceof \stdClass) || !self::local_video($row, $driveid)) {
                continue;
            }
            $item = self::video_row($row, $driveid);
            if ($item !== null && !isset($items[$item['itemid']])) {
                $items[$item['itemid']] = $item;
            }
        }
        return $items;
    }

    /**
     * Reject folders, deleted items, remote shortcuts and cross-drive matches.
     *
     * @param \stdClass $row Provider row.
     * @param string $driveid Expected drive id.
     * @return bool The item belongs directly to the requested drive.
     */
    private static function local_video(\stdClass $row, string $driveid): bool {
        return !isset($row->deleted) && !isset($row->folder) && !isset($row->remoteItem)
            && ($row->parentReference->driveId ?? null) === $driveid;
    }

    /**
     * Validate the name and resource identity without fetching video bytes.
     *
     * @param \stdClass $row Provider item.
     * @param string $driveid Expected drive.
     * @return array|null Safe display fields and ids, or null for an unusable row.
     */
    private static function video_row(\stdClass $row, string $driveid): ?array {
        $id = $row->id ?? null;
        $name = $row->name ?? null;
        if (!is_string($id) || $id === '' || !self::valid_name($name)) {
            return null;
        }
        try {
            microsoft_graph::resource_url($driveid, $id);
        } catch (\invalid_parameter_exception $error) {
            return null;
        }
        $fields = self::video_fields($row);
        if ($fields === null) {
            return null;
        }
        return ['driveid' => $driveid, 'itemid' => $id, 'name' => $name] + $fields;
    }

    /**
     * Bound private names and reject invalid text while leaving HTML escaping to output.
     *
     * @param mixed $name Provider name.
     * @return bool Non-empty bounded UTF-8 name without control characters.
     */
    private static function valid_name(mixed $name): bool {
        return is_string($name) && $name !== '' && strlen($name) <= 1024
            && preg_match('//u', $name) === 1 && !preg_match('/[\x00-\x1f\x7f]/', $name);
    }

    /**
     * Require usable video facets before offering a provisional reference.
     *
     * @param \stdClass $row Provider item.
     * @return array|null Normalised video metadata, or null.
     */
    private static function video_fields(\stdClass $row): ?array {
        if (!(($row->file ?? null) instanceof \stdClass) || !(($row->video ?? null) instanceof \stdClass)) {
            return null;
        }
        $mime = $row->file->mimeType ?? null;
        if (!is_string($mime) || !preg_match('~\Avideo/[A-Za-z0-9!#$&^_.+-]{1,80}\z~D', $mime)) {
            return null;
        }
        $duration = $row->video->duration ?? null;
        $size = $row->size ?? null;
        if (!is_int($duration) || $duration <= 0 || !is_int($size) || $size < 0) {
            return null;
        }
        return ['mime_type' => strtolower($mime), 'duration_seconds' => $duration / 1000, 'size_bytes' => $size];
    }
}
