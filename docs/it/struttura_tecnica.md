# mod_videotrack — Struttura tecnica del plugin
Documento aggiornato per la release **1.4.143**. Sostituisce le sezioni storiche obsolete della precedente `struttura_tecnica.md` con una fotografia operativa della struttura reale del plugin.
## 1. Scopo del documento
Questa guida documenta la struttura del plugin, lo scopo dei file principali e l’inventario delle funzioni PHP/AMD presenti nella baseline corrente. Non è un changelog: i dettagli storici sono mantenuti solo nei documenti separati elencati sotto.
## 2. Documenti collegati in `/docs`
Il file canonico per il layer AJAX è `docs/it/ajax-layer.md`. Il precedente duplicato `docs/ajax_layer.md` è stato rimosso perché ridondante.
| File | Ruolo |
|---|---|
| `docs/it/funzionalita.md` | Descrizione funzionale in italiano per amministratori, docenti e reviewer. |
| `docs/en/funzionalita.md` | Traduzione inglese della descrizione funzionale. |
| `docs/it/ajax-layer.md` | Rationale e flusso del layer AJAX, inclusi retry, timeout, validazione e scope token. |
| `docs/it/event_bus.md` | Documentazione del bus eventi AMD, namespace supportati ed esempi. |
| `docs/it/architecture_notes_1.4.121.md` | Note architetturali sui flussi complessi residui. |
| `docs/it/wcag_audit_1.4.116.md` | Audit WCAG finale storico. |
| `docs/it/wcag_edge_audit_1.4.122.md` | Audit edge-case WCAG: focus restore, confirm fallback e keyboard navigation. |
| `docs/it/mhq_audit_1.4.117.md` | Audit finale Moodle HQ storico. |
| `docs/it/amd_audit_1.4.115.md` | Audit AMD storico. |
| `docs/it/sendbeacon_review_1.4.123.md` | Valutazione tecnica sendBeacon/fallback. |
| `docs/it/strict_review_patch_assessment_1.4.124.md` | Valutazione dell’ipotesi di patch strict review. |
| `docs/it/candidate_release_1.4.118.md` | Documento storico di candidate release. |

## 3. Struttura delle directory
| Percorso | Scopo |
|---|---|
| `amd/src/` | Sorgenti JavaScript AMD. Qualsiasi modifica richiede `grunt amd` reale e inclusione degli artefatti generati. |
| `amd/build/` | Artefatti AMD minificati e source map generati da Moodle Grunt. |
| `backup/moodle2/` | Definizioni di backup e restore delle istanze Videotrack. |
| `classes/admin/` | Impostazioni amministrative custom con validazione stretta. |
| `classes/completion/` | Regole di completamento personalizzate. |
| `classes/event/` | Eventi Moodle emessi per audit log. |
| `classes/external/` | Web service AJAX richiamati dai moduli AMD. |
| `classes/local/` | Logica di dominio lato server, tracking e privacy manager. |
| `classes/privacy/` | Provider Privacy API Moodle. |
| `classes/task/` | Scheduled task di pulizia/anonymisation. |
| `db/` | Schema database, upgrade, capability, servizi e task. |
| `docs/it/` | Documentazione tecnica, funzionale e audit in italiano. |
| `docs/en/` | Technical, functional, and audit documentation in English. |
| `lang/` | Stringhe localizzate. |
| `pix/` | Icone e risorse grafiche statiche. |
| `tests/` | Test PHPUnit, non eseguibili senza ambiente Moodle test completo. |

## 4. Inventario file e responsabilità
| File | Scopo |
|---|---|
| `.gitattributes` | File di supporto del plugin. |
| `.moodleignore` | File di supporto del plugin. |
| `PRIVACY.md` | Descrizione privacy di alto livello. |
| `amd/src/core/adapter.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api/error.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api/retry.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api/scope.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api/transport.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api/validator.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/api.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/beacon.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/confirm.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/debug.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/events.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/intervalbar.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/notes/row.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/notes/toggle.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/notes.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/poster.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/progress.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/reactions.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/resume.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player/status.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/player.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/progress.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/reactions.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/segment.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/session.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/state.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/status.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/events.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/heartbeat.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/lifecycle.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/segment.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/state.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker/time.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/tracker.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/ui.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/core/utils.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/html5_player.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/player.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/presets.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/report.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `amd/src/vimeo_player.js` | Sorgente AMD: implementa logica client-side; il file corrispondente in `amd/build/` è l’artefatto minificato generato da Grunt. |
| `backup/moodle2/backup_videotrack_activity_task.class.php` | Integrazione backup/restore Moodle. |
| `backup/moodle2/backup_videotrack_stepslib.php` | Integrazione backup/restore Moodle. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | Integrazione backup/restore Moodle. |
| `backup/moodle2/restore_videotrack_stepslib.php` | Integrazione backup/restore Moodle. |
| `classes/admin/setting_int_range.php` | Tipo impostazione amministrativa custom con validazione dedicata. |
| `classes/admin/setting_nonnegative_int.php` | Tipo impostazione amministrativa custom con validazione dedicata. |
| `classes/admin/setting_retention_days.php` | Tipo impostazione amministrativa custom con validazione dedicata. |
| `classes/completion/custom_completion.php` | File di supporto del plugin. |
| `classes/event/activity_completed.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/course_module_viewed.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/note_deleted.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/note_saved.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/notes_exported.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/reaction_deleted.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/reaction_saved.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/segment_saved.php` | Evento Moodle per audit log e osservabilità. |
| `classes/event/student_progress_reset.php` | Evento Moodle per audit log e osservabilità. |
| `classes/external/delete_note.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/external/delete_reaction.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/external/helper.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/external/save_note.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/external/save_reaction.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/external/save_segment.php` | Classe web service AJAX: valida input, capability e contesto prima di scrivere dati. |
| `classes/local/privacy_manager.php` | Logica locale riutilizzabile lato server. |
| `classes/local/tracker.php` | Logica locale riutilizzabile lato server. |
| `classes/privacy/provider.php` | Implementazione Moodle Privacy API. |
| `classes/task/cleanup_task.php` | File di supporto del plugin. |
| `db/access.php` | Definizione database, servizi, capability o upgrade Moodle. |
| `db/install.xml` | Definizione database, servizi, capability o upgrade Moodle. |
| `db/mobile.php` | Definizione database, servizi, capability o upgrade Moodle. |
| `db/services.php` | Definizione database, servizi, capability o upgrade Moodle. |
| `db/tasks.php` | Definizione database, servizi, capability o upgrade Moodle. |
| `db/upgrade.php` | Definizione database, servizi, capability o upgrade Moodle. |
| `docs/it/ajax-layer.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/amd_audit_1.4.115.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/architecture_notes_1.4.121.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/candidate_release_1.4.118.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/event_bus.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/funzionalita.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/mhq_audit_1.4.117.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/sendbeacon_review_1.4.123.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/strict_review_patch_assessment_1.4.124.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/struttura_tecnica.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/wcag_audit_1.4.116.md` | Documentazione tecnica o funzionale del plugin. |
| `docs/it/wcag_edge_audit_1.4.122.md` | Documentazione tecnica o funzionale del plugin. |
| `environment.xml` | Requisiti di ambiente dichiarati a Moodle. |
| `index.php` | Lista istanze del modulo nel corso. |
| `lang/de/videotrack.php` | Stringhe localizzate del componente. |
| `lang/en/videotrack.php` | Stringhe localizzate del componente. |
| `lang/es/videotrack.php` | Stringhe localizzate del componente. |
| `lang/fr/videotrack.php` | Stringhe localizzate del componente. |
| `lang/hi/videotrack.php` | Stringhe localizzate del componente. |
| `lang/it/videotrack.php` | Stringhe localizzate del componente. |
| `lang/pl/videotrack.php` | Stringhe localizzate del componente. |
| `lang/pt/videotrack.php` | Stringhe localizzate del componente. |
| `lib.php` | API Moodle del modulo: CRUD attività, filearea, completion, gradebook, reset corso e pluginfile. |
| `locallib.php` | Helper applicativi non API: parsing URL, configurazioni, reazioni, preset e formattazione. |
| `mod_form.php` | Definizione del form attività Moodle. |
| `pix/icon.svg` | File di supporto del plugin. |
| `presets.php` | Pagina amministrativa per preset reazioni. |
| `report.php` | Report docente per singola attività. |
| `reports_course.php` | Report aggregato a livello corso. |
| `settings.php` | Impostazioni amministrative del plugin. |
| `styles.css` | Stili CSS del plugin. |
| `tests/admin_settings_test.php` | Test PHPUnit del plugin; richiedono ambiente Moodle test completo. |
| `tests/lib_test.php` | Test PHPUnit del plugin; richiedono ambiente Moodle test completo. |
| `tests/locallib_test.php` | Test PHPUnit del plugin; richiedono ambiente Moodle test completo. |
| `tests/tracker_test.php` | Test PHPUnit del plugin; richiedono ambiente Moodle test completo. |
| `version.php` | Manifest del plugin: versione, release, Moodle richiesto, maturità e dipendenze. |
| `view.php` | Entry point della vista studente. |

## 5. Database e dati persistenti
Le tabelle sono definite in `db/install.xml` e aggiornate da `db/upgrade.php`. Il modello dati separa istanza attività, segmenti grezzi, stato aggregato, definizioni reazioni ed eventi nota/reazione. La logica di merge e semplificazione degli intervalli è concentrata in `classes/local/tracker.php`, mentre retention e anonimizzazione sono concentrate in `classes/local/privacy_manager.php`.

## 6. Layer AMD e regole di build
I file in `amd/src/` sono sorgenti. I corrispondenti `amd/build/*.min.js` e `*.map` sono artefatti generati. Una patch che modifica `amd/src/*` deve eseguire realmente `grunt amd` nell’ambiente Moodle validato, controllare `git status` e includere tutti i file generati modificati.

## 7. Inventario funzioni PHP e JavaScript
La tabella seguente elenca ogni funzione rilevata nei file PHP e nei sorgenti AMD della baseline corrente. La descrizione è volutamente sintetica: per i dettagli implementativi fare riferimento al file sorgente e ai documenti tecnici collegati.
| File | Tipo | Funzione | Scopo |
|---|---:|---|---|
| `lib.php` | PHP | `videotrack_supports()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_whitelist_record()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_add_instance()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_update_instance()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_video_fields()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_playbackspeeds_field()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_save_uploaded_video()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `lib.php` | PHP | `videotrack_delete_upload_source_files()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `lib.php` | PHP | `videotrack_get_upload_url()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_module_context_from_data()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_save_poster_image()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `lib.php` | PHP | `videotrack_is_valid_reaction_icon_class()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `lib.php` | PHP | `videotrack_save_reaction_definitions()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `lib.php` | PHP | `videotrack_user_outline()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_user_complete()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_extend_settings_navigation()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_extend_navigation_course()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_html5controls()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_html5controls_field()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_player_behavior_fields()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_player_width()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_rewind_step()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_fastforward_step()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_vtt_url()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_captions_fields()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_process_grade_fields()` | Integra il plugin con il gradebook Moodle. |
| `lib.php` | PHP | `videotrack_grade_item_update()` | Integra il plugin con il gradebook Moodle. |
| `lib.php` | PHP | `videotrack_set_user_grade()` | Integra il plugin con il gradebook Moodle. |
| `lib.php` | PHP | `videotrack_get_user_grade()` | Integra il plugin con il gradebook Moodle. |
| `lib.php` | PHP | `videotrack_get_poster_url()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_delete_user_progress()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `lib.php` | PHP | `videotrack_grade_item_delete()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `lib.php` | PHP | `videotrack_delete_instance()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `lib.php` | PHP | `videotrack_get_coursemodule_info()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_view()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_get_completion_active_rule_descriptions()` | Gestisce regole o stato di completamento Moodle. |
| `lib.php` | PHP | `videotrack_update_completion_for_user()` | Gestisce regole o stato di completamento Moodle. |
| `lib.php` | PHP | `videotrack_reset_course_userdata()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_reset_course_form_definition()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_reset_course_form_defaults()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_resize_reaction_icon()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `lib.php` | PHP | `videotrack_pluginfile()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `lib.php` | PHP | `videotrack_recalculate_all_states()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_get_config_int()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_extract_videoid()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_extract_vimeo_id()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_get_playback_speeds()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_get_max_playback_rate()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_get_site_playback_speeds()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_format_seconds()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_build_required_reaction_notice()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `locallib.php` | PHP | `videotrack_get_reactions()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `locallib.php` | PHP | `videotrack_reaction_icon_url()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `locallib.php` | PHP | `videotrack_render_reaction_icon()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `locallib.php` | PHP | `videotrack_get_all_presets()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_save_presets()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `locallib.php` | PHP | `videotrack_get_preset_select_options()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_get_all_presets_for_js()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_require_preset_amd()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `locallib.php` | PHP | `videotrack_optional_iso_date_param()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `mod_form.php` | PHP | `definition()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `mod_form.php` | PHP | `add_reaction_elements()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `mod_form.php` | PHP | `get_reaction_repeat_count()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `mod_form.php` | PHP | `add_completion_rules()` | Gestisce regole o stato di completamento Moodle. |
| `mod_form.php` | PHP | `completion_rule_enabled()` | Gestisce regole o stato di completamento Moodle. |
| `mod_form.php` | PHP | `data_preprocessing()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `mod_form.php` | PHP | `validation()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `report.php` | PHP | `videotrack_report_user_label()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `report.php` | PHP | `videotrack_report_date_to_timestamp()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `report.php` | PHP | `videotrack_csv_safe()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `report.php` | PHP | `videotrack_csv_safe_row()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `amd/src/html5_player.js` | JS | `uuid()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `safeNumber()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `saveSegment()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/html5_player.js` | JS | `hasMedia()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `saveCurrentProgress()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/html5_player.js` | JS | `updateProgress()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/html5_player.js` | JS | `startSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/html5_player.js` | JS | `closeSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/html5_player.js` | JS | `startHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `stopHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `showResumeNotice()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/html5_player.js` | JS | `installGlobalListeners()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `buildPlayer()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/html5_player.js` | JS | `buildControlBar()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/html5_player.js` | JS | `makeBtn()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `attachTrackingEvents()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `setReactionButtons()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/html5_player.js` | JS | `announceReactionUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/html5_player.js` | JS | `installReactionHandler()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `appendReactionRow()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/html5_player.js` | JS | `loadTranscript()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `showTranscriptUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/html5_player.js` | JS | `stripVttCueMarkup()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `parseVTT()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `vttTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `renderTranscript()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/html5_player.js` | JS | `syncTranscript()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `getCurrentVideoTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `prefersReducedMotion()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `installNotesToggle()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `installNoteHandler()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `buildChaptersBar()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/html5_player.js` | JS | `showChaptersUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/html5_player.js` | JS | `renderChaptersBar()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/html5_player.js` | JS | `installPosterHandler()` | Installa listener o handler lato client. |
| `amd/src/html5_player.js` | JS | `removePoster()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `requested()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `pct()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `getCurrentTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `hasPlayer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `shouldSkip()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/html5_player.js` | JS | `sendBeacon()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/html5_player.js` | JS | `sendSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/html5_player.js` | JS | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `amd/src/player.js` | JS | `uuid()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `loadApi()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `updateProgress()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/player.js` | JS | `updateIntervalBar()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/player.js` | JS | `saveSegment()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/player.js` | JS | `hasPlayer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `saveCurrentProgress()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/player.js` | JS | `closeCurrentSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/player.js` | JS | `startCurrentSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/player.js` | JS | `setReactionButtons()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/player.js` | JS | `announceReactionUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/player.js` | JS | `replayFragment()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `handleSeekByPolling()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `onPlayerStateChange()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `showResumeNotice()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/player.js` | JS | `installGlobalListeners()` | Installa listener o handler lato client. |
| `amd/src/player.js` | JS | `appendReactionRow()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/player.js` | JS | `buildPlayer()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/player.js` | JS | `buildYouTubeSkipButtons()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/player.js` | JS | `getCurrentVideoTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `installReactionHandler()` | Installa listener o handler lato client. |
| `amd/src/player.js` | JS | `installNotesToggle()` | Installa listener o handler lato client. |
| `amd/src/player.js` | JS | `installNoteHandler()` | Installa listener o handler lato client. |
| `amd/src/player.js` | JS | `installPosterHandler()` | Installa listener o handler lato client. |
| `amd/src/player.js` | JS | `removePoster()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `showRewind()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/player.js` | JS | `showFF()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/player.js` | JS | `onHidden()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `sendBeacon()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/player.js` | JS | `sendSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/player.js` | JS | `onReady()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `onAutoplayBlocked()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `onError()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/player.js` | JS | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `amd/src/presets.js` | JS | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `amd/src/report.js` | JS | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `amd/src/vimeo_player.js` | JS | `uuid()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `saveSegment()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/vimeo_player.js` | JS | `hasPlayer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `saveCurrentProgress()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/vimeo_player.js` | JS | `updateProgress()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/vimeo_player.js` | JS | `startSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/vimeo_player.js` | JS | `closeSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/vimeo_player.js` | JS | `startHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `stopHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `showResumeNotice()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/vimeo_player.js` | JS | `installGlobalListeners()` | Installa listener o handler lato client. |
| `amd/src/vimeo_player.js` | JS | `loadVimeoSDK()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `buildPlayer()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/vimeo_player.js` | JS | `buildVimeoSkipButtons()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/vimeo_player.js` | JS | `setReactionButtons()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/vimeo_player.js` | JS | `announceReactionUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/vimeo_player.js` | JS | `installReactionHandler()` | Installa listener o handler lato client. |
| `amd/src/vimeo_player.js` | JS | `appendReactionRow()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/vimeo_player.js` | JS | `getCurrentVideoTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `installNotesToggle()` | Installa listener o handler lato client. |
| `amd/src/vimeo_player.js` | JS | `installNoteHandler()` | Installa listener o handler lato client. |
| `amd/src/vimeo_player.js` | JS | `installPosterHandler()` | Installa listener o handler lato client. |
| `amd/src/vimeo_player.js` | JS | `removePoster()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `showRewind()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/vimeo_player.js` | JS | `showFF()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/vimeo_player.js` | JS | `getCurrentTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `onHidden()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/vimeo_player.js` | JS | `sendBeacon()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/vimeo_player.js` | JS | `sendSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/vimeo_player.js` | JS | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `amd/src/core/adapter.js` | JS | `normaliseProviderType()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/adapter.js` | JS | `isKnownProviderType()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getCapabilityDefinition()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getCapabilityMethods()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getCapabilityProperties()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `isAvailable()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `can()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `hasCapability()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getCapabilities()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canCurrentTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canDuration()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canPlay()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canPause()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canSeek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canPlaybackRate()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canVolume()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canMute()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canPaused()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `canEnded()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `normaliseTime()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/adapter.js` | JS | `resolveSkipTarget()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getCurrentTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getDuration()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `normaliseVolume()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/adapter.js` | JS | `getVolume()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `setVolume()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `isMuted()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `setMuted()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `getPlaybackRate()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `setPlaybackRate()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `isPaused()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `isEnded()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `run()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `play()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `pause()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/adapter.js` | JS | `seek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api.js` | JS | `call()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api.js` | JS | `attemptRequest()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api.js` | JS | `buildSegmentArgs()` | Costruisce markup, configurazione o struttura dati per interfaccia e rendering. |
| `amd/src/core/api.js` | JS | `saveSegment()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/beacon.js` | JS | `sendSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/core/confirm.js` | JS | `attachToForms()` | Installa listener o handler lato client. |
| `amd/src/core/debug.js` | JS | `log()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `create()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `ensure()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `emit()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `on()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `off()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `once()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `count()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/events.js` | JS | `clear()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `uuid()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `getIntervalBarColor()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `parseIntervals()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `updateIntervalBar()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/player.js` | JS | `showResumeNotice()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player.js` | JS | `configureStatus()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `showStatusMessage()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player.js` | JS | `showErrorStatusMessage()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player.js` | JS | `announceStatusMessage()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player.js` | JS | `onFirstPlay()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `installNoteHandler()` | Installa listener o handler lato client. |
| `amd/src/core/player.js` | JS | `removePoster()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player.js` | JS | `getPlayerShell()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/progress.js` | JS | `pickNumber()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/progress.js` | JS | `formatPercent()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/progress.js` | JS | `updatePercentText()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/progress.js` | JS | `updateFallbackProgress()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/progress.js` | JS | `updateProgress()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/reactions.js` | JS | `createState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/reactions.js` | JS | `getStatusRegion()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/reactions.js` | JS | `announceStatus()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/reactions.js` | JS | `announceAvailability()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/reactions.js` | JS | `announceUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/reactions.js` | JS | `setButtons()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/reactions.js` | JS | `text()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/segment.js` | JS | `finiteSeconds()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/segment.js` | JS | `normaliseSaveReason()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/segment.js` | JS | `clampSegmentTimes()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/core/segment.js` | JS | `calculateInteractionEnd()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/session.js` | JS | `uuid()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/state.js` | JS | `create()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `getState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `clampTimeout()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `configure()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `normaliseMessage()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/status.js` | JS | `getLiveRegion()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `remove()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `getContainer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `normaliseDismissLabel()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/status.js` | JS | `normaliseTimeout()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/status.js` | JS | `announce()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/status.js` | JS | `clear()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/status.js` | JS | `show()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/status.js` | JS | `text()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker.js` | JS | `saveHeartbeatIfDue()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker.js` | JS | `runHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker.js` | JS | `sendUnloadBeacon()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/core/tracker.js` | JS | `installLifecycleHandlers()` | Installa listener o handler lato client. |
| `amd/src/core/tracker.js` | JS | `uninstallLifecycleHandlers()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker.js` | JS | `cancelPendingRequests()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/ui.js` | JS | `setReactionButtons()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `amd/src/core/ui.js` | JS | `isSafeIconSrc()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/ui.js` | JS | `isSafeIconClass()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/ui.js` | JS | `appendIconSafe()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `safeInt()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `pad()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `formatSeconds()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `decodeHtmlEntitiesOnce()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `amd/src/core/utils.js` | JS | `decodeHtmlEntitiesForValidation()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `amd/src/core/utils.js` | JS | `validateWebVttText()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `amd/src/core/utils.js` | JS | `validateTextResponse()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `amd/src/core/utils.js` | JS | `isSafeFetchUrl()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `isSafeBeaconUrl()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/core/utils.js` | JS | `fetchTextWithTimeout()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `sessionSet()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `sessionGet()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/utils.js` | JS | `contentType()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `getNetworkState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `isBrowserOffline()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `getErrorCode()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `getErrorMessage()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `getErrorStatus()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `classifyAjaxError()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/error.js` | JS | `normaliseAjaxError()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/api/error.js` | JS | `isTransientAjaxError()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/retry.js` | JS | `getRetryJitter()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/retry.js` | JS | `normalizeRetryCount()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/api/retry.js` | JS | `delay()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/retry.js` | JS | `performanceOffset()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/retry.js` | JS | `locationOffset()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/scope.js` | JS | `createRequestScope()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/scope.js` | JS | `nextToken()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/scope.js` | JS | `isCurrent()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/scope.js` | JS | `resolveIfCurrent()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/transport.js` | JS | `withTimeout()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/transport.js` | JS | `send()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/validator.js` | JS | `normaliseMethodName()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/api/validator.js` | JS | `createValidationError()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `amd/src/core/api/validator.js` | JS | `isPlainObject()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/validator.js` | JS | `getUtf8Length()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/validator.js` | JS | `isSafeArgValue()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/validator.js` | JS | `hasNonNegativeNumber()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/api/validator.js` | JS | `validateArgs()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `amd/src/core/player/intervalbar.js` | JS | `getColor()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/intervalbar.js` | JS | `parse()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/intervalbar.js` | JS | `updateTextAlternative()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/player/intervalbar.js` | JS | `drawIntervals()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/intervalbar.js` | JS | `update()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/player/notes.js` | JS | `getRemainingChars()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/notes.js` | JS | `updateCharCounter()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `amd/src/core/player/notes.js` | JS | `setButtonState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/notes.js` | JS | `installHandler()` | Installa listener o handler lato client. |
| `amd/src/core/player/notes.js` | JS | `ajax()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/notes.js` | JS | `restoreSaveButtonState()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/player/notes.js` | JS | `showResponseWarnings()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/notes.js` | JS | `announceLimitedNotes()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/notes.js` | JS | `announceCharThreshold()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/notes.js` | JS | `setLocalButtonState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/notes.js` | JS | `message()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/poster.js` | JS | `onFirstPlay()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/poster.js` | JS | `remove()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/progress.js` | JS | `clampSegmentTimes()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/core/player/progress.js` | JS | `normaliseSaveReason()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/player/progress.js` | JS | `saveCurrentProgress()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/player/progress.js` | JS | `sendBeaconSegment()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/core/player/reactions.js` | JS | `announceAvailability()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/reactions.js` | JS | `announceUnavailable()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/resume.js` | JS | `showNotice()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/status.js` | JS | `getShell()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/status.js` | JS | `configure()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/status.js` | JS | `showMessage()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/status.js` | JS | `showErrorMessage()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/status.js` | JS | `announce()` | Mostra o annuncia messaggi/interazioni UI in modo accessibile. |
| `amd/src/core/player/notes/row.js` | JS | `appendRow()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/player/notes/toggle.js` | JS | `install()` | Installa listener o handler lato client. |
| `amd/src/core/player/notes/toggle.js` | JS | `setCollapsed()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `on()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `once()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `off()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `count()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `clear()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/events.js` | JS | `emit()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `safeBooleanCallback()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `resetHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `captureHeartbeatSegment()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `amd/src/core/tracker/heartbeat.js` | JS | `normaliseHeartbeatInterval()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/tracker/heartbeat.js` | JS | `pollInterval()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `startPolling()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `stopPolling()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `shouldSaveHeartbeat()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/heartbeat.js` | JS | `reopenAfterHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `saveHeartbeatIfDue()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/heartbeat.js` | JS | `runHeartbeat()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `clearHeartbeatRunning()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `hasPlayer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/heartbeat.js` | JS | `shouldSkip()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/lifecycle.js` | JS | `runAfterStop()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/lifecycle.js` | JS | `sendUnloadBeacon()` | Gestisce invio best-effort dei segmenti tramite sendBeacon quando disponibile. |
| `amd/src/core/tracker/lifecycle.js` | JS | `closeThenStop()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/lifecycle.js` | JS | `installLifecycleHandlers()` | Installa listener o handler lato client. |
| `amd/src/core/tracker/lifecycle.js` | JS | `uninstallLifecycleHandlers()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/lifecycle.js` | JS | `hasPlayer()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/lifecycle.js` | JS | `stopPolling()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/segment.js` | JS | `openSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/core/tracker/segment.js` | JS | `closeSegment()` | Gestisce segmenti di visione e salvataggio progressi. |
| `amd/src/core/tracker/segment.js` | JS | `enqueueSegmentSave()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/segment.js` | JS | `isPlayerAvailable()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/segment.js` | JS | `closeAndSaveSegment()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/segment.js` | JS | `reopenAfterInteractionSave()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/segment.js` | JS | `saveCurrentProgress()` | Salva dati del plugin dopo normalizzazione e validazione. |
| `amd/src/core/tracker/state.js` | JS | `normaliseTime()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/tracker/state.js` | JS | `normaliseTrackerState()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `amd/src/core/tracker/state.js` | JS | `isKnownTrackerState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `getTrackerState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `getTransitionToken()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `isTransitionCurrent()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `canTransition()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `applyTrackerStateFlags()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `setTrackerState()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markIdle()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markPlaying()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markPaused()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markSeeking()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markEnded()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/state.js` | JS | `markDestroyed()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `syncTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `resolveCurrentTime()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `markProgrammaticSeek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `consumeProgrammaticSeek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `resolveSeek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `blockSeek()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `clearSeekBlock()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `amd/src/core/tracker/time.js` | JS | `shouldStopReplay()` | Funzione client-side AMD per coordinare UI, player, tracking o servizi AJAX. |
| `backup/moodle2/backup_videotrack_activity_task.class.php` | PHP | `define_my_settings()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/backup_videotrack_activity_task.class.php` | PHP | `define_my_steps()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/backup_videotrack_activity_task.class.php` | PHP | `encode_content_links()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `backup/moodle2/backup_videotrack_stepslib.php` | PHP | `define_structure()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | PHP | `define_my_settings()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | PHP | `define_my_steps()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | PHP | `define_decode_contents()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_activity_task.class.php` | PHP | `define_decode_rules()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `define_structure()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `process_videotrack()` | Elabora input provenienti da form, backup/restore o web service. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `process_videotrack_reaction()` | Elabora input provenienti da form, backup/restore o web service. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `process_videotrack_segment()` | Elabora input provenienti da form, backup/restore o web service. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `process_videotrack_state()` | Elabora input provenienti da form, backup/restore o web service. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `process_videotrack_reactionevent()` | Elabora input provenienti da form, backup/restore o web service. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `normalise_interval_json()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `get_restored_cmid()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `backup/moodle2/restore_videotrack_stepslib.php` | PHP | `after_execute()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/admin/setting_int_range.php` | PHP | `__construct()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/admin/setting_int_range.php` | PHP | `validate()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/admin/setting_nonnegative_int.php` | PHP | `validate()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/admin/setting_retention_days.php` | PHP | `write_setting()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/completion/custom_completion.php` | PHP | `get_defined_custom_rules()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/completion/custom_completion.php` | PHP | `get_state()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/completion/custom_completion.php` | PHP | `get_custom_rule_descriptions()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/completion/custom_completion.php` | PHP | `get_required_reaction_labels()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/activity_completed.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/activity_completed.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/activity_completed.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/activity_completed.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/activity_completed.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/course_module_viewed.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/note_deleted.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/note_deleted.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_deleted.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_deleted.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_deleted.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_saved.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/note_saved.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_saved.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_saved.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/note_saved.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/notes_exported.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/notes_exported.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/notes_exported.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/notes_exported.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/notes_exported.php` | PHP | `validate_data()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/event/notes_exported.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/notes_exported.php` | PHP | `get_other_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_deleted.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/reaction_deleted.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_deleted.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_deleted.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_deleted.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_saved.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/reaction_saved.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_saved.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_saved.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/reaction_saved.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/segment_saved.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/segment_saved.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/segment_saved.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/segment_saved.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/segment_saved.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/student_progress_reset.php` | PHP | `init()` | Inizializza il modulo, l’evento o la pagina collegando configurazione, DOM e callback necessari. |
| `classes/event/student_progress_reset.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/student_progress_reset.php` | PHP | `get_description()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/student_progress_reset.php` | PHP | `validate_data()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/event/student_progress_reset.php` | PHP | `get_url()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/event/student_progress_reset.php` | PHP | `get_objectid_mapping()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/external/delete_note.php` | PHP | `execute_parameters()` | Dichiara lo schema dei parametri del web service. |
| `classes/external/delete_note.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `classes/external/delete_note.php` | PHP | `execute_returns()` | Dichiara lo schema del valore di ritorno del web service. |
| `classes/external/delete_reaction.php` | PHP | `execute_parameters()` | Dichiara lo schema dei parametri del web service. |
| `classes/external/delete_reaction.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `classes/external/delete_reaction.php` | PHP | `execute_returns()` | Dichiara lo schema del valore di ritorno del web service. |
| `classes/external/helper.php` | PHP | `require_ajax_sesskey()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/external/helper.php` | PHP | `validate_positive_id()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/external/helper.php` | PHP | `validate_session_id()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/external/helper.php` | PHP | `validate_end_reason()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/external/helper.php` | PHP | `validate_bounded_float()` | Valida parametri, configurazioni o dati utente prima dell’uso. |
| `classes/external/helper.php` | PHP | `load_and_validate_context()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/external/save_note.php` | PHP | `execute_parameters()` | Dichiara lo schema dei parametri del web service. |
| `classes/external/save_note.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `classes/external/save_note.php` | PHP | `execute_returns()` | Dichiara lo schema del valore di ritorno del web service. |
| `classes/external/save_reaction.php` | PHP | `execute_parameters()` | Dichiara lo schema dei parametri del web service. |
| `classes/external/save_reaction.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `classes/external/save_reaction.php` | PHP | `execute_returns()` | Dichiara lo schema del valore di ritorno del web service. |
| `classes/external/save_segment.php` | PHP | `execute_parameters()` | Dichiara lo schema dei parametri del web service. |
| `classes/external/save_segment.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `classes/external/save_segment.php` | PHP | `execute_returns()` | Dichiara lo schema del valore di ritorno del web service. |
| `classes/local/privacy_manager.php` | PHP | `retention_period_seconds()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymisation_salt()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymous_userid()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymous_sessionid()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `delete_user_data_in_context()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/local/privacy_manager.php` | PHP | `delete_all_user_data_in_context()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_user_in_context()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_all_users_in_context()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_user_records()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_expired_records()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_old_user_rows()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_state_rows()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `anonymise_one_state_row()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/local/privacy_manager.php` | PHP | `merge_interval_json()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `current_state_snapshot()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/local/tracker.php` | PHP | `normalise_interval()` | Normalizza valori esterni in una forma sicura e prevedibile. |
| `classes/local/tracker.php` | PHP | `decode_intervals()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `encode_intervals()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `merge_intervals()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `cap_intervals()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `simplify_intervals()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `covered_seconds()` | Gestisce intervalli di visione e loro rappresentazione persistente. |
| `classes/local/tracker.php` | PHP | `reaction_counts()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `classes/local/tracker.php` | PHP | `invalidate_reaction_counts_cache()` | Gestisce definizioni, eventi o UI delle reazioni contestuali. |
| `classes/local/tracker.php` | PHP | `has_recent_playback()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/local/tracker.php` | PHP | `has_watched_videotime()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/local/tracker.php` | PHP | `completion_satisfied()` | Gestisce regole o stato di completamento Moodle. |
| `classes/local/tracker.php` | PHP | `create_default_state()` | Funzione server-side del plugin; lo scopo specifico è descritto dal nome e dal contesto del file. |
| `classes/local/tracker.php` | PHP | `update_state()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `classes/local/tracker.php` | PHP | `update_moodle_completion_if_changed()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `classes/local/tracker.php` | PHP | `refresh_completion()` | Aggiorna stato, progressi, completion o rappresentazioni derivate. |
| `classes/privacy/provider.php` | PHP | `format_interval_second()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/privacy/provider.php` | PHP | `get_metadata()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/privacy/provider.php` | PHP | `get_contexts_for_userid()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/privacy/provider.php` | PHP | `get_users_in_context()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/privacy/provider.php` | PHP | `export_user_data()` | Partecipa a esportazione, cancellazione o anonimizzazione dei dati personali. |
| `classes/privacy/provider.php` | PHP | `delete_data_for_all_users_in_context()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/privacy/provider.php` | PHP | `delete_data_for_user()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/privacy/provider.php` | PHP | `delete_data_for_users()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/privacy/provider.php` | PHP | `delete_records_for_users_in_context()` | Elimina o anonimizza dati nel perimetro consentito da Moodle e dalle capability. |
| `classes/task/cleanup_task.php` | PHP | `get_name()` | Recupera o calcola il dato indicato dal nome, applicando normalizzazione e controlli quando necessari. |
| `classes/task/cleanup_task.php` | PHP | `execute()` | Esegue il web service dopo validazione di parametri, sesskey, contesto e capability. |
| `db/upgrade.php` | PHP | `xmldb_videotrack_upgrade()` | Integra il plugin con il gradebook Moodle. |
| `tests/admin_settings_test.php` | PHP | `setUp()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/admin_settings_test.php` | PHP | `test_nonnegative_int_validation_accepts_zero_and_rejects_invalid_values()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/admin_settings_test.php` | PHP | `test_int_range_validation_enforces_configured_bounds()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/admin_settings_test.php` | PHP | `test_unlimited_retention_requires_explicit_confirmation()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/lib_test.php` | PHP | `test_supports_expected_core_features()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/lib_test.php` | PHP | `test_groups_are_explicitly_not_supported()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/lib_test.php` | PHP | `test_activity_chooser_metadata_is_reported()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/lib_test.php` | PHP | `test_unknown_feature_returns_null()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `setUp()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_extract_videoid_accepts_supported_youtube_urls()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_extract_vimeo_id_accepts_supported_vimeo_urls()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_format_seconds_clamps_and_formats_duration()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_get_config_int_preserves_zero_and_clamps_values()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_get_config_int_rejects_invalid_bounds()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/locallib_test.php` | PHP | `test_get_playback_speeds_filters_and_applies_site_cap()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `setUp()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `test_normalise_interval_clamps_and_rejects_empty_ranges()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `test_decode_intervals_filters_invalid_ranges()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `test_merge_intervals_and_covered_seconds_are_deterministic()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `test_simplify_intervals_never_overestimates_coverage()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |
| `tests/tracker_test.php` | PHP | `test_cap_intervals_limits_count_and_preserves_order()` | Metodo di test PHPUnit o preparazione fixture per verificare comportamento server-side. |

## 8. Parti obsolete rimosse
- Rimossa la cronologia release estesa dalla guida tecnica: non descriveva più la struttura corrente e rendeva difficile la review.
- Rimosso `docs/ajax_layer.md` perché duplicava il documento canonico `docs/it/ajax-layer.md`.
- I documenti di audit/release ancora utili sono mantenuti e referenziati nella sezione 2.
