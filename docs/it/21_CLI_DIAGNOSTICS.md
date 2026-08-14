# Validazione CLI e benchmark Analytics

VideoTrack distribuisce due strumenti CLI in sola lettura per amministrazione locale/staging e manutenzione futura. Si trovano in `mod/videotrack/cli/` e caricano la normale configurazione Moodle, quindi vanno eseguiti su un'installazione Moodle in cui il plugin è installato.

## Modello di sicurezza

Entrambi i comandi sono esclusivamente diagnostici. Non inseriscono, aggiornano o cancellano record Moodle e non espongono una modalità repair. Vanno eseguiti con un account di sistema che possa leggere codice/configurazione Moodle e collegarsi al database configurato. Quando sono usati per un deploy o un confronto prestazionale, conservare l'output insieme alle evidenze della release.

## Validatore installazione/release

```bash
php mod/videotrack/cli/validate.php
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/validate.php --verbose
php mod/videotrack/cli/validate.php --strict
```

Il validatore controlla:

- release/versione dei file rispetto alla versione installata in `config_plugins`;
- ramo Moodle corrente rispetto a `$plugin->supported`;
- tabelle, campi e indici dichiarati in `db/install.xml` rispetto al database reale;
- funzioni AJAX dichiarate in `db/services.php` e relative classi/metodi external;
- parità di chiavi e placeholder fra i language pack mantenuti;
- ogni sorgente AMD rispetto a build minificata e `sourcesContent` della source map;
- marker di release pubblici correnti in README e CHANGELOG;
- valori selezionati di configurazione privacy, tracking e performance.

Il codice di uscita è `0` se non esistono failure. I warning non rendono fallito il comando salvo uso di `--strict`. Per archiviare evidenze di release è consigliato `--json`.

Il validatore integra ma non sostituisce PHPUnit, PHPCS, Grunt, test browser runtime, verifiche Privacy API o backup/restore.

## Benchmark Course Analytics

```bash
php mod/videotrack/cli/benchmark_course_analytics.php \
    --courseid=4 \
    --userid=2 \
    --minusers=2 \
    --runs=5 \
    --perioddays=7
```

Parametri opzionali:

- `--activityid=<id>` sceglie l'istanza VideoTrack usata nei due scenari a singola attività. Se omesso, il comando preferisce l'attività con più righe `videotrack_state`, poi l'id più basso.
- `--groupid=<id>` applica lo stesso filtro di gruppo accessibile usato dalla dashboard di corso.
- `--minusers=<n>` imposta la soglia privacy Analytics del benchmark; minimo `2`.
- `--runs=<n>` controlla le ripetizioni per scenario, da `1` a `50`.
- `--perioddays=<n>` controlla gli scenari sul periodo recente, da `1` a `3650` giorni.

L'output JSON contiene quattro scenari:

1. `all_time_single` — una attività, tutto lo storico;
2. `all_time_all` — tutte le attività VideoTrack visibili, tutto lo storico;
3. `period_single` — una attività nel periodo richiesto;
4. `period_all` — tutte le attività visibili nel periodo richiesto.

Ogni run registra numero righe restituite, read database, query totali, tempo DB e wall-clock. La sezione `scaling` confronta i read reali dello scenario completo con la stima ingenua `singola attività × numero attività configurate`.

Il benchmark è un'evidenza comparativa, non una soglia universale pass/fail. Densità del dataset, gruppi/capability, cache DB, cache Moodle e carico dell'host influenzano i tempi. Conservare il JSON completo insieme alle versioni Moodle/PHP/DB e annotare se tutte le attività configurate contengono realmente log learner.

## Benchmark maintainer registrato per U-016

Un'esecuzione maintainer su Moodle 5.0.9 / PHP 8.2.32 / DB famiglia MySQL ha usato un corso reale con 40 attività VideoTrack configurate, non tutte popolate da log learner, cinque run per scenario e periodo di sette giorni. Lo scenario all-time su tutte le attività ha registrato mediana 86 read/query, 24,602 ms DB e 37,913 ms wall; lo scenario sette giorni su tutte le attività ha registrato gli stessi 86 read/query mediani, 29,896 ms DB e 46,645 ms wall. Il rapporto read all-vs-naive è 0,3583 in entrambi i confronti.

Questo chiude il gate di benchmark su dataset reale originariamente previsto da U-016 per il corso osservato. Non equivale a dichiarare worst-case la scalabilità di un corso sintetico in cui tutte le attività contengono una grande storia learner; fixture di stress restano un'evidenza futura utile, non un blocco per la baseline registrata.

## Workflow di manutenzione consigliato

Prima di una release archiviare l'output di `validate.php --json` insieme alle evidenze PHPUnit/PHPCS/Grunt. Rieseguire il benchmark Course Analytics quando cambiano `classes/local/course_analytics.php`, learner scope, group scope, forma SQL Analytics o indici pertinenti, confrontando quando possibile i risultati con una run precedente sullo stesso dataset/ambiente.
