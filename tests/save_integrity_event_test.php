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
use mod_videotrack\external\save_integrity_event;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the integrity-indicator external function declaration.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(save_integrity_event::class)]
final class save_integrity_event_test extends advanced_testcase {
    /**
     * The external parameters use Moodle-supported types.
     */
    public function test_execute_parameters_uses_supported_moodle_parameter_types(): void {
        $this->assertInstanceOf(
            external_function_parameters::class,
            save_integrity_event::execute_parameters()
        );
    }
}
