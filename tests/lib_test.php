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
     *
     * @covers ::videotrack_supports
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
     *
     * @covers ::videotrack_supports
     */
    public function test_groups_are_explicitly_not_supported(): void {
        $this->assertFalse(\videotrack_supports(FEATURE_GROUPS));
        $this->assertFalse(\videotrack_supports(FEATURE_GROUPINGS));
    }

    /**
     * Activity chooser metadata should remain predictable.
     *
     * @covers ::videotrack_supports
     */
    public function test_activity_chooser_metadata_is_reported(): void {
        $this->assertSame(MOD_ARCHETYPE_RESOURCE, \videotrack_supports(FEATURE_MOD_ARCHETYPE));
        $this->assertSame(MOD_PURPOSE_CONTENT, \videotrack_supports(FEATURE_MOD_PURPOSE));
    }

    /**
     * Unknown features should keep Moodle's default handling path.
     *
     * @covers ::videotrack_supports
     */
    public function test_unknown_feature_returns_null(): void {
        $this->assertNull(\videotrack_supports('mod_videotrack_unknown_feature'));
    }

    /**
     * The instance bookmark checkbox must be persisted as a strict boolean field.
     *
     * @covers ::videotrack_process_player_behavior_fields
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
     *
     * @covers ::videotrack_process_captions_fields
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
}
