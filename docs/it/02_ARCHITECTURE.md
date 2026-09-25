# Architettura

## Livelli

1. **Entry point Moodle** — `view.php`, pagine report, form e callback lifecycle eseguono i controlli e preparano stato posseduto dal server.
2. **Servizi di dominio** — le classi in `classes/local/` implementano tracking, Analytics, scope, export privacy-safe, timed text, integrità e presa visione.
3. **Servizi esterni** — `classes/external/` espone nove scritture AJAX tramite `db/services.php`; la validazione comune è in `external\helper`.
4. **Adapter player** — `html5_player.js`, `player.js` (YouTube) e `vimeo_player.js` traducono le callback dei provider nel contratto condiviso.
5. **Core AMD condiviso** — trasporto/retry API, lifecycle tracker, intervalli, reazioni, note, segnalibri, trascrizione, presa visione, focus guard, stato e UI.
6. **Persistenza** — sette tabelle XMLDB, aree Moodle File API e gradebook core.
7. **Report** — report per studente, dashboard corso/docente e Analytics di istanza/tra corsi con data-format export.

## Confine di validazione del form

`mod_form.php` conserva la validazione che dipende da file draft, stato del database o contesto del corso. La policy
pura sui valori inviati appartiene a `classes/local/form_validation.php`. L'aggregatore scalare mantiene l'ordine
degli errori delegando percentuale di completamento, campi interi limitati, dipendenze delle regole di reazione e
JSON opzionale dei preset a validatori privati mirati. L'attivazione del completamento personalizzato combina allo
stesso modo tre predicati espliciti: la percentuale richiede durata e valore positivo; le regole di reazione
richiedono che le reazioni siano abilitate; la presa visione richiede sia la funzione sia il relativo flag di
completamento. I campi Moodle con suffisso usano come fallback i corrispondenti valori senza suffisso.

## Confine di parsing degli input multimediali

La gestione server degli URL YouTube e Vimeo inizia in `locallib.php` con un unico normalizzatore HTTPS neutrale
rispetto al provider. Rifiuta interruzioni di riga, host mancanti e schemi non HTTPS, poi rende canonici host,
percorso e query. Solo dopo questo passaggio gli estrattori applicano le rispettive allowlist esplicite di host e
percorsi. Gli identificatori YouTube attraversano un validatore dedicato per scalarità, lunghezza e alfabeto,
compresi i valori decodificati dalla query. I timestamp video accettano secondi numerici oppure una grammatica
ancorata a due/tre componenti; solo minuti e secondi sono limitati a 0–59. I filtri report restano più restrittivi e
richiedono il formato con due punti prima di usare il parser condiviso.

## Contratto player

Ogni adapter deve fornire tempo corrente, durata, play/pausa, seek, velocità e fine. I moduli condivisi non assumono comportamenti uguali tra provider. Resume, replay e correzione seek programmatici sono distinti dal seek utente. I limiti SDK di YouTube e Vimeo sono trattati esplicitamente.

## Modello dati

- `videotrack`: configurazione attività.
- `videotrack_seg`: segmenti di visione append-only.
- `videotrack_state`: intervalli uniti, progresso e completamento per utente/attività.
- `videotrack_react`: definizioni delle reazioni configurate.
- `videotrack_reactev`: reazioni standard, note personali e segnalibri privati distinti da `notetype`.
- `videotrack_integrity`: segnali diagnostici limitati.
- `videotrack_acknowledge`: conferme versionate e fotografia del progresso.

Il codice ordinario di presentazione e partecipazione legge le definizioni di reazione attive tramite
`videotrack_get_reactions()`. Le operazioni lifecycle che devono preservare i riferimenti degli eventi storici usano
il contratto esplicito `videotrack_get_all_reactions()`, che restituisce anche le definizioni soft-deleted. I due
scope sono API separate invece di modalità booleane della stessa funzione, quindi ogni chiamante dichiara il proprio
intento di conservazione dei dati.

Anche lo storage timed text usa scope espliciti. Le letture standard di `timed_text` accedono soltanto alle aree
dedicate `transcripts` e `chapters`. Metodi di compatibilità nominati separatamente preferiscono prima queste
risorse canoniche e poi usano la vecchia area `subtitles` per le attività video caricate migrate. `view.php`
seleziona il contratto di compatibilità soltanto quando esiste la sorgente sottotitoli legacy.

## Identità e scope

Contesto modulo e capability Moodle sono autorevoli. `mod/videotrack:participate` identifica esplicitamente gli utenti per cui possono essere scritti telemetria learner e strumenti personali; l’accesso ai report è indipendente. La visibilità dei gruppi usa la modalità effettiva dell’attività. Gli Analytics tra corsi rivalutano partecipazione, report e gruppi per ogni attività e identificano lo stesso video tramite ID provider, content hash del file caricato o URL esterno canonico. L'identità URL esterna espone un solo contratto pubblico; normalizzatori privati per authority, percorso e query rendono esplicite le regole per porte predefinite, separatori, frammenti e ordine della query.

## Confine di fiducia del registro di riproduzione

Un evento PLAY del provider apre un handshake server a credito zero tramite `mod_videotrack_start_playback`. Le richieste segmento maturano poi credito cumulativo soltanto dal tempo server trascorso a una velocità consentita. Ogni richiesta possiede un identificativo persistente di idempotenza protetto da indice univoco; i retry riutilizzano il risultato memorizzato senza inserire un nuovo segmento grezzo. `intervaljson` resta una rappresentazione di trasporto limitata, mentre la copertura unica esatta viene ricalcolata dalle righe validate al raggiungimento del limite.

## Architettura privacy

La raccolta è subordinata alle funzioni abilitate. Gli Analytics di istanza espongono aggregati esatti soltanto a chi possiede l’accesso ai report individuali entro capability e scope gruppi Moodle; chi dispone del solo accesso aggregato mantiene la soglia minima configurata. Le dashboard separate di corso e docente mantengono il proprio comportamento capability/privacy in attesa della revisione dedicata. Le etichette dei segnalibri sono visibili solo al proprietario. Il testo delle note personali è visibile al proprietario e può essere consultato/esportato dai docenti autorizzati quando le note sono abilitate; il testo è escluso dagli Analytics aggregati. L’export Privacy elabora collezioni grandi in blocchi limitati. Cancellazione e retention pianificata eliminano i record personali invece di conservare pseudonimi deterministici. `videotrack_state` è un dato personale derivato e viene ricostruito soltanto dai segmenti validati e dagli input di completamento ancora conservati. Il backup con dati utente esclude record scaduti e stato derivato; il restore applica la retention del sito destinazione e ricostruisce lo stato dopo il ripristino del completamento Moodle. I file di configurazione dell’attività vengono eliminati dal normale ciclo di vita del modulo, non dalla cancellazione dei dati learner.

## Architettura accessibile

I controlli sono da tastiera e hanno nomi accessibili; gli stati dinamici usano live region; sono supportati movimento ridotto e colori forzati; l’overlay poster espone il pulsante Play alle tecnologie assistive. La politica focus predefinita usa la visibilità del documento, non il semplice blur della finestra. I pulsanti dei capitoli VTT condividono il contratto focus-visible esplicito e mantengono un contorno di stato attivo in modalità colori forzati.

## Asset generati

`amd/src` è canonico. `amd/build` e source map sono artefatti generati e cambiano soltanto dopo una build Moodle reale.
