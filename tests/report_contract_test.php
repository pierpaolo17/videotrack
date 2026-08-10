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
}
