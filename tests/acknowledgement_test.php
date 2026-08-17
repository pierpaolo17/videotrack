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
     * Statement identity changes when content, format or the end-gating policy changes.
     */
    public function test_statement_hash_versions_the_statement_content(): void {
        $instance = (object)[
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => '<p>I have read this.</p>',
            'acknowledgementformat' => FORMAT_HTML,
        ];
        $first = acknowledgement::statement_hash($instance);
        $legacy = hash('sha256', FORMAT_HTML . "\n" . '<p>I have read this.</p>');
        $this->assertSame($legacy, $first);
        $this->assertSame($first, acknowledgement::statement_hash(clone $instance));

        $instance->acknowledgementtiming = acknowledgement::TIMING_VIDEO_END;
        $this->assertNotSame($first, acknowledgement::statement_hash($instance));

        $instance->acknowledgementtiming = acknowledgement::TIMING_ANYTIME;
        $instance->acknowledgementtext = '<p>I have read the updated statement.</p>';
        $this->assertNotSame($first, acknowledgement::statement_hash($instance));
    }

    /**
     * Only a confirmation matching the current statement version is returned.
     */
    public function test_current_record_rejects_superseded_statement_versions(): void {
        global $DB;

        $this->resetAfterTest(true);
        $instance = (object)[
            'id' => 92001,
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => '<p>Original statement</p>',
            'acknowledgementformat' => FORMAT_HTML,
            'acknowledgementtiming' => acknowledgement::TIMING_ANYTIME,
        ];
        $userid = 92002;
        $recordid = $DB->insert_record('videotrack_acknowledge', (object)[
            'videotrackid' => $instance->id,
            'courseid' => 1,
            'cmid' => 1,
            'userid' => $userid,
            'statementhash' => acknowledgement::statement_hash($instance),
            'instanceversion' => 1,
            'timeconfirmed' => time(),
        ]);

        $current = acknowledgement::current_record($instance, $userid);
        $this->assertNotNull($current);
        $this->assertSame($recordid, (int)$current->id);

        $instance->acknowledgementtext = '<p>Updated statement</p>';
        $this->assertNull(acknowledgement::current_record($instance, $userid));

        $instance->acknowledgementtext = '<p>Original statement</p>';
        $instance->acknowledgementtiming = acknowledgement::TIMING_VIDEO_END;
        $this->assertNull(acknowledgement::current_record($instance, $userid));
    }

    /**
     * End-gated confirmation requires persisted tracking to reach the final second.
     */
    public function test_video_end_requirement_uses_persisted_intervals(): void {
        $instance = (object)[
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => 'Required statement',
            'acknowledgementtiming' => acknowledgement::TIMING_VIDEO_END,
            'durationseconds' => 60,
        ];
        $state = (object)[
            'durationseconds' => 60,
            'lastposition' => 20,
            'uniquecoveredseconds' => 30,
            'completionpercent' => 50,
            'intervaljson' => '[[0,58.8]]',
        ];
        $this->assertFalse(acknowledgement::has_reached_video_end($instance, $state));
        $this->assertFalse(acknowledgement::can_confirm($instance, $state));

        $state->intervaljson = '[[0,59.2]]';
        $this->assertTrue(acknowledgement::has_reached_video_end($instance, $state));
        $this->assertTrue(acknowledgement::can_confirm($instance, $state));
    }

    /**
     * Confirmation snapshots preserve unique viewed time and percentage at that moment.
     */
    public function test_progress_snapshot_uses_unique_coverage(): void {
        $instance = (object)['durationseconds' => 100];
        $state = (object)[
            'durationseconds' => 100,
            'lastposition' => 75,
            'uniquecoveredseconds' => 64.125,
            'completionpercent' => 64.13,
            'intervaljson' => '[[0,64.125]]',
        ];
        $snapshot = acknowledgement::progress_snapshot($instance, $state);
        $this->assertSame(64.125, $snapshot['viewedseconds']);
        $this->assertSame(64.13, $snapshot['viewedpercent']);
        $this->assertFalse($snapshot['reachedend']);
    }

    /**
     * Historical client/state duration cannot unlock an end-gated acknowledgement.
     */
    public function test_video_end_requires_teacher_configured_duration(): void {
        $instance = (object)[
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => 'Required statement',
            'acknowledgementtiming' => acknowledgement::TIMING_VIDEO_END,
            'durationseconds' => 0,
        ];
        $state = (object)[
            'durationseconds' => 1,
            'lastposition' => 1,
            'uniquecoveredseconds' => 1,
            'completionpercent' => 100,
            'intervaljson' => '[[0,1]]',
        ];

        $snapshot = acknowledgement::progress_snapshot($instance, $state);
        $this->assertSame(0.0, $snapshot['duration']);
        $this->assertNull($snapshot['viewedpercent']);
        $this->assertFalse($snapshot['reachedend']);
        $this->assertFalse(acknowledgement::can_confirm($instance, $state));
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

    /**
     * Authorised exact reporting can expose one learner's aggregate acknowledgement snapshot.
     */
    public function test_analytics_summary_supports_exact_single_user_reporting(): void {
        $summary = acknowledgement::analytics_summary([(object)[
            'userid' => 10,
            'viewedseconds' => 45.0,
            'viewedpercent' => 75.0,
        ]], 1);

        $this->assertFalse($summary['suppressed']);
        $this->assertFalse($summary['progresssuppressed']);
        $this->assertSame(1, $summary['confirmationcount']);
        $this->assertSame(1, $summary['studentcount']);
        $this->assertSame(45.0, $summary['averageviewedseconds']);
        $this->assertSame(75.0, $summary['averageviewedpercent']);
    }

    /**
     * Analytics summary averages available snapshots and masks small populations.
     */
    public function test_analytics_summary_preserves_legacy_and_privacy_rules(): void {
        $records = [
            (object)['userid' => 10, 'viewedseconds' => 40.0, 'viewedpercent' => 50.0],
            (object)['userid' => 11, 'viewedseconds' => 80.0, 'viewedpercent' => 100.0],
            (object)['userid' => 12, 'viewedseconds' => null, 'viewedpercent' => null],
        ];
        $summary = acknowledgement::analytics_summary($records, 2);
        $this->assertSame(3, $summary['confirmationcount']);
        $this->assertSame(3, $summary['studentcount']);
        $this->assertSame(2, $summary['progresscount']);
        $this->assertSame(1, $summary['progressmissing']);
        $this->assertSame(60.0, $summary['averageviewedseconds']);
        $this->assertSame(75.0, $summary['averageviewedpercent']);
        $this->assertFalse($summary['suppressed']);
        $this->assertFalse($summary['progresssuppressed']);

        $progresssuppressed = acknowledgement::analytics_summary($records, 3);
        $this->assertFalse($progresssuppressed['suppressed']);
        $this->assertTrue($progresssuppressed['progresssuppressed']);
        $this->assertNull($progresssuppressed['averageviewedseconds']);

        $suppressed = acknowledgement::analytics_summary($records, 4);
        $this->assertTrue($suppressed['suppressed']);
        $this->assertNull($suppressed['confirmationcount']);
        $this->assertNull($suppressed['averageviewedpercent']);
    }
}
