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

namespace mod_videotrack\local;

use context_module;
use core_text;
use moodle_url;
use stdClass;

/**
 * Manages teacher-provided WebVTT transcript and chapter files.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class timed_text {
    /** Maximum number of transcript languages stored for one activity. */
    public const MAX_TRANSCRIPT_FILES = 10;

    /** Maximum accepted size of one WebVTT file. */
    public const MAX_FILE_SIZE = 1048576;

    /**
     * Return Moodle draft-area options for WebVTT files.
     *
     * @param int $maxfiles Maximum number of files.
     * @return array Draft-area options.
     */
    public static function file_options(int $maxfiles): array {
        return [
            'subdirs' => false,
            'maxfiles' => max(1, $maxfiles),
            'maxbytes' => self::MAX_FILE_SIZE,
            'accepted_types' => ['.vtt'],
        ];
    }

    /**
     * Save transcript and chapter draft areas for an activity.
     *
     * @param stdClass $data Submitted activity data.
     * @return void
     */
    public static function save_files(stdClass $data): void {
        if (empty($data->coursemodule)) {
            unset($data->transcriptfiles, $data->chapterfile);
            return;
        }

        $context = context_module::instance((int)$data->coursemodule);
        $areas = [
            'transcriptfiles' => ['transcripts', self::MAX_TRANSCRIPT_FILES],
            'chapterfile' => ['chapters', 1],
        ];
        foreach ($areas as $field => [$filearea, $maxfiles]) {
            $draftitemid = (int)($data->{$field} ?? 0);
            if ($draftitemid > 0) {
                file_save_draft_area_files(
                    $draftitemid,
                    $context->id,
                    'mod_videotrack',
                    $filearea,
                    0,
                    self::file_options($maxfiles)
                );
            }
            unset($data->{$field});
        }
    }

    /**
     * Return transcript tracks, falling back to the legacy subtitle file.
     *
     * Transcript file names may be language codes such as en.vtt or pt-BR.vtt.
     * Files with other names remain usable and are labelled with their base name.
     *
     * @param int $cmid Course-module id.
     * @param string $fallbacklanguage Default language code.
     * @param bool $allowlegacy Whether the legacy subtitles area may be used.
     * @return array Track descriptors for JavaScript.
     */
    public static function transcript_tracks(
        int $cmid,
        string $fallbacklanguage = '',
        bool $allowlegacy = false
    ): array {
        $context = context_module::instance($cmid);
        $files = self::area_files($context->id, 'transcripts');
        $legacy = false;
        if (!$files && $allowlegacy) {
            $files = self::area_files($context->id, 'subtitles');
            $legacy = true;
        }

        $tracks = [];
        foreach ($files as $file) {
            $language = self::language_from_filename(
                $file->get_filename(),
                count($files) === 1 ? $fallbacklanguage : ''
            );
            $tracks[] = [
                'url' => (string)self::file_url($context->id, $file->get_filearea(), $file),
                'language' => $language,
                'label' => self::language_label($language, $file->get_filename()),
                'legacy' => $legacy,
            ];
        }
        return $tracks;
    }

    /**
     * Return the dedicated chapter source or a legacy subtitle fallback.
     *
     * @param int $cmid Course-module id.
     * @param bool $allowlegacy Whether the old subtitle-based chapter source may be used.
     * @return array|null Source descriptor with url and legacy keys.
     */
    public static function chapter_source(int $cmid, bool $allowlegacy = false): ?array {
        $context = context_module::instance($cmid);
        $files = self::area_files($context->id, 'chapters');
        $legacy = false;
        if (!$files && $allowlegacy) {
            $files = self::area_files($context->id, 'subtitles');
            $legacy = true;
        }
        if (!$files) {
            return null;
        }
        $file = reset($files);
        return [
            'url' => (string)self::file_url($context->id, $file->get_filearea(), $file),
            'legacy' => $legacy,
        ];
    }

    /**
     * Extract a BCP-47-like language code from a WebVTT filename.
     *
     * @param string $filename File name.
     * @param string $fallback Fallback language code.
     * @return string Normalised language code or an empty string.
     */
    public static function language_from_filename(string $filename, string $fallback = ''): string {
        $stem = pathinfo($filename, PATHINFO_FILENAME);
        if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i', $stem)) {
            return core_text::strtolower($stem);
        }
        $fallback = trim($fallback);
        if (preg_match('/^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/i', $fallback)) {
            return core_text::strtolower($fallback);
        }
        return '';
    }

    /**
     * Validate the beginning and size of a WebVTT payload.
     *
     * @param string $content File content.
     * @return bool True for an acceptable WebVTT payload.
     */
    public static function is_valid_vtt_content(string $content): bool {
        if ($content === '' || strlen($content) > self::MAX_FILE_SIZE) {
            return false;
        }
        $prefix = ltrim(substr($content, 0, 128), "\xEF\xBB\xBF\t\n\r ");
        return strncmp($prefix, 'WEBVTT', 6) === 0;
    }

    /**
     * Return non-directory files from one activity file area.
     *
     * @param int $contextid Context id.
     * @param string $filearea File area.
     * @return array Stored files.
     */
    private static function area_files(int $contextid, string $filearea): array {
        $files = get_file_storage()->get_area_files(
            $contextid,
            'mod_videotrack',
            $filearea,
            0,
            'filename ASC, id ASC',
            false
        );
        return array_values($files);
    }

    /**
     * Build a pluginfile URL for a stored timed-text file.
     *
     * @param int $contextid Context id.
     * @param string $filearea File area.
     * @param \stored_file $file Stored file.
     * @return moodle_url File URL.
     */
    private static function file_url(int $contextid, string $filearea, \stored_file $file): moodle_url {
        return moodle_url::make_pluginfile_url(
            $contextid,
            'mod_videotrack',
            $filearea,
            0,
            $file->get_filepath(),
            $file->get_filename()
        );
    }

    /**
     * Return a readable language or file label.
     *
     * @param string $language Language code.
     * @param string $filename File name.
     * @return string Label for the transcript selector.
     */
    private static function language_label(string $language, string $filename): string {
        if ($language !== '') {
            $translations = get_string_manager()->get_list_of_translations();
            if (isset($translations[$language])) {
                return (string)$translations[$language];
            }
            $base = strtok($language, '-');
            if ($base !== false && isset($translations[$base])) {
                return (string)$translations[$base] . ' (' . $language . ')';
            }
            return core_text::strtoupper($language);
        }
        return clean_param(pathinfo($filename, PATHINFO_FILENAME), PARAM_FILE);
    }
}
