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

use mod_videotrack\local\form_definition;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Native duplication, restore and browser-POST regressions for saved activity settings.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class activity_settings_restore_test extends \advanced_testcase {
    /**
     * Load the native controllers and real form callbacks on an isolated test site.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
        require_once($CFG->dirroot . '/course/lib.php');
        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        require_once($CFG->libdir . '/formslib.php');
        require_once(__DIR__ . '/../lib.php');
    }

    /**
     * Actual single-activity duplication preserves every scalar setting and the same-course Forum.
     */
    public function test_duplicate_preserves_all_settings_without_copying_owned_notes(): void {
        global $DB;

        [$course, $activity, $forum] = $this->fixture();
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id);
        $DB->insert_record('videotrack_reactev', (object)[
            'videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid,
            'userid' => $student->id, 'videoid' => $activity->videoid, 'sessionid' => 'original-owned-note',
            'reactionid' => 0, 'reactionkey' => '', 'reactionlabel' => '', 'reactiondesc' => null,
            'notetext' => 'Original learner note', 'notetype' => 'note', 'videotime' => 15,
            'playbackrate' => 1, 'isdeleted' => 0, 'timecreated' => time(), 'timemodified' => time(),
        ]);
        $cm = get_fast_modinfo($course)->get_cm((int)$activity->cmid);
        if (method_exists(\core_courseformat\local\cmactions::class, 'duplicate')) {
            $copy = \core_courseformat\formatactions::cm((int)$course->id)->duplicate((int)$cm->id);
        } else {
            $copy = \duplicate_module($course, $cm);
        }
        $this->assertNotNull($copy);
        $this->assert_settings((int)$activity->id, (int)$copy->instance, (int)$forum->id);
        $tables = ['videotrack_seg', 'videotrack_state', 'videotrack_reactev', 'videotrack_integrity', 'videotrack_acknowledge'];
        foreach ($tables as $table) {
            $this->assertSame(0, $DB->count_records($table, ['videotrackid' => $copy->instance]), $table);
        }
        $this->assertSame(1, $DB->count_records('videotrack_reactev', ['videotrackid' => $activity->id]));
    }

    /**
     * A Forum created after VideoTrack must be resolved after the complete course restore.
     */
    public function test_course_restore_remaps_a_later_forum_without_user_data(): void {
        global $DB;

        [$course, $activity, $forum] = $this->fixture();
        $restored = $this->roundtrip($course, $activity, true);
        $newforum = $DB->get_record('forum', ['course' => $restored->course, 'name' => $forum->name], '*', MUST_EXIST);
        $this->assertNotEquals($forum->id, $newforum->id);
        $this->assert_settings((int)$activity->id, (int)$restored->id, (int)$newforum->id);
    }

    /**
     * An omitted Forum from another course cannot be rebound by its original numeric id.
     */
    public function test_activity_restore_into_another_course_disables_an_omitted_forum(): void {
        [$course, $activity] = $this->fixture();
        $restored = $this->roundtrip($course, $activity, false);
        $this->assert_settings((int)$activity->id, (int)$restored->id, 0);
    }

    /**
     * Saving a stored activity record does not erase scalar checkbox preferences.
     */
    public function test_record_update_preserves_playback_csv_and_control_preferences(): void {
        global $DB;

        [, $activity] = $this->fixture();
        $before = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $data = clone $before;
        $data->instance = $activity->id;
        $data->coursemodule = $activity->cmid;
        $data->name = 'A different name';
        $this->assertTrue(videotrack_update_instance($data));
        $after = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $fields = ['playbackspeeds', 'csvexportfields', 'csvdelimiter', 'html5controls', 'linkedforumid', 'forumpostingenabled'];
        foreach ($fields as $field) {
            $this->assertSame($before->{$field}, $after->{$field}, $field);
        }
    }

    /**
     * Real PHP query parsing and QuickForm must agree on fractional-rate field names and unchecked values.
     */
    public function test_fractional_speed_names_survive_php_post_parsing_and_quickform_export(): void {
        set_config('playbackspeeds', '0.25,0.5,1,1.5,2', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $form = new \MoodleQuickForm('videotrack_speed_post', 'post', '');
        form_definition::add_playback_speed_elements($form, \context_course::instance($course->id));
        $values = [];
        foreach ($form->getElement('playbackspeedsgroup')->getElements() as $element) {
            $name = $element->getName();
            $rate = str_replace('_', '.', substr($name, strlen('playbackspeed_')));
            $values[$name] = in_array($rate, ['0.25', '1', '1.5'], true) ? $rate : 0;
        }
        parse_str(http_build_query($values), $post);
        $this->assertSame(array_keys($values), array_keys($post));
        $form->updateSubmission($post, []);
        $data = (object)$form->exportValues();
        videotrack_process_playbackspeeds_field($data);
        $this->assertSame('0.25,1,1.5', $data->playbackspeeds);
        foreach ($post as $name => $value) {
            $this->assertObjectNotHasProperty($name, $data);
        }
        $inherited = (object)['playbackspeeds' => '', 'html5controls' => ''];
        foreach (videotrack_get_site_playback_speeds() as $rate) {
            $inherited->{videotrack_playback_speed_field_name((string)$rate)} = $rate;
        }
        foreach (videotrack_get_html5controls($inherited) as $control) {
            $inherited->{'html5ctrl_' . $control} = $control;
        }
        videotrack_process_playbackspeeds_field($inherited);
        videotrack_process_html5controls_field($inherited);
        $this->assertSame('', $inherited->playbackspeeds);
        $this->assertSame('', $inherited->html5controls);
    }

    /**
     * Create an already configured activity, followed by its linked Forum.
     *
     * @return array Course, VideoTrack and Forum records.
     */
    private function fixture(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id, 'durationseconds' => 60]);
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course->id, 'name' => 'Later linked Forum', 'type' => 'general',
        ]);
        // Model previously saved settings independently of form checkbox normalisation.
        $DB->update_record('videotrack', (object)[
            'id' => $activity->id, 'playbackspeeds' => '0.5,1,1.5',
            'csvexportfields' => 'coursefullname,instancename,email,department', 'csvdelimiter' => 'semicolon',
            'html5controls' => 'play,progress,current,volume', 'loopenabled' => 1, 'startmuted' => 1,
            'rewindstep' => 5, 'fastforwardstep' => 10, 'allowseekforward' => 0, 'maxplaybackrate' => 150,
            'studentnotesenabled' => 1, 'bookmarksenabled' => 1,
            'forumpostingenabled' => 1, 'linkedforumid' => $forum->id, 'forumsubjecttemplate' => 'Question at {time}',
        ]);
        return [$course, $activity, $forum];
    }

    /**
     * Execute native backup/restore controllers with configuration-only activity or course backups.
     *
     * @param \stdClass $course Original course.
     * @param \stdClass $activity Original activity.
     * @param bool $wholecourse Include the later Forum.
     * @return \stdClass Restored VideoTrack record.
     */
    private function roundtrip(\stdClass $course, \stdClass $activity, bool $wholecourse): \stdClass {
        global $USER, $DB;

        $backup = new \backup_controller(
            $wholecourse ? \backup::TYPE_1COURSE : \backup::TYPE_1ACTIVITY,
            $wholecourse ? $course->id : $activity->cmid,
            \backup::FORMAT_MOODLE, \backup::INTERACTIVE_NO, \backup::MODE_IMPORT, $USER->id
        );
        try {
            $backup->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $backup->get_plan()->get_setting('users')->set_value(false);
            $backupid = $backup->get_backupid();
            $backup->execute_plan();
        } finally {
            $backup->destroy();
        }
        $courseid = \restore_dbops::create_new_course('Restored settings', 'restored_settings', $course->category);
        $restore = new \restore_controller(
            $backupid, $courseid, \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE
        );
        try {
            $restore->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $restore->get_plan()->get_setting('users')->set_value(false);
            $this->assertTrue($restore->execute_precheck());
            $restore->execute_plan();
        } finally {
            $restore->destroy();
        }
        return $DB->get_record('videotrack', ['course' => $courseid], '*', MUST_EXIST);
    }

    /**
     * Compare every saved scalar except the fields Moodle deliberately assigns to the copy.
     *
     * @param int $sourceid Original activity id.
     * @param int $copyid Copy activity id.
     * @param int $forumid Expected destination Forum, or zero for a missing relation.
     */
    private function assert_settings(int $sourceid, int $copyid, int $forumid): void {
        global $DB;

        $source = $DB->get_record('videotrack', ['id' => $sourceid], '*', MUST_EXIST);
        $copy = $DB->get_record('videotrack', ['id' => $copyid], '*', MUST_EXIST);
        $source->linkedforumid = $forumid;
        $source->forumpostingenabled = (int)($forumid > 0);
        foreach (get_object_vars($source) as $field => $value) {
            if (!in_array($field, ['id', 'course', 'name', 'timemodified', 'timecreated'], true)) {
                $this->assertEquals($value, $copy->{$field}, $field);
            }
        }
    }
}
