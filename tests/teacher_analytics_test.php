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

use mod_videotrack\local\teacher_analytics;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for cross-course dashboard helpers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(teacher_analytics::class)]
final class teacher_analytics_test extends \advanced_testcase {
    /**
     * Relative periods produce stable inclusive timestamp bounds.
     */
    public function test_period_bounds(): void {
        $now = 2000000000;
        $this->assertSame([0, 0], teacher_analytics::period_bounds(0, $now));
        $this->assertSame([$now - (7 * DAYSECS), $now], teacher_analytics::period_bounds(7, $now));
        $this->assertSame([0, 0], teacher_analytics::period_bounds(-1, $now));
    }
}
