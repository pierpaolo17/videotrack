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
$cm = cm_info::create($cm); // Moodle 4+: set_module_viewed and completion functions require cm_info.
$context = context_module::instance($cm->id);
require_capability('mod/videotrack:view', $context);

// Register the view, course_module_viewed event and view-based completion.
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
    // Fetch one extra row instead of running a separate COUNT query. This keeps.
    // The common student view to a single note/reaction query while still.
    // Detecting whether the table is truncated.
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
$PAGE->set_title(format_string($videotrack->name, true, ['context' => $context]));
$PAGE->set_heading(format_string($course->fullname, true, ['context' => context_course::instance($course->id)]));
$replaystart = optional_param('replaystart', -1, PARAM_INT);
$replayend   = optional_param('replayend', -1, PARAM_INT);
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
$vtturl      = ($source === 'upload' && !empty($videotrack->captions)) ? videotrack_get_vtt_url((int)$cm->id) : null;
$posterurl   = videotrack_get_poster_url((int)$cm->id);
$heartbeat   = videotrack_get_config_int('heartbeatinterval', 30, 5, 300);
$distractionfree = !empty(get_config('mod_videotrack', 'distractionfree'));
$notemaxlength = videotrack_get_config_int('notemaxlength', 2000, 100, 10000);
$notesmaxrendered = 200;

// Validate intervaljson before passing it to JS, keeping a valid JSON array.
// Even if the DB field is corrupted or null.
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
    'allowplaybackratechange' => (bool)$videotrack->allowplaybackratechange,
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
    'showtranscript'         => !empty($videotrack->captions) && !empty($videotrack->showtranscript) && $vtturl !== null,
    // Feature 10: VTT chapters use the same source as the transcript.
    'showchapters'           => !empty($videotrack->captions) && !empty($videotrack->showchapters) && $vtturl !== null,
    // Feature 12: poster preview image URL (empty when no image is configured).
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
    'replaylabel'            => get_string('report:replay', 'mod_videotrack'),
    'removelabel'            => get_string('removereaction', 'mod_videotrack'),
    'removenotelabel'        => get_string('removenotelabel', 'mod_videotrack'),
    'noteerrorlabel'         => get_string('noteerrorlabel', 'mod_videotrack'),
    'notesavedlabel'         => get_string('notesavedlabel', 'mod_videotrack'),
    'notedeletedlabel'       => get_string('notedeletedlabel', 'mod_videotrack'),
    'noteplaybackrequiredlabel' => get_string('noteplaybackrequiredlabel', 'mod_videotrack'),
    'noteemptylabel'         => get_string('noteemptylabel', 'mod_videotrack'),
    'notetoolonglabel'       => get_string('notetoolonglabel', 'mod_videotrack'),
    'studentnoteslimitedlabel' => get_string('studentnoteslimitedlabel', 'mod_videotrack', $notesmaxrendered),
    'notesmaxrendered'       => $notesmaxrendered,
    'charsremaininglabel'    => get_string('charsremaininglabel', 'mod_videotrack'),
    'notemaxlength'          => $notemaxlength,
    'dismisslabel'           => get_string('dismisslabel', 'mod_videotrack'),
    'statusdefaultlabel'     => get_string('status:default', 'mod_videotrack'),
    'statuserrorlabel'       => get_string('status:error', 'mod_videotrack'),
    'rewindlabel'            => get_string('rewindlabel', 'mod_videotrack'),
    'fastforwardlabel'       => get_string('fastforwardlabel', 'mod_videotrack'),
    'secondslabel'           => get_string('secondslabel', 'mod_videotrack'),
    'reactionerrorlabel'     => get_string('reaction:error', 'mod_videotrack'),
    'reactionunavailablelabel' => get_string('reactionsavailableonlyduringplayback', 'mod_videotrack'),
    'reactionsreadylabel'    => get_string('reactionsreadyannounce', 'mod_videotrack'),
    'reactionannouncementinterval' => videotrack_get_config_int('reactionannouncementinterval', 30000, 0, 120000),
    'reactionreadydebouncems' => videotrack_get_config_int('reactionreadydebouncems', 400, 0, 2000),
    'statusinfotimeoutms'    => videotrack_get_config_int('statusinfotimeoutms', 8000, 4000, 20000),
    'statuserrortimeoutms'   => videotrack_get_config_int('statuserrortimeoutms', 6000, 6000, 30000),
    'autoblockedlabel'       => get_string('autoblockedlabel', 'mod_videotrack'),
    'vimeocspwarnlabel'      => get_string('vimeocspwarnlabel', 'mod_videotrack'),
    'sdkerrorlabel'          => get_string('sdkerrorlabel', 'mod_videotrack'),
    'transcriptunavailablelabel' => get_string('transcript_unavailable', 'mod_videotrack'),
    'nofilelabel'            => get_string('nofilelabel', 'mod_videotrack'),
    'html5controlslabel'     => get_string('html5:controls', 'mod_videotrack'),
    'html5playlabel'         => get_string('html5:play', 'mod_videotrack'),
    'html5pauselabel'        => get_string('html5:pause', 'mod_videotrack'),
    'html5seeklabel'         => get_string('html5:seek', 'mod_videotrack'),
    'html5volumelabel'       => get_string('html5:volume', 'mod_videotrack'),
    'html5mutelabel'         => get_string('html5:mute', 'mod_videotrack'),
    'html5unmutelabel'       => get_string('html5:unmute', 'mod_videotrack'),
    'html5speedlabel'        => get_string('html5:speed', 'mod_videotrack'),
    'html5piplabel'          => get_string('html5:pip', 'mod_videotrack'),
    'html5fullscreenlabel'   => get_string('html5:fullscreen', 'mod_videotrack'),
    'html5downloadlabel'     => get_string('html5:download', 'mod_videotrack'),
    'resumelabel'            => get_string('resumelabel', 'mod_videotrack'),
    // O1/U1 fix: resumedlabel removed — showResumeNotice now uses config.resumelabel directly.
    'beaconurl'              => (string)(new moodle_url('/lib/ajax/service.php', ['sesskey' => sesskey()])),
    'replaystart'            => $replaystart >= 0 ? $replaystart : null,
    'replayend'              => $replayend >= 0 ? $replayend : null,
    // Feature 1: resume from the previous position when enabled and lastposition > 5s.
    'resumeposition'         => (!empty($videotrack->resumeplayback) && $state && (float)$state->lastposition > 5.0)
                                    ? round((float)$state->lastposition, 3) : 0,
    // Feature 6: maximum playback rate in hundredths (0=unlimited, 150=1.5x, etc.).
    'maxplaybackrate'        => (int)($videotrack->maxplaybackrate ?? 0),
    'heartbeatinterval'      => $heartbeat,
    'videourl'               => ($source === 'upload')
        ? (string)(videotrack_get_upload_url($videotrack->id, $cm->id) ?? '')
        : '',
    'intervaljson'           => $safeintervals,
    'duration'               => (float)($videotrack->durationseconds ?? 0),
];

// Page layout must be set before js_call_amd and OUTPUT->header().
if ($distractionfree) {
    $PAGE->set_pagelayout('embedded');
    $PAGE->add_body_class('videotrack-distractionfree');
}

// Load the correct AMD module depending on video source.
if ($source === 'vimeo') {
    $PAGE->requires->js_call_amd('mod_videotrack/vimeo_player', 'init', [$playerconfig]);
} else if ($source === 'upload') {
    $PAGE->requires->js_call_amd('mod_videotrack/html5_player', 'init', [$playerconfig]);
} else {
    $PAGE->requires->js_call_amd('mod_videotrack/player', 'init', [$playerconfig]);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($videotrack->name, true, ['context' => $context]));

// SEC-5: the grade block must be rendered after OUTPUT->header() to respect the Moodle layout.
// It is shown only when showgradeto=1, grading is active and the user is not a teacher/manager.
if (
    !empty($videotrack->showgradeto) &&
    !empty($videotrack->grade) &&
    !has_capability('mod/videotrack:viewreport', $context)
) {
    require_once($CFG->libdir . '/gradelib.php');
    $usergrade = videotrack_get_user_grade($videotrack, (int)$USER->id);
    if ($usergrade !== null) {
        $grademax       = $videotrack->grade > 0 ? (int)$videotrack->grade : null;
        $gradepasslabel = '';
        if (!empty($videotrack->gradepass)) {
            $passed         = $usergrade >= (float)$videotrack->gradepass;
            $gradepasslabel = html_writer::tag(
                'span',
                $OUTPUT->pix_icon($passed ? 'i/valid' : 'i/invalid', '', 'moodle', ['aria-hidden' => 'true']) . ' ' . html_writer::tag('span', get_string($passed ? 'grade:pass' : 'grade:fail', 'mod_videotrack'), ['class' => $passed ? 'text-success ms-1' : 'text-danger ms-1']),
                ['class' => 'ms-2']
            );
        }
        echo html_writer::div(
            html_writer::tag(
                'strong',
                $OUTPUT->pix_icon('i/grades', '', 'moodle', ['class' => 'me-1', 'aria-hidden' => 'true']) . get_string('grade') . ': '
            ) .
            format_float($usergrade, 2) .
            ($grademax !== null ? ' / ' . $grademax : '') .
            $gradepasslabel,
            'videotrack-grade-student alert alert-secondary mt-2'
        );
    } else {
        echo html_writer::div(
            html_writer::tag(
                'strong',
                $OUTPUT->pix_icon('i/grades', '', 'moodle', ['class' => 'me-1', 'aria-hidden' => 'true']) . get_string('grade') . ': '
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
    echo $OUTPUT->notification(
        format_text(
            $notice,
            $videotrack->reactionnoticeformat ?: FORMAT_HTML,
            ['context' => $context, 'trusted' => false]
        ),
        'info'
    );
}
if (in_array($source, ['youtube', 'vimeo'], true)) {
    $providername = get_string('source:' . $source, 'mod_videotrack');
    echo $OUTPUT->notification(get_string('externalproviderprivacy_notice', 'mod_videotrack', $providername), 'info', true);
}

$covered = $state ? (float)$state->uniquecoveredseconds : 0.0;
$percent = $state ? (float)$state->completionpercent : 0.0;
$percentattr = (string)round($percent, 1);
$uniquereactionids = [];
if (!empty($videotrack->reactionsenabled)) {
    // When the student report rows are complete, reuse them instead of issuing.
    // A separate DISTINCT query. If rows were truncated, query distinct ids.
    if ($showstudentreport && !$eventtruncated) {
        foreach ($events as $event) {
            if ((int)$event->reactionid > 0) {
                $uniquereactionids[(int)$event->reactionid] = true;
            }
        }
    } else {
        // The separate DISTINCT query is needed when the student report is hidden.
        // Or truncated to the latest 200 rows; otherwise the in-memory rows are enough.
        $uniquereactionids = array_flip($DB->get_fieldset_select(
            'videotrack_reactev',
            'DISTINCT reactionid',
            $eventwhere . ' AND reactionid > 0',
            $eventparams
        ));
    }
}

echo html_writer::start_div(
    'videotrack-player-shell',
    ['style' => 'max-width:' . (int)$playerwidth . 'px']
);
echo html_writer::start_div('videotrack-layout');

// Player section: left column in landscape, full width in portrait.
echo html_writer::start_div('videotrack-player-section');
echo html_writer::start_div('videotrack-player-wrap');
// Loading placeholder: visible until the JavaScript player initialises.
// Automatically removed when the player is created (YouTube/Vimeo replaces the div).
$loadingtext = get_string('playerloading', 'mod_videotrack');
echo html_writer::div(
    html_writer::tag('span', $loadingtext, ['class' => 'sr-only']) .
    html_writer::tag('div', '', ['class' => 'videotrack-loading-spinner', 'aria-hidden' => 'true']),
    'videotrack-player-loading',
    ['aria-label' => $loadingtext, 'role' => 'status']
);
echo html_writer::div('', '', ['id' => 'mod-videotrack-player']);
// Feature 12: poster preview before playback when configured.
if ($posterurl) {
    echo html_writer::start_div('videotrack-poster-overlay', [
        'id'         => 'videotrack-poster-overlay',
        'role'       => 'none',
        'aria-hidden' => 'true',
    ]);
    echo html_writer::empty_tag('img', [
        'src'   => (string)$posterurl,
        'class' => 'videotrack-poster-img',
        'alt'   => '',
        // Decorative: the player already has the title.
    ]);
    // Accessible play overlay button.
    echo html_writer::tag(
        'button',
        html_writer::tag('span', html_writer::tag('svg', html_writer::tag('path', '', ['d' => 'M8 5v14l11-7z']), ['viewBox' => '0 0 24 24', 'focusable' => 'false', 'aria-hidden' => 'true']), ['class' => 'videotrack-poster-play-icon', 'aria-hidden' => 'true']),
        [ 'type'       => 'button', 'class'      => 'videotrack-poster-play-btn', 'id'         => 'videotrack-poster-play-btn', 'aria-label' => get_string('playbutton_label', 'mod_videotrack'), ]
    );
    echo html_writer::end_div();
}
echo html_writer::end_div(); // Videotrack-player-wrap.

// Visual watched-interval bar (canvas updated by JavaScript).
echo html_writer::tag('canvas', '', [
    'id'         => 'videotrack-interval-bar',
    'width'      => '800',
    'height'     => '24',
    'class'      => 'videotrack-interval-bar mt-1',
    'role'       => 'img',
    'title'      => get_string('intervalbar_title', 'mod_videotrack'),
    'aria-label' => get_string('intervalbar_title', 'mod_videotrack') . ' — ' .
                    format_float($percent, 1) . '%',
    'aria-describedby' => 'videotrack-interval-bar-status',
]);
echo html_writer::tag('progress', format_float($percent, 1) . '%', [
    'id' => 'videotrack-interval-progress',
    'class' => 'videotrack-interval-progress',
    'value' => $percentattr,
    'max' => '100',
    'aria-label' => get_string('intervalbar_title', 'mod_videotrack'),
    'aria-hidden' => 'true',
]);
echo html_writer::tag(
    'span',
    get_string('intervalbar_title', 'mod_videotrack') . ' — ' . format_float($percent, 1) . '%',
    ['id' => 'videotrack-interval-bar-status', 'class' => 'videotrack-interval-bar-status']
);
echo html_writer::end_div(); // Videotrack-player-section.

// Sidebar: progress, reactions and student reactions table.
echo html_writer::start_div('videotrack-sidebar');

// Feature 8: interactive VTT transcript panel (upload source only, with a VTT file).
if (!empty($videotrack->showtranscript) && $vtturl !== null) {
    echo html_writer::start_div('videotrack-transcript-panel', ['id' => 'videotrack-transcript-panel']);
    echo html_writer::tag(
        'h3',
        get_string('transcript_title', 'mod_videotrack'),
        ['class' => 'h6 mt-0 mb-1']
    );
    echo html_writer::div(
        html_writer::tag(
            'p',
            get_string('transcript_loading', 'mod_videotrack'),
            ['class' => 'text-muted small', 'role' => 'status']
        ),
        'videotrack-transcript-content',
        ['id' => 'videotrack-transcript-content']
    );
    echo html_writer::end_div(); // Videotrack-transcript-panel.
}

// Feature 11: collapsible student personal notes panel.
if (!empty($videotrack->studentnotesenabled)) {
    echo html_writer::start_div('videotrack-notes-panel mt-2 mb-2', [
        'id'   => 'videotrack-notes-panel',
        'role' => 'region',
        'aria-label' => get_string('studentnotes_title', 'mod_videotrack'),
    ]);
    // Header with show/hide toggle. The initial aria-label is updated synchronously.
    // By the AMD player from sessionStorage before the panel is used.
    echo html_writer::start_div('videotrack-notes-header d-flex align-items-center justify-content-between mb-1');
    echo html_writer::tag(
        'h3',
        get_string('studentnotes_title', 'mod_videotrack'),
        ['class' => 'h6 mt-0 mb-0']
    );
    echo html_writer::tag(
        'button',
        get_string('notes_hide', 'mod_videotrack'),
        [ 'type'          => 'button', 'id'            => 'videotrack-notes-toggle', 'class'         => 'btn btn-link btn-sm p-0 videotrack-notes-toggle', 'aria-expanded' => 'true', 'aria-controls' => 'videotrack-notes-body', 'aria-label'    => get_string('notes_hide', 'mod_videotrack') . ': ' . get_string('studentnotes_title', 'mod_videotrack'), ]
    );
    echo html_writer::end_div(); // Notes-header.
    // Collapsible body, hidden or shown by the toggle.
    // Data-collapsed is read by installNotesToggle before rendering to avoid a flash.
    echo html_writer::start_div('videotrack-notes-body', [
        'id'             => 'videotrack-notes-body',
        'data-collapsed' => '0',
        // JavaScript overrides this with the sessionStorage value.
    ]);
    // Textarea and Save button, managed by JavaScript.
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
        'aria-describedby' => 'videotrack-note-hint videotrack-note-charcount videotrack-note-live-status',
    ]);
    echo html_writer::tag('button',
        get_string('studentnote_save', 'mod_videotrack'),
        [
            'type'         => 'button',
            'id'           => 'videotrack-note-save',
            'class'        => 'btn btn-sm btn-primary videotrack-note-save',
            'aria-disabled' => 'true',
            // Enabled only during playback, managed by JavaScript.
            'aria-describedby' => 'videotrack-note-hint',
        ]
    );
    // Remaining character counter, updated in real time by JavaScript.
    echo html_writer::tag('span', $notemaxlength . ' ' . get_string('charsremaininglabel', 'mod_videotrack'), [
        'id'         => 'videotrack-note-charcount',
        'class'       => 'videotrack-note-charcount small text-muted ms-2',
    ]);
    // Dedicated live status for threshold announcements. Keeping this separate.
    // From the visual counter avoids verbose announcements on every keystroke.
    echo html_writer::tag('span', '', [
        'id'          => 'videotrack-note-live-status',
        'class'       => 'sr-only visually-hidden',
        'role'        => 'status',
        'aria-live'   => 'polite',
        'aria-atomic' => 'true',
    ]);
    // Notice: the note is saved at the current video timestamp.
    echo html_writer::tag(
        'p',
        get_string('studentnote_hint', 'mod_videotrack'),
        ['id' => 'videotrack-note-hint', 'class' => 'small text-muted mt-1 mb-1']
    );
    // Saved notes list, populated by JavaScript and server-side rendering.
    echo html_writer::start_tag('ol', [
        'id'         => 'videotrack-notes-list',
        'class'      => 'videotrack-notes-list list-unstyled mt-1',
        'aria-label' => get_string('studentnotes_list_label', 'mod_videotrack'),
    ]);
    // Saved notes: limit the main view to the latest notes to avoid heavy pages.
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
        echo html_writer::tag(
            'p',
            get_string('studentnotes_view_limited', 'mod_videotrack', $noteslimit) . ' ' . html_writer::link(new moodle_url('/mod/videotrack/report.php', [ 'id' => $cm->id, 'mode' => 'student', 'userid' => $USER->id, ]), get_string('report:viewfullreport', 'mod_videotrack')),
            ['class' => 'small text-muted']
        );
    }
    foreach ($existingnotes as $note) {
        echo html_writer::start_tag('li', [
            'class'        => 'videotrack-note-item',
            'data-noteid'  => $note->id,
        ]);
        echo html_writer::tag(
            'span',
            videotrack_format_seconds((float)$note->videotime),
            ['class' => 'videotrack-note-time text-muted me-1 small']
        );
        echo html_writer::tag(
            'span',
            s($note->notetext),
            ['class' => 'videotrack-note-text']
        );
        echo html_writer::tag('button',
            get_string('removenote', 'mod_videotrack'),
            [
                'type'       => 'button',
                'class'      => 'btn btn-link btn-sm videotrack-delete-note ms-1',
                'data-noteid'=> $note->id,
                // WCAG 2.4.6: contextual aria-label with the note timestamp.
                'aria-label' => get_string('removenote', 'mod_videotrack') . ' — ' .
                                videotrack_format_seconds((float)$note->videotime),
            ]
        );
        echo html_writer::end_tag('li');
    }
    echo html_writer::end_tag('ol');
    echo html_writer::end_div(); // Videotrack-notes-body.
    echo html_writer::end_div(); // Videotrack-notes-panel.
}

echo html_writer::start_div('videotrack-progress mb-2');
echo html_writer::tag(
    'div',
    get_string('progress', 'mod_videotrack') . ': ' . html_writer::tag('strong', format_float($percent, 2) . '%', [ 'id' => 'videotrack-progress-percent', ])
);
echo html_writer::tag(
    'div',
    get_string('report:uniquecoveredseconds', 'mod_videotrack') . ': ' . html_writer::tag('span', s(videotrack_format_seconds($covered)), ['id' => 'videotrack-covered-seconds'])
);
echo html_writer::tag(
    'div',
    get_string('uniquereactions', 'mod_videotrack') . ': ' . html_writer::tag('span', (string)count($uniquereactionids), ['id' => 'videotrack-unique-reactions'])
);
echo html_writer::end_div(); // Videotrack-progress.

if (!empty($videotrack->reactionsenabled) && $reactions) {
    echo html_writer::start_div('videotrack-reactions', ['id' => 'videotrack-reactions']);
    foreach ($reactions as $reaction) {
        $iconwithlabel  = videotrack_render_reaction_icon($reaction, $context, true);
        $icontype = clean_param((string)($reaction->icontype ?? 'emoji'), PARAM_ALPHA);
        $iconvalue = (string)($reaction->iconvalue ?? '');
        $iconsrc = ($icontype === 'file') ? videotrack_reaction_icon_url($context, $reaction) : '';
        $icontext = ($icontype === 'emoji') ? ($iconvalue !== '' ? $iconvalue : (string)$reaction->label) : '';
        echo html_writer::tag('button', $iconwithlabel, [
            'type'                  => 'button',
            'class'                 => 'btn btn-outline-secondary videotrack-reaction-btn',
            'data-reactionid'       => $reaction->id,
            'data-reactionlabel'    => $reaction->label,
            'data-reactiondesc'     => $reaction->description,
            'data-reactionicontype'  => $icontype,
            'data-reactioniconclass' => $icontype === 'fa' ? $iconvalue : '',
            'data-reactioniconsrc'   => $icontype === 'file' ? $iconsrc : '',
            'data-reactionicontext'  => $icontext,
            'title'                 => $reaction->description,
            // Keep aria-disabled buttons focusable: keyboard and screen reader users.
            // Can activate them to receive the explanatory live-region feedback.
            'aria-disabled'         => 'true',
            'aria-describedby'      => 'videotrack-reactions-hint',
            'aria-label'            => $reaction->label,
        ]);
    }
    // Student explanation: reactions are recorded only during playback.
    // This is by design (requirement item 4).
    echo html_writer::tag(
        'p',
        get_string('reactions_hint', 'mod_videotrack'),
        ['class' => 'videotrack-reactions-hint', 'id' => 'videotrack-reactions-hint']
    );
    echo html_writer::tag('span', '', [
        'id'          => 'videotrack-reactions-live-status',
        'class'       => 'sr-only visually-hidden',
        'role'        => 'status',
        'aria-live'   => 'polite',
        'aria-atomic' => 'true',
    ]);
    echo html_writer::end_div(); // Videotrack-reactions.
}

if ($showstudentreport) {
    // Clear visual separation between personal notes and reactions in the student view.
    echo html_writer::tag(
        'h4',
        get_string('reportstudent', 'mod_videotrack'),
        ['class' => 'h5 mt-3 mb-2 videotrack-reactions-section-heading']
    );
    echo html_writer::start_div('videotrack-reactions-table-wrap');
    // A2: the sr-only caption is enough; removing aria-label avoids assistive technologies.
    // Announcing the table title twice (caption + aria-label).
    echo html_writer::start_tag('table', ['class' => 'generaltable']);
    echo html_writer::tag(
        'caption',
        get_string('reportstudent', 'mod_videotrack'),
        ['class' => 'sr-only']
    );
    echo html_writer::start_tag('thead');
    echo html_writer::tag(
        'tr',
        html_writer::tag('th', get_string('report:timestamp', 'mod_videotrack'), ['scope' => 'col']) . html_writer::tag('th', get_string('report:reaction', 'mod_videotrack'), ['scope' => 'col']) . html_writer::tag('th', get_string('report:description', 'mod_videotrack'), ['scope' => 'col']) . html_writer::tag('th', get_string('report:replay', 'mod_videotrack'), ['scope' => 'col']) . html_writer::tag('th', get_string('report:delete', 'mod_videotrack'), ['scope' => 'col'])
    );
    echo html_writer::end_tag('thead');
    echo html_writer::start_tag('tbody', [
        'id' => 'videotrack-my-reactions',
    ]);
    if (empty($events)) {
        echo html_writer::tag('tr',
            html_writer::tag(
                'td',
                get_string('noreactionsyet', 'mod_videotrack'),
                ['colspan' => '5', 'class' => 'text-muted text-center py-2']
            ),
            // Class used by JavaScript (appendReactionRow) to remove this row.
            // When the first dynamic reaction is added.
            ['class' => 'videotrack-no-reactions-placeholder']
        );
    }
    // Precompute reactionmap for O(1) lookup instead of O(n) per event.
    $reactionmapview = [];
    foreach ($reactions as $r) {
        $reactionmapview[(int)$r->id] = $r;
    }

    if ($eventtruncated) {
        $totalreactions = $DB->count_records_select('videotrack_reactev', $eventwhere, $eventparams);
        $showninfo = (object)['shown' => count($events), 'total' => $totalreactions];
        $reporturl = new moodle_url('/mod/videotrack/report.php', ['id' => $cm->id, 'mode' => 'student']);
        echo html_writer::tag(
            'tr',
            html_writer::tag('td', get_string('report:showingrecentreactionsoftotal', 'mod_videotrack', $showninfo) . ' ' . html_writer::link($reporturl, get_string('report:viewfullreport', 'mod_videotrack')), ['colspan' => '5', 'class' => 'text-muted text-center py-2'])
        );
    }
    foreach ($events as $event) {
        $start    = max(0, (float)$event->videotime - 30);
        $end      = (float)$event->videotime + 30;
        $reaction = $reactionmapview[(int)$event->reactionid] ?? null;
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
            [
                'type'       => 'button',
                'class'      => 'btn btn-secondary btn-sm videotrack-replay',
                'data-start' => $start,
                'data-end'   => $end,
                // WCAG 2.4.6: contextual aria-label distinguishes identical buttons for screen readers.
                'aria-label' => get_string('report:replay', 'mod_videotrack') . ' — ' . $timestampfmt,
            ]
        ));
        echo html_writer::tag('td', html_writer::tag('button',
            get_string('removereaction', 'mod_videotrack'),
            [
                'type'        => 'button',
                'class'       => 'btn btn-link btn-sm videotrack-delete-reaction',
                'data-eventid' => $event->id,
                // WCAG 2.4.6: contextual aria-label distinguishes identical delete buttons for screen readers.
                'aria-label'  => get_string('removereaction', 'mod_videotrack') . ' — ' . $timestampfmt,
            ]
        ));
        echo html_writer::end_tag('tr');
    }
    echo html_writer::end_tag('tbody');
    echo html_writer::end_tag('table');
    echo html_writer::end_div(); // Videotrack-reactions-table-wrap.
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

echo html_writer::end_div(); // Videotrack-sidebar.
echo html_writer::end_div(); // Videotrack-layout.
echo html_writer::end_div(); // Videotrack-player-shell.

echo $OUTPUT->footer();
