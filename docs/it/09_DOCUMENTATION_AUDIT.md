# Audit della documentazione

Baseline: VideoTrack **1.7.123** (`2026090403`).

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
- contratto di accessibilità, troubleshooting, gate build/release e CI del repository;
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
- Marker documentali, README principali e artefatti ER identificano 1.7.123 / 2026090403.
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
- La guida GitHub Actions documenta trigger, permessi minimi, matrice Moodle/PHP/database a sei job, controlli
  bloccanti e consultivi, bootstrap del database ordinario nei layout classico/`public/`, confronto autorevole
  `moodle-plugin-ci grunt`, log conservati, faildump e l'attuale confine di configurazione PHPStan/Psalm.
- Le due coppie di build AMD corrette nella 1.7.123 sono output canonico di Moodle Grunt; le source map hanno mapping
  non vuoti e includono sorgenti byte per byte identici agli AMD distribuiti.
- Gli helper accessibili renderizzati usano le classi Moodle 5 / Bootstrap 5 `visually-hidden`; i live region del
  player sono presenti nel markup iniziale e le normali viste attività non usano il fallback Bootstrap 4 `sr-only`.
- `.github/workflows/ci.yml` è presente nel repository ma escluso dagli archivi Moodle sia da `.gitattributes` sia
  da `.moodleignore`.

## Controlli di release

Prima della promozione verificare:

1. assenza di directory o link `archive/` nel pacchetto;
2. assenza di narrazioni di versioni obsolete nei documenti correnti;
3. risoluzione dei link Markdown e parità dei set EN/IT;
4. identità degli inventari file/callable con l'albero esatto;
5. corrispondenza dei conteggi XMLDB/ER e delle etichette dei riferimenti condizionali con `db/install.xml`;
6. contratti PHPUnit di release hygiene, relazioni XMLDB e uninstall/gradebook, più gate server proporzionato, verdi;
7. corrispondenza di sintassi, versioni action, selettori della matrice, permessi e link del workflow GitHub.
