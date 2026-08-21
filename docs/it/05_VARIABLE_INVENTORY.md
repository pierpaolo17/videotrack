# Inventario dati e configurazione

Questo documento registra campi persistenti e contratti di configurazione pubblici. Le variabili locali restano documentate da tipi, DocBlock e JSDoc del sorgente.

## Tabelle XMLDB

### `videotrack`

Main table for videotrack instances

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `course` | `int`(10) | NOT NULL; default `0` |  |
| `name` | `char`(255) | NOT NULL |  |
| `intro` | `text` | nullable |  |
| `introformat` | `int`(4) | NOT NULL; default `0` |  |
| `youtubeurl` | `text` | nullable |  |
| `videoid` | `char`(32) | nullable; default `` |  |
| `videosource` | `char`(20) | NOT NULL; default `youtube` | youtube | vimeo | upload |
| `videourl` | `text` | nullable | URL Vimeo o riferimento al nome del file caricato |
| `playbackspeeds` | `char`(100) | nullable; default `` | Velocità consentite separate da virgola; vuoto = default sito |
| `autoplay` | `int`(1) | NOT NULL; default `0` | Avvia automaticamente il video |
| `loopenabled` | `int`(1) | NOT NULL; default `0` | Ripeti il video al termine |
| `startmuted` | `int`(1) | NOT NULL; default `0` | Avvia il video senza audio |
| `allowdownload` | `int`(1) | NOT NULL; default `0` | Mostra il controllo download (solo sorgente caricata) |
| `html5controls` | `char`(255) | nullable; default `` | Elenco controlli HTML5 separati da virgola; vuoto = default sito |
| `playerwidth` | `int`(5) | NOT NULL; default `0` | Larghezza massima player in px; 0 = default sito |
| `rewindstep` | `int`(3) | NOT NULL; default `0` | Secondi indietro per clic; 0 = default sito |
| `fastforwardstep` | `int`(3) | NOT NULL; default `0` | Secondi avanti per clic; 0 = default sito |
| `captions` | `int`(1) | NOT NULL; default `0` | Abilita sottotitoli |
| `captionslang` | `char`(10) | nullable; default `` | Codice lingua sottotitoli predefinito (es. it, en) |
| `durationseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `showcontrols` | `int`(1) | NOT NULL; default `1` |  |
| `disablekeyboard` | `int`(1) | NOT NULL; default `0` |  |
| `showfullscreen` | `int`(1) | NOT NULL; default `1` |  |
| `allowseekforward` | `int`(1) | NOT NULL; default `1` |  |
| `allowseekbackward` | `int`(1) | NOT NULL; default `1` |  |
| `allowplaybackratechange` | `int`(1) | NOT NULL; default `1` |  |
| `resumeplayback` | `int`(1) | NOT NULL; default `0` | Riprendi dall’ultima posizione salvata |
| `maxplaybackrate` | `int`(4) | NOT NULL; default `0` | Velocità massima in centesimi (0=nessun limite, 150=1,5x, 200=2x) |
| `blockedseekplaybackrate` | `int`(4) | NOT NULL; default `50` | Velocità dopo avanzamento bloccato in centesimi (50=0,5x, 100=1x) |
| `showtranscript` | `int`(1) | NOT NULL; default `0` | Mostra il pannello trascrizione VTT interattiva per le sorgenti supportate |
| `showchapters` | `int`(1) | NOT NULL; default `0` | Mostra la navigazione capitoli VTT per le sorgenti supportate |
| `studentnotesenabled` | `int`(1) | NOT NULL; default `0` | Consenti note personali temporizzate durante la visione |
| `bookmarksenabled` | `int`(1) | NOT NULL; default `0` | Consenti segnalibri privati nominati in timestamp già visti |
| `integrityindicatorsenabled` | `int`(1) | NOT NULL; default `0` | Registra indicatori diagnostici di integrità privacy-safe |
| `pauseonfocusloss` | `int`(1) | NOT NULL; default `0` | Metti in pausa quando la pagina è nascosta; il focus finestra segue la policy accessibilità sito |
| `preventpictureinpicture` | `int`(1) | NOT NULL; default `0` | Prevenzione best effort del Picture-in-Picture |
| `randomfocuspauses` | `int`(1) | NOT NULL; default `0` | Pausa dopo un intervallo casuale attivo per richiamare l’attenzione |
| `acknowledgementenabled` | `int`(1) | NOT NULL; default `0` | Richiedi una presa visione esplicita della dichiarazione configurata |
| `acknowledgementtext` | `text` | nullable | Dichiarazione di presa visione scritta dal docente |
| `acknowledgementformat` | `int`(4) | NOT NULL; default `1` | Formato del testo della dichiarazione |
| `acknowledgementtiming` | `int`(1) | NOT NULL; default `0` | 0=conferma in qualsiasi momento; 1=solo dopo l’ultimo secondo |
| `completionacknowledgement` | `int`(1) | NOT NULL; default `0` | Usa la presa visione corrente come condizione di completamento |
| `forumpostingenabled` | `int`(1) | NOT NULL; default `0` | Consenti di comporre una discussione Forum dal tempo video corrente |
| `linkedforumid` | `int`(10) | NOT NULL; default `0` | ID dell’istanza Forum collegata nello stesso corso |
| `forumsubjecttemplate` | `text` | nullable | Template oggetto Forum con placeholder {timestamp} e {activity} |
| `csvdelimiter` | `char`(20) | NOT NULL; default `inherit` | Override separatore CSV: eredita, virgola, punto e virgola, section, hash o pipe |
| `csvexportfields` | `text` | nullable | Campi facoltativi di contesto/identità separati da virgola per gli export CSV |
| `countbyvideotime` | `int`(1) | NOT NULL; default `1` |  |
| `completionpercent` | `int`(3) | NOT NULL; default `0` |  |
| `reactionsenabled` | `int`(1) | NOT NULL; default `0` |  |
| `reactionsrequired` | `int`(1) | NOT NULL; default `0` |  |
| `minreactions` | `int`(10) | NOT NULL; default `0` |  |
| `requireallreactiontypes` | `int`(1) | NOT NULL; default `0` |  |
| `completionlogic` | `char`(10) | NOT NULL; default `and` |  |
| `clusterwindow` | `int`(3) | NOT NULL; default `30` |  |
| `showstudentreport` | `int`(1) | NOT NULL; default `1` |  |
| `showreactionnotice` | `int`(1) | NOT NULL; default `1` |  |
| `reactionnoticeformat` | `int`(4) | NOT NULL; default `1` |  |
| `reactionnotice` | `text` | nullable |  |
| `grade` | `int`(10) | NOT NULL; default `0` | 0=nessun voto, >0=punteggio massimo, <0=ID scala negativo |
| `gradepass` | `number`(10) | NOT NULL; default `0.00000` | Voto minimo per superare (gradepass nel gradebook) |
| `showgradeto` | `int`(1) | NOT NULL; default `0` | 1=mostra il voto allo studente in view.php |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_seg`

Richieste di riproduzione e segmenti visti validati dal server

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `requestid` | `char`(64) | NOT NULL | Identificativo di idempotenza generato una sola volta per richiesta browser |
| `wallclockstart` | `int`(10) | NOT NULL; default `0` |  |
| `wallclockend` | `int`(10) | NOT NULL; default `0` |  |
| `videotimestart` | `number`(10) | NOT NULL; default `0.000` |  |
| `videotimeend` | `number`(10) | NOT NULL; default `0.000` |  |
| `playbackrate` | `number`(6) | NOT NULL; default `1.000` |  |
| `endreason` | `char`(32) | NOT NULL; default `unknown` |  |
| `servervalidated` | `int`(1) | NOT NULL; default `0` | Indica se il segmento ha superato il controllo di riproduzione autorevole lato server |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_state`

Aggregated unique coverage per user and activity

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `lastposition` | `number`(10) | NOT NULL; default `0.000` |  |
| `durationseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `serverlastactivity` | `int`(20) | NOT NULL; default `0` | Timestamp in millisecondi dell’ultimo handshake o richiesta segmento |
| `serverplaybacksessionid` | `char`(64) | NOT NULL; default vuoto | Sessione browser attualmente autorizzata a consumare il credito server |
| `serverbudgetseconds` | `number`(12,3) | NOT NULL; default `0.000` | Budget cumulativo di credito di riproduzione autorizzato dal server |
| `servercreditedseconds` | `number`(12,3) | NOT NULL; default `0.000` | Secondi video grezzi cumulativi addebitati al budget server |
| `uniquecoveredseconds` | `number`(10) | NOT NULL; default `0.000` |  |
| `completionpercent` | `number`(6) | NOT NULL; default `0.00` |  |
| `intervaljson` | `text` | nullable |  |
| `iscompleted` | `int`(1) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_integrity`

Privacy-safe diagnostic integrity signals

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `eventtype` | `char`(32) | NOT NULL |  |
| `videotime` | `number`(10) | NOT NULL; default `0.000` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_react`

Configured reactions per activity

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `reactionkey` | `char`(100) | NOT NULL |  |
| `label` | `char`(255) | NOT NULL |  |
| `description` | `text` | nullable |  |
| `icontype` | `char`(10) | NOT NULL; default `emoji` |  |
| `iconvalue` | `char`(255) | NOT NULL |  |
| `requiredforcompletion` | `int`(1) | NOT NULL; default `0` |  |
| `sortorder` | `int`(10) | NOT NULL; default `0` |  |
| `isdeleted` | `int`(1) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_reactev`

Reaction click events

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `videoid` | `char`(32) | NOT NULL |  |
| `sessionid` | `char`(64) | NOT NULL |  |
| `reactionid` | `int`(10) | NOT NULL; default `0` |  |
| `reactionkey` | `char`(100) | NOT NULL |  |
| `reactionlabel` | `char`(255) | NOT NULL |  |
| `reactiondesc` | `text` | nullable |  |
| `notetext` | `text` | nullable | Testo privato per note o etichette segnalibro |
| `notetype` | `char`(20) | NOT NULL | Vuoto=reazione; note=nota studente; bookmark=segnalibro privato |
| `videotime` | `number`(10) | NOT NULL; default `0.000` |  |
| `playbackrate` | `number`(6) | NOT NULL; default `1.000` |  |
| `isdeleted` | `int`(1) | NOT NULL; default `0` |  |
| `timecreated` | `int`(10) | NOT NULL; default `0` |  |
| `timemodified` | `int`(10) | NOT NULL; default `0` |  |

### `videotrack_acknowledge`

Explicit learner acknowledgements of versioned activity statements

| Campo | Tipo | Null/default | Commento |
|---|---|---|---|
| `id` | `int`(10) | NOT NULL |  |
| `videotrackid` | `int`(10) | NOT NULL; default `0` |  |
| `courseid` | `int`(10) | NOT NULL; default `0` |  |
| `cmid` | `int`(10) | NOT NULL; default `0` |  |
| `userid` | `int`(10) | NOT NULL; default `0` |  |
| `statementhash` | `char`(64) | NOT NULL |  |
| `instanceversion` | `int`(10) | NOT NULL; default `0` |  |
| `viewedseconds` | `number`(10) | nullable | Secondi unici coperti alla conferma; null per conferme storiche |
| `viewedpercent` | `number`(6) | nullable | Percentuale video coperta alla conferma; null per conferme storiche |
| `timeconfirmed` | `int`(10) | NOT NULL; default `0` |  |

## Impostazioni sito

Tutte le chiavi sono memorizzate sotto `mod_videotrack`.

- `analyticsminusers`
- `bookmarkmaxlength`
- `bookmarksenabled`
- `csvdelimiter`
- `csvexportfields`
- `default_allowdownload`
- `default_allowplaybackratechange`
- `default_allowseekbackward`
- `default_allowseekforward`
- `default_autoplay`
- `default_captions`
- `default_captionslang`
- `default_clusterwindow`
- `default_completionpercent`
- `default_disablekeyboard`
- `default_loop`
- `default_showcontrols`
- `default_showfullscreen`
- `default_startmuted`
- `distractionfree`
- `fastforwardstep`
- `focuslossgraceseconds`
- `focuslosspolicy`
- `gd_missing_warning`
- `heading_accessibility`
- `heading_csvexport`
- `heading_defaults`
- `heading_html5controls`
- `heading_integrity`
- `heading_performance`
- `heading_player`
- `heading_playerbehavior`
- `heading_presets`
- `heading_privacy`
- `heartbeatinterval`
- `html5controls`
- `maxplaybackrate`
- `notemaxlength`
- `playbackspeeds`
- `playerwidth`
- `presets_link`
- `randompausemaxseconds`
- `randompauseminseconds`
- `reactionannouncementinterval`
- `reactionreadydebouncems`
- `reportclusterlimit`
- `reportnotespagesize`
- `resumeplayback`
- `retention_unlimited_warning`
- `retentionperioddays`
- `retentionunlimitedconfirmed`
- `rewindstep`
- `statuserrortimeoutms`
- `statusinfotimeoutms`
- `strictsessionvalidation`
- `studentnotesenabled`
- `validationfallbackdays`

## Gruppo di eccezione focus del corso

L'eccezione di accessibilità alla policy focus strict usa un gruppo Moodle core per corso; VideoTrack non memorizza la motivazione dell'accomodamento né altri attributi del partecipante.

- `idnumber` stabile: `mod_videotrack_focus_exception`
- `visibility`: `GROUPS_VISIBILITY_NONE`
- `participation`: `0`
- L'appartenenza diretta trasforma solo per quel partecipante la policy effettiva di sfocatura finestra da `strict` a `hiddenonly`.
- Pausa su documento nascosto, validazione della riproduzione, completamento e regole di interazione restano invariati.

## Capability di partecipazione learner

- `mod/videotrack:participate` — permesso canonico di scrittura nel contesto modulo per tracking learner, reazioni, note, segnalibri, indicatori di integrità, prese visione ed export personale dei segnalibri. I permessi predefiniti sono clonati da `moodle/course:isincompletionreports`; l’accesso ai report è indipendente.

## Capability dei report di attività

- `mod/videotrack:viewreport` — autorizzazione storica completa e retrocompatibile ai report di attività; implica tutti i permessi granulari e continua a proteggere manutenzione/reset/ricalcolo dei report. Durante l’upgrade ciascuna nuova capability granulare clona le assegnazioni di ruolo esistenti di `viewreport`, che l’amministratore può poi separare.
- `mod/videotrack:viewaggregatereport` — visualizza cumulativo e Analytics di istanza mantenendo il masking `analyticsminusers` e senza filtro per studente.
- `mod/videotrack:viewindividualreport` — visualizza il dettaglio learner; rende inoltre esatti gli aggregati autorizzati dell’attività entro lo stesso scope Moodle learner/gruppi.
- `mod/videotrack:exportaggregatereport` — scarica output aggregati cumulativi/Analytics con lo stesso livello privacy della vista aggregata.
- `mod/videotrack:exportindividualreport` — scarica dati learner/dettagliati quando è autorizzata anche la visualizzazione individuale.

## Servizi AJAX

- `mod_videotrack_save_integrity_event` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_start_playback` — apre una finestra server idempotente a credito zero prima del tracking dei segmenti.
- `mod_videotrack_save_segment` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_save_reaction` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_delete_reaction` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_delete_note` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_save_bookmark` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_delete_bookmark` — servizio di scrittura autenticato con `mod/videotrack:participate`.
- `mod_videotrack_save_note` — servizio di scrittura autenticato con `mod/videotrack:participate`.

## Chiavi di configurazione browser/player

L’elemento script JSON creato da `view.php` è il contratto unico server→player.

- `allowdownload`
- `allowplaybackratechange`
- `allowseekbackward`
- `allowseekforward`
- `autoblockedlabel`
- `autoplay`
- `beaconurl`
- `blockedseekplaybackrate`
- `bookmarkdeletedlabel`
- `bookmarkemptylabel`
- `bookmarkerrorlabel`
- `bookmarkmaxlength`
- `bookmarkreplaylabel`
- `bookmarksavedlabel`
- `bookmarksenabled`
- `bookmarkslimitedlabel`
- `bookmarksmaxrendered`
- `bookmarksnonelabel`
- `bookmarktoolonglabel`
- `captions`
- `captionslang`
- `chapterlabel`
- `chapterlegacymode`
- `chapterslabel`
- `chaptersloadinglabel`
- `chaptersunavailablelabel`
- `chapterurl`
- `charsremaininglabel`
- `cmid`
- `disablekeyboard`
- `dismisslabel`
- `duration` — durata osservata dal player usata soltanto per UI/clamping della timeline; non è mai autorevole per completamento o presa visione a fine video.
- `fastforwardlabel`
- `fastforwardstep`
- `focuslossgracems`
- `focuslosspolicy`
- `focuspausedlabel`
- `forumpostbuttonid`
- `forumposterrorlabel`
- `forumpoststatusid`
- `forumposturl`
- `heartbeatinterval`
- `html5controls`
- `html5controlslabel`
- `html5downloadlabel`
- `html5elapsedlabel`
- `html5fullscreenlabel`
- `html5mutelabel`
- `html5pauselabel`
- `html5piplabel`
- `html5playlabel`
- `html5seeklabel`
- `html5speedlabel`
- `html5unmutelabel`
- `html5volumelabel`
- `integrityerrorlabel`
- `integrityindicatorsenabled`
- `intervaljson`
- `loop`
- `maxplaybackrate`
- `nofilelabel`
- `notedeletedlabel`
- `noteemptylabel`
- `noteerrorlabel`
- `notemaxlength`
- `noteplaybackrequiredlabel`
- `notesavedlabel`
- `noteshidelabel`
- `noteshowlabel`
- `notesmaxrendered`
- `notespaneltitle`
- `notetoolonglabel`
- `origin`
- `pauseonfocusloss`
- `pipblockedlabel`
- `playbackspeeds`
- `playerwidth`
- `posterurl`
- `preventpictureinpicture`
- `randomfocuspauses`
- `randompausedlabel`
- `randompausemaxseconds`
- `randompauseminseconds`
- `reactionannouncementinterval`
- `reactionerrorlabel`
- `reactionreadydebouncems`
- `reactionsenabled`
- `reactionsreadylabel`
- `reactionunavailablelabel`
- `removebookmarklabel`
- `removelabel`
- `removenotelabel`
- `replayend`
- `replaylabel`
- `replaystart`
- `requiredpercent`
- `resumelabel`
- `resumeposition`
- `rewindlabel`
- `rewindstep`
- `sdkerrorlabel`
- `secondslabel`
- `seekforwardblockedlabel` — Messaggio runtime localizzato mostrato dopo un tentativo di seek avanti bloccato.
- `seekforwardblockedspeedlabel` — Suffisso runtime localizzato che riporta la velocità effettiva di recupero.
- `seekforwarddisabledlabel` — Avviso policy persistente localizzato quando il seek avanti è disabilitato.
- `seekforwarddisabledspeedlabel` — Suffisso policy localizzato che riporta la velocità di recupero configurata per il seek bloccato.
- `showchapters`
- `showcontrols`
- `showfullscreen`
- `showtranscript`
- `startmuted`
- `statusdefaultlabel`
- `statuserrorlabel`
- `statuserrortimeoutms`
- `statusinfotimeoutms`
- `studentnotesenabled`
- `studentnoteslimitedlabel`
- `timedtextseekblockedlabel`
- `timedtextseekfailedlabel`
- `trackingenabled`
- `transcriptdefaultlanguage`
- `transcriptlanguagelabel`
- `transcriptloadinglabel`
- `transcriptresultslabel`
- `transcriptsearchlabel`
- `transcriptsearchplaceholder`
- `transcripttracks`
- `transcriptunavailablelabel`
- `videoid`
- `videosource`
- `videourl`
- `vimeocspwarnlabel`
- `vtturl`

## File area

- video caricato; poster; sottotitoli VTT; tracce trascrizione ricercabili; capitoli VTT; icone reazioni caricate.
