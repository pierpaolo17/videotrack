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
use context_module;
use mod_videotrack\local\report_access;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for granular activity-report capabilities.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(report_access::class)]
final class report_access_test extends advanced_testcase {
    /**
     * Aggregate-only roles stay separated from individual detail and exports.
     */
    public function test_aggregate_only_role_keeps_individual_access_separate(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = context_module::instance($forum->cmid);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack aggregate assistant', 'videotrackaggregateassistant', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:viewaggregatereport', CAP_ALLOW, $roleid, $context->id);
        assign_capability('mod/videotrack:exportaggregatereport', CAP_ALLOW, $roleid, $context->id);

        $this->assertTrue(report_access::can_view_aggregate($context, $user->id));
        $this->assertTrue(report_access::can_export_aggregate($context, $user->id));
        $this->assertFalse(report_access::can_view_individual($context, $user->id));
        $this->assertFalse(report_access::can_export_individual($context, $user->id));
        $this->assertFalse(report_access::has_legacy_full_access($context, $user->id));
    }

    /**
     * Individual report visibility also permits aggregate viewing but not downloads.
     */
    public function test_individual_view_implies_aggregate_view_without_export_permissions(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = context_module::instance($forum->cmid);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack individual viewer', 'videotrackindividualviewer', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:viewindividualreport', CAP_ALLOW, $roleid, $context->id);

        $this->assertTrue(report_access::can_view_individual($context, $user->id));
        $this->assertTrue(report_access::can_view_aggregate($context, $user->id));
        $this->assertFalse(report_access::can_export_aggregate($context, $user->id));
        $this->assertFalse(report_access::can_export_individual($context, $user->id));
    }

    /**
     * The historical viewreport capability remains a backwards-compatible full grant.
     */
    public function test_legacy_viewreport_remains_full_access(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $forum = $generator->create_module('forum', ['course' => $course->id]);
        $context = context_module::instance($forum->cmid);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack legacy report viewer', 'videotracklegacyviewer', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:viewreport', CAP_ALLOW, $roleid, $context->id);

        $this->assertTrue(report_access::has_legacy_full_access($context, $user->id));
        $this->assertTrue(report_access::can_view_aggregate($context, $user->id));
        $this->assertTrue(report_access::can_view_individual($context, $user->id));
        $this->assertTrue(report_access::can_export_aggregate($context, $user->id));
        $this->assertTrue(report_access::can_export_individual($context, $user->id));
    }
}
