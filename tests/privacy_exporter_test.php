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

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use mod_videotrack\privacy\event_exporter;
use mod_videotrack\privacy\exporter;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Checks the actual Moodle privacy writer paths, values and bounded event chunks.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
#[CoversClass(exporter::class)]
#[CoversClass(event_exporter::class)]
final class privacy_exporter_test extends \advanced_testcase {
    /**
     * State, segments and all six interaction groups retain their export contract.
     */
    public function test_state_segments_and_deleted_event_categories_are_exported_for_approved_user(): void {
        global $DB;

        $this->resetAfterTest();
        [$activity, $context] = $this->create_activity();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $time = 2000000000;
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => $activity->id, 'courseid' => $activity->course,
            'cmid' => $context->instanceid, 'userid' => $user->id,
            'videoid' => 'exporttest', 'serverplaybacksessionid' => '',
            'intervaljson' => '[[0,83],[165,190],[3,2]]', 'iscompleted' => 1,
            'serverlastactivity' => $time * 1000,
            'timecreated' => $time, 'timemodified' => $time,
        ]);
        $segment = [
            'videotrackid' => $activity->id, 'courseid' => $activity->course,
            'cmid' => $context->instanceid, 'userid' => $user->id,
            'videoid' => 'exporttest', 'sessionid' => 'private-session',
            'requestid' => 'first', 'wallclockstart' => $time,
            'wallclockend' => $time + 5, 'videotimestart' => 0,
            'videotimeend' => 5, 'timecreated' => $time,
        ];
        $DB->insert_record('videotrack_seg', (object)$segment);
        $segment['userid'] = $other->id;
        $segment['requestid'] = 'other';
        $DB->insert_record('videotrack_seg', (object)$segment);

        $labels = [
            ['reaction', 0, 'active'], ['reaction', 1, 'deleted'],
            ['note', 0, 'notes'], ['note', 1, 'deletednotes'],
            ['bookmark', 0, 'bookmarks'], ['bookmark', 1, 'deletedbookmarks'],
        ];
        foreach ($labels as $index => [$type, $deleted, $label]) {
            $this->insert_event($activity, $context, $user->id, $type, $deleted, $label, $time + $index);
        }
        $this->insert_event($activity, $context, $other->id, 'note', 0, 'other-user', $time + 10);

        writer::reset();
        exporter::export_user_data(new approved_contextlist($user, 'mod_videotrack', [$context->id]));
        $written = writer::with_context($context);
        $watch = get_string('watch', 'mod_videotrack');
        $state = $written->get_data([$watch, get_string('privacy:state', 'mod_videotrack')])->state;
        $this->assertSame('0:00-1:23, 2:45-3:10', $state->intervaljson);
        $this->assertSame(transform::datetime($time), $state->serverlastactivity);
        $this->assertSame(transform::yesno(true), $state->iscompleted);
        $this->assertArrayNotHasKey('userid', (array)$state);
        $segments = $written->get_data([$watch, get_string('privacy:segmentschunk', 'mod_videotrack', 1)])->segments;
        $this->assertCount(1, $segments);
        $this->assertSame('first', $segments[0]->requestid);
        $this->assertArrayNotHasKey('sessionid', (array)$segments[0]);
        $this->assertSame(
            get_string('privacy:videoid_export_note', 'mod_videotrack', 'exporttest'),
            $segments[0]->videoid
        );

        $categories = [
            ['reactionsheader', 'privacy:reactionsactivechunk', 'events', 'active', false],
            ['reactionsheader', 'privacy:reactionsdeletedchunk', 'events', 'deleted', true],
            ['studentnotes_title', 'privacy:notesactivechunk', 'notes', 'notes', false],
            ['studentnotes_title', 'privacy:notesdeletedchunk', 'notes', 'deletednotes', true],
            ['bookmarks_title', 'privacy:bookmarksactivechunk', 'bookmarks', 'bookmarks', false],
            ['bookmarks_title', 'privacy:bookmarksdeletedchunk', 'bookmarks', 'deletedbookmarks', true],
        ];
        foreach ($categories as [$title, $chunk, $field, $label, $deleted]) {
            $path = [get_string($title, 'mod_videotrack'), get_string($chunk, 'mod_videotrack', 1)];
            $data = $written->get_data($path);
            $this->assertCount(1, $data->$field);
            $event = $data->{$field}[0];
            $this->assertSame($label, $event->notetext);
            $this->assertSame(transform::yesno($deleted), $event->isdeleted);
            $this->assertArrayNotHasKey('userid', (array)$event);
            $this->assertArrayNotHasKey('reactionid', (array)$event);
        }
    }

    /**
     * The 500-row boundary retains both completed and partial chunk numbering.
     */
    public function test_event_chunks_keep_insertion_order_and_separate_counters(): void {
        $this->resetAfterTest();
        [$activity, $context] = $this->create_activity();
        $user = $this->getDataGenerator()->create_user();
        for ($index = 0; $index < 501; $index++) {
            $this->insert_event($activity, $context, $user->id, '', 0, (string)$index, 2000000000 + $index);
        }
        $this->insert_event($activity, $context, $user->id, 'note', 1, 'deleted note', 2000000501);

        writer::reset();
        exporter::export_user_data(new approved_contextlist($user, 'mod_videotrack', [$context->id]));
        $written = writer::with_context($context);
        $title = get_string('reactionsheader', 'mod_videotrack');
        $first = $written->get_data([$title, get_string('privacy:reactionsactivechunk', 'mod_videotrack', 1)]);
        $second = $written->get_data([$title, get_string('privacy:reactionsactivechunk', 'mod_videotrack', 2)]);
        $this->assertCount(500, $first->events);
        $this->assertSame('0', $first->events[0]->notetext);
        $this->assertSame('499', $first->events[499]->notetext);
        $this->assertCount(1, $second->events);
        $this->assertSame('500', $second->events[0]->notetext);
        $notepath = [
            get_string('studentnotes_title', 'mod_videotrack'),
            get_string('privacy:notesdeletedchunk', 'mod_videotrack', 1),
        ];
        $this->assertSame('deleted note', $written->get_data($notepath)->notes[0]->notetext);
    }

    /**
     * Create a real module context using the same minimal fixture as retention tests.
     *
     * @return array Activity and module context.
     */
    private function create_activity(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course(['enablecompletion' => 1]);
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $time = time();
        $id = $DB->insert_record('videotrack', (object)[
            'course' => $course->id, 'name' => 'Privacy export test',
            'videoid' => 'exporttest', 'videosource' => 'youtube',
            'durationseconds' => 600, 'timecreated' => $time, 'timemodified' => $time,
        ]);
        $moduleid = $DB->get_field('modules', 'id', ['name' => 'videotrack'], MUST_EXIST);
        $DB->update_record('course_modules', (object)[
            'id' => $forum->cmid, 'module' => $moduleid,
            'instance' => $id, 'completion' => COMPLETION_TRACKING_NONE,
        ]);
        rebuild_course_cache($course->id, true);
        return [$DB->get_record('videotrack', ['id' => $id], '*', MUST_EXIST), \context_module::instance($forum->cmid)];
    }

    /**
     * Insert one private interaction for a given user and category.
     *
     * @param \stdClass $activity VideoTrack activity.
     * @param \context_module $context Module context.
     * @param int $userid Owner of the event.
     * @param string $type Empty for reactions, note or bookmark otherwise.
     * @param int $deleted Deletion flag stored in the table.
     * @param string $label Personal text.
     * @param int $time Timestamp controlling event order.
     */
    private function insert_event(
        \stdClass $activity,
        \context_module $context,
        int $userid,
        string $type,
        int $deleted,
        string $label,
        int $time
    ): void {
        global $DB;

        $DB->insert_record('videotrack_reactev', (object)[
            'videotrackid' => $activity->id, 'courseid' => $activity->course,
            'cmid' => $context->instanceid, 'userid' => $userid,
            'videoid' => 'exporttest', 'sessionid' => 'private-session',
            'reactionkey' => '', 'reactionlabel' => '', 'notetype' => $type,
            'notetext' => $label, 'isdeleted' => $deleted,
            'timecreated' => $time, 'timemodified' => $time,
        ]);
    }
}
