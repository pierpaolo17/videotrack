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
 * Accessibility contracts for interactive timed-text controls.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class accessibility_contract_test extends advanced_testcase {
    /**
     * Chapter buttons must expose a keyboard focus indicator in normal and forced-colour modes.
     */
    public function test_chapter_buttons_have_visible_keyboard_focus_contract(): void {
        $styles = file_get_contents(__DIR__ . '/../styles.css');
        $this->assertIsString($styles);

        $this->assertStringContainsString('.videotrack-chapter-btn:focus {', $styles);
        $this->assertStringContainsString('.videotrack-chapter-btn:focus:not(:focus-visible)', $styles);
        $this->assertStringContainsString('.videotrack-chapter-btn:focus-visible {', $styles);
        $this->assertStringContainsString('outline: 3px solid var(--videotrack-focus-ring);', $styles);
        $this->assertStringContainsString('.videotrack-chapter-btn.videotrack-chapter-active {', $styles);
        $this->assertStringContainsString('outline: 2px solid Highlight;', $styles);
    }

    /**
     * Chapter navigation keeps native button semantics and accessible names.
     */
    public function test_chapter_navigation_keeps_native_button_semantics(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/core/player/timed_text.js');
        $this->assertIsString($source);

        $this->assertStringContainsString("bar.setAttribute('role', 'navigation');", $source);
        $this->assertStringContainsString("bar.setAttribute('aria-label', config.chapterslabel || '');", $source);
        $this->assertStringContainsString("var button = document.createElement('button');", $source);
        $this->assertStringContainsString("button.type = 'button';", $source);
        $this->assertStringContainsString("button.className = 'videotrack-chapter-btn';", $source);
        $this->assertStringContainsString("button.setAttribute('aria-label'", $source);
    }
}
