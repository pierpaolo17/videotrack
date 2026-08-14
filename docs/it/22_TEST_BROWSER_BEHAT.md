# Automazione browser con Behat

VideoTrack ha avviato la fase di automazione browser nella release 1.7.45; la 1.7.49 estende lo scenario learner deterministico al contratto completo di ordinamento verticale. Il plugin distribuisce un generator Moodle in `tests/generator/lib.php` e gli scenari browser in `tests/behat/`.

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

La copertura corrente, rafforzata nella 1.7.49:

- crea un'attività VideoTrack tramite `mod_videotrack_generator`;
- abilita reazioni, note personali e segnalibri personali;
- apre l'attività come learner;
- verifica che i pulsanti reazione, il composer nota e il composer segnalibro restino visibili fuori dai `<details>`;
- verifica l'ordine pulsanti reazione → **Le mie reazioni** → form nota → **Le mie note** → form segnalibro → **I miei segnalibri**;
- verifica che le tre cronologie partano chiuse e possano essere aperte indipendentemente.
- aggiunge una regressione ruoli learner/docente/dual-role: i controlli sono attivi per learner e dual-role e read-only per il solo docente.

Il contratto nativo `<details>/<summary>` non richiede JavaScript VideoTrack: le sezioni restano quindi utilizzabili anche da tastiera se un modulo AMD non viene caricato.


### Harness locale deterministica per il seek HTML5

Dalla 1.7.49 il generator accetta il campo di solo test `behathtml5fixture=1`. Crea un'attività VideoTrack con sorgente upload usando `tests/fixtures/behat-video.mp4.b64`, una piccola fixture locale di 60 secondi. `tests/behat/behat_mod_videotrack.php` espone step browser che attendono i metadata, eseguono un seek sul media HTML5 e verificano il timestamp risultante.

Per eseguire soltanto la regressione seek HTML5:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack_html5_seek'
```

Le asserzioni deterministiche correnti coprono entrambe le policy: un salto avanti bloccato a 20 secondi deve tornare alla frontier già vista, mentre lo stesso salto resta a 20 secondi quando il seek avanti è consentito. Il test usa l'adapter HTML5 reale e la File API Moodle locale, senza dipendere dalla disponibilità di YouTube o Vimeo.

## Roadmap P2 ancora aperta

U-007 è **in corso**, non chiuso da questo primo scenario. La matrice browser residua deve aggiungere copertura deterministica per:

1. learner / dual-role / teacher;
2. harness provider deterministiche YouTube / Vimeo (la policy seek HTML5 locale è coperta dalla 1.7.49);
3. seek avanti consentito / bloccato e seek indietro;
4. snapshot del segmento al timestamp pre-seek e timestamp di rollback;
5. reazione / nota / segnalibro / Forum subito dopo seek e rollback;
6. resume, completion e alert impilati.

Gli scenari provider dovrebbero evitare dipendenze dalla disponibilità della rete pubblica quando una harness locale deterministica può esercitare lo stesso contratto dell'adapter.

## Evidenze di release

I risultati Behat appartengono all'albero esatto su cui sono stati eseguiti. Registrare versione Moodle, browser/driver, numero scenari e failure. PHPUnit/PHPCS/Grunt verdi non sostituiscono l'automazione browser e un risultato Behat di una release precedente non va attribuito a un tree successivo.

Dalla 1.7.49 la feature HTML5 può anche pre-caricare un intervallo di visione validato e verifica che reazione, nota e segnalibro restino salvabili dopo un seek avanti bloccato e il conseguente rollback su una posizione già vista. In questo modo avanza la matrice post-seek/rollback senza dipendere da provider pubblici.
