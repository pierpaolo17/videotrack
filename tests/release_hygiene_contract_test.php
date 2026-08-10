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
 * Static contracts for release hygiene and documentation alignment.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class release_hygiene_contract_test extends advanced_testcase {
    /**
     * CSV export formatting must always specify a Moodle context.
     */
    public function test_csv_export_format_strings_have_explicit_context(): void {
        $source = file_get_contents(__DIR__ . '/../classes/local/csv_export.php');
        $this->assertIsString($source);

        preg_match_all('/^.*format_string\(.*$/m', $source, $matches);
        $this->assertCount(5, $matches[0]);
        foreach ($matches[0] as $line) {
            $this->assertStringContainsString("['context' =>", $line);
        }
    }

    /**
     * Italian environment feedback must use native UTF-8 spelling.
     */
    public function test_environment_italian_feedback_uses_utf8(): void {
        $source = file_get_contents(__DIR__ . '/../environment.xml');
        $this->assertIsString($source);

        $this->assertStringContainsString('non è abilitata', $source);
        $this->assertStringContainsString('verrà servito così com\'è', $source);
        $this->assertStringContainsString('può influire', $source);
        $this->assertStringNotContainsString("non e'", $source);
        $this->assertStringNotContainsString("verra'", $source);
        $this->assertStringNotContainsString("cosi'", $source);
        $this->assertStringNotContainsString("puo'", $source);
    }

    /**
     * Root release documentation must point at the current canonical changelog.
     */
    public function test_readmes_and_changelog_track_current_release(): void {
        $changelog = file_get_contents(__DIR__ . '/../CHANGELOG.md');
        $readme = file_get_contents(__DIR__ . '/../README.md');
        $readmeit = file_get_contents(__DIR__ . '/../README_IT.md');
        $this->assertIsString($changelog);
        $this->assertIsString($readme);
        $this->assertIsString($readmeit);

        $plugin = new \stdClass();
        require(__DIR__ . '/../version.php');
        $release = (string)$plugin->release;

        $this->assertStringContainsString('## ' . $release . ' - ', $changelog);
        $this->assertStringContainsString('Current release documented by this tree: **' . $release . '**', $readme);
        $this->assertStringContainsString(
            'Release corrente documentata da questo albero: **' . $release . '**',
            $readmeit
        );
        $this->assertStringContainsString('(CHANGELOG.md)', $readme);
        $this->assertStringContainsString('(CHANGELOG.md)', $readmeit);
        $this->assertStringNotContainsString('Current release documented by this tree: **1.6.36**', $readme);
        $this->assertStringNotContainsString('Release documentata da questo albero: **1.6.36**', $readmeit);
    }

    /**
     * EN and IT privacy summaries must retain the same section structure.
     */
    public function test_privacy_summaries_keep_parallel_section_structure(): void {
        $english = file(__DIR__ . '/../PRIVACY.md', FILE_IGNORE_NEW_LINES);
        $italian = file(__DIR__ . '/../PRIVACY_IT.md', FILE_IGNORE_NEW_LINES);
        $this->assertIsArray($english);
        $this->assertIsArray($italian);

        $englishlevels = array_map(
            static fn(string $line): int => strlen($line) - strlen(ltrim($line, '#')),
            array_values(array_filter($english, static fn(string $line): bool => str_starts_with($line, '#')))
        );
        $italianlevels = array_map(
            static fn(string $line): int => strlen($line) - strlen(ltrim($line, '#')),
            array_values(array_filter($italian, static fn(string $line): bool => str_starts_with($line, '#')))
        );

        $this->assertSame($englishlevels, $italianlevels);
    }
}
