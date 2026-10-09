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

use html_writer;

/**
 * Privacy-safe reaction, bookmark and acknowledgement summaries.
 *
 * Presentation-only services keep the request controller auditable without
 * changing Analytics queries, privacy thresholds or rendered contracts.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_summary_view {
    /**
     * Renders a privacy-safe overall reaction summary.
     *
     * @param array $summary Event and distinct-student counts plus suppression state.
     * @return string Plain summary, or an empty string when values are unavailable.
     */
    public static function reaction_summary(array $summary): string {
        if (empty($summary['hasdata']) || !empty($summary['suppressed'])) {
            return '';
        }
        $eventcount = (int)($summary['eventcount'] ?? 0);
        if ($eventcount <= 0) {
            return '';
        }

        $events = get_string('report:analytics_reactions_detected', 'mod_videotrack') . ' ' .
            html_writer::tag('strong', (string)$eventcount);
        $students = get_string('report:analytics_students_involved', 'mod_videotrack') . ' ' .
            html_writer::tag('strong', (string)(int)($summary['studentcount'] ?? 0));
        return html_writer::div($events . html_writer::empty_tag('br') . $students, 'mb-3');
    }

    /**
     * Renders a privacy-safe bookmark usage summary without exposing labels or timestamps.
     *
     * @param array $summary Event and distinct-student counts plus suppression state.
     * @param int $minusers Privacy threshold.
     * @return string Summary or privacy warning.
     */
    public static function bookmark_summary(array $summary, int $minusers): string {
        global $OUTPUT;

        $hasdata = !empty($summary['hasdata']);
        $suppressed = $hasdata && !empty($summary['suppressed']);
        $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
        $eventvalue = $suppressed ? $hidden : (string)(int)($summary['eventcount'] ?? 0);
        $studentvalue = $suppressed ? $hidden : (string)(int)($summary['studentcount'] ?? 0);

        $cards = [
            [get_string('report:analytics_bookmarks_saved', 'mod_videotrack'), $eventvalue],
            [get_string('report:analytics_bookmark_students', 'mod_videotrack'), $studentvalue],
        ];
        $content = html_writer::tag(
            'h4',
            get_string('report:analytics_bookmarks_title', 'mod_videotrack'),
            ['id' => 'videotrack-analytics-bookmarks-title']
        );
        $content .= html_writer::start_div('videotrack-analytics-summary');
        foreach ($cards as [$label, $value]) {
            $content .= html_writer::div(
                html_writer::div(s($value), 'videotrack-analytics-summary-value') .
                    html_writer::div(s($label), 'videotrack-analytics-summary-label'),
                'videotrack-analytics-summary-card'
            );
        }
        $content .= html_writer::end_div();
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_bookmarks_private', 'mod_videotrack'),
            ['class' => 'text-muted small mb-2']
        );

        if (!$hasdata) {
            $content .= html_writer::tag(
                'p',
                get_string('report:analytics_bookmarks_none', 'mod_videotrack'),
                ['class' => 'text-muted mb-0']
            );
        } else if ($suppressed) {
            $content .= $OUTPUT->notification(
                get_string('report:analytics_bookmarks_suppressed', 'mod_videotrack', $minusers),
                'warning'
            );
        }

        return html_writer::tag(
            'section',
            $content,
            [
                'class' => 'videotrack-analytics-bookmarks mb-4',
                'aria-labelledby' => 'videotrack-analytics-bookmarks-title',
            ]
        );
    }

    /**
     * Renders privacy-safe acknowledgement Analytics.
     *
     * @param array $summary Confirmation counts and progress averages.
     * @param int $minusers Privacy threshold.
     * @param int $enabledactivitycount Number of activities with acknowledgement enabled.
     * @param int $anytimeactivitycount Number using the anytime policy.
     * @param int $videoendactivitycount Number requiring the final video second.
     * @return string Summary section.
     */
    public static function acknowledgement_summary(
        array $summary,
        int $minusers,
        int $enabledactivitycount,
        int $anytimeactivitycount,
        int $videoendactivitycount
    ): string {
        global $OUTPUT;

        $hasdata = !empty($summary['hasdata']);
        $suppressed = $hasdata && !empty($summary['suppressed']);
        $progresssuppressed = $hasdata && !empty($summary['progresssuppressed']);
        $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
        $unavailable = get_string('report:analytics_acknowledgements_unavailable', 'mod_videotrack');
        $confirmationvalue = $suppressed ? $hidden : (string)(int)($summary['confirmationcount'] ?? 0);
        $studentvalue = $suppressed ? $hidden : (string)(int)($summary['studentcount'] ?? 0);
        $secondsvalue = ($suppressed || $progresssuppressed)
            ? $hidden
            : ($summary['averageviewedseconds'] === null
                ? $unavailable
                : videotrack_format_seconds((float)$summary['averageviewedseconds']));
        $percentvalue = ($suppressed || $progresssuppressed)
            ? $hidden
            : ($summary['averageviewedpercent'] === null
                ? $unavailable
                : format_float((float)$summary['averageviewedpercent'], 1) . '%');

        $cards = [
            [get_string('report:analytics_acknowledgements_confirmations', 'mod_videotrack'), $confirmationvalue],
            [get_string('report:analytics_acknowledgements_students', 'mod_videotrack'), $studentvalue],
            [get_string('report:analytics_acknowledgements_average_seconds', 'mod_videotrack'), $secondsvalue],
            [get_string('report:analytics_acknowledgements_average_percent', 'mod_videotrack'), $percentvalue],
        ];
        $content = html_writer::tag(
            'h4',
            get_string('report:analytics_acknowledgements_title', 'mod_videotrack'),
            ['id' => 'videotrack-analytics-acknowledgements-title']
        );
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_acknowledgements_scope', 'mod_videotrack', [
                'activities' => $enabledactivitycount,
                'anytime' => $anytimeactivitycount,
                'videoend' => $videoendactivitycount,
            ]),
            ['class' => 'text-muted small']
        );
        $content .= html_writer::start_div('videotrack-analytics-summary');
        foreach ($cards as [$label, $value]) {
            $content .= html_writer::div(
                html_writer::div(s($value), 'videotrack-analytics-summary-value') .
                    html_writer::div(s($label), 'videotrack-analytics-summary-label'),
                'videotrack-analytics-summary-card'
            );
        }
        $content .= html_writer::end_div();
        $content .= html_writer::tag(
            'p',
            get_string('report:analytics_acknowledgements_private', 'mod_videotrack'),
            ['class' => 'text-muted small mb-2']
        );

        if (!$hasdata) {
            $content .= html_writer::tag(
                'p',
                get_string('report:analytics_acknowledgements_none', 'mod_videotrack'),
                ['class' => 'text-muted mb-0']
            );
        } else if ($suppressed) {
            $content .= $OUTPUT->notification(
                get_string('report:analytics_acknowledgements_suppressed', 'mod_videotrack', $minusers),
                'warning'
            );
        } else if ($progresssuppressed) {
            $content .= $OUTPUT->notification(
                get_string('report:analytics_acknowledgements_progress_suppressed', 'mod_videotrack', $minusers),
                'warning'
            );
        }
        if (!$suppressed && (int)($summary['progressmissing'] ?? 0) > 0) {
            $content .= html_writer::tag(
                'p',
                get_string(
                    'report:analytics_acknowledgements_legacy',
                    'mod_videotrack',
                    (int)$summary['progressmissing']
                ),
                ['class' => 'text-muted small mb-0']
            );
        }

        return html_writer::tag('section', $content, [
            'class' => 'videotrack-analytics-acknowledgements mb-4',
            'aria-labelledby' => 'videotrack-analytics-acknowledgements-title',
        ]);
    }
}
