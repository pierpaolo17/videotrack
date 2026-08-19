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
        $this->assertStringNotContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_', $readme);
        $this->assertStringNotContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_', $readmeit);
        $moodleignore = file_get_contents(__DIR__ . '/../.moodleignore');
        $this->assertIsString($moodleignore);
        $this->assertStringContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_*.md', $moodleignore);
        $this->assertStringNotContainsString('Current release documented by this tree: **1.6.36**', $readme);
        $this->assertStringNotContainsString('Release documentata da questo albero: **1.6.36**', $readmeit);

        $phpcsconfig = file_get_contents(__DIR__ . '/../phpcs.xml.dist');
        $this->assertIsString($phpcsconfig);
        $this->assertStringContainsString('<rule ref="moodle-extra">', $phpcsconfig);
        $this->assertStringNotContainsString('moodle.Files.LangFilesOrdering.IncorrectOrder', $phpcsconfig);
        $this->assertStringNotContainsString('moodle.Files.LangFilesOrdering.UnexpectedComment', $phpcsconfig);
        $this->assertStringContainsString('moodle.PHPUnit.TestCaseCovers.Missing', $phpcsconfig);
    }


    /**
     * Current documentation indexes and inventories must track the plugin release.
     */
    public function test_current_documentation_tracks_release(): void {
        $plugin = new \stdClass();
        require(__DIR__ . '/../version.php');
        $release = (string)$plugin->release;
        $version = (string)$plugin->version;

        $englishindex = file_get_contents(__DIR__ . '/../docs/en/00_INDEX.md');
        $italianindex = file_get_contents(__DIR__ . '/../docs/it/00_INDEX.md');
        $englishinventory = file_get_contents(__DIR__ . '/../docs/en/03_FILE_INVENTORY.md');
        $italianinventory = file_get_contents(__DIR__ . '/../docs/it/03_FILE_INVENTORY.md');
        $englishaudit = file_get_contents(__DIR__ . '/../docs/en/09_DOCUMENTATION_AUDIT.md');
        $italianaudit = file_get_contents(__DIR__ . '/../docs/it/09_DOCUMENTATION_AUDIT.md');

        $documents = [$englishindex, $italianindex, $englishinventory, $italianinventory, $englishaudit, $italianaudit];
        foreach ($documents as $document) {
            $this->assertIsString($document);
        }
        $privatehistory = glob(__DIR__ . '/../VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_*.md');
        $this->assertIsArray($privatehistory);
        $this->assertCount(0, $privatehistory);
        $this->assertStringNotContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_', $englishindex);
        $this->assertStringNotContainsString('VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_', $italianindex);
        $tick = chr(96);
        $versionmarker = '** (' . $tick . $version . $tick . ')';
        $this->assertStringContainsString('**' . $release . $versionmarker, $englishindex);
        $this->assertStringContainsString('**' . $release . $versionmarker, $italianindex);
        $this->assertStringContainsString('VideoTrack ' . $release . ' tree', $englishinventory);
        $this->assertStringContainsString('VideoTrack ' . $release, $italianinventory);
        $this->assertStringContainsString('VideoTrack **' . $release . $versionmarker, $englishaudit);
        $this->assertStringContainsString('VideoTrack **' . $release . $versionmarker, $italianaudit);
    }

    /**
     * Maintained language packs must expose the same keys and Moodle placeholders.
     */
    public function test_maintained_language_packs_share_keys_and_placeholders(): void {
        $langdir = __DIR__ . '/../lang';
        $languages = ['de', 'en', 'es', 'fr', 'hi', 'it', 'pl', 'pt'];
        $contracts = [];

        foreach ($languages as $language) {
            $source = file_get_contents($langdir . '/' . $language . '/videotrack.php');
            $this->assertIsString($source);
            preg_match_all(
                "/\\\$string\['([^']+)'\]\s*=/",
                $source,
                $keymatches,
                PREG_OFFSET_CAPTURE
            );
            $keys = array_map(static fn(array $match): string => $match[0], $keymatches[1]);
            $sortedkeys = $keys;
            sort($sortedkeys);
            $this->assertCount(count(array_unique($sortedkeys)), $sortedkeys, 'Duplicate language keys in ' . $language);
            $this->assertSame($sortedkeys, $keys, 'Language keys must remain alphabetically ordered: ' . $language);
            $firststring = strpos($source, '$string[');
            $this->assertNotFalse($firststring);
            $this->assertStringNotContainsString("\n//", substr($source, $firststring));

            $placeholders = [];
            $matchcount = count($keymatches[0]);
            for ($index = 0; $index < $matchcount; $index++) {
                $key = $keymatches[1][$index][0];
                $assignmentstart = $keymatches[0][$index][1] + strlen($keymatches[0][$index][0]);
                $assignmentend = $index + 1 < $matchcount ? $keymatches[0][$index + 1][1] : strlen($source);
                $assignment = substr($source, $assignmentstart, $assignmentend - $assignmentstart);
                preg_match_all('/\{\$a(?:->\w+)?\}/', $assignment, $placeholdermatches);
                $values = array_values(array_unique($placeholdermatches[0]));
                sort($values);
                $placeholders[$key] = $values;
            }
            ksort($placeholders);
            $contracts[$language] = ['keys' => $sortedkeys, 'placeholders' => $placeholders];
        }

        foreach ($languages as $language) {
            $this->assertSame($contracts['en']['keys'], $contracts[$language]['keys'], 'Key mismatch: ' . $language);
            $this->assertSame(
                $contracts['en']['placeholders'],
                $contracts[$language]['placeholders'],
                'Placeholder mismatch: ' . $language
            );
        }
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
