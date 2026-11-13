# Provider multimediali e durata

## 1.7.256: base delle connessioni ai provider

Questa candidata aggiunge il riferimento al servizio OAuth Microsoft nelle impostazioni sito (predefinito: Nessuno), un servizio di verifica locale in sola lettura e provider_status.php. Le credenziali restano gestite dal core. I controlli verificano tipo provider, abilitazione/uso per servizi, configurazione client, HTTPS degli endpoint/sito e stato locale dell’account di sistema/scopi core. Non scambiano o rinnovano token e non contattano Microsoft. OneDrive/SharePoint non è ancora una sorgente video selezionabile.

Il manutentore dà ora priorità alle sorgenti autenticate: OneDrive/SharePoint, Zoom Cloud Recording e poi Google Drive; l’App seguirà. La prova App → browser/HTTPS è esplicitamente saltata. L’estensione completa dei sottotitoli multilingua è rinviata perché non è circoscrivibile con sufficiente affidabilità a una sola release tra HTML5 e overlay dei provider. Trascrizioni e sottotitoli esistenti restano disponibili. Il manuale di accessibilità Moodle 5.3 resta in standby.

VideoTrack supporta media caricati/HTML5, YouTube e Vimeo tramite adapter distinti. Il core browser coordina il
comportamento condiviso; il timing specifico degli SDK resta dentro ogni adapter.

## Selezione della sorgente

| Sorgente | Identificatori salvati | Dipendenza runtime | Proposta durata |
|---|---|---|---|
| Upload | File area Moodle e identificatore normalizzato | HTMLMediaElement browser | `loadedmetadata` del file locale selezionato. |
| YouTube | URL e video id estratto | YouTube IFrame API | Probe nascosto e muto interroga `getDuration()` fino a metadata o timeout. |
| Vimeo | URL e id numerico estratto | Vimeo Player SDK | Promise `getDuration()` entro il timeout condiviso. |

Il detector opera soltanto nel form docente. Non scrive dati di tracking learner, non rende i metadata provider
autorevoli da soli e distrugge player temporanei/timer dopo successo, errore o timeout.

## Confine di storage dei file caricati

Il salvataggio di video caricati e poster risolve il `context_module` dai valori `coursemodule`/`cmid` inviati,
quando disponibili, oppure dall'istanza VideoTrack persistita insieme al corso per le chiamate lifecycle che non
contengono quei campi del form. Se il contesto non è risolvibile l'operazione non scrive nulla; i file non vengono
mai salvati in un contesto di sistema o di corso ipotizzato.

La risoluzione dell'URL del media caricato verifica inoltre che l'id del modulo corso appartenga all'istanza
VideoTrack richiesta prima di leggere la file area. Un chiamante non può quindi combinare l'id di un'attività con
un contesto modulo non correlato. La normalizzazione dei campi video accetta il collaboratore Moodle form fornito
dai callback del modulo oppure `null`; ogni altro oggetto non nullo è un errore di programmazione.

## Parsing server di URL e timestamp

`videotrack_parse_https_media_url()` è il confine neutrale rispetto al provider per gli URL multimediali esterni
inviati. Rifiuta valori vuoti, malformati, non HTTPS o con interruzioni di riga e restituisce host minuscolo senza
punto finale, percorso con separatori ripetuti compressi e query sempre stringa. `videotrack_extract_videoid()` e
`videotrack_extract_vimeo_id()` applicano poi allowlist distinte di host e percorsi. I valori della query YouTube
passano anche da `videotrack_normalise_youtube_video_id()`, che accetta soltanto un identificatore provider scalare
di 11 caratteri.

`videotrack_parse_video_timestamp()` accetta secondi numerici oppure due/tre componenti numeriche separate da due
punti. La grammatica limita minuti e secondi a 0–59, consentendo ore o minuti accumulati. Valori vuoti e malformati
restituiscono `null`; i numeri negativi conservano il clamp consolidato a zero. I filtri report richiedono prima il
formato con due punti tramite `videotrack_parse_report_timestamp()`.

La configurazione localizzata è serializzata in un elemento DOM `application/json`. Il bootstrap AMD riceve solo
l'id dell'elemento, analizza il JSON e installa listener per sorgente. Si evitano argomenti `js_call_amd()` troppo
grandi e si mantengono testi/URL codificati in sicurezza.

## Durata autorevole

`durationseconds` salvato è il denominatore della percentuale vista e della completion. Il docente deve verificare
la proposta. L'inserimento manuale è necessario se privacy/consenso del provider, rete, media privato/eliminato o
metadati browser impediscono il rilevamento. Salvare `0` disabilita esplicitamente il calcolo percentuale. Lo stato
live adiacente converte il valore corrente in `HH:MM:SS`; è informativo e non introduce un secondo dato salvato.

## Contratto degli adapter

Ogni adapter espone play/pause, tempo corrente, durata, seek, velocità ed eventi lifecycle necessari al tracker.
Resume e replay cercano la posizione solo dopo la readiness. Il recupero da salto avanti bloccato cattura la
posizione pre-seek e non deve ripetere il seek durante i retry di play.

HTML5 offre il maggior controllo locale. YouTube e Vimeo restano soggetti a SDK, policy iframe e disponibilità del
servizio pubblico. Le promise dei loader si azzerano dopo un errore perché un tentativo successivo possa recuperare.

## Pipeline di configurazione della velocità

`videotrack_get_playback_speeds()` è l'entry point pubblico della policy. Delega quattro fasi esplicite: parsing e
normalizzazione della lista configurata, calcolo del limite positivo più restrittivo tra sito e attività,
applicazione del limite e garanzia finale della velocità normale `1.0`. I valori devono essere finiti, maggiori di
zero e non superiori a `4.0`; i duplicati vengono eliminati e il risultato ordinato. Un'impostazione vuota o il
valore legacy letterale `0` usa `0.75,1,1.25,1.5,2`, mentre una lista priva di valori validi torna a `1.0`. Se il
cambio velocità è disabilitato, la pipeline viene bypassata ed è esposta soltanto `1.0`.

La telemetria usa `videotrack_get_tracking_playback_speeds()`: parte dalla lista visibile al learner e può aggiungere
soltanto la velocità configurata per il recupero da seek bloccato. Il recupero interno resta così valido senza
diventare una scelta ordinaria dell'utente.

## Verifica

- testare parsing URL/id e input non validi/privati;
- testare il fallback del contesto basato sull'istanza e il rifiuto di identificatori attività/modulo incoerenti;
- testare host maiuscoli/con punto finale, interruzioni di riga, query non scalari e componenti timestamp malformate;
- testare metadata durata immediati e ritardati;
- verificare probe muti, limitati e distrutti;
- testare per ogni provider resume, seek indietro, recupero salto bloccato, velocità e pausa terminale;
- confrontare `amd/src` con build/map e fare smoke pubblico quando cambiano le policy esterne.

## Confini dei testi correnti

Le impostazioni sottotitoli richiedono tracce già presenti sul provider; la disponibilità dipende dal provider.
Per i media caricati, la lingua è un metadato dell’unica traccia VTT allegata. I file di trascrizione ricercabile
sono separati. La ripresa server fornisce la posizione salvata oltre 5 secondi quando abilitata; il ritorno
manuale all’inizio rispetta la policy all’indietro. Il recupero della posizione dal browser nei provider resta
un percorso esistente separato. L’accesso ai file segue regole Moodle di corso/modulo e permesso view. Il poster
scompare all’avvio, anche automatico. Queste correzioni testuali non cambiano percorsi runtime.

L’estensione concordata dei sottotitoli VTT multilingua segue lavoro web e decisione App, prima delle
integrazioni autenticate. Ciclo file, scelta lingua e limiti provider/fullscreen devono essere implementati
e provati separatamente; i testi correnti non annunciano tale estensione.
