# Audit di uscita U-017: manutenibilità

Albero corrente: VideoTrack **1.7.234** (`2026110504`).

## Decisione e stato delle evidenze

Questo albero è la candidata di uscita U-017. Fermare la sequenza pianificata di estrazioni strutturali dopo il
consolidamento, mantenendo le segnalazioni residue sotto elencate con proprietari e motivazioni espliciti. Questa
release non cambia implementazioni di produzione, AMD, firme pubbliche dei servizi, schema o regole di analisi.
Quattro test aggiuntivi sugli effetti persistiti colmano lacune specifiche del tracker e dell'endpoint segmenti reale.

La baseline accettata è lo ZIP reale 1.7.233 di 396 file, SHA-256
`5d23832480f34395e233fcfaaf1a7a1c5054d3cab3b668ad320640b56c7ac7e7`, identico al commit unito
`6554576e60651cdd295b826eb00ccf2bd3b97adf`. I sei job iniziali e i sei post-merge superano ciascuno 365 test
PHPUnit, 3.920 asserzioni, 24 scenari Behat e dieci controlli strict. Il report server Moodle 5.0/5.3 supera PHPCS,
lint PHP, Grunt e la stessa suite PHPUnit. Entrambi gli strict server forniti danno 10 PASS / 0 WARN / 0 FAIL.

I nuovi test 1.7.234 non sono stati eseguiti nell'ambiente di generazione patch, dove PHP non è disponibile. Sono
requisiti di accettazione, non evidenze dichiarate verdi. Marcare U-017 chiuso solo dopo esito positivo dei sei job
CI della candidata esatta, artifact post-merge, ZIP reale e gate server/strict proporzionati. Un errore mantiene
aperta la candidata e richiede una correzione circoscritta. Non è richiesto un numero fisso di ulteriori refactor.

## Revisione delle responsabilità

| Area | Proprietari attuali e confine conservato | Decisione di uscita |
|---|---|---|
| Report e Analytics | `report_access`, servizi query/filtri temporali/aggregazione, loader Analytics e servizi di presentazione/export. I controller mantengono routing e controlli di accesso espliciti. | Conservare scope e privacy esistenti; nessuna ulteriore piccola estrazione selezionata. |
| Pagina attività | `view.php` conserva contesto/bootstrap, configurazione e rendering. La route POST di presa visione richiede partecipazione, POST e sesskey, poi delega conferma e completamento. | Mantenere la route esplicita senza duplicare policy della presa visione negli helper di presentazione. |
| Form attività | `form_definition`, `form_playback_settings`, `form_reaction_settings`, proprietari di preprocessing e validazione. Il form mantiene hook Moodle, conteggio righe e ciclo delle bozze file. | La tranche pianificata sui campi è completa. L'orchestrazione residua è accettata. |
| Tracker PHP | Algebra intervalli, guardie del registro, aggiornamento atomico e completamento restano in `tracker`; la configurazione del completamento ha il proprio proprietario. | Conservare il proprietario coeso della transazione. Spostare rami di guardia fra servizi cambia il perimetro da dimostrare. |
| Tracking browser condiviso | `core/tracker` delega eventi, stato/tempo, heartbeat, lifecycle e coda segmenti a moduli dedicati. `core/api` centralizza validazione, trasporto, retry e scope delle richieste. | Le responsabilità condivise sono già esplicite. Le policy indipendenti dal provider restano qui. |
| Provider concreti | `player.js` è YouTube; `html5_player.js` possiede media/controlli nativi; `vimeo_player.js` lifecycle SDK, recupero e callback seek. Tutti usano tracker/player/API/adapter condivisi. | La sola dimensione non giustifica l'unificazione dei comportamenti asincroni. Piccoli wrapper/adattatori di configurazione sono duplicazione accettata. |
| Export | `csv_export` resta facciata; selezione campi, identità, writer eventi e valori timestamp hanno proprietari dedicati. | Conservare chiamanti e confini privacy/contesto. |
| Lifecycle Moodle | Callback, upgrade storici, retention privacy, backup/restore e gradebook mantengono gli ingressi del framework. | Conservare compatibilità e cronologia delle migrazioni. |

La revisione provider segue import/deleghe reali e percorsi seek, resume, salvataggio e lifecycle. Behat esistente
copre playback/seek/completamento/presa visione HTML5 e contratti adapter YouTube/Vimeo. Gli SDK esterni dei test
deterministici sono sostituiti da double: non costituiscono certificazione completa di provider/browser reali.
Estrarre ulteriormente alias o wrapper di configurazione cosmetici aggiungerebbe un ciclo build/validazione senza
isolare una nuova responsabilità di dominio. Riaprire il confine quando un difetto o una funzione ne dimostra l'utilità.

## Revisione degli effetti del tracker

`update_state()` conserva questo ordine: lock canonico attività/utente, transazione delegata, caricamento/creazione
stato, risoluzione replay, guardie sessione attiva/frontiera seek, credito server cumulativo, persistenza delle
evidenze accettate, aggregazione copertura validata e completamento, commit e rilascio. L'evento di completamento
segue il commit riuscito. L'endpoint emette eventi significativi e sincronizza il completamento Moodle solo per una
richiesta nuova accettata. Le uscite anticipate per replay e rifiuto restano distinte.

La revisione sorgente conferma che il percorso eccezioni richiede rollback e rilascio del lock. Questa release non
dichiara test con processi concorrenti per contesa del lock o guasti database iniettati: tali limiti restano espliciti
nell'audit di produzione/lifecycle. L'ordine delle mutazioni non viene riscritto per abbassare le metriche.

| Contratto | Evidenza |
|---|---|
| Credito da tempo server, velocità limitata e debito di tolleranza cumulativo | Test comportamentali esistenti in `tracker_test.php`. |
| Proprietà della sessione attiva, rifiuto tab obsoleto, chiusure pausa/lifecycle | Test esistenti sullo stato persistito in `tracker_test.php`; contratti lifecycle/provider condivisi. |
| Replay accettato e copertura unica invariata | Test persistito esistente; rafforzato dal nuovo caso endpoint/completamento. |
| Una richiesta rifiutata per budget resta rifiutata se ritentata più tardi | Nuovo `tracking_effects_test::test_budget_rejection_retry_preserves_state_and_audit_row()`. |
| Il seek rifiutato conserva la frontiera; il suo replay non azzera un budget successivo | Nuovo `tracking_effects_test::test_forward_rejection_keeps_frontier_and_does_not_replay_its_budget_reset()`. |
| Al limite intervalli, la copertura esatta è ricostruita soltanto dal registro validato | Nuovo `tracking_effects_test::test_live_interval_cap_rebuild_preserves_exact_validated_coverage()`. |
| Il retry autenticato conserva la riga completamento Moodle ed emette una volta gli eventi significativi/completamento | Nuovo `tracking_effects_test::test_external_completion_retry_keeps_persisted_effects_and_events_once()`. |
| Confini lock, commit, rollback ed effetti successivi | Revisione sorgente; contratti sorgente e test lifecycle/privacy esistenti mantenuti. Nessuna nuova dichiarazione su concorrenza/guasti iniettati. |

I nuovi test usano attività reali, studenti iscritti, tabelle registro/stato e sink eventi Moodle. I casi tracker
usano un tempo server fornito; il caso endpoint usa il tempo di produzione dopo un handshake esplicito precedente.
Non servono sleep o richieste di rete ai provider. Le righe rifiutate restano evidenze di audit e non entrano nella
copertura validata. Il caso limite intervalli verifica la ricostruzione database reale, non soltanto l'helper puro.

## Segnalazioni PHPMD residue revisionate

Il report reale 1.7.233 contiene **24** segnalazioni. La candidata 1.7.234 mantiene invariati tutti i corpi di
produzione analizzati e i ruleset: il totale atteso resta 24, da verificare sull'intero report CI, senza aggiunte.
Questo è un registro esplicito del debito, non una soppressione, una dichiarazione di zero finding o un'accettazione
generica di segnalazioni future.

| File/proprietario | Segnalazioni | Motivo del mantenimento e condizione di riapertura |
|---|---:|---|
| `classes/external/save_segment.php` | 1 sulla lista parametri | La firma AJAX pubblica di dieci campi coincide con dichiarazione e chiamanti. Riaprire solo con una modifica deliberata del contratto. |
| `classes/local/csv_export.php` | 1 sul numero metodi pubblici | Facciata stabile con implementazioni dedicate. Riaprire per una modifica reale dell'API export mantenendo controlli di contesto e identità. |
| `classes/local/tracker.php` | 6 | Dimensione/API/complessità della classe e complessità, NPath e lunghezza di `update_state()`. Conservare insieme registro, stato ed effetti transazionali; ogni futura separazione deve rispettare la matrice sopra. |
| `db/upgrade.php` | 3 | Complessità, NPath e lunghezza riflettono migrazioni storiche ordinate. Non riscrivere upgrade distribuiti per una metrica. Riaprire per un difetto documentato di upgrade. |
| `lib.php` | 11 | Nove parametri formali inutilizzati su firme callback/pubbliche e due argomenti booleani consolidati. Conservare chiamanti Moodle/lifecycle; rivedere solo per una modifica concreta compatibile. |
| `mod_form.php` | 2 | Complessità della classe e lunghezza di `definition()` dopo la separazione di campi, validazione e preprocessing. Il form ha 23 metodi e conserva orchestrazione del framework. Riaprire per una modifica funzionale con un confine utile. |
| **Totale** | **24** | Nessuna riduzione di regole, perimetro o baseline degli analizzatori. |

## Promozione e fase successiva

1. La CI candidata deve superare tutti i sei job e confrontare tutte le segnalazioni PHPMD; PHPStan/Psalm mantengono
   scope di produzione e output consultivo/deprecazioni revisionati.
2. Confrontare albero unito e digest dei sei artifact post-merge con la candidata approvata.
3. Accettare ZIP reale, controlli server PHPCS/lint/Grunt/PHPUnit su Moodle 5.0/5.3 ed entrambi gli strict.
4. Se questi gate passano, chiudere U-017 con questo registro e fermare le micro-release strutturali finché nuove
   evidenze non ne motivano una. Un difetto di produzione prevale sempre sulla decisione di stop.

Proseguire quindi la roadmap web: matrice browser/provider/ruoli U-007, accessibilità manuale U-020, revisione
semantica delle traduzioni e audit finale produzione/lifecycle/privacy/benchmark su uno ZIP esatto. L'automazione
non certifica accessibilità manuale, ogni browser/SDK o operazioni di produzione. Valutare Moodle App dopo i gate
web; OneDrive/SharePoint, Zoom Cloud Recording e Google Drive restano fasi successive in quest'ordine.

Vedere [analisi statica](24_ANALISI_STATICA.md), [copertura browser](22_TEST_BROWSER_BEHAT.md),
[gate di release](07_BUILD_TEST_RELEASE.md) e [accessibilità](19_ACCESSIBILITY.md).
