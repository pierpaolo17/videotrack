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
use core_privacy\local\request\writer;
use mod_videotrack\privacy\provider;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Exercises real course backup/restore and privacy erasure on the restored records.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class lifecycle_integration_test extends \advanced_testcase {
    /**
     * A configuration-only restore copies shared files and definitions without learner records.
     */
    public function test_restore_without_users_keeps_configuration_and_shared_files(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->create_fixture();
        $source = $this->snapshot((int)$fixture['activity']->id);
        $courseid = $this->restore_course($fixture['course'], false, 0);
        $copy = $this->assert_restored_configuration($fixture, $courseid);

        foreach (array_keys($source) as $table) {
            $this->assertFalse($DB->record_exists($table, ['videotrackid' => $copy->id]), $table);
        }
        $item = $DB->get_record('grade_items', [
            'courseid' => $courseid, 'itemmodule' => 'videotrack',
            'iteminstance' => $copy->id, 'itemnumber' => 0,
        ], '*', MUST_EXIST);
        $this->assertFalse($DB->record_exists('grade_grades', ['itemid' => $item->id]));
        $cm = get_coursemodule_from_instance('videotrack', $copy->id, $courseid, false, MUST_EXIST);
        $this->assertFalse($DB->record_exists('course_modules_completion', ['coursemoduleid' => $cm->id]));
        $this->assertEquals($source, $this->snapshot((int)$fixture['activity']->id));
    }

    /**
     * Destination retention rebuilds evidence before scoped Privacy API export and erasure.
     */
    public function test_restore_with_users_rebuilds_evidence_and_scopes_privacy_erasure(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $fixture = $this->create_fixture();
        $source = $this->snapshot((int)$fixture['activity']->id);
        $courseid = $this->restore_course($fixture['course'], true, 10);
        $copy = $this->assert_restored_configuration($fixture, $courseid);
        $cm = get_coursemodule_from_instance('videotrack', $copy->id, $courseid, false, MUST_EXIST);
        $context = \core\context\module::instance($cm->id);
        foreach ($fixture['users'] as $index => $user) {
            $conditions = ['videotrackid' => $copy->id, 'userid' => $user->id];
            $this->assertSame(2, $DB->count_records('videotrack_seg', $conditions));
            $this->assertSame(3, $DB->count_records('videotrack_reactev', $conditions));
            $this->assertSame(1, $DB->count_records('videotrack_integrity', $conditions));
            $this->assertSame(1, $DB->count_records('videotrack_acknowledge', $conditions));
            $state = $DB->get_record('videotrack_state', $conditions, '*', MUST_EXIST);
            $this->assertSame($courseid, (int)$state->courseid);
            $this->assertSame((int)$cm->id, (int)$state->cmid);
            $this->assertEqualsWithDelta(20 + ($index * 10), (float)$state->uniquecoveredseconds, 0.001);
            $this->assertSame(0, (int)$state->iscompleted);
            $completion = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => $cm->id, 'userid' => $user->id,
            ], '*', MUST_EXIST);
            $this->assertSame(COMPLETION_INCOMPLETE, (int)$completion->completionstate);
        }
        foreach ($this->snapshot((int)$copy->id) as $table => $records) {
            foreach ($records as $record) {
                $this->assertSame($courseid, (int)$record->courseid, $table);
                $this->assertSame((int)$cm->id, (int)$record->cmid, $table);
            }
        }
        $user = $fixture['users'][0];
        $other = $fixture['users'][1];
        $approved = new approved_contextlist($user, 'mod_videotrack', [$context->id]);
        writer::reset();
        provider::export_user_data($approved);
        $written = writer::with_context($context);
        $watch = get_string('watch', 'mod_videotrack');
        $state = $written->get_data([$watch, get_string('privacy:state', 'mod_videotrack')])->state;
        $this->assertSame('0:00-0:20', $state->intervaljson);
        $this->assertArrayNotHasKey('userid', (array)$state);
        $segments = $written->get_data([$watch, get_string('privacy:segmentschunk', 'mod_videotrack', 1)])->segments;
        $this->assertCount(2, $segments);
        $this->assertEqualsCanonicalizing(
            ['retained' . $user->id, 'invalid' . $user->id],
            array_map(static fn($segment) => $segment->requestid, $segments)
        );
        $item = $DB->get_record('grade_items', [
            'courseid' => $courseid, 'itemmodule' => 'videotrack',
            'iteminstance' => $copy->id, 'itemnumber' => 0,
        ], '*', MUST_EXIST);
        $grades = $DB->get_records('grade_grades', ['itemid' => $item->id], 'userid, id');
        $this->assertCount(2, $grades);
        provider::delete_data_for_user($approved);
        foreach (array_keys($source) as $table) {
            $this->assertFalse($DB->record_exists($table, ['videotrackid' => $copy->id, 'userid' => $user->id]));
            $this->assertTrue($DB->record_exists($table, ['videotrackid' => $copy->id, 'userid' => $other->id]));
        }
        provider::delete_data_for_all_users_in_context($context);
        foreach (array_keys($source) as $table) {
            $this->assertFalse($DB->record_exists($table, ['videotrackid' => $copy->id]));
        }
        $this->assertEquals($grades, $DB->get_records('grade_grades', ['itemid' => $item->id], 'userid, id'));
        $this->assertEquals($source, $this->snapshot((int)$fixture['activity']->id));
        $this->assert_restored_configuration($fixture, $courseid);
    }

    /**
     * Build a populated course with both shared configuration and independent learner evidence.
     *
     * @return array Course, activity, reaction id, files and users.
     */
    private function create_fixture(): array {
        global $CFG, $DB;

        require_once($CFG->libdir . '/gradelib.php');
        set_config('retentionperioddays', 0, 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $forum = $generator->create_module('forum', ['course' => $course->id, 'name' => 'Lifecycle Forum']);
        $module = $generator->create_module('videotrack', [
            'course' => $course->id, 'name' => 'Lifecycle VideoTrack', 'behathtml5fixture' => 1,
            'behatemptyreactions' => 1, 'durationseconds' => 100, 'grade' => 100,
            'completion' => COMPLETION_TRACKING_AUTOMATIC, 'completionpercentenabled' => 1, 'completionpercent' => 50,
            'reactionsenabled' => 1, 'studentnotesenabled' => 1, 'bookmarksenabled' => 1,
            'randomfocuspauses' => 1, 'randompauseminseconds' => 120, 'randompausemaxseconds' => 600,
            'forumpostingenabled' => 1, 'linkedforumid' => $forum->id,
        ]);
        $activity = $DB->get_record('videotrack', ['id' => $module->id], '*', MUST_EXIST);
        $activity->cmid = (int)$module->cmid;
        $context = \core\context\module::instance($activity->cmid);
        $now = time();
        $reactionid = (int)$DB->insert_record('videotrack_react', (object)[
            'videotrackid' => $activity->id, 'reactionkey' => 'lifecycle', 'label' => 'Historical reaction',
            'description' => 'Preserve historical meaning', 'icontype' => 'file', 'iconvalue' => 'reaction.png',
            'requiredforcompletion' => 0, 'sortorder' => 1, 'isdeleted' => 1,
            'timecreated' => $now, 'timemodified' => $now,
        ]);
        $vtt = "WEBVTT\n\n00:00.000 --> 00:01.000\nLifecycle\n";
        $image = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jrS8AAAAASUVORK5CYII=');
        foreach ([
            ['intro', 0, 'shared.txt', 'Shared introduction'],
            ['subtitles', 0, 'captions_it.vtt', $vtt],
            ['transcripts', 0, 'transcript_it.vtt', $vtt],
            ['transcripts', 0, 'transcript_en.vtt', str_replace('Lifecycle', 'English lifecycle', $vtt)],
            ['chapters', 0, 'chapters_it.vtt', $vtt],
            ['posterimage', 0, 'poster.png', $image],
            ['reactionicon', $reactionid, 'reaction.png', $image],
        ] as [$area, $itemid, $filename, $content]) {
            get_file_storage()->create_file_from_string([
                'contextid' => $context->id, 'component' => 'mod_videotrack', 'filearea' => $area,
                'itemid' => $itemid, 'filepath' => '/', 'filename' => $filename,
            ], $content);
        }
        $users = [$generator->create_user(), $generator->create_user()];
        foreach ($users as $index => $user) {
            $generator->enrol_user($user->id, $course->id, 'student');
            $this->insert_user_records($activity, (int)$user->id, $reactionid, $now, 20 + ($index * 10));
            $result = \grade_update(
                'mod/videotrack',
                $course->id,
                'mod',
                'videotrack',
                $activity->id,
                0,
                (object)['userid' => $user->id, 'rawgrade' => 80]
            );
            $this->assertSame(GRADE_UPDATE_OK, $result);
            $completion = (object)[
                'coursemoduleid' => $activity->cmid, 'userid' => $user->id,
                'completionstate' => COMPLETION_COMPLETE, 'timemodified' => $now,
            ];
            $completionid = $DB->get_field('course_modules_completion', 'id', [
                'coursemoduleid' => $activity->cmid, 'userid' => $user->id,
            ]);
            if ($completionid) {
                $completion->id = $completionid;
                $DB->update_record('course_modules_completion', $completion);
            } else {
                $DB->insert_record('course_modules_completion', $completion);
            }
        }
        return compact('course', 'activity', 'reactionid', 'context', 'users');
    }

    /**
     * Insert old, retained and unvalidated evidence plus deliberately stale aggregate completion.
     *
     * @param \stdClass $activity Activity record, including cmid.
     * @param int $userid Learner id.
     * @param int $reactionid Historical reaction definition.
     * @param int $now Time of fixture construction.
     * @param int $span Retained validated viewing duration.
     */
    private function insert_user_records(\stdClass $activity, int $userid, int $reactionid, int $now, int $span): void {
        global $DB;

        $old = $now - (20 * DAYSECS);
        $recent = $now - (2 * DAYSECS);
        $ids = [
            'videotrackid' => $activity->id,
            'courseid' => $activity->course,
            'cmid' => $activity->cmid,
            'userid' => $userid,
        ];
        $common = $ids + ['videoid' => $activity->videoid];
        foreach ([['old', $old, 80, 1], ['retained', $recent, $span, 1], ['invalid', $recent, 100, 0]] as $segment) {
            [$request, $timestamp, $end, $validated] = $segment;
            $DB->insert_record('videotrack_seg', (object)($common + [
                'sessionid' => 'lifecycle' . $userid, 'requestid' => $request . $userid,
                'wallclockstart' => $timestamp * 1000, 'wallclockend' => ($timestamp + $end) * 1000,
                'videotimestart' => 0, 'videotimeend' => $end, 'playbackrate' => 1,
                'endreason' => 'pause', 'servervalidated' => $validated, 'timecreated' => $timestamp,
            ]));
        }
        $DB->insert_record('videotrack_state', (object)($common + [
            'serverplaybacksessionid' => '', 'lastposition' => 100, 'durationseconds' => 100,
            'uniquecoveredseconds' => 100, 'completionpercent' => 100, 'intervaljson' => '[[0,100]]',
            'iscompleted' => 1, 'timecreated' => $old, 'timemodified' => $recent,
        ]));
        foreach ([
            ['reaction', $reactionid, $recent], ['note', 0, $recent],
            ['bookmark', 0, $recent], ['note', 0, $old],
        ] as [$type, $definition, $timestamp]) {
            $DB->insert_record('videotrack_reactev', (object)($common + [
                'sessionid' => 'lifecycle' . $userid, 'reactionid' => $definition,
                'reactionkey' => $definition ? 'lifecycle' : '', 'reactionlabel' => $definition ? 'Historical reaction' : '',
                'notetype' => $definition ? '' : $type, 'notetext' => $type . ' from ' . $userid,
                'videotime' => 5, 'playbackrate' => 1, 'isdeleted' => 0,
                'timecreated' => $timestamp, 'timemodified' => $timestamp,
            ]));
        }
        foreach ([$old, $recent] as $timestamp) {
            $DB->insert_record('videotrack_integrity', (object)($common + [
                'sessionid' => 'lifecycle' . $userid, 'eventtype' => 'tabhidden',
                'videotime' => 5, 'timecreated' => $timestamp,
            ]));
            $DB->insert_record('videotrack_acknowledge', (object)($ids + [
                'statementhash' => hash('sha256', 'statement' . $timestamp), 'instanceversion' => 1,
                'viewedseconds' => 10, 'viewedpercent' => 10, 'timeconfirmed' => $timestamp,
            ]));
        }
    }

    /**
     * Use Moodle's real synchronous backup and restore controllers, retaining their assertions and hooks.
     *
     * @param \stdClass $course Source course.
     * @param bool $userdata Whether to include learner data.
     * @param int $retentiondays Destination policy, applied after backup has captured its source data.
     * @return int Restored course id.
     */
    private function restore_course(\stdClass $course, bool $userdata, int $retentiondays): int {
        global $CFG, $USER;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $backup = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        try {
            $backup->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $backup->get_plan()->get_setting('users')->set_value($userdata);
            $backupid = $backup->get_backupid();
            $backup->execute_plan();
        } finally {
            $backup->destroy();
        }
        set_config('retentionperioddays', $retentiondays, 'mod_videotrack');
        $courseid = \restore_dbops::create_new_course($course->fullname, $course->shortname . '_restored', $course->category);
        $restore = new \restore_controller(
            $backupid,
            $courseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        try {
            $restore->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $restore->get_plan()->get_setting('users')->set_value($userdata);
            $this->assertTrue($restore->execute_precheck());
            $restore->execute_plan();
        } finally {
            $restore->destroy();
        }
        return (int)$courseid;
    }

    /**
     * Check actual destination records, Forum mapping, grade-item uniqueness and file content hashes.
     *
     * @param array $fixture Original course fixture.
     * @param int $courseid Destination course.
     * @return \stdClass Restored activity.
     */
    private function assert_restored_configuration(array $fixture, int $courseid): \stdClass {
        global $DB;

        $original = $fixture['activity'];
        $copy = $DB->get_record('videotrack', ['course' => $courseid, 'name' => $original->name], '*', MUST_EXIST);
        $this->assertNotSame((int)$original->id, (int)$copy->id);
        $this->assertNotSame((int)$original->course, $courseid);
        $this->assertSame(120, (int)$copy->randompauseminseconds);
        $this->assertSame(600, (int)$copy->randompausemaxseconds);
        $this->assertSame(1, (int)$copy->randomfocuspauses);
        $this->assertSame(50, (int)$copy->completionpercent);
        $this->assertEquals(100, $copy->durationseconds);
        $this->assertSame(1, (int)$copy->forumpostingenabled);
        $this->assertNotSame((int)$original->linkedforumid, (int)$copy->linkedforumid);
        $this->assertTrue($DB->record_exists('forum', ['id' => $copy->linkedforumid, 'course' => $courseid]));
        $this->assertSame(1, $DB->count_records('grade_items', [
            'courseid' => $courseid, 'itemtype' => 'mod', 'itemmodule' => 'videotrack',
            'iteminstance' => $copy->id, 'itemnumber' => 0,
        ]));
        $reaction = $DB->get_record('videotrack_react', [
            'videotrackid' => $copy->id, 'reactionkey' => 'lifecycle',
        ], '*', MUST_EXIST);
        $this->assertNotSame($fixture['reactionid'], (int)$reaction->id);
        $this->assertSame(1, (int)$reaction->isdeleted);
        foreach ($DB->get_records('videotrack_reactev', [
            'videotrackid' => $copy->id, 'reactionkey' => 'lifecycle',
        ]) as $event) {
            $this->assertSame((int)$reaction->id, (int)$event->reactionid);
        }
        $cm = get_coursemodule_from_instance('videotrack', $copy->id, $courseid, false, MUST_EXIST);
        $context = \core\context\module::instance($cm->id);
        $fs = get_file_storage();
        foreach (['intro', 'videocontent', 'subtitles', 'transcripts', 'chapters', 'posterimage', 'reactionicon'] as $area) {
            $itemid = $area === 'reactionicon' ? $fixture['reactionid'] : 0;
            $files = $fs->get_area_files($fixture['context']->id, 'mod_videotrack', $area, $itemid, 'filename', false);
            $this->assertNotEmpty($files, $area);
            $mappeditemid = $area === 'reactionicon' ? (int)$reaction->id : 0;
            $copies = $fs->get_area_files($context->id, 'mod_videotrack', $area, $mappeditemid, 'filename', false);
            $this->assertCount(count($files), $copies, $area);
            foreach ($files as $file) {
                $restored = $fs->get_file(
                    $context->id,
                    'mod_videotrack',
                    $area,
                    $mappeditemid,
                    $file->get_filepath(),
                    $file->get_filename()
                );
                $this->assertNotFalse($restored, $area);
                $this->assertSame($file->get_contenthash(), $restored->get_contenthash(), $area);
            }
        }
        return $copy;
    }

    /**
     * Read all plugin-owned learner records for exact source-isolation comparisons.
     *
     * @param int $videotrackid Activity id.
     * @return array Records per learner-data table, ordered by id.
     */
    private function snapshot(int $videotrackid): array {
        global $DB;

        $records = [];
        $tables = [
            'videotrack_seg', 'videotrack_state', 'videotrack_reactev',
            'videotrack_integrity', 'videotrack_acknowledge',
        ];
        foreach ($tables as $table) {
            $records[$table] = $DB->get_records($table, ['videotrackid' => $videotrackid], 'id');
        }
        return $records;
    }
}
