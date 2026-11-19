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
 * Administrator metadata probe using only the current user's core OAuth client.
 *
 * Core owns session and refresh tokens. No system-client fallback is permitted.
 * This diagnostic does not grant learner playback access or create a login flow.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_user_probe {
    /** @var \Closure Trusted injectable current-user client factory. */
    private \Closure $clientfactory;

    /**
     * Retain the public core API for the current user's selected OAuth service.
     *
     * @param \Closure|null $clientfactory Factory accepting an issuer and returning a client or false.
     */
    public function __construct(?\Closure $clientfactory = null) {
        $this->clientfactory = $clientfactory ?? static function (issuer $issuer): client {
            return api::get_user_oauth_client(
                $issuer,
                new \moodle_url('/mod/videotrack/provider_check.php'),
                '',
                true
            );
        };
    }

    /**
     * Inspect one explicit resource through the administrator's own Microsoft account.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Optional video item id.
     * @return array Redacted diagnostic; core may refresh the current user's tokens.
     */
    public function run(string $driveid, string $itemid = ''): array {
        require_capability('moodle/site:config', \context_system::instance());
        $result = [
            'provider' => 'microsoft',
            'account_context' => 'current_user',
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
            $configuration = provider_connection::user_configuration_status('microsoft');
            $result['status'] = $configuration['status'];
            if ($configuration['status'] !== 'configuration_complete') {
                return $result;
            }
            $issuer = api::get_issuer($configuration['issuerid']);
            if (!provider_connection::microsoft_endpoints_supported($issuer)) {
                $result['status'] = 'oauth_endpoints_unsupported';
                return $result;
            }
            $result['phase'] = 'oauth';
            $result['status'] = 'user_oauth_not_connected';
            $result['oauth_refresh_possible'] = true;
            $client = ($this->clientfactory)($issuer);
            if (!($client instanceof client) || !$client->is_logged_in()) {
                return $result;
            }
            return array_replace($result, (new microsoft_graph($client))->inspect($driveid, $itemid));
        } catch (\Throwable $error) {
            $result['status'] = $result['phase'] === 'oauth' ? 'oauth_failed' : 'configuration_failed';
            return $result;
        }
    }
}
