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

use core\oauth2\client;

/**
 * Bounded metadata and private preview requests against public Microsoft Graph.
 *
 * This service never requests file content. General diagnostics omit identities,
 * URLs, bodies and credentials; separate results retain private page/preview links.
 * It does not implement tracked playback.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_graph {
    /** @var int Maximum metadata response accepted by the JSON parser. */
    private const MAX_RESPONSE_BYTES = 1048576;

    /** @var client Authenticated core client; credentials remain owned by core. */
    private client $client;

    /**
     * Retain an authenticated core OAuth client, without copying its credentials.
     *
     * @param client $client Authenticated Moodle OAuth client.
     */
    public function __construct(client $client) {
        $this->client = $client;
    }

    /**
     * Build a fixed-host metadata URL from explicit opaque resource identifiers.
     *
     * Explicit drive ids avoid the auto-provisioning behaviour of GET /me/drive.
     * URLs, paths, shares and query strings are not accepted as identifiers.
     *
     * @param string $driveid Explicit drive identifier, never a URL.
     * @param string $itemid Optional item identifier, never a path or URL.
     * @return string Public-cloud Graph metadata URL.
     */
    public static function resource_url(string $driveid, string $itemid = ''): string {
        foreach ([$driveid, $itemid === '' ? $driveid : $itemid] as $identifier) {
            if (!preg_match('/\A[A-Za-z0-9!_-]{1,256}\z/D', $identifier)) {
                throw new \invalid_parameter_exception('Invalid Microsoft resource identifier.');
            }
        }
        $url = 'https://graph.microsoft.com/v1.0/drives/' . rawurlencode($driveid);
        if ($itemid === '') {
            return $url . '?$select=id,driveType';
        }
        return $url . '/items/' . rawurlencode($itemid) . '?$select=id,file,video,size,deleted';
    }

    /**
     * Perform one metadata GET and return only an allowlisted diagnostic result.
     *
     * No retries, redirects, content requests or per-activity writes occur here.
     * The parsing limit does not impose a streaming download limit on core curl.
     *
     * @param string $driveid Explicit drive identifier.
     * @param string $itemid Optional video item identifier.
     * @return array Redacted Graph status and validated scalar metadata.
     */
    public function inspect(string $driveid, string $itemid = ''): array {
        $url = self::resource_url($driveid, $itemid);
        [$result, $data] = $this->get_metadata($url);
        if ($data === null) {
            return $result;
        }
        return array_replace($result, $itemid === '' ? self::drive_metadata($data) : self::video_metadata($data));
    }

    /**
     * Resolve the original page of a validated video without requesting its bytes.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Required explicit video item id.
     * @return microsoft_link_result Private page link separated from diagnostics.
     */
    public function video_link(string $driveid, string $itemid): microsoft_link_result {
        if ($itemid === '') {
            throw new \invalid_parameter_exception('A video item id is required.');
        }
        $url = self::resource_url($driveid, $itemid) . ',webUrl';
        [$result, $data] = $this->get_metadata($url);
        if ($data === null) {
            return new microsoft_link_result($result);
        }
        $result = array_replace($result, self::video_metadata($data));
        if ($result['status'] !== 'video_accessible') {
            return new microsoft_link_result($result);
        }
        $result['status'] = 'provider_link_unavailable';
        return new microsoft_link_result($result, is_string($data->webUrl ?? null) ? $data->webUrl : null);
    }

    /**
     * Request a temporary native preview only after the same video passes metadata checks.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Required video item id.
     * @return microsoft_preview_result Private caller-only preview, never tracked playback.
     */
    public function video_preview(string $driveid, string $itemid): microsoft_preview_result {
        if ($itemid === '') {
            throw new \invalid_parameter_exception('A video item id is required.');
        }
        $result = $this->inspect($driveid, $itemid);
        $result['preview_attempted'] = false;
        if ($result['status'] !== 'video_accessible') {
            return new microsoft_preview_result($result);
        }
        $url = explode('?', self::resource_url($driveid, $itemid), 2)[0] . '/preview';
        return $this->request_preview($url, $result);
    }

    /**
     * Send one bounded JSON POST without redirects, retries or content requests.
     *
     * @param string $url Fixed-host preview endpoint built from validated opaque ids.
     * @param array $result Redacted successful video metadata.
     * @return microsoft_preview_result Private GET preview or reduced failure.
     */
    private function request_preview(string $url, array $result): microsoft_preview_result {
        $result['preview_attempted'] = true;
        $result['status'] = 'graph_transport_failed';
        $result['http_status'] = 0;
        try {
            $this->client->setHeader('Content-Type: application/json');
            $body = $this->client->post($url, '{}', [
                'CURLOPT_FOLLOWLOCATION' => false,
                'CURLOPT_MAXREDIRS' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 5,
                'CURLOPT_TIMEOUT' => 15,
                'CURLOPT_SSL_VERIFYPEER' => true,
                'CURLOPT_SSL_VERIFYHOST' => 2,
            ]);
            $info = $this->client->get_info();
            $result['http_status'] = (int)($info['http_code'] ?? 0);
            if ($this->client->get_errno() !== 0) {
                return new microsoft_preview_result($result);
            }
            if ($result['http_status'] !== 200) {
                $result['status'] = self::http_status($result['http_status']);
                return new microsoft_preview_result($result);
            }
            $result['status'] = 'graph_invalid_response';
            return self::preview_response($body, $result);
        } catch (\Throwable $error) {
            return new microsoft_preview_result($result);
        } finally {
            $this->client->resetHeader();
        }
    }

    /**
     * Parse bounded preview JSON, keeping its opaque URL outside diagnostics.
     *
     * @param string|false $body Core curl response for the preview POST.
     * @param array $result Redacted transport and video metadata.
     * @return microsoft_preview_result GET-only preview; provider POST forms are unsupported.
     */
    private static function preview_response(string|false $body, array $result): microsoft_preview_result {
        if (!is_string($body) || strlen($body) > self::MAX_RESPONSE_BYTES) {
            return new microsoft_preview_result($result);
        }
        $data = json_decode($body, false, 16, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        if (!($data instanceof \stdClass)) {
            return new microsoft_preview_result($result);
        }
        $result['status'] = 'preview_get_unavailable';
        if (!isset($data->getUrl)) {
            return new microsoft_preview_result($result);
        }
        if (!is_string($data->getUrl)) {
            $result['status'] = 'graph_invalid_response';
            return new microsoft_preview_result($result);
        }
        return new microsoft_preview_result($result, $data->getUrl);
    }

    /**
     * Read bounded Graph metadata and keep raw data private to this service.
     *
     * @param string $url Fixed-host URL built by resource_url, with an optional webUrl selection.
     * @return array Pair of redacted transport diagnostic and decoded metadata or null.
     */
    private function get_metadata(string $url): array {
        $result = [
            'phase' => 'graph',
            'graph_attempted' => true,
            'playback_implemented' => false,
            'status' => 'graph_transport_failed',
            'http_status' => 0,
        ];
        try {
            $body = $this->client->get($url, [], [
                'CURLOPT_FOLLOWLOCATION' => false,
                'CURLOPT_MAXREDIRS' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 5,
                'CURLOPT_TIMEOUT' => 15,
                'CURLOPT_SSL_VERIFYPEER' => true,
                'CURLOPT_SSL_VERIFYHOST' => 2,
            ]);
            $info = $this->client->get_info();
            $result['http_status'] = (int)($info['http_code'] ?? 0);
            if ($this->client->get_errno() !== 0) {
                return [$result, null];
            }
            if ($result['http_status'] !== 200) {
                $result['status'] = self::http_status($result['http_status']);
                return [$result, null];
            }
            $result['status'] = 'graph_invalid_response';
            $data = self::decode_metadata($body);
            return [$result, $data];
        } catch (\Throwable $error) {
            // Core exceptions and provider error bodies may contain credentials or private URLs.
            return [$result, null];
        }
    }

    /**
     * Decode bounded metadata and require a non-empty string resource identity.
     *
     * @param string|false $body Core curl response, retained only during this request.
     * @return \stdClass|null Decoded object, or null for an unusable response.
     */
    private static function decode_metadata(string|false $body): ?\stdClass {
        if (!is_string($body) || strlen($body) > self::MAX_RESPONSE_BYTES) {
            return null;
        }
        $data = json_decode($body, false, 16, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        if (!($data instanceof \stdClass) || !isset($data->id) || !is_string($data->id) || $data->id === '') {
            return null;
        }
        return $data;
    }

    /**
     * Classify unsuccessful HTTP responses without inspecting their private bodies.
     *
     * @param int $code HTTP status, or zero when no HTTP response was received.
     * @return string Stable diagnostic code.
     */
    private static function http_status(int $code): string {
        return match (true) {
            $code === 401 => 'graph_unauthorised',
            $code === 403 => 'graph_forbidden',
            $code === 404 => 'graph_not_found',
            $code === 429 => 'graph_throttled',
            $code >= 300 && $code < 400 => 'graph_redirect_refused',
            $code >= 500 && $code < 600 => 'graph_unavailable',
            default => 'graph_request_failed',
        };
    }

    /**
     * Select a documented drive type without exposing names, owners or identifiers.
     *
     * @param \stdClass $data Decoded drive metadata.
     * @return array Redacted drive result.
     */
    private static function drive_metadata(\stdClass $data): array {
        if (!in_array($data->driveType ?? null, ['personal', 'business', 'documentLibrary'], true)) {
            return ['status' => 'graph_invalid_response'];
        }
        return ['status' => 'drive_accessible', 'drive_type' => $data->driveType];
    }

    /**
     * Validate video facets and convert Microsoft's millisecond duration to seconds.
     *
     * @param \stdClass $data Decoded item metadata.
     * @return array Redacted video result; provider URLs and identities are discarded.
     */
    private static function video_metadata(\stdClass $data): array {
        if (isset($data->deleted)) {
            return ['status' => 'graph_item_deleted'];
        }
        $mime = self::video_mime_type($data);
        if ($mime === null) {
            return ['status' => 'graph_not_video'];
        }
        $duration = $data->video->duration ?? null;
        $size = $data->size ?? null;
        if (!is_int($duration) || $duration <= 0 || !is_int($size) || $size < 0) {
            return ['status' => 'graph_video_metadata_incomplete'];
        }
        return [
            'status' => 'video_accessible',
            'mime_type' => $mime,
            'duration_seconds' => $duration / 1000,
            'size_bytes' => $size,
        ];
    }

    /**
     * Validate the file facet and return only a normalised video MIME type.
     *
     * @param \stdClass $data Decoded item metadata.
     * @return string|null Allowed MIME type, or null for folders/non-video files.
     */
    private static function video_mime_type(\stdClass $data): ?string {
        if (!isset($data->file) || !($data->file instanceof \stdClass)) {
            return null;
        }
        $mime = $data->file->mimeType ?? null;
        if (!is_string($mime) || !preg_match('~\Avideo/[A-Za-z0-9!#$&^_.+-]{1,80}\z~D', $mime)) {
            return null;
        }
        return strtolower($mime);
    }
}
