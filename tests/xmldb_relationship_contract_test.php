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
 * Contracts for the declarative XMLDB relationship model.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class xmldb_relationship_contract_test extends advanced_testcase {
    /**
     * Stable application references must be declared as XMLDB foreign keys.
     */
    public function test_stable_foreign_keys_are_declared(): void {
        global $CFG;

        $file = new \xmldb_file($CFG->dirroot . '/mod/videotrack/db/install.xml');
        $this->assertTrue($file->loadXMLStructure());
        $structure = $file->getStructure();
        $this->assertNotNull($structure);

        $expected = [
            'videotrack.course_fk' => 'course=>course.id',
            'videotrack_seg.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_seg.course_fk' => 'courseid=>course.id',
            'videotrack_seg.cm_fk' => 'cmid=>course_modules.id',
            'videotrack_seg.user_fk' => 'userid=>user.id',
            'videotrack_state.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_state.course_fk' => 'courseid=>course.id',
            'videotrack_state.cm_fk' => 'cmid=>course_modules.id',
            'videotrack_state.user_fk' => 'userid=>user.id',
            'videotrack_integrity.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_integrity.course_fk' => 'courseid=>course.id',
            'videotrack_integrity.cm_fk' => 'cmid=>course_modules.id',
            'videotrack_integrity.user_fk' => 'userid=>user.id',
            'videotrack_react.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_reactev.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_reactev.course_fk' => 'courseid=>course.id',
            'videotrack_reactev.cm_fk' => 'cmid=>course_modules.id',
            'videotrack_reactev.user_fk' => 'userid=>user.id',
            'videotrack_acknowledge.videotrack_fk' => 'videotrackid=>videotrack.id',
            'videotrack_acknowledge.course_fk' => 'courseid=>course.id',
            'videotrack_acknowledge.cm_fk' => 'cmid=>course_modules.id',
            'videotrack_acknowledge.user_fk' => 'userid=>user.id',
        ];
        $actual = [];
        foreach ($structure->getTables() as $table) {
            foreach ($table->getKeys() as $key) {
                if ($key->getType() !== XMLDB_KEY_FOREIGN) {
                    continue;
                }
                $actual[$table->getName() . '.' . $key->getName()] =
                    implode(',', $key->getFields()) . '=>' . $key->getRefTable() . '.' .
                    implode(',', $key->getRefFields());
            }
        }

        $this->assertSame($expected, $actual);

        $upgradesource = (string)file_get_contents($CFG->dirroot . '/mod/videotrack/db/upgrade.php');
        foreach ($expected as $relation => $target) {
            [, $keyname] = explode('.', $relation, 2);
            [$fields, $reference] = explode('=>', $target, 2);
            [$reftable, $reffields] = explode('.', $reference, 2);
            $definition = "['{$keyname}', ['{$fields}'], '{$reftable}', ['{$reffields}']]";
            $this->assertStringContainsString($definition, $upgradesource);
        }
    }

    /**
     * Fields with a legitimate zero sentinel must remain conditional references.
     */
    public function test_conditional_references_are_not_declared_as_foreign_keys(): void {
        global $CFG;

        $file = new \xmldb_file($CFG->dirroot . '/mod/videotrack/db/install.xml');
        $this->assertTrue($file->loadXMLStructure());
        $structure = $file->getStructure();
        $this->assertNotNull($structure);

        $forbidden = [
            'videotrack.linkedforumid',
            'videotrack_reactev.reactionid',
        ];
        foreach ($structure->getTables() as $table) {
            foreach ($table->getKeys() as $key) {
                if ($key->getType() !== XMLDB_KEY_FOREIGN) {
                    continue;
                }
                foreach ($key->getFields() as $field) {
                    $this->assertNotContains($table->getName() . '.' . $field, $forbidden);
                }
            }
        }
    }

    /**
     * XMLDB generates an exact backing index for each foreign key.
     */
    public function test_foreign_key_indexes_are_not_duplicated_explicitly(): void {
        global $CFG;

        $file = new \xmldb_file($CFG->dirroot . '/mod/videotrack/db/install.xml');
        $this->assertTrue($file->loadXMLStructure());
        $structure = $file->getStructure();
        $this->assertNotNull($structure);

        foreach ($structure->getTables() as $table) {
            $foreignfields = [];
            foreach ($table->getKeys() as $key) {
                if ($key->getType() === XMLDB_KEY_FOREIGN) {
                    $foreignfields[] = implode(',', $key->getFields());
                }
            }
            foreach ($table->getIndexes() as $index) {
                $this->assertNotContains(implode(',', $index->getFields()), $foreignfields);
            }
        }
    }
}
