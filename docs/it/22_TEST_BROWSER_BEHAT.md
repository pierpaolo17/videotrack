# Automazione browser con Behat

VideoTrack ha avviato la fase di automazione browser nella release 1.7.45; la 1.7.51 aggiunge contratti PHPUnit di parità seek/interazioni fra provider mantenendo la suite Behat HTML5 post-rollback deterministica introdotta fino alla 1.7.50. Il plugin distribuisce un generator Moodle in `tests/generator/lib.php` e gli scenari browser in `tests/behat/`.

## Scopo

PHPUnit, PHPCS e Grunt sono necessari ma non riproducono timing e callback reali di browser/provider. Behat viene quindi usato per i contratti deterministici della pagina learner che richiedono un browser reale. La matrice specifica dei provider viene aggiunta progressivamente.

## Requisiti

Usare il normale ambiente Behat di Moodle su installazione locale o staging. Non eseguire test browser distruttivi su dati di produzione. Configurare `$CFG->behat_wwwroot`, `$CFG->behat_dataroot` e `$CFG->behat_prefix` secondo la documentazione Moodle, poi inizializzare il sito Behat dalla root Moodle:

```bash
php admin/tool/behat/cli/init.php
```

Dopo aver aggiunto o modificato feature, generator o step Behat, inizializzare nuovamente prima della suite.

## Eseguire gli scenari VideoTrack

Dalla root Moodle:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Per eseguire soltanto lo scenario delle sezioni personali compatte:

```bash
php admin/tool/behat/cli/run.php --name='Active controls stay visible and saved personal history is collapsed by default'
```

## Copertura automatica corrente

La copertura corrente, rafforzata nella 1.7.50:

- crea un'attività VideoTrack tramite `mod_videotrack_generator`;
- abilita reazioni, note personali e segnalibri personali;
- apre l'attività come learner;
- verifica che i pulsanti reazione, il composer nota e il composer segnalibro restino visibili fuori dai `<details>`;
- verifica l'ordine pulsanti reazione → **Le mie reazioni** → form nota → **Le mie note** → form segnalibro → **I miei segnalibri**;
- verifica che le tre cronologie partano chiuse e possano essere aperte indipendentemente.
- aggiunge una regressione ruoli learner/docente/dual-role: i controlli sono attivi per learner e dual-role e read-only per il solo docente.

Il contratto nativo `<details>/<summary>` non richiede JavaScript VideoTrack: le sezioni restano quindi utilizzabili anche da tastiera se un modulo AMD non viene caricato.


### Harness locale deterministica per il seek HTML5

Il generator accetta il campo di solo test `behathtml5fixture=1`. Crea un'attività VideoTrack con sorgente upload usando `tests/fixtures/behat-video.mp4.b64`, una piccola fixture locale di 60 secondi. `tests/behat/behat_mod_videotrack.php` espone step browser che attendono i metadata, eseguono un seek sul media HTML5 e verificano il timestamp risultante.

Per eseguire soltanto la regressione seek HTML5:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack_html5_seek'
```

Le asserzioni deterministiche correnti coprono entrambe le policy: un salto avanti bloccato a 20 secondi deve tornare alla frontier già vista, mentre lo stesso salto resta a 20 secondi quando il seek avanti è consentito. Il test usa l'adapter HTML5 reale e la File API Moodle locale, senza dipendere dalla disponibilità di YouTube o Vimeo. Dalla 1.7.50 il generator accetta anche `behatlinkedforum=<nome>` per risolvere un Forum fixture dello stesso corso usato nello scenario composer post-rollback.

La release 1.7.51 aggiunge anche `tests/provider_seek_snapshot_contract_test.php`: protegge staticamente l’ordine dello snapshot pre-seek e l’uso di timestamp rollback-safe per YouTube, HTML5 e Vimeo. È copertura complementare: non rende complete le harness browser YouTube/Vimeo ancora aperte.

## Limiti correnti della copertura browser

La suite distribuita documenta esplicitamente ciò che non è ancora deterministico. Restano da coprire:

1. harness provider deterministiche YouTube / Vimeo;
2. parity del seek indietro sui provider oltre agli scenari HTML5 correnti sul seek avanti;
3. asserzione end-to-end dell'esatto snapshot del segmento pre-seek persistito prima del salto;
4. scenari browser per resume, completion e alert impilati.

Gli scenari provider dovrebbero evitare dipendenze dalla disponibilità della rete pubblica quando una harness locale deterministica può esercitare lo stesso contratto dell'adapter.

## Evidenze di release

I risultati Behat appartengono all'albero esatto su cui sono stati eseguiti. Registrare versione Moodle, browser/driver, numero scenari e failure. PHPUnit/PHPCS/Grunt verdi non sostituiscono l'automazione browser e un risultato Behat di una release precedente non va attribuito a un tree successivo.

La feature HTML5 può pre-caricare un intervallo di visione validato; nella 1.7.50 lo scenario verifica che reazione, nota, segnalibro e accesso al composer del Forum collegato restino validi dopo un seek avanti bloccato e il conseguente rollback su una posizione già vista. L'asserzione Forum controlla anche che l'URL del composer contenga un timestamp interno all'intervallo già validato.
