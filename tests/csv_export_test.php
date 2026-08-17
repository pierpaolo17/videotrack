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

        $videotrack->csvdelimiter = csv_export::DELIMITER_SECTION;
        $this->assertSame('§', csv_export::delimiter($videotrack));

        $videotrack->csvdelimiter = csv_export::DELIMITER_HASH;
        $this->assertSame('#', csv_export::delimiter($videotrack));

        $videotrack->csvdelimiter = csv_export::DELIMITER_PIPE;
        $this->assertSame('|', csv_export::delimiter($videotrack));
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
     * Site and activity configuration expose the video-link column.
     */
    public function test_field_options_include_video_link(): void {
        $this->resetAfterTest();

        $this->assertArrayHasKey('videolink', csv_export::field_options(null));
    }

    /**
     * Event headers end with the format-specific count/date column.
     */
    public function test_event_headers_match_custom_export_format(): void {
        $detailed = csv_export::event_headers(false);
        $overall = csv_export::event_headers(true);

        $this->assertSame(get_string('report:csvcol_created', 'mod_videotrack'), end($detailed));
        $this->assertSame(get_string('report:students', 'mod_videotrack'), end($overall));
        $this->assertSame(8, count($detailed));
        $this->assertSame(8, count($overall));
    }

    /**
     * Identity columns export last name and first name separately.
     */
    public function test_identity_columns_split_lastname_and_firstname(): void {
        $course = (object)['id' => 7, 'fullname' => 'Course', 'shortname' => 'C'];
        $videotrack = (object)['name' => 'Video'];
        $user = (object)['id' => 11, 'firstname' => 'Ada', 'lastname' => 'Lovelace'];

        $this->assertSame([get_string('lastname'), get_string('firstname')], csv_export::identity_headers([]));
        $this->assertSame(
            ['Lovelace', 'Ada'],
            csv_export::identity_values([], $course, $videotrack, $user, 'Ada Lovelace', 1, \context_system::instance())
        );
    }

    /**
     * Overall exports concatenate notes inside their time cluster.
     */
    public function test_cluster_notes_concatenates_comments_and_counts_students(): void {
        $notes = [
            (object)['userid' => 10, 'notetext' => 'nota1', 'videotime' => 20.0],
            (object)['userid' => 11, 'notetext' => 'nota2', 'videotime' => 24.0],
            (object)['userid' => 10, 'notetext' => 'nota3', 'videotime' => 27.0],
            (object)['userid' => 12, 'notetext' => 'nota4', 'videotime' => 60.0],
        ];

        $clusters = csv_export::cluster_notes($notes, 10);

        $this->assertCount(2, $clusters);
        $this->assertSame('{nota1}{nota2}{nota3}', $clusters[0]['comment']);
        $this->assertSame(3, $clusters[0]['count']);
        $this->assertSame(2, $clusters[0]['students']);
        $this->assertSame(20.0, $clusters[0]['first']);
        $this->assertSame(27.0, $clusters[0]['last']);
        $this->assertSame('{nota4}', $clusters[1]['comment']);
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

    /**
     * CSV output starts with the UTF-8 BOM expected by common spreadsheet applications.
     */
    public function test_write_utf8_bom(): void {
        $handle = fopen('php://temp', 'w+');
        csv_export::write_utf8_bom($handle);
        rewind($handle);

        $this->assertSame("\xEF\xBB\xBF", fread($handle, 3));
        fclose($handle);
    }

    /**
     * Multibyte delimiters are written without relying on fputcsv single-byte limits.
     */
    public function test_write_row_supports_section_sign_delimiter(): void {
        $handle = fopen('php://temp', 'w+');
        csv_export::write_row($handle, ['venerdì', 'dell’attività', 'a§b'], '§');
        rewind($handle);

        $this->assertSame("venerdì§dell’attività§\"a§b\"\r\n", stream_get_contents($handle));
        fclose($handle);
    }
}
