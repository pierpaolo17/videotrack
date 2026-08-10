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
     * Blocked-seek recovery must resume independently from playback-rate writes.
     */
    public function test_blocked_seek_resume_does_not_wait_for_penalty_promise(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $recover = strpos($source, 'function recoverBlockedSeek(fallback, wasPlaying, label, recoveryRate)');
        $this->assertNotFalse($recover);
        $nextfunction = strpos($source, 'function getBlockedSeekPlaybackRate', $recover);
        $this->assertNotFalse($nextfunction);
        $section = substr($source, $recover, $nextfunction - $recover);
        $resume = strpos($section, 'scheduleBlockedSeekResume(wasPlaying, label, recoveryId);');
        $penalty = strpos($section, 'applyBlockedSeekPenalty(');

        $this->assertNotFalse($resume);
        $this->assertNotFalse($penalty);
        $this->assertLessThan($penalty, $resume);
        $this->assertStringNotContainsString('penaltypromise', $section);
    }

    /**
     * Forward guard state must end with the rollback rather than surviving it.
     */
    public function test_blocked_seek_clears_forward_guard_after_rollback(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $recover = strpos($source, 'function recoverBlockedSeek(fallback, wasPlaying, label, recoveryRate)');
        $this->assertNotFalse($recover);
        $nextfunction = strpos($source, 'function getBlockedSeekPlaybackRate', $recover);
        $this->assertNotFalse($nextfunction);
        $section = substr($source, $recover, $nextfunction - $recover);

        $this->assertStringContainsString('state._vimeoBlockedForwardSeekUntil = 0;', $section);
        $this->assertStringContainsString('state._vimeoBlockedForwardSeekFallback = 0;', $section);
        $this->assertStringNotContainsString('Date.now() + 7500', $section);
    }

    /**
     * Resume retries may request play but must never perform another rollback seek.
     */
    public function test_blocked_seek_resume_retries_never_reseek(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $resume = strpos($source, 'function resumeBlockedSeekIfPaused(label, recoveryId)');
        $this->assertNotFalse($resume);
        $recover = strpos($source, 'function recoverBlockedSeek(', $resume);
        $this->assertNotFalse($recover);
        $section = substr($source, $resume, $recover - $resume);

        $this->assertStringContainsString("player.play().catch(function(error)", $section);
        $this->assertStringContainsString("typeof player.getPaused === 'function'", $section);
        $this->assertStringNotContainsString('player.setCurrentTime(', $section);
        $this->assertStringNotContainsString('playVimeoAfterSeek(', $section);
    }

    /**
     * Transient Vimeo pauses during recovery must not cancel the playback handshake.
     */
    public function test_transient_pause_does_not_cancel_blocked_seek_playback(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $pause = strpos($source, "player.on('pause', function()");
        $this->assertNotFalse($pause);
        $ended = strpos($source, "player.on('ended', function()", $pause);
        $this->assertNotFalse($ended);
        $section = substr($source, $pause, $ended - $pause);
        $guard = strpos($section, 'if (state.ended || state.seekblocked');
        $cancel = strpos($section, 'Api.cancelPlaybackStart(state);');
        $focuspaused = strpos($section, 'focusGuard.setPlaying(false);');

        $this->assertNotFalse($guard);
        $this->assertNotFalse($cancel);
        $this->assertNotFalse($focuspaused);
        $this->assertLessThan($cancel, $guard);
        $this->assertLessThan($focuspaused, $guard);
        $this->assertStringContainsString('resumeBlockedSeekIfPaused(resumelabel, recoveryId);', $section);
    }

    /**
     * Blocked-seek delayed work must remain scoped to the current recovery generation.
     */
    public function test_blocked_seek_resume_is_generation_scoped(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $this->assertStringContainsString('function nextBlockedSeekRecoveryId()', $source);
        $this->assertStringContainsString('function isBlockedSeekRecoveryCurrent(recoveryId)', $source);
        $this->assertStringContainsString('function scheduleBlockedSeekRecoveryTimer(recoveryId, callback, delay)', $source);
        $this->assertStringContainsString('resumeBlockedSeekIfPaused(resumelabel, recoveryId);', $source);
        $this->assertStringNotContainsString('function verifyBlockedSeekRollback(', $source);
    }

    /**
     * The seeked fallback must use provider playback evidence and forward penalty rate.
     */
    public function test_seeked_fallback_uses_vimeo_playback_resolver_and_penalty(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($source);

        $seeked = strpos($source, "player.on('seeked', function(data)");
        $this->assertNotFalse($seeked);
        $playbackratechange = strpos($source, "player.on('playbackratechange', function(data)", $seeked);
        $this->assertNotFalse($playbackratechange);
        $section = substr($source, $seeked, $playbackratechange - $seeked);

        $this->assertStringContainsString('resolveVimeoSeekWasPlaying()', $section);
        $this->assertStringContainsString(
            'seek.forward ? getBlockedSeekPlaybackRate(seek.fallbackTime, seek.oldTime) : undefined',
            $section
        );
    }
}
