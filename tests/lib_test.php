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
}
