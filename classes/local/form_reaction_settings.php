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
 * Builds reaction settings and row controls, then applies the course-scoped completion lock.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_reaction_settings {
    /**
     * Add the general reaction fields before the activity form creates its repeated rows.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context $context Original activity form context used by the notice editor.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_elements(\MoodleQuickForm $mform, \context $context, \context_course $coursecontext): void {
        $canoverridecompleting = has_capability('mod/videotrack:overridecompletionsettings', $coursecontext);
        $cfg = function (string $key, $fallback) {
            $val = get_config('mod_videotrack', $key);
            return ($val !== false && $val !== '') ? $val : $fallback;
        };

        // Reactions section.
        $mform->addElement('header', 'reactionsheader', get_string('reactionsheader', 'mod_videotrack'));
        $mform->addElement('advcheckbox', 'reactionsenabled', get_string('reactionsenabled', 'mod_videotrack'));

        $mform->setType('reactionsenabled', PARAM_BOOL);
        $mform->setDefault('reactionsenabled', 0);
        $mform->addElement(
            'static',
            'reactionconfiguration_note',
            '',
            html_writer::tag(
                'div',
                get_string('err:reactionconfigurationrequired', 'mod_videotrack'),
                ['class' => 'alert alert-info mb-0']
            )
        );
        $mform->hideIf('reactionconfiguration_note', 'reactionsenabled', 'notchecked');

        self::add_notice_elements($mform, $context);

        $mform->addElement('advcheckbox', 'reactionsrequired', get_string('reactionsrequired', 'mod_videotrack'));

        $mform->setType('reactionsrequired', PARAM_BOOL);
        $mform->setDefault('reactionsrequired', 0);
        $mform->addElement('text', 'minreactions', get_string('minreactions', 'mod_videotrack'), ['size' => 3]);
        $mform->setType('minreactions', PARAM_INT);
        $mform->setDefault('minreactions', 0);
        $mform->addElement('advcheckbox', 'requireallreactiontypes', get_string('requireallreactiontypes', 'mod_videotrack'));

        $mform->setType('requireallreactiontypes', PARAM_BOOL);
        $mform->setDefault('requireallreactiontypes', 0);
        $mform->disabledIf('reactionsrequired', 'reactionsenabled', 'notchecked');
        $mform->disabledIf('minreactions', 'reactionsenabled', 'notchecked');
        $mform->disabledIf('minreactions', 'reactionsrequired', 'notchecked');
        $mform->disabledIf('requireallreactiontypes', 'reactionsenabled', 'notchecked');
        $mform->addElement(
            'static',
            'reactioncompletionguide',
            get_string('completionreactionrules', 'mod_videotrack'),
            get_string('completionreactionrules_desc', 'mod_videotrack')
        );
        // Completion settings locked when teacher lacks overridecompletionsettings.
        if (!$canoverridecompleting) {
            $mform->addElement(
                'static',
                'completionsettings_locked_notice',
                '',
                html_writer::tag('div', get_string('setting:lockedbyAdmin', 'mod_videotrack'), ['class' => 'alert alert-info'])
            );
        }

        $mform->addElement(
            'select',
            'clusterwindow',
            get_string('clusterwindow', 'mod_videotrack'),
            [10 => 10, 15 => 15, 20 => 20, 30 => 30, 60 => 60]
        );
        $mform->setType('clusterwindow', PARAM_INT);
        $mform->setDefault('clusterwindow', (int)$cfg('default_clusterwindow', 30));

        if (!$canoverridecompleting) {
            $mform->freeze('clusterwindow');
        }

        $mform->addElement('advcheckbox', 'showstudentreport', get_string('showstudentreport', 'mod_videotrack'));

        $mform->setType('showstudentreport', PARAM_BOOL);
        $mform->setDefault('showstudentreport', 1);

        // Reaction preset selector.
        $presetoptions = videotrack_get_preset_select_options();
        if (count($presetoptions) > 1) {
            // Only show the selector if at least one preset has been configured.
            $mform->addElement(
                'select',
                'reactionpreset',
                get_string('reactionpreset', 'mod_videotrack'),
                $presetoptions
            );
            $mform->setType('reactionpreset', PARAM_ALPHANUMEXT);
            $mform->setDefault('reactionpreset', '');
            $mform->addHelpButton('reactionpreset', 'reactionpreset', 'mod_videotrack');
            // Hidden field used by JS to carry preset JSON to the client.
            $mform->addElement('hidden', 'reactionpreset_json', '');
            $mform->setType('reactionpreset_json', PARAM_RAW_TRIMMED);
        }
    }

    /**
     * Add one reaction row and its icon visibility rules in the original field order.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param int $i Zero-based reaction row index selected by the activity form.
     * @param array $options Original file manager options for reaction icons.
     */
    public static function add_row_elements(\MoodleQuickForm $mform, int $i, array $options): void {
        $mform->addElement(
            'html',
            html_writer::tag(
                'h4',
                get_string('reactionx', 'mod_videotrack', $i + 1),
                ['class' => 'h5 mt-4 mb-2 videotrack-reaction-config-heading']
            )
        );

        $mform->addElement('hidden', 'reactionid[' . $i . ']', 0);
        $mform->setType('reactionid[' . $i . ']', PARAM_INT);

        $mform->addElement(
            'text',
            'reactionlabel[' . $i . ']',
            get_string('reactionlabel', 'mod_videotrack'),
            ['size' => 24]
        );
        $mform->setType('reactionlabel[' . $i . ']', PARAM_TEXT);

        $mform->addElement(
            'text',
            'reactiondescription[' . $i . ']',
            get_string('reactiondescription', 'mod_videotrack'),
            ['size' => 36]
        );
        $mform->setType('reactiondescription[' . $i . ']', PARAM_TEXT);

        $mform->addElement(
            'select',
            'reactionicontype[' . $i . ']',
            get_string('reactionicontype', 'mod_videotrack'),
            [
                '' => get_string('choosedots'),
                'emoji' => get_string('icontype:emoji', 'mod_videotrack'),
                'fa' => get_string('icontype:fa', 'mod_videotrack'),
                'file' => get_string('icontype:file', 'mod_videotrack'),
            ]
        );
        $mform->setType('reactionicontype[' . $i . ']', PARAM_ALPHA);

        $iconlistid = 'videotrack-reactioniconvalue-suggestions-' . $i;
        $mform->addElement(
            'text',
            'reactioniconvalue[' . $i . ']',
            get_string('reactioniconvalue', 'mod_videotrack'),
            [
                'size' => 24,
                'list' => $iconlistid,
                'class' => 'videotrack-icon-value-input',
            ]
        );
        $mform->setType('reactioniconvalue[' . $i . ']', PARAM_TEXT);
        $mform->addHelpButton('reactioniconvalue[' . $i . ']', 'reactioniconvalue', 'mod_videotrack');
        $mform->addElement(
            'static',
            'reactioniconvalue_suggestions_' . $i,
            '',
            videotrack_reaction_icon_datalist($iconlistid)
                . videotrack_reaction_icon_picker('reactioniconvalue[' . $i . ']', 'reactionicontype[' . $i . ']')
        );

        $mform->addElement(
            'filemanager',
            'reactioniconfile_' . $i,
            get_string('reactioniconfile', 'mod_videotrack'),
            null,
            $options
        );
        $mform->addHelpButton('reactioniconfile_' . $i, 'reactioniconfile', 'mod_videotrack');
        // Warn teachers about square proportions and automatic resizing.
        $mform->addElement(
            'static',
            'reactioniconfile_notice_' . $i,
            '',
            html_writer::tag(
                'small',
                get_string('reactioniconfile_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );

        $mform->addElement(
            'advcheckbox',
            'reactionrequired[' . $i . ']',
            get_string('reactionrequired', 'mod_videotrack')
        );
        $mform->setType('reactionrequired[' . $i . ']', PARAM_BOOL);
        self::add_row_visibility($mform, $i);
    }

    /**
     * Apply the existing icon input visibility rules after adding the row fields.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param int $i Zero-based reaction row index.
     */
    private static function add_row_visibility(\MoodleQuickForm $mform, int $i): void {
        $mform->disabledIf(
            'reactioniconvalue[' . $i . ']',
            'reactionicontype[' . $i . ']',
            'eq',
            'file'
        );
        $mform->disabledIf(
            'reactioniconvalue[' . $i . ']',
            'reactionicontype[' . $i . ']',
            'eq',
            ''
        );
        $mform->hideIf(
            'reactioniconvalue[' . $i . ']',
            'reactionicontype[' . $i . ']',
            'eq',
            'file'
        );
        $mform->hideIf(
            'reactioniconvalue[' . $i . ']',
            'reactionicontype[' . $i . ']',
            'eq',
            ''
        );
        $mform->hideIf(
            'reactioniconvalue_suggestions_' . $i,
            'reactionicontype[' . $i . ']',
            'eq',
            'file'
        );
        $mform->hideIf(
            'reactioniconvalue_suggestions_' . $i,
            'reactionicontype[' . $i . ']',
            'eq',
            ''
        );
        $mform->disabledIf(
            'reactioniconfile_' . $i,
            'reactionicontype[' . $i . ']',
            'neq',
            'file'
        );
        $mform->hideIf(
            'reactioniconfile_' . $i,
            'reactionicontype[' . $i . ']',
            'neq',
            'file'
        );
        $mform->hideIf(
            'reactioniconfile_notice_' . $i,
            'reactionicontype[' . $i . ']',
            'neq',
            'file'
        );
    }

    /**
     * Freeze the original completion controls after the requested number of reaction rows exists.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     * @param int $repeatcount Number of repeated reaction rows created by the activity form.
     */
    public static function apply_completion_lock(
        \MoodleQuickForm $mform,
        \context_course $coursecontext,
        int $repeatcount
    ): void {
        if (!has_capability('mod/videotrack:overridecompletionsettings', $coursecontext)) {
            foreach (['reactionsrequired', 'minreactions', 'requireallreactiontypes'] as $field) {
                $mform->freeze($field);
            }
            for ($i = 0; $i < $repeatcount; $i++) {
                $mform->freeze('reactionrequired[' . $i . ']');
            }
        }
    }

    /**
     * Add the notice switch and the original context-bound editor without attachments or trusted HTML.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context $context Original activity form context.
     */
    private static function add_notice_elements(\MoodleQuickForm $mform, \context $context): void {
        $mform->addElement('advcheckbox', 'showreactionnotice', get_string('showreactionnotice', 'mod_videotrack'));
        $mform->setType('showreactionnotice', PARAM_BOOL);
        $mform->setDefault('showreactionnotice', 1);
        $mform->disabledIf('showreactionnotice', 'reactionsenabled', 'notchecked');
        $reactionnoticeoptions = [
            'context' => $context,
            'maxfiles' => 0,
            'maxbytes' => 0,
            'trusttext' => false,
            'noclean' => false,
        ];
        $mform->addElement(
            'editor',
            'reactionnotice_editor',
            get_string('reactionnotice', 'mod_videotrack'),
            null,
            $reactionnoticeoptions
        );
        $mform->disabledIf('reactionnotice_editor', 'reactionsenabled', 'notchecked');
        $mform->disabledIf('reactionnotice_editor', 'showreactionnotice', 'notchecked');
    }
}
