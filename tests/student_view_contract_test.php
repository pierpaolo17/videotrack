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
 * Static contracts for the learner-facing VideoTrack page.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class student_view_contract_test extends advanced_testcase {
    /**
     * Personal history lists must use independent native details sections.
     */
    public function test_personal_lists_are_native_collapsible_sections(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);

        foreach (
            [
                'videotrack-student-section-reactions',
                'videotrack-student-section-notes',
                'videotrack-student-section-bookmarks',
            ] as $class
        ) {
            $this->assertStringContainsString($class, $source);
        }
        $this->assertGreaterThanOrEqual(3, substr_count($source, "html_writer::start_tag('details'"));
        $this->assertStringContainsString("get_string('reportstudent', 'mod_videotrack')", $source);
        $this->assertStringContainsString("get_string('studentnotes_title', 'mod_videotrack')", $source);
        $this->assertStringContainsString("get_string('bookmarks_title', 'mod_videotrack')", $source);
        $this->assertStringNotContainsString("'open' => 'open'", $source);
    }

    /**
     * Learner controls and saved history must follow the stable vertical page order.
     */
    public function test_learner_page_has_stable_vertical_order(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);

        $markers = [
            "'videotrack-player-notice videotrack-inline-notice alert alert-info'",
            "start_div('videotrack-player-wrap'",
            "'id'         => 'videotrack-interval-bar'",
            "'id' => 'videotrack-progress-summary'",
            "start_div('videotrack-reactions mt-3'",
            "'id' => 'videotrack-reactions-list-section'",
            "'id' => 'videotrack-note-composer'",
            "'id' => 'videotrack-notes-panel'",
            "'id' => 'videotrack-bookmark-composer'",
            "'id' => 'videotrack-bookmarks-panel'",
            "'id' => 'videotrack-forum-action'",
        ];
        $positions = [];
        foreach ($markers as $marker) {
            $position = strpos($source, $marker);
            $this->assertNotFalse($position, 'Missing learner-page marker: ' . $marker);
            $positions[] = $position;
        }
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($positions, $sorted);

        $notecomposer = substr(
            $source,
            $positions[6],
            $positions[7] - $positions[6]
        );
        $notesdetails = substr(
            $source,
            $positions[7],
            $positions[8] - $positions[7]
        );
        $bookmarkcomposer = substr(
            $source,
            $positions[8],
            $positions[9] - $positions[8]
        );

        $this->assertStringContainsString("'id'          => 'videotrack-note-input'", $notecomposer);
        $this->assertStringNotContainsString("'id'          => 'videotrack-note-input'", $notesdetails);
        $this->assertStringContainsString("'id' => 'videotrack-bookmark-input'", $bookmarkcomposer);

        $styles = file_get_contents(__DIR__ . '/../styles.css');
        $this->assertIsString($styles);
        $selector = '.path-mod-videotrack .activity-header .badge.bg-light.text-dark {';
        $start = strpos($styles, $selector);
        $this->assertNotFalse($start);
        $end = strpos($styles, '}', $start);
        $this->assertNotFalse($end);
        $rule = substr($styles, $start, $end - $start);
        $this->assertStringContainsString('--bs-bg-opacity: 0;', $rule);
        $this->assertStringContainsString('border: 0;', $rule);
        $this->assertStringContainsString('border-radius: 0;', $rule);
        $this->assertStringContainsString('padding: 0;', $rule);
    }
}
