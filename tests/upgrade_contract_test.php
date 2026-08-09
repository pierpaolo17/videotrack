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

        $corefastforward = strpos($source, '$oldversion = 2026060428;');
        $ledgerfastforward = strpos($source, '$oldversion = 2026060447;');
        $firstlegacy = strpos($source, 'if ($oldversion < 2026043008)');
        $this->assertNotFalse($corefastforward);
        $this->assertNotFalse($ledgerfastforward);
        $this->assertNotFalse($firstlegacy);
        $this->assertLessThan($firstlegacy, $corefastforward);
        $this->assertLessThan($firstlegacy, $ledgerfastforward);
        $this->assertStringContainsString('$coremoderntables = [', $source);
        $this->assertStringContainsString("new xmldb_field('studentnotesenabled')", $source);
        $this->assertStringContainsString("new xmldb_field('sessionid')", $source);
        $this->assertStringContainsString("new xmldb_field('requestid')", $source);
        $this->assertStringContainsString("new xmldb_field('servervalidated')", $source);
        $this->assertStringContainsString('if ($oldversion < 2026060453)', $source);
    }

    /**
     * Failed pre-production installs must converge above the obsolete 2026063000 lineage.
     */
    public function test_failed_install_schema_recovery_supersedes_obsolete_lineage(): void {
        global $CFG;

        $versionsource = file_get_contents($CFG->dirroot . '/mod/videotrack/version.php');
        $upgradesource = file_get_contents($CFG->dirroot . '/mod/videotrack/db/upgrade.php');
        $repairsource = file_get_contents($CFG->dirroot . '/mod/videotrack/db/repairlib.php');
        $this->assertIsString($versionsource);
        $this->assertIsString($upgradesource);
        $this->assertIsString($repairsource);

        $matched = preg_match('/\$plugin->version\s*=\s*(\d+);/', $versionsource, $matches);
        $this->assertSame(1, $matched);
        $this->assertGreaterThan(2026063000, (int) $matches[1]);
        $this->assertStringContainsString('if ($oldversion < 2026063001)', $upgradesource);
        $this->assertStringContainsString('videotrack_repair_preproduction_schema();', $upgradesource);
        $this->assertStringContainsString("new xmldb_table('videotrack_progress')", $repairsource);
        $this->assertStringContainsString('$dbman->drop_table($legacytable);', $repairsource);
        $this->assertStringContainsString('foreach ($structure->getTables() as $table)', $repairsource);
        $this->assertStringContainsString('$dbman->table_exists($table)', $repairsource);
        $this->assertStringContainsString('$dbman->field_exists($table, $field)', $repairsource);
        $this->assertStringContainsString('$dbman->index_exists($table, $index)', $repairsource);
        $this->assertStringNotContainsString(
            '$dbman->field_exists(new xmldb_table(\'videotrack_progress\')',
            $upgradesource
        );
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

    /**
     * Fresh installs and 1.6.36 upgrades must repair stale VideoTrack gradebook rows.
     */
    public function test_preproduction_gradebook_repair_covers_install_and_upgrade(): void {
        global $CFG;

        $installsource = file_get_contents($CFG->dirroot . '/mod/videotrack/db/install.php');
        $upgradesource = file_get_contents($CFG->dirroot . '/mod/videotrack/db/upgrade.php');
        $repairsource = file_get_contents($CFG->dirroot . '/mod/videotrack/db/repairlib.php');
        $this->assertIsString($installsource);
        $this->assertIsString($upgradesource);
        $this->assertIsString($repairsource);

        $this->assertStringContainsString('videotrack_repair_preproduction_gradebook_rows();', $installsource);
        $this->assertStringContainsString('if ($oldversion < 2026060452)', $upgradesource);
        $this->assertStringContainsString('videotrack_repair_preproduction_gradebook_rows();', $upgradesource);
        $this->assertStringContainsString("itemmodule = :itemmodule", $repairsource);
        $this->assertStringContainsString("delete_records_select('grade_grades'", $repairsource);
        $this->assertStringContainsString("delete_records_select('grade_items'", $repairsource);
        $this->assertStringNotContainsString('grade_item::', $repairsource);
        $this->assertStringNotContainsString('grade_update(', $repairsource);
    }
}
