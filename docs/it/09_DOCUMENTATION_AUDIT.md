# Audit della documentazione

Baseline: VideoTrack **1.7.98** (`2026082101`).

## Copertura

- File non documentali inventariati: **280/280**.
- Funzioni/metodi PHP nominati inventariati: **722**.
- Callable AMD nominati rilevati e inventariati: **647**.
- Tabelle XMLDB documentate: **7**.
- Chiavi impostazioni sito documentate: **57**.
- Chiavi configurazione player documentate: **133**.
- Servizi AJAX documentati: **9**.
- Language pack: otto pacchetti con lo stesso contratto di **987 chiavi**; i testi operativi sono tradotti, mentre termini tecnici e nomi propri possono legittimamente coincidere.
- Panoramiche root: `README.md` (inglese) e `README_IT.md` (italiano).
- Sintesi privacy root: `PRIVACY.md` e `PRIVACY_IT.md`.
- Diagnostica CLI distribuita documentata in `21_CLI_DIAGNOSTICS.md` e coperta da contratti statici di sola lettura.
- Automazione browser Behat documentata in `22_TEST_BROWSER_BEHAT.md`; U-007 è tracciato come in corso.
- I contratti statici resume/completion/alert impilati completano l’ambiente Behat ora operativo; il gate browser reale 1.7.97 ha superato 10/10 scenari e 152/152 step su Moodle 5.0 e 5.3, mentre gli smoke test provider più ampi restano separati.
- La navigazione capitoli ha ora un contratto esplicito focus-visible/colori forzati; la matrice manuale tastiera/high-contrast resta un gate per la chiusura finale di U-020.

## Verifica documentazione pre-produzione 1.7.98

- L’inventario corrente dei file non documentali è **280/280** dopo l’aggiunta del feature Behat deterministico per la presa visione HTML5.
- L’inventario funzioni resta **722 PHP / 647 AMD** callable nominati; questa tranche test-only non aggiunge step PHP o callable di produzione.
- U-007 copre ora conferma/persistenza della presa visione immediata, blocco prima del video-end e sblocco dopo evidenza validata fino all’ultimo secondo.
- Runtime di produzione, AMD, schema, capability, privacy, Analytics, tracking e language pack restano invariati.
- Restano pendenti asserzioni browser sullo stato completion, alert impilati e harness deterministici dei provider esterni.

## Verifica documentazione pre-produzione 1.7.97

- L’inventario corrente dei file non documentali è **279/279** dopo l’aggiunta del feature Behat deterministico per il playback HTML5.
- L’inventario funzioni è **722 PHP / 647 AMD** callable nominati dopo l’aggiunta di un metodo Behat per verificare lo stato play/pause.
- U-017 viene chiuso per stop condition: gli audit finali di `mod_form.php`, degli entrypoint HTML5/Vimeo/YouTube e degli hotspot PHP residui non evidenziano un’ulteriore estrazione ovvia a basso rischio con rapporto beneficio/rischio favorevole.
- È attiva la chiusura U-007 browser: la 1.7.97 aggiunge scenari deterministici HTML5 per resume, seek indietro e play/pause senza modificare runtime di produzione o sorgenti AMD.
- Restano pendenti harness YouTube/Vimeo indipendenti dalla rete pubblica, completion browser e alert impilati.

## Verifica documentale correttiva 1.7.96
- L'inventario corrente dei file non documentali resta a **278/278** voci.
- L'inventario funzioni resta a **721 PHP / 647 AMD** callable nominati; questa tranche correttiva non aggiunge callable.
- La release 1.7.96 corregge l'aspettativa obsoleta di `completion_contract_test` emersa dal gate PHPUnit reale della 1.7.95: l'implementazione delle reaction richieste viene ora verificata in `local\form_validation`, dove la policy è stata intenzionalmente estratta.
- Il comportamento di abilitazione completion e lo scope U-017 della 1.7.95 restano invariati.
- Non sono incluse modifiche a runtime di produzione, schema, capability, privacy, report/Analytics, tracking, player, AMD, Behat o language pack.

## Verifica documentale pre-produzione 1.7.95

- L'inventario corrente dei file non documentali resta a **278/278** voci.
- L'inventario funzioni è **721 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper puro di abilitazione completion e del relativo test comportamentale.
- La release 1.7.95 continua U-017 su `mod_form.php` spostando la decisione di abilitazione delle regole di completion personalizzate in `local\form_validation::completion_rule_enabled()`.
- Il callback Moodle del form resta un delegato sottile; sono preservate le semantiche durata/percentuale, regole reaction e acknowledgement.
- Non sono incluse modifiche a schema, capability, privacy, report/Analytics, tracking, completion runtime, player, AMD, Behat o language pack.

## Verifica documentale correttiva 1.7.94

- L'inventario corrente dei file non documentali resta a **278/278** voci.
- L'inventario funzioni resta a **719 PHP / 647 AMD** callable nominati; questa tranche correttiva non aggiunge callable di produzione o test.
- La release 1.7.94 corregge l'unico errore PHPCS `PSR2.Classes.ClassDeclaration.CloseBraceAfterBody` rilevato in `tests/form_validation_test.php` dal gate maintainer reale della 1.7.93.
- Il comportamento PHPUnit resta invariato rispetto alla 1.7.93; U-017 non avanza in questa release correttiva.
- Indici, inventari e marker audit della release corrente sono sincronizzati alla 1.7.94.

## Verifica documentale pre-produzione 1.7.93

- L'inventario corrente dei file non documentali è **278/278** dopo l'aggiunta di `classes/local/form_validation.php` e `tests/form_validation_test.php`.
- L'inventario funzioni è **719 PHP / 647 AMD** callable nominati.
- La release 1.7.93 prosegue U-017 spostando fuori da `mod_form.php` soltanto la policy autonoma di validazione scalare/JSON; la validazione contestuale di file, VTT, icone reaction e forum resta nel form.
- Nessun file AMD o language pack cambia in questa tranche.

## Verifica documentale pre-produzione 1.7.92

- L’inventario corrente dei file non documentali resta a **276/276** voci.
- L’inventario funzioni è **712 PHP / 647 AMD** callable nominati dopo l’aggiunta dell’helper highlight Analytics e del relativo test comportamentale.
- La release 1.7.92 continua U-017 dalla baseline server-green 1.7.91 spostando la selezione di top-watched, top-replayed e largest-drop fuori da `report.php` in `local\report_support::analytics_highlights()`.
- L’helper preserva esattamente filtro dei bin già sottoposti a privacy, disponibilità dei replay, ordinamento, reset sulle discontinuità e limite a cinque elementi.
- Non sono incluse modifiche a SQL, builder/soglia privacy Analytics, capability dei report, export, schema, tracking, completion o runtime AMD/player.

## Verifica documentazione pre-produzione 1.7.91

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **710 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper di selezione del fallback state e del relativo test comportamentale.
- La release 1.7.91 continua U-017 dalla baseline server-green 1.7.90 spostando da `report.php` a `local\report_support::analytics_prefers_state_fallback()` la scelta tra Analytics da segmenti grezzi e fallback da state.
- L'helper preserva esattamente la priorità sul numero di viewer e l'epsilon stretto `0.001` sui secondi unici; la soglia privacy continua a essere applicata successivamente nel controller.
- Non sono incluse modifiche a SQL, builder/soglie privacy Analytics, capability report, export, schema, tracking, completion o runtime AMD/player.

## Verifica documentazione pre-produzione 1.7.90

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **708 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper per i conteggi di timing della presa visione e del relativo test comportamentale.
- La release 1.7.90 riprende U-017 dalla baseline server-green 1.7.89 spostando da `report.php` a `local\report_support::analytics_acknowledgement_timing_counts()` il conteggio dei bucket timing degli Analytics di presa visione.
- L'helper preserva la semantica di `acknowledgement::requires_video_end()`, incluso il fallback esistente su anytime per valori timing mancanti o non validi.
- Non sono incluse modifiche a SQL, aggregazioni/soglie privacy Analytics, capability report, export, schema, tracking, completion o runtime AMD/player.

## Verifica documentazione correttiva 1.7.89

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni resta a **706 PHP / 647 AMD** callable nominati; questa tranche correttiva non aggiunge callable di produzione o test.
- Gli Analytics state racchiudono ora l'intero scope capability-safe prima di aggiungere `videoid = :analyticsstatevideoid`, quindi il filtro provider si applica a ogni ramo `OR`.
- Lo scope state senza filtro provider e il contratto del parametro nominato restano invariati.
- Indici e intestazioni inventario della release corrente sono sincronizzati dopo il fallimento release-hygiene della 1.7.88; U-017 non avanza in questa release correttiva.

## Verifica documentazione pre-produzione 1.7.88

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **706 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper Analytics state e del relativo test comportamentale.
- La release 1.7.88 continua U-017 con l'estrazione meccanicamente equivalente del filtro provider opzionale degli Analytics state da `report.php` in `local\report_support::analytics_state_condition()`.
- Scope capability-safe, parametro `analyticsstatevideoid` e semantica di concatenazione SQL esistente nel controller restano invariati; questo refactor non normalizza la precedenza SQL. Analytics segment/reaction/bookmark/integrity/acknowledgement, soglie privacy, capability report, export, schema, tracking, completion e runtime AMD/player restano invariati.

## Verifica documentazione pre-produzione 1.7.87

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **704 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper Analytics segmenti e del relativo test comportamentale.
- La release 1.7.87 continua U-017 con un'estrazione meccanicamente equivalente del filtro `servervalidated = 1` e del filtro provider opzionale per gli Analytics dei segmenti da `report.php` in `local\report_support::analytics_segment_condition()`.
- Lo scope Analytics capability-safe e il parametro provider opzionale `analyticssegmentvideoid` restano identici; il filtro state Analytics resta nel controller e reaction/bookmark/integrity/acknowledgement Analytics, soglie privacy, capability report, export, schema, tracking, completion e runtime AMD/player restano invariati.

## Verifica documentazione pre-produzione 1.7.86

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **702 PHP / 647 AMD** callable nominati dopo l'aggiunta dell'helper Analytics integrity e del relativo test comportamentale.
- La release 1.7.86 riprende U-017 con un'estrazione meccanicamente equivalente del filtro provider opzionale per gli Analytics integrity da `report.php` in `local\report_support::analytics_integrity_condition()`.
- Lo scope Analytics capability-safe e il filtro provider opzionale `videoid` con parametro `analyticsintegrityvideoid` restano identici; Analytics reaction/bookmark/acknowledgement, soglie privacy, capability report, export, schema, tracking, completion e runtime AMD/player restano invariati.
- Il `phpcs.xml.dist` canonico resta il gate full `moodle-extra` senza esclusioni introdotto dalla 1.7.85.

## Verifica documentazione pre-produzione 1.7.85

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni passa a **700 PHP / 647 AMD** callable nominati.
- I metadata di coverage PHPUnit usano attributi in tutte le classi test top-level; non restano annotazioni legacy `@covers`.
- Il `phpcs.xml.dist` canonico contiene ora il ruleset `moodle-extra` completo senza esclusioni specifiche VideoTrack.
- Questa tranche modifica solo test, metadata di release e documentazione; implementazione runtime e asset AMD restano invariati.

## Verifica documentazione pre-produzione 1.7.84

- L'inventario corrente dei file non documentali resta a **276/276** voci.
- L'inventario funzioni resta a **699 PHP / 647 AMD** callable nominati.
- Tutti gli 8 language pack mantengono **987 chiavi** e contratti placeholder identici; le assegnazioni sono ora alfabetiche globalmente e i commenti di sezione post-header sono stati rimossi.
- Il `phpcs.xml.dist` canonico riattiva entrambi i controlli `moodle.Files.LangFilesOrdering` e differisce soltanto `moodle.PHPUnit.TestCaseCovers.Missing`.
- Nessuna modifica a runtime, schema, capability, privacy, export, Analytics, completion, tracking o AMD/player.

## Verifica documentazione pre-produzione 1.7.83

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.83: **276/276** voci; il file aggiunto è `phpcs.xml.dist` a livello repository.
- L'inventario funzioni resta a **699** funzioni/metodi PHP nominati e **647** callable AMD nominati; sono state riallineate le posizioni sorgente modificate.
- I due failure Behat comuni alle quattro versioni Moodle sono stabilizzati puntando i selector CSS univoci dei `<summary>` nativi dello storico learner invece del selector Moodle generico `text`; markup e runtime learner restano invariati.
- Il gate PHPCS canonico usa ora `moodle-extra` tramite `phpcs.xml.dist`, escludendo soltanto i tre codici warning esatti presenti nella baseline 1.7.82; la scansione `moodle-extra` completa resta evidenza consultiva del debito.
- U-017 è sospeso per questa release correttiva.

## Verifica documentazione pre-produzione 1.7.82

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.82: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **699** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.82 continua U-017 con l'estrazione meccanicamente equivalente della decorazione SQL dei bookmark Analytics da `report.php` a `local\report_support::analytics_bookmark_condition()`, con copertura comportamentale e contratto di delega del controller.
- Scope Analytics capability-safe, esclusione delle righe eliminate, selezione dei soli bookmark e filtro provider `videoid` opzionale con parametro `analyticsbookmarkvideoid` restano invariati. Soglie privacy/clustering Analytics, Analytics reazioni/integrity/presa visione, capability report, export, schema, tracking, completion e runtime AMD/player restano intenzionalmente fuori da questa tranche.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare solo dopo gate maintainer PHPUnit/PHPCS verdi per la 1.7.82.

## Verifica documentazione pre-produzione 1.7.81

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.81: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **697** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.81 continua U-017 con l'estrazione meccanicamente equivalente della decorazione SQL delle reazioni Analytics da `report.php` a `local\report_support::analytics_reaction_condition()`, con copertura comportamentale e contratto di delega del controller.
- Scope Analytics capability-safe, esclusione delle righe eliminate, selezione degli eventi di reazione standard e filtro provider `videoid` opzionale con parametro `analyticsreactionvideoid` restano invariati. Soglie privacy/clustering Analytics, Analytics bookmark/integrity/presa visione, capability report, export, schema, tracking, completion e runtime AMD/player restano intenzionalmente fuori da questa tranche.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare solo dopo gate maintainer PHPUnit/PHPCS verdi per la 1.7.81.

## Verifica documentazione pre-produzione 1.7.80

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.80: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **695** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.80 continua U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri delle note personali per-studente da `report.php` a `local\report_support::note_event_condition()`, con copertura comportamentale e contratto di delega del controller.
- Lo scope learner canonico, la selezione note personali/righe non eliminate, il filtro studente opzionale e i limiti inclusivi `timecreated` inferiore/superiore restano invariati. Export note, privacy note, capability report, Analytics, schema, tracking, completion e runtime AMD/player restano intenzionalmente fuori da questa tranche.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare solo dopo gate maintainer PHPUnit/PHPCS verdi per la 1.7.80.

## Verifica documentazione pre-produzione 1.7.79

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.79: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **693** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.79 continua U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri usata per individuare gli utenti rappresentati nei segmenti da `report.php` a `local\report_support::segment_user_condition()`, con copertura comportamentale e contratto di delega del controller.
- Lo scope learner canonico e la chiave parametro esistente `vtid` restano invariati; non viene introdotto un filtro studente opzionale perché la query di discovery segmenti della 1.7.78 intenzionalmente non lo applicava. Caricamento/validazione segmenti, query stato, capability/privacy report, export, Analytics, schema, tracking, completion e runtime AMD/player restano intenzionalmente fuori da questa tranche.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare con un'altra piccola estrazione server-side autonoma solo dopo gate maintainer PHPUnit/PHPCS verdi.

## Verifica documentazione pre-produzione 1.7.78

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.78: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **691** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.78 continua U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri di `videotrack_state` da `report.php` a `local\report_support::state_condition()`, con copertura comportamentale e contratto di delega del controller.
- Lo scope learner canonico, il filtro studente opzionale e le chiavi parametro esistenti `svtid`/`suid` restano invariati; ordinamento query stato, scope segmenti, capability/privacy report, export, Analytics, schema, tracking, completion e runtime AMD/player restano intenzionalmente fuori da questa tranche.
- Il conteggio headline corrente dell'inventario/audit funzioni italiano viene riallineato all'inventario generato, preservando i conteggi storici delle release precedenti.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare con un'altra piccola estrazione server-side autonoma solo dopo gate maintainer PHPUnit/PHPCS verdi.

## Verifica documentazione pre-produzione 1.7.77

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.77: **275/275** voci.
- Inventario funzioni rigenerato dalle posizioni sorgente: **689** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.77 riprende U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri usata per individuare gli utenti con note personali da `report.php` a `local\report_support::note_user_condition()`, con copertura comportamentale e contratto di delega del controller.
- Learner scope, selezione opzionale dello studente e chiavi parametro `vtid`/`uid` restano invariati; contenuto/filtri data delle note, privacy delle note ed export restano intenzionalmente fuori da questa tranche.
- Le tabelle XMLDB restano **7**, le impostazioni **57**, i servizi AJAX **9** e le chiavi di configurazione browser/player **133**.
- Gli otto language pack mantenuti restano allineati a **987** chiavi con placeholder Moodle coerenti.
- I link Markdown relativi sono stati ricontrollati sull'albero candidato senza target mancanti.
- U-017 resta in corso; la tranche successiva deve continuare con un'altra piccola estrazione server-side autonoma solo dopo gate maintainer PHPUnit/PHPCS verdi.

## Verifica documentazione pre-produzione 1.7.76

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.76: **275/275** voci.
- Inventario funzioni rigenerato sulle posizioni sorgente correnti: **687** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- I report di attività separano ora vista aggregata, vista individuale, export aggregato ed export individuale tramite quattro capability nel contesto modulo; la capability storica `mod/videotrack:viewreport` resta un grant completo retrocompatibile e l’upgrade ne clona le assegnazioni di ruolo esistenti sulle quattro nuove capability prima di eventuali personalizzazioni successive.
- Chi dispone soltanto dell’accesso aggregato non può filtrare per learner e mantiene il masking `analyticsminusers`; chi possiede la vista individuale riceve valori cumulativi/Analytics di istanza esatti entro il proprio scope Moodle learner/gruppi. Gli Analytics cross-course dello stesso video sono esatti soltanto se l’accesso individuale copre ogni attività inclusa.
- I CSV dettagliati/personali richiedono vista individuale più export individuale; i download aggregati richiedono export aggregato e mantengono la stessa soglia privacy della pagina. Reset/ricalcolo/manutenzione report restano dietro l’accesso storico completo.
- L'inventario XMLDB resta di **7** tabelle; impostazioni sito **57**, servizi AJAX **9**, chiavi browser/player **133**.
- Tutti gli otto language pack mantenuti espongono **987** chiavi identiche, nessun duplicato e placeholder Moodle coerenti.
- I link Markdown relativi della documentazione sono stati verificati contro l’albero senza target mancanti.
- Questa è una release correttiva di autorizzazione/privacy; U-017 è intenzionalmente sospeso fino ai gate maintainer PHPUnit/PHPCS verdi.

## Verifica documentazione pre-produzione 1.7.74

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.74: **273/273** voci.
- Inventario funzioni rigenerato sulle posizioni sorgente correnti: **677** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.74 avanza U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri degli eventi integrity standard da `report.php` in `local\report_support::integrity_event_condition()`, con copertura comportamentale e contratto di delega del controller.
- Scope learner, selezione utente opzionale, limiti inclusivi sul tempo video e le chiavi parametro esistenti `integrityvtid`/`integrityuserid`/`integritytimefrom`/`integritytimeto` restano invariati rispetto al controller 1.7.73.
- L'inventario XMLDB coincide con tutte le **7** tabelle e con ogni campo dichiarato in `db/install.xml`.
- L'inventario impostazioni coincide con tutte le **57** impostazioni `mod_videotrack`.
- L'inventario servizi AJAX coincide con tutti i **9** servizi dichiarati.
- L'inventario configurazione browser/player coincide con tutte le **133** chiavi.
- Tutti gli otto language pack mantenuti espongono **982** chiavi identiche, nessun duplicato e placeholder Moodle coerenti.
- I link Markdown relativi della documentazione sono stati verificati contro l'albero senza target mancanti.
- I documenti tecnici storici sotto `archive/` restano storici; indici e inventari correnti sono ribasati alla 1.7.74. Gli artefatti interni roadmap/lesson del maintainer non fanno parte del tree distribuito.

## Verifica documentazione pre-produzione 1.7.73

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.73: **273/273** voci.
- Inventario funzioni rigenerato sulle posizioni sorgente correnti: **675** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.73 avanza U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri degli eventi bookmark standard da `report.php` in `local\report_support::bookmark_event_condition()`, con copertura comportamentale e contratto di delega del controller.
- Scope learner, filtro bookmark/non cancellato, selezione utente opzionale e limiti inclusivi sul tempo video mantengono gli stessi frammenti SQL e le stesse chiavi dei parametri nominati usati dal controller 1.7.72.
- L'inventario XMLDB coincide con tutte le **7** tabelle e con ogni campo dichiarato in `db/install.xml`.
- L'inventario impostazioni coincide con tutte le **57** impostazioni `mod_videotrack`.
- L'inventario servizi AJAX coincide con tutti i **9** servizi dichiarati.
- L'inventario configurazione browser/player coincide con tutte le **133** chiavi.
- Tutti gli otto language pack mantenuti espongono **982** chiavi identiche, nessun duplicato e placeholder Moodle coerenti.
- I link Markdown relativi della documentazione sono stati verificati contro l'albero senza target mancanti.
- I documenti tecnici storici sotto `archive/` restano storici; indici e inventari correnti sono ribasati alla 1.7.73. Gli artefatti interni roadmap/lesson del maintainer non fanno parte del tree distribuito.

## Verifica documentazione pre-produzione 1.7.72

- Inventario corrente dei file non documentali rigenerato sull'albero candidato 1.7.72: **273/273** voci.
- Inventario funzioni rigenerato sulle posizioni sorgente correnti: **673** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- La release 1.7.72 avanza U-017 con l'estrazione meccanicamente equivalente della costruzione SQL/parametri degli eventi di reazione standard da `report.php` in `local\report_support::reaction_event_condition()`, con copertura comportamentale e contratto di delega del controller.
- Le asserzioni dirette del writer seguono ora il contratto line-feed esistente di `csv_export::write_row()` / `fputcsv()` per delimitatori a un carattere; valori evento, ordine colonne e delega del controller restano invariati.
- L'inventario XMLDB coincide con tutte le **7** tabelle e con ogni campo dichiarato in `db/install.xml`.
- L'inventario impostazioni coincide con tutte le **57** impostazioni `mod_videotrack`.
- L'inventario servizi AJAX coincide con tutti i **9** servizi dichiarati.
- L'inventario configurazione browser/player coincide con tutte le **133** chiavi, incluse le quattro label seek FW introdotte dopo la 1.6.33.
- Tutti gli otto language pack mantenuti espongono **982** chiavi identiche, nessun duplicato e placeholder Moodle coerenti.
- I link Markdown relativi della documentazione sono stati verificati contro l'albero senza target mancanti.
- I documenti tecnici storici sotto `archive/` restano storici; indici e inventari correnti sono ribasati alla 1.7.72. Gli artefatti interni roadmap/lesson del maintainer non fanno parte del tree distribuito.

## Regole di aggiornamento

I documenti correnti non devono contenere affermazioni di release senza indicare versione/stato. Gli audit 1.4.x sono isolati in `archive/`. Ogni modifica a schema, servizi, File API, configurazione player, report, privacy, accessibilità o traduzioni richiede l’aggiornamento dei documenti numerati pertinenti.

## Audit automatici attesi

Ogni release deve confrontare inventario file/albero, inventario funzioni/sorgente, chiavi e placeholder, `get_string` statici, XMLDB/backup-restore, servizi/classi, sorgenti/build AMD e link Markdown/file esistenti.

## Copertura retention basata sulla cancellazione 1.6.33

- La retention pianificata elimina definitivamente segmenti, interazioni, indicatori e prese visione scaduti; non conserva più pseudonimi deterministici con utente negativo né una chiave di mapping.
- `videotrack_state` è trattato come dato personale derivato, ricostruito dai segmenti validati e dagli input di completamento ancora conservati, e rimosso quando tali input non esistono più.
- I contatori di credito obsoleti vengono azzerati, i guard attivi e limitati vengono preservati e il completamento personalizzato Moodle viene sincronizzato con lo stato ricostruito.
- I backup con dati utente includono soltanto record con utente positivo entro la retention del sito sorgente e omettono lo stato derivato; il restore applica la retention del sito destinazione e ricostruisce lo stato dopo il ripristino della completion del modulo.
- La cancellazione Privacy API elimina i record learner, mentre i file condivisi dell’attività restano governati dal ciclo di vita dell’attività.
- I test di regressione coprono dati vecchi/recenti misti, ricostruzione/rimozione dello stato, guard attivi, rimozione dei pseudonimi legacy e cancellazione utente dopo la retention.

## Copertura registro di riproduzione 1.6.32

- `mod_videotrack_start_playback` stabilisce un timestamp server a credito zero prima dell’apertura di un segmento tracciato.
- Ogni handshake e segmento possiede un identificativo persistente protetto da indice univoco attività/utente/richiesta.
- I retry riutilizzano un risultato identico già salvato e non possono duplicare righe, transizioni di completamento o eventi.
- Il credito è cumulativo, deriva dal tempo server trascorso e da una velocità consentita, con una tolleranza limitata per il clock che resta debito cumulativo fra richieste e handshake.
- I secondi coperti esatti restano monotoni quando la lista compatta raggiunge 500 intervalli.
- XMLDB, upgrade, backup/restore, Privacy API, metadati lingua, sorgenti/build AMD e test di regressione descrivono lo stesso contratto di ledger.

## Copertura contratto runtime e privacy 1.6.31

- La whitelist AJAX client viene confrontata con tutti i servizi AJAX Moodle dichiarati; gli indicatori di integrità non vengono più rifiutati come `invalid-method`.
- Ogni endpoint di mutazione learner verifica il sesskey Moodle prima di caricare contesto del modulo o dati dal database.
- La soppressione privacy di un intervallo si propaga al totale spettatori e alle percentuali di retention che usano quel totale come denominatore.
- Le risposte delle reazioni espongono soltanto campi icona strutturati; il player HTML5 non analizza più il campo HTML grezzo `iconhtml`.
- Le release 1.6.25–1.6.31 sono documentate come code-only rispetto a XMLDB e non introducono savepoint no-op.

## Copertura partecipazione esplicita 1.6.29

- UI attività, servizi di scrittura learner e popolazioni dei report usano `mod/videotrack:participate`.
- L’accesso ai report non disabilita più un partecipante con ruolo multiplo.
- Docenti e manager standard restano non tracciati perché la capability è assegnata all’archetipo Studente e il privilegio amministrativo do-anything viene ignorato per questa decisione.
- Gli amministratori dei ruoli possono assegnare o revocare la capability ai ruoli personalizzati.

## Copertura trasporto configurazione durata 1.6.28

- La configurazione localizzata del detector è conservata in un elemento script JSON nel DOM del form attività.
- `js_call_amd()` riceve soltanto l’id dell’elemento di configurazione, mantenendo l’argomento serializzato molto sotto la soglia Moodle developer di 1024 caratteri.
- Il modulo AMD verifica e analizza la configurazione DOM prima di installare il detector.

## Copertura durata automatica 1.6.27

- Il form docente può proporre la durata dai metadati YouTube, Vimeo o del file locale.
- La proposta è modificabile, annunciata tramite una regione live accessibile e diventa autorevole soltanto quando il docente salva.
- La durata runtime learner non può aggiornare il valore memorizzato; se provider o browser non espongono i metadati, il campo resta manuale.

## Copertura affidabilità strumenti player 1.6.26

- Una durata verificata pari a `0` disabilita esplicitamente percentuale vista e completamento percentuale, mantenendo il tracking degli intervalli validati e gli strumenti di studio abilitati.
- L’intero fieldset dei controlli HTML5 è nascosto per YouTube e Vimeo e ricompare quando la sorgente diventa un file locale.
- I controlli delle reazioni learner restano disponibili in riproduzione e in pausa, ma il server accetta solo timestamp già visualizzati e validati.
- Note e segnalibri abilitati sono mostrati ai partecipanti espliciti; chi non possiede `mod/videotrack:participate` riceve un’anteprima disabilitata e non può creare telemetria learner.
- Le segnalazioni PHPCS rilevate sulla 1.6.25 in `view.php`, `report.php` e `tests/tracker_test.php` sono corrette nell’albero 1.6.26.

## Copertura regressioni interfaccia 1.6.25

- Il renderer Analytics delle prese visione inizializza sia la soppressione dei conteggi sia quella del progresso.
- Le definizioni delle reazioni restano nella sezione principale Reazioni e non sono modellate come un insieme fisso di due elementi.
- L’ordine nella pagina attività è: reazioni, azione Forum opzionale, quindi storico personale delle reazioni/segnalibri.
- Anteprima per chi consulta i report e persistenza learner restano stati di fiducia distinti.

## Copertura persistenza segnalibri 1.6.30

La documentazione corrente registra la parità ripristinata tra il motivo segmento AMD `bookmark` e la whitelist di validazione PHP. Conserva inoltre il contratto di sicurezza secondo cui il timestamp di un segnalibro deve appartenere a progresso visto e validato dal server.

- 1.7.45 adds Moodle Behat/generator documentation and records U-007 as in progress rather than complete.
