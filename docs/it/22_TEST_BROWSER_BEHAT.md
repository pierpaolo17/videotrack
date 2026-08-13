# Automazione browser con Behat

VideoTrack avvia la fase di automazione browser nella release 1.7.45. Il plugin distribuisce un generator Moodle in `tests/generator/lib.php` e gli scenari browser in `tests/behat/`.

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
php admin/tool/behat/cli/run.php --name='Personal sections are collapsed by default and can be opened independently'
```

## Copertura automatica corrente

La 1.7.45 introduce la prima regressione browser deterministica:

- crea un'attività VideoTrack tramite `mod_videotrack_generator`;
- abilita reazioni, note personali e segnalibri personali;
- apre l'attività come learner;
- verifica che **Le mie reazioni**, **Le mie note** e **I miei segnalibri** siano tre sezioni native `<details>` distinte;
- verifica che tutte partano chiuse e possano essere aperte indipendentemente.

Il contratto nativo `<details>/<summary>` non richiede JavaScript VideoTrack: le sezioni restano quindi utilizzabili anche da tastiera se un modulo AMD non viene caricato.

## Roadmap P2 ancora aperta

U-007 è **in corso**, non chiuso da questo primo scenario. La matrice browser residua deve aggiungere copertura deterministica per:

1. learner / dual-role / teacher;
2. HTML5 / YouTube / Vimeo;
3. seek avanti consentito / bloccato e seek indietro;
4. snapshot del segmento al timestamp pre-seek e timestamp di rollback;
5. reazione / nota / segnalibro / Forum subito dopo seek e rollback;
6. resume, completion e alert impilati.

Gli scenari provider dovrebbero evitare dipendenze dalla disponibilità della rete pubblica quando una harness locale deterministica può esercitare lo stesso contratto dell'adapter.

## Evidenze di release

I risultati Behat appartengono all'albero esatto su cui sono stati eseguiti. Registrare versione Moodle, browser/driver, numero scenari e failure. PHPUnit/PHPCS/Grunt verdi non sostituiscono l'automazione browser e un risultato Behat di una release precedente non va attribuito a un tree successivo.
