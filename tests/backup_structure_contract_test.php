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
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Source contracts for the backup structure.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class backup_structure_contract_test extends advanced_testcase {
    /**
     * The public structure hook must retain its ordered construction stages.
     */
    public function test_structure_hook_delegates_to_ordered_stages(): void {
        $source = $this->source();
        $start = strpos($source, 'protected function define_structure()');
        $end = strpos($source, 'private function create_activity_element()', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $method = substr($source, $start, $end - $start);

        $stages = [
            '$this->create_activity_element()',
            '$this->create_reaction_elements()',
            '$this->create_user_elements()',
            '$this->attach_user_elements($videotrack, $userelements)',
            '$this->configure_sources($videotrack, $reaction, $userelements, $userinfo, $retentioncutoff)',
            '$this->annotate_files($videotrack, $reaction)',
            '$this->prepare_activity_structure($videotrack)',
        ];
        $position = -1;
        foreach ($stages as $stage) {
            $next = strpos($method, $stage);
            $this->assertNotFalse($next);
            $this->assertGreaterThan($position, $next);
            $position = $next;
        }
    }

    /**
     * User data must remain conditional and constrained by retention and user identity.
     */
    public function test_user_data_sources_keep_privacy_boundaries(): void {
        $source = $this->source();

        $this->assertStringContainsString("foreach (['segment', 'reactionevent', 'integrityevent',", $source);
        $this->assertStringContainsString('\'acknowledgement\'] as $name)', $source);
        $this->assertStringContainsString('if ($userinfo) {', $source);
        $this->assertSame(4, substr_count($source, 'AND userid > 0'));
        $this->assertSame(3, substr_count($source, 'AND timecreated >= :cutoff'));
        $this->assertSame(1, substr_count($source, 'AND timeconfirmed >= :cutoff'));
        $this->assertSame(4, substr_count($source, "annotate_ids('user', 'userid')"));
        $this->assertStringContainsString("'cutoff' => backup_helper::is_sqlparam($retentioncutoff)", $source);
    }

    /**
     * Activity, forum and file-area contracts must remain registered.
     */
    public function test_activity_and_file_contracts_remain_complete(): void {
        $source = $this->source();

        $this->assertStringContainsString(
            "set_source_table('videotrack', ['id' => backup::VAR_ACTIVITYID])",
            $source
        );
        $this->assertStringContainsString(
            "set_source_table('videotrack_react', ['videotrackid' => backup::VAR_PARENTID])",
            $source
        );
        $this->assertStringContainsString("annotate_ids('forum', 'linkedforumid')", $source);
        foreach (['intro', 'videocontent', 'subtitles', 'transcripts', 'chapters', 'posterimage'] as $filearea) {
            $this->assertStringContainsString("annotate_files('mod_videotrack', '$filearea', null)", $source);
        }
        $this->assertStringContainsString(
            "annotate_files('mod_videotrack', 'reactionicon', 'id')",
            $source
        );
    }

    /**
     * Read the backup source file.
     *
     * @return string
     */
    private function source(): string {
        $source = file_get_contents(__DIR__ . '/../backup/moodle2/backup_videotrack_stepslib.php');
        $this->assertIsString($source);
        return $source;
    }
}
