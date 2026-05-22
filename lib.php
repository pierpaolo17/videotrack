<?php

defined('MOODLE_INTERNAL') || die();

use mod_videotrack\local\tracker;

require_once(__DIR__ . '/locallib.php');

function videotrack_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
        case FEATURE_SHOW_DESCRIPTION:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_BACKUP_MOODLE2:   // Required: enables course backup/restore.
            return true;
        case FEATURE_GRADE_HAS_GRADE:   // Grading support enabled.
            return true;
        case FEATURE_GROUPS:           // Explicit: groups not supported.
        case FEATURE_GROUPINGS:        // Explicit: groupings not supported.
            return false;
        case FEATURE_MOD_PURPOSE:      // Moodle 4+: activity chooser category.
            return MOD_PURPOSE_CONTENT;
        default:
            return null;
    }
}

/**
 * Restituisce un oggetto con soli i campi della tabella {videotrack}.
 * Evita che $data del form (con campi extra come videofile, posterimage,
 * reactionlabel_, ecc.) venga passato direttamente a insert/update_record,
 * il che causerebbe eccezioni DB su colonne inesistenti.
 *
 * @param stdClass $data  Dati grezzi dal form.
 * @return stdClass       Oggetto con soli campi tabella.
 */
function videotrack_whitelist_record(stdClass $data): stdClass {
    static $columns = null;
    if ($columns === null) {
        global $DB;
        $columns = array_keys($DB->get_columns('videotrack'));
    }
    $record = new stdClass();
    foreach ($columns as $col) {
        if (isset($data->$col)) {
            $record->$col = $data->$col;
        }
    }
    return $record;
}

function videotrack_add_instance($data, $mform = null) {
    global $DB;
    $data->durationseconds = 0;
    $data->timecreated     = time();
    $data->timemodified    = $data->timecreated;
    $data->videosource     = $data->videosource ?? 'youtube';

    videotrack_process_video_fields($data, $mform);
    videotrack_process_grade_fields($data);
    videotrack_process_playbackspeeds_field($data);
    videotrack_process_player_behavior_fields($data);
    videotrack_process_html5controls_field($data);
    videotrack_process_captions_fields($data);

    if (!empty($data->reactionnotice_editor) && is_array($data->reactionnotice_editor)) {
        $data->reactionnotice       = $data->reactionnotice_editor['text'] ?? '';
        $data->reactionnoticeformat = $data->reactionnotice_editor['format'] ?? FORMAT_HTML;
    }
    $id     = $DB->insert_record('videotrack', videotrack_whitelist_record($data));
    $data->id = $id;

    // Save uploaded video file (if any).
    if ($data->videosource === 'upload') {
        videotrack_save_uploaded_video($id, $data);
    }
    // C5: Save poster image (all sources).
    videotrack_save_poster_image($id, $data);

    videotrack_save_reaction_definitions($id, $data);
    videotrack_grade_item_update($data);
    return $id;
}

/**
 * Updates an existing videotrack activity instance.
 *
 * @param  stdClass       $data   Form data from mod_form.
 * @param  moodleform|null $mform  The form object (unused).
 * @return bool                    True on success.
 */
function videotrack_update_instance($data, $mform = null) {
    global $DB;
    $data->id          = $data->instance;
    $data->timemodified = time();
    $data->videosource = $data->videosource ?? 'youtube';

    videotrack_process_video_fields($data, $mform);
    videotrack_process_grade_fields($data);
    videotrack_process_playbackspeeds_field($data);
    videotrack_process_player_behavior_fields($data);
    videotrack_process_html5controls_field($data);
    videotrack_process_captions_fields($data);

    if (!empty($data->reactionnotice_editor) && is_array($data->reactionnotice_editor)) {
        $data->reactionnotice       = $data->reactionnotice_editor['text'] ?? '';
        $data->reactionnoticeformat = $data->reactionnotice_editor['format'] ?? FORMAT_HTML;
    }
    $result = $DB->update_record('videotrack', videotrack_whitelist_record($data));

    if ($data->videosource === 'upload') {
        videotrack_save_uploaded_video($data->id, $data);
    }
    // C5: Save poster image (all sources).
    videotrack_save_poster_image($data->id, $data);

    videotrack_save_reaction_definitions($data->id, $data);
    videotrack_grade_item_update($data);
    return $result;
}

/**
 * Normalises video-source-specific fields before DB write.
 * Sets videosource, videoid, videourl appropriately.
 *
 * @param stdClass      $data
 * @param moodleform|null $mform
 */
function videotrack_process_video_fields(stdClass $data, $mform = null): void {
    $source = $data->videosource ?? 'youtube';

    if ($source === 'youtube') {
        $url = trim((string)($data->youtubeurl ?? ''));
        $id  = videotrack_extract_videoid($url);
        if (!$id) {
            throw new moodle_exception('invalidyoutubeurl', 'mod_videotrack');
        }
        $data->videoid  = $id;
        $data->videourl = $url;

    } else if ($source === 'vimeo') {
        $url = trim((string)($data->vimeourl ?? ''));
        $id  = videotrack_extract_vimeo_id($url);
        if (!$id) {
            throw new moodle_exception('invalidvimeourl', 'mod_videotrack');
        }
        $data->videoid  = $id;
        $data->videourl = $url;
        $data->youtubeurl = null;

    } else if ($source === 'upload') {
        // videoid and videourl will be set after file_save_draft_area_files.
        $data->videoid    = '';
        $data->videourl   = 'upload';
        $data->youtubeurl = null;
    }
}

/**
 * Normalises the playbackspeeds field submitted from mod_form checkboxes.
 *
 * The form creates individual fields named playbackspeed_0.75, playbackspeed_1, etc.
 * (not a single array field) because advcheckbox inside a group works that way.
 * This function aggregates those individual values into a comma-separated string
 * stored in $data->playbackspeeds.
 *
 * @param stdClass $data  Form data, modified in place.
 */
function videotrack_process_playbackspeeds_field(stdClass $data): void {
    // Collect individual playbackspeed_N checkbox values.
    $allpossible = ['0.25', '0.5', '0.75', '1', '1.25', '1.5', '1.75', '2', '3', '4'];
    $selected = [];
    foreach ($allpossible as $v) {
        $key = 'playbackspeed_' . $v;
        // advcheckbox returns the unchecked value (0) or the checked value ($v).
        if (isset($data->{$key}) && (float)$data->{$key} > 0) {
            $selected[] = (float)$v;
        }
        // Clean up individual fields so they don't pollute the DB record.
        unset($data->{$key});
    }

    if (!empty($selected)) {
        sort($selected);
        $data->playbackspeeds = implode(',', $selected);
    } else {
        // No checkbox selected → use site default (empty string).
        $data->playbackspeeds = '';
    }
}

/**
 * Saves the uploaded video file into the mod_videotrack filearea.
 *
 * @param int      $instanceid
 * @param stdClass $data
 */
function videotrack_save_uploaded_video(int $instanceid, stdClass $data): void {
    if (empty($data->coursemodule)) {
        return;
    }
    $context     = context_module::instance((int)$data->coursemodule);
    $draftitemid = (int)($data->videofile ?? 0);
    if ($draftitemid > 0) {
        file_save_draft_area_files($draftitemid, $context->id, 'mod_videotrack', 'videocontent', 0, [
            'subdirs'        => false,
            'maxfiles'       => 1,
            'accepted_types' => ['.mp4', '.webm', '.mp3', '.m4v', '.mov', '.aac', '.m4a'],
        ]);
    }
}

/**
 * Returns the URL of the uploaded video file for an instance, or null if none.
 *
 * @param  int     $instanceid
 * @param  int     $cmid
 * @return moodle_url|null
 */
function videotrack_get_upload_url(int $instanceid, int $cmid): ?moodle_url {
    $context = context_module::instance($cmid);
    $fs      = get_file_storage();
    $files   = $fs->get_area_files($context->id, 'mod_videotrack', 'videocontent', 0, 'id', false);
    if (empty($files)) {
        return null;
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $context->id, 'mod_videotrack', 'videocontent', 0,
        $file->get_filepath(), $file->get_filename()
    );
}



/**
 * Resolves the context_module for a form data object during instance save.
 * Checks coursemodule, cmid and falls back to get_coursemodule_from_instance.
 *
 * @param  stdClass  $data        Form data.
 * @param  int       $instanceid  Videotrack instance ID (used for fallback lookup).
 * @return context_module|null
 */
function videotrack_get_module_context_from_data(stdClass $data, int $instanceid = 0): ?context_module {
    if (!empty($data->coursemodule)) {
        return context_module::instance((int)$data->coursemodule);
    }
    if (!empty($data->cmid)) {
        return context_module::instance((int)$data->cmid);
    }
    if ($instanceid > 0 && !empty($data->course)) {
        $cm = get_coursemodule_from_instance('videotrack', $instanceid, (int)$data->course, false, IGNORE_MISSING);
        if ($cm) {
            return context_module::instance((int)$cm->id);
        }
    }
    return null;
}

/**
 * Saves the uploaded poster/preview image into the 'posterimage' filearea.
 * Called from add_instance and update_instance.
 *
 * @param int      $instanceid
 * @param stdClass $data        Form data (must have coursemodule and posterimage draft itemid).
 */
function videotrack_save_poster_image(int $instanceid, stdClass $data): void {
    if (empty($data->coursemodule)) {
        return;
    }
    $context     = context_module::instance((int)$data->coursemodule);
    $draftitemid = (int)($data->posterimage ?? 0);

    // Se draftitemid è 0, il docente non ha interagito con il filepicker:
    // non chiamiamo file_save_draft_area_files per evitare la cancellazione
    // dell'immagine poster esistente (file_save con itemid=0 cancella la filearea).
    // Il filepicker Moodle invia sempre un itemid > 0 quando è stato toccato,
    // anche se l'utente ha rimosso il file (la draft area esiste ma è vuota).
    if ($draftitemid <= 0) {
        return;
    }

    file_save_draft_area_files($draftitemid, $context->id, 'mod_videotrack', 'posterimage', 0, [
        'subdirs'        => false,
        'maxfiles'       => 1,
        'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
    ]);
}

function videotrack_save_reaction_definitions(int $videotrackid, stdClass $data): void {
    global $DB;

    $context = videotrack_get_module_context_from_data($data, $videotrackid);
    $existing = $DB->get_records('videotrack_react', ['videotrackid' => $videotrackid], '', 'id, sortorder');
    $labels = $data->reactionlabel ?? [];
    $descriptions = $data->reactiondescription ?? [];
    $icontypes = $data->reactionicontype ?? [];
    $iconvalues = $data->reactioniconvalue ?? [];
    $requireds = $data->reactionrequired ?? [];
    $reactionids = $data->reactionid ?? [];

    // B3 fix: wrap all DB writes in a delegated transaction.
    // Without this, a failure mid-loop (e.g. on the 3rd of 5 reactions) left the
    // reaction table in a partially updated state with no rollback path.
    // File-area operations (file_save_draft_area_files, delete_area_files) are NOT
    // transactional and must run AFTER allow_commit() — collected in $fileops below.
    $transaction = $DB->start_delegated_transaction();
    $keptids = [];
    $sort = 1;
    $now = time();
    // O1 fix: collect file operations to execute after the DB transaction commits.
    // Previously file_get_draft_area_info() was called for every reaction regardless
    // of icontype, wasting I/O for emoji and Font Awesome reactions.
    $fileops = []; // Each entry: ['reactionid'=>int, 'context'=>ctx, 'draftitemid'=>int, 'clear'=>bool]

    foreach ($labels as $idx => $label) {
        $label = trim((string)$label);
        if ($label === '') {
            continue;
        }

        $icontype = in_array(($icontypes[$idx] ?? 'emoji'), ['emoji', 'fa', 'file']) ? $icontypes[$idx] : 'emoji';
        $reactionid = (int)($reactionids[$idx] ?? 0);
        $basekey = clean_param(core_text::strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', $label)), PARAM_ALPHANUMEXT);
        $basekey = trim($basekey, '_');
        if ($basekey === '') {
            $basekey = 'reaction';
        }
        // Keep keys stable enough for reporting but avoid collisions such as "like!" vs "like_".
        $reactionkey = core_text::substr($basekey, 0, 80) . '_' . $sort . '_' . substr(hash('sha256', $label . ':' . $sort), 0, 8);

        $record = (object)[
            'videotrackid' => $videotrackid,
            'reactionkey' => $reactionkey,
            'label' => $label,
            'description' => trim((string)($descriptions[$idx] ?? '')),
            'icontype' => $icontype,
            'iconvalue' => trim((string)($iconvalues[$idx] ?? '')),
            'requiredforcompletion' => empty($requireds[$idx]) ? 0 : 1,
            'sortorder' => $sort,
            'isdeleted' => 0,  // Esplicito: resetta soft-delete se la reazione viene riattivata.
            'timemodified' => $now,
        ];
        if ($icontype === 'file') {
            $record->iconvalue = '';
        } else if ($icontype === 'emoji') {
            $record->iconvalue = clean_param($record->iconvalue, PARAM_TEXT);
        } else if ($icontype === 'fa') {
            $record->iconvalue = clean_param($record->iconvalue, PARAM_NOTAGS);
            if (!preg_match('/^[a-z0-9 \-]+$/i', $record->iconvalue)) {
                $record->iconvalue = 'fa-regular fa-circle';
            }
        }

        if ($reactionid > 0 && isset($existing[$reactionid])) {
            $record->id = $reactionid;
            $DB->update_record('videotrack_react', $record);
        } else {
            $record->timecreated = $now;
            $reactionid = $DB->insert_record('videotrack_react', $record);
        }

        $keptids[$reactionid] = true;

        // O1 fix: collect file operations — defer until after DB commit.
        // file_get_draft_area_info() is called only for 'file' icontype.
        if ($context) {
            if ($icontype === 'file') {
                $fieldname   = 'reactioniconfile_' . $idx;
                $draftitemid = isset($data->{$fieldname}) ? (int)$data->{$fieldname} : 0;
                // O1 fix: file_get_draft_area_info() now called only for 'file' reactions.
                $draftinfo   = $draftitemid > 0 ? file_get_draft_area_info($draftitemid) : ['filecount' => 0];
                if ($draftitemid > 0 && !empty($draftinfo['filecount'])) {
                    $fileops[] = ['reactionid' => $reactionid, 'context' => $context,
                                  'draftitemid' => $draftitemid, 'clear' => true];
                }
            } else if ($reactionid > 0) {
                $fileops[] = ['reactionid' => $reactionid, 'context' => $context,
                              'draftitemid' => 0, 'clear' => true];
            }
        }

        $sort++;
    }

    foreach ($existing as $oldreactionid => $oldreaction) {
        if (!isset($keptids[$oldreactionid])) {
            // Soft-delete: keep the definition and its file area intact.
            // Historical reports/events may still reference this reaction and should
            // keep rendering the original icon when available.
            $DB->set_field('videotrack_react', 'isdeleted', 1, ['id' => $oldreactionid]);
        }
    }

    // B3 fix: commit DB transaction before file operations (files are not transactional).
    $transaction->allow_commit();

    // Execute file-area operations after the DB commit.
    if ($fileops) {
        $fs = get_file_storage();
        foreach ($fileops as $op) {
            $fs->delete_area_files($op['context']->id, 'mod_videotrack', 'reactionicon', $op['reactionid']);
            if ($op['draftitemid'] > 0) {
                file_save_draft_area_files($op['draftitemid'], $op['context']->id, 'mod_videotrack',
                    'reactionicon', $op['reactionid'], [
                        'subdirs'        => 0,
                        'maxfiles'       => 1,
                        'accepted_types' => ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
                    ]);
                // Ridimensiona a 64×64px (crop centrato) dopo il salvataggio.
                videotrack_resize_reaction_icon($op['context'], $op['reactionid'], $fs);
            }
        }
    }
}

/**
 * Returns a summary of a student's viewing progress for the activity outline.
 * Shown in the "Activity report" page of the participant profile.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $videotrack
 * @return stdClass
 */
function videotrack_user_outline($course, $user, $mod, $videotrack) {
    global $DB;
    $return       = new stdClass();
    $return->time = 0;
    $return->info = '';
    $state = $DB->get_record('videotrack_state', [
        'videotrackid' => $videotrack->id,
        'userid'       => $user->id,
    ]);
    if ($state) {
        $return->info = get_string('outline:percent', 'mod_videotrack',
            format_float((float)$state->completionpercent, 1));
        $return->time = (int)$state->timemodified;
    }
    return $return;
}

/**
 * Prints a detailed view of a student's watching history for the activity.
 * Shown in the "Activity report" page of the participant profile.
 *
 * @param stdClass $course
 * @param stdClass $user
 * @param stdClass $mod
 * @param stdClass $videotrack
 */
function videotrack_user_complete($course, $user, $mod, $videotrack) {
    global $DB;
    $state = $DB->get_record('videotrack_state', [
        'videotrackid' => $videotrack->id,
        'userid'       => $user->id,
    ]);
    if (!$state) {
        echo html_writer::tag('p', get_string('outline:nodata', 'mod_videotrack'),
            ['class' => 'text-muted']);
        return;
    }
    $table            = new html_table();
    $table->attributes['class'] = 'generaltable';
    $table->data = [
        [get_string('report:uniquecoveredseconds', 'mod_videotrack'),
         videotrack_format_seconds((float)$state->uniquecoveredseconds)],
        [get_string('report:completionpercent', 'mod_videotrack'),
         format_float((float)$state->completionpercent, 1) . '%'],
        [get_string('report:lastposition', 'mod_videotrack'),
         videotrack_format_seconds((float)$state->lastposition)],
        [get_string('report:iscompleted', 'mod_videotrack'),
         $state->iscompleted
             ? get_string('yes', 'mod_videotrack')
             : get_string('no',  'mod_videotrack')],
    ];
    echo html_writer::table($table);
}

/**
 * Adds a "Report" link to the activity's settings navigation (secondary nav).
 *
 * @param settings_navigation $settings
 * @param navigation_node     $videotracknode
 */
function videotrack_extend_settings_navigation($settings, $videotracknode) {
    global $PAGE;
    $keys      = $videotracknode->get_children_key_list();
    $beforekey = null;
    $i = array_search('modedit', $keys);
    if ($i === false && array_key_exists(0, $keys)) {
        $beforekey = $keys[0];
    } else if (array_key_exists($i + 1, $keys)) {
        $beforekey = $keys[$i + 1];
    }
    if (has_capability('mod/videotrack:viewreport', $PAGE->cm->context)) {
        $node = navigation_node::create(
            get_string('reportteacher', 'mod_videotrack'),
            new moodle_url('/mod/videotrack/report.php', ['id' => $PAGE->cm->id]),
            navigation_node::TYPE_SETTING,
            null,
            'mod_videotrack_report',
            new pix_icon('i/report', '')
        );
        $videotracknode->add_node($node, $beforekey);
    }
}

/**
 * Adds a "Video track reports" link to the course reports navigation node.
 *
 * @param navigation_node $navigation
 * @param stdClass        $course
 * @param context         $context
 */
function videotrack_extend_navigation_course($navigation, $course, $context) {
    $node = $navigation->get('coursereports');
    if (!$node || !has_capability('mod/videotrack:viewcoursereport', $context)) {
        return;
    }

    $url = new moodle_url('/mod/videotrack/reports_course.php', ['course' => $course->id]);
    $node->add(
        get_string('coursereport:navlink', 'mod_videotrack'),
        $url,
        navigation_node::TYPE_SETTING,
        null, null,
        new pix_icon('i/report', '')
    );
}


/**
 * Returns the effective list of HTML5 player controls for an activity.
 * Instance setting overrides site default when present.
 *
 * @param  stdClass  $videotrack
 * @return string[]   Array of control identifiers.
 */
function videotrack_get_html5controls(stdClass $videotrack): array {
    $raw = !empty($videotrack->html5controls)
        ? $videotrack->html5controls
        : (string)get_config('mod_videotrack', 'html5controls');
    if (empty($raw)) {
        $raw = 'play,progress,current,duration,mute,volume,speed,fullscreen';
    }
    return array_values(array_filter(array_map('trim', explode(',', $raw))));
}

/**
 * Normalises html5controls field: aggregates html5ctrl_* checkbox fields
 * into a comma-separated string in $data->html5controls.
 *
 * @param stdClass $data
 */
function videotrack_process_html5controls_field(stdClass $data): void {
    $allpossible = ['play', 'rewind', 'fastforward', 'progress', 'current', 'duration',
                    'mute', 'volume', 'speed', 'pip', 'fullscreen', 'download'];
    $selected = [];
    foreach ($allpossible as $ctrl) {
        $key = 'html5ctrl_' . $ctrl;
        if (!empty($data->{$key})) {
            $selected[] = $ctrl;
        }
        unset($data->{$key});
    }
    $data->html5controls = $selected ? implode(',', $selected) : '';
}

/**
 * Normalises autoplay/loop/startmuted/allowdownload boolean fields.
 *
 * @param stdClass $data
 */
function videotrack_process_player_behavior_fields(stdClass $data): void {
    foreach (['autoplay', 'loop', 'startmuted', 'allowdownload', 'resumeplayback',
              'showtranscript', 'showchapters', 'studentnotesenabled'] as $field) {
        $data->{$field} = empty($data->{$field}) ? 0 : 1;
    }
    // maxplaybackrate: intero in centesimi (0=nessun limite, 150=1.5×, ecc.).
    $data->maxplaybackrate = (int)($data->maxplaybackrate ?? 0);
}

/**
 * Returns the effective max player width in px.
 *
 * Instance value 0 means "use the site default". The site setting is now
 * validated as 1..4096, so invalid legacy values fall back to 960 in one place.
 *
 * @param  stdClass  $videotrack
 * @return int  Width in px.
 */
function videotrack_get_player_width(stdClass $videotrack): int {
    $w = (int)($videotrack->playerwidth ?? 0);
    if ($w > 0) {
        return max(1, min(4096, $w));
    }

    return videotrack_get_config_int('playerwidth', 960, 1, 4096);
}

/**
 * Returns effective rewind step in seconds (instance override → site default → 10).
 *
 * @param  stdClass  $videotrack
 * @return int
 */
function videotrack_get_rewind_step(stdClass $videotrack): int {
    $v = (int)($videotrack->rewindstep ?? 0);
    if ($v > 0) {
        return min(300, $v);
    }

    $site = get_config('mod_videotrack', 'rewindstep');
    if ($site === false || $site === null || $site === '') {
        return 10;
    }

    // Site-level 0 disables the default. Activity-level overrides may still re-enable the button.
    return max(0, min(300, (int)$site));
}

/**
 * Returns effective fast-forward step in seconds (instance override → site default → 10).
 *
 * @param  stdClass  $videotrack
 * @return int
 */
function videotrack_get_fastforward_step(stdClass $videotrack): int {
    $v = (int)($videotrack->fastforwardstep ?? 0);
    if ($v > 0) {
        return min(300, $v);
    }

    $site = get_config('mod_videotrack', 'fastforwardstep');
    if ($site === false || $site === null || $site === '') {
        return 10;
    }

    // Site-level 0 disables the default. Activity-level overrides may still re-enable the button.
    return max(0, min(300, (int)$site));
}

/**
 * Returns the URL of the VTT subtitle file for an upload instance, or null.
 *
 * @param  int  $cmid
 * @return moodle_url|null
 */
function videotrack_get_vtt_url(int $cmid): ?moodle_url {
    $context = context_module::instance($cmid);
    $fs      = get_file_storage();
    $files   = $fs->get_area_files($context->id, 'mod_videotrack', 'subtitles', 0, 'id', false);
    if (empty($files)) {
        return null;
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $context->id, 'mod_videotrack', 'subtitles', 0,
        $file->get_filepath(), $file->get_filename()
    );
}

/**
 * Normalises captions fields from form data.
 *
 * @param stdClass $data
 */
function videotrack_process_captions_fields(stdClass $data): void {
    $data->captions     = empty($data->captions) ? 0 : 1;
    $data->captionslang = trim((string)($data->captionslang ?? ''));
    // Save VTT file if uploaded (upload source only).
    if (!empty($data->coursemodule) && !empty($data->vttfile)) {
        $context     = context_module::instance((int)$data->coursemodule);
        $draftitemid = (int)$data->vttfile;
        if ($draftitemid > 0) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'mod_videotrack', 'subtitles', 0);
            file_save_draft_area_files($draftitemid, $context->id, 'mod_videotrack', 'subtitles', 0, [
                'subdirs'        => false,
                'maxfiles'       => 1,
                'accepted_types' => ['.vtt'],
            ]);
        }
    }
    unset($data->vttfile);
}

/**
 * Normalises the grade-related fields submitted by mod_form before DB insert/update.
 * Moodle's standard grading elements submit 'grade' as a signed integer:
 *   0   = no grade
 *  >0   = numeric max points
 *  <0   = -(scale_id)
 * gradepass is submitted separately and must be stored alongside grade.
 *
 * @param stdClass $data  Form data, modified in place.
 */
function videotrack_process_grade_fields(stdClass $data): void {
    // Ensure grade is always a clean integer.
    $data->grade = (int)($data->grade ?? 0);

    // Clamp gradepass to [0, grade] for numeric grades.
    if ($data->grade > 0 && isset($data->gradepass)) {
        $data->gradepass = min((float)$data->gradepass, (float)$data->grade);
        $data->gradepass = max(0.0, $data->gradepass);
    } elseif ($data->grade == 0) {
        $data->gradepass = 0;
    }

    $data->showgradeto = empty($data->showgradeto) ? 0 : 1;
}

/**
 * Creates or updates the grade item in the Moodle gradebook for this activity.
 *
 * Called by add_instance, update_instance and videotrack_grade_item_update.
 * The $videotrack->grade field encodes the grading type:
 *   0  = no grade
 *  >0  = numeric, max points = grade
 *  <0  = scale, scale id = abs(grade)
 *
 * @param  stdClass   $videotrack  Instance record (must have ->grade and ->gradepass).
 * @param  mixed      $grades      Optional: specific grades array to push, or 'reset'.
 * @return int                     Result of grade_update().
 */
function videotrack_grade_item_update(stdClass $videotrack, $grades = null): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname'  => $videotrack->name,
        'idnumber'  => $videotrack->cmidnumber ?? '',
    ];

    if (!isset($videotrack->grade) || $videotrack->grade == 0) {
        // No grading: delete the grade item if it exists.
        $params['gradetype'] = GRADE_TYPE_NONE;
    } elseif ($videotrack->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = (float)$videotrack->grade;
        $params['grademin']  = 0;
    } else {
        // Negative value encodes a scale id.
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -(int)$videotrack->grade;
    }

    // Set passing grade in the gradebook item.
    if (!empty($videotrack->gradepass)) {
        $params['gradepass'] = (float)$videotrack->gradepass;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update(
        'mod/videotrack',
        $videotrack->course,
        'mod',
        'videotrack',
        $videotrack->id,
        0,
        $grades,
        $params
    );
}

/**
 * Pushes a single user grade to the Moodle gradebook.
 *
 * @param stdClass  $videotrack  Instance record.
 * @param int       $userid      User being graded.
 * @param float     $gradevalue  Raw grade value (points or scale item id).
 * @return int                   Result of grade_update().
 */
function videotrack_set_user_grade(stdClass $videotrack, int $userid, float $gradevalue): int {
    $grades = [
        $userid => (object)[
            'userid'   => $userid,
            'rawgrade' => $gradevalue,
        ],
    ];
    return videotrack_grade_item_update($videotrack, $grades);
}

/**
 * Returns a user's current raw grade for this activity, or null if not graded.
 *
 * @param  stdClass  $videotrack  Instance record.
 * @param  int       $userid
 * @return float|null
 */
function videotrack_get_user_grade(stdClass $videotrack, int $userid): ?float {
    global $CFG, $DB;
    require_once($CFG->libdir . '/gradelib.php');

    $grades = grade_get_grades(
        $videotrack->course,
        'mod',
        'videotrack',
        $videotrack->id,
        $userid
    );

    if (empty($grades->items[0]->grades[$userid]->grade)) {
        return null;
    }
    return (float)$grades->items[0]->grades[$userid]->grade;
}

/**
 * Returns the URL of the poster/preview image for this instance, or null if not set.
 * The image is stored in the filearea 'posterimage' with itemid=0.
 *
 * @param  int  $cmid
 * @return moodle_url|null
 */
function videotrack_get_poster_url(int $cmid): ?moodle_url {
    $context = context_module::instance($cmid);
    $fs      = get_file_storage();
    $files   = $fs->get_area_files($context->id, 'mod_videotrack', 'posterimage', 0, 'filename', false);
    if (empty($files)) {
        return null;
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $context->id, 'mod_videotrack', 'posterimage', 0, '/', $file->get_filename()
    );
}

/**
 * Deletes the viewing progress for a single user in a videotrack instance.
 * Used by the teacher report when only viewing progress needs to be reset.
 * Removes: videotrack_state, videotrack_seg, reaction events and personal notes
 * for this user+instance.
 *
 * @param  stdClass  $videotrack  Instance record.
 * @param  int       $userid      The user whose progress to reset.
 * @return void
 */
function videotrack_delete_user_progress(stdClass $videotrack, int $userid): void {
    global $DB;
    $DB->delete_records('videotrack_state', [
        'videotrackid' => $videotrack->id,
        'userid'       => $userid,
    ]);
    $DB->delete_records('videotrack_seg', [
        'videotrackid' => $videotrack->id,
        'userid'       => $userid,
    ]);
    // B2 fix: delete reactions and personal notes so the reset is complete.
    // Without this, reactions/notes survive the reset and still appear in the
    // student view and influence completion. Mirrors the behaviour of the
    // per-student reset in report.php (which already deletes videotrack_reactev).
    $DB->delete_records('videotrack_reactev', [
        'videotrackid' => $videotrack->id,
        'userid'       => $userid,
    ]);
}

/**
 * Removes the grade item from the gradebook when the activity is deleted.
 *
 * @param  stdClass  $videotrack  Instance record.
 * @return int                    Result of grade_update().
 */
function videotrack_grade_item_delete(stdClass $videotrack): int {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');
    return grade_update(
        'mod/videotrack',
        $videotrack->course,
        'mod',
        'videotrack',
        $videotrack->id,
        0,
        null,
        ['deleted' => 1]
    );
}

function videotrack_delete_instance($id) {
    global $DB;
    if (!$videotrack = $DB->get_record('videotrack', ['id' => $id])) {
        return false;
    }
    $cm = get_coursemodule_from_instance('videotrack', $id, $videotrack->course, false, IGNORE_MISSING);
    if ($cm) {
        $context = context_module::instance($cm->id);
        $fs = get_file_storage();
        // Cancella tutte le filearea del plugin per questo contesto.
        foreach (['reactionicon', 'videocontent', 'subtitles', 'intro', 'posterimage'] as $area) {
            $fs->delete_area_files($context->id, 'mod_videotrack', $area);
        }
    }
    $DB->delete_records('videotrack_seg',     ['videotrackid' => $videotrack->id]);
    $DB->delete_records('videotrack_state',   ['videotrackid' => $videotrack->id]);
    $DB->delete_records('videotrack_reactev', ['videotrackid' => $videotrack->id]);
    $DB->delete_records('videotrack_react',   ['videotrackid' => $videotrack->id]);
    $DB->delete_records('videotrack',         ['id'           => $videotrack->id]);
    videotrack_grade_item_delete($videotrack);
    return true;
}

function videotrack_get_coursemodule_info($coursemodule) {
    global $DB;
    if (!$videotrack = $DB->get_record('videotrack', ['id' => $coursemodule->instance], '*', IGNORE_MISSING)) {
        return null;
    }
    $info = new cached_cm_info();
    $info->name = $videotrack->name;
    if ($coursemodule->showdescription) {
        $info->content = format_module_intro('videotrack', $videotrack, $coursemodule->id, false);
    }
    return $info;
}

function videotrack_view($videotrack, $course, $cm, $context) {
    $event = \mod_videotrack\event\course_module_viewed::create([
        'objectid' => $videotrack->id,
        'context' => $context,
    ]);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('videotrack', $videotrack);
    $event->trigger();
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

function videotrack_get_completion_active_rule_descriptions($cm) {
    global $DB;
    $descriptions = [];
    $videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
    if (!empty($videotrack->completionpercent)) {
        $descriptions[] = get_string('completiondetail:percent', 'mod_videotrack', $videotrack->completionpercent);
    }
    if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
        $descriptions[] = get_string('completiondetail:minreactions', 'mod_videotrack', $videotrack->minreactions);
    }
    $requiredreactions = $DB->get_records('videotrack_react', [
        'videotrackid' => $videotrack->id,
        'requiredforcompletion' => 1,
        'isdeleted' => 0,
    ], 'sortorder ASC, id ASC', 'id,label');
    if (!empty($requiredreactions)) {
        $labels = array_map(static function($reaction) {
            return format_string($reaction->label);
        }, array_values($requiredreactions));
        $descriptions[] = get_string('completiondetail:requiredreactions', 'mod_videotrack', implode(', ', $labels));
    }
    if (!empty($videotrack->requireallreactiontypes)) {
        $descriptions[] = get_string('completiondetail:allreactiontypes', 'mod_videotrack');
    }
    return $descriptions;
}

function videotrack_update_completion_for_user(stdClass $videotrack, cm_info $cm, int $userid): void {
    $state = tracker::refresh_completion($videotrack, $cm, $userid);
    $completion = new completion_info(get_course($videotrack->course));
    if ($state->iscompleted) {
        $completion->update_state($cm, COMPLETION_COMPLETE, $userid);
    } else {
        $completion->update_state($cm, COMPLETION_INCOMPLETE, $userid);
    }
}

/**
 * Returns the items that can be reset in a course reset.
 * Called by Moodle when building the course reset form.
 *
 * @param object $mform  The course reset form.
 */
function videotrack_reset_course_userdata($data) {
    global $DB, $CFG;
    $status = [];
    $componentstr = get_string('modulename', 'mod_videotrack');

    if (!empty($data->reset_videotrack_userdata)) {
        $instances = $DB->get_records('videotrack', ['course' => $data->courseid], '', 'id,grade,course,name');
        require_once($CFG->libdir . '/gradelib.php');
        foreach ($instances as $instance) {
            $DB->delete_records('videotrack_seg',     ['videotrackid' => $instance->id]);
            $DB->delete_records('videotrack_state',   ['videotrackid' => $instance->id]);
            $DB->delete_records('videotrack_reactev', ['videotrackid' => $instance->id]);
            // Azzera anche i voti nel gradebook per questa istanza.
            if (!empty($instance->grade)) {
                grade_update('mod/videotrack', $data->courseid, 'mod', 'videotrack',
                    $instance->id, 0, null, ['reset' => true]);
            }
        }
        $status[] = [
            'component' => $componentstr,
            'item'      => get_string('reset:userdata', 'mod_videotrack'),
            'error'     => false,
        ];
    }
    return $status;
}

/**
 * Populates the course reset form with videotrack-specific options.
 * Moodle HQ hook: called when building the course reset form.
 *
 * @param object $mform  The course reset form (MoodleQuickForm).
 */
function videotrack_reset_course_form_definition($mform) {
    $mform->addElement('header', 'videotrackheader',
        get_string('modulename', 'mod_videotrack'));
    $mform->addElement('checkbox', 'reset_videotrack_userdata',
        get_string('modulename', 'mod_videotrack'),
        get_string('reset:userdata', 'mod_videotrack'));
}

/**
 * Returns default values for the VideoTrack course reset form options.
 *
 * @param stdClass $course Course record.
 * @return array Default reset options.
 */
function videotrack_reset_course_form_defaults($course) {
    return ['reset_videotrack_userdata' => 0];
}

/**
 * Ridimensiona l'icona di reazione a 64×64px con crop centrato.
 *
 * Legge il file dall'area di storage Moodle, lo ridimensiona con GD,
 * e sovrascrive il file originale con la versione ridimensionata in PNG.
 * Se GD non è disponibile o il formato non è supportato, il file viene
 * lasciato invariato (nessun errore bloccante — il CSS gestisce le dimensioni).
 *
 * @param context_module    $context    Contesto del modulo.
 * @param int               $reactionid ID della reazione (itemid del filearea).
 * @param file_storage      $fs         Istanza del file storage Moodle.
 */
function videotrack_resize_reaction_icon(context_module $context, int $reactionid, file_storage $fs): void {
    if (!function_exists('imagecreatefromstring')) {
        // GD non disponibile: il ridimensionamento non avviene.
        // L'avviso all'admin è già visibile nella pagina impostazioni (settings.php)
        // e nel check environment.xml. Non blocchiamo il salvataggio.
        debugging('mod_videotrack: GD PHP extension is not available. ' .
            'Reaction icon for reactionid=' . $reactionid . ' was NOT resized to 64×64px. ' .
            'Install php-gd to enable automatic icon resizing.',
            DEBUG_NORMAL);
        return;
    }

    $files = $fs->get_area_files($context->id, 'mod_videotrack', 'reactionicon', $reactionid, '', false);
    if (empty($files)) {
        return;
    }
    $file = reset($files);

    $srcdata  = $file->get_content();
    // PHP 8.0+ lancia ValueError (non Warning) per dati non immagine.
    // try/catch è necessario; @ non cattura Error/ValueError.
    try {
        $srcimage = imagecreatefromstring($srcdata);
    } catch (\Throwable $e) {
        $srcimage = false;
    }
    if (!$srcimage) {
        // Formato non supportato da GD (es. gif animata, webp su PHP < 7.2).
        return;
    }

    $srcw = imagesx($srcimage);
    $srch = imagesy($srcimage);
    $target = 64;

    // Crop centrato: calcola il quadrato massimo che sta nell'immagine originale.
    $cropsize = min($srcw, $srch);
    $cropx    = (int)(($srcw - $cropsize) / 2);
    $cropy    = (int)(($srch - $cropsize) / 2);

    $dst = imagecreatetruecolor($target, $target);
    // Sfondo trasparente per PNG.
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
    imagefill($dst, 0, 0, $transparent);

    imagecopyresampled($dst, $srcimage, 0, 0, $cropx, $cropy, $target, $target, $cropsize, $cropsize);
    imagedestroy($srcimage);

    // Salva come PNG in un buffer temporaneo.
    ob_start();
    imagepng($dst, null, 6); // Compressione 6 = buon bilanciamento qualità/dimensione.
    $pngdata = ob_get_clean();
    imagedestroy($dst);

    if (empty($pngdata)) {
        return;
    }

    // Sovrascrive il file nel filearea Moodle con la versione ridimensionata.
    $oldname  = $file->get_filename();
    $newname  = pathinfo($oldname, PATHINFO_FILENAME) . '.png';
    $filepath = $file->get_filepath();

    $file->delete();

    $filerecord = [
        'contextid' => $context->id,
        'component' => 'mod_videotrack',
        'filearea'  => 'reactionicon',
        'itemid'    => $reactionid,
        'filepath'  => $filepath,
        'filename'  => $newname,
        'mimetype'  => 'image/png',
        'timecreated'  => time(),
        'timemodified' => time(),
    ];
    $fs->create_file_from_string($filerecord, $pngdata);
}

/**
 * Serves files from the reactionicon filearea.
 *
 * @param stdClass      $course
 * @param stdClass      $cm
 * @param context       $context
 * @param string        $filearea
 * @param array         $args
 * @param bool          $forcedownload
 * @param array         $options
 * @return bool|void    False if file not found, otherwise sends the file.
 */
function videotrack_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }
    if (!in_array($filearea, ['reactionicon', 'videocontent', 'subtitles', 'posterimage'], true)) {
        return false;
    }
    $allowedextensions = [
        'reactionicon' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'posterimage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'subtitles' => ['vtt'],
        'videocontent' => ['mp4', 'webm', 'mp3', 'm4v', 'mov', 'aac', 'm4a'],
    ];
    require_login($course, true, $cm);
    if (!has_capability('mod/videotrack:view', $context)) {
        return false;
    }
    $itemid   = (int)array_shift($args);
    $filepath = count($args) > 1 ? ('/' . implode('/', array_slice($args, 0, -1)) . '/') : '/';
    $filename = end($args);
    if ($filename === false) {
        return false;
    }
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_videotrack', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        return false;
    }
    $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedextensions[$filearea], true)) {
        return false;
    }
    if (in_array($filearea, ['reactionicon', 'posterimage'], true)
            && strpos((string)$file->get_mimetype(), 'image/') !== 0) {
        return false;
    }
    if ($filearea === 'subtitles' && !in_array($file->get_mimetype(), ['text/vtt', 'text/plain'], true)) {
        return false;
    }
    if ($filearea === 'videocontent') {
        global $DB;
        $videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], 'id, allowdownload', MUST_EXIST);
        if (empty($videotrack->allowdownload) && $forcedownload) {
            return false;
        }
        $forcedownload = !empty($videotrack->allowdownload) ? $forcedownload : false;
    }
    // Uploaded videos: allow 1 hour browser caching. Poster: 5 min. Icons: no cache.
    $lifetime = ($filearea === 'videocontent') ? 3600 : (($filearea === 'posterimage') ? 300 : 0);
    send_stored_file($file, $lifetime, 0, $forcedownload, $options);
}

/**
 * Ricalcola lo stato aggregato (completionpercent, iscompleted) di tutti gli utenti
 * di una singola istanza videotrack a partire dai segmenti raw.
 * Utile dopo aver modificato la durata del video o i criteri di completamento.
 *
 * @param  int      $videotrackid   ID dell'istanza videotrack.
 * @param  cm_info  $cm             Course module info.
 * @return int                      Numero di record di stato aggiornati.
 */
function videotrack_recalculate_all_states(int $videotrackid, cm_info $cm): int {
    global $DB;
    $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid], '*', MUST_EXIST);
    $course     = get_course($videotrack->course);
    $completion = new completion_info($course);
    $updated    = 0;
    // O2: use get_recordset instead of get_records to avoid loading all state rows into
    // memory at once. On courses with hundreds of students get_records() would allocate
    // a large array; get_recordset() streams one row at a time.
    $rs = $DB->get_recordset('videotrack_state', ['videotrackid' => $videotrackid], '', 'userid');
    foreach ($rs as $staterow) {
        $state = tracker::refresh_completion($videotrack, $cm, (int)$staterow->userid);
        // Aggiorna anche il completamento Moodle (il tick ✓ nel corso).
        // refresh_completion aggiorna videotrack_state ma non la tabella course_modules_completion.
        $completion->update_state(
            $cm,
            $state->iscompleted ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE,
            (int)$staterow->userid
        );
        $updated++;
    }
    $rs->close();
    return $updated;
}
