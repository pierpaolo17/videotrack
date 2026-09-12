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
Moodle `public/mod/videotrack`. Carica le API stabili di upgrade, CLI, gruppi, API esterna Forum, amministrazione e
form, seguite dai grafi di include canonici di backup e restore Moodle prima delle librerie
step Moodle 2 usate dal perimetro di produzione. L'ordine è necessario perché le librerie step dichiarano
immediatamente le sottoclassi. Questo bootstrap esplicito serve perché gli analizzatori non seguono in modo
affidabile ogni `require` legacy basata su variabili. Non scandisce l'intero filesystem e non seleziona mirror
`.types` generati. Il sito scelto deve essere già installato e il database deve essere raggiungibile.

`psalm.xml` carica anche `tools/static-analysis/moodle-legacy-aliases.phpstub`. Lo stub dichiara staticamente il nome
globale di compatibilità `renderable` come estensione di `core\output\renderable`. Rappresenta così l'alias runtime
di Moodle 5.0 in una forma indicizzabile da Psalm: gli stub vengono analizzati per le dichiarazioni, mentre una
chiamata eseguibile a `class_alias()` non è efficace.

La policy opzionale Psalm `ensureOverrideAttribute` è disabilitata esplicitamente. L'attributo nativo
`#[\Override]` è disponibile da PHP 8.3, mentre la matrice Moodle 5.0 supportata include PHP 8.2. L'analisi di
ereditarietà e firme resta attiva; viene disabilitata soltanto la regola stilistica incompatibile sulla presenza
dell'attributo.

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

Durante la baseline iniziale, l'exit 2 di Psalm viene accettato come esito documentato di analisi completata con
finding; l'exit 1 e ogni altro stato non zero restano errori bloccanti. L'exit 1 di PHPStan è consultivo soltanto
quando è presente il normale riepilogo numerico e non compare alcun indicatore di errore interno o analisi
incompleta. Anche l'exit 2 di PHPMD viene convertito in esito consultivo. I finding restano quindi visibili senza
accettare crash o report parziali come evidenze valide.

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
invalide. I primi report GitHub 1.7.125 sono rifiutati come baseline di risanamento. Su Moodle 5.0 PHPStan ha
segnalato classi parent legacy irrisolte e Psalm si è interrotto con un errore interno sullo storage di `renderable`;
Moodle PHPCS ha inoltre rilevato lo stato globale intenzionale del bootstrap eseguito prima del caricamento di
Moodle. Su Moodle 5.3 Psalm ha completato l'analisi con finding, ma il workflow ha classificato erroneamente il suo
exit 2 documentato come errore dello strumento. La release 1.7.126 ha corretto PHPCS e classificazione degli exit
code, ma il suo bootstrap degli analizzatori caricava `backup_stepslib.php` prima di `backup_execution_step` e i due
job statici si sono fermati all'avvio. La release 1.7.127 ha poi consentito a PHPStan di completare con 366 finding
su entrambi i rami e a Psalm di completare con 468 finding su Moodle 5.3. Psalm 6.16.1 si interrompeva ancora su
Moodle 5.0 perché l'alias runtime `renderable` di Moodle non aveva uno storage Psalm. La release 1.7.128 aveva
inserito la mappatura eseguibile `class_alias()` in uno stub configurato, ma il report 1.7.128 ha dimostrato che
Psalm non elabora lì quella istruzione come dichiarazione di classe e ha ripetuto la stessa eccezione. La release
1.7.129 la sostituisce con una dichiarazione statica dell'interfaccia globale, indicizzabile da Psalm. La matrice
1.7.129 si è quindi conclusa correttamente: PHPStan ha rilevato 366 finding e Psalm 468 finding sia su Moodle 5.0 sia
su Moodle 5.3, mentre il ruleset PHPMD revisionato ne ha rilevati 161. La release 1.7.130 avvia il risanamento
caricando le vere definizioni Moodle di upgrade, CLI, gruppi e API esterna Forum richieste dal perimetro di
produzione; nessun file runtime o perimetro di analisi viene modificato.
La release 1.7.131 disabilita quindi la policy opzionale sull'attributo override, perché applicare l'attributo nativo
suggerito violerebbe il minimo PHP 8.2 supportato. I 104 finding stilistici risultanti non sono accettati come difetti
del codice.
La matrice 1.7.131 verificata riporta 137 finding PHPStan, tutti con identificatore `variable.undefined`, e 136
finding Psalm su ciascun ramo Moodle analizzato. La release 1.7.132 documenta le variabili globali iniettate dai
loader Moodle con dichiarazioni `@var` circoscritte nei dieci file di produzione interessati. Queste dichiarazioni
sono neutre a runtime e non sostituiscono valori del framework né inizializzazioni eseguibili.
La matrice 1.7.132 conferma zero finding PHPStan e 57 finding Psalm su entrambi i rami. La release 1.7.133 modifica
le tre annotazioni rifiutate da Moodle PHPCS in `settings.php` e `version.php`: Psalm usa la configurazione nativa
tipizzata `globals`, mentre PHPStan include due regole specifiche per percorso che corrispondono soltanto ai nomi
iniettati dal framework. Il controllo degli ignore non più utilizzati resta attivo e tutti i perimetri rimangono
invariati.
