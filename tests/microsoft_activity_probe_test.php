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
use mod_videotrack\form\microsoft_activity_reference_form;
use mod_videotrack\form\microsoft_probe_form;
use mod_videotrack\local\microsoft_activity_reference;
use mod_videotrack\local\microsoft_graph;
use mod_videotrack\local\microsoft_user_probe;
use mod_videotrack\local\microsoft_video_reference;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Activity-bound diagnostics keep the saved identity, actor and watching evidence separate.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_activity_reference::class)]
#[CoversClass(microsoft_activity_reference_form::class)]
#[CoversClass(microsoft_probe_form::class)]
final class microsoft_activity_probe_test extends \advanced_testcase {
    /**
     * Isolate the administrator and load the real form library without a tenant connection.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->libdir . '/formslib.php');
    }

    /**
     * Reading saved context never modifies the activity, viewing records, grades or native completion.
     */
    public function test_context_reads_saved_identity_without_writing_data(): void {
        global $DB;

        [, $activity] = $this->activity();
        $DB->set_field('videotrack', 'microsoftreference', $this->reference(7), ['id' => $activity->id]);
        $before = $this->snapshot();
        $context = microsoft_activity_reference::diagnostic_context((int)$activity->cmid);
        $this->assertSame($activity->name, $context['activityname']);
        $this->assertSame($this->reference(7), $context['activityreference']);
        $this->assertEquals($before, $this->snapshot());
    }

    /**
     * A teacher is denied before the supplied course-module id is even resolved.
     */
    public function test_teacher_is_denied_before_module_lookup(): void {
        [$course] = $this->activity();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'editingteacher');
        $this->setUser($user);
        $this->expectException(\required_capability_exception::class);
        microsoft_activity_reference::diagnostic_context(-1);
    }

    /**
     * Guests cannot inspect the saved identity or module existence.
     */
    public function test_guest_is_denied_before_module_lookup(): void {
        $this->setGuestUser();
        $this->expectException(\required_capability_exception::class);
        microsoft_activity_reference::diagnostic_context(0);
    }

    /**
     * Site configuration alone does not replace permission to manage the owning course's activities.
     */
    public function test_site_configuration_permission_does_not_bypass_course_permission(): void {
        [$course, $activity] = $this->activity();
        $user = $this->getDataGenerator()->create_user();
        $role = create_role('VideoTrack diagnostic scope', 'vtdiagnosticscope', '');
        $system = \context_system::instance();
        $context = \context_course::instance($course->id);
        role_assign($role, $user->id, $system->id);
        assign_capability('moodle/site:config', CAP_ALLOW, $role, $system->id);
        assign_capability('moodle/course:manageactivities', CAP_PREVENT, $role, $context->id);
        $this->setUser($user);
        $this->assertTrue(has_capability('moodle/site:config', $system));
        $this->assertFalse(has_capability('moodle/course:manageactivities', $context));
        $this->expectException(\required_capability_exception::class);
        microsoft_activity_reference::diagnostic_context((int)$activity->cmid);
    }

    /**
     * Nonpositive ids cannot silently select a global or different activity inside the context resolver.
     */
    public function test_nonpositive_module_ids_are_rejected(): void {
        foreach ([0, -1] as $id) {
            try {
                microsoft_activity_reference::diagnostic_context($id);
                $this->fail('A nonpositive activity id was accepted.');
            } catch (\invalid_parameter_exception $error) {
                $this->assertStringNotContainsString('private-', $error->getMessage());
            }
        }
    }

    /**
     * A Forum's module id cannot resolve to a coincidentally numbered VideoTrack instance.
     */
    public function test_other_module_type_is_rejected(): void {
        [$course] = $this->activity();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $this->expectException(\moodle_exception::class);
        microsoft_activity_reference::diagnostic_context((int)$forum->cmid);
    }

    /**
     * The bound form has only an operation choice and an escaped activity label, with no client-selected target.
     */
    public function test_bound_form_has_no_editable_identity_or_account(): void {
        $form = new microsoft_probe_form(null, [
            'activityname' => '<b>Saved activity</b>', 'activityreference' => '',
        ]);
        $quickform = (new \ReflectionProperty($form, '_form'))->getValue($form);
        $this->assertTrue($quickform->elementExists('operation'));
        foreach (['driveid', 'itemid', 'reference', 'accountcontext', 'targetmode'] as $field) {
            $this->assertFalse($quickform->elementExists($field), $field);
        }
        $html = $quickform->getElement('activitytarget')->toHtml();
        $this->assertStringContainsString('&lt;b&gt;Saved activity&lt;/b&gt;', $html);
        $this->assertStringNotContainsString('<b>Saved activity</b>', $html);
    }

    /**
     * GET query fields cannot submit a bound probe or trigger OAuth/provider work.
     */
    public function test_get_does_not_submit_a_bound_probe(): void {
        microsoft_probe_form::mock_submit(['operation' => 'metadata'], [], 'get');
        $form = new microsoft_probe_form(null, ['activityname' => 'Saved', 'activityreference' => ''], 'post');
        $this->assertFalse($form->is_submitted());
        $this->assertNull($form->get_data());
    }

    /**
     * The real form rejects an invalid CSRF token before bound inputs can be read.
     */
    public function test_bound_form_rejects_invalid_sesskey(): void {
        microsoft_probe_form::mock_submit(['operation' => 'metadata']);
        $_POST['sesskey'] = 'invalid-session-marker';
        $_GET = [];
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        new microsoft_probe_form(null, ['activityname' => 'Saved', 'activityreference' => '']);
    }

    /**
     * Empty, malformed, unbound and differently bound saved references stop locally.
     */
    public function test_invalid_or_stale_saved_references_are_rejected(): void {
        $service = $this->service();
        $id = (int)$service->get('id');
        foreach (['', 'private-marker', $this->reference(0), $this->reference($id + 1)] as $reference) {
            $form = new microsoft_probe_form(null, ['activityname' => 'Saved', 'activityreference' => $reference]);
            $this->assertSame(['operation'], array_keys($form->validation(['operation' => 'metadata'], [])));
        }
    }

    /**
     * A real submitted form ignores forged resource/account fields and resolves only the server-supplied identity.
     */
    public function test_valid_post_ignores_forged_target_and_system_account(): void {
        $service = $this->service();
        microsoft_probe_form::mock_submit([
            'operation' => 'metadata', 'driveid' => 'forged-drive', 'itemid' => 'forged-item',
            'reference' => 'forged-private-marker', 'accountcontext' => 'system', 'targetmode' => 'manual',
        ]);
        $form = new microsoft_probe_form(null, [
            'activityname' => 'Saved', 'activityreference' => $this->reference((int)$service->get('id')),
        ]);
        $data = $form->get_data();
        $this->assertNotNull($data);
        $target = $form->resolve_target($data);
        $this->assertSame('current_user', $target->accountcontext);
        $this->assertSame('b!Saved_drive', $target->driveid);
        $this->assertSame('Saved_video', $target->itemid);
        $this->assertStringNotContainsString('forged-', json_encode($target));
        $this->assertObjectNotHasProperty('reference', $target);
    }

    /**
     * A new request reloads a subsequently changed saved reference instead of trusting the old browser values.
     */
    public function test_new_request_uses_the_latest_saved_reference(): void {
        global $DB;

        $service = $this->service();
        [, $activity] = $this->activity();
        $id = (int)$service->get('id');
        $DB->set_field('videotrack', 'microsoftreference', $this->reference($id), ['id' => $activity->id]);
        $old = microsoft_activity_reference::diagnostic_context((int)$activity->cmid);
        $updated = $this->reference($id, 'Updated_video');
        $DB->set_field('videotrack', 'microsoftreference', $updated, ['id' => $activity->id]);
        $current = microsoft_activity_reference::diagnostic_context((int)$activity->cmid);
        $this->assertNotSame($old['activityreference'], $current['activityreference']);
        $form = new microsoft_probe_form(null, $current);
        $target = $form->resolve_target((object)[
            'operation' => 'metadata', 'reference' => $old['activityreference'], 'accountcontext' => 'system',
        ]);
        $this->assertSame('Updated_video', $target->itemid);
        $this->assertSame($updated, $DB->get_field('videotrack', 'microsoftreference', ['id' => $activity->id]));
    }

    /**
     * All three existing actions remain valid; an arbitrary action cannot enter the controller dispatch.
     */
    public function test_bound_operation_is_allowlisted(): void {
        $service = $this->service();
        $form = new microsoft_probe_form(null, [
            'activityname' => 'Saved', 'activityreference' => $this->reference((int)$service->get('id')),
        ]);
        foreach (['metadata', 'provider_link', 'provider_preview'] as $operation) {
            $this->assertSame([], $form->validation(['operation' => $operation], []));
        }
        foreach (['', 'download', 'other_user', 'provider_preview?token=private-marker'] as $operation) {
            $this->assertSame(['operation'], array_keys($form->validation(['operation' => $operation], [])));
        }
    }

    /**
     * Only an administrator editing a saved activity sees a shortcut containing solely its module id.
     */
    public function test_shortcut_is_admin_only_and_contains_no_resource_identity(): void {
        [$course, $activity] = $this->activity();
        $form = new \MoodleQuickForm('vt_saved_reference_admin', 'post', '');
        microsoft_activity_reference_form::add_elements($form, (int)$activity->cmid);
        $html = $form->getElement('microsoftreferencecheck')->toHtml();
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('noopener noreferrer', $html);
        $this->assertSame(1, preg_match('/href="([^"]+)"/', $html, $match));
        parse_str(parse_url(html_entity_decode($match[1], ENT_QUOTES), PHP_URL_QUERY), $query);
        $this->assertSame(['activitycmid' => (string)$activity->cmid], $query);
        $new = new \MoodleQuickForm('vt_new_reference', 'post', '');
        microsoft_activity_reference_form::add_elements($new);
        $this->assertFalse($new->elementExists('microsoftreferencecheck'));
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $restricted = new \MoodleQuickForm('vt_saved_reference_teacher', 'post', '');
        microsoft_activity_reference_form::add_elements($restricted, (int)$activity->cmid);
        $this->assertFalse($restricted->elementExists('microsoftreferencecheck'));
    }

    /**
     * Resolved identities enter the current-user transport afresh, with no cached success or activity writes.
     */
    public function test_saved_identity_reuses_personal_transport_and_preserves_data(): void {
        global $CFG, $DB;

        $service = $this->service();
        [, $activity] = $this->activity();
        $DB->set_field('videotrack', 'microsoftreference', $this->reference((int)$service->get('id')), ['id' => $activity->id]);
        $before = $this->snapshot();
        $form = new microsoft_probe_form(null, microsoft_activity_reference::diagnostic_context((int)$activity->cmid));
        $target = $form->resolve_target((object)['operation' => 'metadata']);
        $client = $this->getMockBuilder(client::class)->disableOriginalConstructor()
            ->onlyMethods(['is_logged_in', 'get', 'get_info', 'get_errno'])->getMock();
        $client->method('is_logged_in')->willReturn(true);
        $client->expects($this->exactly(2))->method('get')
            ->with(microsoft_graph::resource_url('b!Saved_drive', 'Saved_video'), [], $this->isType('array'))
            ->willReturnOnConsecutiveCalls(
                '{"id":"Saved_video","file":{"mimeType":"video/mp4"},"video":{"duration":60000},"size":123}',
                '{"error":"private-provider-marker"}'
            );
        $client->method('get_info')->willReturnOnConsecutiveCalls(['http_code' => 200], ['http_code' => 403]);
        $client->method('get_errno')->willReturn(0);
        $probe = new microsoft_user_probe(static fn(issuer $selected): client => $client);
        $originalroot = $CFG->wwwroot;
        try {
            $CFG->wwwroot = 'https://moodle.example.org';
            $first = $probe->run($target->driveid, $target->itemid);
            $second = $probe->run($target->driveid, $target->itemid);
        } finally {
            $CFG->wwwroot = $originalroot;
        }
        $this->assertSame('video_accessible', $first['status']);
        $this->assertSame('current_user', $first['account_context']);
        $this->assertFalse($first['playback_implemented']);
        $this->assertSame('graph_forbidden', $second['status']);
        $this->assertStringNotContainsString('private-provider-marker', json_encode($second));
        $this->assertEquals($before, $this->snapshot());
    }

    /**
     * Create an ordinary active source without importing a Microsoft resource.
     *
     * @return array Course and VideoTrack records.
     */
    private function activity(): array {
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        return [$course, $activity];
    }

    /**
     * Create local issuer/endpoints only; all real transport remains mocked.
     *
     * @return issuer Locally configured Microsoft issuer.
     */
    private function service(): issuer {
        $service = new issuer(0, (object)[
            'name' => 'VideoTrack saved activity fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'private-client-marker', 'clientsecret' => 'private-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
        ]);
        $service->create();
        foreach (['authorization' => 'authorize', 'token' => 'token'] as $name => $action) {
            (new endpoint(0, (object)[
                'issuerid' => $service->get('id'), 'name' => $name . '_endpoint',
                'url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/' . $action,
            ]))->create();
        }
        set_config('microsoftissuerid', $service->get('id'), 'mod_videotrack');
        return $service;
    }

    /**
     * Produce a canonical saved identity, including an explicit unbound restore value.
     *
     * @param int $issuerid Local service id or zero.
     * @param string $itemid Opaque video id.
     * @return string Stored reference fixture.
     */
    private function reference(int $issuerid, string $itemid = 'Saved_video'): string {
        return json_encode([
            'version' => 1, 'provider' => 'microsoft', 'issuerid' => $issuerid,
            'driveid' => 'b!Saved_drive', 'itemid' => $itemid,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Read exact ordered evidence and core aggregate rows without modifying them.
     *
     * @return array Complete rows for every owned table plus grades/completion.
     */
    private function snapshot(): array {
        global $DB;

        $result = [];
        $tables = ['videotrack', 'videotrack_seg', 'videotrack_state', 'videotrack_react', 'videotrack_reactev',
            'videotrack_integrity', 'videotrack_acknowledge', 'grade_items', 'grade_grades', 'course_modules_completion'];
        foreach ($tables as $table) {
            $result[$table] = $DB->get_records($table, null, 'id');
        }
        return $result;
    }
}
