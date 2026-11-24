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
 * Administrator Microsoft diagnostics using only the current user's core OAuth client.
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
        [$diagnostic] = $this->execute($driveid, $itemid, 'metadata');
        return (new microsoft_link_result($diagnostic))->diagnostic();
    }

    /**
     * Resolve a private provider page using only the current user's core account.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Required video item id.
     * @return microsoft_link_result Private page link and redacted diagnostics.
     */
    public function video_link(string $driveid, string $itemid): microsoft_link_result {
        [$diagnostic, $url] = $this->execute($driveid, $itemid, 'provider_link');
        return new microsoft_link_result($diagnostic, $url);
    }

    /**
     * Obtain a temporary preview solely for the current administrator's own use.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Required video item id.
     * @return microsoft_preview_result Private preview, without shared-account fallback.
     */
    public function video_preview(string $driveid, string $itemid): microsoft_preview_result {
        [$diagnostic, $url] = $this->execute($driveid, $itemid, 'provider_preview');
        return new microsoft_preview_result($diagnostic, $url);
    }

    /**
     * Apply the same permission, target and OAuth guards to each explicit action.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Optional metadata or required provider-link video item id.
     * @param string $action Internal action selected by a public method, never raw request data.
     * @return array Pair of reduced diagnostics and a private URL, or null.
     */
    private function execute(string $driveid, string $itemid, string $action): array {
        require_capability('moodle/site:config', \context_system::instance());
        $result = self::initial_result();
        if (!self::valid_target($driveid, $itemid, $action)) {
            return [$result, null];
        }
        [$result, $client] = $this->prepare_client($result);
        if ($client === null) {
            return [$result, null];
        }
        try {
            $graph = new microsoft_graph($client);
            [$diagnostic, $url] = self::inspect_resource($graph, $driveid, $itemid, $action);
            return [array_replace($result, $diagnostic), $url];
        } catch (\Throwable $error) {
            $result['status'] = 'oauth_failed';
            return [$result, null];
        }
    }

    /**
     * Search one explicit drive using the same current-user OAuth guards.
     *
     * @param string $driveid Explicit drive id.
     * @param string $query Bounded search text, never a URL or raw OData expression.
     * @return microsoft_search_result Private candidates and reduced diagnostics.
     */
    public function search_videos(string $driveid, string $query): microsoft_search_result {
        require_capability('moodle/site:config', \context_system::instance());
        $result = self::initial_result();
        try {
            microsoft_graph_search::resource_url($driveid, $query);
        } catch (\invalid_parameter_exception $error) {
            return new microsoft_search_result($result);
        }
        [$result, $client] = $this->prepare_client($result);
        if ($client === null) {
            return new microsoft_search_result($result);
        }
        $response = (new microsoft_graph_search($client))->search($driveid, $query);
        return new microsoft_search_result(array_replace($result, $response->diagnostic()), $response->items());
    }

    /**
     * Start each explicitly authorised action with the existing redacted contract.
     *
     * @return array Inert local diagnostic fields.
     */
    private static function initial_result(): array {
        return [
            'provider' => 'microsoft',
            'account_context' => 'current_user',
            'phase' => 'configuration',
            'status' => 'invalid_target',
            'graph_attempted' => false,
            'oauth_refresh_possible' => false,
            'playback_implemented' => false,
        ];
    }

    /**
     * Acquire only the current user's client after a public action checked capability and target.
     *
     * @param array $result Initial redacted diagnostic.
     * @return array Pair of diagnostic and connected client, or null on a guarded failure.
     */
    private function prepare_client(array $result): array {
        try {
            $configuration = provider_connection::user_configuration_status('microsoft');
            $result['status'] = $configuration['status'];
            if ($configuration['status'] !== 'configuration_complete') {
                return [$result, null];
            }
            $issuer = api::get_issuer($configuration['issuerid']);
            if (!provider_connection::microsoft_endpoints_supported($issuer)) {
                $result['status'] = 'oauth_endpoints_unsupported';
                return [$result, null];
            }
            $result['phase'] = 'oauth';
            $result['status'] = 'user_oauth_not_connected';
            $result['oauth_refresh_possible'] = true;
            $client = ($this->clientfactory)($issuer);
            if (!($client instanceof client) || !$client->is_logged_in()) {
                return [$result, null];
            }
            return [$result, $client];
        } catch (\Throwable $error) {
            $result['status'] = $result['phase'] === 'oauth' ? 'oauth_failed' : 'configuration_failed';
            return [$result, null];
        }
    }

    /**
     * Dispatch only internal actions after the current user's client is authenticated.
     *
     * @param microsoft_graph $graph Current-user Graph transport.
     * @param string $driveid Validated explicit drive id.
     * @param string $itemid Optional metadata or required video item id.
     * @param string $action Internal action selected by a public method.
     * @return array Pair of redacted diagnostics and an optional private URL.
     */
    private static function inspect_resource(microsoft_graph $graph, string $driveid, string $itemid, string $action): array {
        $response = match ($action) {
            'provider_link' => $graph->video_link($driveid, $itemid),
            'provider_preview' => $graph->video_preview($driveid, $itemid),
            default => new microsoft_link_result($graph->inspect($driveid, $itemid)),
        };
        return [$response->diagnostic(), $response->url()];
    }

    /**
     * Validate the target before inspecting configuration or starting OAuth.
     *
     * @param string $driveid Explicit drive id.
     * @param string $itemid Optional metadata or required page-link item id.
     * @param string $action Internal action selected by the public method.
     * @return bool Resource ids meet the selected action's contract.
     */
    private static function valid_target(string $driveid, string $itemid, string $action): bool {
        if ($action !== 'metadata' && $itemid === '') {
            return false;
        }
        try {
            microsoft_graph::resource_url($driveid, $itemid);
        } catch (\invalid_parameter_exception $error) {
            return false;
        }
        return true;
    }
}
