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
 * Italian language strings for mod_videotrack.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'VideoTrack';
$string['modulename'] = 'VideoTrack';
$string['modulenameplural'] = 'VideoTrack';
$string['pluginadministration'] = 'Amministrazione VideoTrack';
$string['videotrack:addinstance'] = 'Aggiungere una nuova attività VideoTrack';
$string['videotrack:view'] = 'Visualizzare VideoTrack';
$string['videotrack:viewreport'] = 'Visualizzare i report di VideoTrack';
$string['videotrack:viewownreport'] = 'Visualizzare il proprio report di VideoTrack';
$string['videoname'] = 'Nome attività';
$string['youtubeurl'] = 'URL YouTube';
$string['youtubeurl_help'] = 'Incollare un URL YouTube standard, breve o di incorporamento.';
$string['showcontrols'] = 'Mostra controlli del player';
$string['disablekeyboard'] = 'Disabilita scorciatoie da tastiera';
$string['showfullscreen'] = 'Mostra pulsante schermo intero';
$string['allowseekforward'] = 'Consenti avanzamento';
$string['allowseekbackward'] = 'Consenti riavvolgimento';
$string['allowplaybackratechange'] = 'Consenti variazione della velocità';
$string['countbyvideotime'] = 'Conteggia la copertura sulla timeline del video';
$string['countbyvideotime_help'] = 'Consigliato. Il completamento è basato sui secondi unici coperti nella timeline del video, non sulle revisioni ripetute.';
$string['err:completionpercentrange'] = 'La percentuale di completamento deve essere compresa tra 0 e 100.';
$string['completionpercent'] = 'Percentuale di completamento richiesta';
$string['completiondetail:percent'] = 'Richiede la visualizzazione di almeno il {$a}% del video';
$string['completiondetail:minreactions'] = 'Richiede almeno {$a} reazioni distinte';
$string['completiondetail:allreactiontypes'] = 'Richiede almeno una reazione per ciascun tipo configurato';
$string['reactionsheader'] = 'Reazioni';
$string['reactionsenabled'] = 'Abilita reazioni';
$string['reactionsrequired'] = 'Richiedi reazioni';
$string['minreactions'] = 'Numero minimo di reazioni distinte';
$string['requireallreactiontypes'] = 'Richiedi almeno una reazione per ogni tipo configurato';
$string['completionlogic'] = 'Logica di completamento';
$string['logicand'] = 'Tutte le condizioni abilitate (AND)';
$string['logicor'] = 'Qualsiasi condizione abilitata (OR)';
$string['clusterwindow'] = 'Finestra di clustering (secondi)';
$string['showstudentreport'] = 'Mostra il report allo studente';
$string['showreactionnotice'] = 'Mostra l’avviso sulle reazioni';
$string['reactionnotice'] = 'Avviso sulle reazioni';
$string['reactionlabel'] = 'Etichetta reazione';
$string['reactiondescription'] = 'Descrizione reazione';
$string['reactionicontype'] = 'Tipo icona';
$string['reactioniconvalue'] = 'Valore icona';
$string['reactioniconvalue_help'] = 'Per Emoji, inserisci il carattere emoji. Per Font Awesome, inserisci una classe supportata dal tema Moodle, ad esempio fa fa-smile per temi Font Awesome 5 o fa-regular fa-face-smile per temi Font Awesome 6. La disponibilità delle icone dipende dal tema Moodle attivo e dalla versione di Font Awesome installata. Lascia vuoto questo campo quando usi un file icona caricato.';
$string['reactioniconfile'] = 'File icona reazione';
$string['reactioniconfile_help'] = 'File immagine opzionale usato quando il tipo icona è “File caricato”. I formati accettati dipendono dal supporto Moodle per le immagini web.';
$string['reactionrequired'] = 'Richiesta per il completamento';
$string['icontype:emoji'] = 'Emoji';
$string['icontype:fa'] = 'Classe Font Awesome';
$string['icontype:file'] = 'File caricato';
$string['addreaction'] = 'Aggiungi reazione';
$string['invalidyoutubeurl'] = 'URL YouTube non valido.';
$string['err:minreactionsrequired'] = 'Impostare un numero minimo di reazioni distinte oppure abilitare la regola che richiede tutti i tipi di reazione.';
$string['notice:minreactions'] = 'Sono richieste almeno {$a} reazioni distinte.';
$string['notice:requiredtypes'] = 'Tipi di reazione richiesti: {$a}.';
$string['watch'] = 'Guarda';
$string['reportstudent'] = 'Le mie reazioni';
$string['reportteacher'] = 'Report docente';
$string['report:cumulative'] = 'Cumulativo';
$string['report:perstudent'] = 'Per studente';
$string['report:userid'] = 'Utente';
$string['report:uniquecoveredseconds'] = 'Secondi unici coperti';
$string['report:completionpercent'] = 'Completamento %';
$string['report:lastposition'] = 'Ultima posizione';
$string['report:iscompleted'] = 'Completato';
$string['report:noattempts'] = 'Nessun dato di visualizzazione trovato.';
$string['report:noreactions'] = 'Nessun dato di reazione trovato.';
$string['report:timestamp'] = 'Timestamp';
$string['report:reaction'] = 'Reazione';
$string['report:description'] = 'Descrizione';
$string['report:clicks'] = 'Clic';
$string['report:students'] = 'Studenti';
$string['report:replay'] = 'Rivedi frammento';
$string['report:delete'] = 'Elimina';
$string['report:sort'] = 'Ordina per';
$string['report:sorttime'] = 'Timestamp';
$string['report:sortreaction'] = 'Reazione';
$string['report:sortclicks'] = 'Numero di clic';
$string['report:aggregation'] = 'Aggregazione';
$string['report:aggregationtype'] = 'Stessa reazione nella finestra';
$string['report:aggregationpeak'] = 'Picco di qualsiasi reazione';
$string['report:exportcsv'] = 'Esporta CSV';
$string['progress'] = 'Avanzamento';
$string['uniquereactions'] = 'Reazioni distinte';
$string['removereaction'] = 'Rimuovi reazione';
$string['playerunavailable'] = 'Impossibile inizializzare il player.';
$string['yes'] = 'Sì';
$string['no'] = 'No';
$string['modulename_link'] = 'mod/videotrack/view';

$string['setting:heartbeatinterval'] = 'Intervallo heartbeat (secondi)';
$string['setting:heartbeatinterval_desc'] = 'Con quale frequenza il player salva sul server il segmento di visione in corso durante la riproduzione continua. Valori più bassi riducono il rischio di perdita di dati in caso di crash del browser o caduta della connessione, ma aumentano il carico sul server (una richiesta AJAX + due query al database per studente per intervallo). Intervallo consigliato: 15–120 secondi. Valore minimo applicato: 5 secondi (valori inferiori a 5 vengono automaticamente portati a 5 dal server).';
$string['setting:reportclusterlimit'] = 'Limite cluster report cumulativo';
$string['setting:reportclusterlimit_desc'] = 'Numero massimo di cluster di reazioni visualizzati nel report cumulativo prima di chiedere ai docenti di restringere i filtri. Valori più alti consentono analisi più estese su dataset grandi, ma usano più memoria durante visualizzazione ed esportazione.';
$string['setting:reportnotespagesize'] = 'Dimensione pagina note studente';
$string['setting:reportnotespagesize_desc'] = 'Numero di note personali mostrate per pagina nel report delle note studente. Valori piu bassi riducono l\'uso di memoria nei corsi numerosi; valori piu alti riducono la paginazione. Predefinito: 100.';
$string['setting:reactionannouncementinterval'] = 'Intervallo annunci accessibili delle reazioni (millisecondi)';
$string['setting:reactionannouncementinterval_desc'] = 'Intervallo minimo, in millisecondi, tra annunci ripetuti “reazioni non disponibili” per screen reader. Usa un valore più basso per feedback frequenti in video brevi o più alto per ridurre gli annunci ripetuti. Imposta 0 per disabilitare gli annunci ripetuti. Intervallo consigliato quando attivo: 10000–60000 millisecondi. Esempi: 10000 = 10 secondi, 30000 = 30 secondi, 60000 = 1 minuto.';
$string['setting:reactionreadydebouncems'] = 'Debounce per reazioni pronte (millisecondi)';

$string['setting:statusinfotimeoutms'] = 'Status message timeout (milliseconds)';
$string['setting:statusinfotimeoutms_desc'] = 'How long informational player status messages remain visible before auto-dismissal. Recommended range: 4000–20000 milliseconds.';
$string['setting:statuserrortimeoutms'] = 'Error status timeout (milliseconds)';
$string['setting:statuserrortimeoutms_desc'] = 'How long player error messages remain visible before auto-dismissal. Use a longer timeout to improve accessibility and recovery time. Recommended range: 6000–30000 milliseconds.';
$string['setting:reactionreadydebouncems_desc'] = 'Ritardo minimo, in millisecondi, prima di ripetere l’annuncio “reazioni disponibili” dopo una pausa e ripresa rapide. Imposta 0 per disabilitare questo debounce.';
$string['setting:heading_performance'] = 'Prestazioni';
$string['setting:heading_accessibility'] = 'Accessibilità';
$string['setting:heading_accessibility_desc'] = 'Impostazioni per annunci delle tecnologie assistive e feedback per tastiera/screen reader.';
$string['setting:heading_defaults'] = 'Valori predefiniti per le nuove attività';
$string['setting:heading_defaults_desc'] = 'Questi valori vengono usati come predefiniti quando un docente crea una nuova attività VideoTrack. Ogni attività può comunque essere configurata individualmente.';
$string['setting:default_desc'] = 'Valore predefinito per le nuove attività. Può essere sovrascritto dal docente per ogni singola attività.';
$string['setting:default_completionpercent_desc'] = 'Percentuale minima predefinita di video che lo studente deve guardare per completare l’attività. Impostare a 0 per lasciare la regola di completamento disabilitata per impostazione predefinita. Può essere sovrascritta dal docente per ogni singola attività.';
$string['event:segment_saved'] = 'Segmento di visione salvato';
$string['event:reaction_saved'] = 'Reazione inviata';
$string['event:note_saved'] = 'Nota studente salvata';
$string['event:note_deleted'] = 'Nota personale eliminata';
$string['event:reaction_deleted'] = 'Reazione eliminata';

$string['reactionx'] = 'Reazione {$a}';

$string['err:reactioniconfilerequired'] = 'Caricare un file icona quando il tipo icona è impostato su File caricato.';


$string['privacy:metadata:common:timecreated'] = 'Ora di creazione del record.';
$string['privacy:metadata:common:timemodified'] = 'Ora dell’ultima modifica del record.';

$string['privacy:metadata:common:videotrackid'] = 'Identificatore interno dell\'attivita VideoTrack associata al record.';
$string['privacy:metadata:common:courseid'] = 'Identificatore del corso associato all\'attivita.';
$string['privacy:metadata:common:cmid'] = 'Identificatore del modulo del corso associato all\'attivita.';
$string['privacy:metadata:common:videoid'] = 'Identificatore del video o del contenuto configurato per l\'attivita.';
$string['privacy:metadata:videotrack_reactev:reactionid'] = 'Identificatore interno della definizione di reazione usata quando l\'evento e stato registrato.';
$string['privacy:metadata:external:ipaddress'] = 'Il provider esterno puo ricevere l\'indirizzo IP dell\'utente durante le normali richieste del browser.';
$string['privacy:metadata:external:cookies'] = 'Il provider esterno puo impostare o leggere cookie secondo la propria informativa privacy e le impostazioni del browser.';
$string['privacy:metadata:external:useragent'] = 'Il provider esterno puo ricevere informazioni sul browser e sul dispositivo, come l\'intestazione user-agent.';
$string['privacy:metadata:videotrack_seg'] = 'Memorizza i segmenti di visualizzazione registrati per un utente in una attività video.';
$string['privacy:metadata:videotrack_seg:userid'] = 'L’utente a cui appartiene il segmento di visualizzazione registrato.';
$string['privacy:metadata:videotrack_seg:sessionid'] = 'Identificativo della sessione browser associata al segmento di visualizzazione.';
$string['privacy:metadata:videotrack_seg:wallclockstart'] = 'Ora server di inizio del segmento.';
$string['privacy:metadata:videotrack_seg:wallclockend'] = 'Ora server di fine del segmento.';
$string['privacy:metadata:videotrack_seg:videotimestart'] = 'Posizione nella timeline del video all’inizio del segmento.';
$string['privacy:metadata:videotrack_seg:videotimeend'] = 'Posizione nella timeline del video alla fine del segmento.';
$string['privacy:metadata:videotrack_seg:playbackrate'] = 'Velocità di riproduzione usata durante il segmento.';
$string['privacy:metadata:videotrack_seg:endreason'] = 'Motivo per cui il segmento è terminato.';
$string['privacy:metadata:videotrack_state'] = 'Memorizza lo stato aggregato di visualizzazione di un utente in una attività video.';
$string['privacy:metadata:videotrack_state:userid'] = 'L’utente per cui è stato salvato lo stato aggregato.';
$string['privacy:metadata:videotrack_state:lastposition'] = 'Ultima posizione nota raggiunta dall’utente nella timeline del video.';
$string['privacy:metadata:videotrack_state:durationseconds'] = 'Durata del video tracciato in secondi.';
$string['privacy:metadata:videotrack_state:uniquecoveredseconds'] = 'Numero di secondi unici della timeline coperti dall’utente.';
$string['privacy:metadata:videotrack_state:completionpercent'] = 'Percentuale di completamento calcolata per l’utente.';
$string['privacy:metadata:videotrack_state:intervaljson'] = 'Intervalli uniti usati per calcolare la copertura unica.';
$string['privacy:metadata:videotrack_state:iscompleted'] = 'Indica se l’attività è attualmente marcata come completata per l’utente.';
$string['privacy:metadata:videotrack_reactev'] = 'Memorizza gli eventi di reazione registrati mentre l’utente guarda il video.';
$string['privacy:metadata:videotrack_reactev:userid'] = 'L’utente che ha inviato la reazione.';
$string['privacy:metadata:videotrack_reactev:sessionid'] = 'Identificativo della sessione browser associata all’evento reazione.';
$string['privacy:metadata:videotrack_reactev:reactionkey'] = 'Chiave interna della reazione al momento della registrazione.';
$string['privacy:metadata:videotrack_reactev:reactionlabel'] = 'Etichetta della reazione mostrata all’utente al momento della registrazione.';
$string['privacy:metadata:videotrack_reactev:reactiondesc'] = 'Descrizione della reazione mostrata all’utente al momento della registrazione.';
$string['privacy:metadata:videotrack_reactev:videotime'] = 'Posizione nella timeline del video quando la reazione è stata registrata.';
$string['privacy:metadata:videotrack_reactev:playbackrate'] = 'Velocità di riproduzione quando la reazione è stata registrata.';
$string['privacy:metadata:videotrack_reactev:isdeleted'] = 'Indica se l’evento reazione è stato eliminato dall’utente.';

$string['videotrack:viewcoursereport'] = 'Visualizzare il report VideoTrack a livello corso';
$string['videotrack:viewcoursereport_desc'] = 'Consente di visualizzare il report aggregato VideoTrack per l’intero corso.';
$string['videotrack:overrideplayersettings'] = 'Ignora le impostazioni player della piattaforma';
$string['videotrack:overrideplayersettings_desc'] = 'Consente al docente di modificare le impostazioni del player (seek, velocità, controlli, tastiera, schermo intero) impostate dall’amministratore come default di piattaforma. Revocando questa capability si applica una policy player uniforme su tutto il sito.';
$string['videotrack:overridecompletionsettings'] = 'Ignora le impostazioni di completamento della piattaforma';
$string['videotrack:managereactions'] = 'Gestisci le definizioni delle reazioni VideoTrack';
$string['videotrack:managereactions_desc'] = 'Consente al docente di aggiungere, modificare ed eliminare le definizioni delle reazioni durante la creazione o modifica di un\'attività VideoTrack.';
$string['videotrack:grade'] = 'Valuta la visione VideoTrack degli studenti';
$string['videotrack:grade_desc'] = 'Consente all\'utente di assegnare voti ai progressi di visione degli studenti.';
$string['videotrack:overridecompletionsettings_desc'] = 'Consente al docente di modificare le impostazioni di completamento (percentuale richiesta, finestra cluster) impostate dall’amministratore come default di piattaforma. Revocando questa capability si applicano soglie di completamento uniformi su tutto il sito.';
$string['setting:lockedbyAdmin'] = 'Queste impostazioni sono bloccate dall’amministratore di piattaforma e non possono essere modificate per le singole attività.';
$string['setting:heading_presets'] = 'Preset reazioni';
$string['setting:heading_presets_desc'] = 'Set di reazioni a livello di sito che i docenti possono usare come punto di partenza quando configurano una nuova attività VideoTrack.';
$string['reactionpreset'] = 'Applica un preset di reazioni';
$string['reactionpreset_help'] = 'Seleziona un preset per pre-compilare i campi reazione qui sotto. Puoi modificare liberamente i valori dopo aver applicato il preset. Lascia vuoto per configurare le reazioni manualmente.';
$string['reactionpreset:none'] = '— configura manualmente —';
$string['presets:manage'] = 'Gestisci preset reazioni';
$string['presets:pagetitle'] = 'VideoTrack — Preset reazioni';
$string['presets:intro'] = 'Definisci set di reazioni a livello di sito che i docenti possono usare come punto di partenza nella creazione di un’attività VideoTrack. Le reazioni vengono copiate nell’attività e il docente può modificarle liberamente.';
$string['presets:addpreset'] = 'Aggiungi preset';
$string['presets:backtolist'] = 'Torna alla lista preset';
$string['presets:saved'] = 'Preset salvato.';
$string['presets:deleted'] = 'Preset eliminato.';
$string['presets:notfound'] = 'Preset non trovato.';
$string['presets:noneyet'] = 'Non è stato ancora configurato nessun preset di reazioni.';
$string['presets:confirmdelete'] = 'Sei sicuro di voler eliminare questo preset?';
$string['confirmfallback'] = 'Impossibile aprire la finestra di conferma. Riprova.';
$string['presets:presetdetails'] = 'Dettagli preset';
$string['presets:name'] = 'Nome preset';
$string['presets:key'] = 'Chiave preset';
$string['presets:key_help'] = 'Identificatore univoco (solo lettere, numeri e underscore). Non può essere modificato dopo la creazione.';
$string['presets:reactions'] = 'Reazioni';
$string['presets:reactions_help'] = 'Lascia il label vuoto per saltare una riga. Le icone da file non sono supportate nei preset.';
$string['presets:col_name'] = 'Nome';
$string['presets:col_key'] = 'Chiave';
$string['presets:col_reactions'] = 'Reazioni';
$string['presets:col_actions'] = 'Azioni';

$string['reset:userdata'] = 'Elimina tutti i dati di visione degli studenti (segmenti, stati, reazioni)';
$string['report:recalculate'] = 'Ricalcola tutti gli stati di completamento';
$string['report:recalculated'] = 'Stati di completamento ricalcolati per {$a} utenti.';
$string['report:heatmap_desc'] = 'Heatmap delle reazioni sul timeline del video (altezza barra = numero di click in quel punto):';
$string['report:heatmap_supplementary'] = 'La heatmap è una visualizzazione supplementare. I dati completi dei cluster sono disponibili nella tabella seguente.';
$string['event:activity_completed'] = 'Attività VideoTrack completata';

$string['reactioniconfile_notice'] = 'L’immagine verrà ridimensionata automaticamente a 64×64 pixel (ritaglio centrato). Per risultati ottimali, carica un’immagine quadrata (proporzione 1:1). Formati accettati: JPG, PNG, GIF, WebP.';
$string['reactions_hint'] = 'Fai clic su un bottone reazione mentre il video è in riproduzione per registrare la tua reazione in quel momento.';

$string['showgradeto'] = 'Mostra voto allo studente';
$string['showgradeto_help'] = 'Se abilitato, lo studente vedrà il proprio voto e lo stato di sufficienza direttamente nella pagina dell’attività.';
$string['report:grade'] = 'Voto';
$string['report:gradesaved'] = 'Voto salvato con successo.';
$string['report:gradepass_hint'] = 'Sufficienza: {$a}';
$string['report:gradenotset'] = 'Non ancora valutato';

$string['videosource'] = 'Sorgente video';
$string['source:youtube'] = 'YouTube';
$string['source:vimeo'] = 'Vimeo';
$string['source:upload'] = 'Carica (MP4 / WebM / MP3)';
$string['vimeourl'] = 'URL Vimeo';
$string['vimeourl_help'] = 'Incolla l’URL del video Vimeo (es. https://vimeo.com/123456789).';
$string['invalidvimeourl'] = 'L’URL non sembra essere un URL Vimeo valido.';
$string['videofile'] = 'File video / audio';
$string['videofile_help'] = 'Carica un file MP4, WebM, MP3, M4V, MOV, AAC o M4A.';
$string['videofile_notice'] = 'Formati accettati: MP4, WebM, MP3, M4V, MOV, AAC, M4A. Il file è archiviato in modo sicuro su questo server Moodle e viene servito solo agli studenti iscritti.';
$string['setting:heading_player'] = 'Comportamento del player';
$string['setting:playbackspeeds'] = 'Velocità di riproduzione disponibili';
$string['setting:playbackspeeds_desc'] = 'Seleziona le velocità di riproduzione disponibili su tutta la piattaforma. I docenti possono restringere questo elenco per le singole attività (se hanno la capability di override). Il valore 1× (normale) è sempre consigliato.';
$string['setting:playbackspeeds_teacher_desc'] = 'Seleziona le velocità di riproduzione disponibili per questa attività. Vengono mostrate solo le velocità abilitate a livello di sito. Lascia tutto selezionato per usare il default di piattaforma.';
$string['setting:speed_normal'] = 'normale';
$string['setting:distractionfree'] = 'Modalità distraction-free';
$string['setting:distractionfree_desc'] = 'Se abilitata, header, footer e navigazione Moodle vengono nascosti quando uno studente visualizza l’attività. Utile per ambienti embedded o kiosk.';
$string['intervalbar_title'] = 'Intervalli visti — i segmenti verdi indicano le porzioni di video già guardate.';
$string['outline:percent'] = '{$a}% guardato';
$string['outline:nodata'] = 'Nessun dato di visione registrato.';
$string['coursereport:title'] = 'VideoTrack — Report di corso';
$string['coursereport:navlink'] = 'Report VideoTrack';
$string['coursereport:intro'] = 'Panoramica di tutte le attività VideoTrack nel corso.';
$string['coursereport:nodata'] = 'Nessuna attività VideoTrack trovata in questo corso.';
$string['coursereport:col_activity'] = 'Attività';
$string['coursereport:col_source'] = 'Sorgente';
$string['coursereport:col_duration'] = 'Durata';
$string['coursereport:col_students_started'] = 'Studenti iniziati';
$string['coursereport:col_avg_percent'] = 'Copertura media';
$string['coursereport:col_completions'] = 'Completamenti';
$string['coursereport:col_reactions'] = 'Reazioni';
$string['coursereport:col_actions'] = 'Azioni';

$string['grade:pass'] = 'Sufficiente';
$string['grade:fail'] = 'Insufficiente';

$string['autoplay'] = 'Riproduzione automatica';
$string['autoplay_help'] = 'Avvia il video automaticamente al caricamento della pagina. I browser richiedono che il video sia in muto per il funzionamento dell’autoplay. Abilitare l’autoplay attiverà automaticamente "Avvia in muto".';
$string['loop'] = 'Ripeti in loop';
$string['startmuted'] = 'Avvia in muto';
$string['startmuted_help'] = 'Avvia la riproduzione con l’audio disattivato. Gli studenti possono riattivare il suono manualmente. Richiesto dalla maggior parte dei browser quando è attivo l’Autoplay.';
$string['allowdownload'] = 'Permetti download (solo sorgente upload)';
$string['setting:allowdownload_desc'] = 'Mostra un pulsante download nel player HTML5 e permette il download tramite clic destro dei file video/audio caricati.';
$string['setting:heading_playerbehavior'] = 'Comportamento predefinito del player';
$string['setting:heading_playerbehavior_desc'] = 'Valori predefiniti per autoplay, loop, muto e download per le nuove attività. I docenti possono sovrascrivere se hanno la capability di override.';
$string['setting:heading_html5controls'] = 'Controlli player HTML5 (sorgente upload)';
$string['setting:heading_html5controls_desc'] = 'Seleziona i controlli disponibili nella barra player HTML5 personalizzata. Si applica solo alle attività con sorgente Upload.';
$string['setting:html5controls'] = 'Controlli disponibili';
$string['setting:html5controls_desc'] = 'Seleziona i controlli da mostrare nel player HTML5.';
$string['setting:html5controls_teacher_desc'] = 'Seleziona i controlli da mostrare nel player. Sono disponibili solo i controlli abilitati a livello di sito.';
$string['ctrl:play'] = 'Play / Pausa';
$string['ctrl:progress'] = 'Barra di avanzamento';
$string['ctrl:current'] = 'Tempo corrente';
$string['ctrl:duration'] = 'Durata';
$string['ctrl:mute'] = 'Pulsante muto';
$string['ctrl:volume'] = 'Cursore volume';
$string['ctrl:speed'] = 'Velocità di riproduzione';
$string['ctrl:pip'] = 'Picture-in-Picture';
$string['ctrl:fullscreen'] = 'Schermo intero';
$string['ctrl:download'] = 'Pulsante download';

$string['setting:playerwidth'] = 'Larghezza massima player (px)';
$string['setting:playerwidth_desc'] = 'Larghezza massima del player video in pixel (1–4096). I docenti possono sovrascrivere per singola attività (valore istanza 0 = usa il default di sito). Consigliato: 960.';
$string['playerwidth'] = 'Larghezza massima player (px)';
$string['playerwidth_help'] = 'Larghezza massima del player per questa attività in pixel. Lascia 0 per usare il default di piattaforma.';
$string['playerwidth_zero_note'] = 'Inserisci 0 per ereditare il valore predefinito della piattaforma, oppure un valore da 1 a 4096 pixel per questa attività.';
$string['setting:rewindstep'] = 'Passo rewind (secondi)';
$string['setting:rewindstep_desc'] = 'Quanti secondi salta indietro il pulsante rewind come impostazione predefinita. I docenti possono sovrascrivere il valore per singola attività. Imposta 0 per nascondere il pulsante rewind di default; gli override dell’attività possono comunque riabilitarlo. Default: 10. Importante: se "Permetti seek all’indietro" è disabilitato per un’attività, il pulsante rewind non comparirà anche se questo valore è > 0.';
$string['rewindstep'] = 'Passo rewind (secondi)';
$string['rewindstep_help'] = 'Quanti secondi salta indietro il pulsante rewind per questa attività. Lascia 0 per usare il default di piattaforma. Se il default di piattaforma è 0, il pulsante resta nascosto salvo valore specifico impostato in questa attività. Nota: se "Permetti seek all’indietro" è disabilitato per questa attività, il pulsante rewind non comparirà indipendentemente da questo valore — le due impostazioni interagiscono tra loro.';
$string['setting:fastforwardstep'] = 'Passo avanzamento rapido (secondi)';
$string['setting:fastforwardstep_desc'] = 'Quanti secondi salta avanti il pulsante fast-forward come impostazione predefinita. I docenti possono sovrascrivere il valore per singola attività. Imposta 0 per nascondere il pulsante fast-forward di default; gli override dell’attività possono comunque riabilitarlo. Default: 10. Importante: se "Permetti seek in avanti" è disabilitato per un’attività, il pulsante fast-forward non comparirà anche se questo valore è > 0.';
$string['fastforwardstep'] = 'Passo avanzamento rapido (secondi)';
$string['fastforwardstep_help'] = 'Quanti secondi salta avanti il pulsante fast-forward per questa attività. Lascia 0 per usare il default di piattaforma. Se il default di piattaforma è 0, il pulsante resta nascosto salvo valore specifico impostato in questa attività. Nota: se "Permetti seek in avanti" è disabilitato per questa attività, il pulsante fast-forward non comparirà indipendentemente da questo valore — le due impostazioni interagiscono tra loro.';
$string['captionsheader'] = 'Sottotitoli';
$string['captions'] = 'Abilita sottotitoli';
$string['captions_help'] = 'Se abilitato: YouTube — i sottotitoli vengono mostrati di default; Vimeo — viene attivata la traccia nella lingua indicata (deve essere precaricata su Vimeo.com); Upload — viene usato il file VTT allegato.';
$string['setting:default_captions_desc'] = 'Abilita i sottotitoli per default nelle nuove attività. I docenti possono sovrascrivere.';
$string['captionslang'] = 'Lingua sottotitoli predefinita';
$string['captionslang_help'] = 'Codice lingua ISO 639-1 (es. it, en, de). Per YouTube imposta la lingua preferita. Per Vimeo seleziona la traccia precaricata. Per Upload indica la lingua del file VTT.';
$string['setting:captionslang_desc'] = 'Lingua predefinita dei sottotitoli (ISO 639-1, es. it, en). I docenti possono sovrascrivere.';
$string['vttfile'] = 'File sottotitoli (.vtt)';
$string['vttfile_help'] = 'Carica un file WebVTT (.vtt). Verrà servito al browser dello studente e mostrato come sottotitoli nel player.';
$string['vttfile_notice'] = 'Formato accettato: WebVTT (.vtt). È supportato un solo file. Deve corrispondere al codice lingua indicato sopra.';
$string['vimeo_captions_notice'] = 'I sottotitoli Vimeo si gestiscono su Vimeo.com. Carica le tue tracce lì. Il codice lingua indicato sopra verrà usato per attivare automaticamente la traccia corrispondente.';
$string['ctrl:rewind'] = 'Pulsante rewind';
$string['ctrl:fastforward'] = 'Pulsante avanzamento rapido';

$string['playerloading'] = 'Caricamento del player video, attendere…';
$string['noreactionsyet'] = 'Nessuna reazione registrata. Reagisci mentre il video è in riproduzione.';
$string['reaction:error'] = 'Impossibile salvare la reazione. Riprova.';

// ── Feature 1: Resume playback ────────────────────────────────────────────
$string['resumeplayback'] = 'Riprendi riproduzione';
$string['resumeplayback_desc'] = 'Riprende automaticamente il video dal punto in cui lo studente lo ha interrotto nell’ultima sessione.';
$string['resumeplayback_help'] = 'Se abilitato, il video parte dall’ultima posizione salvata (se oltre 5 secondi dall’inizio). Lo studente può comunque tornare manualmente all’inizio.';
$string['setting:resumeplayback'] = 'Riprendi riproduzione (predefinito)';
$string['setting:resumeplayback_desc'] = 'Impostazione predefinita per le nuove attività VideoTrack. I docenti possono sovrascriverla per singola attività.';

// ── Feature 6: Max playback rate ──────────────────────────────────────────
$string['maxplaybackrate'] = 'Velocità massima di riproduzione';
$string['maxplaybackrate_desc'] = 'Limita la velocità massima selezionabile dagli studenti. 0 = nessun limite.';
$string['maxplaybackrate_help'] = 'Se impostato, gli studenti non possono riprodurre il video a velocità superiore, anche quando i controlli del player consentono valori più alti. Serve a scoraggiare una fruizione troppo rapida.';
$string['maxplaybackrate_nolimit'] = 'Nessun limite';
$string['setting:maxplaybackrate'] = 'Velocità massima di riproduzione (predefinita)';
$string['setting:maxplaybackrate_desc'] = 'Velocità massima predefinita per le nuove attività. I docenti possono sovrascriverla per singola attività.';

// ── Feature 8: Transcript interattivo ─────────────────────────────────────
$string['showtranscript'] = 'Mostra trascrizione interattiva';
$string['showtranscript_desc'] = 'Visualizza accanto al video un pannello di trascrizione scorrevole e cliccabile (richiede un file sottotitoli VTT).';
$string['showtranscript_help'] = 'Analizza il file VTT caricato e lo mostra come elenco cliccabile. Ogni voce contiene timestamp e testo; il clic porta il video a quel punto. Il cue attivo viene evidenziato e portato automaticamente in vista.';
$string['transcript_title'] = 'Trascrizione';
$string['transcript_unavailable'] = 'La trascrizione non è disponibile per questo video.';
$string['transcript_loading'] = 'Caricamento trascrizione…';

// ── Feature 3: Autoplay blocked ───────────────────────────────────────────
$string['autoblockedlabel'] = 'Fai clic sul video per avviare la riproduzione.';

// ── Feature 4: Vimeo CSP warning ──────────────────────────────────────────
$string['sdkerrorlabel'] = 'Impossibile caricare il player video. Il problema può dipendere da ad-blocker, Content Security Policy o restrizioni di rete. Disabilita i blocchi dei contenuti o contatta l’amministratore.';
$string['vimeocspwarnlabel'] = 'Impossibile caricare il player Vimeo. Controlla la connessione di rete o chiedi all’amministratore di consentire player.vimeo.com nella Content Security Policy.';

// ── Feature 5: Resume label ───────────────────────────────────────────────
$string['resumelabel'] = 'Ripresa da';
// ── Report: azioni studente ──
$string['report:actions'] = 'Azioni';
$string['report:resetstudent'] = 'Azzera progresso';
$string['report:resetstudent_confirm'] = 'Sei sicuro di voler azzerare il progresso di questo studente? Verrà eliminata tutta la cronologia di visualizzazione e le reazioni. L’operazione non è reversibile.';
$string['report:studentreset'] = 'Il progresso dello studente è stato azzerato.';
// ── Feature 10/11/12 strings ──
$string['showchapters'] = 'Mostra navigazione capitoli';
$string['showchapters_desc'] = 'Visualizza una barra di navigazione con i marcatori di capitolo estratti dal file VTT. I capitoli sono cue VTT con testo inferiore a 80 caratteri.';
$string['showchapters_help'] = 'Se il file VTT caricato contiene cue brevi (meno di 80 caratteri), questi vengono interpretati come titoli di capitolo e mostrati come barra di navigazione cliccabile sopra i controlli video. Il clic porta il video a quel punto.';
$string['chapters_label'] = 'Capitoli video';
$string['chapters_unavailable'] = 'I capitoli non sono disponibili per questo video.';
$string['chapter_label'] = 'Capitolo';
$string['studentnotesenabled'] = 'Abilita note studente';
$string['studentnotesenabled_desc'] = 'Consente agli studenti di scrivere note personali con timestamp durante la visione del video.';
$string['studentnotesenabled_help'] = 'Se abilitato, accanto al video appare un’area di testo. Gli studenti possono scrivere una nota e salvarla al timestamp corrente del video. Le note sono visibili solo allo studente che le ha scritte e ai gestori tramite report. Le note possono essere eliminate dallo studente.';
$string['setting:studentnotesenabled'] = 'Abilita note studente (predefinito)';
$string['setting:studentnotesenabled_desc'] = 'Impostazione predefinita per le nuove attività VideoTrack. I docenti possono sovrascriverla per singola attività.';
$string['setting:notemaxlength'] = 'Lunghezza massima delle note';
$string['setting:notemaxlength_desc'] = 'Numero massimo di caratteri consentiti per ogni nota personale dello studente. Predefinito: 2000.';
$string['studentnotes_title'] = 'Le mie note';
$string['studentnote_placeholder'] = 'Scrivi una nota in questo punto del video…';
$string['studentnote_save'] = 'Salva nota';
$string['studentnote_hint'] = 'La nota verrà salvata al timestamp corrente del video. Il video deve essere in riproduzione.';
$string['studentnotes_list_label'] = 'Note salvate';
$string['studentnote_label'] = 'Nota studente';
$string['noteerrorlabel'] = 'Impossibile salvare la nota. Riprova.';
$string['notesavedlabel'] = 'Nota salvata.';
$string['notedeletedlabel'] = 'Nota rimossa.';
$string['noteemptylabel'] = 'La nota è vuota. Scrivi una nota prima di salvarla.';
$string['notetoolonglabel'] = 'La nota supera la lunghezza massima consentita dal sito.';
$string['studentnoteslimitedlabel'] = 'Sono visualizzate solo le ultime {$a} note.';
$string['noteplaybackrequiredlabel'] = 'Avvia la riproduzione prima di salvare una nota.';
$string['charsremaininglabel'] = 'caratteri rimanenti';
$string['posterimage'] = 'Immagine poster / anteprima';
$string['posterimage_help'] = 'Carica un’immagine da mostrare come anteprima prima dell’avvio del video. L’immagine resta visibile finché lo studente non fa clic su play. Formati accettati: JPG, PNG, WebP, GIF. Dimensione consigliata: 1280×720 px (16:9).';
$string['posterimage_notice'] = 'L’immagine poster viene mostrata prima della riproduzione e nascosta automaticamente quando il video parte.';
$string['playbutton_label'] = 'Riproduci video';
$string['setting:maxplaybackrate_nolimit'] = 'Nessun limite';
// ── Privacy: campi nuovi notetext/notetype ───────────────────────────────
$string['privacy:metadata:videotrack_reactev:notetext'] = 'Testo di una nota personale scritta dallo studente a uno specifico timestamp del video.';
$string['privacy:metadata:videotrack_reactev:notetype'] = 'Tipo di evento: vuoto per reazioni standard, "note" per note personali dello studente.';

// ── Errore note disabilitate ──────────────────────────────────────────────
$string['reactionsdisabled'] = 'Le reazioni sono disabilitate per questa attività VideoTrack. Chiedi al docente o all’amministratore del corso di abilitarle se sono richieste.';
$string['studentnotesdisabled'] = 'Le note studente non sono abilitate per questa attività.';
// ── C3: no file uploaded ──
$string['nofilelabel'] = 'Nessun file video è stato caricato per questa attività.';
$string['removenote'] = 'Rimuovi nota';
// ── Note toggle + report note ──
$string['notes_hide'] = 'Nascondi note';
$string['notes_show'] = 'Mostra note';
$string['report:notes_title'] = 'Note degli studenti';
$string['report:nonotes'] = 'Nessuna nota è stata scritta per questa attività.';
$string['report:notedate'] = 'Scritta il';
$string['report:exportnotes_csv'] = 'Esporta note come CSV';
// ── Localisation: skip buttons, dismiss, note remove ──
$string['dismisslabel'] = 'Chiudi';
$string['status:default'] = 'Aggiornamento di stato.';
$string['status:error'] = 'Si è verificato un errore. Riprova.';
$string['rewindlabel'] = 'Indietro';
$string['fastforwardlabel'] = 'Avanti veloce';
$string['secondslabel'] = 'secondi';
$string['removenotelabel'] = 'Rimuovi nota';
// ── Help strings ──
$string['gradepass_help'] = 'Il voto minimo richiesto per superare questa attività. Gli studenti che raggiungono questo voto o superiore sono considerati idonei.';


$string['completiondetail:requiredreactions'] = 'Deve includere queste reazioni richieste: {$a}';

$string['error:playbackrequired'] = 'Il video deve essere in riproduzione prima di poter salvare questa azione.';
// ── GD warning strings ──
$string['setting:gd_missing_title'] = 'Estensione PHP GD non disponibile.';
$string['setting:gd_missing_desc'] = 'Le immagini caricate dai docenti come icone reazione NON verranno ridimensionate automaticamente a 64×64 pixel. Il file originale verrà servito così com’è e potrebbe influire sui tempi di caricamento se è grande. Per abilitare il ridimensionamento automatico chiedere all’amministratore del server di installare il pacchetto php-gd.';

$string['report:heatmap_legend'] = 'Legenda colori della heatmap reazioni';

$string['report:clusterlimitreached'] = 'Il report ha raggiunto il numero massimo di cluster visualizzati. Usa i filtri o una finestra temporale più ristretta per un’analisi completa.';

$string['report:showingrecentreactionsoftotal'] = 'Sono mostrate {$a->shown} reazioni su {$a->total} totali, dalla più vecchia alla più recente.';

$string['report:viewfullreport'] = 'Visualizza il report completo';
$string['studentnotes_view_limited'] = 'Sono visualizzate le ultime {$a} note. Apri il report completo per rivederle tutte.';
$string['report:skiptoheatmaptable'] = 'Salta la heatmap e vai alla tabella dati';
$string['report:heatmap_textsummary'] = 'Il grafico contiene {$a->clusters} cluster; il cluster più grande contiene {$a->max} clic.';
$string['err:reactioniconvaluerequired'] = 'Inserire un emoji o una classe Font Awesome.';
$string['err:reactioniconvalueinvalidfa'] = 'Inserisci solo nomi di classi Font Awesome validi, usando lettere, numeri, spazi e trattini.';

$string['error:reactionratelimit'] = 'Sono state inviate troppe reazioni in poco tempo. Continua la visione del video e riprova.';
$string['event:student_progress_reset'] = 'Dati VideoTrack dello studente reimpostati';
$string['report:timefrom'] = 'Dal secondo';
$string['report:timeto'] = 'Al secondo';
$string['report:clusterlimitreached_help'] = 'Il report cumulativo ha raggiunto il limite di cluster visualizzabili. Usa i filtri per utente, reazione o tempo del video per restringere l’analisi e recuperare i cluster successivi.';
$string['report:topclusterssummary'] = 'Cluster più rilevanti nella selezione:';
$string['report:topclusteritem'] = '{$a->time}: {$a->reaction}, {$a->clicks} clic';
$string['error:notesratelimit'] = 'Sono state inviate troppe note in poco tempo. Attendere prima di aggiungerne un’altra.';

$string['privacy:segmentschunk'] = 'Segmenti di visione - parte {$a}';

$string['privacy:reactionsactivechunk'] = 'Reazioni attive - parte {$a}';

$string['privacy:reactionsdeletedchunk'] = 'Reazioni eliminate - parte {$a}';

$string['privacy:notesactivechunk'] = 'Note attive - parte {$a}';

$string['privacy:notesdeletedchunk'] = 'Note eliminate - parte {$a}';

$string['report:clusterlimitreached_csv'] = 'ATTENZIONE: è stato raggiunto il limite dei cluster. L’esportazione può essere incompleta; applicare filtri per utente, reazione o tempo e ripetere l’export.';

$string['report:notecreatedfrom'] = 'Note dalla data';

$string['report:notecreatedto'] = 'Note alla data';

$string['reactionsavailableonlyduringplayback'] = 'Le reazioni sono disponibili solo durante la riproduzione del video.';
$string['reactionsreadyannounce'] = 'Le reazioni sono ora disponibili.';

$string['privacy:state'] = 'Stato di completamento';

$string['report:clusterlimitrequiresfilters'] = 'Il report cumulativo è parziale. Applica un filtro sull’intervallo temporale del video per recuperare in modo affidabile i cluster rimanenti.';

$string['report:clusterlimitrequiresfilters_csv'] = 'L’esportazione cumulativa è parziale perché non è stato applicato un filtro sull’intervallo temporale del video. Applica i filtri Da secondo/A secondo e ripeti l’export.';
$string['report:clusterexportblocked_csv'] = 'L’esportazione è stata interrotta per evitare dati incompleti. Applica un filtro sull’intervallo del video ed esporta di nuovo.';
$string['report:clusterdisplayblocked'] = 'La tabella dei cluster è stata nascosta per evitare dati incompleti. Applica un filtro sull’intervallo del video per continuare.';
$string['unknownreaction'] = 'Reazione sconosciuta';
$string['externalprovider_notice'] = 'I provider video esterni come YouTube e Vimeo possono trattare dati personali e impostare cookie secondo le rispettive informative privacy. Usa file caricati quando il trasferimento a terzi non è consentito.';
$string['privacy:metadata:youtube'] = 'Quando viene usato un video YouTube, il browser dell’utente si collega a YouTube per caricare e riprodurre il video.';
$string['privacy:metadata:youtube:videoid'] = 'Identificativo del video YouTube configurato per questa attività.';
$string['privacy:metadata:youtube:url'] = 'URL YouTube configurato per questa attività.';
$string['privacy:metadata:vimeo'] = 'Quando viene usato un video Vimeo, il browser dell’utente si collega a Vimeo per caricare e riprodurre il video.';
$string['privacy:metadata:vimeo:videoid'] = 'Identificativo del video Vimeo configurato per questa attività.';
$string['privacy:metadata:vimeo:url'] = 'URL Vimeo configurato per questa attività.';

$string['html5:controls'] = 'Controlli video';
$string['html5:play'] = 'Riproduci';
$string['html5:pause'] = 'Pausa';
$string['html5:seek'] = 'Cerca';
$string['html5:volume'] = 'Volume';
$string['html5:mute'] = 'Disattiva audio';
$string['html5:unmute'] = 'Attiva audio';
$string['html5:speed'] = 'Velocità';
$string['html5:pip'] = 'Picture-in-picture';
$string['html5:fullscreen'] = 'Schermo intero';
$string['html5:download'] = 'Scarica';

// GDPR retention and academic-integrity.
$string['setting:heading_privacy'] = 'Privacy e conservazione dati';
$string['setting:heading_privacy_desc'] = 'Configura come VideoTrack conserva dati di tracciamento, note e reazioni.';
$string['setting:retentionperioddays'] = 'Periodo di conservazione dei dati di tracciamento (giorni)';
$string['setting:retentionperioddays_desc'] = 'Numero di giorni dopo i quali VideoTrack anonimizza i dati vecchi di tracciamento, note e reazioni (incluse le etichette testuali libere) per la retention automatica. Imposta 0 per conservare i dati senza scadenza. Le richieste di oblio gestite dalla Privacy API di Moodle eliminano definitivamente i record di tracciamento, stato, reazioni e note dell\'utente nel contesto selezionato.';
$string['setting:retentionprivacynotice'] = 'I dati di tracciamento, le note e le reazioni sono dati personali. Verifica una base giuridica valida, aggiorna l\'informativa privacy del sito ed evita la conservazione illimitata salvo giustificazione.';
$string['task:cleanup'] = 'Anonimizza i dati VideoTrack scaduti';
$string['privacy:anonymised'] = '[anonimizzato]';
$string['error:playbackpositionnotwatched'] = 'Questa posizione del video non risulta ancora visualizzata, quindi l’azione non può essere salvata.';

$string['setting:strictsessionvalidation'] = 'Richiedi la stessa sessione browser per validare note e reazioni';
$string['setting:validationfallbackdays'] = 'Finestra di validazione dello storico di riproduzione (giorni)';
$string['setting:validationfallbackdays_privacywarning'] = 'Valori vicini al massimo dovrebbero essere usati solo se il sito dispone di una giustificazione documentata per privacy e integrità accademica.';
$string['setting:validationfallbackdays_desc'] = 'Età massima, in giorni, dei segmenti già guardati che possono autorizzare note e reazioni dopo un refresh o un cambio browser. Imposta 0 per consentire segmenti storici guardati senza limite; migliora l’usabilità ma rende la validazione di integrità accademica più permissiva. I controlli sulla stessa sessione e sulla riproduzione recente vengono sempre tentati per primi.';
$string['setting:strictsessionvalidation_desc'] = 'Se abilitato, note e reazioni possono essere salvate solo per timestamp visualizzati nella sessione browser corrente. Se disabilitato, VideoTrack accetta timestamp già visualizzati dallo stesso utente nella stessa attività, migliorando l’usabilità dopo aggiornamenti pagina o cambi browser e continuando a rifiutare posizioni non visualizzate.';

$string['setting:intrangerequired'] = 'Inserisci un numero intero compreso tra {$a->min} e {$a->max}.';
$string['err:playerwidthrequired'] = 'Inserisci 0 per usare il default di piattaforma oppure un numero intero da 1 a 4096 pixel.';
$string['err:playbacksteprequired'] = 'Inserisci un numero intero da 0 a 300 secondi. Usa 0 per il default di piattaforma.';
$string['setting:nonnegativeintrequired'] = 'Inserisci un numero intero maggiore o uguale a 0.';

$string['report:anonymiseduser'] = 'Utente anonimizzato';

$string['report:exportnotes_privacywarning'] = 'Questa esportazione può contenere dati personali presenti nelle note degli studenti. Scaricala e conservala solo se hai una finalità valida ed eliminala quando non è più necessaria.';

$string['privacy:videoid_export_note'] = 'Identificativo video/contenuto: {$a}';
$string['privacy:anonymisedreaction'] = 'Reazione anonimizzata';

// 1.3.87 accessibility and privacy confirmation strings.
$string['invalidvideosource'] = 'Sorgente video non valida.';
$string['report:gradeinputfor'] = 'Voto per {$a}';
$string['report:savegradefor'] = 'Salva voto per {$a}';
$string['report:gradepassed'] = 'Superato';
$string['report:gradefailed'] = 'Non superato';
$string['report:exportnotes_confirm'] = 'Confermo che questa esportazione delle note può contenere dati personali e che ho una finalità valida per scaricarla.';
$string['report:exportnotes_confirmrequired'] = 'Conferma l’avviso sull’esportazione di dati personali prima di scaricare le note.';
$string['coursereport:avgcoverage'] = 'Copertura media: {$a}%';

$string['report:exportnotes_csv_personaldata'] = 'Esporta le note come CSV, inclusi possibili dati personali';

$string['presets:deletearia'] = 'Elimina preset {$a}';
$string['presets:reactionlabelaria'] = 'Reazione {$a}: etichetta';
$string['presets:reactiondescriptionaria'] = 'Reazione {$a}: descrizione';
$string['presets:reactionicontypearia'] = 'Reazione {$a}: tipo di icona';
$string['presets:reactioniconvaluearia'] = 'Reazione {$a}: valore icona';
$string['presets:reactionrequiredaria'] = 'Reazione {$a}: richiesta per il completamento';
$string['err:reactionpresetjson'] = 'I dati del preset reazioni non sono validi. Ricarica la pagina e riprova.';
$string['presets:reactionstablecaption'] = 'Righe del preset di reazioni';
$string['privacy:intervals_none'] = 'Nessun intervallo di visione registrato.';
$string['privacy:intervals_unavailable'] = 'Intervalli di visione non disponibili o non validi.';

$string['warning:suspicioussegment'] = 'Il segmento di visione non è stato registrato perché supera la finestra di riproduzione prevista. Continua la visione normalmente e riprova.';

$string['event:notes_exported'] = 'Note personali esportate';

$string['externalproviderprivacy_notice'] = 'Questa attività carica il video da {$a}. Il browser può inviare dati tecnici, come indirizzo IP, user agent e cookie, a questo fornitore secondo l\'informativa privacy del sito.';

$string['setting:retentionunlimitedwarning_title'] = 'La conservazione illimitata di VideoTrack è attiva.';

$string['setting:retentionunlimitedwarning_desc'] = 'Il valore 0 conserva dati di tracciamento, note e reazioni senza scadenza. Verifica che sia giustificato dalla policy GDPR/privacy del sito, oppure imposta un periodo finito, ad esempio 730 giorni.';
$string['setting:retentionunlimitedconfirm'] = 'Comprendo le implicazioni della conservazione illimitata di VideoTrack';
$string['setting:retentionunlimitedconfirm_desc'] = 'Obbligatorio quando il periodo di conservazione è impostato a 0. Conferma che la conservazione illimitata è stata valutata ed è giustificata dalla policy GDPR/privacy del sito.';
$string['setting:retentionunlimitedconfirm_required'] = 'Devi confermare le implicazioni della conservazione illimitata di VideoTrack prima di salvare un periodo di conservazione pari a 0.';

$string['warning:notetruncated'] = 'La nota è stata salvata, ma è stata ridotta alla lunghezza massima consentita dal sito.';

$string['error:securetokenunavailable'] = 'Non è disponibile un generatore sicuro di token casuali. VideoTrack non può creare in sicurezza le chiavi di anonimizzazione.';

$string['hiddeninstancelabel'] = 'Nascosto agli studenti: {$a}';

$string['setting:nonnegativeintmax'] = 'Il valore non può essere superiore a {$a}.';

$string['restore_missing_reaction_mapping'] = 'Ripristino mod_videotrack: mapping reazione mancante per il vecchio id reazione {$a}; creazione di una reazione segnaposto nascosta.';
$string['restore_placeholder_reaction'] = 'Reazione ripristinata';
$string['privacy_cleanup_failed'] = 'Pulizia retention GDPR di VideoTrack fallita: {$a}';
$string['privacy_cleanup_unlimited'] = 'Retention GDPR di VideoTrack: retention illimitata configurata; nessun record anonimizzato.';
$string['privacy_cleanup_anonymised'] = 'Retention GDPR di VideoTrack: anonimizzati {$a->segments} segmenti, {$a->states} stati e {$a->events} eventi reazione/nota su {$a->processed} coppie utente/attività.';
$string['privacy_cleanup_remaining'] = 'Restano altri record che saranno elaborati in una successiva esecuzione: {$a}.';

// Developer diagnostics used by AMD modules.
$string['debug:ajaxdeferredoffline'] = 'VideoTrack AJAX request deferred while browser is offline: {$a->method}.';
$string['debug:ajaxretry'] = 'VideoTrack retrying transient AJAX failure for {$a->method}: {$a->message}.';
$string['debug:ajaxswallowed'] = 'VideoTrack handled AJAX failure for {$a->context} ({$a->category}): {$a->message}.';
$string['debug:beaconunsafe'] = 'VideoTrack sendBeacon skipped because the endpoint is not safe.';
$string['debug:beaconpayloadlarge'] = 'VideoTrack sendBeacon skipped because the encoded payload is too large.';
$string['debug:beaconnotaccepted'] = 'VideoTrack sendBeacon was not accepted by the browser.';
$string['debug:beaconfailed'] = 'VideoTrack sendBeacon failed: {$a->message}.';
$string['debug:eventhandlerlimit'] = 'VideoTrack event handler limit reached for {$a->event}.';
$string['debug:asynceventhandlerfailed'] = 'VideoTrack asynchronous event handler failed for {$a->event}: {$a->message}.';
$string['debug:eventhandlerfailed'] = 'VideoTrack event handler failed for {$a->event}: {$a->message}.';
$string['debug:invalidintervaljson'] = 'VideoTrack ignored invalid interval JSON: {$a->message}.';
$string['debug:notedeletionfailed'] = 'VideoTrack note deletion failed: {$a->message}.';
$string['debug:playershellmissing'] = 'VideoTrack player shell not found; delegated handlers were not installed.';
$string['debug:sessionsavefailed'] = 'VideoTrack could not save {$a->context}: {$a->message}.';
$string['debug:sessionreadfailed'] = 'VideoTrack could not read {$a->context}: {$a->message}.';
$string['debug:playrequestfailed'] = 'VideoTrack play request failed: {$a->message}.';
$string['debug:vttloadfailed'] = 'VideoTrack could not load the VTT transcript: {$a->message}.';
$string['debug:chaptersfailed'] = 'VideoTrack chapters processing failed: {$a->message}.';
$string['debug:autoplaycleanupfailed'] = 'VideoTrack autoplay notice cleanup failed: {$a->message}.';
$string['debug:youtubeplayererror'] = 'VideoTrack YouTube player reported an error.';
$string['debug:vimeosdkfailed'] = 'VideoTrack failed to load the Vimeo Player SDK from player.vimeo.com.';
$string['debug:presetsinitialised'] = 'VideoTrack presets JavaScript initialised.';
$string['debug:reportinitialised'] = 'VideoTrack report JavaScript initialised.';
