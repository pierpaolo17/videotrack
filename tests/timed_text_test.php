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
use mod_videotrack\local\timed_text;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for teacher-provided timed-text helpers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(timed_text::class)]
final class timed_text_test extends advanced_testcase {
    /**
     * Language codes are derived from conventional WebVTT filenames.
     */
    public function test_language_from_filename_accepts_bcp47_like_names(): void {
        $this->assertSame('it', timed_text::language_from_filename('it.vtt'));
        $this->assertSame('pt-br', timed_text::language_from_filename('pt-BR.vtt'));
        $this->assertSame('en', timed_text::language_from_filename('transcript.vtt', 'en'));
        $this->assertSame('', timed_text::language_from_filename('transcript.vtt'));
    }

    /**
     * WebVTT validation accepts a BOM and rejects malformed or oversized files.
     */
    public function test_is_valid_vtt_content_checks_signature_and_size(): void {
        $this->assertTrue(timed_text::is_valid_vtt_content("WEBVTT\n\n00:00.000 --> 00:01.000\nText"));
        $this->assertTrue(timed_text::is_valid_vtt_content("\xEF\xBB\xBF WEBVTT\n"));
        $this->assertFalse(timed_text::is_valid_vtt_content('not vtt'));
        $this->assertFalse(timed_text::is_valid_vtt_content(str_repeat('x', timed_text::MAX_FILE_SIZE + 1)));
    }

    /**
     * File options enforce the project limits and WebVTT extension.
     */
    public function test_file_options_enforce_vtt_limits(): void {
        $options = timed_text::file_options(timed_text::MAX_TRANSCRIPT_FILES);

        $this->assertFalse($options['subdirs']);
        $this->assertSame(timed_text::MAX_TRANSCRIPT_FILES, $options['maxfiles']);
        $this->assertSame(timed_text::MAX_FILE_SIZE, $options['maxbytes']);
        $this->assertSame(['.vtt'], $options['accepted_types']);
    }

    /**
     * Canonical timed-text lookups do not read the legacy subtitle area.
     */
    public function test_canonical_lookups_ignore_legacy_subtitle_file(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $context = \context_module::instance((int)$activity->cmid);
        get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_videotrack',
            'filearea' => 'subtitles',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'it.vtt',
        ], "WEBVTT\n\n00:00.000 --> 00:01.000\nLegacy");

        $this->assertSame([], timed_text::transcript_tracks((int)$activity->cmid, 'it'));
        $this->assertNull(timed_text::chapter_source((int)$activity->cmid));
    }

    /**
     * Explicit fallback lookups expose the legacy subtitle file when canonical areas are empty.
     */
    public function test_explicit_legacy_fallback_uses_subtitle_file(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $context = \context_module::instance((int)$activity->cmid);
        get_file_storage()->create_file_from_string([
            'contextid' => $context->id,
            'component' => 'mod_videotrack',
            'filearea' => 'subtitles',
            'itemid' => 0,
            'filepath' => '/',
            'filename' => 'it.vtt',
        ], "WEBVTT\n\n00:00.000 --> 00:01.000\nLegacy");

        $tracks = timed_text::transcript_tracks_with_legacy_fallback((int)$activity->cmid, 'en');
        $this->assertCount(1, $tracks);
        $this->assertSame('it', $tracks[0]['language']);
        $this->assertTrue($tracks[0]['legacy']);
        $this->assertStringContainsString('/subtitles/0/it.vtt', $tracks[0]['url']);

        $chapter = timed_text::chapter_source_with_legacy_fallback((int)$activity->cmid);
        $this->assertNotNull($chapter);
        $this->assertTrue($chapter['legacy']);
        $this->assertStringContainsString('/subtitles/0/it.vtt', $chapter['url']);
    }

    /**
     * Explicit fallback lookups prefer dedicated transcript and chapter files.
     */
    public function test_explicit_legacy_fallback_prefers_canonical_files(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $activity = $this->getDataGenerator()->create_module('videotrack', ['course' => $course->id]);
        $context = \context_module::instance((int)$activity->cmid);
        foreach (
            [
                'subtitles' => 'legacy.vtt',
                'transcripts' => 'en.vtt',
                'chapters' => 'chapters.vtt',
            ] as $filearea => $filename
        ) {
            get_file_storage()->create_file_from_string([
                'contextid' => $context->id,
                'component' => 'mod_videotrack',
                'filearea' => $filearea,
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $filename,
            ], "WEBVTT\n\n00:00.000 --> 00:01.000\nCanonical");
        }

        $tracks = timed_text::transcript_tracks_with_legacy_fallback((int)$activity->cmid, 'it');
        $this->assertCount(1, $tracks);
        $this->assertSame('en', $tracks[0]['language']);
        $this->assertFalse($tracks[0]['legacy']);
        $this->assertStringContainsString('/transcripts/0/en.vtt', $tracks[0]['url']);

        $chapter = timed_text::chapter_source_with_legacy_fallback((int)$activity->cmid);
        $this->assertNotNull($chapter);
        $this->assertFalse($chapter['legacy']);
        $this->assertStringContainsString('/chapters/0/chapters.vtt', $chapter['url']);
    }
}
