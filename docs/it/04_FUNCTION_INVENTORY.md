# Inventario delle funzioni nominate

Generato dai sorgenti VideoTrack 1.6.28. Le closure anonime sono intenzionalmente omesse.

Voci PHP: **470**. Voci AMD: **596**.

## Funzioni e metodi PHP

| File:riga | Funzione o metodo | Contratto |
|---|---|---|
| `backup/moodle2/backup_videotrack_activity_task.class.php:36` | `backup_videotrack_activity_task::define_my_settings` | Definisce `my settings`. |
| `backup/moodle2/backup_videotrack_activity_task.class.php:42` | `backup_videotrack_activity_task::define_my_steps` | Definisce `my steps`. |
| `backup/moodle2/backup_videotrack_activity_task.class.php:52` | `backup_videotrack_activity_task::encode_content_links` | Implementa `encode content links`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/backup_videotrack_stepslib.php:34` | `backup_videotrack_activity_structure_step::define_structure` | Definisce `structure`. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:36` | `restore_videotrack_activity_task::define_my_settings` | Definisce `my settings`. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:42` | `restore_videotrack_activity_task::define_my_steps` | Definisce `my steps`. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:51` | `restore_videotrack_activity_task::define_decode_contents` | Definisce `decode contents`. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:60` | `restore_videotrack_activity_task::define_decode_rules` | Definisce `decode rules`. |
| `backup/moodle2/restore_videotrack_stepslib.php:34` | `restore_videotrack_activity_structure_step::define_structure` | Definisce `structure`. |
| `backup/moodle2/restore_videotrack_stepslib.php:59` | `restore_videotrack_activity_structure_step::process_videotrack` | Implementa `process videotrack`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:93` | `restore_videotrack_activity_structure_step::process_videotrack_reaction` | Implementa `process videotrack reaction`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:123` | `restore_videotrack_activity_structure_step::process_videotrack_segment` | Implementa `process videotrack segment`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:165` | `restore_videotrack_activity_structure_step::process_videotrack_state` | Implementa `process videotrack state`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:211` | `restore_videotrack_activity_structure_step::process_videotrack_reactionevent` | Implementa `process videotrack reactionevent`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:294` | `restore_videotrack_activity_structure_step::process_videotrack_integrityevent` | Implementa `process videotrack integrityevent`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:331` | `restore_videotrack_activity_structure_step::process_videotrack_acknowledgement` | Implementa `process videotrack acknowledgement`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:376` | `restore_videotrack_activity_structure_step::normalise_interval_json` | Implementa `normalise interval json`; il contratto dettagliato è nel DocBlock sorgente. |
| `backup/moodle2/restore_videotrack_stepslib.php:387` | `restore_videotrack_activity_structure_step::get_restored_cmid` | Restituisce `restored cmid`. |
| `backup/moodle2/restore_videotrack_stepslib.php:394` | `restore_videotrack_activity_structure_step::after_execute` | Implementa `after execute`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/admin/setting_int_range.php:43` | `mod_videotrack\admin\setting_int_range::__construct` | Implementa `construct`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/admin/setting_int_range.php:61` | `mod_videotrack\admin\setting_int_range::validate` | Implementa `validate`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/admin/setting_nonnegative_int.php:36` | `mod_videotrack\admin\setting_nonnegative_int::validate` | Implementa `validate`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/admin/setting_retention_days.php:38` | `mod_videotrack\admin\setting_retention_days::write_setting` | Implementa `write setting`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/completion/custom_completion.php:36` | `mod_videotrack\completion\custom_completion::get_sort_order` | Restituisce `sort order`. |
| `classes/completion/custom_completion.php:51` | `mod_videotrack\completion\custom_completion::get_defined_custom_rules` | Restituisce `defined custom rules`. |
| `classes/completion/custom_completion.php:61` | `mod_videotrack\completion\custom_completion::get_state` | Restituisce `state`. |
| `classes/completion/custom_completion.php:147` | `mod_videotrack\completion\custom_completion::get_custom_rule_descriptions` | Restituisce `custom rule descriptions`. |
| `classes/completion/custom_completion.php:195` | `mod_videotrack\completion\custom_completion::get_required_reaction_labels` | Restituisce `required reaction labels`. |
| `classes/event/acknowledgement_confirmed.php:30` | `mod_videotrack\event\acknowledgement_confirmed::init` | Inizializza `init`. |
| `classes/event/acknowledgement_confirmed.php:41` | `mod_videotrack\event\acknowledgement_confirmed::get_name` | Restituisce `name`. |
| `classes/event/acknowledgement_confirmed.php:50` | `mod_videotrack\event\acknowledgement_confirmed::get_description` | Restituisce `description`. |
| `classes/event/acknowledgement_confirmed.php:60` | `mod_videotrack\event\acknowledgement_confirmed::get_url` | Restituisce `url`. |
| `classes/event/acknowledgement_confirmed.php:69` | `mod_videotrack\event\acknowledgement_confirmed::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/activity_completed.php:31` | `mod_videotrack\event\activity_completed::init` | Inizializza `init`. |
| `classes/event/activity_completed.php:42` | `mod_videotrack\event\activity_completed::get_name` | Restituisce `name`. |
| `classes/event/activity_completed.php:51` | `mod_videotrack\event\activity_completed::get_description` | Restituisce `description`. |
| `classes/event/activity_completed.php:63` | `mod_videotrack\event\activity_completed::get_url` | Restituisce `url`. |
| `classes/event/activity_completed.php:72` | `mod_videotrack\event\activity_completed::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/bookmark_deleted.php:30` | `mod_videotrack\event\bookmark_deleted::init` | Inizializza `init`. |
| `classes/event/bookmark_deleted.php:41` | `mod_videotrack\event\bookmark_deleted::get_name` | Restituisce `name`. |
| `classes/event/bookmark_deleted.php:50` | `mod_videotrack\event\bookmark_deleted::get_description` | Restituisce `description`. |
| `classes/event/bookmark_deleted.php:60` | `mod_videotrack\event\bookmark_deleted::get_url` | Restituisce `url`. |
| `classes/event/bookmark_deleted.php:69` | `mod_videotrack\event\bookmark_deleted::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/bookmark_exported.php:30` | `mod_videotrack\event\bookmark_exported::init` | Inizializza `init`. |
| `classes/event/bookmark_exported.php:41` | `mod_videotrack\event\bookmark_exported::get_name` | Restituisce `name`. |
| `classes/event/bookmark_exported.php:50` | `mod_videotrack\event\bookmark_exported::get_description` | Restituisce `description`. |
| `classes/event/bookmark_exported.php:60` | `mod_videotrack\event\bookmark_exported::get_url` | Restituisce `url`. |
| `classes/event/bookmark_exported.php:67` | `mod_videotrack\event\bookmark_exported::validate_data` | Valida `data`. |
| `classes/event/bookmark_exported.php:79` | `mod_videotrack\event\bookmark_exported::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/bookmark_saved.php:30` | `mod_videotrack\event\bookmark_saved::init` | Inizializza `init`. |
| `classes/event/bookmark_saved.php:41` | `mod_videotrack\event\bookmark_saved::get_name` | Restituisce `name`. |
| `classes/event/bookmark_saved.php:50` | `mod_videotrack\event\bookmark_saved::get_description` | Restituisce `description`. |
| `classes/event/bookmark_saved.php:60` | `mod_videotrack\event\bookmark_saved::get_url` | Restituisce `url`. |
| `classes/event/bookmark_saved.php:69` | `mod_videotrack\event\bookmark_saved::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/course_module_viewed.php:30` | `mod_videotrack\event\course_module_viewed::init` | Inizializza `init`. |
| `classes/event/note_deleted.php:30` | `mod_videotrack\event\note_deleted::init` | Inizializza `init`. |
| `classes/event/note_deleted.php:41` | `mod_videotrack\event\note_deleted::get_name` | Restituisce `name`. |
| `classes/event/note_deleted.php:50` | `mod_videotrack\event\note_deleted::get_description` | Restituisce `description`. |
| `classes/event/note_deleted.php:60` | `mod_videotrack\event\note_deleted::get_url` | Restituisce `url`. |
| `classes/event/note_deleted.php:69` | `mod_videotrack\event\note_deleted::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/note_saved.php:32` | `mod_videotrack\event\note_saved::init` | Inizializza `init`. |
| `classes/event/note_saved.php:43` | `mod_videotrack\event\note_saved::get_name` | Restituisce `name`. |
| `classes/event/note_saved.php:52` | `mod_videotrack\event\note_saved::get_description` | Restituisce `description`. |
| `classes/event/note_saved.php:64` | `mod_videotrack\event\note_saved::get_url` | Restituisce `url`. |
| `classes/event/note_saved.php:73` | `mod_videotrack\event\note_saved::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/notes_exported.php:30` | `mod_videotrack\event\notes_exported::init` | Inizializza `init`. |
| `classes/event/notes_exported.php:41` | `mod_videotrack\event\notes_exported::get_name` | Restituisce `name`. |
| `classes/event/notes_exported.php:50` | `mod_videotrack\event\notes_exported::get_description` | Restituisce `description`. |
| `classes/event/notes_exported.php:62` | `mod_videotrack\event\notes_exported::get_url` | Restituisce `url`. |
| `classes/event/notes_exported.php:70` | `mod_videotrack\event\notes_exported::validate_data` | Valida `data`. |
| `classes/event/notes_exported.php:94` | `mod_videotrack\event\notes_exported::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/notes_exported.php:103` | `mod_videotrack\event\notes_exported::get_other_mapping` | Restituisce `other mapping`. |
| `classes/event/reaction_deleted.php:30` | `mod_videotrack\event\reaction_deleted::init` | Inizializza `init`. |
| `classes/event/reaction_deleted.php:41` | `mod_videotrack\event\reaction_deleted::get_name` | Restituisce `name`. |
| `classes/event/reaction_deleted.php:50` | `mod_videotrack\event\reaction_deleted::get_description` | Restituisce `description`. |
| `classes/event/reaction_deleted.php:61` | `mod_videotrack\event\reaction_deleted::get_url` | Restituisce `url`. |
| `classes/event/reaction_deleted.php:70` | `mod_videotrack\event\reaction_deleted::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/reaction_saved.php:30` | `mod_videotrack\event\reaction_saved::init` | Inizializza `init`. |
| `classes/event/reaction_saved.php:41` | `mod_videotrack\event\reaction_saved::get_name` | Restituisce `name`. |
| `classes/event/reaction_saved.php:50` | `mod_videotrack\event\reaction_saved::get_description` | Restituisce `description`. |
| `classes/event/reaction_saved.php:65` | `mod_videotrack\event\reaction_saved::get_url` | Restituisce `url`. |
| `classes/event/reaction_saved.php:74` | `mod_videotrack\event\reaction_saved::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/report_exported.php:30` | `mod_videotrack\event\report_exported::init` | Inizializza `init`. |
| `classes/event/report_exported.php:41` | `mod_videotrack\event\report_exported::get_name` | Restituisce `name`. |
| `classes/event/report_exported.php:50` | `mod_videotrack\event\report_exported::get_description` | Restituisce `description`. |
| `classes/event/report_exported.php:61` | `mod_videotrack\event\report_exported::get_url` | Restituisce `url`. |
| `classes/event/report_exported.php:68` | `mod_videotrack\event\report_exported::validate_data` | Valida `data`. |
| `classes/event/report_exported.php:86` | `mod_videotrack\event\report_exported::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/segment_saved.php:31` | `mod_videotrack\event\segment_saved::init` | Inizializza `init`. |
| `classes/event/segment_saved.php:42` | `mod_videotrack\event\segment_saved::get_name` | Restituisce `name`. |
| `classes/event/segment_saved.php:51` | `mod_videotrack\event\segment_saved::get_description` | Restituisce `description`. |
| `classes/event/segment_saved.php:63` | `mod_videotrack\event\segment_saved::get_url` | Restituisce `url`. |
| `classes/event/segment_saved.php:72` | `mod_videotrack\event\segment_saved::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/event/student_progress_reset.php:30` | `mod_videotrack\event\student_progress_reset::init` | Inizializza `init`. |
| `classes/event/student_progress_reset.php:41` | `mod_videotrack\event\student_progress_reset::get_name` | Restituisce `name`. |
| `classes/event/student_progress_reset.php:50` | `mod_videotrack\event\student_progress_reset::get_description` | Restituisce `description`. |
| `classes/event/student_progress_reset.php:65` | `mod_videotrack\event\student_progress_reset::validate_data` | Valida `data`. |
| `classes/event/student_progress_reset.php:82` | `mod_videotrack\event\student_progress_reset::get_url` | Restituisce `url`. |
| `classes/event/student_progress_reset.php:91` | `mod_videotrack\event\student_progress_reset::get_objectid_mapping` | Restituisce `objectid mapping`. |
| `classes/external/delete_bookmark.php:44` | `mod_videotrack\external\delete_bookmark::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/delete_bookmark.php:58` | `mod_videotrack\external\delete_bookmark::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/delete_bookmark.php:91` | `mod_videotrack\external\delete_bookmark::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/delete_note.php:44` | `mod_videotrack\external\delete_note::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/delete_note.php:65` | `mod_videotrack\external\delete_note::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/delete_note.php:118` | `mod_videotrack\external\delete_note::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/delete_reaction.php:45` | `mod_videotrack\external\delete_reaction::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/delete_reaction.php:62` | `mod_videotrack\external\delete_reaction::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/delete_reaction.php:133` | `mod_videotrack\external\delete_reaction::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/helper.php:43` | `mod_videotrack\external\helper::require_ajax_sesskey` | Implementa `require ajax sesskey`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/external/helper.php:55` | `mod_videotrack\external\helper::validate_positive_id` | Valida `positive id`. |
| `classes/external/helper.php:75` | `mod_videotrack\external\helper::validate_session_id` | Valida `session id`. |
| `classes/external/helper.php:89` | `mod_videotrack\external\helper::validate_end_reason` | Valida `end reason`. |
| `classes/external/helper.php:110` | `mod_videotrack\external\helper::validate_bounded_float` | Valida `bounded float`. |
| `classes/external/helper.php:123` | `mod_videotrack\external\helper::load_and_validate_context` | Carica `and validate context`. |
| `classes/external/save_bookmark.php:45` | `mod_videotrack\external\save_bookmark::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/save_bookmark.php:65` | `mod_videotrack\external\save_bookmark::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/save_bookmark.php:168` | `mod_videotrack\external\save_bookmark::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/save_integrity_event.php:47` | `mod_videotrack\external\save_integrity_event::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/save_integrity_event.php:65` | `mod_videotrack\external\save_integrity_event::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/save_integrity_event.php:144` | `mod_videotrack\external\save_integrity_event::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/save_note.php:48` | `mod_videotrack\external\save_note::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/save_note.php:68` | `mod_videotrack\external\save_note::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/save_note.php:215` | `mod_videotrack\external\save_note::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/save_reaction.php:45` | `mod_videotrack\external\save_reaction::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/save_reaction.php:65` | `mod_videotrack\external\save_reaction::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/save_reaction.php:285` | `mod_videotrack\external\save_reaction::export_reaction_for_client` | Esporta `reaction for client`. |
| `classes/external/save_reaction.php:312` | `mod_videotrack\external\save_reaction::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/external/save_segment.php:48` | `mod_videotrack\external\save_segment::execute_parameters` | Esegue il contratto del servizio `parameters`. |
| `classes/external/save_segment.php:76` | `mod_videotrack\external\save_segment::execute` | Esegue il contratto del servizio `execute`. |
| `classes/external/save_segment.php:281` | `mod_videotrack\external\save_segment::execute_returns` | Esegue il contratto del servizio `returns`. |
| `classes/form/forum_post_form.php:40` | `mod_videotrack\form\forum_post_form::definition` | Implementa `definition`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/form/forum_post_form.php:114` | `mod_videotrack\form\forum_post_form::validation` | Implementa `validation`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:46` | `mod_videotrack\local\acknowledgement::is_enabled` | Determina `enabled`. |
| `classes/local/acknowledgement.php:57` | `mod_videotrack\local\acknowledgement::has_visible_text` | Verifica la presenza di `visible text`. |
| `classes/local/acknowledgement.php:69` | `mod_videotrack\local\acknowledgement::timing` | Implementa `timing`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:82` | `mod_videotrack\local\acknowledgement::requires_video_end` | Implementa `requires video end`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:94` | `mod_videotrack\local\acknowledgement::statement_hash` | Implementa `statement hash`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:108` | `mod_videotrack\local\acknowledgement::progress_snapshot` | Implementa `progress snapshot`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:133` | `mod_videotrack\local\acknowledgement::has_reached_video_end` | Verifica la presenza di `reached video end`. |
| `classes/local/acknowledgement.php:156` | `mod_videotrack\local\acknowledgement::can_confirm` | Implementa `can confirm`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:172` | `mod_videotrack\local\acknowledgement::analytics_summary` | Implementa `analytics summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:232` | `mod_videotrack\local\acknowledgement::current_record` | Implementa `current record`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:256` | `mod_videotrack\local\acknowledgement::confirm` | Implementa `confirm`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/acknowledgement.php:316` | `mod_videotrack\local\acknowledgement::current_records` | Implementa `current records`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:48` | `mod_videotrack\local\analytics::resolve_duration` | Risoluzione di `duration`. |
| `classes/local/analytics.php:66` | `mod_videotrack\local\analytics::default_bin_size` | Implementa `default bin size`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:93` | `mod_videotrack\local\analytics::restrict_to_own_groups` | Implementa `restrict to own groups`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:104` | `mod_videotrack\local\analytics::normalise_bin_size` | Implementa `normalise bin size`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:132` | `mod_videotrack\local\analytics::build` | Implementa `build`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:232` | `mod_videotrack\local\analytics::build_from_states` | Costruisce `from states`. |
| `classes/local/analytics.php:270` | `mod_videotrack\local\analytics::apply_privacy_threshold` | Implementa `apply privacy threshold`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:314` | `mod_videotrack\local\analytics::count_summary` | Implementa `count summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:337` | `mod_videotrack\local\analytics::reaction_summary` | Implementa `reaction summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:349` | `mod_videotrack\local\analytics::cluster_reactions` | Implementa `cluster reactions`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:410` | `mod_videotrack\local\analytics::append_visible_reaction_cluster` | Implementa `append visible reaction cluster`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:439` | `mod_videotrack\local\analytics::add_user_intervals` | Implementa `add user intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics.php:475` | `mod_videotrack\local\analytics::add_interval_to_map` | Implementa `add interval to map`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:42` | `mod_videotrack\local\analytics_scope::matching_accessible_instances` | Implementa `matching accessible instances`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:144` | `mod_videotrack\local\analytics_scope::effective_groupmode` | Implementa `effective groupmode`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:175` | `mod_videotrack\local\analytics_scope::accessible_group_ids` | Implementa `accessible group ids`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:210` | `mod_videotrack\local\analytics_scope::technical_identity` | Implementa `technical identity`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:261` | `mod_videotrack\local\analytics_scope::normalise_external_url` | Implementa `normalise external url`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_scope.php:294` | `mod_videotrack\local\analytics_scope::get_instance_record` | Restituisce `instance record`. |
| `classes/local/analytics_table_export.php:35` | `mod_videotrack\local\analytics_table_export::enabled_formats` | Implementa `enabled formats`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_table_export.php:46` | `mod_videotrack\local\analytics_table_export::columns` | Implementa `columns`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_table_export.php:72` | `mod_videotrack\local\analytics_table_export::export_columns` | Esporta `columns`. |
| `classes/local/analytics_table_export.php:105` | `mod_videotrack\local\analytics_table_export::rows` | Implementa `rows`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/analytics_table_export.php:175` | `mod_videotrack\local\analytics_table_export::export_rows` | Esporta `rows`. |
| `classes/local/course_analytics.php:46` | `mod_videotrack\local\course_analytics::get_course_rows` | Restituisce `course rows`. |
| `classes/local/course_analytics.php:169` | `mod_videotrack\local\course_analytics::summarise_states` | Implementa `summarise states`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/course_analytics.php:220` | `mod_videotrack\local\course_analytics::median` | Implementa `median`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/course_analytics.php:240` | `mod_videotrack\local\course_analytics::largest_adjacent_drop` | Implementa `largest adjacent drop`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/course_analytics.php:274` | `mod_videotrack\local\course_analytics::learner_scope_sql` | Implementa `learner scope sql`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/course_analytics.php:308` | `mod_videotrack\local\course_analytics::load_states` | Carica `states`. |
| `classes/local/course_analytics.php:347` | `mod_videotrack\local\course_analytics::load_event_summary` | Carica `event summary`. |
| `classes/local/csv_export.php:78` | `mod_videotrack\local\csv_export::delimiter_options` | Implementa `delimiter options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:97` | `mod_videotrack\local\csv_export::delimiter` | Implementa `delimiter`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:121` | `mod_videotrack\local\csv_export::field_options` | Implementa `field options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:169` | `mod_videotrack\local\csv_export::form_field_options` | Implementa `form field options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:195` | `mod_videotrack\local\csv_export::site_default_fields` | Implementa `site default fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:210` | `mod_videotrack\local\csv_export::activity_fields` | Implementa `activity fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:225` | `mod_videotrack\local\csv_export::form_element_name` | Implementa `form element name`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:236` | `mod_videotrack\local\csv_export::process_form_fields` | Implementa `process form fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:256` | `mod_videotrack\local\csv_export::selected_user_fields` | Implementa `selected user fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:269` | `mod_videotrack\local\csv_export::load_users` | Carica `users`. |
| `classes/local/csv_export.php:303` | `mod_videotrack\local\csv_export::identity_headers` | Implementa `identity headers`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:332` | `mod_videotrack\local\csv_export::identity_values` | Implementa `identity values`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:372` | `mod_videotrack\local\csv_export::cluster_notes` | Implementa `cluster notes`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:422` | `mod_videotrack\local\csv_export::write_utf8_bom` | Implementa `write utf8 bom`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:434` | `mod_videotrack\local\csv_export::write_row` | Implementa `write row`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:459` | `mod_videotrack\local\csv_export::safe_value` | Implementa `safe value`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:476` | `mod_videotrack\local\csv_export::normalise_field_list` | Implementa `normalise field list`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:490` | `mod_videotrack\local\csv_export::field_label` | Implementa `field label`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:507` | `mod_videotrack\local\csv_export::field_value` | Implementa `field value`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/csv_export.php:543` | `mod_videotrack\local\csv_export::video_url` | Implementa `video url`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/forum_bridge.php:47` | `mod_videotrack\local\forum_bridge::resolve_destination` | Risoluzione di `destination`. |
| `classes/local/forum_bridge.php:99` | `mod_videotrack\local\forum_bridge::get_group_options` | Restituisce `group options`. |
| `classes/local/forum_bridge.php:135` | `mod_videotrack\local\forum_bridge::can_choose_subscription` | Implementa `can choose subscription`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/forum_bridge.php:154` | `mod_videotrack\local\forum_bridge::create_discussion` | Crea `discussion`. |
| `classes/local/integrity.php:78` | `mod_videotrack\local\integrity::normalise_random_pause_bounds` | Implementa `normalise random pause bounds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/integrity.php:100` | `mod_videotrack\local\integrity::random_pause_bounds` | Implementa `random pause bounds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/integrity.php:114` | `mod_videotrack\local\integrity::focus_loss_policy` | Implementa `focus loss policy`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/integrity.php:126` | `mod_videotrack\local\integrity::focus_loss_grace_seconds` | Implementa `focus loss grace seconds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/integrity.php:139` | `mod_videotrack\local\integrity::validate_event_type` | Valida `event type`. |
| `classes/local/integrity.php:152` | `mod_videotrack\local\integrity::label_string` | Implementa `label string`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/integrity.php:164` | `mod_videotrack\local\integrity::summarise` | Implementa `summarise`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/learner_scope.php:41` | `mod_videotrack\local\learner_scope::sql` | Costruisce lo scope SQL canonico per learner, iscrizione, capability report e gruppi. |
| `classes/local/learner_scope.php:91` | `mod_videotrack\local\learner_scope::prefix_named_params` | Prefissa i parametri SQL nominati per evitare collisioni negli scope learner multi-attività. |
| `classes/local/learner_scope.php:116` | `mod_videotrack\local\learner_scope::user_is_visible` | Verifica che un learner sia visibile al docente corrente. |
| `classes/local/privacy_manager.php:49` | `mod_videotrack\local\privacy_manager::retention_period_seconds` | Implementa `retention period seconds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:67` | `mod_videotrack\local\privacy_manager::anonymisation_salt` | Implementa `anonymisation salt`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:119` | `mod_videotrack\local\privacy_manager::anonymous_userid` | Implementa `anonymous userid`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:133` | `mod_videotrack\local\privacy_manager::anonymous_sessionid` | Implementa `anonymous sessionid`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:147` | `mod_videotrack\local\privacy_manager::delete_user_data_in_context` | Elimina `user data in context`. |
| `classes/local/privacy_manager.php:174` | `mod_videotrack\local\privacy_manager::delete_all_user_data_in_context` | Elimina `all user data in context`. |
| `classes/local/privacy_manager.php:209` | `mod_videotrack\local\privacy_manager::anonymise_user_in_context` | Implementa `anonymise user in context`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:225` | `mod_videotrack\local\privacy_manager::anonymise_all_users_in_context` | Implementa `anonymise all users in context`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:276` | `mod_videotrack\local\privacy_manager::anonymise_user_records` | Implementa `anonymise user records`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:352` | `mod_videotrack\local\privacy_manager::anonymise_expired_records` | Implementa `anonymise expired records`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:436` | `mod_videotrack\local\privacy_manager::anonymise_old_user_rows` | Implementa `anonymise old user rows`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:541` | `mod_videotrack\local\privacy_manager::anonymise_state_rows` | Implementa `anonymise state rows`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:562` | `mod_videotrack\local\privacy_manager::anonymise_one_state_row` | Implementa `anonymise one state row`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/privacy_manager.php:599` | `mod_videotrack\local\privacy_manager::merge_interval_json` | Implementa `merge interval json`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/teacher_analytics.php:36` | `mod_videotrack\local\teacher_analytics::accessible_courses` | Implementa `accessible courses`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/teacher_analytics.php:72` | `mod_videotrack\local\teacher_analytics::dashboard_rows` | Implementa `dashboard rows`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/teacher_analytics.php:119` | `mod_videotrack\local\teacher_analytics::activity_options` | Implementa `activity options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/teacher_analytics.php:141` | `mod_videotrack\local\teacher_analytics::group_options` | Implementa `group options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/teacher_analytics.php:165` | `mod_videotrack\local\teacher_analytics::period_bounds` | Implementa `period bounds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:44` | `mod_videotrack\local\timed_text::file_options` | Implementa `file options`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:59` | `mod_videotrack\local\timed_text::save_files` | Salva `files`. |
| `classes/local/timed_text.php:97` | `mod_videotrack\local\timed_text::transcript_tracks` | Implementa `transcript tracks`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:133` | `mod_videotrack\local\timed_text::chapter_source` | Implementa `chapter source`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:158` | `mod_videotrack\local\timed_text::language_from_filename` | Implementa `language from filename`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:176` | `mod_videotrack\local\timed_text::is_valid_vtt_content` | Determina `valid vtt content`. |
| `classes/local/timed_text.php:191` | `mod_videotrack\local\timed_text::area_files` | Implementa `area files`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:211` | `mod_videotrack\local\timed_text::file_url` | Implementa `file url`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/timed_text.php:229` | `mod_videotrack\local\timed_text::language_label` | Implementa `language label`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:56` | `mod_videotrack\local\tracker::current_state_snapshot` | Implementa `current state snapshot`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:75` | `mod_videotrack\local\tracker::normalise_interval` | Implementa `normalise interval`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:95` | `mod_videotrack\local\tracker::decode_intervals` | Implementa `decode intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:128` | `mod_videotrack\local\tracker::encode_intervals` | Implementa `encode intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:138` | `mod_videotrack\local\tracker::merge_intervals` | Implementa `merge intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:165` | `mod_videotrack\local\tracker::cap_intervals` | Implementa `cap intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:188` | `mod_videotrack\local\tracker::simplify_intervals` | Implementa `simplify intervals`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:211` | `mod_videotrack\local\tracker::covered_seconds` | Implementa `covered seconds`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:226` | `mod_videotrack\local\tracker::reaction_counts` | Implementa `reaction counts`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:268` | `mod_videotrack\local\tracker::invalidate_reactioncountscache` | Implementa `invalidate reactioncountscache`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:287` | `mod_videotrack\local\tracker::has_recent_playback` | Verifica se un timestamp è supportato da un heartbeat recente e validato; mantenuto per diagnostica e compatibilità. |
| `classes/local/tracker.php:385` | `mod_videotrack\local\tracker::has_watched_videotime` | Verifica la presenza di `watched videotime`. |
| `classes/local/tracker.php:475` | `mod_videotrack\local\tracker::completion_satisfied` | Implementa `completion satisfied`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:532` | `mod_videotrack\local\tracker::create_default_state` | Crea `default state`. |
| `classes/local/tracker.php:568` | `mod_videotrack\local\tracker::advance_server_credit_budget` | Avanza il budget cumulativo persistito basato sul tempo server e rifiuta i segmenti oltre credito. |
| `classes/local/tracker.php:609` | `mod_videotrack\local\tracker::forward_interval_allowed` | Applica la frontiera server già vista quando il seek in avanti è disabilitato. |
| `classes/local/tracker.php:641` | `mod_videotrack\local\tracker::update_state` | Aggiorna `state`. |
| `classes/local/tracker.php:791` | `mod_videotrack\local\tracker::update_moodle_completion_if_changed` | Aggiorna `moodle completion if changed`. |
| `classes/local/tracker.php:815` | `mod_videotrack\local\tracker::aggregate_segments` | Implementa `aggregate segments`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:854` | `mod_videotrack\local\tracker::rebuild_state_from_segments` | Implementa `rebuild state from segments`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/local/tracker.php:945` | `mod_videotrack\local\tracker::refresh_completion` | Implementa `refresh completion`; il contratto dettagliato è nel DocBlock sorgente. |
| `classes/privacy/provider.php:48` | `mod_videotrack\privacy\provider::format_interval_second` | Formatta `interval second`. |
| `classes/privacy/provider.php:59` | `mod_videotrack\privacy\provider::get_metadata` | Restituisce `metadata`. |
| `classes/privacy/provider.php:165` | `mod_videotrack\privacy\provider::get_contexts_for_userid` | Restituisce `contexts for userid`. |
| `classes/privacy/provider.php:204` | `mod_videotrack\privacy\provider::get_users_in_context` | Restituisce `users in context`. |
| `classes/privacy/provider.php:237` | `mod_videotrack\privacy\provider::export_user_data` | Esporta `user data`. |
| `classes/privacy/provider.php:590` | `mod_videotrack\privacy\provider::delete_data_for_all_users_in_context` | Elimina `data for all users in context`. |
| `classes/privacy/provider.php:603` | `mod_videotrack\privacy\provider::delete_data_for_user` | Elimina `data for user`. |
| `classes/privacy/provider.php:615` | `mod_videotrack\privacy\provider::delete_data_for_users` | Elimina `data for users`. |
| `classes/privacy/provider.php:628` | `mod_videotrack\privacy\provider::delete_records_for_users_in_context` | Elimina `records for users in context`. |
| `classes/task/cleanup_task.php:38` | `mod_videotrack\task\cleanup_task::get_name` | Restituisce `name`. |
| `classes/task/cleanup_task.php:45` | `mod_videotrack\task\cleanup_task::execute` | Esegue il contratto del servizio `execute`. |
| `db/upgrade.php:35` | `xmldb_videotrack_upgrade` | Implementa `xmldb videotrack upgrade`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:37` | `videotrack_supports` | Implementa `videotrack supports`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:69` | `videotrack_whitelist_record` | Implementa `videotrack whitelist record`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:94` | `videotrack_add_instance` | Implementa `videotrack add instance`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:141` | `videotrack_update_instance` | Implementa `videotrack update instance`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:186` | `videotrack_process_forum_fields` | Implementa `videotrack process forum fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:205` | `videotrack_process_acknowledgement_fields` | Implementa `videotrack process acknowledgement fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:232` | `videotrack_process_video_fields` | Implementa `videotrack process video fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:274` | `videotrack_process_playbackspeeds_field` | Implementa `videotrack process playbackspeeds field`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:303` | `videotrack_save_uploaded_video` | Implementa `videotrack save uploaded video`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:324` | `videotrack_delete_upload_source_files` | Implementa `videotrack delete upload source files`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:341` | `videotrack_get_upload_url` | Implementa `videotrack get upload url`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:369` | `videotrack_get_module_context_from_data` | Implementa `videotrack get module context from data`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:392` | `videotrack_save_poster_image` | Implementa `videotrack save poster image`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:425` | `videotrack_is_valid_reaction_icon_class` | Implementa `videotrack is valid reaction icon class`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:460` | `videotrack_save_reaction_definitions` | Implementa `videotrack save reaction definitions`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:603` | `videotrack_user_outline` | Implementa `videotrack user outline`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:632` | `videotrack_user_complete` | Implementa `videotrack user complete`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:673` | `videotrack_extend_settings_navigation` | Implementa `videotrack extend settings navigation`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:703` | `videotrack_extend_navigation_course` | Implementa `videotrack extend navigation course`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:736` | `videotrack_get_html5controls` | Implementa `videotrack get html5controls`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:752` | `videotrack_process_html5controls_field` | Implementa `videotrack process html5controls field`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:783` | `videotrack_process_player_behavior_fields` | Implementa `videotrack process player behavior fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:820` | `videotrack_get_player_width` | Implementa `videotrack get player width`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:835` | `videotrack_get_rewind_step` | Implementa `videotrack get rewind step`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:856` | `videotrack_get_fastforward_step` | Implementa `videotrack get fastforward step`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:877` | `videotrack_get_vtt_url` | Implementa `videotrack get vtt url`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:900` | `videotrack_process_captions_fields` | Implementa `videotrack process captions fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:943` | `videotrack_process_grade_fields` | Implementa `videotrack process grade fields`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:971` | `videotrack_grade_item_update` | Implementa `videotrack grade item update`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1029` | `videotrack_update_grades` | Implementa `videotrack update grades`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1041` | `videotrack_set_user_grade` | Implementa `videotrack set user grade`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1058` | `videotrack_get_user_grade` | Implementa `videotrack get user grade`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1083` | `videotrack_get_poster_url` | Implementa `videotrack get poster url`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1123` | `videotrack_delete_user_progress` | Implementa `videotrack delete user progress`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1155` | `videotrack_grade_item_delete` | Implementa `videotrack grade item delete`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1176` | `videotrack_delete_instance` | Implementa `videotrack delete instance`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1232` | `videotrack_get_coursemodule_info` | Implementa `videotrack get coursemodule info`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1253` | `videotrack_view` | Implementa `videotrack view`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1272` | `videotrack_get_completion_active_rule_descriptions` | Implementa `videotrack get completion active rule descriptions`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1310` | `videotrack_update_completion_for_user` | Implementa `videotrack update completion for user`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1326` | `videotrack_reset_course_userdata` | Implementa `videotrack reset course userdata`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1375` | `videotrack_reset_course_form_definition` | Implementa `videotrack reset course form definition`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1395` | `videotrack_reset_course_form_defaults` | Implementa `videotrack reset course form defaults`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1411` | `videotrack_resize_reaction_icon` | Implementa `videotrack resize reaction icon`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1506` | `videotrack_pluginfile` | Implementa `videotrack pluginfile`; il contratto dettagliato è nel DocBlock sorgente. |
| `lib.php:1615` | `videotrack_recalculate_all_states` | Implementa `videotrack recalculate all states`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:38` | `videotrack_get_config_int` | Implementa `videotrack get config int`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:57` | `videotrack_extract_videoid` | Implementa `videotrack extract videoid`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:108` | `videotrack_extract_vimeo_id` | Implementa `videotrack extract vimeo id`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:148` | `videotrack_get_playback_speeds` | Implementa `videotrack get playback speeds`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:196` | `videotrack_get_max_playback_rate` | Implementa `videotrack get max playback rate`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:209` | `videotrack_get_site_playback_speeds` | Implementa `videotrack get site playback speeds`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:228` | `videotrack_format_seconds` | Implementa `videotrack format seconds`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:246` | `videotrack_format_video_timestamp` | Implementa `videotrack format video timestamp`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:266` | `videotrack_parse_video_timestamp` | Implementa `videotrack parse video timestamp`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:306` | `videotrack_parse_report_timestamp` | Implementa `videotrack parse report timestamp`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:326` | `videotrack_build_required_reaction_notice` | Implementa `videotrack build required reaction notice`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:348` | `videotrack_get_reactions` | Implementa `videotrack get reactions`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:375` | `videotrack_reaction_icon_url` | Implementa `videotrack reaction icon url`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:416` | `videotrack_render_reaction_icon` | Implementa `videotrack render reaction icon`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:463` | `videotrack_get_fallback_reaction_emoji_catalog` | Implementa `videotrack get fallback reaction emoji catalog`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:497` | `videotrack_get_moodle_reaction_emoji_catalog` | Implementa `videotrack get moodle reaction emoji catalog`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:549` | `videotrack_get_reaction_icon_catalog` | Implementa `videotrack get reaction icon catalog`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:595` | `videotrack_get_reaction_icon_suggestions` | Implementa `videotrack get reaction icon suggestions`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:613` | `videotrack_reaction_icon_datalist` | Implementa `videotrack reaction icon datalist`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:640` | `videotrack_reaction_icon_picker` | Implementa `videotrack reaction icon picker`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:761` | `videotrack_get_all_presets` | Implementa `videotrack get all presets`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:775` | `videotrack_save_presets` | Implementa `videotrack save presets`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:785` | `videotrack_get_preset_select_options` | Implementa `videotrack get preset select options`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:800` | `videotrack_get_all_presets_for_js` | Implementa `videotrack get all presets for js`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:816` | `videotrack_require_preset_amd` | Implementa `videotrack require preset amd`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:832` | `videotrack_optional_iso_date_param` | Implementa `videotrack optional iso date param`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:843` | `videotrack_get_compatible_forum_types` | Implementa `videotrack get compatible forum types`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:853` | `videotrack_get_compatible_forums` | Implementa `videotrack get compatible forums`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:880` | `videotrack_get_compatible_forum_options` | Implementa `videotrack get compatible forum options`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:907` | `videotrack_is_compatible_forum` | Implementa `videotrack is compatible forum`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:923` | `videotrack_build_forum_subject` | Implementa `videotrack build forum subject`; il contratto dettagliato è nel DocBlock sorgente. |
| `locallib.php:952` | `videotrack_build_replay_url` | Implementa `videotrack build replay url`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:46` | `mod_videotrack_mod_form::definition` | Implementa `definition`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1086` | `mod_videotrack_mod_form::apply_default_section_expansion` | Implementa `apply default section expansion`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1123` | `mod_videotrack_mod_form::require_filepicker_accept_filter` | Implementa `require filepicker accept filter`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1169` | `mod_videotrack_mod_form::add_reaction_elements` | Implementa `add reaction elements`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1348` | `mod_videotrack_mod_form::get_reaction_repeat_count` | Restituisce `reaction repeat count`. |
| `mod_form.php:1375` | `mod_videotrack_mod_form::add_completion_rules` | Implementa `add completion rules`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1418` | `mod_videotrack_mod_form::completion_rule_enabled` | Implementa `completion rule enabled`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1432` | `mod_videotrack_mod_form::data_preprocessing` | Implementa `data preprocessing`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1682` | `mod_videotrack_mod_form::draft_area_contains_only_reaction_images` | Implementa `draft area contains only reaction images`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1718` | `mod_videotrack_mod_form::draft_area_contains_only_vtt` | Implementa `draft area contains only vtt`; il contratto dettagliato è nel DocBlock sorgente. |
| `mod_form.php:1750` | `mod_videotrack_mod_form::validation` | Implementa `validation`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:36` | `videotrack_report_user_label` | Implementa `videotrack report user label`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:54` | `videotrack_report_date_to_timestamp` | Implementa `videotrack report date to timestamp`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:76` | `videotrack_report_optional_time_param` | Implementa `videotrack report optional time param`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:128` | `videotrack_report_duration_filter` | Implementa `videotrack report duration filter`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:191` | `videotrack_report_analytics_scope_condition` | Implementa `videotrack report analytics scope condition`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:258` | `videotrack_report_acknowledgement_scope_condition` | Implementa `videotrack report acknowledgement scope condition`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:326` | `videotrack_report_tabs` | Implementa `videotrack report tabs`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:369` | `videotrack_report_analytics_interval` | Implementa `videotrack report analytics interval`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:383` | `videotrack_report_render_analytics_heatmap` | Implementa `videotrack report render analytics heatmap`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:508` | `videotrack_report_render_analytics_methodology` | Implementa `videotrack report render analytics methodology`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:568` | `videotrack_report_render_privacy_alert` | Implementa `videotrack report render privacy alert`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:595` | `videotrack_report_render_heatmap_legend` | Implementa `videotrack report render heatmap legend`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:640` | `videotrack_report_render_analytics_download` | Implementa `videotrack report render analytics download`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:697` | `videotrack_report_render_reaction_clusters` | Implementa `videotrack report render reaction clusters`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:732` | `videotrack_report_render_reaction_summary` | Implementa `videotrack report render reaction summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:755` | `videotrack_report_render_bookmark_summary` | Implementa `videotrack report render bookmark summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:821` | `videotrack_report_render_acknowledgement_summary` | Implementa `videotrack report render acknowledgement summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:930` | `videotrack_report_render_integrity_summary` | Implementa `videotrack report render integrity summary`; il contratto dettagliato è nel DocBlock sorgente. |
| `report.php:1021` | `videotrack_report_render_analytics_retention` | Implementa `videotrack report render analytics retention`; il contratto dettagliato è nel DocBlock sorgente. |
| `reports_course.php:35` | `videotrack_course_report_count_cell` | Implementa `videotrack course report count cell`; il contratto dettagliato è nel DocBlock sorgente. |
| `reports_course.php:58` | `videotrack_course_report_percentage_cell` | Implementa `videotrack course report percentage cell`; il contratto dettagliato è nel DocBlock sorgente. |
| `reports_course.php:99` | `videotrack_course_report_drop_cell` | Implementa `videotrack course report drop cell`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/acknowledgement_test.php:36` | `mod_videotrack\acknowledgement_test::test_statement_hash_versions_the_statement_content` | Verifica tramite PHPUnit `statement hash versions the statement content`. |
| `tests/acknowledgement_test.php:58` | `mod_videotrack\acknowledgement_test::test_video_end_requirement_uses_persisted_intervals` | Verifica tramite PHPUnit `video end requirement uses persisted intervals`. |
| `tests/acknowledgement_test.php:83` | `mod_videotrack\acknowledgement_test::test_progress_snapshot_uses_unique_coverage` | Verifica tramite PHPUnit `progress snapshot uses unique coverage`. |
| `tests/acknowledgement_test.php:101` | `mod_videotrack\acknowledgement_test::test_video_end_requires_teacher_configured_duration` | Una durata storica lato stato/client non può sbloccare la presa visione a fine video senza durata verificata configurata dal docente. |
| `tests/acknowledgement_test.php:126` | `mod_videotrack\acknowledgement_test::test_enabled_state_requires_nonempty_visible_text` | Verifica tramite PHPUnit `enabled state requires nonempty visible text`. |
| `tests/acknowledgement_test.php:146` | `mod_videotrack\acknowledgement_test::test_analytics_summary_preserves_legacy_and_privacy_rules` | Verifica tramite PHPUnit `analytics summary preserves legacy and privacy rules`. |
| `tests/admin_settings_test.php:40` | `mod_videotrack\admin_settings_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/admin_settings_test.php:50` | `mod_videotrack\admin_settings_test::test_nonnegative_int_validation_accepts_zero_and_rejects_invalid_values` | Verifica tramite PHPUnit `nonnegative int validation accepts zero and rejects invalid values`. |
| `tests/admin_settings_test.php:63` | `mod_videotrack\admin_settings_test::test_int_range_validation_enforces_configured_bounds` | Verifica tramite PHPUnit `int range validation enforces configured bounds`. |
| `tests/admin_settings_test.php:76` | `mod_videotrack\admin_settings_test::test_unlimited_retention_requires_explicit_confirmation` | Verifica tramite PHPUnit `unlimited retention requires explicit confirmation`. |
| `tests/analytics_scope_test.php:36` | `mod_videotrack\analytics_scope_test::test_provider_identity_uses_exact_video_id` | Verifica tramite PHPUnit `provider identity uses exact video id`. |
| `tests/analytics_scope_test.php:55` | `mod_videotrack\analytics_scope_test::test_external_url_identity_is_normalised` | Verifica tramite PHPUnit `external url identity is normalised`. |
| `tests/analytics_scope_test.php:69` | `mod_videotrack\analytics_scope_test::test_effective_groupmode_satisfies_moodle_course_module_contract` | Verifica tramite PHPUnit `effective groupmode satisfies moodle course module contract`. |
| `tests/analytics_table_export_test.php:34` | `mod_videotrack\analytics_table_export_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/analytics_table_export_test.php:42` | `mod_videotrack\analytics_table_export_test::test_rows_match_accessible_table_privacy_rules` | Verifica tramite PHPUnit `rows match accessible table privacy rules`. |
| `tests/analytics_table_export_test.php:80` | `mod_videotrack\analytics_table_export_test::test_rows_mark_unavailable_replay_metrics` | Verifica tramite PHPUnit `rows mark unavailable replay metrics`. |
| `tests/analytics_table_export_test.php:99` | `mod_videotrack\analytics_table_export_test::test_export_rows_include_acknowledgement_summary` | Verifica tramite PHPUnit `export rows include acknowledgement summary`. |
| `tests/analytics_test.php:36` | `mod_videotrack\analytics_test::test_bin_size_is_normalised_for_duration` | Verifica tramite PHPUnit `bin size is normalised for duration`. |
| `tests/analytics_test.php:45` | `mod_videotrack\analytics_test::test_build_separates_unique_and_repeated_viewing` | Verifica tramite PHPUnit `build separates unique and repeated viewing`. |
| `tests/analytics_test.php:72` | `mod_videotrack\analytics_test::test_privacy_threshold_masks_small_values` | Verifica tramite PHPUnit `privacy threshold masks small values`. |
| `tests/analytics_test.php:119` | `mod_videotrack\analytics_test::test_privacy_threshold_keeps_zero_intervals_visible` | Verifica tramite PHPUnit `privacy threshold keeps zero intervals visible`. |
| `tests/analytics_test.php:146` | `mod_videotrack\analytics_test::test_reaction_cluster_limit_is_reported` | Verifica tramite PHPUnit `reaction cluster limit is reported`. |
| `tests/analytics_test.php:173` | `mod_videotrack\analytics_test::test_reaction_clusters_apply_student_threshold` | Verifica tramite PHPUnit `reaction clusters apply student threshold`. |
| `tests/analytics_test.php:197` | `mod_videotrack\analytics_test::test_reaction_clusters_use_stable_reaction_keys` | Verifica tramite PHPUnit `reaction clusters use stable reaction keys`. |
| `tests/analytics_test.php:250` | `mod_videotrack\analytics_test::test_reaction_privacy_is_independent_from_viewing_privacy` | Verifica tramite PHPUnit `reaction privacy is independent from viewing privacy`. |
| `tests/analytics_test.php:278` | `mod_videotrack\analytics_test::test_group_scope_restriction_uses_effective_activity_mode` | Verifica tramite PHPUnit `group scope restriction uses effective activity mode`. |
| `tests/analytics_test.php:288` | `mod_videotrack\analytics_test::test_reaction_summary_masks_small_populations` | Verifica tramite PHPUnit `reaction summary masks small populations`. |
| `tests/analytics_test.php:309` | `mod_videotrack\analytics_test::test_build_from_states_recovers_unique_viewers` | Verifica tramite PHPUnit `build from states recovers unique viewers`. |
| `tests/analytics_test.php:329` | `mod_videotrack\analytics_test::test_resolve_duration_uses_best_persisted_source` | Verifica tramite PHPUnit `resolve duration uses best persisted source`. |
| `tests/course_analytics_test.php:36` | `mod_videotrack\course_analytics_test::test_median_handles_common_dataset_shapes` | Verifica tramite PHPUnit `median handles common dataset shapes`. |
| `tests/course_analytics_test.php:45` | `mod_videotrack\course_analytics_test::test_state_summary_reuses_timeline_analytics` | Verifica tramite PHPUnit `state summary reuses timeline analytics`. |
| `tests/course_analytics_test.php:69` | `mod_videotrack\course_analytics_test::test_state_summary_masks_small_activity_population` | Verifica tramite PHPUnit `state summary masks small activity population`. |
| `tests/course_analytics_test.php:90` | `mod_videotrack\course_analytics_test::test_state_summary_masks_small_completion_subgroups` | Verifica tramite PHPUnit `state summary masks small completion subgroups`. |
| `tests/course_analytics_test.php:111` | `mod_videotrack\course_analytics_test::test_largest_drop_ignores_suppressed_bins` | Verifica tramite PHPUnit `largest drop ignores suppressed bins`. |
| `tests/course_analytics_test.php:133` | `mod_videotrack\course_analytics_test::state` | Implementa `state`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/csv_export_test.php:37` | `mod_videotrack\csv_export_test::test_delimiter_resolution` | Verifica tramite PHPUnit `delimiter resolution`. |
| `tests/csv_export_test.php:60` | `mod_videotrack\csv_export_test::test_process_form_fields` | Verifica tramite PHPUnit `process form fields`. |
| `tests/csv_export_test.php:77` | `mod_videotrack\csv_export_test::test_field_options_include_video_link` | Verifica tramite PHPUnit `field options include video link`. |
| `tests/csv_export_test.php:86` | `mod_videotrack\csv_export_test::test_identity_columns_split_lastname_and_firstname` | Verifica tramite PHPUnit `identity columns split lastname and firstname`. |
| `tests/csv_export_test.php:101` | `mod_videotrack\csv_export_test::test_cluster_notes_concatenates_comments_and_counts_students` | Verifica tramite PHPUnit `cluster notes concatenates comments and counts students`. |
| `tests/csv_export_test.php:123` | `mod_videotrack\csv_export_test::test_safe_value_blocks_formula_injection` | Verifica tramite PHPUnit `safe value blocks formula injection`. |
| `tests/csv_export_test.php:133` | `mod_videotrack\csv_export_test::test_write_utf8_bom` | Verifica tramite PHPUnit `write utf8 bom`. |
| `tests/csv_export_test.php:145` | `mod_videotrack\csv_export_test::test_write_row_supports_section_sign_delimiter` | Verifica tramite PHPUnit `write row supports section sign delimiter`. |
| `tests/forum_bridge_test.php:37` | `mod_videotrack\forum_bridge_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/forum_bridge_test.php:45` | `mod_videotrack\forum_bridge_test::test_disabled_integration_is_rejected` | Verifica tramite PHPUnit `disabled integration is rejected`. |
| `tests/forum_bridge_test.php:59` | `mod_videotrack\forum_bridge_test::test_enrolled_student_can_resolve_compatible_forum` | Verifica tramite PHPUnit `enrolled student can resolve compatible forum`. |
| `tests/integrity_test.php:36` | `mod_videotrack\integrity_test::test_event_type_validation_is_allowlist_based` | Verifica tramite PHPUnit `event type validation is allowlist based`. |
| `tests/integrity_test.php:48` | `mod_videotrack\integrity_test::test_summary_applies_distinct_user_privacy_threshold` | Verifica tramite PHPUnit `summary applies distinct user privacy threshold`. |
| `tests/integrity_test.php:66` | `mod_videotrack\integrity_test::test_random_pause_bounds_are_configurable_and_normalised` | Verifica tramite PHPUnit `random pause bounds are configurable and normalised`. |
| `tests/integrity_test.php:84` | `mod_videotrack\integrity_test::test_focus_policy_defaults_and_strict_override` | Verifica tramite PHPUnit `focus policy defaults and strict override`. |
| `tests/lib_test.php:37` | `mod_videotrack\lib_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/lib_test.php:47` | `mod_videotrack\lib_test::test_supports_expected_core_features` | Verifica tramite PHPUnit `supports expected core features`. |
| `tests/lib_test.php:61` | `mod_videotrack\lib_test::test_groups_are_explicitly_not_supported` | Verifica tramite PHPUnit `groups are explicitly not supported`. |
| `tests/lib_test.php:71` | `mod_videotrack\lib_test::test_activity_chooser_metadata_is_reported` | Verifica tramite PHPUnit `activity chooser metadata is reported`. |
| `tests/lib_test.php:81` | `mod_videotrack\lib_test::test_unknown_feature_returns_null` | Verifica tramite PHPUnit `unknown feature returns null`. |
| `tests/lib_test.php:90` | `mod_videotrack\lib_test::test_player_behavior_fields_normalise_bookmark_setting` | Verifica tramite PHPUnit `player behavior fields normalise bookmark setting`. |
| `tests/lib_test.php:115` | `mod_videotrack\lib_test::test_caption_normalisation_preserves_provider_timed_text_settings` | Verifica tramite PHPUnit `caption normalisation preserves provider timed text settings`. |
| `tests/locallib_test.php:35` | `mod_videotrack\locallib_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/locallib_test.php:45` | `mod_videotrack\locallib_test::test_extract_videoid_accepts_supported_youtube_urls` | Verifica tramite PHPUnit `extract videoid accepts supported youtube urls`. |
| `tests/locallib_test.php:62` | `mod_videotrack\locallib_test::test_extract_vimeo_id_accepts_supported_vimeo_urls` | Verifica tramite PHPUnit `extract vimeo id accepts supported vimeo urls`. |
| `tests/locallib_test.php:78` | `mod_videotrack\locallib_test::test_format_seconds_clamps_and_formats_duration` | Verifica tramite PHPUnit `format seconds clamps and formats duration`. |
| `tests/locallib_test.php:90` | `mod_videotrack\locallib_test::test_format_video_timestamp_uses_total_duration` | Verifica tramite PHPUnit `format video timestamp uses total duration`. |
| `tests/locallib_test.php:101` | `mod_videotrack\locallib_test::test_parse_video_timestamp_accepts_supported_formats` | Verifica tramite PHPUnit `parse video timestamp accepts supported formats`. |
| `tests/locallib_test.php:116` | `mod_videotrack\locallib_test::test_parse_report_timestamp_requires_colon_format` | Verifica tramite PHPUnit `parse report timestamp requires colon format`. |
| `tests/locallib_test.php:129` | `mod_videotrack\locallib_test::test_get_config_int_preserves_zero_and_clamps_values` | Verifica tramite PHPUnit `get config int preserves zero and clamps values`. |
| `tests/locallib_test.php:147` | `mod_videotrack\locallib_test::test_get_config_int_rejects_invalid_bounds` | Verifica tramite PHPUnit `get config int rejects invalid bounds`. |
| `tests/locallib_test.php:157` | `mod_videotrack\locallib_test::test_get_playback_speeds_filters_and_applies_site_cap` | Verifica tramite PHPUnit `get playback speeds filters and applies site cap`. |
| `tests/locallib_test.php:178` | `mod_videotrack\locallib_test::test_compatible_forum_types_exclude_single_use_forums` | Verifica tramite PHPUnit `compatible forum types exclude single use forums`. |
| `tests/locallib_test.php:187` | `mod_videotrack\locallib_test::test_build_replay_url_applies_window_and_duration` | Verifica tramite PHPUnit `build replay url applies window and duration`. |
| `tests/locallib_test.php:201` | `mod_videotrack\locallib_test::test_build_forum_subject_replaces_supported_placeholders` | Verifica tramite PHPUnit `build forum subject replaces supported placeholders`. |
| `tests/locallib_test.php:215` | `mod_videotrack\locallib_test::test_build_forum_subject_uses_default_template` | Verifica tramite PHPUnit `build forum subject uses default template`. |
| `tests/save_bookmark_test.php:37` | `mod_videotrack\save_bookmark_test::test_execute_parameters_uses_supported_moodle_parameter_types` | Verifica tramite PHPUnit `execute parameters uses supported moodle parameter types`. |
| `tests/save_integrity_event_test.php:37` | `mod_videotrack\save_integrity_event_test::test_execute_parameters_uses_supported_moodle_parameter_types` | Verifica tramite PHPUnit `execute parameters uses supported moodle parameter types`. |
| `tests/save_note_test.php:37` | `mod_videotrack\save_note_test::test_execute_parameters_uses_supported_moodle_parameter_types` | Verifica tramite PHPUnit `execute parameters uses supported moodle parameter types`. |
| `tests/teacher_analytics_test.php:34` | `mod_videotrack\teacher_analytics_test::test_period_bounds` | Verifica tramite PHPUnit `period bounds`. |
| `tests/timed_text_test.php:35` | `mod_videotrack\timed_text_test::test_language_from_filename_accepts_bcp47_like_names` | Verifica tramite PHPUnit `language from filename accepts bcp47 like names`. |
| `tests/timed_text_test.php:47` | `mod_videotrack\timed_text_test::test_is_valid_vtt_content_checks_signature_and_size` | Verifica tramite PHPUnit `is valid vtt content checks signature and size`. |
| `tests/timed_text_test.php:59` | `mod_videotrack\timed_text_test::test_file_options_enforce_vtt_limits` | Verifica tramite PHPUnit `file options enforce vtt limits`. |
| `tests/tracker_test.php:40` | `mod_videotrack\tracker_test::setUp` | Implementa `setUp`; il contratto dettagliato è nel DocBlock sorgente. |
| `tests/tracker_test.php:48` | `mod_videotrack\tracker_test::test_normalise_interval_clamps_and_rejects_empty_ranges` | Verifica tramite PHPUnit `normalise interval clamps and rejects empty ranges`. |
| `tests/tracker_test.php:59` | `mod_videotrack\tracker_test::test_decode_intervals_filters_invalid_ranges` | Verifica tramite PHPUnit `decode intervals filters invalid ranges`. |
| `tests/tracker_test.php:76` | `mod_videotrack\tracker_test::test_merge_intervals_and_covered_seconds_are_deterministic` | Verifica tramite PHPUnit `merge intervals and covered seconds are deterministic`. |
| `tests/tracker_test.php:91` | `mod_videotrack\tracker_test::test_simplify_intervals_never_overestimates_coverage` | Verifica tramite PHPUnit `simplify intervals never overestimates coverage`. |
| `tests/tracker_test.php:108` | `mod_videotrack\tracker_test::test_aggregate_segments_rebuilds_state_values` | Verifica tramite PHPUnit `aggregate segments rebuilds state values`. |
| `tests/tracker_test.php:146` | `mod_videotrack\tracker_test::test_cap_intervals_limits_count_and_preserves_order` | Verifica tramite PHPUnit `cap intervals limits count and preserves order`. |
| `tests/tracker_test.php:165` | `mod_videotrack\tracker_test::test_server_credit_budget_is_cumulative` | Verifica che aumentare la frequenza delle richieste non rigeneri il credito cumulativo server. |
| `tests/tracker_test.php:199` | `mod_videotrack\tracker_test::test_forward_interval_guard_rejects_unwatched_jump` | Verifica che il server rifiuti salti in avanti quando il seek in avanti è disabilitato. |
| `tests/tracker_test.php:212` | `mod_videotrack\tracker_test::test_watched_time_validation_ignores_unvalidated_raw_segments` | Funzione o metodo nominato; vedere DocBlock sorgente e chiamanti per il contratto dettagliato. |
| `tests/tracker_test.php:240` | `mod_videotrack\tracker_test::test_watched_time_validation_uses_aggregate_state_fallback` | Verifica tramite PHPUnit `watched time validation uses aggregate state fallback`. |

## Callable AMD nominati

| File:riga | Callable | Responsabilità |
|---|---|---|
| `amd/src/core/adapter.js:74` | `normaliseProviderType` | Callable `normaliseProviderType` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:85` | `isKnownProviderType` | Callable `isKnownProviderType` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:96` | `getCapabilityDefinition` | Callable `getCapabilityDefinition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:105` | `getCapabilityMethods` | Callable `getCapabilityMethods` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:110` | `getCapabilityProperties` | Callable `getCapabilityProperties` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:136` | `isAvailable` | Callable `isAvailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:152` | `can` | Callable `can` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:170` | `hasCapability` | Callable `hasCapability` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:186` | `getCapabilities` | Callable `getCapabilities` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:204` | `canCurrentTime` | Callable `canCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:208` | `canDuration` | Callable `canDuration` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:212` | `canPlay` | Callable `canPlay` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:216` | `canPause` | Callable `canPause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:220` | `canSeek` | Callable `canSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:224` | `canPlaybackRate` | Callable `canPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:228` | `canVolume` | Callable `canVolume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:232` | `canMute` | Callable `canMute` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:236` | `canPaused` | Callable `canPaused` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:240` | `canEnded` | Callable `canEnded` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:251` | `normaliseTime` | Callable `normaliseTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:272` | `resolveSkipTarget` | Callable `resolveSkipTarget` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:302` | `getCurrentTime` | Callable `getCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:329` | `getDuration` | Callable `getDuration` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:358` | `normaliseVolume` | Callable `normaliseVolume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:376` | `getVolume` | Callable `getVolume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:404` | `setVolume` | Callable `setVolume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:428` | `isMuted` | Callable `isMuted` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:456` | `setMuted` | Callable `setMuted` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:479` | `getPlaybackRate` | Callable `getPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:510` | `setPlaybackRate` | Callable `setPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:537` | `isPaused` | Callable `isPaused` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:566` | `isEnded` | Callable `isEnded` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:611` | `run` | Callable `run` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:612` | `logFailure` | Callable `logFailure` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:644` | `play` | Callable `play` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:656` | `pause` | Callable `pause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/adapter.js:669` | `seek` | Callable `seek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api.js:61` | `call` | Callable `call` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api.js:87` | `attemptRequest` | Callable `attemptRequest` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api.js:138` | `buildSegmentArgs` | Callable `buildSegmentArgs` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api.js:174` | `saveSegment` | Callable `saveSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:29` | `getNetworkState` | Callable `getNetworkState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:41` | `isBrowserOffline` | Callable `isBrowserOffline` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:51` | `getErrorCode` | Callable `getErrorCode` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:67` | `getErrorMessage` | Callable `getErrorMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:83` | `getErrorStatus` | Callable `getErrorStatus` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:99` | `classifyAjaxError` | Callable `classifyAjaxError` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:145` | `normaliseAjaxError` | Callable `normaliseAjaxError` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/error.js:171` | `isTransientAjaxError` | Callable `isTransientAjaxError` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/retry.js:24` | `getRetryJitter` | Callable `getRetryJitter` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/retry.js:52` | `normalizeRetryCount` | Callable `normalizeRetryCount` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/retry.js:67` | `delay` | Callable `delay` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/scope.js:18` | `createRequestScope` | Callable `createRequestScope` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/scope.js:28` | `nextToken` | Callable `nextToken` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/scope.js:43` | `isCurrent` | Callable `isCurrent` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/scope.js:55` | `resolveIfCurrent` | Callable `resolveIfCurrent` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/transport.js:27` | `withTimeout` | Callable `withTimeout` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/transport.js:60` | `send` | Callable `send` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:53` | `normaliseMethodName` | Callable `normaliseMethodName` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:71` | `createValidationError` | Callable `createValidationError` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:85` | `isPlainObject` | Callable `isPlainObject` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:95` | `getUtf8Length` | Callable `getUtf8Length` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:110` | `isSafeArgValue` | Callable `isSafeArgValue` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:148` | `hasNonNegativeNumber` | Callable `hasNonNegativeNumber` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/api/validator.js:165` | `validateArgs` | Callable `validateArgs` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/beacon.js:31` | `sendSegment` | Callable `sendSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:38` | `submitForm` | Callable `submitForm` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:61` | `getFocusableElement` | Callable `getFocusableElement` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:74` | `restoreFocus` | Callable `restoreFocus` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:93` | `focusModal` | Callable `focusModal` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:115` | `normaliseText` | Callable `normaliseText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:126` | `resolveString` | Callable `resolveString` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:151` | `showInlineFallback` | Callable `showInlineFallback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:177` | `showModalConfirm` | Callable `showModalConfirm` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/confirm.js:240` | `attachToForms` | Callable `attachToForms` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/debug.js:21` | `log` | Callable `log` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:21` | `create` | Callable `create` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:35` | `normaliseEventName` | Callable `normaliseEventName` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:51` | `on` | Callable `on` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:57` | `removeHandler` | Callable `removeHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:85` | `off` | Callable `off` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:103` | `emit` | Callable `emit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:135` | `once` | Callable `once` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:136` | `unsubscribe` | Callable `unsubscribe` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:137` | `wrapped` | Callable `wrapped` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:151` | `count` | Callable `count` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:164` | `clear` | Callable `clear` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:176` | `ensure` | Callable `ensure` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/events.js:194` | `emit` | Callable `emit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:29` | `uuid` | Callable `uuid` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:43` | `getIntervalBarColor` | Callable `getIntervalBarColor` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:54` | `parseIntervals` | Callable `parseIntervals` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:65` | `updateIntervalBar` | Callable `updateIntervalBar` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:81` | `showResumeNotice` | Callable `showResumeNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:90` | `configureStatus` | Callable `configureStatus` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:102` | `showStatusMessage` | Callable `showStatusMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:114` | `showErrorStatusMessage` | Callable `showErrorStatusMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:124` | `announceStatusMessage` | Callable `announceStatusMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:139` | `onFirstPlay` | Callable `onFirstPlay` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:155` | `installNoteHandler` | Callable `installNoteHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:164` | `installBookmarkHandler` | Callable `installBookmarkHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:177` | `removePoster` | Callable `removePoster` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player.js:187` | `getPlayerShell` | Callable `getPlayerShell` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/acknowledgement.js:17` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:10` | `resolveBookmarkTime` | Callable `resolveBookmarkTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:19` | `appendRow` | Callable `appendRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:77` | `installHandler` | Callable `installHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:101` | `ajax` | Callable `ajax` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:109` | `restore` | Callable `restore` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:117` | `saveHandler` | Callable `saveHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:172` | `listHandler` | Callable `listHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/bookmarks.js:219` | `cleanup` | Callable `cleanup` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:26` | `randomInteger` | Callable `randomInteger` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:43` | `create` | Callable `create` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:65` | `currentTime` | Callable `currentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:85` | `record` | Callable `record` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:107` | `clearRandomTimer` | Callable `clearRandomTimer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:115` | `clearBlurTimer` | Callable `clearBlurTimer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:127` | `showMessage` | Callable `showMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:139` | `pausePlayback` | Callable `pausePlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:157` | `scheduleRandomPause` | Callable `scheduleRandomPause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:181` | `noteAction` | Callable `noteAction` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:194` | `setPlaying` | Callable `setPlaying` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:207` | `noteProgress` | Callable `noteProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:229` | `applyPictureInPicturePolicy` | Callable `applyPictureInPicturePolicy` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:260` | `onVisibilityChange` | Callable `onVisibilityChange` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:272` | `onWindowFocus` | Callable `onWindowFocus` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:276` | `onWindowBlur` | Callable `onWindowBlur` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:305` | `onShellInteraction` | Callable `onShellInteraction` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/focus_guard.js:341` | `destroy` | Callable `destroy` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/forum.js:15` | `install` | Callable `install` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/forum.js:24` | `setBusy` | Callable `setBusy` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/forum.js:28` | `handler` | Callable `handler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/forum.js:56` | `cleanup` | Callable `cleanup` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/intervalbar.js:22` | `getColor` | Callable `getColor` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/intervalbar.js:33` | `parse` | Callable `parse` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/intervalbar.js:55` | `updateTextAlternative` | Callable `updateTextAlternative` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/intervalbar.js:88` | `drawIntervals` | Callable `drawIntervals` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/intervalbar.js:119` | `update` | Callable `update` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:28` | `getRemainingChars` | Callable `getRemainingChars` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:44` | `updateCharCounter` | Callable `updateCharCounter` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:63` | `setButtonState` | Callable `setButtonState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:83` | `resolveNoteTime` | Callable `resolveNoteTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:105` | `installHandler` | Callable `installHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:130` | `ajax` | Callable `ajax` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:139` | `restoreSaveButtonState` | Callable `restoreSaveButtonState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:150` | `showResponseWarnings` | Callable `showResponseWarnings` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:161` | `announceLimitedNotes` | Callable `announceLimitedNotes` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:169` | `announceCharThreshold` | Callable `announceCharThreshold` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:200` | `setLocalButtonState` | Callable `setLocalButtonState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:206` | `playStateHandler` | Callable `playStateHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:209` | `cleanupNoteHandler` | Callable `cleanupNoteHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:225` | `saveClickHandler` | Callable `saveClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:295` | `noteListClickHandler` | Callable `noteListClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes.js:337` | `textareaInputHandler` | Callable `textareaInputHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes/row.js:19` | `appendRow` | Callable `appendRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes/toggle.js:19` | `install` | Callable `install` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes/toggle.js:34` | `setCollapsed` | Callable `setCollapsed` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes/toggle.js:45` | `toggleClickHandler` | Callable `toggleClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/notes/toggle.js:49` | `cleanupToggleHandler` | Callable `cleanupToggleHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/poster.js:16` | `onFirstPlay` | Callable `onFirstPlay` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/poster.js:30` | `remove` | Callable `remove` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/progress.js:25` | `clampSegmentTimes` | Callable `clampSegmentTimes` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/progress.js:35` | `normaliseSaveReason` | Callable `normaliseSaveReason` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/progress.js:49` | `saveCurrentProgress` | Callable `saveCurrentProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/progress.js:64` | `sendBeaconSegment` | Callable `sendBeaconSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/reactions.js:21` | `announceAvailability` | Callable `announceAvailability` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/reactions.js:31` | `announceUnavailable` | Callable `announceUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/resume.js:16` | `showNotice` | Callable `showNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/status.js:21` | `getShell` | Callable `getShell` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/status.js:34` | `configure` | Callable `configure` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/status.js:46` | `showMessage` | Callable `showMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/status.js:62` | `showErrorMessage` | Callable `showErrorMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/status.js:80` | `announce` | Callable `announce` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:21` | `stripCueMarkup` | Callable `stripCueMarkup` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:41` | `vttTime` | Callable `vttTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:64` | `parseVtt` | Callable `parseVtt` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:109` | `countLabel` | Callable `countLabel` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:119` | `create` | Callable `create` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:145` | `announce` | Callable `announce` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:166` | `navigate` | Callable `navigate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:184` | `preferredTrackIndex` | Callable `preferredTrackIndex` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:205` | `buildTranscriptControls` | Callable `buildTranscriptControls` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:257` | `filterTranscript` | Callable `filterTranscript` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:277` | `renderTranscript` | Callable `renderTranscript` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:314` | `showTranscriptUnavailable` | Callable `showTranscriptUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:331` | `loadTranscript` | Callable `loadTranscript` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:356` | `renderChapters` | Callable `renderChapters` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:389` | `showChaptersUnavailable` | Callable `showChaptersUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:404` | `loadChapters` | Callable `loadChapters` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:433` | `update` | Callable `update` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:487` | `poll` | Callable `poll` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/player/timed_text.js:504` | `destroy` | Callable `destroy` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:21` | `pickNumber` | Callable `pickNumber` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:44` | `formatPercent` | Callable `formatPercent` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:54` | `updatePercentText` | Callable `updatePercentText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:66` | `updateFallbackProgress` | Callable `updateFallbackProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:93` | `updateProgress` | Callable `updateProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:139` | `buildLiveSnapshot` | Callable `buildLiveSnapshot` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/progress.js:195` | `updateLiveProgress` | Callable `updateLiveProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:27` | `createState` | Callable `createState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:50` | `getStatusRegion` | Callable `getStatusRegion` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:69` | `announceStatus` | Callable `announceStatus` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:101` | `announceAvailability` | Callable `announceAvailability` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:148` | `announceUnavailable` | Callable `announceUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/reactions.js:189` | `setButtons` | Callable `setButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/segment.js:25` | `finiteSeconds` | Callable `finiteSeconds` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/segment.js:36` | `normaliseSaveReason` | Callable `normaliseSaveReason` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/segment.js:52` | `clampSegmentTimes` | Callable `clampSegmentTimes` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/segment.js:75` | `calculateInteractionEnd` | Callable `calculateInteractionEnd` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/session.js:22` | `uuid` | Callable `uuid` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/state.js:21` | `create` | Callable `create` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:41` | `getState` | Callable `getState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:58` | `clampTimeout` | Callable `clampTimeout` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:71` | `configure` | Callable `configure` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:89` | `normaliseMessage` | Callable `normaliseMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:104` | `getLiveRegion` | Callable `getLiveRegion` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:133` | `remove` | Callable `remove` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:148` | `getContainer` | Callable `getContainer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:158` | `normaliseDismissLabel` | Callable `normaliseDismissLabel` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:170` | `normaliseTimeout` | Callable `normaliseTimeout` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:197` | `announce` | Callable `announce` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:228` | `clear` | Callable `clear` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/status.js:264` | `show` | Callable `show` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:116` | `saveHeartbeatIfDue` | Callable `saveHeartbeatIfDue` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:126` | `runHeartbeat` | Callable `runHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:136` | `sendUnloadBeacon` | Callable `sendUnloadBeacon` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:146` | `installLifecycleHandlers` | Callable `installLifecycleHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:158` | `uninstallLifecycleHandlers` | Callable `uninstallLifecycleHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker.js:173` | `cancelPendingRequests` | Callable `cancelPendingRequests` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:19` | `on` | Callable `on` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:31` | `once` | Callable `once` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:42` | `off` | Callable `off` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:55` | `count` | Callable `count` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:67` | `clear` | Callable `clear` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/events.js:80` | `emit` | Callable `emit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:29` | `safeBooleanCallback` | Callable `safeBooleanCallback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:47` | `resetHeartbeat` | Callable `resetHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:62` | `captureHeartbeatSegment` | Callable `captureHeartbeatSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:79` | `normaliseHeartbeatInterval` | Callable `normaliseHeartbeatInterval` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:93` | `pollInterval` | Callable `pollInterval` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:110` | `startPolling` | Callable `startPolling` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:126` | `stopPolling` | Callable `stopPolling` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:144` | `shouldSaveHeartbeat` | Callable `shouldSaveHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:160` | `reopenAfterHeartbeat` | Callable `reopenAfterHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:179` | `saveHeartbeatIfDue` | Callable `saveHeartbeatIfDue` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:242` | `runHeartbeat` | Callable `runHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/heartbeat.js:268` | `clearHeartbeatRunning` | Callable `clearHeartbeatRunning` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:23` | `runAfterStop` | Callable `runAfterStop` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:39` | `sendUnloadBeacon` | Callable `sendUnloadBeacon` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:90` | `closeThenStop` | Callable `closeThenStop` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:132` | `installLifecycleHandlers` | Callable `installLifecycleHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:156` | `onVisibilityChange` | Callable `onVisibilityChange` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:164` | `onPageHide` | Callable `onPageHide` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:169` | `onBeforeUnload` | Callable `onBeforeUnload` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/lifecycle.js:203` | `uninstallLifecycleHandlers` | Callable `uninstallLifecycleHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:34` | `openSegment` | Callable `openSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:62` | `closeSegment` | Callable `closeSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:94` | `enqueueSegmentSave` | Callable `enqueueSegmentSave` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:117` | `isPlayerAvailable` | Callable `isPlayerAvailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:134` | `closeAndSaveSegment` | Callable `closeAndSaveSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:175` | `reopenAfterInteractionSave` | Callable `reopenAfterInteractionSave` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/segment.js:193` | `saveCurrentProgress` | Callable `saveCurrentProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:17` | `normaliseTime` | Callable `normaliseTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:65` | `normaliseTrackerState` | Callable `normaliseTrackerState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:83` | `isKnownTrackerState` | Callable `isKnownTrackerState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:99` | `getTrackerState` | Callable `getTrackerState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:112` | `getTransitionToken` | Callable `getTransitionToken` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:123` | `isTransitionCurrent` | Callable `isTransitionCurrent` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:134` | `canTransition` | Callable `canTransition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:152` | `applyTrackerStateFlags` | Callable `applyTrackerStateFlags` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:185` | `setTrackerState` | Callable `setTrackerState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:215` | `markIdle` | Callable `markIdle` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:226` | `markPlaying` | Callable `markPlaying` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:237` | `markPaused` | Callable `markPaused` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:248` | `markSeeking` | Callable `markSeeking` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:259` | `markEnded` | Callable `markEnded` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/state.js:270` | `markDestroyed` | Callable `markDestroyed` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:26` | `syncTime` | Callable `syncTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:49` | `resolveCurrentTime` | Callable `resolveCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:65` | `markProgrammaticSeek` | Callable `markProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:80` | `consumeProgrammaticSeek` | Callable `consumeProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:106` | `resolveSeek` | Callable `resolveSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:144` | `blockSeek` | Callable `blockSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:173` | `clearSeekBlock` | Callable `clearSeekBlock` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/tracker/time.js:199` | `shouldStopReplay` | Callable `shouldStopReplay` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/ui.js:21` | `setReactionButtons` | Callable `setReactionButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/ui.js:44` | `isSafeIconSrc` | Callable `isSafeIconSrc` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/ui.js:79` | `isSafeIconClass` | Callable `isSafeIconClass` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/ui.js:106` | `appendIconSafe` | Callable `appendIconSafe` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/ui.js:161` | `notifyVideoEnded` | Callable `notifyVideoEnded` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:24` | `safeInt` | Callable `safeInt` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:35` | `pad` | Callable `pad` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:46` | `formatSeconds` | Callable `formatSeconds` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:84` | `decodeHtmlEntitiesOnce` | Callable `decodeHtmlEntitiesOnce` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:102` | `decodeHtmlEntitiesForValidation` | Callable `decodeHtmlEntitiesForValidation` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:116` | `validateWebVttText` | Callable `validateWebVttText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:160` | `validateTextResponse` | Callable `validateTextResponse` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:200` | `isSafeFetchUrl` | Callable `isSafeFetchUrl` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:227` | `isSafeBeaconUrl` | Callable `isSafeBeaconUrl` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:255` | `fetchTextWithTimeout` | Callable `fetchTextWithTimeout` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:335` | `sessionSet` | Callable `sessionSet` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/core/utils.js:354` | `sessionGet` | Callable `sessionGet` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/form/duration.js:35` | `normaliseDuration` | Limita e arrotonda una durata rilevata prima che possa essere proposta nel form. |
| `amd/src/form/duration.js:44` | `formatDuration` | Formatta una durata rilevata per il campo modificabile dal docente. |
| `amd/src/form/duration.js:48` | `renderMessage` | Inserisce la durata formattata nel template di stato localizzato. |
| `amd/src/form/duration.js:52` | `setStatus` | Aggiorna la regione di stato accessibile della durata e lo stato visivo. |
| `amd/src/form/duration.js:62` | `parseHttpsUrl` | Analizza soltanto URL HTTPS dei provider accettati dal detector del form. |
| `amd/src/form/duration.js:71` | `extractYouTubeId` | Estrae un ID YouTube supportato usando le stesse forme URL accettate lato server. |
| `amd/src/form/duration.js:92` | `extractVimeoSource` | Estrae ID Vimeo ed eventuale hash di privacy da un URL supportato. |
| `amd/src/form/duration.js:120` | `getProbeHost` | Crea il contenitore delle sonde fuori schermo e nascosto alle tecnologie assistive. |
| `amd/src/form/duration.js:140` | `loadYouTubeApi` | Carica o riusa YouTube IFrame API senza sostituire callback ready già presenti. |
| `amd/src/form/duration.js:196` | `detectYouTubeDuration` | Legge la durata da un player YouTube fuori schermo e senza riproduzione. |
| `amd/src/form/duration.js:245` | `onReady` | Completa la sonda YouTube quando il provider espone una durata positiva. |
| `amd/src/form/duration.js:253` | `onError` | Rifiuta la sonda YouTube quando il provider non carica il video selezionato. |
| `amd/src/form/duration.js:262` | `loadVimeoApi` | Carica o riusa Vimeo Player SDK evitando conflitti anonymous-define con RequireJS. |
| `amd/src/form/duration.js:325` | `detectVimeoDuration` | Legge la durata da un iframe Vimeo senza riproduzione, conservando l’eventuale hash di privacy. |
| `amd/src/form/duration.js:372` | `findLocalFileUrl` | Individua l’URL draft Moodle same-origin esposto dal file picker locale. |
| `amd/src/form/duration.js:389` | `detectLocalDuration` | Legge la durata audio/video locale dai metadati HTML media senza avviare la riproduzione. |
| `amd/src/form/duration.js:443` | `resolveSource` | Costruisce fingerprint della sorgente corrente e detector specifico del provider. |
| `amd/src/form/duration.js:449` | `detect` | Avvia il detector specifico per la sorgente risolta. |
| `amd/src/form/duration.js:458` | `detect` | Avvia il detector specifico per la sorgente risolta. |
| `amd/src/form/duration.js:467` | `detect` | Avvia il detector specifico per la sorgente risolta. |
| `amd/src/form/duration.js:475` | `getElements` | Risolve i controlli necessari del form attività e la regione di stato. |
| `amd/src/form/duration.js:489` | `install` | Coordina debounce, rifiuto delle risposte obsolete, override manuali e cambi sorgente. |
| `amd/src/form/duration.js:610` | `init` | Legge la configurazione del detector dall’elemento JSON nel DOM del form e inizializza la proposta best effort della durata nel form docente attendibile. |
| `amd/src/html5_player.js:56` | `resolveConfig` | Callable `resolveConfig` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:67` | `uuid` | Callable `uuid` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:71` | `safeNumber` | Callable `safeNumber` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:76` | `getMaxWatchedFromIntervals` | Callable `getMaxWatchedFromIntervals` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:92` | `markAllowedForwardTime` | Callable `markAllowedForwardTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:96` | `getAllowedForwardLimit` | Callable `getAllowedForwardLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:101` | `normaliseControls` | Callable `normaliseControls` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:120` | `getConfiguredMaxPlaybackRate` | Callable `getConfiguredMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:131` | `getPlaybackRatePenalty` | Callable `getPlaybackRatePenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:139` | `getNaturalPlaybackTolerance` | Callable `getNaturalPlaybackTolerance` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:147` | `setSpeedButtonState` | Callable `setSpeedButtonState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:157` | `writePlaybackRate` | Callable `writePlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:168` | `enforceMaxPlaybackRate` | Callable `enforceMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:186` | `getBlockedSeekPlaybackRate` | Callable `getBlockedSeekPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:195` | `applyBlockedSeekPenalty` | Callable `applyBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:199` | `retryBlockedSeekPenalty` | Callable `retryBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:212` | `markHTML5PlaybackObserved` | Callable `markHTML5PlaybackObserved` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:216` | `resolveHTML5SeekWasPlaying` | Callable `resolveHTML5SeekWasPlaying` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:221` | `playHTML5AfterSeek` | Callable `playHTML5AfterSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:226` | `attempt` | Callable `attempt` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:270` | `scheduleBlockedSeekResume` | Callable `scheduleBlockedSeekResume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:282` | `finishProgrammaticSeek` | Callable `finishProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:295` | `scheduleProgrammaticSeekFallback` | Callable `scheduleProgrammaticSeekFallback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:306` | `startProgrammaticSeek` | Callable `startProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:312` | `blockForwardSeek` | Callable `blockForwardSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:341` | `saveSegment` | Callable `saveSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:349` | `hasMedia` | Callable `hasMedia` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:356` | `saveCurrentProgress` | Callable `saveCurrentProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:360` | `updateProgress` | Callable `updateProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:371` | `resolveReactionTime` | Callable `resolveReactionTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:380` | `isDefinitiveReactionFailure` | Callable `isDefinitiveReactionFailure` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:394` | `removeReactionRow` | Callable `removeReactionRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:402` | `startSegment` | Callable `startSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:421` | `closeSegment` | Callable `closeSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:429` | `startHeartbeat` | Callable `startHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:434` | `getCurrentTime` | Callable `getCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:438` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:441` | `shouldSkip` | Callable `shouldSkip` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:449` | `stopHeartbeat` | Callable `stopHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:453` | `showResumeNotice` | Callable `showResumeNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:466` | `replayHTML5Fragment` | Callable `replayHTML5Fragment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:481` | `installGlobalListeners` | Callable `installGlobalListeners` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:485` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:486` | `sendBeacon` | Callable `sendBeacon` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:489` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:491` | `sendSegment` | Callable `sendSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:501` | `handleReplayClick` | Callable `handleReplayClick` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:525` | `buildPlayer` | Callable `buildPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:615` | `formatElapsedTime` | Callable `formatElapsedTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:631` | `updateElapsedDisplays` | Callable `updateElapsedDisplays` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:642` | `buildControlBar` | Callable `buildControlBar` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:871` | `updatePipPressed` | Callable `updatePipPressed` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:881` | `cleanupPipHandler` | Callable `cleanupPipHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:899` | `updateFullscreenPressed` | Callable `updateFullscreenPressed` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:902` | `cleanupFullscreenHandler` | Callable `cleanupFullscreenHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1015` | `makeBtn` | Callable `makeBtn` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1028` | `attachTrackingEvents` | Callable `attachTrackingEvents` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1163` | `setReactionButtons` | Callable `setReactionButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1168` | `announceReactionUnavailable` | Callable `announceReactionUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1176` | `installReactionHandler` | Callable `installReactionHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1180` | `appendReactionRow` | Callable `appendReactionRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1256` | `reactionKeydownHandler` | Callable `reactionKeydownHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1265` | `reactionClickHandler` | Callable `reactionClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1399` | `cleanupReactionRootHandlers` | Callable `cleanupReactionRootHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1415` | `getCurrentVideoTime` | Callable `getCurrentVideoTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1422` | `initialiseFocusGuard` | Callable `initialiseFocusGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1427` | `pause` | Callable `pause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1430` | `showMessage` | Callable `showMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1439` | `installNotesToggle` | Callable `installNotesToggle` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1448` | `installNoteHandler` | Callable `installNoteHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1463` | `installBookmarkHandler` | Callable `installBookmarkHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1481` | `navigateTimedText` | Callable `navigateTimedText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1502` | `installPosterHandler` | Callable `installPosterHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1506` | `removePoster` | Callable `removePoster` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1511` | `posterClickHandler` | Callable `posterClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1549` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/html5_player.js:1579` | `getDuration` | Callable `getDuration` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:42` | `resolveConfig` | Callable `resolveConfig` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:53` | `uuid` | Callable `uuid` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:57` | `loadApi` | Callable `loadApi` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:75` | `updateProgress` | Callable `updateProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:86` | `resolveReactionTime` | Callable `resolveReactionTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:102` | `updateIntervalBar` | Callable `updateIntervalBar` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:106` | `updateLiveIntervalBar` | Callable `updateLiveIntervalBar` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:110` | `markAllowedForwardTime` | Callable `markAllowedForwardTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:115` | `getAllowedForwardLimit` | Callable `getAllowedForwardLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:119` | `isForwardTargetAlreadyWatched` | Callable `isForwardTargetAlreadyWatched` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:124` | `isNormalForwardPlayback` | Callable `isNormalForwardPlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:136` | `isForwardSeekRecoveryPlayback` | Callable `isForwardSeekRecoveryPlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:141` | `resetForwardSeekRecovery` | Callable `resetForwardSeekRecovery` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:153` | `getMaxWatchedFromIntervals` | Callable `getMaxWatchedFromIntervals` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:176` | `getResumeStorageKey` | Callable `getResumeStorageKey` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:180` | `readStoredResumePosition` | Callable `readStoredResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:191` | `rememberResumePosition` | Callable `rememberResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:205` | `resolveResumePosition` | Callable `resolveResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:211` | `initialiseKnownProgress` | Callable `initialiseKnownProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:222` | `getBlockedSeekPlaybackRate` | Callable `getBlockedSeekPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:231` | `blockForwardSeek` | Callable `blockForwardSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:254` | `saveSegment` | Callable `saveSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:262` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:266` | `saveCurrentProgress` | Callable `saveCurrentProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:270` | `closeCurrentSegment` | Callable `closeCurrentSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:277` | `getConfiguredMaxPlaybackRate` | Callable `getConfiguredMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:291` | `getPlaybackRatePenalty` | Callable `getPlaybackRatePenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:299` | `writePlaybackRate` | Callable `writePlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:320` | `applyBlockedSeekPenalty` | Callable `applyBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:324` | `retryBlockedSeekPenalty` | Callable `retryBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:335` | `enforceMaxPlaybackRate` | Callable `enforceMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:365` | `installPlaybackRateGuard` | Callable `installPlaybackRateGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:376` | `cleanupPlaybackRateGuard` | Callable `cleanupPlaybackRateGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:383` | `startCurrentSegment` | Callable `startCurrentSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:402` | `setReactionButtons` | Callable `setReactionButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:407` | `announceReactionUnavailable` | Callable `announceReactionUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:411` | `replayFragment` | Callable `replayFragment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:428` | `handleSeekByPolling` | Callable `handleSeekByPolling` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:543` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:550` | `onPlayerStateChange` | Callable `onPlayerStateChange` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:590` | `showResumeNotice` | Callable `showResumeNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:597` | `installGlobalListeners` | Callable `installGlobalListeners` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:601` | `onHidden` | Callable `onHidden` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:604` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:605` | `sendBeacon` | Callable `sendBeacon` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:608` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:610` | `sendSegment` | Callable `sendSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:626` | `reactionKeydownHandler` | Callable `reactionKeydownHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:635` | `reactionClickHandler` | Callable `reactionClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:762` | `cleanupReactionRootHandlers` | Callable `cleanupReactionRootHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:781` | `appendReactionRow` | Callable `appendReactionRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:859` | `buildPlayer` | Callable `buildPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:887` | `onReady` | Callable `onReady` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:919` | `onPlaybackRateChange` | Callable `onPlaybackRateChange` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:922` | `onAutoplayBlocked` | Callable `onAutoplayBlocked` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:936` | `removeNotice` | Callable `removeNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:954` | `onError` | Callable `onError` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:970` | `buildYouTubeSkipButtons` | Callable `buildYouTubeSkipButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1050` | `getCurrentVideoTime` | Callable `getCurrentVideoTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1057` | `initialiseFocusGuard` | Callable `initialiseFocusGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1062` | `pause` | Callable `pause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1065` | `showMessage` | Callable `showMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1082` | `installReactionHandler` | Callable `installReactionHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1091` | `installNotesToggle` | Callable `installNotesToggle` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1100` | `installNoteHandler` | Callable `installNoteHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1115` | `installBookmarkHandler` | Callable `installBookmarkHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1133` | `navigateTimedText` | Callable `navigateTimedText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1160` | `installPosterHandler` | Callable `installPosterHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1164` | `removePoster` | Callable `removePoster` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1169` | `posterClickHandler` | Callable `posterClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1207` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/player.js:1239` | `getDuration` | Callable `getDuration` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:29` | `cssEscape` | Callable `cssEscape` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:36` | `queryByName` | Callable `queryByName` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:43` | `findPicker` | Callable `findPicker` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:47` | `findTargetInput` | Callable `findTargetInput` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:56` | `findTypeSelect` | Callable `findTypeSelect` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:61` | `renderPreview` | Callable `renderPreview` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:89` | `updateChoiceState` | Callable `updateChoiceState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:107` | `filterDialog` | Callable `filterDialog` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:149` | `closePicker` | Callable `closePicker` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:163` | `openPicker` | Callable `openPicker` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:184` | `attachIconPickers` | Callable `attachIconPickers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/presets.js:266` | `installHtml5SourceVisibility` | Mostra l’intero fieldset dei controlli HTML5 soltanto quando la sorgente dell’istanza è un file locale. |
| `amd/src/presets.js:304` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/report.js:37` | `attachConfirm` | Callable `attachConfirm` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/report.js:55` | `initialiseExportFormat` | Callable `initialiseExportFormat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/report.js:61` | `update` | Callable `update` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/report.js:80` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:55` | `resolveConfig` | Callable `resolveConfig` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:66` | `uuid` | Callable `uuid` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:71` | `saveSegment` | Callable `saveSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:79` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:83` | `saveCurrentProgress` | Callable `saveCurrentProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:87` | `updateProgress` | Callable `updateProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:98` | `resolveReactionTime` | Callable `resolveReactionTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:107` | `updateLiveIntervalBar` | Callable `updateLiveIntervalBar` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:111` | `markAllowedForwardTime` | Callable `markAllowedForwardTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:116` | `getAllowedForwardLimit` | Callable `getAllowedForwardLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:120` | `getBlockedForwardGuardLimit` | Callable `getBlockedForwardGuardLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:130` | `getBlockedForwardRecoveryLimit` | Callable `getBlockedForwardRecoveryLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:144` | `isBlockedForwardRecoveryPlayback` | Callable `isBlockedForwardRecoveryPlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:149` | `isVimeoForwardTimeBlocked` | Callable `isVimeoForwardTimeBlocked` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:155` | `markVimeoProgrammaticSeek` | Callable `markVimeoProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:161` | `clearVimeoProgrammaticSeekTarget` | Callable `clearVimeoProgrammaticSeekTarget` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:166` | `consumeVimeoProgrammaticSeek` | Callable `consumeVimeoProgrammaticSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:181` | `isNormalForwardPlayback` | Callable `isNormalForwardPlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:193` | `isForwardSeekRecoveryPlayback` | Callable `isForwardSeekRecoveryPlayback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:198` | `resetForwardSeekRecovery` | Callable `resetForwardSeekRecovery` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:212` | `getMaxWatchedFromIntervals` | Callable `getMaxWatchedFromIntervals` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:234` | `isVimeoBackwardSeekAllowed` | Callable `isVimeoBackwardSeekAllowed` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:240` | `isReplaySeekActive` | Callable `isReplaySeekActive` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:244` | `getRecentVimeoUserSeek` | Callable `getRecentVimeoUserSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:253` | `clearRecentVimeoUserSeek` | Callable `clearRecentVimeoUserSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:257` | `markVimeoPlaybackObserved` | Callable `markVimeoPlaybackObserved` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:261` | `resolveVimeoSeekWasPlaying` | Callable `resolveVimeoSeekWasPlaying` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:268` | `rememberVimeoUserSeek` | Callable `rememberVimeoUserSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:283` | `clearBlockedSeekResumeState` | Callable `clearBlockedSeekResumeState` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:292` | `clearBlockedSeekResumeRequest` | Callable `clearBlockedSeekResumeRequest` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:310` | `playVimeoAfterSeek` | Callable `playVimeoAfterSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:321` | `complete` | Callable `complete` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:338` | `attempt` | Callable `attempt` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:444` | `ensureVimeoRuntimePlaying` | Callable `ensureVimeoRuntimePlaying` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:457` | `getResumeStorageKey` | Callable `getResumeStorageKey` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:461` | `readStoredResumePosition` | Callable `readStoredResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:482` | `rememberResumePosition` | Callable `rememberResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:496` | `resolveResumePosition` | Callable `resolveResumePosition` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:510` | `startVimeoRuntimePolling` | Callable `startVimeoRuntimePolling` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:518` | `stopVimeoRuntimePolling` | Callable `stopVimeoRuntimePolling` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:526` | `readVimeoValue` | Callable `readVimeoValue` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:536` | `pauseRuntimeSegment` | Callable `pauseRuntimeSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:546` | `pollVimeoRuntime` | Callable `pollVimeoRuntime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:582` | `getCurrentTime` | Callable `getCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:586` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:594` | `handleVimeoTime` | Callable `handleVimeoTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:672` | `initialiseKnownProgress` | Callable `initialiseKnownProgress` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:683` | `scheduleBlockedSeekResume` | Callable `scheduleBlockedSeekResume` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:704` | `verifyBlockedSeekRollback` | Callable `verifyBlockedSeekRollback` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:730` | `recoverBlockedSeek` | Callable `recoverBlockedSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:745` | `finish` | Callable `finish` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:781` | `getBlockedSeekPlaybackRate` | Callable `getBlockedSeekPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:790` | `blockForwardSeek` | Callable `blockForwardSeek` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:816` | `getConfiguredMaxPlaybackRate` | Callable `getConfiguredMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:827` | `getPlaybackRatePenalty` | Callable `getPlaybackRatePenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:835` | `applyBlockedSeekPenalty` | Callable `applyBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:839` | `retryBlockedSeekPenalty` | Callable `retryBlockedSeekPenalty` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:854` | `writePlaybackRate` | Callable `writePlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:868` | `retryPlaybackRateLimit` | Callable `retryPlaybackRateLimit` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:883` | `enforcePlaybackRateValue` | Callable `enforcePlaybackRateValue` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:903` | `enforceMaxPlaybackRate` | Callable `enforceMaxPlaybackRate` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:915` | `installPlaybackRateGuard` | Callable `installPlaybackRateGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:924` | `cleanupPlaybackRateGuard` | Callable `cleanupPlaybackRateGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:933` | `startSegment` | Callable `startSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:947` | `closeSegment` | Callable `closeSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:955` | `startHeartbeat` | Callable `startHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:960` | `getCurrentTime` | Callable `getCurrentTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:964` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:972` | `stopHeartbeat` | Callable `stopHeartbeat` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:982` | `showResumeNotice` | Callable `showResumeNotice` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:986` | `installGlobalListeners` | Callable `installGlobalListeners` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:991` | `onHidden` | Callable `onHidden` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:994` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:995` | `sendBeacon` | Callable `sendBeacon` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:998` | `hasPlayer` | Callable `hasPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:999` | `sendSegment` | Callable `sendSegment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1009` | `loadVimeoSDK` | Callable `loadVimeoSDK` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1016` | `restoreDefine` | Callable `restoreDefine` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1063` | `resolveVimeoSource` | Callable `resolveVimeoSource` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1098` | `buildVimeoIframe` | Callable `buildVimeoIframe` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1137` | `replayVimeoFragment` | Callable `replayVimeoFragment` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1181` | `buildPlayer` | Callable `buildPlayer` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1452` | `handleReplayClick` | Callable `handleReplayClick` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1481` | `buildVimeoSkipButtons` | Callable `buildVimeoSkipButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1565` | `setReactionButtons` | Callable `setReactionButtons` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1570` | `announceReactionUnavailable` | Callable `announceReactionUnavailable` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1575` | `installReactionHandler` | Callable `installReactionHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1579` | `appendReactionRow` | Callable `appendReactionRow` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1649` | `reactionKeydownHandler` | Callable `reactionKeydownHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1658` | `reactionClickHandler` | Callable `reactionClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1762` | `cleanupReactionRootHandlers` | Callable `cleanupReactionRootHandlers` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1782` | `getCurrentVideoTime` | Callable `getCurrentVideoTime` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1796` | `initialiseFocusGuard` | Callable `initialiseFocusGuard` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1801` | `pause` | Callable `pause` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1804` | `showMessage` | Callable `showMessage` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1813` | `installNotesToggle` | Callable `installNotesToggle` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1822` | `installNoteHandler` | Callable `installNoteHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1837` | `installBookmarkHandler` | Callable `installBookmarkHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1855` | `navigateTimedText` | Callable `navigateTimedText` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1889` | `installPosterHandler` | Callable `installPosterHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1893` | `removePoster` | Callable `removePoster` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1898` | `posterClickHandler` | Callable `posterClickHandler` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1941` | `init` | Callable `init` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
| `amd/src/vimeo_player.js:1972` | `getDuration` | Callable `getDuration` del modulo; JSDoc e chiamanti definiscono parametri ed effetti. |
