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

use mod_videotrack\local\analytics;
use mod_videotrack\local\course_analytics;
use mod_videotrack\local\focus_policy;
use mod_videotrack\local\instance_settings_policy;
use mod_videotrack\local\report_access;
use mod_videotrack\local\report_support;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Regression coverage for the externally reviewed authorisation and privacy gaps.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(instance_settings_policy::class)]
#[CoversClass(course_analytics::class)]
#[CoversClass(report_access::class)]
#[CoversClass(report_support::class)]
#[CoversClass(focus_policy::class)]
final class security_hardening_test extends \advanced_testcase {
    /**
     * Load the actual module save callbacks for each regression.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        require_once(__DIR__ . '/../lib.php');
    }

    /**
     * Creation uses site defaults rather than forged scalar or checkbox values.
     */
    public function test_creation_policy_uses_defaults_and_clears_aliases(): void {
        $course = $this->getDataGenerator()->create_course();
        $this->restricted_teacher($course);
        set_config('default_allowseekforward', 0, 'mod_videotrack');
        set_config('default_loop', 1, 'mod_videotrack');
        set_config('default_completionpercent', 75, 'mod_videotrack');
        $data = (object)[
            'course' => $course->id, 'durationseconds' => 120,
            'allowseekforward' => 1, 'loopenabled' => 0, 'completionpercent' => 1,
            'playbackspeed_4' => 4, 'html5ctrl_download' => 1,
            'reactionid' => [0], 'reactionlabel' => ['New'], 'reactionrequired' => [1],
        ];
        instance_settings_policy::enforce($data);
        \videotrack_process_playbackspeeds_field($data);
        \videotrack_process_html5controls_field($data);
        $this->assertEquals(0, $data->allowseekforward);
        $this->assertEquals(1, $data->loopenabled);
        $this->assertEquals(75, $data->completionpercent);
        $this->assertSame('', $data->playbackspeeds);
        $this->assertSame('', $data->html5controls);
        $this->assertSame([0], $data->reactionrequired);
    }

    /**
     * Real updates retain locked values and VTT files before caption processing.
     */
    public function test_update_rejects_forged_values_before_file_side_effects(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'videosource' => 'upload', 'captions' => 1,
            'durationseconds' => 120, 'completionpercent' => 75, 'allowseekforward' => 0,
        ]);
        $context = \context_module::instance($activity->cmid);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id, 'component' => 'mod_videotrack',
            'filearea' => 'subtitles', 'itemid' => 0, 'filepath' => '/', 'filename' => 'test.vtt',
        ], "WEBVTT\n\n00:00.000 --> 00:01.000\nTest\n");
        $teacher = $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        $data->allowseekforward = 1;
        $data->completionpercent = 1;
        $data->captions = 0;
        $data->playbackspeed_4 = 4;
        $othercourse = $this->getDataGenerator()->create_course();
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $othercourse->id, $roleid);
        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', \context_course::instance($othercourse->id)));
        $data->course = $othercourse->id;
        $this->assertTrue(\videotrack_update_instance($data));
        $stored = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->assertSame((int)$course->id, (int)$stored->course);
        $this->assertSame(0, (int)$stored->allowseekforward);
        $this->assertSame(75, (int)$stored->completionpercent);
        $this->assertSame(1, (int)$stored->captions);
        $keptfile = get_file_storage()->get_file_by_id($file->get_id());
        $this->assertInstanceOf(\stored_file::class, $keptfile);
        $this->assertSame($file->get_contenthash(), $keptfile->get_contenthash());
    }

    /**
     * Explicitly authorised editors retain the ability to change settings.
     */
    public function test_authorised_update_still_changes_protected_fields(): void {
        global $DB;
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 120, 'completionpercent' => 75,
        ]);
        $data = $this->edit_data($activity);
        $data->completionpercent = 25;
        $data->allowseekforward = 0;
        $this->assertTrue(\videotrack_update_instance($data));
        $stored = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->assertSame(25, (int)$stored->completionpercent);
        $this->assertSame(0, (int)$stored->allowseekforward);
    }

    /**
     * Zero duration cannot silently disable a locked percentage rule.
     */
    public function test_zero_duration_cannot_disable_a_locked_percentage(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 120, 'completionpercent' => 75,
        ]);
        $before = $DB->get_record('videotrack', ['id' => $activity->id]);
        $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        $data->durationseconds = 0;
        try {
            \videotrack_update_instance($data);
            $this->fail('A locked completion rule must reject zero duration.');
        } catch (\moodle_exception $error) {
            $this->assertSame('error:lockedcompletionduration', $error->errorcode);
        }
        $this->assertEquals($before, $DB->get_record('videotrack', ['id' => $activity->id]));
    }

    /**
     * Required flags follow stored reaction ids even after reordering the form.
     */
    public function test_reordered_reactions_preserve_requirements_and_enablement(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'reactionsenabled' => 1,
        ]);
        $reaction = $DB->get_record('videotrack_react', ['videotrackid' => $activity->id], '*', MUST_EXIST);
        $DB->set_field('videotrack_react', 'requiredforcompletion', 1, ['id' => $reaction->id]);
        $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        $data->reactionid = [0, $reaction->id];
        $data->reactionlabel = ['Optional', $reaction->label];
        $data->reactionrequired = [1, 0];
        $data->reactionsenabled = 0;
        $this->assertTrue(\videotrack_update_instance($data));
        $this->assertSame(1, (int)$DB->get_field('videotrack', 'reactionsenabled', ['id' => $activity->id]));
        $this->assertSame(1, (int)$DB->get_field('videotrack_react', 'requiredforcompletion', ['id' => $reaction->id]));
        $this->assertSame(0, (int)$DB->get_field('videotrack_react', 'requiredforcompletion', [
            'videotrackid' => $activity->id, 'label' => 'Optional',
        ]));
    }

    /**
     * Removing an existing type cannot weaken locked reaction completion rules.
     */
    public function test_removing_a_required_reaction_is_rejected(): void {
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'reactionsenabled' => 1, 'requireallreactiontypes' => 1,
        ]);
        $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        $data->reactionlabel = [];
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('error:lockedcompletionreactions', 'mod_videotrack'));
        \videotrack_update_instance($data);
    }

    /**
     * A locked acknowledgement rule cannot be bypassed by disabling its feature.
     */
    public function test_locked_acknowledgement_keeps_its_required_dependencies(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'acknowledgementenabled' => 1,
            'acknowledgementtext' => 'Required statement', 'acknowledgementtiming' => 1,
            'completionacknowledgement' => 1,
        ]);
        $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        $data->acknowledgementenabled = 0;
        $data->acknowledgementtiming = 0;
        $data->completionacknowledgement = 0;
        $this->assertTrue(\videotrack_update_instance($data));
        $stored = $DB->get_record('videotrack', ['id' => $activity->id]);
        $this->assertSame(1, (int)$stored->acknowledgementenabled);
        $this->assertSame(1, (int)$stored->completionacknowledgement);
        $this->assertSame(1, (int)$stored->acknowledgementtiming);
    }

    /**
     * An empty acknowledgement editor is rejected before saving a locked rule.
     */
    public function test_locked_acknowledgement_rejects_an_empty_statement(): void {
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'acknowledgementenabled' => 1,
            'acknowledgementtext' => 'Required statement', 'completionacknowledgement' => 1,
        ]);
        $this->restricted_teacher($course);
        $data = $this->edit_data($activity);
        foreach (['', '<p>&nbsp;</p>', '<span>　</span>'] as $text) {
            $data->acknowledgement_editor = ['text' => $text, 'format' => FORMAT_HTML];
            try {
                \videotrack_update_instance($data);
                $this->fail('A locked acknowledgement must contain visible text.');
            } catch (\moodle_exception $error) {
                $this->assertSame('error:lockedcompletionack', $error->errorcode);
            }
        }
    }

    /**
     * Aggregate-only access masks each metric below the configured floor.
     */
    public function test_aggregate_dashboard_cannot_request_exact_small_cohorts(): void {
        [$course, $activity, $viewer, $students] = $this->analytics_fixture();
        set_config('analyticsminusers', 5, 'mod_videotrack');
        global $CFG;
        require_once($CFG->dirroot . '/group/lib.php');
        foreach ([1, 4, 5] as $size) {
            $group = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
            foreach (array_slice($students, 0, $size) as $student) {
                groups_add_member($group->id, $student->id);
            }
            $row = course_analytics::get_course_rows($course, $viewer->id, 1, $activity->id, $group->id)[$activity->id];
            $this->assertSame(5, $row->summary['minusers']);
            $this->assertSame($size < 5, $row->summary['datasetsuppressed']);
            $this->assertSame($size < 5 ? null : 40.0, $row->summary['averagepercent']);
            foreach ([$row->summary['started'], $row->reactions, $row->notes, $row->bookmarks] as $count) {
                $this->assertSame($size < 5, $count['suppressed']);
                $this->assertSame($size < 5 ? null : $size, $count['eventcount']);
            }
        }
    }

    /**
     * A course-report grant alone never reveals statistics for an activity.
     */
    public function test_course_only_role_without_activity_report_access_gets_no_rows(): void {
        global $DB;
        [$course, $activity, $viewer] = $this->analytics_fixture();
        $context = \context_module::instance($activity->cmid);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'vtsecurityaggregate'], MUST_EXIST);
        assign_capability('mod/videotrack:viewaggregatereport', CAP_PROHIBIT, $roleid, $context->id);
        $this->assertFalse(report_access::can_view_aggregate($context, $viewer->id));
        $this->assertSame([], course_analytics::get_course_rows($course, $viewer->id, 1));
    }

    /**
     * An individual grant applies only to its activity, never the neighbouring one.
     */
    public function test_mixed_module_permissions_resolve_thresholds_independently(): void {
        global $DB;
        [$course, $first, $viewer] = $this->analytics_fixture();
        $second = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'vtsecurityaggregate'], MUST_EXIST);
        assign_capability('mod/videotrack:viewindividualreport', CAP_ALLOW, $roleid, \context_module::instance($first->cmid)->id);
        $rows = course_analytics::get_course_rows($course, $viewer->id, 1);
        $this->assertSame(1, $rows[$first->id]->summary['minusers']);
        $this->assertSame(5, $rows[$second->id]->summary['minusers']);
        $this->assertSame(8, report_access::minimum_users(\context_module::instance($second->cmid), $viewer->id, 8));
    }

    /**
     * Clearing a points grade preserves null, while zero remains a real grade.
     */
    public function test_points_grade_can_be_cleared_without_recording_zero(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id, 'grade' => 100]);
        $this->assertSame(GRADE_UPDATE_OK, \videotrack_set_user_grade($activity, $student->id, 80));
        $this->assertSame(GRADE_UPDATE_OK, \videotrack_set_user_grade($activity, $student->id, null));
        $item = $DB->get_record('grade_items', ['itemmodule' => 'videotrack', 'iteminstance' => $activity->id]);
        $this->assertNull($DB->get_field('grade_grades', 'rawgrade', ['itemid' => $item->id, 'userid' => $student->id]));
        $this->assertNull(\videotrack_get_user_grade($activity, $student->id));
        $this->assertSame(GRADE_UPDATE_OK, \videotrack_set_user_grade($activity, $student->id, 0));
        $this->assertEquals(0, $DB->get_field('grade_grades', 'rawgrade', ['itemid' => $item->id, 'userid' => $student->id]));
    }

    /**
     * Clearing a scale grade does not select the lowest scale item.
     */
    public function test_scale_grade_can_be_cleared(): void {
        global $DB;
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $scale = $this->getDataGenerator()->create_scale(['scale' => 'First,Second,Third']);
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id, 'grade' => -$scale->id]);
        \videotrack_set_user_grade($activity, $student->id, 2);
        \videotrack_set_user_grade($activity, $student->id, null);
        $item = $DB->get_record('grade_items', ['itemmodule' => 'videotrack', 'iteminstance' => $activity->id]);
        $this->assertNull($DB->get_field('grade_grades', 'rawgrade', ['itemid' => $item->id, 'userid' => $student->id]));
        $this->assertNull(\videotrack_get_user_grade($activity, $student->id));
    }

    /**
     * HTML labels are escaped once, while CSV labels keep their original text.
     */
    public function test_user_labels_separate_html_from_plain_export_text(): void {
        $user = $this->getDataGenerator()->create_user();
        $user->firstname = '<img src=x onerror=alert(1)> A&B';
        $user->lastname = 'Example';
        $user->email = 'a&b@example.invalid';
        $map = [$user->id => $user];
        $plain = report_support::user_label($user->id, $map, true);
        $safe = report_support::user_label_html($user->id, $map, true);
        $this->assertSame(fullname($user) . ' (' . $user->email . ')', $plain);
        $this->assertStringNotContainsString('<img', $safe);
        $this->assertSame(s($plain), $safe);
        $this->assertStringNotContainsString('a&b@example.invalid', report_support::user_label_html($user->id, $map, false));
        $this->assertSame($safe, report_support::user_options([[$user->id]], $map, true)[$user->id]);
        $this->assertStringContainsString('&lt;5', report_support::dashboard_count_cell(analytics::count_summary(1, 1, 5), 5));
        $this->assertSame('0', report_support::dashboard_count_cell(analytics::count_summary(0, 0, 5), 5));
    }

    /**
     * Full uninstall removes owned groups even when there are no grade items.
     */
    public function test_uninstall_removes_only_owned_groups_without_grades(): void {
        global $CFG, $DB;
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id, 'grade' => 0]);
        $owned = focus_policy::exception_group($course->id);
        $other = $this->getDataGenerator()->create_group(['courseid' => $course->id, 'name' => $owned->name]);
        $DB->set_field('groups', 'name', 'Renamed accessibility group', ['id' => $owned->id]);
        require_once($CFG->dirroot . '/mod/videotrack/db/uninstall.php');
        \xmldb_videotrack_uninstall();
        $this->assertFalse($DB->record_exists('groups', ['id' => $owned->id]));
        $this->assertTrue($DB->record_exists('groups', ['id' => $other->id]));
        \xmldb_videotrack_uninstall();
        $this->assertTrue($DB->record_exists('groups', ['id' => $other->id]));
        $view = file_get_contents($CFG->dirroot . '/mod/videotrack/view.php');
        $this->assertStringNotContainsString('focus_policy::ensure_exception_group', $view);
    }

    /**
     * The actual creation callback enforces policy when core supplies no form.
     */
    public function test_core_creation_without_form_enforces_the_same_policy(): void {
        global $CFG, $DB;
        $course = $this->getDataGenerator()->create_course();
        $this->restricted_teacher($course);
        set_config('default_allowseekforward', 0, 'mod_videotrack');
        set_config('default_completionpercent', 75, 'mod_videotrack');
        require_once($CFG->dirroot . '/course/modlib.php');
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_videotrack');
        $prepare = new \ReflectionMethod(\testing_module_generator::class, 'prepare_moduleinfo_record');
        $record = $prepare->invoke($generator, (object)[
            'course' => $course->id, 'name' => 'Restricted creation', 'intro' => '', 'introformat' => FORMAT_HTML,
            'videosource' => 'youtube', 'youtubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'durationseconds' => 120, 'allowseekforward' => 1, 'completionpercent' => 1,
        ], []);
        $created = add_moduleinfo($record, $course);
        $stored = $DB->get_record('videotrack', ['id' => $created->instance], '*', MUST_EXIST);
        $this->assertSame(0, (int)$stored->allowseekforward);
        $this->assertSame(75, (int)$stored->completionpercent);
    }

    /**
     * Period-based summaries use the same aggregate privacy floor as all-time rows.
     */
    public function test_period_dashboard_masks_a_single_group_member(): void {
        global $CFG, $DB;
        [$course, $activity, $viewer, $students] = $this->analytics_fixture();
        require_once($CFG->dirroot . '/group/lib.php');
        $group = $this->getDataGenerator()->create_group(['courseid' => $course->id]);
        groups_add_member($group->id, $students[0]->id);
        $now = time();
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid,
            'userid' => $students[0]->id, 'videoid' => 'security-fixture', 'sessionid' => 'security',
            'requestid' => 'security-period', 'wallclockstart' => $now * 1000 - 10000,
            'wallclockend' => $now * 1000, 'videotimestart' => 0, 'videotimeend' => 10,
            'playbackrate' => 1, 'endreason' => 'pause', 'servervalidated' => 1, 'timecreated' => $now,
        ]);
        $rows = course_analytics::get_course_rows($course, $viewer->id, 1, $activity->id, $group->id, $now - 60, $now + 1);
        $row = $rows[$activity->id];
        $this->assertSame(5, $row->summary['minusers']);
        $this->assertTrue($row->summary['datasetsuppressed']);
        $this->assertNull($row->summary['averagepercent']);
        $this->assertNull($row->notes['eventcount']);
        $this->assertNull($row->bookmarks['eventcount']);
    }

    /**
     * A failed grade-item cleanup rolls back earlier grade moves in the same repair.
     */
    public function test_grade_repair_rolls_back_a_mid_batch_failure(): void {
        global $CFG, $DB;
        $this->preventResetByRollback();
        require_once($CFG->dirroot . '/mod/videotrack/db/repairlib.php');
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id, 'grade' => 100]);
        $original = $DB->get_record('grade_items', ['itemmodule' => 'videotrack', 'iteminstance' => $activity->id]);
        $gradeid = $DB->insert_record('grade_grades', (object)[
            'itemid' => $original->id, 'userid' => $student->id, 'rawgrade' => 80,
            'rawgrademax' => 100, 'rawgrademin' => 0, 'finalgrade' => 80,
            'timecreated' => time(), 'timemodified' => time(),
        ]);
        $duplicate = clone $original;
        unset($duplicate->id);
        $duplicateid = $DB->insert_record('grade_items', $duplicate);
        $database = $DB;
        $DB = new class($database) {
            /** @var object Real database used for every operation before the injected failure. */
            private object $database;

            /**
             * Keep the real connection and its delegated transactions.
             *
             * @param object $database Native Moodle database.
             */
            public function __construct(object $database) {
                $this->database = $database;
            }

            /**
             * Fail only after the repair has already moved a user grade.
             *
             * @param string $method Delegated database method.
             * @param array $arguments Method arguments.
             * @return mixed Native database result.
             */
            public function __call(string $method, array $arguments): mixed {
                if ($method === 'delete_records_select' && $arguments[0] === 'grade_items') {
                    throw new \coding_exception('Injected grade-item write failure.');
                }
                return $this->database->{$method}(...$arguments);
            }
        };
        try {
            \videotrack_repair_preproduction_gradebook_rows();
            $this->fail('The injected database failure must be propagated.');
        } catch (\coding_exception $error) {
            $this->assertStringContainsString('Injected grade-item write failure.', $error->getMessage());
        } finally {
            $DB = $database;
        }
        $this->assertSame((int)$original->id, (int)$DB->get_field('grade_grades', 'itemid', ['id' => $gradeid]));
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $duplicateid]));
        $this->assertTrue($DB->record_exists('grade_items', ['id' => $original->id]));
    }

    /**
     * Return an editing teacher whose two override capabilities are revoked.
     *
     * @param \stdClass $course Course receiving the restricted teacher.
     * @return \stdClass Teacher record.
     */
    private function restricted_teacher(\stdClass $course): \stdClass {
        global $DB;
        $teacher = $this->getDataGenerator()->create_user();
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, $roleid);
        $context = \context_course::instance($course->id);
        foreach (['overrideplayersettings', 'overridecompletionsettings'] as $name) {
            assign_capability('mod/videotrack:' . $name, CAP_PROHIBIT, $roleid, $context->id);
            $this->assertFalse(has_capability('mod/videotrack:' . $name, $context, $teacher->id));
        }
        $this->setUser($teacher);
        return $teacher;
    }

    /**
     * Return a complete edit payload with current reaction identities.
     *
     * @param \stdClass $activity Activity returned by the module generator.
     * @return \stdClass Editable data for the real update callback.
     */
    private function edit_data(\stdClass $activity): \stdClass {
        global $DB;
        $data = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $data->instance = $activity->id;
        $data->coursemodule = $activity->cmid;
        $data->reactionid = $data->reactionlabel = $data->reactionrequired = [];
        foreach ($DB->get_records('videotrack_react', ['videotrackid' => $activity->id, 'isdeleted' => 0]) as $reaction) {
            $data->reactionid[] = $reaction->id;
            $data->reactionlabel[] = $reaction->label;
            $data->reactionrequired[] = $reaction->requiredforcompletion;
        }
        return $data;
    }

    /**
     * Build five enrolled learners with states and each kind of interaction.
     *
     * @return array Course, activity, aggregate viewer and learner records.
     */
    private function analytics_fixture(): array {
        global $DB;
        $generator = $this->getDataGenerator();
        set_config('analyticsminusers', 5, 'mod_videotrack');
        $course = $generator->create_course();
        $activity = $generator->create_module('videotrack', ['course' => $course->id, 'bookmarksenabled' => 1]);
        $viewer = $generator->create_user();
        $roleid = create_role('Security aggregate viewer', 'vtsecurityaggregate', '');
        $caps = [
            'moodle/course:view',
            'mod/videotrack:view',
            'mod/videotrack:viewcoursereport',
            'mod/videotrack:viewaggregatereport',
        ];
        foreach ($caps as $cap) {
            assign_capability($cap, CAP_ALLOW, $roleid, \context_system::instance()->id);
        }
        $generator->enrol_user($viewer->id, $course->id, $roleid);
        $studentrole = (int)$DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $students = [];
        $now = time();
        for ($i = 0; $i < 5; $i++) {
            $student = $generator->create_user();
            $generator->enrol_user($student->id, $course->id, $studentrole);
            $students[] = $student;
            $DB->insert_record('videotrack_state', (object)[
                'videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid,
                'userid' => $student->id, 'videoid' => 'security-fixture', 'serverplaybacksessionid' => '', 'lastposition' => 48,
                'durationseconds' => 120, 'uniquecoveredseconds' => 48, 'completionpercent' => 40,
                'intervaljson' => '[[0,48]]', 'iscompleted' => 0, 'timecreated' => $now, 'timemodified' => $now,
            ]);
            foreach (['', 'note', 'bookmark'] as $type) {
                $DB->insert_record('videotrack_reactev', (object)[
                    'videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid,
                    'userid' => $student->id, 'videoid' => 'security-fixture', 'sessionid' => 'security',
                    'reactionid' => 0, 'reactionkey' => '', 'reactionlabel' => '', 'reactiondesc' => '',
                    'notetype' => $type, 'notetext' => 'Test', 'videotime' => 10, 'playbackrate' => 1,
                    'isdeleted' => 0, 'timecreated' => $now, 'timemodified' => $now,
                ]);
            }
        }
        return [$course, $activity, $viewer, $students];
    }
}
