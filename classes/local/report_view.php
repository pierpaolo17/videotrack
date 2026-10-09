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
use moodle_url;

/**
 * Shared controls and explanations for the teacher Analytics report.
 *
 * Presentation-only services keep the request controller auditable without
 * changing Analytics queries, privacy thresholds or rendered contracts.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_view {
    /**
     * Renders the expandable explanation of analytics calculations and privacy.
     *
     * @param int $minusers Privacy threshold.
     * @param bool $haspartialmasking Whether some interval values are masked.
     * @param bool $showbookmarks Whether bookmark aggregates are available.
     * @param bool $showintegrity Whether diagnostic integrity indicators are available.
     * @param bool $showacknowledgements Whether acknowledgement aggregates are available.
     * @return string Accessible details markup.
     */
    public static function analytics_methodology(
        int $minusers,
        bool $haspartialmasking,
        bool $showbookmarks,
        bool $showintegrity,
        bool $showacknowledgements
    ): string {
        $items = [
            get_string('report:analytics_method_unique', 'mod_videotrack'),
            get_string('report:analytics_method_retention', 'mod_videotrack'),
            get_string('report:analytics_method_heatmap', 'mod_videotrack'),
            get_string('report:analytics_method_reactions', 'mod_videotrack'),
        ];
        if ($showbookmarks) {
            $items[] = get_string('report:analytics_method_bookmarks', 'mod_videotrack');
        }
        if ($showintegrity) {
            $items[] = get_string('integrity:methodology', 'mod_videotrack');
        }
        if ($showacknowledgements) {
            $items[] = get_string('report:analytics_method_acknowledgements', 'mod_videotrack');
        }
        $content = html_writer::tag(
            'p',
            get_string('report:analytics_method_intro', 'mod_videotrack'),
            ['class' => 'mb-2']
        );
        $content .= html_writer::alist($items, ['class' => 'mb-2']);
        if ($minusers > \mod_videotrack\local\analytics::EXACT_REPORT_MIN_USERS) {
            $content .= html_writer::tag(
                'p',
                get_string('report:analytics_method_privacy', 'mod_videotrack', $minusers),
                ['class' => 'mb-0']
            );
            if ($haspartialmasking) {
                $content .= html_writer::tag(
                    'p',
                    get_string('report:analytics_method_partial', 'mod_videotrack'),
                    ['class' => 'mt-2 mb-0']
                );
            }
        }

        return html_writer::tag(
            'details',
            html_writer::tag(
                'summary',
                get_string('report:analytics_method_toggle', 'mod_videotrack'),
                ['class' => 'btn btn-secondary btn-sm']
            ) . html_writer::div($content, 'videotrack-analytics-method-content'),
            ['class' => 'videotrack-analytics-method mb-3']
        );
    }

    /**
     * Renders one privacy warning only when a dataset cannot be displayed.
     *
     * @param bool $viewingsuppressed Whether viewing analytics are hidden.
     * @param bool $reactionssuppressed Whether reaction totals are hidden.
     * @param int $minusers Privacy threshold.
     * @return string Warning notification or an empty string.
     */
    public static function privacy_alert(
        bool $viewingsuppressed,
        bool $reactionssuppressed,
        int $minusers
    ): string {
        global $OUTPUT;

        if (!$viewingsuppressed && !$reactionssuppressed) {
            return '';
        }
        if ($viewingsuppressed && $reactionssuppressed) {
            $stringkey = 'report:analytics_privacy_unavailable_both';
        } else if ($viewingsuppressed) {
            $stringkey = 'report:analytics_privacy_unavailable_viewing';
        } else {
            $stringkey = 'report:analytics_privacy_unavailable_reactions';
        }
        return $OUTPUT->notification(get_string($stringkey, 'mod_videotrack', $minusers), 'warning');
    }

    /**
     * Renders the analytics table download selector.
     *
     * @param array $formats Enabled data formats.
     * @param array $params Current analytics filter parameters.
     * @return string Download form or an empty string.
     */
    public static function analytics_download(array $formats, array $params): string {
        if (!$formats) {
            return '';
        }

        $options = [];
        foreach ($formats as $format) {
            $options[$format] = get_string('dataformat', 'dataformat_' . $format);
        }
        $form = html_writer::start_tag('form', [
            'method' => 'get',
            'action' => (new moodle_url('/mod/videotrack/report.php'))->out(false),
            'class' => 'videotrack-analytics-download-form d-flex flex-wrap align-items-end mb-2',
        ]);
        foreach ($params as $name => $value) {
            $form .= html_writer::empty_tag('input', [
                'type' => 'hidden',
                'name' => $name,
                'value' => $value,
            ]);
        }
        $form .= html_writer::empty_tag('input', [
            'type' => 'hidden',
            'name' => 'sesskey',
            'value' => sesskey(),
        ]);
        $form .= html_writer::start_div('form-group mb-0 mr-2');
        $form .= html_writer::label(
            get_string('report:analytics_download_label', 'mod_videotrack'),
            'id_analyticsformat',
            false,
            ['class' => 'd-block']
        );
        $form .= html_writer::select($options, 'analyticsformat', '', false, [
            'id' => 'id_analyticsformat',
            'class' => 'custom-select',
        ]);
        $form .= html_writer::end_div();
        $form .= html_writer::tag('button', get_string('download'), [
            'type' => 'submit',
            'class' => 'btn btn-secondary',
        ]);
        $form .= html_writer::end_tag('form');
        return $form;
    }
}
