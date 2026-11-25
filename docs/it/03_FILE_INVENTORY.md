# Inventario dei file distribuiti

Questo inventario è generato sull'albero repository VideoTrack 1.7.272. Elenca
sorgenti, asset, test e guide di release selezionate, inclusi sei file CI/tooling
solo repository. Il JavaScript canonico è in `amd/src`; `amd/build` è generato.

Voci: **427** — 421 file distribuiti e sei solo repository. L'albero completo ha
519 file repository / 493 distribuiti, includendo tutta la documentazione.

| File | Responsabilità |
|---|---|
| `.gitattributes` | Attributi testuali/binari del repository. |
| `.github/capture_native_amd.py` | Raccolta CI dell’output Grunt Moodle nativo, con controlli su sorgenti/commit e mappe. |
| `.github/lifecycle/run.sh` | Runner solo repository: installazioni storiche reali, upgrade/ripetizione e uninstall popolato sul sito CI temporaneo. |
| `.github/lifecycle/site.php` | Fixture protetta, asserzioni di conservazione e controlli dei residui operativi dopo uninstall, solo repository. |
| `.github/static-analysis/composer.json` | Ambiente strumenti PHPStan/Psalm versionato e riservato alla CI del repository. |
| `.github/workflows/ci.yml` | Matrice GitHub Actions del repository, esclusa dagli archivi Moodle tramite `export-ignore`. |
| `.gitignore` | Impedisce il commit degli artifact CI e dei report server generati; è escluso dagli archivi di release. |
| `.moodleignore` | File esclusi dal pacchetto Moodle, inclusa l'evidenza CI/server generata. |
| `amd/build/completion_requirements.min.js` | Build AMD minificata generata per la presentazione raggruppata dei requisiti di completamento tramite Grunt Moodle. |
| `amd/build/completion_requirements.min.js.map` | Source map generata per la build AMD `completion_requirements`; non modificare manualmente. |
| `amd/build/core/adapter.min.js` | Build AMD minificata generata per `core/adapter` tramite Grunt Moodle. |
| `amd/build/core/adapter.min.js.map` | Source map generata per la build AMD `core/adapter`; non modificare manualmente. |
| `amd/build/core/api.min.js` | Build AMD minificata generata per `core/api` tramite Grunt Moodle. |
| `amd/build/core/api.min.js.map` | Source map generata per la build AMD `core/api`; non modificare manualmente. |
| `amd/build/core/api/error.min.js` | Build AMD minificata generata per `core/api/error` tramite Grunt Moodle. |
| `amd/build/core/api/error.min.js.map` | Source map generata per la build AMD `core/api/error`; non modificare manualmente. |
| `amd/build/core/api/retry.min.js` | Build AMD minificata generata per `core/api/retry` tramite Grunt Moodle. |
| `amd/build/core/api/retry.min.js.map` | Source map generata per la build AMD `core/api/retry`; non modificare manualmente. |
| `amd/build/core/api/scope.min.js` | Build AMD minificata generata per `core/api/scope` tramite Grunt Moodle. |
| `amd/build/core/api/scope.min.js.map` | Source map generata per la build AMD `core/api/scope`; non modificare manualmente. |
| `amd/build/core/api/transport.min.js` | Build AMD minificata generata per `core/api/transport` tramite Grunt Moodle. |
| `amd/build/core/api/transport.min.js.map` | Source map generata per la build AMD `core/api/transport`; non modificare manualmente. |
| `amd/build/core/api/validator.min.js` | Build AMD minificata generata per `core/api/validator` tramite Grunt Moodle. |
| `amd/build/core/api/validator.min.js.map` | Source map generata per la build AMD `core/api/validator`; non modificare manualmente. |
| `amd/build/core/beacon.min.js` | Build AMD minificata generata per `core/beacon` tramite Grunt Moodle. |
| `amd/build/core/beacon.min.js.map` | Source map generata per la build AMD `core/beacon`; non modificare manualmente. |
| `amd/build/core/confirm.min.js` | Build AMD minificata generata per `core/confirm` tramite Grunt Moodle. |
| `amd/build/core/confirm.min.js.map` | Source map generata per la build AMD `core/confirm`; non modificare manualmente. |
| `amd/build/core/debug.min.js` | Build AMD minificata generata per `core/debug` tramite Grunt Moodle. |
| `amd/build/core/debug.min.js.map` | Source map generata per la build AMD `core/debug`; non modificare manualmente. |
| `amd/build/core/events.min.js` | Build AMD minificata generata per `core/events` tramite Grunt Moodle. |
| `amd/build/core/events.min.js.map` | Source map generata per la build AMD `core/events`; non modificare manualmente. |
| `amd/build/core/player.min.js` | Build AMD minificata generata per `core/player` tramite Grunt Moodle. |
| `amd/build/core/player.min.js.map` | Source map generata per la build AMD `core/player`; non modificare manualmente. |
| `amd/build/core/player/acknowledgement.min.js` | Build AMD minificata generata per `core/player/acknowledgement` tramite Grunt Moodle. |
| `amd/build/core/player/acknowledgement.min.js.map` | Source map generata per la build AMD `core/player/acknowledgement`; non modificare manualmente. |
| `amd/build/core/player/bookmarks.min.js` | Build AMD minificata generata per `core/player/bookmarks` tramite Grunt Moodle. |
| `amd/build/core/player/bookmarks.min.js.map` | Source map generata per la build AMD `core/player/bookmarks`; non modificare manualmente. |
| `amd/build/core/player/focus_guard.min.js` | Build AMD minificata generata per `core/player/focus_guard` tramite Grunt Moodle. |
| `amd/build/core/player/focus_guard.min.js.map` | Source map generata per la build AMD `core/player/focus_guard`; non modificare manualmente. |
| `amd/build/core/player/forum.min.js` | Build AMD minificata generata per `core/player/forum` tramite Grunt Moodle. |
| `amd/build/core/player/forum.min.js.map` | Source map generata per la build AMD `core/player/forum`; non modificare manualmente. |
| `amd/build/core/player/intervalbar.min.js` | Build AMD minificata generata per `core/player/intervalbar` tramite Grunt Moodle. |
| `amd/build/core/player/intervalbar.min.js.map` | Source map generata per la build AMD `core/player/intervalbar`; non modificare manualmente. |
| `amd/build/core/player/notes.min.js` | Build AMD minificata generata per `core/player/notes` tramite Grunt Moodle. |
| `amd/build/core/player/notes.min.js.map` | Source map generata per la build AMD `core/player/notes`; non modificare manualmente. |
| `amd/build/core/player/notes/row.min.js` | Build AMD minificata generata per `core/player/notes/row` tramite Grunt Moodle. |
| `amd/build/core/player/notes/row.min.js.map` | Source map generata per la build AMD `core/player/notes/row`; non modificare manualmente. |
| `amd/build/core/player/notes/toggle.min.js` | Build AMD minificata generata per `core/player/notes/toggle` tramite Grunt Moodle. |
| `amd/build/core/player/notes/toggle.min.js.map` | Source map generata per la build AMD `core/player/notes/toggle`; non modificare manualmente. |
| `amd/build/core/player/poster.min.js` | Build AMD minificata generata per `core/player/poster` tramite Grunt Moodle. |
| `amd/build/core/player/poster.min.js.map` | Source map generata per la build AMD `core/player/poster`; non modificare manualmente. |
| `amd/build/core/player/progress.min.js` | Build AMD minificata generata per `core/player/progress` tramite Grunt Moodle. |
| `amd/build/core/player/progress.min.js.map` | Source map generata per la build AMD `core/player/progress`; non modificare manualmente. |
| `amd/build/core/player/reactions.min.js` | Build AMD minificata generata per `core/player/reactions` tramite Grunt Moodle. |
| `amd/build/core/player/reactions.min.js.map` | Source map generata per la build AMD `core/player/reactions`; non modificare manualmente. |
| `amd/build/core/player/resume.min.js` | Build AMD minificata generata per `core/player/resume` tramite Grunt Moodle. |
| `amd/build/core/player/resume.min.js.map` | Source map generata per la build AMD `core/player/resume`; non modificare manualmente. |
| `amd/build/core/player/status.min.js` | Build AMD minificata generata per `core/player/status` tramite Grunt Moodle. |
| `amd/build/core/player/status.min.js.map` | Source map generata per la build AMD `core/player/status`; non modificare manualmente. |
| `amd/build/core/player/timed_text.min.js` | Build AMD minificata generata per `core/player/timed_text` tramite Grunt Moodle. |
| `amd/build/core/player/timed_text.min.js.map` | Source map generata per la build AMD `core/player/timed_text`; non modificare manualmente. |
| `amd/build/core/progress.min.js` | Build AMD minificata generata per `core/progress` tramite Grunt Moodle. |
| `amd/build/core/progress.min.js.map` | Source map generata per la build AMD `core/progress`; non modificare manualmente. |
| `amd/build/core/reactions.min.js` | Build AMD minificata generata per `core/reactions` tramite Grunt Moodle. |
| `amd/build/core/reactions.min.js.map` | Source map generata per la build AMD `core/reactions`; non modificare manualmente. |
| `amd/build/core/segment.min.js` | Build AMD minificata generata per `core/segment` tramite Grunt Moodle. |
| `amd/build/core/segment.min.js.map` | Source map generata per la build AMD `core/segment`; non modificare manualmente. |
| `amd/build/core/session.min.js` | Build AMD minificata generata per `core/session` tramite Grunt Moodle. |
| `amd/build/core/session.min.js.map` | Source map generata per la build AMD `core/session`; non modificare manualmente. |
| `amd/build/core/state.min.js` | Build AMD minificata generata per `core/state` tramite Grunt Moodle. |
| `amd/build/core/state.min.js.map` | Source map generata per la build AMD `core/state`; non modificare manualmente. |
| `amd/build/core/status.min.js` | Build AMD minificata generata per `core/status` tramite Grunt Moodle. |
| `amd/build/core/status.min.js.map` | Source map generata per la build AMD `core/status`; non modificare manualmente. |
| `amd/build/core/tracker.min.js` | Build AMD minificata generata per `core/tracker` tramite Grunt Moodle. |
| `amd/build/core/tracker.min.js.map` | Source map generata per la build AMD `core/tracker`; non modificare manualmente. |
| `amd/build/core/tracker/events.min.js` | Build AMD minificata generata per `core/tracker/events` tramite Grunt Moodle. |
| `amd/build/core/tracker/events.min.js.map` | Source map generata per la build AMD `core/tracker/events`; non modificare manualmente. |
| `amd/build/core/tracker/heartbeat.min.js` | Build AMD minificata generata per `core/tracker/heartbeat` tramite Grunt Moodle. |
| `amd/build/core/tracker/heartbeat.min.js.map` | Source map generata per la build AMD `core/tracker/heartbeat`; non modificare manualmente. |
| `amd/build/core/tracker/lifecycle.min.js` | Build AMD minificata generata per `core/tracker/lifecycle` tramite Grunt Moodle. |
| `amd/build/core/tracker/lifecycle.min.js.map` | Source map generata per la build AMD `core/tracker/lifecycle`; non modificare manualmente. |
| `amd/build/core/tracker/segment.min.js` | Build AMD minificata generata per `core/tracker/segment` tramite Grunt Moodle. |
| `amd/build/core/tracker/segment.min.js.map` | Source map generata per la build AMD `core/tracker/segment`; non modificare manualmente. |
| `amd/build/core/tracker/state.min.js` | Build AMD minificata generata per `core/tracker/state` tramite Grunt Moodle. |
| `amd/build/core/tracker/state.min.js.map` | Source map generata per la build AMD `core/tracker/state`; non modificare manualmente. |
| `amd/build/core/tracker/time.min.js` | Build AMD minificata generata per `core/tracker/time` tramite Grunt Moodle. |
| `amd/build/core/tracker/time.min.js.map` | Source map generata per la build AMD `core/tracker/time`; non modificare manualmente. |
| `amd/build/core/ui.min.js` | Build AMD minificata generata per `core/ui` tramite Grunt Moodle. |
| `amd/build/core/ui.min.js.map` | Source map generata per la build AMD `core/ui`; non modificare manualmente. |
| `amd/build/core/utils.min.js` | Build AMD minificata generata per `core/utils` tramite Grunt Moodle. |
| `amd/build/core/utils.min.js.map` | Source map generata per la build AMD `core/utils`; non modificare manualmente. |
| `amd/build/form/duration.min.js` | Build AMD minificata generata per `form/duration` tramite Grunt Moodle. |
| `amd/build/form/duration.min.js.map` | Source map generata per la build AMD `form/duration`; non modificare manualmente. |
| `amd/build/html5_player.min.js` | Build AMD minificata generata per `html5_player` tramite Grunt Moodle. |
| `amd/build/html5_player.min.js.map` | Source map generata per la build AMD `html5_player`; non modificare manualmente. |
| `amd/build/player.min.js` | Build AMD minificata generata per `player` tramite Grunt Moodle. |
| `amd/build/player.min.js.map` | Source map generata per la build AMD `player`; non modificare manualmente. |
| `amd/build/presets.min.js` | Build AMD minificata generata per `presets` tramite Grunt Moodle. |
| `amd/build/presets.min.js.map` | Source map generata per la build AMD `presets`; non modificare manualmente. |
| `amd/build/report.min.js` | Build AMD minificata generata per `report` tramite Grunt Moodle. |
| `amd/build/report.min.js.map` | Source map generata per la build AMD `report`; non modificare manualmente. |
| `amd/build/vimeo_player.min.js` | Build AMD minificata generata per `vimeo_player` tramite Grunt Moodle. |
| `amd/build/vimeo_player.min.js.map` | Source map generata per la build AMD `vimeo_player`; non modificare manualmente. |
| `amd/src/completion_requirements.js` | Sorgente AMD canonica che colloca l’etichetta AND/OR VideoTrack prima di un elenco Moodle con più requisiti, senza cambiare stato o semantica del completamento. |
| `amd/src/core/adapter.js` | Sorgente AMD canonica modificabile per `core/adapter`. |
| `amd/src/core/api.js` | Sorgente AMD canonica modificabile per `core/api`. |
| `amd/src/core/api/error.js` | Sorgente AMD canonica modificabile per `core/api/error`. |
| `amd/src/core/api/retry.js` | Sorgente AMD canonica modificabile per `core/api/retry`. |
| `amd/src/core/api/scope.js` | Sorgente AMD canonica modificabile per `core/api/scope`. |
| `amd/src/core/api/transport.js` | Sorgente AMD canonica modificabile per `core/api/transport`. |
| `amd/src/core/api/validator.js` | Sorgente AMD canonica modificabile per `core/api/validator`. |
| `amd/src/core/beacon.js` | Sorgente AMD canonica modificabile per `core/beacon`. |
| `amd/src/core/confirm.js` | Sorgente AMD canonica modificabile per `core/confirm`. |
| `amd/src/core/debug.js` | Sorgente AMD canonica modificabile per `core/debug`. |
| `amd/src/core/events.js` | Sorgente AMD canonica modificabile per `core/events`. |
| `amd/src/core/player.js` | Sorgente AMD canonica modificabile per `core/player`. |
| `amd/src/core/player/acknowledgement.js` | Sorgente AMD canonica modificabile per `core/player/acknowledgement`. |
| `amd/src/core/player/bookmarks.js` | Sorgente AMD canonica modificabile per `core/player/bookmarks`. |
| `amd/src/core/player/focus_guard.js` | Sorgente AMD canonica modificabile per `core/player/focus_guard`. |
| `amd/src/core/player/forum.js` | Sorgente AMD canonica modificabile per `core/player/forum`. |
| `amd/src/core/player/intervalbar.js` | Sorgente AMD canonica modificabile per `core/player/intervalbar`. |
| `amd/src/core/player/notes.js` | Sorgente AMD canonica modificabile per `core/player/notes`. |
| `amd/src/core/player/notes/row.js` | Sorgente AMD canonica modificabile per `core/player/notes/row`. |
| `amd/src/core/player/notes/toggle.js` | Sorgente AMD canonica modificabile per `core/player/notes/toggle`. |
| `amd/src/core/player/poster.js` | Sorgente AMD canonica modificabile per `core/player/poster`. |
| `amd/src/core/player/progress.js` | Sorgente AMD canonica modificabile per `core/player/progress`. |
| `amd/src/core/player/reactions.js` | Sorgente AMD canonica modificabile per `core/player/reactions`. |
| `amd/src/core/player/resume.js` | Sorgente AMD canonica modificabile per `core/player/resume`. |
| `amd/src/core/player/status.js` | Sorgente AMD canonica modificabile per `core/player/status`. |
| `amd/src/core/player/timed_text.js` | Sorgente AMD canonica modificabile per `core/player/timed_text`. |
| `amd/src/core/progress.js` | Sorgente AMD canonica modificabile per `core/progress`. |
| `amd/src/core/reactions.js` | Sorgente AMD canonica modificabile per `core/reactions`. |
| `amd/src/core/segment.js` | Sorgente AMD canonica modificabile per `core/segment`. |
| `amd/src/core/session.js` | Sorgente AMD canonica modificabile per `core/session`. |
| `amd/src/core/state.js` | Sorgente AMD canonica modificabile per `core/state`. |
| `amd/src/core/status.js` | Sorgente AMD canonica modificabile per `core/status`. |
| `amd/src/core/tracker.js` | Sorgente AMD canonica modificabile per `core/tracker`. |
| `amd/src/core/tracker/events.js` | Sorgente AMD canonica modificabile per `core/tracker/events`. |
| `amd/src/core/tracker/heartbeat.js` | Sorgente AMD canonica modificabile per `core/tracker/heartbeat`. |
| `amd/src/core/tracker/lifecycle.js` | Sorgente AMD canonica modificabile per `core/tracker/lifecycle`. |
| `amd/src/core/tracker/segment.js` | Sorgente AMD canonica modificabile per `core/tracker/segment`. |
| `amd/src/core/tracker/state.js` | Sorgente AMD canonica modificabile per `core/tracker/state`. |
| `amd/src/core/tracker/time.js` | Sorgente AMD canonica modificabile per `core/tracker/time`. |
| `amd/src/core/ui.js` | Sorgente AMD canonica modificabile per `core/ui`. |
| `amd/src/core/utils.js` | Sorgente AMD canonica modificabile per `core/utils`. |
| `amd/src/form/duration.js` | Sorgente AMD canonica del form che legge la configurazione localizzata e propone la durata da metadati YouTube, Vimeo o file locale; il probe YouTube attende i metadati ritardati senza scrivere dati di tracciamento VideoTrack. |
| `amd/src/html5_player.js` | Sorgente AMD canonica modificabile per `html5_player`. |
| `amd/src/player.js` | Sorgente AMD canonica modificabile per `player`. |
| `amd/src/presets.js` | Sorgente AMD canonica modificabile per `presets`. |
| `amd/src/report.js` | Sorgente AMD canonica modificabile per `report`. |
| `amd/src/vimeo_player.js` | Sorgente AMD canonica modificabile per `vimeo_player`. |
| `backup/moodle2/backup_videotrack_activity_task.class.php` | Componente Moodle backup/restore `backup_videotrack_activity_task.class.php`. |
| `backup/moodle2/backup_videotrack_stepslib.php` | Componente Moodle backup/restore `backup_videotrack_stepslib.php`. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | Componente Moodle backup/restore `restore_videotrack_activity_task.class.php`. |
| `backup/moodle2/restore_videotrack_stepslib.php` | Componente Moodle backup/restore `restore_videotrack_stepslib.php`. |
| `bookmarks.php` | Pagina ed esportazione CSV dei segnalibri, accessibili soltanto al proprietario. |
| `classes/admin/setting_int_range.php` | Classe di impostazione amministrativa `setting_int_range`. |
| `classes/admin/setting_nonnegative_int.php` | Classe di impostazione amministrativa `setting_nonnegative_int`. |
| `classes/admin/setting_retention_days.php` | Classe di impostazione amministrativa `setting_retention_days`. |
| `classes/completion/custom_completion.php` | Implementazione delle condizioni personalizzate di completamento. |
| `classes/event/acknowledgement_confirmed.php` | Evento Moodle `acknowledgement_confirmed` per audit e osservabilità. |
| `classes/event/activity_completed.php` | Evento Moodle `activity_completed` per audit e osservabilità. |
| `classes/event/bookmark_deleted.php` | Evento Moodle `bookmark_deleted` per audit e osservabilità. |
| `classes/event/bookmark_exported.php` | Evento Moodle `bookmark_exported` per audit e osservabilità. |
| `classes/event/bookmark_saved.php` | Evento Moodle `bookmark_saved` per audit e osservabilità. |
| `classes/event/course_module_viewed.php` | Evento Moodle `course_module_viewed` per audit e osservabilità. |
| `classes/event/note_deleted.php` | Evento Moodle `note_deleted` per audit e osservabilità. |
| `classes/event/note_saved.php` | Evento Moodle `note_saved` per audit e osservabilità. |
| `classes/event/notes_exported.php` | Evento Moodle `notes_exported` per audit e osservabilità. |
| `classes/event/reaction_deleted.php` | Evento Moodle `reaction_deleted` per audit e osservabilità. |
| `classes/event/reaction_saved.php` | Evento Moodle `reaction_saved` per audit e osservabilità. |
| `classes/event/report_exported.php` | Evento Moodle `report_exported` per audit e osservabilità. |
| `classes/event/segment_saved.php` | Evento Moodle `segment_saved` per audit e osservabilità. |
| `classes/event/student_progress_reset.php` | Evento Moodle `student_progress_reset` per audit e osservabilità. |
| `classes/external/delete_bookmark.php` | Servizio esterno AJAX `delete_bookmark` con validazione lato server. |
| `classes/external/delete_note.php` | Servizio esterno AJAX `delete_note` con validazione lato server. |
| `classes/external/delete_reaction.php` | Servizio esterno AJAX `delete_reaction` con validazione lato server. |
| `classes/external/helper.php` | Servizio esterno AJAX `helper` con validazione lato server. |
| `classes/external/save_bookmark.php` | Servizio esterno AJAX `save_bookmark` con validazione lato server. |
| `classes/external/save_integrity_event.php` | Servizio esterno AJAX `save_integrity_event` con validazione lato server. |
| `classes/external/save_note.php` | Salva una nota personale attraverso fasi esplicite di normalizzazione, evidenza vista, rate limit globale, persistenza e warning. |
| `classes/external/save_reaction.php` | Salva una reazione con fasi ordinate di validazione, evidenza vista, limite tra sessioni e duplicati/persistenza sotto lock, poi mappa il risultato post-scrittura nella risposta stabile. |
| `classes/external/save_segment.php` | Valida e salva i segmenti visti tramite tracker, mantenendo payload e risposta del servizio AJAX. |
| `classes/external/start_playback.php` | Servizio AJAX che apre una finestra server idempotente a credito zero. |
| `classes/form/forum_post_form.php` | Form Moodle `forum_post_form`. |
| `classes/form/microsoft_activity_reference_form.php` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `classes/form/microsoft_probe_form.php` | Modulo POST con validazione degli ID opachi prima di OAuth o Graph. |
| `classes/form/microsoft_search_form.php` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `classes/local/acknowledgement.php` | Servizio di dominio `acknowledgement` riusabile da pagine, report o servizi. |
| `classes/local/acknowledgement_analytics.php` | Calcolo aggregato delle prese visione con soglia privacy e dati legacy. |
| `classes/local/analytics.php` | API Analytics stabile, riepiloghi protetti dalla privacy e cluster delle reazioni. |
| `classes/local/analytics_scope.php` | Servizio di dominio `analytics_scope` riusabile da pagine, report o servizi. |
| `classes/local/analytics_scope_matcher.php` | Trova candidati Analytics per lo stesso video e applica l’accesso aggregato per modulo. |
| `classes/local/analytics_table_export.php` | Servizio di dominio `analytics_table_export` riusabile da pagine, report o servizi. |
| `classes/local/analytics_timeline.php` | Calcola bin temporali, visione unica e replay da segmenti grezzi o intervalli di stato conservati. |
| `classes/local/completion_config.php` | Classe locale di dominio/servizio corrente; vedere il DocBlock sorgente per il contratto runtime. |
| `classes/local/course_analytics.php` | Servizio di dominio `course_analytics` riusabile da pagine, report o servizi. |
| `classes/local/course_analytics_loader.php` | Carica scope delle attività visibili, filtrati per capability, e record batchati di stato, segmenti e eventi. |
| `classes/local/csv_event_context.php` | Contesto tipizzato per attività, studenti e opzioni condivise dalle righe evento CSV del report docente. |
| `classes/local/csv_event_timestamps.php` | Timestamp video rappresentativo, iniziale e finale, tipizzati per le righe evento CSV del report docente. |
| `classes/local/csv_event_writer.php` | Writer contestuale per le righe evento CSV personalizzate del report docente. |
| `classes/local/csv_export.php` | Servizio di dominio `csv_export` riusabile da pagine, report o servizi. |
| `classes/local/csv_export_fields.php` | Risolve campi CSV configurati, identità visibili e record utente selezionati. |
| `classes/local/csv_export_identity.php` | Formatta colonne di identità CSV, etichette Moodle e link video. |
| `classes/local/focus_policy.php` | Eccezione di accessibilità al focus rigido per corso basata su un gruppo core Moodle nascosto. |
| `classes/local/form_definition.php` | Costruisce selettori sorgente/durata e gruppi player/testo temporizzato, CSV, Forum, integrità e presa visione. |
| `classes/local/form_playback_settings.php` | Costruisce campi riproduzione controllati dal sito con default esatti, blocchi per corso e opzioni dei limiti di velocità. |
| `classes/local/form_preprocessing.php` | Prepara bozze reazioni e default di avviso/preset, completamento, playback, CSV e sito prima del rendering del form. |
| `classes/local/form_reaction_settings.php` | Costruisce controlli reaction generali e campi/visibilità icone delle singole righe, poi applica i blocchi completamento per corso. |
| `classes/local/form_validation.php` | Valida disponibilità sorgenti, impostazioni scalari e contratti di completamento/presa visione. |
| `classes/local/forum_bridge.php` | Servizio di dominio `forum_bridge` riusabile da pagine, report o servizi. |
| `classes/local/instance_settings_policy.php` | Policy server sui campi e sulle dipendenze di completamento prima della persistenza. |
| `classes/local/integrity.php` | Servizio di dominio `integrity` riusabile da pagine, report o servizi. |
| `classes/local/learner_scope.php` | Regole canoniche di visibilità learner/report per ruoli e gruppi. |
| `classes/local/microsoft_activity_reference.php` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `classes/local/microsoft_graph.php` | GET metadati e POST anteprima privato verso host fisso, con diagnostica drive/video redatta. |
| `classes/local/microsoft_graph_probe.php` | Controlli locali ed endpoint Microsoft prima del client OAuth core di sistema. |
| `classes/local/microsoft_graph_search.php` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `classes/local/microsoft_link_result.php` | Separa URL privati della pagina provider e diagnostica serializzabile; impone limiti destinazione/query. |
| `classes/local/microsoft_preview_result.php` | Parser anteprima circoscritto, URL temporaneo privato e diagnostica allowlist. |
| `classes/local/microsoft_search_result.php` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `classes/local/microsoft_user_connection.php` | Collegamento OAuth core personale esplicito con POST/sesskey e ritorno monouso. |
| `classes/local/microsoft_user_probe.php` | Diagnostica amministrativa con utente OAuth core corrente, senza ripiego sistema. |
| `classes/local/microsoft_video_reference.php` | Identità versionata issuer locale/drive/video, parser circoscritto ed export amministrativo esplicito. |
| `classes/local/personal_item.php` | Cancellazione definitiva del proprietario e retry serializzati. |
| `classes/local/playback_recovery.php` | Recupero storico esplicito e relativo contratto di conservazione dati. |
| `classes/local/privacy_manager.php` | Servizio di dominio `privacy_manager` riusabile da pagine, report o servizi. |
| `classes/local/privacy_retention_pair.php` | Elimina i dati personali scaduti e ricostruisce lo stato di un utente e attività sotto il lock canonico. |
| `classes/local/provider_connection.php` | Stato OAuth locale in sola lettura e scelta dei servizi compatibili; credenziali gestite dal core. |
| `classes/local/random_pause_policy.php` | Risolve intervalli di attenzione dalla durata, limiti docente indipendenti e valori importati sicuri. |
| `classes/local/reaction_form_validation.php` | Policy di validazione pura per icone e campi obbligatori delle righe reazione nel form attività. |
| `classes/local/reaction_write_service.php` | Esegue invalidazione cache, consegna evento, conteggio aggregato e refresh completion dopo la persistenza con warning limitati. |
| `classes/local/report_access.php` | Policy centralizzata delle capability granulari dei report di attività con fallback retrocompatibile al permesso completo. |
| `classes/local/report_aggregation.php` | Individuazione pura dei cali di viewer e ordinamento dei cluster di reazione completati per i report docente. |
| `classes/local/report_analytics_query.php` | Costruttori SQL capability-safe per query docente Analytics, provider e prese visione correnti. |
| `classes/local/report_event_query.php` | Costruttori SQL per caricamento eventi e individuazione learner nel report docente. |
| `classes/local/report_integrity_view.php` | Stati di presentazione integrità privacy-safe attivo, disattivato e soli controlli. |
| `classes/local/report_summary_view.php` | Riepiloghi di presentazione privacy-safe per reazioni, segnalibri e prese visione. |
| `classes/local/report_support.php` | Supporto di presentazione, opzioni, highlight e clustering reazioni del report docente. |
| `classes/local/report_time_filter.php` | Limiti data, parsing strutturato/legacy e controlli durata accessibili per i report docente. |
| `classes/local/report_timeline_view.php` | Presentazione di intervalli, grafici, legenda e cluster reazioni Analytics docente. |
| `classes/local/report_view.php` | Controlli condivisi per metodologia, avvisi privacy e download Analytics docente. |
| `classes/local/resume_storage.php` | Identità di ripresa browser per utente, attività e sorgente. |
| `classes/local/teacher_analytics.php` | Servizio di dominio `teacher_analytics` riusabile da pagine, report o servizi. |
| `classes/local/timed_text.php` | Risolve file WebVTT canonici di trascrizione/capitoli e fallback sottotitoli legacy espliciti. |
| `classes/local/tracker.php` | Servizio di dominio `tracker` riusabile da pagine, report o servizi. |
| `classes/local/video_source_policy.php` | Impone la disponibilità delle sorgenti del sito, filtra le scelte e conta le attività senza richieste ai fornitori. |
| `classes/privacy/event_exporter.php` | Esporta reazioni, note e segnalibri attivi/cancellati in sei blocchi privacy separati da 500 righe. |
| `classes/privacy/exporter.php` | Esporta i dati degli utenti approvati tramite Privacy API Moodle mantenendo chunking e regole di sanitizzazione. |
| `classes/privacy/provider.php` | Provider Moodle Privacy API del plugin. |
| `classes/task/cleanup_task.php` | Task pianificato per retention e pulizia. |
| `cli/benchmark_course_analytics.php` | Benchmark CLI in sola lettura per query e tempi dell’aggregazione Course Analytics batchata. |
| `cli/provider_probe.php` | Diagnostica autenticata esplicita; può rinnovare OAuth core, senza scritture demo. |
| `cli/provider_status.php` | Diagnostica JSON in sola lettura della configurazione provider; nessuna richiesta remota o rinnovo token. |
| `cli/validate.php` | Validatore CLI in sola lettura per versione, XMLDB, integrità riferimenti/gradebook, servizi, lingue, AMD e configurazione critica. |
| `db/access.php` | Capability e default degli archetipi di ruolo. |
| `db/install.php` | File corrente di supporto database/installazione/upgrade distribuito con il plugin. |
| `db/install.xml` | Schema XMLDB canonico delle tabelle del plugin. |
| `db/mobile.php` | Dichiarazione Moodle App intenzionalmente vuota fino alla fase dedicata di validazione App. |
| `db/repairlib.php` | File corrente di supporto database/installazione/upgrade distribuito con il plugin. |
| `db/services.php` | Dichiarazioni delle funzioni esterne AJAX. |
| `db/tasks.php` | Dichiarazione del task pianificato. |
| `db/uninstall.php` | Hook uninstall anticipato: conserva la pulizia Grade API ed elimina solo la completion nativa propria prima che il core perda lo scope. |
| `db/upgrade.php` | Upgrade XMLDB incrementali e savepoint. |
| `db/upgradelib.php` | Recupero XMLDB riservato all’upgrade. |
| `docs/en/29_SECURITY_REVIEW.md` | Segnalazioni di sicurezza, ambito implementato e validazione nativa richiesta. |
| `docs/en/30_MICROSOFT_GRAPH.md` | Protocollo inglese OAuth/Graph e gate su tenant reale. |
| `docs/en/31_SOURCE_POLICY.md` | Guida inglese alla policy sorgenti e valutazione del repository Zoom opzionale. |
| `docs/en/32_MICROSOFT_WEB_CHECK.md` | Guida inglese alla diagnostica Microsoft dalla GUI amministrativa. |
| `docs/en/33_MICROSOFT_USER_ACCESS.md` | Guida inglese alla diagnostica Microsoft personale e ai confini di autorizzazione. |
| `docs/en/34_MICROSOFT_PROVIDER_PAGE.md` | Guida inglese all’apertura pagina Microsoft e ai confini del futuro incorporamento. |
| `docs/en/35_MICROSOFT_USER_CONNECTION.md` | Guida inglese a collegamento Microsoft personale, confini sessione e verifiche. |
| `docs/en/36_CI_BEHAT_EVIDENCE.md` | Guida inglese a conservazione artefatti Behat, tentativi e limiti della fotografia finale. |
| `docs/en/37_MICROSOFT_PRIVATE_PREVIEW.md` | Guida inglese ad anteprima diagnostica Microsoft privata, identità e procedura di prova reale. |
| `docs/en/38_MICROSOFT_VIDEO_REFERENCES.md` | Procedura identità riutilizzabile, confini di sicurezza, limiti e riferimenti primari. |
| `docs/en/39_TRACKING_RECOVERY_HOTFIX.md` | Recupero storico esplicito e relativo contratto di conservazione dati. |
| `docs/en/40_MICROSOFT_VIDEO_SEARCH.md` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `docs/en/41_MICROSOFT_ACTIVITY_REFERENCES.md` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `docs/en/42_REPORT_REPLAY_STATE.md` | Regressione dell’ordine degli eventi nel replay o relativa guida tecnica. |
| `docs/it/29_REVISIONE_SICUREZZA.md` | Implementazione delle revisioni di sicurezza e lavoro residuo in italiano. |
| `docs/it/30_MICROSOFT_GRAPH.md` | Protocollo italiano OAuth/Graph e gate su tenant reale. |
| `docs/it/31_SOURCE_POLICY.md` | Guida italiana alla policy sorgenti e valutazione del repository Zoom opzionale. |
| `docs/it/32_MICROSOFT_WEB_CHECK.md` | Guida italiana alla diagnostica Microsoft dalla GUI amministrativa. |
| `docs/it/33_MICROSOFT_USER_ACCESS.md` | Guida italiana alla diagnostica Microsoft personale e ai confini di autorizzazione. |
| `docs/it/34_MICROSOFT_PROVIDER_PAGE.md` | Guida italiana all’apertura pagina Microsoft e ai confini del futuro incorporamento. |
| `docs/it/35_MICROSOFT_USER_CONNECTION.md` | Guida italiana a collegamento Microsoft personale, confini sessione e verifiche. |
| `docs/it/36_CI_BEHAT_EVIDENCE.md` | Guida italiana a conservazione artefatti Behat, tentativi e limiti della fotografia finale. |
| `docs/it/37_MICROSOFT_PRIVATE_PREVIEW.md` | Guida italiana ad anteprima diagnostica Microsoft privata, identità e procedura di prova reale. |
| `docs/it/38_MICROSOFT_VIDEO_REFERENCES.md` | Procedura identità riutilizzabile, confini di sicurezza, limiti e riferimenti primari. |
| `docs/it/39_TRACKING_RECOVERY_HOTFIX.md` | Recupero storico esplicito e relativo contratto di conservazione dati. |
| `docs/it/40_MICROSOFT_VIDEO_SEARCH.md` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `docs/it/41_MICROSOFT_ACTIVITY_REFERENCES.md` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `docs/it/42_REPORT_REPLAY_STATE.md` | Regressione dell’ordine degli eventi nel replay o relativa guida tecnica. |
| `environment.xml` | Requisiti di ambiente verificati da Moodle. |
| `forum_post.php` | Composer Forum temporizzato; la memorizzazione finale è delegata a mod_forum. |
| `index.php` | Indice delle attività del corso. |
| `lang/de/videotrack.php` | Language pack `de`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/en/videotrack.php` | Language pack `en`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/es/videotrack.php` | Language pack `es`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/fr/videotrack.php` | Language pack `fr`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/hi/videotrack.php` | Language pack `hi`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/it/videotrack.php` | Language pack `it`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/pl/videotrack.php` | Language pack `pl`; chiavi e placeholder devono corrispondere all’inglese. |
| `lang/pt/videotrack.php` | Language pack `pt`; chiavi e placeholder devono corrispondere all’inglese. |
| `lib.php` | Lifecycle Moodle, feature support, gradebook, completamento, File API e callback di reset. |
| `locallib.php` | Helper procedurali condivisi per URL, timestamp, stato, report, file area e configurazione. |
| `mod_form.php` | Form impostazioni attività, validazione, file manager e completamento personalizzato. |
| `phpcs.xml.dist` | Configurazione canonica del gate PHPCS basata sul ruleset Moodle Extra completo, senza esclusioni specifiche VideoTrack. |
| `phpmd.xml` | Ruleset PHPMD di produzione revisionato; il naming Moodle resta governato da PHPCS. |
| `phpstan.neon.dist` | Perimetro PHPStan Moodle-aware sul codice di produzione e livello iniziale. |
| `pix/icon.png` | Icona attività Moodle a colori fornita per questa release. |
| `pix/icon.svg` | Controparte trasparente autosufficiente del PNG scelto, usata dai formati corso Moodle e dai contenitori attività Boost. |
| `presets.php` | Interfaccia docente/amministratore per preset riutilizzabili delle reazioni. |
| `provider_check.php` | Pagina amministrativa di stato locale e verifica esplicita protetta dei metadati Microsoft. |
| `provider_search.php` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `psalm.xml` | Perimetro Psalm Moodle-aware sul codice di produzione e livello iniziale. |
| `report.php` | Report docente di istanza e pagina Analytics esatta per viewer autorizzati, con export CSV/Excel/ODS. |
| `reports_course.php` | Dashboard docente delle attività VideoTrack del corso. |
| `reports_teacher.php` | Dashboard centrata sul docente tra corsi e attività accessibili. |
| `settings.php` | Impostazioni sito, default, privacy, prestazioni e politiche di accessibilità. |
| `styles.css` | Stili responsivi e accessibili per player, report, trascrizione, Analytics e controlli. |
| `tests/accessibility_contract_test.php` | Contratti statici di accessibilità per i controlli capitolo del timed text. |
| `tests/acknowledgement_test.php` | Copertura PHPUnit per acknowledgement test. |
| `tests/admin_settings_test.php` | Copertura PHPUnit per admin settings test. |
| `tests/ajax_contract_test.php` | Copertura PHPUnit per i contratti AJAX browser e degli endpoint di mutazione. |
| `tests/analytics_performance_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/analytics_scope_test.php` | Copertura PHPUnit per analytics scope test. |
| `tests/analytics_table_export_test.php` | Copertura PHPUnit per analytics table export test. |
| `tests/analytics_test.php` | Copertura PHPUnit per analytics test. |
| `tests/backup_structure_contract_test.php` | Contratti sorgente per ordine delle fasi di backup, filtri privacy, identificatori e aree file. |
| `tests/behat/behat_mod_videotrack.php` | Step Behat deterministici per readiness, controlli, timestamp, rollback e watched evidence dei player HTML5 e provider. |
| `tests/behat/browser_allowed_seek_tracking.feature` | Casi browser di seek consentito senza seed: intervalli validati separati e nessun completamento dai gap saltati. |
| `tests/behat/browser_ended_replay_rate.feature` | Fine naturale, replay già visto, 2x autorizzato e riserva oltre tetto senza seed su HTML5/YouTube/Vimeo. |
| `tests/behat/browser_report_groups.feature` | Evidenze ottenute dal browser, scelte studente esatte e confini gruppi separati sulle richieste dirette dei report. |
| `tests/behat/browser_resume_privacy.feature` | Ripresa YouTube/Vimeo tra accessi di studenti diversi. |
| `tests/behat/browser_role_tracking.feature` | Esclusione tracking anteprima docente e completamento con doppio ruolo senza seed su HTML5/YouTube/Vimeo. |
| `tests/behat/browser_rollback_interactions.feature` | Completamento da visione browser senza seed seguito da rollback in pausa, interazioni persistite e timestamp composer Forum sicuro. |
| `tests/behat/browser_tracking_completion.feature` | Scenari browser HTML5/YouTube/Vimeo senza progresso predisposto: pause accettate e completamento nativo da visione. |
| `tests/behat/completion_requirements_layout.feature` | Regressione browser per il raggruppamento dell’etichetta logica composita VideoTrack sopra l’elenco condiviso dei requisiti Moodle. |
| `tests/behat/focus_exception_policy.feature` | Contratto browser per la policy focus rigida con e senza membership nel gruppo eccezioni nascosto. |
| `tests/behat/html5_acknowledgement_contract.feature` | Regressione browser deterministica per presa visione immediata, gate al video-end e conferma persistita. |
| `tests/behat/html5_completion_contract.feature` | Regressione browser deterministica per la persistenza della completion Moodle dopo visione validata e presa visione. |
| `tests/behat/html5_playback_contract.feature` | Regressione browser deterministica per resume HTML5 validato, seek indietro consentito e stato reale play/pause. |
| `tests/behat/html5_seek_policy.feature` | Regressione browser per seek avanti HTML5 consentito e bloccato senza dipendenze di rete pubblica. |
| `tests/behat/learner_role_controls.feature` | Regressione Behat dello scope ruoli per controlli learner, docente e dual-role. |
| `tests/behat/microsoft_account_context.feature` | Verifiche browser deterministiche sulla scelta esplicita dell’account condiviso/personale. |
| `tests/behat/microsoft_activity_reference.feature` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `tests/behat/microsoft_preview.feature` | Contratti browser deterministici GET locale, validazione personale e configurazione assente. |
| `tests/behat/microsoft_provider_check.feature` | Tre scenari amministrativi deterministici senza connessione Microsoft. |
| `tests/behat/microsoft_provider_page.feature` | Tre scenari deterministici del modulo pagina provider personale senza rete reale. |
| `tests/behat/microsoft_search.feature` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `tests/behat/microsoft_user_connection.feature` | Uno scenario browser di configurazione locale senza autenticazione esterna. |
| `tests/behat/microsoft_video_reference.feature` | Browser locale: default riferimento e rifiuto URL, servizio diverso e account condiviso. |
| `tests/behat/random_attention_policy.feature` | Configurazione effettiva e contratti di pausa automatica/ripresa HTML5/YouTube/Vimeo con un minuto reale. |
| `tests/behat/student_personal_sections.feature` | Regressione browser Behat per le sezioni personali collassabili del learner. |
| `tests/behat/video_source_policy.feature` | Scenari browser per scelte predefinite, sorgenti disabilitate in modifica, riproduzione bloccata e tutte le sorgenti disabilitate. |
| `tests/behat/vimeo_provider_contract.feature` | Contratto browser senza rete pubblica per l’adapter Vimeo di produzione invariato. |
| `tests/behat/youtube_provider_contract.feature` | Contratto browser senza rete pubblica per l’adapter YouTube di produzione invariato. |
| `tests/behat/youtube_report_replay.feature` | Regressione dell’ordine degli eventi nel replay o relativa guida tecnica. |
| `tests/cli_contract_test.php` | Contratti statici che mantengono i CLI distribuiti in sola lettura, documentati e legati ai percorsi Analytics reali. |
| `tests/completion_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/course_analytics_test.php` | Copertura PHPUnit per course analytics test. |
| `tests/csv_event_writer_test.php` | Copertura PHPUnit per la scrittura delle righe evento CSV personalizzate. |
| `tests/csv_export_test.php` | Copertura PHPUnit per csv export test. |
| `tests/fixtures/behat-video.mp4.b64` | Fixture video locale di 60 secondi codificata Base64 e decodificata esclusivamente dai test browser HTML5. |
| `tests/fixtures/behat-vimeo-player.js` | Doppio deterministico test-only dell’SDK Vimeo caricato esclusivamente dal fixture Behat riservato. |
| `tests/fixtures/behat-youtube-player.js` | Doppio deterministico test-only dell’SDK YouTube caricato esclusivamente dal fixture Behat riservato. |
| `tests/focus_policy_test.php` | Copertura PHPUnit per creazione gruppo nascosto, membership e policy focus effettiva. |
| `tests/form_definition_test.php` | Test runtime QuickForm su DOM/AMD durata, default player/testo temporizzato, opzioni file e permessi, CSV, Forum, integrità e presa visione. |
| `tests/form_playback_settings_test.php` | Test runtime QuickForm per default assenti/vuoti/zero, blocchi per corso, controlli modificabili e selezioni di velocità salvate. |
| `tests/form_preprocessing_test.php` | Test runtime dei default del form per completamento, playback e CSV. |
| `tests/form_reaction_settings_test.php` | Test runtime QuickForm per default/editor/preset reaction, righe/selettore/upload, dipendenze, valori salvati, conteggi del form e blocchi per corso. |
| `tests/form_validation_test.php` | Copertura comportamentale e di delega per la validazione autonoma del form attività. |
| `tests/forum_bridge_test.php` | Copertura PHPUnit per forum bridge test. |
| `tests/generator/lib.php` | Generator del modulo Moodle usato dalle fixture PHPUnit e Behat. |
| `tests/generator_test.php` | Copertura PHPUnit del generator del modulo VideoTrack. |
| `tests/gradebook_restore_contract_test.php` | Regressioni riparazione gradebook, pulizia voti uninstall e completion nativa circoscritta con e senza grade item. |
| `tests/integrity_test.php` | Copertura PHPUnit per integrity test. |
| `tests/learner_scope_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/lib_test.php` | Copertura PHPUnit per lib test. |
| `tests/lifecycle_integration_test.php` | Integrazioni reali framework backup/restore/privacy, task retention registrato, reset corso standard e cancellazione sincrona attività. |
| `tests/locallib_test.php` | Copertura PHPUnit per locallib test. |
| `tests/microsoft_activity_reference_test.php` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `tests/microsoft_graph_test.php` | Diciannove regressioni native simulate per i confini dei metadati autenticati. |
| `tests/microsoft_preview_test.php` | Regressioni native anteprima: URL, trasporto, account personale, conservazione e richieste. |
| `tests/microsoft_probe_form_test.php` | Regressioni native per modulo, sessione, metodo HTTP e accesso amministrativo. |
| `tests/microsoft_reference_restore_test.php` | Persistenza Microsoft facoltativa, backup portabile e relative guide/verifiche. |
| `tests/microsoft_search_test.php` | Ricerca metadati Microsoft protetta, validazione o relative regressioni. |
| `tests/microsoft_user_connection_test.php` | Regressioni native connessione/URL core/stato/permessi senza scambio provider reale. |
| `tests/microsoft_user_probe_test.php` | Regressioni native per configurazione, accesso, login, fallback e modulo senza rete reale. |
| `tests/microsoft_video_link_test.php` | Regressioni native URL/Graph/personali/modulo/controller senza rete provider. |
| `tests/microsoft_video_reference_test.php` | Regressioni native su identità, form/CSRF e risoluzione personale nuova a ogni richiesta. |
| `tests/mod_form_validation_test.php` | Copertura PHPUnit per sorgenti non valide, file VTT mancanti e requisiti delle icone delle reazioni. |
| `tests/playback_recovery_test.php` | Recupero storico esplicito e relativo contratto di conservazione dati. |
| `tests/player_resume_completion_alert_contract_test.php` | Contratti cross-provider per resume, completion e avvisi; le asserzioni completion seguono il proprietario corrente della mutazione, incluso il servizio post-scrittura reazioni. |
| `tests/privacy_exporter_test.php` | Verifica PHPUnit dei percorsi export, filtro utente e limite dei blocchi da 500. |
| `tests/privacy_manager_test.php` | Copertura PHPUnit per retention GDPR basata sulla cancellazione, ricostruzione dello stato e cancellazione utente. |
| `tests/provider_connection_test.php` | Test Moodle con fixture OAuth per servizi assenti/disabilitati/non compatibili, HTTPS, omissione segreti e stato in sola lettura. |
| `tests/provider_loader_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/provider_seek_snapshot_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/random_pause_policy_test.php` | Test comportamentali policy/form/salvataggio e duplicazione Moodle reale delle impostazioni di attenzione. |
| `tests/release_hygiene_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/report_access_test.php` | Copertura PHPUnit comportamentale della separazione tra vista/export aggregati e individuali dei report. |
| `tests/report_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/report_support_test.php` | Copertura PHPUnit comportamentale per helper di supporto e query del report docente. |
| `tests/report_time_filter_test.php` | Copertura PHPUnit dei contratti data, durata strutturata e controlli accessibili del report docente. |
| `tests/report_view_test.php` | Copertura PHPUnit comportamentale per i quattro helper focalizzati di presentazione Analytics docente. |
| `tests/save_bookmark_test.php` | Copertura PHPUnit per save bookmark test. |
| `tests/save_integrity_event_test.php` | Copertura PHPUnit per save integrity event test. |
| `tests/save_note_test.php` | Copertura PHPUnit di parametri nota, limiti timestamp, ordine dei guard e rate limit tra sessioni. |
| `tests/save_reaction_test.php` | Contratti sorgente per finestre duplicati, limite tra sessioni, lock, delega al collaboratore, mapping stabile della risposta e ordine pre/post-insert. |
| `tests/security_followup_test.php` | Regressioni native su accessi, cancellazione, ripresa, POST, completamento e HTML. |
| `tests/security_hardening_test.php` | Regressioni native su permessi, privacy delle coorti, voti, HTML, lifecycle e rollback. |
| `tests/student_view_contract_test.php` | Contratto statico della vista learner per le sezioni personali native. |
| `tests/teacher_analytics_test.php` | Copertura PHPUnit per teacher analytics test. |
| `tests/timed_text_test.php` | Copertura PHPUnit per validazione VTT, aree File API canoniche e precedenza del fallback legacy. |
| `tests/tracker_test.php` | Copertura PHPUnit per tracker test. |
| `tests/tracking_effects_test.php` | Test sugli effetti persistiti: retry rifiutati per budget/seek, ricostruzione al limite intervalli e idempotenza completamento/eventi autenticati. |
| `tests/upgrade_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/video_source_policy_test.php` | Regressioni native per sorgenti predefinite, scritture alterate, tracciamento residuo, conservazione file/stati e cancellazioni personali. |
| `tests/vimeo_seek_contract_test.php` | Test PHPUnit/di contratto corrente distribuito con il plugin. |
| `tests/xmldb_relationship_contract_test.php` | Contratti XMLDB per foreign key, riferimenti-sentinella e indici generati. |
| `tools/static-analysis/bootstrap.php` | Bootstrap comune che carica il Moodle selezionato e le API parent legacy stabili richieste dal perimetro di produzione. |
| `tools/static-analysis/moodle-legacy-aliases.phpstub` | Dichiarazione statica riservata a Psalm del nome globale di compatibilità `renderable` di Moodle 5.0. |
| `version.php` | Metadati, versione, maturità e rami Moodle supportati. |
| `view.php` | Pagina studente dell’attività: autorizzazione, caricamento stato, configurazione DOM privacy-safe e avvio del player. |
