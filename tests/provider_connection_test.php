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

use advanced_testcase;
use core\oauth2\endpoint;
use core\oauth2\issuer;
use core\oauth2\system_account;
use mod_videotrack\local\provider_connection;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Local OAuth readiness and credential disclosure regressions.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(provider_connection::class)]
final class provider_connection_test extends advanced_testcase {
    /** @var string Original site root restored after each test. */
    private string $originalroot;

    /** Set a deterministic HTTPS site without contacting a provider. */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        global $CFG;
        $this->originalroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://moodle.example.org';
    }

    /** Restore the test runner's site root. */
    protected function tearDown(): void {
        global $CFG;
        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /** No configuration is a supported inert state. */
    public function test_unconfigured_connection_is_inert(): void {
        $status = provider_connection::configuration_status('microsoft');
        $this->assertSame('not_configured', $status['status']);
        $this->assertSame(0, $status['issuerid']);
        $this->assertSame([], $status['checks']);
        $this->assertFalse($status['network_checked']);
        $this->assertFalse($status['playback_implemented']);
    }

    /** Deleted service references remain diagnosable without repairing core data. */
    public function test_deleted_issuer_is_reported(): void {
        $issuer = $this->create_issuer();
        $id = (int)$issuer->get('id');
        $issuer->delete();
        $this->assertSame('issuer_missing', provider_connection::configuration_status('microsoft')['status']);
        $this->assertSame($id, (int)get_config('mod_videotrack', 'microsoftissuerid'));
    }

    /** Disabled services fail readiness but remain visible for diagnosis. */
    public function test_disabled_service_is_incomplete(): void {
        $issuer = $this->create_issuer(['enabled' => false]);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertSame('configuration_incomplete', $status['status']);
        $this->assertFalse($status['checks']['enabled']);
        $this->assertArrayHasKey((int)$issuer->get('id'), provider_connection::issuer_options('microsoft'));
    }

    /** Provider type prevents silently selecting an unrelated OAuth connection. */
    public function test_wrong_provider_type_is_rejected_by_readiness(): void {
        $issuer = $this->create_issuer(['servicetype' => 'google']);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertFalse($status['checks']['provider_type']);
        $this->assertSame('configuration_incomplete', $status['status']);
        $this->assertArrayNotHasKey((int)$issuer->get('id'), provider_connection::issuer_options('microsoft'));
    }

    /** Login-only and mail-only services cannot provide media service access. */
    public function test_service_usage_restrictions_are_respected(): void {
        $issuer = $this->create_issuer();
        foreach ([issuer::LOGINONLY, issuer::SMTPWITHXOAUTH2] as $usage) {
            $issuer->set('showonloginpage', $usage)->update();
            $status = provider_connection::configuration_status('microsoft');
            $this->assertFalse($status['checks']['available_for_services']);
            $this->assertSame('configuration_incomplete', $status['status']);
        }
    }

    /** Missing secrets are diagnosed without including their values in output. */
    public function test_missing_client_credentials_are_incomplete(): void {
        $this->create_issuer(['clientsecret' => '']);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertFalse($status['checks']['client_configured']);
        $this->assertSame('configuration_incomplete', $status['status']);
    }

    /** A user login alone does not provide a stored system account. */
    public function test_missing_system_account_is_incomplete(): void {
        global $DB;
        $issuer = $this->create_issuer();
        $DB->delete_records('oauth2_system_account', ['issuerid' => $issuer->get('id')]);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertFalse($status['checks']['system_account_configured']);
        $this->assertSame('configuration_incomplete', $status['status']);
    }

    /** HTTPS belongs to readiness rather than being inferred from an issuer name. */
    public function test_http_site_is_incomplete(): void {
        global $CFG;
        $this->create_issuer();
        $CFG->wwwroot = 'http://moodle.example.org';
        $status = provider_connection::configuration_status('microsoft');
        $this->assertFalse($status['site_https']);
        $this->assertSame('configuration_incomplete', $status['status']);
    }

    /** Missing and insecure endpoints cannot make a connection look complete. */
    public function test_endpoint_configuration_is_checked(): void {
        global $DB;
        $issuer = $this->create_issuer();
        $tokenendpoint = endpoint::get_record(['issuerid' => $issuer->get('id'), 'name' => 'token_endpoint']);
        // Model invalid persisted data; normal core editing already requires HTTPS.
        $DB->set_field('oauth2_endpoint', 'url', 'http://login.example.org/token', ['id' => $tokenendpoint->get('id')]);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertFalse($status['checks']['token_https']);
        $this->assertSame('configuration_incomplete', $status['status']);
        $tokenendpoint->delete();
        $this->assertFalse(provider_connection::configuration_status('microsoft')['checks']['token_https']);
    }

    /** Complete local configuration neither discloses secrets nor changes OAuth records. */
    public function test_complete_configuration_is_read_only_and_redacted(): void {
        global $DB;
        $issuer = $this->create_issuer();
        $before = $DB->get_record('oauth2_issuer', ['id' => $issuer->get('id')]);
        $account = $DB->get_record('oauth2_system_account', ['issuerid' => $issuer->get('id')]);
        $status = provider_connection::configuration_status('microsoft');
        $this->assertSame('configuration_complete', $status['status']);
        $this->assertNotContains(false, $status['checks']);
        $this->assertFalse($status['network_checked']);
        $this->assertFalse($status['playback_implemented']);
        $encoded = json_encode($status);
        foreach (['client-id-marker', 'client-secret-marker', 'refresh-marker', 'account@example.org'] as $secret) {
            $this->assertStringNotContainsString($secret, $encoded);
        }
        $this->assertEquals($before, $DB->get_record('oauth2_issuer', ['id' => $issuer->get('id')]));
        $this->assertEquals($account, $DB->get_record('oauth2_system_account', ['issuerid' => $issuer->get('id')]));
        $this->assertSame(
            [0 => get_string('none'), (int)$issuer->get('id') => 'VideoTrack fixture Microsoft'],
            provider_connection::issuer_options('microsoft')
        );
    }

    /** Unsupported provider keys cannot inspect arbitrary configuration. */
    public function test_unknown_provider_is_rejected(): void {
        $this->expectException(\invalid_parameter_exception::class);
        provider_connection::configuration_status('unknown');
    }

    /**
     * Create local core OAuth records without discovery or token exchange.
     *
     * @param array $overrides Issuer fixture overrides.
     * @return issuer Local issuer with fake credentials and a stored fake account.
     */
    private function create_issuer(array $overrides = []): issuer {
        $issuer = new issuer(0, (object)array_replace([
            'name' => 'VideoTrack fixture Microsoft',
            'servicetype' => 'microsoft',
            'clientid' => 'client-id-marker',
            'clientsecret' => 'client-secret-marker',
            'enabled' => true,
            'showonloginpage' => issuer::SERVICEONLY,
        ], $overrides));
        $issuer->create();
        foreach (['authorization', 'token'] as $name) {
            $endpoint = new endpoint(0, (object)[
                'issuerid' => $issuer->get('id'),
                'name' => $name . '_endpoint',
                'url' => 'https://login.example.org/' . $name,
            ]);
            $endpoint->create();
        }
        $account = new system_account(0, (object)[
            'issuerid' => $issuer->get('id'),
            'refreshtoken' => 'refresh-marker',
            'grantedscopes' => \core\oauth2\api::get_system_scopes_for_issuer($issuer),
            'email' => 'account@example.org',
            'username' => 'fixture-account',
        ]);
        $account->create();
        set_config('microsoftissuerid', $issuer->get('id'), 'mod_videotrack');
        return $issuer;
    }
}
