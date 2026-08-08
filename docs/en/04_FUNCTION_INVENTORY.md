# Named function inventory

Generated from the VideoTrack 1.6.31 sources. Anonymous closures are intentionally omitted.

PHP entries: **476**. AMD entries: **613**.

## PHP functions and methods

| File:line | Function or method | Contract |
|---|---|---|
| `backup/moodle2/backup_videotrack_activity_task.class.php:36` | `backup_videotrack_activity_task::define_my_settings` | Define backup settings for the activity. |
| `backup/moodle2/backup_videotrack_activity_task.class.php:42` | `backup_videotrack_activity_task::define_my_steps` | Define backup steps for the activity. |
| `backup/moodle2/backup_videotrack_activity_task.class.php:52` | `backup_videotrack_activity_task::encode_content_links` | Encode links to VideoTrack activity instances in backed up content. |
| `backup/moodle2/backup_videotrack_stepslib.php:34` | `backup_videotrack_activity_structure_step::define_structure` | Define the backup structure for the activity and its related user data. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:36` | `restore_videotrack_activity_task::define_my_settings` | Define restore settings for the activity. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:42` | `restore_videotrack_activity_task::define_my_steps` | Define restore steps for the activity. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:51` | `restore_videotrack_activity_task::define_decode_contents` | Define content fields that need link decoding during restore. |
| `backup/moodle2/restore_videotrack_activity_task.class.php:60` | `restore_videotrack_activity_task::define_decode_rules` | Define restore link decoding rules for VideoTrack activity URLs. |
| `backup/moodle2/restore_videotrack_stepslib.php:34` | `restore_videotrack_activity_structure_step::define_structure` | Define the restore structure for configuration, viewing, interaction, integrity and acknowledgement records. |
| `backup/moodle2/restore_videotrack_stepslib.php:59` | `restore_videotrack_activity_structure_step::process_videotrack` | Restore the main VideoTrack activity record. |
| `backup/moodle2/restore_videotrack_stepslib.php:93` | `restore_videotrack_activity_structure_step::process_videotrack_reaction` | Restore a configured reaction. |
| `backup/moodle2/restore_videotrack_stepslib.php:123` | `restore_videotrack_activity_structure_step::process_videotrack_segment` | Restore one user playback segment. |
| `backup/moodle2/restore_videotrack_stepslib.php:165` | `restore_videotrack_activity_structure_step::process_videotrack_state` | Restore one persisted user playback state. |
| `backup/moodle2/restore_videotrack_stepslib.php:211` | `restore_videotrack_activity_structure_step::process_videotrack_reactionevent` | Restore one user reaction or note event. |
| `backup/moodle2/restore_videotrack_stepslib.php:294` | `restore_videotrack_activity_structure_step::process_videotrack_integrityevent` | Restore one privacy-safe integrity signal. |
| `backup/moodle2/restore_videotrack_stepslib.php:331` | `restore_videotrack_activity_structure_step::process_videotrack_acknowledgement` | Restore one versioned learner acknowledgement. |
| `backup/moodle2/restore_videotrack_stepslib.php:376` | `restore_videotrack_activity_structure_step::normalise_interval_json` | Normalise restored interval JSON before storing it again. |
| `backup/moodle2/restore_videotrack_stepslib.php:387` | `restore_videotrack_activity_structure_step::get_restored_cmid` | Return the new course module id created by the restore task. |
| `backup/moodle2/restore_videotrack_stepslib.php:394` | `restore_videotrack_activity_structure_step::after_execute` | Restore related files and recreate the grade item after records are restored. |
| `classes/admin/setting_int_range.php:43` | `mod_videotrack\admin\setting_int_range::__construct` | Constructor. |
| `classes/admin/setting_int_range.php:61` | `mod_videotrack\admin\setting_int_range::validate` | Validate the setting value. |
| `classes/admin/setting_nonnegative_int.php:36` | `mod_videotrack\admin\setting_nonnegative_int::validate` | Validate the setting value. |
| `classes/admin/setting_retention_days.php:38` | `mod_videotrack\admin\setting_retention_days::write_setting` | Persist the setting and log when unlimited retention is newly enabled. |
| `classes/completion/custom_completion.php:36` | `mod_videotrack\completion\custom_completion::get_sort_order` | Return the display order for custom completion rules. |
| `classes/completion/custom_completion.php:51` | `mod_videotrack\completion\custom_completion::get_defined_custom_rules` | Return the list of custom completion rules implemented by the activity. |
| `classes/completion/custom_completion.php:61` | `mod_videotrack\completion\custom_completion::get_state` | Return the completion state for a single custom rule. |
| `classes/completion/custom_completion.php:147` | `mod_videotrack\completion\custom_completion::get_custom_rule_descriptions` | Return human-readable descriptions for configured custom completion rules. |
| `classes/completion/custom_completion.php:195` | `mod_videotrack\completion\custom_completion::get_required_reaction_labels` | Return formatted labels for reactions required for completion. |
| `classes/event/acknowledgement_confirmed.php:30` | `mod_videotrack\event\acknowledgement_confirmed::init` | Initialise event metadata. |
| `classes/event/acknowledgement_confirmed.php:41` | `mod_videotrack\event\acknowledgement_confirmed::get_name` | Return the event display name. |
| `classes/event/acknowledgement_confirmed.php:50` | `mod_videotrack\event\acknowledgement_confirmed::get_description` | Return a human-readable event description. |
| `classes/event/acknowledgement_confirmed.php:60` | `mod_videotrack\event\acknowledgement_confirmed::get_url` | Return the URL associated with this event. |
| `classes/event/acknowledgement_confirmed.php:69` | `mod_videotrack\event\acknowledgement_confirmed::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/activity_completed.php:31` | `mod_videotrack\event\activity_completed::init` | Initialise event metadata. |
| `classes/event/activity_completed.php:42` | `mod_videotrack\event\activity_completed::get_name` | Return the event display name. |
| `classes/event/activity_completed.php:51` | `mod_videotrack\event\activity_completed::get_description` | Return a human-readable event description. |
| `classes/event/activity_completed.php:63` | `mod_videotrack\event\activity_completed::get_url` | Return the URL associated with this event. |
| `classes/event/activity_completed.php:72` | `mod_videotrack\event\activity_completed::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/bookmark_deleted.php:30` | `mod_videotrack\event\bookmark_deleted::init` | Initialise event metadata. |
| `classes/event/bookmark_deleted.php:41` | `mod_videotrack\event\bookmark_deleted::get_name` | Return the event display name. |
| `classes/event/bookmark_deleted.php:50` | `mod_videotrack\event\bookmark_deleted::get_description` | Return a human-readable event description. |
| `classes/event/bookmark_deleted.php:60` | `mod_videotrack\event\bookmark_deleted::get_url` | Return the URL associated with this event. |
| `classes/event/bookmark_deleted.php:69` | `mod_videotrack\event\bookmark_deleted::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/bookmark_exported.php:30` | `mod_videotrack\event\bookmark_exported::init` | Initialise event metadata. |
| `classes/event/bookmark_exported.php:41` | `mod_videotrack\event\bookmark_exported::get_name` | Return the event display name. |
| `classes/event/bookmark_exported.php:50` | `mod_videotrack\event\bookmark_exported::get_description` | Return a human-readable event description. |
| `classes/event/bookmark_exported.php:60` | `mod_videotrack\event\bookmark_exported::get_url` | Return the URL associated with this event. |
| `classes/event/bookmark_exported.php:67` | `mod_videotrack\event\bookmark_exported::validate_data` | Validate event data before dispatch. |
| `classes/event/bookmark_exported.php:79` | `mod_videotrack\event\bookmark_exported::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/bookmark_saved.php:30` | `mod_videotrack\event\bookmark_saved::init` | Initialise event metadata. |
| `classes/event/bookmark_saved.php:41` | `mod_videotrack\event\bookmark_saved::get_name` | Return the event display name. |
| `classes/event/bookmark_saved.php:50` | `mod_videotrack\event\bookmark_saved::get_description` | Return a human-readable event description. |
| `classes/event/bookmark_saved.php:60` | `mod_videotrack\event\bookmark_saved::get_url` | Return the URL associated with this event. |
| `classes/event/bookmark_saved.php:69` | `mod_videotrack\event\bookmark_saved::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/course_module_viewed.php:30` | `mod_videotrack\event\course_module_viewed::init` | Initialise event metadata. |
| `classes/event/note_deleted.php:30` | `mod_videotrack\event\note_deleted::init` | Initialise event metadata. |
| `classes/event/note_deleted.php:41` | `mod_videotrack\event\note_deleted::get_name` | Return the event display name. |
| `classes/event/note_deleted.php:50` | `mod_videotrack\event\note_deleted::get_description` | Return a human-readable event description. |
| `classes/event/note_deleted.php:60` | `mod_videotrack\event\note_deleted::get_url` | Return the URL associated with this event. |
| `classes/event/note_deleted.php:69` | `mod_videotrack\event\note_deleted::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/note_saved.php:32` | `mod_videotrack\event\note_saved::init` | Initialise event metadata. |
| `classes/event/note_saved.php:43` | `mod_videotrack\event\note_saved::get_name` | Return the event display name. |
| `classes/event/note_saved.php:52` | `mod_videotrack\event\note_saved::get_description` | Return a human-readable event description. |
| `classes/event/note_saved.php:64` | `mod_videotrack\event\note_saved::get_url` | Return the URL associated with this event. |
| `classes/event/note_saved.php:73` | `mod_videotrack\event\note_saved::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/notes_exported.php:30` | `mod_videotrack\event\notes_exported::init` | Initialise event metadata. |
| `classes/event/notes_exported.php:41` | `mod_videotrack\event\notes_exported::get_name` | Return the event display name. |
| `classes/event/notes_exported.php:50` | `mod_videotrack\event\notes_exported::get_description` | Return a human-readable event description. |
| `classes/event/notes_exported.php:62` | `mod_videotrack\event\notes_exported::get_url` | Return the URL associated with this event. |
| `classes/event/notes_exported.php:70` | `mod_videotrack\event\notes_exported::validate_data` | Validate event data before dispatch. |
| `classes/event/notes_exported.php:94` | `mod_videotrack\event\notes_exported::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/notes_exported.php:103` | `mod_videotrack\event\notes_exported::get_other_mapping` | Return other-field mapping information for backup and restore. |
| `classes/event/reaction_deleted.php:30` | `mod_videotrack\event\reaction_deleted::init` | Initialise event metadata. |
| `classes/event/reaction_deleted.php:41` | `mod_videotrack\event\reaction_deleted::get_name` | Return the event display name. |
| `classes/event/reaction_deleted.php:50` | `mod_videotrack\event\reaction_deleted::get_description` | Return a human-readable event description. |
| `classes/event/reaction_deleted.php:61` | `mod_videotrack\event\reaction_deleted::get_url` | Return the URL associated with this event. |
| `classes/event/reaction_deleted.php:70` | `mod_videotrack\event\reaction_deleted::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/reaction_saved.php:30` | `mod_videotrack\event\reaction_saved::init` | Initialise event metadata. |
| `classes/event/reaction_saved.php:41` | `mod_videotrack\event\reaction_saved::get_name` | Return the event display name. |
| `classes/event/reaction_saved.php:50` | `mod_videotrack\event\reaction_saved::get_description` | Return a human-readable event description. |
| `classes/event/reaction_saved.php:65` | `mod_videotrack\event\reaction_saved::get_url` | Return the URL associated with this event. |
| `classes/event/reaction_saved.php:74` | `mod_videotrack\event\reaction_saved::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/report_exported.php:30` | `mod_videotrack\event\report_exported::init` | Initialise event metadata. |
| `classes/event/report_exported.php:41` | `mod_videotrack\event\report_exported::get_name` | Return the event display name. |
| `classes/event/report_exported.php:50` | `mod_videotrack\event\report_exported::get_description` | Return a human-readable event description. |
| `classes/event/report_exported.php:61` | `mod_videotrack\event\report_exported::get_url` | Return the URL associated with this event. |
| `classes/event/report_exported.php:68` | `mod_videotrack\event\report_exported::validate_data` | Validate event data before dispatch. |
| `classes/event/report_exported.php:86` | `mod_videotrack\event\report_exported::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/segment_saved.php:31` | `mod_videotrack\event\segment_saved::init` | Initialise event metadata. |
| `classes/event/segment_saved.php:42` | `mod_videotrack\event\segment_saved::get_name` | Return the event display name. |
| `classes/event/segment_saved.php:51` | `mod_videotrack\event\segment_saved::get_description` | Return a human-readable event description. |
| `classes/event/segment_saved.php:63` | `mod_videotrack\event\segment_saved::get_url` | Return the URL associated with this event. |
| `classes/event/segment_saved.php:72` | `mod_videotrack\event\segment_saved::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/event/student_progress_reset.php:30` | `mod_videotrack\event\student_progress_reset::init` | Initialise event metadata. |
| `classes/event/student_progress_reset.php:41` | `mod_videotrack\event\student_progress_reset::get_name` | Return the event display name. |
| `classes/event/student_progress_reset.php:50` | `mod_videotrack\event\student_progress_reset::get_description` | Return a human-readable event description. |
| `classes/event/student_progress_reset.php:65` | `mod_videotrack\event\student_progress_reset::validate_data` | Validate event data before dispatch. |
| `classes/event/student_progress_reset.php:82` | `mod_videotrack\event\student_progress_reset::get_url` | Return the URL associated with this event. |
| `classes/event/student_progress_reset.php:91` | `mod_videotrack\event\student_progress_reset::get_objectid_mapping` | Return object id mapping information for backup and restore. |
| `classes/external/delete_bookmark.php:44` | `mod_videotrack\external\delete_bookmark::execute_parameters` | Returns the external function parameters. |
| `classes/external/delete_bookmark.php:58` | `mod_videotrack\external\delete_bookmark::execute` | Delete a private bookmark. |
| `classes/external/delete_bookmark.php:91` | `mod_videotrack\external\delete_bookmark::execute_returns` | Returns the external function result structure. |
| `classes/external/delete_note.php:44` | `mod_videotrack\external\delete_note::execute_parameters` | Returns the external function parameters. |
| `classes/external/delete_note.php:65` | `mod_videotrack\external\delete_note::execute` | Soft-deletes a personal note owned by the current user. |
| `classes/external/delete_note.php:118` | `mod_videotrack\external\delete_note::execute_returns` | Returns the external function result structure. |
| `classes/external/delete_reaction.php:45` | `mod_videotrack\external\delete_reaction::execute_parameters` | Returns the external function parameters. |
| `classes/external/delete_reaction.php:62` | `mod_videotrack\external\delete_reaction::execute` | Soft-deletes a standard reaction owned by the current user. |
| `classes/external/delete_reaction.php:133` | `mod_videotrack\external\delete_reaction::execute_returns` | Returns the external function result structure. |
| `classes/external/helper.php:43` | `mod_videotrack\external\helper::require_ajax_sesskey` | Requires the Moodle session key for browser-originated AJAX mutations. |
| `classes/external/helper.php:55` | `mod_videotrack\external\helper::validate_positive_id` | Validates a positive database identifier received by an AJAX endpoint. |
| `classes/external/helper.php:75` | `mod_videotrack\external\helper::validate_session_id` | Validates a browser playback session id generated by the AMD player. |
| `classes/external/helper.php:89` | `mod_videotrack\external\helper::validate_end_reason` | Validates the reason used to close a viewing segment. |
| `classes/external/helper.php:111` | `mod_videotrack\external\helper::validate_bounded_float` | Validates that a float-like parameter is finite and inside a bounded range. |
| `classes/external/helper.php:124` | `mod_videotrack\external\helper::load_and_validate_context` | Loads the activity and validates login, context, view and explicit participation capability. |
| `classes/external/save_bookmark.php:45` | `mod_videotrack\external\save_bookmark::execute_parameters` | Returns the external function parameters. |
| `classes/external/save_bookmark.php:65` | `mod_videotrack\external\save_bookmark::execute` | Save a private bookmark. |
| `classes/external/save_bookmark.php:168` | `mod_videotrack\external\save_bookmark::execute_returns` | Returns the external function result structure. |
| `classes/external/save_integrity_event.php:47` | `mod_videotrack\external\save_integrity_event::execute_parameters` | Return the external function parameters. |
| `classes/external/save_integrity_event.php:65` | `mod_videotrack\external\save_integrity_event::execute` | Store one integrity signal when recording is enabled for the activity. |
| `classes/external/save_integrity_event.php:140` | `mod_videotrack\external\save_integrity_event::execute_returns` | Return the external function result structure. |
| `classes/external/save_note.php:48` | `mod_videotrack\external\save_note::execute_parameters` | Returns the external function parameters. |
| `classes/external/save_note.php:68` | `mod_videotrack\external\save_note::execute` | Saves a personal note for the current user. |
| `classes/external/save_note.php:215` | `mod_videotrack\external\save_note::execute_returns` | Returns the external function result structure. |
| `classes/external/save_reaction.php:45` | `mod_videotrack\external\save_reaction::execute_parameters` | Returns the external function parameters. |
| `classes/external/save_reaction.php:65` | `mod_videotrack\external\save_reaction::execute` | Saves a configured reaction for the current user. |
| `classes/external/save_reaction.php:285` | `mod_videotrack\external\save_reaction::export_reaction_for_client` | Exports the saved reaction definition for immediate client-side rendering. |
| `classes/external/save_reaction.php:311` | `mod_videotrack\external\save_reaction::execute_returns` | Returns the external function result structure. |
| `classes/external/save_segment.php:48` | `mod_videotrack\external\save_segment::execute_parameters` | Returns the external function parameters. |
| `classes/external/save_segment.php:76` | `mod_videotrack\external\save_segment::execute` | Saves a watched video segment and refreshes aggregate progress. |
| `classes/external/save_segment.php:281` | `mod_videotrack\external\save_segment::execute_returns` | Returns the external function result structure. |
| `classes/form/forum_post_form.php:40` | `mod_videotrack\form\forum_post_form::definition` | Defines the composer form. |
| `classes/form/forum_post_form.php:114` | `mod_videotrack\form\forum_post_form::validation` | Validates required composer content and the submitted group. |
| `classes/local/acknowledgement.php:46` | `mod_videotrack\local\acknowledgement::is_enabled` | Return whether the current activity contains an enabled acknowledgement statement. |
| `classes/local/acknowledgement.php:57` | `mod_videotrack\local\acknowledgement::has_visible_text` | Return whether formatted content contains visible non-spacing text. |
| `classes/local/acknowledgement.php:69` | `mod_videotrack\local\acknowledgement::timing` | Return the configured acknowledgement timing policy. |
| `classes/local/acknowledgement.php:82` | `mod_videotrack\local\acknowledgement::requires_video_end` | Return whether the statement requires the final video second. |
| `classes/local/acknowledgement.php:94` | `mod_videotrack\local\acknowledgement::statement_hash` | Build the stable hash used to identify the current statement version. |
| `classes/local/acknowledgement.php:108` | `mod_videotrack\local\acknowledgement::progress_snapshot` | Build the immutable viewing snapshot stored with a confirmation. |
| `classes/local/acknowledgement.php:133` | `mod_videotrack\local\acknowledgement::has_reached_video_end` | Return whether persisted tracking proves that the final video second was reached. |
| `classes/local/acknowledgement.php:156` | `mod_videotrack\local\acknowledgement::can_confirm` | Return whether the user may submit the current statement now. |
| `classes/local/acknowledgement.php:172` | `mod_videotrack\local\acknowledgement::analytics_summary` | Build a privacy-safe Analytics summary from current confirmation records. |
| `classes/local/acknowledgement.php:232` | `mod_videotrack\local\acknowledgement::current_record` | Return the current confirmation record for a user, when present. |
| `classes/local/acknowledgement.php:256` | `mod_videotrack\local\acknowledgement::confirm` | Record the user's explicit confirmation of the current statement. |
| `classes/local/acknowledgement.php:316` | `mod_videotrack\local\acknowledgement::current_records` | Return current confirmations keyed by user id. |
| `classes/local/analytics.php:48` | `mod_videotrack\local\analytics::resolve_duration` | Resolves the best known duration from instance, aggregate state and segments. |
| `classes/local/analytics.php:66` | `mod_videotrack\local\analytics::default_bin_size` | Chooses a useful default granularity for a video duration. |
| `classes/local/analytics.php:93` | `mod_videotrack\local\analytics::restrict_to_own_groups` | Determines whether analytics must be restricted to the viewer's own groups. |
| `classes/local/analytics.php:104` | `mod_videotrack\local\analytics::normalise_bin_size` | Validates a requested bin size and automatically enforces MAX_BINS. |
| `classes/local/analytics.php:132` | `mod_videotrack\local\analytics::build` | Builds unique-view and replay metrics from raw segment records. |
| `classes/local/analytics.php:235` | `mod_videotrack\local\analytics::build_from_states` | Builds unique-view analytics from canonical aggregate state records. |
| `classes/local/analytics.php:273` | `mod_videotrack\local\analytics::apply_privacy_threshold` | Masks small datasets and bins according to the configured privacy threshold. |
| `classes/local/analytics.php:333` | `mod_videotrack\local\analytics::count_summary` | Applies a distinct-user privacy threshold to an aggregate event count. |
| `classes/local/analytics.php:356` | `mod_videotrack\local\analytics::reaction_summary` | Applies the distinct-user privacy threshold to an overall reaction summary. |
| `classes/local/analytics.php:368` | `mod_videotrack\local\analytics::cluster_reactions` | Clusters reaction events for an optional privacy-safe timeline overlay. |
| `classes/local/analytics.php:429` | `mod_videotrack\local\analytics::append_visible_reaction_cluster` | Finalises one reaction cluster and retains it when it is privacy-safe. |
| `classes/local/analytics.php:458` | `mod_videotrack\local\analytics::add_user_intervals` | Adds one user's raw and merged intervals to global bins. |
| `classes/local/analytics.php:494` | `mod_videotrack\local\analytics::add_interval_to_map` | Distributes one interval across timeline bins. |
| `classes/local/analytics_scope.php:42` | `mod_videotrack\local\analytics_scope::matching_accessible_instances` | Returns matching VideoTrack activities for which the user can view reports. |
| `classes/local/analytics_scope.php:144` | `mod_videotrack\local\analytics_scope::effective_groupmode` | Returns the effective Moodle group mode for one Analytics scope. |
| `classes/local/analytics_scope.php:175` | `mod_videotrack\local\analytics_scope::accessible_group_ids` | Returns the group ids whose learners are visible to a report viewer. |
| `classes/local/analytics_scope.php:210` | `mod_videotrack\local\analytics_scope::technical_identity` | Resolves the stable technical identity of one configured video. |
| `classes/local/analytics_scope.php:261` | `mod_videotrack\local\analytics_scope::normalise_external_url` | Normalises an external media URL for technical-identity comparisons. |
| `classes/local/analytics_scope.php:294` | `mod_videotrack\local\analytics_scope::get_instance_record` | Loads one instance together with course-module fields needed by Analytics. |
| `classes/local/analytics_table_export.php:35` | `mod_videotrack\local\analytics_table_export::enabled_formats` | Returns supported formats that are enabled in the current Moodle site. |
| `classes/local/analytics_table_export.php:46` | `mod_videotrack\local\analytics_table_export::columns` | Returns localised export column headings. |
| `classes/local/analytics_table_export.php:72` | `mod_videotrack\local\analytics_table_export::export_columns` | Returns columns for the combined Analytics download. |
| `classes/local/analytics_table_export.php:105` | `mod_videotrack\local\analytics_table_export::rows` | Builds rows equivalent to the accessible HTML table. |
| `classes/local/analytics_table_export.php:178` | `mod_videotrack\local\analytics_table_export::export_rows` | Builds a combined timeline and acknowledgement export. |
| `classes/local/course_analytics.php:46` | `mod_videotrack\local\course_analytics::get_course_rows` | Builds one dashboard row for every visible VideoTrack activity in a course. |
| `classes/local/course_analytics.php:169` | `mod_videotrack\local\course_analytics::summarise_states` | Summarises state rows using the same timeline analytics service as report.php. |
| `classes/local/course_analytics.php:220` | `mod_videotrack\local\course_analytics::median` | Calculates the median of a numeric list. |
| `classes/local/course_analytics.php:240` | `mod_videotrack\local\course_analytics::largest_adjacent_drop` | Finds the largest privacy-safe retention decrease between adjacent bins. |
| `classes/local/course_analytics.php:274` | `mod_videotrack\local\course_analytics::learner_scope_sql` | Builds the SQL condition for active enrolled participants in the permitted groups. |
| `classes/local/course_analytics.php:302` | `mod_videotrack\local\course_analytics::load_states` | Loads aggregate state rows for one activity and learner scope. |
| `classes/local/course_analytics.php:341` | `mod_videotrack\local\course_analytics::load_event_summary` | Loads a privacy-safe reaction, note or bookmark count for one activity. |
| `classes/local/csv_export.php:78` | `mod_videotrack\local\csv_export::delimiter_options` | Returns delimiter options stored as symbolic values. |
| `classes/local/csv_export.php:97` | `mod_videotrack\local\csv_export::delimiter` | Resolves the actual one-character delimiter for an activity. |
| `classes/local/csv_export.php:121` | `mod_videotrack\local\csv_export::field_options` | Returns all configurable export fields. |
| `classes/local/csv_export.php:169` | `mod_videotrack\local\csv_export::form_field_options` | Returns the export-field choices shown in an activity form. |
| `classes/local/csv_export.php:195` | `mod_videotrack\local\csv_export::site_default_fields` | Returns the site default list of optional export fields. |
| `classes/local/csv_export.php:210` | `mod_videotrack\local\csv_export::activity_fields` | Resolves activity fields, applying Moodle identity permissions. |
| `classes/local/csv_export.php:225` | `mod_videotrack\local\csv_export::form_element_name` | Returns the deterministic form element name for an export field. |
| `classes/local/csv_export.php:236` | `mod_videotrack\local\csv_export::process_form_fields` | Aggregates submitted export-field checkboxes and removes helper fields. |
| `classes/local/csv_export.php:256` | `mod_videotrack\local\csv_export::selected_user_fields` | Returns only selected user-table/custom-profile keys. |
| `classes/local/csv_export.php:269` | `mod_videotrack\local\csv_export::load_users` | Loads users and selected visible identity fields in one query. |
| `classes/local/csv_export.php:303` | `mod_videotrack\local\csv_export::identity_headers` | Returns localised headers preceding event/state-specific columns. |
| `classes/local/csv_export.php:332` | `mod_videotrack\local\csv_export::identity_values` | Returns values matching identity_headers(). |
| `classes/local/csv_export.php:372` | `mod_videotrack\local\csv_export::cluster_notes` | Groups note events into time clusters for the overall CSV export. |
| `classes/local/csv_export.php:422` | `mod_videotrack\local\csv_export::write_utf8_bom` | Writes a UTF-8 byte-order mark for spreadsheet applications. |
| `classes/local/csv_export.php:434` | `mod_videotrack\local\csv_export::write_row` | Writes a CSV row using an explicit escape parameter for PHP 8.4 compatibility. |
| `classes/local/csv_export.php:459` | `mod_videotrack\local\csv_export::safe_value` | Protects spreadsheet consumers from formula injection. |
| `classes/local/csv_export.php:476` | `mod_videotrack\local\csv_export::normalise_field_list` | Normalises a comma-separated field list. |
| `classes/local/csv_export.php:490` | `mod_videotrack\local\csv_export::field_label` | Returns a field label. |
| `classes/local/csv_export.php:507` | `mod_videotrack\local\csv_export::field_value` | Returns one configured field value. |
| `classes/local/csv_export.php:543` | `mod_videotrack\local\csv_export::video_url` | Returns the configured video URL for CSV exports. |
| `classes/local/forum_bridge.php:47` | `mod_videotrack\local\forum_bridge::resolve_destination` | Resolves and validates the configured forum for the current user. |
| `classes/local/forum_bridge.php:99` | `mod_videotrack\local\forum_bridge::get_group_options` | Returns the groups in which the current user can create a discussion. |
| `classes/local/forum_bridge.php:135` | `mod_videotrack\local\forum_bridge::can_choose_subscription` | Indicates whether the student can choose discussion subscription. |
| `classes/local/forum_bridge.php:154` | `mod_videotrack\local\forum_bridge::create_discussion` | Creates a new Forum discussion through the official external API. |
| `classes/local/integrity.php:78` | `mod_videotrack\local\integrity::normalise_random_pause_bounds` | Normalise the site-level random attention-pause bounds. |
| `classes/local/integrity.php:100` | `mod_videotrack\local\integrity::random_pause_bounds` | Return the effective site-level random attention-pause bounds. |
| `classes/local/integrity.php:114` | `mod_videotrack\local\integrity::focus_loss_policy` | Return the effective site-level focus-loss policy. |
| `classes/local/integrity.php:126` | `mod_videotrack\local\integrity::focus_loss_grace_seconds` | Return the site-level grace period for strict window-focus handling. |
| `classes/local/integrity.php:139` | `mod_videotrack\local\integrity::validate_event_type` | Validate a client-supplied signal type. |
| `classes/local/integrity.php:152` | `mod_videotrack\local\integrity::label_string` | Return the language string identifier for one signal type. |
| `classes/local/integrity.php:164` | `mod_videotrack\local\integrity::summarise` | Build a privacy-safe summary from raw grouped rows. |
| `classes/local/learner_scope.php:41` | `mod_videotrack\local\learner_scope::sql` | Build canonical learner SQL scoped by explicit participation, enrolment and groups. |
| `classes/local/learner_scope.php:94` | `mod_videotrack\local\learner_scope::prefix_named_params` | Prefix named SQL parameters so multi-activity learner scopes cannot collide. |
| `classes/local/learner_scope.php:119` | `mod_videotrack\local\learner_scope::user_is_visible` | Check whether one learner is visible to the current report viewer. |
| `classes/local/privacy_manager.php:49` | `mod_videotrack\local\privacy_manager::retention_period_seconds` | Returns the configured retention period in seconds. |
| `classes/local/privacy_manager.php:67` | `mod_videotrack\local\privacy_manager::anonymisation_salt` | Returns the local anonymisation salt, creating it on first use. |
| `classes/local/privacy_manager.php:119` | `mod_videotrack\local\privacy_manager::anonymous_userid` | Builds a stable negative user id with collisions that are extremely unlikely in normal Moodle use. |
| `classes/local/privacy_manager.php:133` | `mod_videotrack\local\privacy_manager::anonymous_sessionid` | Builds a deterministic non-identifying session id. |
| `classes/local/privacy_manager.php:147` | `mod_videotrack\local\privacy_manager::delete_user_data_in_context` | Permanently deletes all personal tracking records for one user in one module context. |
| `classes/local/privacy_manager.php:174` | `mod_videotrack\local\privacy_manager::delete_all_user_data_in_context` | Permanently deletes all personal tracking records in one module context. |
| `classes/local/privacy_manager.php:209` | `mod_videotrack\local\privacy_manager::anonymise_user_in_context` | Anonymises all personal tracking records for one user in one module context. |
| `classes/local/privacy_manager.php:225` | `mod_videotrack\local\privacy_manager::anonymise_all_users_in_context` | Anonymises all real users' tracking records in one module context. |
| `classes/local/privacy_manager.php:276` | `mod_videotrack\local\privacy_manager::anonymise_user_records` | Anonymises one user's records for a course module. |
| `classes/local/privacy_manager.php:352` | `mod_videotrack\local\privacy_manager::anonymise_expired_records` | Anonymises old records according to the configured retention period. |
| `classes/local/privacy_manager.php:436` | `mod_videotrack\local\privacy_manager::anonymise_old_user_rows` | Anonymises old rows for one user/module pair. |
| `classes/local/privacy_manager.php:541` | `mod_videotrack\local\privacy_manager::anonymise_state_rows` | Anonymises state rows and safely merges with an existing anonymous state row. |
| `classes/local/privacy_manager.php:562` | `mod_videotrack\local\privacy_manager::anonymise_one_state_row` | Anonymises a single state row, merging on unique-key collision. |
| `classes/local/privacy_manager.php:599` | `mod_videotrack\local\privacy_manager::merge_interval_json` | Merges two JSON interval lists. |
| `classes/local/teacher_analytics.php:36` | `mod_videotrack\local\teacher_analytics::accessible_courses` | Returns courses where the user can view the VideoTrack course report. |
| `classes/local/teacher_analytics.php:72` | `mod_videotrack\local\teacher_analytics::dashboard_rows` | Builds all accessible dashboard rows using the course analytics service. |
| `classes/local/teacher_analytics.php:119` | `mod_videotrack\local\teacher_analytics::activity_options` | Returns accessible VideoTrack activities for one selected course. |
| `classes/local/teacher_analytics.php:141` | `mod_videotrack\local\teacher_analytics::group_options` | Returns groups the report viewer may use as a filter in one course. |
| `classes/local/teacher_analytics.php:165` | `mod_videotrack\local\teacher_analytics::period_bounds` | Converts a relative period into inclusive timestamp bounds. |
| `classes/local/timed_text.php:44` | `mod_videotrack\local\timed_text::file_options` | Return Moodle draft-area options for WebVTT files. |
| `classes/local/timed_text.php:59` | `mod_videotrack\local\timed_text::save_files` | Save transcript and chapter draft areas for an activity. |
| `classes/local/timed_text.php:97` | `mod_videotrack\local\timed_text::transcript_tracks` | Return transcript tracks, falling back to the legacy subtitle file. |
| `classes/local/timed_text.php:133` | `mod_videotrack\local\timed_text::chapter_source` | Return the dedicated chapter source or a legacy subtitle fallback. |
| `classes/local/timed_text.php:158` | `mod_videotrack\local\timed_text::language_from_filename` | Extract a BCP-47-like language code from a WebVTT filename. |
| `classes/local/timed_text.php:176` | `mod_videotrack\local\timed_text::is_valid_vtt_content` | Validate the beginning and size of a WebVTT payload. |
| `classes/local/timed_text.php:191` | `mod_videotrack\local\timed_text::area_files` | Return non-directory files from one activity file area. |
| `classes/local/timed_text.php:211` | `mod_videotrack\local\timed_text::file_url` | Build a pluginfile URL for a stored timed-text file. |
| `classes/local/timed_text.php:229` | `mod_videotrack\local\timed_text::language_label` | Return a readable language or file label. |
| `classes/local/tracker.php:56` | `mod_videotrack\local\tracker::current_state_snapshot` | Returns the current persisted state, or a safe in-memory default when no state row exists yet. Used as a non-fatal fallback when the per-user state lock is temporarily contended. |
| `classes/local/tracker.php:75` | `mod_videotrack\local\tracker::normalise_interval` | Normalises a playback interval and rejects empty or invalid ranges. |
| `classes/local/tracker.php:95` | `mod_videotrack\local\tracker::decode_intervals` | Decodes a JSON list of intervals into safe normalised intervals. |
| `classes/local/tracker.php:128` | `mod_videotrack\local\tracker::encode_intervals` | Encodes intervals for persistence in videotrack_state.intervaljson. |
| `classes/local/tracker.php:138` | `mod_videotrack\local\tracker::merge_intervals` | Merges overlapping intervals while preserving watched coverage. |
| `classes/local/tracker.php:165` | `mod_videotrack\local\tracker::cap_intervals` | If the number of intervals exceeds MAX_INTERVALS, keep the longest intervals and discard smaller fragments. This avoids inventing watched coverage by merging unseen gaps, at the cost of controlled precision loss in extreme cases. |
| `classes/local/tracker.php:188` | `mod_videotrack\local\tracker::simplify_intervals` | Reduce the interval array to the target count without merging unseen gaps. |
| `classes/local/tracker.php:211` | `mod_videotrack\local\tracker::covered_seconds` | Calculates the total covered seconds represented by interval ranges. |
| `classes/local/tracker.php:226` | `mod_videotrack\local\tracker::reaction_counts` | Returns cached reaction counters for one user/activity pair. |
| `classes/local/tracker.php:268` | `mod_videotrack\local\tracker::invalidate_reactioncountscache` | Invalidates the per-request cache for reaction_counts. Must be called after any insert or soft-delete on videotrack_reactev to ensure subsequent calls within the same request see fresh data. |
| `classes/local/tracker.php:287` | `mod_videotrack\local\tracker::has_recent_playback` | Checks whether a timestamp is backed by a recent validated playback heartbeat; retained for diagnostics and compatibility. |
| `classes/local/tracker.php:385` | `mod_videotrack\local\tracker::has_watched_videotime` | Returns true when the requested video time is inside a watched segment. |
| `classes/local/tracker.php:475` | `mod_videotrack\local\tracker::completion_satisfied` | Evaluates the custom VideoTrack completion rules. |
| `classes/local/tracker.php:532` | `mod_videotrack\local\tracker::create_default_state` | Creates the default aggregate state record for a user/activity pair. |
| `classes/local/tracker.php:568` | `mod_videotrack\local\tracker::advance_server_credit_budget` | Advance the persisted cumulative server-time playback-credit budget and reject over-credit candidates. |
| `classes/local/tracker.php:609` | `mod_videotrack\local\tracker::forward_interval_allowed` | Enforce the server-known watched frontier when forward seeking is disabled. |
| `classes/local/tracker.php:641` | `mod_videotrack\local\tracker::update_state` | Update the aggregated viewing state for one user. |
| `classes/local/tracker.php:791` | `mod_videotrack\local\tracker::update_moodle_completion_if_changed` | Synchronises Moodle completion only when the persisted completion state differs from the computed VideoTrack state. This avoids redundant writes on every heartbeat/reaction while preserving normal completion semantics. |
| `classes/local/tracker.php:815` | `mod_videotrack\local\tracker::aggregate_segments` | Aggregates persisted raw segments without trusting an existing state snapshot. |
| `classes/local/tracker.php:854` | `mod_videotrack\local\tracker::rebuild_state_from_segments` | Rebuilds one user's aggregate state from raw segment rows and reaction events. |
| `classes/local/tracker.php:945` | `mod_videotrack\local\tracker::refresh_completion` | Recomputes and persists completion state for one user/activity pair. |
| `classes/privacy/provider.php:48` | `mod_videotrack\privacy\provider::format_interval_second` | Formats a number of seconds for human-readable privacy exports. |
| `classes/privacy/provider.php:59` | `mod_videotrack\privacy\provider::get_metadata` | Describes the personal data stored by this plugin. |
| `classes/privacy/provider.php:165` | `mod_videotrack\privacy\provider::get_contexts_for_userid` | Returns contexts that contain user information for the specified user. |
| `classes/privacy/provider.php:204` | `mod_videotrack\privacy\provider::get_users_in_context` | Adds users with data in the supplied context to the user list. |
| `classes/privacy/provider.php:237` | `mod_videotrack\privacy\provider::export_user_data` | Exports personal data for the approved context list. |
| `classes/privacy/provider.php:590` | `mod_videotrack\privacy\provider::delete_data_for_all_users_in_context` | Deletes all plugin-owned user data for a module context. |
| `classes/privacy/provider.php:603` | `mod_videotrack\privacy\provider::delete_data_for_user` | Deletes personal data for the approved context list. |
| `classes/privacy/provider.php:615` | `mod_videotrack\privacy\provider::delete_data_for_users` | Deletes personal data for users in the supplied user list. |
| `classes/privacy/provider.php:628` | `mod_videotrack\privacy\provider::delete_records_for_users_in_context` | Deletes user records for GDPR erasure requests. |
| `classes/task/cleanup_task.php:38` | `mod_videotrack\task\cleanup_task::get_name` | Returns the task name. |
| `classes/task/cleanup_task.php:45` | `mod_videotrack\task\cleanup_task::execute` | Executes the retention task. |
| `db/upgrade.php:35` | `xmldb_videotrack_upgrade` | Upgrade script for mod_videotrack. |
| `lib.php:37` | `videotrack_supports` | Returns the Moodle features supported by the activity module. |
| `lib.php:69` | `videotrack_whitelist_record` | Returns an object containing only fields from the {videotrack} table. Prevents raw form data (including extra fields such as videofile, posterimage, reactionlabel_, etc.) from being passed directly to insert/update_record, which would otherwise trigger DB exceptions for non-existent columns. |
| `lib.php:94` | `videotrack_add_instance` | Adds a new VideoTrack activity instance. |
| `lib.php:145` | `videotrack_update_instance` | Updates an existing videotrack activity instance. |
| `lib.php:194` | `videotrack_process_forum_fields` | Normalises and validates optional Forum integration fields before a database write. |
| `lib.php:213` | `videotrack_process_acknowledgement_fields` | Normalises the optional learner acknowledgement fields. |
| `lib.php:240` | `videotrack_process_video_fields` | Normalises video-source-specific fields before DB write. Sets videosource, videoid, videourl appropriately. |
| `lib.php:282` | `videotrack_process_playbackspeeds_field` | Normalises the playbackspeeds field submitted from mod_form checkboxes. |
| `lib.php:311` | `videotrack_save_uploaded_video` | Saves the uploaded video file into the mod_videotrack filearea. |
| `lib.php:332` | `videotrack_delete_upload_source_files` | Deletes upload-only files when an activity is no longer using the upload source. |
| `lib.php:349` | `videotrack_get_upload_url` | Returns the URL of the uploaded video file for an instance, or null if none. |
| `lib.php:377` | `videotrack_get_module_context_from_data` | Resolves the context_module for a form data object during instance save. Checks coursemodule, cmid and falls back to get_coursemodule_from_instance. |
| `lib.php:400` | `videotrack_save_poster_image` | Saves the uploaded poster/preview image into the 'posterimage' filearea. Called from add_instance and update_instance. |
| `lib.php:433` | `videotrack_is_valid_reaction_icon_class` | Validates the Font Awesome class list used for reaction icons. |
| `lib.php:468` | `videotrack_save_reaction_definitions` | Saves the configured reaction definitions for a VideoTrack activity. |
| `lib.php:613` | `videotrack_user_outline` | Returns a summary of a student's viewing progress for the activity outline. Shown in the "Activity report" page of the participant profile. |
| `lib.php:642` | `videotrack_user_complete` | Prints a detailed view of a student's watching history for the activity. Shown in the "Activity report" page of the participant profile. |
| `lib.php:683` | `videotrack_extend_settings_navigation` | Adds a "Report" link to the activity's settings navigation (secondary nav). |
| `lib.php:713` | `videotrack_extend_navigation_course` | Adds a "Video track reports" link to the course reports navigation node. |
| `lib.php:746` | `videotrack_get_html5controls` | Returns the effective list of HTML5 player controls for an activity. Instance setting overrides site default when present. |
| `lib.php:762` | `videotrack_process_html5controls_field` | Normalises html5controls field: aggregates html5ctrl_* checkbox fields into a comma-separated string in $data->html5controls. |
| `lib.php:793` | `videotrack_process_player_behavior_fields` | Normalises autoplay/loop/startmuted/allowdownload boolean fields. |
| `lib.php:830` | `videotrack_get_player_width` | Returns the effective max player width in px. |
| `lib.php:845` | `videotrack_get_rewind_step` | Returns effective rewind step in seconds (instance override → site default → 10). |
| `lib.php:866` | `videotrack_get_fastforward_step` | Returns effective fast-forward step in seconds (instance override → site default → 10). |
| `lib.php:887` | `videotrack_get_vtt_url` | Returns the URL of the VTT subtitle file for an upload instance, or null. |
| `lib.php:910` | `videotrack_process_captions_fields` | Normalises captions fields from form data. |
| `lib.php:953` | `videotrack_process_grade_fields` | Normalises the grade-related fields submitted by mod_form before DB insert/update. Moodle's standard grading elements submit 'grade' as a signed integer: 0   = no grade >0   = numeric max points <0   = -(scale_id) gradepass is submitted separately and must be stored alongside grade. |
| `lib.php:981` | `videotrack_grade_item_update` | Creates or updates the grade item in the Moodle gradebook for this activity. |
| `lib.php:1039` | `videotrack_update_grades` | Updates all grades for this activity in the Moodle gradebook. |
| `lib.php:1051` | `videotrack_set_user_grade` | Pushes a single user grade to the Moodle gradebook. |
| `lib.php:1068` | `videotrack_get_user_grade` | Returns a user's current raw grade for this activity, or null if not graded. |
| `lib.php:1093` | `videotrack_get_poster_url` | Returns the URL of the poster/preview image for this instance, or null if not set. The image is stored in the filearea 'posterimage' with itemid=0. |
| `lib.php:1133` | `videotrack_delete_user_progress` | Deletes all plugin-owned VideoTrack data for one user in an activity. |
| `lib.php:1165` | `videotrack_grade_item_delete` | Removes the grade item from the gradebook when the activity is deleted. |
| `lib.php:1186` | `videotrack_delete_instance` | Deletes a VideoTrack activity instance and related records. |
| `lib.php:1242` | `videotrack_get_coursemodule_info` | Returns cached course-module information for the activity. |
| `lib.php:1263` | `videotrack_view` | Registers an activity view and updates view-based completion. |
| `lib.php:1282` | `videotrack_get_completion_active_rule_descriptions` | Returns active custom completion rule descriptions for the activity. |
| `lib.php:1320` | `videotrack_update_completion_for_user` | Recalculates completion for a specific user. |
| `lib.php:1336` | `videotrack_reset_course_userdata` | Returns the items that can be reset in a course reset. Called by Moodle when building the course reset form. |
| `lib.php:1385` | `videotrack_reset_course_form_definition` | Populates the course reset form with videotrack-specific options. Moodle HQ hook: called when building the course reset form. |
| `lib.php:1405` | `videotrack_reset_course_form_defaults` | Returns default values for the VideoTrack course reset form options. |
| `lib.php:1421` | `videotrack_resize_reaction_icon` | Resize the reaction icon to 64x64px using a centred crop. |
| `lib.php:1516` | `videotrack_pluginfile` | Serves files from the reactionicon filearea. |
| `lib.php:1625` | `videotrack_recalculate_all_states` | Recalculate the aggregate state (completionpercent, iscompleted) for all users in a single VideoTrack instance from raw segments. Useful after changing the video duration or completion criteria. |
| `locallib.php:38` | `videotrack_get_config_int` | Reads an integer mod_videotrack configuration value while preserving explicit zero values. |
| `locallib.php:57` | `videotrack_extract_videoid` | Extracts the 11-character YouTube video ID from a URL. |
| `locallib.php:108` | `videotrack_extract_vimeo_id` | Extracts the Vimeo numeric video ID from a Vimeo URL. |
| `locallib.php:148` | `videotrack_get_playback_speeds` | Returns the effective list of allowed playback speeds for an activity. If the instance has its own playbackspeeds, those override the site default. Speeds above the site maxplaybackrate are filtered out. |
| `locallib.php:196` | `videotrack_get_max_playback_rate` | Returns the site-wide maximum playback rate cap (0 = no limit). |
| `locallib.php:209` | `videotrack_get_site_playback_speeds` | Returns the site-wide available playback speeds as configured by the admin. |
| `locallib.php:228` | `videotrack_format_seconds` | Formats a number of seconds into a human-readable MM:SS or H:MM:SS string. |
| `locallib.php:246` | `videotrack_format_video_timestamp` | Formats a video timestamp using the video's total duration to select MM:SS or HH:MM:SS. |
| `locallib.php:266` | `videotrack_parse_video_timestamp` | Parses seconds, MM:SS or HH:MM:SS into a non-negative number of seconds. |
| `locallib.php:306` | `videotrack_parse_report_timestamp` | Parses a report filter time in MM:SS or HH:MM:SS format. |
| `locallib.php:326` | `videotrack_build_required_reaction_notice` | Builds a human-readable notice string describing the reaction requirements for this activity. Used as the default reaction notice when the teacher has not written a custom one. |
| `locallib.php:348` | `videotrack_get_reactions` | Returns all reaction definitions for a videotrack instance, sorted by sortorder. Results are statically cached within the request to avoid duplicate DB queries when both the reaction buttons and the reaction table need the same data. |
| `locallib.php:375` | `videotrack_reaction_icon_url` | Returns a pluginfile URL for a stored reaction icon. |
| `locallib.php:416` | `videotrack_render_reaction_icon` | Renders a reaction icon with an optional accessible label. |
| `locallib.php:463` | `videotrack_get_fallback_reaction_emoji_catalog` | Returns the curated fallback emoji catalogue used when Moodle/TinyMCE data is unavailable. |
| `locallib.php:497` | `videotrack_get_moodle_reaction_emoji_catalog` | Returns the full Moodle/TinyMCE emoji catalogue when available. |
| `locallib.php:549` | `videotrack_get_reaction_icon_catalog` | Returns reaction icon values grouped by type and theme. |
| `locallib.php:595` | `videotrack_get_reaction_icon_suggestions` | Returns common icon values suggested for reaction icons. |
| `locallib.php:613` | `videotrack_reaction_icon_datalist` | Builds a HTML datalist for reaction icon values. |
| `locallib.php:640` | `videotrack_reaction_icon_picker` | Builds an accessible visual picker for reaction icon values. |
| `locallib.php:761` | `videotrack_get_all_presets` | Returns all reaction presets stored in config as a keyed array. |
| `locallib.php:775` | `videotrack_save_presets` | Saves the full presets array back to config. |
| `locallib.php:785` | `videotrack_get_preset_select_options` | Returns presets formatted for a Moodle select element. First option is always the empty "manual configuration" choice. |
| `locallib.php:800` | `videotrack_get_all_presets_for_js` | Returns all presets as a flat array keyed by preset key, for the JS client. |
| `locallib.php:816` | `videotrack_require_preset_amd` | Registers the AMD preset selector module for the mod_form page. Called from mod_form.php definition() after the preset select element is added. |
| `locallib.php:832` | `videotrack_optional_iso_date_param` | Reads an optional ISO date (YYYY-MM-DD) report filter safely. |
| `locallib.php:843` | `videotrack_get_compatible_forum_types` | Returns forum types that can receive repeated student discussions from VideoTrack. |
| `locallib.php:853` | `videotrack_get_compatible_forums` | Returns compatible Forum instances from one course, including module status metadata. |
| `locallib.php:880` | `videotrack_get_compatible_forum_options` | Builds form options for compatible forums in a course. |
| `locallib.php:907` | `videotrack_is_compatible_forum` | Validates that a forum is a compatible destination in the given course. |
| `locallib.php:923` | `videotrack_build_forum_subject` | Builds the default Forum discussion subject from the configured template. |
| `locallib.php:952` | `videotrack_build_replay_url` | Builds the canonical replay URL for a timestamp and symmetric pre-roll window. |
| `mod_form.php:46` | `mod_videotrack_mod_form::definition` | Defines the activity settings form. |
| `mod_form.php:1086` | `mod_videotrack_mod_form::apply_default_section_expansion` | Applies the default collapsed state to the instance configuration sections. |
| `mod_form.php:1123` | `mod_videotrack_mod_form::require_filepicker_accept_filter` | Adds a client-side accept attribute to repository upload inputs. |
| `mod_form.php:1169` | `mod_videotrack_mod_form::add_reaction_elements` | Adds repeated form elements used to configure reaction buttons. |
| `mod_form.php:1348` | `mod_videotrack_mod_form::get_reaction_repeat_count` | Returns the number of reaction rows to render in the form. |
| `mod_form.php:1375` | `mod_videotrack_mod_form::add_completion_rules` | Adds VideoTrack-specific completion rules. |
| `mod_form.php:1418` | `mod_videotrack_mod_form::completion_rule_enabled` | Checks whether the custom completion rule is enabled. |
| `mod_form.php:1432` | `mod_videotrack_mod_form::data_preprocessing` | Prepares default values and draft areas before the form is displayed. |
| `mod_form.php:1682` | `mod_videotrack_mod_form::draft_area_contains_only_reaction_images` | Checks that a reaction icon draft area contains only allowed image files. |
| `mod_form.php:1718` | `mod_videotrack_mod_form::draft_area_contains_only_vtt` | Check that a draft area contains only valid WebVTT files. |
| `mod_form.php:1750` | `mod_videotrack_mod_form::validation` | Validates submitted activity settings. |
| `report.php:36` | `videotrack_report_user_label` | Formats a report user label without exposing anonymised pseudo-user ids. |
| `report.php:54` | `videotrack_report_date_to_timestamp` | Converts an ISO date-only parameter to a timestamp in the user's timezone. |
| `report.php:76` | `videotrack_report_optional_time_param` | Reads an optional video-time filter. |
| `report.php:128` | `videotrack_report_duration_filter` | Renders a structured duration filter using number inputs. |
| `report.php:191` | `videotrack_report_analytics_scope_condition` | Builds a capability-safe SQL condition for one or more Analytics activities. |
| `report.php:258` | `videotrack_report_acknowledgement_scope_condition` | Builds a capability-safe SQL condition for current acknowledgement versions. |
| `report.php:326` | `videotrack_report_tabs` | Builds the report tab set. |
| `report.php:369` | `videotrack_report_analytics_interval` | Formats a timeline interval for analytics reports. |
| `report.php:383` | `videotrack_report_render_analytics_heatmap` | Renders the unique-view heatmap with optional reaction-cluster markers. |
| `report.php:515` | `videotrack_report_render_analytics_methodology` | Renders the expandable explanation of analytics calculations and privacy. |
| `report.php:575` | `videotrack_report_render_privacy_alert` | Renders one privacy warning only when a dataset cannot be displayed. |
| `report.php:602` | `videotrack_report_render_heatmap_legend` | Renders a legend explaining heatmap intervals, intensity and markers. |
| `report.php:647` | `videotrack_report_render_analytics_download` | Renders the analytics table download selector. |
| `report.php:704` | `videotrack_report_render_reaction_clusters` | Render privacy-safe reaction clusters independently from viewing analytics. |
| `report.php:739` | `videotrack_report_render_reaction_summary` | Renders a privacy-safe overall reaction summary. |
| `report.php:762` | `videotrack_report_render_bookmark_summary` | Renders a privacy-safe bookmark usage summary without exposing labels or timestamps. |
| `report.php:828` | `videotrack_report_render_acknowledgement_summary` | Renders privacy-safe acknowledgement Analytics. |
| `report.php:937` | `videotrack_report_render_integrity_summary` | Renders privacy-safe diagnostic integrity indicators. |
| `report.php:1028` | `videotrack_report_render_analytics_retention` | Renders the retention line chart. |
| `reports_course.php:35` | `videotrack_course_report_count_cell` | Renders a privacy-safe aggregate count. |
| `reports_course.php:58` | `videotrack_course_report_percentage_cell` | Renders an aggregate percentage, preserving privacy suppression. |
| `reports_course.php:99` | `videotrack_course_report_drop_cell` | Renders the largest adjacent retention decrease. |
| `tests/acknowledgement_test.php:36` | `mod_videotrack\acknowledgement_test::test_statement_hash_versions_the_statement_content` | Statement identity changes when content, format or the end-gating policy changes. |
| `tests/acknowledgement_test.php:58` | `mod_videotrack\acknowledgement_test::test_video_end_requirement_uses_persisted_intervals` | End-gated confirmation requires persisted tracking to reach the final second. |
| `tests/acknowledgement_test.php:83` | `mod_videotrack\acknowledgement_test::test_progress_snapshot_uses_unique_coverage` | Confirmation snapshots preserve unique viewed time and percentage at that moment. |
| `tests/acknowledgement_test.php:101` | `mod_videotrack\acknowledgement_test::test_video_end_requires_teacher_configured_duration` | Historical state/client duration cannot unlock end-gated acknowledgement without teacher-configured verified duration. |
| `tests/acknowledgement_test.php:126` | `mod_videotrack\acknowledgement_test::test_enabled_state_requires_nonempty_visible_text` | Empty or disabled statements are never offered for confirmation. |
| `tests/acknowledgement_test.php:146` | `mod_videotrack\acknowledgement_test::test_analytics_summary_preserves_legacy_and_privacy_rules` | Analytics summary averages available snapshots and masks small populations. |
| `tests/admin_settings_test.php:40` | `mod_videotrack\admin_settings_test::setUp` | Load Moodle admin setting base classes before instantiating plugin settings. |
| `tests/admin_settings_test.php:50` | `mod_videotrack\admin_settings_test::test_nonnegative_int_validation_accepts_zero_and_rejects_invalid_values` | Non-negative integer settings accept zero and reject unsafe values. |
| `tests/admin_settings_test.php:63` | `mod_videotrack\admin_settings_test::test_int_range_validation_enforces_configured_bounds` | Range settings enforce both configured boundaries. |
| `tests/admin_settings_test.php:76` | `mod_videotrack\admin_settings_test::test_unlimited_retention_requires_explicit_confirmation` | Unlimited retention cannot be saved without explicit administrator confirmation. |
| `tests/ajax_contract_test.php:35` | `mod_videotrack\ajax_contract_test::test_ajax_service_allowlist_matches_declared_services` | Ensures every declared Moodle AJAX service is present in the client allowlist. |
| `tests/ajax_contract_test.php:64` | `mod_videotrack\ajax_contract_test::test_static_amd_api_calls_are_declared` | Ensures static AMD API calls target declared services. |
| `tests/ajax_contract_test.php:92` | `mod_videotrack\ajax_contract_test::test_sesskey_is_checked_before_context_loading` | Ensures mutation endpoints validate the sesskey before loading context data. |
| `tests/ajax_contract_test.php:118` | `mod_videotrack\ajax_contract_test::test_reaction_runtime_contract_contains_no_raw_html_field` | Ensures reaction responses and HTML5 rendering contain no raw-HTML icon field. |
| `tests/analytics_scope_test.php:36` | `mod_videotrack\analytics_scope_test::test_provider_identity_uses_exact_video_id` | Provider identities use the exact provider video id, not the activity name. |
| `tests/analytics_scope_test.php:55` | `mod_videotrack\analytics_scope_test::test_external_url_identity_is_normalised` | External URLs ignore fragments and normalise host, port and query ordering. |
| `tests/analytics_scope_test.php:69` | `mod_videotrack\analytics_scope_test::test_effective_groupmode_satisfies_moodle_course_module_contract` | Analytics scope descriptors satisfy Moodle's course-module group-mode contract. |
| `tests/analytics_table_export_test.php:34` | `mod_videotrack\analytics_table_export_test::setUp` | Load the shared duration-formatting helpers. |
| `tests/analytics_table_export_test.php:42` | `mod_videotrack\analytics_table_export_test::test_rows_match_accessible_table_privacy_rules` | Export rows preserve masking and optional reaction values. |
| `tests/analytics_table_export_test.php:81` | `mod_videotrack\analytics_table_export_test::test_rows_mark_unavailable_replay_metrics` | Aggregate-state fallback exports the replay status without fabricating values. |
| `tests/analytics_table_export_test.php:100` | `mod_videotrack\analytics_table_export_test::test_export_rows_include_acknowledgement_summary` | Combined exports append one privacy-safe acknowledgement summary row. |
| `tests/analytics_test.php:36` | `mod_videotrack\analytics_test::test_bin_size_is_normalised_for_duration` | Granularity uses supported values and caps the number of bins. |
| `tests/analytics_test.php:45` | `mod_videotrack\analytics_test::test_build_separates_unique_and_repeated_viewing` | Unique coverage and replay time are separated per user and bin. |
| `tests/analytics_test.php:72` | `mod_videotrack\analytics_test::test_privacy_threshold_masks_small_values` | Small datasets and small positive bins are hidden by the privacy threshold. |
| `tests/analytics_test.php:123` | `mod_videotrack\analytics_test::test_privacy_threshold_keeps_zero_intervals_visible` | Empty timeline intervals remain visible as zero and do not disclose a small subgroup. |
| `tests/analytics_test.php:152` | `mod_videotrack\analytics_test::test_privacy_threshold_keeps_total_when_only_replays_are_suppressed` | A replay-only suppressed subgroup does not hide the total-viewer denominator. |
| `tests/analytics_test.php:176` | `mod_videotrack\analytics_test::test_reaction_cluster_limit_is_reported` | The reaction overlay reports when its privacy-safe cluster limit is reached. |
| `tests/analytics_test.php:203` | `mod_videotrack\analytics_test::test_reaction_clusters_apply_student_threshold` | Reaction clusters are visible only when enough distinct students contribute. |
| `tests/analytics_test.php:227` | `mod_videotrack\analytics_test::test_reaction_clusters_use_stable_reaction_keys` | Cross-course clusters use the saved reaction key instead of local database ids. |
| `tests/analytics_test.php:280` | `mod_videotrack\analytics_test::test_reaction_privacy_is_independent_from_viewing_privacy` | Privacy-safe reaction clusters remain computable when viewing data is suppressed. |
| `tests/analytics_test.php:308` | `mod_videotrack\analytics_test::test_group_scope_restriction_uses_effective_activity_mode` | Course groups do not restrict analytics when the activity uses no groups. |
| `tests/analytics_test.php:318` | `mod_videotrack\analytics_test::test_reaction_summary_masks_small_populations` | Overall reaction counts are exposed only after the distinct-user threshold. |
| `tests/analytics_test.php:339` | `mod_videotrack\analytics_test::test_build_from_states_recovers_unique_viewers` | Aggregate state intervals recover unique-view analytics without raw replay data. |
| `tests/analytics_test.php:359` | `mod_videotrack\analytics_test::test_resolve_duration_uses_best_persisted_source` | Analytics can recover duration from aggregate state when the instance field is empty. |
| `tests/course_analytics_test.php:38` | `mod_videotrack\course_analytics_test::test_median_handles_common_dataset_shapes` | Median supports odd, even and empty datasets. |
| `tests/course_analytics_test.php:47` | `mod_videotrack\course_analytics_test::test_state_summary_reuses_timeline_analytics` | Course summaries expose mean, median, completion and the main visible drop. |
| `tests/course_analytics_test.php:71` | `mod_videotrack\course_analytics_test::test_state_summary_masks_small_activity_population` | A small activity population does not expose exact dashboard values. |
| `tests/course_analytics_test.php:92` | `mod_videotrack\course_analytics_test::test_state_summary_masks_small_completion_subgroups` | Completion subgroups remain masked even when the total population is visible. |
| `tests/course_analytics_test.php:113` | `mod_videotrack\course_analytics_test::test_largest_drop_ignores_suppressed_bins` | Suppressed timeline bins are excluded from main-drop calculations. |
| `tests/course_analytics_test.php:128` | `mod_videotrack\course_analytics_test::test_participation_scope_is_independent_from_report_access` | Explicit participation remains available to a learner who also has report access. |
| `tests/course_analytics_test.php:171` | `mod_videotrack\course_analytics_test::state` | Creates one aggregate state fixture. |
| `tests/csv_export_test.php:37` | `mod_videotrack\csv_export_test::test_delimiter_resolution` | Activity delimiter settings inherit or override the site default. |
| `tests/csv_export_test.php:60` | `mod_videotrack\csv_export_test::test_process_form_fields` | Submitted checkbox helpers are collapsed into the persisted field list. |
| `tests/csv_export_test.php:77` | `mod_videotrack\csv_export_test::test_field_options_include_video_link` | Site and activity configuration expose the video-link column. |
| `tests/csv_export_test.php:86` | `mod_videotrack\csv_export_test::test_identity_columns_split_lastname_and_firstname` | Identity columns export last name and first name separately. |
| `tests/csv_export_test.php:101` | `mod_videotrack\csv_export_test::test_cluster_notes_concatenates_comments_and_counts_students` | Overall exports concatenate notes inside their time cluster. |
| `tests/csv_export_test.php:123` | `mod_videotrack\csv_export_test::test_safe_value_blocks_formula_injection` | Spreadsheet formula prefixes are neutralised without changing normal values. |
| `tests/csv_export_test.php:133` | `mod_videotrack\csv_export_test::test_write_utf8_bom` | CSV output starts with the UTF-8 BOM expected by common spreadsheet applications. |
| `tests/csv_export_test.php:145` | `mod_videotrack\csv_export_test::test_write_row_supports_section_sign_delimiter` | Multibyte delimiters are written without relying on fputcsv single-byte limits. |
| `tests/forum_bridge_test.php:37` | `mod_videotrack\forum_bridge_test::setUp` | Loads global VideoTrack helpers required by the bridge. |
| `tests/forum_bridge_test.php:45` | `mod_videotrack\forum_bridge_test::test_disabled_integration_is_rejected` | A disabled integration fails before any Forum record is accessed. |
| `tests/forum_bridge_test.php:59` | `mod_videotrack\forum_bridge_test::test_enrolled_student_can_resolve_compatible_forum` | An enrolled student can resolve a compatible Forum in the same course. |
| `tests/integrity_test.php:36` | `mod_videotrack\integrity_test::test_event_type_validation_is_allowlist_based` | Supported event types are accepted and unknown values are rejected. |
| `tests/integrity_test.php:48` | `mod_videotrack\integrity_test::test_summary_applies_distinct_user_privacy_threshold` | Small contributing groups are hidden independently for every signal type. |
| `tests/integrity_test.php:66` | `mod_videotrack\integrity_test::test_random_pause_bounds_are_configurable_and_normalised` | Site-level random-pause bounds use the documented defaults and safe normalisation. |
| `tests/integrity_test.php:84` | `mod_videotrack\integrity_test::test_focus_policy_defaults_and_strict_override` | Site focus settings default to the accessibility-oriented policy. |
| `tests/lib_test.php:37` | `mod_videotrack\lib_test::setUp` | Load module callbacks under test. |
| `tests/lib_test.php:47` | `mod_videotrack\lib_test::test_supports_expected_core_features` | Basic supported feature flags should remain stable across refactors. |
| `tests/lib_test.php:61` | `mod_videotrack\lib_test::test_groups_are_explicitly_not_supported` | Group features are intentionally disabled for this activity. |
| `tests/lib_test.php:71` | `mod_videotrack\lib_test::test_activity_chooser_metadata_is_reported` | Activity chooser metadata should remain predictable. |
| `tests/lib_test.php:81` | `mod_videotrack\lib_test::test_unknown_feature_returns_null` | Unknown features should keep Moodle's default handling path. |
| `tests/lib_test.php:90` | `mod_videotrack\lib_test::test_player_behavior_fields_normalise_bookmark_setting` | The instance bookmark checkbox must be persisted as a strict boolean field. |
| `tests/lib_test.php:118` | `mod_videotrack\lib_test::test_caption_normalisation_preserves_provider_timed_text_settings` | Provider transcript and chapter switches must survive caption normalisation. |
| `tests/locallib_test.php:35` | `mod_videotrack\locallib_test::setUp` | Load helper functions under test. |
| `tests/locallib_test.php:45` | `mod_videotrack\locallib_test::test_extract_videoid_accepts_supported_youtube_urls` | YouTube extraction accepts supported HTTPS URL shapes and rejects unsafe input. |
| `tests/locallib_test.php:62` | `mod_videotrack\locallib_test::test_extract_vimeo_id_accepts_supported_vimeo_urls` | Vimeo extraction accepts supported HTTPS URL shapes and rejects unsafe input. |
| `tests/locallib_test.php:78` | `mod_videotrack\locallib_test::test_format_seconds_clamps_and_formats_duration` | Human-readable time formatting clamps negative values and switches to hours when needed. |
| `tests/locallib_test.php:90` | `mod_videotrack\locallib_test::test_format_video_timestamp_uses_total_duration` | Video timestamps use the total duration to keep one stable display format. |
| `tests/locallib_test.php:101` | `mod_videotrack\locallib_test::test_parse_video_timestamp_accepts_supported_formats` | Video-time filters accept seconds, MM:SS and HH:MM:SS. |
| `tests/locallib_test.php:116` | `mod_videotrack\locallib_test::test_parse_report_timestamp_requires_colon_format` | Report filters accept only MM:SS and HH:MM:SS durations. |
| `tests/locallib_test.php:129` | `mod_videotrack\locallib_test::test_get_config_int_preserves_zero_and_clamps_values` | Bounded integer settings preserve explicit zero and clamp out-of-range values. |
| `tests/locallib_test.php:147` | `mod_videotrack\locallib_test::test_get_config_int_rejects_invalid_bounds` | Invalid helper bounds should fail loudly for developers. |
| `tests/locallib_test.php:157` | `mod_videotrack\locallib_test::test_get_playback_speeds_filters_and_applies_site_cap` | Instance playback speeds override site defaults and remain capped by the site maximum. |
| `tests/locallib_test.php:178` | `mod_videotrack\locallib_test::test_compatible_forum_types_exclude_single_use_forums` | Forum compatibility is restricted to repeatable discussion types. |
| `tests/locallib_test.php:187` | `mod_videotrack\locallib_test::test_build_replay_url_applies_window_and_duration` | Replay links apply the configured symmetric window and duration cap. |
| `tests/locallib_test.php:201` | `mod_videotrack\locallib_test::test_build_forum_subject_replaces_supported_placeholders` | Forum subject templates replace supported placeholders and preserve static text. |
| `tests/locallib_test.php:215` | `mod_videotrack\locallib_test::test_build_forum_subject_uses_default_template` | Empty Forum subject templates use the language-pack default. |
| `tests/save_bookmark_test.php:39` | `mod_videotrack\save_bookmark_test::test_execute_parameters_uses_supported_moodle_parameter_types` | The external parameters use Moodle-supported types and the bookmark segment reason is accepted by the server whitelist. |
| `tests/save_integrity_event_test.php:37` | `mod_videotrack\save_integrity_event_test::test_execute_parameters_uses_supported_moodle_parameter_types` | The external parameters use Moodle-supported types. |
| `tests/save_note_test.php:37` | `mod_videotrack\save_note_test::test_execute_parameters_uses_supported_moodle_parameter_types` | The external parameter structure must use parameter types defined by Moodle. |
| `tests/teacher_analytics_test.php:34` | `mod_videotrack\teacher_analytics_test::test_period_bounds` | Relative periods produce stable inclusive timestamp bounds. |
| `tests/timed_text_test.php:35` | `mod_videotrack\timed_text_test::test_language_from_filename_accepts_bcp47_like_names` | Language codes are derived from conventional WebVTT filenames. |
| `tests/timed_text_test.php:47` | `mod_videotrack\timed_text_test::test_is_valid_vtt_content_checks_signature_and_size` | WebVTT validation accepts a BOM and rejects malformed or oversized files. |
| `tests/timed_text_test.php:59` | `mod_videotrack\timed_text_test::test_file_options_enforce_vtt_limits` | File options enforce the project limits and WebVTT extension. |
| `tests/tracker_test.php:40` | `mod_videotrack\tracker_test::setUp` | Load tracker class under test. |
| `tests/tracker_test.php:48` | `mod_videotrack\tracker_test::test_normalise_interval_clamps_and_rejects_empty_ranges` | Interval normalisation clamps against video duration and rejects empty ranges. |
| `tests/tracker_test.php:59` | `mod_videotrack\tracker_test::test_decode_intervals_filters_invalid_ranges` | Invalid decoded interval data is ignored before it can affect completion. |
| `tests/tracker_test.php:76` | `mod_videotrack\tracker_test::test_merge_intervals_and_covered_seconds_are_deterministic` | Overlapping and adjacent intervals are merged deterministically. |
| `tests/tracker_test.php:91` | `mod_videotrack\tracker_test::test_simplify_intervals_never_overestimates_coverage` | Simplification keeps the longest fragments without merging unseen gaps. |
| `tests/tracker_test.php:108` | `mod_videotrack\tracker_test::test_aggregate_segments_rebuilds_state_values` | Raw segment aggregation rebuilds coverage and the latest resume position. |
| `tests/tracker_test.php:146` | `mod_videotrack\tracker_test::test_cap_intervals_limits_count_and_preserves_order` | The interval cap limits pathological data while preserving timeline order. |
| `tests/tracker_test.php:165` | `mod_videotrack\tracker_test::test_server_credit_budget_is_cumulative` | Verify request frequency cannot replenish the cumulative server-time credit budget. |
| `tests/tracker_test.php:199` | `mod_videotrack\tracker_test::test_forward_interval_guard_rejects_unwatched_jump` | Verify the server rejects forward jumps when the activity disables forward seeking. |
| `tests/tracker_test.php:212` | `mod_videotrack\tracker_test::test_watched_time_validation_ignores_unvalidated_raw_segments` | Named function or method; see the source DocBlock and callers for its detailed contract. |
| `tests/tracker_test.php:240` | `mod_videotrack\tracker_test::test_watched_time_validation_uses_aggregate_state_fallback` | Watched-time validation falls back to persisted aggregate intervals. |

## AMD named callables

| File:line | Callable | Responsibility |
|---|---|---|
| `amd/src/core/adapter.js:74` | `normaliseProviderType` | Named callable `normaliseProviderType` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:85` | `isKnownProviderType` | Named callable `isKnownProviderType` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:96` | `getCapabilityDefinition` | Named callable `getCapabilityDefinition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:105` | `getCapabilityMethods` | Named callable `getCapabilityMethods` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:110` | `getCapabilityProperties` | Named callable `getCapabilityProperties` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:136` | `isAvailable` | Named callable `isAvailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:152` | `can` | Named callable `can` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:170` | `hasCapability` | Named callable `hasCapability` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:186` | `getCapabilities` | Named callable `getCapabilities` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:204` | `canCurrentTime` | Named callable `canCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:208` | `canDuration` | Named callable `canDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:212` | `canPlay` | Named callable `canPlay` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:216` | `canPause` | Named callable `canPause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:220` | `canSeek` | Named callable `canSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:224` | `canPlaybackRate` | Named callable `canPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:228` | `canVolume` | Named callable `canVolume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:232` | `canMute` | Named callable `canMute` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:236` | `canPaused` | Named callable `canPaused` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:240` | `canEnded` | Named callable `canEnded` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:251` | `normaliseTime` | Named callable `normaliseTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:272` | `resolveSkipTarget` | Named callable `resolveSkipTarget` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:302` | `getCurrentTime` | Named callable `getCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:329` | `getDuration` | Named callable `getDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:358` | `normaliseVolume` | Named callable `normaliseVolume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:376` | `getVolume` | Named callable `getVolume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:404` | `setVolume` | Named callable `setVolume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:428` | `isMuted` | Named callable `isMuted` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:456` | `setMuted` | Named callable `setMuted` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:479` | `getPlaybackRate` | Named callable `getPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:510` | `setPlaybackRate` | Named callable `setPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:537` | `isPaused` | Named callable `isPaused` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:566` | `isEnded` | Named callable `isEnded` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:611` | `run` | Named callable `run` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:612` | `logFailure` | Named callable `logFailure` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:644` | `play` | Named callable `play` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:656` | `pause` | Named callable `pause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/adapter.js:669` | `seek` | Named callable `seek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:29` | `getNetworkState` | Named callable `getNetworkState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:41` | `isBrowserOffline` | Named callable `isBrowserOffline` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:51` | `getErrorCode` | Named callable `getErrorCode` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:67` | `getErrorMessage` | Named callable `getErrorMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:83` | `getErrorStatus` | Named callable `getErrorStatus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:99` | `classifyAjaxError` | Named callable `classifyAjaxError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:145` | `normaliseAjaxError` | Named callable `normaliseAjaxError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/error.js:171` | `isTransientAjaxError` | Named callable `isTransientAjaxError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/retry.js:24` | `getRetryJitter` | Named callable `getRetryJitter` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/retry.js:52` | `normalizeRetryCount` | Named callable `normalizeRetryCount` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/retry.js:67` | `delay` | Named callable `delay` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/scope.js:18` | `createRequestScope` | Named callable `createRequestScope` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/scope.js:28` | `nextToken` | Named callable `nextToken` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/scope.js:43` | `isCurrent` | Named callable `isCurrent` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/scope.js:55` | `resolveIfCurrent` | Named callable `resolveIfCurrent` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/transport.js:27` | `withTimeout` | Named callable `withTimeout` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/transport.js:60` | `send` | Named callable `send` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:54` | `normaliseMethodName` | Named callable `normaliseMethodName` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:72` | `createValidationError` | Named callable `createValidationError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:86` | `isPlainObject` | Named callable `isPlainObject` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:96` | `getUtf8Length` | Named callable `getUtf8Length` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:111` | `isSafeArgValue` | Named callable `isSafeArgValue` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:149` | `hasNonNegativeNumber` | Named callable `hasNonNegativeNumber` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api/validator.js:166` | `validateArgs` | Named callable `validateArgs` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api.js:61` | `call` | Named callable `call` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api.js:87` | `attemptRequest` | Named callable `attemptRequest` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api.js:135` | `buildSegmentArgs` | Named callable `buildSegmentArgs` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/api.js:174` | `saveSegment` | Named callable `saveSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/beacon.js:31` | `sendSegment` | Named callable `sendSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:38` | `submitForm` | Named callable `submitForm` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:61` | `getFocusableElement` | Named callable `getFocusableElement` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:74` | `restoreFocus` | Named callable `restoreFocus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:93` | `focusModal` | Named callable `focusModal` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:115` | `normaliseText` | Named callable `normaliseText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:126` | `resolveString` | Named callable `resolveString` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:151` | `showInlineFallback` | Named callable `showInlineFallback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:177` | `showModalConfirm` | Named callable `showModalConfirm` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/confirm.js:240` | `attachToForms` | Named callable `attachToForms` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/debug.js:21` | `log` | Named callable `log` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:21` | `create` | Named callable `create` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:35` | `normaliseEventName` | Named callable `normaliseEventName` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:51` | `on` | Named callable `on` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:57` | `removeHandler` | Named callable `removeHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:85` | `off` | Named callable `off` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:103` | `emit` | Named callable `emit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:135` | `once` | Named callable `once` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:136` | `unsubscribe` | Named callable `unsubscribe` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:137` | `wrapped` | Named callable `wrapped` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:151` | `count` | Named callable `count` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:164` | `clear` | Named callable `clear` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:176` | `ensure` | Named callable `ensure` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/events.js:194` | `emit` | Named callable `emit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/acknowledgement.js:17` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:10` | `resolveBookmarkTime` | Named callable `resolveBookmarkTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:19` | `appendRow` | Named callable `appendRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:77` | `installHandler` | Named callable `installHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:101` | `ajax` | Named callable `ajax` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:109` | `restore` | Named callable `restore` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:117` | `saveHandler` | Named callable `saveHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:172` | `listHandler` | Named callable `listHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/bookmarks.js:219` | `cleanup` | Named callable `cleanup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:26` | `randomInteger` | Named callable `randomInteger` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:43` | `create` | Named callable `create` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:65` | `currentTime` | Named callable `currentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:85` | `record` | Named callable `record` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:107` | `clearRandomTimer` | Named callable `clearRandomTimer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:115` | `clearBlurTimer` | Named callable `clearBlurTimer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:127` | `showMessage` | Named callable `showMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:139` | `pausePlayback` | Named callable `pausePlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:157` | `scheduleRandomPause` | Named callable `scheduleRandomPause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:181` | `noteAction` | Named callable `noteAction` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:194` | `setPlaying` | Named callable `setPlaying` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:207` | `noteProgress` | Named callable `noteProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:229` | `applyPictureInPicturePolicy` | Named callable `applyPictureInPicturePolicy` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:260` | `onVisibilityChange` | Named callable `onVisibilityChange` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:272` | `onWindowFocus` | Named callable `onWindowFocus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:276` | `onWindowBlur` | Named callable `onWindowBlur` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:305` | `onShellInteraction` | Named callable `onShellInteraction` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/focus_guard.js:341` | `destroy` | Named callable `destroy` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/forum.js:15` | `install` | Named callable `install` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/forum.js:24` | `setBusy` | Named callable `setBusy` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/forum.js:28` | `handler` | Named callable `handler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/forum.js:56` | `cleanup` | Named callable `cleanup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/intervalbar.js:22` | `getColor` | Named callable `getColor` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/intervalbar.js:33` | `parse` | Named callable `parse` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/intervalbar.js:55` | `updateTextAlternative` | Named callable `updateTextAlternative` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/intervalbar.js:88` | `drawIntervals` | Named callable `drawIntervals` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/intervalbar.js:119` | `update` | Named callable `update` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes/row.js:19` | `appendRow` | Named callable `appendRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes/toggle.js:19` | `install` | Named callable `install` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes/toggle.js:34` | `setCollapsed` | Named callable `setCollapsed` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes/toggle.js:45` | `toggleClickHandler` | Named callable `toggleClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes/toggle.js:49` | `cleanupToggleHandler` | Named callable `cleanupToggleHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:28` | `getRemainingChars` | Named callable `getRemainingChars` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:44` | `updateCharCounter` | Named callable `updateCharCounter` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:63` | `setButtonState` | Named callable `setButtonState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:83` | `resolveNoteTime` | Named callable `resolveNoteTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:105` | `installHandler` | Named callable `installHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:130` | `ajax` | Named callable `ajax` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:139` | `restoreSaveButtonState` | Named callable `restoreSaveButtonState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:150` | `showResponseWarnings` | Named callable `showResponseWarnings` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:161` | `announceLimitedNotes` | Named callable `announceLimitedNotes` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:169` | `announceCharThreshold` | Named callable `announceCharThreshold` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:200` | `setLocalButtonState` | Named callable `setLocalButtonState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:206` | `playStateHandler` | Named callable `playStateHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:209` | `cleanupNoteHandler` | Named callable `cleanupNoteHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:225` | `saveClickHandler` | Named callable `saveClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:295` | `noteListClickHandler` | Named callable `noteListClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/notes.js:337` | `textareaInputHandler` | Named callable `textareaInputHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/poster.js:16` | `onFirstPlay` | Named callable `onFirstPlay` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/poster.js:30` | `remove` | Named callable `remove` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/progress.js:25` | `clampSegmentTimes` | Named callable `clampSegmentTimes` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/progress.js:35` | `normaliseSaveReason` | Named callable `normaliseSaveReason` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/progress.js:49` | `saveCurrentProgress` | Named callable `saveCurrentProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/progress.js:64` | `sendBeaconSegment` | Named callable `sendBeaconSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/reactions.js:21` | `announceAvailability` | Named callable `announceAvailability` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/reactions.js:31` | `announceUnavailable` | Named callable `announceUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/resume.js:16` | `showNotice` | Named callable `showNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/status.js:21` | `getShell` | Named callable `getShell` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/status.js:34` | `configure` | Named callable `configure` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/status.js:46` | `showMessage` | Named callable `showMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/status.js:62` | `showErrorMessage` | Named callable `showErrorMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/status.js:80` | `announce` | Named callable `announce` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:21` | `stripCueMarkup` | Named callable `stripCueMarkup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:41` | `vttTime` | Named callable `vttTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:64` | `parseVtt` | Named callable `parseVtt` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:109` | `countLabel` | Named callable `countLabel` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:119` | `create` | Named callable `create` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:145` | `announce` | Named callable `announce` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:166` | `navigate` | Named callable `navigate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:184` | `preferredTrackIndex` | Named callable `preferredTrackIndex` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:205` | `buildTranscriptControls` | Named callable `buildTranscriptControls` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:257` | `filterTranscript` | Named callable `filterTranscript` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:277` | `renderTranscript` | Named callable `renderTranscript` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:314` | `showTranscriptUnavailable` | Named callable `showTranscriptUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:331` | `loadTranscript` | Named callable `loadTranscript` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:356` | `renderChapters` | Named callable `renderChapters` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:389` | `showChaptersUnavailable` | Named callable `showChaptersUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:404` | `loadChapters` | Named callable `loadChapters` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:433` | `update` | Named callable `update` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:487` | `poll` | Named callable `poll` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player/timed_text.js:504` | `destroy` | Named callable `destroy` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:29` | `uuid` | Named callable `uuid` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:43` | `getIntervalBarColor` | Named callable `getIntervalBarColor` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:54` | `parseIntervals` | Named callable `parseIntervals` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:65` | `updateIntervalBar` | Named callable `updateIntervalBar` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:81` | `showResumeNotice` | Named callable `showResumeNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:90` | `configureStatus` | Named callable `configureStatus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:102` | `showStatusMessage` | Named callable `showStatusMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:114` | `showErrorStatusMessage` | Named callable `showErrorStatusMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:124` | `announceStatusMessage` | Named callable `announceStatusMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:139` | `onFirstPlay` | Named callable `onFirstPlay` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:155` | `installNoteHandler` | Named callable `installNoteHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:164` | `installBookmarkHandler` | Named callable `installBookmarkHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:177` | `removePoster` | Named callable `removePoster` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/player.js:187` | `getPlayerShell` | Named callable `getPlayerShell` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:21` | `pickNumber` | Named callable `pickNumber` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:44` | `formatPercent` | Named callable `formatPercent` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:54` | `updatePercentText` | Named callable `updatePercentText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:66` | `updateFallbackProgress` | Named callable `updateFallbackProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:93` | `updateProgress` | Named callable `updateProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:139` | `buildLiveSnapshot` | Named callable `buildLiveSnapshot` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/progress.js:195` | `updateLiveProgress` | Named callable `updateLiveProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:27` | `createState` | Named callable `createState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:50` | `getStatusRegion` | Named callable `getStatusRegion` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:69` | `announceStatus` | Named callable `announceStatus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:101` | `announceAvailability` | Named callable `announceAvailability` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:148` | `announceUnavailable` | Named callable `announceUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/reactions.js:189` | `setButtons` | Named callable `setButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/segment.js:25` | `finiteSeconds` | Named callable `finiteSeconds` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/segment.js:36` | `normaliseSaveReason` | Named callable `normaliseSaveReason` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/segment.js:52` | `clampSegmentTimes` | Named callable `clampSegmentTimes` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/segment.js:75` | `calculateInteractionEnd` | Named callable `calculateInteractionEnd` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/session.js:22` | `uuid` | Named callable `uuid` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/state.js:21` | `create` | Named callable `create` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:41` | `getState` | Named callable `getState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:58` | `clampTimeout` | Named callable `clampTimeout` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:71` | `configure` | Named callable `configure` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:89` | `normaliseMessage` | Named callable `normaliseMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:104` | `getLiveRegion` | Named callable `getLiveRegion` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:133` | `remove` | Named callable `remove` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:148` | `getContainer` | Named callable `getContainer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:158` | `normaliseDismissLabel` | Named callable `normaliseDismissLabel` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:170` | `normaliseTimeout` | Named callable `normaliseTimeout` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:197` | `announce` | Named callable `announce` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:228` | `clear` | Named callable `clear` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/status.js:264` | `show` | Named callable `show` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:19` | `on` | Named callable `on` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:31` | `once` | Named callable `once` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:42` | `off` | Named callable `off` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:55` | `count` | Named callable `count` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:67` | `clear` | Named callable `clear` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/events.js:80` | `emit` | Named callable `emit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:29` | `safeBooleanCallback` | Named callable `safeBooleanCallback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:47` | `resetHeartbeat` | Named callable `resetHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:62` | `captureHeartbeatSegment` | Named callable `captureHeartbeatSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:79` | `normaliseHeartbeatInterval` | Named callable `normaliseHeartbeatInterval` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:93` | `pollInterval` | Named callable `pollInterval` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:110` | `startPolling` | Named callable `startPolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:126` | `stopPolling` | Named callable `stopPolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:144` | `shouldSaveHeartbeat` | Named callable `shouldSaveHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:160` | `reopenAfterHeartbeat` | Named callable `reopenAfterHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:179` | `saveHeartbeatIfDue` | Named callable `saveHeartbeatIfDue` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:242` | `runHeartbeat` | Named callable `runHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:245` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:248` | `shouldSkip` | Named callable `shouldSkip` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/heartbeat.js:268` | `clearHeartbeatRunning` | Named callable `clearHeartbeatRunning` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:23` | `runAfterStop` | Named callable `runAfterStop` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:39` | `sendUnloadBeacon` | Named callable `sendUnloadBeacon` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:42` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:90` | `closeThenStop` | Named callable `closeThenStop` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:132` | `installLifecycleHandlers` | Named callable `installLifecycleHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:146` | `stopPolling` | Named callable `stopPolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:151` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:156` | `onVisibilityChange` | Named callable `onVisibilityChange` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:164` | `onPageHide` | Named callable `onPageHide` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:169` | `onBeforeUnload` | Named callable `onBeforeUnload` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/lifecycle.js:203` | `uninstallLifecycleHandlers` | Named callable `uninstallLifecycleHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:34` | `openSegment` | Named callable `openSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:62` | `closeSegment` | Named callable `closeSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:94` | `enqueueSegmentSave` | Named callable `enqueueSegmentSave` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:117` | `isPlayerAvailable` | Named callable `isPlayerAvailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:134` | `closeAndSaveSegment` | Named callable `closeAndSaveSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:175` | `reopenAfterInteractionSave` | Named callable `reopenAfterInteractionSave` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/segment.js:193` | `saveCurrentProgress` | Named callable `saveCurrentProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:17` | `normaliseTime` | Named callable `normaliseTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:65` | `normaliseTrackerState` | Named callable `normaliseTrackerState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:83` | `isKnownTrackerState` | Named callable `isKnownTrackerState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:99` | `getTrackerState` | Named callable `getTrackerState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:112` | `getTransitionToken` | Named callable `getTransitionToken` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:123` | `isTransitionCurrent` | Named callable `isTransitionCurrent` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:134` | `canTransition` | Named callable `canTransition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:152` | `applyTrackerStateFlags` | Named callable `applyTrackerStateFlags` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:185` | `setTrackerState` | Named callable `setTrackerState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:215` | `markIdle` | Named callable `markIdle` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:226` | `markPlaying` | Named callable `markPlaying` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:237` | `markPaused` | Named callable `markPaused` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:248` | `markSeeking` | Named callable `markSeeking` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:259` | `markEnded` | Named callable `markEnded` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/state.js:270` | `markDestroyed` | Named callable `markDestroyed` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:26` | `syncTime` | Named callable `syncTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:49` | `resolveCurrentTime` | Named callable `resolveCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:65` | `markProgrammaticSeek` | Named callable `markProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:80` | `consumeProgrammaticSeek` | Named callable `consumeProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:106` | `resolveSeek` | Named callable `resolveSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:144` | `blockSeek` | Named callable `blockSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:173` | `clearSeekBlock` | Named callable `clearSeekBlock` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker/time.js:199` | `shouldStopReplay` | Named callable `shouldStopReplay` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:116` | `saveHeartbeatIfDue` | Named callable `saveHeartbeatIfDue` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:126` | `runHeartbeat` | Named callable `runHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:136` | `sendUnloadBeacon` | Named callable `sendUnloadBeacon` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:146` | `installLifecycleHandlers` | Named callable `installLifecycleHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:158` | `uninstallLifecycleHandlers` | Named callable `uninstallLifecycleHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/tracker.js:173` | `cancelPendingRequests` | Named callable `cancelPendingRequests` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/ui.js:22` | `setReactionButtons` | Named callable `setReactionButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/ui.js:45` | `isSafeIconSrc` | Named callable `isSafeIconSrc` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/ui.js:80` | `isSafeIconClass` | Named callable `isSafeIconClass` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/ui.js:107` | `appendIconSafe` | Named callable `appendIconSafe` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/ui.js:162` | `notifyVideoEnded` | Named callable `notifyVideoEnded` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:24` | `safeInt` | Named callable `safeInt` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:35` | `pad` | Named callable `pad` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:46` | `formatSeconds` | Named callable `formatSeconds` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:84` | `decodeHtmlEntitiesOnce` | Named callable `decodeHtmlEntitiesOnce` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:102` | `decodeHtmlEntitiesForValidation` | Named callable `decodeHtmlEntitiesForValidation` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:116` | `validateWebVttText` | Named callable `validateWebVttText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:160` | `validateTextResponse` | Named callable `validateTextResponse` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:200` | `isSafeFetchUrl` | Named callable `isSafeFetchUrl` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:227` | `isSafeBeaconUrl` | Named callable `isSafeBeaconUrl` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:255` | `fetchTextWithTimeout` | Named callable `fetchTextWithTimeout` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:335` | `sessionSet` | Named callable `sessionSet` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/core/utils.js:354` | `sessionGet` | Named callable `sessionGet` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:35` | `normaliseDuration` | Named callable `normaliseDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:44` | `formatDuration` | Named callable `formatDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:48` | `renderMessage` | Named callable `renderMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:52` | `setStatus` | Named callable `setStatus` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:62` | `parseHttpsUrl` | Named callable `parseHttpsUrl` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:71` | `extractYouTubeId` | Named callable `extractYouTubeId` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:92` | `extractVimeoSource` | Named callable `extractVimeoSource` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:120` | `getProbeHost` | Named callable `getProbeHost` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:140` | `loadYouTubeApi` | Named callable `loadYouTubeApi` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:156` | `ready` | Named callable `ready` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:196` | `detectYouTubeDuration` | Named callable `detectYouTubeDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:206` | `cleanup` | Named callable `cleanup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:218` | `finish` | Named callable `finish` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:245` | `onReady` | Named callable `onReady` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:253` | `onError` | Named callable `onError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:262` | `loadVimeoApi` | Named callable `loadVimeoApi` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:272` | `restoreDefine` | Named callable `restoreDefine` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:284` | `ready` | Named callable `ready` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:325` | `detectVimeoDuration` | Named callable `detectVimeoDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:341` | `cleanup` | Named callable `cleanup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:372` | `findLocalFileUrl` | Named callable `findLocalFileUrl` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:389` | `detectLocalDuration` | Named callable `detectLocalDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:399` | `cleanup` | Named callable `cleanup` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:406` | `finish` | Named callable `finish` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:443` | `resolveSource` | Named callable `resolveSource` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:449` | `detect` | Named callable `detect` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:458` | `detect` | Named callable `detect` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:467` | `detect` | Named callable `detect` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:475` | `getElements` | Named callable `getElements` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:489` | `install` | Named callable `install` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:505` | `schedule` | Named callable `schedule` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:510` | `run` | Named callable `run` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:574` | `sourceChanged` | Named callable `sourceChanged` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/form/duration.js:610` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:56` | `resolveConfig` | Named callable `resolveConfig` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:67` | `uuid` | Named callable `uuid` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:71` | `safeNumber` | Named callable `safeNumber` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:76` | `getMaxWatchedFromIntervals` | Named callable `getMaxWatchedFromIntervals` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:92` | `markAllowedForwardTime` | Named callable `markAllowedForwardTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:96` | `getAllowedForwardLimit` | Named callable `getAllowedForwardLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:101` | `normaliseControls` | Named callable `normaliseControls` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:120` | `getConfiguredMaxPlaybackRate` | Named callable `getConfiguredMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:131` | `getPlaybackRatePenalty` | Named callable `getPlaybackRatePenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:139` | `getNaturalPlaybackTolerance` | Named callable `getNaturalPlaybackTolerance` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:147` | `setSpeedButtonState` | Named callable `setSpeedButtonState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:157` | `writePlaybackRate` | Named callable `writePlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:168` | `enforceMaxPlaybackRate` | Named callable `enforceMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:186` | `getBlockedSeekPlaybackRate` | Named callable `getBlockedSeekPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:195` | `applyBlockedSeekPenalty` | Named callable `applyBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:199` | `retryBlockedSeekPenalty` | Named callable `retryBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:212` | `markHTML5PlaybackObserved` | Named callable `markHTML5PlaybackObserved` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:216` | `resolveHTML5SeekWasPlaying` | Named callable `resolveHTML5SeekWasPlaying` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:221` | `playHTML5AfterSeek` | Named callable `playHTML5AfterSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:226` | `attempt` | Named callable `attempt` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:270` | `scheduleBlockedSeekResume` | Named callable `scheduleBlockedSeekResume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:282` | `finishProgrammaticSeek` | Named callable `finishProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:295` | `scheduleProgrammaticSeekFallback` | Named callable `scheduleProgrammaticSeekFallback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:306` | `startProgrammaticSeek` | Named callable `startProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:312` | `blockForwardSeek` | Named callable `blockForwardSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:341` | `saveSegment` | Named callable `saveSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:349` | `hasMedia` | Named callable `hasMedia` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:356` | `saveCurrentProgress` | Named callable `saveCurrentProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:360` | `updateProgress` | Named callable `updateProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:371` | `resolveReactionTime` | Named callable `resolveReactionTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:380` | `isDefinitiveReactionFailure` | Named callable `isDefinitiveReactionFailure` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:394` | `removeReactionRow` | Named callable `removeReactionRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:402` | `startSegment` | Named callable `startSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:421` | `closeSegment` | Named callable `closeSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:429` | `startHeartbeat` | Named callable `startHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:434` | `getCurrentTime` | Named callable `getCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:438` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:441` | `shouldSkip` | Named callable `shouldSkip` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:449` | `stopHeartbeat` | Named callable `stopHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:453` | `showResumeNotice` | Named callable `showResumeNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:466` | `replayHTML5Fragment` | Named callable `replayHTML5Fragment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:481` | `installGlobalListeners` | Named callable `installGlobalListeners` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:485` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:486` | `sendBeacon` | Named callable `sendBeacon` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:489` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:491` | `sendSegment` | Named callable `sendSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:501` | `handleReplayClick` | Named callable `handleReplayClick` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:525` | `buildPlayer` | Named callable `buildPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:615` | `formatElapsedTime` | Named callable `formatElapsedTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:631` | `updateElapsedDisplays` | Named callable `updateElapsedDisplays` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:642` | `buildControlBar` | Named callable `buildControlBar` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:871` | `updatePipPressed` | Named callable `updatePipPressed` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:881` | `cleanupPipHandler` | Named callable `cleanupPipHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:899` | `updateFullscreenPressed` | Named callable `updateFullscreenPressed` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:902` | `cleanupFullscreenHandler` | Named callable `cleanupFullscreenHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1015` | `makeBtn` | Named callable `makeBtn` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1028` | `attachTrackingEvents` | Named callable `attachTrackingEvents` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1162` | `setReactionButtons` | Named callable `setReactionButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1167` | `announceReactionUnavailable` | Named callable `announceReactionUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1175` | `installReactionHandler` | Named callable `installReactionHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1179` | `appendReactionRow` | Named callable `appendReactionRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1249` | `reactionKeydownHandler` | Named callable `reactionKeydownHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1258` | `reactionClickHandler` | Named callable `reactionClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1392` | `cleanupReactionRootHandlers` | Named callable `cleanupReactionRootHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1408` | `getCurrentVideoTime` | Named callable `getCurrentVideoTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1415` | `initialiseFocusGuard` | Named callable `initialiseFocusGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1420` | `pause` | Named callable `pause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1423` | `showMessage` | Named callable `showMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1432` | `installNotesToggle` | Named callable `installNotesToggle` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1441` | `installNoteHandler` | Named callable `installNoteHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1456` | `installBookmarkHandler` | Named callable `installBookmarkHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1474` | `navigateTimedText` | Named callable `navigateTimedText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1495` | `installPosterHandler` | Named callable `installPosterHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1499` | `removePoster` | Named callable `removePoster` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1504` | `posterClickHandler` | Named callable `posterClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1542` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/html5_player.js:1572` | `getDuration` | Named callable `getDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:42` | `resolveConfig` | Named callable `resolveConfig` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:53` | `uuid` | Named callable `uuid` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:57` | `loadApi` | Named callable `loadApi` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:75` | `updateProgress` | Named callable `updateProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:86` | `resolveReactionTime` | Named callable `resolveReactionTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:102` | `updateIntervalBar` | Named callable `updateIntervalBar` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:106` | `updateLiveIntervalBar` | Named callable `updateLiveIntervalBar` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:110` | `markAllowedForwardTime` | Named callable `markAllowedForwardTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:115` | `getAllowedForwardLimit` | Named callable `getAllowedForwardLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:119` | `isForwardTargetAlreadyWatched` | Named callable `isForwardTargetAlreadyWatched` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:124` | `isNormalForwardPlayback` | Named callable `isNormalForwardPlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:136` | `isForwardSeekRecoveryPlayback` | Named callable `isForwardSeekRecoveryPlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:141` | `resetForwardSeekRecovery` | Named callable `resetForwardSeekRecovery` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:153` | `getMaxWatchedFromIntervals` | Named callable `getMaxWatchedFromIntervals` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:176` | `getResumeStorageKey` | Named callable `getResumeStorageKey` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:180` | `readStoredResumePosition` | Named callable `readStoredResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:191` | `rememberResumePosition` | Named callable `rememberResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:205` | `resolveResumePosition` | Named callable `resolveResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:211` | `initialiseKnownProgress` | Named callable `initialiseKnownProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:222` | `getBlockedSeekPlaybackRate` | Named callable `getBlockedSeekPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:231` | `blockForwardSeek` | Named callable `blockForwardSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:254` | `saveSegment` | Named callable `saveSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:262` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:266` | `saveCurrentProgress` | Named callable `saveCurrentProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:270` | `closeCurrentSegment` | Named callable `closeCurrentSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:277` | `getConfiguredMaxPlaybackRate` | Named callable `getConfiguredMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:291` | `getPlaybackRatePenalty` | Named callable `getPlaybackRatePenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:299` | `writePlaybackRate` | Named callable `writePlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:320` | `applyBlockedSeekPenalty` | Named callable `applyBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:324` | `retryBlockedSeekPenalty` | Named callable `retryBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:335` | `enforceMaxPlaybackRate` | Named callable `enforceMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:365` | `installPlaybackRateGuard` | Named callable `installPlaybackRateGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:376` | `cleanupPlaybackRateGuard` | Named callable `cleanupPlaybackRateGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:383` | `startCurrentSegment` | Named callable `startCurrentSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:402` | `setReactionButtons` | Named callable `setReactionButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:407` | `announceReactionUnavailable` | Named callable `announceReactionUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:411` | `replayFragment` | Named callable `replayFragment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:428` | `handleSeekByPolling` | Named callable `handleSeekByPolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:543` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:550` | `onPlayerStateChange` | Named callable `onPlayerStateChange` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:589` | `showResumeNotice` | Named callable `showResumeNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:596` | `installGlobalListeners` | Named callable `installGlobalListeners` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:600` | `onHidden` | Named callable `onHidden` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:603` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:604` | `sendBeacon` | Named callable `sendBeacon` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:607` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:609` | `sendSegment` | Named callable `sendSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:625` | `reactionKeydownHandler` | Named callable `reactionKeydownHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:634` | `reactionClickHandler` | Named callable `reactionClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:761` | `cleanupReactionRootHandlers` | Named callable `cleanupReactionRootHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:780` | `appendReactionRow` | Named callable `appendReactionRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:856` | `buildPlayer` | Named callable `buildPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:884` | `onReady` | Named callable `onReady` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:916` | `onPlaybackRateChange` | Named callable `onPlaybackRateChange` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:919` | `onAutoplayBlocked` | Named callable `onAutoplayBlocked` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:933` | `removeNotice` | Named callable `removeNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:951` | `onError` | Named callable `onError` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:967` | `buildYouTubeSkipButtons` | Named callable `buildYouTubeSkipButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1047` | `getCurrentVideoTime` | Named callable `getCurrentVideoTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1054` | `initialiseFocusGuard` | Named callable `initialiseFocusGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1059` | `pause` | Named callable `pause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1062` | `showMessage` | Named callable `showMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1079` | `installReactionHandler` | Named callable `installReactionHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1088` | `installNotesToggle` | Named callable `installNotesToggle` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1097` | `installNoteHandler` | Named callable `installNoteHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1112` | `installBookmarkHandler` | Named callable `installBookmarkHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1130` | `navigateTimedText` | Named callable `navigateTimedText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1157` | `installPosterHandler` | Named callable `installPosterHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1161` | `removePoster` | Named callable `removePoster` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1166` | `posterClickHandler` | Named callable `posterClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1204` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/player.js:1236` | `getDuration` | Named callable `getDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:29` | `cssEscape` | Named callable `cssEscape` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:36` | `queryByName` | Named callable `queryByName` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:43` | `findPicker` | Named callable `findPicker` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:47` | `findTargetInput` | Named callable `findTargetInput` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:56` | `findTypeSelect` | Named callable `findTypeSelect` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:61` | `renderPreview` | Named callable `renderPreview` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:89` | `updateChoiceState` | Named callable `updateChoiceState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:107` | `filterDialog` | Named callable `filterDialog` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:149` | `closePicker` | Named callable `closePicker` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:163` | `openPicker` | Named callable `openPicker` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:184` | `attachIconPickers` | Named callable `attachIconPickers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:266` | `installHtml5SourceVisibility` | Named callable `installHtml5SourceVisibility` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:284` | `update` | Named callable `update` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/presets.js:304` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/report.js:37` | `attachConfirm` | Named callable `attachConfirm` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/report.js:55` | `initialiseExportFormat` | Named callable `initialiseExportFormat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/report.js:61` | `update` | Named callable `update` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/report.js:80` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:55` | `resolveConfig` | Named callable `resolveConfig` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:66` | `uuid` | Named callable `uuid` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:71` | `saveSegment` | Named callable `saveSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:79` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:83` | `saveCurrentProgress` | Named callable `saveCurrentProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:87` | `updateProgress` | Named callable `updateProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:98` | `resolveReactionTime` | Named callable `resolveReactionTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:107` | `updateLiveIntervalBar` | Named callable `updateLiveIntervalBar` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:111` | `markAllowedForwardTime` | Named callable `markAllowedForwardTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:116` | `getAllowedForwardLimit` | Named callable `getAllowedForwardLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:120` | `getBlockedForwardGuardLimit` | Named callable `getBlockedForwardGuardLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:130` | `getBlockedForwardRecoveryLimit` | Named callable `getBlockedForwardRecoveryLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:144` | `isBlockedForwardRecoveryPlayback` | Named callable `isBlockedForwardRecoveryPlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:149` | `isVimeoForwardTimeBlocked` | Named callable `isVimeoForwardTimeBlocked` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:155` | `markVimeoProgrammaticSeek` | Named callable `markVimeoProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:161` | `clearVimeoProgrammaticSeekTarget` | Named callable `clearVimeoProgrammaticSeekTarget` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:166` | `consumeVimeoProgrammaticSeek` | Named callable `consumeVimeoProgrammaticSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:181` | `isNormalForwardPlayback` | Named callable `isNormalForwardPlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:193` | `isForwardSeekRecoveryPlayback` | Named callable `isForwardSeekRecoveryPlayback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:198` | `resetForwardSeekRecovery` | Named callable `resetForwardSeekRecovery` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:212` | `getMaxWatchedFromIntervals` | Named callable `getMaxWatchedFromIntervals` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:234` | `isVimeoBackwardSeekAllowed` | Named callable `isVimeoBackwardSeekAllowed` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:240` | `isReplaySeekActive` | Named callable `isReplaySeekActive` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:244` | `getRecentVimeoUserSeek` | Named callable `getRecentVimeoUserSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:253` | `clearRecentVimeoUserSeek` | Named callable `clearRecentVimeoUserSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:257` | `markVimeoPlaybackObserved` | Named callable `markVimeoPlaybackObserved` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:261` | `resolveVimeoSeekWasPlaying` | Named callable `resolveVimeoSeekWasPlaying` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:268` | `rememberVimeoUserSeek` | Named callable `rememberVimeoUserSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:283` | `clearBlockedSeekResumeState` | Named callable `clearBlockedSeekResumeState` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:292` | `clearBlockedSeekResumeRequest` | Named callable `clearBlockedSeekResumeRequest` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:310` | `playVimeoAfterSeek` | Named callable `playVimeoAfterSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:321` | `complete` | Named callable `complete` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:338` | `attempt` | Named callable `attempt` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:444` | `ensureVimeoRuntimePlaying` | Named callable `ensureVimeoRuntimePlaying` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:457` | `getResumeStorageKey` | Named callable `getResumeStorageKey` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:461` | `readStoredResumePosition` | Named callable `readStoredResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:482` | `rememberResumePosition` | Named callable `rememberResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:496` | `resolveResumePosition` | Named callable `resolveResumePosition` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:510` | `startVimeoRuntimePolling` | Named callable `startVimeoRuntimePolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:518` | `stopVimeoRuntimePolling` | Named callable `stopVimeoRuntimePolling` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:526` | `readVimeoValue` | Named callable `readVimeoValue` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:536` | `pauseRuntimeSegment` | Named callable `pauseRuntimeSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:546` | `pollVimeoRuntime` | Named callable `pollVimeoRuntime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:582` | `getCurrentTime` | Named callable `getCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:586` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:594` | `handleVimeoTime` | Named callable `handleVimeoTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:672` | `initialiseKnownProgress` | Named callable `initialiseKnownProgress` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:683` | `scheduleBlockedSeekResume` | Named callable `scheduleBlockedSeekResume` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:704` | `verifyBlockedSeekRollback` | Named callable `verifyBlockedSeekRollback` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:730` | `recoverBlockedSeek` | Named callable `recoverBlockedSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:745` | `finish` | Named callable `finish` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:781` | `getBlockedSeekPlaybackRate` | Named callable `getBlockedSeekPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:790` | `blockForwardSeek` | Named callable `blockForwardSeek` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:816` | `getConfiguredMaxPlaybackRate` | Named callable `getConfiguredMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:827` | `getPlaybackRatePenalty` | Named callable `getPlaybackRatePenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:835` | `applyBlockedSeekPenalty` | Named callable `applyBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:839` | `retryBlockedSeekPenalty` | Named callable `retryBlockedSeekPenalty` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:854` | `writePlaybackRate` | Named callable `writePlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:868` | `retryPlaybackRateLimit` | Named callable `retryPlaybackRateLimit` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:883` | `enforcePlaybackRateValue` | Named callable `enforcePlaybackRateValue` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:903` | `enforceMaxPlaybackRate` | Named callable `enforceMaxPlaybackRate` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:915` | `installPlaybackRateGuard` | Named callable `installPlaybackRateGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:924` | `cleanupPlaybackRateGuard` | Named callable `cleanupPlaybackRateGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:933` | `startSegment` | Named callable `startSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:947` | `closeSegment` | Named callable `closeSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:955` | `startHeartbeat` | Named callable `startHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:960` | `getCurrentTime` | Named callable `getCurrentTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:964` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:972` | `stopHeartbeat` | Named callable `stopHeartbeat` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:982` | `showResumeNotice` | Named callable `showResumeNotice` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:986` | `installGlobalListeners` | Named callable `installGlobalListeners` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:991` | `onHidden` | Named callable `onHidden` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:994` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:995` | `sendBeacon` | Named callable `sendBeacon` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:998` | `hasPlayer` | Named callable `hasPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:999` | `sendSegment` | Named callable `sendSegment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1009` | `loadVimeoSDK` | Named callable `loadVimeoSDK` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1016` | `restoreDefine` | Named callable `restoreDefine` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1063` | `resolveVimeoSource` | Named callable `resolveVimeoSource` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1098` | `buildVimeoIframe` | Named callable `buildVimeoIframe` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1137` | `replayVimeoFragment` | Named callable `replayVimeoFragment` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1181` | `buildPlayer` | Named callable `buildPlayer` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1451` | `handleReplayClick` | Named callable `handleReplayClick` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1480` | `buildVimeoSkipButtons` | Named callable `buildVimeoSkipButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1564` | `setReactionButtons` | Named callable `setReactionButtons` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1569` | `announceReactionUnavailable` | Named callable `announceReactionUnavailable` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1574` | `installReactionHandler` | Named callable `installReactionHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1578` | `appendReactionRow` | Named callable `appendReactionRow` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1648` | `reactionKeydownHandler` | Named callable `reactionKeydownHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1657` | `reactionClickHandler` | Named callable `reactionClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1761` | `cleanupReactionRootHandlers` | Named callable `cleanupReactionRootHandlers` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1781` | `getCurrentVideoTime` | Named callable `getCurrentVideoTime` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1795` | `initialiseFocusGuard` | Named callable `initialiseFocusGuard` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1800` | `pause` | Named callable `pause` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1803` | `showMessage` | Named callable `showMessage` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1812` | `installNotesToggle` | Named callable `installNotesToggle` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1821` | `installNoteHandler` | Named callable `installNoteHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1836` | `installBookmarkHandler` | Named callable `installBookmarkHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1854` | `navigateTimedText` | Named callable `navigateTimedText` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1888` | `installPosterHandler` | Named callable `installPosterHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1892` | `removePoster` | Named callable `removePoster` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1897` | `posterClickHandler` | Named callable `posterClickHandler` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1940` | `init` | Named callable `init` in the module; see its JSDoc and callers for parameter and side-effect details. |
| `amd/src/vimeo_player.js:1971` | `getDuration` | Named callable `getDuration` in the module; see its JSDoc and callers for parameter and side-effect details. |
