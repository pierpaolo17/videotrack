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
 * Static contracts for provider seek snapshots and rollback-safe learner actions.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class provider_seek_snapshot_contract_test extends advanced_testcase {
    /**
     * YouTube must snapshot the trusted pre-seek position before a blocked rollback.
     */
    public function test_youtube_blocked_seek_snapshots_pre_seek_position_before_rollback(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/player.js');
        $this->assertIsString($source);

        $start = strpos($source, 'function blockForwardSeek(target, fallbackTime, previousTime)');
        $end = strpos($source, 'function saveSegment(', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $section = substr($source, $start, $end - $start);

        $snapshot = strpos($section, "Tracker.saveOpenSegmentSnapshot(state, previous, saveSegment, 'seek')");
        $rollback = strpos($section, 'Adapter.seek(fallback');
        $this->assertNotFalse($snapshot);
        $this->assertNotFalse($rollback);
        $this->assertLessThan($rollback, $snapshot);
        $this->assertStringNotContainsString('saveOpenSegmentSnapshot(state, fallback', $section);
    }

    /**
     * HTML5 must snapshot the trusted pre-seek position before a blocked rollback.
     */
    public function test_html5_blocked_seek_snapshots_pre_seek_position_before_rollback(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/html5_player.js');
        $this->assertIsString($source);

        $start = strpos($source, 'function blockForwardSeek(target, fallbackTime)');
        $end = strpos($source, 'function saveSegment(', $start);
        $this->assertNotFalse($start);
        $this->assertNotFalse($end);
        $section = substr($source, $start, $end - $start);

        $snapshot = strpos($section, "Tracker.saveOpenSegmentSnapshot(state, previous, saveSegment, 'seek')");
        $rollback = strpos($section, 'media.currentTime = fallback;');
        $this->assertNotFalse($snapshot);
        $this->assertNotFalse($rollback);
        $this->assertLessThan($rollback, $snapshot);
        $this->assertStringNotContainsString('saveOpenSegmentSnapshot(state, fallback', $section);
    }

    /**
     * Vimeo must retain the provider-independent pre-seek position for user seeks.
     */
    public function test_vimeo_user_seek_retains_pre_seek_position_for_seek_resolution(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $remember = strpos($source, 'function rememberVimeoUserSeek(target)');
        $clear = strpos($source, 'function clearBlockedSeekRecoveryTimers()', $remember);
        $this->assertNotFalse($remember);
        $this->assertNotFalse($clear);
        $remembersection = substr($source, $remember, $clear - $remember);
        $this->assertStringContainsString('previous: Tracker.normaliseTime(state.lasttime),', $remembersection);
        $this->assertStringContainsString('allowedLimit: getAllowedForwardLimit(),', $remembersection);

        $seeked = strpos($source, "player.on('seeked', function(data)");
        $ratechange = strpos($source, "player.on('playbackratechange', function(data)", $seeked);
        $this->assertNotFalse($seeked);
        $this->assertNotFalse($ratechange);
        $seekedsection = substr($source, $seeked, $ratechange - $seeked);
        $snapshot = strpos($seekedsection, "saveSegment(state.segmentstart, seek.oldTime, 'seek');");
        $newsegment = strpos($seekedsection, 'startSegment(seek.newTime);');
        $this->assertNotFalse($snapshot);
        $this->assertNotFalse($newsegment);
        $this->assertLessThan($newsegment, $snapshot);
    }

    /**
     * All providers must use the rollback-safe interaction timestamp for learner actions.
     */
    public function test_all_providers_wire_rollback_safe_timestamp_to_personal_actions(): void {
        foreach (['player.js', 'html5_player.js', 'vimeo_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString('function getInteractionVideoTime()', $source, $filename);
            $this->assertStringContainsString('return Tracker.normaliseTime(state.lasttime);', $source, $filename);
            $this->assertStringContainsString("saveCurrentProgress('reaction').then", $source, $filename);
            $this->assertStringContainsString('Promise.resolve(getInteractionVideoTime()).then', $source, $filename);
            $this->assertGreaterThanOrEqual(
                2,
                substr_count($source, 'getCurrentVideoTime: getInteractionVideoTime'),
                $filename
            );
            $this->assertStringContainsString('getCurrentTime: getInteractionVideoTime', $source, $filename);
        }
    }
}
