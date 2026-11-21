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
use mod_videotrack\local\microsoft_user_probe;
use mod_videotrack\local\microsoft_video_reference;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Reusable identities remain separate from credentials, permissions and fresh URLs.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_video_reference::class)]
#[CoversClass(microsoft_probe_form::class)]
#[CoversClass(microsoft_user_probe::class)]
final class microsoft_video_reference_test extends \advanced_testcase {
    /** @var string Original runner root. */
    private string $originalroot;

    /**
     * Isolate requests and configuration without contacting a real tenant.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->originalroot = $CFG->wwwroot;
        $CFG->wwwroot = 'https://moodle.example.org';
        set_config('microsoftissuerid', 7, 'mod_videotrack');
    }

    /**
     * Restore the runner even if a guard rejects a request.
     */
    protected function tearDown(): void {
        global $CFG;

        $CFG->wwwroot = $this->originalroot;
        parent::tearDown();
    }

    /**
     * Only explicit encoding exposes case-preserved opaque ids and the local service.
     */
    public function test_reference_roundtrip_preserves_opaque_identity(): void {
        $reference = new microsoft_video_reference(7, 'b!Drive-A_1', 'Video_2-!');
        $copy = microsoft_video_reference::from_json($reference->encode());
        $this->assertSame($reference->encode(), $copy->encode());
        $this->assertSame([
            'accountcontext' => 'current_user', 'driveid' => 'b!Drive-A_1', 'itemid' => 'Video_2-!',
        ], $copy->target());
        $this->assertSame('{}', json_encode($reference));
        $this->assertSame(['version', 'provider', 'issuerid', 'driveid', 'itemid'], array_keys(json_decode($copy->encode(), true)));
    }

    /**
     * Ordinary JSON formatting and property order do not change identity.
     */
    public function test_reordered_json_whitespace_is_accepted(): void {
        $text = ' { "itemid":"video", "issuerid":7, "provider":"microsoft", "driveid":"drive", "version":1 } ';
        $this->assertSame('drive', microsoft_video_reference::from_json($text)->target()['driveid']);
    }

    /**
     * Non-string, malformed, nested and oversized inputs fail without exposing input.
     */
    public function test_invalid_json_and_non_string_inputs_are_rejected(): void {
        $inputs = [null, false, 7, [], new \stdClass(), '', '{', '[]', 'null', '7', '"private-marker"'];
        $inputs[] = str_repeat(' ', 4097) . $this->reference();
        $inputs[] = str_repeat('{"private-marker":', 8) . '{}' . str_repeat('}', 8);
        $inputs[] = "{\"private-marker\":\"\xff\"}";
        foreach ($inputs as $input) {
            $this->assert_invalid($input);
        }
    }

    /**
     * Unknown fields, URLs and credentials cannot be imported into the identity.
     */
    public function test_exact_fields_exclude_credentials_and_provider_urls(): void {
        foreach (['url', 'getUrl', 'access_token', 'clientsecret', 'user', 'operation'] as $field) {
            $this->assert_invalid($this->reference([$field => 'private-marker']));
        }
        foreach (['version', 'provider', 'issuerid', 'driveid', 'itemid'] as $field) {
            $data = json_decode($this->reference(), true);
            unset($data[$field]);
            $this->assert_invalid(json_encode($data));
        }
    }

    /**
     * Unsupported versions and provider lookalikes are not silently normalised.
     */
    public function test_version_and_provider_types_are_strict(): void {
        foreach ([0, 2, '1', true, 1.5] as $value) {
            $this->assert_invalid($this->reference(['version' => $value]));
        }
        foreach (['Microsoft', 'google', '', 1, null] as $value) {
            $this->assert_invalid($this->reference(['provider' => $value]));
        }
    }

    /**
     * The local service must be a positive JSON integer, never an external identity.
     */
    public function test_issuer_type_and_range_are_restricted(): void {
        foreach ([0, -1, '7', true, 7.5, null, 'https://example.org'] as $value) {
            $this->assert_invalid($this->reference(['issuerid' => $value]));
        }
    }

    /**
     * Resource ids retain the existing fixed-host Graph grammar and require a video.
     */
    public function test_unsafe_or_missing_resource_ids_are_rejected(): void {
        foreach (['driveid', 'itemid'] as $field) {
            foreach (['', '/content', 'https://example.org/private-marker', 'a?b=1', 'a b', str_repeat('A', 257), null] as $id) {
                $this->assert_invalid($this->reference([$field => $id]));
            }
        }
    }

    /**
     * Selecting another core service requires an explicit new identity check.
     */
    public function test_selected_issuer_must_match(): void {
        $reference = microsoft_video_reference::from_json($this->reference());
        $reference->assert_issuer(7);
        $this->expectException(\invalid_parameter_exception::class);
        $reference->assert_issuer(8);
    }

    /**
     * Successful personal video checks export identity without copying result URLs.
     */
    public function test_successful_result_offers_only_identity(): void {
        $target = (object)['accountcontext' => 'current_user', 'driveid' => 'drive', 'itemid' => 'video'];
        foreach (['video_accessible', 'provider_link_ready', 'preview_ready'] as $status) {
            $diagnostic = [
                'status' => $status, 'account_context' => 'current_user',
                'url' => 'private-marker', 'access_token' => 'private-marker',
            ];
            $reference = microsoft_video_reference::for_result(7, $target, $diagnostic);
            $this->assertNotNull($reference);
            $this->assertSame($this->reference(), $reference->encode());
            $this->assertStringNotContainsString('private-marker', $reference->encode());
        }
    }

    /**
     * A drive, failed check or shared account cannot produce a personal video reference.
     */
    public function test_unsuccessful_or_shared_results_do_not_offer_reference(): void {
        $target = (object)['accountcontext' => 'current_user', 'driveid' => 'drive', 'itemid' => 'video'];
        $statuses = ['drive_accessible', 'graph_forbidden', 'invalid_target', 'not_configured', 'preview_url_unsupported'];
        foreach ($statuses as $status) {
            $this->assertNull(microsoft_video_reference::for_result(7, $target, ['status' => $status]));
        }
        $this->assertNull(microsoft_video_reference::for_result(0, $target, ['status' => 'video_accessible']));
        $this->assertNull(microsoft_video_reference::for_result(7, $target, [
            'status' => 'video_accessible', 'account_context' => 'system',
        ]));
        $target->accountcontext = 'system';
        $this->assertNull(microsoft_video_reference::for_result(7, $target, ['status' => 'video_accessible']));
        $target->accountcontext = 'current_user';
        $target->itemid = '';
        $this->assertNull(microsoft_video_reference::for_result(7, $target, ['status' => 'drive_accessible']));
    }

    /**
     * All existing personal operations accept the same validated video reference.
     */
    public function test_reference_form_supports_existing_personal_actions(): void {
        $form = new microsoft_probe_form();
        foreach (['metadata', 'provider_link', 'provider_preview'] as $operation) {
            $this->assertSame([], $form->validation($this->reference_data(['operation' => $operation]), []));
        }
    }

    /**
     * Reference input cannot select a shared account, including metadata requests.
     */
    public function test_reference_form_requires_personal_account(): void {
        $errors = (new microsoft_probe_form())->validation($this->reference_data(['accountcontext' => 'system']), []);
        $this->assertSame(['accountcontext'], array_keys($errors));
    }

    /**
     * Unknown target modes and malformed reference arrays have local field errors.
     */
    public function test_invalid_mode_or_reference_cannot_fall_back_to_manual_ids(): void {
        $form = new microsoft_probe_form();
        $errors = $form->validation($this->reference_data(['targetmode' => 'url']), []);
        $this->assertSame(['targetmode'], array_keys($errors));
        foreach (['private-marker', [], null] as $value) {
            $errors = $form->validation($this->reference_data(['reference' => $value, 'driveid' => 'drive']), []);
            $this->assertSame(['reference'], array_keys($errors));
            $this->assertSame(get_string('providercheck:referenceinvalid', 'mod_videotrack'), $errors['reference']);
        }
    }

    /**
     * References for another local issuer fail before OAuth or Graph is possible.
     */
    public function test_reference_form_rejects_another_service(): void {
        set_config('microsoftissuerid', 8, 'mod_videotrack');
        $errors = (new microsoft_probe_form())->validation($this->reference_data(), []);
        $this->assertSame(['reference'], array_keys($errors));
        $this->assertSame(get_string('providercheck:referenceissuer', 'mod_videotrack'), $errors['reference']);
    }

    /**
     * Manual input retains its original object and ignores an inactive reference field.
     */
    public function test_manual_target_contract_is_retained(): void {
        $form = new microsoft_probe_form();
        $data = (object)['driveid' => 'drive', 'itemid' => '', 'reference' => 'private-marker'];
        $this->assertSame([], $form->validation((array)$data, []));
        $this->assertSame($data, $form->resolve_target($data));
    }

    /**
     * A valid reference overrides inactive manual fields without mutating submitted data.
     */
    public function test_reference_target_keeps_operation_and_exact_ids(): void {
        $data = (object)$this->reference_data(['driveid' => '/invalid', 'itemid' => 'other']);
        $form = new microsoft_probe_form();
        $this->assertSame([], $form->validation((array)$data, []));
        $target = $form->resolve_target($data);
        $this->assertNotSame($data, $target);
        $this->assertSame('drive', $target->driveid);
        $this->assertSame('video', $target->itemid);
        $this->assertSame('provider_preview', $target->operation);
        $this->assertSame('current_user', $target->accountcontext);
        $this->assertSame('/invalid', $data->driveid);
    }

    /**
     * The controller conversion rechecks service selection after form validation.
     */
    public function test_target_resolution_rechecks_changed_issuer(): void {
        $form = new microsoft_probe_form();
        $data = $this->reference_data();
        $this->assertSame([], $form->validation($data, []));
        set_config('microsoftissuerid', 9, 'mod_videotrack');
        $this->expectException(\invalid_parameter_exception::class);
        $form->resolve_target((object)$data);
    }

    /**
     * URL parameters cannot submit a reusable target or trigger a fresh preview.
     */
    public function test_get_cannot_submit_reference_input(): void {
        microsoft_probe_form::mock_submit($this->reference_data(), [], 'get');
        $form = new microsoft_probe_form(null, null, 'post');
        $this->assertFalse($form->is_submitted());
        $this->assertNull($form->get_data());
    }

    /**
     * An invalid sesskey is rejected before reference resolution.
     */
    public function test_reference_post_requires_a_valid_sesskey(): void {
        microsoft_probe_form::mock_submit($this->reference_data());
        $_POST['sesskey'] = 'invalid-session-marker';
        $_GET = [];
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        new microsoft_probe_form();
    }

    /**
     * The controller keeps its access checks and escapes an explicit read-only export.
     */
    public function test_controller_keeps_guards_and_separate_escaped_reference(): void {
        $source = file_get_contents(__DIR__ . '/../provider_check.php');
        $this->assertLessThan(strpos($source, '$form->get_data()'), strpos($source, 'require_capability('));
        $this->assertLessThan(strpos($source, '$form->resolve_target('), strpos($source, 'require_sesskey();'));
        $this->assertLessThan(strpos($source, '$probe->video_preview('), strpos($source, '$form->resolve_target('));
        $this->assertStringContainsString("html_writer::tag('textarea', s(\$videoreference)", $source);
        $this->assertStringContainsString("'readonly' => 'readonly'", $source);
        $this->assertStringContainsString("s(json_encode(\$result,", $source);
        $this->assertStringNotContainsString('set_config(', $source);
        $this->assertStringNotContainsString('->insert_record(', $source);
        $this->assertStringNotContainsString('->update_record(', $source);
    }

    /**
     * Reusing the same identity requests new metadata and preview URLs without activity writes.
     */
    public function test_repeated_reference_resolution_requests_fresh_previews(): void {
        global $DB;

        $issuer = $this->create_issuer();
        $text = (new microsoft_video_reference((int)$issuer->get('id'), 'drive', 'video'))->encode();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $tables = ['videotrack', 'videotrack_react', 'videotrack_reactev', 'videotrack_seg',
            'videotrack_state', 'videotrack_integrity', 'videotrack_acknowledge'];
        $before = [];
        foreach ($tables as $table) {
            $before[$table] = $DB->get_records($table);
        }
        foreach (['first-private-marker', 'second-private-marker'] as $marker) {
            $client = $this->preview_client('https://tenant.sharepoint.com/embed.aspx?tempauth=' . $marker);
            $probe = new microsoft_user_probe(static fn(issuer $issuer): client => $client);
            $target = (new microsoft_probe_form())->resolve_target((object)$this->reference_data(['reference' => $text]));
            $result = $probe->video_preview($target->driveid, $target->itemid);
            $this->assertSame('preview_ready', $result->diagnostic()['status']);
            $this->assertStringContainsString($marker, $result->url());
            $this->assertStringNotContainsString($marker, json_encode($result));
            $this->assertSame($text, microsoft_video_reference::from_json($text)->encode());
        }
        foreach ($before as $table => $rows) {
            $this->assertEquals($rows, $DB->get_records($table));
        }
    }

    /**
     * Revoked access returns a fresh failure instead of reviving a previous preview.
     */
    public function test_reference_reuse_does_not_recover_an_old_url_after_denial(): void {
        $issuer = $this->create_issuer();
        $reference = new microsoft_video_reference((int)$issuer->get('id'), 'drive', 'video');
        $target = $reference->target();
        $client = $this->preview_client('https://tenant.sharepoint.com/embed.aspx?tempauth=private-marker');
        $first = (new microsoft_user_probe(static fn(issuer $issuer): client => $client))
            ->video_preview($target['driveid'], $target['itemid']);
        $this->assertNotNull($first->url());
        $denied = $this->preview_client('', 403);
        $second = (new microsoft_user_probe(static fn(issuer $issuer): client => $denied))
            ->video_preview($target['driveid'], $target['itemid']);
        $this->assertSame('graph_forbidden', $second->diagnostic()['status']);
        $this->assertNull($second->url());
        $this->assertFalse($second->diagnostic()['preview_attempted']);
        $this->assertStringNotContainsString('private-marker', $reference->encode());
    }

    /**
     * Possessing a valid reference does not authorise a course teacher to run diagnostics.
     */
    public function test_reference_is_not_an_administration_access_grant(): void {
        $target = microsoft_video_reference::from_json($this->reference())->target();
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $probe = new microsoft_user_probe(static function (issuer $issuer): client {
            throw new \RuntimeException('The factory must not run.');
        });
        $this->expectException(\required_capability_exception::class);
        $probe->video_preview($target['driveid'], $target['itemid']);
    }

    /**
     * Encode a synthetic reference with explicit property replacements.
     *
     * @param array $overrides Synthetic field replacements.
     * @return string Reference text with no real identifiers.
     */
    private function reference(array $overrides = []): string {
        return json_encode(array_replace([
            'version' => 1, 'provider' => 'microsoft', 'issuerid' => 7, 'driveid' => 'drive', 'itemid' => 'video',
        ], $overrides), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Supply the normal personal preview form fields for reference input.
     *
     * @param array $overrides Submitted field replacements.
     * @return array Synthetic form fields.
     */
    private function reference_data(array $overrides = []): array {
        return array_replace([
            'operation' => 'provider_preview', 'accountcontext' => 'current_user',
            'targetmode' => 'reference', 'reference' => $this->reference(),
        ], $overrides);
    }

    /**
     * Require a controlled exception without reflecting private input.
     *
     * @param mixed $input Malformed administrator input.
     */
    private function assert_invalid(mixed $input): void {
        try {
            microsoft_video_reference::from_json($input);
            $this->fail('Invalid reference was accepted.');
        } catch (\invalid_parameter_exception $error) {
            $this->assertStringNotContainsString('private-marker', $error->getMessage());
        }
    }

    /**
     * Create the same local work/school OAuth fixture used by native preview tests.
     *
     * @return issuer Selected core issuer without a shared account.
     */
    private function create_issuer(): issuer {
        $issuer = new issuer(0, (object)[
            'name' => 'VideoTrack reference fixture', 'image' => '', 'servicetype' => 'microsoft',
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
     * Expect one authenticated metadata GET and, only on success, one preview POST.
     *
     * @param string $url Synthetic temporary response URL.
     * @param int $code Metadata HTTP status.
     * @return client Core client double; no real OAuth credentials or network.
     */
    private function preview_client(string $url, int $code = 200): client {
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['get', 'post', 'get_info', 'get_errno', 'is_logged_in', 'setHeader', 'resetHeader'])->getMock();
        $client->method('is_logged_in')->willReturn(true);
        $client->expects($this->once())->method('get')->with(
            'https://graph.microsoft.com/v1.0/drives/drive/items/video?$select=id,file,video,size,deleted',
            [],
            $this->transport_options()
        )->willReturn(json_encode([
            'id' => 'video', 'file' => ['mimeType' => 'video/mp4'], 'video' => ['duration' => 5280000], 'size' => 1234,
        ]));
        $client->method('get_info')->willReturn(['http_code' => $code]);
        $client->method('get_errno')->willReturn(0);
        if ($code === 200) {
            $client->expects($this->once())->method('post')->with(
                'https://graph.microsoft.com/v1.0/drives/drive/items/video/preview',
                '{}',
                $this->transport_options()
            )->willReturn(json_encode(['getUrl' => $url]));
            $client->expects($this->once())->method('setHeader')->with('Content-Type: application/json');
            $client->expects($this->once())->method('resetHeader');
        } else {
            $client->expects($this->never())->method('post');
            $client->expects($this->never())->method('setHeader');
        }
        return $client;
    }

    /**
     * Retain the accepted TLS, timeout and no-redirect transport contract.
     *
     * @return array Expected core curl options.
     */
    private function transport_options(): array {
        return [
            'CURLOPT_FOLLOWLOCATION' => false, 'CURLOPT_MAXREDIRS' => 0,
            'CURLOPT_CONNECTTIMEOUT' => 5, 'CURLOPT_TIMEOUT' => 15,
            'CURLOPT_SSL_VERIFYPEER' => true, 'CURLOPT_SSL_VERIFYHOST' => 2,
        ];
    }
}
