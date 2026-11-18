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

use mod_videotrack\external\delete_bookmark;
use mod_videotrack\external\delete_note;
use mod_videotrack\external\helper;
use mod_videotrack\external\save_segment;
use mod_videotrack\external\start_playback;
use mod_videotrack\local\video_source_policy;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for site-wide video source availability.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(video_source_policy::class)]
#[CoversClass(helper::class)]
#[CoversClass(start_playback::class)]
#[CoversClass(save_segment::class)]
#[CoversClass(delete_note::class)]
#[CoversClass(delete_bookmark::class)]
final class video_source_policy_test extends \advanced_testcase {
    /** @var array Original request fields restored after every test. */
    private array $originalpost;

    /**
     * Isolate configuration changes and load real activity callbacks.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        $this->resetAfterTest();
        $this->originalpost = $_POST;
        $this->setAdminUser();
        require_once($CFG->dirroot . '/mod/videotrack/lib.php');
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');
    }

    /**
     * Restore submitted fields even after an expected rejection.
     */
    protected function tearDown(): void {
        $_POST = $this->originalpost;
        parent::tearDown();
    }

    /**
     * Missing settings preserve all three supported sources on existing sites.
     */
    public function test_missing_settings_preserve_defaults(): void {
        foreach (video_source_policy::SOURCES as $source) {
            unset_config('enable' . $source, 'mod_videotrack');
            $this->assertTrue(video_source_policy::is_enabled($source));
            video_source_policy::require_enabled($source);
        }
        $this->assertSame(['youtube', 'vimeo', 'upload'], array_keys(video_source_policy::options()));
        foreach (['', 'html5', 'zoom', 'microsoft', 'google', 'unknown'] as $source) {
            $this->assertFalse(video_source_policy::is_enabled($source));
        }
    }

    /**
     * Each toggle is independent and malformed stored values cannot enable it.
     */
    public function test_switches_are_independent_and_fail_closed(): void {
        foreach (video_source_policy::SOURCES as $source) {
            set_config('enable' . $source, 0, 'mod_videotrack');
            foreach (video_source_policy::SOURCES as $candidate) {
                $this->assertSame($source !== $candidate, video_source_policy::is_enabled($candidate));
            }
            set_config('enable' . $source, 'unexpected', 'mod_videotrack');
            $this->assertFalse(video_source_policy::is_enabled($source));
            set_config('enable' . $source, 1, 'mod_videotrack');
            $this->assertTrue(video_source_policy::is_enabled($source));
        }
    }

    /**
     * Editing retains the original disabled value without offering it for creation.
     */
    public function test_options_preserve_existing_disabled_source(): void {
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assertSame(['vimeo', 'upload'], array_keys(video_source_policy::options()));
        $editing = video_source_policy::options('youtube');
        $this->assertSame(['youtube', 'vimeo', 'upload'], array_keys($editing));
        $this->assertSame(
            get_string('source:disabledoption', 'mod_videotrack', get_string('source:youtube', 'mod_videotrack')),
            $editing['youtube']
        );
        $this->assertFalse(video_source_policy::is_enabled('youtube'));
        $this->assertArrayNotHasKey('zoom', video_source_policy::options('zoom'));
    }

    /**
     * All-off has an explicit empty placeholder rather than a disabled fallback.
     */
    public function test_all_disabled_sources_have_no_creation_fallback(): void {
        foreach (video_source_policy::SOURCES as $source) {
            set_config('enable' . $source, 0, 'mod_videotrack');
        }
        $this->assertSame(
            ['' => get_string('source:noneenabled', 'mod_videotrack')],
            video_source_policy::options()
        );
        $this->assertSame(['upload'], array_keys(video_source_policy::options('upload')));
    }

    /**
     * Form validation rejects a forged disabled source before media validation.
     */
    public function test_form_rejects_disabled_and_empty_selections(): void {
        $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(\mod_videotrack_mod_form::class, 'source_errors');
        foreach (video_source_policy::SOURCES as $source) {
            set_config('enable' . $source, 0, 'mod_videotrack');
            $errors = $method->invoke($form, ['videosource' => $source, 'youtubeurl' => 'invalid']);
            $this->assertSame(['videosource'], array_keys($errors));
            $this->assertSame(
                get_string('error:videosourcedisabled', 'mod_videotrack', get_string('source:' . $source, 'mod_videotrack')),
                $errors['videosource']
            );
        }
        $this->assertSame(
            ['videosource' => get_string('source:noneenabled', 'mod_videotrack')],
            $method->invoke($form, ['videosource' => ''])
        );
    }

    /**
     * A direct create callback cannot bypass the policy with no Moodle form.
     */
    public function test_create_callback_rejects_disabled_source_before_insert(): void {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        set_config('enableyoutube', 0, 'mod_videotrack');
        $data = (object)[
            'course' => $course->id,
            'name' => 'Forged source',
            'videosource' => 'youtube',
            'youtubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ];
        $this->assert_disabled(static fn() => \videotrack_add_instance($data));
        $this->assertFalse($DB->record_exists('videotrack', ['course' => $course->id]));
    }

    /**
     * A direct update rejection preserves the entire original activity row.
     */
    public function test_update_callback_preserves_disabled_activity(): void {
        global $DB;

        $activity = $this->fixture();
        $before = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->setAdminUser();
        set_config('enableyoutube', 0, 'mod_videotrack');
        $data = clone $before;
        $data->instance = $activity->id;
        $data->name = 'Must not be persisted';
        $this->assert_disabled(static fn() => \videotrack_update_instance($data));
        $this->assertEquals($before, $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST));
    }

    /**
     * Teachers can explicitly switch an existing activity to an enabled source.
     */
    public function test_update_can_switch_to_an_enabled_source(): void {
        global $DB;

        $activity = $this->fixture();
        $this->setAdminUser();
        set_config('enableyoutube', 0, 'mod_videotrack');
        $data = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $data->instance = $activity->id;
        $data->videosource = 'vimeo';
        $data->vimeourl = 'https://vimeo.com/987654321';
        $this->assertTrue(\videotrack_update_instance($data));
        $updated = $DB->get_record('videotrack', ['id' => $activity->id], '*', MUST_EXIST);
        $this->assertSame('vimeo', $updated->videosource);
        $this->assertSame('987654321', $updated->videoid);
    }

    /**
     * Disabled sources reject learner context for writes but retain owner deletion access.
     */
    public function test_context_guard_preserves_owner_deletion_access(): void {
        $activity = $this->fixture();
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assert_disabled(static fn() => helper::load_and_validate_context((int)$activity->cmid));
        $loaded = helper::load_and_validate_context((int)$activity->cmid, false);
        $this->assertSame((int)$activity->id, (int)$loaded['videotrack']->id);
    }

    /**
     * Starting playback after a toggle cannot create learner state.
     */
    public function test_start_playback_is_rejected_before_state_write(): void {
        global $DB;

        $activity = $this->fixture();
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assert_disabled(static fn() => start_playback::execute(
            (int)$activity->cmid,
            md5('source-policy-session'),
            md5('source-policy-start'),
            0.0
        ));
        $this->assertFalse($DB->record_exists('videotrack_state', ['videotrackid' => $activity->id]));
    }

    /**
     * A tab opened before disabling cannot submit new segments or alter saved state.
     */
    public function test_stale_tab_cannot_submit_segments_after_disabling(): void {
        global $DB;

        $activity = $this->fixture();
        start_playback::execute((int)$activity->cmid, md5('source-policy-session'), md5('source-policy-open'), 0.0);
        $before = $DB->get_records('videotrack_state', ['videotrackid' => $activity->id]);
        $this->assertNotEmpty($before);
        set_config('enableyoutube', 0, 'mod_videotrack');
        $end = (int)round(microtime(true) * 1000);
        $this->assert_disabled(static fn() => save_segment::execute(
            (int)$activity->cmid,
            md5('source-policy-session'),
            md5('source-policy-segment'),
            0.0,
            1.0,
            $end - 1000,
            $end,
            1.0,
            'heartbeat',
            120.0
        ));
        $this->assertSame(0, $DB->count_records('videotrack_seg', ['videotrackid' => $activity->id]));
        $this->assertEquals($before, $DB->get_records('videotrack_state', ['videotrackid' => $activity->id]));
    }

    /**
     * Owners can still permanently erase existing personal content while disabled.
     */
    public function test_disabled_source_does_not_prevent_personal_erasure(): void {
        global $DB, $USER;

        $activity = $this->fixture();
        $ids = [];
        foreach (['note', 'bookmark'] as $type) {
            $ids[$type] = $DB->insert_record('videotrack_reactev', (object)[
                'videotrackid' => $activity->id,
                'courseid' => $activity->course,
                'cmid' => $activity->cmid,
                'userid' => $USER->id,
                'videoid' => $activity->videoid,
                'sessionid' => md5('source-policy-session'),
                'reactionid' => 0,
                'reactionkey' => '',
                'reactionlabel' => '',
                'notetype' => $type,
                'notetext' => 'Private saved item',
                'videotime' => 0,
                'playbackrate' => 1,
                'isdeleted' => 0,
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
        }
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assertTrue(delete_note::execute((int)$activity->cmid, (int)$ids['note'])['deleted']);
        $this->assertTrue(delete_bookmark::execute((int)$activity->cmid, (int)$ids['bookmark'])['deleted']);
        $this->assertFalse($DB->record_exists('videotrack_reactev', ['videotrackid' => $activity->id]));
    }

    /**
     * Direct media access is blocked while stored video and caption files are retained.
     */
    public function test_uploaded_media_access_follows_policy_without_deleting_files(): void {
        $activity = $this->fixture('upload');
        $context = \context_module::instance((int)$activity->cmid);
        $file = get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_videotrack',
            'filearea' => 'videocontent',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'retained-video.mp4',
        ], 'Stored fixture');
        $cm = (object)['instance' => $activity->id];
        $this->assertTrue(\videotrack_pluginfile_area_available($cm, 'videocontent'));
        set_config('enableupload', 0, 'mod_videotrack');
        $this->assertFalse(\videotrack_pluginfile_area_available($cm, 'videocontent'));
        $this->assertFalse(\videotrack_pluginfile_area_available($cm, 'subtitles'));
        $this->assertTrue(\videotrack_pluginfile_area_available($cm, 'poster'));
        $this->assertSame('Stored fixture', $file->get_content());
        set_config('enableupload', 1, 'mod_videotrack');
        $this->assertTrue(\videotrack_pluginfile_area_available($cm, 'videocontent'));
        $this->assertSame('Stored fixture', $file->get_content());
    }

    /**
     * The administrator sees affected counts even when a source is disabled.
     */
    public function test_usage_counts_include_disabled_activities(): void {
        $this->fixture();
        $this->fixture();
        $this->fixture('vimeo');
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assertSame(['youtube' => 2, 'vimeo' => 1, 'upload' => 0], video_source_policy::usage_counts());
    }

    /**
     * Re-enabling restores access to the same media identity and learner state.
     */
    public function test_reenable_preserves_media_identity_and_state(): void {
        global $DB;

        $activity = $this->fixture();
        start_playback::execute((int)$activity->cmid, md5('source-policy-session'), md5('source-policy-restore'), 0.0);
        $before = $DB->get_records('videotrack_state', ['videotrackid' => $activity->id]);
        set_config('enableyoutube', 0, 'mod_videotrack');
        $this->assert_disabled(static fn() => helper::load_and_validate_context((int)$activity->cmid));
        set_config('enableyoutube', 1, 'mod_videotrack');
        $loaded = helper::load_and_validate_context((int)$activity->cmid);
        $this->assertSame($activity->videoid, $loaded['videotrack']->videoid);
        $this->assertEquals($before, $DB->get_records('videotrack_state', ['videotrackid' => $activity->id]));
    }

    /**
     * Create a real activity and an enrolled learner with a valid request.
     *
     * @param string $source Supported video source.
     * @return \stdClass Activity record with its course-module id.
     */
    private function fixture(string $source = 'youtube'): \stdClass {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $activity = $generator->create_module('videotrack', [
            'course' => $course->id,
            'videosource' => $source,
            'vimeourl' => 'https://vimeo.com/987654321',
            'studentnotesenabled' => 1,
            'bookmarksenabled' => 1,
        ]);
        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);
        $_POST['sesskey'] = sesskey();
        return $activity;
    }

    /**
     * Assert the exact policy rejection rather than an unrelated validation error.
     *
     * @param callable $operation Real operation expected to reject a disabled source.
     */
    private function assert_disabled(callable $operation): void {
        try {
            $operation();
        } catch (\moodle_exception $error) {
            $this->assertSame('error:videosourcedisabled', $error->errorcode);
            return;
        }
        $this->fail('A disabled source was accepted.');
    }
}
