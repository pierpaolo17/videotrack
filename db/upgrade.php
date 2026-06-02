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
        // Versione 0.8.1: fix apostrofi lang, durationseconds solo crescente,
        // whitelist record DB, settings maxplaybackrate dedup, transazione atomica
        // save_segment, capabilities managereactions/grade, simplify_intervals no-overcount,
        // reaction_counts SQL, appendIconSafe whitelist, GDPR intervaljson, log heartbeat,
        // placeholder prima reazione, export CSV note con useridfilter.
        $table = new xmldb_table('videotrack');
        // Aggiunge campi che potrebbero mancare in upgrade da versioni molto vecchie
        // (questi erano in install.xml ma non in blocchi upgrade precedenti).
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
            ), // allineato a install.xml
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
            ), // allineato a install.xml
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
        // Versione 0.8.6: isdeleted su videotrack_react, settings centesimi,
        // backup posterimage rimosso, upgrade campi mancanti, URL esterni icone,
        // email report con capability, aria-describedby heatmap, SVG corso,
        // validazione upload, durationseconds max 24h.

        // Aggiunge isdeleted a videotrack_react per soft-delete reazioni.
        $react = new xmldb_table('videotrack_react');
        $field = new xmldb_field('isdeleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'sortorder');
        if (!$dbman->field_exists($react, $field)) {
            $dbman->add_field($react, $field);
        }
        upgrade_mod_savepoint(true, 2026050236, 'videotrack');
    }

    if ($oldversion < 2026050243) {
        // Versione 0.9.5: fix has_recent_playback parametri SQL duplicati,
        // reaction_counts ottimizzata in save_reaction, catch JS mostra messaggio server.
        upgrade_mod_savepoint(true, 2026050243, 'videotrack');
    }

    if ($oldversion < 2026050244) {
        // Versione 0.9.6: environment.xml per GD, avviso settings admin, debugging GD.
        upgrade_mod_savepoint(true, 2026050244, 'videotrack');
    }

    if ($oldversion < 2026050245) {
        // Versione 0.9.7: normalizzazione messaggi environment.xml (whitespace).
        upgrade_mod_savepoint(true, 2026050245, 'videotrack');
    }

    if ($oldversion < 2026050246) {
        // Versione 0.9.8: fix install.xml COMMENT escape, maxplaybackrate /100,
        // videotrack_get_reactions isdeleted, mod_form reazioni attive, lib.php
        // isdeleted=0 su update, backup reazioni soft-deleted, default allineati,
        // query email minimizzazione, aria-label delete contestuale.
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

    if ($oldversion < 2026073600) {
        // Release 1.4.36: report-driven hardening, AMD cleanup and minified rebuild.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026073600, 'videotrack');
    }

    if ($oldversion < 2026080100) {
        // Release 1.4.37: normalise plugin and XMLDB version metadata.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080100, 'videotrack');
    }

    if ($oldversion < 2026080101) {
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

        upgrade_mod_savepoint(true, 2026080101, 'videotrack');
    }

    if ($oldversion < 2026080102) {
        // Release 1.4.39: code-only privacy hardening; no database schema change.
        upgrade_mod_savepoint(true, 2026080102, 'videotrack');
    }


    if ($oldversion < 2026080103) {
        // Release 1.4.40: notes AJAX hardening and rebuilt AMD assets.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080103, 'videotrack');
    }

    if ($oldversion < 2026080104) {
        // Release 1.4.41: intro pluginfile support.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080104, 'videotrack');
    }

    if ($oldversion < 2026080105) {
        // Release 1.4.42: version metadata, language and documentation cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080105, 'videotrack');
    }

    if ($oldversion < 2026080106) {
        // Release 1.4.43: AMD notes handler cleanup; no database schema changes.
        upgrade_mod_savepoint(true, 2026080106, 'videotrack');
    }


    if ($oldversion < 2026080107) {
        // Release 1.4.44: accessibility and status-message UX hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080107, 'videotrack');
    }


    if ($oldversion < 2026080108) {
        // No database changes: release 1.4.45 contains AMD-only hardening.
        upgrade_mod_savepoint(true, 2026080108, 'videotrack');
    }


    if ($oldversion < 2026080109) {
        // Release 1.4.46: view attribute escaping cleanup; no database schema changes.
        upgrade_mod_savepoint(true, 2026080109, 'videotrack');
    }


    if ($oldversion < 2026080110) {
        // Release 1.4.47: uploaded file serving hardening; no database schema changes.
        upgrade_mod_savepoint(true, 2026080110, 'videotrack');
    }

    if ($oldversion < 2026080111) {
        // Release 1.4.48: form input type normalisation for Moodle HQ compliance.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026080111, 'videotrack');
    }

    return true;
}
