# PHPStan, Psalm e PHPMD

L'analisi statica è un percorso di hardening progressivo, non sostituisce i test runtime Moodle. VideoTrack conserva
le configurazioni nel plugin affinché esecuzioni locali, server e GitHub usino lo stesso perimetro di produzione.

## Configurazione e responsabilità

| Strumento | Configurazione | Policy iniziale | Scopo |
|---|---|---|---|
| PHPStan | `phpstan.neon.dist` | livello 1, baseline consultiva | Analisi dei tipi e dei contratti di chiamata. |
| Psalm | `psalm.xml` | error level 8, baseline consultiva | Analisi indipendente di tipi e flusso dati. |
| PHPMD | `phpmd.xml` | finding revisionati consultivi | Complessità, dimensione, design, flag booleani e codice inutilizzato. |
| Moodle PHPCS | `phpcs.xml.dist` | zero warning, bloccante | Stile, naming e regole di compatibilità Moodle. |

PHPStan e Psalm coprono soltanto directory ed entry point di produzione. Escludono test, dipendenze e core Moodle
dalla scansione, ma caricano le definizioni core dal sito installato. PHPMD esclude test, language pack,
documentazione e strumenti nel proprio ruleset. Crash, configurazione invalida o report mancante non sono finding
consultivi: sono errori infrastrutturali.

## Bootstrap Moodle

`tools/static-analysis/bootstrap.php` carica il `config.php` del Moodle installato prima dell'analisi. Usa
`MOODLE_ROOT` quando definita, poi verifica le root deterministiche ricavate dai layout classico `mod/videotrack` e
Moodle `public/mod/videotrack`. Non scandisce l'intero filesystem e non seleziona mirror `.types` generati. Il sito
scelto deve essere già installato e il database deve essere raggiungibile.

## Comando maintainer

Dalla root Moodle, dopo aver sincronizzato l'esatta candidata:

```bash
moodle-test -p mod_videotrack -m 50,53 -c phpstan,phpdoc,phpmd,psalm
```

Il runner usa le configurazioni versionate. PHPStan avanza dal livello 1 all'8 e Psalm dall'error level 8 all'1,
fermandosi al primo livello fallito. PHPMD e PHPDoc usano il reale exit status. Conservare report completo e log di
ogni livello; non riportare soltanto il livello massimo superato.

Per esecuzioni PHPStan/Psalm dirette fuori da un percorso plugin installato, esportare prima la root reale:

```bash
export MOODLE_ROOT=/percorso/assoluto/moodle
vendor/bin/phpstan analyse --configuration=/percorso/assoluto/videotrack/phpstan.neon.dist
vendor/bin/psalm --config=/percorso/assoluto/videotrack/psalm.xml --no-cache
```

## Job baseline GitHub

Il workflow del repository installa le versioni dirette esatte dichiarate in
`.github/static-analysis/composer.json`. PHPStan e Psalm vengono eseguiti dopo l'installazione del sito ordinario
nei job MariaDB Moodle 5.0 e 5.3. Output completi e versioni installate sono conservati in `phpstan.txt`, `psalm.txt`
e `static-tools.txt`. PHPMD viene eseguito nel job qualità canonico Moodle 5.0.

Durante la baseline iniziale, un exit non zero PHPStan/Psalm è accettato come consultivo soltanto se l'output
contiene un riepilogo riconoscibile di analisi completata. Anche l'exit 2 di PHPMD viene convertito in esito
consultivo. Riepiloghi mancanti e ogni altro errore di strumento/configurazione restano bloccanti: un workflow verde
dimostra quindi l'esecuzione degli analizzatori e la presenza di evidenze leggibili anche prima di azzerare i finding.

## Lettura e risanamento dei finding

1. Rifiutare risultati dominati da simboli Moodle irrisolti, percorsi core/vendor o errori di setup.
2. Raggruppare i finding validi per regola e causa comune, registrando i conteggi per file di produzione.
3. Correggere un gruppo circoscritto senza cambiare firme pubbliche Moodle o indebolire validazioni runtime.
4. Rieseguire PHPCS, PHPDoc, lint e i gate PHPUnit/Behat/lifecycle coinvolti dopo ogni tranche di codice.
5. Rieseguire tutti e tre gli analizzatori su Moodle 5.0 e 5.3 e confrontare i report esatti, non solo i totali.
6. Rendere uno strumento bloccante solo con baseline accettata a zero o policy di baseline revisionata e versionata.

Le soppressioni sono eccezionali. Ognuna deve usare la forma più circoscritta supportata, stare accanto al contratto
framework inevitabile, spiegare perché il codice non può cambiare in sicurezza e avere un test di regressione dove
il comportamento è rilevante. Baseline generate, ignore globali e riduzioni del perimetro non devono mai servire
soltanto a ottenere un output verde.

## Stato corrente

L'esecuzione PHPMD generica precedente a questa configurazione aveva rilevato 215 problemi in 42 file e resta solo
un riferimento pre-ruleset. I vecchi output PHPStan/Psalm standalone non risolvevano Moodle e sono baseline
invalide. I primi report GitHub/server 1.7.125 costituiscono quindi l'inizio del risanamento del codice e devono
essere auditati prima di dichiarare corretto qualsiasi finding.
