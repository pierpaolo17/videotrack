# Audit della documentazione

Baseline: VideoTrack **1.7.252** (`2026111202`).

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

- U-017/U-007 restano chiusi. ZIP/server/strict reali 1.7.249 e CI candidata/main su sei job sono accettati.
  La candidata 1.7.250 aggiunge il guard gestione reset mancante e due scenari browser reali, completa la review
  assistita delle 997 chiavi e registra otto PASS manuali nello scope Moodle 5.0 dichiarato. Restano copertura
  manuale 5.3 e gate nativi candidata. Vedere la [review corrente](27_REVISIONE_LINGUE.md).

- `form_reaction_settings` possiede impostazioni generali, avviso/preset nel contesto e campi delle singole righe,
  seguiti dalle regole di visibilità delle icone. Il form mantiene conteggio, limiti, registrazione filtro upload
  e pulsante Aggiungi senza invio, poi delega i blocchi completamento con il conteggio effettivo. Il permesso di
  completamento resta distinto da quello del player. Test runtime coprono ordine, tipi, target univoci del
  selettore, opzioni file originali, valori salvati, dipendenze esatte, conteggi del form reale e blocchi su righe reali.
- `form_playback_settings` possiede il gruppo riproduzione controllato dal sito con un ingresso pubblico e uno
  stadio privato per le velocità limite. Il fallback false/vuoto conserva lo zero esplicito e il blocco per corso
  congela gli stessi sette campi; resume/velocità massima restano modificabili. `form_definition` mantiene nove metodi pubblici.
- La costruzione della durata conserva campo numerico, regioni di stato accessibili, identificatori, limite
  di 86.400 secondi, token dei messaggi e JSON DOM con escape esadecimale. L'init AMD riceve solo l'identificatore
  corrispondente sulla pagina fornita dal form; il contratto provider esistente segue la delega esplicita.
- `form_definition` costruisce durata, player/testo temporizzato, campi CSV, Forum, integrità e presa visione nelle posizioni previste;
  opzioni del corso, segnaposto salvati e contesto/opzioni dell’editor restano espliciti. Il form conserva
  validazione in invio ed espansione delle sezioni senza dipendenza diretta dalla classe delle prese visione.
- Gli indici inglese e italiano hanno stesso perimetro e ordinamento.
- Marker documentali, README principali e artefatti ER identificano 1.7.248 / 2026111101.
- L’export privacy Moodle delega stato, segmenti, eventi, integrità e prese visione nello stesso ordine;
  sei categorie di eventi conservano percorsi, pulizia dei dati, blocchi da 500 e ordine finale.
- I controlli del form per sorgenti, file VTT, icone delle reazioni e forum mantengono l'ordine originale tramite
  helper dedicati; le righe delle reazioni conservano ordine degli errori e requisiti della configurazione;
  i controlli puri per valore icona e campi obbligatori risiedono in `reaction_form_validation`.
- `form_preprocessing` mantiene l'ordine dei default per reazioni, completamento, playback, CSV e sito;
  le bozze VTT e poster restano nel form dopo la chiamata al parent Moodle.
- L'editor dell'avviso reazioni e il JSON dei preset sono preparati dopo i draft VTT e prima della bozza poster;
  un editor già presente viene mantenuto quando esiste un avviso salvato.
- `videotrack_pluginfile()` mantiene firma e ordine dei controlli del callback Moodle; helper dedicati
  verificano aree, percorsi, estensione/MIME/contenuto WebVTT e regole di download/cache.
- `privacy_retention_pair` elabora ogni coppia con gli stessi lock, transazione e completamento
  successivo al commit; `privacy_manager` mantiene le API pubbliche di cancellazione e batch.
- La facciata Analytics di istanza delega durata, scelta dei bin e aggregazione della timeline
  da segmenti/stati a `analytics_timeline`; privacy masking e cluster delle reazioni restano nella facciata.
- Le Analytics tra corsi documentano la facciata stabile `analytics_scope` e il matcher focalizzato che possiede
  selezione candidati, predicati dell’identità tecnica, filtro capability, fallback dell’istanza corrente e ordine.
- Le Analytics del corso delegano gli scope delle attività visibili e le letture batchate di stati, segmenti nel
  periodo ed eventi a `course_analytics_loader`; `course_analytics` conserva riepiloghi e righe protette dalla privacy.
- Il completamento personalizzato legge le condizioni delle reazioni abilitate tramite
  `completion_config::reaction_checks()`; le decisioni AND/OR delle reazioni e dell'attività
  usano entrambe `completion_config::combine_checks()`.
- La facciata CSV mantiene i metodi pubblici e delega la scelta dei campi in base al contesto a
  `csv_export_fields` e la formattazione dell’identità a `csv_export_identity`.
- Il writer CSV riceve un contesto tipizzato per attività e utenti; resta responsabile delle righe e dei filtri
  sugli utenti. L'export separa la formattazione dei campi attività dalla pulizia dei campi utente;
  la conferma della presa visione verifica la disponibilità prima di cercare o creare il record learner.
- Le righe evento CSV trasportano tre timestamp video nullable in un valore tipizzato; il riepilogo dei segnalibri
  conserva le celle dei tempi vuote e la colonna del numero di studenti.
- Il servizio segmenti valida i dieci campi originali prima dei controlli sesskey e contesto,
  persiste tramite la stessa transazione del tracker ed emette eventi e completion solo per i segmenti nuovi accettati.
- L’export privacy Moodle resta accessibile da `provider::export_user_data()`, che delega
  a `exporter`; formattazione intervalli, chunk da 500 record e sanitizzazione restano invariati.
- I metadati privacy elencano i servizi esterni dopo le tabelle database; la retention cancella le righe
  personali scadute prima di ricalcolare lo stato, nello stesso lock e nella stessa transazione delegata.
- Il ricalcolo individua i learner tracciati nelle stesse quattro tabelle; la retention ricostruisce lo stato
  trattenuto con il lock e la transazione esistenti prima di sincronizzare il completamento Moodle.
- Il tracker riusa lo snapshot esistente e persiste lo stato ricostruito nella transazione delegata;
  la retention sincronizza il completamento Moodle dopo il commit mentre mantiene il lock dello stato.
- Le ricostruzioni autonome acquisiscono e rilasciano il lock canonico; la retention passa al medesimo metodo
  l'oggetto lock già detenuto e ne mantiene la proprietà durante la transazione e la sincronizzazione.
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
- Il salvataggio di video caricati e poster risolve soltanto contesti modulo legati agli identificatori del form o
  alla coppia attività/corso persistita. La risoluzione URL rifiuta un modulo corso appartenente a un'altra istanza
  VideoTrack e l'helper dei campi video valida ogni collaboratore Moodle form non nullo.
- Le letture delle reazioni hanno scope espliciti: l'helper standard restituisce le definizioni attive, mentre il
  codice lifecycle può richiedere definizioni attive e soft-deleted senza flag booleani. Prima di insert/update i
  record attività sono filtrati sulle vere colonne della tabella tramite una cache di metadata per richiesta.
- Le letture timed text hanno contratti canonici e di compatibilità espliciti. I file dedicati di
  trascrizione/capitoli mantengono la precedenza e la vecchia area sottotitoli viene usata soltanto dai metodi di
  fallback nominati selezionati per le attività con video caricato migrate.
- La guida GitHub Actions documenta trigger, permessi minimi, matrice Moodle/PHP/database a sei job, controlli
  bloccanti e consultivi, bootstrap deterministico nei layout classico/`public/`, rifiuto dei mirror `.types`,
  confronto autorevole `moodle-plugin-ci grunt`, log conservati e faildump.
- I job MariaDB e PostgreSQL al limite superiore selezionano esplicitamente `MOODLE_503_STABLE`. Non usano mai
  `main` upstream, che può avanzare oltre l'intervallo di supporto 500–503 dichiarato da VideoTrack.
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
  esplicite per delimitatori, forma degli export Analytics e rendering delle percentuali di corso.
  `report_analytics_query`, `report_event_query` e `report_support` possiedono ciascuno sette contratti pubblici coesi
  rispettivamente per SQL Analytics, SQL eventi e lavoro residuo di presentazione/opzioni/highlight/clustering.
  `report_time_filter` possiede quattro contratti coesi per date/richiesta/rendering e `report_aggregation` possiede
  individuazione dei cali di viewer e ordinamento dei cluster. Il rendering Analytics docente è diviso tra servizi
  per controlli condivisi, timeline, riepiloghi aggregati e stati integrità, tutti sotto la soglia dei metodi
  pubblici e separati da query e aggregazione. Le fasi dei grafici timeline preservano gap privacy e ordine SVG,
  mentre le fasi delle prese visione preservano la soppressione completa e del solo progresso. La costruzione
  dell'export combinato possiede separatamente padding timeline, riepiloghi evento ordinati, fallback integrità e
  valori delle prese visione, conservando i contratti pubblici delle righe e della privacy. La scoperta dei candidati
  per lo stesso video è isolata in `analytics_scope_matcher`; le sue fasi conservano identità per id provider, hash
  upload e URL canonico insieme ai controlli capability aggregati per modulo. Il callback delle
  feature Moodle usa una tabella, i valori Font
  Awesome contengono esattamente un nome
  icona validato e le icone renderizzate includono sempre l'etichetta visibile senza un flag booleano di
  presentazione. I conteggi esatti si leggono nell'artifact CI della candidata corrente.
- Le due coppie di build AMD corrette nella 1.7.123 sono output canonico di Moodle Grunt; le source map hanno mapping
  non vuoti e includono sorgenti byte per byte identici agli AMD distribuiti.
- Gli helper accessibili renderizzati usano le classi Moodle 5 / Bootstrap 5 `visually-hidden`; i live region del
  player sono presenti nel markup iniziale e le normali viste attività non usano il fallback Bootstrap 4 `sr-only`.
- `.github/workflows/ci.yml` e il manifest degli analizzatori fissati sono presenti nel repository ma esclusi dagli
  archivi Moodle. Configurazioni e bootstrap distribuiti restano disponibili ai maintainer e ai gate server.
- Le evidenze CI/server generate non fanno parte dell'albero del plugin. Il `.gitignore` del repository, le regole
  Git archive `export-ignore`, le regole Moodle `.moodleignore` e un contratto PHPUnit di release hygiene impediscono
  insieme che `reports-*`, `videotrack-ci-*.zip` e `report_completo_videotrack_*.zip` entrino nelle release future.
  L'archivio canonico di release deve contenere quindi soltanto i 415 file legittimi del plugin.

## Controlli di release

Prima della promozione verificare:

1. assenza nel pacchetto di directory o link `archive/` e `reports-*`, oltre agli ZIP CI/server generati;
2. assenza di narrazioni di versioni obsolete nei documenti correnti;
3. risoluzione dei link Markdown e parità dei set EN/IT;
4. identità degli inventari file/callable con l'albero esatto;
5. corrispondenza dei conteggi XMLDB/ER e delle etichette dei riferimenti condizionali con `db/install.xml`;
6. contratti PHPUnit di release hygiene, relazioni XMLDB e uninstall/gradebook, più gate server proporzionato, verdi;
7. corrispondenza di sintassi, versioni action, selettori della matrice, permessi e link del workflow GitHub.

## Review corrente e fasi successive concordate

La 251 reale/main `823a6ac458c5cc8a2ed5a5fbea729dabfdac6330` è accettata con ZIP esatto415 file,
due report server e strict 10/0/0 forniti. Suite nativa381/4296 e Behat 52/1154 superati. La review assistita
997 chiavi / 7976 valori è completa; U-017/U-007 restano chiusi e le evidenze manuali circoscritte sono conservate.

La candidata 252 aggiunge il callback canonico di reset Moodle, conservando corpo e flag dell'implementazione.
Due test reali reset/delete sono in attesa. Attesi 383 metodi PHPUnit; asserzioni da misurare. Restano Behat 52/1154,
415 file/167 PHP/32 documenti per lingua/346 righe file/654 callable AMD. I callable PHP diventano 1113; entrambi gli
inventari usano le righe effettive. XMLDB 7/151/22/22, nove AJAX, 997 chiavi per lingua, AMD, grant e regole CI
restano invariati. L'ER cambia solo etichetta versione.

Vedere [le evidenze di produzione](28_USCITA_PRODUZIONE.md) per scoperta callback, ownership, rilievi sorgente ed evidenze
residue su upgrade storico/uninstall, benchmark e manuale. Servono CI candidata/dopo merge e ZIP/server/strict
reali; il gate completo produzione/App resta aperto. Decisione App e VTT precedono i provider autenticati
OneDrive/SharePoint → Zoom → Google Drive come concordato.
