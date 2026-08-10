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
 * Contract tests for Analytics query-shape optimisations.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics_performance_contract_test extends advanced_testcase {
    /**
     * Teacher activity options must not execute the full Analytics aggregation.
     */
    public function test_teacher_activity_options_use_lightweight_modinfo_path(): void {
        $source = file_get_contents(__DIR__ . '/../classes/local/teacher_analytics.php');
        $this->assertIsString($source);
        $start = strpos($source, 'public static function activity_options');
        $end = strpos($source, 'public static function group_options', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $method = substr($source, $start, $end - $start);

        $this->assertStringContainsString('get_fast_modinfo($course, $userid)', $method);
        $this->assertStringContainsString("has_capability('mod/videotrack:viewreport'", $method);
        $this->assertStringNotContainsString('course_analytics::get_course_rows', $method);
    }

    /**
     * Course Analytics must batch event aggregates and preload course group existence.
     */
    public function test_course_dashboard_batches_event_summaries_and_group_scope(): void {
        $source = file_get_contents(__DIR__ . '/../classes/local/course_analytics.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('private const EVENT_SCOPE_BATCH_SIZE = 20;', $source);
        $this->assertStringContainsString('load_event_summaries_for_scopes(', $source);
        $this->assertStringContainsString('array_chunk($scopes, self::EVENT_SCOPE_BATCH_SIZE, true)', $source);
        $this->assertStringContainsString('UNION ALL', $source);
        $this->assertStringNotContainsString('private static function load_event_summary(', $source);
        $this->assertStringContainsString('$coursehasgroups = $DB->record_exists(\'groups\'', $source);
        $this->assertStringContainsString(
            'analytics_scope::accessible_group_ids($instance, $viewerid, $coursehasgroups)',
            $source
        );
    }
}
