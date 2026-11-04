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
 * Builds course-scoped player/timed-text settings, CSV, Forum, integrity and acknowledgement groups.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_definition {
    /**
     * Add captions and timed-text fields with provider visibility and course override permissions.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_caption_elements(\MoodleQuickForm $mform, \context_course $coursecontext): void {
        // Captions and subtitles.
        $mform->addElement(
            'header',
            'captionsheader',
            get_string('captionsheader', 'mod_videotrack')
        );

        $mform->addElement('advcheckbox', 'captions', get_string('captions', 'mod_videotrack'));

        $mform->setType('captions', PARAM_BOOL);
        $mform->setDefault('captions', (int)(bool)get_config('mod_videotrack', 'default_captions'));
        $mform->addHelpButton('captions', 'captions', 'mod_videotrack');

        $mform->addElement(
            'text',
            'captionslang',
            get_string('captionslang', 'mod_videotrack'),
            ['size' => 8]
        );
        $mform->setType('captionslang', PARAM_NOTAGS);
        $captionslang = get_config('mod_videotrack', 'default_captionslang');
        $mform->setDefault('captionslang', $captionslang === false ? '' : $captionslang);
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

        self::add_transcript_and_chapter_elements($mform);

        // Notice for Vimeo: captions must be pre-loaded on Vimeo.com.
        $mform->addElement(
            'static',
            'vimeo_captions_notice',
            '',
            html_writer::tag('div', get_string('vimeo_captions_notice', 'mod_videotrack'), ['class' => 'alert alert-info'])
        );
        $mform->hideIf('vimeo_captions_notice', 'videosource', 'neq', 'vimeo');
        $mform->hideIf('vimeo_captions_notice', 'captions', 'notchecked');

        if (!has_capability('mod/videotrack:overrideplayersettings', $coursecontext)) {
            $mform->freeze('captions');
            $mform->freeze('captionslang');
        }
    }

    /**
     * Add provider-independent transcript and chapter files with the canonical WebVTT options.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     */
    private static function add_transcript_and_chapter_elements(\MoodleQuickForm $mform): void {
        // Interactive transcript files are independent from the player provider.
        $mform->addElement(
            'filemanager',
            'transcriptfiles',
            get_string('transcriptfiles', 'mod_videotrack'),
            null,
            \mod_videotrack\local\timed_text::file_options(
                \mod_videotrack\local\timed_text::MAX_TRANSCRIPT_FILES
            )
        );
        $mform->setType('transcriptfiles', PARAM_INT);
        $mform->addHelpButton('transcriptfiles', 'transcriptfiles', 'mod_videotrack');
        $mform->addElement(
            'static',
            'transcriptfiles_notice',
            '',
            html_writer::tag(
                'small',
                get_string('transcriptfiles_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );
        $mform->addElement(
            'static',
            'transcriptfiles_provider_notice',
            '',
            html_writer::tag(
                'small',
                get_string('transcriptfiles_provider_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );
        $mform->hideIf('transcriptfiles_provider_notice', 'videosource', 'eq', 'upload');

        $mform->addElement(
            'advcheckbox',
            'showtranscript',
            get_string('showtranscript', 'mod_videotrack'),
            get_string('showtranscript_desc', 'mod_videotrack')
        );
        $mform->setType('showtranscript', PARAM_BOOL);
        $mform->setDefault('showtranscript', 0);
        $mform->addHelpButton('showtranscript', 'showtranscript', 'mod_videotrack');

        // Chapters use a dedicated WebVTT file; each cue represents one chapter.
        $mform->addElement(
            'filepicker',
            'chapterfile',
            get_string('chapterfile', 'mod_videotrack'),
            null,
            \mod_videotrack\local\timed_text::file_options(1)
        );
        $mform->setType('chapterfile', PARAM_INT);
        $mform->addHelpButton('chapterfile', 'chapterfile', 'mod_videotrack');
        $mform->addElement(
            'static',
            'chapterfile_notice',
            '',
            html_writer::tag(
                'small',
                get_string('chapterfile_notice', 'mod_videotrack'),
                ['class' => 'text-muted form-text']
            )
        );
        $mform->addElement(
            'advcheckbox',
            'showchapters',
            get_string('showchapters', 'mod_videotrack'),
            get_string('showchapters_desc', 'mod_videotrack')
        );
        $mform->setType('showchapters', PARAM_BOOL);
        $mform->setDefault('showchapters', 0);
        $mform->addHelpButton('showchapters', 'showchapters', 'mod_videotrack');
    }

    /**
     * Add player behaviour with inherited numeric defaults and course-scoped override permissions.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_player_behavior_elements(\MoodleQuickForm $mform, \context_course $coursecontext): void {
        $canoverrideplayer = has_capability('mod/videotrack:overrideplayersettings', $coursecontext);
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
        $mform->setDefault('autoplay', (int)(bool)get_config('mod_videotrack', 'default_autoplay'));
        $mform->addHelpButton('autoplay', 'autoplay', 'mod_videotrack');

        $mform->addElement('advcheckbox', 'loopenabled', get_string('loop', 'mod_videotrack'));

        $mform->setType('loopenabled', PARAM_BOOL);
        $mform->setDefault('loopenabled', (int)(bool)get_config('mod_videotrack', 'default_loop'));

        $mform->addElement('advcheckbox', 'startmuted', get_string('startmuted', 'mod_videotrack'));

        $mform->setType('startmuted', PARAM_BOOL);
        $mform->setDefault('startmuted', (int)(bool)get_config('mod_videotrack', 'default_startmuted'));
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
        $mform->setDefault('allowdownload', (int)(bool)get_config('mod_videotrack', 'default_allowdownload'));
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
    }

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
