# Validazione CLI e benchmark Analytics

VideoTrack distribuisce tre strumenti CLI in sola lettura. Eseguirli dalla root Moodle con lo stesso utente di
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
- conteggio grade item VideoTrack e voti attivi, unicità dell'item canonico e risoluzione dei contesti
  attività/corso/course module;
- servizi AJAX e metodi external;
- parità chiavi/placeholder degli otto language pack;
- corrispondenza sorgenti/build/source map AMD, compresi mapping non vuoti e identità dei sorgenti incorporati;
- marker release in README e changelog;
- configurazione selezionata di privacy, prestazioni e tracking.

Lo status di uscita è non zero se un controllo fallisce. I warning vanno analizzati anche senza failure. Lo strumento
non installa, non aggiorna, non scrive impostazioni e non ripara dati.

Per l'evidenza di release usare `--strict`. Nel JSON, il dettaglio `xmldb` separa conteggi di indici espliciti e
foreign key ed elenca oggetti mancanti o orfani. `reference_integrity` riporta incoerenze semantiche di contesto e
riferimenti condizionali. `gradebook_integrity` riporta contesti invalidi e item canonici duplicati e deve risultare
PASS prima della disinstallazione. Un failure va indagato partendo da un backup del database: il validatore non ha
volutamente una modalità di riparazione.

## Benchmark Course Analytics

```bash
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> \
    --activityid=<instanceid> --minusers=5 --runs=10 --perioddays=7 > benchmark.json
```

Il benchmark emette sempre JSON; `--json` appartiene al validatore e non e una opzione del benchmark.
Usare il percorso del plugin installato: il layout `public/` puo collocarlo in `public/mod/videotrack/`.

`courseid` seleziona un corso esistente. `userid` seleziona un viewer attivo autorizzato. Prima di selezionare
le attivita o misurare le query, il CLI carica il record completo dell'utente e lo imposta come utente corrente
in memoria nel processo Moodle. Il JSON registra `userid` e `current_userid`. Questo allinea il contesto dei
controlli capability al viewer scelto; non cambia ruoli, iscrizioni, impostazioni o dati degli studenti. Resta una
misura CLI, non del caricamento della pagina nel browser.

`activityid` e l'ID interno VideoTrack, non il course-module dell'URL `view.php?id=...`. Se omesso, lo strumento
preferisce l'attivita con piu stati. I quattro scenari combinano all-time/periodo e attivita singola/tutte.
`runs` ha default 5 e ammette1..50; `perioddays` ha default 7. La soglia privacy `minusers` ha default indipendente 2:
passare esplicitamente il valore del sito per un confronto corrispondente. L'esempio usa5 senza cambiare
l'impostazione del sito. `groupid` restringe facoltativamente lo scope gruppi gia autorizzato.

Ogni scenario riporta raw run, min/mediana/max di tempo totale, letture/query/tempo DB e numero di attivita
restituite. `all_vs_naive_ratio` confronta le letture di tutte le attivita con la stima aritmetica delle letture
singole moltiplicate per le attivita configurate. Verificare righe restituite, attivita configurate e densita dei
dati prima di interpretarla: non e una misura di chiamate singole ripetute realmente.

### Evidenza Moodle 5.3 e limiti del campione

Il 2026-11-13 il maintainer ha fornito diagnostica reale della baseline 1.7.254: 42 attivita visibili, 28 stati
sintetici target, 28776 segmenti target e 14388 segmenti target nei sette giorni. Altre attivita contribuiscono 21
stati all-time ulteriori. Il profiler separato ha usato soglia 5 e 10 run per scenario. Il replay dei componenti
pubblici coincide con riepiloghi, conteggi eventi e permessi canonici in tutti i 40run. Anche il passaggio
controllato dall'utente corrente CLI0 al viewer 10 conserva tutti i 40 risultati, mentre le query passano9-a-6
per l'attivita e218-a-92 per il corso. Il contesto CLI riferito a un altro utente aggiunge 126 query in questa
misura del corso; non era stata applicata una ottimizzazione degli Analytics di produzione.

Con il viewer corrente, il replay del corso usa 86 query per gli scope, 3 per i conteggi eventi, 3 per stati o
segmenti e 0 per i flag permessi finali. La sorgente risolve ancora due scope SQL studenti per attivita: questi
dati non dimostrano un numero totale costante di query. La lettura dei 14388 segmenti del periodo e il calcolo
dei riepiloghi rimangono un costo distinto dalla preparazione dei permessi.

Questi risultati verificano il confronto diagnostico sulla 254, non l'esecuzione nativa della candidata 255.
Ripetere il CLI distribuito con la255 dopo l'upgrade e conservare il JSON con `current_userid`. Il profiler
esegue un warm-up esplicito; le fasi del replay sono esecuzioni separate e le loro mediane non si sommano come
parti strumentate del totale canonico. Warm-up e prima esecuzione piu lenta non provano una cache fredda.

Il benchmark e in sola lettura ma puo essere costoso su corsi grandi. Registrare stato di database/cache e
carico del server, confrontare dataset equivalenti e conservare il JSON originale. Il campione non rappresenta 42
attivita tutte densamente popolate, carico concorrente, accessibilita o una validazione end-to-end del player.
Il dataset sintetico conservato e utilizzabile per screenshot; l'accessibilita manuale Moodle 5.3 resta in standby.

## Gestione delle evidenze

Archiviare l'output JSON con i test di release indicando versioni plugin/Moodle/PHP/database e configurazione critica.
Il validatore completa PHPCS, lint, PHPUnit, Grunt e Behat senza sostituirli. Rieseguire il benchmark quando cambiano
SQL Analytics, indici, scope gruppi/cohort o logica di aggregazione.

## Diagnostica delle connessioni ai provider

Questa candidata aggiunge il riferimento al servizio OAuth Microsoft nelle impostazioni sito (predefinito: Nessuno), un servizio di verifica locale in sola lettura e provider_status.php. Le credenziali restano gestite dal core. I controlli verificano tipo provider, abilitazione/uso per servizi, configurazione client, HTTPS degli endpoint/sito e stato locale dell’account di sistema/scopi core. Non scambiano o rinnovano token e non contattano Microsoft. OneDrive/SharePoint non è ancora una sorgente video selezionabile.

```bash
php mod/videotrack/cli/provider_status.php
php mod/videotrack/cli/provider_status.php --provider=microsoft --strict
```

L’output è sempre JSON. Lo stato è not_configured, issuer_missing, configuration_incomplete o configuration_complete. Non vengono emessi segreti, identificatori account o richieste HTTP ai provider. L’uscita normale è0; --strict termina con2 se la configurazione locale non è completa. Provider/opzioni sconosciuti terminano con2. L’account memorizzato viene confrontato con gli scopi richiesti dal core, senza verificare consenso Microsoft o accesso a un file remoto. network_checked=false e playback_implemented=false restano espliciti anche con configurazione locale completa. La configurazione facoltativa non è richiesta per superare cli/validate.php. Il sito di test richiede HTTPS prima di una vera callback OAuth Microsoft.
