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

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */

defined('MOODLE_INTERNAL') || die();

/** @var stdClass $CFG Moodle configuration provided by the activity form loader. */
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Activity settings form for the VideoTrack module.
 */
class mod_videotrack_mod_form extends moodleform_mod {
    /** Minimum number of reaction configuration rows shown to teachers. */
    private const MIN_REACTION_ROWS = 4;

    /** Maximum number of reaction definitions accepted by the instance form. */
    private const MAX_REACTION_ROWS = 30;

    /** @var int */
    protected $reactionrepeatcount = 0;

    /**
     * Defines the activity settings form.
     */
    public function definition() {
        global $COURSE, $PAGE;
        $mform = $this->_form;

        // Capability checks for admin-controlled overrides.
        $coursecontext = context_course::instance($COURSE->id);
        $canoverrideplayer     = has_capability('mod/videotrack:overrideplayersettings', $coursecontext);
        $canoverridecompleting = has_capability('mod/videotrack:overridecompletionsettings', $coursecontext);

        // Read defaults configured by the site administrator.
        $cfg = function (string $key, $fallback) {
            $val = get_config('mod_videotrack', $key);
            return ($val !== false && $val !== '') ? $val : $fallback;
        };

        $mform->addElement('text', 'name', get_string('videoname', 'mod_videotrack'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        // Video source selector.
        $mform->addElement('header', 'videosourceheader', get_string('videosource', 'mod_videotrack'));

        $sourceoptions = [
            'youtube' => get_string('source:youtube', 'mod_videotrack'),
            'vimeo'   => get_string('source:vimeo', 'mod_videotrack'),
            'upload'  => get_string('source:upload', 'mod_videotrack'),
        ];
        $mform->addElement('select', 'videosource', get_string('videosource', 'mod_videotrack'), $sourceoptions);
        $mform->setType('videosource', PARAM_ALPHA);
        $mform->setDefault('videosource', 'youtube');

        // YouTube URL.
        $mform->addElement('text', 'youtubeurl', get_string('youtubeurl', 'mod_videotrack'), ['size' => '80']);
        $mform->setType('youtubeurl', PARAM_URL);
        $mform->addHelpButton('youtubeurl', 'youtubeurl', 'mod_videotrack');
        $mform->hideIf('youtubeurl', 'videosource', 'neq', 'youtube');

        // Vimeo URL.
        $mform->addElement('text', 'vimeourl', get_string('vimeourl', 'mod_videotrack'), ['size' => '80']);
        $mform->setType('vimeourl', PARAM_URL);
        $mform->addHelpButton('vimeourl', 'vimeourl', 'mod_videotrack');
        $mform->hideIf('vimeourl', 'videosource', 'neq', 'vimeo');

        $mform->addElement(
            'static',
            'externalprovider_notice',
            '',
            html_writer::tag('small', get_string('externalprovider_notice', 'mod_videotrack'), ['class' => 'text-muted form-text'])
        );
        $mform->hideIf('externalprovider_notice', 'videosource', 'eq', 'upload');

        // Upload file.
        $fileoptions = [
            'subdirs'        => false,
            'maxfiles'       => 1,
            'accepted_types' => ['.mp4', '.webm', '.mp3', '.m4v', '.mov', '.aac', '.m4a'],
        ];
        $mform->addElement(
            'filepicker',
            'videofile',
            get_string('videofile', 'mod_videotrack'),
            null,
            $fileoptions
        );
        $mform->setType('videofile', PARAM_INT);
        $mform->addHelpButton('videofile', 'videofile', 'mod_videotrack');
        $mform->addElement(
            'static',
            'videofile_notice',
            '',
            html_writer::tag('small', get_string('videofile_notice', 'mod_videotrack'), ['class' => 'text-muted form-text'])
        );
        $mform->hideIf('videofile', 'videosource', 'neq', 'upload');
        // Poster / preview image for all video sources.
        $posteropt = ['subdirs' => false, 'maxfiles' => 1,
            'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif']];
        $mform->addElement(
            'filepicker',
            'posterimage',
            get_string('posterimage', 'mod_videotrack'),
            null,
            $posteropt
        );
        $mform->setType('posterimage', PARAM_INT);
        $mform->addHelpButton('posterimage', 'posterimage', 'mod_videotrack');
        $mform->addElement(
            'static',
            'posterimage_notice',
            '',
            html_writer::tag('small', get_string('posterimage_notice', 'mod_videotrack'), ['class' => 'text-muted form-text'])
        );

        $mform->hideIf('videofile_notice', 'videosource', 'neq', 'upload');

        \mod_videotrack\local\form_definition::add_duration_elements($mform, $PAGE);

        // Player settings locked when teacher lacks overrideplayersettings.
        if (!$canoverrideplayer) {
            $mform->addElement(
                'static',
                'playersettings_locked_notice',
                '',
                html_writer::tag('div', get_string('setting:lockedbyAdmin', 'mod_videotrack'), ['class' => 'alert alert-info'])
            );
        }

        $mform->addElement('advcheckbox', 'showcontrols', get_string('showcontrols', 'mod_videotrack'));

        $mform->setType('showcontrols', PARAM_BOOL);
        $mform->setDefault('showcontrols', (int)(bool)$cfg('default_showcontrols', 1));

        $mform->addElement('advcheckbox', 'disablekeyboard', get_string('disablekeyboard', 'mod_videotrack'));

        $mform->setType('disablekeyboard', PARAM_BOOL);
        $mform->setDefault('disablekeyboard', (int)(bool)$cfg('default_disablekeyboard', 0));

        $mform->addElement('advcheckbox', 'showfullscreen', get_string('showfullscreen', 'mod_videotrack'));

        $mform->setType('showfullscreen', PARAM_BOOL);
        $mform->setDefault('showfullscreen', (int)(bool)$cfg('default_showfullscreen', 1));

        $mform->addElement('advcheckbox', 'allowseekforward', get_string('allowseekforward', 'mod_videotrack'));

        $mform->setType('allowseekforward', PARAM_BOOL);
        $mform->setDefault('allowseekforward', (int)(bool)$cfg('default_allowseekforward', 1));

        $mform->addElement('advcheckbox', 'allowseekbackward', get_string('allowseekbackward', 'mod_videotrack'));

        $mform->setType('allowseekbackward', PARAM_BOOL);
        $mform->setDefault('allowseekbackward', (int)(bool)$cfg('default_allowseekbackward', 1));

        $mform->addElement('advcheckbox', 'allowplaybackratechange', get_string('allowplaybackratechange', 'mod_videotrack'));

        $mform->setType('allowplaybackratechange', PARAM_BOOL);
        $mform->setDefault('allowplaybackratechange', (int)(bool)$cfg('default_allowplaybackratechange', 1));

        // Resume playback from the last saved position.
        $mform->addElement(
            'advcheckbox',
            'resumeplayback',
            get_string('resumeplayback', 'mod_videotrack'),
            get_string('resumeplayback_desc', 'mod_videotrack')
        );

        $mform->setType('resumeplayback', PARAM_BOOL);
        $mform->setDefault('resumeplayback', (int)get_config('mod_videotrack', 'resumeplayback'));
        $mform->addHelpButton('resumeplayback', 'resumeplayback', 'mod_videotrack');

        // Maximum playback rate limit (0 = no limit).
        $maxspeedoptions = [
            0   => get_string('maxplaybackrate_nolimit', 'mod_videotrack'),
            100 => '1×', 125 => '1.25×', 150 => '1.5×', 175 => '1.75×',
            200 => '2×', 300 => '3×', 400 => '4×',
        ];
        $mform->addElement(
            'select',
            'maxplaybackrate',
            get_string('maxplaybackrate', 'mod_videotrack'),
            $maxspeedoptions
        );
        $mform->setType('maxplaybackrate', PARAM_INT);
        $mform->setDefault('maxplaybackrate', (int)get_config('mod_videotrack', 'maxplaybackrate'));
        $mform->addHelpButton('maxplaybackrate', 'maxplaybackrate', 'mod_videotrack');

        $blockedseekrateoptions = [
            50 => '0.5×',
            75 => '0.75×',
            100 => '1×',
        ];
        $mform->addElement(
            'select',
            'blockedseekplaybackrate',
            get_string('blockedseekplaybackrate', 'mod_videotrack'),
            $blockedseekrateoptions
        );
        $mform->setType('blockedseekplaybackrate', PARAM_INT);
        $mform->setDefault('blockedseekplaybackrate', 50);
        $mform->addHelpButton('blockedseekplaybackrate', 'blockedseekplaybackrate', 'mod_videotrack');

        // Lock player fields if the teacher does not have the override capability.
        if (!$canoverrideplayer) {
            $lockedfields = [
                'showcontrols',
                'disablekeyboard',
                'showfullscreen',
                'allowseekforward',
                'allowseekbackward',
                'allowplaybackratechange',
                'blockedseekplaybackrate',
            ];
            foreach ($lockedfields as $field) {
                $mform->freeze($field);
            }
        }

        \mod_videotrack\local\form_definition::add_player_behavior_elements($mform, $coursecontext);

        \mod_videotrack\local\form_definition::add_caption_elements($mform, $coursecontext);

        \mod_videotrack\local\form_definition::add_html5_control_elements($mform, $coursecontext);

        // YouTube embed extras (iv_load_policy, rel — always applied).
        // No user-facing control needed: we always set rel=0 and iv_load_policy=3.
        \mod_videotrack\local\form_definition::add_playback_speed_elements($mform, $coursecontext);

        $mform->addElement('advcheckbox', 'countbyvideotime', get_string('countbyvideotime', 'mod_videotrack'));

        $mform->setType('countbyvideotime', PARAM_BOOL);
        $mform->addHelpButton('countbyvideotime', 'countbyvideotime', 'mod_videotrack');
        $mform->setDefault('countbyvideotime', 1);

        \mod_videotrack\local\form_definition::add_csv_elements(
            $mform,
            $this->context ?: $coursecontext
        );

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

        $mform->addElement('advcheckbox', 'showreactionnotice', get_string('showreactionnotice', 'mod_videotrack'));
        $mform->setType('showreactionnotice', PARAM_BOOL);
        $mform->setDefault('showreactionnotice', 1);
        $mform->disabledIf('showreactionnotice', 'reactionsenabled', 'notchecked');
        $reactionnoticeoptions = [
            'context' => $this->context,
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

        $this->add_reaction_elements();
        if (!$canoverridecompleting) {
            foreach (['reactionsrequired', 'minreactions', 'requireallreactiontypes'] as $field) {
                $mform->freeze($field);
            }
            for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
                $mform->freeze('reactionrequired[' . $i . ']');
            }
        }

        \mod_videotrack\local\form_definition::add_forum_elements(
            $mform,
            (int)$COURSE->id,
            $this->current ?? null
        );

        // Personal study tools.
        $mform->addElement(
            'header',
            'personalstudytoolsheader',
            get_string('personalstudytoolsheader', 'mod_videotrack')
        );
        $mform->addElement(
            'static',
            'personalstudytoolsintro',
            '',
            get_string('personalstudytoolsintro', 'mod_videotrack')
        );

        // Student personal notes.
        $mform->addElement(
            'advcheckbox',
            'studentnotesenabled',
            get_string('studentnotesenabled', 'mod_videotrack'),
            get_string('studentnotesenabled_desc', 'mod_videotrack')
        );

        $mform->setType('studentnotesenabled', PARAM_BOOL);
        $mform->setDefault('studentnotesenabled', (int)get_config('mod_videotrack', 'studentnotesenabled'));
        $mform->addHelpButton('studentnotesenabled', 'studentnotesenabled', 'mod_videotrack');
        $mform->addElement(
            'advcheckbox',
            'bookmarksenabled',
            get_string('bookmarksenabled', 'mod_videotrack'),
            get_string('bookmarksenabled_desc', 'mod_videotrack')
        );
        $mform->setType('bookmarksenabled', PARAM_BOOL);
        $mform->setDefault('bookmarksenabled', (int)get_config('mod_videotrack', 'bookmarksenabled'));
        $mform->addHelpButton('bookmarksenabled', 'bookmarksenabled', 'mod_videotrack');

        \mod_videotrack\local\form_definition::add_integrity_elements($mform);

        \mod_videotrack\local\form_definition::add_acknowledgement_elements(
            $mform,
            $this->context
        );

        // Grading section using standard Moodle grading elements.
        $this->standard_grading_coursemodule_elements();

        // The standard grading elements already add and configure gradepass.

        // Show the grade to the student in view.php.
        $mform->addElement(
            'advcheckbox',
            'showgradeto',
            get_string('showgradeto', 'mod_videotrack')
        );

        $mform->setType('showgradeto', PARAM_BOOL);
        $mform->setDefault('showgradeto', 0);
        $mform->disabledIf('showgradeto', 'grade[modgrade_type]', 'eq', 'none');

        // Register the AMD module that pre-populates reactions when the preset changes.
        videotrack_require_preset_amd($this->reactionrepeatcount ?: 4);

        $this->standard_coursemodule_elements();
        $this->apply_default_section_expansion();
        $this->add_action_buttons();
    }

    /**
     * Applies the default collapsed state to the instance configuration sections.
     *
     * The video source remains open so the primary activity input is immediately visible.
     */
    protected function apply_default_section_expansion(): void {
        $mform = $this->_form;
        $headers = [
            'videosourceheader' => true,
            'playerbehaviorheader' => false,
            'captionsheader' => false,
            'html5controlsheader' => false,
            'playbackspeedsheader' => false,
            'csvexportheader' => false,
            'forumlinkheader' => false,
            'reactionsheader' => false,
            'personalstudytoolsheader' => false,
            'integritysettingsheader' => false,
            'acknowledgementheader' => false,
            'modoutcomes' => false,
            'modstandardgrade' => false,
            'modstandardelshdr' => false,
            'availabilityconditionsheader' => false,
            'activitycompletionheader' => false,
            'tagshdr' => false,
            'modstandardratings' => false,
        ];
        foreach ($headers as $header => $expanded) {
            if ($mform->elementExists($header)) {
                $mform->setExpanded($header, $expanded);
            }
        }
    }

    /**
     * Adds a client-side accept attribute to repository upload inputs.
     *
     * Moodle validates filemanager accepted_types on submit and upload, but the
     * repository upload dialog does not add an HTML accept attribute to its file
     * input. This small enhancement mirrors the accepted_types hidden fields into
     * the browser file chooser, so teachers see only the expected file types.
     */
    protected function require_filepicker_accept_filter(): void {
        global $PAGE;

        $PAGE->requires->js_init_code(<<<'JS'
(function() {
    'use strict';

    var applyAcceptFilter = function(root) {
        var inputs = (root || document).querySelectorAll('input[type="file"][name="repo_upload_file"]');
        inputs.forEach(function(input) {
            var form = input.closest('form');
            if (!form) {
                return;
            }
            var values = Array.prototype.map.call(
                form.querySelectorAll('input[name="accepted_types[]"]'),
                function(node) {
                    return (node.value || '').trim();
                }
            ).filter(Boolean);
            if (!values.length || values.indexOf('*') !== -1) {
                return;
            }
            input.setAttribute('accept', values.join(','));
        });
    };

    applyAcceptFilter(document);
    if (window.MutationObserver && document.body) {
        new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        applyAcceptFilter(node);
                    }
                });
            });
        }).observe(document.body, {childList: true, subtree: true});
    }
}());
JS);
    }

    /**
     * Adds repeated form elements used to configure reaction buttons.
     */
    protected function add_reaction_elements(): void {
        $mform = $this->_form;
        $repeatcount = $this->get_reaction_repeat_count();
        $this->reactionrepeatcount = $repeatcount;
        $this->require_filepicker_accept_filter();

        $mform->addElement('hidden', 'reaction_repeats', $repeatcount);
        $mform->setType('reaction_repeats', PARAM_INT);

        $reactionimagefiletypes = ['.jpg', '.jpeg', '.png', '.webp', '.gif'];
        $options = [
            'subdirs'        => 0,
            'maxfiles'       => 1,
            'accepted_types' => $reactionimagefiletypes,
            'maxbytes'       => 0,
            'return_types'   => FILE_INTERNAL,
        ];

        for ($i = 0; $i < $repeatcount; $i++) {
            $this->add_reaction_row_fields($i, $options);
            $this->add_reaction_row_visibility($i);
        }

        if ($repeatcount < self::MAX_REACTION_ROWS) {
            $mform->registerNoSubmitButton('reaction_add_fields');
            $mform->addElement(
                'submit',
                'reaction_add_fields',
                get_string('addreaction', 'mod_videotrack')
            );
        }
    }

    /**
     * Add one reaction's editable fields in their current form order.
     *
     * @param int $i Zero-based reaction row index.
     * @param array $options File manager options for reaction icons.
     * @return void
     */
    private function add_reaction_row_fields(int $i, array $options): void {
        $mform = $this->_form;
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
    }

    /**
     * Apply the existing icon input visibility rules after adding the row fields.
     *
     * @param int $i Zero-based reaction row index.
     * @return void
     */
    private function add_reaction_row_visibility(int $i): void {
        $mform = $this->_form;
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
     * Returns the number of reaction rows to render in the form.
     *
     * @return int Number of reaction rows.
     */
    protected function get_reaction_repeat_count(): int {
        global $DB;

        $count = optional_param('reaction_repeats', 0, PARAM_INT);
        if ($count <= 0) {
            $activecount = 0;
            if (!empty($this->_instance)) {
                $activecount = (int)$DB->count_records(
                    'videotrack_react',
                    ['videotrackid' => $this->_instance, 'isdeleted' => 0]
                );
            }
            // Always show several editable rows and one spare row when existing
            // definitions already fill the initial set. The Add reaction button
            // can continue expanding the form up to the explicit safety cap.
            $count = max(self::MIN_REACTION_ROWS, $activecount + 1);
        }
        if (optional_param('reaction_add_fields', '', PARAM_ALPHANUMEXT) !== '') {
            $count++;
        }
        return min(max($count, self::MIN_REACTION_ROWS), self::MAX_REACTION_ROWS);
    }

    /**
     * Adds VideoTrack-specific completion rules.
     *
     * @return array List of completion rule element names.
     */
    public function add_completion_rules() {
        global $COURSE;
        $mform = $this->_form;
        $coursecontext = context_course::instance($COURSE->id);
        $canoverride = has_capability('mod/videotrack:overridecompletionsettings', $coursecontext);
        $defaultpercent = (int)(get_config('mod_videotrack', 'default_completionpercent') ?: 0);
        $suffix = $this->get_suffix();
        $completionpercent = 'completionpercent' . $suffix;
        $completionpercentgroup = 'completionpercentgroup' . $suffix;
        $completionacknowledgement = 'completionacknowledgement' . $suffix;
        $completionlogic = 'completionlogic' . $suffix;
        $completionreactionrules = 'completionreactionrules' . $suffix;

        $group = [];
        $group[] = $mform->createElement('text', $completionpercent, '', ['size' => 3]);
        $mform->setType($completionpercent, PARAM_INT);
        $mform->setDefault($completionpercent, $defaultpercent);
        $mform->addGroup(
            $group,
            $completionpercentgroup,
            get_string('completionpercent', 'mod_videotrack'),
            ' ',
            false
        );

        $mform->addElement(
            'advcheckbox',
            $completionacknowledgement,
            get_string('completionacknowledgement', 'mod_videotrack'),
            get_string('completionacknowledgement_desc', 'mod_videotrack')
        );
        $mform->setType($completionacknowledgement, PARAM_BOOL);
        $mform->setDefault($completionacknowledgement, 0);
        $mform->disabledIf($completionacknowledgement, 'acknowledgementenabled', 'notchecked');

        $mform->addElement('select', $completionlogic, get_string('completionlogic', 'mod_videotrack'), [
            'and' => get_string('logicand', 'mod_videotrack'),
            'or' => get_string('logicor', 'mod_videotrack'),
        ]);
        $mform->setType($completionlogic, PARAM_ALPHA);
        $mform->setDefault($completionlogic, 'and');

        $mform->addElement(
            'static',
            $completionreactionrules,
            '',
            get_string('completionreactionrules_desc', 'mod_videotrack')
        );

        if (!$canoverride) {
            $mform->freeze($completionpercentgroup);
            $mform->freeze($completionacknowledgement);
            $mform->freeze($completionlogic);
        }

        return [
            $completionpercentgroup,
            $completionacknowledgement,
            $completionlogic,
            $completionreactionrules,
        ];
    }

    /**
     * Checks whether the custom completion rule is enabled.
     *
     * @param array $data Submitted form data.
     * @return bool True when at least one custom completion condition is active.
     */
    public function completion_rule_enabled($data) {
        return \mod_videotrack\local\form_validation::completion_rule_enabled(
            $data,
            $this->get_suffix()
        );
    }

    /**
     * Prepares default values and draft areas before the form is displayed.
     *
     * @param array $defaultvalues Default values passed by reference.
     */
    public function data_preprocessing(&$defaultvalues) {
        parent::data_preprocessing($defaultvalues);

        \mod_videotrack\local\form_preprocessing::reaction_defaults(
            $defaultvalues,
            (int)$this->_instance,
            $this->reactionrepeatcount
        );
        \mod_videotrack\local\form_preprocessing::completion_defaults(
            $defaultvalues,
            $this->get_suffix(),
            (int)$this->_instance
        );
        \mod_videotrack\local\form_preprocessing::playback_defaults($defaultvalues);
        \mod_videotrack\local\form_preprocessing::csv_defaults($defaultvalues, $this->context);
        \mod_videotrack\local\form_preprocessing::site_defaults($defaultvalues);

        // Prepare timed-text draft areas for every provider and upload-only media areas when needed.
        if (!empty($this->_instance)) {
            $cmtimedtext = get_coursemodule_from_instance(
                'videotrack',
                $this->_instance,
                0,
                false,
                IGNORE_MISSING
            );
            if ($cmtimedtext) {
                $timedtextcontext = context_module::instance($cmtimedtext->id);
                $transcriptdraft = file_get_submitted_draft_itemid('transcriptfiles');
                file_prepare_draft_area(
                    $transcriptdraft,
                    $timedtextcontext->id,
                    'mod_videotrack',
                    'transcripts',
                    0,
                    \mod_videotrack\local\timed_text::file_options(
                        \mod_videotrack\local\timed_text::MAX_TRANSCRIPT_FILES
                    )
                );
                $defaultvalues['transcriptfiles'] = $transcriptdraft;

                $chapterdraft = file_get_submitted_draft_itemid('chapterfile');
                file_prepare_draft_area(
                    $chapterdraft,
                    $timedtextcontext->id,
                    'mod_videotrack',
                    'chapters',
                    0,
                    \mod_videotrack\local\timed_text::file_options(1)
                );
                $defaultvalues['chapterfile'] = $chapterdraft;

                if (($defaultvalues['videosource'] ?? 'youtube') === 'upload') {
                    $subtitleid = file_get_submitted_draft_itemid('vttfile');
                    file_prepare_draft_area(
                        $subtitleid,
                        $timedtextcontext->id,
                        'mod_videotrack',
                        'subtitles',
                        0,
                        \mod_videotrack\local\timed_text::file_options(1)
                    );
                    $defaultvalues['vttfile'] = $subtitleid;

                    $videodraft = file_get_submitted_draft_itemid('videofile');
                    file_prepare_draft_area($videodraft, $timedtextcontext->id, 'mod_videotrack', 'videocontent', 0, [
                        'subdirs' => false,
                        'maxfiles' => 1,
                        'accepted_types' => ['.mp4', '.webm', '.mp3', '.m4v', '.mov', '.aac', '.m4a'],
                    ]);
                    $defaultvalues['videofile'] = $videodraft;
                }
            }
        }
        \mod_videotrack\local\form_preprocessing::reaction_notice_defaults($defaultvalues);

        // Prepare the poster image draft area for all video sources.
        if (!empty($this->_instance)) {
            $cmforposter = get_coursemodule_from_instance('videotrack', $this->_instance, 0, false, IGNORE_MISSING);
            if ($cmforposter) {
                $ctxposter    = context_module::instance($cmforposter->id);
                $draftposter  = file_get_submitted_draft_itemid('posterimage');
                file_prepare_draft_area($draftposter, $ctxposter->id, 'mod_videotrack', 'posterimage', 0, [
                    'subdirs'        => false,
                    'maxfiles'       => 1,
                    'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
                ]);
                $defaultvalues['posterimage'] = $draftposter;
            }
        }
    }

    /**
     * Checks that a reaction icon draft area contains only allowed image files.
     *
     * @param int $draftitemid Draft item id.
     * @param array $allowedextensions Allowed lowercase extensions without leading dots.
     * @return bool True when every file in the draft area is an allowed image.
     */
    protected function draft_area_contains_only_reaction_images(int $draftitemid, array $allowedextensions): bool {
        global $USER;

        if ($draftitemid <= 0) {
            return true;
        }

        $usercontext = context_user::instance($USER->id);
        $files = get_file_storage()->get_area_files(
            $usercontext->id,
            'user',
            'draft',
            $draftitemid,
            'id',
            false
        );

        foreach ($files as $file) {
            $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedextensions, true)) {
                return false;
            }
            if (strpos((string)$file->get_mimetype(), 'image/') !== 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check that a draft area contains only valid WebVTT files.
     *
     * @param int $draftitemid Draft item id.
     * @return bool True when all files are valid WebVTT payloads.
     */
    protected function draft_area_contains_only_vtt(int $draftitemid): bool {
        global $USER;

        if ($draftitemid <= 0) {
            return true;
        }
        $files = get_file_storage()->get_area_files(
            context_user::instance($USER->id)->id,
            'user',
            'draft',
            $draftitemid,
            'id ASC',
            false
        );
        foreach ($files as $file) {
            if (core_text::strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION)) !== 'vtt') {
                return false;
            }
            if (!\mod_videotrack\local\timed_text::is_valid_vtt_content($file->get_content())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks the selected video source and its required media.
     *
     * @param array $data Submitted form data.
     * @return array Source validation errors.
     */
    protected function source_errors(array $data): array {
        $source = $data['videosource'] ?? 'youtube';
        if ($source === 'youtube') {
            return self::youtube_source_errors($data);
        }
        if ($source === 'vimeo') {
            return self::vimeo_source_errors($data);
        }
        if ($source === 'upload') {
            return self::upload_source_errors($data);
        }
        return ['videosource' => get_string('invalidvideosource', 'mod_videotrack')];
    }

    /**
     * Checks the required YouTube URL.
     *
     * @param array $data Submitted form data.
     * @return array YouTube validation errors.
     */
    private static function youtube_source_errors(array $data): array {
        if (empty($data['youtubeurl']) || !videotrack_extract_videoid((string)$data['youtubeurl'])) {
            return ['youtubeurl' => get_string('invalidyoutubeurl', 'mod_videotrack')];
        }
        return [];
    }

    /**
     * Checks the required Vimeo URL.
     *
     * @param array $data Submitted form data.
     * @return array Vimeo validation errors.
     */
    private static function vimeo_source_errors(array $data): array {
        if (empty($data['vimeourl']) || !videotrack_extract_vimeo_id((string)$data['vimeourl'])) {
            return ['vimeourl' => get_string('invalidvimeourl', 'mod_videotrack')];
        }
        return [];
    }

    /**
     * Checks a new upload while preserving the file on edit.
     *
     * @param array $data Submitted form data.
     * @return array Upload validation errors.
     */
    private static function upload_source_errors(array $data): array {
        // For new activities, the video file is required.
        // For edits, the existing file is kept even when the file picker is empty.
        $draftitemid = (int)($data['videofile'] ?? 0);
        $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
        $isnew       = empty($data['instance']); // The instance value is zero for a new activity.
        if ($isnew && empty($fileinfo['filecount'])) {
            return ['videofile' => get_string('required')];
        }
        return [];
    }

    /**
     * Checks draft WebVTT files and enabled timed-text options.
     *
     * @param array $data Submitted form data.
     * @param string $source Selected video source.
     * @return array Timed-text validation errors.
     */
    protected function timed_text_errors(array $data, string $source): array {
        $transcriptdraft = (int)($data['transcriptfiles'] ?? 0);
        $chapterdraft = (int)($data['chapterfile'] ?? 0);
        $subtitleid = (int)($data['vttfile'] ?? 0);
        $transcriptinfo = $transcriptdraft > 0 ? file_get_draft_area_info($transcriptdraft) : [];
        $chapterinfo = $chapterdraft > 0 ? file_get_draft_area_info($chapterdraft) : [];
        $subtitleinfo = $subtitleid > 0 ? file_get_draft_area_info($subtitleid) : [];
        $haslegacytimedtext = $source === 'upload' && !empty($data['captions']) && !empty($subtitleinfo['filecount']);

        $errors = $this->timed_text_draft_errors($transcriptdraft, $chapterdraft, $transcriptinfo, $chapterinfo);
        return array_replace(
            $errors,
            self::timed_text_required_errors($data, $transcriptinfo, $chapterinfo, $haslegacytimedtext)
        );
    }

    /**
     * Checks the content types in populated transcript and chapter drafts.
     *
     * @param int $transcriptdraft Transcript draft item id.
     * @param int $chapterdraft Chapter draft item id.
     * @param array $transcriptinfo Transcript draft information.
     * @param array $chapterinfo Chapter draft information.
     * @return array Invalid draft errors.
     */
    private function timed_text_draft_errors(
        int $transcriptdraft,
        int $chapterdraft,
        array $transcriptinfo,
        array $chapterinfo
    ): array {
        $errors = [];
        if (!empty($transcriptinfo['filecount']) && !$this->draft_area_contains_only_vtt($transcriptdraft)) {
            $errors['transcriptfiles'] = get_string('err:invalidvttfile', 'mod_videotrack');
        }
        if (!empty($chapterinfo['filecount']) && !$this->draft_area_contains_only_vtt($chapterdraft)) {
            $errors['chapterfile'] = get_string('err:invalidvttfile', 'mod_videotrack');
        }
        return $errors;
    }

    /**
     * Requires a timed-text file when its activity option is enabled.
     *
     * @param array $data Submitted form data.
     * @param array $transcriptinfo Transcript draft information.
     * @param array $chapterinfo Chapter draft information.
     * @param bool $haslegacytimedtext Whether a legacy upload VTT can satisfy the requirement.
     * @return array Missing-file errors.
     */
    private static function timed_text_required_errors(
        array $data,
        array $transcriptinfo,
        array $chapterinfo,
        bool $haslegacytimedtext
    ): array {
        $errors = [];
        if (!empty($data['showtranscript']) && empty($transcriptinfo['filecount']) && !$haslegacytimedtext) {
            $errors['transcriptfiles'] = get_string('err:transcriptfilerequired', 'mod_videotrack');
        }
        if (!empty($data['showchapters']) && empty($chapterinfo['filecount']) && !$haslegacytimedtext) {
            $errors['chapterfile'] = get_string('err:chapterfilerequired', 'mod_videotrack');
        }

        return $errors;
    }

    /**
     * Validates a reaction icon file, including an existing icon during edits.
     *
     * @param array $data Submitted form data.
     * @param int $i Reaction row index.
     * @param int $reactionid Existing reaction id.
     * @param array $allowedimageextensions Image extensions allowed in a draft.
     * @param bool $reactionsenabled Whether reactions are enabled.
     * @param bool $rowhastext Whether this row contains a label or description.
     * @return array Icon presence and file validation errors.
     */
    protected function reaction_file_icon_errors(
        array $data,
        int $i,
        int $reactionid,
        array $allowedimageextensions,
        bool $reactionsenabled,
        bool $rowhastext
    ): array {
        $errors = [];
        $draftitemid = (int)($data['reactioniconfile_' . $i] ?? 0);
        $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
        $hasfile     = !empty($fileinfo['filecount']);

        if (!$hasfile) {
            $hasfile = $this->existing_reaction_icon_file($reactionid);
        }

        $hasicon = $hasfile;
        $hasvalidimagefile = $this->draft_area_contains_only_reaction_images(
            $draftitemid,
            $allowedimageextensions
        );
        if ($reactionsenabled && $rowhastext && !$hasfile) {
            $errors['reactioniconfile_' . $i] =
                get_string('err:reactioniconfilerequired', 'mod_videotrack');
        } else if ($hasfile && !$hasvalidimagefile) {
            $errors['reactioniconfile_' . $i] =
                get_string('err:reactioniconfileinvalid', 'mod_videotrack');
        }
        return ['hasicon' => $hasicon, 'errors' => $errors];
    }

    /**
     * Keeps an existing icon when an edit has no populated draft area.
     *
     * @param int $reactionid Existing reaction id.
     * @return bool Whether the reaction has a stored icon.
     */
    protected function existing_reaction_icon_file(int $reactionid): bool {
        if ($reactionid <= 0 || empty($this->_instance)) {
            return false;
        }
        $cm = get_coursemodule_from_instance('videotrack', $this->_instance, 0, false, IGNORE_MISSING);
        if (!$cm) {
            return false;
        }
        $context = context_module::instance($cm->id);
        return !get_file_storage()->is_area_empty(
            $context->id,
            'mod_videotrack',
            'reactionicon',
            $reactionid
        );
    }

    /**
     * Checks the selected forum when forum posting is enabled.
     *
     * @param array $data Submitted form data.
     * @return array Forum validation errors.
     */
    protected function forum_errors(array $data): array {
        global $COURSE;
        $errors = [];
        if (!empty($data['forumpostingenabled'])) {
            $forumid = isset($data['linkedforumid']) ? (int)$data['linkedforumid'] : 0;
            if ($forumid <= 0) {
                $errors['linkedforumid'] = get_string('forum:errorforumrequired', 'mod_videotrack');
            } else if (!videotrack_is_compatible_forum((int)$COURSE->id, $forumid)) {
                $errors['linkedforumid'] = get_string('forum:errorinvaliddestination', 'mod_videotrack');
            }
        }

        return $errors;
    }

    /**
     * Validates the repeated reaction rows and the enabled configuration.
     *
     * @param array $data Submitted form data.
     * @return array Validation errors indexed by form element name.
     */
    protected function reaction_errors(array $data): array {
        $errors = [];
        $configuredreactioncount = 0;
        for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
            $row = $this->reaction_row_errors($data, $i);
            $errors = array_replace($errors, $row['errors']);
            if ($row['configured']) {
                $configuredreactioncount++;
            }
        }
        if (!empty($data['reactionsenabled']) && $configuredreactioncount === 0) {
            $errors['reactionsenabled'] = get_string('err:reactionconfigurationrequired', 'mod_videotrack');
        }

        return $errors;
    }

    /**
     * Validates the icon and required fields of one repeated reaction.
     *
     * @param array $data Submitted form data.
     * @param int $i Reaction row index.
     * @return array Errors and whether this row configures a reaction.
     */
    private function reaction_row_errors(array $data, int $i): array {
        $label = trim((string)($data['reactionlabel'][$i] ?? ''));
        $description = trim((string)($data['reactiondescription'][$i] ?? ''));
        $type = (string)($data['reactionicontype'][$i] ?? '');
        $rowhastext = $label !== '' || $description !== '';
        $reactionsenabled = !empty($data['reactionsenabled']);

        if (!in_array($type, ['emoji', 'fa', 'file'], true)) {
            $errors = [];
            if ($reactionsenabled && $rowhastext) {
                $errors['reactionicontype[' . $i . ']'] = get_string('err:reactionicontyperequired', 'mod_videotrack');
            }
            return ['errors' => $errors, 'configured' => false];
        }

        if ($type === 'file') {
            $icon = $this->reaction_file_icon_errors(
                $data,
                $i,
                (int)($data['reactionid'][$i] ?? 0),
                ['jpg', 'jpeg', 'png', 'webp', 'gif'],
                $reactionsenabled,
                $rowhastext
            );
        } else {
            $icon = \mod_videotrack\local\reaction_form_validation::reaction_text_icon_errors(
                $data,
                $i,
                $type,
                $reactionsenabled,
                $rowhastext
            );
        }

        $details = \mod_videotrack\local\reaction_form_validation::reaction_details_errors(
            $label,
            $description,
            $reactionsenabled,
            $icon['hasicon'],
            $i
        );
        return [
            'errors' => array_replace($icon['errors'], $details['errors']),
            'configured' => $details['configured'],
        ];
    }

    /**
     * Validates submitted activity settings.
     *
     * @param array $data Submitted form data.
     * @param array $files Submitted files.
     * @return array Validation errors indexed by form element name.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $source = $data['videosource'] ?? 'youtube';
        $errors = array_replace($errors, $this->source_errors($data));
        $errors = array_replace($errors, $this->timed_text_errors($data, $source));

        $errors = array_replace(
            $errors,
            \mod_videotrack\local\form_validation::scalar_settings_errors($data, $this->get_suffix())
        );

        $errors = array_replace($errors, $this->reaction_errors($data));

        $errors = array_replace(
            $errors,
            \mod_videotrack\local\form_validation::acknowledgement_errors($data)
        );

        $errors = array_replace($errors, $this->forum_errors($data));

        $errors = array_replace(
            $errors,
            \mod_videotrack\local\form_validation::duration_errors($data)
        );

        return $errors;
    }
}
