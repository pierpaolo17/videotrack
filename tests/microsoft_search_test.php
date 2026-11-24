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
use mod_videotrack\form\microsoft_search_form;
use mod_videotrack\local\microsoft_graph_search;
use mod_videotrack\local\microsoft_search_result;
use mod_videotrack\local\microsoft_user_probe;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Personal bounded discovery keeps private metadata separate from diagnostic JSON.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_graph_search::class)]
#[CoversClass(microsoft_search_result::class)]
#[CoversClass(microsoft_user_probe::class)]
#[CoversClass(microsoft_search_form::class)]
#[CoversClass(\mod_videotrack\privacy\provider::class)]
final class microsoft_search_test extends \advanced_testcase {
    /** @var string Original runner root. */
    private string $originalroot;

    /**
     * Isolate fake HTTPS administrator requests without real provider credentials.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->originalroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://moodle.example.org';
        set_config('microsoftissuerid', 0, 'mod_videotrack');
    }

    /**
     * Restore the runner even after a guarded failure.
     */
    protected function tearDown(): void {
        global $CFG;

        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * OData apostrophes and URL-reserved query characters stay inside one string.
     */
    public function test_query_url_is_fixed_and_odata_escaped(): void {
        $url = microsoft_graph_search::resource_url('b!Drive', "O'Neil & lezione");
        $this->assertStringStartsWith('https://graph.microsoft.com/v1.0/drives/b%21Drive/root/search(', $url);
        $this->assertStringContainsString("q='O%27%27Neil%20%26%20lezione'", $url);
        $this->assertStringEndsWith('&$top=20', $url);
        $this->assertStringContainsString('parentReference,remoteItem', $url);
        $this->assertStringNotContainsString('/me/', $url);
    }

    /**
     * Invalid query text is rejected locally rather than normalised into another search.
     */
    public function test_invalid_queries_are_rejected(): void {
        $queries = ['', 'a', str_repeat('x', 101), "bad\nquery", ' leading', 'trailing ', "\xff\xff"];
        foreach ($queries as $query) {
            try {
                microsoft_graph_search::resource_url('drive', $query);
                $this->fail('Invalid query accepted.');
            } catch (\invalid_parameter_exception $error) {
                $this->assertStringNotContainsString('private-marker', $error->getMessage());
            }
        }
        $this->assertStringContainsString('%E8%AA%B2%E7%A8%8B', microsoft_graph_search::resource_url('drive', '課程'));
    }

    /**
     * A caller cannot replace the Graph drive with a URL or path.
     */
    public function test_invalid_drive_cannot_control_endpoint(): void {
        $this->expectException(\invalid_parameter_exception::class);
        microsoft_graph_search::resource_url('https://example.org/private-marker', 'lesson');
    }

    /**
     * Valid private rows are allowlisted and excluded from ordinary JSON diagnostics.
     */
    public function test_private_candidates_do_not_enter_json(): void {
        $row = $this->row(['name' => '<private-marker>.mp4', 'webUrl' => 'https://private-marker.example']);
        $result = microsoft_search_result::from_response($this->body([$row]), 'drive', ['status' => 'graph_invalid_response']);
        $this->assertSame('search_complete', $result->diagnostic()['status']);
        $this->assertSame(1, $result->diagnostic()['returned_count']);
        $this->assertSame('<private-marker>.mp4', $result->items()[0]['name']);
        $this->assertArrayNotHasKey('webUrl', $result->items()[0]);
        $this->assertStringNotContainsString('private-marker', json_encode($result));
        $this->assertFalse($result->diagnostic()['playback_implemented']);
    }

    /**
     * Folders, deletions, shortcuts and cross-drive hits never become choices.
     */
    public function test_nonlocal_or_nonvideo_entries_are_skipped(): void {
        $overrides = [['folder' => (object)[]], ['deleted' => (object)[]], ['remoteItem' => (object)[]],
            ['parentReference' => ['driveId' => 'other']], ['parentReference' => null]];
        foreach ($overrides as $change) {
            $result = microsoft_search_result::from_response($this->body([$this->row($change)]), 'drive', []);
            $this->assertSame([], $result->items());
        }
    }

    /**
     * Invalid ids, names, MIME, duration and size are refused without raw provider fields.
     */
    public function test_invalid_video_fields_are_skipped(): void {
        $overrides = [['id' => ''], ['id' => '/content'], ['name' => ''], ['name' => "line\nname"],
            ['name' => str_repeat('x', 1025)], ['file' => null], ['file' => ['mimeType' => 'text/plain']],
            ['video' => ['duration' => 0]], ['size' => -1], ['size' => '100']];
        foreach ($overrides as $change) {
            $result = microsoft_search_result::from_response($this->body([$this->row($change)]), 'drive', []);
            $this->assertSame([], $result->items());
        }
    }

    /**
     * Duplicate resource ids produce one deterministic choice.
     */
    public function test_duplicate_ids_are_deduplicated(): void {
        $rows = [$this->row(), $this->row(['name' => 'second.mp4'])];
        $result = microsoft_search_result::from_response($this->body($rows), 'drive', []);
        $this->assertCount(1, $result->items());
        $this->assertSame('lesson.mp4', $result->items()[0]['name']);
    }

    /**
     * A valid empty collection is a completed page, not an HTTP failure.
     */
    public function test_empty_collection_is_a_successful_page(): void {
        $result = microsoft_search_result::from_response($this->body([]), 'drive', []);
        $this->assertSame('search_complete', $result->diagnostic()['status']);
        $this->assertSame(0, $result->diagnostic()['returned_count']);
        $this->assertFalse($result->diagnostic()['more_results']);
    }

    /**
     * Continuation URLs become a boolean only; no second request is made.
     */
    public function test_continuation_url_is_not_returned_or_followed(): void {
        $body = json_encode(['value' => [$this->row()], '@odata.nextLink' => 'https://private-marker.example']);
        $result = (new microsoft_graph_search($this->client($body)))->search('drive', 'lesson');
        $this->assertTrue($result->diagnostic()['more_results']);
        $this->assertStringNotContainsString('private-marker', json_encode($result));
    }

    /**
     * An oversized row collection is rejected instead of silently expanding the page.
     */
    public function test_provider_cannot_exceed_requested_page_bound(): void {
        $result = (new microsoft_graph_search($this->client($this->body(array_fill(0, 21, $this->row())))))
            ->search('drive', 'lesson');
        $this->assertSame('graph_invalid_response', $result->diagnostic()['status']);
        $this->assertSame([], $result->items());
    }

    /**
     * Search performs exactly one fixed metadata GET with the accepted transport options.
     */
    public function test_search_uses_one_bounded_get_without_post(): void {
        $result = (new microsoft_graph_search($this->client($this->body([$this->row()]))))->search('drive', 'lesson');
        $this->assertSame('search_complete', $result->diagnostic()['status']);
        $this->assertSame(5280, (int)$result->items()[0]['duration_seconds']);
    }

    /**
     * HTTP failures retain existing categories without exposing response bodies.
     */
    public function test_http_failures_are_redacted_without_retry(): void {
        $statuses = [302 => 'graph_redirect_refused', 401 => 'graph_unauthorised', 403 => 'graph_forbidden',
            404 => 'graph_not_found', 429 => 'graph_throttled', 503 => 'graph_unavailable', 400 => 'graph_request_failed'];
        foreach ($statuses as $code => $status) {
            $result = (new microsoft_graph_search($this->client('private-marker', $code)))->search('drive', 'lesson');
            $this->assertSame($status, $result->diagnostic()['status']);
            $this->assertSame([], $result->items());
            $this->assertStringNotContainsString('private-marker', json_encode($result));
        }
    }

    /**
     * Curl errors do not turn a stale body into choices.
     */
    public function test_transport_errno_is_a_reduced_failure(): void {
        $client = $this->client($this->body([$this->row()]), 200, 28);
        $result = (new microsoft_graph_search($client))->search('drive', 'lesson');
        $this->assertSame('graph_transport_failed', $result->diagnostic()['status']);
        $this->assertSame([], $result->items());
    }

    /**
     * Exceptions cannot reveal query, token or provider body text.
     */
    public function test_transport_exception_is_redacted(): void {
        $client = $this->client(new \RuntimeException('private-marker'));
        $result = (new microsoft_graph_search($client))->search('drive', 'lesson');
        $this->assertSame('graph_transport_failed', $result->diagnostic()['status']);
        $this->assertStringNotContainsString('private-marker', json_encode($result));
    }

    /**
     * Invalid JSON, collection shapes, response size and depth fail within the transport boundary.
     */
    public function test_invalid_provider_bodies_are_reduced(): void {
        $bodies = [false, '{', '[]', '{}', '{"value":{}}', str_repeat('x', 1048577),
            str_repeat('{"x":', 20) . '{}' . str_repeat('}', 20)];
        foreach ($bodies as $body) {
            $result = (new microsoft_graph_search($this->client($body)))->search('drive', 'lesson');
            $this->assertSame('graph_invalid_response', $result->diagnostic()['status']);
            $this->assertSame([], $result->items());
        }
    }

    /**
     * Personal search reuses the guarded factory and leaves all activity tables unchanged.
     */
    public function test_personal_search_preserves_activity_data(): void {
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
        $client = $this->client($this->body([$this->row()]));
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->search_videos('drive', 'lesson');
        $this->assertSame('current_user', $result->diagnostic()['account_context']);
        $this->assertSame('search_complete', $result->diagnostic()['status']);
        foreach ($before as $table => $records) {
            $this->assertEquals($records, $DB->get_records($table));
        }
    }

    /**
     * Malformed queries stop before even the OAuth factory is available.
     */
    public function test_invalid_target_stops_before_oauth(): void {
        $result = $this->guarded_probe()->search_videos('drive', 'x');
        $this->assertSame('invalid_target', $result->diagnostic()['status']);
        $this->assertFalse($result->diagnostic()['oauth_refresh_possible']);
    }

    /**
     * Missing local configuration never acquires a current-user client.
     */
    public function test_missing_configuration_stops_before_factory(): void {
        $result = $this->guarded_probe()->search_videos('drive', 'lesson');
        $this->assertSame('not_configured', $result->diagnostic()['status']);
        $this->assertFalse($result->diagnostic()['graph_attempted']);
    }

    /**
     * A real shared-account fixture cannot replace a disconnected personal account.
     */
    public function test_disconnected_user_has_no_shared_fallback(): void {
        $issuer = $this->create_issuer();
        (new system_account(0, (object)[
            'issuerid' => $issuer->get('id'), 'refreshtoken' => 'system-marker',
            'grantedscopes' => api::get_system_scopes_for_issuer($issuer),
            'email' => 'system@example.org', 'username' => 'system-fixture',
        ]))->create();
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'is_logged_in'])->getMock();
        $client->expects($this->never())->method('get');
        $client->expects($this->never())->method('post');
        $client->method('is_logged_in')->willReturn(false);
        $result = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))->search_videos('drive', 'lesson');
        $this->assertSame('user_oauth_not_connected', $result->diagnostic()['status']);
        $this->assertSame([], $result->items());
    }

    /**
     * Course teachers cannot turn search into an unguarded file discovery endpoint.
     */
    public function test_teacher_cannot_search_before_factory(): void {
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->expectException(\required_capability_exception::class);
        $this->guarded_probe()->search_videos('drive', 'lesson');
    }

    /**
     * Form validation identifies local drive and query errors without normalising URLs.
     */
    public function test_form_validates_local_target_fields(): void {
        $form = new microsoft_search_form();
        $this->assertSame([], $form->validation(['driveid' => 'drive', 'searchquery' => 'lesson'], []));
        $errors = $form->validation(['driveid' => 'https://example.org', 'searchquery' => 'x'], []);
        $this->assertSame(['driveid', 'searchquery'], array_keys($errors));
        $errors = $form->validation(['driveid' => [], 'searchquery' => []], []);
        $this->assertSame(['driveid', 'searchquery'], array_keys($errors));
    }

    /**
     * Crafted GET fields and form markers cannot submit a search.
     */
    public function test_get_cannot_submit_search(): void {
        microsoft_search_form::mock_submit(['driveid' => 'drive', 'searchquery' => 'lesson'], [], 'get');
        $form = new microsoft_search_form(null, null, 'post');
        $this->assertFalse($form->is_submitted());
        $this->assertNull($form->get_data());
    }

    /**
     * Invalid sesskeys are rejected before search input is consumed.
     */
    public function test_search_form_rejects_invalid_sesskey(): void {
        microsoft_search_form::mock_submit(['driveid' => 'drive', 'searchquery' => 'lesson']);
        $_POST['sesskey'] = 'invalid-session-marker';
        $_GET = [];
        $this->expectException(\moodle_exception::class);
        new microsoft_search_form();
    }

    /**
     * Controller guards, separate escaped output and no player/media writes stay explicit.
     */
    public function test_controller_keeps_private_output_and_guard_order(): void {
        $source = file_get_contents(__DIR__ . '/../provider_search.php');
        $this->assertLessThan(strpos($source, '$form->get_data()'), strpos($source, 'require_capability('));
        $this->assertLessThan(strpos($source, '->search_videos('), strpos($source, 'require_sesskey();'));
        $this->assertStringContainsString("heading(s(\$item['name'])", $source);
        $this->assertStringContainsString("html_writer::tag('textarea', s(\$reference->encode())", $source);
        $this->assertStringContainsString("'readonly' => 'readonly'", $source);
        $this->assertStringContainsString('s(json_encode($diagnostic,', $source);
        $this->assertStringNotContainsString('iframe', $source);
        $this->assertStringNotContainsString('->insert_record(', $source);
        $this->assertStringNotContainsString('set_config(', $source);
    }

    /**
     * Privacy metadata explicitly declares the search text sent to Microsoft.
     */
    public function test_privacy_declares_search_text_transfer(): void {
        $collection = \mod_videotrack\privacy\provider::get_metadata(
            new \core_privacy\local\metadata\collection('mod_videotrack')
        );
        $locations = array_filter($collection->get_collection(), static fn($item) => $item->get_name() === 'microsoft');
        $this->assertCount(1, $locations);
        $fields = reset($locations)->get_privacy_fields();
        $this->assertSame('privacy:metadata:microsoft:searchquery', $fields['searchquery']);
    }

    /**
     * Provide synthetic searchable video metadata.
     *
     * @param array $overrides Field replacements.
     * @return array Provider row with no real identifiers.
     */
    private function row(array $overrides = []): array {
        return array_replace([
            'id' => 'video', 'name' => 'lesson.mp4', 'file' => ['mimeType' => 'video/mp4'],
            'video' => ['duration' => 5280000], 'size' => 1234, 'parentReference' => ['driveId' => 'drive'],
        ], $overrides);
    }

    /**
     * Build one synthetic provider page.
     *
     * @param array $rows Synthetic entries.
     * @return string JSON collection.
     */
    private function body(array $rows): string {
        return json_encode(['value' => $rows], JSON_THROW_ON_ERROR);
    }

    /**
     * Fail if a local rejection accidentally reaches OAuth.
     *
     * @return microsoft_user_probe Guarded fake factory.
     */
    private function guarded_probe(): microsoft_user_probe {
        return new microsoft_user_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('The factory must not run.');
        });
    }

    /**
     * Create a local Microsoft service without an actual account connection.
     *
     * @return issuer Selected fake core issuer.
     */
    private function create_issuer(): issuer {
        $issuer = new issuer(0, (object)[
            'name' => 'VideoTrack search fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'client-marker', 'clientsecret' => 'secret-marker',
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
     * Require exactly one search GET, never POST or content traffic.
     *
     * @param string|false|\Throwable $body Response or thrown error.
     * @param int $code HTTP status.
     * @param int $errno Curl error.
     * @return client Core client double.
     */
    private function client(string|false|\Throwable $body, int $code = 200, int $errno = 0): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'get_info', 'get_errno', 'is_logged_in'])->getMock();
        $client->method('is_logged_in')->willReturn(true);
        $url = "https://graph.microsoft.com/v1.0/drives/drive/root/search(q='lesson')"
            . '?$select=id,name,file,folder,video,size,deleted,parentReference,remoteItem&$top=20';
        $request = $client->expects($this->once())->method('get')->with($url, [], [
            'CURLOPT_FOLLOWLOCATION' => false, 'CURLOPT_MAXREDIRS' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 5, 'CURLOPT_TIMEOUT' => 15,
            'CURLOPT_SSL_VERIFYPEER' => true, 'CURLOPT_SSL_VERIFYHOST' => 2,
        ]);
        if ($body instanceof \Throwable) {
            $request->willThrowException($body);
        } else {
            $request->willReturn($body);
        }
        $client->expects($this->never())->method('post');
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn($errno);
        return $client;
    }
}
