<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/locallib.php');

global $DB, $USER, $CFG, $PAGE, $OUTPUT;

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
if ($id) {
    $cm = get_coursemodule_from_id('videotrack', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
    $videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
} else {
    $videotrack = $DB->get_record('videotrack', ['id' => $n], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $videotrack->course], '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('videotrack', $videotrack->id, $course->id, false, MUST_EXIST);
}
require_login($course, true, $cm);
$cm = cm_info::create($cm); // Moodle 4+: set_module_viewed e le funzioni di completamento richiedono cm_info.
$context = context_module::instance($cm->id);
require_capability('mod/videotrack:view', $context);

// Registra visita, evento course_module_viewed e completamento da visita.
videotrack_view($videotrack, $course, $cm, $context);

$reactions = array_values(videotrack_get_reactions($videotrack->id));
$state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $USER->id]);
$canviewownreport = has_capability('mod/videotrack:viewownreport', $context);
$showstudentreport = !empty($videotrack->showstudentreport) && $canviewownreport;
// Reused by the student report query and by the unique-reaction fallback below.
$eventwhere = "videotrackid = :vtid AND userid = :uid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)";
$eventparams = ['vtid' => $videotrack->id, 'uid' => $USER->id];
$events = [];
$eventtruncated = false;
if ($showstudentreport) {
    // Fetch one extra row instead of running a separate COUNT query. This keeps
    // the common student view to a single note/reaction query while still
    // detecting whether the table is truncated.
    $events = $DB->get_records_select(
        'videotrack_reactev',
        $eventwhere,
        $eventparams,
        'videotime ASC',
        '*',
        0,
        201
    );
    $eventtruncated = count($events) > 200;
    if ($eventtruncated) {
        $events = array_slice($events, 0, 200, true);
    }
}
$notice = trim((string)$videotrack->reactionnotice);
if ($notice === '' && !empty($videotrack->showreactionnotice)) {
    $notice = videotrack_build_required_reaction_notice($videotrack, $reactions);
}

$PAGE->set_url('/mod/videotrack/view.php', ['id' => $cm->id]);
$PAGE->set_context($context);
$PAGE->set_title(format_string($videotrack->name));
$PAGE->set_heading(format_string($course->fullname));
$replaystart = optional_param('replaystart', -1, PARAM_INT);
$replayend   = optional_param('replayend',   -1, PARAM_INT);
$durationseconds = max(0, (int)($videotrack->durationseconds ?? 0));
if ($durationseconds > 0) {
    if ($replaystart >= 0) {
        $replaystart = min($replaystart, $durationseconds);
    }
    if ($replayend >= 0) {
        $replayend = min($replayend, $durationseconds);
    }
    if ($replaystart >= 0 && $replayend >= 0 && $replayend < $replaystart) {
        $replayend = $replaystart;
    }
}
$source      = $videotrack->videosource ?? 'youtube';
$speeds      = videotrack_get_playback_speeds($videotrack);
$html5ctrl   = videotrack_get_html5controls($videotrack);
$playerwidth = videotrack_get_player_width($videotrack);
$rewindstep  = videotrack_get_rewind_step($videotrack);
$ffstep      = videotrack_get_fastforward_step($videotrack);
$vtturl      = ($source === 'upload') ? videotrack_get_vtt_url((int)$cm->id) : null;
$posterurl   = videotrack_get_poster_url((int)$cm->id);
$heartbeat   = videotrack_get_config_int('heartbeatinterval', 30, 5, 300);
$distractionfree = !empty(get_config('mod_videotrack', 'distractionfree'));
$notemaxlength = videotrack_get_config_int('notemaxlength', 2000, 100, 10000);

// Valida intervaljson prima di passarlo al JS: garantisce array JSON valido
// anche se il campo DB fosse corrotto o null.
$rawintervals = $state ? (string)$state->intervaljson : '[]';
$decodedcheck = json_decode($rawintervals, true);
$safeintervals = is_array($decodedcheck) ? $rawintervals : '[]';

$playerconfig = [
    'cmid'                   => (int)$cm->id,
    'videoid'                => $videotrack->videoid,
    'videosource'            => $source,
    'showcontrols'           => (bool)$videotrack->showcontrols,
    'disablekeyboard'        => (bool)$videotrack->disablekeyboard,
    'showfullscreen'         => (bool)$videotrack->showfullscreen,
    'allowseekforward'       => (bool)$videotrack->allowseekforward,
    'allowseekbackward'      => (bool)$videotrack->allowseekbackward,
    'allowplaybackratechange'=> (bool)$videotrack->allowplaybackratechange,
    'autoplay'               => (bool)($videotrack->autoplay ?? false),
    'loop'                   => (bool)($videotrack->loop ?? false),
    'startmuted'             => (bool)($videotrack->startmuted ?? false),
    'allowdownload'          => (bool)($videotrack->allowdownload ?? false),
    'playbackspeeds'         => $speeds,
    'html5controls'          => $html5ctrl,
    'playerwidth'            => $playerwidth,
    'rewindstep'             => $rewindstep,
    'fastforwardstep'        => $ffstep,
    'captions'               => (bool)($videotrack->captions ?? false),
    'captionslang'           => (string)($videotrack->captionslang ?? ''),
    'vtturl'                 => $vtturl ? (string)$vtturl : '',
    'showtranscript'         => !empty($videotrack->showtranscript) && $vtturl !== null,
    // Feature 10: Capitoli VTT — stessa sorgente del transcript.
    'showchapters'           => !empty($videotrack->showchapters) && $vtturl !== null,
    // Feature 12: Poster — URL immagine anteprima (null = nessuna immagine).
    'posterurl'              => $posterurl ? (string)$posterurl : '',
    'chapterslabel'          => get_string('chapters_label', 'mod_videotrack'),
    'chapterlabel'           => get_string('chapter_label', 'mod_videotrack'),
    'chaptersunavailablelabel' => get_string('chapters_unavailable', 'mod_videotrack'),
    'requiredpercent'        => (int)$videotrack->completionpercent,
    'origin'                 => (string)$CFG->wwwroot,
    'reactionsenabled'       => (bool)$videotrack->reactionsenabled,
    'studentnotesenabled'    => !empty($videotrack->studentnotesenabled),
    'notespaneltitle'        => get_string('studentnotes_title', 'mod_videotrack'),
    'noteshidelabel'         => get_string('notes_hide', 'mod_videotrack'),
    'noteshowlabel'          => get_string('notes_show', 'mod_videotrack'),
    'replaylabel'            => get_string('report:replay',      'mod_videotrack'),
    'removelabel'            => get_string('removereaction',     'mod_videotrack'),
    'removenotelabel'        => get_string('removenotelabel',    'mod_videotrack'),
    'noteerrorlabel'         => get_string('noteerrorlabel',    'mod_videotrack'),
    'charsremaininglabel'    => get_string('charsremaininglabel', 'mod_videotrack'),
    'notemaxlength'          => $notemaxlength,
    'dismisslabel'           => get_string('dismisslabel',       'mod_videotrack'),
    'rewindlabel'            => get_string('rewindlabel',        'mod_videotrack'),
    'fastforwardlabel'       => get_string('fastforwardlabel',   'mod_videotrack'),
    'secondslabel'           => get_string('secondslabel',       'mod_videotrack'),
    'reactionerrorlabel'     => get_string('reaction:error',     'mod_videotrack'),
    'reactionunavailablelabel' => get_string('reactionsavailableonlyduringplayback', 'mod_videotrack'),
    'reactionsreadylabel'    => get_string('reactionsreadyannounce', 'mod_videotrack'),
    'reactionannouncementinterval' => videotrack_get_config_int('reactionannouncementinterval', 30000, 0, 120000),
    'reactionreadydebouncems' => videotrack_get_config_int('reactionreadydebouncems', 400, 0, 2000),
    'autoblockedlabel'       => get_string('autoblockedlabel',   'mod_videotrack'),
    'vimeocspwarnlabel'      => get_string('vimeocspwarnlabel',  'mod_videotrack'),
    'sdkerrorlabel'          => get_string('sdkerrorlabel',       'mod_videotrack'),
    'transcriptunavailablelabel' => get_string('transcript_unavailable', 'mod_videotrack'),
    'nofilelabel'            => get_string('nofilelabel',         'mod_videotrack'),
    'html5controlslabel'     => get_string('html5:controls',      'mod_videotrack'),
    'html5playlabel'         => get_string('html5:play',          'mod_videotrack'),
    'html5pauselabel'        => get_string('html5:pause',         'mod_videotrack'),
    'html5seeklabel'         => get_string('html5:seek',          'mod_videotrack'),
    'html5volumelabel'       => get_string('html5:volume',        'mod_videotrack'),
    'html5mutelabel'         => get_string('html5:mute',          'mod_videotrack'),
    'html5unmutelabel'       => get_string('html5:unmute',        'mod_videotrack'),
    'html5speedlabel'        => get_string('html5:speed',         'mod_videotrack'),
    'html5piplabel'          => get_string('html5:pip',           'mod_videotrack'),
    'html5fullscreenlabel'   => get_string('html5:fullscreen',    'mod_videotrack'),
    'html5downloadlabel'     => get_string('html5:download',      'mod_videotrack'),
    'resumelabel'            => get_string('resumelabel',          'mod_videotrack'),
    // O1/U1 fix: resumedlabel removed — showResumeNotice now uses config.resumelabel directly.
    'beaconurl'              => (string)(new moodle_url('/lib/ajax/service.php', ['sesskey' => sesskey()])),
    'replaystart'            => $replaystart >= 0 ? $replaystart : null,
    'replayend'              => $replayend   >= 0 ? $replayend   : null,
    // Feature 1: Resume dal punto lasciato (solo se abilitato e lastposition > 5s).
    'resumeposition'         => (!empty($videotrack->resumeplayback) && $state && (float)$state->lastposition > 5.0)
                                    ? round((float)$state->lastposition, 3) : 0,
    // Feature 6: Limite max velocità in centesimi (0=illimitato, 150=1.5×, ecc.).
    'maxplaybackrate'        => (int)($videotrack->maxplaybackrate ?? 0),
    'heartbeatinterval'      => $heartbeat,
    'videourl'               => ($source === 'upload')
        ? (string)(videotrack_get_upload_url($videotrack->id, $cm->id) ?? '')
        : '',
    'intervaljson'           => $safeintervals,
    'duration'               => (float)($videotrack->durationseconds ?? 0),
];

// set_pagelayout va chiamato PRIMA di js_call_amd e di OUTPUT->header().
if ($distractionfree) {
    $PAGE->set_pagelayout('embedded');
    $PAGE->add_body_class('videotrack-distractionfree');
}

// Load the correct AMD module depending on video source.
if ($source === 'vimeo') {
    $PAGE->requires->js_call_amd('mod_videotrack/vimeo_player', 'init', [$playerconfig]);
} else if ($source === 'upload') {
    $PAGE->requires->js_call_amd('mod_videotrack/html5_player',  'init', [$playerconfig]);
} else {
    $PAGE->requires->js_call_amd('mod_videotrack/player',        'init', [$playerconfig]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($videotrack->name));

// SEC-5: il blocco voto va DOPO OUTPUT->header() per rispettare il layout Moodle.
// Mostrato solo se showgradeto=1, grade attivo e l'utente non è docente/manager.
if (!empty($videotrack->showgradeto) && !empty($videotrack->grade) &&
        !has_capability('mod/videotrack:viewreport', $context)) {
    require_once($CFG->libdir . '/gradelib.php');
    $usergrade = videotrack_get_user_grade($videotrack, (int)$USER->id);
    if ($usergrade !== null) {
        $grademax       = $videotrack->grade > 0 ? (int)$videotrack->grade : null;
        $gradepasslabel = '';
        if (!empty($videotrack->gradepass)) {
            $passed         = $usergrade >= (float)$videotrack->gradepass;
            $gradepasslabel = html_writer::tag('span',
                html_writer::tag('span', $passed ? '✓' : '✗', ['aria-hidden' => 'true']) . ' ' .
                    html_writer::tag('span',
                        get_string($passed ? 'grade:pass' : 'grade:fail', 'mod_videotrack'),
                        ['class' => $passed ? 'text-success ms-1' : 'text-danger ms-1']
                    ),
                ['class' => 'ms-2']
            );
        }
        echo html_writer::div(
            html_writer::tag('strong',
                $OUTPUT->pix_icon('i/grades', '', 'moodle', ['class' => 'me-1', 'aria-hidden' => 'true']) .
                    get_string('grade') . ': '
            ) .
            format_float($usergrade, 2) .
            ($grademax !== null ? ' / ' . $grademax : '') .
            $gradepasslabel,
            'videotrack-grade-student alert alert-secondary mt-2'
        );
    } else {
        echo html_writer::div(
            html_writer::tag('strong',
                $OUTPUT->pix_icon('i/grades', '', 'moodle', ['class' => 'me-1', 'aria-hidden' => 'true']) .
                    get_string('grade') . ': '
            ) .
            get_string('report:gradenotset', 'mod_videotrack'),
            'videotrack-grade-student alert alert-light mt-2'
        );
    }
}
if (trim(strip_tags($videotrack->intro ?? '')) !== '') {
    echo format_module_intro('videotrack', $videotrack, $cm->id);
}
if ($notice !== '') {
    echo $OUTPUT->notification(format_text($notice, $videotrack->reactionnoticeformat ?: FORMAT_HTML,
        ['context' => $context, 'trusted' => false]), 'info');
}

$covered = $state ? (float)$state->uniquecoveredseconds : 0.0;
$percent = $state ? (float)$state->completionpercent : 0.0;
$uniquereactionids = [];
if (!empty($videotrack->reactionsenabled)) {
    // When the student report rows are complete, reuse them instead of issuing
    // a separate DISTINCT query. If rows were truncated, query distinct ids.
    if ($showstudentreport && !$eventtruncated) {
        foreach ($events as $event) {
            if ((int)$event->reactionid > 0) {
                $uniquereactionids[(int)$event->reactionid] = true;
            }
        }
    } else {
        // The separate DISTINCT query is needed when the student report is hidden
        // or truncated to the latest 200 rows; otherwise the in-memory rows are enough.
        $uniquereactionids = array_flip($DB->get_fieldset_select(
            'videotrack_reactev',
            'DISTINCT reactionid',
            $eventwhere . ' AND reactionid > 0',
            $eventparams
        ));
    }
}

echo html_writer::start_div('videotrack-player-shell',
    ['style' => 'max-width:' . (int)$playerwidth . 'px']);
echo html_writer::start_div('videotrack-layout');

// ── Sezione player (sinistra in landscape, piena larghezza in portrait) ──
echo html_writer::start_div('videotrack-player-section');
echo html_writer::start_div('videotrack-player-wrap');
// Placeholder di caricamento: visibile fino a quando il player JS non inizializza.
// Rimosso automaticamente quando il player viene creato (YT/Vimeo sostituisce il div).
$loadingtext = get_string('playerloading', 'mod_videotrack');
echo html_writer::div(
    html_writer::tag('span', $loadingtext, ['class' => 'sr-only']) .
    html_writer::tag('div', '', ['class' => 'videotrack-loading-spinner', 'aria-hidden' => 'true']),
    'videotrack-player-loading',
    ['aria-label' => $loadingtext, 'role' => 'status']
);
echo html_writer::div('', '', ['id' => 'mod-videotrack-player']);
// Feature 12: poster/anteprima pre-play (se caricata).
if ($posterurl) {
    echo html_writer::start_div('videotrack-poster-overlay', [
        'id'         => 'videotrack-poster-overlay',
        'role'       => 'none',
        'aria-hidden'=> 'true',
    ]);
    echo html_writer::empty_tag('img', [
        'src'   => (string)$posterurl,
        'class' => 'videotrack-poster-img',
        'alt'   => '',  // Decorativa: il player ha già il titolo.
    ]);
    // Pulsante play overlay accessibile.
    echo html_writer::tag('button',
        html_writer::tag('span', '▶', ['class' => 'videotrack-poster-play-icon', 'aria-hidden' => 'true']),
        [
            'type'       => 'button',
            'class'      => 'videotrack-poster-play-btn',
            'id'         => 'videotrack-poster-play-btn',
            'aria-label' => get_string('playbutton_label', 'mod_videotrack'),
        ]
    );
    echo html_writer::end_div();
}
echo html_writer::end_div(); // videotrack-player-wrap

// Barra visuale degli intervalli guardati (canvas aggiornato dal JS).
echo html_writer::tag('canvas', '', [
    'id'         => 'videotrack-interval-bar',
    'width'      => '800',
    'height'     => '24',
    'class'      => 'videotrack-interval-bar mt-1',
    'role'       => 'img',
    'title'      => get_string('intervalbar_title', 'mod_videotrack'),
    'aria-label' => get_string('intervalbar_title', 'mod_videotrack') . ' — ' .
                    format_float($percent, 1) . '%',
]);
echo html_writer::tag('span',
    get_string('intervalbar_title', 'mod_videotrack') . ' — ' . format_float($percent, 1) . '%',
    ['id' => 'videotrack-interval-bar-status', 'class' => 'sr-only', 'aria-live' => 'polite', 'aria-atomic' => 'true']
);
echo html_writer::end_div(); // videotrack-player-section

// ── Sidebar: progresso + reazioni + tabella reazioni studente ──
echo html_writer::start_div('videotrack-sidebar');

// Feature 8: pannello transcript VTT interattivo (solo sorgente upload con VTT caricato).
if (!empty($videotrack->showtranscript) && $vtturl !== null) {
    echo html_writer::start_div('videotrack-transcript-panel', ['id' => 'videotrack-transcript-panel']);
    echo html_writer::tag('h3',
        get_string('transcript_title', 'mod_videotrack'),
        ['class' => 'h6 mt-0 mb-1']
    );
    echo html_writer::div(
        html_writer::tag('p',
            get_string('transcript_loading', 'mod_videotrack'),
            ['class' => 'text-muted small']
        ),
        'videotrack-transcript-content',
        ['id' => 'videotrack-transcript-content', 'aria-live' => 'polite', 'aria-atomic' => 'false']
    );
    echo html_writer::end_div(); // videotrack-transcript-panel
}

// Feature 11: pannello note personali studente con toggle collassabile.
if (!empty($videotrack->studentnotesenabled)) {
    echo html_writer::start_div('videotrack-notes-panel mt-2 mb-2', [
        'id'   => 'videotrack-notes-panel',
        'role' => 'region',
        'aria-label' => get_string('studentnotes_title', 'mod_videotrack'),
    ]);
    // Header with show/hide toggle. The initial aria-label is updated synchronously
    // by the AMD player from sessionStorage before the panel is used.
    echo html_writer::start_div('videotrack-notes-header d-flex align-items-center justify-content-between mb-1');
    echo html_writer::tag('h3',
        get_string('studentnotes_title', 'mod_videotrack'),
        ['class' => 'h6 mt-0 mb-0']
    );
    echo html_writer::tag('button',
        get_string('notes_hide', 'mod_videotrack'),
        [
            'type'          => 'button',
            'id'            => 'videotrack-notes-toggle',
            'class'         => 'btn btn-link btn-sm p-0 videotrack-notes-toggle',
            'aria-expanded' => 'true',
            'aria-controls' => 'videotrack-notes-body',
            'aria-label'    => get_string('notes_hide', 'mod_videotrack') . ': ' .
                               get_string('studentnotes_title', 'mod_videotrack'),
        ]
    );
    echo html_writer::end_div(); // notes-header
    // Corpo collassabile — nascosto/mostrato dal toggle.
    // data-collapsed letto da installNotesToggle prima del render per evitare flash.
    echo html_writer::start_div('videotrack-notes-body', [
        'id'             => 'videotrack-notes-body',
        'data-collapsed' => '0',  // JS sovrascrive con valore da sessionStorage.
    ]);
    // Textarea + bottone Salva — gestita da JS.
    echo html_writer::tag('label', get_string('studentnotes_title', 'mod_videotrack'), [
        'for'   => 'videotrack-note-input',
        'class' => 'form-label small mb-1 videotrack-note-label',
    ]);
    echo html_writer::tag('textarea', '', [
        'id'          => 'videotrack-note-input',
        'class'       => 'form-control form-control-sm mb-1 videotrack-note-input',
        'rows'        => '3',
        'maxlength'   => (string)$notemaxlength,
        'placeholder'      => get_string('studentnote_placeholder', 'mod_videotrack'),
        'aria-describedby' => 'videotrack-note-hint videotrack-note-charcount',
    ]);
    echo html_writer::tag('button',
        get_string('studentnote_save', 'mod_videotrack'),
        [
            'type'         => 'button',
            'id'           => 'videotrack-note-save',
            'class'        => 'btn btn-sm btn-primary videotrack-note-save',
            'aria-disabled'=> 'true',  // Abilitato solo durante play, gestito da JS.
        ]
    );
    // Contatore caratteri rimanenti — aggiornato in tempo reale da JS.
    echo html_writer::tag('span', $notemaxlength . ' ' . get_string('charsremaininglabel', 'mod_videotrack'), [
        'id'         => 'videotrack-note-charcount',
        'class'      => 'videotrack-note-charcount small text-muted ms-2',
        'aria-live'  => 'polite',
        'aria-atomic'=> 'true',
    ]);
    // Avviso: la nota viene salvata al timestamp attuale del video.
    echo html_writer::tag('p',
        get_string('studentnote_hint', 'mod_videotrack'),
        ['id' => 'videotrack-note-hint', 'class' => 'small text-muted mt-1 mb-1']
    );
    // Lista delle note salvate (popolata da JS + server-side).
    echo html_writer::start_tag('ol', [
        'id'         => 'videotrack-notes-list',
        'class'      => 'videotrack-notes-list list-unstyled mt-1',
        'aria-live'  => 'polite',
        'aria-label' => get_string('studentnotes_list_label', 'mod_videotrack'),
    ]);
    // Note già salvate: limita la view principale alle ultime note per evitare pagine pesanti.
    $noteslimit = 200;
    $existingnotes = $DB->get_records('videotrack_reactev', [
        'videotrackid' => $videotrack->id,
        'userid'       => $USER->id,
        'notetype'     => 'note',
        'isdeleted'    => 0,
    ], 'timecreated DESC', 'id, videotime, notetext', 0, $noteslimit + 1);
    $noteslimited = count($existingnotes) > $noteslimit;
    if ($noteslimited) {
        array_pop($existingnotes);
    }
    $existingnotes = array_reverse($existingnotes, true);
    if ($noteslimited) {
        echo html_writer::tag('p',
            get_string('studentnotes_view_limited', 'mod_videotrack', $noteslimit) . ' ' .
            html_writer::link(new moodle_url('/mod/videotrack/report.php', [
                'id' => $cm->id, 'mode' => 'student', 'userid' => $USER->id,
            ]), get_string('report:viewfullreport', 'mod_videotrack')),
            ['class' => 'small text-muted']
        );
    }
    foreach ($existingnotes as $note) {
        echo html_writer::start_tag('li', [
            'class'        => 'videotrack-note-item',
            'data-noteid'  => $note->id,
        ]);
        echo html_writer::tag('span',
            videotrack_format_seconds((float)$note->videotime),
            ['class' => 'videotrack-note-time text-muted me-1 small']
        );
        echo html_writer::tag('span',
            s($note->notetext),
            ['class' => 'videotrack-note-text']
        );
        echo html_writer::tag('button',
            get_string('removenote', 'mod_videotrack'),
            [
                'type'       => 'button',
                'class'      => 'btn btn-link btn-sm videotrack-delete-note ms-1',
                'data-noteid'=> $note->id,
                // WCAG 2.4.6: aria-label contestuale con il timestamp della nota.
                'aria-label' => get_string('removenote', 'mod_videotrack') . ' — ' .
                                videotrack_format_seconds((float)$note->videotime),
            ]
        );
        echo html_writer::end_tag('li');
    }
    echo html_writer::end_tag('ol');
    echo html_writer::end_div(); // videotrack-notes-body
    echo html_writer::end_div(); // videotrack-notes-panel
}

echo html_writer::start_div('videotrack-progress mb-2');
echo html_writer::tag('div',
    get_string('progress', 'mod_videotrack') . ': ' . html_writer::tag('strong',
        format_float($percent, 2) . '%',
        [
            'id' => 'videotrack-progress-percent',
            'aria-live' => 'polite',
            'aria-atomic' => 'true',
        ]
    )
);
echo html_writer::tag('div',
    get_string('report:uniquecoveredseconds', 'mod_videotrack') . ': ' .
    html_writer::tag('span',
        s(videotrack_format_seconds($covered)),
        ['id' => 'videotrack-covered-seconds', 'aria-live' => 'polite']
    )
);
echo html_writer::tag('div',
    get_string('uniquereactions', 'mod_videotrack') . ': ' .
    html_writer::tag('span',
        (string)count($uniquereactionids),
        ['id' => 'videotrack-unique-reactions', 'aria-live' => 'polite']
    )
);
echo html_writer::end_div(); // videotrack-progress

if (!empty($videotrack->reactionsenabled) && $reactions) {
    echo html_writer::start_div('videotrack-reactions', ['id' => 'videotrack-reactions']);
    foreach ($reactions as $reaction) {
        $iconwithlabel  = videotrack_render_reaction_icon($reaction, $context, true);
        $icononlyhtml   = videotrack_render_reaction_icon($reaction, $context, false);
        echo html_writer::tag('button', $iconwithlabel, [
            'type'                  => 'button',
            'class'                 => 'btn btn-outline-secondary videotrack-reaction-btn',
            'data-reactionid'       => $reaction->id,
            'data-reactionlabel'    => s($reaction->label),
            'data-reactiondesc'     => s($reaction->description),
            'data-reactioniconhtml' => s($icononlyhtml),
            'title'                 => s($reaction->description),
            // Keep aria-disabled buttons focusable: keyboard and screen reader users
            // can activate them to receive the explanatory live-region feedback.
            'aria-disabled'         => 'true',
            'aria-describedby'      => 'videotrack-reactions-hint',
            'aria-label'            => s($reaction->label),
        ]);
    }
    // Spiegazione per lo studente: le reazioni si registrano solo durante la riproduzione.
    // Questo è by-design (punto 4 dei requisiti).
    echo html_writer::tag('p',
        get_string('reactions_hint', 'mod_videotrack'),
        ['class' => 'videotrack-reactions-hint', 'id' => 'videotrack-reactions-hint', 'aria-live' => 'polite']
    );
    echo html_writer::end_div(); // videotrack-reactions
}

if ($showstudentreport) {
    // Separazione visiva netta tra note personali e reazioni nella vista studente.
    echo html_writer::tag('h4',
        get_string('reportstudent', 'mod_videotrack'),
        ['class' => 'h5 mt-3 mb-2 videotrack-reactions-section-heading']
    );
    echo html_writer::start_div('videotrack-reactions-table-wrap');
    // A2: caption sr-only è sufficiente — rimuovere aria-label evita che gli AT
    // annuncino il titolo della tabella due volte (caption + aria-label).
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::tag('caption',
        get_string('reportstudent', 'mod_videotrack'),
        ['class' => 'sr-only']
    );
    echo html_writer::start_tag('thead');
    echo html_writer::tag('tr',
        html_writer::tag('th', get_string('report:timestamp',   'mod_videotrack'), ['scope' => 'col']) .
        html_writer::tag('th', get_string('report:reaction',    'mod_videotrack'), ['scope' => 'col']) .
        html_writer::tag('th', get_string('report:description', 'mod_videotrack'), ['scope' => 'col']) .
        html_writer::tag('th', get_string('report:replay',      'mod_videotrack'), ['scope' => 'col']) .
        html_writer::tag('th', get_string('report:delete',      'mod_videotrack'), ['scope' => 'col'])
    );
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody', [
        'id' => 'videotrack-my-reactions',
        'aria-live' => 'polite',
        'aria-relevant' => 'additions',
    ]);
    if (empty($events)) {
        echo html_writer::tag('tr',
            html_writer::tag('td',
                get_string('noreactionsyet', 'mod_videotrack'),
                ['colspan' => '5', 'class' => 'text-muted text-center py-2']
            ),
            // Classe usata dal JS (appendReactionRow) per rimuovere questa riga
            // quando viene aggiunta la prima reazione dinamicamente.
            ['class' => 'videotrack-no-reactions-placeholder']
        );
    }
    // O1: pre-calcola reactionmap per lookup O(1) invece di O(n) per evento.
    $reactionmap_view = [];
    foreach ($reactions as $r) { $reactionmap_view[(int)$r->id] = $r; }

    if ($eventtruncated) {
        $totalreactions = $DB->count_records_select('videotrack_reactev', $eventwhere, $eventparams);
        $showninfo = (object)['shown' => count($events), 'total' => $totalreactions];
        $reporturl = new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'student']);
        echo html_writer::tag('tr',
            html_writer::tag('td',
                get_string('report:showingrecentreactionsoftotal', 'mod_videotrack', $showninfo) . ' ' .
                    html_writer::link($reporturl, get_string('report:viewfullreport', 'mod_videotrack')),
                ['colspan' => '5', 'class' => 'text-muted text-center py-2']
            )
        );
    }
    foreach ($events as $event) {
        $start    = max(0, (float)$event->videotime - 30);
        $end      = (float)$event->videotime + 30;
        $reaction = $reactionmap_view[(int)$event->reactionid] ?? null;
        $timestampfmt = videotrack_format_seconds((float)$event->videotime);
        echo html_writer::start_tag('tr', ['data-eventid' => $event->id]);
        echo html_writer::tag('td', videotrack_format_seconds((float)$event->videotime));
        echo html_writer::tag('td', html_writer::span(
            $reaction ? videotrack_render_reaction_icon($reaction, $context, true) : s($event->reactionlabel),
            'videotrack-report-icon'
        ));
        echo html_writer::tag('td', s($event->reactiondesc));
        echo html_writer::tag('td', html_writer::tag('button',
            get_string('report:replay', 'mod_videotrack'),
            ['type'       => 'button',
             'class'      => 'btn btn-secondary btn-sm videotrack-replay',
             'data-start' => $start,
             'data-end'   => $end,
             // WCAG 2.4.6: aria-label contestuale distingue i bottoni identici per SR.
             'aria-label' => get_string('report:replay', 'mod_videotrack') . ' — ' . $timestampfmt,
            ]
        ));
        echo html_writer::tag('td', html_writer::tag('button',
            get_string('removereaction', 'mod_videotrack'),
            ['type'       => 'button',
             'class'      => 'btn btn-link btn-sm videotrack-delete-reaction',
             'data-eventid' => $event->id,
             // WCAG 2.4.6: aria-label contestuale per il bottone elimina.
             'aria-label' => get_string('removereaction', 'mod_videotrack') . ' — ' . $timestampfmt,
            ]
        ));
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
    echo html_writer::end_div(); // videotrack-reactions-table-wrap
}

if (has_capability('mod/videotrack:viewreport', $context)) {
    echo html_writer::div(
        html_writer::link(
            new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id]),
            get_string('reportteacher', 'mod_videotrack')
        ),
        'mt-2'
    );
}

echo html_writer::end_div(); // videotrack-sidebar
echo html_writer::end_div(); // videotrack-layout
echo html_writer::end_div(); // videotrack-player-shell

echo $OUTPUT->footer();
