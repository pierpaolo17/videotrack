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
use core\oauth2\api;
use core\oauth2\client;
use core\oauth2\endpoint;
use core\oauth2\issuer;
use core\oauth2\system_account;
use mod_videotrack\local\microsoft_graph;
use mod_videotrack\local\microsoft_graph_probe;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Authenticated metadata boundaries without Microsoft credentials or HTTP calls.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_graph::class)]
#[CoversClass(microsoft_graph_probe::class)]
final class microsoft_graph_test extends advanced_testcase {
    /** @var string Test runner's original site URL. */
    private string $originalroot;

    /**
     * Use a deterministic HTTPS fixture without changing the runner permanently.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        global $CFG;
        $this->originalroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://moodle.example.org';
    }

    /**
     * Restore the runner URL even after an assertion fails.
     */
    protected function tearDown(): void {
        global $CFG;
        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * Only explicit drive paths on the fixed Graph host can be constructed.
     */
    public function test_explicit_resource_paths_and_selects(): void {
        $this->assertSame(
            'https://graph.microsoft.com/v1.0/drives/b%21opaque-drive?$select=id,driveType',
            microsoft_graph::resource_url('b!opaque-drive')
        );
        $this->assertSame(
            'https://graph.microsoft.com/v1.0/drives/b%21opaque-drive/items/ITEM_123?$select=id,file,video,size,deleted',
            microsoft_graph::resource_url('b!opaque-drive', 'ITEM_123')
        );
    }

    /**
     * URLs, path traversal, query injection, controls and unbounded ids stay inert.
     */
    public function test_invalid_targets_never_obtain_an_oauth_client(): void {
        $probe = $this->inert_probe();
        foreach (['', '../other', 'https://example.org', 'a/b', 'a?x=1', 'a#x', 'a%2Fb', "a\nb", str_repeat('a', 257)] as $id) {
            $result = $probe->run($id);
            $this->assertSame('invalid_target', $result['status']);
            $this->assertFalse($result['oauth_refresh_possible']);
            $this->assertFalse($result['graph_attempted']);
            if ($id !== '') {
                $this->assertSame('invalid_target', $probe->run('drive', $id)['status']);
            }
        }
    }

    /**
     * An unselected provider must not initiate OAuth or provision a drive.
     */
    public function test_unconfigured_probe_is_inert(): void {
        $result = $this->inert_probe()->run('drive');
        $this->assertSame('not_configured', $result['status']);
        $this->assertSame('configuration', $result['phase']);
        $this->assertFalse($result['graph_attempted']);
        $this->assertFalse($result['oauth_refresh_possible']);
    }

    /**
     * Local site HTTPS is required even when the stored service is otherwise ready.
     */
    public function test_http_site_cannot_refresh_tokens(): void {
        global $CFG;
        $this->create_issuer();
        $CFG->wwwroot = 'http://moodle.example.org';
        $result = $this->inert_probe()->run('drive');
        $this->assertSame('configuration_incomplete', $result['status']);
        $this->assertFalse($result['oauth_refresh_possible']);
    }

    /**
     * Disabled, wrong-provider and login-only services cannot reach the factory.
     */
    public function test_incomplete_issuers_cannot_obtain_clients(): void {
        foreach ([['enabled' => false], ['servicetype' => 'google'], ['showonloginpage' => issuer::LOGINONLY]] as $changes) {
            $this->create_issuer($changes);
            $result = $this->inert_probe()->run('drive');
            $this->assertSame('configuration_incomplete', $result['status']);
            $this->assertFalse($result['graph_attempted']);
        }
    }

    /**
     * Untrusted endpoints in stored configuration cannot receive credentials.
     */
    public function test_untrusted_oauth_destinations_are_refused_before_factory(): void {
        global $DB;

        $issuer = $this->create_issuer();
        $token = endpoint::get_record(['issuerid' => $issuer->get('id'), 'name' => 'token_endpoint']);
        $cases = [
            ['https://login.example.org/common/oauth2/v2.0/token', 'oauth_endpoints_unsupported'],
            ['https://login.microsoftonline.com.evil.example/common/oauth2/v2.0/token', 'oauth_endpoints_unsupported'],
            ['https://login.microsoftonline.com@evil.example/common/oauth2/v2.0/token', 'configuration_incomplete'],
            ['https://login.microsoftonline.com/common/oauth2/v2.0/token?redirect=secret', 'oauth_endpoints_unsupported'],
            ['https://login.microsoftonline.com/common/oauth2/v2.0/token#secret', 'oauth_endpoints_unsupported'],
            ['https://login.microsoftonline.com/common/oauth2/v2.0/../token', 'oauth_endpoints_unsupported'],
            ['https://login.microsoftonline.com:444/common/oauth2/v2.0/token', 'oauth_endpoints_unsupported'],
        ];
        foreach ($cases as [$url, $expected]) {
            // Simulate stored/imported corruption: PARAM_URL rejects userinfo before persistent::update can save it.
            $DB->set_field('oauth2_endpoint', 'url', $url, ['id' => $token->get('id')]);
            $result = $this->inert_probe()->run('drive');
            $this->assertSame($expected, $result['status']);
            $this->assertFalse($result['oauth_refresh_possible']);
            $this->assertFalse($result['graph_attempted']);
        }
    }

    /**
     * Both authorization and token endpoints must belong to Microsoft identity.
     */
    public function test_untrusted_authorization_endpoint_is_refused(): void {
        $issuer = $this->create_issuer();
        $authorization = endpoint::get_record(['issuerid' => $issuer->get('id'), 'name' => 'authorization_endpoint']);
        $authorization->set('url', 'https://login.example.org/authorize')->update();
        $this->assertSame('oauth_endpoints_unsupported', $this->inert_probe()->run('drive')['status']);
    }

    /**
     * A failed core refresh is distinct from Graph access and never leaks secrets.
     */
    public function test_oauth_unavailable_is_not_graph_success(): void {
        $this->create_issuer();
        $probe = new microsoft_graph_probe(static function (issuer $issuer): false {
            return false;
        });
        $result = $probe->run('drive');
        $this->assertSame('oauth_unavailable', $result['status']);
        $this->assertSame('oauth', $result['phase']);
        $this->assertTrue($result['oauth_refresh_possible']);
        $this->assertFalse($result['graph_attempted']);
    }

    /**
     * Core exceptions cannot expose tokens, account data or endpoint responses.
     */
    public function test_oauth_exception_is_redacted(): void {
        $this->create_issuer();
        $probe = new microsoft_graph_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('secret-access-token account@example.org https://private.example/download');
        });
        $result = $probe->run('drive');
        $this->assertSame('oauth_failed', $result['status']);
        $this->assertFalse($result['graph_attempted']);
        $this->assertStringNotContainsString('secret', json_encode($result));
        $this->assertStringNotContainsString('example.org', json_encode($result));
    }

    /**
     * An authenticated client is acquired once and receives only the explicit target.
     */
    public function test_ready_probe_uses_core_client_without_activity_writes(): void {
        global $DB;
        $issuer = $this->create_issuer();
        $client = $this->response_client('{"id":"private-drive","driveType":"business"}');
        $calls = 0;
        $factory = function (issuer $selected) use ($issuer, $client, &$calls): client {
            $this->assertSame($issuer->get('id'), $selected->get('id'));
            $calls++;
            return $client;
        };
        $tables = ['videotrack', 'videotrack_state', 'videotrack_seg', 'videotrack_reactev', 'videotrack_acknowledge'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->count_records($table);
        }
        $result = (new microsoft_graph_probe($factory))->run('drive');
        $this->assertSame(1, $calls);
        $this->assertSame('drive_accessible', $result['status']);
        $this->assertSame('graph', $result['phase']);
        $this->assertTrue($result['graph_attempted']);
        $this->assertTrue($result['oauth_refresh_possible']);
        $this->assertFalse($result['playback_implemented']);
        foreach ($before as $table => $count) {
            $this->assertSame($count, $DB->count_records($table));
        }
    }

    /**
     * Documented drive types are returned without resource or owner identities.
     */
    public function test_drive_metadata_is_allowlisted(): void {
        foreach (['personal', 'business', 'documentLibrary'] as $type) {
            $body = json_encode([
                'id' => 'private-id', 'driveType' => $type, 'name' => 'private-name',
                'owner' => ['email' => 'private@example.org'], 'access_token' => 'private-token',
                'webUrl' => 'https://private.example.org',
            ]);
            $result = (new microsoft_graph($this->response_client($body)))->inspect('drive');
            $this->assertSame('drive_accessible', $result['status']);
            $this->assertSame($type, $result['drive_type']);
            $this->assertStringNotContainsString('private', json_encode($result));
        }
    }

    /**
     * Video duration is in milliseconds and download URLs never reach diagnostics.
     */
    public function test_video_metadata_conversion_and_redaction(): void {
        $body = json_encode([
            'id' => 'private-id', 'name' => 'private-name', 'size' => 123456,
            'file' => ['mimeType' => 'video/mp4'], 'video' => ['duration' => 5280123],
            '@microsoft.graph.downloadUrl' => 'https://private.example.org/download?token=private-token',
            'createdBy' => ['user' => ['email' => 'private@example.org']],
        ]);
        $result = (new microsoft_graph($this->response_client($body)))->inspect('drive', 'item');
        $this->assertSame('video_accessible', $result['status']);
        $this->assertEquals(5280.123, $result['duration_seconds']);
        $this->assertSame(123456, $result['size_bytes']);
        $this->assertSame('video/mp4', $result['mime_type']);
        $this->assertStringNotContainsString('private', json_encode($result));
        $this->assertSame([
            'phase', 'graph_attempted', 'playback_implemented', 'status', 'http_status',
            'mime_type', 'duration_seconds', 'size_bytes',
        ], array_keys($result));
    }

    /**
     * Metadata access uses one GET with TLS verification and no redirects or retries.
     */
    public function test_request_transport_policy(): void {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'get_info', 'get_errno'])->getMock();
        $client->expects($this->once())->method('get')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/item?$select=id,file,video,size,deleted',
            [],
            [
                'CURLOPT_FOLLOWLOCATION' => false,
                'CURLOPT_MAXREDIRS' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 5,
                'CURLOPT_TIMEOUT' => 15,
                'CURLOPT_SSL_VERIFYPEER' => true,
                'CURLOPT_SSL_VERIFYHOST' => 2,
            ]
        )->willReturn('{"id":"item","size":42,"file":{"mimeType":"video/mp4"},"video":{"duration":1000}}');
        $client->method('get_info')->willReturn(['http_code' => 200]);
        $client->method('get_errno')->willReturn(0);
        $this->assertSame('video_accessible', (new microsoft_graph($client))->inspect('drive', 'item')['status']);
    }

    /**
     * Refused, missing, throttled, redirected and unavailable resources stay distinct.
     */
    public function test_http_failures_do_not_parse_or_echo_provider_errors(): void {
        $cases = [
            401 => 'graph_unauthorised', 403 => 'graph_forbidden', 404 => 'graph_not_found',
            429 => 'graph_throttled', 302 => 'graph_redirect_refused', 307 => 'graph_redirect_refused',
            500 => 'graph_unavailable', 503 => 'graph_unavailable', 400 => 'graph_request_failed',
            0 => 'graph_request_failed',
        ];
        foreach ($cases as $code => $status) {
            $client = $this->response_client('{"error":"private-token private@example.org"}', $code);
            $result = (new microsoft_graph($client))->inspect('drive', 'item');
            $this->assertSame($status, $result['status']);
            $this->assertSame($code, $result['http_status']);
            $this->assertStringNotContainsString('private', json_encode($result));
        }
    }

    /**
     * Curl failures cannot be accepted as successful metadata, even with HTTP 200.
     */
    public function test_transport_errors_override_http_success(): void {
        $client = $this->response_client('{"id":"private","driveType":"business"}', 200, 28);
        $result = (new microsoft_graph($client))->inspect('drive');
        $this->assertSame('graph_transport_failed', $result['status']);
        $this->assertArrayNotHasKey('drive_type', $result);
    }

    /**
     * Request exceptions are redacted without attempting a second request.
     */
    public function test_transport_exception_is_redacted(): void {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $client->expects($this->once())->method('get')->willThrowException(new \RuntimeException('private-token'));
        $result = (new microsoft_graph($client))->inspect('drive');
        $this->assertSame('graph_transport_failed', $result['status']);
        $this->assertStringNotContainsString('private', json_encode($result));
    }

    /**
     * Invalid, oversized or unexpected JSON cannot pass as drive metadata.
     */
    public function test_invalid_responses_are_rejected(): void {
        $bodies = [
            false, '', '{', 'null', '[]', '"private"', '{"driveType":"business"}',
            '{"id":123,"driveType":"business"}', '{"id":"x","driveType":"unexpected"}',
            str_repeat(' ', 1048577),
        ];
        foreach ($bodies as $body) {
            $result = (new microsoft_graph($this->response_client($body)))->inspect('drive');
            $this->assertSame('graph_invalid_response', $result['status']);
            $this->assertArrayNotHasKey('drive_type', $result);
        }
    }

    /**
     * Deleted items, folders and non-video files cannot be reported as videos.
     */
    public function test_non_video_and_deleted_items_are_rejected(): void {
        $cases = [
            ['id' => 'x', 'deleted' => (object)[]],
            ['id' => 'x', 'folder' => (object)[]],
            ['id' => 'x', 'file' => ['mimeType' => 'application/pdf']],
            ['id' => 'x', 'file' => ['mimeType' => "video/mp4\nprivate"]],
        ];
        foreach ($cases as $index => $data) {
            $result = (new microsoft_graph($this->response_client(json_encode($data))))->inspect('drive', 'item');
            $this->assertSame($index === 0 ? 'graph_item_deleted' : 'graph_not_video', $result['status']);
            $this->assertArrayNotHasKey('duration_seconds', $result);
        }
    }

    /**
     * Missing or malformed duration/size facets never become invented metadata.
     */
    public function test_invalid_video_numbers_are_rejected(): void {
        foreach ([[null, 1], [0, 1], [-1, 1], ['5280000', 1], [1.5, 1], [5280000, -1], [5280000, '2']] as $numbers) {
            [$duration, $size] = $numbers;
            $body = json_encode([
                'id' => 'x', 'file' => ['mimeType' => 'video/mp4'],
                'video' => ['duration' => $duration], 'size' => $size,
            ]);
            $result = (new microsoft_graph($this->response_client($body)))->inspect('drive', 'item');
            $this->assertSame('graph_video_metadata_incomplete', $result['status']);
            $this->assertArrayNotHasKey('duration_seconds', $result);
        }
    }

    /**
     * Build a probe whose factory fails the test if a readiness guard is bypassed.
     *
     * @return microsoft_graph_probe Probe that must remain locally inert.
     */
    private function inert_probe(): microsoft_graph_probe {
        return new microsoft_graph_probe(function (issuer $issuer): client {
            $this->fail('The OAuth client factory must not be called.');
        });
    }

    /**
     * Mock the real core client API and require exactly one metadata request.
     *
     * @param string|false $body Synthetic Graph response or transport failure.
     * @param int $code HTTP status.
     * @param int $errno Curl error number.
     * @return client Core client mock with no constructor or HTTP side effects.
     */
    private function response_client(string|false $body, int $code = 200, int $errno = 0): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'get_info', 'get_errno'])->getMock();
        $client->expects($this->once())->method('get')->willReturn($body);
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn($errno);
        return $client;
    }

    /**
     * Store a complete core OAuth fixture without discovery or real credentials.
     *
     * @param array $overrides Local issuer field overrides.
     * @return issuer Microsoft service with fake credentials and system account.
     */
    private function create_issuer(array $overrides = []): issuer {
        $issuer = new issuer(0, (object)array_replace([
            'name' => 'VideoTrack Graph fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'client-id-marker', 'clientsecret' => 'client-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
        ], $overrides));
        $issuer->create();
        foreach (['authorization' => 'authorize', 'token' => 'token'] as $name => $action) {
            $endpoint = new endpoint(0, (object)[
                'issuerid' => $issuer->get('id'), 'name' => $name . '_endpoint',
                'url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/' . $action,
            ]);
            $endpoint->create();
        }
        $account = new system_account(0, (object)[
            'issuerid' => $issuer->get('id'), 'refreshtoken' => 'refresh-marker',
            'grantedscopes' => api::get_system_scopes_for_issuer($issuer),
            'email' => 'account@example.org', 'username' => 'fixture-account',
        ]);
        $account->create();
        set_config('microsoftissuerid', $issuer->get('id'), 'mod_videotrack');
        return $issuer;
    }
}
