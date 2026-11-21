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
use mod_videotrack\local\microsoft_graph;
use mod_videotrack\local\microsoft_link_result;
use mod_videotrack\local\microsoft_preview_result;
use mod_videotrack\local\microsoft_user_probe;
use mod_videotrack\privacy\provider as privacy_provider;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Private Microsoft preview boundaries with synthetic responses and no provider traffic.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_preview_result::class)]
#[CoversClass(microsoft_graph::class)]
#[CoversClass(microsoft_user_probe::class)]
#[CoversClass(microsoft_probe_form::class)]
#[CoversClass(privacy_provider::class)]
final class microsoft_preview_test extends \advanced_testcase {
    /** @var string Original runner URL, restored after every test. */
    private string $originalroot;

    /**
     * Use an isolated administrator HTTPS context without real credentials.
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
     * Restore the runner even after a rejected operation.
     */
    protected function tearDown(): void {
        global $CFG;

        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * Supported opaque preview URLs retain query bytes, casing and caller credentials.
     */
    public function test_preview_urls_are_preserved_only_in_the_private_accessor(): void {
        foreach ([
            'https://tenant.sharepoint.com/_layouts/15/embed.aspx?tempauth=private-marker&b=a%2Bb',
            'HTTPS://Tenant-My.sharepoint.com:443/sites/course/embed.aspx?access_token=private-marker',
            'https://www.onedrive.com/embed?token=private-marker',
        ] as $url) {
            $result = new microsoft_preview_result(['preview_attempted' => true], $url);
            $this->assertSame($url, $result->url());
            $this->assertSame('preview_ready', $result->diagnostic()['status']);
            $this->assertFalse($result->diagnostic()['playback_implemented']);
            $this->assertStringNotContainsString('private-marker', json_encode($result));
        }
    }

    /**
     * Unsafe URI components and unsupported hosts cannot become frame sources.
     */
    public function test_unsafe_preview_destinations_are_rejected(): void {
        foreach ([
            '', 'javascript:alert(1)', '//tenant.sharepoint.com/embed.aspx',
            'http://tenant.sharepoint.com/embed.aspx', 'https://127.0.0.1/embed.aspx',
            'https://tenant.sharepoint.com.evil.example/embed.aspx', 'https://sharepoint.com/embed.aspx',
            'https://tenant.sharepoint.cn/embed.aspx', 'https://onedrive.live.com/embed',
            'https://www.onedrive.com.evil.example/embed', 'https://tenant.sharepoint.com:444/embed.aspx',
            'https://user:password@tenant.sharepoint.com/embed.aspx',
            'https://@tenant.sharepoint.com/embed.aspx', 'https://tenant.sharepoint.com/embed.aspx#fragment',
            "https://tenant.sharepoint.com/embed.aspx\n", 'https://tenant.sharepoint.com\\@evil.example/embed.aspx',
            'https://tenant.sharepoint.com/embed.aspx?' . str_repeat('a', 8192),
        ] as $url) {
            $result = new microsoft_preview_result([], $url);
            $this->assertNull($result->url());
            $this->assertSame('preview_url_unsupported', $result->diagnostic()['status']);
        }
    }

    /**
     * Ordinary pages, download paths and provider POST forms are not GET previews.
     */
    public function test_only_supported_embed_paths_are_accepted(): void {
        foreach ([
            'https://tenant.sharepoint.com/video.mp4', 'https://tenant.sharepoint.com/',
            'https://tenant.sharepoint.com/embed.aspx/extra', 'https://tenant.sharepoint.com/download.aspx',
            'https://www.onedrive.com/embed_by_post', 'https://www.onedrive.com/redirect',
        ] as $url) {
            $this->assertNull((new microsoft_preview_result([], $url))->url());
        }
    }

    /**
     * Preview access is intentionally separate from the bearer-free original-page result.
     */
    public function test_preview_does_not_relax_the_existing_page_link_boundary(): void {
        $url = 'https://tenant.sharepoint.com/embed.aspx?tempauth=private-marker';
        $this->assertNull((new microsoft_link_result([], $url))->url());
        $this->assertSame($url, (new microsoft_preview_result([], $url))->url());
    }

    /**
     * Plain or unfamiliar authentication URLs cannot silently become unauthenticated frames.
     */
    public function test_unverified_preview_authentication_does_not_create_a_frame_url(): void {
        foreach ([
            'https://tenant.sharepoint.com/_layouts/15/embed.aspx?web=1',
            'https://www.onedrive.com/embed', 'https://www.onedrive.com/embed?token=',
            'https://www.onedrive.com/embed?unknown_auth=private-marker',
        ] as $url) {
            $result = new microsoft_preview_result([], $url);
            $this->assertSame('preview_authentication_unverified', $result->diagnostic()['status']);
            $this->assertNull($result->url());
        }
        $body = json_encode(['getUrl' => 'https://tenant.sharepoint.com/_layouts/15/embed.aspx?web=1']);
        $result = (new microsoft_graph($this->preview_client($body)))->video_preview('drive', 'video');
        $this->assertTrue($result->diagnostic()['preview_attempted']);
        $this->assertSame('preview_authentication_unverified', $result->diagnostic()['status']);
        $this->assertNull($result->url());
    }

    /**
     * Unknown response fields, raw URLs and token markers never enter diagnostic JSON.
     */
    public function test_preview_serialisation_is_allowlisted(): void {
        $url = 'https://www.onedrive.com/embed?token=private-marker';
        $result = new microsoft_preview_result([
            'provider' => 'microsoft', 'account_context' => 'current_user',
            'status' => 'video_accessible', 'mime_type' => 'video/mp4',
            'preview_attempted' => true, 'getUrl' => $url, 'postParameters' => 'private-marker',
            'access_token' => 'private-marker', 'playback_implemented' => true,
        ], $url);
        $this->assertSame($result->diagnostic(), $result->jsonSerialize());
        $this->assertStringNotContainsString('private-marker', json_encode($result));
        $this->assertStringNotContainsString('onedrive.com', json_encode($result));
        $this->assertTrue($result->diagnostic()['preview_attempted']);
        $this->assertFalse($result->diagnostic()['playback_implemented']);
    }

    /**
     * A validated video causes one fixed-host metadata GET and one bounded JSON POST.
     */
    public function test_graph_preview_uses_the_same_ids_and_bounded_json_post(): void {
        $url = 'https://tenant.sharepoint.com/embed.aspx?tempauth=private-marker';
        $result = (new microsoft_graph($this->preview_client(json_encode(['getUrl' => $url]))))
            ->video_preview('drive', 'video');
        $this->assertSame($url, $result->url());
        $this->assertSame('preview_ready', $result->diagnostic()['status']);
        $this->assertEquals(5280, $result->diagnostic()['duration_seconds']);
        $this->assertTrue($result->diagnostic()['preview_attempted']);
        $this->assertStringNotContainsString('private-marker', json_encode($result));
    }

    /**
     * A missing video id is rejected before any metadata or preview request.
     */
    public function test_graph_preview_requires_a_video_item(): void {
        $client = $this->idle_client();
        $this->expectException(\invalid_parameter_exception::class);
        (new microsoft_graph($client))->video_preview('drive', '');
    }

    /**
     * URLs cannot be passed in place of resource identifiers.
     */
    public function test_graph_preview_rejects_invalid_ids_before_http(): void {
        $client = $this->idle_client();
        $this->expectException(\invalid_parameter_exception::class);
        (new microsoft_graph($client))->video_preview('https://untrusted.example.org', 'video');
    }

    /**
     * Denied or unavailable metadata never creates a delegated preview URL.
     */
    public function test_unsuccessful_metadata_never_posts_a_preview(): void {
        foreach ([401, 403, 404, 429, 500] as $code) {
            $result = (new microsoft_graph($this->metadata_client('{"error":"private-marker"}', $code)))
                ->video_preview('drive', 'video');
            $this->assertNull($result->url());
            $this->assertFalse($result->diagnostic()['preview_attempted']);
            $this->assertStringNotContainsString('private-marker', json_encode($result));
        }
    }

    /**
     * Deleted items, non-video files and incomplete metadata cannot reach preview POST.
     */
    public function test_non_video_metadata_never_posts_a_preview(): void {
        foreach ([
            ['deleted' => (object)[]], ['file' => null], ['video' => ['duration' => 0]], ['size' => -1],
        ] as $overrides) {
            $result = (new microsoft_graph($this->metadata_client($this->video_body($overrides))))
                ->video_preview('drive', 'video');
            $this->assertNull($result->url());
            $this->assertFalse($result->diagnostic()['preview_attempted']);
        }
    }

    /**
     * Unusable metadata bodies stop before the preview operation.
     */
    public function test_invalid_metadata_body_never_posts_a_preview(): void {
        foreach ([false, '', '[]', '{', '{"id":"video"}'] as $body) {
            $result = (new microsoft_graph($this->metadata_client($body)))->video_preview('drive', 'video');
            $this->assertNull($result->url());
            $this->assertFalse($result->diagnostic()['preview_attempted']);
        }
    }

    /**
     * Preview HTTP failures and redirects are classified without following provider URLs.
     */
    public function test_preview_http_failures_are_redacted_and_not_retried(): void {
        foreach ([
            302 => 'graph_redirect_refused', 401 => 'graph_unauthorised', 403 => 'graph_forbidden',
            404 => 'graph_not_found', 429 => 'graph_throttled', 503 => 'graph_unavailable',
            400 => 'graph_request_failed',
        ] as $code => $status) {
            $result = (new microsoft_graph($this->preview_client('{"error":"private-marker"}', $code)))
                ->video_preview('drive', 'video');
            $this->assertSame($status, $result->diagnostic()['status']);
            $this->assertTrue($result->diagnostic()['preview_attempted']);
            $this->assertNull($result->url());
            $this->assertStringNotContainsString('private-marker', json_encode($result));
        }
    }

    /**
     * Curl errors and exceptions leave no private URL and restore temporary headers.
     */
    public function test_preview_transport_failures_are_reduced(): void {
        foreach ([
            $this->preview_client(false, 0, 28),
            $this->preview_client(new \RuntimeException('private-marker')),
        ] as $client) {
            $result = (new microsoft_graph($client))->video_preview('drive', 'video');
            $this->assertSame('graph_transport_failed', $result->diagnostic()['status']);
            $this->assertNull($result->url());
            $this->assertStringNotContainsString('private-marker', json_encode($result));
        }
    }

    /**
     * Invalid, oversized or excessively nested preview JSON cannot become a frame.
     */
    public function test_invalid_preview_responses_are_rejected(): void {
        foreach ([
            false, '', '{', '[]', 'null', '{"getUrl":7}', str_repeat('x', 1048577),
            str_repeat('{"x":', 20) . '{}' . str_repeat('}', 20),
        ] as $body) {
            $result = (new microsoft_graph($this->preview_client($body)))->video_preview('drive', 'video');
            $this->assertSame('graph_invalid_response', $result->diagnostic()['status']);
            $this->assertNull($result->url());
        }
    }

    /**
     * Provider POST-only previews are not submitted through an untrusted HTML form.
     */
    public function test_post_only_preview_is_reported_unavailable(): void {
        $body = json_encode(['postUrl' => 'https://www.onedrive.com/embed_by_post', 'postParameters' => 'private-marker']);
        $result = (new microsoft_graph($this->preview_client($body)))->video_preview('drive', 'video');
        $this->assertSame('preview_get_unavailable', $result->diagnostic()['status']);
        $this->assertNull($result->url());
        $this->assertStringNotContainsString('private-marker', json_encode($result));
    }

    /**
     * An otherwise successful API result still passes through destination validation.
     */
    public function test_graph_cannot_return_an_arbitrary_frame_source(): void {
        $body = json_encode(['getUrl' => 'https://untrusted.example.org/embed.aspx']);
        $result = (new microsoft_graph($this->preview_client($body)))->video_preview('drive', 'video');
        $this->assertSame('preview_url_unsupported', $result->diagnostic()['status']);
        $this->assertNull($result->url());
    }

    /**
     * Course teachers do not gain site-wide preview rights.
     */
    public function test_teacher_cannot_request_a_preview(): void {
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->expectException(\required_capability_exception::class);
        (new microsoft_user_probe())->video_preview('drive', 'video');
    }

    /**
     * Guest denial precedes target or configuration checks.
     */
    public function test_guest_cannot_request_preview_readiness(): void {
        $this->setGuestUser();
        $this->expectException(\required_capability_exception::class);
        (new microsoft_user_probe())->video_preview('/', '');
    }

    /**
     * Invalid preview targets do not construct even a user OAuth client.
     */
    public function test_personal_invalid_target_stops_before_oauth(): void {
        $calls = 0;
        $probe = new microsoft_user_probe(static function (issuer $issuer) use (&$calls): bool {
            $calls++;
            return false;
        });
        foreach ([['drive', ''], ['../drive', 'video'], ['drive', 'https://untrusted.example.org']] as [$drive, $item]) {
            $this->assertSame('invalid_target', $probe->video_preview($drive, $item)->diagnostic()['status']);
        }
        $this->assertSame(0, $calls);
    }

    /**
     * Missing local configuration does not start authentication or Graph traffic.
     */
    public function test_personal_preview_requires_local_configuration(): void {
        $calls = 0;
        $probe = new microsoft_user_probe(static function (issuer $issuer) use (&$calls): bool {
            $calls++;
            return false;
        });
        $result = $probe->video_preview('drive', 'video');
        $this->assertSame('not_configured', $result->diagnostic()['status']);
        $this->assertFalse($result->diagnostic()['graph_attempted']);
        $this->assertSame(0, $calls);
    }

    /**
     * Unsupported identity endpoints stop before the user client factory.
     */
    public function test_personal_preview_checks_identity_endpoints(): void {
        global $DB;

        $issuer = $this->create_issuer();
        $DB->set_field('oauth2_endpoint', 'url', 'https://untrusted.example.org/token', [
            'issuerid' => $issuer->get('id'), 'name' => 'token_endpoint',
        ]);
        $calls = 0;
        $probe = new microsoft_user_probe(static function (issuer $issuer) use (&$calls): bool {
            $calls++;
            return false;
        });
        $result = $probe->video_preview('drive', 'video');
        $this->assertSame('oauth_endpoints_unsupported', $result->diagnostic()['status']);
        $this->assertSame(0, $calls);
    }

    /**
     * A disconnected user cannot fall back to the configured shared identity.
     */
    public function test_disconnected_personal_account_has_no_preview_or_fallback(): void {
        $issuer = $this->create_issuer();
        (new system_account(0, (object)[
            'issuerid' => $issuer->get('id'), 'refreshtoken' => 'system-refresh-marker',
            'grantedscopes' => api::get_system_scopes_for_issuer($issuer),
            'email' => 'system@example.org', 'username' => 'system-fixture',
        ]))->create();
        $client = $this->idle_client();
        $client->method('is_logged_in')->willReturn(false);
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->video_preview('drive', 'video');
        $this->assertSame('user_oauth_not_connected', $result->diagnostic()['status']);
        $this->assertSame('current_user', $result->diagnostic()['account_context']);
        $this->assertFalse($result->diagnostic()['graph_attempted']);
        $this->assertNull($result->url());
    }

    /**
     * A successful personal preview retains existing activity and tracking data.
     */
    public function test_personal_preview_preserves_activity_data_and_identity(): void {
        global $DB;

        $this->create_issuer();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $tables = ['videotrack', 'videotrack_seg', 'videotrack_state', 'videotrack_react',
            'videotrack_reactev', 'videotrack_integrity', 'videotrack_acknowledge'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->get_records($table);
        }
        $client = $this->preview_client('{"getUrl":"https://www.onedrive.com/embed?token=private-marker"}');
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->video_preview('drive', 'video');
        $this->assertSame('preview_ready', $result->diagnostic()['status']);
        $this->assertSame('current_user', $result->diagnostic()['account_context']);
        $this->assertFalse($result->diagnostic()['playback_implemented']);
        foreach ($before as $table => $records) {
            $this->assertEquals($records, $DB->get_records($table));
        }
    }

    /**
     * OAuth failures do not expose provider exception details.
     */
    public function test_personal_preview_redacts_oauth_exceptions(): void {
        $this->create_issuer();
        $probe = new microsoft_user_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('private-marker');
        });
        $result = $probe->video_preview('drive', 'video');
        $this->assertSame('oauth_failed', $result->diagnostic()['status']);
        $this->assertNull($result->url());
        $this->assertStringNotContainsString('private-marker', json_encode($result));
    }

    /**
     * Preview form validation requires a personal account and a video item.
     */
    public function test_preview_form_has_explicit_personal_video_requirements(): void {
        $form = new microsoft_probe_form();
        $data = ['operation' => 'provider_preview', 'driveid' => 'drive'];
        $errors = $form->validation($data, []);
        $this->assertArrayHasKey('accountcontext', $errors);
        $this->assertSame(get_string('providercheck:previewitemrequired', 'mod_videotrack'), $errors['itemid']);
        $data['accountcontext'] = 'current_user';
        $data['itemid'] = 'video';
        $this->assertSame([], $form->validation($data, []));
        $data['itemid'] = 'https://untrusted.example.org';
        $this->assertArrayHasKey('itemid', $form->validation($data, []));
    }

    /**
     * A crafted GET cannot turn preview fields into a submitted form.
     */
    public function test_preview_cannot_be_submitted_by_get(): void {
        microsoft_probe_form::mock_submit([
            'operation' => 'provider_preview', 'accountcontext' => 'current_user',
            'driveid' => 'drive', 'itemid' => 'video',
        ], [], 'get');
        $form = new microsoft_probe_form(null, null, 'post');
        $this->assertFalse($form->is_submitted());
        $this->assertNull($form->get_data());
    }

    /**
     * Invalid sesskeys are rejected before preview target extraction.
     */
    public function test_preview_form_rejects_invalid_sesskey(): void {
        microsoft_probe_form::mock_submit([
            'operation' => 'provider_preview', 'accountcontext' => 'current_user',
            'driveid' => 'drive', 'itemid' => 'video',
        ]);
        $_POST['sesskey'] = 'invalid-session-marker';
        $_GET = [];
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        new microsoft_probe_form();
    }

    /**
     * The protected controller keeps URL rendering separate from diagnostics and tracking.
     */
    public function test_controller_preview_boundary_and_frame_attributes(): void {
        $source = file_get_contents(__DIR__ . '/../provider_check.php');
        $this->assertLessThan(strpos($source, '$probe->video_preview('), strpos($source, 'require_sesskey();'));
        $this->assertStringContainsString('$result = $previewresult->diagnostic();', $source);
        $this->assertStringContainsString('$previewurl = $previewresult->url();', $source);
        $this->assertStringContainsString('if ($previewurl !== null) {', $source);
        $this->assertStringContainsString("html_writer::tag('iframe', '', [", $source);
        $this->assertStringContainsString("'src' => \$previewurl", $source);
        $this->assertStringContainsString("'title' => get_string('providercheck:previewtitle'", $source);
        $this->assertStringContainsString("'sandbox' => 'allow-scripts allow-same-origin allow-forms allow-popups'", $source);
        $this->assertStringContainsString("'referrerpolicy' => 'no-referrer'", $source);
        $this->assertStringContainsString('$PAGE->set_cacheable(false);', $source);
        $this->assertStringNotContainsString('videotrack_view(', $source);
        $this->assertStringNotContainsString('allow-top-navigation', $source);
    }

    /**
     * The Privacy API declares Microsoft diagnostic and browser-preview transfers.
     */
    public function test_privacy_metadata_declares_microsoft_transfers(): void {
        $collection = privacy_provider::get_metadata(new \core_privacy\local\metadata\collection('mod_videotrack'));
        $locations = array_filter($collection->get_collection(), static fn($item) => $item->get_name() === 'microsoft');
        $this->assertCount(1, $locations);
        $fields = reset($locations)->get_privacy_fields();
        $this->assertEqualsCanonicalizing(
            ['resourceid', 'authorisation', 'url', 'ipaddress', 'cookies', 'useragent'],
            array_keys($fields)
        );
        foreach ($fields as $key) {
            $this->assertTrue(get_string_manager()->string_exists($key, 'mod_videotrack'));
        }
    }

    /**
     * Create a selected work/school issuer without any shared system account.
     *
     * @return issuer Local fake Microsoft issuer.
     */
    private function create_issuer(): issuer {
        $issuer = new issuer(0, (object)[
            'name' => 'VideoTrack preview fixture', 'image' => '', 'servicetype' => 'microsoft',
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
     * Provide a complete synthetic video metadata response.
     *
     * @param array $overrides Target property replacements.
     * @return string Synthetic JSON body.
     */
    private function video_body(array $overrides = []): string {
        return json_encode(array_replace([
            'id' => 'video', 'file' => ['mimeType' => 'video/mp4'],
            'video' => ['duration' => 5280000], 'size' => 1234,
        ], $overrides), JSON_THROW_ON_ERROR);
    }

    /**
     * Make both network methods fail the test if a guard is bypassed.
     *
     * @return client Uninitialised client double with no allowed HTTP calls.
     */
    private function idle_client(): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'is_logged_in'])->getMock();
        $client->expects($this->never())->method('get');
        $client->expects($this->never())->method('post');
        return $client;
    }

    /**
     * Allow only a metadata GET when the returned resource cannot reach preview POST.
     *
     * @param string|false $body Synthetic metadata response.
     * @param int $code Metadata HTTP status.
     * @return client Metadata-only client double.
     */
    private function metadata_client(string|false $body, int $code = 200): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'get_info', 'get_errno'])->getMock();
        $client->expects($this->once())->method('get')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/video?$select=id,file,video,size,deleted',
            [], $this->transport_options()
        )->willReturn($body);
        $client->expects($this->never())->method('post');
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn(0);
        return $client;
    }

    /**
     * Require exactly one metadata GET and one JSON preview POST using the same ids.
     *
     * @param string|false|\Throwable $body Synthetic preview response or thrown transport error.
     * @param int $code Preview HTTP status.
     * @param int $errno Preview curl error.
     * @return client Connected current-user client double with no real credentials.
     */
    private function preview_client(string|false|\Throwable $body, int $code = 200, int $errno = 0): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'get_info', 'get_errno', 'is_logged_in', 'setHeader', 'resetHeader'])->getMock();
        $client->method('is_logged_in')->willReturn(true);
        $client->expects($this->once())->method('get')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/video?$select=id,file,video,size,deleted',
            [], $this->transport_options()
        )->willReturn($this->video_body());
        $post = $client->expects($this->once())->method('post')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/video/preview', '{}', $this->transport_options()
        );
        if ($body instanceof \Throwable) {
            $post->willThrowException($body);
        } else {
            $post->willReturn($body);
        }
        $client->expects($this->once())->method('setHeader')->with('Content-Type: application/json');
        $client->expects($this->once())->method('resetHeader');
        $client->method('get_info')->willReturnOnConsecutiveCalls(['http_code' => 200], ['http_code' => $code]);
        $client->method('get_errno')->willReturnOnConsecutiveCalls(0, $errno);
        return $client;
    }

    /**
     * Keep both Graph requests under the accepted timeout, TLS and redirect policy.
     *
     * @return array Exact bounded core curl options.
     */
    private function transport_options(): array {
        return [
            'CURLOPT_FOLLOWLOCATION' => false, 'CURLOPT_MAXREDIRS' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 5, 'CURLOPT_TIMEOUT' => 15,
            'CURLOPT_SSL_VERIFYPEER' => true, 'CURLOPT_SSL_VERIFYHOST' => 2,
        ];
    }
}
