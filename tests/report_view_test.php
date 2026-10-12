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

use mod_videotrack\local\report_integrity_view;
use mod_videotrack\local\report_summary_view;
use mod_videotrack\local\report_timeline_view;
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
#[CoversClass(report_timeline_view::class)]
#[CoversClass(report_summary_view::class)]
#[CoversClass(report_integrity_view::class)]
final class report_view_test extends \advanced_testcase {
    /**
     * Load the shared video timestamp helpers used by the presentation services.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * Visible reaction totals remain rendered while privacy-suppressed totals remain hidden.
     */
    public function test_reaction_summary_preserves_privacy_contract(): void {
        $visible = report_summary_view::reaction_summary([
            'hasdata' => true,
            'suppressed' => false,
            'eventcount' => 7,
            'studentcount' => 3,
        ]);
        $this->assertStringContainsString('7', $visible);
        $this->assertStringContainsString('3', $visible);

        $suppressed = report_summary_view::reaction_summary([
            'hasdata' => true,
            'suppressed' => true,
            'eventcount' => 7,
            'studentcount' => 3,
        ]);
        $this->assertSame('', $suppressed);
    }

    /**
     * Heatmap rendering preserves visible, partially hidden and fully suppressed intervals plus reaction markers.
     */
    public function test_heatmap_preserves_privacy_and_reaction_marker_contracts(): void {
        $markup = report_timeline_view::analytics_heatmap([
            [
                'start' => 0.0,
                'end' => 10.0,
                'viewers' => 4,
                'retention' => 80.0,
                'suppressed' => false,
                'retentionsuppressed' => false,
            ],
            [
                'start' => 10.0,
                'end' => 20.0,
                'viewers' => 2,
                'retention' => null,
                'suppressed' => false,
                'retentionsuppressed' => true,
            ],
            [
                'start' => 20.0,
                'end' => 30.0,
                'viewers' => null,
                'retention' => null,
                'suppressed' => true,
                'retentionsuppressed' => true,
            ],
        ], 30.0, [[
            'timestamp' => 15.0,
            'reactionlabel' => 'Useful',
            'count' => 3,
            'students' => 2,
        ]], 2);

        $this->assertSame(3, substr_count($markup, 'class="videotrack-analytics-bin'));
        $this->assertStringContainsString('opacity="' . format_float(1.0, 3, false, true) . '"', $markup);
        $this->assertStringContainsString('opacity="' . format_float(0.5, 3, false, true) . '"', $markup);
        $this->assertStringContainsString('url(#videotrack-analytics-suppressed-pattern)', $markup);
        $this->assertStringContainsString('videotrack-analytics-reaction-cluster', $markup);
        $this->assertStringContainsString('Useful', $markup);
    }

    /**
     * Exact instance Analytics methodology does not present a minimum-user privacy threshold.
     */
    public function test_exact_analytics_methodology_omits_privacy_threshold_text(): void {
        $markup = report_view::analytics_methodology(1, false, true, true, true);

        $this->assertStringContainsString(
            get_string('report:analytics_method_intro', 'mod_videotrack'),
            $markup
        );
        $this->assertStringNotContainsString(
            get_string('report:analytics_method_privacy', 'mod_videotrack', 1),
            $markup
        );
    }

    /**
     * A fully privacy-suppressed retention series explains why no line is visible.
     */
    public function test_retention_chart_explains_full_privacy_suppression(): void {
        $markup = report_timeline_view::analytics_retention([[
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
     * Retention rendering keeps visible points contiguous only until a privacy gap.
     */
    public function test_retention_chart_preserves_visible_paths_and_privacy_gaps(): void {
        $markup = report_timeline_view::analytics_retention([
            [
                'start' => 0.0,
                'end' => 10.0,
                'viewers' => 4,
                'retention' => 100.0,
                'suppressed' => false,
                'retentionsuppressed' => false,
            ],
            [
                'start' => 10.0,
                'end' => 20.0,
                'viewers' => 3,
                'retention' => 75.0,
                'suppressed' => false,
                'retentionsuppressed' => false,
            ],
            [
                'start' => 20.0,
                'end' => 30.0,
                'viewers' => null,
                'retention' => null,
                'suppressed' => true,
                'retentionsuppressed' => true,
            ],
            [
                'start' => 30.0,
                'end' => 40.0,
                'viewers' => 2,
                'retention' => 50.0,
                'suppressed' => false,
                'retentionsuppressed' => false,
            ],
        ], 40.0);

        $this->assertSame(3, substr_count($markup, 'videotrack-analytics-retention-point'));
        $this->assertSame(1, substr_count($markup, 'videotrack-analytics-retention-line'));
        $this->assertStringNotContainsString('videotrack-analytics-privacy-label', $markup);
    }

    /**
     * Acknowledgement rendering preserves visible, progress-hidden and fully hidden states.
     */
    public function test_acknowledgement_summary_preserves_privacy_states(): void {
        $visible = report_summary_view::acknowledgement_summary([
            'hasdata' => true,
            'suppressed' => false,
            'progresssuppressed' => false,
            'confirmationcount' => 4,
            'studentcount' => 2,
            'averageviewedseconds' => 75.0,
            'averageviewedpercent' => 62.5,
            'progressmissing' => 1,
        ], 2, 3, 2, 1);
        $this->assertStringContainsString('>4<', $visible);
        $this->assertStringContainsString('>2<', $visible);
        $this->assertStringContainsString(videotrack_format_seconds(75.0), $visible);
        $this->assertStringContainsString(format_float(62.5, 1) . '%', $visible);
        $this->assertStringContainsString(
            get_string('report:analytics_acknowledgements_legacy', 'mod_videotrack', 1),
            $visible
        );

        $progresshidden = report_summary_view::acknowledgement_summary([
            'hasdata' => true,
            'suppressed' => false,
            'progresssuppressed' => true,
            'confirmationcount' => 4,
            'studentcount' => 2,
            'averageviewedseconds' => null,
            'averageviewedpercent' => null,
            'progressmissing' => 0,
        ], 2, 3, 2, 1);
        $this->assertStringContainsString(
            get_string('report:analytics_acknowledgements_progress_suppressed', 'mod_videotrack', 2),
            $progresshidden
        );
        $this->assertStringContainsString(
            get_string('report:analytics_notavailable_privacy', 'mod_videotrack'),
            $progresshidden
        );

        $hidden = report_summary_view::acknowledgement_summary([
            'hasdata' => true,
            'suppressed' => true,
            'progresssuppressed' => true,
            'confirmationcount' => 4,
            'studentcount' => 2,
            'averageviewedseconds' => 75.0,
            'averageviewedpercent' => 62.5,
            'progressmissing' => 1,
        ], 2, 3, 2, 1);
        $this->assertStringContainsString(
            get_string('report:analytics_acknowledgements_suppressed', 'mod_videotrack', 2),
            $hidden
        );
        $this->assertStringNotContainsString(
            get_string('report:analytics_acknowledgements_legacy', 'mod_videotrack', 1),
            $hidden
        );
    }

    /**
     * Analytics interval formatting stays tied to the canonical video timestamp helper.
     */
    public function test_analytics_interval_uses_canonical_video_timestamp_format(): void {
        $this->assertSame(
            videotrack_format_video_timestamp(5.0, 65.0) . '–' . videotrack_format_video_timestamp(15.0, 65.0),
            report_timeline_view::analytics_interval(5.0, 15.0, 65.0)
        );
    }

    /**
     * Integrity rendering exposes separate contracts for enabled, disabled and control-only states.
     */
    public function test_integrity_summary_states_preserve_messages_and_privacy_table(): void {
        $eventtype = \mod_videotrack\local\integrity::EVENT_TYPES[0];
        $enabled = report_integrity_view::integrity_summary([
            $eventtype => [
                'hasdata' => true,
                'suppressed' => false,
                'eventcount' => 7,
                'studentcount' => 3,
            ],
        ], 2, 4);

        $this->assertStringContainsString(
            get_string('integrity:analytics_enabled', 'mod_videotrack', 4),
            $enabled
        );
        $this->assertStringContainsString('<table', $enabled);
        $this->assertStringContainsString('>7<', $enabled);
        $this->assertStringContainsString('>3<', $enabled);

        $disabled = report_integrity_view::integrity_disabled_summary();
        $this->assertStringContainsString(
            get_string('integrity:analytics_disabled', 'mod_videotrack'),
            $disabled
        );
        $this->assertStringNotContainsString('<table', $disabled);

        $controls = report_integrity_view::integrity_controls_without_recording_summary();
        $this->assertStringContainsString(
            get_string('integrity:analytics_recording_disabled_controls', 'mod_videotrack'),
            $controls
        );
        $this->assertStringNotContainsString('<table', $controls);
    }
}
