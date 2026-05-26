<?php

$string['pluginname'] = 'VideoTrack';
$string['modulename'] = 'VideoTrack';
$string['modulenameplural'] = 'VideoTracks';
$string['pluginadministration'] = 'VideoTrack administration';
$string['videotrack:addinstance'] = 'Add a new VideoTrack activity';
$string['videotrack:view'] = 'View VideoTrack';
$string['videotrack:viewreport'] = 'View VideoTrack reports';
$string['videotrack:viewownreport'] = 'View own VideoTrack report';
$string['videoname'] = 'Activity name';
$string['youtubeurl'] = 'YouTube URL';
$string['youtubeurl_help'] = 'Paste a standard YouTube watch URL, short URL, or embed URL.';
$string['showcontrols'] = 'Show player controls';
$string['disablekeyboard'] = 'Disable keyboard shortcuts';
$string['showfullscreen'] = 'Show fullscreen button';
$string['allowseekforward'] = 'Allow seeking forward';
$string['allowseekbackward'] = 'Allow seeking backward';
$string['allowplaybackratechange'] = 'Allow playback rate changes';
$string['countbyvideotime'] = 'Count coverage by video timeline';
$string['countbyvideotime_help'] = 'Recommended. Completion is based on unique covered seconds on the video timeline, not on repeated watching.';
$string['completionpercent'] = 'Required completion percentage';
$string['completiondetail:percent'] = 'Require viewing at least {$a}% of the video';
$string['completiondetail:minreactions'] = 'Require at least {$a} distinct reactions';
$string['completiondetail:allreactiontypes'] = 'Require at least one reaction for each configured reaction type';
$string['reactionsheader'] = 'Reactions';
$string['reactionsenabled'] = 'Enable reactions';
$string['reactionsrequired'] = 'Require reactions';
$string['minreactions'] = 'Minimum distinct reactions';
$string['requireallreactiontypes'] = 'Require at least one reaction for each configured type';
$string['completionlogic'] = 'Completion logic';
$string['logicand'] = 'All enabled conditions (AND)';
$string['logicor'] = 'Any enabled condition (OR)';
$string['clusterwindow'] = 'Cluster window (seconds)';
$string['showstudentreport'] = 'Show report to students';
$string['showreactionnotice'] = 'Show reaction notice';
$string['reactionnotice'] = 'Reaction notice';
$string['reactionlabel'] = 'Reaction label';
$string['reactiondescription'] = 'Reaction description';
$string['reactionicontype'] = 'Icon type';
$string['reactioniconvalue'] = 'Icon value';
$string['reactioniconvalue_help'] = 'For Emoji, enter the emoji character. For Font Awesome, enter a class supported by the Moodle theme, for example fa fa-smile for Font Awesome 5 themes or fa-regular fa-face-smile for Font Awesome 6 themes. Icon availability depends on the active Moodle theme and installed Font Awesome version. Leave this field empty when using an uploaded icon file.';
$string['reactioniconfile'] = 'Reaction icon file';
$string['reactioniconfile_help'] = 'Optional image file used when the icon type is “Uploaded file”. Accepted formats depend on Moodle web image support.';
$string['reactionrequired'] = 'Required for completion';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Font Awesome class';
$string['icontype:file'] = 'Uploaded file';
$string['addreaction'] = 'Add reaction';
$string['invalidyoutubeurl'] = 'Invalid YouTube URL.';
$string['err:minreactionsrequired'] = 'Set a minimum number of distinct reactions or enable the rule requiring all reaction types.';
$string['notice:minreactions'] = 'At least {$a} distinct reactions are required.';
$string['notice:requiredtypes'] = 'Required reaction types: {$a}.';
$string['watch'] = 'Watch';
$string['reportstudent'] = 'My reactions';
$string['reportteacher'] = 'Teacher report';
$string['report:cumulative'] = 'Cumulative';
$string['report:perstudent'] = 'Per student';
$string['report:userid'] = 'User';
$string['report:uniquecoveredseconds'] = 'Unique covered seconds';
$string['report:completionpercent'] = 'Completion %';
$string['report:lastposition'] = 'Last position';
$string['report:iscompleted'] = 'Completed';
$string['report:noattempts'] = 'No viewing data found.';
$string['report:noreactions'] = 'No reaction data found.';
$string['report:timestamp'] = 'Timestamp';
$string['report:reaction'] = 'Reaction';
$string['report:description'] = 'Description';
$string['report:clicks'] = 'Clicks';
$string['report:students'] = 'Students';
$string['report:replay'] = 'Replay fragment';
$string['report:delete'] = 'Delete';
$string['report:sort'] = 'Sort by';
$string['report:sorttime'] = 'Timestamp';
$string['report:sortreaction'] = 'Reaction';
$string['report:sortclicks'] = 'Clicks';
$string['report:aggregation'] = 'Aggregation';
$string['report:aggregationtype'] = 'Same reaction within window';
$string['report:aggregationpeak'] = 'Any reaction peak';
$string['report:exportcsv'] = 'Export CSV';
$string['progress'] = 'Progress';
$string['uniquereactions'] = 'Distinct reactions';
$string['removereaction'] = 'Remove reaction';
$string['playerunavailable'] = 'The player could not be initialized.';
$string['yes'] = 'Yes';
$string['no'] = 'No';
// Used by Moodle core for the activity help link.
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heartbeatinterval'] = 'Heartbeat interval (seconds)';
$string['setting:heartbeatinterval_desc'] = 'How often the player saves the current viewing segment to the server during continuous playback. Lower values reduce the risk of data loss on browser crash or network failure, but increase server load (one AJAX request + two database queries per student per interval). Recommended range: 15–120 seconds. Minimum enforced value: 5 seconds (values below 5 are automatically raised to 5 by the server).';
$string['setting:reactionannouncementinterval'] = 'Reaction accessibility announcement interval (milliseconds)';
$string['setting:reactionannouncementinterval_desc'] = 'Minimum interval, in milliseconds, between repeated “reactions unavailable” screen-reader announcements. Use a lower value for frequent feedback in short videos, or a higher value to reduce repeated announcements. Set to 0 to disable repeated announcements. Recommended range when enabled: 10000–60000 milliseconds. Examples: 10000 = 10 seconds, 30000 = 30 seconds, 60000 = 1 minute.';
$string['setting:reactionreadydebouncems'] = 'Reaction ready debounce (milliseconds)';
$string['setting:reactionreadydebouncems_desc'] = 'Minimum delay, in milliseconds, before repeating the “reactions available” announcement after a rapid pause and resume. Set to 0 to disable this debounce.';
$string['setting:heading_performance'] = 'Performance';
$string['setting:heading_accessibility'] = 'Accessibility';
$string['setting:heading_accessibility_desc'] = 'Settings for assistive technology announcements and keyboard/screen-reader feedback.';
$string['setting:heading_defaults'] = 'Default values for new activities';
$string['setting:heading_defaults_desc'] = 'These values are used as defaults when a teacher creates a new VideoTrack activity. Each activity can still be configured individually.';
$string['setting:default_desc'] = 'Default value for new activities. Can be overridden by the teacher for each individual activity.';
$string['setting:default_completionpercent_desc'] = 'Default minimum percentage of the video the student must watch to complete the activity. Set to 0 to leave the completion rule disabled by default. Can be overridden by the teacher for each individual activity.';
$string['event:segment_saved'] = 'Viewing segment saved';
$string['event:reaction_saved'] = 'Reaction submitted';
$string['event:note_saved'] = 'Student note saved';
$string['event:note_deleted'] = 'Personal note deleted';
$string['event:reaction_deleted'] = 'Reaction deleted';

$string['reactionx'] = 'Reaction {$a}';

$string['err:reactioniconfilerequired'] = 'Upload an icon file when the icon type is set to Uploaded file.';


$string['privacy:metadata:common:timecreated'] = 'Time when the record was created.';
$string['privacy:metadata:common:timemodified'] = 'Time when the record was last modified.';
$string['privacy:metadata:videotrack_seg'] = 'Stores viewing segments recorded for a user in a video activity.';
$string['privacy:metadata:videotrack_seg:userid'] = 'The user whose viewing segment was recorded.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Browser session identifier associated with the viewing segment.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Server time when the segment started.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Server time when the segment ended.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Video timeline position at the start of the segment.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Video timeline position at the end of the segment.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Playback speed used during the segment.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Reason why the segment ended.';
$string['privacy:metadata:videotrack_state'] = 'Stores the aggregated viewing state for a user in a video activity.';
$string['privacy:metadata:videotrack_state:userid'] = 'The user whose aggregate state was stored.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Last known position reached by the user in the video timeline.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Duration of the tracked video in seconds.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Number of unique timeline seconds covered by the user.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Completion percentage calculated for the user.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Merged intervals used to calculate unique coverage.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Whether the activity is currently marked complete for the user.';
$string['privacy:metadata:videotrack_reactev'] = 'Stores reaction events recorded while a user watches the video.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'The user who submitted the reaction.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Browser session identifier associated with the reaction event.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Internal key of the reaction at the time it was recorded.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Reaction label shown to the user when the event was recorded.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Reaction description shown to the user when the event was recorded.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Video timeline position when the reaction was recorded.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Playback speed when the reaction was recorded.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Whether the reaction event was deleted by the user.';

$string['videotrack:viewcoursereport'] = 'View course-level VideoTrack report';
$string['videotrack:viewcoursereport_desc'] = 'Allows the user to view the aggregated VideoTrack report for the whole course.';
$string['videotrack:overrideplayersettings'] = 'Override platform player settings';
$string['videotrack:overrideplayersettings_desc'] = 'Allows the teacher to change player settings (seek, rate, controls, keyboard, fullscreen) that the administrator has set as platform-wide defaults. Revoke this capability to enforce a uniform player policy across the site.';
$string['videotrack:overridecompletionsettings'] = 'Override platform completion settings';
$string['videotrack:overridecompletionsettings_desc'] = 'Allows the teacher to change completion settings (required percentage, cluster window) that the administrator has set as platform-wide defaults. Revoke this capability to enforce uniform completion thresholds across the site.';
$string['setting:lockedbyAdmin'] = 'These settings are locked by the platform administrator and cannot be changed for individual activities.';
$string['setting:heading_presets'] = 'Reaction presets';
$string['setting:heading_presets_desc'] = 'Site-wide sets of reactions that teachers can use as a starting point when configuring a new VideoTrack activity.';
$string['reactionpreset'] = 'Apply a reaction preset';
$string['reactionpreset_help'] = 'Select a preset to pre-fill the reaction fields below. You can freely edit the values after applying the preset. Leave blank to configure reactions manually.';
$string['reactionpreset:none'] = '— configure manually —';
$string['presets:manage'] = 'Manage reaction presets';
$string['presets:pagetitle'] = 'VideoTrack — Reaction presets';
$string['presets:intro'] = 'Define site-wide reaction presets that teachers can use as a starting point when creating a VideoTrack activity. Reactions are copied into the activity and can be freely edited by the teacher.';
$string['presets:addpreset'] = 'Add preset';
$string['presets:backtolist'] = 'Back to preset list';
$string['presets:saved'] = 'Preset saved.';
$string['presets:deleted'] = 'Preset deleted.';
$string['presets:notfound'] = 'Preset not found.';
$string['presets:noneyet'] = 'No reaction presets have been configured yet.';
$string['presets:confirmdelete'] = 'Are you sure you want to delete this preset?';
$string['presets:presetdetails'] = 'Preset details';
$string['presets:name'] = 'Preset name';
$string['presets:key'] = 'Preset key';
$string['presets:key_help'] = 'Unique identifier (letters, numbers and underscores only). Cannot be changed after creation.';
$string['presets:reactions'] = 'Reactions';
$string['presets:reactions_help'] = 'Leave the label empty to skip a row. File-based icons are not supported in presets.';
$string['presets:col_name'] = 'Name';
$string['presets:col_key'] = 'Key';
$string['presets:col_reactions'] = 'Reactions';
$string['presets:col_actions'] = 'Actions';

$string['reset:userdata'] = 'Delete all student viewing data (segments, states, reactions)';
$string['report:recalculate'] = 'Recalculate all completion states';
$string['report:recalculated'] = 'Completion states recalculated for {$a} users.';
$string['report:heatmap_desc'] = 'Reaction heatmap on video timeline (bar height = number of clicks at that point):';
$string['report:heatmap_supplementary'] = 'The heatmap is a supplementary visualisation. The complete cluster data is available in the table below.';
$string['event:activity_completed'] = 'VideoTrack activity completed';

$string['reactioniconfile_notice'] = 'The image will be automatically resized to 64×64 pixels (center crop). For best results, upload a square image (1:1 ratio). Accepted formats: JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Click a reaction button while the video is playing to record your reaction at that moment.';

$string['showgradeto'] = 'Show grade to student';
$string['showgradeto_help'] = 'If enabled, the student will see their grade and pass/fail status directly in the activity page.';
$string['report:grade'] = 'Grade';
$string['report:gradesaved'] = 'Grade saved successfully.';
$string['report:gradepass_hint'] = 'Pass mark: {$a}';
$string['report:gradenotset'] = 'Not graded yet';

$string['videosource'] = 'Video source';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Upload (MP4 / WebM / MP3)';
$string['vimeourl'] = 'Vimeo URL';
$string['vimeourl_help'] = 'Paste the URL of the Vimeo video (e.g. https://vimeo.com/123456789).';
$string['invalidvimeourl'] = 'The URL does not appear to be a valid Vimeo video URL.';
$string['videofile'] = 'Video / audio file';
$string['videofile_help'] = 'Upload an MP4, WebM, MP3, M4V, MOV, AAC or M4A file.';
$string['videofile_notice'] = 'Accepted formats: MP4, WebM, MP3, M4V, MOV, AAC, M4A. The file is stored securely on this Moodle server and served only to enrolled students.';
$string['setting:heading_player'] = 'Player behaviour';
$string['setting:playbackspeeds'] = 'Available playback speeds';
$string['setting:playbackspeeds_desc'] = 'Select which playback speeds are available across the site. Teachers can restrict this list for individual activities (if they have the override capability). The value 1× (normal) is always recommended.';
$string['setting:playbackspeeds_teacher_desc'] = 'Select which playback speeds to allow for this activity. Only speeds enabled at site level are shown. Leave all selected to use the site default.';
$string['setting:speed_normal'] = 'normal';
$string['setting:distractionfree'] = 'Distraction-free mode';
$string['setting:distractionfree_desc'] = 'When enabled, the Moodle header, footer and navigation are hidden when a student views the activity. Useful for embedded or kiosk environments.';
$string['intervalbar_title'] = 'Watched intervals — green segments indicate portions of the video you have already watched.';
$string['outline:percent'] = '{$a}% watched';
$string['outline:nodata'] = 'No viewing data recorded yet.';
$string['coursereport:title'] = 'VideoTrack — Course report';
$string['coursereport:navlink'] = 'VideoTrack reports';
$string['coursereport:intro'] = 'Overview of all VideoTrack activities in this course.';
$string['coursereport:nodata'] = 'No VideoTrack activities found in this course.';
$string['coursereport:col_activity'] = 'Activity';
$string['coursereport:col_source'] = 'Source';
$string['coursereport:col_duration'] = 'Duration';
$string['coursereport:col_students_started'] = 'Students started';
$string['coursereport:col_avg_percent'] = 'Avg. coverage';
$string['coursereport:col_completions'] = 'Completions';
$string['coursereport:col_reactions'] = 'Reactions';
$string['coursereport:col_actions'] = 'Actions';

$string['grade:pass'] = 'Pass';
$string['grade:fail'] = 'Fail';

$string['autoplay'] = 'Autoplay';
$string['autoplay_help'] = 'Start the video automatically when the page loads. Note: browsers require the video to be muted for autoplay to work reliably. Enabling autoplay will automatically enable Start muted.';
$string['loop'] = 'Loop';
$string['startmuted'] = 'Start muted';
$string['startmuted_help'] = 'Start playback with the audio muted. Students can unmute manually. Required by most browsers when Autoplay is enabled.';
$string['allowdownload'] = 'Allow download (upload source only)';
$string['setting:allowdownload_desc'] = 'Show a download button in the HTML5 player and allow right-click download of uploaded video/audio files.';
$string['setting:heading_playerbehavior'] = 'Default player behaviour';
$string['setting:heading_playerbehavior_desc'] = 'Default values for autoplay, loop, mute and download for new activities. Teachers can override these if they have the player settings override capability.';
$string['setting:heading_html5controls'] = 'HTML5 player controls (upload source)';
$string['setting:heading_html5controls_desc'] = 'Select which controls are available in the custom HTML5 player bar. This applies only to activities using the Upload video source. Teachers can restrict this list for individual activities.';
$string['setting:html5controls'] = 'Available controls';
$string['setting:html5controls_desc'] = 'Select which controls to show in the HTML5 player. Teachers can choose from these controls for individual activities.';
$string['setting:html5controls_teacher_desc'] = 'Select which controls to show in the player. Only controls enabled at site level are available. Leave all checked to use the site default.';
$string['ctrl:play'] = 'Play / Pause';
$string['ctrl:progress'] = 'Progress bar';
$string['ctrl:current'] = 'Current time';
$string['ctrl:duration'] = 'Duration';
$string['ctrl:mute'] = 'Mute button';
$string['ctrl:volume'] = 'Volume slider';
$string['ctrl:speed'] = 'Playback speed';
$string['ctrl:pip'] = 'Picture-in-Picture';
$string['ctrl:fullscreen'] = 'Fullscreen';
$string['ctrl:download'] = 'Download button';

$string['setting:playerwidth'] = 'Maximum player width (px)';
$string['setting:playerwidth_desc'] = 'Maximum width of the video player in pixels (1–4096). Teachers can override this for individual activities (instance value 0 means use the site default). Recommended: 960.';
$string['playerwidth'] = 'Maximum player width (px)';
$string['playerwidth_help'] = 'Sets the maximum width of the video player for this activity in pixels. Leave 0 to use the platform default.';
$string['playerwidth_zero_note'] = 'Enter 0 to inherit the platform default, or enter a value from 1 to 4096 pixels for this activity.';
$string['setting:rewindstep'] = 'Rewind step (seconds)';
$string['setting:rewindstep_desc'] = 'How many seconds the rewind button skips back by default. Teachers can override this for individual activities. Set 0 to hide rewind buttons by default; activity overrides can still re-enable them. Default: 10. Important: if "Allow seek backward" is disabled for an activity, the rewind button will not appear even if this value is > 0.';
$string['rewindstep'] = 'Rewind step (seconds)';
$string['rewindstep_help'] = 'How many seconds the rewind button skips back for this activity. Leave 0 to use the platform default. If the platform default is 0, the button is hidden unless this activity sets its own value. Note: if "Allow seek backward" is disabled for this activity, the rewind button will not appear regardless of this value — the two settings work together.';
$string['setting:fastforwardstep'] = 'Fast-forward step (seconds)';
$string['setting:fastforwardstep_desc'] = 'How many seconds the fast-forward button skips forward by default. Teachers can override this for individual activities. Set 0 to hide fast-forward buttons by default; activity overrides can still re-enable them. Default: 10. Important: if "Allow seek forward" is disabled for an activity, the fast-forward button will not appear even if this value is > 0.';
$string['fastforwardstep'] = 'Fast-forward step (seconds)';
$string['fastforwardstep_help'] = 'How many seconds the fast-forward button skips ahead for this activity. Leave 0 to use the platform default. If the platform default is 0, the button is hidden unless this activity sets its own value. Note: if "Allow seek forward" is disabled for this activity, the fast-forward button will not appear regardless of this value — the two settings work together.';
$string['captionsheader'] = 'Captions and subtitles';
$string['captions'] = 'Enable captions / subtitles';
$string['captions_help'] = 'When enabled: YouTube — captions are shown by default; Vimeo — the track matching the language code is activated (must be pre-loaded on Vimeo.com); Upload — the attached VTT file is used.';
$string['setting:default_captions_desc'] = 'Enable captions / subtitles by default for new activities. Teachers can override for individual activities.';
$string['captionslang'] = 'Default caption language';
$string['captionslang_help'] = 'ISO 639-1 language code (e.g. en, it, de). For YouTube it sets the preferred language. For Vimeo it selects the pre-loaded track. For Upload, enter the language of the VTT file.';
$string['setting:captionslang_desc'] = 'Default caption language code (ISO 639-1, e.g. en, it). Teachers can override for individual activities.';
$string['vttfile'] = 'Subtitle file (.vtt)';
$string['vttfile_help'] = 'Upload a WebVTT (.vtt) subtitle file. The file will be served to the student\'s browser and displayed as subtitles in the video player.';
$string['vttfile_notice'] = 'Accepted format: WebVTT (.vtt). Only one file is supported. The file must match the language code specified above.';
$string['vimeo_captions_notice'] = 'Vimeo captions are managed on Vimeo.com. Upload your subtitle tracks there. The language code set above will be used to activate the matching track automatically.';
$string['ctrl:rewind'] = 'Rewind button';
$string['ctrl:fastforward'] = 'Fast-forward button';

$string['playerloading'] = 'Video player loading, please wait…';
$string['noreactionsyet'] = 'No reactions recorded yet. React while the video is playing.';
$string['reaction:error'] = 'Could not save your reaction. Please try again.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback']          = 'Resume playback';
$string['resumeplayback_desc']     = 'Automatically resume video from where the student left off in their last session.';
$string['resumeplayback_help']     = 'When enabled, the video will start from the last saved position (if more than 5 seconds into the video). Students can always seek to the beginning manually.';
$string['setting:resumeplayback']  = 'Resume playback (default)';
$string['setting:resumeplayback_desc'] = 'Default setting for new videotrack activities. Teachers can override this per-activity.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate']              = 'Maximum playback rate';
$string['maxplaybackrate_desc']         = 'Limit the maximum video speed that students can select. 0 = no limit.';
$string['maxplaybackrate_help']         = 'When set, students cannot play the video faster than this speed even if the player controls allow higher values. This discourages rushing through content.';
$string['maxplaybackrate_nolimit']      = 'No limit';
$string['setting:maxplaybackrate']      = 'Maximum playback rate (default)';
$string['setting:maxplaybackrate_desc'] = 'Default maximum playback rate for new activities. Teachers can override per-activity.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript']           = 'Show interactive transcript';
$string['showtranscript_desc']      = 'Display a scrollable, clickable transcript panel next to the video (requires a VTT subtitle file).';
$string['showtranscript_help']      = 'Parses the uploaded VTT subtitle file and renders it as a clickable list. Each entry shows the timestamp and text; clicking jumps the video to that point. The active cue is highlighted and scrolls into view automatically.';
$string['transcript_title']         = 'Transcript';
$string['transcript_unavailable'] = 'Transcript is not available for this video.';
$string['transcript_loading']       = 'Loading transcript…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel']         = 'Click the video to start playback.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['sdkerrorlabel'] = 'The video player could not be loaded. This may be caused by an ad-blocker, Content Security Policy or network restriction. Please disable content blockers or contact your administrator.';
$string['vimeocspwarnlabel']        = 'The Vimeo player could not be loaded. Please check your network connection, or ask your administrator to allow player.vimeo.com in the Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel']              = 'Resuming from';
// ── Report: azioni studente ──
$string['report:actions'] = 'Actions';
$string['report:resetstudent'] = 'Reset progress';
$string['report:resetstudent_confirm'] = 'Are you sure you want to reset this student\'s progress? This will delete all their viewing history and reactions and cannot be undone.';
$string['report:studentreset'] = 'Student progress has been reset.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Show chapter navigation';
$string['showchapters_desc'] = 'Display a navigation bar with chapter markers extracted from the VTT file. Chapters are VTT cues with text shorter than 80 characters.';
$string['showchapters_help'] = 'If the uploaded VTT file contains short cues (under 80 characters), they are interpreted as chapter titles and rendered as a clickable navigation bar above the video controls. Clicking a chapter jumps to that point.';
$string['chapters_label'] = 'Video chapters';
$string['chapters_unavailable'] = 'Chapters are not available for this video.';
$string['chapter_label'] = 'Chapter';
$string['studentnotesenabled'] = 'Enable student notes';
$string['studentnotesenabled_desc'] = 'Allow students to write personal timestamped notes while watching the video.';
$string['studentnotesenabled_help'] = 'When enabled, a text area appears next to the video. Students can type a note and save it at the current video timestamp. Notes are visible only to the student who wrote them (and to managers via the report). Notes can be deleted by the student.';
$string['setting:studentnotesenabled'] = 'Enable student notes (default)';
$string['setting:studentnotesenabled_desc'] = 'Default setting for new videotrack activities. Teachers can override this per-activity.';
$string['setting:notemaxlength'] = 'Maximum note length';
$string['setting:notemaxlength_desc'] = 'Maximum number of characters allowed for each personal student note. Default: 2000.';
$string['studentnotes_title'] = 'My notes';
$string['studentnote_placeholder'] = 'Write a note at this moment in the video…';
$string['studentnote_save'] = 'Save note';
$string['studentnote_hint'] = 'The note will be saved at the current video timestamp. The video must be playing.';
$string['studentnotes_list_label'] = 'Saved notes';
$string['studentnote_label'] = 'Student note';
$string['noteerrorlabel'] = 'Could not save note. Please try again.';
$string['notesavedlabel'] = 'Note saved.';
$string['notedeletedlabel'] = 'Note removed.';
$string['noteplaybackrequiredlabel'] = 'Start playback before saving a note.';
$string['charsremaininglabel'] = 'characters remaining';
$string['posterimage'] = 'Poster / preview image';
$string['posterimage_help'] = 'Upload an image to display as a preview before the video starts. The image is shown until the student clicks play. Accepted formats: JPG, PNG, WebP, GIF. Recommended size: 1280×720px (16:9).';
$string['posterimage_notice'] = 'The poster image is shown before playback starts. It is hidden automatically when the video plays.';
$string['playbutton_label'] = 'Play video';
$string['setting:maxplaybackrate_nolimit'] = 'No limit';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'The text of a personal note written by the student at a specific video timestamp.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'The type of event: empty for standard reactions, "note" for personal student notes.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['reactionsdisabled'] = 'Reactions are disabled for this VideoTrack activity. Ask your teacher or course administrator to enable reactions if they are required.';
$string['studentnotesdisabled'] = 'Student notes are not enabled for this activity.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'No video file has been uploaded for this activity.';
$string['removenote'] = 'Remove note';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Hide notes';
$string['notes_show'] = 'Show notes';
$string['report:notes_title'] = 'Student notes';
$string['report:nonotes'] = 'No notes have been written for this activity.';
$string['report:notedate'] = 'Written on';
$string['report:exportnotes_csv'] = 'Export notes as CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Dismiss';
$string['status:default'] = 'Status update.';
$string['status:error'] = 'An error occurred. Please try again.';
$string['rewindlabel'] = 'Rewind';
$string['fastforwardlabel'] = 'Fast-forward';
$string['secondslabel'] = 'seconds';
$string['removenotelabel'] = 'Remove note';
// ── Help strings ──
$string['gradepass_help'] = 'The minimum grade required to pass this activity. Students who achieve this grade or higher are considered to have passed.';


$string['completiondetail:requiredreactions'] = 'Must include these required reactions: {$a}';

$string['error:playbackrequired'] = 'The video must be playing before this action can be saved.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'GD PHP extension not available.';
$string['setting:gd_missing_desc'] = 'Reaction icon images uploaded by teachers will NOT be automatically resized to 64×64 pixels. The original file will be served as-is, which may affect page load performance for large images. To enable automatic resizing, ask your server administrator to install the php-gd package.';

$string['report:heatmap_legend'] = 'Reaction heatmap colour legend';

$string['report:clusterlimitreached'] = 'The report reached the maximum number of clusters displayed. Use filters or a narrower time window for a complete analysis.';

$string['report:showingrecentreactionsoftotal'] = 'Showing {$a->shown} of {$a->total} reactions, from oldest to newest.';

$string['report:viewfullreport'] = 'View the full report';
$string['studentnotes_view_limited'] = 'Showing the latest {$a} notes. Open the full report to review all notes.';
$string['report:skiptoheatmaptable'] = 'Skip heatmap and go to the data table';
$string['report:heatmap_textsummary'] = 'The chart contains {$a->clusters} clusters; the largest cluster contains {$a->max} clicks.';
$string['err:reactioniconvaluerequired'] = 'Enter an emoji or a Font Awesome class.';
$string['err:reactioniconvalueinvalidfa'] = 'Enter only valid Font Awesome class names, using letters, numbers, spaces and hyphens.';

$string['error:reactionratelimit'] = 'Too many reactions were submitted in a short time. Please continue watching the video and try again.';
$string['event:student_progress_reset'] = 'Student VideoTrack data reset';
$string['report:timefrom'] = 'From second';
$string['report:timeto'] = 'To second';
$string['report:clusterlimitreached_help'] = 'The cumulative report reached the cluster display limit. Use the user, reaction or video-time filters to narrow the analysis and retrieve later clusters.';
$string['report:topclusterssummary'] = 'Most relevant clusters in this selection:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} clicks';
$string['error:notesratelimit'] = 'Too many notes were submitted in a short time. Please wait before adding another note.';

$string['privacy:segmentschunk'] = 'Video viewing segments - part {$a}';

$string['privacy:reactionsactivechunk'] = 'Active reactions - part {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Deleted reactions - part {$a}';

$string['privacy:notesactivechunk'] = 'Active notes - part {$a}';

$string['privacy:notesdeletedchunk'] = 'Deleted notes - part {$a}';

$string['report:clusterlimitreached_csv'] = 'WARNING: the cluster limit was reached. The export may be incomplete; apply user, reaction or time filters and export again.';

$string['report:notecreatedfrom'] = 'Notes from date';

$string['report:notecreatedto'] = 'Notes to date';

$string['reactionsavailableonlyduringplayback'] = 'Reactions are available only during video playback.';
$string['reactionsreadyannounce'] = 'Reactions are now available.';

$string['privacy:state'] = 'Completion state';

$string['report:clusterlimitrequiresfilters'] = 'The cumulative report is partial. Apply a video-time range filter to retrieve the remaining clusters reliably.';

$string['report:clusterlimitrequiresfilters_csv'] = 'The cumulative export is partial because no video-time range filter was applied. Apply From second/To second filters and export again.';
$string['report:clusterexportblocked_csv'] = 'The export was stopped to avoid returning incomplete data. Apply a video-time range filter and export again.';
$string['report:clusterdisplayblocked'] = 'The cluster table has been hidden to avoid showing incomplete data. Apply a video-time range filter to continue.';
$string['unknownreaction'] = 'Unknown reaction';
$string['externalprovider_notice'] = 'External video providers such as YouTube and Vimeo may process personal data and set cookies according to their own privacy policies. Use uploaded files when third-party transfer is not allowed.';
$string['privacy:metadata:youtube'] = 'When a YouTube video is used, the user browser connects to YouTube to load and play the video.';
$string['privacy:metadata:youtube:videoid'] = 'The YouTube video identifier configured for this activity.';
$string['privacy:metadata:youtube:url'] = 'The YouTube URL configured for this activity.';
$string['privacy:metadata:vimeo'] = 'When a Vimeo video is used, the user browser connects to Vimeo to load and play the video.';
$string['privacy:metadata:vimeo:videoid'] = 'The Vimeo video identifier configured for this activity.';
$string['privacy:metadata:vimeo:url'] = 'The Vimeo URL configured for this activity.';

$string['html5:controls'] = 'Video controls';
$string['html5:play'] = 'Play';
$string['html5:pause'] = 'Pause';
$string['html5:seek'] = 'Seek';
$string['html5:volume'] = 'Volume';
$string['html5:mute'] = 'Mute';
$string['html5:unmute'] = 'Unmute';
$string['html5:speed'] = 'Speed';
$string['html5:pip'] = 'Picture-in-picture';
$string['html5:fullscreen'] = 'Fullscreen';
$string['html5:download'] = 'Download';

// GDPR retention and academic-integrity.
$string['setting:heading_privacy'] = 'Privacy and data retention';
$string['setting:heading_privacy_desc'] = 'Configure how VideoTrack stores tracking, notes and reaction data.';
$string['setting:retentionperioddays'] = 'Retention period for tracking data (days)';
$string['setting:retentionperioddays_desc'] = 'Number of days after which VideoTrack anonymises old tracking, notes and reaction data (including free-text reaction labels) for retention cleanup. Set to 0 to retain data indefinitely. User erasure requests handled through the Moodle Privacy API permanently delete the user\'s tracking, state, reaction and note records for the selected context.';
$string['setting:strictsessionvalidation'] = 'Require same browser session for note and reaction validation';
$string['setting:validationfallbackdays'] = 'Historical playback validation window (days)';
$string['setting:validationfallbackdays_desc'] = 'Maximum age, in days, for previously watched segments that may authorise notes and reactions after a refresh or browser change. Set to 0 to allow historical watched segments indefinitely; this improves usability but makes academic-integrity validation more permissive. The same-session and recent-playback checks are always attempted first.';
$string['setting:strictsessionvalidation_desc'] = 'When enabled, notes and reactions can only be saved for timestamps watched in the current browser session. When disabled, VideoTrack accepts timestamps already watched by the same user in the same activity, improving usability after refreshes or browser changes while still rejecting unwatched timestamps.';
$string['task:cleanup'] = 'Anonymise expired VideoTrack tracking data';
$string['privacy:anonymised'] = '[anonymised]';
$string['error:playbackpositionnotwatched'] = 'This video position has not been watched yet, so the action cannot be saved.';

$string['setting:intrangerequired'] = 'Enter a whole number between {$a->min} and {$a->max}.';
$string['err:playerwidthrequired'] = 'Enter 0 to use the platform default, or a whole number from 1 to 4096 pixels.';
$string['err:playbacksteprequired'] = 'Enter a whole number from 0 to 300 seconds. Use 0 for the platform default.';
$string['setting:nonnegativeintrequired'] = 'Enter a whole number greater than or equal to 0.';

$string['report:anonymiseduser'] = 'Anonymised user';

$string['report:exportnotes_privacywarning'] = 'This export may contain personal data from student notes. Download and store it only when you have a valid purpose and delete it when it is no longer needed.';

$string['invalidvideosource'] = 'Invalid video source.';
$string['report:gradeinputfor'] = 'Grade for {$a}';
$string['report:savegradefor'] = 'Save grade for {$a}';
$string['report:gradepassed'] = 'Passed';
$string['report:gradefailed'] = 'Not passed';
$string['report:exportnotes_confirm'] = 'I confirm that this notes export may contain personal data and that I have a valid purpose for downloading it.';
$string['report:exportnotes_confirmrequired'] = 'Confirm the personal-data export notice before downloading notes.';

$string['privacy:videoid_export_note'] = 'Video/content identifier: {$a}';
$string['privacy:anonymisedreaction'] = 'Anonymised reaction';
$string['coursereport:avgcoverage'] = 'Average coverage: {$a}%';

$string['report:exportnotes_csv_personaldata'] = 'Export notes as CSV, including possible personal data';

$string['presets:deletearia'] = 'Delete preset {$a}';
$string['presets:reactionlabelaria'] = 'Reaction {$a}: label';
$string['presets:reactiondescriptionaria'] = 'Reaction {$a}: description';
$string['presets:reactionicontypearia'] = 'Reaction {$a}: icon type';
$string['presets:reactioniconvaluearia'] = 'Reaction {$a}: icon value';
$string['presets:reactionrequiredaria'] = 'Reaction {$a}: required for completion';
$string['err:reactionpresetjson'] = 'The reaction preset data is invalid. Reload the page and try again.';
$string['presets:reactionstablecaption'] = 'Reaction preset rows';
$string['privacy:intervals_none'] = 'No viewing intervals recorded.';
$string['privacy:intervals_unavailable'] = 'Viewing intervals unavailable or invalid.';
