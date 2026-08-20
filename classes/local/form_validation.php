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

namespace mod_videotrack\local;

/**
 * Pure validation policy helpers for the activity settings form.
 *
 * File, database and course-context validation remains in mod_form.php.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_validation {
    /**
     * Validate scalar settings that do not depend on files, database state or course context.
     *
     * @param array $data Submitted form data.
     * @param string $suffix Moodle completion-field suffix.
     * @return array Validation errors indexed by form field name.
     */
    public static function scalar_settings_errors(array $data, string $suffix): array {
        $errors = [];

        $completionpercentname = 'completionpercent' . $suffix;
        $completionpercentgroupname = 'completionpercentgroup' . $suffix;
        $completionpercent = $data[$completionpercentname] ?? ($data['completionpercent'] ?? null);
        if (
            $completionpercent !== null
            && ((int)$completionpercent < 0 || (int)$completionpercent > 100)
        ) {
            $errors[$completionpercentgroupname] = get_string('err:completionpercentrange', 'mod_videotrack');
        }

        if (array_key_exists('playerwidth', $data)) {
            $playerwidth = (int)$data['playerwidth'];
            if ($playerwidth < 0 || $playerwidth > 4096) {
                $errors['playerwidth'] = get_string('err:playerwidthrequired', 'mod_videotrack');
            }
        }

        foreach (['rewindstep', 'fastforwardstep'] as $stepfield) {
            if (array_key_exists($stepfield, $data)) {
                $step = (int)$data[$stepfield];
                if ($step < 0 || $step > 300) {
                    $errors[$stepfield] = get_string('err:playbacksteprequired', 'mod_videotrack');
                }
            }
        }

        if (
            !empty($data['reactionsrequired'])
            && empty($data['minreactions'])
            && empty($data['requireallreactiontypes'])
        ) {
            $errors['minreactions'] = get_string('err:minreactionsrequired', 'mod_videotrack');
        }

        if (array_key_exists('reactionpreset_json', $data) && trim((string)$data['reactionpreset_json']) !== '') {
            $presetjson = json_decode((string)$data['reactionpreset_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($presetjson)) {
                $errors['reactionpreset'] = get_string('err:reactionpresetjson', 'mod_videotrack');
            }
        }

        return $errors;
    }

    /**
     * Validate acknowledgement timing and statement text.
     *
     * @param array $data Submitted form data.
     * @return array Validation errors indexed by form field name.
     */
    public static function acknowledgement_errors(array $data): array {
        $errors = [];
        if (empty($data['acknowledgementenabled'])) {
            return $errors;
        }

        $rawtiming = $data['acknowledgementtiming'] ?? acknowledgement::TIMING_ANYTIME;
        $timing = (int)$rawtiming;
        if (
            !in_array($timing, [
                acknowledgement::TIMING_ANYTIME,
                acknowledgement::TIMING_VIDEO_END,
            ], true)
        ) {
            $errors['acknowledgementtiming'] = get_string(
                'acknowledgement:errortiming',
                'mod_videotrack'
            );
        }

        $acknowledgementeditor = $data['acknowledgement_editor'] ?? [];
        $acknowledgementtext = is_array($acknowledgementeditor)
            ? (string)($acknowledgementeditor['text'] ?? '')
            : '';
        if (!acknowledgement::has_visible_text($acknowledgementtext)) {
            $errors['acknowledgement_editor'] = get_string(
                'acknowledgement:errorstatementrequired',
                'mod_videotrack'
            );
        }

        return $errors;
    }

    /**
     * Determine whether at least one custom completion rule is enabled.
     *
     * @param array $data Submitted form data.
     * @param string $suffix Moodle completion-field suffix.
     * @return bool True when at least one custom completion condition is active.
     */
    public static function completion_rule_enabled(array $data, string $suffix): bool {
        $completionpercent = $data['completionpercent' . $suffix]
            ?? ($data['completionpercent'] ?? 0);
        $completionacknowledgement = $data['completionacknowledgement' . $suffix]
            ?? ($data['completionacknowledgement'] ?? 0);
        $requiredreactions = array_filter(array_map('intval', (array)($data['reactionrequired'] ?? [])));
        $reactionrules = !empty($data['reactionsenabled']) && (
            (!empty($data['reactionsrequired']) && !empty($data['minreactions']))
            || !empty($data['requireallreactiontypes'])
            || !empty($requiredreactions)
        );

        return (!empty($data['durationseconds'])
                && !empty($completionpercent)
                && (int)$completionpercent > 0)
            || $reactionrules
            || (!empty($completionacknowledgement) && !empty($data['acknowledgementenabled']));
    }

    /**
     * Validate teacher-authoritative duration and the video-end acknowledgement dependency.
     *
     * @param array $data Submitted form data.
     * @return array Validation errors indexed by form field name.
     */
    public static function duration_errors(array $data): array {
        $errors = [];
        $durationseconds = isset($data['durationseconds']) ? (float)$data['durationseconds'] : 0.0;
        if (!is_finite($durationseconds) || $durationseconds < 0 || $durationseconds > 86400) {
            $errors['durationseconds'] = get_string('durationseconds_invalid', 'mod_videotrack');
        }

        $requiresduration = !empty($data['acknowledgementenabled'])
            && (int)($data['acknowledgementtiming'] ?? 0) === acknowledgement::TIMING_VIDEO_END;
        if ($requiresduration && $durationseconds <= 0) {
            $errors['durationseconds'] = get_string('durationseconds_required', 'mod_videotrack');
        }

        return $errors;
    }
}
