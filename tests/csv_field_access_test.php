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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack;

use mod_videotrack\local\csv_export;
use mod_videotrack\local\csv_export_fields;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * CSV permission, preference and actual output regressions.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(csv_export_fields::class)]
final class csv_field_access_test extends \advanced_testcase {
    /**
     * Isolate the permissions and load the custom-profile API.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/user/profile/lib.php');
    }

    /**
     * An administrator can export populated fields outside the identity display list.
     */
    public function test_admin_exports_standard_and_custom_values_outside_identity_display_list(): void {
        global $DB;

        set_config('showuseridentity', 'email');
        set_config('hiddenuserfields', 'department,phone1');
        $custom = $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'vtdepartmentcode', 'name' => 'Department code', 'datatype' => 'text', 'visible' => PROFILE_VISIBLE_NONE,
        ]);
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $context = \context_module::instance($activity->cmid);
        $student = $this->getDataGenerator()->create_user(['department' => 'Physics', 'phone1' => '123456']);
        $this->getDataGenerator()->enrol_user($student->id, $course->id);
        $DB->insert_record('user_info_data', (object)[
            'userid' => $student->id, 'fieldid' => $custom->id, 'data' => 'DEP42', 'dataformat' => FORMAT_PLAIN,
        ]);
        $activity->csvexportfields = 'department,phone1,profile_field_vtdepartmentcode';
        $selected = csv_export::activity_fields($activity, $context);
        $this->assertSame(explode(',', $activity->csvexportfields), $selected);
        $this->assertSame([], csv_export::unavailable_reasons($context));
        $users = csv_export::load_users([(int)$student->id], $selected);
        $values = csv_export::identity_values($selected, $course, $activity, $users[$student->id], '', $activity->cmid, $context);
        $handle = fopen('php://temp', 'w+');
        csv_export::write_row($handle, $values, ',');
        rewind($handle);
        $row = fgetcsv($handle, 0, ',', '"', '');
        fclose($handle);
        $this->assertSame([$student->lastname, $student->firstname, 'Physics', '123456', 'DEP42'], $row);
    }

    /**
     * A scoped viewer keeps hidden/custom restrictions even when the site preselects them.
     */
    public function test_scoped_viewer_gets_precise_reasons_and_only_visible_profile_fields(): void {
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', 'idnumber');
        $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'vtpublic', 'name' => 'Public field', 'datatype' => 'text', 'visible' => PROFILE_VISIBLE_ALL,
        ]);
        $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'vtprivate', 'name' => 'Private field', 'datatype' => 'text', 'visible' => PROFILE_VISIBLE_PRIVATE,
        ]);
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $this->viewer($context);
        $options = csv_export::field_options($context);
        $this->assertArrayHasKey('email', $options);
        $this->assertArrayHasKey('city', $options);
        $this->assertArrayHasKey('profile_field_vtpublic', $options);
        $this->assertArrayNotHasKey('idnumber', $options);
        $this->assertArrayNotHasKey('profile_field_vtprivate', $options);
        $reasons = csv_export::unavailable_reasons($context);
        $this->assertSame('report:csvfield_unavailable_hidden', $reasons['idnumber']);
        $this->assertSame('report:csvfield_unavailable_configuration', $reasons['phone1']);
        $this->assertSame('report:csvfield_unavailable_profile', $reasons['profile_field_vtprivate']);
    }

    /**
     * Full-profile permission grants the administrative scalar fields without changing global identity settings.
     */
    public function test_full_profile_permission_is_effective_only_in_its_course(): void {
        set_config('showuseridentity', 'email');
        set_config('hiddenuserfields', '');
        $course = $this->getDataGenerator()->create_course();
        $other = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $role = $this->viewer($context);
        assign_capability('moodle/user:viewalldetails', CAP_ALLOW, $role, $context->id);
        accesslib_clear_all_caches_for_unit_testing();
        $allowed = csv_export::field_options($context);
        $outside = csv_export::field_options(\context_course::instance($other->id));
        foreach (['username', 'department', 'institution', 'idnumber'] as $field) {
            $this->assertArrayHasKey($field, $allowed);
            $this->assertArrayNotHasKey($field, $outside);
        }
    }

    /**
     * Disabled selections survive an unrelated save while runtime CSV still omits their values.
     */
    public function test_unavailable_stored_preferences_survive_but_do_not_authorise_export(): void {
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', 'idnumber');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $this->viewer($context);
        $previous = (object)['csvexportfields' => 'idnumber,email'];
        $data = (object)[csv_export::form_element_name('email') => 1];
        csv_export::process_form_fields($data, $context, $previous);
        $this->assertSame('idnumber,email', $data->csvexportfields);
        $selected = csv_export::activity_fields($data, $context);
        $this->assertSame(['email'], $selected);
        $this->assertNotContains(get_string('report:csvfield_idnumber', 'mod_videotrack'), csv_export::identity_headers($selected));
    }

    /**
     * An attacker cannot add a newly selected unavailable field through a forged checkbox.
     */
    public function test_forged_unavailable_checkbox_does_not_add_a_new_preference(): void {
        set_config('showuseridentity', 'email');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $this->viewer($context);
        $data = (object)[csv_export::form_element_name('phone1') => 1];
        csv_export::process_form_fields($data, $context, (object)['csvexportfields' => 'none']);
        $this->assertSame('none', $data->csvexportfields);
    }

    /**
     * Stored records, explicit none and inherited selections retain their distinct meaning.
     */
    public function test_scalar_preferences_inheritance_and_explicit_none_remain_distinct(): void {
        set_config('csvexportfields', 'coursefullname,email', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        foreach (['', 'none', 'department,email'] as $raw) {
            $data = (object)['csvexportfields' => $raw];
            csv_export::process_form_fields($data, $context);
            $this->assertSame($raw, $data->csvexportfields);
        }
        $data = (object)[
            csv_export::form_element_name('coursefullname') => 1,
            csv_export::form_element_name('email') => 1,
        ];
        csv_export::process_form_fields($data, $context, (object)['csvexportfields' => '']);
        $this->assertSame('', $data->csvexportfields);
        $none = (object)[csv_export::form_element_name('email') => 0];
        csv_export::process_form_fields($none, $context, (object)['csvexportfields' => 'none']);
        $this->assertSame('none', $none->csvexportfields);
    }

    /**
     * Create a viewer with explicit field permissions, independent of archetype defaults.
     *
     * @param \context $context Course context.
     * @return int Assigned role id.
     */
    private function viewer(\context $context): int {
        $user = $this->getDataGenerator()->create_user();
        $role = create_role('VideoTrack CSV scope', 'vtcsvscope', '');
        role_assign($role, $user->id, $context->id);
        foreach (['moodle/site:viewuseridentity', 'moodle/user:viewdetails'] as $capability) {
            assign_capability($capability, CAP_ALLOW, $role, $context->id);
        }
        foreach (['moodle/user:viewalldetails', 'moodle/course:viewhiddenuserfields', 'moodle/course:useremail'] as $capability) {
            assign_capability($capability, CAP_PREVENT, $role, $context->id);
        }
        $this->setUser($user);
        return $role;
    }
}
