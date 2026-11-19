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
 * Separate a private provider-page link from serialisable diagnostics.
 *
 * Only public-cloud SharePoint/OneDrive for Business resource pages are accepted.
 * A browser link is not proof of that browser's account or of watched progress.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_link_result implements \JsonSerializable {
    /** @var array Safe diagnostic fields, without URLs or raw provider data. */
    private readonly array $diagnostic;

    /** @var string|null Validated private resource-page URL, never serialised. */
    private readonly ?string $url;

    /**
     * Retain a diagnostic and optionally validate a provider resource-page URL.
     *
     * @param array $diagnostic Internal diagnostic fields.
     * @param string|null $url Candidate webUrl from a validated Graph video item.
     */
    public function __construct(array $diagnostic, ?string $url = null) {
        $diagnostic['playback_implemented'] = false;
        $safeurl = null;
        if ($url !== null) {
            if (self::is_supported_url($url)) {
                $safeurl = $url;
                $diagnostic['status'] = 'provider_link_ready';
            } else {
                $diagnostic['status'] = 'provider_link_unsupported';
            }
        }
        $this->url = $safeurl;
        $this->diagnostic = array_intersect_key($diagnostic, array_flip([
            'provider', 'account_context', 'phase', 'status', 'graph_attempted',
            'oauth_refresh_possible', 'playback_implemented', 'http_status',
            'drive_type', 'mime_type', 'duration_seconds', 'size_bytes',
        ]));
    }

    /**
     * Return only the redacted diagnostic, suitable for escaped administrator JSON.
     *
     * @return array Safe diagnostic fields.
     */
    public function diagnostic(): array {
        return $this->diagnostic;
    }

    /**
     * Return the private page link for this authorised response only.
     *
     * @return string|null Validated opaque URL, without any URL reconstruction.
     */
    public function url(): ?string {
        return $this->url;
    }

    /**
     * Prevent accidental JSON serialisation from exposing the private URL.
     *
     * @return array Same reduced diagnostic contract.
     */
    public function jsonSerialize(): array {
        return $this->diagnostic;
    }

    /**
     * Restrict browser destinations and reject known bearer-link query parameters.
     *
     * The URL is never fetched by Moodle. This check is a destination boundary,
     * not a guarantee about every possible tenant authentication mechanism.
     *
     * @param string $url Opaque provider page URL.
     * @return bool Supported HTTPS resource page without unsafe URI components.
     */
    private static function is_supported_url(string $url): bool {
        if ($url === '' || strlen($url) > 8192 || preg_match('/[\s\x00-\x1f\x7f\\\\]/', $url)) {
            return false;
        }
        $parts = parse_url($url);
        if (!is_array($parts)) {
            return false;
        }
        return self::is_supported_origin($parts) && !self::has_bearer_query($parts['query'] ?? '');
    }

    /**
     * Restrict the destination authority to the supported Microsoft public cloud.
     *
     * @param array $parts Parsed URL components.
     * @return bool Supported HTTPS authority without credentials or fragment.
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
        return preg_match('/\A(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+sharepoint\.com\z/D', $host) === 1;
    }

    /**
     * Reject familiar bearer query keys without changing the opaque original URL.
     *
     * @param string $query Original encoded query string.
     * @return bool A recognised token-bearing query key exists.
     */
    private static function has_bearer_query(string $query): bool {
        foreach (explode('&', str_replace(';', '&', $query)) as $pair) {
            $key = strtolower(urldecode(explode('=', $pair, 2)[0]));
            if (in_array($key, ['access_token', 'tempauth', 'authkey', 'token', 'sig', 'signature'], true)) {
                return true;
            }
        }
        return false;
    }
}
