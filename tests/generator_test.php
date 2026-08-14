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

/**
 * Test the module generator used by PHPUnit and Behat.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class generator_test extends advanced_testcase {
    /**
     * The generator must create a browser-test-ready activity without UI form interaction.
     */
    public function test_generator_creates_activity_with_learner_features(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_videotrack');
        $activity = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Generated VideoTrack',
            'reactionsenabled' => 1,
            'studentnotesenabled' => 1,
            'bookmarksenabled' => 1,
        ]);

        $this->assertGreaterThan(0, (int)$activity->cmid);
        $this->assertSame('youtube', $activity->videosource);
        $this->assertSame(1, (int)$activity->reactionsenabled);
        $this->assertSame(1, (int)$activity->studentnotesenabled);
        $this->assertSame(1, (int)$activity->bookmarksenabled);
        $reaction = $DB->get_record('videotrack_react', [
            'videotrackid' => $activity->id,
            'reactionkey' => 'behat_test_reaction',
            'isdeleted' => 0,
        ], '*', MUST_EXIST);
        $this->assertSame('Test reaction', $reaction->label);
    }

    /**
     * The generator must be able to create a local HTML5 fixture without public network access.
     */
    public function test_generator_creates_local_html5_fixture(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_videotrack');
        $activity = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Generated HTML5 VideoTrack',
            'behathtml5fixture' => 1,
        ]);

        $this->assertSame('upload', $activity->videosource);
        $context = \context_module::instance((int)$activity->cmid);
        $files = get_file_storage()->get_area_files(
            $context->id,
            'mod_videotrack',
            'videocontent',
            0,
            'id',
            false
        );
        $this->assertCount(1, $files);
        $file = reset($files);
        $this->assertInstanceOf(\stored_file::class, $file);
        $this->assertSame('behat-video.mp4', $file->get_filename());
    }

    /**
     * The generator must resolve a named Forum for deterministic Behat scenarios.
     */
    public function test_generator_links_named_forum_fixture(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', [
            'course' => $course->id,
            'name' => 'Linked Forum',
            'type' => 'general',
        ]);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_videotrack');
        $activity = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Generated Forum VideoTrack',
            'behatlinkedforum' => 'Linked Forum',
        ]);

        $this->assertSame(1, (int)$activity->forumpostingenabled);
        $this->assertSame((int)$forum->id, (int)$activity->linkedforumid);
    }
}
