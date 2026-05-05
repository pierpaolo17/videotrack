<?php

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php');

class mod_videotrack_mod_form extends moodleform_mod {
    /** @var int */
    protected $reactionrepeatcount = 0;

    public function definition() {
        global $COURSE;
        $mform = $this->_form;

        // Capability checks for admin-controlled overrides.
        $coursecontext = context_course::instance($COURSE->id);
        $canoverrideplayer     = has_capability('mod/videotrack:overrideplayersettings', $coursecontext);
        $canoverridecompleting = has_capability('mod/videotrack:overridecompletionsettings', $coursecontext);

        // Legge i default configurati dall'amministratore di piattaforma.
        $cfg = function(string $key, $fallback) {
            $val = get_config('mod_videotrack', $key);
            return ($val !== false && $val !== '') ? $val : $fallback;
        };

        $mform->addElement('text', 'name', get_string('videoname', 'mod_videotrack'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements();

        // ----------------------------------------------------------------
        // Video source selector.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'videosourceheader', get_string('videosource', 'mod_videotrack'));

        $sourceoptions = [
            'youtube' => get_string('source:youtube', 'mod_videotrack'),
            'vimeo'   => get_string('source:vimeo',   'mod_videotrack'),
            'upload'  => get_string('source:upload',  'mod_videotrack'),
        ];
        $mform->addElement('select', 'videosource', get_string('videosource', 'mod_videotrack'), $sourceoptions);
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

        // Upload file.
        $fileoptions = [
            'subdirs'        => false,
            'maxfiles'       => 1,
            'accepted_types' => ['.mp4', '.webm', '.mp3', '.m4v', '.mov', '.aac', '.m4a'],
        ];
        $mform->addElement('filepicker', 'videofile',
            get_string('videofile', 'mod_videotrack'), null, $fileoptions);
        $mform->addHelpButton('videofile', 'videofile', 'mod_videotrack');
        $mform->addElement('static', 'videofile_notice', '',
            html_writer::tag('small',
                get_string('videofile_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );
        $mform->hideIf('videofile', 'videosource', 'neq', 'upload');
        // ----------------------------------------------------------------
        // Poster / preview image (all video sources).
        // ----------------------------------------------------------------
        $posteropt = ['subdirs' => false, 'maxfiles' => 1,
            'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif']];
        $mform->addElement('filepicker', 'posterimage',
            get_string('posterimage', 'mod_videotrack'), null, $posteropt);
        $mform->addHelpButton('posterimage', 'posterimage', 'mod_videotrack');
        $mform->addElement('static', 'posterimage_notice', '',
            html_writer::tag('small',
                get_string('posterimage_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );

        $mform->hideIf('videofile_notice', 'videosource', 'neq', 'upload');

        // ----------------------------------------------------------------
        // Player settings — locked if teacher lacks overrideplayersettings.
        // ----------------------------------------------------------------
        if (!$canoverrideplayer) {
            $mform->addElement('static', 'playersettings_locked_notice', '',
                html_writer::tag('div',
                    get_string('setting:lockedbyAdmin', 'mod_videotrack'),
                    ['class' => 'alert alert-info']
                )
            );
        }

        $mform->addElement('advcheckbox', 'showcontrols', get_string('showcontrols', 'mod_videotrack'));
        $mform->setDefault('showcontrols', (int)(bool)$cfg('default_showcontrols', 1));

        $mform->addElement('advcheckbox', 'disablekeyboard', get_string('disablekeyboard', 'mod_videotrack'));
        $mform->setDefault('disablekeyboard', (int)(bool)$cfg('default_disablekeyboard', 0));

        $mform->addElement('advcheckbox', 'showfullscreen', get_string('showfullscreen', 'mod_videotrack'));
        $mform->setDefault('showfullscreen', (int)(bool)$cfg('default_showfullscreen', 1));

        $mform->addElement('advcheckbox', 'allowseekforward', get_string('allowseekforward', 'mod_videotrack'));
        $mform->setDefault('allowseekforward', (int)(bool)$cfg('default_allowseekforward', 1));

        $mform->addElement('advcheckbox', 'allowseekbackward', get_string('allowseekbackward', 'mod_videotrack'));
        $mform->setDefault('allowseekbackward', (int)(bool)$cfg('default_allowseekbackward', 1));

        $mform->addElement('advcheckbox', 'allowplaybackratechange', get_string('allowplaybackratechange', 'mod_videotrack'));
        $mform->setDefault('allowplaybackratechange', (int)(bool)$cfg('default_allowplaybackratechange', 1));

        // Resume playback dal punto lasciato dall'ultima sessione.
        $mform->addElement('advcheckbox', 'resumeplayback',
            get_string('resumeplayback', 'mod_videotrack'),
            get_string('resumeplayback_desc', 'mod_videotrack'));
        $mform->setDefault('resumeplayback', (int)get_config('mod_videotrack', 'resumeplayback'));
        $mform->addHelpButton('resumeplayback', 'resumeplayback', 'mod_videotrack');

        // Limite massimo velocità di riproduzione (0 = nessun limite).
        $maxspeedoptions = [
            0   => get_string('maxplaybackrate_nolimit', 'mod_videotrack'),
            125 => '1.25×', 150 => '1.5×', 175 => '1.75×',
            200 => '2×',    300 => '3×',   400 => '4×',
        ];
        $mform->addElement('select', 'maxplaybackrate',
            get_string('maxplaybackrate', 'mod_videotrack'), $maxspeedoptions);
        $mform->setDefault('maxplaybackrate', (int)get_config('mod_videotrack', 'maxplaybackrate'));
        $mform->addHelpButton('maxplaybackrate', 'maxplaybackrate', 'mod_videotrack');

        // Lock player fields if the teacher does not have the override capability.
        if (!$canoverrideplayer) {
            foreach (['showcontrols', 'disablekeyboard', 'showfullscreen',
                      'allowseekforward', 'allowseekbackward', 'allowplaybackratechange'] as $field) {
                $mform->freeze($field);
            }
        }

        // ----------------------------------------------------------------
        // Additional player behaviour — autoplay, loop, mute, download.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'playerbehaviorheader',
            get_string('setting:heading_playerbehavior', 'mod_videotrack'));

        // Max player width (px). 0 = use site default.
        $mform->addElement('text', 'playerwidth',
            get_string('setting:playerwidth', 'mod_videotrack'), ['size' => 6]);
        $mform->setType('playerwidth', PARAM_INT);
        $mform->setDefault('playerwidth', 0);
        $mform->addHelpButton('playerwidth', 'playerwidth', 'mod_videotrack');

        $mform->addElement('advcheckbox', 'autoplay', get_string('autoplay', 'mod_videotrack'));
        $mform->setDefault('autoplay', (int)(bool)$cfg('default_autoplay', 0));
        $mform->addHelpButton('autoplay', 'autoplay', 'mod_videotrack');

        $mform->addElement('advcheckbox', 'loop', get_string('loop', 'mod_videotrack'));
        $mform->setDefault('loop', (int)(bool)$cfg('default_loop', 0));

        $mform->addElement('advcheckbox', 'startmuted', get_string('startmuted', 'mod_videotrack'));
        $mform->setDefault('startmuted', (int)(bool)$cfg('default_startmuted', 0));
        $mform->addHelpButton('startmuted', 'startmuted', 'mod_videotrack');

        // Rewind step (seconds). 0 = use site default.
        $mform->addElement('text', 'rewindstep',
            get_string('setting:rewindstep', 'mod_videotrack'), ['size' => 4]);
        $mform->setType('rewindstep', PARAM_INT);
        $mform->setDefault('rewindstep', 0);
        $mform->addHelpButton('rewindstep', 'rewindstep', 'mod_videotrack');

        // Fast-forward step (seconds). 0 = use site default.
        $mform->addElement('text', 'fastforwardstep',
            get_string('setting:fastforwardstep', 'mod_videotrack'), ['size' => 4]);
        $mform->setType('fastforwardstep', PARAM_INT);
        $mform->setDefault('fastforwardstep', 0);
        $mform->addHelpButton('fastforwardstep', 'fastforwardstep', 'mod_videotrack');

        // allowdownload: visible only for upload source.
        $mform->addElement('advcheckbox', 'allowdownload', get_string('allowdownload', 'mod_videotrack'));
        $mform->setDefault('allowdownload', (int)(bool)$cfg('default_allowdownload', 0));
        $mform->hideIf('allowdownload', 'videosource', 'neq', 'upload');

        if (!$canoverrideplayer) {
            foreach (['playerwidth', 'autoplay', 'loop', 'startmuted',
                      'rewindstep', 'fastforwardstep', 'allowdownload'] as $field) {
                $mform->freeze($field);
            }
        }

        // ----------------------------------------------------------------
        // Captions / Subtitles.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'captionsheader',
            get_string('captionsheader', 'mod_videotrack'));

        $mform->addElement('advcheckbox', 'captions', get_string('captions', 'mod_videotrack'));
        $mform->setDefault('captions', (int)(bool)$cfg('default_captions', 0));
        $mform->addHelpButton('captions', 'captions', 'mod_videotrack');

        $mform->addElement('text', 'captionslang',
            get_string('captionslang', 'mod_videotrack'), ['size' => 8]);
        $mform->setType('captionslang', PARAM_NOTAGS);
        $mform->setDefault('captionslang', $cfg('default_captionslang', ''));
        $mform->addHelpButton('captionslang', 'captionslang', 'mod_videotrack');
        $mform->disabledIf('captionslang', 'captions', 'notchecked');

        // VTT file upload — upload source only.
        $vttopt = ['subdirs' => false, 'maxfiles' => 1, 'accepted_types' => ['.vtt']];
        $mform->addElement('filepicker', 'vttfile',
            get_string('vttfile', 'mod_videotrack'), null, $vttopt);
        $mform->addHelpButton('vttfile', 'vttfile', 'mod_videotrack');
        $mform->addElement('static', 'vttfile_notice', '',
            html_writer::tag('small',
                get_string('vttfile_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );
        $mform->hideIf('vttfile',        'videosource', 'neq', 'upload');
        $mform->hideIf('vttfile_notice', 'videosource', 'neq', 'upload');
        $mform->hideIf('vttfile',        'captions', 'notchecked');
        $mform->hideIf('vttfile_notice', 'captions', 'notchecked');

        // Feature 8: Transcript VTT interattivo (solo upload + VTT caricato).
        $mform->addElement('advcheckbox', 'showtranscript',
            get_string('showtranscript', 'mod_videotrack'),
            get_string('showtranscript_desc', 'mod_videotrack'));
        $mform->setDefault('showtranscript', 0);
        $mform->addHelpButton('showtranscript', 'showtranscript', 'mod_videotrack');
        $mform->hideIf('showtranscript', 'videosource', 'neq', 'upload');
        $mform->hideIf('showtranscript', 'captions', 'notchecked');

        // Feature 10: Capitoli VTT navigabili (usa lo stesso file VTT).
        $mform->addElement('advcheckbox', 'showchapters',
            get_string('showchapters', 'mod_videotrack'),
            get_string('showchapters_desc', 'mod_videotrack'));
        $mform->setDefault('showchapters', 0);
        $mform->addHelpButton('showchapters', 'showchapters', 'mod_videotrack');
        $mform->hideIf('showchapters', 'videosource', 'neq', 'upload');
        $mform->hideIf('showchapters', 'captions', 'notchecked');

        // Notice for Vimeo: captions must be pre-loaded on Vimeo.com.
        $mform->addElement('static', 'vimeo_captions_notice', '',
            html_writer::tag('div',
                get_string('vimeo_captions_notice', 'mod_videotrack'),
                ['class' => 'alert alert-info']
            )
        );
        $mform->hideIf('vimeo_captions_notice', 'videosource', 'neq', 'vimeo');
        $mform->hideIf('vimeo_captions_notice', 'captions', 'notchecked');

        if (!$canoverrideplayer) {
            $mform->freeze('captions');
            $mform->freeze('captionslang');
        }

        // ----------------------------------------------------------------
        // HTML5 player controls — visible only for upload source.
        // Teacher can choose which controls to show (within site admin limits).
        // ----------------------------------------------------------------
        $sitecontrols     = videotrack_get_html5controls((object)['html5controls' => '']);
        $allctrl          = [
            'play'       => get_string('ctrl:play',       'mod_videotrack'),
            'rewind'     => get_string('ctrl:rewind',     'mod_videotrack'),
            'fastforward'=> get_string('ctrl:fastforward','mod_videotrack'),
            'progress'   => get_string('ctrl:progress',   'mod_videotrack'),
            'current'    => get_string('ctrl:current',    'mod_videotrack'),
            'duration'   => get_string('ctrl:duration',   'mod_videotrack'),
            'mute'       => get_string('ctrl:mute',       'mod_videotrack'),
            'volume'     => get_string('ctrl:volume',     'mod_videotrack'),
            'speed'      => get_string('ctrl:speed',      'mod_videotrack'),
            'pip'        => get_string('ctrl:pip',        'mod_videotrack'),
            'fullscreen' => get_string('ctrl:fullscreen', 'mod_videotrack'),
            'download'   => get_string('ctrl:download',   'mod_videotrack'),
        ];
        // Only show controls that admin enabled at site level.
        $availablectrl = array_filter($allctrl,
            fn($k) => in_array($k, $sitecontrols), ARRAY_FILTER_USE_KEY);

        if (!empty($availablectrl)) {
            $mform->addElement('header', 'html5controlsheader',
                get_string('setting:heading_html5controls', 'mod_videotrack'));
            $mform->hideIf('html5controlsheader', 'videosource', 'neq', 'upload');

            $mform->addElement('static', 'html5controls_desc', '',
                html_writer::tag('small',
                    get_string('setting:html5controls_teacher_desc', 'mod_videotrack'),
                    ['class' => 'text-muted']
                )
            );
            $mform->hideIf('html5controls_desc', 'videosource', 'neq', 'upload');

            $ctrlgroup = [];
            foreach ($availablectrl as $key => $label) {
                $ctrlgroup[] = $mform->createElement(
                    'advcheckbox', "html5ctrl_{$key}", '', $label, [], [0, $key]
                );
                $mform->setType("html5ctrl_{$key}", PARAM_TEXT);
                $mform->hideIf("html5ctrl_{$key}", 'videosource', 'neq', 'upload');
            }
            $mform->addGroup($ctrlgroup, 'html5controlsgroup',
                get_string('setting:html5controls', 'mod_videotrack'), ' ', false);
            $mform->hideIf('html5controlsgroup', 'videosource', 'neq', 'upload');

            if (!$canoverrideplayer) {
                $mform->freeze('html5controlsgroup');
            }
        }

        // YouTube embed extras (iv_load_policy, rel — always applied).
        // No user-facing control needed: we always set rel=0 and iv_load_policy=3.
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
            $mform->addElement('header', 'playbackspeedsheader',
                get_string('setting:playbackspeeds', 'mod_videotrack'));
            $mform->addElement('static', 'playbackspeeds_desc', '',
                html_writer::tag('small',
                    get_string('setting:playbackspeeds_teacher_desc', 'mod_videotrack'),
                    ['class' => 'text-muted']
                )
            );
            $checkboxgroup = [];
            foreach ($availablespeeds as $val => $label) {
                $checkboxgroup[] = $mform->createElement(
                    'advcheckbox', "playbackspeed_{$val}", '', $label, [], [0, $val]
                );
                $mform->setType("playbackspeed_{$val}", PARAM_TEXT);
            }
            $mform->addGroup($checkboxgroup, 'playbackspeedsgroup',
                get_string('setting:playbackspeeds', 'mod_videotrack'), ' ', false);

            if (!$canoverrideplayer) {
                $mform->freeze('playbackspeedsgroup');
            }
        }

        $mform->addElement('advcheckbox', 'countbyvideotime', get_string('countbyvideotime', 'mod_videotrack'));
        $mform->addHelpButton('countbyvideotime', 'countbyvideotime', 'mod_videotrack');
        $mform->setDefault('countbyvideotime', 1);

        // ----------------------------------------------------------------
        // Reactions section.
        // ----------------------------------------------------------------
        $mform->addElement('header', 'reactionsheader', get_string('reactionsheader', 'mod_videotrack'));
        $mform->addElement('advcheckbox', 'reactionsenabled', get_string('reactionsenabled', 'mod_videotrack'));
        $mform->setDefault('reactionsenabled', 1);
        $mform->addElement('advcheckbox', 'reactionsrequired', get_string('reactionsrequired', 'mod_videotrack'));
        $mform->setDefault('reactionsrequired', 0);
        $mform->addElement('text', 'minreactions', get_string('minreactions', 'mod_videotrack'), ['size' => 3]);
        $mform->setType('minreactions', PARAM_INT);
        $mform->setDefault('minreactions', 0);
        $mform->addElement('advcheckbox', 'requireallreactiontypes', get_string('requireallreactiontypes', 'mod_videotrack'));
        $mform->setDefault('requireallreactiontypes', 0);
        $mform->addElement('select', 'completionlogic', get_string('completionlogic', 'mod_videotrack'), [
            'and' => get_string('logicand', 'mod_videotrack'),
            'or'  => get_string('logicor', 'mod_videotrack'),
        ]);
        $mform->setDefault('completionlogic', 'and');

        // ----------------------------------------------------------------
        // Completion settings — locked if teacher lacks overridecompletionsettings.
        // ----------------------------------------------------------------
        if (!$canoverridecompleting) {
            $mform->addElement('static', 'completionsettings_locked_notice', '',
                html_writer::tag('div',
                    get_string('setting:lockedbyAdmin', 'mod_videotrack'),
                    ['class' => 'alert alert-info']
                )
            );
        }

        $mform->addElement('select', 'clusterwindow', get_string('clusterwindow', 'mod_videotrack'),
            [10 => 10, 15 => 15, 20 => 20, 30 => 30, 60 => 60]);
        $mform->setDefault('clusterwindow', (int)$cfg('default_clusterwindow', 30));

        if (!$canoverridecompleting) {
            $mform->freeze('clusterwindow');
        }

        $mform->addElement('advcheckbox', 'showstudentreport', get_string('showstudentreport', 'mod_videotrack'));
        $mform->setDefault('showstudentreport', 1);

        // Note personali studente.
        $mform->addElement('advcheckbox', 'studentnotesenabled',
            get_string('studentnotesenabled', 'mod_videotrack'),
            get_string('studentnotesenabled_desc', 'mod_videotrack'));
        $mform->setDefault('studentnotesenabled', (int)get_config('mod_videotrack', 'studentnotesenabled'));
        $mform->addHelpButton('studentnotesenabled', 'studentnotesenabled', 'mod_videotrack');
        $mform->addElement('advcheckbox', 'showreactionnotice', get_string('showreactionnotice', 'mod_videotrack'));
        $mform->setDefault('showreactionnotice', 1);
        $reactionnoticeoptions = [
            'context' => $this->context,
            'maxfiles' => 0,
            'maxbytes' => 0,
            'trusttext' => false,
            'noclean' => false,
        ];
        $mform->addElement('editor', 'reactionnotice_editor', get_string('reactionnotice', 'mod_videotrack'),
            null, $reactionnoticeoptions);

        // ----------------------------------------------------------------
        // Reaction preset selector.
        // ----------------------------------------------------------------
        $presetoptions = videotrack_get_preset_select_options();
        if (count($presetoptions) > 1) {
            // Only show the selector if at least one preset has been configured.
            $mform->addElement('select', 'reactionpreset',
                get_string('reactionpreset', 'mod_videotrack'), $presetoptions);
            $mform->setDefault('reactionpreset', '');
            $mform->addHelpButton('reactionpreset', 'reactionpreset', 'mod_videotrack');
            // Hidden field used by JS to carry preset JSON to the client.
            $mform->addElement('hidden', 'reactionpreset_json', '');
            $mform->setType('reactionpreset_json', PARAM_RAW);
        }

        $this->add_reaction_elements();

        // ----------------------------------------------------------------
        // Grading section — standard Moodle grading elements.
        // ----------------------------------------------------------------
        $this->standard_grading_coursemodule_elements();

        // Sufficienza: visibile solo quando si usa un tipo di valutazione ≠ None.
        $mform->addElement('text', 'gradepass',
            get_string('gradepass', 'grades'), ['size' => 6]);
        $mform->setType('gradepass', PARAM_FLOAT);
        $mform->setDefault('gradepass', 0);
        $mform->addHelpButton('gradepass', 'gradepass', 'grades');
        $mform->disabledIf('gradepass', 'grade[modgrade_type]', 'eq', 'none');

        // Mostra voto allo studente in view.php.
        $mform->addElement('advcheckbox', 'showgradeto',
            get_string('showgradeto', 'mod_videotrack'));
        $mform->setDefault('showgradeto', 0);
        $mform->disabledIf('showgradeto', 'grade[modgrade_type]', 'eq', 'none');

        // Registra il modulo AMD che pre-popola le reazioni al cambio preset.
        videotrack_require_preset_amd($this->reactionrepeatcount ?: 4);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    protected function add_reaction_elements(): void {
        $mform = $this->_form;
        $repeatcount = $this->get_reaction_repeat_count();
        $this->reactionrepeatcount = $repeatcount;

        $mform->addElement('hidden', 'reaction_repeats', $repeatcount);
        $mform->setType('reaction_repeats', PARAM_INT);

        $options = [
            'subdirs'        => 0,
            'maxfiles'       => 1,
            'accepted_types' => ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
            'maxbytes'       => 0,
            'return_types'   => FILE_INTERNAL,
        ];

        for ($i = 0; $i < $repeatcount; $i++) {
            $mform->addElement('header', 'reactionheader_' . $i,
                get_string('reactionx', 'mod_videotrack', $i + 1));
            $mform->setExpanded('reactionheader_' . $i, $i < 2);

            $mform->addElement('hidden', 'reactionid[' . $i . ']', 0);
            $mform->setType('reactionid[' . $i . ']', PARAM_INT);

            $mform->addElement('text', 'reactionlabel[' . $i . ']',
                get_string('reactionlabel', 'mod_videotrack'), ['size' => 24]);
            $mform->setType('reactionlabel[' . $i . ']', PARAM_TEXT);

            $mform->addElement('text', 'reactiondescription[' . $i . ']',
                get_string('reactiondescription', 'mod_videotrack'), ['size' => 36]);
            $mform->setType('reactiondescription[' . $i . ']', PARAM_TEXT);

            $mform->addElement('select', 'reactionicontype[' . $i . ']',
                get_string('reactionicontype', 'mod_videotrack'), [
                    'emoji' => get_string('icontype:emoji', 'mod_videotrack'),
                    'fa'    => get_string('icontype:fa', 'mod_videotrack'),
                    'file'  => get_string('icontype:file', 'mod_videotrack'),
                ]);
            $mform->setType('reactionicontype[' . $i . ']', PARAM_ALPHA);

            $mform->addElement('text', 'reactioniconvalue[' . $i . ']',
                get_string('reactioniconvalue', 'mod_videotrack'), ['size' => 24]);
            $mform->setType('reactioniconvalue[' . $i . ']', PARAM_TEXT);
            $mform->addHelpButton('reactioniconvalue[' . $i . ']', 'reactioniconvalue', 'mod_videotrack');

            $mform->addElement('filemanager', 'reactioniconfile_' . $i,
                get_string('reactioniconfile', 'mod_videotrack'), null, $options);
            $mform->addHelpButton('reactioniconfile_' . $i, 'reactioniconfile', 'mod_videotrack');
            // Avviso: proporzoni 1:1 e ridimensionamento automatico.
            $mform->addElement('static', 'reactioniconfile_notice_' . $i, '',
                html_writer::tag('small',
                    get_string('reactioniconfile_notice', 'mod_videotrack'),
                    ['class' => 'text-muted form-text']
                )
            );

            $mform->addElement('advcheckbox', 'reactionrequired[' . $i . ']',
                get_string('reactionrequired', 'mod_videotrack'));

            $mform->disabledIf('reactioniconvalue[' . $i . ']',
                'reactionicontype[' . $i . ']', 'eq', 'file');
            $mform->disabledIf('reactioniconfile_' . $i,
                'reactionicontype[' . $i . ']', 'neq', 'file');
        }

        $mform->registerNoSubmitButton('reaction_add_fields');
        $mform->addElement('submit', 'reaction_add_fields',
            get_string('addreaction', 'mod_videotrack'));
    }

    protected function get_reaction_repeat_count(): int {
        $count = optional_param('reaction_repeats', 0, PARAM_INT);
        if ($count <= 0) {
            if (!empty($this->_instance)) {
                global $DB;
                $count = (int)$DB->count_records('videotrack_react',
                    ['videotrackid' => $this->_instance]);
            }
            if ($count <= 0) {
                $count = 4;
            }
        }
        if (optional_param('reaction_add_fields', '', PARAM_RAW) !== '') {
            $count++;
        }
        return min(max($count, 1), 30);
    }

    public function add_completion_rules() {
        global $COURSE;
        $mform = $this->_form;
        $coursecontext   = context_course::instance($COURSE->id);
        $canoverride     = has_capability('mod/videotrack:overridecompletionsettings', $coursecontext);
        $defaultpercent  = (int)(get_config('mod_videotrack', 'default_completionpercent') ?: 0);

        $group   = [];
        $group[] = $mform->createElement('text', 'completionpercent', '', ['size' => 3]);
        $mform->setType('completionpercent', PARAM_INT);
        $mform->setDefault('completionpercent', $defaultpercent);
        $mform->addGroup($group, 'completionpercentgroup',
            get_string('completionpercent', 'mod_videotrack'), ' ', false);

        if (!$canoverride) {
            $mform->freeze('completionpercentgroup');
        }

        return ['completionpercentgroup'];
    }

    public function completion_rule_enabled($data) {
        return (!empty($data['completionpercent']) && (int)$data['completionpercent'] > 0) ||
            (!empty($data['reactionsrequired']) && !empty($data['minreactions'])) ||
            !empty($data['requireallreactiontypes']);
    }

    public function data_preprocessing(&$defaultvalues) {
        global $DB;
        // Carica le reazioni esistenti per il form di modifica.
        // Moodle chiama data_preprocessing() prima di mostrare il form: è il posto corretto
        // (set_data() è il metodo pubblico del form base e non va sovrascritto per questa logica).
        if (!empty($this->_instance)) {
            $reactions = $DB->get_records('videotrack_react',
                ['videotrackid' => $this->_instance, 'isdeleted' => 0], 'sortorder ASC');
            $cm = get_coursemodule_from_instance('videotrack',
                $this->_instance, 0, false, IGNORE_MISSING);
            $context = $cm ? context_module::instance($cm->id) : null;
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
                    file_prepare_draft_area($draftitemid, $context->id,
                        'mod_videotrack', 'reactionicon', (int)$reaction->id, [
                            'subdirs'        => 0,
                            'maxfiles'       => 1,
                            'accepted_types' => ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
                        ]);
                }
                $defaultvalues['reactioniconfile_' . $i] = $draftitemid;
                $i++;
            }
        }
        // Draft area per le righe vuote.
        for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
            $field = 'reactioniconfile_' . $i;
            if (!isset($defaultvalues[$field])) {
                $defaultvalues[$field] = file_get_submitted_draft_itemid($field);
            }
        }
        if (!isset($defaultvalues['completionpercent'])) {
            $defaultvalues['completionpercent'] = 0;
        }
        // Pre-popola gradepass dal DB quando si modifica un'attività esistente.
        if (!isset($defaultvalues['gradepass']) && !empty($this->_instance)) {
            $gradepass = $DB->get_field('videotrack', 'gradepass', ['id' => $this->_instance]);
            $defaultvalues['gradepass'] = ($gradepass !== false) ? format_float((float)$gradepass, 5) : 0;
        }
        // Pre-popola vimeourl se la sorgente è Vimeo.
        if (($defaultvalues['videosource'] ?? 'youtube') === 'vimeo'
                && !empty($defaultvalues['videourl'])) {
            $defaultvalues['vimeourl'] = $defaultvalues['videourl'];
        }
        // Pre-popola le checkbox delle velocità di riproduzione.
        $activespeeds = [];
        if (!empty($defaultvalues['playbackspeeds'])) {
            $activespeeds = array_map('strval', array_map('floatval',
                preg_split('/[,\n]+/', $defaultvalues['playbackspeeds'])));
        } else {
            // Usa i default del sito.
            $activespeeds = array_map('strval', videotrack_get_site_playback_speeds());
        }
        foreach (['0.25','0.5','0.75','1','1.25','1.5','1.75','2','3','4'] as $v) {
            $defaultvalues["playbackspeed_{$v}"] = in_array($v, $activespeeds) ? $v : 0;
        }

        // Pre-popola le checkbox dei controlli HTML5.
        $activecontrols = !empty($defaultvalues['html5controls'])
            ? array_map('trim', explode(',', $defaultvalues['html5controls']))
            : videotrack_get_html5controls((object)['html5controls' => '']);
        foreach (['play','rewind','fastforward','progress','current','duration','mute','volume',
                  'speed','pip','fullscreen','download'] as $ctrl) {
            $defaultvalues["html5ctrl_{$ctrl}"] = in_array($ctrl, $activecontrols) ? $ctrl : 0;
        }

        // Pre-popola i campi booleani comportamento player.
        foreach (['autoplay', 'loop', 'startmuted', 'allowdownload'] as $field) {
            if (!isset($defaultvalues[$field])) {
                $cfgval = get_config('mod_videotrack', 'default_' . $field);
                $defaultvalues[$field] = ($cfgval !== false) ? (int)(bool)$cfgval : 0;
            }
        }

        // Pre-popola i campi numerici con default sito se il valore è 0.
        foreach (['playerwidth', 'rewindstep', 'fastforwardstep'] as $field) {
            if (empty($defaultvalues[$field])) {
                $defaultvalues[$field] = 0; // 0 = usa default sito
            }
        }

        // Pre-popola captions e lingua.
        if (!isset($defaultvalues['captions'])) {
            $defaultvalues['captions'] = (int)(bool)get_config('mod_videotrack', 'default_captions');
        }
        if (!isset($defaultvalues['captionslang']) || $defaultvalues['captionslang'] === '') {
            $defaultvalues['captionslang'] = (string)get_config('mod_videotrack', 'default_captionslang');
        }

        // Prepara draft area per file VTT e video caricato (un solo get_coursemodule).
        if (($defaultvalues['videosource'] ?? 'youtube') === 'upload' && !empty($this->_instance)) {
            $cmupload = get_coursemodule_from_instance('videotrack', $this->_instance, 0, false, IGNORE_MISSING);
            if ($cmupload) {
                $ctxupload = context_module::instance($cmupload->id);
                // VTT subtitles.
                $draftitemid = file_get_submitted_draft_itemid('vttfile');
                file_prepare_draft_area($draftitemid, $ctxupload->id, 'mod_videotrack', 'subtitles', 0, [
                    'subdirs' => false, 'maxfiles' => 1, 'accepted_types' => ['.vtt'],
                ]);
                $defaultvalues['vttfile'] = $draftitemid;
                // Video content.
                $draftitemid2 = file_get_submitted_draft_itemid('videofile');
                file_prepare_draft_area($draftitemid2, $ctxupload->id, 'mod_videotrack', 'videocontent', 0, [
                    'subdirs' => false, 'maxfiles' => 1,
                    'accepted_types' => ['.mp4', '.webm', '.mp3', '.m4v', '.mov', '.aac', '.m4a'],
                ]);
                $defaultvalues['videofile'] = $draftitemid2;
            }
        }
        if (!isset($defaultvalues['reactionnotice_editor'])
                && isset($defaultvalues['reactionnotice'])) {
            $defaultvalues['reactionnotice_editor'] = [
                'text'   => $defaultvalues['reactionnotice'],
                'format' => $defaultvalues['reactionnoticeformat'] ?? FORMAT_HTML,
            ];
        }
        $defaultvalues['reactionpreset_json'] =
            json_encode(videotrack_get_all_presets_for_js());

        // C4: Prepara draft area per posterimage (disponibile per tutte le sorgenti).
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

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $source = $data['videosource'] ?? 'youtube';
        if ($source === 'youtube') {
            if (empty($data['youtubeurl']) || !videotrack_extract_videoid((string)$data['youtubeurl'])) {
                $errors['youtubeurl'] = get_string('invalidyoutubeurl', 'mod_videotrack');
            }
        } else if ($source === 'vimeo') {
            if (empty($data['vimeourl']) || !videotrack_extract_vimeo_id((string)$data['vimeourl'])) {
                $errors['vimeourl'] = get_string('invalidvimeourl', 'mod_videotrack');
            }
        } else if ($source === 'upload') {
            // Per nuove attività, il file video è obbligatorio.
            // Per le modifiche, il file esistente viene mantenuto anche se il filepicker è vuoto.
            $draftitemid = (int)($data['videofile'] ?? 0);
            $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
            $isNew       = empty($data['instance']); // $data['instance'] = 0 se nuova attività.
            if ($isNew && empty($fileinfo['filecount'])) {
                $errors['videofile'] = get_string('required');
            }
        }
        if (isset($data['completionpercent']) &&
                ((int)$data['completionpercent'] < 0 || (int)$data['completionpercent'] > 100)) {
            $errors['completionpercentgroup'] = '0-100';
        }
        if (!empty($data['reactionsrequired']) &&
                empty($data['minreactions']) && empty($data['requireallreactiontypes'])) {
            $errors['minreactions'] = get_string('err:minreactionsrequired', 'mod_videotrack');
        }

        $labels = $data['reactionlabel'] ?? [];
        $types  = $data['reactionicontype'] ?? [];
        for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
            $label = trim((string)($labels[$i] ?? ''));
            $type  = (string)($types[$i] ?? 'emoji');
            if ($label === '') {
                continue;
            }
            if ($type === 'file') {
                $draftitemid = (int)($data['reactioniconfile_' . $i] ?? 0);
                $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
                $hasfile     = !empty($fileinfo['filecount']);

                // In edit mode, do not block saving if an existing icon file is still
                // associated with this reaction and the draft area was not populated.
                $reactionids = $data['reactionid'] ?? [];
                $reactionid  = (int)($reactionids[$i] ?? 0);
                if (!$hasfile && $reactionid > 0 && !empty($this->_instance)) {
                    global $DB;
                    $cm = get_coursemodule_from_instance('videotrack', $this->_instance, 0, false, IGNORE_MISSING);
                    if ($cm) {
                        $context = context_module::instance($cm->id);
                        $hasfile = !get_file_storage()->is_area_empty(
                            $context->id, 'mod_videotrack', 'reactionicon', $reactionid
                        );
                    }
                }

                if (!$hasfile) {
                    $errors['reactioniconfile_' . $i] =
                        get_string('err:reactioniconfilerequired', 'mod_videotrack');
                }
            } else if (trim((string)($data['reactioniconvalue'][$i] ?? '')) === '') {
                $errors['reactioniconvalue[' . $i . ']'] = get_string('err:reactioniconvaluerequired', 'mod_videotrack');
            }
        }
        return $errors;
    }
}
