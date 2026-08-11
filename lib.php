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

defined('MOODLE_INTERNAL') || die();

use mod_videotrack\local\completion_config;
use mod_videotrack\local\tracker;

require_once(__DIR__ . '/locallib.php');

/**
 * Returns the Moodle features supported by the activity module.
 *
 * @param string $feature Feature constant requested by Moodle.
 * @return mixed Supported value, false or null when not supported.
 */
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
        case FEATURE_MOD_ARCHETYPE:     // Activity chooser archetype for a content resource.
            return MOD_ARCHETYPE_RESOURCE;
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
 * Returns an object containing only fields from the {videotrack} table.
 * Prevents raw form data (including extra fields such as videofile, posterimage,
 * reactionlabel_, etc.) from being passed directly to insert/update_record,
 * which would otherwise trigger DB exceptions for non-existent columns.
 *
 * @param stdClass $data Raw form data.
 * @param bool $resetcache If true, refreshes the column cache (useful in tests).
 * @return stdClass Object containing table fields only.
 */
function videotrack_whitelist_record(stdClass $data, bool $resetcache = false): stdClass {
    static $columns = null;
    if ($resetcache) {
        $columns = null;
    }
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

/**
 * Adds a new VideoTrack activity instance.
 *
 * @param stdClass $data Submitted activity data.
 * @param moodleform|null $mform Submitted Moodle form.
 * @return int New activity id.
 */
function videotrack_add_instance($data, $mform = null) {
    global $DB;
    $data->durationseconds = max(0.0, min(86400.0, (float)($data->durationseconds ?? 0)));
    if ($data->durationseconds <= 0.0) {
        // A zero verified duration means that watched percentage is intentionally unused.
        $data->completionpercent = 0;
    }
    $data->timecreated     = time();
    $data->timemodified    = $data->timecreated;
    $data->videosource     = $data->videosource ?? 'youtube';

    videotrack_process_video_fields($data, $mform);
    videotrack_process_grade_fields($data);
    videotrack_process_playbackspeeds_field($data);
    videotrack_process_player_behavior_fields($data);
    videotrack_process_html5controls_field($data);
    videotrack_process_captions_fields($data);
    videotrack_process_forum_fields($data);
    videotrack_process_acknowledgement_fields($data);
    \mod_videotrack\local\csv_export::process_form_fields(
        $data,
        context_course::instance((int)$data->course)
    );

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
    // Save poster image and optional timed-text files for all sources.
    videotrack_save_poster_image($id, $data);
    \mod_videotrack\local\timed_text::save_files($data);

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
    $previous = $DB->get_record('videotrack', ['id' => $data->instance], '*', MUST_EXIST);
    $previouscompletionsignature = completion_config::signature($previous);
    $data->id          = $data->instance;
    $data->timemodified = time();
    $data->videosource = $data->videosource ?? 'youtube';
    $data->durationseconds = max(0.0, min(86400.0, (float)($data->durationseconds ?? 0)));
    if ($data->durationseconds <= 0.0) {
        // A zero verified duration means that watched percentage is intentionally unused.
        $data->completionpercent = 0;
    }

    videotrack_process_video_fields($data, $mform);
    videotrack_process_grade_fields($data);
    videotrack_process_playbackspeeds_field($data);
    videotrack_process_player_behavior_fields($data);
    videotrack_process_html5controls_field($data);
    videotrack_process_captions_fields($data);
    videotrack_process_forum_fields($data);
    videotrack_process_acknowledgement_fields($data);
    $csvcontext = !empty($data->coursemodule)
        ? context_module::instance((int)$data->coursemodule)
        : context_course::instance((int)$data->course);
    \mod_videotrack\local\csv_export::process_form_fields($data, $csvcontext);

    if (!empty($data->reactionnotice_editor) && is_array($data->reactionnotice_editor)) {
        $data->reactionnotice       = $data->reactionnotice_editor['text'] ?? '';
        $data->reactionnoticeformat = $data->reactionnotice_editor['format'] ?? FORMAT_HTML;
    }
    $result = $DB->update_record('videotrack', videotrack_whitelist_record($data));

    if ($data->videosource === 'upload') {
        videotrack_save_uploaded_video($data->id, $data);
    } else {
        videotrack_delete_upload_source_files($data->id, $data);
    }
    // Save poster image and optional timed-text files for all sources.
    videotrack_save_poster_image($data->id, $data);
    \mod_videotrack\local\timed_text::save_files($data);

    videotrack_save_reaction_definitions($data->id, $data);

    $updated = $DB->get_record('videotrack', ['id' => $data->id], '*', MUST_EXIST);
    if (
        !empty($data->coursemodule)
        && $previouscompletionsignature !== completion_config::signature($updated)
    ) {
        // Invalidate modinfo so the state rebuild uses the updated activity record.
        // During the normal module-edit flow Moodle resets native completion after
        // update_instance(), using a freshly rebuilt cm_info. Rebuild VideoTrack's
        // persisted state first, but avoid writing course_modules_completion twice.
        get_fast_modinfo((int)$data->course, 0, true);
        $cm = get_fast_modinfo((int)$data->course)->get_cm((int)$data->coursemodule);
        $synchronisemoodle = empty($data->completionunlocked);
        videotrack_recalculate_all_states((int)$data->id, $cm, 0, $synchronisemoodle);
    }

    videotrack_grade_item_update($data);
    return $result;
}

/**
 * Normalises and validates optional Forum integration fields before a database write.
 *
 * @param stdClass $data Activity record being saved.
 */
function videotrack_process_forum_fields(stdClass $data): void {
    $data->forumpostingenabled = empty($data->forumpostingenabled) ? 0 : 1;
    $data->linkedforumid = isset($data->linkedforumid) ? (int)$data->linkedforumid : 0;
    $template = clean_param(trim((string)($data->forumsubjecttemplate ?? '')), PARAM_TEXT);
    $data->forumsubjecttemplate = core_text::substr($template, 0, 255);
    if (!$data->forumpostingenabled) {
        $data->linkedforumid = 0;
        return;
    }
    if (!videotrack_is_compatible_forum((int)$data->course, $data->linkedforumid)) {
        throw new moodle_exception('forum:errorinvaliddestination', 'mod_videotrack');
    }
}

/**
 * Normalises the optional learner acknowledgement fields.
 *
 * @param stdClass $data Activity record being saved.
 */
function videotrack_process_acknowledgement_fields(stdClass $data): void {
    $data->acknowledgementenabled = empty($data->acknowledgementenabled) ? 0 : 1;
    $data->completionacknowledgement = empty($data->completionacknowledgement) ? 0 : 1;
    $timing = (int)($data->acknowledgementtiming ?? \mod_videotrack\local\acknowledgement::TIMING_ANYTIME);
    $data->acknowledgementtiming = in_array($timing, [
        \mod_videotrack\local\acknowledgement::TIMING_ANYTIME,
        \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END,
    ], true) ? $timing : \mod_videotrack\local\acknowledgement::TIMING_ANYTIME;
    if (!empty($data->acknowledgement_editor) && is_array($data->acknowledgement_editor)) {
        $data->acknowledgementtext = (string)($data->acknowledgement_editor['text'] ?? '');
        $data->acknowledgementformat = (int)($data->acknowledgement_editor['format'] ?? FORMAT_HTML);
    } else {
        $data->acknowledgementtext = (string)($data->acknowledgementtext ?? '');
        $data->acknowledgementformat = (int)($data->acknowledgementformat ?? FORMAT_HTML);
    }
    if (!$data->acknowledgementenabled) {
        $data->completionacknowledgement = 0;
    }
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
    $allowedsources = ['youtube', 'vimeo', 'upload'];
    if (!in_array($source, $allowedsources, true)) {
        throw new moodle_exception('invalidvideosource', 'mod_videotrack');
    }

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
        // Videoid and videourl will be set after file_save_draft_area_files.
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
        // Advcheckbox returns the unchecked value (0) or the checked value ($v).
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
 * Deletes upload-only files when an activity is no longer using the upload source.
 *
 * @param int $instanceid Videotrack instance id.
 * @param stdClass $data Form data.
 */
function videotrack_delete_upload_source_files(int $instanceid, stdClass $data): void {
    $context = videotrack_get_module_context_from_data($data, $instanceid);
    if (!$context) {
        return;
    }
    $fs = get_file_storage();
    $fs->delete_area_files($context->id, 'mod_videotrack', 'videocontent', 0);
    $fs->delete_area_files($context->id, 'mod_videotrack', 'subtitles', 0);
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
        $context->id,
        'mod_videotrack',
        'videocontent',
        0,
        $file->get_filepath(),
        $file->get_filename()
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

    // If draftitemid is 0, the teacher did not interact with the file picker:
    // Do not call file_save_draft_area_files here: when itemid is 0, Moodle would.
    // Clear the poster image file area even though the teacher did not edit it.
    // Moodle file picker always sends an itemid > 0 once it has been touched,.
    // Even if the user removed the file (the draft area exists but is empty).
    if ($draftitemid <= 0) {
        return;
    }

    file_save_draft_area_files($draftitemid, $context->id, 'mod_videotrack', 'posterimage', 0, [
        'subdirs'        => false,
        'maxfiles'       => 1,
        'accepted_types' => ['.jpg', '.jpeg', '.png', '.webp', '.gif'],
    ]);
}


/**
 * Validates the Font Awesome class list used for reaction icons.
 *
 * The Moodle form and the save pipeline both use the same rules so client-side
 * rendering never has to accept broader values than the server stores.
 *
 * @param string $value Candidate class list.
 * @return bool True when the class list is a safe Font Awesome subset.
 */
function videotrack_is_valid_reaction_icon_class(string $value): bool {
    $value = trim($value);
    if ($value === '' || core_text::strlen($value) > 160) {
        return false;
    }
    if (!preg_match('/^[a-z0-9 -]+$/i', $value)) {
        return false;
    }
    $parts = preg_split('/\s+/', $value, -1, PREG_SPLIT_NO_EMPTY);
    if (!$parts || count($parts) > 4) {
        return false;
    }
    $styleclasses = ['fa' => true, 'fas' => true, 'far' => true, 'fab' => true,
        'fa-solid' => true, 'fa-regular' => true, 'fa-brands' => true];
    $utilitypattern = '/^fa-(?:fw|lg|xs|sm|[1-9]x|2xs|xl|2xl|spin|pulse|rotate-(?:90|180|270)|flip-(?:horizontal|vertical|both))$/';
    $iconnames = 0;
    foreach ($parts as $part) {
        if (isset($styleclasses[$part]) || preg_match($utilitypattern, $part)) {
            continue;
        }
        if (preg_match('/^fa-[a-z0-9][a-z0-9-]{1,46}$/', $part)) {
            $iconnames++;
            continue;
        }
        return false;
    }
    return $iconnames === 1;
}

/**
 * Saves the configured reaction definitions for a VideoTrack activity.
 *
 * @param int $videotrackid VideoTrack instance id.
 * @param stdClass $data Submitted form data.
 */
function videotrack_save_reaction_definitions(int $videotrackid, stdClass $data): void {
    global $DB;

    $context = videotrack_get_module_context_from_data($data, $videotrackid);
    if (!$context) {
        debugging('Unable to resolve module context while saving VideoTrack reaction definitions.', DEBUG_DEVELOPER);
        return;
    }
    $existing = $DB->get_records('videotrack_react', ['videotrackid' => $videotrackid], '', 'id, sortorder');
    $labels = $data->reactionlabel ?? [];
    $descriptions = $data->reactiondescription ?? [];
    $icontypes = $data->reactionicontype ?? [];
    $iconvalues = $data->reactioniconvalue ?? [];
    $requireds = $data->reactionrequired ?? [];
    $reactionids = $data->reactionid ?? [];

    // Wrap all DB writes in a delegated transaction.
    // This prevents a partial update when one reaction definition fails mid-loop.
    // File-area operations are collected and executed after the transaction commits.
    $transaction = $DB->start_delegated_transaction();
    $keptids = [];
    $sort = 1;
    $now = time();
    // Collect file operations to execute after the DB transaction commits.
    // Previously file_get_draft_area_info() was called for every reaction regardless
    // of icontype, wasting I/O for emoji and Font Awesome reactions.
    $fileops = [];

    foreach ($labels as $idx => $label) {
        $label = trim((string)$label);
        if ($label === '') {
            continue;
        }

        $icontype = in_array(($icontypes[$idx] ?? 'emoji'), ['emoji', 'fa', 'file'], true)
            ? $icontypes[$idx]
            : 'emoji';
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
            'isdeleted' => 0, // Explicitly reset soft-delete when a reaction is reactivated.
            'timemodified' => $now,
        ];
        if ($icontype === 'file') {
            $record->iconvalue = '';
        } else if ($icontype === 'emoji') {
            $record->iconvalue = clean_param($record->iconvalue, PARAM_TEXT);
        } else if ($icontype === 'fa') {
            $record->iconvalue = clean_param($record->iconvalue, PARAM_NOTAGS);
            if (!videotrack_is_valid_reaction_icon_class($record->iconvalue)) {
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
        // File_get_draft_area_info() is called only for 'file' icontype.
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

        $sort++;
    }

    foreach ($existing as $oldreactionid => $oldreaction) {
        if (!isset($keptids[$oldreactionid])) {
            // Soft-delete: keep the definition and its file area intact.
            // Historical reports/events may still reference this reaction and should.
            // Keep rendering the original icon when available.
            $DB->set_field('videotrack_react', 'isdeleted', 1, ['id' => $oldreactionid]);
        }
    }

    // B3 fix: commit DB transaction before file operations (files are not transactional).
    $transaction->allow_commit();

    completion_config::reset_required_reaction_cache((int)$data->course);

    // Execute file-area operations after the DB commit.
    if ($fileops) {
        $fs = get_file_storage();
        foreach ($fileops as $op) {
            $fs->delete_area_files($op['context']->id, 'mod_videotrack', 'reactionicon', $op['reactionid']);
            if ($op['draftitemid'] > 0) {
                file_save_draft_area_files(
                    $op['draftitemid'],
                    $op['context']->id,
                    'mod_videotrack',
                    'reactionicon',
                    $op['reactionid'],
                    [
                        'subdirs' => 0,
                        'maxfiles' => 1,
                        'accepted_types' => ['.jpg', '.jpeg', '.png', '.gif', '.webp'],
                    ]
                );
                // Resize to 64x64px with a centred crop after saving.
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
        $return->info = get_string(
            'outline:percent',
            'mod_videotrack',
            format_float((float)$state->completionpercent, 1)
        );
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
        echo html_writer::tag(
            'p',
            get_string('outline:nodata', 'mod_videotrack'),
            ['class' => 'text-muted']
        );
        return;
    }
    $table            = new html_table();
    $table->caption = get_string('report:perstudent', 'mod_videotrack');
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
             : get_string(
                 'no',
                 'mod_videotrack'
             )],
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
        null,
        null,
        new pix_icon('i/report', '')
    );
    $node->add(
        get_string('teacherdashboard:navlink', 'mod_videotrack'),
        new moodle_url('/mod/videotrack/reports_teacher.php'),
        navigation_node::TYPE_SETTING,
        null,
        null,
        new pix_icon('i/dashboard', '')
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
    $allpossible = [
        'play',
        'rewind',
        'fastforward',
        'progress',
        'current',
        'duration',
        'mute',
        'volume',
        'speed',
        'pip',
        'fullscreen',
        'download',
    ];
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
    $behaviourfields = [
        'autoplay',
        'loopenabled',
        'startmuted',
        'allowdownload',
        'resumeplayback',
        'showtranscript',
        'showchapters',
        'studentnotesenabled',
        'bookmarksenabled',
        'integrityindicatorsenabled',
        'pauseonfocusloss',
        'preventpictureinpicture',
        'randomfocuspauses',
        'acknowledgementenabled',
        'completionacknowledgement',
    ];
    foreach ($behaviourfields as $field) {
        $data->{$field} = empty($data->{$field}) ? 0 : 1;
    }
    // Playback rates are stored as integers in hundredths (50=0.5x, 100=1x).
    $data->maxplaybackrate = (int)($data->maxplaybackrate ?? 0);
    $blockedseekrate = (int)($data->blockedseekplaybackrate ?? 50);
    $allowedblockedrates = [50, 75, 100];
    $data->blockedseekplaybackrate = in_array($blockedseekrate, $allowedblockedrates, true) ? $blockedseekrate : 50;
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
        $context->id,
        'mod_videotrack',
        'subtitles',
        0,
        $file->get_filepath(),
        $file->get_filename()
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

    $isupload = (($data->videosource ?? 'youtube') === 'upload');
    if (!$isupload || empty($data->captions)) {
        // The legacy subtitles area belongs only to captions for uploaded media.
        // Dedicated transcript and chapter files remain valid for every source.
        $context = videotrack_get_module_context_from_data($data, (int)($data->id ?? 0));
        if ($context) {
            get_file_storage()->delete_area_files($context->id, 'mod_videotrack', 'subtitles', 0);
        }
        unset($data->vttfile);
        return;
    }

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
    } else if ($data->grade == 0) {
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
    } else if ($videotrack->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax']  = (float)$videotrack->grade;
        $params['grademin']  = 0;
    } else {
        // Negative value encodes a scale id.
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -(int)$videotrack->grade;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    $result = grade_update(
        'mod/videotrack',
        $videotrack->course,
        'mod',
        'videotrack',
        $videotrack->id,
        0,
        $grades,
        $params
    );

    // The grade_update() function does not accept gradepass in its itemdetails allowlist.
    // Keep the canonical itemnumber-0 grade item aligned explicitly.
    if ($result === GRADE_UPDATE_OK && !empty($videotrack->grade)) {
        $gradeitem = grade_item::fetch([
            'courseid' => (int)$videotrack->course,
            'itemtype' => 'mod',
            'itemmodule' => 'videotrack',
            'iteminstance' => (int)$videotrack->id,
            'itemnumber' => 0,
        ]);
        if ($gradeitem) {
            $gradepass = max(0.0, (float)($videotrack->gradepass ?? 0));
            if (grade_floats_different((float)$gradeitem->gradepass, $gradepass)) {
                $gradeitem->gradepass = $gradepass;
                $gradeitem->update('mod/videotrack');
            }
        }
    }

    return $result;
}


/**
 * Updates all grades for this activity in the Moodle gradebook.
 *
 * Moodle expects activity modules that implement grade item updates to also
 * expose an update_grades callback. Videotrack does not keep a separate grade
 * table to recalculate from, so this callback keeps the grade item definition in
 * sync and leaves per-user grade updates to the tracker workflow.
 *
 * @param stdClass $videotrack Activity instance record.
 * @param int $userid Optional user id, unused because grades are pushed when calculated.
 * @param bool $nullifnone Whether missing grades should be nulled, unused by this module.
 * @return int Result of grade item update.
 */
function videotrack_update_grades(stdClass $videotrack, int $userid = 0, bool $nullifnone = true): int {
    return videotrack_grade_item_update($videotrack);
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

    if (!isset($grades->items[0]->grades[$userid])) {
        return null;
    }
    $grade = $grades->items[0]->grades[$userid]->grade;
    return $grade === null ? null : (float)$grade;
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
    if (!$file || $file->is_directory()) {
        return null;
    }

    $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
    if (
        !in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)
        || strpos((string)$file->get_mimetype(), 'image/') !== 0
    ) {
        return null;
    }

    return moodle_url::make_pluginfile_url(
        $context->id,
        'mod_videotrack',
        'posterimage',
        0,
        '/',
        $file->get_filename()
    );
}

/**
 * Deletes all plugin-owned VideoTrack data for one user in an activity.
 *
 * The teacher reset removes viewing state and segments, reactions, notes,
 * bookmarks, integrity indicators and acknowledgement records.
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
    // Delete reactions, notes and bookmarks so the reset is complete.
    // Otherwise they survive the reset, remain visible and may affect completion.
    $DB->delete_records('videotrack_reactev', [
        'videotrackid' => $videotrack->id,
        'userid'       => $userid,
    ]);
    $DB->delete_records('videotrack_integrity', [
        'videotrackid' => $videotrack->id,
        'userid'       => $userid,
    ]);
    $DB->delete_records('videotrack_acknowledge', [
        'videotrackid' => $videotrack->id,
        'userid' => $userid,
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

/**
 * Deletes a VideoTrack activity instance and related records.
 *
 * @param int $id Activity instance id.
 * @return bool True when the instance is deleted, false when it does not exist.
 */
function videotrack_delete_instance($id) {
    global $DB;

    if (!$videotrack = $DB->get_record('videotrack', ['id' => $id])) {
        return false;
    }

    $transaction = $DB->start_delegated_transaction();
    try {
        // Remove the gradebook item while the activity record still exists so
        // gradebook callbacks can resolve module metadata. Keeping this inside
        // the delegated transaction prevents deletion if cleanup throws an exception.
        videotrack_grade_item_delete($videotrack);

        $DB->delete_records(
            'videotrack_seg',
            ['videotrackid' => $videotrack->id]
        );
        $DB->delete_records(
            'videotrack_state',
            ['videotrackid' => $videotrack->id]
        );
        $DB->delete_records('videotrack_reactev', ['videotrackid' => $videotrack->id]);
        $DB->delete_records('videotrack_integrity', ['videotrackid' => $videotrack->id]);
        $DB->delete_records('videotrack_acknowledge', ['videotrackid' => $videotrack->id]);
        $DB->delete_records(
            'videotrack_react',
            ['videotrackid' => $videotrack->id]
        );
        $DB->delete_records(
            'videotrack',
            ['id'           => $videotrack->id]
        );
        $transaction->allow_commit();
    } catch (Throwable $e) {
        $transaction->rollback($e);
        throw $e;
    }

    $cm = get_coursemodule_from_instance('videotrack', $id, $videotrack->course, false, IGNORE_MISSING);
    if ($cm) {
        $context = context_module::instance($cm->id);
        // Delete all plugin file areas after the DB transaction because Moodle
        // delegated transactions do not cover file storage.
        get_file_storage()->delete_area_files($context->id, 'mod_videotrack');
    }

    return true;
}

/**
 * Returns cached course-module information for the activity.
 *
 * @param stdClass $coursemodule Course module record.
 * @return cached_cm_info|null Cached information or null when the instance is missing.
 */
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
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $requiredreactionset = completion_config::required_reaction_activity_set((int)$videotrack->course);
        $hasrequiredreactions = isset($requiredreactionset[(int)$videotrack->id]);
        $info->customdata['customcompletionrules'][\mod_videotrack\completion\custom_completion::RULE] =
            completion_config::has_custom_rules($videotrack, $hasrequiredreactions);
    }
    return $info;
}

/**
 * Registers an activity view and updates view-based completion.
 *
 * @param stdClass $videotrack Activity instance.
 * @param stdClass $course Course record.
 * @param cm_info $cm Course module information.
 * @param context_module $context Module context.
 */
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

/**
 * Returns the automatic completion state using the canonical VideoTrack rules.
 *
 * This legacy callback remains useful to Moodle code paths that still ask the
 * module directly for completion state. The modern custom_completion class uses
 * the same tracker service, so both APIs have identical AND/OR semantics.
 *
 * @param stdClass $course Course record.
 * @param cm_info $cm Course module information.
 * @param int $userid User id.
 * @param bool $type Core aggregation fallback when no custom rule is enabled.
 * @return bool Completion state, or the supplied fallback when no custom rule is active.
 */
function videotrack_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
    $requiredreactionids = !empty($videotrack->reactionsenabled)
        ? completion_config::required_reaction_ids((int)$videotrack->id)
        : [];
    if (!completion_config::has_custom_rules($videotrack, !empty($requiredreactionids))) {
        return $type;
    }
    $state = $DB->get_record('videotrack_state', [
        'videotrackid' => $videotrack->id,
        'userid' => $userid,
    ]);
    if (!$state) {
        $state = (object)[
            'userid' => (int)$userid,
            'completionpercent' => 0,
        ];
    }
    $reactionsummary = !empty($videotrack->reactionsenabled)
        ? tracker::reaction_counts((int)$videotrack->id, (int)$userid)
        : ['uniquecount' => 0, 'uniqueids' => []];

    return tracker::completion_satisfied(
        $videotrack,
        $state,
        $reactionsummary,
        $requiredreactionids
    );
}

/**
 * Returns active custom completion rule descriptions for the activity.
 *
 * @param cm_info $cm Course module information.
 * @return string[] Completion rule descriptions.
 */
function videotrack_get_completion_active_rule_descriptions($cm) {
    global $DB;

    if (
        empty($cm->customdata['customcompletionrules'][\mod_videotrack\completion\custom_completion::RULE])
        || $cm->completion != COMPLETION_TRACKING_AUTOMATIC
    ) {
        return [];
    }
    $videotrack = $DB->get_record('videotrack', ['id' => $cm->instance], '*', MUST_EXIST);
    if (!completion_config::has_custom_rules($videotrack)) {
        return [];
    }
    $context = context_module::instance($cm->id);
    $conditions = completion_config::active_condition_descriptions($videotrack, $context);
    if (!$conditions) {
        return [];
    }
    $logic = get_string('completiondetail:logicand', 'mod_videotrack');
    return [get_string('completiondetail:videotrackconditions', 'mod_videotrack', (object)[
        'logic' => $logic,
        'conditions' => implode('; ', $conditions),
    ])];
}

/**
 * Recalculates completion for a specific user.
 *
 * @param stdClass $videotrack Activity instance.
 * @param cm_info $cm Course module information.
 * @param int $userid User id.
 */
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
            $DB->delete_records(
                'videotrack_seg',
                ['videotrackid' => $instance->id]
            );
            $DB->delete_records(
                'videotrack_state',
                ['videotrackid' => $instance->id]
            );
            $DB->delete_records('videotrack_reactev', ['videotrackid' => $instance->id]);
            $DB->delete_records('videotrack_integrity', ['videotrackid' => $instance->id]);
            $DB->delete_records('videotrack_acknowledge', ['videotrackid' => $instance->id]);
            // Azzera anche i voti nel gradebook per questa istanza.
            if (!empty($instance->grade)) {
                grade_update(
                    'mod/videotrack',
                    $data->courseid,
                    'mod',
                    'videotrack',
                    $instance->id,
                    0,
                    null,
                    ['reset' => true]
                );
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
    $mform->addElement(
        'header',
        'videotrackheader',
        get_string('modulename', 'mod_videotrack')
    );
    $mform->addElement(
        'checkbox',
        'reset_videotrack_userdata',
        get_string('modulename', 'mod_videotrack'),
        get_string('reset:userdata', 'mod_videotrack')
    );
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
 * Resize the reaction icon to 64x64px using a centred crop.
 *
 * Reads the file from Moodle file storage and resizes it with GD,
 * and overwrites the original file with the resized PNG version.
 * If GD is unavailable or the format is unsupported, the file is
 * left unchanged (no blocking error; CSS handles the dimensions).
 *
 * @param context_module    $context    Module context.
 * @param int               $reactionid Reaction ID (filearea itemid).
 * @param file_storage      $fs         Moodle file storage instance.
 */
function videotrack_resize_reaction_icon(context_module $context, int $reactionid, file_storage $fs): void {
    if (!function_exists('imagecreatefromstring')) {
        // GD is not available: resizing is skipped.
        // The admin warning is already visible on the settings page (settings.php).
        // And in the environment.xml check. Do not block saving.
        debugging(
            'mod_videotrack: GD PHP extension is not available. ' .
                'Reaction icon for reactionid=' . $reactionid . ' was NOT resized to 64x64px. ' .
                'Install php-gd to enable automatic icon resizing.',
            DEBUG_NORMAL
        );
        return;
    }

    $files = $fs->get_area_files($context->id, 'mod_videotrack', 'reactionicon', $reactionid, '', false);
    if (empty($files)) {
        return;
    }
    $file = reset($files);

    $srcdata  = $file->get_content();
    // PHP 8.0+ lancia ValueError (non Warning) per dati non immagine.
    // Try/catch is required; @ does not catch Error/ValueError.
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

    // Save as PNG in a temporary buffer.
    ob_start();
    imagepng($dst, null, 6); // Compression level 6 balances quality and size.
    $pngdata = ob_get_clean();
    imagedestroy($dst);

    if (empty($pngdata)) {
        return;
    }

    // Overwrite the file in the Moodle file area with the resized version.
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
    global $DB;

    if ($context->contextlevel !== CONTEXT_MODULE) {
        return false;
    }
    $allowedfileareas = [
        'intro',
        'reactionicon',
        'videocontent',
        'subtitles',
        'transcripts',
        'chapters',
        'posterimage',
    ];
    if (!in_array($filearea, $allowedfileareas, true)) {
        return false;
    }
    $allowedextensions = [
        'reactionicon' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'posterimage' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'subtitles' => ['vtt'],
        'transcripts' => ['vtt'],
        'chapters' => ['vtt'],
        'videocontent' => ['mp4', 'webm', 'mp3', 'm4v', 'mov', 'aac', 'm4a'],
    ];
    require_login($course, true, $cm);
    if (!has_capability('mod/videotrack:view', $context)) {
        return false;
    }
    if (in_array($filearea, ['videocontent', 'subtitles'], true)) {
        $instance = $DB->get_record('videotrack', ['id' => $cm->instance], 'id, videosource, captions', MUST_EXIST);
        if ((string)$instance->videosource !== 'upload') {
            return false;
        }
        if ($filearea === 'subtitles' && empty($instance->captions)) {
            return false;
        }
    }
    if (in_array($filearea, ['transcripts', 'chapters'], true)) {
        $fields = 'id, showtranscript, showchapters';
        $instance = $DB->get_record('videotrack', ['id' => $cm->instance], $fields, MUST_EXIST);
        if ($filearea === 'transcripts' && empty($instance->showtranscript)) {
            return false;
        }
        if ($filearea === 'chapters' && empty($instance->showchapters)) {
            return false;
        }
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
    if ($filearea === 'intro') {
        // Intro files are managed by Moodle core editor/filepicker controls.
        // They intentionally follow the standard module intro serving path.
        // The stricter checks below apply only to VideoTrack-specific uploads.
        return send_stored_file($file, 0, 0, $forcedownload, $options);
    }
    $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedextensions[$filearea], true)) {
        return false;
    }
    if (
        in_array($filearea, ['reactionicon', 'posterimage'], true)
        && strpos((string)$file->get_mimetype(), 'image/') !== 0
    ) {
        return false;
    }
    if (in_array($filearea, ['subtitles', 'transcripts', 'chapters'], true)) {
        if (!in_array($file->get_mimetype(), ['text/vtt', 'text/plain', 'application/octet-stream'], true)) {
            return false;
        }
        if (
            $file->get_filesize() > \mod_videotrack\local\timed_text::MAX_FILE_SIZE
            || !\mod_videotrack\local\timed_text::is_valid_vtt_content($file->get_content())
        ) {
            return false;
        }
    }
    if ($filearea === 'videocontent') {
        $allowdownload = (int)$DB->get_field('videotrack', 'allowdownload', ['id' => $cm->instance], MUST_EXIST);
        if (empty($allowdownload) && $forcedownload) {
            return false;
        }
        $forcedownload = !empty($allowdownload) ? $forcedownload : false;
    }
    // Uploaded videos: allow 1 hour browser caching. Poster: 5 min. Icons: no cache.
    $lifetime = ($filearea === 'videocontent') ? 3600 : (($filearea === 'posterimage') ? 300 : 0);
    send_stored_file($file, $lifetime, 0, $forcedownload, $options);
}

/**
 * Recalculate the aggregate state (completionpercent, iscompleted) for all users
 * in a single VideoTrack instance from raw segments.
 * Useful after changing the video duration or completion criteria.
 *
 * @param  int      $videotrackid   VideoTrack instance id.
 * @param  cm_info  $cm             Course module info.
 * @param  int      $userid          Optional user id; zero recalculates every tracked user.
 * @param  bool     $synchronisemoodle Whether to ask Moodle to re-evaluate native completion too.
 * @return int                      Number of updated state records.
 */
function videotrack_recalculate_all_states(
    int $videotrackid,
    cm_info $cm,
    int $userid = 0,
    bool $synchronisemoodle = true
): int {
    global $DB;

    $videotrack = $DB->get_record('videotrack', ['id' => $videotrackid], '*', MUST_EXIST);
    $completion = $synchronisemoodle ? new completion_info(get_course($videotrack->course)) : null;
    $hascustomcompletion = $synchronisemoodle && completion_config::has_custom_rules($videotrack);
    $userids = [];
    if ($userid > 0) {
        foreach (['videotrack_state', 'videotrack_seg', 'videotrack_reactev', 'videotrack_acknowledge'] as $table) {
            if ($DB->record_exists($table, ['videotrackid' => $videotrackid, 'userid' => $userid])) {
                $userids[$userid] = true;
                break;
            }
        }
    } else {
        foreach (['videotrack_state', 'videotrack_seg', 'videotrack_reactev', 'videotrack_acknowledge'] as $table) {
            foreach (
                $DB->get_fieldset_select(
                    $table,
                    'DISTINCT userid',
                    'videotrackid = :vtid',
                    ['vtid' => $videotrackid]
                ) as $trackeduserid
            ) {
                if ((int)$trackeduserid > 0) {
                    $userids[(int)$trackeduserid] = true;
                }
            }
        }
    }

    $updated = 0;
    foreach (array_keys($userids) as $userid) {
        $state = tracker::rebuild_state_from_segments($videotrack, $cm, $userid);
        if ($state === null) {
            continue;
        }
        if ($synchronisemoodle) {
            if ($hascustomcompletion) {
                tracker::update_moodle_completion_if_changed(
                    $completion,
                    $cm,
                    !empty($state->iscompleted),
                    $userid
                );
            } else {
                // Custom rules may have just been removed. Let Moodle recompute any
                // remaining standard completion conditions such as view or grade.
                $completion->update_state($cm, COMPLETION_UNKNOWN, $userid);
            }
        }
        $updated++;
    }
    return $updated;
}
