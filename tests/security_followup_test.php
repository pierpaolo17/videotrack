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
use mod_videotrack\local\personal_item;
use mod_videotrack\local\report_view;
use mod_videotrack\local\resume_storage;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Native regressions for permanent deletion and browser/request isolation.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(personal_item::class)]
#[CoversClass(delete_note::class)]
#[CoversClass(delete_bookmark::class)]
#[CoversClass(report_view::class)]
#[CoversClass(\mod_videotrack\local\report_support::class)]
#[CoversClass(resume_storage::class)]
final class security_followup_test extends \advanced_testcase {
    /** @var \stdClass Current participating learner. */
    private \stdClass $learner;
    /** @var \stdClass Real activity with personal tools enabled. */
    private \stdClass $activity;
    /** @var array Original submitted fields. */
    private array $originalpost;

    /**
     * Create a real enrolled learner and restore the request after each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->originalpost = $_POST;
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->activity = $generator->create_module('videotrack', [
            'course' => $course->id, 'studentnotesenabled' => 1, 'bookmarksenabled' => 1,
        ]);
        $this->learner = $generator->create_user();
        $generator->enrol_user($this->learner->id, $course->id, 'student');
        $this->setUser($this->learner);
        $_POST['sesskey'] = sesskey();
    }

    /**
     * Restore the original submitted fields even after an expected exception.
     */
    protected function tearDown(): void {
        $_POST = $this->originalpost;
        parent::tearDown();
    }

    /**
     * Notes disappear physically; the legacy argument and repeated request remain supported.
     */
    public function test_note_erasure_and_legacy_retry_emit_one_content_free_event(): void {
        global $DB;
        $id = $this->insert_item('note');
        $sink = $this->redirectEvents();
        $first = delete_note::execute((int)$this->activity->cmid, 0, $id);
        $second = delete_note::execute((int)$this->activity->cmid, $id);
        $this->assertSame($first, $second);
        $this->assertTrue($first['deleted']);
        $this->assertFalse($DB->record_exists('videotrack_reactev', ['id' => $id]));
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(\mod_videotrack\event\note_deleted::class, $events[0]);
        $this->assertStringNotContainsString('private-content', json_encode($events[0]->get_data()));
    }

    /**
     * Bookmark removal is permanent and its successful response can be replayed.
     */
    public function test_bookmark_erasure_and_retry_emit_one_event(): void {
        global $DB;
        $id = $this->insert_item('bookmark');
        $sink = $this->redirectEvents();
        $first = delete_bookmark::execute((int)$this->activity->cmid, $id);
        $this->assertSame($first, delete_bookmark::execute((int)$this->activity->cmid, $id));
        $this->assertTrue($first['deleted']);
        $this->assertFalse($DB->record_exists('videotrack_reactev', ['id' => $id]));
        $this->assertCount(1, $sink->get_events());
        $this->assertInstanceOf(\mod_videotrack\event\bookmark_deleted::class, $sink->get_events()[0]);
    }

    /**
     * A previously removed personal row can be erased without logging its removal twice.
     */
    public function test_legacy_removed_items_are_erased_without_new_events(): void {
        global $DB;
        $noteid = $this->insert_item('note', 1);
        $bookmarkid = $this->insert_item('bookmark', 1);
        $sink = $this->redirectEvents();
        delete_note::execute((int)$this->activity->cmid, $noteid);
        delete_bookmark::execute((int)$this->activity->cmid, $bookmarkid);
        $this->assertFalse($DB->record_exists('videotrack_reactev', ['id' => $noteid]));
        $this->assertFalse($DB->record_exists('videotrack_reactev', ['id' => $bookmarkid]));
        $this->assertCount(0, $sink->get_events());
    }

    /**
     * An existing row owned by another learner remains protected.
     */
    public function test_foreign_learner_item_is_not_erased(): void {
        global $DB;
        $other = $this->getDataGenerator()->create_user();
        $id = $this->insert_item('note', 0, (int)$other->id);
        try {
            delete_note::execute((int)$this->activity->cmid, $id);
            $this->fail('Foreign note deletion was accepted.');
        } catch (\dml_missing_record_exception $error) {
            $this->assertTrue($DB->record_exists('videotrack_reactev', ['id' => $id, 'userid' => $other->id]));
        }
    }

    /**
     * An owned bookmark cannot be removed through the note endpoint.
     */
    public function test_wrong_personal_item_type_is_not_erased(): void {
        global $DB;
        $id = $this->insert_item('bookmark');
        try {
            delete_note::execute((int)$this->activity->cmid, $id);
            $this->fail('A bookmark was accepted as a note.');
        } catch (\dml_missing_record_exception $error) {
            $this->assertTrue($DB->record_exists('videotrack_reactev', ['id' => $id, 'notetype' => 'bookmark']));
        }
    }

    /**
     * An existing note from another instance cannot cross the activity boundary.
     */
    public function test_foreign_activity_item_is_not_erased(): void {
        global $DB;
        $id = $this->insert_item('note');
        $other = $this->getDataGenerator()->create_module('videotrack', ['course' => $this->activity->course]);
        try {
            delete_note::execute((int)$other->cmid, $id);
            $this->fail('A note from another activity was accepted.');
        } catch (\dml_missing_record_exception $error) {
            $this->assertTrue($DB->record_exists('videotrack_reactev', ['id' => $id]));
        }
    }

    /**
     * The no-op retry path still requires a valid session key.
     */
    public function test_missing_item_retry_does_not_bypass_sesskey(): void {
        $_POST['sesskey'] = 'invalid';
        $this->expectException(\moodle_exception::class);
        delete_note::execute((int)$this->activity->cmid, PHP_INT_MAX);
    }

    /**
     * A missing id does not allow a staff preview to bypass participation checks.
     */
    public function test_missing_item_retry_requires_participation(): void {
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $this->activity->course, 'editingteacher');
        $this->setUser($teacher);
        $_POST['sesskey'] = sesskey();
        $this->expectException(\moodle_exception::class);
        delete_bookmark::execute((int)$this->activity->cmid, PHP_INT_MAX);
    }

    /**
     * The core completion cleanup keeps neighbouring activities intact and is repeatable.
     */
    public function test_uninstall_completion_api_preserves_neighbouring_activity(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/videotrack/db/uninstall.php');
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $this->activity->course]);
        foreach ([$this->activity->cmid, $forum->cmid] as $cmid) {
            $DB->insert_record('course_modules_completion', (object)[
                'coursemoduleid' => $cmid, 'userid' => $this->learner->id,
                'completionstate' => 1, 'timemodified' => time(),
            ]);
        }
        $moduleid = (int)$DB->get_field('modules', 'id', ['name' => 'videotrack'], MUST_EXIST);
        \videotrack_uninstall_delete_completion($moduleid);
        \videotrack_uninstall_delete_completion($moduleid);
        $this->assertFalse($DB->record_exists('course_modules_completion', ['coursemoduleid' => $this->activity->cmid]));
        $this->assertTrue($DB->record_exists('course_modules_completion', ['coursemoduleid' => $forum->cmid]));
    }

    /**
     * A module whose course is already absent uses only the ownership-scoped fallback.
     */
    public function test_orphan_completion_fallback_remains_bounded(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/mod/videotrack/db/uninstall.php');
        $DB->insert_record('course_modules_completion', (object)[
            'coursemoduleid' => $this->activity->cmid, 'userid' => $this->learner->id,
            'completionstate' => 1, 'timemodified' => time(),
        ]);
        $DB->set_field('course_modules', 'course', PHP_INT_MAX, ['id' => $this->activity->cmid]);
        $moduleid = (int)$DB->get_field('modules', 'id', ['name' => 'videotrack'], MUST_EXIST);
        \videotrack_uninstall_delete_completion($moduleid);
        $this->assertFalse($DB->record_exists('course_modules_completion', ['coursemoduleid' => $this->activity->cmid]));
        $this->assertTrue($DB->record_exists('course', ['id' => $this->activity->course]));
    }

    /**
     * Resume keys isolate users, modules, media, providers and Moodle installations.
     */
    public function test_resume_storage_keys_isolate_all_identity_components(): void {
        global $CFG;
        $key = resume_storage::key(11, 47, 'youtube', 'one');
        $this->assertMatchesRegularExpression('/^videotrack:lastposition:v2:[a-f0-9]{64}$/', $key);
        $this->assertSame($key, resume_storage::key(11, 47, 'youtube', 'one'));
        $identities = [
            [12, 47, 'youtube', 'one'], [11, 48, 'youtube', 'one'],
            [11, 47, 'vimeo', 'one'], [11, 47, 'youtube', 'two'],
        ];
        foreach ($identities as $identity) {
            $this->assertNotSame($key, resume_storage::key(...$identity));
        }
        $wwwroot = $CFG->wwwroot;
        try {
            $CFG->wwwroot .= '/another-installation';
            $this->assertNotSame($key, resume_storage::key(11, 47, 'youtube', 'one'));
        } finally {
            $CFG->wwwroot = $wwwroot;
        }
        $this->assertSame('', resume_storage::key(0, 47, 'youtube', 'one'));
        $this->assertSame('', resume_storage::key(11, 0, 'youtube', 'one'));
        $this->assertSame('', resume_storage::key(11, 47, 'youtube', ''));
    }

    /**
     * Analytics downloads put the session key in a POST body while retaining selected filters.
     */
    public function test_analytics_download_form_posts_filters_and_session_key(): void {
        $markup = report_view::analytics_download(['csv'], ['id' => 47, 'mode' => 'analytics', 'analyticsgroupid' => 3]);
        $dom = new \DOMDocument();
        $dom->loadHTML($markup);
        $xpath = new \DOMXPath($dom);
        $form = $xpath->query('//form')->item(0);
        $this->assertSame('post', $form->getAttribute('method'));
        $this->assertStringNotContainsString('sesskey', $form->getAttribute('action'));
        foreach (['id' => '47', 'mode' => 'analytics', 'analyticsgroupid' => '3', 'sesskey' => sesskey()] as $name => $value) {
            $fields = $xpath->query('//form/input[@name="' . $name . '"]');
            $this->assertSame(1, $fields->length);
            $this->assertSame($value, $fields->item(0)->getAttribute('value'));
        }
    }

    /**
     * Course-format labels retain safe markup while removing executable content.
     */
    public function test_course_format_section_labels_are_cleaned_at_output(): void {
        $label = '<strong>Section A &amp; B</strong><img src="x" onerror="alert(1)"><script>alert(2)</script>';
        $context = \context_course::instance($this->activity->course);
        $html = \mod_videotrack\local\report_support::section_label_html($label, $context);
        $this->assertStringContainsString('<strong>Section A &amp; B</strong>', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    /**
     * Insert one identifiable personal record without calling write-side rate limiting.
     *
     * @param string $type Personal item type.
     * @param int $deleted Whether this simulates an old removed row.
     * @param int $userid Optional different owner.
     * @return int Persisted id.
     */
    private function insert_item(string $type, int $deleted = 0, int $userid = 0): int {
        global $DB;
        return (int)$DB->insert_record('videotrack_reactev', (object)[
            'videotrackid' => $this->activity->id, 'courseid' => $this->activity->course,
            'cmid' => $this->activity->cmid, 'userid' => $userid ?: $this->learner->id,
            'videoid' => 'testvideo', 'sessionid' => 'test-session', 'reactionid' => 0,
            'reactionkey' => '', 'reactionlabel' => '', 'reactiondesc' => '',
            'notetype' => $type, 'notetext' => 'private-content', 'videotime' => 10,
            'playbackrate' => 1, 'isdeleted' => $deleted, 'timecreated' => time(), 'timemodified' => time(),
        ]);
    }
}
