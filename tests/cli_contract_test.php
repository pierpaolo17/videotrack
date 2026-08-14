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
 * Static contracts for the distributed VideoTrack CLI tools.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class cli_contract_test extends advanced_testcase {
    /**
     * The validator must remain read-only and cover the release-critical contracts.
     */
    public function test_validator_is_read_only_and_covers_release_contracts(): void {
        $source = file_get_contents(__DIR__ . '/../cli/validate.php');
        $this->assertIsString($source);

        $this->assertStringContainsString("define('CLI_SCRIPT', true);", $source);
        $this->assertStringContainsString("require_once(\$CFG->libdir . '/clilib.php');", $source);
        $this->assertStringContainsString("new xmldb_file(\$pluginroot . '/db/install.xml')", $source);
        $this->assertStringContainsString("require(\$pluginroot . '/db/services.php');", $source);
        $this->assertStringContainsString("\$pluginroot . '/amd/src'", $source);
        $this->assertStringContainsString("\$pluginroot . '/lang/'", $source);
        $this->assertStringContainsString("\$pluginroot . '/CHANGELOG.md'", $source);
        $this->assertStringContainsString("'critical_config'", $source);
        $this->assertStringNotContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_', $source);

        foreach (['set_config(', 'insert_record(', 'update_record(', 'delete_records(', 'delete_records_select('] as $write) {
            $this->assertStringNotContainsString($write, $source);
        }
    }

    /**
     * The course Analytics benchmark must measure the real batched aggregation path.
     */
    public function test_course_analytics_benchmark_uses_real_read_only_metrics(): void {
        $source = file_get_contents(__DIR__ . '/../cli/benchmark_course_analytics.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('\\mod_videotrack\\local\\course_analytics::get_course_rows(', $source);
        $this->assertStringContainsString('$DB->perf_get_reads()', $source);
        $this->assertStringContainsString('$DB->perf_get_queries()', $source);
        $this->assertStringContainsString('$DB->perf_get_queries_time()', $source);
        $this->assertStringContainsString("'all_time_single'", $source);
        $this->assertStringContainsString("'all_time_all'", $source);
        $this->assertStringContainsString("'period_single'", $source);
        $this->assertStringContainsString("'period_all'", $source);
        $this->assertStringContainsString("'all_vs_naive_ratio'", $source);

        foreach (['insert_record(', 'update_record(', 'delete_records(', 'set_config('] as $write) {
            $this->assertStringNotContainsString($write, $source);
        }
    }

    /**
     * Administrator and maintainer documentation must point to both CLI tools.
     */
    public function test_cli_tools_are_documented_for_admins_and_maintainers(): void {
        $english = file_get_contents(__DIR__ . '/../docs/en/21_CLI_DIAGNOSTICS.md');
        $italian = file_get_contents(__DIR__ . '/../docs/it/21_CLI_DIAGNOSTICS.md');
        $build = file_get_contents(__DIR__ . '/../docs/en/07_BUILD_TEST_RELEASE.md');
        $this->assertIsString($english);
        $this->assertIsString($italian);
        $this->assertIsString($build);

        foreach ([$english, $italian, $build] as $document) {
            $this->assertStringContainsString('cli/validate.php', $document);
            $this->assertStringContainsString('cli/benchmark_course_analytics.php', $document);
        }
    }
}
