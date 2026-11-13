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
use core\oauth2\issuer;

/**
 * Read-only configuration boundary for authenticated media providers.
 *
 * Credentials remain in Moodle OAuth 2 services. Local configuration checks do
 * not refresh tokens, contact providers, or claim that remote playback works.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class provider_connection {
    /** Provider configuration references implemented by this foundation. */
    private const DEFINITIONS = [
        'microsoft' => ['setting' => 'microsoftissuerid', 'servicetype' => 'microsoft'],
    ];

    /**
     * Return existing OAuth services of the expected provider type.
     *
     * Disabled and incomplete services remain selectable so administrators can
     * inspect and repair them through Moodle's existing OAuth administration.
     *
     * @param string $provider Provider registry key.
     * @return array Issuer ids and names; zero means no service selected.
     */
    public static function issuer_options(string $provider): array {
        $definition = self::definition($provider);
        $options = [0 => get_string('none')];
        foreach (api::get_all_issuers() as $issuer) {
            if ((string)$issuer->get('servicetype') === $definition['servicetype']) {
                $options[(int)$issuer->get('id')] = (string)$issuer->get('name');
            }
        }
        return $options;
    }

    /**
     * Inspect stored configuration without exposing credentials or making HTTP calls.
     *
     * A stored system account is not evidence of a currently valid remote token.
     * Endpoint checks establish HTTPS syntax, not provider reachability or consent.
     *
     * @param string $provider Provider registry key.
     * @return array Local status and boolean checks safe for CLI diagnostics.
     */
    public static function configuration_status(string $provider): array {
        global $CFG;

        $definition = self::definition($provider);
        $issuerid = max(0, (int)get_config('mod_videotrack', $definition['setting']));
        $result = [
            'provider' => $provider,
            'issuerid' => $issuerid,
            'status' => 'not_configured',
            'site_https' => self::is_https_url((string)$CFG->wwwroot),
            'checks' => [],
            'network_checked' => false,
            'playback_implemented' => false,
        ];
        if ($issuerid === 0) {
            return $result;
        }
        try {
            $issuer = api::get_issuer($issuerid);
        } catch (\dml_missing_record_exception $error) {
            $result['status'] = 'issuer_missing';
            return $result;
        }

        $result['checks'] = [
            'provider_type' => (string)$issuer->get('servicetype') === $definition['servicetype'],
            'enabled' => (bool)$issuer->get('enabled'),
            'available_for_services' => in_array(
                (int)$issuer->get('showonloginpage'),
                [issuer::EVERYWHERE, issuer::SERVICEONLY],
                true
            ),
            'client_configured' => $issuer->is_configured(),
            'authorization_https' => self::is_https_url((string)$issuer->get_endpoint_url('authorization')),
            'token_https' => self::is_https_url((string)$issuer->get_endpoint_url('token')),
            'system_account_configured' => $issuer->is_system_account_connected(),
        ];
        $complete = $result['site_https'] && !in_array(false, $result['checks'], true);
        $result['status'] = $complete ? 'configuration_complete' : 'configuration_incomplete';
        return $result;
    }

    /**
     * Resolve the deliberately bounded provider registry.
     *
     * @param string $provider Provider key.
     * @return array Configuration reference and expected OAuth service type.
     */
    private static function definition(string $provider): array {
        if (!isset(self::DEFINITIONS[$provider])) {
            throw new \invalid_parameter_exception('Unsupported media provider.');
        }
        return self::DEFINITIONS[$provider];
    }

    /**
     * Check whether a configured absolute URL uses HTTPS.
     *
     * @param string $url Configured URL, never fetched here.
     * @return bool True for an absolute HTTPS URL without userinfo.
     */
    private static function is_https_url(string $url): bool {
        $parts = parse_url($url);
        return is_array($parts)
            && strtolower((string)($parts['scheme'] ?? '')) === 'https'
            && !empty($parts['host'])
            && !isset($parts['user']) && !isset($parts['pass']);
    }
}
