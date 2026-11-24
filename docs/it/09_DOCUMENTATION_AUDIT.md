# Audit della documentazione

Candidata corrente: VideoTrack **1.7.271** (`2026112401`), basata sullo ZIP270 effettivo accettato.

## Accettazione270

483file distribuiti corrispondono a main63325ff0923543bda9ae3548a2f713cb80bae4b9,
albero61bc66351d2a1cb24508049aa123011a9544bbe8; SHA256 ZIP
3927f8ec40f199f52fe5d5f92d490f648244cdbcb59fea17569bc06e1711aa2d.
Candidato corretto45392734528/main45424709649 superano sei job ciascuno, Unit620/6483,
Behat78/1458senza retry, Grunt nativo, strict10/0/0, regressioni JS e due matrici
storiche aggiornamento/ripetizione/disinstallazione popolata con fotografie verificate.
Il report server20261124_173403 supera otto comandi: canonici/lint203condivisi,
Unit620/6483su50/53 ed entrambi i Grunt con hash invariati. Entrambi i report strict
utente superano10/0/0. L’utente conferma dati demo e grafica corretti: conservarli.

Restano visibili PHPMD36avvisi e Psalm ParadoxicalCondition sulla guardia standard
MOODLE_INTERNAL in db/services.php25, più758/786informazioni. PHPStan pulito.
Sono risultati270, non prove dell’esecuzione271.

## Modifiche e verifiche da completare

La [guida riferimenti attività](41_MICROSOFT_ACTIVITY_REFERENCES.md) descrive metadati,
permessi, associazione locale esplicita e backup portabile, senza concedere accesso.
XMLDB aggiunge un campo nullable (152totali); restano9metodi AJAX e48gruppiAMD.
La fixture lifecycle attende il nuovo NULL conservando tutte le precedenti asserzioni.
Il fix urgente di tracciamento/recupero e la casella temporanea restano presenti.

Conteggi: 516file repository/490distribuiti, 206PHP distribuiti,
1582callablePHP incluse7helperCI,660AMD,640metodi unitari,
80scenari espansi/1484passi e8lingue con1076chiavi. Tutti620metodi unitari precedenti,
78scenari e relative risorse restano identici. Inventari alle posizioni effettive.

Controlli locali: contratti sorgente/lessicali/upgrade, codice studente conservato,
lingue/placeholder, mappe esistenti esatte, regressioni JS e patch reversibile.
Gli esiti271iniziali sono riportati sotto. PHP/Moodle/MariaDB restano indisponibili
localmente; CI della candidata corretta, lifecycle e server sono da eseguire. OAuth/ricerca/anteprima reali,
eventi browser e telemetria studente attendibile richiedono HTTPS e account reale.
Microsoft resta indisponibile come sorgente tracciata. Nessuno ZIP plugin generato:
lo produce il manutentore dopo CI main accettata. Roadmap provider/App e accessibilità
manuale5.3 mantengono ordinamento e stato precedenti.

Quattro script Node ausiliari storici (backup_restore_static, privacy_static,
performance_static, deprecation_static) falliscono sia sulla270accettata sia sulla271:
attendono vecchie strutture sorgente o docs/struttura_tecnica.md. Restano invariati e
non sono risultati CI nativi. Passano tracker, sei gruppi sorgenti/sei compilati e adapter.

## CI271iniziale e correzione

Run45485426732 sul commit9a08e4111f6bd6f1ddb25e6f81010108cc06dcae,
albero751c68247bf7aaf8adcd6ec91429141c6b0f12f1. Tutte le sei matrici eseguono640metodi/
6610asserzioni con un solo fallimento: il test documentale esistente richiede
letteralmente `VideoTrack **1.7.271** (` seguito dal marcatore versione in entrambi
gli audit. Le intestazioni omettevano il nome del prodotto. Tutti20nuovi metodi
funzionali passano, compresi quattro backup/restore corso nativi. Corretti entrambi
i marcatori, conservando test esistente e tutte le asserzioni.

Il job qualità segnala15rilievi PHPCS: sette spaziature nei foreach multilinea di
microsoft_activity_reference_test.php e otto argomenti dei costruttori multilinea di
microsoft_reference_restore_test.php. Modificati soltanto gli spazi: flussi completi
di token eseguibili identici. Aggiornate le posizioni nei due inventari delle funzioni.
Questa correzione non modifica runtime, schema, lingue, AMD o workflow.

Tutte le sei matrici superano Behat80scenari/1484passi senza retry, strict10/0/0,
Grunt nativo e tracker più sei gruppi sorgenti/sei compilati. I sei archivi nativi
interni e i sei wrapper caricati separatamente coincidono: verificati48hash sorgenti,
96hash build/mappe, commit, comando nativo e digest nel log di cattura. I byte AMD
coincidono con la271inviata. Lint206PHP e PHPDoc superati, come lint/PHPCS separati
dell’helper lifecycle. PHPStan pulito su50/53; Psalm conserva un rilievo sulla guardia
standard più763/791informazioni. PHPMD segnala40avvisi, mantenuti visibili senza soppressioni.

Tutti i job falliscono complessivamente per l’asserzione documentale; il job qualità
anche per PHPCS. I due passaggi lifecycle storici condizionati sono saltati: non si
dichiara successo271per upgrade/disinstallazione. La candidata corretta richiede
nuova CI completa prima del merge, poi i consueti controlli main e server.
La race YouTube del replay da report, riprodotta separatamente, resta per la prossima
release; la correzione sorgente e i test sono esterni a questa correzione271.
