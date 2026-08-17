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
use mod_videotrack\local\csv_event_writer;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for the custom CSV event writer.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(csv_event_writer::class)]
final class csv_event_writer_test extends advanced_testcase {
    /**
     * Detailed rows retain user identity, canonical timestamps and creation time.
     */
    public function test_write_detailed_event_row(): void {
        $handle = fopen('php://temp', 'w+');
        $writer = new csv_event_writer(
            $handle,
            ',',
            [],
            (object)['id' => 7, 'fullname' => 'Course', 'shortname' => 'C'],
            (object)['name' => 'Video'],
            [11 => (object)['id' => 11, 'firstname' => 'Ada', 'lastname' => 'Lovelace']],
            21,
            \context_system::instance(),
            120.0,
            false
        );

        $writer->write(11, 'Reaction', 'Like', '', 65.0, 60.0, 70.0, 3, 'Created');
        rewind($handle);

        $this->assertSame(
            "Lovelace,Ada,Reaction,Like,,01:05,01:00,01:10,3,Created\n",
            stream_get_contents($handle)
        );
        fclose($handle);
    }

    /**
     * Overall rows use student count instead of creation time and allow aggregate user id zero.
     */
    public function test_write_overall_event_row(): void {
        $handle = fopen('php://temp', 'w+');
        $writer = new csv_event_writer(
            $handle,
            ',',
            [],
            (object)['id' => 7, 'fullname' => 'Course', 'shortname' => 'C'],
            (object)['name' => 'Video'],
            [],
            21,
            \context_system::instance(),
            120.0,
            true
        );

        $writer->write(0, 'Reaction', 'Like', '', 65.0, 60.0, 70.0, 3, '', 2);
        rewind($handle);

        $this->assertSame(
            ",,Reaction,Like,,01:05,01:00,01:10,3,2\n",
            stream_get_contents($handle)
        );
        fclose($handle);
    }

    /**
     * Positive user ids outside the prepared export user map remain silently excluded.
     */
    public function test_write_skips_unknown_positive_user(): void {
        $handle = fopen('php://temp', 'w+');
        $writer = new csv_event_writer(
            $handle,
            ',',
            [],
            (object)['id' => 7, 'fullname' => 'Course', 'shortname' => 'C'],
            (object)['name' => 'Video'],
            [],
            21,
            \context_system::instance(),
            120.0,
            false
        );

        $writer->write(99, 'Reaction', 'Like', '', 65.0, 65.0, 65.0, 1, 'Created');
        rewind($handle);

        $this->assertSame('', stream_get_contents($handle));
        fclose($handle);
    }
}
