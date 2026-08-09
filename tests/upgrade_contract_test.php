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
 * Upgrade-script regression tests.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class upgrade_contract_test extends advanced_testcase {
    /**
     * Gradebook recovery in upgrade.php must not call runtime gradebook APIs.
     */
    public function test_gradebook_recovery_uses_dml_only(): void {
        global $CFG;

        $source = file_get_contents($CFG->dirroot . '/mod/videotrack/db/upgrade.php');
        $this->assertIsString($source);

        $forbidden = [
            'grade_item::',
            'grade_update(',
            '/gradelib.php',
        ];
        foreach ($forbidden as $token) {
            $this->assertStringNotContainsString($token, $source);
        }

        $this->assertStringContainsString("delete_records_select('grade_grades'", $source);
        $this->assertStringContainsString("delete_records_select('grade_items'", $source);
    }
}
