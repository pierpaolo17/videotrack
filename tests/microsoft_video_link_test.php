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
use mod_videotrack\form\microsoft_probe_form;
use mod_videotrack\local\microsoft_graph;
use mod_videotrack\local\microsoft_link_result;
use mod_videotrack\local\microsoft_user_probe;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Original Microsoft page handoff without live accounts or external requests.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_link_result::class)]
#[CoversClass(microsoft_graph::class)]
#[CoversClass(microsoft_user_probe::class)]
#[CoversClass(microsoft_probe_form::class)]
final class microsoft_video_link_test extends \advanced_testcase {
    /** @var string Runner URL, restored after each test. */
    private string $originalroot;

    /**
     * Use an isolated administrator HTTPS context with no real credentials.
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
     * Restore the runner after both successful and rejected requests.
     */
    protected function tearDown(): void {
        global $CFG;

        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * Supported opaque page links preserve casing, encoding and query ordering.
     */
    public function test_supported_page_urls_are_not_rewritten(): void {
        $urls = [
            'https://tenant.sharepoint.com/sites/course/My%20video.mp4?web=1&name=a%2Bb',
            'HTTPS://Tenant-My.sharepoint.com:443/p/video?web=1&name=token',
        ];
        foreach ($urls as $url) {
            $result = new microsoft_link_result(['status' => 'video_accessible'], $url);
            $this->assertSame($url, $result->url());
            $this->assertSame('provider_link_ready', $result->diagnostic()['status']);
            $this->assertFalse($result->diagnostic()['playback_implemented']);
        }
    }

    /**
     * Browser destinations reject unsafe authorities and unsupported cloud hosts.
     */
    public function test_unsafe_page_destinations_are_rejected(): void {
        $urls = [
            '', 'http://tenant.sharepoint.com/v', 'javascript:alert(1)',
            'https://tenant.sharepoint.com.evil.example/v', 'https://sharepoint.com/v',
            'https://127.0.0.1/v', 'https://www.onedrive.com/embed', 'https://onedrive.live.com/v',
            'https://tenant.sharepoint.cn/v', 'https://tenant.sharepoint.com:444/v',
            'https://user:password@tenant.sharepoint.com/v', 'https://@tenant.sharepoint.com/v',
            'https://tenant.sharepoint.com/v#fragment', "https://tenant.sharepoint.com/v\n",
            'https://tenant.sharepoint.com\\@evil.example/v',
            'https://tenant.sharepoint.com/' . str_repeat('a', 8200),
        ];
        foreach ($urls as $url) {
            $result = new microsoft_link_result(['status' => 'video_accessible'], $url);
            $this->assertNull($result->url());
            $this->assertSame('provider_link_unsupported', $result->diagnostic()['status']);
        }
    }

    /**
     * Known credential-bearing query keys are rejected even when encoded.
     */
    public function test_bearer_query_parameters_are_rejected(): void {
        foreach (['tempauth=x', 'ACCESS_TOKEN=x', '%61uthkey=x', 'web=1;token=x', 'sig=x', 'signature=x'] as $query) {
            $result = new microsoft_link_result([], 'https://tenant.sharepoint.com/video?' . $query);
            $this->assertNull($result->url());
            $this->assertSame('provider_link_unsupported', $result->diagnostic()['status']);
        }
    }

    /**
     * Serialisation omits both the private URL and unknown provider fields.
     */
    public function test_serialisation_never_exposes_the_private_url(): void {
        $url = 'https://tenant.sharepoint.com/sites/private/video.mp4';
        $result = new microsoft_link_result([
            'status' => 'video_accessible', 'mime_type' => 'video/mp4',
            'webUrl' => $url, 'access_token' => 'private-token-marker',
            '@microsoft.graph.downloadUrl' => 'private-download-marker', 'playback_implemented' => true,
        ], $url);
        $this->assertSame($url, $result->url());
        $this->assertSame($result->diagnostic(), $result->jsonSerialize());
        $this->assertStringNotContainsString('private', json_encode($result));
        $this->assertFalse($result->diagnostic()['playback_implemented']);
    }

    /**
     * Resolving a page requests one fixed-host metadata resource and never content.
     */
    public function test_graph_link_uses_one_bounded_metadata_get(): void {
        $url = 'https://tenant.sharepoint.com/sites/private/video.mp4?web=1';
        $body = $this->video_body(['webUrl' => $url, 'name' => 'private-name-marker']);
        $client = $this->response_client($body);
        $result = (new microsoft_graph($client))->video_link('drive', 'video');
        $this->assertSame($url, $result->url());
        $this->assertSame('provider_link_ready', $result->diagnostic()['status']);
        $this->assertEquals(5280, $result->diagnostic()['duration_seconds']);
        $this->assertStringNotContainsString('private', json_encode($result));
    }

    /**
     * A missing item cannot accidentally request a drive as though it were a video.
     */
    public function test_graph_link_requires_a_video_item(): void {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $client->expects($this->never())->method('get');
        $this->expectException(\invalid_parameter_exception::class);
        (new microsoft_graph($client))->video_link('drive', '');
    }

    /**
     * Resource URLs and paths are not accepted as opaque identifiers.
     */
    public function test_graph_link_rejects_invalid_target_before_http(): void {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()->onlyMethods(['get'])->getMock();
        $client->expects($this->never())->method('get');
        $this->expectException(\invalid_parameter_exception::class);
        (new microsoft_graph($client))->video_link('drive', 'https://untrusted.example.org');
    }

    /**
     * Deleted, non-video and incomplete items cannot produce a page button.
     */
    public function test_graph_link_requires_valid_video_metadata(): void {
        $cases = [
            [$this->video_body(['deleted' => (object)[]]), 'graph_item_deleted'],
            [$this->video_body(['file' => null]), 'graph_not_video'],
            [$this->video_body(['video' => (object)[]]), 'graph_video_metadata_incomplete'],
        ];
        foreach ($cases as [$body, $expected]) {
            $result = (new microsoft_graph($this->response_client($body)))->video_link('drive', 'video');
            $this->assertSame($expected, $result->diagnostic()['status']);
            $this->assertNull($result->url());
        }
    }

    /**
     * Missing or non-string webUrl remains unavailable rather than an arbitrary link.
     */
    public function test_missing_or_non_string_web_url_is_unavailable(): void {
        foreach ([null, false, 7, ['url' => 'https://tenant.sharepoint.com/video']] as $value) {
            $body = $this->video_body(['webUrl' => $value]);
            $result = (new microsoft_graph($this->response_client($body)))->video_link('drive', 'video');
            $this->assertSame('provider_link_unavailable', $result->diagnostic()['status']);
            $this->assertNull($result->url());
        }
    }

    /**
     * A provider response cannot hand the browser an unsupported destination.
     */
    public function test_graph_link_validates_the_returned_destination(): void {
        $body = $this->video_body(['webUrl' => 'https://untrusted.example.org/private']);
        $result = (new microsoft_graph($this->response_client($body)))->video_link('drive', 'video');
        $this->assertSame('provider_link_unsupported', $result->diagnostic()['status']);
        $this->assertNull($result->url());
        $this->assertStringNotContainsString('untrusted', json_encode($result));
    }

    /**
     * Denials, redirects, throttling and service errors do not get followed or retried.
     */
    public function test_graph_errors_keep_the_existing_diagnostic_contract(): void {
        $cases = [
            403 => 'graph_forbidden', 404 => 'graph_not_found', 302 => 'graph_redirect_refused',
            429 => 'graph_throttled', 503 => 'graph_unavailable',
        ];
        foreach ($cases as $code => $expected) {
            $client = $this->response_client('{"error":"private-token-marker"}', $code);
            $result = (new microsoft_graph($client))->video_link('drive', 'video');
            $this->assertSame($expected, $result->diagnostic()['status']);
            $this->assertNull($result->url());
            $this->assertStringNotContainsString('private', json_encode($result));
        }
    }

    /**
     * Invalid, oversized and unsuccessful transport responses retain no URL.
     */
    public function test_invalid_and_oversized_graph_responses_are_rejected(): void {
        foreach (['not-json', '[]', '{"id":"x","padding":"' . str_repeat('a', 1048576) . '"}'] as $body) {
            $result = (new microsoft_graph($this->response_client($body)))->video_link('drive', 'video');
            $this->assertSame('graph_invalid_response', $result->diagnostic()['status']);
            $this->assertNull($result->url());
        }
        $result = (new microsoft_graph($this->response_client('', 0, 7)))->video_link('drive', 'video');
        $this->assertSame('graph_transport_failed', $result->diagnostic()['status']);
        $this->assertNull($result->url());
    }

    /**
     * Missing ids and absent configuration cannot cross the personal OAuth boundary.
     */
    public function test_personal_link_guards_are_inert(): void {
        $probe = new microsoft_user_probe(function (issuer $issuer): client {
            $this->fail('The OAuth factory must not run for an invalid or unconfigured link.');
        });
        $this->assertSame('invalid_target', $probe->video_link('drive', '')->diagnostic()['status']);
        $this->assertSame('invalid_target', $probe->video_link('../drive', 'video')->diagnostic()['status']);
        unset_config('microsoftissuerid', 'mod_videotrack');
        $this->assertSame('not_configured', $probe->video_link('drive', 'video')->diagnostic()['status']);
    }

    /**
     * A personal Graph denial stays denied rather than switching accounts.
     */
    public function test_personal_link_never_falls_back_after_a_denial(): void {
        $this->create_issuer();
        $client = $this->response_client('{"error":"private-token-marker"}', 403);
        $calls = 0;
        $probe = new microsoft_user_probe(function (issuer $issuer) use ($client, &$calls): client {
            $calls++;
            return $client;
        });
        $result = $probe->video_link('drive', 'video');
        $this->assertSame(1, $calls);
        $this->assertSame('current_user', $result->diagnostic()['account_context']);
        $this->assertSame('graph_forbidden', $result->diagnostic()['status']);
        $this->assertNull($result->url());
    }

    /**
     * A successful page resolution does not change plugin learner or configuration data.
     */
    public function test_personal_link_retains_activity_data_and_no_tracking_claim(): void {
        global $DB;

        $this->create_issuer();
        $tables = ['videotrack', 'videotrack_seg', 'videotrack_state', 'videotrack_react',
            'videotrack_reactev', 'videotrack_integrity', 'videotrack_acknowledge'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->count_records($table);
        }
        $url = 'https://tenant.sharepoint.com/sites/private/video.mp4?web=1';
        $client = $this->response_client($this->video_body(['webUrl' => $url]));
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->video_link('drive', 'video');
        $this->assertSame('provider_link_ready', $result->diagnostic()['status']);
        $this->assertSame('current_user', $result->diagnostic()['account_context']);
        $this->assertSame($url, $result->url());
        $this->assertFalse($result->diagnostic()['playback_implemented']);
        $this->assertStringNotContainsString('private', json_encode($result));
        foreach ($before as $table => $count) {
            $this->assertSame($count, $DB->count_records($table));
        }
    }

    /**
     * A course teacher is denied before link resolution.
     */
    public function test_non_administrators_cannot_resolve_provider_links(): void {
        $teacher = $this->getDataGenerator()->create_user();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->expectException(\required_capability_exception::class);
        (new microsoft_user_probe())->video_link('drive', 'video');
    }

    /**
     * Guests cannot query link readiness even with an invalid target.
     */
    public function test_guests_are_denied_before_target_validation(): void {
        $this->setGuestUser();
        $this->expectException(\required_capability_exception::class);
        (new microsoft_user_probe())->video_link('../drive', '');
    }

    /**
     * Core OAuth exceptions stay redacted in the link result and its JSON.
     */
    public function test_personal_oauth_failure_keeps_the_link_private(): void {
        $this->create_issuer();
        $probe = new microsoft_user_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('private-token-marker https://private.example.org');
        });
        $result = $probe->video_link('drive', 'video');
        $this->assertSame('oauth_failed', $result->diagnostic()['status']);
        $this->assertNull($result->url());
        $this->assertStringNotContainsString('private', json_encode($result));
    }

    /**
     * Provider-page actions require a personal account and an explicit item.
     */
    public function test_form_link_action_has_stricter_requirements(): void {
        $form = new microsoft_probe_form();
        $this->assertSame([], $form->validation(['driveid' => 'drive'], []));
        $errors = $form->validation(['driveid' => 'drive', 'operation' => 'provider_link'], []);
        $this->assertArrayHasKey('accountcontext', $errors);
        $this->assertArrayHasKey('itemid', $errors);
        $this->assertSame([], $form->validation([
            'driveid' => 'drive', 'itemid' => 'video',
            'accountcontext' => 'current_user', 'operation' => 'provider_link',
        ], []));
        $this->assertArrayHasKey('operation', $form->validation(['driveid' => 'drive', 'operation' => 'preview'], []));
    }

    /**
     * The original-page branch keeps its external link separate from diagnostic preview.
     */
    public function test_controller_keeps_the_handoff_separate_from_json_and_tracking(): void {
        $source = file_get_contents(__DIR__ . '/../provider_check.php');
        $this->assertLessThan(strpos($source, '$probe->video_link('), strpos($source, 'require_sesskey();'));
        $this->assertStringContainsString("if (\$data->accountcontext !== 'current_user')", $source);
        $this->assertStringContainsString('$result = $linkresult->diagnostic();', $source);
        $this->assertStringContainsString('$providerlink = $linkresult->url();', $source);
        $this->assertStringContainsString("'rel' => 'noopener noreferrer'", $source);
        $this->assertStringContainsString("'referrerpolicy' => 'no-referrer'", $source);
        $this->assertStringContainsString("'href' => \$providerlink", $source);
        $linkstart = strpos($source, 'if ($providerlink !== null) {');
        $previewstart = strpos($source, 'if ($previewurl !== null) {');
        $this->assertNotFalse($linkstart);
        $this->assertNotFalse($previewstart);
        $this->assertGreaterThan($linkstart, $previewstart);
        $this->assertStringNotContainsString('iframe', substr($source, $linkstart, $previewstart - $linkstart));
        $this->assertStringNotContainsString('videotrack_view(', $source);
    }

    /**
     * Create a supported fake service without a shared system account.
     *
     * @return issuer Local selected issuer, with no live credentials.
     */
    private function create_issuer(): issuer {
        $issuer = new issuer(0, (object)[
            'name' => 'VideoTrack page fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'client-id-marker', 'clientsecret' => 'client-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
        ]);
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

    /**
     * Produce valid video metadata with explicitly controlled returned fields.
     *
     * @param array $overrides Response property overrides.
     * @return string Synthetic JSON body.
     */
    private function video_body(array $overrides = []): string {
        return json_encode(array_replace([
            'id' => 'private-id-marker', 'file' => ['mimeType' => 'video/mp4'],
            'video' => ['duration' => 5280000], 'size' => 1234,
            'webUrl' => 'https://tenant.sharepoint.com/sites/private/video.mp4',
        ], $overrides), JSON_THROW_ON_ERROR);
    }

    /**
     * Require exactly one fixed-host request with unchanged transport restrictions.
     *
     * @param string $body Synthetic Graph response.
     * @param int $code HTTP status.
     * @param int $errno Curl error number.
     * @return client Connected core user client mock without network side effects.
     */
    private function response_client(string $body, int $code = 200, int $errno = 0): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'get_info', 'get_errno', 'is_logged_in'])->getMock();
        $client->method('is_logged_in')->willReturn(true);
        $client->expects($this->once())->method('get')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/video?$select=id,file,video,size,deleted,webUrl',
            [],
            [
                'CURLOPT_FOLLOWLOCATION' => false, 'CURLOPT_MAXREDIRS' => 0,
                'CURLOPT_CONNECTTIMEOUT' => 5, 'CURLOPT_TIMEOUT' => 15,
                'CURLOPT_SSL_VERIFYPEER' => true, 'CURLOPT_SSL_VERIFYHOST' => 2,
            ]
        )->willReturn($body);
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn($errno);
        return $client;
    }
}
