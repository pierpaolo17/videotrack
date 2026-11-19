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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack\local;

use core\oauth2\api;
use core\oauth2\client;
use core\oauth2\issuer;

/**
 * Explicit administrator connection using the current user's core OAuth client.
 *
 * Only a short-lived, one-use request marker belongs to VideoTrack. Core owns
 * credentials, its callback and token handling; this service never calls Graph.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class microsoft_user_connection {
    /** Session property holding a request marker, never credentials. */
    private const SESSION_KEY = 'mod_videotrack_msconnect';

    /** @var \Closure Trusted current-user client factory. */
    private \Closure $clientfactory;

    /** @var string Reduced local outcome without exception or provider contents. */
    private string $status = 'not_started';

    /**
     * Reuse the public core API without requesting additional scopes.
     *
     * @param \Closure|null $clientfactory Factory accepting an issuer and fixed local return URL.
     */
    public function __construct(?\Closure $clientfactory = null) {
        $this->clientfactory = $clientfactory ?? static function (issuer $issuer, \moodle_url $returnurl): client {
            return api::get_user_oauth_client($issuer, $returnurl, '', true);
        };
    }

    /**
     * Begin only after an explicit authorised POST; retain no login URL.
     *
     * @return \moodle_url|null Core login URL, or null for a reduced local outcome.
     */
    public function begin(): ?\moodle_url {
        global $SESSION, $USER;

        require_capability('moodle/site:config', \context_system::instance());
        self::require_start_request();
        unset($SESSION->{self::SESSION_KEY});
        try {
            $issuer = $this->configured_issuer();
            if ($issuer === null) {
                return null;
            }
            $nonce = bin2hex(random_bytes(32));
            $returnurl = self::return_url($nonce);
            $client = ($this->clientfactory)($issuer, $returnurl);
            if (!($client instanceof client)) {
                $this->status = 'oauth_failed';
                return null;
            }
            if ($client->is_logged_in()) {
                $this->status = 'already_connected';
                return null;
            }
            $url = $client->get_login_url();
            if (!($url instanceof \moodle_url) || !self::supported_login($url, $issuer, $returnurl)) {
                $this->status = 'oauth_login_unsupported';
                return null;
            }
            $SESSION->{self::SESSION_KEY} = [
                'userid' => (int)$USER->id,
                'issuerid' => (int)$issuer->get('id'),
                'expires' => time() + 600,
                'nonce' => $nonce,
            ];
            $this->status = 'connection_started';
            return $url;
        } catch (\Throwable $error) {
            $this->status = 'oauth_failed';
            return null;
        }
    }

    /**
     * Consume a matching request before core handles the returned authorisation.
     *
     * @param string $nonce One-use marker returned through core's local state URL.
     * @param bool $denied Whether core returned a provider error; its contents are ignored.
     * @return string Reduced connection outcome, never evidence of video access.
     */
    public function complete(string $nonce, bool $denied = false): string {
        global $SESSION;

        require_capability('moodle/site:config', \context_system::instance());
        require_sesskey();
        $pending = $SESSION->{self::SESSION_KEY} ?? null;
        if (!self::matching_request($pending, $nonce)) {
            return $this->status = 'connection_expired';
        }
        unset($SESSION->{self::SESSION_KEY});
        if ($denied) {
            return $this->status = 'connection_cancelled';
        }
        try {
            $issuer = $this->configured_issuer($pending['issuerid']);
            if ($issuer === null) {
                return $this->status;
            }
            $client = ($this->clientfactory)($issuer, self::return_url($nonce));
            $connected = $client instanceof client && $client->is_logged_in();
            return $this->status = $connected ? 'connected' : 'oauth_failed';
        } catch (\Throwable $error) {
            return $this->status = 'oauth_failed';
        }
    }

    /**
     * Return only the local outcome; login URLs and callback data are excluded.
     *
     * @return string Reduced status identifier.
     */
    public function status(): string {
        return $this->status;
    }

    /**
     * Refuse GET starts, missing CSRF proof and injected authorisation codes.
     */
    private static function require_start_request(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            throw new \invalid_parameter_exception('A POST request is required.');
        }
        require_sesskey();
        if (optional_param('oauth2code', '', PARAM_RAW) !== '') {
            throw new \invalid_parameter_exception('Unexpected authorisation response.');
        }
    }

    /**
     * Recheck the selected service and Microsoft destinations before any client.
     *
     * @param int $expectedissuerid Optional issuer bound to an existing request.
     * @return issuer|null Supported selected service, or null with a reduced status.
     */
    private function configured_issuer(int $expectedissuerid = 0): ?issuer {
        $configuration = provider_connection::user_configuration_status('microsoft');
        $this->status = $configuration['status'];
        if ($configuration['status'] !== 'configuration_complete') {
            return null;
        }
        if ($expectedissuerid !== 0 && $configuration['issuerid'] !== $expectedissuerid) {
            $this->status = 'configuration_changed';
            return null;
        }
        $issuer = api::get_issuer($configuration['issuerid']);
        if (!provider_connection::microsoft_endpoints_supported($issuer)) {
            $this->status = 'oauth_endpoints_unsupported';
            return null;
        }
        return $issuer;
    }

    /**
     * Bind the local return to this request and the current Moodle session.
     *
     * @param string $nonce Private random request marker.
     * @return \moodle_url Fixed local destination used by core's callback.
     */
    private static function return_url(string $nonce): \moodle_url {
        return new \moodle_url('/mod/videotrack/provider_check.php', [
            'oauthreturn' => 1,
            'connectnonce' => $nonce,
            'sesskey' => sesskey(),
        ]);
    }

    /**
     * Retain core's destination and protocol fields even with custom login params.
     *
     * @param \moodle_url $url Core-generated authorisation URL.
     * @param issuer $issuer Supported selected service.
     * @param \moodle_url $returnurl Fixed local destination with request proof.
     * @return bool Core's critical request fields have not been overridden.
     */
    private static function supported_login(\moodle_url $url, issuer $issuer, \moodle_url $returnurl): bool {
        $authorization = new \moodle_url($issuer->get_endpoint_url('authorization'));
        return $url->compare($authorization, URL_MATCH_BASE)
            && $url->get_param('state') === $returnurl->out_as_local_url(false)
            && $url->get_param('redirect_uri') === \oauth2_client::callback_url()->out(false)
            && $url->get_param('client_id') === $issuer->get('clientid')
            && $url->get_param('response_type') === 'code';
    }

    /**
     * Reject expired, different-user or mismatched responses without OAuth work.
     *
     * @param mixed $pending Local session marker, never a token.
     * @param string $nonce Returned request marker.
     * @return bool The marker belongs to this current user's unexpired request.
     */
    private static function matching_request($pending, string $nonce): bool {
        global $USER;

        return is_array($pending)
            && ($pending['userid'] ?? 0) === (int)$USER->id
            && is_int($pending['issuerid'] ?? null)
            && $pending['issuerid'] > 0
            && is_int($pending['expires'] ?? null)
            && $pending['expires'] >= time()
            && is_string($pending['nonce'] ?? null)
            && hash_equals($pending['nonce'], $nonce);
    }
}
