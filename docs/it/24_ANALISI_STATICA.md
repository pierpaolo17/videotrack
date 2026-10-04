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
form, seguite dai grafi di include canonici di backup e restore Moodle prima delle librerie step Moodle 2 usate dal
perimetro di produzione. L'ordine è necessario perché le librerie step dichiarano immediatamente le sottoclassi.
Questo bootstrap esplicito serve perché gli analizzatori non seguono in modo affidabile ogni `require` legacy basata
su variabili. Non scandisce l'intero filesystem e non seleziona mirror `.types` generati. Il sito scelto deve essere
già installato e il database deve essere raggiungibile.

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

L'exit 2 di Psalm viene accettato soltanto come esito documentato di analisi completata con finding; l'exit 1 e ogni
altro stato non zero restano errori bloccanti. L'exit 1 di PHPStan è consultivo soltanto quando è presente il normale
riepilogo numerico e non compare alcun indicatore di errore interno o analisi incompleta. Anche l'exit 2 di PHPMD
viene convertito in esito consultivo. I finding restano visibili senza accettare crash o report parziali come
evidenze valide.

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

Le firme degli helper privati e i relativi DocBlock canonici devono esprimere lo stesso contratto. Usare il tipo
nativo `stdClass` per i record DML Moodle quando la forma del record è una precondizione intenzionale, e passare un
valore primitivo invece di un oggetto framework quando l'helper usa soltanto quel valore. Conservare un vero
contratto generico `object` quando l'helper non ispeziona membri specifici dell'implementazione. Non aggiungere
blocchi inline `@var` destinati soltanto ai parametri, né tag `@phpstan-param`/`@psalm-param`: Moodle Code Checker
rifiuta queste forme. Per gli array validati, mantenere il contratto canonico `array` ed eseguire i cast nel punto in
cui il valore scalare concreto viene salvato o passato a un'API tipizzata. Un `@var` inline è riservato a una vera
assegnazione il cui nome di variabile coincide con la riga di codice immediatamente successiva, non alla nuova
dichiarazione di un parametro di metodo.

## Stato corrente

- PHPStan e Psalm completano l'analisi su entrambi i rami limite supportati. I riepiloghi bloccanti/errori sono
  puliti; l'output consultivo completo resta negli artifact CI e non viene copiato in questa guida.
- I globali forniti dai loader Moodle hanno contratti di tipo circoscritti. Cinque entry point diretti mantengono
  soltanto l'eccezione PHPCS necessaria attorno alle dichiarazioni inline PHPStan; il controllo degli ignore non più
  utilizzati resta attivo.
- Lo stub legacy riservato a Psalm rappresenta l'alias globale `renderable` e il tipo di ritorno Moodle errato
  `xmlddb_field`. Non esegue codice runtime, non sopprime categorie e non riduce il perimetro analizzato.
- `report_support`, `report_analytics_query` e `report_event_query` espongono ciascuno sette contratti pubblici coesi.
  SQL Analytics/provider/prese visione e SQL eventi/individuazione learner sono separati dal supporto per
  presentazione, opzioni, highlight e clustering. Il servizio mirato `report_time_filter` possiede quattro contratti
  pubblici coesi per limiti data, parsing richiesta e rendering dei
  controlli durata; il parser strutturato delega presenza, cifre, normalizzazione e limiti a fasi private nominate.
  Il servizio `report_aggregation` possiede individuazione pura dei cali di viewer e ordinamento dei cluster. Questi
  confini preservano il contratto del controller eliminando la complessità di metodo prima assegnata a
  `report_support::optional_time_param()`. Anche la scelta del delimitatore CSV usa entry point distinti per sito e
  attività; nessuna delle due API usa flag di comportamento.
- `videotrack_render_reaction_icon()` restituisce sempre l'icona insieme alla relativa etichetta accessibile visibile.
  I chiamanti non passano più un flag booleano di presentazione inutilizzato.
- La lettura delle reazioni usa funzioni distinte per scope attivo e lifecycle completo. Il filtro dei record ha un
  solo contratto stabile con cache per richiesta; nessuna delle due API usa uno switch booleano opzionale.
- La lettura timed text usa metodi canonici per trascrizioni/capitoli e metodi di fallback legacy nominati
  esplicitamente. I percorsi di compatibilità mantengono la precedenza canonica senza switch booleani.
- La policy delle velocità espone un contratto pubblico supportato da fasi nominate per parsing, limite effettivo,
  filtro e garanzia della velocità normale. Anche parsing emoji TinyMCE e rendering di intestazione/tab/corpo del
  selettore reazioni sono isolati, mentre il salvataggio retention separa conferma e audit successivo al salvataggio.
- La presentazione degli indicatori di integrità usa entry point distinti per stato attivo, disattivato e controlli
  focus senza registrazione; l'API di presentazione non contiene più flag booleani di comportamento.
- La scrittura delle note personali espone un solo entry point external supportato da fasi nominate per testo,
  timestamp, evidenza vista, rate limit tra sessioni, persistenza e warning. I contratti su ordine dei guard e scope
  del limite impediscono al refactoring di complessità di indebolire il confine di scrittura. I contratti privati
  dei record DML usano il tipo nativo `stdClass`, mentre la persistenza riceve l'id scalare del modulo che salva.
- La scrittura dei segnalibri personali espone le corrispondenti fasi nominate per etichetta, timestamp, evidenza
  vista, rate limit tra sessioni, persistenza ed evento. I contratti sorgente mantengono l'ordine di validazione e
  sicurezza e impediscono che il limite di dieci in dieci secondi per utente/attività venga ristretto a una sola
  sessione browser.
- La scrittura delle reazioni espone fasi nominate per validazione richiesta, definizione attiva, timestamp,
  evidenza vista, burst limit, duplicati e soft-ignore. L'endpoint external possiede ogni decisione pre-insert di
  sicurezza e concorrenza; il servizio mirato `reaction_write_service` possiede il lavoro
  cache/evento/conteggio/completion successivo alla persistenza. I contratti sorgente preservano entrambi gli ordini,
  il mapping stabile della risposta e il contratto generico del contesto usato dall'helper export. Il contratto
  completion tra servizi verifica questo proprietario corrente invece di imporre che le chiamate estratte restino
  nell'endpoint pubblico. Il suo guard esplicito `MOODLE_INTERNAL` mantiene una sola eccezione PHPCS sulla riga,
  perché la classe autoloaded non ha effetti collaterali top-level.
- Quattro helper interni media/file consumano l'intera firma tramite contratti runtime: validazione del Moodle
  form, risoluzione del contesto modulo consapevole dell'istanza e controllo di coerenza tra modulo corso e
  istanza. I parametri richiesti dei callback Moodle restano invariati anche quando l'invocazione appartiene al
  framework.
- Il callback delle feature Moodle è una tabella diretta feature-valore, mentre la validazione delle reazioni Font
  Awesome separa il sottoinsieme revisionato dei decoratori da un solo nome icona. Contratti pubblici, limiti e
  default fail-closed sono coperti indipendentemente dalla riduzione di complessità.
- PHPMD resta consultivo. I rilievi residui riguardano complessità, dimensione, classi pubbliche ampie, parametri
  obbligatori dei callback Moodle e flag booleani che richiedono refactoring comportamentali separati. I conteggi
  correnti esatti vanno letti nell'artifact CI prodotto per la candidata in esame.
- Configurazione degli analizzatori, perimetro di produzione, schema database e asset AMD non cambiano con queste
  pulizie delle API. I conteggi della candidata diventano autorevoli soltanto dopo aver conservato e revisionato gli
  artifact CI completi.
