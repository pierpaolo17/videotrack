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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

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
}
