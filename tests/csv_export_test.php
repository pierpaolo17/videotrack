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
use mod_videotrack\local\csv_export;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * PHPUnit coverage for CSV export configuration helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(csv_export::class)]
final class csv_export_test extends advanced_testcase {
    /**
     * Activity delimiter settings inherit or override the site default.
     */
    public function test_delimiter_resolution(): void {
        $this->resetAfterTest();
        set_config('csvdelimiter', csv_export::DELIMITER_SEMICOLON, 'mod_videotrack');

        $videotrack = (object)['csvdelimiter' => csv_export::DELIMITER_INHERIT];
        $this->assertSame(';', csv_export::delimiter($videotrack));

        $videotrack->csvdelimiter = csv_export::DELIMITER_COMMA;
        $this->assertSame(',', csv_export::delimiter($videotrack));
    }

    /**
     * Submitted checkbox helpers are collapsed into the persisted field list.
     */
    public function test_process_form_fields(): void {
        $data = new stdClass();
        $coursefield = csv_export::form_element_name('coursefullname');
        $emailfield = csv_export::form_element_name('email');
        $data->{$coursefield} = 1;
        $data->{$emailfield} = 1;

        csv_export::process_form_fields($data);

        $this->assertSame('coursefullname,email', $data->csvexportfields);
        $this->assertObjectNotHasProperty($coursefield, $data);
        $this->assertObjectNotHasProperty($emailfield, $data);
    }

    /**
     * Spreadsheet formula prefixes are neutralised without changing normal values.
     */
    public function test_safe_value_blocks_formula_injection(): void {
        $this->assertSame("'=2+2", csv_export::safe_value('=2+2'));
        $this->assertSame("'  @SUM(A1:A2)", csv_export::safe_value('  @SUM(A1:A2)'));
        $this->assertSame('ordinary text', csv_export::safe_value('ordinary text'));
        $this->assertSame(42, csv_export::safe_value(42));
    }
}
