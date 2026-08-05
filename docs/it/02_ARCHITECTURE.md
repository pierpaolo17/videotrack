# Architettura

## Panoramica

Videotrack è diviso in layer Moodle/PHP, layer dominio server-side e layer AMD client-side.

```text
Moodle page/view.php
    -> player AMD module
        -> shared core modules
            -> AJAX services
                -> external API classes
                    -> tracker.php / DB / completion / events
```

## Componenti principali

- `lib.php`: callback Moodle, CRUD attività, file area, gradebook, completion e reset.
- `locallib.php`: helper applicativi, cataloghi reazioni, rendering icone, preset, parsing URL.
- `mod_form.php`: form impostazioni attività, validazione, file picker immagini e reazioni.
- `classes/external/*`: servizi AJAX dichiarati in `db/services.php`.
- `classes/local/tracker.php`: normalizzazione segmenti, merge intervalli, stato utente, completion, conteggio reazioni.
- `classes/local/csv_export.php`: separatore CSV, colonne configurabili, visibilità dei campi profilo e generazione sicura delle righe.
- `amd/src/core/*`: infrastruttura client condivisa.
- `amd/src/html5_player.js`, `amd/src/player.js`, `amd/src/vimeo_player.js`: runtime player.

L'istanza `videotrack` memorizza anche `blockedseekplaybackrate`, `csvdelimiter` e `csvexportfields`; gli ultimi due controllano formato CSV e colonne di esportazione della singola attività.

## Servizi AJAX

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
| videotrack | Main table for videotrack instances | id, course, name, intro, introformat, youtubeurl, videoid, videosource, videourl, playbackspeeds, autoplay, loopenabled, startmuted, allowdownload, html5controls, playerwidth, rewindstep, fastforwardstep, captions, captionslang, durationseconds, showcontrols, disablekeyboard, showfullscreen, allowseekforward, allowseekbackward, allowplaybackratechange, resumeplayback, maxplaybackrate, blockedseekplaybackrate, showtranscript, showchapters, studentnotesenabled, csvdelimiter, csvexportfields, countbyvideotime, completionpercent, reactionsenabled, reactionsrequired, minreactions, requireallreactiontypes, completionlogic, clusterwindow, showstudentreport, showreactionnotice, reactionnoticeformat, reactionnotice, grade, gradepass, showgradeto, timemodified, timecreated | primary: id | course_idx: course; videoid_idx: videoid |
| videotrack_seg | Watched segments | id, videotrackid, courseid, cmid, userid, videoid, sessionid, wallclockstart, wallclockend, videotimestart, videotimeend, playbackrate, endreason, timecreated | primary: id | vt_user_idx: videotrackid, userid; session_idx: sessionid; cm_user_idx: cmid, userid; vt_user_sess_time_idx: videotrackid, userid, sessionid, timecreated |
| videotrack_state | Aggregated unique coverage per user and activity | id, videotrackid, courseid, cmid, userid, videoid, lastposition, durationseconds, uniquecoveredseconds, completionpercent, intervaljson, iscompleted, timemodified, timecreated | primary: id | vt_user_uix: videotrackid, userid; cm_user_idx: cmid, userid |
| videotrack_react | Configured reactions per activity | id, videotrackid, reactionkey, label, description, icontype, iconvalue, requiredforcompletion, sortorder, isdeleted, timecreated, timemodified | primary: id | vt_sort_idx: videotrackid, sortorder |
| videotrack_reactev | Reaction click events | id, videotrackid, courseid, cmid, userid, videoid, sessionid, reactionid, reactionkey, reactionlabel, reactiondesc, notetext, notetype, videotime, playbackrate, isdeleted, timecreated, timemodified | primary: id | vt_reaction_idx: videotrackid, reactionid, isdeleted; user_vt_idx: userid, videotrackid, isdeleted; vt_user_sess_time_idx: videotrackid, userid, sessionid, timecreated; vt_user_del_type_time_idx: videotrackid, userid, isdeleted, notetype, timecreated; vt_user_reaction_del_time_idx: videotrackid, userid, reactionid, isdeleted, timecreated; vt_user_note_del_time_idx: videotrackid, userid, notetype, isdeleted, timecreated |

## Confine analytics per istanza (1.6.0)

Gli analytics costituiscono un livello di sola lettura sopra `videotrack_seg` e `videotrack_reactev`. Il servizio `analytics` gestisce aggregazione degli intervalli e mascheramento privacy; `report.php` gestisce ambito capability/gruppi e presentazione. Il livello non scrive stati, non modifica la completion e non richiama codice dei player. Dalla 1.6.7 `analytics_scope` risolve l’identità tecnica del video e individua le istanze corrispondenti, verificando `mod/videotrack:viewreport` su ogni contesto modulo. Nella vista tra corsi `report.php` costruisce un ambito separato per istanza, applica i relativi gruppi consentiti e poi ordina i record per `userid`, così `analytics` unifica lo stesso utente Moodle prima dell’aggregazione.
