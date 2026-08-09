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
     * Modern pre-production schemas must bypass incompatible legacy migrations.
     */
    public function test_modern_schema_fast_forward_precedes_legacy_steps(): void {
        global $CFG;

        $source = file_get_contents($CFG->dirroot . '/mod/videotrack/db/upgrade.php');
        $this->assertIsString($source);

        $fastforward = strpos($source, '$oldversion = 2026060447;');
        $firstlegacy = strpos($source, 'if ($oldversion < 2026043008)');
        $this->assertNotFalse($fastforward);
        $this->assertNotFalse($firstlegacy);
        $this->assertLessThan($firstlegacy, $fastforward);
        $this->assertStringContainsString('table_exists(new xmldb_table($tablename))', $source);
        $this->assertStringContainsString("new xmldb_field('requestid')", $source);
        $this->assertStringContainsString("new xmldb_field('servervalidated')", $source);
    }

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
