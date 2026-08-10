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
use mod_videotrack\external\save_reaction;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Regression contracts for reaction anti-spam safeguards.
 *
 * These tests deliberately inspect the external function source because the
 * safeguards are implemented as one atomic DB predicate around a Moodle lock.
 * Keeping that predicate intact avoids extracting production logic merely for
 * testability while still protecting the exact server-side contract.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(save_reaction::class)]
final class save_reaction_test extends advanced_testcase {
    /**
     * Returns the current server-side reaction handler source.
     *
     * @return string
     */
    private function source(): string {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_reaction.php');
        $this->assertNotFalse($source);
        return $source;
    }

    /**
     * Any reaction already saved in the same displayed second blocks another one.
     */
    public function test_same_displayed_second_is_global_across_reaction_types(): void {
        $source = $this->source();

        $this->assertStringContainsString('$displaysecond = (int)round($videotime);', $source);
        $this->assertStringContainsString('$videosecondstart = max(0.0, $displaysecond - 0.5);', $source);
        $this->assertStringContainsString('$videosecondend = $displaysecond + 0.5;', $source);
        $this->assertStringContainsString(
            '(videotime >= :secondstart AND videotime < :secondend) OR ',
            $source
        );
    }

    /**
     * Repeating the same reaction is blocked for three wall-clock or video seconds.
     */
    public function test_same_reaction_keeps_three_second_temporal_window(): void {
        $source = $this->source();

        $this->assertStringContainsString(
            '(reactionid = :reactionid AND (timecreated >= :since OR ABS(videotime - :videotime) < :window))',
            $source
        );
        $this->assertStringContainsString("'since' => \$now - 3,", $source);
        $this->assertStringContainsString("'window' => 3.0,", $source);
    }

    /**
     * The three-second timeline window remains scoped to the same reaction id.
     */
    public function test_different_reactions_are_not_globally_blocked_by_three_second_window(): void {
        $source = $this->source();

        $needle = '(reactionid = :reactionid AND (timecreated >= :since OR ABS(videotime - :videotime) < :window))';
        $this->assertStringContainsString($needle, $source);
        $this->assertSame(1, substr_count($source, 'ABS(videotime - :videotime) < :window'));
    }

    /**
     * Burst protection remains ten reactions in ten seconds across session ids.
     */
    public function test_burst_limit_is_ten_in_ten_seconds_and_not_session_scoped(): void {
        $source = $this->source();
        $start = strpos($source, '$burstcount = $DB->count_records_select(');
        $end = strpos($source, 'if ($burstcount >= 10)', $start === false ? 0 : $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $burstblock = substr($source, $start, $end - $start + strlen('if ($burstcount >= 10)'));

        $this->assertStringContainsString("'bsince' => \$now - 10,", $burstblock);
        $this->assertStringContainsString('if ($burstcount >= 10)', $burstblock);
        $this->assertStringNotContainsString('sessionid', $burstblock);
    }

    /**
     * Near-simultaneous requests are serialised per activity and learner.
     */
    public function test_reaction_writes_remain_serialised_per_activity_and_user(): void {
        $source = $this->source();

        $this->assertStringContainsString(
            "\$reactionlockkey = 'reaction:' . \$videotrack->id . ':' . (int)\$USER->id;",
            $source
        );
        $this->assertStringContainsString('$reactionlockfactory->get_lock($reactionlockkey, 10);', $source);
        $this->assertStringContainsString('$reactionlock->release();', $source);
    }

    /**
     * Duplicate suppression remains a soft ignore rather than a client-visible error.
     */
    public function test_duplicate_reaction_is_soft_ignored(): void {
        $source = $this->source();
        $start = strpos($source, 'if ($duplicatereaction) {');
        $end = strpos($source, '$record = (object)[', $start === false ? 0 : $start);

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $duplicateblock = substr($source, $start, $end - $start);

        $this->assertStringContainsString("'reactioneventid' => 0,", $duplicateblock);
        $this->assertStringContainsString("'warnings'        => [],", $duplicateblock);
    }
}
