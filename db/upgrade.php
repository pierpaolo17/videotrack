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
        // Version 1.0.7: accessibility parity for reaction buttons, clearer cumulative
        // report cluster-limit warnings, localized privacy export state section, and
        // version number aligned with the public release sequence. No database schema changes.
        upgrade_mod_savepoint(true, 2026050507, 'videotrack');
    }

    return true;
}
