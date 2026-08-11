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
 * Static contract tests for browser AJAX calls and mutation endpoints.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class ajax_contract_test extends advanced_testcase {
    /**
     * Every Moodle AJAX service must be present in the client allowlist.
     */
    public function test_ajax_service_allowlist_matches_declared_services(): void {
        $functions = [];
        require(__DIR__ . '/../db/services.php');

        $declared = [];
        foreach ($functions as $name => $definition) {
            if (!empty($definition['ajax']) && str_starts_with($name, 'mod_videotrack_')) {
                $declared[] = $name;
            }
        }
        sort($declared);

        $validatorsource = file_get_contents(__DIR__ . '/../amd/src/core/api/validator.js');
        $this->assertIsString($validatorsource);
        $this->assertSame(
            1,
            preg_match('/var ALLOWED_METHODS = \{(?<body>.*?)\n    \};/s', $validatorsource, $matches)
        );
        preg_match_all('/\b(mod_videotrack_[a-z0-9_]+)\s*:\s*true\b/', $matches['body'], $allowlistmatches);
        $allowlist = array_values(array_unique($allowlistmatches[1]));
        sort($allowlist);

        $this->assertSame($declared, $allowlist);
        $this->assertStringContainsString('integrity-required-fields', $validatorsource);
        $this->assertStringContainsString('playback-start-required-fields', $validatorsource);
    }

    /**
     * Static API calls in AMD sources must target declared and allowed methods.
     */
    public function test_static_amd_api_calls_are_declared(): void {
        $functions = [];
        require(__DIR__ . '/../db/services.php');
        $declared = array_keys($functions);

        $called = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(__DIR__ . '/../amd/src', \FilesystemIterator::SKIP_DOTS)
        );
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'js') {
                continue;
            }
            $source = file_get_contents($file->getPathname());
            $this->assertIsString($source);
            preg_match_all('/(?:Api\.)?call\(\s*[\'"](mod_videotrack_[a-z0-9_]+)[\'"]/', $source, $matches);
            $called = array_merge($called, $matches[1]);
        }

        foreach (array_unique($called) as $method) {
            $this->assertContains($method, $declared, "Undeclared AJAX method used by AMD: {$method}");
        }
        $this->assertContains('mod_videotrack_save_integrity_event', $called);
        $this->assertContains('mod_videotrack_start_playback', $called);
    }

    /**
     * Browser mutation endpoints must reject invalid sesskeys before loading context data.
     */
    public function test_sesskey_is_checked_before_context_loading(): void {
        $endpoints = [
            'start_playback.php',
            'save_segment.php',
            'save_reaction.php',
            'delete_reaction.php',
            'save_note.php',
            'delete_note.php',
            'save_bookmark.php',
            'delete_bookmark.php',
            'save_integrity_event.php',
        ];

        foreach ($endpoints as $endpoint) {
            $source = file_get_contents(__DIR__ . '/../classes/external/' . $endpoint);
            $this->assertIsString($source);
            $sesskeyposition = strpos($source, 'helper::require_ajax_sesskey();');
            $contextposition = strpos($source, 'helper::load_and_validate_context');
            $this->assertNotFalse($sesskeyposition, "Missing sesskey check in {$endpoint}");
            $this->assertNotFalse($contextposition, "Missing context validation in {$endpoint}");
            $this->assertLessThan($contextposition, $sesskeyposition, "Late sesskey check in {$endpoint}");
        }
    }

    /**
     * Segment persistence must accept the internally configured blocked-seek recovery rate.
     */
    public function test_segment_rate_validation_uses_tracking_speed_contract(): void {
        $source = file_get_contents(__DIR__ . '/../classes/external/save_segment.php');
        $this->assertIsString($source);
        $this->assertStringContainsString('videotrack_get_tracking_playback_speeds($videotrack)', $source);
        $this->assertStringNotContainsString('videotrack_get_playback_speeds($videotrack)', $source);
    }

    /**
     * Explicit learner interactions must surface progress-save failures instead of swallowing them.
     */
    public function test_interaction_progress_flushes_do_not_swallow_ajax_failures(): void {
        foreach (['player.js', 'vimeo_player.js', 'html5_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString(
                "['reaction', 'note', 'bookmark', 'interaction'].indexOf(reason) !== -1",
                $source
            );
            $this->assertStringContainsString('swallowFailures: !interactionSave', $source);
        }
    }

    /**
     * Interaction timestamps must ignore zero-valued no-op segment responses.
     */
    public function test_note_and_bookmark_timestamps_fall_back_when_saved_end_is_zero(): void {
        $resolvers = [
            'core/player/bookmarks.js' => 'resolveBookmarkTime',
            'core/player/notes.js' => 'resolveNoteTime',
        ];
        foreach ($resolvers as $filename => $resolver) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString('function ' . $resolver . '(progressResponse, fallbackTime)', $source);
            $this->assertStringContainsString('Number.isFinite(savedEnd) && savedEnd > 0', $source);
            $this->assertStringNotContainsString('Number.isFinite(savedEnd) && savedEnd >= 0', $source);
        }
    }

    /**
     * Blocked forward seeks must persist the last legitimate in-memory frontier.
     */
    public function test_blocked_forward_seek_persists_frontier_before_rollback(): void {
        $tracker = file_get_contents(__DIR__ . '/../amd/src/core/tracker/segment.js');
        $facade = file_get_contents(__DIR__ . '/../amd/src/core/tracker.js');
        $this->assertIsString($tracker);
        $this->assertIsString($facade);
        $this->assertStringContainsString(
            'function saveOpenSegmentSnapshot(state, end, saveSegment, reason)',
            $tracker
        );
        $this->assertStringContainsString('saveOpenSegmentSnapshot: saveOpenSegmentSnapshot', $facade);

        foreach (['player.js', 'vimeo_player.js', 'html5_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString(
                "Tracker.saveOpenSegmentSnapshot(state, fallback, saveSegment, 'seek')",
                $source
            );
            $this->assertStringContainsString('persistBlockedSeekFrontier(fallback);', $source);
        }
    }

    /**
     * Forum navigation must flush learner progress before validating its timestamp.
     */
    public function test_forum_timestamp_waits_for_progress_flush(): void {
        $forum = file_get_contents(__DIR__ . '/../amd/src/core/player/forum.js');
        $this->assertIsString($forum);
        $this->assertStringContainsString("options.saveCurrentProgress('interaction')", $forum);
        $this->assertStringContainsString(
            'Number.isFinite(savedEnd) && savedEnd > 0',
            $forum
        );
        $this->assertLessThan(
            strpos($forum, 'window.location.assign'),
            strpos($forum, "options.saveCurrentProgress('interaction')")
        );

        foreach (['player.js', 'vimeo_player.js', 'html5_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString(
                'saveCurrentProgress: config.trackingenabled ? saveCurrentProgress : null',
                $source
            );
        }
    }

    /**
     * Reaction responses must expose structured icon data only.
     */
    public function test_reaction_runtime_contract_contains_no_raw_html_field(): void {
        $external = file_get_contents(__DIR__ . '/../classes/external/save_reaction.php');
        $html5 = file_get_contents(__DIR__ . '/../amd/src/html5_player.js');
        $this->assertIsString($external);
        $this->assertIsString($html5);

        $this->assertStringNotContainsString('iconhtml', $external);
        $this->assertStringNotContainsString('reaction.iconhtml', $html5);
        $this->assertStringNotContainsString('template.innerHTML', $html5);
    }

    /**
     * Interaction writes honour allowed forward seeking without dropping same-session playback validation.
     */
    public function test_interaction_writes_use_policy_aware_timestamp_validation(): void {
        $root = dirname(__DIR__);
        foreach (['save_reaction.php', 'save_note.php', 'save_bookmark.php'] as $file) {
            $source = file_get_contents($root . '/classes/external/' . $file);
            $this->assertIsString($source);
            $this->assertStringContainsString('tracker::interaction_timestamp_allowed(', $source);
        }
    }
}
