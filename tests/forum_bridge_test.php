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

use advanced_testcase;
use mod_videotrack\local\forum_bridge;
use moodle_exception;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for the optional Forum bridge.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(forum_bridge::class)]
final class forum_bridge_test extends advanced_testcase {
    /**
     * Loads global VideoTrack helpers required by the bridge.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * A disabled integration fails before any Forum record is accessed.
     */
    public function test_disabled_integration_is_rejected(): void {
        $course = (object)['id' => 12];
        $videotrack = (object)[
            'forumpostingenabled' => 0,
            'linkedforumid' => 0,
        ];

        $this->expectException(moodle_exception::class);
        forum_bridge::resolve_destination($videotrack, $course);
    }

    /**
     * An enrolled student can resolve a compatible Forum in the same course.
     */
    public function test_enrolled_student_can_resolve_compatible_forum(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        $forum = $generator->create_module('forum', [
            'course' => $course->id,
            'type' => 'general',
        ]);
        $this->setUser($user);

        $destination = forum_bridge::resolve_destination((object)[
            'forumpostingenabled' => 1,
            'linkedforumid' => $forum->id,
        ], $course);

        $this->assertSame((int)$forum->id, (int)$destination['forum']->id);
        $this->assertNotEmpty($destination['groupoptions']);
    }

    /**
     * A learner cannot attach a Forum discussion to an unwatched timestamp.
     */
    public function test_learner_forum_timestamp_requires_watched_progress(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);

        $this->expectException(moodle_exception::class);
        forum_bridge::validate_timestamp_access((object)['id' => 2001], $context, $user->id, 25.0);
    }

    /**
     * A learner may attach a Forum discussion to server-validated watched progress.
     */
    public function test_learner_forum_timestamp_accepts_watched_progress(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('strictsessionvalidation', 1, 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);
        $this->insert_validated_segment(2002, $course->id, $forum->cmid, $user->id, 10.0, 30.0);

        forum_bridge::validate_timestamp_access(
            (object)['id' => 2002],
            $context,
            $user->id,
            25.0,
            'different-forum-session'
        );
        $this->addToAssertionCount(1);
    }

    /**
     * An allowed forward seek can authorise the Forum timestamp from validated same-session progress.
     */
    public function test_learner_forum_timestamp_accepts_allowed_forward_seek_session(): void {
        global $DB;

        $this->resetAfterTest();
        set_config('heartbeatinterval', 30, 'mod_videotrack');
        set_config('strictsessionvalidation', 1, 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);
        $sessionid = 'forum-forward-session';
        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => 2005,
            'courseid' => $course->id,
            'cmid' => $forum->cmid,
            'userid' => $user->id,
            'videoid' => 'forum-forward-video',
            'sessionid' => $sessionid,
            'requestid' => bin2hex(random_bytes(16)),
            'wallclockstart' => time(),
            'wallclockend' => time(),
            'videotimestart' => 59.0,
            'videotimeend' => 60.0,
            'playbackrate' => 1.0,
            'endreason' => 'interaction',
            'servervalidated' => 1,
            'timecreated' => time(),
        ]);

        forum_bridge::validate_timestamp_access(
            (object)['id' => 2005, 'allowseekforward' => 1],
            $context,
            $user->id,
            60.0,
            $sessionid
        );
        $this->addToAssertionCount(1);
    }

    /**
     * A pure report viewer can reference a timestamp without learner playback evidence.
     */
    public function test_teacher_forum_timestamp_bypasses_learner_watched_check(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $teacher = $generator->create_user();
        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);
        $generator->enrol_user($teacher->id, $course->id, $teacherroleid);
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);

        forum_bridge::validate_timestamp_access((object)['id' => 2003], $context, $teacher->id, 50.0);
        $this->addToAssertionCount(1);
    }

    /**
     * A dual-role learner remains subject to learner timestamp validation.
     */
    public function test_dual_role_learner_does_not_bypass_watched_check(): void {
        global $DB;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();
        $studentroleid = $DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $teacherroleid = $DB->get_field('role', 'id', ['shortname' => 'teacher'], MUST_EXIST);
        $generator->enrol_user($user->id, $course->id, $studentroleid);
        role_assign($teacherroleid, $user->id, \context_course::instance($course->id)->id);
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = \context_module::instance($forum->cmid);

        $this->assertTrue(has_capability('mod/videotrack:participate', $context, $user->id, false));
        $this->assertTrue(has_capability('mod/videotrack:viewreport', $context, $user->id));
        $this->expectException(moodle_exception::class);
        forum_bridge::validate_timestamp_access((object)['id' => 2004], $context, $user->id, 15.0);
    }

    /**
     * The Forum composer invokes timestamp validation before presenting the form.
     */
    public function test_forum_composer_invokes_timestamp_access_validation(): void {
        $source = file_get_contents(__DIR__ . '/../forum_post.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('forum_bridge::validate_timestamp_access(', $source);
        $this->assertStringContainsString("optional_param('sessionid', '', PARAM_ALPHANUMEXT)", $source);
    }

    /**
     * Inserts one server-validated watched segment fixture.
     *
     * @param int $videotrackid VideoTrack activity id.
     * @param int $courseid Course id.
     * @param int $cmid Course-module id.
     * @param int $userid User id.
     * @param float $start Segment start.
     * @param float $end Segment end.
     */
    private function insert_validated_segment(
        int $videotrackid,
        int $courseid,
        int $cmid,
        int $userid,
        float $start,
        float $end
    ): void {
        global $DB;

        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => $videotrackid,
            'courseid' => $courseid,
            'cmid' => $cmid,
            'userid' => $userid,
            'videoid' => 'forum-video',
            'sessionid' => 'forum-session',
            'requestid' => bin2hex(random_bytes(16)),
            'wallclockstart' => time() - 5,
            'wallclockend' => time(),
            'videotimestart' => $start,
            'videotimeend' => $end,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => time(),
        ]);
    }
}
