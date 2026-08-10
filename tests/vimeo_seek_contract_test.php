<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

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
        $resumeState = strpos($source, 'state._vimeoBlockedSeekResume = {', $resume);

        $this->assertNotFalse($resumeState);
        $this->assertNotFalse($immediate);
        $this->assertNotFalse($retry);
        $this->assertLessThan($immediate, $resumeState);
        $this->assertLessThan($retry, $immediate);
        $this->assertStringContainsString('Vimeo blocked seek immediate resume', $source);
    }
}
