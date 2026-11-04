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

/**
 * Builds course-scoped playback choices, CSV, Forum, integrity and acknowledgement form groups.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_definition {
    /**
     * Add site-permitted HTML5 controls with course-scoped override permissions.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_html5_control_elements(\MoodleQuickForm $mform, \context_course $coursecontext): void {
        // HTML5 player controls visible only for upload source.
        $sitecontrols     = videotrack_get_html5controls((object)['html5controls' => '']);
        $allctrl          = [
            'play'        => get_string('ctrl:play', 'mod_videotrack'),
            'rewind'      => get_string('ctrl:rewind', 'mod_videotrack'),
            'fastforward' => get_string('ctrl:fastforward', 'mod_videotrack'),
            'progress'    => get_string('ctrl:progress', 'mod_videotrack'),
            'current'     => get_string('ctrl:current', 'mod_videotrack'),
            'duration'    => get_string('ctrl:duration', 'mod_videotrack'),
            'mute'        => get_string('ctrl:mute', 'mod_videotrack'),
            'volume'      => get_string('ctrl:volume', 'mod_videotrack'),
            'speed'       => get_string('ctrl:speed', 'mod_videotrack'),
            'pip'         => get_string('ctrl:pip', 'mod_videotrack'),
            'fullscreen' => get_string('ctrl:fullscreen', 'mod_videotrack'),
            'download'    => get_string('ctrl:download', 'mod_videotrack'),
        ];
        // Only show controls that admin enabled at site level.
        $availablectrl = array_filter(
            $allctrl,
            fn($k) => in_array($k, $sitecontrols),
            ARRAY_FILTER_USE_KEY
        );

        if (!empty($availablectrl)) {
            $mform->addElement(
                'header',
                'html5controlsheader',
                get_string('setting:heading_html5controls', 'mod_videotrack')
            );
            $mform->hideIf('html5controlsheader', 'videosource', 'neq', 'upload');

            $mform->addElement(
                'static',
                'html5controls_desc',
                '',
                html_writer::tag(
                    'small',
                    get_string('setting:html5controls_teacher_desc', 'mod_videotrack'),
                    ['class' => 'text-muted']
                )
            );
            $mform->hideIf('html5controls_desc', 'videosource', 'neq', 'upload');

            $ctrlgroup = [];
            foreach ($availablectrl as $key => $label) {
                $ctrlgroup[] = $mform->createElement(
                    'advcheckbox',
                    "html5ctrl_{$key}",
                    '',
                    $label,
                    [],
                    [0, $key]
                );
                $mform->setType("html5ctrl_{$key}", PARAM_TEXT);
                $mform->hideIf("html5ctrl_{$key}", 'videosource', 'neq', 'upload');
            }
            $mform->addGroup(
                $ctrlgroup,
                'html5controlsgroup',
                get_string('setting:html5controls', 'mod_videotrack'),
                ' ',
                false
            );
            $mform->hideIf('html5controlsgroup', 'videosource', 'neq', 'upload');

            if (!has_capability('mod/videotrack:overrideplayersettings', $coursecontext)) {
                $mform->freeze('html5controlsgroup');
            }
        }
    }

    /**
     * Add site-permitted playback speeds with stable flat field names and course permissions.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_playback_speed_elements(\MoodleQuickForm $mform, \context_course $coursecontext): void {
        // Playback speeds available from the site configuration.
        $allspeeds = [
            '0.25' => '0.25×', '0.5' => '0.5×', '0.75' => '0.75×',
            '1'    => '1× (' . get_string('setting:speed_normal', 'mod_videotrack') . ')',
            '1.25' => '1.25×', '1.5' => '1.5×', '1.75' => '1.75×',
            '2' => '2×', '3' => '3×', '4' => '4×',
        ];
        // Only show speeds that admin has enabled at site level.
        $sitespeeds = videotrack_get_site_playback_speeds();
        $availablespeeds = array_filter($allspeeds, fn($k) => in_array((float)$k, $sitespeeds), ARRAY_FILTER_USE_KEY);

        if (!empty($availablespeeds)) {
            $mform->addElement(
                'header',
                'playbackspeedsheader',
                get_string('setting:playbackspeeds', 'mod_videotrack')
            );
            $mform->addElement(
                'static',
                'playbackspeeds_desc',
                '',
                html_writer::tag(
                    'small',
                    get_string('setting:playbackspeeds_teacher_desc', 'mod_videotrack'),
                    ['class' => 'text-muted']
                )
            );
            $checkboxgroup = [];
            foreach ($availablespeeds as $val => $label) {
                $checkboxgroup[] = $mform->createElement(
                    'advcheckbox',
                    "playbackspeed_{$val}",
                    '',
                    $label,
                    [],
                    [0, $val]
                );
                $mform->setType("playbackspeed_{$val}", PARAM_TEXT);
            }
            $mform->addGroup(
                $checkboxgroup,
                'playbackspeedsgroup',
                get_string('setting:playbackspeeds', 'mod_videotrack'),
                ' ',
                false
            );

            if (!has_capability('mod/videotrack:overrideplayersettings', $coursecontext)) {
                $mform->freeze('playbackspeedsgroup');
            }
        }
    }

    /**
     * Add CSV choices with identity fields enabled only when allowed by Moodle.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context $csvcontext Effective activity or course context.
     */
    public static function add_csv_elements(\MoodleQuickForm $mform, \context $csvcontext): void {
        // CSV export settings.
        $mform->addElement(
            'header',
            'csvexportheader',
            get_string('setting:heading_csvexport', 'mod_videotrack')
        );
        $mform->addElement(
            'select',
            'csvdelimiter',
            get_string('setting:csvdelimiter', 'mod_videotrack'),
            \mod_videotrack\local\csv_export::delimiter_options_with_inherit()
        );
        $mform->setType('csvdelimiter', PARAM_ALPHA);
        $mform->setDefault('csvdelimiter', \mod_videotrack\local\csv_export::DELIMITER_INHERIT);
        $mform->addHelpButton('csvdelimiter', 'setting:csvdelimiter', 'mod_videotrack');
        $mform->addElement(
            'static',
            'csvexportfields_desc',
            '',
            html_writer::tag(
                'small',
                get_string('setting:csvexportfields_teacher_desc', 'mod_videotrack'),
                ['class' => 'text-muted']
            )
        );
        $csvfieldoptions = \mod_videotrack\local\csv_export::form_field_options($csvcontext);
        $allowedcsvfields = array_keys(\mod_videotrack\local\csv_export::field_options($csvcontext));
        $csvfieldcheckboxes = [];
        foreach ($csvfieldoptions as $field => $label) {
            $elementname = \mod_videotrack\local\csv_export::form_element_name($field);
            $isallowedcsvfield = in_array($field, $allowedcsvfields, true);
            $attributes = [];
            if (!$isallowedcsvfield) {
                $label .= ' (' . get_string('notavailable') . ')';
                $attributes['disabled'] = 'disabled';
            }
            $csvfieldcheckboxes[] = $mform->createElement(
                'advcheckbox',
                $elementname,
                '',
                $label,
                $attributes,
                [0, 1]
            );
            $mform->setType($elementname, PARAM_BOOL);
        }
        if ($csvfieldcheckboxes) {
            $mform->addGroup(
                $csvfieldcheckboxes,
                'csvexportfieldsgroup',
                get_string('setting:csvexportfields', 'mod_videotrack'),
                ' ',
                false
            );
        }
    }

    /**
     * Add Forum fields while retaining unavailable saved destinations and field order.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param int $courseid Course whose compatible forums are offered.
     * @param \stdClass|null $current Current activity settings, or null for a new activity.
     */
    public static function add_forum_elements(\MoodleQuickForm $mform, int $courseid, ?\stdClass $current): void {
        // Optional link to a Forum activity in the same course.
        $mform->addElement('header', 'forumlinkheader', get_string('forum:settingsheader', 'mod_videotrack'));
        $forumoptions = videotrack_get_compatible_forum_options($courseid);
        $currentforumid = isset($current->linkedforumid) ? (int)$current->linkedforumid : 0;
        if ($currentforumid > 0 && !isset($forumoptions[$currentforumid])) {
            $forumoptions[$currentforumid] = get_string('forum:configuredunavailable', 'mod_videotrack');
        }
        $enableattributes = [];
        if (!$forumoptions) {
            $enableattributes['disabled'] = 'disabled';
        }
        $mform->addElement(
            'advcheckbox',
            'forumpostingenabled',
            get_string('forum:enable', 'mod_videotrack'),
            get_string('forum:enable_help', 'mod_videotrack'),
            $enableattributes
        );
        $mform->setDefault('forumpostingenabled', 0);
        $mform->setType('forumpostingenabled', PARAM_BOOL);

        $selectoptions = [0 => get_string('forum:selectforum', 'mod_videotrack')] + $forumoptions;
        $mform->addElement(
            'select',
            'linkedforumid',
            get_string('forum:linkedforum', 'mod_videotrack'),
            $selectoptions
        );
        $mform->setType('linkedforumid', PARAM_INT);
        $mform->setDefault('linkedforumid', 0);
        $mform->hideIf('linkedforumid', 'forumpostingenabled', 'notchecked');
        $mform->disabledIf('linkedforumid', 'forumpostingenabled', 'notchecked');

        $mform->addElement(
            'text',
            'forumsubjecttemplate',
            get_string('forum:subjecttemplate', 'mod_videotrack'),
            ['size' => 64, 'maxlength' => 255]
        );
        $mform->setType('forumsubjecttemplate', PARAM_TEXT);
        $mform->setDefault(
            'forumsubjecttemplate',
            get_string('forum:subjecttemplatedefault', 'mod_videotrack')
        );
        $mform->addHelpButton('forumsubjecttemplate', 'forum:subjecttemplate', 'mod_videotrack');
        $mform->hideIf('forumsubjecttemplate', 'forumpostingenabled', 'notchecked');
        if (!$forumoptions) {
            $mform->freeze('linkedforumid');
            $mform->addElement(
                'static',
                'forumnoneavailable',
                '',
                get_string('forum:nocompatibleforums', 'mod_videotrack')
            );
        }
        $mform->addElement(
            'static',
            'forumprivacyinfo',
            '',
            get_string('forum:privacyinfo', 'mod_videotrack')
        );
    }

    /**
     * Add integrity controls and the effective site focus-policy summary.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     */
    public static function add_integrity_elements(\MoodleQuickForm $mform): void {
        // Integrity indicators and optional focus controls.
        $mform->addElement(
            'header',
            'integritysettingsheader',
            get_string('integrity:settingsheader', 'mod_videotrack')
        );
        $mform->addElement(
            'static',
            'integritysettingsintro',
            '',
            get_string('integrity:settingsintro', 'mod_videotrack')
        );
        $mform->addElement(
            'advcheckbox',
            'integrityindicatorsenabled',
            get_string('integrity:enable', 'mod_videotrack'),
            get_string('integrity:enable_desc', 'mod_videotrack')
        );
        $mform->setType('integrityindicatorsenabled', PARAM_BOOL);
        $mform->setDefault('integrityindicatorsenabled', 0);
        $mform->addHelpButton('integrityindicatorsenabled', 'integrity:enable', 'mod_videotrack');
        $mform->addElement(
            'advcheckbox',
            'pauseonfocusloss',
            get_string('integrity:pauseonfocusloss', 'mod_videotrack'),
            get_string('integrity:pauseonfocusloss_desc', 'mod_videotrack')
        );
        $mform->setType('pauseonfocusloss', PARAM_BOOL);
        $mform->setDefault('pauseonfocusloss', 0);
        $mform->addElement(
            'advcheckbox',
            'preventpictureinpicture',
            get_string('integrity:preventpip', 'mod_videotrack'),
            get_string('integrity:preventpip_desc', 'mod_videotrack')
        );
        $mform->setType('preventpictureinpicture', PARAM_BOOL);
        $mform->setDefault('preventpictureinpicture', 0);
        $mform->addElement(
            'advcheckbox',
            'randomfocuspauses',
            get_string('integrity:randompauses', 'mod_videotrack'),
            get_string('integrity:randompauses_desc', 'mod_videotrack')
        );
        $mform->setType('randomfocuspauses', PARAM_BOOL);
        $mform->setDefault('randomfocuspauses', 0);

        $randompausebounds = \mod_videotrack\local\integrity::random_pause_bounds();
        $focuslosspolicy = \mod_videotrack\local\integrity::focus_loss_policy();
        $focuslossgrace = \mod_videotrack\local\integrity::focus_loss_grace_seconds();
        $focuspolicylabel = $focuslosspolicy === \mod_videotrack\local\integrity::FOCUS_POLICY_STRICT
            ? get_string('setting:focuslosspolicy_strict', 'mod_videotrack')
            : get_string('setting:focuslosspolicy_hiddenonly', 'mod_videotrack');
        $mform->addElement(
            'static',
            'integritysitepolicy',
            get_string('integrity:sitepolicy', 'mod_videotrack'),
            get_string('integrity:sitepolicy_desc', 'mod_videotrack', (object)[
                'minimum' => $randompausebounds['min'],
                'maximum' => $randompausebounds['max'],
                'policy' => $focuspolicylabel,
                'grace' => $focuslossgrace,
            ])
        );
    }

    /**
     * Add acknowledgement settings with the current activity context and safe editor options.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context $context Activity form context.
     */
    public static function add_acknowledgement_elements(\MoodleQuickForm $mform, \context $context): void {
        $mform->addElement(
            'header',
            'acknowledgementheader',
            get_string('acknowledgement:header', 'mod_videotrack')
        );
        $mform->setExpanded('acknowledgementheader', false);
        $mform->addElement(
            'advcheckbox',
            'acknowledgementenabled',
            get_string('acknowledgement:enabled', 'mod_videotrack'),
            get_string('acknowledgement:enabled_desc', 'mod_videotrack')
        );
        $mform->setType('acknowledgementenabled', PARAM_BOOL);
        $mform->setDefault('acknowledgementenabled', 0);

        $mform->addElement(
            'select',
            'acknowledgementtiming',
            get_string('acknowledgement:timing', 'mod_videotrack'),
            [
                \mod_videotrack\local\acknowledgement::TIMING_ANYTIME =>
                    get_string('acknowledgement:timing_anytime', 'mod_videotrack'),
                \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END =>
                    get_string('acknowledgement:timing_videoend', 'mod_videotrack'),
            ]
        );
        $mform->setType('acknowledgementtiming', PARAM_INT);
        $mform->setDefault(
            'acknowledgementtiming',
            \mod_videotrack\local\acknowledgement::TIMING_ANYTIME
        );
        $mform->addHelpButton('acknowledgementtiming', 'acknowledgement:timing', 'mod_videotrack');
        $mform->disabledIf('acknowledgementtiming', 'acknowledgementenabled', 'notchecked');
        $acknowledgementoptions = [
            'context' => $context,
            'maxfiles' => 0,
            'maxbytes' => 0,
            'trusttext' => false,
            'noclean' => false,
        ];
        $mform->addElement(
            'editor',
            'acknowledgement_editor',
            get_string('acknowledgement:text', 'mod_videotrack'),
            null,
            $acknowledgementoptions
        );
        $mform->addHelpButton('acknowledgement_editor', 'acknowledgement:text', 'mod_videotrack');
        $mform->disabledIf('acknowledgement_editor', 'acknowledgementenabled', 'notchecked');
    }
}
