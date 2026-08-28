# Validazione CLI e benchmark Analytics

VideoTrack distribuisce due strumenti CLI in sola lettura. Eseguirli dalla root Moodle con lo stesso utente di
sistema normalmente usato per la manutenzione CLI.

## Validatore installazione/release

```bash
php mod/videotrack/cli/validate.php
php mod/videotrack/cli/validate.php --json
```

Il validatore controlla:

- versione installata rispetto a `version.php`;
- ramo Moodle supportato;
- tabelle, campi, indici espliciti e indici generati dalle foreign key rispetto a `db/install.xml`;
- rilevamento orfani per tutte le 22 relazioni stabili dichiarate;
- coerenza di ogni `courseid`/`cmid` denormalizzato con attività padre e course module VideoTrack;
- riferimenti condizionali Forum e reazione, rispettando le sentinelle `0` legittime;
- servizi AJAX e metodi external;
- parità chiavi/placeholder degli otto language pack;
- corrispondenza sorgenti/build/source map AMD;
- marker release in README e changelog;
- configurazione selezionata di privacy, prestazioni e tracking.

Lo status di uscita è non zero se un controllo fallisce. I warning vanno analizzati anche senza failure. Lo strumento
non installa, non aggiorna, non scrive impostazioni e non ripara dati.

Per l'evidenza di release usare `--strict`. Nel JSON, il dettaglio `xmldb` separa conteggi di indici espliciti e
foreign key ed elenca oggetti mancanti o orfani. `reference_integrity` riporta incoerenze semantiche di contesto e
riferimenti condizionali. Un failure va indagato partendo da un backup del database: il validatore non ha volutamente
una modalità di riparazione.

## Benchmark Course Analytics

```bash
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> --json
```

`courseid` seleziona un corso esistente. `userid` identifica un utente autorizzato il cui scope Moodle viene usato
dalla query Analytics reale. Il benchmark esegue l'aggregazione batch di produzione per viste all-time e periodo,
nelle varianti attività singola/tutte. Riporta tempo, letture/query/tempo database, righe e rapporto all-vs-naive.

È in sola lettura ma può essere costoso su un corso grande. Eseguirlo in una finestra adeguata, registrare stato di
database/cache e confrontare dataset equivalenti. Un singolo corso non dimostra lo scaling worst-case.

## Gestione delle evidenze

Archiviare l'output JSON con i test di release indicando versioni plugin/Moodle/PHP/database e configurazione critica.
Il validatore completa PHPCS, lint, PHPUnit, Grunt e Behat senza sostituirli. Rieseguire il benchmark quando cambiano
SQL Analytics, indici, scope gruppi/cohort o logica di aggregazione.
