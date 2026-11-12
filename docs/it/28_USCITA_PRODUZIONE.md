# Evidenze integrate sul ciclo di vita e sul gate di produzione

Candidata corrente: VideoTrack **1.7.251** (`2026111201`). Audit registrato 2026-11-12.

## Baseline accettata e obiettivo

La **1.7.250 / 2026111103** reale è accettata: 412 file dello ZIP coincidono con main
`7a5b5f391dcc1ef0fd63f8640ce325e93f9ada25`. SHA-256 del pacchetto:
`69820512260e51382a463d3b0986e9dd89154a6d1774776483908b9ba63666e8`.
Il report server di 10 file ha SHA-256
`34688bc88280d367e475f86e9a8345959939be4bb84460ff54884e474f0a80f2`.
Entrambi i report Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e PHPUnit 379/4076; i due strict forniti
superano 10/0/0. Gli hash AMD prima e dopo Grunt sono identici. Sono osservazioni del manutentore/server e
della CI; in questo workspace non sono state eseguite prove Moodle native.

La candidata corretta e la CI dopo merge superano 52 scenari Behat / 1154 passi in ogni report riuscito
selezionato. Il primo job Moodle 5.3/MariaDB dopo merge ha perso il server web locale ed è fallito; un nuovo
job ha superato le prove sullo stesso codice. Conservare questa storia e la precedente correzione PHPCS.
Non dichiarare sei prime esecuzioni pulite o una causa accertata dell'arresto del server di test. I gate statici
configurati passano; PHPMD conserva 24 rilievi rivisti e Psalm 733/761 segnalazioni consultive, invariate dalla
250 corretta e una in più rispetto alla 249 per ramo analizzato.

La 251 aggiunge due prove di integrazione utili. La duplicazione reale verifica già le impostazioni delle
pause; i nuovi casi collegano backup/restore reali del corso a evidenze studente, file condivisi e Privacy API.
Logica di produzione, grant, servizi, lingue, DB e asset AMD restano identici byte per byte alla 250.

## Nuovi casi e limiti della prova

`tests/lifecycle_integration_test.php` usa controller Moodle reali di backup/restore e un nuovo corso di
destinazione. Segue il modello sincrono dei test backup di Moodle sui rami 500/503. `MODE_IMPORT` conserva
la vera struttura XML/file temporanea evitando la compressione: non è una certificazione indipendente di
download/estrazione MBZ, importazione tra siti o compatibilità con backup storici.

| Caso | Confini persistiti verificati |
|---|---|
| Senza dati utente | Override pause/durata, mapping Forum collegato, reazione soft-deleted e relativo file icona, un solo grade item canonico, sette aree file condivise con due lingue di trascrizione; nessun dato studente plugin, voto o completion copiato; record studente plugin di origine invariati. |
| Con dati utente | Il backup include righe vecchie e recenti con retention illimitata; la policy viene ristretta prima del restore. Le righe scadute sono escluse, quelle non validate non gonfiano lo stato, aggregate/completion obsolete sono ricostruite, i record puntano al nuovo corso/modulo, Privacy API esporta solo lo studente approvato, la cancellazione utente conserva l'altro studente, quella di contesto elimina il resto, configurazione/file condivisi e voti di competenza core restano, i record studente plugin di origine sono invariati. |

La fixture contiene deliberatamente stato/completion completi ma obsoleti, segmenti validati conservati e
segmenti non validati, reazioni/note/segnalibri, eventi integrità e versioni della presa visione. Sono input di
test: non si dichiara che li abbia prodotti un browser. Le evidenze browser reali restano nella suite U-007
accettata. Prima di accettare i risultati dei due nuovi casi serve la CI nativa della candidata.

Suite candidata attesa: **381 metodi test PHPUnit**; il totale asserzioni va misurato nella CI nativa.
Behat resta a **52 scenari / 1154 passi**. L'albero corrente contiene **415 file**, **167 file PHP**,
**1110 callable PHP nominati**, **654 callable AMD**, **346 righe inventario file** e **32 documenti per
lingua**. XMLDB resta `2026110601`, 7 tabelle / 151 campi / 22 indici / 22 chiavi esterne; nove servizi AJAX,
otto pacchetti da 997 chiavi e 48 terne sorgente/build/map AMD sono invariati. L'ER cambia solo etichetta versione.

## Stato del gate di produzione

| Area | Evidenza e limite residuo |
|---|---|
| Pacchetto esatto, schema, servizi, contratti lingue/build | La 250 reale è accettata. Per la 251 servono ancora CI candidata, CI dopo merge e accettazione pacchetto/server/strict reali. |
| Installazione nuova e matrice Moodle supportata | Coperte dalla normale installazione/validazione CI su 5.0–5.3; non dedurne un upgrade storico eseguito. |
| Upgrade storico/interrotto e uninstall completo | Restano contratti sorgente e test mirati esistenti. Upgrade storico/uninstall su sito eliminabile, con verifica zero residui, restano osservazioni di produzione distinte. |
| Backup/restore e privacy dopo il ripristino | Duplicazione impostazioni e test privacy circoscritti superati sulla 250. I due nuovi casi tra corsi sono in attesa sulla 251. |
| Reset corso/studente, ruoli/gruppi, completion nativa | Restano i contratti unit/browser accettati, compresi i due casi POST reali di reset della 250. |
| Retention | Restano i test cancellazione/ricostruzione; qui si aggiunge l'integrazione con policy di destinazione. Non si dichiarano prove sui tempi scheduler o fault injection. |
| Analytics e benchmark | SQL/indici/scope/aggregazioni invariati. Nessun nuovo benchmark eseguito o dedotto dal numero dei test/query. Le osservazioni su dataset ampio, più attività/gruppi e periodi 7/30/90 giorni vanno registrate con versioni e condizioni dati/cache dove richiesto dal gate finale di deployment. |
| Accessibilità manuale U-020 | Restano otto PASS dichiarati sulla 248 / Moodle 5.0 / Academi / Windows 11 / Chrome / NVDA entro quello scope. Le osservazioni Moodle 5.3 non sono registrate; nessuna nuova esecuzione manuale o certificazione WCAG è dedotta. |
| Moodle App | Non è dichiarato supporto dedicato (`db/mobile.php` conserva la lista addon vuota). La decisione su perimetro/supporto segue il gate web residuo; nessuna parità WebView/nativa/offline è dichiarata. |

U-017 e U-007 essenziale restano chiusi. Questa release avanza le evidenze integrate sul ciclo di vita;
non chiude con la sola documentazione l'intero gate, le osservazioni manuali rinviate o la decisione App.
Non aggiungere refactoring cosmetici o release artificiali per simulare il completamento di un gate.

## Release e ordine della roadmap

Usare la CI candidata e i normali controlli server PHPCS/lint/Grunt/PHPUnit: includono i nuovi test.
Mantenere la stessa release per le correzioni della candidata. Accettare una baseline solo dal relativo ZIP
reale dopo merge, report e strict. Upgrade storico/uninstall, benchmark e osservazioni manuali di deployment
restano evidenze esplicite, senza compilare risultati attesi come se fossero prove eseguite.

Completare il precedente lavoro web/ciclo vita/privacy/benchmark/manuale e la decisione App esplicita; poi
implementare i sottotitoli VTT multilingua concordati. Proseguire con base autenticata per provider,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive e consolidamento tra provider.

Vedere [ciclo di vita](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [privacy](18_PRIVACY_RETENTION.md),
[CLI/benchmark](21_CLI_DIAGNOSTICS.md), [evidenze manuali](19_ACCESSIBILITY.md),
[revisione lingue](27_REVISIONE_LINGUE.md) e [uscita browser](26_USCITA_BROWSER.md).
