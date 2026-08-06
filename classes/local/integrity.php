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

defined('MOODLE_INTERNAL') || die();

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

    /** Minimum random focus-pause delay in seconds (exclusive lower bound requested by the feature). */
    public const RANDOM_PAUSE_MIN_SECONDS = 301;

    /** Maximum random focus-pause delay in seconds (exclusive upper bound requested by the feature). */
    public const RANDOM_PAUSE_MAX_SECONDS = 1799;

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
