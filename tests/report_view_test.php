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

use mod_videotrack\local\report_view;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural tests for teacher Analytics rendering helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(report_view::class)]
final class report_view_test extends \advanced_testcase {
    /**
     * Load the shared video timestamp helpers used by the presentation helper.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * Visible reaction totals remain rendered while privacy-suppressed totals remain hidden.
     */
    public function test_reaction_summary_preserves_privacy_contract(): void {
        $visible = report_view::reaction_summary([
            'hasdata' => true,
            'suppressed' => false,
            'eventcount' => 7,
            'studentcount' => 3,
        ]);
        $this->assertStringContainsString('7', $visible);
        $this->assertStringContainsString('3', $visible);

        $suppressed = report_view::reaction_summary([
            'hasdata' => true,
            'suppressed' => true,
            'eventcount' => 7,
            'studentcount' => 3,
        ]);
        $this->assertSame('', $suppressed);
    }

    /**
     * A fully privacy-suppressed retention series explains why no line is visible.
     */
    public function test_retention_chart_explains_full_privacy_suppression(): void {
        $markup = report_view::analytics_retention([[
            'start' => 0.0,
            'end' => 10.0,
            'viewers' => null,
            'retention' => null,
            'suppressed' => true,
            'retentionsuppressed' => true,
        ]], 10.0);

        $message = get_string('report:analytics_retention_privacy_hidden', 'mod_videotrack');
        $this->assertStringContainsString(s($message), $markup);
        $this->assertStringContainsString('videotrack-analytics-privacy-label', $markup);
    }

    /**
     * Analytics interval formatting stays tied to the canonical video timestamp helper.
     */
    public function test_analytics_interval_uses_canonical_video_timestamp_format(): void {
        $this->assertSame(
            videotrack_format_video_timestamp(5.0, 65.0) . '–' . videotrack_format_video_timestamp(15.0, 65.0),
            report_view::analytics_interval(5.0, 15.0, 65.0)
        );
    }
}
