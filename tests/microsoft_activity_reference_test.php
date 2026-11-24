<?php
// This file is part of Moodle - https://moodle.org/
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

use core\oauth2\issuer;
use mod_videotrack\form\microsoft_activity_reference_form;
use mod_videotrack\local\microsoft_activity_reference;
use mod_videotrack\local\microsoft_video_reference;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Reference preparation must preserve playback, ownership and explicit local binding.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_activity_reference::class)]
#[CoversClass(microsoft_activity_reference_form::class)]
final class microsoft_activity_reference_test extends \advanced_testcase {
    /**
     * Keep all identities and service settings isolated without a tenant connection.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * The existing diagnostic output is accepted without copying transient URLs.
     */
    public function test_diagnostic_reference_is_canonicalised(): void {
        $value = (new microsoft_video_reference(7, 'b!Drive_1', 'Video-2'))->encode();
        $stored = microsoft_activity_reference::canonical($value);
        $this->assertSame($this->reference(7), $stored);
        $this->assertSame($stored, microsoft_activity_reference::canonical($stored));
        $this->assertSame('', microsoft_activity_reference::canonical(null));
        $this->assertSame('', microsoft_activity_reference::canonical(''));
    }

    /**
     * Scalar grammar rejects credentials, malformed shapes and ambiguous versions.
     */
    public function test_malformed_or_private_payloads_are_rejected(): void {
        $inputs = [false, 7, [], new \stdClass(), 'null', '[]', '{', str_repeat(' ', 4097)];
        foreach ([
            ['url' => 'private-marker'], ['access_token' => 'private-marker'],
            ['version' => '1'], ['version' => 2], ['provider' => 'google'],
            ['issuerid' => -1], ['issuerid' => '7'], ['issuerid' => true],
            ['driveid' => 'https://example.org/private-marker'], ['driveid' => []],
            ['itemid' => ''], ['itemid' => '../content'], ['itemid' => str_repeat('A', 257)],
        ] as $change) {
            $inputs[] = json_encode(array_replace(json_decode($this->reference(7), true), $change));
        }
        foreach ($inputs as $value) {
            $this->assert_invalid(static fn() => microsoft_activity_reference::canonical($value));
        }
    }

    /**
     * A backup carries only portable resource identifiers, including after restore.
     */
    public function test_portable_identity_discards_the_local_binding(): void {
        $portable = microsoft_activity_reference::to_backup($this->reference(7));
        $this->assertSame(['version' => 1, 'provider' => 'microsoft', 'driveid' => 'b!Drive_1', 'itemid' => 'Video-2'], $portable);
        $this->assertSame([], microsoft_activity_reference::to_backup(null));
        $this->assertSame([], microsoft_activity_reference::to_backup(''));
        $portable['version'] = '1';
        $restored = microsoft_activity_reference::from_backup($portable);
        $this->assertSame($this->reference(0), $restored);
        $portable['version'] = 1;
        $this->assertSame($portable, microsoft_activity_reference::to_backup($restored));
    }

    /**
     * A portable backup cannot smuggle a site binding, credential or resource URL.
     */
    public function test_portable_import_rejects_private_or_unknown_fields(): void {
        $valid = microsoft_activity_reference::to_backup($this->reference(7));
        foreach ([['issuerid' => 7], ['url' => 'private-marker'], ['access_token' => 'private-marker'],
                ['version' => 2], ['driveid' => 'https://example.org'], ['itemid' => []]] as $change) {
            $value = array_replace($valid, $change);
            $this->assert_invalid(static fn() => microsoft_activity_reference::from_backup($value));
        }
        unset($valid['provider']);
        $this->assert_invalid(static fn() => microsoft_activity_reference::from_backup($valid));
    }

    /**
     * New local metadata requires an enabled selected Microsoft service.
     */
    public function test_new_reference_uses_the_selected_local_service(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $data = (object)['course' => $course->id, 'microsoftreference' => $this->reference((int)$service->get('id'))];
        microsoft_activity_reference::process_form_fields($data);
        $this->assertSame($this->reference((int)$service->get('id')), $data->microsoftreference);
        $this->assertFalse(property_exists($data, 'videoid'));
    }

    /**
     * A service change never silently rewrites an existing activity identity.
     */
    public function test_unchanged_and_sparse_updates_survive_configuration_changes(): void {
        $course = $this->getDataGenerator()->create_course();
        $previous = (object)['course' => $course->id, 'microsoftreference' => $this->reference(7)];
        set_config('microsoftissuerid', 999999, 'mod_videotrack');
        foreach ([(object)[], clone $previous] as $data) {
            microsoft_activity_reference::process_form_fields($data, $previous);
            $this->assertSame($previous->microsoftreference, $data->microsoftreference);
        }
        $empty = (object)['microsoftreference' => ''];
        microsoft_activity_reference::process_form_fields($empty);
        $this->assertNull($empty->microsoftreference);
    }

    /**
     * Authorised removal is possible even when the original service has gone away.
     */
    public function test_teacher_can_clear_a_stale_reference(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->editing_teacher($course);
        $previous = (object)['course' => $course->id, 'microsoftreference' => 'invalid-old-marker'];
        $data = (object)['microsoftreference' => ''];
        microsoft_activity_reference::process_form_fields($data, $previous);
        $this->assertSame('', $data->microsoftreference);
    }

    /**
     * A mismatched or absent local service is rejected before a changed identity is saved.
     */
    public function test_changed_reference_requires_the_current_service(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        foreach ([0, (int)$service->get('id') + 1] as $id) {
            $data = (object)['course' => $course->id, 'microsoftreference' => $this->reference($id)];
            $this->assert_invalid(static fn() => microsoft_activity_reference::process_form_fields($data));
        }
        set_config('microsoftissuerid', 999999, 'mod_videotrack');
        $data = (object)['course' => $course->id, 'microsoftreference' => $this->reference((int)$service->get('id'))];
        $this->assert_invalid(static fn() => microsoft_activity_reference::process_form_fields($data));
    }

    /**
     * Disabling or selecting a different provider cannot grant a binding.
     */
    public function test_disabled_and_non_microsoft_services_are_rejected(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $data = (object)['course' => $course->id, 'microsoftreference' => $this->reference((int)$service->get('id'))];
        $service->set('enabled', false);
        $service->update();
        $this->assert_invalid(static fn() => microsoft_activity_reference::process_form_fields($data));
        $service->set('enabled', true);
        $service->set('servicetype', 'google');
        $service->update();
        $this->assert_invalid(static fn() => microsoft_activity_reference::process_form_fields($data));
    }

    /**
     * Restored resource ids remain unbound on ordinary edits even with a new service selected.
     */
    public function test_restored_reference_requires_deliberate_rebinding(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $previous = (object)['course' => $course->id, 'microsoftreference' => $this->reference(0)];
        $data = clone $previous;
        microsoft_activity_reference::process_form_fields($data, $previous);
        $this->assertSame($this->reference(0), $data->microsoftreference);
        $data->microsoftreferencebind = 1;
        microsoft_activity_reference::process_form_fields($data, $previous);
        $this->assertSame($this->reference((int)$service->get('id')), $data->microsoftreference);
    }

    /**
     * An explicit rebind uses the destination setting rather than an imported source id.
     */
    public function test_deliberate_rebinding_overrides_only_the_local_service(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $this->editing_teacher($course);
        $data = (object)['course' => $course->id, 'microsoftreference' => $this->reference(999999), 'microsoftreferencebind' => 1];
        microsoft_activity_reference::process_form_fields($data);
        $this->assertSame($this->reference((int)$service->get('id')), $data->microsoftreference);
    }

    /**
     * A binding action never invents a target when the reference field is absent or empty.
     */
    public function test_empty_or_missing_reference_cannot_be_bound(): void {
        foreach ([(object)['microsoftreferencebind' => 1],
                (object)['microsoftreferencebind' => 1, 'microsoftreference' => '']] as $data) {
            $this->assert_invalid(static fn() => microsoft_activity_reference::process_form_fields($data));
        }
    }

    /**
     * Reference edits and clears require course activity management permission.
     */
    public function test_unprivileged_user_cannot_change_or_clear_reference(): void {
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);
        foreach (['', $this->reference((int)$service->get('id'))] as $value) {
            $data = (object)['microsoftreference' => $value];
            $previous = (object)['course' => $course->id, 'microsoftreference' => $this->reference(0)];
            try {
                microsoft_activity_reference::process_form_fields($data, $previous);
                $this->fail('A learner changed the activity reference.');
            } catch (\required_capability_exception $error) {
                $this->assertSame($value, $data->microsoftreference);
            }
        }
    }

    /**
     * Field validation reports a translatable error without reflecting private input.
     */
    public function test_form_errors_do_not_expose_submitted_contents(): void {
        $errors = microsoft_activity_reference_form::errors(['microsoftreference' => 'private-marker'], null);
        $this->assertSame(['microsoftreference' => get_string('microsoftreference:invalid', 'mod_videotrack')], $errors);
        $this->assertStringNotContainsString('private-marker', json_encode($errors));
    }

    /**
     * Actual activity callbacks persist metadata while retaining the playable source.
     */
    public function test_activity_callbacks_preserve_active_video_and_learner_tables(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        $service = $this->service();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 5280,
            'microsoftreference' => $this->reference((int)$service->get('id')),
        ]);
        $before = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $data = clone $before;
        $data->instance = $activity->id;
        $data->coursemodule = $activity->cmid;
        $this->assertTrue(videotrack_update_instance($data));
        $after = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        foreach (['microsoftreference', 'videoid', 'youtubeurl', 'videosource', 'durationseconds', 'completionpercent'] as $field) {
            $this->assertEquals($before->$field, $after->$field, $field);
        }
        foreach (['videotrack_seg', 'videotrack_state', 'videotrack_reactev', 'videotrack_acknowledge'] as $table) {
            $this->assertSame(0, $DB->count_records($table));
        }
    }

    /**
     * Invalid metadata aborts a real activity update before changing any stored activity field.
     */
    public function test_invalid_update_does_not_partially_write_the_activity(): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $before = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $data = (object)['instance' => $activity->id, 'name' => 'Should not be written', 'microsoftreference' => 'private-marker'];
        $this->assert_invalid(static fn() => videotrack_update_instance($data));
        $this->assertEquals($before, $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST));
    }

    /**
     * Create only local OAuth configuration; no token or HTTP client is requested.
     *
     * @return issuer Selected local Microsoft service.
     */
    private function service(): issuer {
        $service = new issuer(0, (object)[
            'name' => 'VideoTrack activity reference fixture', 'image' => '', 'servicetype' => 'microsoft',
            'clientid' => 'private-client-marker', 'clientsecret' => 'private-secret-marker',
            'enabled' => true, 'showonloginpage' => issuer::SERVICEONLY,
        ]);
        $service->create();
        set_config('microsoftissuerid', $service->get('id'), 'mod_videotrack');
        return $service;
    }

    /**
     * Use a genuine course role when checking permitted local metadata changes.
     *
     * @param \stdClass $course Course owning the activity.
     */
    private function editing_teacher(\stdClass $course): void {
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
    }

    /**
     * Produce the fixed resource identity under a caller-selected local binding.
     *
     * @param int $issuerid Local service id or zero for an unbound restore.
     * @return string Canonical stored identity.
     */
    private function reference(int $issuerid): string {
        return json_encode([
            'version' => 1, 'provider' => 'microsoft', 'issuerid' => $issuerid,
            'driveid' => 'b!Drive_1', 'itemid' => 'Video-2',
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * Assert a bounded validation error without exposing the submitted marker.
     *
     * @param callable $operation Operation expected to reject its input.
     */
    private function assert_invalid(callable $operation): void {
        try {
            $operation();
            $this->fail('Invalid reference was accepted.');
        } catch (\invalid_parameter_exception $error) {
            $this->assertStringNotContainsString('private-marker', $error->getMessage());
        }
    }
}
