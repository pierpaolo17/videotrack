<?php

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
            new xmldb_field('showstudentreport',       XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'), // allineato a install.xml
            new xmldb_field('clusterwindow',           XMLDB_TYPE_INTEGER, '5', null, XMLDB_NOTNULL, null, '30'),
            new xmldb_field('disablekeyboard',         XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field('showfullscreen',          XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowplaybackratechange', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowseekforward',        XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('allowseekbackward',       XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1'),
            new xmldb_field('reactionsrequired',       XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0'),
            new xmldb_field('minreactions',            XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0'), // allineato a install.xml
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


    if ($oldversion < 2026053000) {
        // v1.0.30: privacy user lists exclude anonymised pseudo-users,
        // anonymised ids are scoped per activity, retention/anonymisation
        // recordsets are processed safely after closing cursors, and report
        // output hides negative pseudo-user identifiers.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053000, 'videotrack');
    }


    if ($oldversion < 2026053100) {
        // v1.0.31: external input hardening, restore handling for anonymised
        // aggregate records, language string completion and coding style cleanup.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026053100, 'videotrack');
    }


    if ($oldversion < 2026060100) {
        // v1.0.32: documentation, privacy wording, translation completeness
        // and anonymisation salt locking hardening. No database schema changes.
        upgrade_mod_savepoint(true, 2026060100, 'videotrack');
    }


    if ($oldversion < 2026060200) {
        // v1.0.33: anonymised session ids are kept within the database
        // sessionid length limit and documentation/logging refinements were
        // applied. No database schema changes.
        upgrade_mod_savepoint(true, 2026060200, 'videotrack');
    }


    if ($oldversion < 2026060300) {
        // v1.0.34: Polish language pack and documentation alignment.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060300, 'videotrack');
    }


    if ($oldversion < 2026060400) {
        // v1.0.35: security, accessibility and Moodle HQ refinements.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060400, 'videotrack');
    }


    if ($oldversion < 2026060500) {
        // v1.0.36: language capability string keys were aligned with Moodle
        // role UI conventions and Font Awesome help text was clarified.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060500, 'videotrack');
    }


    if ($oldversion < 2026060600) {
        // Backfill indexes introduced in 1.0.36 install.xml but missing from
        // upgrade.php, plus report/view accessibility refinements.
        $table = new xmldb_table('videotrack_reactev');
        if ($dbman->table_exists($table)) {
            $indexes = [
                new xmldb_index('vt_user_idx', XMLDB_INDEX_NOTUNIQUE, ['videotrackid', 'userid', 'isdeleted']),
                new xmldb_index('vt_reaction_idx', XMLDB_INDEX_NOTUNIQUE, ['videotrackid', 'reactionid', 'isdeleted']),
                new xmldb_index('user_vt_idx', XMLDB_INDEX_NOTUNIQUE, ['userid', 'videotrackid', 'isdeleted']),
            ];
            foreach ($indexes as $index) {
                if (!$dbman->index_exists($table, $index)) {
                    $dbman->add_index($table, $index);
                }
            }
        }
        upgrade_mod_savepoint(true, 2026060600, 'videotrack');
    }


    if ($oldversion < 2026060700) {
        // v1.0.38: restore Polish language pack, update documentation, clarify
        // retention/pseudonymisation behaviour, reduce noisy debug logging and
        // make note/reaction validation more usable after pauses without accepting
        // unwatched timestamps. No database schema changes.
        upgrade_mod_savepoint(true, 2026060700, 'videotrack');
    }


    if ($oldversion < 2026060800) {
        // v1.0.39: validation fallback age setting, Polish language fixes,
        // minor accessibility, UX and coding-style refinements.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026060800, 'videotrack');
    }


    if ($oldversion < 2026060900) {
        // v1.0.40: course report aggregation fix, initial disabled state for
        // reaction buttons, stricter heartbeat setting validation and clearer
        // validation/privacy documentation. No database schema changes.
        upgrade_mod_savepoint(true, 2026060900, 'videotrack');
    }

    if ($oldversion < 2026061000) {
        // v1.0.41: accessibility, report performance and admin setting hardening.
        // No database schema changes.
        upgrade_mod_savepoint(true, 2026061000, 'videotrack');
    }


    if ($oldversion < 2026061100) {
        // v1.0.42: hotfix for autoloaded admin setting classes, reaction
        // accessibility refinements, course report SQL optimisation and
        // language/documentation cleanup. No database schema changes.
        upgrade_mod_savepoint(true, 2026061100, 'videotrack');
    }


    if ($oldversion < 2026061200) {
        // v1.0.43: accessibility announcements, admin setting class cleanup,
        // dark-mode friendly interval bars and reaction count index.
        $table = new xmldb_table('videotrack_reactev');
        $index = new xmldb_index('vt_user_del_type_time_idx', XMLDB_INDEX_NOTUNIQUE,
            ['videotrackid', 'userid', 'isdeleted', 'notetype', 'timecreated']);
        if ($dbman->table_exists($table) && !$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
        upgrade_mod_savepoint(true, 2026061200, 'videotrack');
    }


    if ($oldversion < 2026061300) {
        // v1.0.44: remove redundant reaction-event indexes now covered by
        // more selective composite indexes, and apply accessibility/build
        // maintenance refinements. No data transformation required.
        $table = new xmldb_table('videotrack_reactev');
        if ($dbman->table_exists($table)) {
            $indexes = [
                new xmldb_index('vt_user_idx', XMLDB_INDEX_NOTUNIQUE,
                    ['videotrackid', 'userid', 'isdeleted']),
                new xmldb_index('vt_user_type_time_idx', XMLDB_INDEX_NOTUNIQUE,
                    ['videotrackid', 'userid', 'notetype', 'timecreated']),
            ];
            foreach ($indexes as $index) {
                if ($dbman->index_exists($table, $index)) {
                    $dbman->drop_index($table, $index);
                }
            }
        }
        upgrade_mod_savepoint(true, 2026061300, 'videotrack');
    }


    if ($oldversion < 2026061400) {
        // v1.0.45: language, accessibility and validation refinements only.
        // No schema or data transformation required.
        upgrade_mod_savepoint(true, 2026061400, 'videotrack');
    }


    if ($oldversion < 2026061500) {
        // v1.0.46: accessibility, configuration, language and documentation refinements.
        // No schema or data transformation required.
        upgrade_mod_savepoint(true, 2026061500, 'videotrack');
    }


    if ($oldversion < 2026061600) {
        // v1.0.47: accessibility setting refinements, CSS maintenance,
        // language cleanup and rebuilt AMD artefacts. No schema changes.
        upgrade_mod_savepoint(true, 2026061600, 'videotrack');
    }

    if ($oldversion < 2026061700) {
        // v1.0.48: accessibility announcement defaults, copy refinements and
        // rebuilt AMD artefacts. No schema changes.
        upgrade_mod_savepoint(true, 2026061700, 'videotrack');
    }

    if ($oldversion < 2026061800) {
        // v1.0.49: standardise reaction announcement interval storage to milliseconds.
        $currentinterval = get_config('mod_videotrack', 'reactionannouncementinterval');
        if ($currentinterval !== false && $currentinterval !== null && $currentinterval !== '') {
            $currentinterval = (int)$currentinterval;
            if ($currentinterval > 0 && $currentinterval <= 120) {
                set_config('reactionannouncementinterval', $currentinterval * 1000, 'mod_videotrack');
            } else if ($currentinterval === 0) {
                // Explicitly disabled by the administrator; preserve the setting.
            } else if ($currentinterval > 120 && $currentinterval <= 120000) {
                // Value is already stored as milliseconds after the unit change; keep it unchanged.
            } else {
                // Out-of-range legacy value: reset to the documented default of 30 seconds.
                set_config('reactionannouncementinterval', 30000, 'mod_videotrack');
            }
        }

        upgrade_mod_savepoint(true, 2026061800, 'videotrack');
    }

    if ($oldversion < 2026061900) {
        // v1.0.50: language and setting copy refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026061900, 'videotrack');
    }

    if ($oldversion < 2026062000) {
        // v1.0.51: preserve disabled AT announcement settings and harden heartbeat bounds. No schema changes.
        upgrade_mod_savepoint(true, 2026062000, 'videotrack');
    }


    if ($oldversion < 2026062100) {
        // v1.0.52: preserve site-level zero values for rewind/fast-forward and harden validation defaults.
        upgrade_mod_savepoint(true, 2026062100, 'videotrack');
    }


    if ($oldversion < 2026062200) {
        // v1.0.53: teacher-facing validation and configuration helper consistency. No schema changes.
        upgrade_mod_savepoint(true, 2026062200, 'videotrack');
    }


    if ($oldversion < 2026062300) {
        // v1.0.54: runtime include hardening, player-width default preservation and teacher-facing copy.
        upgrade_mod_savepoint(true, 2026062300, 'videotrack');
    }


    if ($oldversion < 2026062400) {
        // v1.0.55: packaging recovery and configuration validation cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026062400, 'videotrack');
    }

    if ($oldversion < 2026062500) {
        // v1.0.56: player-width validation alignment. Legacy site-level 0 meant "use default 960".
        $legacywidth = get_config('mod_videotrack', 'playerwidth');
        if ($legacywidth !== false && $legacywidth !== null && $legacywidth !== '' && (int)$legacywidth === 0) {
            set_config('playerwidth', 960, 'mod_videotrack');
        }

        upgrade_mod_savepoint(true, 2026062500, 'videotrack');
    }

    if ($oldversion < 2026062600) {
        // v1.0.57: Polish language refinements and teacher-facing player width guidance. No schema changes.
        upgrade_mod_savepoint(true, 2026062600, 'videotrack');
    }

    if ($oldversion < 2026062700) {
        // v1.0.58: documentation, language and accessibility refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026062700, 'videotrack');
    }


    if ($oldversion < 2026062800) {
        // v1.0.59: language/help text refinements and form accessibility note cleanup. No schema changes.
        upgrade_mod_savepoint(true, 2026062800, 'videotrack');
    }


    if ($oldversion < 2026062900) {
        // v1.0.60: language/help text refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026062900, 'videotrack');
    }

    if ($oldversion < 2026063000) {
        // v1.0.61: Polish help text refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026063000, 'videotrack');
    }

    if ($oldversion < 2026070100) {
        // v1.0.62: documentation coverage and factual corrections. No schema changes.
        upgrade_mod_savepoint(true, 2026070100, 'videotrack');
    }

    if ($oldversion < 2026070101) {
        // v1.0.63: privacy and documentation refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026070101, 'videotrack');
    }

    if ($oldversion < 2026070102) {
        // v1.0.64: release metadata and documentation version alignment. No schema changes.
        upgrade_mod_savepoint(true, 2026070102, 'videotrack');
    }

    if ($oldversion < 2026070103) {
        // v1.0.65: HTML5 player robustness fixes. No database schema changes.
        upgrade_mod_savepoint(true, 2026070103, 'videotrack');
    }


    if ($oldversion < 2026070104) {
        // v1.0.66: HTML5 accessibility refinements and VTT timeout documentation. No schema changes.
        upgrade_mod_savepoint(true, 2026070104, 'videotrack');
    }

    if ($oldversion < 2026070105) {
        // v1.0.67: HTML5 accessibility and transcript seek refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026070105, 'videotrack');
    }


    if ($oldversion < 2026070106) {
        // v1.0.68: HTML5 volume initial state, note counter and documentation refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026070106, 'videotrack');
    }

    if ($oldversion < 2026070107) {
        // v1.0.69: tracking concurrency, privacy export and accessibility refinements. No schema changes.
        upgrade_mod_savepoint(true, 2026070107, 'videotrack');
    }

    if ($oldversion < 2026070108) {
        // v1.0.70: release metadata, refresh-completion lock and VTT fetch/parser hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026070108, 'videotrack');
    }

    if ($oldversion < 2026070109) {
        // v1.0.71: accessibility, VTT validation, note queries and idempotent delete hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026070109, 'videotrack');
    }


    if ($oldversion < 2026070110) {
        // v1.0.72: note endpoint semantics, transcript fallback and accessibility/build hardening. No schema changes.
        upgrade_mod_savepoint(true, 2026070110, 'videotrack');
    }

    return true;
}
