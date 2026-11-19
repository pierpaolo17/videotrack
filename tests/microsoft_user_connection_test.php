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

namespace mod_videotrack;

use core\oauth2\client;
use core\oauth2\endpoint;
use core\oauth2\issuer;
use mod_videotrack\local\microsoft_user_connection;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Personal core OAuth connection boundaries without a live provider.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_user_connection::class)]
final class microsoft_user_connection_test extends \advanced_testcase {
    /** @var string Original site URL. */
    private string $originalroot;

    /** @var array Original POST fields. */
    private array $originalpost;

    /** @var array Original GET fields. */
    private array $originalget;

    /** @var string|null Original HTTP method. */
    private ?string $originalmethod;

    /**
     * Isolate an administrator POST, with fake HTTPS configuration only.
     */
    protected function setUp(): void {
        global $CFG, $SESSION, $USER;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $USER->ignoresesskey = false;
        $this->originalroot = $CFG->wwwroot;
        $this->originalpost = $_POST;
        $this->originalget = $_GET;
        $this->originalmethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $CFG->wwwroot = 'https://moodle.example.org';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET = [];
        $_POST = ['sesskey' => sesskey()];
        unset($SESSION->mod_videotrack_msconnect);
    }

    /**
     * Restore global request data and discard only the synthetic request marker.
     */
    protected function tearDown(): void {
        global $CFG, $SESSION;

        $CFG->wwwroot = $this->originalroot;
        $_POST = $this->originalpost;
        $_GET = $this->originalget;
        if ($this->originalmethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->originalmethod;
        }
        unset($SESSION->mod_videotrack_msconnect);
        parent::tearDown();
    }

    /**
     * The real core factory builds a session-bound URL without contacting Microsoft.
     */
    public function test_default_factory_uses_core_login_and_a_private_request_marker(): void {
        global $SESSION, $USER, $DB;

        $issuer = $this->create_issuer();
        $service = new microsoft_user_connection();
        $url = $service->begin();
        $this->assertInstanceOf(\moodle_url::class, $url);
        $this->assertSame('connection_started', $service->status());
        $pending = $SESSION->mod_videotrack_msconnect;
        $this->assertSame(['userid', 'issuerid', 'expires', 'nonce'], array_keys($pending));
        $this->assertSame((int)$USER->id, $pending['userid']);
        $this->assertSame((int)$issuer->get('id'), $pending['issuerid']);
        $this->assertMatchesRegularExpression('/\A[0-9a-f]{64}\z/', $pending['nonce']);
        $this->assertGreaterThanOrEqual(time() + 595, $pending['expires']);
        $this->assertLessThanOrEqual(time() + 600, $pending['expires']);
        $state = new \moodle_url($url->get_param('state'));
        $this->assertSame('/mod/videotrack/provider_check.php', $state->get_path());
        $this->assertSame('1', (string)$state->get_param('oauthreturn'));
        $this->assertSame($pending['nonce'], $state->get_param('connectnonce'));
        $this->assertSame(sesskey(), $state->get_param('sesskey'));
        $this->assertSame(\oauth2_client::callback_url()->out(false), $url->get_param('redirect_uri'));
        $this->assertSame('code', $url->get_param('response_type'));
        $this->assertSame('client-id-marker', $url->get_param('client_id'));
        $this->assertSame('Files.Read offline_access', $url->get_param('scope'));
        $this->assertSame(0, $DB->count_records('oauth2_system_account'));
        $this->assertStringNotContainsString('client-secret-marker', $url->out(false));
        $this->assertSame('{}', json_encode($service));
    }

    /**
     * Even an administrator GET carrying a sesskey cannot start OAuth.
     */
    public function test_get_start_is_rejected_before_the_factory(): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->expectException(\invalid_parameter_exception::class);
        $this->inert_service()->begin();
    }

    /**
     * A POST with the wrong session proof cannot inspect the selected account.
     */
    public function test_bad_sesskey_is_rejected_before_the_factory(): void {
        $_POST['sesskey'] = 'wrong-session-marker';
        $this->expectException(\moodle_exception::class);
        $this->inert_service()->begin();
    }

    /**
     * A start cannot be used to redeem a supplied authorisation code.
     */
    public function test_start_rejects_injected_authorisation_responses(): void {
        $_POST['oauth2code'] = 'private-code-marker';
        $this->expectException(\invalid_parameter_exception::class);
        $this->inert_service()->begin();
    }

    /**
     * A regular authenticated user cannot start an administrative connection.
     */
    public function test_non_administrator_is_denied_before_configuration(): void {
        $this->setUser($this->getDataGenerator()->create_user());
        $this->expectException(\required_capability_exception::class);
        $this->inert_service()->begin();
    }

    /**
     * A guest cannot complete even an invalid request.
     */
    public function test_guest_completion_is_denied_before_pending_state(): void {
        $this->setGuestUser();
        $this->expectException(\required_capability_exception::class);
        $this->inert_service()->complete('missing');
    }

    /**
     * Missing and deleted issuer selections stay local.
     */
    public function test_missing_and_deleted_configuration_is_inert(): void {
        foreach ([0 => 'not_configured', 999999 => 'issuer_missing'] as $id => $status) {
            set_config('microsoftissuerid', $id, 'mod_videotrack');
            $service = $this->inert_service();
            $this->assertNull($service->begin());
            $this->assertSame($status, $service->status());
        }
    }

    /**
     * Service availability, provider type, credentials and HTTPS remain mandatory.
     */
    public function test_incomplete_configuration_cannot_construct_a_client(): void {
        global $CFG;

        $cases = [['enabled' => false], ['showonloginpage' => issuer::LOGINONLY],
            ['servicetype' => 'google'], ['clientsecret' => '']];
        foreach ($cases as $overrides) {
            $this->create_issuer($overrides);
            $service = $this->inert_service();
            $this->assertNull($service->begin());
            $this->assertSame('configuration_incomplete', $service->status());
        }
        $this->create_issuer();
        $CFG->wwwroot = 'http://moodle.example.org';
        $service = $this->inert_service();
        $this->assertNull($service->begin());
        $this->assertSame('configuration_incomplete', $service->status());
    }

    /**
     * Custom identity destinations are refused before core receives credentials.
     */
    public function test_untrusted_endpoint_cannot_construct_a_client(): void {
        global $DB;

        $issuer = $this->create_issuer();
        $DB->set_field('oauth2_endpoint', 'url', 'https://untrusted.example.org/token', [
            'issuerid' => $issuer->get('id'), 'name' => 'token_endpoint',
        ]);
        $service = $this->inert_service();
        $this->assertNull($service->begin());
        $this->assertSame('oauth_endpoints_unsupported', $service->status());
    }

    /**
     * An existing personal login needs no external redirect or pending marker.
     */
    public function test_already_connected_account_does_not_start_a_round_trip(): void {
        global $SESSION;

        $this->create_issuer();
        $service = $this->connected_service();
        $this->assertNull($service->begin());
        $this->assertSame('already_connected', $service->status());
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
    }

    /**
     * A matching callback is consumed before the personal client is used.
     */
    public function test_matching_completion_uses_personal_core_and_consumes_the_marker(): void {
        global $SESSION, $DB;

        $this->start();
        $pending = $SESSION->mod_videotrack_msconnect;
        $counts = [];
        $tables = ['videotrack', 'videotrack_seg', 'videotrack_state', 'videotrack_reactev',
            'videotrack_integrity', 'videotrack_acknowledge'];
        foreach ($tables as $table) {
            $counts[$table] = $DB->count_records($table);
        }
        $this->assertSame('connected', $this->connected_service()->complete($pending['nonce']));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
        foreach ($counts as $table => $count) {
            $this->assertSame($count, $DB->count_records($table));
        }
    }

    /**
     * A consumed response cannot call OAuth a second time.
     */
    public function test_completed_callback_cannot_be_replayed(): void {
        global $SESSION;

        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $this->assertSame('connected', $this->connected_service()->complete($nonce));
        $this->assertSame('connection_expired', $this->inert_service()->complete($nonce));
    }

    /**
     * A matching marker cannot bypass the callback's Moodle session proof.
     */
    public function test_callback_still_requires_a_valid_sesskey(): void {
        global $SESSION;

        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $_POST['sesskey'] = 'wrong-session-marker';
        $this->expectException(\moodle_exception::class);
        $this->inert_service()->complete($nonce);
    }

    /**
     * A mismatched nonce does not consume the legitimate pending request.
     */
    public function test_wrong_nonce_is_inert_and_preserves_the_legitimate_request(): void {
        global $SESSION;

        $this->start();
        $pending = $SESSION->mod_videotrack_msconnect;
        $this->assertSame('connection_expired', $this->inert_service()->complete('wrong'));
        $this->assertSame($pending, $SESSION->mod_videotrack_msconnect);
        $this->assertSame('connected', $this->connected_service()->complete($pending['nonce']));
    }

    /**
     * Expired, different-user and malformed markers cannot reach OAuth.
     */
    public function test_expired_or_different_user_markers_are_inert(): void {
        global $SESSION;

        $this->start();
        $pending = $SESSION->mod_videotrack_msconnect;
        $cases = [['expires' => time() - 1], ['userid' => $pending['userid'] + 1],
            ['expires' => 'invalid'], ['nonce' => []], ['issuerid' => 0], ['issuerid' => 'invalid']];
        foreach ($cases as $overrides) {
            $SESSION->mod_videotrack_msconnect = array_replace($pending, $overrides);
            $this->assertSame('connection_expired', $this->inert_service()->complete($pending['nonce']));
        }
    }

    /**
     * Provider denial consumes the request without calling the client factory.
     */
    public function test_denied_callback_never_redeems_or_refreshes_tokens(): void {
        global $SESSION;

        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $_GET['error'] = 'private-provider-error-marker';
        $this->assertSame('connection_cancelled', $this->inert_service()->complete($nonce));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
        $this->assertSame('connection_expired', $this->inert_service()->complete($nonce));
    }

    /**
     * An issuer changed during the round trip cannot receive the response.
     */
    public function test_changed_issuer_is_rejected_before_the_factory(): void {
        global $SESSION;

        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $this->create_issuer();
        $this->assertSame('configuration_changed', $this->inert_service()->complete($nonce));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
    }

    /**
     * Disabling the selected issuer invalidates the pending connection locally.
     */
    public function test_disabled_issuer_is_rechecked_on_return(): void {
        global $SESSION, $DB;

        $issuer = $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $DB->set_field('oauth2_issuer', 'enabled', 0, ['id' => $issuer->get('id')]);
        $this->assertSame('configuration_incomplete', $this->inert_service()->complete($nonce));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
    }

    /**
     * Exceptions are reduced and failed completions cannot be replayed.
     */
    public function test_oauth_exceptions_are_redacted(): void {
        global $SESSION;

        $this->create_issuer();
        $service = new microsoft_user_connection(static function (issuer $issuer, \moodle_url $url): client {
            throw new \RuntimeException('private-code-marker client-secret-marker');
        });
        $this->assertNull($service->begin());
        $this->assertSame('oauth_failed', $service->status());
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $this->assertSame('oauth_failed', $service->complete($nonce));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
    }

    /**
     * Modified core authorisation destinations and critical fields are refused.
     */
    public function test_login_url_protocol_fields_cannot_be_overridden(): void {
        global $SESSION;

        $this->create_issuer();
        foreach (['state', 'redirect_uri', 'client_id', 'response_type', 'host'] as $field) {
            $service = new microsoft_user_connection(function (issuer $issuer, \moodle_url $returnurl) use ($field): client {
                $url = (new client($issuer, $returnurl, '', false, true))->get_login_url();
                if ($field === 'host') {
                    $url = new \moodle_url('https://untrusted.example.org/authorize', $url->params());
                } else {
                    $url->param($field, 'unsafe-marker');
                }
                $mock = $this->response_client(false);
                $mock->method('get_login_url')->willReturn($url);
                return $mock;
            });
            $this->assertNull($service->begin());
            $this->assertSame('oauth_login_unsupported', $service->status());
            $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
        }
    }

    /**
     * A new explicit attempt replaces the old nonce, without changing identities.
     */
    public function test_new_attempt_invalidates_the_earlier_round_trip(): void {
        global $SESSION;

        $this->start();
        $first = $SESSION->mod_videotrack_msconnect;
        $this->assertNotNull((new microsoft_user_connection())->begin());
        $second = $SESSION->mod_videotrack_msconnect;
        $this->assertNotSame($first['nonce'], $second['nonce']);
        $this->assertSame('connection_expired', $this->inert_service()->complete($first['nonce']));
        $this->assertSame($second, $SESSION->mod_videotrack_msconnect);
    }

    /**
     * A valid callback still needs a connected core client; no fallback is used.
     */
    public function test_unconnected_callback_is_not_reported_as_success(): void {
        global $SESSION;

        $this->start();
        $nonce = $SESSION->mod_videotrack_msconnect['nonce'];
        $service = new microsoft_user_connection(fn(issuer $issuer, \moodle_url $url): client => $this->response_client(false));
        $this->assertSame('oauth_failed', $service->complete($nonce));
        $this->assertFalse(isset($SESSION->mod_videotrack_msconnect));
    }

    /**
     * Return a factory that must never be invoked.
     *
     * @return microsoft_user_connection Inert connection service.
     */
    private function inert_service(): microsoft_user_connection {
        return new microsoft_user_connection(function (issuer $issuer, \moodle_url $url): client {
            $this->fail('OAuth must not be reached.');
        });
    }

    /**
     * Provide one connected personal client without network methods.
     *
     * @return microsoft_user_connection Connected service fixture.
     */
    private function connected_service(): microsoft_user_connection {
        return new microsoft_user_connection(fn(issuer $issuer, \moodle_url $url): client => $this->response_client(true));
    }

    /**
     * Mock login state and forbid all resource/token HTTP calls.
     *
     * @param bool $connected Synthetic current-user login state.
     * @return client Core mock with no remote operations.
     */
    private function response_client(bool $connected): client {
        $mock = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['is_logged_in', 'get_login_url', 'get', 'post'])->getMock();
        $mock->expects($this->once())->method('is_logged_in')->willReturn($connected);
        $mock->expects($this->never())->method('get');
        $mock->expects($this->never())->method('post');
        return $mock;
    }

    /**
     * Create a supported selected issuer and begin a real core URL build.
     *
     * @return issuer Newly selected local fixture.
     */
    private function start(): issuer {
        $issuer = $this->create_issuer();
        $this->assertNotNull((new microsoft_user_connection())->begin());
        return $issuer;
    }

    /**
     * Create a fake local service, without a shared account or refresh token.
     *
     * @param array $overrides Optional issuer fixture overrides.
     * @return issuer Selected local Microsoft service.
     */
    private function create_issuer(array $overrides = []): issuer {
        $issuer = new issuer(0, (object)array_replace([
            'name' => 'VideoTrack connect fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'client-id-marker', 'clientsecret' => 'client-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
            'loginscopesoffline' => 'Files.Read offline_access',
        ], $overrides));
        $issuer->create();
        foreach (['authorization' => 'authorize', 'token' => 'token'] as $name => $action) {
            (new endpoint(0, (object)[
                'issuerid' => $issuer->get('id'), 'name' => $name . '_endpoint',
                'url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/' . $action,
            ]))->create();
        }
        set_config('microsoftissuerid', $issuer->get('id'), 'mod_videotrack');
        return $issuer;
    }
}
