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

/**
 * Static contracts for resume, completion synchronisation and player notices.
 *
 * These contracts protect provider parity while browser/Behat execution is
 * temporarily unavailable in the maintainer environment.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class player_resume_completion_alert_contract_test extends advanced_testcase {
    /**
     * Automatic resume must stay inside the server-validated frontier when forward seek is disabled.
     */
    public function test_all_providers_clamp_resume_to_validated_frontier(): void {
        foreach (['player.js', 'html5_player.js', 'vimeo_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);

            $start = strpos($source, 'function resolveResumePosition(');
            $this->assertNotFalse($start, $filename);
            $end = strpos($source, "\n    function ", $start + 1);
            $section = $end === false ? substr($source, $start) : substr($source, $start, $end - $start);

            $this->assertStringContainsString('config.allowseekforward === false', $section, $filename);
            $this->assertStringContainsString('getMaxWatchedFromIntervals(', $section, $filename);
            $this->assertStringContainsString('if (position > allowed + 0.75)', $section, $filename);
            $this->assertStringContainsString('return allowed;', $section, $filename);
        }
    }

    /**
     * A direct replay/report target must take precedence over a stored automatic resume point.
     */
    public function test_direct_replay_precedes_automatic_resume_for_all_providers(): void {
        $youtube = file_get_contents(__DIR__ . '/../amd/src/player.js');
        $html5 = file_get_contents(__DIR__ . '/../amd/src/html5_player.js');
        $vimeo = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($youtube);
        $this->assertIsString($html5);
        $this->assertIsString($vimeo);

        $youtubebuild = strpos($youtube, 'function buildPlayer()');
        $youtubereplay = strpos($youtube, "typeof config.replaystart === 'number'", $youtubebuild);
        $youtuberesume = strpos($youtube, 'initialStart = Math.floor(resolveResumePosition());', $youtubebuild);
        $this->assertNotFalse($youtubereplay);
        $this->assertNotFalse($youtuberesume);
        $this->assertLessThan($youtuberesume, $youtubereplay);

        $html5replay = strpos($html5, "typeof config.replaystart === 'number' && config.replaystart >= 0");
        $html5resume = strpos($html5, 'var resumePosition = resolveResumePosition(config.resumeposition);', $html5replay);
        $this->assertNotFalse($html5replay);
        $this->assertNotFalse($html5resume);
        $this->assertLessThan($html5resume, $html5replay);

        $vimeoready = strpos($vimeo, 'player.ready().then(function()');
        $vimeoreplay = strpos($vimeo, 'var directReplayStart =', $vimeoready);
        $vimeobranch = strpos($vimeo, 'if (directReplayStart !== null)', $vimeoreplay);
        $vimeoresume = strpos($vimeo, '} else if (resumePosition > 2)', $vimeobranch);
        $this->assertNotFalse($vimeoreplay);
        $this->assertNotFalse($vimeobranch);
        $this->assertNotFalse($vimeoresume);
        $this->assertLessThan($vimeoresume, $vimeobranch);
    }

    /**
     * Learner mutations that can change custom completion must synchronise Moodle completion.
     */
    public function test_completion_mutations_synchronise_moodle_state(): void {
        $savesegment = file_get_contents(__DIR__ . '/../classes/external/save_segment.php');
        $savereaction = file_get_contents(__DIR__ . '/../classes/external/save_reaction.php');
        $deletereaction = file_get_contents(__DIR__ . '/../classes/external/delete_reaction.php');
        $view = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($savesegment);
        $this->assertIsString($savereaction);
        $this->assertIsString($deletereaction);
        $this->assertIsString($view);

        foreach ([$savesegment, $savereaction, $deletereaction] as $source) {
            $this->assertStringContainsString('tracker::update_moodle_completion_if_changed(', $source);
        }
        foreach ([$savereaction, $deletereaction] as $source) {
            $this->assertStringContainsString('tracker::refresh_completion(', $source);
        }

        $confirm = strpos($view, "if (\$ackaction === 'confirm')");
        $refresh = strpos($view, 'tracker::refresh_completion(', $confirm);
        $moodle = strpos($view, '$completion->update_state($cm, COMPLETION_UNKNOWN', $confirm);
        $this->assertNotFalse($confirm);
        $this->assertNotFalse($refresh);
        $this->assertNotFalse($moodle);
        $this->assertLessThan($moodle, $refresh);
    }

    /**
     * VideoTrack completion events and Moodle writes must be transition-aware rather than heartbeat-noisy.
     */
    public function test_completion_transitions_avoid_redundant_events_and_writes(): void {
        $tracker = file_get_contents(__DIR__ . '/../classes/local/tracker.php');
        $this->assertIsString($tracker);

        $this->assertStringContainsString('if (!$wascompleted && $state->iscompleted)', $tracker);
        $this->assertStringContainsString('activity_completed::create([', $tracker);
        $this->assertStringContainsString('$currentlycomplete = in_array($currentstate, $completestates, true);', $tracker);
        $this->assertStringContainsString(
            "if ((\$iscompleted && !\$currentlycomplete) || (!\$iscompleted && \$currentstate !== COMPLETION_INCOMPLETE))",
            $tracker
        );
    }

    /**
     * Persistent resume/policy notices must coexist with transient status messages.
     */
    public function test_persistent_notices_coexist_with_transient_status_messages(): void {
        $status = file_get_contents(__DIR__ . '/../amd/src/core/status.js');
        $resume = file_get_contents(__DIR__ . '/../amd/src/core/player/resume.js');
        $this->assertIsString($status);
        $this->assertIsString($resume);

        $clearstart = strpos($status, 'function clear(container)');
        $policystart = strpos($status, 'function showPolicy(', $clearstart);
        $this->assertNotFalse($clearstart);
        $this->assertNotFalse($policystart);
        $clearsection = substr($status, $clearstart, $policystart - $clearstart);
        $this->assertStringContainsString("remove(target.querySelector('.videotrack-status-message'))", $clearsection);
        $this->assertStringNotContainsString('videotrack-seek-policy-notice', $clearsection);
        $this->assertStringNotContainsString('videotrack-resume-notice', $clearsection);

        $policyend = strpos($status, "\n    function show(", $policystart);
        $this->assertNotFalse($policyend);
        $policysection = substr($status, $policystart, $policyend - $policystart);
        $this->assertStringContainsString('videotrack-seek-policy-notice videotrack-inline-notice', $policysection);
        $this->assertStringNotContainsString('clear(container);', $policysection);

        $this->assertStringContainsString("notice.id = 'videotrack-resume-notice';", $resume);
        $this->assertStringContainsString("notice.setAttribute('role', 'status');", $resume);
        $this->assertStringContainsString('videotrack-inline-notice-close', $resume);
    }
}
