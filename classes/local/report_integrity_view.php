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

use html_table;
use html_writer;

/**
 * Privacy-safe integrity summaries for the teacher Analytics report.
 *
 * Presentation-only services keep the request controller auditable without
 * changing Analytics queries, privacy thresholds or rendered contracts.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_integrity_view {
    /**
     * Renders privacy-safe diagnostic integrity indicators when recording is enabled.
     *
     * The values are signals to review in context, never proof of misconduct.
     *
     * @param array $summary Per-event-type counts and suppression state.
     * @param int $minusers Privacy threshold.
     * @param int $enabledactivitycount Number of activities with signal recording enabled.
     * @return string Summary section.
     */
    public static function integrity_summary(
        array $summary,
        int $minusers,
        int $enabledactivitycount = 1
    ): string {
        global $OUTPUT;

        $rows = [];
        $hassuppressed = false;
        foreach (\mod_videotrack\local\integrity::EVENT_TYPES as $eventtype) {
            $item = $summary[$eventtype] ?? [];
            if (empty($item['hasdata'])) {
                continue;
            }
            $suppressed = !empty($item['suppressed']);
            $hassuppressed = $hassuppressed || $suppressed;
            $hidden = get_string('report:analytics_notavailable_privacy', 'mod_videotrack');
            $rows[] = [
                get_string(\mod_videotrack\local\integrity::label_string($eventtype), 'mod_videotrack'),
                $suppressed ? $hidden : (string)(int)($item['eventcount'] ?? 0),
                $suppressed ? $hidden : (string)(int)($item['studentcount'] ?? 0),
            ];
        }

        $content = self::integrity_intro();
        $content .= html_writer::tag(
            'p',
            get_string('integrity:analytics_enabled', 'mod_videotrack', max(1, $enabledactivitycount)),
            ['class' => 'small font-weight-bold']
        );

        if (!$rows) {
            $content .= html_writer::tag(
                'p',
                get_string('integrity:nodata', 'mod_videotrack'),
                ['class' => 'text-muted mb-0']
            );
        } else {
            $table = new html_table();
            $table->caption = get_string('integrity:reporttitle', 'mod_videotrack');
            $table->head = [
                get_string('integrity:signal', 'mod_videotrack'),
                get_string('integrity:events', 'mod_videotrack'),
                get_string('integrity:students', 'mod_videotrack'),
            ];
            $table->data = $rows;
            $content .= html_writer::table($table);
            if ($hassuppressed) {
                $content .= $OUTPUT->notification(
                    get_string('integrity:suppressed', 'mod_videotrack', $minusers),
                    'warning'
                );
            }
        }

        return self::integrity_section($content);
    }

    /**
     * Renders the information state used when integrity recording is disabled.
     *
     * @return string Summary section.
     */
    public static function integrity_disabled_summary(): string {
        return self::integrity_unavailable_summary(
            get_string('integrity:analytics_disabled', 'mod_videotrack'),
            'info'
        );
    }

    /**
     * Renders the warning used when focus controls run without integrity recording.
     *
     * @return string Summary section.
     */
    public static function integrity_controls_without_recording_summary(): string {
        return self::integrity_unavailable_summary(
            get_string('integrity:analytics_recording_disabled_controls', 'mod_videotrack'),
            'warning'
        );
    }

    /**
     * Builds the shared heading and explanatory text for integrity summaries.
     *
     * @return string Summary introduction.
     */
    private static function integrity_intro(): string {
        $content = html_writer::tag(
            'h4',
            get_string('integrity:reporttitle', 'mod_videotrack'),
            ['id' => 'videotrack-integrity-summary-title']
        );
        $content .= html_writer::tag(
            'p',
            get_string('integrity:reportintro', 'mod_videotrack'),
            ['class' => 'text-muted small']
        );
        return $content;
    }

    /**
     * Renders an integrity state that has no event table.
     *
     * @param string $message Localised explanation.
     * @param string $notificationtype Moodle notification type.
     * @return string Summary section.
     */
    private static function integrity_unavailable_summary(string $message, string $notificationtype): string {
        global $OUTPUT;

        $content = self::integrity_intro();
        $content .= $OUTPUT->notification($message, $notificationtype);
        return self::integrity_section($content);
    }

    /**
     * Wraps integrity summary content in its accessible landmark.
     *
     * @param string $content Summary content.
     * @return string Summary section.
     */
    private static function integrity_section(string $content): string {
        return html_writer::tag('section', $content, [
            'class' => 'videotrack-integrity-summary mb-4',
            'aria-labelledby' => 'videotrack-integrity-summary-title',
        ]);
    }
}
