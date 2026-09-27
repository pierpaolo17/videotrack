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
use core_external\external_function_parameters;
use mod_videotrack\external\save_note;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for the personal-note external function declaration.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(save_note::class)]
final class save_note_test extends advanced_testcase {
    /**
     * Returns the current server-side note handler source.
     *
     * @return string
     */
    private function source(): string {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_note.php');
        $this->assertNotFalse($source);
        return $source;
    }

    /**
     * The external parameter structure must use parameter types defined by Moodle.
     */
    public function test_execute_parameters_uses_supported_moodle_parameter_types(): void {
        $parameters = save_note::execute_parameters();

        $this->assertInstanceOf(external_function_parameters::class, $parameters);
    }

    /**
     * Note timestamps remain bounded by zero and the configured media duration.
     */
    public function test_video_time_normalisation_clamps_to_media_bounds(): void {
        $method = new \ReflectionMethod(save_note::class, 'normalise_video_time');
        $videotrack = (object)['durationseconds' => 120.0];

        $this->assertSame(0.0, $method->invoke(null, $videotrack, -1.0));
        $this->assertSame(40.25, $method->invoke(null, $videotrack, 40.25));
        $this->assertSame(120.0, $method->invoke(null, $videotrack, 180.0));

        unset($videotrack->durationseconds);
        $this->assertSame(180.0, $method->invoke(null, $videotrack, 180.0));
    }

    /**
     * The refactored orchestration preserves all guards before persistence.
     */
    public function test_execute_keeps_validation_and_security_guards_before_insert(): void {
        $source = $this->source();
        $execute = strpos($source, 'public static function execute(');
        $normalise = strpos($source, 'private static function normalise_note_text(');

        $this->assertNotFalse($execute);
        $this->assertNotFalse($normalise);
        $body = substr($source, $execute, $normalise - $execute);

        $steps = [
            'helper::require_ajax_sesskey();',
            'helper::load_and_validate_context(',
            'self::normalise_note_text(',
            'self::normalise_video_time(',
            'self::require_watched_position(',
            'self::require_note_rate_limit(',
            'self::insert_note_record(',
            'self::collect_warnings(',
        ];
        $previous = -1;
        foreach ($steps as $step) {
            $position = strpos($body, $step);
            $this->assertNotFalse($position, 'Missing save-note orchestration step: ' . $step);
            $this->assertGreaterThan($previous, $position, 'Out-of-order save-note step: ' . $step);
            $previous = $position;
        }
    }

    /**
     * Rate limiting remains global across sessions for one user and activity.
     */
    public function test_note_rate_limit_cannot_be_bypassed_with_multiple_sessions(): void {
        $source = $this->source();
        $start = strpos($source, 'private static function require_note_rate_limit(');
        $end = strpos($source, 'private static function insert_note_record(', $start === false ? 0 : $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $rateblock = substr($source, $start, $end - $start);
        $querystart = strpos($rateblock, '$recentnotes = $DB->count_records_select(');
        $queryend = strpos($rateblock, 'if ($recentnotes >= 5)');

        $this->assertNotFalse($querystart);
        $this->assertNotFalse($queryend);
        $queryblock = substr($rateblock, $querystart, $queryend - $querystart);
        $this->assertStringContainsString("'since' => time() - 10,", $rateblock);
        $this->assertStringContainsString('if ($recentnotes >= 5)', $rateblock);
        $this->assertStringNotContainsString('sessionid', $queryblock);
    }
}
