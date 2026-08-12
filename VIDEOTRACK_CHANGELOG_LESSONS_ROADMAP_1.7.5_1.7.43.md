# VideoTrack — Changelog, lesson learned e roadmap pre-produzione

**Intervallo coperto:** `1.7.5` → `1.7.43`
**Baseline documentale candidata:** VideoTrack `1.7.43` (`2026081204`)
**Data consolidamento:** 2026-08-12

## 1. Regola di lettura

Questo documento consolida la cronologia effettiva del ramo 1.7.x a partire dalla 1.7.5, le lesson learned emerse dai test automatici e dai test browser del maintainer e la roadmap residua prima/dopo il rilascio in produzione.

La baseline tecnica resta sempre l'ultimo ZIP reale auditato. Le verifiche riportate per release precedenti sono evidenze storiche e non vengono automaticamente attribuite alla 1.7.43. Per la candidata 1.7.43 PHPUnit, PHPCS Extra e test browser devono essere eseguiti nuovamente dopo l'applicazione della patch.

## 2. Changelog consolidato 1.7.5 → 1.7.43

### 1.7.5 — report per studente e ricalcolo

- Allineata la modalità “Per studente” alla modalità cumulativa per le reazioni e il replay del frammento.
- Corretto il ricalcolo bloccato dall'uso di `ModalSaveCancel::setCancelButtonText()`, non disponibile nell'API Moodle 5.0 reale.
- Lesson: le API JavaScript Moodle vanno verificate sulla release target reale; i rami UI equivalenti richiedono test di parity.

### 1.7.6 — hardening test anti-spam reazioni

- Nessun cambio runtime alle regole anti-spam già presenti.
- Aggiunta copertura per duplicati, finestra temporale, stesso secondo, burst limit, lock concorrente e indipendenza dal `sessionid`.
- Lesson: enforcement affidabile sul server, debounce client solo UX.

### 1.7.7–1.7.11 — Analytics/export e learner scope

- Portata in parità la semantica tra Analytics, export e learner scope.
- Aggiunto export aggregato dei bookmark senza esporre etichette/timestamp privati.
- Rafforzata la validazione della velocità di playback e la persistenza del segmento prima delle interazioni.
- Corretti timestamp note/bookmark e frontier dopo seek FW bloccati.

### 1.7.12–1.7.14 — partecipazione, doppio ruolo e Forum

- Centralizzata la partecipazione learner separandola dall'accesso report.
- Preservato il voto personale del learner con doppio ruolo.
- Il timestamp Forum learner deve riferirsi a una posizione server-validata; i report viewer hanno bypass esplicito.
- Il progresso viene flushato prima dell'apertura del composer Forum.

### 1.7.15–1.7.21 — provider loader e recovery Vimeo

- Resi retry-safe i loader YouTube/Vimeo.
- Eliminata la manipolazione globale di `window.define`.
- Iterata la recovery Vimeo sulla base di evidenza browser; la state machine è stata poi semplificata nella 1.7.22.

### 1.7.22 — Vimeo blocked-seek state machine

- Ridotto il recovery a un rollback seguito da retry della sola riproduzione.
- Evitato che pause transitorie annullassero l'handshake.
- Pulito subito lo stato di guard dopo rollback riuscito.

### 1.7.23 — hygiene, context e changelog canonico

- Aggiunti context espliciti ai `format_string()` CSV.
- Corretto `environment.xml` italiano in UTF-8 nativo.
- Introdotto `CHANGELOG.md` root canonico e alleggeriti i README.

### 1.7.24–1.7.27 — performance Analytics U-016

- Selezione attività docente resa leggera tramite modinfo/capability.
- Precaricato lo stato gruppi e batchati eventi, stati e segmenti su gruppi di attività.
- Eliminati i read DB espliciti per-attività nel loop aggregato.
- Aggiunti contract test sul modello query; resta utile un benchmark con dataset reale grande.

### 1.7.28–1.7.30 — completamento Moodle 5.0

- Allineati suffissi dei campi completion e contratto custom completion Moodle.
- Centralizzata l'attivazione/descrizione delle condizioni e ricalcolo stato su modifiche di configurazione.
- Integrate reazioni e logica AND/OR nella sezione standard Activity completion.
- Corretta la resa dei dettagli completion nell'header Moodle 5.0.

### 1.7.31 — gradebook restore/repair

- Rimossa la doppia creazione del grade item nel restore del modulo.
- Aggiunta recovery DML-only dei grade item duplicati con migrazione non conflittuale dei voti.

### 1.7.32 — semantica OR e interazioni dopo seek consentito

- Ripristinata la semantica `completionlogic=or` come alternativa fra condizioni VideoTrack abilitate.
- Aggiunto il fallback session-aware per reaction/note/bookmark immediatamente dopo seek FW consentito.
- Lesson successiva: il fallback sulle interazioni non sostituisce una segmentazione seek corretta nel player.

### 1.7.33–1.7.35 — hygiene e presentazione completion/privacy

- Corretto PSR-12 nel guard timestamp.
- Stabilizzati test gradebook senza warning generati dal fixture.
- Unificati gli avvisi provider privacy + focus/integrity.
- Migliorati contrasto, wrapping e bordi dei badge completion Moodle.

### 1.7.36 — prima remediation Forum/percentuale/alert

- Passato `sessionid` al composer Forum e applicata policy timestamp session-aware.
- Rimossa la doppia `×` dagli alert temporanei.
- Introdotta protezione della percentuale UI e messaggio per seek FW bloccato.
- I test statici passavano in parte, ma il runtime mostrava ancora Forum/interazioni non salvabili: la causa era più a monte.

### 1.7.37 — resume frontier e wall-clock

- Limitato il resume alla frontier server-validata quando il seek FW è vietato.
- Preservato il wall-clock originale alla chiusura del segmento.
- Corrette hygiene PSR-12 e release documentation.
- Lesson: un payload può essere formalmente valido ma rappresentare una sessione già aperta oltre la frontier autorizzata.

### 1.7.38 — correzione strutturale della segmentazione seek

- Recuperato da Vimeo il contratto corretto: il segmento termina alla posizione pre-seek realmente vista e un nuovo segmento si apre alla destinazione.
- Applicato lo stesso principio a YouTube e HTML5, inclusi rewind/FF/replay/capitoli/resume programmatici.
- Durante rollback di un seek vietato, Forum/reaction/note/bookmark usano il timestamp affidabile pre-rollback.
- Mantenuti intatti ledger server-authoritative 1.6.23 e guard successivi: è stato corretto il client invece di indebolire il server.

### 1.7.39 — alert policy seek FW e resume notice

- Close del resume notice riportato nel normale flusso flex.
- Aggiunto alert persistente quando il docente disabilita seek FW.
- Se la velocità recovery configurata è diversa da 1×, viene mostrata nell'alert; il messaggio runtime mostra la velocità effettivamente applicata.
- Aggiornato il contract test seek alla semantica pre-seek della 1.7.38.

### 1.7.40 — alert provider/privacy/focus

- Anche l'alert combinato provider privacy + focus/integrity usa il close compatto in-flow, senza `alert-dismissible` assoluto.
- Nessuna modifica a player/tracking/interazioni.

### 1.7.41 — consolidamento pre-produzione

- Unificate nella sezione Activity completion le due intestazioni “Logica delle reazioni” e “Completamento tramite reazioni” in una sola etichetta: **“Completamento tramite reazioni e percentuale visualizzazione”**.
- Select AND/OR e testo esplicativo restano invariati.
- Completati i sei language pack che avevano 9 chiavi recenti mancanti; tutti gli otto pack hanno ora **977 chiavi**, nessun duplicato e placeholder Moodle in parità.
- Verificati e riallineati indici, inventari e audit della documentazione alla baseline 1.7.41.
- Aggiunti contract test per parità delle lingue e intestazione completion unica.
- Nessuna modifica a runtime player, tracking, Forum, reaction/note/bookmark, Analytics, schema o upgrade.


### 1.7.42 — bookmark dopo seek indietro e alert transienti

- Un bookmark privato può essere salvato su una posizione già coperta da progresso server-validato anche dopo un seek indietro; i timestamp mai visti restano rifiutati.
- La validazione bookmark verifica prima l'evidenza vista persistita in qualunque sessione ammessa dalla finestra storica, quindi mantiene il fallback session-aware solo per una posizione appena raggiunta tramite seek FW consentito.
- Gli alert transienti del player, inclusi gli errori, usano lo stesso close compatto nel normale flusso flex di resume, privacy/provider e policy seek, senza `alert-dismissible` assoluto.
- Rimossi i due trailing whitespace noti presenti nell'intestazione del documento consolidato 1.7.41.

### 1.7.43 — chiarezza retention privacy e hygiene bookmark

- Il grafico “Retention lungo la timeline” mostra ora un messaggio esplicito quando l’intera serie è nascosta dalla soglia privacy, mantenendo invariata la soppressione dei dati.
- La descrizione accessibile dell’SVG include la stessa informazione quando non esistono punti di retention visualizzabili.
- Corretto l’unico errore PHPCS fixable emerso nella 1.7.42 in `tests/save_bookmark_test.php`; gli 8 warning noti sui backtick restano backlog separato.
- Lesson: un comportamento privacy corretto deve essere anche esplicito nella UI; un grafico vuoto senza spiegazione è ambiguo pur con dati correttamente mascherati.

## 3. Lesson learned 1.7.5 → 1.7.43

### LL-01 — Baseline reale prima di tutto

Nome release, patch precedente e stato server non sono prova del codice in esecuzione. Ogni modifica parte dall'ultimo ZIP reale e viene riapplicata su una copia pulita.

### LL-02 — Static checks verdi non equivalgono a runtime corretto

Le regressioni Modal API, Forum/seek e segmentazione 1.7.36–1.7.38 hanno dimostrato che PHP lint, PHPCS, PHPUnit e Grunt possono non riprodurre callback/provider/browser reali.

### LL-03 — Non allentare il server quando il client descrive male il tempo visto

Il ledger server-authoritative ha correttamente rifiutato segmenti che attraversavano un seek. La soluzione corretta è stata chiudere il segmento alla posizione pre-seek, non rendere permissivo `suspicioussegment`.

### LL-04 — Seek, resume e replay sono transizioni di segmento

Ogni cambio di posizione deve distinguere il tratto realmente riprodotto dal salto. Il nuovo segmento nasce alla destinazione; il gap non produce credito.

### LL-05 — Il wall-clock fa parte del contratto di fiducia

`wallclockstart` deve sopravvivere fino al payload di chiusura. Azzerarlo prima della richiesta rende impossibile al server confrontare tempo video e tempo reale.

### LL-06 — Le interazioni durante rollback devono usare un timestamp affidabile

Forum, reaction, note e bookmark non devono leggere la posizione provider transitoria del seek vietato mentre il player sta tornando alla frontier.

### LL-07 — I player condividono il contratto, non necessariamente l'implementazione

Vimeo ha fornito il comportamento seek di riferimento, ma YouTube/HTML5 hanno callback e percorsi programmatici diversi. La parity va verificata per semantica, non per codice identico.

### LL-08 — Un contract test può diventare obsoleto

Nella 1.7.38 un test pretendeva una specifica stringa/implementazione precedente pur essendo cambiato correttamente il contratto. I test proteggono il comportamento desiderato, non una forma accidentale del codice.

### LL-09 — Alert compatti e Bootstrap dismissible non sono sempre compatibili

`alert-dismissible` usa un close assoluto pensato per layout Bootstrap standard. Negli alert VideoTrack impilati e compatti è più sicuro un layout flex con pulsante nel flusso.

### LL-10 — Lingue: parità di chiavi + placeholder, non solo conteggio

Sei language pack erano rimasti a 968 chiavi mentre EN/IT erano a 977. Da 1.7.41 il contratto deve confrontare set di chiavi, duplicati e placeholder `{$a}`/`{$a->...}`.

### LL-11 — Documentazione “corrente” deve essere realmente corrente

Alla 1.7.40 gli indici dichiaravano ancora 1.6.36 e l'inventario file 1.6.33. I documenti storici possono restare versionati; indici e inventari dichiarati correnti devono essere rigenerati o esplicitamente marcati come snapshot.

### LL-12 — Roadmap e cronologia restano separate

Gli imprevisti di upgrade, gradebook e runtime player hanno cambiato più volte l'ordine delle release. La roadmap indica priorità future, non prova che un finding sia chiuso solo perché il numero di release è stato superato.

### LL-13 — Privacy corretta ma invisibile è comunque un problema UX

Quando la soglia privacy sopprime tutti i valori di retention, lasciare solo assi e griglia fa sembrare il grafico rotto. La UI deve dichiarare esplicitamente che la serie è stata nascosta, senza esporre valori o denominatori ricostruibili.

## 4. Stato roadmap sulla baseline 1.7.43

| Finding/area | Stato 1.7.43 | Evidenza / residuo |
|---|---|---|
| U-007 browser/Behat | **APERTO** | Nel tree non esiste ancora `tests/behat`; i bug 1.7.36–1.7.38 dimostrano il valore di test browser automatici. |
| U-011 doppio ruolo/voto | **CHIUSO codice+test** | Learner scope indipendente dall'accesso report e test dedicati sul voto/partecipazione. |
| U-012 contratto partecipazione | **CHIUSO codice+test** | `learner_scope::can_participate()` è usato da view, Web Service e Forum con test di delega. |
| U-013 Analytics/export parity | **CHIUSO implementazione** | Serie 1.7.7–1.7.11 e contract export/report. Gate dataset reale consigliato. |
| U-014 privacy EN/IT | **IMPLEMENTATO / GATE SEMANTICO** | Struttura parallela testata; resta utile revisione semantica finale pre-produzione. |
| U-015 provider loader retry-safe | **CHIUSO codice+test** | Promise azzerate dopo reject e niente manipolazione globale `window.define`. |
| U-016 performance Analytics | **CHIUSO implementazione / BENCHMARK PENDENTE** | Batch e modello query coperti da test; manca benchmark su dataset reale grande. |
| U-017 manutenibilità file grandi | **APERTO** | `report.php`, `mod_form.php`, `vimeo_player.js`, `html5_player.js` restano molto grandi. |
| U-018 `format_string` context | **CHIUSO area originaria** | CSV export ha context espliciti e contract test; ulteriori chiamate vanno mantenute sotto audit. |
| U-020 WCAG capitoli | **PARZIALE** | forced-colors globale presente; manca un focus-visible dedicato ai chapter button e una matrice manuale completa. |
| U-022 Moodle 5.0–5.3 | **APERTO** | `$plugin->supported = [500, 503]`, ma l'evidenza reale corrente è Moodle 5.0.9. Testare 5.1/5.2/5.3 o restringere il range. |
| U-023 learner scope duplicato | **CHIUSO codice+test** | `course_analytics` riusa lo scope canonico; contract impedisce la reintroduzione dell'helper privato. |
| U-028 changelog root | **CHIUSO** | `CHANGELOG.md` canonico presente dalla 1.7.23. |
| U-029 i18n italiano | **CHIUSO** | `environment.xml` UTF-8 corretto; 1.7.41 riallinea tutti gli otto pack a 977 chiavi. |
| U-030 Forum timestamp watched | **CHIUSO codice+runtime remediation** | Guard server presente; 1.7.38 corregge il confine client che produceva falsi rifiuti. |
| U-031 PHPUnit 12 | **DEFERRED / APERTO** | Restano le 20 deprecazioni DocBlock note finché Moodle CS/toolchain target non consente migrazione coerente agli attributes. |

## 5. Roadmap futura consigliata

### Fase P0 — gate di produzione della 1.7.43

1. Eseguire PHPUnit e PHPCS Extra sulla 1.7.43 reale.
2. Se nessun `amd/src/*` è cambiato, verificare che `amd/build` sia identico alla baseline; non rigenerare AMD inutilmente.
3. Test browser manuale almeno su HTML5, YouTube e Vimeo per: play/pause, resume, RW, seek FW consentito/vietato, reaction, note, bookmark, Forum, completion e alert impilati.
4. Verificare Privacy API/retention e backup/restore su un corso di prova prima del deploy definitivo.
5. Deploy pulito della directory plugin e purge cache Moodle/browser.

### Fase P1 — script CLI di validazione/diagnostica

Riprendere lo script CLI previsto dopo la stabilizzazione runtime. Deve essere non distruttivo per default e produrre un report ripetibile su versione/schema, tabelle/indici, servizi, language contract, AMD src/build, configurazione critica e finding pre-produzione. Qualunque modalità di repair deve essere separata, esplicita e idempotente.

### Fase P2 — browser automation / U-007

- Introdurre scenari Behat o una harness browser compatibile con Moodle per i flussi che i contract test non hanno intercettato.
- Matrice minima: ruolo learner/dual-role/teacher × HTML5/YouTube/Vimeo × seek consentito/vietato × interazioni.
- Aggiungere regressioni per pre-seek segment snapshot e rollback timestamp.

### Fase P3 — compatibilità Moodle / U-022

- Test reale su Moodle 5.1, 5.2 e 5.3 con la stessa suite.
- Se una versione non è validabile, restringere `$plugin->supported` invece di dichiarare supporto non provato.

### Fase P4 — WCAG e lingue

- Completare focus-visible/forced-colors dei chapter button e audit keyboard/screen-reader.
- Far revisionare semanticamente i sei pack aggiornati da persone competenti/native quando possibile; il contract automatico garantisce struttura e placeholder, non qualità linguistica assoluta.

### Fase P5 — performance/manutenibilità

- Benchmark Analytics con dataset reale grande per chiudere definitivamente U-016.
- Ridurre per piccoli passi i file più grandi (U-017), mantenendo test di regressione e senza rifattorizzazioni simultanee ai bug runtime.

### Fase P6 — PHPUnit 12 / U-031

- Migrare i metadata `@covers` ad attributes soltanto quando Moodle CS e la toolchain delle versioni supportate accettano lo stesso contratto senza perdere PHPCS Extra.

## 6. Criterio per dichiarare “production ready”

La 1.7.43 può diventare baseline di produzione soltanto dopo esito reale del gate P0. Il fatto che una release sia `MATURITY_STABLE`, che compili o che i test di una release precedente siano verdi non sostituisce la verifica sulla build esatta da distribuire.
