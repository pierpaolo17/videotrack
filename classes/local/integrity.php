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

/**
 * Integrity-indicator definitions and privacy-safe aggregation helpers.
 *
 * The recorded signals are diagnostic indicators. They are not proof of
 * misconduct and must not be used as an automatic disciplinary decision.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class integrity {
    /** Supported diagnostic signal types. */
    public const EVENT_TYPES = [
        'forwardseek',
        'tabhidden',
        'windowblur',
        'outofviewport',
        'pipattempt',
        'randompause',
        'ratechange',
        'callbackmissing',
        'trackinggap',
    ];

    /** Default minimum random focus-pause delay in seconds. */
    public const RANDOM_PAUSE_DEFAULT_MIN_SECONDS = 300;

    /** Default maximum random focus-pause delay in seconds. */
    public const RANDOM_PAUSE_DEFAULT_MAX_SECONDS = 1800;

    /** Absolute minimum accepted by the site setting. */
    public const RANDOM_PAUSE_ALLOWED_MIN_SECONDS = 60;

    /** Absolute maximum accepted by the site setting. */
    public const RANDOM_PAUSE_ALLOWED_MAX_SECONDS = 7200;

    /** Default grace period before strict window-focus handling is applied. */
    public const FOCUS_LOSS_DEFAULT_GRACE_SECONDS = 5;

    /** Maximum configurable focus-loss grace period. */
    public const FOCUS_LOSS_MAX_GRACE_SECONDS = 30;

    /** Accessibility-oriented focus policy. */
    public const FOCUS_POLICY_HIDDEN_ONLY = 'hiddenonly';

    /** Strict focus policy that may pause after a window blur. */
    public const FOCUS_POLICY_STRICT = 'strict';

    /**
     * Normalise the site-level random attention-pause bounds.
     *
     * Values are clamped to safe operational limits. When an administrator
     * accidentally stores the values in reverse order, they are reordered so
     * playback never receives an invalid interval.
     *
     * @param int|null $minimum Configured minimum in seconds.
     * @param int|null $maximum Configured maximum in seconds.
     * @return array{min:int,max:int} Normalised inclusive bounds.
     */
    public static function normalise_random_pause_bounds(?int $minimum, ?int $maximum): array {
        $minimum = $minimum ?? self::RANDOM_PAUSE_DEFAULT_MIN_SECONDS;
        $maximum = $maximum ?? self::RANDOM_PAUSE_DEFAULT_MAX_SECONDS;
        $minimum = min(
            self::RANDOM_PAUSE_ALLOWED_MAX_SECONDS,
            max(self::RANDOM_PAUSE_ALLOWED_MIN_SECONDS, $minimum)
        );
        $maximum = min(
            self::RANDOM_PAUSE_ALLOWED_MAX_SECONDS,
            max(self::RANDOM_PAUSE_ALLOWED_MIN_SECONDS, $maximum)
        );
        if ($minimum > $maximum) {
            [$minimum, $maximum] = [$maximum, $minimum];
        }
        return ['min' => $minimum, 'max' => $maximum];
    }

    /**
     * Return the effective site-level random attention-pause bounds.
     *
     * @return array{min:int,max:int} Inclusive bounds in seconds.
     */
    public static function random_pause_bounds(): array {
        $minimum = get_config('mod_videotrack', 'randompauseminseconds');
        $maximum = get_config('mod_videotrack', 'randompausemaxseconds');
        return self::normalise_random_pause_bounds(
            $minimum === false ? null : (int)$minimum,
            $maximum === false ? null : (int)$maximum
        );
    }

    /**
     * Return the effective site-level focus-loss policy.
     *
     * @return string One of the FOCUS_POLICY_* constants.
     */
    public static function focus_loss_policy(): string {
        $policy = (string)get_config('mod_videotrack', 'focuslosspolicy');
        return $policy === self::FOCUS_POLICY_STRICT
            ? self::FOCUS_POLICY_STRICT
            : self::FOCUS_POLICY_HIDDEN_ONLY;
    }

    /**
     * Return the site-level grace period for strict window-focus handling.
     *
     * @return int Grace period in seconds.
     */
    public static function focus_loss_grace_seconds(): int {
        $configured = get_config('mod_videotrack', 'focuslossgraceseconds');
        $seconds = $configured === false ? self::FOCUS_LOSS_DEFAULT_GRACE_SECONDS : (int)$configured;
        return min(self::FOCUS_LOSS_MAX_GRACE_SECONDS, max(0, $seconds));
    }

    /**
     * Validate a client-supplied signal type.
     *
     * @param string $eventtype Candidate event type.
     * @return string Validated event type.
     * @throws \invalid_parameter_exception
     */
    public static function validate_event_type(string $eventtype): string {
        if (!in_array($eventtype, self::EVENT_TYPES, true)) {
            throw new \invalid_parameter_exception('Invalid integrity event type');
        }
        return $eventtype;
    }

    /**
     * Return the language string identifier for one signal type.
     *
     * @param string $eventtype Signal type.
     * @return string Language string identifier.
     */
    public static function label_string(string $eventtype): string {
        self::validate_event_type($eventtype);
        return 'integrity:event:' . $eventtype;
    }

    /**
     * Build a privacy-safe summary from raw grouped rows.
     *
     * @param array $rows Rows containing eventtype, eventcount and studentcount.
     * @param int $minusers Minimum distinct users required for exact values.
     * @return array<string, array{eventcount:int|null,studentcount:int|null,suppressed:bool,hasdata:bool}>
     */
    public static function summarise(array $rows, int $minusers): array {
        $summary = [];
        foreach (self::EVENT_TYPES as $eventtype) {
            $summary[$eventtype] = [
                'eventcount' => 0,
                'studentcount' => 0,
                'suppressed' => false,
                'hasdata' => false,
            ];
        }

        foreach ($rows as $row) {
            $eventtype = (string)($row->eventtype ?? '');
            if (!isset($summary[$eventtype])) {
                continue;
            }
            $eventcount = max(0, (int)($row->eventcount ?? 0));
            $studentcount = max(0, (int)($row->studentcount ?? 0));
            $suppressed = $eventcount > 0 && $studentcount < $minusers;
            $summary[$eventtype] = [
                'eventcount' => $suppressed ? null : $eventcount,
                'studentcount' => $suppressed ? null : $studentcount,
                'suppressed' => $suppressed,
                'hasdata' => $eventcount > 0,
            ];
        }

        return $summary;
    }
}
