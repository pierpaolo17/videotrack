# Architecture

## Overview

Videotrack is split into a Moodle/PHP layer, a server-side domain layer and an AMD client-side layer.

```text
Moodle page/view.php
    -> player AMD module
        -> shared core modules
            -> AJAX services
                -> external API classes
                    -> tracker.php / DB / completion / events
```

## Main components

- `lib.php`: Moodle callbacks, activity CRUD, file areas, gradebook, completion and reset.
- `locallib.php`: application helpers, reaction catalogs, icon rendering, presets, URL parsing.
- `mod_form.php`: activity form, validation, image file picker and reactions.
- `classes/external/*`: AJAX services declared in `db/services.php`.
- `classes/local/tracker.php`: segment normalisation, interval merge, user state, completion, reaction counts.
- `amd/src/core/*`: shared client infrastructure.
- `amd/src/html5_player.js`, `amd/src/player.js`, `amd/src/vimeo_player.js`: player runtimes.

## AJAX services

| Service | Class |
| --- | --- |
| mod_videotrack_save_segment | mod_videotrack\\external\\save_segment |
| mod_videotrack_save_reaction | mod_videotrack\\external\\save_reaction |
| mod_videotrack_delete_reaction | mod_videotrack\\external\\delete_reaction |
| mod_videotrack_delete_note | mod_videotrack\\external\\delete_note |
| mod_videotrack_save_note | mod_videotrack\\external\\save_note |

## Database

| Table | Purpose | Fields | Keys | Indexes |
| --- | --- | --- | --- | --- |
| videotrack | Main table for videotrack instances | id, course, name, intro, introformat, youtubeurl, videoid, videosource, videourl, playbackspeeds, autoplay, loopenabled, startmuted, allowdownload, html5controls, playerwidth, rewindstep, fastforwardstep, captions, captionslang, durationseconds, showcontrols, disablekeyboard, showfullscreen, allowseekforward, allowseekbackward, allowplaybackratechange, resumeplayback, maxplaybackrate, showtranscript, showchapters, studentnotesenabled, countbyvideotime, completionpercent, reactionsenabled, reactionsrequired, minreactions, requireallreactiontypes, completionlogic, clusterwindow, showstudentreport, showreactionnotice, reactionnoticeformat, reactionnotice, grade, gradepass, showgradeto, timemodified, timecreated | primary: id | course_idx: course; videoid_idx: videoid |
| videotrack_seg | Watched segments | id, videotrackid, courseid, cmid, userid, videoid, sessionid, wallclockstart, wallclockend, videotimestart, videotimeend, playbackrate, endreason, timecreated | primary: id | vt_user_idx: videotrackid, userid; session_idx: sessionid; cm_user_idx: cmid, userid; vt_user_sess_time_idx: videotrackid, userid, sessionid, timecreated |
| videotrack_state | Aggregated unique coverage per user and activity | id, videotrackid, courseid, cmid, userid, videoid, lastposition, durationseconds, uniquecoveredseconds, completionpercent, intervaljson, iscompleted, timemodified, timecreated | primary: id | vt_user_uix: videotrackid, userid; cm_user_idx: cmid, userid |
| videotrack_react | Configured reactions per activity | id, videotrackid, reactionkey, label, description, icontype, iconvalue, requiredforcompletion, sortorder, isdeleted, timecreated, timemodified | primary: id | vt_sort_idx: videotrackid, sortorder |
| videotrack_reactev | Reaction click events | id, videotrackid, courseid, cmid, userid, videoid, sessionid, reactionid, reactionkey, reactionlabel, reactiondesc, notetext, notetype, videotime, playbackrate, isdeleted, timecreated, timemodified | primary: id | vt_reaction_idx: videotrackid, reactionid, isdeleted; user_vt_idx: userid, videotrackid, isdeleted; vt_user_sess_time_idx: videotrackid, userid, sessionid, timecreated; vt_user_del_type_time_idx: videotrackid, userid, isdeleted, notetype, timecreated; vt_user_reaction_del_time_idx: videotrackid, userid, reactionid, isdeleted, timecreated; vt_user_note_del_time_idx: videotrackid, userid, notetype, isdeleted, timecreated |
