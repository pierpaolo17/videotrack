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

use advanced_testcase;
use core_external\external_function_parameters;
use mod_videotrack\external\helper;
use mod_videotrack\external\save_bookmark;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the private-bookmark external function declaration.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(helper::class)]
#[CoversClass(save_bookmark::class)]
final class save_bookmark_test extends advanced_testcase {
    /**
     * The external parameters use Moodle-supported types.
     */
    public function test_execute_parameters_uses_supported_moodle_parameter_types(): void {
        $this->assertInstanceOf(external_function_parameters::class, save_bookmark::execute_parameters());
        $this->assertSame('bookmark', helper::validate_end_reason('bookmark'));
    }

    /**
     * Previously watched positions remain bookmarkable after a backward seek.
     */
    public function test_bookmark_validation_prefers_existing_watched_progress(): void {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_bookmark.php');
        $this->assertIsString($source);

        $anysession = strpos($source, 'tracker::has_watched_videotime_any_session(');
        $policy = strpos($source, 'tracker::interaction_timestamp_allowed(');
        $this->assertIsInt($anysession);
        $this->assertIsInt($policy);
        $this->assertLessThan($policy, $anysession);
        $this->assertStringContainsString('!$alreadywatched', $source);
    }
}
