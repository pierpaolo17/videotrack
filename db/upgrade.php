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
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for mod_videotrack.
 *
 * Each if-block MUST call upgrade_mod_savepoint() before closing.
 * Blocks without schema changes still need a savepoint for the upgrade
 * engine to track which version the site is currently at.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_videotrack_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2026043008) {
        $table = new xmldb_table('videotrack');

        // grade: 0 = no grade, >0 = max points, <0 = scale id.
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'reactionnotice');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // gradepass: minimum passing grade.
        $field = new xmldb_field('gradepass', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0.00000', 'grade');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // showgradeto: whether to display the grade to the student.
        $field = new xmldb_field('showgradeto', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'gradepass');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026043008, 'videotrack');
    }

    if ($oldversion < 2026050100) {
        $table = new xmldb_table('videotrack');

        // videosource: 'youtube' | 'vimeo' | 'upload'.
        $field = new xmldb_field('videosource', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'youtube', 'videoid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // videourl: Vimeo URL or uploaded file reference.
        $field = new xmldb_field('videourl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'videosource');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // playbackspeeds: comma-separated list.
        $field = new xmldb_field('playbackspeeds', XMLDB_TYPE_CHAR, '100', null, false, null, '', 'showgradeto');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // youtubeurl can now be null for non-YouTube sources.
        $field = new xmldb_field('youtubeurl', XMLDB_TYPE_TEXT, null, null, false, null, null, 'name');
        if ($dbman->field_exists($table, new xmldb_field('youtubeurl'))) {
            $dbman->change_field_notnull($table, $field);
        }

        upgrade_mod_savepoint(true, 2026050100, 'videotrack');
    }

    if ($oldversion < 2026050102) {
        $table = new xmldb_table('videotrack');

        $newfields = [
            new xmldb_field('autoplay',      XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0', 'playbackspeeds'),
            new xmldb_field('loop',          XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0', 'autoplay'),
            new xmldb_field('startmuted',    XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0', 'loop'),
            new xmldb_field('allowdownload', XMLDB_TYPE_INTEGER, '1',   null, XMLDB_NOTNULL, null, '0', 'startmuted'),
            new xmldb_field('html5controls', XMLDB_TYPE_CHAR,    '255', null, false,          null, '', 'allowdownload'),
        ];
        foreach ($newfields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2026050102, 'videotrack');
    }

    if ($oldversion < 2026050103) {
        $table = new xmldb_table('videotrack');

        $newfields = [
            new xmldb_field('playerwidth',     XMLDB_TYPE_INTEGER, '5',  null, XMLDB_NOTNULL, null, '0', 'html5controls'),
            new xmldb_field('rewindstep',      XMLDB_TYPE_INTEGER, '3',  null, XMLDB_NOTNULL, null, '0', 'playerwidth'),
            new xmldb_field('fastforwardstep', XMLDB_TYPE_INTEGER, '3',  null, XMLDB_NOTNULL, null, '0', 'rewindstep'),
            new xmldb_field('captions',        XMLDB_TYPE_INTEGER, '1',  null, XMLDB_NOTNULL, null, '0', 'fastforwardstep'),
            new xmldb_field('captionslang',    XMLDB_TYPE_CHAR,    '10', null, false,          null, '', 'captions'),
        ];
        foreach ($newfields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        upgrade_mod_savepoint(true, 2026050103, 'videotrack');
    }

    // Some intermediate releases only changed PHP/JS/lang files and did not require
    // schema changes. Keep explicit savepoints so sites upgrading through those
    // versions have a clear and auditable upgrade path.

    if ($oldversion < 2026050200) {
        upgrade_mod_savepoint(true, 2026050200, 'videotrack');
    }
    if ($oldversion < 2026050201) {
        upgrade_mod_savepoint(true, 2026050201, 'videotrack');
    }
    if ($oldversion < 2026050202) {
        upgrade_mod_savepoint(true, 2026050202, 'videotrack');
    }
    if ($oldversion < 2026050203) {
        upgrade_mod_savepoint(true, 2026050203, 'videotrack');
    }
    if ($oldversion < 2026050204) {
        upgrade_mod_savepoint(true, 2026050204, 'videotrack');
    }
    if ($oldversion < 2026050205) {
        upgrade_mod_savepoint(true, 2026050205, 'videotrack');
    }
    if ($oldversion < 2026050206) {
        upgrade_mod_savepoint(true, 2026050206, 'videotrack');
    }
    if ($oldversion < 2026050207) {
        upgrade_mod_savepoint(true, 2026050207, 'videotrack');
    }

    if ($oldversion < 2026050208) {
        $table = new xmldb_table('videotrack');

        $field = new xmldb_field('resumeplayback', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('maxplaybackrate', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('showtranscript', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2026050208, 'videotrack');
    }

    if ($oldversion < 2026050209) {
        $table = new xmldb_table('videotrack');

        $field = new xmldb_field('studentnotesenabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('showchapters', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $reacttable = new xmldb_table('videotrack_reactev');

        $field = new xmldb_field('notetext', XMLDB_TYPE_TEXT, null, null, null, null, null, 'reactiondesc');
        if (!$dbman->field_exists($reacttable, $field)) {
            $dbman->add_field($reacttable, $field);
        }
        $field = new xmldb_field('notetype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, '', 'notetext');
        if (!$dbman->field_exists($reacttable, $field)) {
            $dbman->add_field($reacttable, $field);
        }

        upgrade_mod_savepoint(true, 2026050209, 'videotrack');
    }

    if ($oldversion < 2026050210) {
        upgrade_mod_savepoint(true, 2026050210, 'videotrack');
    }
    if ($oldversion < 2026050211) {
        upgrade_mod_savepoint(true, 2026050211, 'videotrack');
    }
    if ($oldversion < 2026050212) {
        upgrade_mod_savepoint(true, 2026050212, 'videotrack');
    }
    if ($oldversion < 2026050213) {
        upgrade_mod_savepoint(true, 2026050213, 'videotrack');
    }
    if ($oldversion < 2026050214) {
        upgrade_mod_savepoint(true, 2026050214, 'videotrack');
    }
    if ($oldversion < 2026050215) {
        upgrade_mod_savepoint(true, 2026050215, 'videotrack');
    }
    if ($oldversion < 2026050216) {
        upgrade_mod_savepoint(true, 2026050216, 'videotrack');
    }
    if ($oldversion < 2026050217) {
        upgrade_mod_savepoint(true, 2026050217, 'videotrack');
    }
    if ($oldversion < 2026050218) {
        upgrade_mod_savepoint(true, 2026050218, 'videotrack');
    }
    if ($oldversion < 2026050219) {
        upgrade_mod_savepoint(true, 2026050219, 'videotrack');
    }
    if ($oldversion < 2026050231) {
        // Version 0.8.1: language apostrophe fixes, monotonic durationseconds,
        // DB record whitelist, deduplicated maxplaybackrate settings, atomic transaction
        // save_segment, capabilities managereactions/grade, simplify_intervals no-overcount,
        // reaction_counts SQL, appendIconSafe whitelist, GDPR intervaljson, log heartbeat,
        // first-reaction placeholder, note CSV export with useridfilter.
        $table = new xmldb_table('videotrack');
        // Adds fields that may be missing when upgrading from very old versions
        // (these existed in install.xml but not in earlier upgrade blocks).
        $maybefields = [
            new xmldb_field('showcontrols',            XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('reactionsenabled',        XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field('reactionnotice',          XMLDB_TYPE_TEXT,    null, null, false, null, null),
            new xmldb_field('reactionnoticeformat',    XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('showreactionnotice',      XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field(
                'showstudentreport',
                XMLDB_TYPE_INTEGER,
                '1',
                null,
                XMLDB_NOTNULL,
                null,
                '1'
            ), // Aligned with install.xml
            new xmldb_field('clusterwindow', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '30'),
            new xmldb_field('disablekeyboard',         XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field('showfullscreen',          XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowplaybackratechange', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowseekforward',        XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowseekbackward',       XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('reactionsrequired',       XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field(
                'minreactions',
                XMLDB_TYPE_INTEGER,
                '10',
                null,
                XMLDB_NOTNULL,
                null,
                '0'
            ), // Aligned with install.xml
            new xmldb_field('requireallreactiontypes', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field('completionlogic',         XMLDB_TYPE_CHAR,   '10', null, XMLDB_NOTNULL, null, 'and'),
        ];
        foreach ($maybefields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }
        upgrade_mod_savepoint(true, 2026050231, 'videotrack');
    }

    if ($oldversion < 2026050236) {
        // Version 0.8.6: isdeleted on videotrack_react, hundredths-based settings,
        // removed posterimage backup, missing-field upgrade, external icon URLs,
        // email report with capability checks, heatmap aria-describedby, course SVG,
        // upload validation, durationseconds maximum 24h.

        // Adds isdeleted to videotrack_react for reaction soft-delete.
        $react = new xmldb_table('videotrack_react');
        $field = new xmldb_field('isdeleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sortorder');
        if (!$dbman->field_exists($react, $field)) {
            $dbman->add_field($react, $field);
        }
        upgrade_mod_savepoint(true, 2026050236, 'videotrack');
    }

    if ($oldversion < 2026050243) {
        // Version 0.9.5: fixes duplicated SQL parameters in has_recent_playback,
        // optimised reaction_counts in save_reaction, JS catch displays server message.
        upgrade_mod_savepoint(true, 2026050243, 'videotrack');
    }

    if ($oldversion < 2026050244) {
        // Version 0.9.6: environment.xml for GD, admin settings warning, GD debugging.
        upgrade_mod_savepoint(true, 2026050244, 'videotrack');
    }

    if ($oldversion < 2026050245) {
        // Version 0.9.7: normalises environment.xml messages (whitespace).
        upgrade_mod_savepoint(true, 2026050245, 'videotrack');
    }

    if ($oldversion < 2026050246) {
        // Version 0.9.8: install.xml COMMENT escaping fix, maxplaybackrate /100,
        // videotrack_get_reactions isdeleted, active reactions in mod_form, lib.php
        // isdeleted=0 on update, backup of soft-deleted reactions, aligned defaults,
        // minimised email query, contextual delete aria-label.
        upgrade_mod_savepoint(true, 2026050246, 'videotrack');
    }


    if ($oldversion < 2026050253) {
        // Version 1.0.5: preserves existing reaction icon files when no new draft file
        // is submitted, adds reaction burst throttling and reset audit event, and improves
        // cumulative report filtering/summary. No database schema changes.
        upgrade_mod_savepoint(true, 2026050253, 'videotrack');
    }


    if ($oldversion < 2026050507) {
        // Version 1.0.7: accessibility parity for reaction buttons (aria-label with
        // timestamp on replay and remove buttons), clearer cumulative report cluster-limit
        // warnings, localized privacy export state section heading, reaction_counts()
        // optimised to two separate queries (avoids GROUP_CONCAT truncation on MySQL),
        // and version number aligned with the public release sequence.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050507, 'videotrack');
    }

    if ($oldversion < 2026050510) {
        // Version 1.0.10: adds composite indexes used by playback validation,
        // reaction burst throttling, and note rate limiting.
        $segtable = new xmldb_table('videotrack_seg');
        $index = new xmldb_index('vt_user_sess_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'sessionid', 'timecreated']);
        if (!$dbman->index_exists($segtable, $index)) {
            $dbman->add_index($segtable, $index);
        }

        $reactevtable = new xmldb_table('videotrack_reactev');
        $index = new xmldb_index('vt_user_sess_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'sessionid', 'timecreated']);
        if (!$dbman->index_exists($reactevtable, $index)) {
            $dbman->add_index($reactevtable, $index);
        }

        $index = new xmldb_index('vt_user_type_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'notetype', 'timecreated']);
        if (!$dbman->index_exists($reactevtable, $index)) {
            $dbman->add_index($reactevtable, $index);
        }

        upgrade_mod_savepoint(true, 2026050510, 'videotrack');
    }


    if ($oldversion < 2026050511) {
        // Version 1.0.11: adds composite indexes used by duplicate reaction throttling
        // and note rate limiting with soft-delete filtering.
        $reactevtable = new xmldb_table('videotrack_reactev');

        $index = new xmldb_index('vt_user_reaction_del_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'reactionid', 'isdeleted', 'timecreated']);
        if (!$dbman->index_exists($reactevtable, $index)) {
            $dbman->add_index($reactevtable, $index);
        }

        $index = new xmldb_index('vt_user_note_del_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'notetype', 'isdeleted', 'timecreated']);
        if (!$dbman->index_exists($reactevtable, $index)) {
            $dbman->add_index($reactevtable, $index);
        }

        upgrade_mod_savepoint(true, 2026050511, 'videotrack');
    }


    if ($oldversion < 2026050513) {
        // Version 1.0.13: fixes a fresh-install XMLDB index definition and adds
        // pluginfile hardening, translation, and documentation polish. Existing
        // installations do not require a schema change because the invalid state
        // index could not be created without the missing field.
        upgrade_mod_savepoint(true, 2026050513, 'videotrack');
    }

    if ($oldversion < 2026050515) {
        // v1.0.15: fix version format (10 digits), install.xml VERSION aligned,
        // videotrack_delete_user_progress() now also deletes videotrack_reactev.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050515, 'videotrack');
    }

    if ($oldversion < 2026050516) {
        // v1.0.16: burst-limit no longer filtered by sessionid (B3),
        // reaction_counts() skipped for completionpercent rule (B4).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050516, 'videotrack');
    }

    if ($oldversion < 2026050517) {
        // v1.0.17: delete_reaction redundant reaction_counts call removed (B5),
        // player.js isProgrammaticSeek flag added for YouTube seek detection (B6).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050517, 'videotrack');
    }

    if ($oldversion < 2026050518) {
        // v1.0.18: reaction_counts per-request static class cache (O1),
        // recalculate_all_states uses get_recordset instead of get_records (O2).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050518, 'videotrack');
    }

    if ($oldversion < 2026050519) {
        // v1.0.19: notes_csv export validates useridfilter with is_enrolled() (S1),
        // intervaljson exported as human-readable MM:SS pairs in GDPR export (G1).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050519, 'videotrack');
    }

    if ($oldversion < 2026050520) {
        // v1.0.20: upgrade.php savepoint 2026050507 comment expanded (M2),
        // save_note.php now fires dedicated note_saved event instead of reaction_saved (M3).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050520, 'videotrack');
    }

    if ($oldversion < 2026050521) {
        // v1.0.21: showStatusMessage keeps error messages visible 8s instead of 4s (U1),
        // keydown handler added for Enter/Space on aria-disabled reaction buttons (A1).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050521, 'videotrack');
    }

    if ($oldversion < 2026050522) {
        // v1.0.22: version.php release string corrected to 1.0.21 (C1),
        // upgrade.php savepoints added for v1.0.15-1.0.21 (C2),
        // note error handler in all three players now uses showStatusMessage()
        // for consistent 8s visibility and correct aria role management (B1/B2/B3/A1).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050522, 'videotrack');
    }

    if ($oldversion < 2026050523) {
        // v1.0.23: PLAYBACK_GRACE_SECONDS constant replaces magic 12.0 in
        // has_recent_playback() (S1), resumedlabel alias removed from playerconfig
        // and showResumeNotice uses resumelabel directly (O1/U1),
        // maturity raised from MATURITY_ALPHA to MATURITY_BETA (M1/M2).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050523, 'videotrack');
    }

    if ($oldversion < 2026050524) {
        // v1.0.24: html5_player.js updateIntervalBar aligned to player.js/vimeo_player.js:
        // added duration parameter (C1), covered calculation, and aria-label update
        // after each redraw (B2/A1 — WCAG 1.1.1 Non-text Content).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050524, 'videotrack');
    }

    if ($oldversion < 2026050525) {
        // v1.0.25: save_segment servergrace dead code removed (B1),
        // videotrack_save_reaction_definitions wrapped in delegated transaction (B3),
        // file_get_draft_area_info called only for file-type reactions (O1).
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026050525, 'videotrack');
    }


    if ($oldversion < 2026052600) {
        // v1.0.26: mobile icon added, AMD build regenerated, accessibility
        // state for note/reaction buttons aligned with real disabled controls.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052600, 'videotrack');
    }

    if ($oldversion < 2026052700) {
        // v1.0.27: GDPR retention task and server-side watched-position
        // validation added. Existing installations need only register the task
        // and settings; no database schema changes.
        upgrade_mod_savepoint(true, 2026052700, 'videotrack');
    }

    if ($oldversion < 2026052800) {
        // v1.0.28: anonymisation collision handling and UX-friendly
        // academic-integrity validation fallback added. No database schema changes.
        upgrade_mod_savepoint(true, 2026052800, 'videotrack');
    }

    if ($oldversion < 2026052900) {
        // v1.0.29: privacy erasure consistently anonymises data, XMLDB
        // version aligned, task logging and retention batching refined.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052900, 'videotrack');
    }



    if ($oldversion < 2026052901) {
        // Release 1.2.29: JavaScript fetch resilience and AMD build alignment. No schema changes.
        upgrade_mod_savepoint(true, 2026052901, 'videotrack');
    }

    if ($oldversion < 2026052902) {
        // Release 1.2.30: compatibility cleanup before the 1.3 refactor branch. No schema changes.
        upgrade_mod_savepoint(true, 2026052902, 'videotrack');
    }


    if ($oldversion < 2026052903) {
        // Release 1.3.94: code-only maintenance fixes, bug-report regression gates and AMD build alignment.
        upgrade_mod_savepoint(true, 2026052903, 'videotrack');
    }

    if ($oldversion < 2026052904) {
        // Code-only release: privacy metadata, accessibility and AMD hardening updates.
        upgrade_mod_savepoint(true, 2026052904, 'videotrack');
    }

    if ($oldversion < 2026052905) {
        // Release 1.3.96: localisation, settings privacy notice and code-only compliance fixes.
        upgrade_mod_savepoint(true, 2026052905, 'videotrack');
    }

    if ($oldversion < 2026052906) {
        // Release 1.3.97: code-only hardening, localisation and backup/restore token fixes.
        upgrade_mod_savepoint(true, 2026052906, 'videotrack');
    }

    if ($oldversion < 2026052907) {
        // Release 1.3.98: retention default, export audit, accessibility and AMD hardening.
        $retention = get_config('mod_videotrack', 'retentionperioddays');
        if ($retention === false || $retention === null || $retention === '') {
            set_config('retentionperioddays', 730, 'mod_videotrack');
        }
        upgrade_mod_savepoint(true, 2026052907, 'videotrack');
    }

    if ($oldversion < 2026052908) {
        // Release 1.3.100: privacy hardening and accessibility/contrast cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052908, 'videotrack');
    }

    if ($oldversion < 2026052909) {
        // Release 1.3.101: AMD robustness, notes accessibility and localisation fixes. No schema changes.
        upgrade_mod_savepoint(true, 2026052909, 'videotrack');
    }

    if ($oldversion < 2026052910) {
        // Release 1.4.0: AMD lifecycle, accessibility and localisation fixes. No schema changes.
        upgrade_mod_savepoint(true, 2026052910, 'videotrack');
    }

    if ($oldversion < 2026052911) {
        // Release 1.4.1: Moodle plugins directory submission cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052911, 'videotrack');
    }

    if ($oldversion < 2026052912) {
        // Release 1.4.2: URL validation hardening, note handling cleanup and AMD build alignment. No schema changes.
        upgrade_mod_savepoint(true, 2026052912, 'videotrack');
    }

    if ($oldversion < 2026052913) {
        // Release 1.4.3: adapter hardening, provider URL normalization and accessibility refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026052913, 'videotrack');
    }

    if ($oldversion < 2026052914) {
        // Release 1.4.4: reaction throttling, accessibility, privacy and configuration hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052914, 'videotrack');
    }

    if ($oldversion < 2026052915) {
        // Release 1.4.5: accessibility, language-comment cleanup and build alignment. No schema changes.
        upgrade_mod_savepoint(true, 2026052915, 'videotrack');
    }

    if ($oldversion < 2026052916) {
        // Release 1.4.6: index compliance, language strings, cleanup task hardening and build alignment. No schema changes.
        upgrade_mod_savepoint(true, 2026052916, 'videotrack');
    }

    if ($oldversion < 2026052917) {
        // Release 1.4.7: version alignment and maintenance release. No schema changes.
        upgrade_mod_savepoint(true, 2026052917, 'videotrack');
    }


    if ($oldversion < 2026052918) {
        // Release 1.4.8: accessibility and Moodle header maintenance. No schema changes.
        upgrade_mod_savepoint(true, 2026052918, 'videotrack');
    }


    if ($oldversion < 2026052919) {
        // Release 1.4.9: accessibility announcement cleanup and maintenance fixes. No schema changes.
        upgrade_mod_savepoint(true, 2026052919, 'videotrack');
    }

    if ($oldversion < 2026052920) {
        // Release 1.4.10: provider URL hardening, lifecycle cleanup and accessibility refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026052920, 'videotrack');
    }

    if ($oldversion < 2026052921) {
        // Release 1.4.11: accessibility, reporting and AMD build hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052921, 'videotrack');
    }

    if ($oldversion < 2026052922) {
        // Release 1.4.12: unload-beacon deduplication and AMD build normalisation. No schema changes.
        upgrade_mod_savepoint(true, 2026052922, 'videotrack');
    }


    if ($oldversion < 2026052923) {
        // Release 1.4.13: AMD rebuild, accessibility and security hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052923, 'videotrack');
    }


    if ($oldversion < 2026052924) {
        // Release 1.4.14: AMD build normalisation, accessibility and runtime hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052924, 'videotrack');
    }


    if ($oldversion < 2026052925) {
        // Release 1.4.15: AMD build normalisation and security/accessibility hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052925, 'videotrack');
    }


    if ($oldversion < 2026052926) {
        // Release 1.4.16: reaction live-region runtime fix and AMD build normalisation. No schema changes.
        upgrade_mod_savepoint(true, 2026052926, 'videotrack');
    }


    if ($oldversion < 2026052927) {
        // Release 1.4.17: AMD rebuild, retry hardening and certification cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052927, 'videotrack');
    }


    if ($oldversion < 2026052928) {
        // Release 1.4.18: AMD validation, accessibility and hardening cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052928, 'videotrack');
    }


    if ($oldversion < 2026052929) {
        // Release 1.4.19: student reset permission and accessibility/compliance cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052929, 'videotrack');
    }

    if ($oldversion < 2026052930) {
        // Release 1.4.20: reporting, poster validation and accessibility hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052930, 'videotrack');
    }

    if ($oldversion < 2026052931) {
        // Release 1.4.21: JS/AMD hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026052931, 'videotrack');
    }

    if ($oldversion < 2026052932) {
        // Release 1.4.22: Moodle HQ style, AMD strict mode and compliance cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052932, 'videotrack');
    }

    if ($oldversion < 2026052933) {
        // Release 1.4.23: accessibility/live-region hardening and AMD validation cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026052933, 'videotrack');
    }


    if ($oldversion < 2026052934) {
        // JavaScript AMD rebuild and hardening release; no database schema changes.
        upgrade_mod_savepoint(true, 2026052934, 'videotrack');
    }


    if ($oldversion < 2026052935) {
        // Release 1.4.25: AMD rebuild and translations update.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052935, 'videotrack');
    }


    if ($oldversion < 2026052936) {
        // Release 1.4.26: AMD lifecycle hardening and own-report CSV export fix.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052936, 'videotrack');
    }


    if ($oldversion < 2026052937) {
        // Release 1.4.27: AMD rebuild, lifecycle hardening and version normalisation.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052937, 'videotrack');
    }

    if ($oldversion < 2026052938) {
        // Release 1.4.28: AMD reaction handling cleanup and test harness compatibility.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052938, 'videotrack');
    }


    if ($oldversion < 2026052939) {
        // Release 1.4.29: upgrade sequence cleanup, settings validation and AMD hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026052939, 'videotrack');
    }

    if ($oldversion < 2026053040) {
        // Release 1.4.30: complete hardening and Moodle HQ packaging cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053040, 'videotrack');
    }


    if ($oldversion < 2026053041) {
        // Release 1.4.31: Moodle HQ style cleanup before initial public install.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053041, 'videotrack');
    }

    if ($oldversion < 2026053042) {
        // Release 1.4.33: version metadata and XMLDB consistency cleanup.
        // No database schema changes for new installations.
        upgrade_mod_savepoint(true, 2026053042, 'videotrack');
    }

    if ($oldversion < 2026053043) {
        // Release 1.4.34: restore hardening and reaction icon consistency.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053043, 'videotrack');
    }

    if ($oldversion < 2026053044) {
        // Release 1.4.35: restore JSON hardening and release metadata update.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053044, 'videotrack');
    }

    if ($oldversion < 2026053045) {
        // Release 1.4.36: report-driven hardening, AMD cleanup and minified rebuild.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053045, 'videotrack');
    }

    if ($oldversion < 2026060100) {
        // Release 1.4.37: normalise plugin and XMLDB version metadata.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060100, 'videotrack');
    }

    if ($oldversion < 2026060101) {
        // Release 1.4.38: align upgraded-site indexes with install.xml.
        $segtable = new xmldb_table('videotrack_seg');
        $index = new xmldb_index('cm_user_idx', XMLDB_INDEX_NOTUNIQUE, ['cmid', 'userid']);
        if (!$dbman->index_exists($segtable, $index)) {
            $dbman->add_index($segtable, $index);
        }

        $statetable = new xmldb_table('videotrack_state');
        $index = new xmldb_index('cm_user_idx', XMLDB_INDEX_NOTUNIQUE, ['cmid', 'userid']);
        if (!$dbman->index_exists($statetable, $index)) {
            $dbman->add_index($statetable, $index);
        }

        $reactevtable = new xmldb_table('videotrack_reactev');
        $oldindex = new xmldb_index('vt_user_type_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'notetype', 'timecreated']);
        if ($dbman->index_exists($reactevtable, $oldindex)) {
            $dbman->drop_index($reactevtable, $oldindex);
        }

        $index = new xmldb_index('vt_user_del_type_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'isdeleted', 'notetype', 'timecreated']);
        if (!$dbman->index_exists($reactevtable, $index)) {
            $dbman->add_index($reactevtable, $index);
        }

        upgrade_mod_savepoint(true, 2026060101, 'videotrack');
    }

    if ($oldversion < 2026060102) {
        // Release 1.4.39: code-only privacy hardening; no database schema change.
        upgrade_mod_savepoint(true, 2026060102, 'videotrack');
    }


    if ($oldversion < 2026060103) {
        // Release 1.4.40: notes AJAX hardening and rebuilt AMD assets.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060103, 'videotrack');
    }

    if ($oldversion < 2026060104) {
        // Release 1.4.41: intro pluginfile support.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060104, 'videotrack');
    }

    if ($oldversion < 2026060105) {
        // Release 1.4.42: version metadata, language and documentation cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060105, 'videotrack');
    }

    if ($oldversion < 2026060106) {
        // Release 1.4.43: AMD notes handler cleanup; no database schema changes.
        upgrade_mod_savepoint(true, 2026060106, 'videotrack');
    }


    if ($oldversion < 2026060107) {
        // Release 1.4.44: accessibility and status-message UX hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060107, 'videotrack');
    }


    if ($oldversion < 2026060108) {
        // No database changes: release 1.4.45 contains AMD-only hardening.
        upgrade_mod_savepoint(true, 2026060108, 'videotrack');
    }


    if ($oldversion < 2026060109) {
        // Release 1.4.46: view attribute escaping cleanup; no database schema changes.
        upgrade_mod_savepoint(true, 2026060109, 'videotrack');
    }


    if ($oldversion < 2026060110) {
        // Release 1.4.47: uploaded file serving hardening; no database schema changes.
        upgrade_mod_savepoint(true, 2026060110, 'videotrack');
    }

    if ($oldversion < 2026060111) {
        // Release 1.4.48: form input type normalisation for Moodle HQ compliance.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060111, 'videotrack');
    }

    if ($oldversion < 2026060112) {
        // Release 1.4.49: subtitle serving and cleanup hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060112, 'videotrack');
    }

    if ($oldversion < 2026060113) {
        // Release 1.4.50: privacy export accumulator cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060113, 'videotrack');
    }


    if ($oldversion < 2026060114) {
        // Release 1.4.51: AMD accessibility and event-bus cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060114, 'videotrack');
    }


    if ($oldversion < 2026060200) {
        // Release 1.4.52: packaging, privacy logging and form handling cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060200, 'videotrack');
    }

    if ($oldversion < 2026060201) {
        // Release 1.4.53: repository-only static tests and documentation packaging alignment.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060201, 'videotrack');
    }


    if ($oldversion < 2026060202) {
        // Release 1.4.54: documentation packaging and privacy task log hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060202, 'videotrack');
    }


    if ($oldversion < 2026060203) {
        // Release 1.4.55: AMD accessibility, event-bus and client validation hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060203, 'videotrack');
    }

    if ($oldversion < 2026060204) {
        // Release 1.4.56: documentation metadata and code-comment cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060204, 'videotrack');
    }

    if ($oldversion < 2026060205) {
        // Release 1.4.57: code-comment language cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060205, 'videotrack');
    }

    if ($oldversion < 2026060206) {
        // Release 1.4.58: AMD maintainability, validation and accessibility cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060206, 'videotrack');
    }

    if ($oldversion < 2026060207) {
        // Release 1.4.59: localised confirmation fallback and Moodle HQ readiness cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060207, 'videotrack');
    }

    if ($oldversion < 2026060208) {
        // Release 1.4.60: status timeout constants and player comment cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060208, 'videotrack');
    }


    if ($oldversion < 2026060209) {
        // Release 1.4.61: accessible status-region relationships and resume notice cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060209, 'videotrack');
    }


    if ($oldversion < 2026060210) {
        // Release 1.4.62: configurable accessible status message timeouts.

        upgrade_mod_savepoint(true, 2026060210, 'videotrack');
    }

    if ($oldversion < 2026060211) {
        // Release 1.4.63: tracker source cleanup for Moodle HQ static analysis.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060211, 'videotrack');
    }

    if ($oldversion < 2026060212) {
        // Release 1.4.64: report header and comment cleanup for Moodle HQ readiness.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060212, 'videotrack');
    }

    if ($oldversion < 2026060213) {
        // Release 1.4.65: configurable cumulative report cluster limit for large datasets.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060213, 'videotrack');
    }


    if ($oldversion < 2026060214) {
        // Release 1.4.66: show the unlimited retention warning only when retention is configured as unlimited.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060214, 'videotrack');
    }


    if ($oldversion < 2026060215) {
        // Release 1.4.67: configurable student-notes report page size for large cohorts.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060215, 'videotrack');
    }


    if ($oldversion < 2026060216) {
        // Release 1.4.68: student-notes report cleanup for Moodle HQ static analysis.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060216, 'videotrack');
    }

    if ($oldversion < 2026060217) {
        // Release 1.4.69: documentation changelog consistency cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060217, 'videotrack');
    }


    if ($oldversion < 2026060218) {
        // Release 1.4.70: conservative tracker-level save serialisation for heartbeat concurrency.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060218, 'videotrack');
    }


    if ($oldversion < 2026060219) {
        // Release 1.4.71: conservative student-note save guard against overlapping submissions.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060219, 'videotrack');
    }


    if ($oldversion < 2026060220) {
        // Release 1.4.72: conservative reaction save/delete guard against overlapping submissions.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060220, 'videotrack');
    }


    if ($oldversion < 2026060221) {
        // Release 1.4.73: conservative tracker stale-continuation guards for asynchronous current-time reads.
        upgrade_mod_savepoint(true, 2026060221, 'videotrack');
    }


    if ($oldversion < 2026060222) {
        // Release 1.4.74: cumulative report closure scope hardening for cluster rendering.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060222, 'videotrack');
    }

    if ($oldversion < 2026060223) {
        // Release 1.4.75: final residual PHP comment language cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060223, 'videotrack');
    }


    if ($oldversion < 2026060224) {
        // Release 1.4.76: course report activity-name formatting uses the course context explicitly.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060224, 'videotrack');
    }


    if ($oldversion < 2026060225) {
        // Release 1.4.77: completion and activity page labels pass explicit contexts to format_string().
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060225, 'videotrack');
    }


    if ($oldversion < 2026060226) {
        // Release 1.4.78: course index page labels pass explicit contexts to format_string().
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060226, 'videotrack');
    }


    if ($oldversion < 2026060227) {
        // Release 1.4.79: reaction timing limits are centralised in the shared AMD reactions module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060227, 'videotrack');
    }


    if ($oldversion < 2026060228) {
        // Release 1.4.80: JSDoc hardening for shared AMD API and tracker modules.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060228, 'videotrack');
    }


    if ($oldversion < 2026060229) {
        // Release 1.4.81: close safe accessibility, GDPR retention logging and report pagination hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060229, 'videotrack');
    }


    if ($oldversion < 2026060230) {
        // Release 1.4.82: accessibility captions and localised status fallback hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060230, 'videotrack');
    }


    if ($oldversion < 2026060231) {
        // Release 1.4.83: add PHPUnit coverage for stable helper functions.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060231, 'videotrack');
    }


    if ($oldversion < 2026060232) {
        // Release 1.4.84: require explicit admin confirmation for unlimited GDPR retention.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060232, 'videotrack');
    }


    if ($oldversion < 2026060233) {
        // Release 1.4.85: remove residual hardcoded AJAX fallback messages from AMD modules.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060233, 'videotrack');
    }


    if ($oldversion < 2026060234) {
        // Release 1.4.86: document the custom AMD AJAX layer design and operational limits.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060234, 'videotrack');
    }


    if ($oldversion < 2026060235) {
        // Release 1.4.87: add PHPUnit coverage for pure tracker interval helpers.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060235, 'videotrack');
    }


    if ($oldversion < 2026060236) {
        // Release 1.4.88: add PHPUnit coverage for custom admin setting validation and GDPR retention confirmation.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060236, 'videotrack');
    }


    if ($oldversion < 2026060237) {
        // Release 1.4.89: document AMD operational limits used by the AJAX and beacon layers.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060237, 'videotrack');
    }


    if ($oldversion < 2026060238) {
        // Release 1.4.90: split AJAX argument validation into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060238, 'videotrack');
    }


    if ($oldversion < 2026060239) {
        // Release 1.4.91: restore the extracted AMD validator module in source and build trees.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060239, 'videotrack');
    }


    if ($oldversion < 2026060240) {
        // Release 1.4.92: split AJAX error classification into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060240, 'videotrack');
    }


    if ($oldversion < 2026060241) {
        // Release 1.4.93: split AJAX retry and jitter handling into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060241, 'videotrack');
    }


    if ($oldversion < 2026060242) {
        // Release 1.4.94: split AJAX transport and timeout handling into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060242, 'videotrack');
    }


    if ($oldversion < 2026060243) {
        // Release 1.4.95: split AJAX request-scope helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060243, 'videotrack');
    }


    if ($oldversion < 2026060244) {
        // Release 1.4.96: restore the extracted AMD request-scope module in source and build trees.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060244, 'videotrack');
    }

    if ($oldversion < 2026060245) {
        // Release 1.4.97: split tracker event helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060245, 'videotrack');
    }


    if ($oldversion < 2026060246) {
        // Release 1.4.98: split tracker state helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060246, 'videotrack');
    }


    if ($oldversion < 2026060247) {
        // Release 1.4.99: split tracker time and seek helpers into a dedicated AMD module.
        upgrade_mod_savepoint(true, 2026060247, 'videotrack');
    }


    if ($oldversion < 2026060248) {
        // Release 1.4.100: split tracker heartbeat helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060248, 'videotrack');
    }


    if ($oldversion < 2026060249) {
        // Release 1.4.101: align tracker heartbeat helper exports after AMD micro-refactor.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060249, 'videotrack');
    }


    if ($oldversion < 2026060250) {
        // Release 1.4.102: split tracker lifecycle helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060250, 'videotrack');
    }


    if ($oldversion < 2026060251) {
        // Release 1.4.103: split tracker segment lifecycle helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060251, 'videotrack');
    }


    if ($oldversion < 2026060252) {
        // Release 1.4.104: split player interval-bar helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060252, 'videotrack');
    }


    if ($oldversion < 2026060253) {
        // Release 1.4.105: fix AMD lint blockers after player interval-bar micro-refactor.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060253, 'videotrack');
    }


    if ($oldversion < 2026060254) {
        // Release 1.4.106: split player resume and poster helpers into dedicated AMD modules.

        upgrade_mod_savepoint(true, 2026060254, 'videotrack');
    }


    if ($oldversion < 2026060255) {
        // Release 1.4.107: split player status helpers into a dedicated AMD module.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060255, 'videotrack');
    }


    if ($oldversion < 2026060256) {
        // Release 1.4.108: restore player resume and poster AMD modules after facade extraction.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060256, 'videotrack');
    }


    if ($oldversion < 2026060257) {
        // Savepoint for Videotrack 1.4.109.
        upgrade_mod_savepoint(true, 2026060257, 'videotrack');
    }

    if ($oldversion < 2026060258) {
        // Release 1.4.110: align personal notes AMD module path with player facade dependency.
        upgrade_mod_savepoint(true, 2026060258, 'videotrack');
    }

    if ($oldversion < 2026060259) {
        // Release 1.4.111: extract player reaction announcement facade.
        upgrade_mod_savepoint(true, 2026060259, 'videotrack');
    }

    return true;
}
