<?php

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/classes/admin/setting_nonnegative_int.php');
require_once(__DIR__ . '/classes/admin/setting_int_range.php');

if ($ADMIN->fulltree) {

    // Show a persistent admin warning when GD is not available.
    if (!function_exists('imagecreatefromstring')) {
        $settings->add(new admin_setting_heading(
            'mod_videotrack/gd_missing_warning',
            '',
            html_writer::div(
                html_writer::tag('strong',
                    get_string('setting:gd_missing_title', 'mod_videotrack')) . ' ' .
                get_string('setting:gd_missing_desc', 'mod_videotrack'),
                'alert alert-warning'
            )
        ));
    }

    // Privacy and data retention.
    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_privacy',
        get_string('setting:heading_privacy', 'mod_videotrack'),
        get_string('setting:heading_privacy_desc', 'mod_videotrack')
    ));

    $settings->add(new \mod_videotrack\admin\setting_nonnegative_int(
        'mod_videotrack/retentionperioddays',
        get_string('setting:retentionperioddays', 'mod_videotrack'),
        get_string('setting:retentionperioddays_desc', 'mod_videotrack'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/strictsessionvalidation',
        get_string('setting:strictsessionvalidation', 'mod_videotrack'),
        get_string('setting:strictsessionvalidation_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new \mod_videotrack\admin\setting_int_range(
        'mod_videotrack/validationfallbackdays',
        get_string('setting:validationfallbackdays', 'mod_videotrack'),
        get_string('setting:validationfallbackdays_desc', 'mod_videotrack'),
        30,
        0,
        3650
    ));

    // Performance.
    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_performance',
        get_string('setting:heading_performance', 'mod_videotrack'),
        ''
    ));

    $settings->add(new \mod_videotrack\admin\setting_int_range(
        'mod_videotrack/heartbeatinterval',
        get_string('setting:heartbeatinterval', 'mod_videotrack'),
        get_string('setting:heartbeatinterval_desc', 'mod_videotrack'),
        30,
        5,
        300
    ));

    // Player behaviour.
    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_player',
        get_string('setting:heading_player', 'mod_videotrack'),
        ''
    ));

    // Playback speeds: multiselect checkboxes.
    // Admin selects which speeds are available on the platform.
    // Teachers can restrict/extend for individual activities.
    $speedoptions = [
        '0.25' => '0.25×',
        '0.5'  => '0.5×',
        '0.75' => '0.75×',
        '1'    => '1× (' . get_string('setting:speed_normal', 'mod_videotrack') . ')',
        '1.25' => '1.25×',
        '1.5'  => '1.5×',
        '1.75' => '1.75×',
        '2'    => '2×',
        '3'    => '3×',
        '4'    => '4×',
    ];
    $settings->add(new admin_setting_configmultiselect(
        'mod_videotrack/playbackspeeds',
        get_string('setting:playbackspeeds', 'mod_videotrack'),
        get_string('setting:playbackspeeds_desc', 'mod_videotrack'),
        ['0.75', '1', '1.25', '1.5', '2'], // default
        $speedoptions
    ));

    // Maximum playback rate: students cannot exceed this speed even if a higher
    // rate is in the allowed list. 0 = no limit (default).
    // I valori sono in CENTESIMI (150 = 1.5×) — stesso formato usato in mod_form.php
    // e nel codice JS (config.maxplaybackrate / 100). Non usare float string ('1.5').
    $settings->add(new admin_setting_configselect(
        'mod_videotrack/maxplaybackrate',
        get_string('setting:maxplaybackrate', 'mod_videotrack'),
        get_string('setting:maxplaybackrate_desc', 'mod_videotrack'),
        0, // 0 = no hard cap
        [
            0   => get_string('setting:maxplaybackrate_nolimit', 'mod_videotrack'),
            100 => '1×',
            125 => '1.25×',
            150 => '1.5×',
            175 => '1.75×',
            200 => '2×',
            300 => '3×',
            400 => '4×',
        ]
    ));

    // Distraction-free mode: hides Moodle header/footer for embedded use.
    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/distractionfree',
        get_string('setting:distractionfree', 'mod_videotrack'),
        get_string('setting:distractionfree_desc', 'mod_videotrack'),
        0
    ));

    // Resume playback: default globale.
    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/resumeplayback',
        get_string('setting:resumeplayback', 'mod_videotrack'),
        get_string('setting:resumeplayback_desc', 'mod_videotrack'),
        0
    ));

    // Note personali studente: default globale.
    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/studentnotesenabled',
        get_string('setting:studentnotesenabled', 'mod_videotrack'),
        get_string('setting:studentnotesenabled_desc', 'mod_videotrack'),
        0
    ));

    // Default player behaviour.

    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_playerbehavior',
        get_string('setting:heading_playerbehavior', 'mod_videotrack'),
        get_string('setting:heading_playerbehavior_desc', 'mod_videotrack')
    ));

    // Max player width.
    $settings->add(new admin_setting_configtext(
        'mod_videotrack/playerwidth',
        get_string('setting:playerwidth', 'mod_videotrack'),
        get_string('setting:playerwidth_desc', 'mod_videotrack'),
        960,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_autoplay',
        get_string('autoplay', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_loop',
        get_string('loop', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_startmuted',
        get_string('startmuted', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_allowdownload',
        get_string('allowdownload', 'mod_videotrack'),
        get_string('setting:allowdownload_desc', 'mod_videotrack'),
        0
    ));

    // Rewind step (seconds).
    $settings->add(new admin_setting_configtext(
        'mod_videotrack/rewindstep',
        get_string('setting:rewindstep', 'mod_videotrack'),
        get_string('setting:rewindstep_desc', 'mod_videotrack'),
        10,
        PARAM_INT
    ));

    // Fast-forward step (seconds).
    $settings->add(new admin_setting_configtext(
        'mod_videotrack/fastforwardstep',
        get_string('setting:fastforwardstep', 'mod_videotrack'),
        get_string('setting:fastforwardstep_desc', 'mod_videotrack'),
        10,
        PARAM_INT
    ));

    // Captions / subtitles.
    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_captions',
        get_string('captions', 'mod_videotrack'),
        get_string('setting:default_captions_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new admin_setting_configtext(
        'mod_videotrack/default_captionslang',
        get_string('captionslang', 'mod_videotrack'),
        get_string('setting:captionslang_desc', 'mod_videotrack'),
        '',
        PARAM_LANG
    ));

    // -------------------------------------------------------------------------
    // HTML5 player controls (upload source only)
    // -------------------------------------------------------------------------

    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_html5controls',
        get_string('setting:heading_html5controls', 'mod_videotrack'),
        get_string('setting:heading_html5controls_desc', 'mod_videotrack')
    ));

    $html5controloptions = [
        'play'        => get_string('ctrl:play',        'mod_videotrack'),
        'rewind'      => get_string('ctrl:rewind',      'mod_videotrack'),
        'fastforward' => get_string('ctrl:fastforward', 'mod_videotrack'),
        'progress'    => get_string('ctrl:progress',    'mod_videotrack'),
        'current'     => get_string('ctrl:current',     'mod_videotrack'),
        'duration'    => get_string('ctrl:duration',    'mod_videotrack'),
        'mute'        => get_string('ctrl:mute',        'mod_videotrack'),
        'volume'      => get_string('ctrl:volume',      'mod_videotrack'),
        'speed'       => get_string('ctrl:speed',       'mod_videotrack'),
        'pip'         => get_string('ctrl:pip',         'mod_videotrack'),
        'fullscreen'  => get_string('ctrl:fullscreen',  'mod_videotrack'),
        'download'    => get_string('ctrl:download',    'mod_videotrack'),
    ];
    $settings->add(new admin_setting_configmultiselect(
        'mod_videotrack/html5controls',
        get_string('setting:html5controls', 'mod_videotrack'),
        get_string('setting:html5controls_desc', 'mod_videotrack'),
        ['play', 'rewind', 'fastforward', 'progress', 'current', 'duration',
         'mute', 'volume', 'speed', 'fullscreen'],
        $html5controloptions
    ));

    // -------------------------------------------------------------------------
    // Default player behaviour
    // -------------------------------------------------------------------------

    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_defaults',
        get_string('setting:heading_defaults', 'mod_videotrack'),
        get_string('setting:heading_defaults_desc', 'mod_videotrack')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_showcontrols',
        get_string('showcontrols', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_showfullscreen',
        get_string('showfullscreen', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_disablekeyboard',
        get_string('disablekeyboard', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_allowseekforward',
        get_string('allowseekforward', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_allowseekbackward',
        get_string('allowseekbackward', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'mod_videotrack/default_allowplaybackratechange',
        get_string('allowplaybackratechange', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'mod_videotrack/default_completionpercent',
        get_string('completionpercent', 'mod_videotrack'),
        get_string('setting:default_completionpercent_desc', 'mod_videotrack'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configselect(
        'mod_videotrack/default_clusterwindow',
        get_string('clusterwindow', 'mod_videotrack'),
        get_string('setting:default_desc', 'mod_videotrack'),
        30,
        [10 => 10, 15 => 15, 20 => 20, 30 => 30, 60 => 60]
    ));

    // -------------------------------------------------------------------------
    // Reaction presets
    // -------------------------------------------------------------------------

    $settings->add(new admin_setting_heading(
        'mod_videotrack/heading_presets',
        get_string('setting:heading_presets', 'mod_videotrack'),
        get_string('setting:heading_presets_desc', 'mod_videotrack')
    ));

    $presetsurl = new moodle_url('/mod/videotrack/presets.php');
    $settings->add(new admin_setting_heading(
        'mod_videotrack/presets_link',
        '',
        html_writer::link($presetsurl,
            get_string('presets:manage', 'mod_videotrack'),
            ['class' => 'btn btn-secondary']
        )
    ));

}
