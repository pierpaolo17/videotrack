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
 * Runtime contracts for Vimeo blocked-seek recovery.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class vimeo_seek_contract_test extends advanced_testcase {
    /**
     * Blocked forward-seek recovery must request play before delayed retries.
     */
    public function test_blocked_seek_requests_immediate_play_before_retry_loop(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $resume = strpos($source, 'function scheduleBlockedSeekResume(wasPlaying, label)');
        $this->assertNotFalse($resume);
        $immediate = strpos($source, 'player.play().catch(function(error)', $resume);
        $retry = strpos($source, 'playVimeoAfterSeek(', $resume);
        $resumestate = strpos($source, 'state._vimeoBlockedSeekResume = {', $resume);

        $this->assertNotFalse($resumestate);
        $this->assertNotFalse($immediate);
        $this->assertNotFalse($retry);
        $this->assertLessThan($immediate, $resumestate);
        $this->assertLessThan($retry, $immediate);
        $this->assertStringContainsString('Vimeo blocked seek immediate resume', $source);
    }

    /**
     * Blocked seek recovery must wait for stable playback evidence before clearing state.
     */
    public function test_blocked_seek_requires_time_advance_before_recovery_completes(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $resume = strpos($source, 'function scheduleBlockedSeekResume(wasPlaying, label)');
        $this->assertNotFalse($resume);
        $section = substr($source, $resume, 1800);

        $this->assertStringContainsString('requiredPlayingObservations: 2', $section);
        $this->assertStringContainsString('requireTimeAdvance: true', $section);
        $this->assertStringNotContainsString('forcePlay: true', $section);
    }

    /**
     * Vimeo play events must not clear blocked-seek recovery before stable playback is confirmed.
     */
    public function test_play_event_does_not_clear_blocked_seek_recovery_early(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $playhandler = strpos($source, "player.on('play', function()");
        $this->assertNotFalse($playhandler);
        $branch = strpos($source, 'if (state._vimeoBlockedSeekResume) {', $playhandler);
        $this->assertNotFalse($branch);
        $nextbranch = strpos($source, 'if (isVimeoForwardTimeBlocked(t, allowedLimit)) {', $branch);
        $this->assertNotFalse($nextbranch);
        $section = substr($source, $branch, $nextbranch - $branch);

        $this->assertStringContainsString('ensureVimeoRuntimePlaying(t);', $section);
        $this->assertStringNotContainsString('clearBlockedSeekResumeRequest();', $section);
        $this->assertStringContainsString(
            'Keep blocked-seek recovery active until playVimeoAfterSeek confirms stable playback.',
            $section
        );
    }
}
