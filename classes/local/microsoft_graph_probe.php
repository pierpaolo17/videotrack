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

use core\oauth2\api;
use core\oauth2\client;
use core\oauth2\issuer;

/**
 * Explicit CLI probe using Moodle's existing Microsoft system OAuth account.
 *
 * Core may refresh and persist OAuth credentials. VideoTrack does not store
 * credentials, change scopes, write activity data or enable a new player source.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_graph_probe {
    /** @var \Closure Core client factory; injectable only by trusted PHP callers. */
    private \Closure $clientfactory;

    /**
     * Retain the core OAuth boundary, allowing tests to avoid real token exchanges.
     *
     * @param \Closure|null $clientfactory Factory accepting an issuer and returning a client or false.
     */
    public function __construct(?\Closure $clientfactory = null) {
        $this->clientfactory = $clientfactory ?? static function (issuer $issuer): client|false {
            return api::get_system_oauth_client($issuer);
        };
    }

    /**
     * Check readiness before allowing OAuth refresh or the bounded Graph GET.
     *
     * @param string $driveid Explicit drive id, never a URL.
     * @param string $itemid Optional video item id, never a path or URL.
     * @return array Redacted configuration, OAuth or Graph diagnostic.
     */
    public function run(string $driveid, string $itemid = ''): array {
        $result = [
            'provider' => 'microsoft',
            'phase' => 'configuration',
            'status' => 'invalid_target',
            'graph_attempted' => false,
            'oauth_refresh_possible' => false,
            'playback_implemented' => false,
        ];
        try {
            microsoft_graph::resource_url($driveid, $itemid);
        } catch (\invalid_parameter_exception $error) {
            return $result;
        }
        try {
            $configuration = provider_connection::configuration_status('microsoft');
            $result['status'] = $configuration['status'];
            if ($configuration['status'] !== 'configuration_complete') {
                return $result;
            }
            $issuer = api::get_issuer($configuration['issuerid']);
            if (!self::trusted_endpoints($issuer)) {
                $result['status'] = 'oauth_endpoints_unsupported';
                return $result;
            }
            $result['phase'] = 'oauth';
            $result['status'] = 'oauth_unavailable';
            $result['oauth_refresh_possible'] = true;
            $client = ($this->clientfactory)($issuer);
            if (!($client instanceof client)) {
                return $result;
            }
            return array_replace($result, (new microsoft_graph($client))->inspect($driveid, $itemid));
        } catch (\Throwable $error) {
            $result['status'] = $result['phase'] === 'oauth' ? 'oauth_failed' : 'configuration_failed';
            return $result;
        }
    }

    /**
     * Refuse custom OAuth destinations before core can send credentials to them.
     *
     * This first public-cloud probe excludes national clouds and custom endpoints.
     * Readiness checks remain local and less restrictive for configuration repair.
     *
     * @param issuer $issuer Configured Microsoft service.
     * @return bool Both endpoints are supported Microsoft identity endpoints.
     */
    private static function trusted_endpoints(issuer $issuer): bool {
        foreach (['authorization' => 'authorize', 'token' => 'token'] as $name => $action) {
            $url = (string)$issuer->get_endpoint_url($name);
            $pattern = '~\Ahttps://login\.microsoftonline\.com/[A-Za-z0-9._-]+/oauth2/(?:v2\.0/)?' . $action . '\z~D';
            if (!preg_match($pattern, $url)) {
                return false;
            }
        }
        return true;
    }
}
