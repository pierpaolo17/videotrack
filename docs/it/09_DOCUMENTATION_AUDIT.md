# Audit della documentazione

Baseline: VideoTrack **1.7.149** (`2026092202`).

## Perimetro

La documentazione è stata verificata contro `version.php`, `db/install.xml`, `db/services.php`, `db/access.php`,
`settings.php`, `mod_form.php`, `classes/local`, `classes/privacy`, backup/restore, coppie sorgente/build AMD e
suite di test distribuite.

La copertura corrente comprende:

- guida completa funzionale e operativa per utenti/amministratori;
- architettura, flussi runtime e confini di fiducia;
- sette tabelle plugin, impostazioni, capability, nove servizi AJAX e file area;
- comportamento dei provider HTML5, YouTube e Vimeo;
- tracking, completion, gradebook, report, Analytics ed export;
- reazioni, note, bookmark, collegamento Forum e presa visione;
- privacy, retention, reset, backup/restore e diagnostica CLI;
- contratto di accessibilità, troubleshooting, gate build/release, CI del repository e policy di analisi statica;
- inventari esaustivi dei file non documentali e dei callable nominati;
- artefatti database/ER Markdown, Mermaid e SVG accessibile.

## Politica di freschezza e storico

`docs/en/archive` e `docs/it/archive` non sono distribuite. Le narrazioni implementative release per release sono
state rimosse dalle guide correnti. Il `CHANGELOG.md` principale contiene soltanto sintesi; per il comportamento
esatto di una versione precedente si usa il relativo tag sorgente e la documentazione inclusa.

Le README principali forniscono una mappa completa delle funzionalità e indirizzano alle guide dettagliate. Non
duplicano intenzionalmente procedure implementative, dizionari dei campi o lunghe spiegazioni sicurezza/privacy.

## Rilievi sull'albero corrente

- Gli indici inglese e italiano hanno stesso perimetro e ordinamento.
- Marker documentali, README principali e artefatti ER identificano 1.7.149 / 2026092202.
- L'identità dell'attività include `pix/icon.png` da 1024 pixel e un `pix/icon.svg` nativo e accessibile derivati
  dalla stessa grafica fornita dal maintainer.
- `db/install.xml` dichiara sette chiavi primarie, 22 foreign key stabili e 22 indici espliciti. XMLDB genera inoltre
  un indice esatto per ogni foreign key, ma nessun vincolo fisico o cascade nel database.
- `linkedforumid` e `reactionid` restano riferimenti condizionali documentati perché la sentinella `0` è valida.
- Il validatore CLI in sola lettura controlla relazioni dichiarate, indici generati, orfani, coerenza del contesto
  denormalizzato e integrità gradebook.
- La documentazione lifecycle copre hook anticipato di uninstall gradebook, fallback per record incoerenti, preflight
  CLI e verifica della disinstallazione a residuo zero. Il runtime CRUD, AJAX, privacy e completion resta invariato.
- Le guide dell'attività e dei media documentano reazioni opt-in, visibilità effettiva delle sezioni learner e
  l'equivalente live della durata in `HH:MM:SS`.
- Le letture delle reazioni hanno scope espliciti: l'helper standard restituisce le definizioni attive, mentre il
  codice lifecycle può richiedere definizioni attive e soft-deleted senza flag booleani. Prima di insert/update i
  record attività sono filtrati sulle vere colonne della tabella tramite una cache di metadata per richiesta.
- Le letture timed text hanno contratti canonici e di compatibilità espliciti. I file dedicati di
  trascrizione/capitoli mantengono la precedenza e la vecchia area sottotitoli viene usata soltanto dai metodi di
  fallback nominati selezionati per le attività con video caricato migrate.
- La guida GitHub Actions documenta trigger, permessi minimi, matrice Moodle/PHP/database a sei job, controlli
  bloccanti e consultivi, bootstrap deterministico nei layout classico/`public/`, rifiuto dei mirror `.types`,
  confronto autorevole `moodle-plugin-ci grunt`, log conservati e faildump.
- PHPStan/Psalm hanno perimetri limitati alla produzione e un bootstrap Moodle installato comune. Il bootstrap
  carica i grafi di include canonici di backup/restore prima delle librerie step Moodle 2. Uno stub Psalm
  circoscritto modella soltanto il nome globale di compatibilità `renderable` e il refuso DocBlock upstream
  `xmlddb_field`. Cinque entry point documentano i globali creati da `config.php` sotto eccezioni PHPCS strettamente
  circoscritte; nessuna categoria degli analizzatori o percorso di produzione viene ignorato.
- Psalm disabilita esplicitamente soltanto `ensureOverrideAttribute`: la correzione nativa richiede PHP 8.3, ma la
  matrice Moodle 5.0 supportata include PHP 8.2. I controlli di ereditarietà e firma restano attivi.
- I file di produzione che consumano globali o contenitori forniti dai loader Moodle hanno contratti di analisi
  circoscritti. Le dichiarazioni esistenti rispettano direttamente Moodle PHPCS; i cinque entry point diretti
  limitano l'eccezione al solo sniff sui DocBlock inline attorno alle loro 20 dichiarazioni PHPStan. Psalm dichiara
  inoltre il globale tipizzato `$DB` e le variabili dei loader usate da `settings.php` e `version.php`. Due regole
  PHPStan specifiche per percorso e nome mantengono attivo il controllo degli ignore non più utilizzati.
- Il `phpmd.xml` attivo elimina il rumore Moodle di naming/framework già revisionato, mantiene le regole runtime
  selezionate e distingue i finding consultivi dagli errori di analizzatore/configurazione. Il codice usa API
  esplicite per delimitatori, limiti data, forma degli export Analytics e rendering delle percentuali di corso;
  `report_support` contiene 25 metodi e le icone di reazione includono sempre l'etichetta visibile senza un flag
  booleano di presentazione. I conteggi esatti si leggono nell'artifact CI della candidata corrente.
- Le due coppie di build AMD corrette nella 1.7.123 sono output canonico di Moodle Grunt; le source map hanno mapping
  non vuoti e includono sorgenti byte per byte identici agli AMD distribuiti.
- Gli helper accessibili renderizzati usano le classi Moodle 5 / Bootstrap 5 `visually-hidden`; i live region del
  player sono presenti nel markup iniziale e le normali viste attività non usano il fallback Bootstrap 4 `sr-only`.
- `.github/workflows/ci.yml` e il manifest degli analizzatori fissati sono presenti nel repository ma esclusi dagli
  archivi Moodle. Configurazioni e bootstrap distribuiti restano disponibili ai maintainer e ai gate server.

## Controlli di release

Prima della promozione verificare:

1. assenza di directory o link `archive/` nel pacchetto;
2. assenza di narrazioni di versioni obsolete nei documenti correnti;
3. risoluzione dei link Markdown e parità dei set EN/IT;
4. identità degli inventari file/callable con l'albero esatto;
5. corrispondenza dei conteggi XMLDB/ER e delle etichette dei riferimenti condizionali con `db/install.xml`;
6. contratti PHPUnit di release hygiene, relazioni XMLDB e uninstall/gradebook, più gate server proporzionato, verdi;
7. corrispondenza di sintassi, versioni action, selettori della matrice, permessi e link del workflow GitHub.
