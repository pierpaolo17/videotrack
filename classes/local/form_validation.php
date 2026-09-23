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
        $errors = self::completion_percent_errors($data, $suffix);
        $errors += self::bounded_integer_errors(
            $data,
            'playerwidth',
            0,
            4096,
            'err:playerwidthrequired'
        );

        foreach (['rewindstep', 'fastforwardstep'] as $stepfield) {
            $errors += self::bounded_integer_errors(
                $data,
                $stepfield,
                0,
                300,
                'err:playbacksteprequired'
            );
        }

        $errors += self::reaction_requirement_errors($data);
        $errors += self::reaction_preset_errors($data);

        return $errors;
    }

    /**
     * Validate the custom-completion percentage field.
     *
     * @param array $data Submitted form data.
     * @param string $suffix Moodle completion-field suffix.
     * @return array Empty array or the percentage-group validation error.
     */
    private static function completion_percent_errors(array $data, string $suffix): array {
        $fieldname = 'completionpercent' . $suffix;
        $value = $data[$fieldname] ?? ($data['completionpercent'] ?? null);
        if ($value === null || ((int)$value >= 0 && (int)$value <= 100)) {
            return [];
        }

        return [
            'completionpercentgroup' . $suffix => get_string('err:completionpercentrange', 'mod_videotrack'),
        ];
    }

    /**
     * Validate one optional bounded integer setting.
     *
     * @param array $data Submitted form data.
     * @param string $fieldname Form field name.
     * @param int $minimum Inclusive minimum value.
     * @param int $maximum Inclusive maximum value.
     * @param string $errorstring Language-string identifier used for invalid values.
     * @return array Empty array or the field validation error.
     */
    private static function bounded_integer_errors(
        array $data,
        string $fieldname,
        int $minimum,
        int $maximum,
        string $errorstring
    ): array {
        if (!array_key_exists($fieldname, $data)) {
            return [];
        }

        $value = (int)$data[$fieldname];
        if ($value >= $minimum && $value <= $maximum) {
            return [];
        }

        return [$fieldname => get_string($errorstring, 'mod_videotrack')];
    }

    /**
     * Validate the dependency between minimum and all-type reaction rules.
     *
     * @param array $data Submitted form data.
     * @return array Empty array or the minimum-reactions validation error.
     */
    private static function reaction_requirement_errors(array $data): array {
        if (
            empty($data['reactionsrequired'])
            || !empty($data['minreactions'])
            || !empty($data['requireallreactiontypes'])
        ) {
            return [];
        }

        return ['minreactions' => get_string('err:minreactionsrequired', 'mod_videotrack')];
    }

    /**
     * Validate optional reaction-preset JSON.
     *
     * @param array $data Submitted form data.
     * @return array Empty array or the reaction-preset validation error.
     */
    private static function reaction_preset_errors(array $data): array {
        if (!array_key_exists('reactionpreset_json', $data)) {
            return [];
        }

        $json = trim((string)$data['reactionpreset_json']);
        if ($json === '') {
            return [];
        }

        $preset = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($preset)) {
            return [];
        }

        return ['reactionpreset' => get_string('err:reactionpresetjson', 'mod_videotrack')];
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
        return self::percentage_completion_rule_enabled($data, $suffix)
            || self::reaction_completion_rule_enabled($data)
            || self::acknowledgement_completion_rule_enabled($data, $suffix);
    }

    /**
     * Determine whether percentage completion is active.
     *
     * @param array $data Submitted form data.
     * @param string $suffix Moodle completion-field suffix.
     * @return bool True when duration and a positive percentage are configured.
     */
    private static function percentage_completion_rule_enabled(array $data, string $suffix): bool {
        $completionpercent = $data['completionpercent' . $suffix]
            ?? ($data['completionpercent'] ?? 0);

        return !empty($data['durationseconds'])
            && !empty($completionpercent)
            && (int)$completionpercent > 0;
    }

    /**
     * Determine whether any reaction completion rule is active.
     *
     * @param array $data Submitted form data.
     * @return bool True when reactions and at least one reaction rule are enabled.
     */
    private static function reaction_completion_rule_enabled(array $data): bool {
        if (empty($data['reactionsenabled'])) {
            return false;
        }

        $requiredreactions = array_filter(array_map('intval', (array)($data['reactionrequired'] ?? [])));
        return (!empty($data['reactionsrequired']) && !empty($data['minreactions']))
            || !empty($data['requireallreactiontypes'])
            || !empty($requiredreactions);
    }

    /**
     * Determine whether acknowledgement completion is active.
     *
     * @param array $data Submitted form data.
     * @param string $suffix Moodle completion-field suffix.
     * @return bool True when acknowledgement and its completion rule are enabled.
     */
    private static function acknowledgement_completion_rule_enabled(array $data, string $suffix): bool {
        $completionacknowledgement = $data['completionacknowledgement' . $suffix]
            ?? ($data['completionacknowledgement'] ?? 0);

        return !empty($completionacknowledgement) && !empty($data['acknowledgementenabled']);
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
