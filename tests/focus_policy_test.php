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
use mod_videotrack\local\focus_policy;
use mod_videotrack\local\integrity;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the course-local strict-focus accessibility exception.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(focus_policy::class)]
final class focus_policy_test extends advanced_testcase {
    /**
     * The exception group is idempotent, hidden and non-participating.
     */
    public function test_exception_group_is_idempotent_hidden_and_non_participating(): void {
        global $DB;

        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $firstid = focus_policy::ensure_exception_group((int)$course->id);
        $DB->set_field('groups', 'visibility', GROUPS_VISIBILITY_ALL, ['id' => $firstid]);
        $DB->set_field('groups', 'participation', 1, ['id' => $firstid]);
        $secondid = focus_policy::ensure_exception_group((int)$course->id);
        $this->assertSame($firstid, $secondid);

        $group = $DB->get_record('groups', ['id' => $firstid], '*', MUST_EXIST);
        $this->assertSame(focus_policy::EXCEPTION_GROUP_IDNUMBER, $group->idnumber);
        $this->assertSame(GROUPS_VISIBILITY_NONE, (int)$group->visibility);
        $this->assertSame(0, (int)$group->participation);
    }

    /**
     * Membership only downgrades strict blur handling to hidden-only.
     */
    public function test_member_receives_hidden_only_policy_when_site_is_strict(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $member = $generator->create_user();
        $groupid = focus_policy::ensure_exception_group((int)$course->id);
        $generator->create_group_member([
            'groupid' => $groupid,
            'userid' => $member->id,
        ]);

        $this->assertTrue(focus_policy::user_has_exception((int)$course->id, (int)$member->id));
        $this->assertSame(
            integrity::FOCUS_POLICY_HIDDEN_ONLY,
            focus_policy::effective_policy(
                (int)$course->id,
                (int)$member->id,
                integrity::FOCUS_POLICY_STRICT
            )
        );
    }

    /**
     * A non-member keeps the strict site policy.
     */
    public function test_non_member_keeps_strict_policy(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $nonmember = $generator->create_user();
        focus_policy::ensure_exception_group((int)$course->id);

        $this->assertFalse(focus_policy::user_has_exception((int)$course->id, (int)$nonmember->id));
        $this->assertSame(
            integrity::FOCUS_POLICY_STRICT,
            focus_policy::effective_policy(
                (int)$course->id,
                (int)$nonmember->id,
                integrity::FOCUS_POLICY_STRICT
            )
        );
    }

    /**
     * The exception never weakens a site policy that is already hidden-only.
     */
    public function test_hidden_only_site_policy_is_unchanged(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $user = $generator->create_user();

        $this->assertSame(
            integrity::FOCUS_POLICY_HIDDEN_ONLY,
            focus_policy::effective_policy(
                (int)$course->id,
                (int)$user->id,
                integrity::FOCUS_POLICY_HIDDEN_ONLY
            )
        );
    }

    /**
     * Activity lifecycle and restore paths keep the exception group available.
     */
    public function test_activity_lifecycle_and_view_are_wired_to_focus_policy(): void {
        global $CFG, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $this->assertTrue($DB->record_exists('groups', [
            'courseid' => $course->id,
            'idnumber' => focus_policy::EXCEPTION_GROUP_IDNUMBER,
        ]));

        $lib = file_get_contents($CFG->dirroot . '/mod/videotrack/lib.php');
        $view = file_get_contents($CFG->dirroot . '/mod/videotrack/view.php');
        $restore = file_get_contents(
            $CFG->dirroot . '/mod/videotrack/backup/moodle2/restore_videotrack_activity_task.class.php'
        );
        $this->assertIsString($lib);
        $this->assertIsString($view);
        $this->assertIsString($restore);
        $this->assertGreaterThanOrEqual(2, substr_count($lib, 'focus_policy::ensure_exception_group'));
        $this->assertStringContainsString('focus_policy::effective_policy', $view);
        $this->assertStringContainsString('focus_policy::ensure_exception_group', $restore);
    }
}
