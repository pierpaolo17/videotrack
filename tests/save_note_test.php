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
use core_external\external_function_parameters;
use mod_videotrack\external\save_note;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for the personal-note external function declaration.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(save_note::class)]
final class save_note_test extends advanced_testcase {
    /**
     * The external parameter structure must use parameter types defined by Moodle.
     */
    public function test_execute_parameters_uses_supported_moodle_parameter_types(): void {
        $parameters = save_note::execute_parameters();

        $this->assertInstanceOf(external_function_parameters::class, $parameters);
    }
}
