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
 * One bounded metadata search in an explicit drive, never a content request.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_graph_search {
    /** @var client Authenticated current-user core client. */
    private client $client;

    /**
     * Retain core's authenticated client without copying credentials.
     *
     * @param client $client Current personal account client.
     */
    public function __construct(client $client) {
        $this->client = $client;
    }

    /**
     * Build a fixed-host, drive-scoped search with encoded OData string content.
     *
     * @param string $driveid Explicit opaque drive id.
     * @param string $query Trimmed UTF-8 text, two to100characters.
     * @return string Metadata endpoint, not a caller-supplied URL.
     */
    public static function resource_url(string $driveid, string $query): string {
        $driveurl = explode('?', microsoft_graph::resource_url($driveid), 2)[0];
        if (!self::valid_query($query)) {
            throw new \invalid_parameter_exception('Invalid Microsoft search query.');
        }
        $literal = rawurlencode(str_replace("'", "''", $query));
        return $driveurl . "/root/search(q='" . $literal . "')"
            . '?$select=id,name,file,folder,video,size,deleted,parentReference,remoteItem&$top=20';
    }

    /**
     * Validate bounded text before acquiring OAuth or sending HTTP.
     *
     * @param string $query Untrusted text.
     * @return bool Supported UTF-8 length without controls or surrounding whitespace.
     */
    private static function valid_query(string $query): bool {
        if (strlen($query) > 400 || preg_match('//u', $query) !== 1 || preg_match('/[\x00-\x1f\x7f]/', $query)) {
            return false;
        }
        $length = \core_text::strlen($query);
        return $query === trim($query) && $length >= 2 && $length <= 100;
    }

    /**
     * Fetch one provider result page without following its continuation or telemetry URLs.
     *
     * @param string $driveid Explicit drive id.
     * @param string $query Valid search text.
     * @return microsoft_search_result Private video candidates and redacted diagnostics.
     */
    public function search(string $driveid, string $query): microsoft_search_result {
        $url = self::resource_url($driveid, $query);
        $result = ['phase' => 'graph', 'status' => 'graph_transport_failed', 'graph_attempted' => true, 'http_status' => 0];
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
                return new microsoft_search_result($result);
            }
            if ($result['http_status'] !== 200) {
                $result['status'] = self::http_status($result['http_status']);
                return new microsoft_search_result($result);
            }
            $result['status'] = 'graph_invalid_response';
            return microsoft_search_result::from_response($body, $driveid, $result);
        } catch (\Throwable $error) {
            return new microsoft_search_result($result);
        }
    }

    /**
     * Retain established Graph failure categories without examining private bodies.
     *
     * @param int $code Provider status.
     * @return string Redacted status.
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
}
