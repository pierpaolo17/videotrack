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
 * Keep a temporary caller-only Microsoft preview separate from JSON diagnostics.
 *
 * Preview URLs may carry delegated access. They belong only to the requesting
 * administrator's response and must not be saved as activity or learner links.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_preview_result implements \JsonSerializable {
    /** @var int Maximum preview response accepted by the JSON parser. */
    private const MAX_RESPONSE_BYTES = 1048576;

    /** @var array Reduced metadata, without URLs, credentials or provider bodies. */
    private readonly array $diagnostic;

    /** @var string|null Opaque GET preview URL for this authorised response only. */
    private readonly ?string $url;

    /**
     * Retain only a supported public-cloud preview destination and safe diagnostics.
     *
     * @param array $diagnostic Internal redacted transport and video metadata.
     * @param string|null $url Candidate getUrl returned by the Graph preview POST.
     */
    public function __construct(array $diagnostic, ?string $url = null) {
        $diagnostic['playback_implemented'] = false;
        $diagnostic['preview_attempted'] = $diagnostic['preview_attempted'] ?? false;
        $safeurl = null;
        if ($url !== null) {
            if (self::is_supported_url($url)) {
                $diagnostic['status'] = 'preview_authentication_unverified';
                if (self::has_embedded_authorisation($url)) {
                    $safeurl = $url;
                    $diagnostic['status'] = 'preview_ready';
                }
            } else {
                $diagnostic['status'] = 'preview_url_unsupported';
            }
        }
        $this->url = $safeurl;
        $this->diagnostic = array_intersect_key($diagnostic, array_flip([
            'provider', 'account_context', 'phase', 'status', 'graph_attempted',
            'oauth_refresh_possible', 'playback_implemented', 'http_status',
            'mime_type', 'duration_seconds', 'size_bytes', 'preview_attempted',
        ]));
    }

    /**
     * Parse bounded preview JSON, keeping its opaque URL outside diagnostics.
     *
     * @param string|false $body Core curl response for the preview POST.
     * @param array $result Redacted transport and video metadata.
     * @return microsoft_preview_result GET-only preview; provider POST forms are unsupported.
     * @throws \JsonException If the provider response is malformed or exceeds the depth limit.
     */
    public static function from_response(string|false $body, array $result): microsoft_preview_result {
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
     * Return safe status and scalar metadata for the escaped diagnostic JSON.
     *
     * @return array Redacted result; preview readiness does not mean tracked playback.
     */
    public function diagnostic(): array {
        return $this->diagnostic;
    }

    /**
     * Return the caller-only opaque URL without rewriting its query parameters.
     *
     * @return string|null Validated temporary GET URL, never a durable identifier.
     */
    public function url(): ?string {
        return $this->url;
    }

    /**
     * Exclude the private URL from JSON serialisation of this object.
     *
     * @return array The same reduced diagnostic contract.
     */
    public function jsonSerialize(): array {
        return $this->diagnostic;
    }

    /**
     * Reject unsafe URI syntax before checking the supported origin and embed path.
     *
     * @param string $url Opaque provider GET URL, never fetched by Moodle.
     * @return bool Supported HTTPS embed destination.
     */
    private static function is_supported_url(string $url): bool {
        if ($url === '' || strlen($url) > 8192 || preg_match('/[\s\x00-\x1f\x7f\\\\]/', $url)) {
            return false;
        }
        $parts = parse_url($url);
        if (!is_array($parts) || !self::is_supported_origin($parts)) {
            return false;
        }
        $path = strtolower($parts['path'] ?? '');
        if (strtolower($parts['host']) === 'www.onedrive.com') {
            return $path === '/embed';
        }
        return str_ends_with($path, '/embed.aspx');
    }

    /**
     * Accept only the supported public-cloud authorities without URI credentials.
     *
     * @param array $parts Parsed preview URL.
     * @return bool SharePoint tenant or documented OneDrive preview host.
     */
    private static function is_supported_origin(array $parts): bool {
        if (
            strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || (isset($parts['port']) && $parts['port'] !== 443)
        ) {
            return false;
        }
        $host = strtolower($parts['host'] ?? '');
        return $host === 'www.onedrive.com'
            || preg_match('/\A(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+sharepoint\.com\z/D', $host) === 1;
    }

    /**
     * Recognise current credential-bearing links without assuming future URL authentication.
     *
     * A recognised key does not prove a credential is usable. Plain or unfamiliar
     * links need an authentication contract this diagnostic does not implement.
     *
     * @param string $url Supported opaque URL, never reconstructed.
     * @return bool A recognised non-empty embedded credential is present.
     */
    private static function has_embedded_authorisation(string $url): bool {
        $query = (string)parse_url($url, PHP_URL_QUERY);
        foreach (explode('&', str_replace(';', '&', $query)) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, '');
            $key = strtolower(rawurldecode($key));
            if ($value !== '' && in_array($key, ['tempauth', 'access_token', 'authkey', 'token', 'sig', 'signature'], true)) {
                return true;
            }
        }
        return false;
    }
}
