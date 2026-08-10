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
 * Static regression contracts for the teacher report.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class report_contract_test extends advanced_testcase {
    /**
     * Per-student reporting must expose reaction replay links.
     */
    public function test_student_report_contains_reaction_replay_section(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString("get_string('report:studentreactions_title'", $source);
        $this->assertStringContainsString('$getstudenteventrecordset()', $source);
        $this->assertStringContainsString("'replaystart' => max(0, \$replaytimestamp - \$window)", $source);
        $this->assertStringContainsString("'replayend' => \$replaytimestamp + \$window", $source);
    }

    /**
     * Moodle 5.0 save/cancel modals do not expose setCancelButtonText().
     */
    public function test_report_confirmation_uses_supported_modal_api(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/core/confirm.js');
        $this->assertIsString($source);

        $this->assertStringContainsString('modal.setSaveButtonText(strings[1]);', $source);
        $this->assertStringNotContainsString('setCancelButtonText', $source);
        $this->assertStringContainsString('root.on(ModalEvents.save', $source);
        $this->assertStringContainsString('submitForm(form);', $source);
    }

    /**
     * The custom teacher CSV export must offer privacy-safe bookmark counts.
     */
    public function test_custom_csv_export_supports_private_bookmark_counts(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString("optional_param('csvincludebookmarks'", $source);
        $this->assertStringContainsString("'name' => 'csvincludebookmarks'", $source);
        $this->assertStringContainsString("AND notetype = 'bookmark'", $source);
        $this->assertStringContainsString('COUNT(DISTINCT userid) AS studentcount', $source);
        $this->assertStringContainsString("get_string('report:bookmarks_count'", $source);
        $this->assertStringContainsString("get_string('report:csvexport_bookmarks_help'", $source);
        $this->assertStringNotContainsString('SELECT userid, notetext', $source);
    }

    /**
     * Dual-role learners keep their own grade even when they can also view reports.
     */
    public function test_student_grade_visibility_depends_on_participation_not_report_access(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('learner_scope::can_participate($context)', $source);
        $this->assertMatchesRegularExpression(
            '/showgradeto.*?grade.*?\$islearner/s',
            $source
        );
        $this->assertStringNotContainsString(
            '!has_capability(\'mod/videotrack:viewreport\', $context)',
            $source
        );
    }

    /**
     * Student grade label uses the plugin-owned translated string.
     */
    public function test_student_grade_label_uses_plugin_string(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);
        $this->assertStringNotContainsString("get_string('grade')", $source);
        $this->assertStringContainsString("get_string('report:grade', 'mod_videotrack')", $source);
    }
}
