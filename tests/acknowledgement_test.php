<?php
// This file is part of Moodle - https://moodle.org/
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
use mod_videotrack\local\acknowledgement;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for versioned learner acknowledgement helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(acknowledgement::class)]
final class acknowledgement_test extends advanced_testcase {
    /**
     * Statement identity changes only when the stored content or format changes.
     */
    public function test_statement_hash_versions_the_statement_content(): void {
        $instance = (object)[
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => '<p>I have read this.</p>',
            'acknowledgementformat' => FORMAT_HTML,
        ];
        $first = acknowledgement::statement_hash($instance);
        $this->assertSame($first, acknowledgement::statement_hash(clone $instance));

        $instance->acknowledgementtext = '<p>I have read the updated statement.</p>';
        $this->assertNotSame($first, acknowledgement::statement_hash($instance));
    }

    /**
     * Empty or disabled statements are never offered for confirmation.
     */
    public function test_enabled_state_requires_nonempty_visible_text(): void {
        $instance = (object)[
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => '<p><br></p>',
        ];
        $this->assertFalse(acknowledgement::is_enabled($instance));

        $instance->acknowledgementtext = '<p>&nbsp; &nbsp;</p>';
        $this->assertFalse(acknowledgement::is_enabled($instance));

        $instance->acknowledgementtext = '<p>Required statement</p>';
        $this->assertTrue(acknowledgement::is_enabled($instance));

        $instance->acknowledgementenabled = 0;
        $this->assertFalse(acknowledgement::is_enabled($instance));
    }
}
