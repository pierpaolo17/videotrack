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

use context;

/**
 * Prepare edit-form values before Moodle renders the activity settings.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_preprocessing {
    /**
     * Load persisted reactions and prepare icon drafts for unfilled rows.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     * @param int $instanceid Activity instance id, or zero for a new activity.
     * @param int $repeatcount Number of reaction form rows.
     */
    public static function reaction_defaults(array &$defaultvalues, int $instanceid, int $repeatcount): void {
        global $DB;

        // Load existing reactions for the edit form.
        // Moodle calls data_preprocessing() before displaying the form: this is the correct place.
        // (set_data() is the public base form method and must not be overridden for this logic).
        if (!empty($instanceid)) {
            $reactions = $DB->get_records(
                'videotrack_react',
                ['videotrackid' => $instanceid, 'isdeleted' => 0],
                'sortorder ASC'
            );
            $cm = get_coursemodule_from_instance(
                'videotrack',
                $instanceid,
                0,
                false,
                IGNORE_MISSING
            );
            $context = $cm ? \context_module::instance($cm->id) : null;
            $i = 0;
            foreach ($reactions as $reaction) {
                $defaultvalues['reactionid'][$i]         = $reaction->id;
                $defaultvalues['reactionlabel'][$i]       = $reaction->label;
                $defaultvalues['reactiondescription'][$i] = $reaction->description;
                $defaultvalues['reactionicontype'][$i]    = $reaction->icontype;
                $defaultvalues['reactioniconvalue'][$i]   =
                    $reaction->icontype === 'file' ? '' : $reaction->iconvalue;
                $defaultvalues['reactionrequired'][$i]    = $reaction->requiredforcompletion;
                $draftitemid = file_get_submitted_draft_itemid('reactioniconfile_' . $i);
                if ($context) {
                    file_prepare_draft_area(
                        $draftitemid,
                        $context->id,
                        'mod_videotrack',
                        'reactionicon',
                        (int)$reaction->id,
                        [
                            'subdirs' => 0,
                            'maxfiles' => 1,
                            'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
                        ]
                    );
                }
                $defaultvalues['reactioniconfile_' . $i] = $draftitemid;
                $i++;
            }
        }
        // Prepare draft areas for empty rows.
        for ($i = 0; $i < $repeatcount; $i++) {
            $field = 'reactioniconfile_' . $i;
            if (!isset($defaultvalues[$field])) {
                $defaultvalues[$field] = file_get_submitted_draft_itemid($field);
            }
        }
    }

    /**
     * Prepare completion, grade and Vimeo defaults.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     * @param string $suffix Moodle completion-field suffix.
     * @param int $instanceid Activity instance id, or zero for a new activity.
     */
    public static function completion_defaults(array &$defaultvalues, string $suffix, int $instanceid): void {
        $defaultvalues['acknowledgement_editor'] = [
            'text' => (string)($defaultvalues['acknowledgementtext'] ?? ''),
            'format' => (int)($defaultvalues['acknowledgementformat'] ?? FORMAT_HTML),
        ];

        if (!isset($defaultvalues['completionpercent'])) {
            $defaultvalues['completionpercent'] = 0;
        }
        if (!isset($defaultvalues['completionacknowledgement'])) {
            $defaultvalues['completionacknowledgement'] = 0;
        }
        if (empty($defaultvalues['completionlogic'])) {
            $defaultvalues['completionlogic'] = 'and';
        }
        foreach (['completionpercent', 'completionacknowledgement', 'completionlogic'] as $field) {
            $defaultvalues[$field . $suffix] = $defaultvalues[$field];
        }

        self::grade_and_media_defaults($defaultvalues, $instanceid);
    }

    /**
     * Load the stored passing grade and mirror an existing Vimeo source URL.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     * @param int $instanceid Activity instance id, or zero for a new activity.
     */
    private static function grade_and_media_defaults(array &$defaultvalues, int $instanceid): void {
        global $DB;

        // Pre-populate gradepass from the database when editing an existing activity.
        if (!isset($defaultvalues['gradepass']) && !empty($instanceid)) {
            $gradepass = $DB->get_field('videotrack', 'gradepass', ['id' => $instanceid]);
            $defaultvalues['gradepass'] = ($gradepass !== false) ? format_float((float)$gradepass, 5) : 0;
        }
        // Pre-populate vimeourl when the source is Vimeo.
        if (
            ($defaultvalues['videosource'] ?? 'youtube') === 'vimeo'
            && !empty($defaultvalues['videourl'])
        ) {
            $defaultvalues['vimeourl'] = $defaultvalues['videourl'];
        }
    }

    /**
     * Prepare playback speed and HTML5 control checkboxes.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     */
    public static function playback_defaults(array &$defaultvalues): void {
        // Pre-populate the playback-rate checkboxes.
        $activespeeds = [];
        if (!empty($defaultvalues['playbackspeeds'])) {
            $activespeeds = array_map('strval', array_map(
                'floatval',
                preg_split('/[,\n]+/', $defaultvalues['playbackspeeds'])
            ));
        } else {
            // Use site defaults.
            $activespeeds = array_map('strval', videotrack_get_site_playback_speeds());
        }
        foreach (['0.25', '0.5', '0.75', '1', '1.25', '1.5', '1.75', '2', '3', '4'] as $v) {
            $defaultvalues["playbackspeed_{$v}"] = in_array($v, $activespeeds) ? $v : 0;
        }

        // Pre-populate the HTML5 control checkboxes.
        $activecontrols = !empty($defaultvalues['html5controls'])
            ? array_map('trim', explode(',', $defaultvalues['html5controls']))
            : videotrack_get_html5controls((object)['html5controls' => '']);
        $html5controls = [
            'play',
            'rewind',
            'fastforward',
            'progress',
            'current',
            'duration',
            'mute',
            'volume',
            'speed',
            'pip',
            'fullscreen',
            'download',
        ];
        foreach ($html5controls as $ctrl) {
            $defaultvalues["html5ctrl_{$ctrl}"] = in_array($ctrl, $activecontrols) ? $ctrl : 0;
        }
    }

    /**
     * Prepare CSV export field and delimiter defaults.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     * @param context|null $context Activity form context, when available.
     */
    public static function csv_defaults(array &$defaultvalues, ?context $context): void {
        global $COURSE;

        // Pre-populate optional CSV export field checkboxes.
        $csvraw = trim((string)($defaultvalues['csvexportfields'] ?? ''));
        if ($csvraw === 'none') {
            $activecsvfields = [];
        } else if ($csvraw === '') {
            $activecsvfields = \mod_videotrack\local\csv_export::site_default_fields();
        } else {
            $activecsvfields = array_values(array_filter(array_map('trim', explode(',', $csvraw))));
        }
        $csvcontext = $context ?: \context_course::instance($COURSE->id);
        foreach (array_keys(\mod_videotrack\local\csv_export::form_field_options($csvcontext)) as $field) {
            $elementname = \mod_videotrack\local\csv_export::form_element_name($field);
            $defaultvalues[$elementname] = in_array($field, $activecsvfields, true) ? 1 : 0;
        }
        if (empty($defaultvalues['csvdelimiter'])) {
            $defaultvalues['csvdelimiter'] = \mod_videotrack\local\csv_export::DELIMITER_INHERIT;
        }
    }

    /**
     * Apply the site defaults for player, caption and language settings.
     *
     * @param array $defaultvalues Moodle form default values, updated in place.
     */
    public static function site_defaults(array &$defaultvalues): void {
        // Pre-populate player behaviour boolean fields.
        $behaviourdefaults = [
            'autoplay' => 'default_autoplay',
            'loopenabled' => 'default_loop',
            'startmuted' => 'default_startmuted',
            'allowdownload' => 'default_allowdownload',
        ];
        foreach ($behaviourdefaults as $field => $configname) {
            if (!isset($defaultvalues[$field])) {
                $cfgval = get_config('mod_videotrack', $configname);
                $defaultvalues[$field] = ($cfgval !== false) ? (int)(bool)$cfgval : 0;
            }
        }

        // Pre-populate numeric fields with site defaults when the value is 0.
        foreach (['playerwidth', 'rewindstep', 'fastforwardstep'] as $field) {
            if (empty($defaultvalues[$field])) {
                $defaultvalues[$field] = 0; // Zero uses the site default.
            }
        }

        // Pre-populate captions and language.
        if (!isset($defaultvalues['captions'])) {
            $defaultvalues['captions'] = (int)(bool)get_config('mod_videotrack', 'default_captions');
        }
        if (!isset($defaultvalues['captionslang']) || $defaultvalues['captionslang'] === '') {
            $defaultvalues['captionslang'] = (string)get_config('mod_videotrack', 'default_captionslang');
        }
    }
}
