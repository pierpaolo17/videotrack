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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php');

/**
 * Activity settings form for the VideoTrack module.
 */
class mod_videotrack_mod_form extends moodleform_mod {
    /** @var int */
    protected $reactionrepeatcount = 0;

    /**
     * Defines the activity settings form.
     */
    public function definition() {
        global $COURSE;
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

        // Additional player behaviour: autoplay, loop, mute and download.
        $mform->addElement(
            'header',
            'playerbehaviorheader',
            get_string('setting:heading_playerbehavior', 'mod_videotrack')
        );

        // Maximum player width in pixels. Zero uses the site default.
        $playerwidthelement = $mform->addElement(
            'text',
            'playerwidth',
            get_string('setting:playerwidth', 'mod_videotrack'),
            ['size' => 6]
        );
        $mform->setType('playerwidth', PARAM_INT);
        $mform->setDefault('playerwidth', 0);
        $mform->addHelpButton('playerwidth', 'playerwidth', 'mod_videotrack');
        $mform->addElement('static', 'playerwidthnote', '', get_string('playerwidth_zero_note', 'mod_videotrack'));
        if ($canoverrideplayer) {
            $playerwidthelement->updateAttributes(['aria-describedby' => 'id_playerwidthnote']);
        }

        $mform->addElement('advcheckbox', 'autoplay', get_string('autoplay', 'mod_videotrack'));

        $mform->setType('autoplay', PARAM_BOOL);
        $mform->setDefault('autoplay', (int)(bool)$cfg('default_autoplay', 0));
        $mform->addHelpButton('autoplay', 'autoplay', 'mod_videotrack');

        $mform->addElement('advcheckbox', 'loopenabled', get_string('loop', 'mod_videotrack'));

        $mform->setType('loopenabled', PARAM_BOOL);
        $mform->setDefault('loopenabled', (int)(bool)$cfg('default_loop', 0));

        $mform->addElement('advcheckbox', 'startmuted', get_string('startmuted', 'mod_videotrack'));

        $mform->setType('startmuted', PARAM_BOOL);
        $mform->setDefault('startmuted', (int)(bool)$cfg('default_startmuted', 0));
        $mform->addHelpButton('startmuted', 'startmuted', 'mod_videotrack');

        // Rewind step in seconds. Zero uses the site default.
        $mform->addElement(
            'text',
            'rewindstep',
            get_string('setting:rewindstep', 'mod_videotrack'),
            ['size' => 4]
        );
        $mform->setType('rewindstep', PARAM_INT);
        $mform->setDefault('rewindstep', 0);
        $mform->addHelpButton('rewindstep', 'rewindstep', 'mod_videotrack');

        // Fast-forward step in seconds. Zero uses the site default.
        $mform->addElement(
            'text',
            'fastforwardstep',
            get_string('setting:fastforwardstep', 'mod_videotrack'),
            ['size' => 4]
        );
        $mform->setType('fastforwardstep', PARAM_INT);
        $mform->setDefault('fastforwardstep', 0);
        $mform->addHelpButton('fastforwardstep', 'fastforwardstep', 'mod_videotrack');

        // Allow download is visible only for uploaded videos.
        $mform->addElement('advcheckbox', 'allowdownload', get_string('allowdownload', 'mod_videotrack'));

        $mform->setType('allowdownload', PARAM_BOOL);
        $mform->setDefault('allowdownload', (int)(bool)$cfg('default_allowdownload', 0));
        $mform->hideIf('allowdownload', 'videosource', 'neq', 'upload');

        if (!$canoverrideplayer) {
            $lockedfields = [
                'playerwidth',
                'autoplay',
                'loopenabled',
                'startmuted',
                'rewindstep',
                'fastforwardstep',
                'allowdownload',
            ];
            foreach ($lockedfields as $field) {
                $mform->freeze($field);
            }
        }

        // Captions and subtitles.
        $mform->addElement(
            'header',
            'captionsheader',
            get_string('captionsheader', 'mod_videotrack')
        );

        $mform->addElement('advcheckbox', 'captions', get_string('captions', 'mod_videotrack'));

        $mform->setType('captions', PARAM_BOOL);
        $mform->setDefault('captions', (int)(bool)$cfg('default_captions', 0));
        $mform->addHelpButton('captions', 'captions', 'mod_videotrack');

        $mform->addElement(
            'text',
            'captionslang',
            get_string('captionslang', 'mod_videotrack'),
            ['size' => 8]
        );
        $mform->setType('captionslang', PARAM_NOTAGS);
        $mform->setDefault('captionslang', $cfg('default_captionslang', ''));
        $mform->addHelpButton('captionslang', 'captionslang', 'mod_videotrack');
        $mform->disabledIf('captionslang', 'captions', 'notchecked');

        // VTT file upload is available only for uploaded videos.
        $vttopt = ['subdirs' => false, 'maxfiles' => 1, 'accepted_types' => ['.vtt']];
        $mform->addElement(
            'filepicker',
            'vttfile',
            get_string('vttfile', 'mod_videotrack'),
            null,
            $vttopt
        );
        $mform->setType('vttfile', PARAM_INT);
        $mform->addHelpButton('vttfile', 'vttfile', 'mod_videotrack');
        $mform->addElement(
            'static',
            'vttfile_notice',
            '',
            html_writer::tag('small', get_string('vttfile_notice', 'mod_videotrack'), ['class' => 'text-muted form-text'])
        );
        $mform->hideIf(
            'vttfile',
            'videosource',
            'neq',
            'upload'
        );
        $mform->hideIf('vttfile_notice', 'videosource', 'neq', 'upload');
        $mform->hideIf(
            'vttfile',
            'captions',
            'notchecked'
        );
        $mform->hideIf('vttfile_notice', 'captions', 'notchecked');

        // Feature 8: interactive VTT transcript (upload source only, with a VTT file).
        $mform->addElement(
            'advcheckbox',
            'showtranscript',
            get_string('showtranscript', 'mod_videotrack'),
            get_string('showtranscript_desc', 'mod_videotrack')
        );

        $mform->setType('showtranscript', PARAM_BOOL);
        $mform->setDefault('showtranscript', 0);
        $mform->addHelpButton('showtranscript', 'showtranscript', 'mod_videotrack');
        $mform->hideIf('showtranscript', 'videosource', 'neq', 'upload');
        $mform->hideIf('showtranscript', 'captions', 'notchecked');

        // Feature 10: navigable VTT chapters (uses the same VTT file).
        $mform->addElement(
            'advcheckbox',
            'showchapters',
            get_string('showchapters', 'mod_videotrack'),
            get_string('showchapters_desc', 'mod_videotrack')
        );

        $mform->setType('showchapters', PARAM_BOOL);
        $mform->setDefault('showchapters', 0);
        $mform->addHelpButton('showchapters', 'showchapters', 'mod_videotrack');
        $mform->hideIf('showchapters', 'videosource', 'neq', 'upload');
        $mform->hideIf('showchapters', 'captions', 'notchecked');

        // Notice for Vimeo: captions must be pre-loaded on Vimeo.com.
        $mform->addElement(
            'static',
            'vimeo_captions_notice',
            '',
            html_writer::tag('div', get_string('vimeo_captions_notice', 'mod_videotrack'), ['class' => 'alert alert-info'])
        );
        $mform->hideIf('vimeo_captions_notice', 'videosource', 'neq', 'vimeo');
        $mform->hideIf('vimeo_captions_notice', 'captions', 'notchecked');

        if (!$canoverrideplayer) {
            $mform->freeze('captions');
            $mform->freeze('captionslang');
        }

        // HTML5 player controls visible only for upload source.
        $sitecontrols     = videotrack_get_html5controls((object)['html5controls' => '']);
        $allctrl          = [
            'play'       => get_string(
                'ctrl:play',
                'mod_videotrack'
            ),
            'rewind'     => get_string(
                'ctrl:rewind',
                'mod_videotrack'
            ),
            'fastforward' => get_string('ctrl:fastforward', 'mod_videotrack'),
            'progress'   => get_string(
                'ctrl:progress',
                'mod_videotrack'
            ),
            'current'    => get_string(
                'ctrl:current',
                'mod_videotrack'
            ),
            'duration'   => get_string(
                'ctrl:duration',
                'mod_videotrack'
            ),
            'mute'       => get_string(
                'ctrl:mute',
                'mod_videotrack'
            ),
            'volume'     => get_string(
                'ctrl:volume',
                'mod_videotrack'
            ),
            'speed'      => get_string(
                'ctrl:speed',
                'mod_videotrack'
            ),
            'pip'        => get_string(
                'ctrl:pip',
                'mod_videotrack'
            ),
            'fullscreen' => get_string('ctrl:fullscreen', 'mod_videotrack'),
            'download'   => get_string(
                'ctrl:download',
                'mod_videotrack'
            ),
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

            if (!$canoverrideplayer) {
                $mform->freeze('playbackspeedsgroup');
            }
        }

        $mform->addElement('advcheckbox', 'countbyvideotime', get_string('countbyvideotime', 'mod_videotrack'));

        $mform->setType('countbyvideotime', PARAM_BOOL);
        $mform->addHelpButton('countbyvideotime', 'countbyvideotime', 'mod_videotrack');
        $mform->setDefault('countbyvideotime', 1);

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
            \mod_videotrack\local\csv_export::delimiter_options(true)
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
        $csvcontext = $this->context ?: $coursecontext;
        $csvfieldoptions = \mod_videotrack\local\csv_export::form_field_options($csvcontext);
        $allowedcsvfields = array_keys(\mod_videotrack\local\csv_export::field_options($csvcontext));
        $csvfieldcheckboxes = [];
        $frozencsvfields = [];
        foreach ($csvfieldoptions as $field => $label) {
            $elementname = \mod_videotrack\local\csv_export::form_element_name($field);
            $isallowedcsvfield = in_array($field, $allowedcsvfields, true);
            if (!$isallowedcsvfield) {
                $label .= ' (' . get_string('notavailable') . ')';
            }
            $csvfieldcheckboxes[] = $mform->createElement(
                'advcheckbox',
                $elementname,
                '',
                $label,
                [],
                [0, 1]
            );
            $mform->setType($elementname, PARAM_BOOL);
            if (!$isallowedcsvfield) {
                $frozencsvfields[] = $elementname;
            }
        }
        if ($csvfieldcheckboxes) {
            $mform->addGroup(
                $csvfieldcheckboxes,
                'csvexportfieldsgroup',
                get_string('setting:csvexportfields', 'mod_videotrack'),
                ' ',
                false
            );
            foreach ($frozencsvfields as $elementname) {
                $mform->freeze($elementname);
            }
        }

        // Reactions section.
        $mform->addElement('header', 'reactionsheader', get_string('reactionsheader', 'mod_videotrack'));
        $mform->addElement('advcheckbox', 'reactionsenabled', get_string('reactionsenabled', 'mod_videotrack'));

        $mform->setType('reactionsenabled', PARAM_BOOL);
        $mform->setDefault('reactionsenabled', 1);
        $mform->addElement('advcheckbox', 'reactionsrequired', get_string('reactionsrequired', 'mod_videotrack'));

        $mform->setType('reactionsrequired', PARAM_BOOL);
        $mform->setDefault('reactionsrequired', 0);
        $mform->addElement('text', 'minreactions', get_string('minreactions', 'mod_videotrack'), ['size' => 3]);
        $mform->setType('minreactions', PARAM_INT);
        $mform->setDefault('minreactions', 0);
        $mform->addElement('advcheckbox', 'requireallreactiontypes', get_string('requireallreactiontypes', 'mod_videotrack'));

        $mform->setType('requireallreactiontypes', PARAM_BOOL);
        $mform->setDefault('requireallreactiontypes', 0);
        $mform->addElement('select', 'completionlogic', get_string('completionlogic', 'mod_videotrack'), [
            'and' => get_string('logicand', 'mod_videotrack'),
            'or'  => get_string('logicor', 'mod_videotrack'),
        ]);
        $mform->setType('completionlogic', PARAM_ALPHA);
        $mform->setDefault('completionlogic', 'and');

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
        $mform->addElement('advcheckbox', 'showreactionnotice', get_string('showreactionnotice', 'mod_videotrack'));

        $mform->setType('showreactionnotice', PARAM_BOOL);
        $mform->setDefault('showreactionnotice', 1);
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
            $mform->setType('reactionpreset_json', PARAM_RAW_TRIM);
        }

        $this->add_reaction_elements();

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
        $this->add_action_buttons();
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
            $mform->addElement(
                'header',
                'reactionheader_' . $i,
                get_string('reactionx', 'mod_videotrack', $i + 1)
            );
            $mform->setExpanded('reactionheader_' . $i, $i < 2);

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

        $mform->registerNoSubmitButton('reaction_add_fields');
        $mform->addElement(
            'submit',
            'reaction_add_fields',
            get_string('addreaction', 'mod_videotrack')
        );
    }

    /**
     * Returns the number of reaction rows to render in the form.
     *
     * @return int Number of reaction rows.
     */
    protected function get_reaction_repeat_count(): int {
        $count = optional_param('reaction_repeats', 0, PARAM_INT);
        if ($count <= 0) {
            if (!empty($this->_instance)) {
                global $DB;
                $count = (int)$DB->count_records(
                    'videotrack_react',
                    ['videotrackid' => $this->_instance]
                );
            }
            if ($count <= 0) {
                $count = 4;
            }
        }
        if (optional_param('reaction_add_fields', '', PARAM_ALPHANUMEXT) !== '') {
            $count++;
        }
        return min(max($count, 1), 30);
    }

    /**
     * Adds VideoTrack-specific completion rules.
     *
     * @return array List of completion rule element names.
     */
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
        $mform->addGroup(
            $group,
            'completionpercentgroup',
            get_string('completionpercent', 'mod_videotrack'),
            ' ',
            false
        );

        if (!$canoverride) {
            $mform->freeze('completionpercentgroup');
        }

        return ['completionpercentgroup'];
    }

    /**
     * Checks whether the custom completion rule is enabled.
     *
     * @param array $data Submitted form data.
     * @return bool True when at least one custom completion condition is active.
     */
    public function completion_rule_enabled($data) {
        return (!empty($data['completionpercent']) && (int)$data['completionpercent'] > 0) ||
            (!empty($data['reactionsrequired']) && !empty($data['minreactions'])) ||
            !empty($data['requireallreactiontypes']);
    }

    /**
     * Prepares default values and draft areas before the form is displayed.
     *
     * @param array $defaultvalues Default values passed by reference.
     */
    public function data_preprocessing(&$defaultvalues) {
        global $COURSE, $DB;
        // Load existing reactions for the edit form.
        // Moodle calls data_preprocessing() before displaying the form: this is the correct place.
        // (set_data() is the public base form method and must not be overridden for this logic).
        if (!empty($this->_instance)) {
            $reactions = $DB->get_records(
                'videotrack_react',
                ['videotrackid' => $this->_instance, 'isdeleted' => 0],
                'sortorder ASC'
            );
            $cm = get_coursemodule_from_instance(
                'videotrack',
                $this->_instance,
                0,
                false,
                IGNORE_MISSING
            );
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
        for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
            $field = 'reactioniconfile_' . $i;
            if (!isset($defaultvalues[$field])) {
                $defaultvalues[$field] = file_get_submitted_draft_itemid($field);
            }
        }
        if (!isset($defaultvalues['completionpercent'])) {
            $defaultvalues['completionpercent'] = 0;
        }
        // Pre-populate gradepass from the database when editing an existing activity.
        if (!isset($defaultvalues['gradepass']) && !empty($this->_instance)) {
            $gradepass = $DB->get_field('videotrack', 'gradepass', ['id' => $this->_instance]);
            $defaultvalues['gradepass'] = ($gradepass !== false) ? format_float((float)$gradepass, 5) : 0;
        }
        // Pre-populate vimeourl when the source is Vimeo.
        if (
            ($defaultvalues['videosource'] ?? 'youtube') === 'vimeo'
            && !empty($defaultvalues['videourl'])
        ) {
            $defaultvalues['vimeourl'] = $defaultvalues['videourl'];
        }
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

        // Pre-populate optional CSV export field checkboxes.
        $csvraw = trim((string)($defaultvalues['csvexportfields'] ?? ''));
        if ($csvraw === 'none') {
            $activecsvfields = [];
        } else if ($csvraw === '') {
            $activecsvfields = \mod_videotrack\local\csv_export::site_default_fields();
        } else {
            $activecsvfields = array_values(array_filter(array_map('trim', explode(',', $csvraw))));
        }
        $csvcontext = $this->context ?: context_course::instance($COURSE->id);
        foreach (\mod_videotrack\local\csv_export::form_field_options($csvcontext) as $field => $label) {
            $elementname = \mod_videotrack\local\csv_export::form_element_name($field);
            $defaultvalues[$elementname] = in_array($field, $activecsvfields, true) ? 1 : 0;
        }
        if (empty($defaultvalues['csvdelimiter'])) {
            $defaultvalues['csvdelimiter'] = \mod_videotrack\local\csv_export::DELIMITER_INHERIT;
        }

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

        // Prepare draft areas for the VTT file and uploaded video (single get_coursemodule call).
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
        if (
            !isset($defaultvalues['reactionnotice_editor'])
            && isset($defaultvalues['reactionnotice'])
        ) {
            $defaultvalues['reactionnotice_editor'] = [
                'text'   => $defaultvalues['reactionnotice'],
                'format' => $defaultvalues['reactionnoticeformat'] ?? FORMAT_HTML,
            ];
        }
        $defaultvalues['reactionpreset_json'] =
            json_encode(videotrack_get_all_presets_for_js());

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
     * Validates submitted activity settings.
     *
     * @param array $data Submitted form data.
     * @param array $files Submitted files.
     * @return array Validation errors indexed by form element name.
     */
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
            // For new activities, the video file is required.
            // For edits, the existing file is kept even when the file picker is empty.
            $draftitemid = (int)($data['videofile'] ?? 0);
            $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
            $isnew       = empty($data['instance']); // The instance value is zero for a new activity.
            if ($isnew && empty($fileinfo['filecount'])) {
                $errors['videofile'] = get_string('required');
            }
        } else {
            $errors['videosource'] = get_string('invalidvideosource', 'mod_videotrack');
        }
        if (
            isset($data['completionpercent'])
            && ((int)$data['completionpercent'] < 0 || (int)$data['completionpercent'] > 100)
        ) {
            $errors['completionpercentgroup'] = get_string('err:completionpercentrange', 'mod_videotrack');
        }

        if (array_key_exists('playerwidth', $data)) {
            $playerwidth = (int)$data['playerwidth'];
            if ($playerwidth < 0 || $playerwidth > 4096) {
                $errors['playerwidth'] = get_string('err:playerwidthrequired', 'mod_videotrack');
            }
        }

        foreach (['rewindstep', 'fastforwardstep'] as $stepfield) {
            if (array_key_exists($stepfield, $data)) {
                $step = (int)$data[$stepfield];
                if ($step < 0 || $step > 300) {
                    $errors[$stepfield] = get_string('err:playbacksteprequired', 'mod_videotrack');
                }
            }
        }
        if (
            !empty($data['reactionsrequired'])
            && empty($data['minreactions'])
            && empty($data['requireallreactiontypes'])
        ) {
            $errors['minreactions'] = get_string('err:minreactionsrequired', 'mod_videotrack');
        }

        if (array_key_exists('reactionpreset_json', $data) && trim((string)$data['reactionpreset_json']) !== '') {
            $presetjson = json_decode((string)$data['reactionpreset_json'], true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($presetjson)) {
                $errors['reactionpreset'] = get_string('err:reactionpresetjson', 'mod_videotrack');
            }
        }

        $labels = $data['reactionlabel'] ?? [];
        $descriptions = $data['reactiondescription'] ?? [];
        $types  = $data['reactionicontype'] ?? [];
        $reactionids = $data['reactionid'] ?? [];
        $allowedimageextensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $reactionsenabled = !empty($data['reactionsenabled']);
        for ($i = 0; $i < $this->reactionrepeatcount; $i++) {
            $label = trim((string)($labels[$i] ?? ''));
            $description = trim((string)($descriptions[$i] ?? ''));
            $type  = (string)($types[$i] ?? '');
            $iconvalue = trim((string)($data['reactioniconvalue'][$i] ?? ''));
            $rowhastext = $label !== '' || $description !== '';
            $hasicon = false;

            if (!in_array($type, ['emoji', 'fa', 'file'], true)) {
                if ($reactionsenabled && $rowhastext) {
                    $errors['reactionicontype[' . $i . ']'] = get_string('err:reactionicontyperequired', 'mod_videotrack');
                }
                continue;
            }

            if ($type === 'file') {
                $draftitemid = (int)($data['reactioniconfile_' . $i] ?? 0);
                $fileinfo    = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : [];
                $hasfile     = !empty($fileinfo['filecount']);

                // In edit mode, do not block saving if an existing icon file is still.
                // Associated with this reaction and the draft area was not populated.
                $reactionid  = (int)($reactionids[$i] ?? 0);
                if (!$hasfile && $reactionid > 0 && !empty($this->_instance)) {
                    global $DB;
                    $cm = get_coursemodule_from_instance('videotrack', $this->_instance, 0, false, IGNORE_MISSING);
                    if ($cm) {
                        $context = context_module::instance($cm->id);
                        $hasfile = !get_file_storage()->is_area_empty(
                            $context->id,
                            'mod_videotrack',
                            'reactionicon',
                            $reactionid
                        );
                    }
                }

                $hasicon = $hasfile;
                $hasvalidimagefile = $draftitemid <= 0 || $this->draft_area_contains_only_reaction_images(
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
            } else {
                $hasicon = $iconvalue !== '';
                if ($reactionsenabled && $rowhastext && !$hasicon) {
                    $errors['reactioniconvalue[' . $i . ']'] = get_string('err:reactioniconvaluerequired', 'mod_videotrack');
                } else if ($hasicon && $type === 'fa' && !videotrack_is_valid_reaction_icon_class($iconvalue)) {
                    $errors['reactioniconvalue[' . $i . ']'] = get_string('err:reactioniconvalueinvalidfa', 'mod_videotrack');
                }
            }

            if ($reactionsenabled && $hasicon) {
                if ($label === '') {
                    $errors['reactionlabel[' . $i . ']'] = get_string('required');
                }
                if ($description === '') {
                    $errors['reactiondescription[' . $i . ']'] = get_string('required');
                }
            }
        }
        return $errors;
    }
}
