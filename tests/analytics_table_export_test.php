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

use mod_videotrack\local\analytics_table_export;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for privacy-safe analytics table exports.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(analytics_table_export::class)]
final class analytics_table_export_test extends \advanced_testcase {
    /**
     * Load the shared duration-formatting helpers.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * Export rows preserve masking and optional reaction values.
     */
    public function test_rows_match_accessible_table_privacy_rules(): void {
        $rows = analytics_table_export::rows([
            [
                'start' => 0,
                'end' => 10,
                'viewers' => null,
                'suppressed' => true,
            ],
            [
                'start' => 10,
                'end' => 20,
                'viewers' => 7,
                'retention' => 70.0,
                'uniqueseconds' => 60.0,
                'repeatseconds' => null,
                'repeatviewers' => null,
                'suppressed' => false,
                'repeatsuppressed' => true,
                'retentionsuppressed' => true,
                'reactionclusters' => 2,
                'reactionevents' => 9,
            ],
        ], 20, true, true, 5);

        $this->assertCount(2, $rows);
        $this->assertSame(get_string('report:analytics_suppressed_value', 'mod_videotrack', 5), $rows[0][1]);
        $this->assertSame('', $rows[0][2]);
        $this->assertSame(7, $rows[1][1]);
        $this->assertSame(get_string('report:analytics_notavailable_privacy', 'mod_videotrack'), $rows[1][2]);
        $this->assertSame(get_string('report:analytics_suppressed_value', 'mod_videotrack', 5), $rows[1][5]);
        $this->assertSame(get_string('report:analytics_reactions_cell', 'mod_videotrack', [
            'clusters' => 2,
            'events' => 9,
        ]), $rows[1][6]);
    }

    /**
     * Aggregate-state fallback exports the replay status without fabricating values.
     */
    public function test_rows_mark_unavailable_replay_metrics(): void {
        $rows = analytics_table_export::rows([[
            'start' => 0,
            'end' => 10,
            'viewers' => 3,
            'retention' => 100.0,
            'uniqueseconds' => 30.0,
            'suppressed' => false,
            'repeatsuppressed' => false,
        ]], 10, false, false, 2);

        $this->assertSame(get_string('report:analytics_repeat_unavailable', 'mod_videotrack'), $rows[0][4]);
        $this->assertSame('', $rows[0][5]);
        $this->assertCount(6, $rows[0]);
    }

    /**
     * Combined exports append one privacy-safe acknowledgement summary row.
     */
    public function test_export_rows_include_acknowledgement_summary(): void {
        $columns = analytics_table_export::export_columns(false, true);
        $rows = analytics_table_export::export_rows([], 0, false, false, 5, [
            'hasdata' => true,
            'confirmationcount' => 8,
            'studentcount' => 6,
            'progresscount' => 7,
            'progressstudentcount' => 6,
            'progressmissing' => 1,
            'averageviewedseconds' => 120.5,
            'averageviewedpercent' => 75.25,
            'suppressed' => false,
            'progresssuppressed' => false,
            'enabledactivitycount' => 3,
            'anytimeactivitycount' => 2,
            'videoendactivitycount' => 1,
        ]);

        $this->assertCount(15, $columns);
        $this->assertCount(1, $rows);
        $this->assertCount(15, $rows[0]);
        $this->assertSame(
            get_string('report:analytics_export_row_acknowledgement', 'mod_videotrack'),
            $rows[0][0]
        );
        $this->assertSame(8, $rows[0][7]);
        $this->assertSame(6, $rows[0][8]);
        $this->assertSame(120.5, $rows[0][9]);
        $this->assertSame(format_float(75.25, 1) . '%', $rows[0][10]);
        $this->assertSame(1, $rows[0][11]);
        $this->assertSame(3, $rows[0][12]);
        $this->assertSame(2, $rows[0][13]);
        $this->assertSame(1, $rows[0][14]);
    }
}
