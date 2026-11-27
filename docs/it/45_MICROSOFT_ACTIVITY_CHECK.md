# Verificare il riferimento Microsoft salvato nell’attività

VideoTrack **1.7.275** (`2026112700`) collega il riferimento facoltativo salvato
nell’attività alla diagnostica amministrativa esistente. Non abilita Microsoft
come sorgente studente e non implementa tracciamento della riproduzione.

## Aprire la verifica

Come amministratore del sito, modificare un’attività VideoTrack esistente ed
espandere **Preparazione video Microsoft**. Usare **Verifica il riferimento Microsoft
salvato**. La diagnostica si apre in un’altra scheda, conservando il modulo ancora
aperto. L’avviso chiarisce che viene usato l’ultimo riferimento salvato: le modifiche
ancora presenti nel modulo non sono incluse. Prima occorre salvare una nuova attività.
I docenti senza permesso di configurazione sito non vedono questo collegamento.

Il link contiene soltanto `activitycmid`, ID del modulo corso Moodle. Non contiene
ID drive/video, JSON del riferimento, URL temporanei, credenziali o account scelti.
Il controller controlla il permesso di sito e quello di gestione attività nel corso
proprietario, poi legge il record corrente. Un modulo di un altro plugin non può
essere sostituito tramite una coincidenza del numero di istanza. Il permesso sito
precede la ricerca del modulo, anche per ID non validi.

## Inviare un’operazione esplicita

L’apertura resta locale: non acquisisce un client OAuth, non contatta Graph e non
crea iframe. Il modulo vincolato mostra il nome escapato dell’attività salvata e le
tre operazioni già esistenti: metadati, pagina originale e anteprima nativa privata.
Non espone campi modificabili per ID, riferimento incollato o scelta account.
Il server usa soltanto il riferimento salvato e l’account personale corrente.

Ogni POST richiede validazione Moodle e sesskey. Ogni richiesta rilegge il record
prima della validazione: un riferimento cambiato dopo l’apertura precedente prevale
sulla vecchia fotografia del browser. Campi aggiuntivi contraffatti non selezionano
un’altra risorsa o l’account condiviso. Le modifiche non salvate non sono scritte.
Riferimenti vuoti, malformati, ripristinati non associati o di un servizio diverso
si fermano localmente con l’errore già esistente. Configurazione, issuer selezionato,
endpoint OAuth e accesso seguono i controlli del probe personale prima di Graph.

Resta una diagnostica amministrativa: è richiesto `moodle/site:config`; la sola
gestione del corso non autorizza l’endpoint. La diagnostica generale conserva le
scelte esplicite manuali/riferimento/account. Collegare un account usa il callback
core esistente e riporta alla diagnostica generale; dopo il collegamento riaprire
il link dall’attività. Non sono aggiunti state OAuth o parametri di ritorno persistiti.

## Identità, media ed evidenze

Ogni operazione esplicita riusa `microsoft_user_probe` e il client OAuth personale
core. Nessun ripiego sull’account condiviso o di sistema. Il core continua a gestire
il rinnovo dei token. Il riferimento identifica una risorsa, non concede accesso.
Restano Graph a host fisso, metadati e operazioni anteprima circoscritte già presenti:
nessun download file, importazione Moodle, proxy streaming, cache URL o retry automatico.
Gli URL privati della pagina/anteprima restano nella risposta amministrativa esistente.
Il JSON diagnostico ordinario non acquisisce ID risorse, nomi, riferimenti o URL.
Conservati no-cache, no-referrer e sandbox iframe esistenti.

La verifica non cambia sorgente HTML5/YouTube/Vimeo attiva, durata impostata, segmenti,
note, reazioni, segnalibri, voti o completamento. Non associa/riautorizza il riferimento
e non ripara dati. Non aggiunge migrazioni, task, capability, servizi o nuovi trasferimenti
Privacy. La casella di recupero storico resta ritirata; ricalcolo ordinario e righe
recuperate mantengono il comportamento accettato. Tutti gli AMD rimangono identici.

## Evidenze e gate successivo

Baseline: ZIP reale 274 SHA256
31f6f085edfe3f1c82f4a6eb418bb3ce08c26b3ed934047741e9e61435bdb220,
501 file identici a main 5e939e64/albero ec807599. Candidata e main superano sei job,
Unit 656/6922, Behat 84/1571 senza retry, entrambi i lifecycle popolati e strict.
Il server effettivo supera otto esiti su 50/53, canonici/lint condivisi 210 file e
build nativi invariati. Il 2026-11-27 il maintainer conferma duplicazione/salvataggi
estranei, CSV e ricalcolo ordinario.

Quindici nuovi metodi PHPUnit coprono permessi, ID di altri moduli, moduli reali
GET/POST/CSRF, input contraffatti, riferimenti cambiati, azioni, riservatezza del link
e integrazione con risposte personali nuove. HTTP provider simulato, compreso un
rifiuto dopo un successo; confrontate righe di evidenze, voti e completamento.
Due scenari browser coprono il link reale in nuova scheda, il rifiuto locale del
riferimento vuoto e l’esclusione del docente. Il controllo del controller generale
mantiene il requisito POST accettando il contesto opzionale fornito dal server.

Verifiche locali su sorgenti, inventari, lingue, asset e patch distinte dall’esecuzione
Moodle. Non c’è un runtime PHP locale: PHPCS/PHPUnit/Behat 275, CI, lifecycle popolati,
server e prove manuali sono ancora attesi. HTTPS e account Microsoft collegato non
sono ancora disponibili alla preparazione. Restano necessari accesso delegato reale,
autenticazione iframe/browser e telemetria supportata tempo/play/pausa/seek prima
della sorgente Microsoft tracciata. I test fittizi non dimostrano questi esiti.
Seguono Zoom, Google Drive e App secondo l’ordine concordato.

Fonti primarie: [anteprima Graph e identità chiamante](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0),
[informazioni opache dell’anteprima](https://learn.microsoft.com/en-us/graph/api/resources/itempreviewinfo?view=graph-rest-1.0),
[API OAuth Moodle](https://docs.moodle.org/dev/OAuth_2_API).
