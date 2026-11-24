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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack;

use mod_videotrack\local\microsoft_activity_reference;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Exercise portable references through real course backup and restore controllers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class microsoft_reference_restore_test extends \advanced_testcase {
    /**
     * Isolate the course controllers and their local fixtures from real providers.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * User-data backups retain the owned note and active video but strip local binding.
     */
    public function test_restore_with_users_preserves_note_and_portable_reference(): void {
        global $DB;

        [$course, $activity, $reference] = $this->fixture();
        $restored = $this->roundtrip($course, true);
        $this->assert_restored($activity, $restored);
        $this->assertSame(1, $DB->count_records('videotrack_reactev', ['videotrackid' => $restored->id, 'notetype' => 'note']));
        $this->assertSame($reference, $DB->get_field('videotrack', 'microsoftreference', ['id' => $activity->id]));
    }

    /**
     * Configuration-only backups still carry the target while excluding learner notes.
     */
    public function test_restore_without_users_keeps_reference_and_excludes_notes(): void {
        global $DB;

        [$course, $activity] = $this->fixture();
        $restored = $this->roundtrip($course, false);
        $this->assert_restored($activity, $restored);
        $this->assertSame(0, $DB->count_records('videotrack_reactev', ['videotrackid' => $restored->id]));
    }

    /**
     * Older backups without the child cannot import a forged raw local-binding field.
     */
    public function test_legacy_backup_ignores_a_raw_reference_field(): void {
        [$course, $activity] = $this->fixture();
        $restored = $this->roundtrip($course, false, true);
        $this->assertNull($restored->microsoftreference);
        $this->assertSame($activity->videoid, $restored->videoid);
        $this->assertSame($activity->videosource, $restored->videosource);
    }

    /**
     * Repeated backups retain portable ids without guessing another site's OAuth service.
     */
    public function test_rebackup_of_an_unbound_reference_stays_unbound(): void {
        global $DB;

        [$course, $activity] = $this->fixture();
        $first = $this->roundtrip($course, false);
        $newcourse = $DB->get_record('course', ['id' => $first->course], '*', MUST_EXIST);
        $second = $this->roundtrip($newcourse, false);
        $this->assert_restored($activity, $second);
        $this->assertSame($first->microsoftreference, $second->microsoftreference);
    }

    /**
     * Create a prepared activity and one independently owned note without provider calls.
     *
     * @return array Source course, playable activity and original local identity.
     */
    private function fixture(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id, 'durationseconds' => 5280, 'studentnotesenabled' => 1,
        ]);
        $reference = microsoft_activity_reference::canonical(json_encode([
            'version' => 1, 'provider' => 'microsoft', 'issuerid' => 777777,
            'driveid' => 'b!PortableDrive', 'itemid' => 'PortableVideo',
        ]));
        // Deliberately unavailable source service: backups must not require tokens or configuration.
        $DB->set_field('videotrack', 'microsoftreference', $reference, ['id' => $activity->id]);
        $DB->insert_record('videotrack_reactev', (object)[
            'videotrackid' => $activity->id, 'courseid' => $course->id, 'cmid' => $activity->cmid,
            'userid' => $user->id, 'videoid' => $activity->videoid, 'sessionid' => 'portable-backup-fixture',
            'reactionid' => 0, 'reactionkey' => '', 'reactionlabel' => '', 'reactiondesc' => null,
            'notetext' => 'Owned learner note', 'notetype' => 'note', 'videotime' => 60,
            'playbackrate' => 1, 'isdeleted' => 0, 'timecreated' => time(), 'timemodified' => time(),
        ]);
        return [$course, $activity, $reference];
    }

    /**
     * Run native controllers and inspect the actual activity XML before restore.
     *
     * @param \stdClass $course Source course.
     * @param bool $userdata Include learner records.
     * @param bool $legacy Replace the portable child with an untrusted raw legacy field.
     * @return \stdClass Restored activity record.
     */
    private function roundtrip(\stdClass $course, bool $userdata, bool $legacy = false): \stdClass {
        global $CFG, $USER, $DB;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
        $backup = new \backup_controller(
            \backup::TYPE_1COURSE, $course->id, \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO, \backup::MODE_IMPORT, $USER->id
        );
        try {
            $backup->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $backup->get_plan()->get_setting('users')->set_value($userdata);
            $backupid = $backup->get_backupid();
            $backup->execute_plan();
            $base = $CFG->backuptempdir ?? ($CFG->tempdir . '/backup');
            $paths = glob($base . '/' . $backupid . '/activities/videotrack_*/videotrack.xml');
            $this->assertCount(1, $paths);
            $dom = new \DOMDocument();
            $this->assertTrue($dom->load($paths[0]));
            $xpath = new \DOMXPath($dom);
            $this->assertSame(1, $xpath->query('//videotrack/microsoft_identity')->length);
            $this->assertSame(0, $xpath->query('//videotrack/microsoftreference | //microsoft_identity/issuerid')->length);
            $this->assertStringNotContainsString('777777', $dom->saveXML());
            if ($legacy) {
                $root = $xpath->query('//videotrack')->item(0);
                $child = $xpath->query('//microsoft_identity')->item(0);
                $root->removeChild($child);
                $root->appendChild($dom->createElement('microsoftreference', 'private-source-credential-marker'));
                $this->assertNotFalse($dom->save($paths[0]));
            }
        } finally {
            $backup->destroy();
        }
        // A destination config change cannot auto-bind the portable activity identity.
        set_config('microsoftissuerid', 888888, 'mod_videotrack');
        $courseid = \restore_dbops::create_new_course($course->fullname, $course->shortname . '_portable', $course->category);
        $restore = new \restore_controller(
            $backupid, $courseid, \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL, $USER->id, \backup::TARGET_NEW_COURSE
        );
        try {
            $restore->get_plan()->get_setting('users')->set_status(\backup_setting::NOT_LOCKED);
            $restore->get_plan()->get_setting('users')->set_value($userdata);
            $this->assertTrue($restore->execute_precheck());
            $restore->execute_plan();
        } finally {
            $restore->destroy();
        }
        return $DB->get_record('videotrack', ['course' => $courseid], '*', MUST_EXIST);
    }

    /**
     * Check that restored metadata never changes the active playable source.
     *
     * @param \stdClass $source Original activity.
     * @param \stdClass $restored Destination activity.
     */
    private function assert_restored(\stdClass $source, \stdClass $restored): void {
        $identity = json_decode($restored->microsoftreference, true, 4, JSON_THROW_ON_ERROR);
        $this->assertSame(0, $identity['issuerid']);
        $this->assertSame('b!PortableDrive', $identity['driveid']);
        $this->assertSame('PortableVideo', $identity['itemid']);
        foreach (['videoid', 'videosource', 'youtubeurl', 'durationseconds'] as $field) {
            $this->assertEquals($source->$field, $restored->$field, $field);
        }
    }
}
