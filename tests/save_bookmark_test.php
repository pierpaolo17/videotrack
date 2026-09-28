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

    /**
     * Bookmark timestamps remain bounded by zero and the configured media duration.
     */
    public function test_video_time_normalisation_clamps_to_media_bounds(): void {
        $method = new \ReflectionMethod(save_bookmark::class, 'normalise_video_time');
        $videotrack = (object)['durationseconds' => 120.0];

        $this->assertSame(0.0, $method->invoke(null, $videotrack, -1.0));
        $this->assertSame(40.25, $method->invoke(null, $videotrack, 40.25));
        $this->assertSame(120.0, $method->invoke(null, $videotrack, 180.0));

        unset($videotrack->durationseconds);
        $this->assertSame(180.0, $method->invoke(null, $videotrack, 180.0));
    }

    /**
     * Validation and security guards remain ordered before persistence.
     */
    public function test_execute_keeps_validation_and_security_guards_before_insert(): void {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_bookmark.php');
        $this->assertIsString($source);
        $execute = strpos($source, 'public static function execute(');
        $normalise = strpos($source, 'private static function normalise_label(');

        $this->assertIsInt($execute);
        $this->assertIsInt($normalise);
        $body = substr($source, $execute, $normalise - $execute);
        $steps = [
            'helper::require_ajax_sesskey();',
            'helper::load_and_validate_context(',
            'self::normalise_label(',
            'self::normalise_video_time(',
            'self::require_watched_position(',
            'self::require_bookmark_rate_limit(',
            'self::insert_bookmark_record(',
            'self::trigger_bookmark_event(',
        ];
        $previous = -1;
        foreach ($steps as $step) {
            $position = strpos($body, $step);
            $this->assertIsInt($position, 'Missing save-bookmark orchestration step: ' . $step);
            $this->assertGreaterThan($previous, $position, 'Out-of-order save-bookmark step: ' . $step);
            $previous = $position;
        }
    }

    /**
     * Rate limiting remains global across sessions for one user and activity.
     */
    public function test_bookmark_rate_limit_cannot_be_bypassed_with_multiple_sessions(): void {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_bookmark.php');
        $this->assertIsString($source);
        $start = strpos($source, 'private static function require_bookmark_rate_limit(');
        $end = strpos($source, 'private static function insert_bookmark_record(', $start === false ? 0 : $start);

        $this->assertIsInt($start);
        $this->assertIsInt($end);
        $rateblock = substr($source, $start, $end - $start);
        $querystart = strpos($rateblock, '$recent = $DB->count_records_select(');
        $queryend = strpos($rateblock, 'if ($recent >= 10)');

        $this->assertIsInt($querystart);
        $this->assertIsInt($queryend);
        $queryblock = substr($rateblock, $querystart, $queryend - $querystart);
        $this->assertStringContainsString("'since' => time() - 10", $queryblock);
        $this->assertStringContainsString('if ($recent >= 10)', $rateblock);
        $this->assertStringNotContainsString('sessionid', $queryblock);
    }
}
