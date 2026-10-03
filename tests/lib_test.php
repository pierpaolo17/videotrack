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
use PHPUnit\Framework\Attributes\CoversFunction;

/**
 * Minimal PHPUnit coverage for the videotrack module callbacks.
 *
 * These tests intentionally exercise stable, side-effect-free callbacks first
 * so they can act as a safe baseline before deeper Moodle integration tests are
 * added in later 1.3.x-dev patches.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversFunction('videotrack_supports')]
#[CoversFunction('videotrack_process_player_behavior_fields')]
#[CoversFunction('videotrack_process_captions_fields')]
#[CoversFunction('videotrack_process_video_fields')]
#[CoversFunction('videotrack_save_uploaded_video')]
#[CoversFunction('videotrack_save_poster_image')]
#[CoversFunction('videotrack_get_upload_url')]
#[CoversFunction('videotrack_get_module_context_from_data')]
#[CoversFunction('videotrack_whitelist_record')]
#[CoversFunction('videotrack_is_valid_reaction_icon_class')]
final class lib_test extends advanced_testcase {
    /**
     * Load module callbacks under test.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../lib.php');
    }

    /**
     * Basic supported feature flags should remain stable across refactors.
     */
    public function test_supports_expected_core_features(): void {
        $this->assertTrue(\videotrack_supports(FEATURE_MOD_INTRO));
        $this->assertTrue(\videotrack_supports(FEATURE_SHOW_DESCRIPTION));
        $this->assertTrue(\videotrack_supports(FEATURE_COMPLETION_TRACKS_VIEWS));
        $this->assertTrue(\videotrack_supports(FEATURE_COMPLETION_HAS_RULES));
        $this->assertTrue(\videotrack_supports(FEATURE_BACKUP_MOODLE2));
        $this->assertTrue(\videotrack_supports(FEATURE_GRADE_HAS_GRADE));
    }

    /**
     * Group features are intentionally disabled for this activity.
     */
    public function test_groups_are_explicitly_not_supported(): void {
        $this->assertFalse(\videotrack_supports(FEATURE_GROUPS));
        $this->assertFalse(\videotrack_supports(FEATURE_GROUPINGS));
    }

    /**
     * Activity chooser metadata should remain predictable.
     */
    public function test_activity_chooser_metadata_is_reported(): void {
        $this->assertSame(MOD_ARCHETYPE_RESOURCE, \videotrack_supports(FEATURE_MOD_ARCHETYPE));
        $this->assertSame(MOD_PURPOSE_CONTENT, \videotrack_supports(FEATURE_MOD_PURPOSE));
    }

    /**
     * Unknown features should keep Moodle's default handling path.
     */
    public function test_unknown_feature_returns_null(): void {
        $this->assertNull(\videotrack_supports('mod_videotrack_unknown_feature'));
    }

    /**
     * Font Awesome reactions accept one icon name plus the reviewed decorator subset.
     */
    public function test_reaction_icon_class_validation_preserves_allowlist(): void {
        $valid = [
            'fa-solid fa-heart',
            'far fa-thumbs-up fa-fw',
            'fab fa-github fa-2xl',
            '  fa fa-star fa-spin  ',
        ];
        foreach ($valid as $value) {
            $this->assertTrue(\videotrack_is_valid_reaction_icon_class($value), $value);
        }

        $invalid = [
            '',
            'fa-solid',
            'fa-heart fa-star',
            'fa-solid fa-heart fa-fw fa-spin fa-lg',
            'fa-solid fa-heart<script>',
            'FA-SOLID FA-HEART',
            'fa-a',
            str_repeat('a', 161),
        ];
        foreach ($invalid as $value) {
            $this->assertFalse(\videotrack_is_valid_reaction_icon_class($value), $value);
        }
    }

    /**
     * Video-source normalisation accepts only the Moodle form contract or null.
     */
    public function test_video_field_processing_rejects_unknown_form_objects(): void {
        $this->expectException(\coding_exception::class);
        \videotrack_process_video_fields((object)['videosource' => 'upload'], new \stdClass());
    }

    /**
     * File saves can resolve the module context from the persisted instance id.
     */
    public function test_uploaded_files_resolve_context_without_coursemodule_form_field(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $usercontext = \context_user::instance((int)$user->id);
        $fs = get_file_storage();

        $videodraftid = file_get_unused_draft_itemid();
        $fs->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $videodraftid,
            'filepath' => '/',
            'filename' => 'video.mp4',
        ], 'video');
        \videotrack_save_uploaded_video((int)$activity->id, (object)[
            'course' => $course->id,
            'videofile' => $videodraftid,
        ]);

        $posterdraftid = file_get_unused_draft_itemid();
        $fs->create_file_from_string([
            'contextid' => $usercontext->id,
            'component' => 'user',
            'filearea' => 'draft',
            'itemid' => $posterdraftid,
            'filepath' => '/',
            'filename' => 'poster.png',
        ], 'poster');
        \videotrack_save_poster_image((int)$activity->id, (object)[
            'course' => $course->id,
            'posterimage' => $posterdraftid,
        ]);

        $context = \context_module::instance((int)$activity->cmid);
        $this->assertTrue($fs->file_exists($context->id, 'mod_videotrack', 'videocontent', 0, '/', 'video.mp4'));
        $this->assertTrue($fs->file_exists($context->id, 'mod_videotrack', 'posterimage', 0, '/', 'poster.png'));
    }

    /**
     * Uploaded-media URLs are returned only for the matching activity instance.
     */
    public function test_upload_url_rejects_course_module_instance_mismatch(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', [
            'course' => $course->id,
            'behathtml5fixture' => 1,
        ]);

        $this->assertInstanceOf(
            \moodle_url::class,
            \videotrack_get_upload_url((int)$activity->id, (int)$activity->cmid)
        );
        $this->assertNull(\videotrack_get_upload_url((int)$activity->id + 1, (int)$activity->cmid));
    }

    /**
     * The instance bookmark checkbox must be persisted as a strict boolean field.
     */
    public function test_player_behavior_fields_normalise_bookmark_setting(): void {
        $disabled = (object)[];
        \videotrack_process_player_behavior_fields($disabled);
        $this->assertSame(0, $disabled->bookmarksenabled);
        $this->assertSame(0, $disabled->studentnotesenabled);

        $enabled = (object)[
            'bookmarksenabled' => '1',
            'studentnotesenabled' => '1',
            'integrityindicatorsenabled' => '1',
            'pauseonfocusloss' => '1',
            'preventpictureinpicture' => '1',
            'randomfocuspauses' => '1',
        ];
        \videotrack_process_player_behavior_fields($enabled);
        $this->assertSame(1, $enabled->bookmarksenabled);
        $this->assertSame(1, $enabled->studentnotesenabled);
        $this->assertSame(1, $enabled->integrityindicatorsenabled);
        $this->assertSame(1, $enabled->pauseonfocusloss);
        $this->assertSame(1, $enabled->preventpictureinpicture);
        $this->assertSame(1, $enabled->randomfocuspauses);
    }

    /**
     * Provider transcript and chapter switches must survive caption normalisation.
     */
    public function test_caption_normalisation_preserves_provider_timed_text_settings(): void {
        $data = (object)[
            'videosource' => 'youtube',
            'captions' => 0,
            'captionslang' => '',
            'showtranscript' => 1,
            'showchapters' => 1,
        ];

        \videotrack_process_captions_fields($data);

        $this->assertSame(1, $data->showtranscript);
        $this->assertSame(1, $data->showchapters);
        $this->assertSame(0, $data->captions);
    }

    /**
     * Record whitelisting keeps table columns and discards form-only fields.
     */
    public function test_whitelist_record_discards_non_table_fields(): void {
        $record = \videotrack_whitelist_record((object)[
            'course' => 17,
            'name' => 'Whitelisted activity',
            'videofile' => 42,
        ]);

        $this->assertSame(17, $record->course);
        $this->assertSame('Whitelisted activity', $record->name);
        $this->assertFalse(property_exists($record, 'videofile'));
    }
}
