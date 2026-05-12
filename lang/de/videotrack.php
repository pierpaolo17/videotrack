<?php

$string['pluginname'] = 'Video-Tracking';
$string['modulename'] = 'Video-Tracking';
$string['modulenameplural'] = 'Video-Trackings';
$string['pluginadministration'] = 'Video-Tracking-Verwaltung';
$string['videotrack:addinstance'] = 'Eine neue Video-Tracking-Aktivität anlegen';
$string['videotrack:view'] = 'Video-Tracking anzeigen';
$string['videotrack:viewreport'] = 'Video-Tracking-Berichte anzeigen';
$string['videotrack:viewownreport'] = 'Eigenen Video-Tracking-Bericht anzeigen';
$string['videoname'] = 'Aktivitätsname';
$string['youtubeurl'] = 'YouTube-URL';
$string['youtubeurl_help'] = 'Fügen Sie eine Standard-, Kurz- oder Einbettungs-URL von YouTube ein.';
$string['showcontrols'] = 'Player-Steuerelemente anzeigen';
$string['disablekeyboard'] = 'Tastaturkürzel deaktivieren';
$string['showfullscreen'] = 'Vollbildschaltfläche anzeigen';
$string['allowseekforward'] = 'Vorspulen erlauben';
$string['allowseekbackward'] = 'Zurückspulen erlauben';
$string['allowplaybackratechange'] = 'Änderung der Wiedergabegeschwindigkeit erlauben';
$string['countbyvideotime'] = 'Abdeckung anhand der Video-Zeitleiste zählen';
$string['countbyvideotime_help'] = 'Empfohlen. Der Abschluss basiert auf eindeutig abgedeckten Sekunden in der Video-Zeitleiste, nicht auf wiederholtem Ansehen.';
$string['completionpercent'] = 'Erforderlicher Abschlussprozentsatz';
$string['completiondetail:percent'] = 'Ansehen von mindestens {$a}% des Videos verlangen';
$string['completiondetail:minreactions'] = 'Mindestens {$a} unterschiedliche Reaktionen verlangen';
$string['completiondetail:allreactiontypes'] = 'Mindestens eine Reaktion für jeden konfigurierten Reaktionstyp verlangen';
$string['reactionsheader'] = 'Reaktionen';
$string['reactionsenabled'] = 'Reaktionen aktivieren';
$string['reactionsrequired'] = 'Reaktionen verlangen';
$string['minreactions'] = 'Mindestanzahl unterschiedlicher Reaktionen';
$string['requireallreactiontypes'] = 'Mindestens eine Reaktion für jeden konfigurierten Typ verlangen';
$string['completionlogic'] = 'Abschlusslogik';
$string['logicand'] = 'Alle aktivierten Bedingungen (AND)';
$string['logicor'] = 'Beliebige aktivierte Bedingung (OR)';
$string['clusterwindow'] = 'Cluster-Fenster (Sekunden)';
$string['showstudentreport'] = 'Bericht für Teilnehmende anzeigen';
$string['showreactionnotice'] = 'Hinweis zu Reaktionen anzeigen';
$string['reactionnotice'] = 'Hinweis zu Reaktionen';
$string['reactionlabel'] = 'Bezeichnung der Reaktion';
$string['reactiondescription'] = 'Beschreibung der Reaktion';
$string['reactionicontype'] = 'Symboltyp';
$string['reactioniconvalue'] = 'Symbolwert';
$string['reactioniconvalue_help'] = 'Für Emoji geben Sie das Emoji-Zeichen ein. Für Font Awesome geben Sie eine vom Moodle-Theme unterstützte Klasse ein, zum Beispiel fa fa-smile für Font-Awesome-5-Themes oder fa-regular fa-face-smile für Font-Awesome-6-Themes. Die Verfügbarkeit der Symbole hängt vom aktiven Moodle-Theme und der installierten Font-Awesome-Version ab. Lassen Sie dieses Feld leer, wenn Sie eine hochgeladene Symboldatei verwenden.';
$string['reactioniconfile'] = 'Reaktions-Symboldatei';
$string['reactioniconfile_help'] = 'Optionale Bilddatei, die verwendet wird, wenn als Symboltyp „Hochgeladene Datei“ ausgewählt ist. Die akzeptierten Formate hängen von der Moodle-Unterstützung für Webbilder ab.';
$string['reactionrequired'] = 'Für den Abschluss erforderlich';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Font-Awesome-Klasse';
$string['icontype:file'] = 'Hochgeladene Datei';
$string['addreaction'] = 'Reaktion hinzufügen';
$string['invalidyoutubeurl'] = 'Ungültige YouTube-URL.';
$string['err:minreactionsrequired'] = 'Legen Sie eine Mindestanzahl unterschiedlicher Reaktionen fest oder aktivieren Sie die Regel, die alle Reaktionstypen verlangt.';
$string['notice:minreactions'] = 'Mindestens {$a} unterschiedliche Reaktionen sind erforderlich.';
$string['notice:requiredtypes'] = 'Erforderliche Reaktionstypen: {$a}.';
$string['watch'] = 'Ansehen';
$string['report'] = 'Berichte';
$string['reportstudent'] = 'Meine Reaktionen';
$string['reportteacher'] = 'Lehrendenbericht';
$string['report:cumulative'] = 'Kumulativ';
$string['report:perstudent'] = 'Pro Teilnehmendem';
$string['report:userid'] = 'Benutzer/in';
$string['report:uniquecoveredseconds'] = 'Eindeutig abgedeckte Sekunden';
$string['report:completionpercent'] = 'Abschluss %';
$string['report:lastposition'] = 'Letzte Position';
$string['report:iscompleted'] = 'Abgeschlossen';
$string['report:noattempts'] = 'Keine Anzeigedaten gefunden.';
$string['report:noreactions'] = 'Keine Reaktionsdaten gefunden.';
$string['report:timestamp'] = 'Zeitstempel';
$string['report:reaction'] = 'Reaktion';
$string['report:description'] = 'Beschreibung';
$string['report:clicks'] = 'Klicks';
$string['report:students'] = 'Teilnehmende';
$string['report:replay'] = 'Ausschnitt erneut abspielen';
$string['report:delete'] = 'Löschen';
$string['report:sort'] = 'Sortieren nach';
$string['report:sorttime'] = 'Zeitstempel';
$string['report:sortreaction'] = 'Reaktion';
$string['report:sortclicks'] = 'Klicks';
$string['report:aggregation'] = 'Aggregation';
$string['report:aggregationtype'] = 'Gleiche Reaktion innerhalb des Fensters';
$string['report:aggregationpeak'] = 'Peak beliebiger Reaktionen';
$string['report:exportcsv'] = 'CSV exportieren';
$string['progress'] = 'Fortschritt';
$string['uniquereactions'] = 'Unterschiedliche Reaktionen';
$string['removereaction'] = 'Reaktion entfernen';
$string['playerunavailable'] = 'Der Player konnte nicht initialisiert werden.';
$string['yes'] = 'Ja';
$string['no'] = 'Nein';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heading_performance'] = 'Leistung';
$string['setting:heading_accessibility'] = 'Barrierefreiheit';
$string['setting:heading_accessibility_desc'] = 'Einstellungen für Hilfstechnologie-Ansagen sowie Tastatur- und Screenreader-Feedback.';
$string['setting:heading_defaults'] = 'Standardwerte für neue Aktivitäten';
$string['setting:heading_defaults_desc'] = 'Diese Werte werden als Standard verwendet, wenn ein Lehrer eine neue Video-Track-Aktivität erstellt. Jede Aktivität kann weiterhin individuell konfiguriert werden.';
$string['setting:default_desc'] = 'Standardwert für neue Aktivitäten. Kann vom Lehrer für jede einzelne Aktivität überschrieben werden.';
$string['setting:default_completionpercent_desc'] = 'Standard-Mindestprozentsatz des Videos, den der Schüler ansehen muss, um die Aktivität abzuschließen. Auf 0 setzen, um die Abschlussregel standardmäßig zu deaktivieren.';
$string['event:segment_saved'] = 'Wiedergabesegment gespeichert';
$string['event:reaction_saved'] = 'Reaktion eingereicht';
$string['event:note_saved'] = 'Studentennotiz gespeichert';
$string['event:reaction_deleted'] = 'Reaktion gelöscht';
$string['setting:heartbeatinterval'] = 'Heartbeat-Intervall (Sekunden)';
$string['setting:heartbeatinterval_desc'] = 'Wie oft der Player das aktuelle Wiedergabesegment während der kontinuierlichen Wiedergabe auf dem Server speichert. Niedrigere Werte reduzieren das Risiko von Datenverlust bei Browser-Absturz oder Netzwerkausfall, erhöhen aber die Serverlast (eine AJAX-Anfrage + zwei Datenbankabfragen pro Student und Intervall). Empfohlener Bereich: 15–120 Sekunden.';
$string['setting:reactionannouncementinterval'] = 'Intervall für barrierefreie Reaktionsansagen (Sekunden)';
$string['setting:reactionannouncementinterval_desc'] = 'Mindestzeit zwischen wiederholten Screenreader-Ansagen „Reaktionen nicht verfügbar“. Verwenden Sie einen niedrigeren Wert für häufigeres Feedback in kurzen Videos oder einen höheren Wert, um Wiederholungen zu reduzieren. Setzen Sie 0, um wiederholte Ansagen zu deaktivieren. Empfohlener Bereich bei Aktivierung: 10–60 Sekunden.';
$string['setting:reactionreadydebouncems'] = 'Entprellung für bereite Reaktionen (Millisekunden)';
$string['setting:reactionreadydebouncems_desc'] = 'Mindestverzögerung, bevor Feedback zu bereiten Reaktionen nach schnellen Wiedergabeänderungen wiederholt wird. Setzen Sie 0, um die Entprellung zu deaktivieren.';

$string['reactionx'] = 'Reaktion {$a}';

$string['err:reactioniconfilerequired'] = 'Laden Sie eine Symboldatei hoch, wenn als Symboltyp Hochgeladene Datei ausgewählt ist.';


$string['privacy:metadata:common:timecreated'] = 'Zeitpunkt, zu dem der Datensatz erstellt wurde.';
$string['privacy:metadata:common:timemodified'] = 'Zeitpunkt der letzten Änderung des Datensatzes.';
$string['privacy:metadata:videotrack_seg'] = 'Speichert für einen Nutzer aufgezeichnete Betrachtungssegmente in einer Videoaktivität.';
$string['privacy:metadata:videotrack_seg:userid'] = 'Der Nutzer, dessen Betrachtungssegment aufgezeichnet wurde.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Browser-Sitzungskennung, die dem Betrachtungssegment zugeordnet ist.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Serverzeit, zu der das Segment begann.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Serverzeit, zu der das Segment endete.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Position auf der Video-Zeitleiste am Anfang des Segments.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Position auf der Video-Zeitleiste am Ende des Segments.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Während des Segments verwendete Wiedergabegeschwindigkeit.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Grund, warum das Segment beendet wurde.';
$string['privacy:metadata:videotrack_state'] = 'Speichert den aggregierten Betrachtungsstatus eines Nutzers in einer Videoaktivität.';
$string['privacy:metadata:videotrack_state:userid'] = 'Der Nutzer, dessen aggregierter Status gespeichert wurde.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Letzte bekannte Position des Nutzers auf der Video-Zeitleiste.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Dauer des verfolgten Videos in Sekunden.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Anzahl eindeutiger Sekunden der Zeitleiste, die vom Nutzer abgedeckt wurden.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Für den Nutzer berechneter Abschlussprozentsatz.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Zusammengeführte Intervalle zur Berechnung der eindeutigen Abdeckung.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Ob die Aktivität für den Nutzer aktuell als abgeschlossen markiert ist.';
$string['privacy:metadata:videotrack_reactev'] = 'Speichert Reaktionsereignisse, die während der Videowiedergabe aufgezeichnet wurden.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'Der Nutzer, der die Reaktion gesendet hat.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Browser-Sitzungskennung, die dem Reaktionsereignis zugeordnet ist.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Interner Schlüssel der Reaktion zum Zeitpunkt der Aufzeichnung.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Dem Nutzer angezeigte Reaktionsbezeichnung zum Zeitpunkt der Aufzeichnung.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Dem Nutzer angezeigte Reaktionsbeschreibung zum Zeitpunkt der Aufzeichnung.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Position auf der Video-Zeitleiste, an der die Reaktion aufgezeichnet wurde.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Wiedergabegeschwindigkeit beim Aufzeichnen der Reaktion.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Gibt an, ob das Reaktionsereignis vom Nutzer gelöscht wurde.';

$string['videotrack:viewcoursereport'] = 'VideoTrack-Kursbericht anzeigen';
$string['videotrack:viewcoursereport_desc'] = 'Erlaubt das Anzeigen des aggregierten VideoTrack-Berichts für den gesamten Kurs.';
$string['videotrack:overrideplayersettings'] = 'Plattform-Playereinstellungen überschreiben';
$string['videotrack:overrideplayersettings_desc'] = 'Erlaubt dem Lehrer, Player-Einstellungen zu ändern, die vom Administrator als plattformweite Standardwerte festgelegt wurden. Durch Entziehen dieser Berechtigung wird eine einheitliche Player-Richtlinie erzwungen.';
$string['videotrack:overridecompletionsettings'] = 'Plattform-Abschlusseinstellungen überschreiben';
$string['videotrack:overridecompletionsettings_desc'] = 'Erlaubt dem Lehrer, Abschlusseinstellungen zu ändern, die vom Administrator als plattformweite Standardwerte festgelegt wurden.';
$string['setting:lockedbyAdmin'] = 'Diese Einstellungen sind vom Plattformadministrator gesperrt und können nicht für einzelne Aktivitäten geändert werden.';
$string['setting:heading_presets'] = 'Reaktions-Presets';
$string['setting:heading_presets_desc'] = 'Seitenweite Reaktionssets, die Lehrer als Ausgangspunkt verwenden können.';
$string['reactionpreset'] = 'Reaktions-Preset anwenden';
$string['reactionpreset_help'] = 'Wählen Sie ein Preset, um die Reaktionsfelder vorauszufüllen. Die Werte können danach frei bearbeitet werden.';
$string['reactionpreset:none'] = '— manuell konfigurieren —';
$string['presets:manage'] = 'Reaktions-Presets verwalten';
$string['presets:pagetitle'] = 'VideoTrack — Reaktions-Presets';
$string['presets:intro'] = 'Definieren Sie seitenweite Reaktions-Presets als Ausgangspunkt für Lehrer.';
$string['presets:addpreset'] = 'Preset hinzufügen';
$string['presets:backtolist'] = 'Zurück zur Preset-Liste';
$string['presets:saved'] = 'Preset gespeichert.';
$string['presets:deleted'] = 'Preset gelöscht.';
$string['presets:notfound'] = 'Preset nicht gefunden.';
$string['presets:noneyet'] = 'Es wurden noch keine Reaktions-Presets konfiguriert.';
$string['presets:confirmdelete'] = 'Möchten Sie dieses Preset wirklich löschen?';
$string['presets:presetdetails'] = 'Preset-Details';
$string['presets:name'] = 'Preset-Name';
$string['presets:key'] = 'Preset-Schlüssel';
$string['presets:key_help'] = 'Eindeutiger Bezeichner (nur Buchstaben, Zahlen und Unterstriche). Kann nach der Erstellung nicht geändert werden.';
$string['presets:reactions'] = 'Reaktionen';
$string['presets:reactions_help'] = 'Lassen Sie das Label leer, um eine Zeile zu überspringen.';
$string['presets:col_name'] = 'Name';
$string['presets:col_key'] = 'Schlüssel';
$string['presets:col_reactions'] = 'Reaktionen';
$string['presets:col_actions'] = 'Aktionen';
$string['setting:heartbeatinterval_min'] = 'Mindest-Durchsetzungswert: 5 Sekunden.';

$string['reset:userdata'] = 'Alle Schülervisionsdaten löschen (Segmente, Zustände, Reaktionen)';
$string['report:recalculate'] = 'Alle Abschlusszustände neu berechnen';
$string['report:recalculated'] = 'Abschlusszustände für {$a} Benutzer neu berechnet.';
$string['report:heatmap_desc'] = 'Reaktions-Heatmap auf der Video-Timeline (Balkenhöhe = Anzahl Klicks an diesem Punkt):';
$string['report:heatmap_supplementary'] = 'Die Heatmap ist eine ergänzende Visualisierung. Die vollständigen Clusterdaten sind in der folgenden Tabelle verfügbar.';
$string['event:activity_completed'] = 'Video-Track-Aktivität abgeschlossen';

$string['reactioniconfile_notice'] = 'Das Bild wird automatisch auf 64×64 Pixel skaliert (zentrierter Zuschnitt). Für beste Ergebnisse ein quadratisches Bild (1:1) hochladen. Akzeptierte Formate: JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Klicke während der Videowiedergabe auf einen Reaktionsknopf, um deine Reaktion in diesem Moment zu speichern.';

$string['showgradeto'] = 'Note dem Schüler anzeigen';
$string['showgradeto_help'] = 'Wenn aktiviert, sieht der Schüler seine Note direkt auf der Aktivitätsseite.';
$string['report:grade'] = 'Note';
$string['report:gradesaved'] = 'Note erfolgreich gespeichert.';
$string['report:gradepass_hint'] = 'Bestehensgrenze: {$a}';
$string['report:gradenotset'] = 'Noch nicht bewertet';

$string['videosource'] = 'Videoquelle';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Hochladen (MP4/WebM/MP3)';
$string['vimeourl'] = 'Vimeo-URL';
$string['vimeourl_help'] = 'Vimeo-Video-URL einfügen.';
$string['invalidvimeourl'] = 'Keine gültige Vimeo-URL.';
$string['videofile'] = 'Video-/Audiodatei';
$string['videofile_help'] = 'MP4, WebM, MP3 hochladen.';
$string['videofile_notice'] = 'Akzeptierte Formate: MP4, WebM, MP3, M4V, MOV, AAC, M4A.';
$string['setting:heading_player'] = 'Player-Verhalten';
$string['setting:playbackspeeds'] = 'Verfügbare Wiedergabegeschwindigkeiten';
$string['setting:playbackspeeds_desc'] = 'Wählen Sie welche Geschwindigkeiten plattformweit verfügbar sind.';
$string['setting:playbackspeeds_teacher_desc'] = 'Wählen Sie die Wiedergabegeschwindigkeiten für diese Aktivität.';
$string['setting:speed_normal'] = 'normal';
$string['setting:distractionfree'] = 'Ablenkungsfreier Modus';
$string['setting:distractionfree_desc'] = 'Versteckt Header, Footer und Navigation beim Abspielen.';
$string['intervalbar_title'] = 'Gesehene Intervalle — grüne Segmente zeigen bereits gesehene Teile.';
$string['outline:percent'] = '{$a}% gesehen';
$string['outline:nodata'] = 'Noch keine Daten.';
$string['coursereport:title'] = 'VideoTrack — Kursbericht';
$string['coursereport:navlink'] = 'VideoTrack Berichte';
$string['coursereport:intro'] = 'Übersicht aller Video-track-Aktivitäten im Kurs.';
$string['coursereport:nodata'] = 'Keine Video-track-Aktivitäten gefunden.';
$string['coursereport:col_activity'] = 'Aktivität';
$string['coursereport:col_source'] = 'Quelle';
$string['coursereport:col_duration'] = 'Dauer';
$string['coursereport:col_students_started'] = 'Gestartete Schüler';
$string['coursereport:col_avg_percent'] = 'Durchschn. Abdeckung';
$string['coursereport:col_completions'] = 'Abschlüsse';
$string['coursereport:col_reactions'] = 'Reaktionen';
$string['coursereport:col_actions'] = 'Aktionen';

$string['grade:pass'] = 'Bestanden';
$string['grade:fail'] = 'Nicht bestanden';

$string['autoplay'] = 'Automatische Wiedergabe';
$string['autoplay_help'] = 'Startet das Video automatisch. Browser erfordern Stummschalten für Autoplay.';
$string['loop'] = 'Wiederholen';
$string['startmuted'] = 'Stumm starten';
$string['startmuted_help'] = 'Startet die Wiedergabe stummgeschaltet.';
$string['allowdownload'] = 'Download erlauben (nur Upload)';
$string['setting:allowdownload_desc'] = 'Zeigt eine Download-Schaltfläche im HTML5-Player.';
$string['setting:heading_playerbehavior'] = 'Standard-Playerverhalten';
$string['setting:heading_playerbehavior_desc'] = 'Standardwerte für Autoplay, Loop, Stummschalten und Download.';
$string['setting:heading_html5controls'] = 'HTML5-Player-Steuerelemente (Upload-Quelle)';
$string['setting:heading_html5controls_desc'] = 'Wählen Sie verfügbare Steuerelemente für den HTML5-Player.';
$string['setting:html5controls'] = 'Verfügbare Steuerelemente';
$string['setting:html5controls_desc'] = 'Steuerelemente im HTML5-Player auswählen.';
$string['setting:html5controls_teacher_desc'] = 'Steuerelemente für diese Aktivität auswählen.';
$string['ctrl:play'] = 'Play/Pause';
$string['ctrl:progress'] = 'Fortschrittsleiste';
$string['ctrl:current'] = 'Aktuelle Zeit';
$string['ctrl:duration'] = 'Dauer';
$string['ctrl:mute'] = 'Stummschalten';
$string['ctrl:volume'] = 'Lautstärke';
$string['ctrl:speed'] = 'Wiedergabegeschwindigkeit';
$string['ctrl:pip'] = 'Bild-in-Bild';
$string['ctrl:fullscreen'] = 'Vollbild';
$string['ctrl:download'] = 'Herunterladen';

$string['setting:playerwidth'] = 'Maximale Playerbreite (px)';
$string['setting:playerwidth_desc'] = 'Maximale Breite des Videoplayers in Pixeln.';
$string['playerwidth'] = 'Maximale Playerbreite (px)';
$string['playerwidth_help'] = 'Maximale Breite des Players für diese Aktivität. 0 = Plattformstandard.';
$string['setting:rewindstep'] = 'Rückspulschritt (Sekunden)';
$string['setting:rewindstep_desc'] = 'Sekunden, um die die Rückspultaste zurückspult. 0 = deaktiviert. Standard: 10. Wichtig: Wenn "Rückspringen erlauben" für eine Aktivität deaktiviert ist, erscheint die Schaltfläche nicht, auch wenn dieser Wert > 0 ist.';
$string['rewindstep'] = 'Rückspulschritt (Sekunden)';
$string['rewindstep_help'] = 'Sekunden für Rückspulen in dieser Aktivität. 0 = Plattformstandard. Hinweis: Wenn "Rückspringen erlauben" deaktiviert ist, erscheint die Schaltfläche nicht, unabhängig von diesem Wert.';
$string['setting:fastforwardstep'] = 'Vorspulschritt (Sekunden)';
$string['setting:fastforwardstep_desc'] = 'Sekunden, um die die Vorspultaste vorspult. 0 = deaktiviert. Standard: 10. Wichtig: Wenn "Vorspringen erlauben" für eine Aktivität deaktiviert ist, erscheint die Schaltfläche nicht, auch wenn dieser Wert > 0 ist.';
$string['fastforwardstep'] = 'Vorspulschritt (Sekunden)';
$string['fastforwardstep_help'] = 'Sekunden für Vorspulen in dieser Aktivität. 0 = Plattformstandard. Hinweis: Wenn "Vorspringen erlauben" deaktiviert ist, erscheint die Schaltfläche nicht, unabhängig von diesem Wert.';
$string['captionsheader'] = 'Untertitel';
$string['captions'] = 'Untertitel aktivieren';
$string['captions_help'] = 'Aktiviert Untertitel für YouTube (Standard), Vimeo (vorgeladene Spur) oder Upload (VTT-Datei).';
$string['setting:default_captions_desc'] = 'Untertitel für neue Aktivitäten standardmäßig aktivieren.';
$string['captionslang'] = 'Standard-Untertitelsprache';
$string['captionslang_help'] = 'ISO 639-1 Sprachcode (z.B. de, en).';
$string['setting:captionslang_desc'] = 'Standard-Untertitelsprache (ISO 639-1).';
$string['vttfile'] = 'Untertiteldatei (.vtt)';
$string['vttfile_help'] = 'WebVTT-Datei hochladen.';
$string['vttfile_notice'] = 'Akzeptiertes Format: WebVTT (.vtt).';
$string['vimeo_captions_notice'] = 'Vimeo-Untertitel werden auf Vimeo.com verwaltet.';
$string['ctrl:rewind'] = 'Rückspultaste';
$string['ctrl:fastforward'] = 'Vorspultaste';

$string['playerloading'] = 'Videoplayer wird geladen, bitte warten…';
$string['noreactionsyet'] = 'Noch keine Reaktionen aufgezeichnet. Reagieren Sie während der Wiedergabe.';
$string['reaction:error'] = 'Reaktion konnte nicht gespeichert werden. Bitte erneut versuchen.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Wiedergabe fortsetzen';
$string['resumeplayback_desc'] = 'Setzt das Video automatisch an der Stelle fort, an der der Teilnehmer in der letzten Sitzung aufgehört hat.';
$string['resumeplayback_help'] = 'Wenn aktiviert, startet das Video an der zuletzt gespeicherten Position (sofern diese mehr als 5 Sekunden nach Beginn liegt). Teilnehmer können jederzeit manuell zum Anfang springen.';
$string['setting:resumeplayback'] = 'Wiedergabe fortsetzen (Standard)';
$string['setting:resumeplayback_desc'] = 'Standardeinstellung für neue VideoTrack-Aktivitäten. Lehrende können sie pro Aktivität überschreiben.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Maximale Wiedergabegeschwindigkeit';
$string['maxplaybackrate_desc'] = 'Begrenzt die maximale Videogeschwindigkeit, die Teilnehmer auswählen können. 0 = keine Begrenzung.';
$string['maxplaybackrate_help'] = 'Wenn gesetzt, können Teilnehmer das Video nicht schneller als diese Geschwindigkeit abspielen, auch wenn die Player-Steuerung höhere Werte anbietet.';
$string['maxplaybackrate_nolimit'] = 'Keine Begrenzung';
$string['setting:maxplaybackrate'] = 'Maximale Wiedergabegeschwindigkeit (Standard)';
$string['setting:maxplaybackrate_desc'] = 'Standardwert für neue Aktivitäten. Lehrende können ihn pro Aktivität überschreiben.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Interaktives Transkript anzeigen';
$string['showtranscript_desc'] = 'Zeigt neben dem Video ein scrollbar- und anklickbares Transkript an (erfordert eine VTT-Untertiteldatei).';
$string['showtranscript_help'] = 'Liest die hochgeladene VTT-Datei aus und zeigt sie als anklickbare Liste an. Jeder Eintrag enthält Zeitmarke und Text; ein Klick springt im Video zu dieser Stelle.';
$string['transcript_title'] = 'Transkript';
$string['transcript_loading'] = 'Transkript wird geladen…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Klicken Sie auf das Video, um die Wiedergabe zu starten.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['vimeocspwarnlabel'] = 'Der Vimeo-Player konnte nicht geladen werden. Prüfen Sie die Netzwerkverbindung oder bitten Sie den Administrator, player.vimeo.com in der Content Security Policy zu erlauben.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'Fortsetzen ab';
// ── Report: azioni studente ──
$string['report:actions'] = 'Aktionen';
$string['report:resetstudent'] = 'Fortschritt zurücksetzen';
$string['report:resetstudent_confirm'] = 'Möchten Sie den Fortschritt dieses Studenten wirklich zurücksetzen? Alle Viewing-Daten und Reaktionen werden gelöscht und können nicht wiederhergestellt werden.';
$string['report:studentreset'] = 'Der Fortschritt des Studenten wurde zurückgesetzt.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Kapitel-Navigation anzeigen';
$string['showchapters_desc'] = 'Zeigt eine Navigationsleiste mit Kapitelmarken aus der VTT-Datei an. Kapitel sind VTT-Cues mit weniger als 80 Zeichen.';
$string['showchapters_help'] = 'Kurze Cues in der VTT-Datei werden als Kapitelüberschriften interpretiert und als anklickbare Navigationsleiste angezeigt.';
$string['chapters_label'] = 'Videokapitel';
$string['chapterslabel'] = 'Videokapitel';
$string['chapter_label'] = 'Kapitel';
$string['chapterlabel'] = 'Kapitel';
$string['studentnotesenabled'] = 'Teilnehmernotizen aktivieren';
$string['studentnotesenabled_desc'] = 'Erlaubt Teilnehmern, beim Ansehen zeitmarkierte persönliche Notizen zu schreiben.';
$string['studentnotesenabled_help'] = 'Wenn aktiviert, erscheint neben dem Video ein Textfeld. Teilnehmer können eine Notiz zur aktuellen Videozeit speichern. Notizen sind nur für den Autor und Manager im Bericht sichtbar.';
$string['setting:studentnotesenabled'] = 'Teilnehmernotizen aktivieren (Standard)';
$string['setting:studentnotesenabled_desc'] = 'Standardeinstellung für neue VideoTrack-Aktivitäten. Lehrende können sie pro Aktivität überschreiben.';
$string['studentnotes_title'] = 'Meine Notizen';
$string['studentnote_placeholder'] = 'Notiz zu dieser Stelle im Video schreiben…';
$string['studentnote_save'] = 'Notiz speichern';
$string['studentnote_hint'] = 'Die Notiz wird zur aktuellen Videozeit gespeichert. Das Video muss abgespielt werden.';
$string['studentnotes_list_label'] = 'Gespeicherte Notizen';
$string['studentnote_label'] = 'Teilnehmernotiz';
$string['noteerrorlabel'] = 'Notiz konnte nicht gespeichert werden. Bitte versuchen Sie es erneut.';
$string['charsremaininglabel'] = 'Zeichen übrig';
$string['posterimage'] = 'Poster-/Vorschaubild';
$string['posterimage_help'] = 'Laden Sie ein Bild hoch, das vor dem Start des Videos angezeigt wird. Akzeptierte Formate: JPG, PNG, WebP, GIF. Empfohlen: 1280×720 px (16:9).';
$string['posterimage_notice'] = 'Das Posterbild wird vor der Wiedergabe angezeigt und beim Start automatisch ausgeblendet.';
$string['playbutton_label'] = 'Video abspielen';
$string['setting:maxplaybackrate_nolimit'] = 'Keine Begrenzung';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Text einer persönlichen Notiz eines Teilnehmers zu einer bestimmten Videozeit.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Ereignistyp: leer für Standardreaktionen, "note" für persönliche Teilnehmernotizen.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['reactionsdisabled'] = 'Reaktionen sind für diese VideoTrack-Aktivität deaktiviert. Bitte bitten Sie Ihre Lehrperson oder Kursadministration, Reaktionen zu aktivieren, falls sie erforderlich sind.';
$string['studentnotesdisabled'] = 'Teilnehmernotizen sind für diese Aktivität nicht aktiviert.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'Für diese Aktivität wurde keine Videodatei hochgeladen.';
$string['removenote'] = 'Notiz entfernen';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Notizen ausblenden';
$string['notes_show'] = 'Notizen anzeigen';
$string['report:notes_title'] = 'Teilnehmernotizen';
$string['report:nonotes'] = 'Für diese Aktivität wurden keine Notizen geschrieben.';
$string['report:notedate'] = 'Geschrieben am';
$string['report:exportnotes_csv'] = 'Notizen als CSV exportieren';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Schließen';
$string['rewindlabel'] = 'Zurückspulen';
$string['fastforwardlabel'] = 'Vorspulen';
$string['secondslabel'] = 'Sekunden';
$string['removenotelabel'] = 'Notiz entfernen';
// ── Help strings ──
$string['gradepass_help'] = 'Die Mindestbewertung zum Bestehen dieser Aktivität. Teilnehmer mit dieser oder einer höheren Bewertung gelten als bestanden.';


$string['completiondetail:requiredreactions'] = 'Muss diese erforderlichen Reaktionen enthalten: {$a}';

$string['error:playbackrequired'] = 'Das Video muss wiedergegeben werden, bevor diese Aktion gespeichert werden kann.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'PHP-Erweiterung GD nicht verfügbar.';
$string['setting:gd_missing_desc'] = 'Von Lehrenden hochgeladene Reaktionssymbole werden NICHT automatisch auf 64×64 Pixel verkleinert. Die Originaldatei wird unverändert ausgeliefert und kann bei großen Bildern die Ladezeit beeinträchtigen. Für automatische Skalierung muss der Serveradministrator php-gd installieren.';

$string['report:heatmap_legend'] = 'Farblegende der Reaktions-Heatmap';

$string['report:clusterlimitreached'] = 'Der Bericht hat die maximale Anzahl angezeigter Cluster erreicht. Verwenden Sie Filter oder ein kleineres Zeitfenster für eine vollständige Analyse.';

$string['report:showingrecentreactionsoftotal'] = 'Es werden {$a->shown} von {$a->total} Reaktionen angezeigt, von der ältesten zur neuesten.';

$string['report:viewfullreport'] = 'Vollständigen Bericht anzeigen';
$string['studentnotes_view_limited'] = 'Es werden die letzten {$a} Notizen angezeigt. Öffnen Sie den vollständigen Bericht, um alle Notizen zu prüfen.';
$string['report:skiptoheatmaptable'] = 'Heatmap überspringen und zur Datentabelle wechseln';
$string['report:heatmap_textsummary'] = 'Das Diagramm enthält {$a->clusters} Cluster; der größte Cluster enthält {$a->max} Klicks.';
$string['err:reactioniconvaluerequired'] = 'Geben Sie ein Emoji oder eine Font-Awesome-Klasse ein.';
$string['err:reactioniconvalueinvalidfa'] = 'Geben Sie nur gültige Font-Awesome-Klassennamen mit Buchstaben, Zahlen, Leerzeichen und Bindestrichen ein.';

$string['error:reactionratelimit'] = 'Es wurden zu viele Reaktionen in kurzer Zeit gesendet. Sehen Sie das Video weiter an und versuchen Sie es erneut.';
$string['event:student_progress_reset'] = 'VideoTrack-Daten des Teilnehmenden zurückgesetzt';
$string['report:timefrom'] = 'Ab Sekunde';
$string['report:timeto'] = 'Bis Sekunde';
$string['report:clusterlimitreached_help'] = 'Der kumulative Bericht hat die maximale Anzahl an anzeigbaren Clustern erreicht. Verwenden Sie Filter nach Nutzer, Reaktion oder Videozeit, um die Analyse einzugrenzen und spätere Cluster abzurufen.';
$string['report:topclusterssummary'] = 'Wichtigste Cluster in dieser Auswahl:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} Klicks';
$string['error:notesratelimit'] = 'Es wurden zu viele Notizen in kurzer Zeit gesendet. Bitte warten Sie, bevor Sie eine weitere Notiz hinzufügen.';

$string['privacy:segmentschunk'] = 'Anzeigesegmente - Teil {$a}';

$string['privacy:reactionsactivechunk'] = 'Aktive Reaktionen - Teil {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Gelöschte Reaktionen - Teil {$a}';

$string['privacy:notesactivechunk'] = 'Aktive Notizen - Teil {$a}';

$string['privacy:notesdeletedchunk'] = 'Gelöschte Notizen - Teil {$a}';

$string['report:clusterlimitreached_csv'] = 'WARNUNG: Die Cluster-Grenze wurde erreicht. Der Export kann unvollständig sein; wenden Sie Nutzer-, Reaktions- oder Zeitfilter an und exportieren Sie erneut.';

$string['report:notecreatedfrom'] = 'Notizen ab Datum';

$string['report:notecreatedto'] = 'Notizen bis Datum';

$string['reactionsavailableonlyduringplayback'] = 'Reaktionen sind nur während der Videowiedergabe verfügbar.';
$string['reactionsreadyannounce'] = 'Reaktionen sind jetzt verfügbar.';

$string['privacy:state'] = 'Abschlussstatus';

$string['report:clusterlimitrequiresfilters'] = 'Der kumulative Bericht ist unvollständig. Wenden Sie einen Video-Zeitbereichsfilter an, um die verbleibenden Cluster zuverlässig abzurufen.';

$string['report:clusterlimitrequiresfilters_csv'] = 'Der kumulative Export ist unvollständig, weil kein Video-Zeitbereichsfilter angewendet wurde. Wenden Sie Von-Sekunde/Bis-Sekunde-Filter an und exportieren Sie erneut.';
$string['report:clusterexportblocked_csv'] = 'Der Export wurde gestoppt, um unvollständige Daten zu vermeiden. Wenden Sie einen Videozeitfilter an und exportieren Sie erneut.';
$string['report:clusterdisplayblocked'] = 'Die Cluster-Tabelle wurde ausgeblendet, um unvollständige Daten zu vermeiden. Wenden Sie einen Videozeitfilter an, um fortzufahren.';
$string['unknownreaction'] = 'Unbekannte Reaktion';

// Moodle HQ review fallback strings added in 1.0.29.
$string['externalprovider_notice'] = 'Externe Videoanbieter wie YouTube und Vimeo können personenbezogene Daten verarbeiten und Cookies gemäß ihren eigenen Datenschutzrichtlinien setzen. Verwenden Sie hochgeladene Dateien, wenn eine Übermittlung an Dritte nicht erlaubt ist.';
$string['privacy:metadata:youtube'] = 'Bei Verwendung eines YouTube-Videos verbindet sich der Browser des Nutzers mit YouTube, um das Video zu laden und abzuspielen.';
$string['privacy:metadata:youtube:videoid'] = 'Die für diese Aktivität konfigurierte YouTube-Video-ID.';
$string['privacy:metadata:youtube:url'] = 'Die für diese Aktivität konfigurierte YouTube-URL.';
$string['privacy:metadata:vimeo'] = 'Bei Verwendung eines Vimeo-Videos verbindet sich der Browser des Nutzers mit Vimeo, um das Video zu laden und abzuspielen.';
$string['privacy:metadata:vimeo:videoid'] = 'Die für diese Aktivität konfigurierte Vimeo-Video-ID.';
$string['privacy:metadata:vimeo:url'] = 'Die für diese Aktivität konfigurierte Vimeo-URL.';
$string['html5:controls'] = 'Videosteuerungen';
$string['html5:play'] = 'Abspielen';
$string['html5:pause'] = 'Pause';
$string['html5:seek'] = 'Suchen';
$string['html5:volume'] = 'Lautstärke';
$string['html5:mute'] = 'Stummschalten';
$string['html5:unmute'] = 'Stummschaltung aufheben';
$string['html5:speed'] = 'Geschwindigkeit';
$string['html5:pip'] = 'Bild-in-Bild';
$string['html5:fullscreen'] = 'Vollbild';
$string['html5:download'] = 'Herunterladen';
$string['setting:heading_privacy'] = 'Datenschutz und Datenaufbewahrung';
$string['setting:heading_privacy_desc'] = 'Konfigurieren Sie, wie VideoTrack Tracking-, Notiz- und Reaktionsdaten speichert.';
$string['setting:retentionperioddays'] = 'Aufbewahrungsfrist für Trackingdaten (Tage)';
$string['setting:retentionperioddays_desc'] = 'Anzahl der Tage, nach denen VideoTrack alte Tracking-, Notiz- und Reaktionsdaten anonymisiert. 0 bedeutet unbegrenzte Aufbewahrung. Löschanfragen von Nutzern werden immer durch gesalzene Anonymisierung statt durch Löschen aggregierter Analysen bearbeitet.';
$string['setting:strictsessionvalidation'] = 'Gleiche Browser-Sitzung zur Validierung von Notizen und Reaktionen verlangen';
$string['setting:validationfallbackdays'] = 'Validierungsfenster für bisherigen Wiedergabeverlauf (Tage)';
$string['setting:validationfallbackdays_desc'] = 'Maximales Alter in Tagen für bereits gesehene Segmente, die nach einer Aktualisierung oder einem Browserwechsel Notizen und Reaktionen erlauben dürfen. 0 erlaubt historische gesehene Segmente ohne Zeitlimit; dies verbessert die Bedienbarkeit, macht die Überprüfung der akademischen Integrität aber permissiver. Prüfungen derselben Sitzung und aktueller Wiedergabe werden immer zuerst versucht.';
$string['setting:strictsessionvalidation_desc'] = 'Wenn aktiviert, können Notizen und Reaktionen nur für Zeitpunkte gespeichert werden, die in der aktuellen Browser-Sitzung angesehen wurden. Wenn deaktiviert, akzeptiert VideoTrack Zeitpunkte, die derselbe Nutzer in derselben Aktivität bereits angesehen hat, und verbessert so die Bedienbarkeit nach Aktualisieren der Seite oder Browserwechseln, während nicht angesehene Positionen weiterhin abgelehnt werden.';
$string['task:cleanup'] = 'Abgelaufene VideoTrack-Trackingdaten anonymisieren';
$string['privacy:anonymised'] = '[anonymisiert]';
$string['error:playbackpositionnotwatched'] = 'Diese Videoposition wurde noch nicht angesehen, daher kann die Aktion nicht gespeichert werden.';

$string['setting:intrangerequired'] = 'Geben Sie eine ganze Zahl zwischen {$a->min} und {$a->max} ein.';
$string['setting:nonnegativeintrequired'] = 'Geben Sie eine ganze Zahl größer oder gleich 0 ein.';
$string['report:anonymiseduser'] = 'Anonymisierter Nutzer';
$string['report:exportnotes_privacywarning'] = 'Dieser Export kann personenbezogene Daten aus Notizen von Lernenden enthalten. Laden und speichern Sie ihn nur bei berechtigtem Zweck und löschen Sie ihn, wenn er nicht mehr benötigt wird.';
