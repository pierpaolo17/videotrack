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

use core\oauth2\api;
use core\oauth2\client;
use core\oauth2\endpoint;
use core\oauth2\issuer;
use core\oauth2\system_account;
use mod_videotrack\form\microsoft_probe_form;
use mod_videotrack\local\microsoft_user_probe;
use mod_videotrack\local\provider_connection;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Current-user Microsoft diagnostics without live tokens or provider requests.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_user_probe::class)]
#[CoversClass(provider_connection::class)]
#[CoversClass(microsoft_probe_form::class)]
final class microsoft_user_probe_test extends \advanced_testcase {
    /** @var string Original runner URL restored after every case. */
    private string $originalroot;

    /**
     * Isolate an authorised HTTPS request without a real OAuth connection.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->originalroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://moodle.example.org';
    }

    /**
     * Restore the runner URL after successful and rejected probes.
     */
    protected function tearDown(): void {
        global $CFG;

        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * User readiness excludes only the shared-account gate, without remote work.
     */
    public function test_user_readiness_does_not_require_a_system_account(): void {
        $this->create_issuer();
        $system = provider_connection::configuration_status('microsoft');
        $user = provider_connection::user_configuration_status('microsoft');
        $this->assertSame('configuration_incomplete', $system['status']);
        $this->assertFalse($system['checks']['system_account_configured']);
        $this->assertSame('configuration_complete', $user['status']);
        $this->assertSame('current_user', $user['account_context']);
        $this->assertArrayNotHasKey('system_account_configured', $user['checks']);
        $this->assertFalse($user['network_checked']);
        $this->assertFalse($user['playback_implemented']);
        unset($system['checks']['system_account_configured']);
        $this->assertSame($system['checks'], $user['checks']);
    }

    /**
     * Disabled, login-only, wrong-provider and incomplete services still fail.
     */
    public function test_user_readiness_retains_service_guards(): void {
        $overrides = [
            ['enabled' => false],
            ['showonloginpage' => issuer::LOGINONLY],
            ['servicetype' => 'google'],
            ['clientsecret' => ''],
        ];
        foreach ($overrides as $override) {
            $this->create_issuer($override);
            $result = $this->inert_probe()->run('drive');
            $this->assertSame('configuration_incomplete', $result['status']);
            $this->assertFalse($result['graph_attempted']);
        }
    }

    /**
     * An HTTP Moodle site cannot obtain a delegated OAuth client.
     */
    public function test_http_site_is_rejected_before_oauth(): void {
        global $CFG;

        $this->create_issuer();
        $CFG->wwwroot = 'http://moodle.example.org';
        $result = $this->inert_probe()->run('drive');
        $this->assertSame('configuration_incomplete', $result['status']);
        $this->assertFalse($result['oauth_refresh_possible']);
    }

    /**
     * Missing or deleted issuer selections are local diagnostic results.
     */
    public function test_missing_and_deleted_issuers_are_inert(): void {
        unset_config('microsoftissuerid', 'mod_videotrack');
        $this->assertSame('not_configured', $this->inert_probe()->run('drive')['status']);
        set_config('microsoftissuerid', 999999, 'mod_videotrack');
        $this->assertSame('issuer_missing', $this->inert_probe()->run('drive')['status']);
    }

    /**
     * Invalid resource targets cannot cause token refresh or Graph requests.
     */
    public function test_invalid_targets_are_inert(): void {
        foreach ([['', ''], ['https://example.org', ''], ['drive', '../video']] as [$drive, $item]) {
            $result = $this->inert_probe()->run($drive, $item);
            $this->assertSame('invalid_target', $result['status']);
            $this->assertFalse($result['oauth_refresh_possible']);
            $this->assertFalse($result['graph_attempted']);
        }
    }

    /**
     * Unsupported HTTPS identity hosts are rejected before the user factory.
     */
    public function test_untrusted_identity_endpoint_is_inert(): void {
        global $DB;

        $issuer = $this->create_issuer();
        $DB->set_field('oauth2_endpoint', 'url', 'https://untrusted.example.org/token', [
            'issuerid' => $issuer->get('id'), 'name' => 'token_endpoint',
        ]);
        $result = $this->inert_probe()->run('drive');
        $this->assertSame('oauth_endpoints_unsupported', $result['status']);
        $this->assertFalse($result['oauth_refresh_possible']);
    }

    /**
     * A configured shared account cannot replace a missing personal client.
     */
    public function test_missing_personal_client_never_falls_back_to_system(): void {
        $issuer = $this->create_issuer();
        (new system_account(0, (object)[
            'issuerid' => $issuer->get('id'), 'refreshtoken' => 'system-refresh-marker',
            'grantedscopes' => api::get_system_scopes_for_issuer($issuer),
            'email' => 'system@example.org', 'username' => 'system-fixture',
        ]))->create();
        $calls = 0;
        $probe = new microsoft_user_probe(function (issuer $selected) use ($issuer, &$calls): false {
            $this->assertSame($issuer->get('id'), $selected->get('id'));
            $calls++;
            return false;
        });
        $result = $probe->run('drive');
        $this->assertSame(1, $calls);
        $this->assertSame('user_oauth_not_connected', $result['status']);
        $this->assertSame('current_user', $result['account_context']);
        $this->assertFalse($result['graph_attempted']);
    }

    /**
     * A client with no valid current-user session cannot send a metadata GET.
     */
    public function test_unconnected_user_never_queries_graph(): void {
        $this->create_issuer();
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['is_logged_in', 'get'])->getMock();
        $client->expects($this->once())->method('is_logged_in')->willReturn(false);
        $client->expects($this->never())->method('get');
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->run('drive');
        $this->assertSame('user_oauth_not_connected', $result['status']);
        $this->assertFalse($result['graph_attempted']);
        $this->assertTrue($result['oauth_refresh_possible']);
    }

    /**
     * The user's video metadata stays redacted and does not change activity data.
     */
    public function test_connected_user_video_metadata_is_redacted(): void {
        global $DB;

        $this->create_issuer();
        $tables = ['videotrack', 'videotrack_seg', 'videotrack_state', 'videotrack_react',
            'videotrack_reactev', 'videotrack_integrity', 'videotrack_acknowledge'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->count_records($table);
        }
        $body = json_encode([
            'id' => 'private-resource-marker', 'file' => ['mimeType' => 'video/mp4'],
            'video' => ['duration' => 5280000], 'size' => 1234, 'name' => 'private-file-marker',
            'webUrl' => 'https://private.example.org', 'access_token' => 'private-token-marker',
        ]);
        $client = $this->response_client($body, 200);
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->run('drive', 'video');
        $this->assertSame('video_accessible', $result['status']);
        $this->assertSame('current_user', $result['account_context']);
        $this->assertEquals(5280, $result['duration_seconds']);
        $this->assertFalse($result['playback_implemented']);
        $this->assertStringNotContainsString('private-', json_encode($result));
        foreach ($before as $table => $count) {
            $this->assertSame($count, $DB->count_records($table));
        }
    }

    /**
     * A denied delegated request returns once without retrying another identity.
     */
    public function test_graph_denial_does_not_retry_another_account(): void {
        $this->create_issuer();
        $client = $this->response_client('{"error":"private-token-marker"}', 403);
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->run('drive');
        $this->assertSame('graph_forbidden', $result['status']);
        $this->assertSame('current_user', $result['account_context']);
        $this->assertTrue($result['graph_attempted']);
        $this->assertStringNotContainsString('private-token-marker', json_encode($result));
    }

    /**
     * OAuth failures cannot expose credentials from a core exception.
     */
    public function test_oauth_exception_is_redacted(): void {
        $this->create_issuer();
        $probe = new microsoft_user_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('private-token-marker https://private.example.org');
        });
        $result = $probe->run('drive');
        $this->assertSame('oauth_failed', $result['status']);
        $this->assertFalse($result['graph_attempted']);
        $this->assertStringNotContainsString('private', json_encode($result));
    }

    /**
     * Course teacher rights do not authorise an administrator diagnostic.
     */
    public function test_course_teacher_is_rejected_before_configuration(): void {
        $teacher = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->expectException(\required_capability_exception::class);
        $this->inert_probe()->run('../invalid');
    }

    /**
     * Guests cannot inspect local provider status through the user probe.
     */
    public function test_guest_is_rejected_before_configuration(): void {
        $this->setGuestUser();
        $this->expectException(\required_capability_exception::class);
        $this->inert_probe()->run('../invalid');
    }

    /**
     * Account choice is explicit and unknown contexts fail form validation.
     */
    public function test_form_account_context_is_allowlisted(): void {
        $form = new microsoft_probe_form();
        foreach (['system', 'current_user'] as $context) {
            $this->assertSame([], $form->validation(['driveid' => 'drive', 'accountcontext' => $context], []));
        }
        foreach (['', 'other_user', 'system_fallback', 'CURRENT_USER'] as $context) {
            $this->assertSame(
                ['accountcontext'],
                array_keys($form->validation(['driveid' => 'drive', 'accountcontext' => $context], []))
            );
        }
    }

    /**
     * A submitted personal-account choice is retained in the validated payload.
     */
    public function test_personal_account_post_preserves_choice(): void {
        microsoft_probe_form::mock_submit(['driveid' => 'drive', 'accountcontext' => 'current_user']);
        $data = (new microsoft_probe_form())->get_data();
        $this->assertNotNull($data);
        $this->assertSame('current_user', $data->accountcontext);
    }

    /**
     * The default factory uses core's current-user API with a fixed local return URL.
     */
    public function test_default_factory_does_not_reference_system_credentials(): void {
        $source = file_get_contents(__DIR__ . '/../classes/local/microsoft_user_probe.php');
        $this->assertStringContainsString('api::get_user_oauth_client(', $source);
        $this->assertStringContainsString("new \\moodle_url('/mod/videotrack/provider_check.php')", $source);
        $forbidden = [
            'get_system_oauth_client', 'oauth2_refresh_token', 'oauth2_access_token', '->get_login_url(', 'redirect(',
        ];
        foreach ($forbidden as $name) {
            $this->assertStringNotContainsString($name, $source);
        }
        $this->assertLessThan(strpos($source, 'resource_url('), strpos($source, 'require_capability('));
        $this->assertLessThan(strpos($source, '($this->clientfactory)('), strpos($source, 'microsoft_endpoints_supported('));
        $this->assertLessThan(strpos($source, '->inspect('), strpos($source, '->is_logged_in()'));
    }

    /**
     * Return a probe whose OAuth boundary must never be crossed.
     *
     * @return microsoft_user_probe Inert diagnostic.
     */
    private function inert_probe(): microsoft_user_probe {
        return new microsoft_user_probe(function (issuer $issuer): client {
            $this->fail('The user OAuth factory must not be called.');
        });
    }

    /**
     * Mock a connected user client with exactly one bounded metadata response.
     *
     * @param string $body Synthetic Graph JSON.
     * @param int $code HTTP status.
     * @return client Core client mock, without token or network side effects.
     */
    private function response_client(string $body, int $code): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['is_logged_in', 'get', 'get_info', 'get_errno'])->getMock();
        $client->expects($this->once())->method('is_logged_in')->willReturn(true);
        $client->expects($this->once())->method('get')->willReturn($body);
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn(0);
        return $client;
    }

    /**
     * Create a selected local Microsoft issuer without a system account.
     *
     * @param array $overrides Local fixture properties.
     * @return issuer Service with fake credentials and supported endpoints.
     */
    private function create_issuer(array $overrides = []): issuer {
        $issuer = new issuer(0, (object)array_replace([
            'name' => 'VideoTrack user fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'client-id-marker', 'clientsecret' => 'client-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
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
