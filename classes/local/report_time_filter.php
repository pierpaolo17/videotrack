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

use html_writer;
use invalid_parameter_exception;

/**
 * Request parsing and accessible controls for teacher-report time filters.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_time_filter {
    /**
     * Converts an ISO date-only parameter to the start of that day in the user's timezone.
     *
     * @param string $date Date in YYYY-MM-DD format.
     * @return int Timestamp, or 0 when the value is empty or invalid.
     */
    public static function date_to_timestamp(string $date): int {
        if ($date === '' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return 0;
        }
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        if (!checkdate($month, $day, $year)) {
            return 0;
        }
        return make_timestamp($year, $month, $day, 0, 0, 0);
    }

    /**
     * Converts an ISO date-only parameter to the end of that day in the user's timezone.
     *
     * @param string $date Date in YYYY-MM-DD format.
     * @return int Timestamp, or 0 when the value is empty or invalid.
     */
    public static function end_date_to_timestamp(string $date): int {
        if ($date === '' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $matches)) {
            return 0;
        }
        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        if (!checkdate($month, $day, $year)) {
            return 0;
        }
        return make_timestamp($year, $month, $day, 23, 59, 59);
    }

    /**
     * Reads an optional video-time filter.
     *
     * The report form submits numeric hour/minute/second controls. Legacy MM:SS and
     * HH:MM:SS links remain supported for backwards compatibility.
     *
     * @param string $name Parameter name.
     * @return float|null Non-negative float value, or null when unset/empty.
     */
    public static function optional_time_param(string $name): ?float {
        $rawparts = self::request_components($name);
        if (self::has_submitted_components($rawparts)) {
            return self::structured_time($rawparts);
        }
        return self::legacy_time($name);
    }

    /**
     * Renders a structured duration filter using number inputs.
     *
     * @param string $name Base parameter name.
     * @param string $label Visible field label.
     * @param float|null $value Current value in seconds.
     * @param bool $showhours Whether to render the hours control.
     * @return string HTML fragment.
     */
    public static function duration_filter(string $name, string $label, ?float $value, bool $showhours): string {
        $totalseconds = $value === null ? null : max(0, (int)round($value));
        $hours = $totalseconds === null ? '' : (string)floor($totalseconds / HOURSECS);
        $minutes = $totalseconds === null ? '' : (string)floor(($totalseconds % HOURSECS) / MINSECS);
        $seconds = $totalseconds === null ? '' : (string)($totalseconds % MINSECS);
        if (!$showhours && $totalseconds !== null) {
            $minutes = (string)floor($totalseconds / MINSECS);
        }

        $groupid = 'id_' . $name . '_group';
        $html = html_writer::span($label, 'mr-1', ['id' => $groupid . '_label']);
        $attributes = [
            'type' => 'number',
            'min' => 0,
            'step' => 1,
            'inputmode' => 'numeric',
            'autocomplete' => 'off',
            'class' => 'form-control form-control-sm videotrack-time-part',
            'style' => 'width:4.5rem',
        ];
        if ($showhours) {
            $html .= html_writer::empty_tag('input', array_merge($attributes, [
                'name' => $name . '_hours',
                'id' => 'id_' . $name . '_hours',
                'value' => $hours,
                'aria-label' => get_string('hours'),
            ]));
            $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
        }
        $html .= html_writer::empty_tag('input', array_merge($attributes, [
            'name' => $name . '_minutes',
            'id' => 'id_' . $name . '_minutes',
            'value' => $minutes,
            'max' => 59,
            'aria-label' => get_string('minutes'),
        ]));
        $html .= html_writer::span(':', 'mx-1', ['aria-hidden' => 'true']);
        $html .= html_writer::empty_tag('input', array_merge($attributes, [
            'name' => $name . '_seconds',
            'id' => 'id_' . $name . '_seconds',
            'value' => $seconds,
            'max' => 59,
            'aria-label' => get_string('seconds'),
        ]));
        return html_writer::div($html, 'd-inline-flex align-items-center mr-3 mb-2', [
            'id' => $groupid,
            'role' => 'group',
            'aria-labelledby' => $groupid . '_label',
        ]);
    }

    /**
     * Reads the structured duration components from the request.
     *
     * @param string $name Base parameter name.
     * @return array Raw component values.
     */
    private static function request_components(string $name): array {
        return [
            'hours' => optional_param($name . '_hours', null, PARAM_RAW_TRIMMED),
            'minutes' => optional_param($name . '_minutes', null, PARAM_RAW_TRIMMED),
            'seconds' => optional_param($name . '_seconds', null, PARAM_RAW_TRIMMED),
        ];
    }

    /**
     * Checks whether any structured component was submitted.
     *
     * @param array $rawparts Raw component values.
     * @return bool Whether structured mode was submitted.
     */
    private static function has_submitted_components(array $rawparts): bool {
        return count(array_filter($rawparts, static fn($value): bool => $value !== null)) > 0;
    }

    /**
     * Parses submitted structured duration components.
     *
     * @param array $rawparts Raw component values.
     * @return float|null Duration in seconds, or null when all fields are empty.
     */
    private static function structured_time(array $rawparts): ?float {
        if (!self::has_component_value($rawparts)) {
            return null;
        }
        self::validate_component_digits($rawparts);
        $hours = self::component_integer($rawparts['hours']);
        $minutes = self::component_integer($rawparts['minutes']);
        $seconds = self::component_integer($rawparts['seconds']);
        self::validate_minute_second_range($minutes, $seconds);
        return (float)(($hours * HOURSECS) + ($minutes * MINSECS) + $seconds);
    }

    /**
     * Checks whether at least one structured field contains a value.
     *
     * @param array $rawparts Raw component values.
     * @return bool Whether one component is non-empty.
     */
    private static function has_component_value(array $rawparts): bool {
        return count(array_filter(
            $rawparts,
            static fn($value): bool => $value !== null && $value !== ''
        )) > 0;
    }

    /**
     * Rejects non-integer structured component values.
     *
     * @param array $rawparts Raw component values.
     * @return void
     */
    private static function validate_component_digits(array $rawparts): void {
        foreach ($rawparts as $rawpart) {
            if ($rawpart !== '' && $rawpart !== null && !preg_match('/^\d+$/', $rawpart)) {
                self::throw_invalid_time();
            }
        }
    }

    /**
     * Normalises one empty or numeric component.
     *
     * @param mixed $rawpart Raw component value.
     * @return int Non-negative integer component.
     */
    private static function component_integer($rawpart): int {
        return ($rawpart === '' || $rawpart === null) ? 0 : (int)$rawpart;
    }

    /**
     * Enforces clock bounds for minute and second components.
     *
     * @param int $minutes Minute component.
     * @param int $seconds Second component.
     * @return void
     */
    private static function validate_minute_second_range(int $minutes, int $seconds): void {
        if ($minutes > 59 || $seconds > 59) {
            self::throw_invalid_time();
        }
    }

    /**
     * Reads and validates the backwards-compatible colon-form parameter.
     *
     * @param string $name Parameter name.
     * @return float|null Parsed duration.
     */
    private static function legacy_time(string $name): ?float {
        $rawvalue = optional_param($name, '', PARAM_RAW_TRIMMED);
        $parsed = videotrack_parse_report_timestamp($rawvalue);
        if ($rawvalue !== '' && $parsed === null) {
            self::throw_invalid_time();
        }
        return $parsed;
    }

    /**
     * Throws the canonical report-time validation exception.
     *
     * @return never
     */
    private static function throw_invalid_time(): never {
        throw new invalid_parameter_exception(get_string('report:timeformatplaceholder', 'mod_videotrack'));
    }
}
