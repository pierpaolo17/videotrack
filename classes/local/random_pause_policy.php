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
 * Resolve bounded attention-pause intervals without changing playback credit.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class random_pause_policy {
    /**
     * Parse an optional whole-second override; zero means automatic.
     *
     * @param mixed $value Submitted or restored scalar value.
     * @return int|null Zero or a safe override, null for invalid input.
     */
    public static function parse_override($value): ?int {
        if (!is_int($value) && !is_string($value) && $value !== null) {
            return null;
        }
        $text = trim((string)$value);
        if ($text === '') {
            return 0;
        }
        if (!ctype_digit($text)) {
            return null;
        }
        $seconds = (int)$text;
        return $seconds === 0 || ($seconds >= integrity::RANDOM_PAUSE_ALLOWED_MIN_SECONDS
            && $seconds <= integrity::RANDOM_PAUSE_ALLOWED_MAX_SECONDS) ? $seconds : null;
    }

    /**
     * Normalise stored overrides without materialising automatic values.
     *
     * Invalid legacy/imported input falls back to automatic. Reversed explicit
     * values are safely ordered; the activity form rejects that pair first.
     *
     * @param \stdClass $activity Activity data updated in place.
     */
    public static function normalise_overrides(\stdClass $activity): void {
        $minimum = self::parse_override($activity->randompauseminseconds ?? null) ?? 0;
        $maximum = self::parse_override($activity->randompausemaxseconds ?? null) ?? 0;
        if ($minimum > 0 && $maximum > 0 && $minimum > $maximum) {
            [$minimum, $maximum] = [$maximum, $minimum];
        }
        $activity->randompauseminseconds = $minimum;
        $activity->randompausemaxseconds = $maximum;
    }

    /**
     * Resolve teacher overrides over duration-derived or site fallback bounds.
     *
     * One explicit bound takes precedence: the automatic counterpart moves
     * only when needed to keep the interval ordered. These are wall seconds
     * since a recognised interaction, not video timeline positions.
     *
     * @param \stdClass $activity Saved teacher-controlled activity configuration.
     * @return array Inclusive min/max in seconds.
     */
    public static function activity_bounds(\stdClass $activity): array {
        $bounds = self::automatic_bounds((float)($activity->durationseconds ?? 0));
        $minimum = self::parse_override($activity->randompauseminseconds ?? null) ?? 0;
        $maximum = self::parse_override($activity->randompausemaxseconds ?? null) ?? 0;
        if ($minimum > 0) {
            $bounds['min'] = $minimum;
            $bounds['max'] = max($bounds['max'], $minimum);
        }
        if ($maximum > 0) {
            $bounds['max'] = $maximum;
            if ($minimum === 0) {
                $bounds['min'] = min($bounds['min'], $maximum);
            }
        }
        return integrity::normalise_random_pause_bounds($bounds['min'], $bounds['max']);
    }

    /**
     * Calculate rounded thirds within operational limits or use the site fallback.
     *
     * @param float $duration Saved activity duration; provider callbacks never supply it.
     * @return array Inclusive bounded interval in seconds.
     */
    private static function automatic_bounds(float $duration): array {
        if (!is_finite($duration) || $duration <= 0) {
            return integrity::random_pause_bounds();
        }
        return integrity::normalise_random_pause_bounds(
            (int)round(min(86400.0, $duration) / 3),
            (int)round(min(86400.0, $duration) * 2 / 3)
        );
    }
    /**
     * Validate independently optional attention-pause bounds before integer cleaning.
     *
     * @param array $data Submitted activity data.
     * @return array Field-specific errors; only two explicit bounds require ordering.
     */
    public static function form_errors(array $data): array {
        $values = [];
        $errors = [];
        foreach (['randompauseminseconds', 'randompausemaxseconds'] as $field) {
            $values[$field] = self::parse_override($data[$field] ?? null);
            if ($values[$field] === null) {
                $errors[$field] = get_string('integrity:randompauseinvalid', 'mod_videotrack');
            }
        }
        if ($errors) {
            return $errors;
        }
        $minimum = $values['randompauseminseconds'];
        $maximum = $values['randompausemaxseconds'];
        if ($minimum > 0 && $maximum > 0 && $minimum > $maximum) {
            $errors['randompausemaxseconds'] = get_string('integrity:randompauseorder', 'mod_videotrack');
        }
        return $errors;
    }
}
