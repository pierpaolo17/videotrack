# Build, test e release

Ogni risultato appartiene all'esatto albero su cui è stato eseguito. Una successiva modifica a documentazione,
asset generati, schema o versione richiede una nuova identità del pacchetto e un gate proporzionato.

## Baseline e classificazione del delta

1. Partire dall'ultimo archivio reale fornito o pubblicato dal maintainer.
2. Registrare release/versione, elenco file, permessi e SHA-256 prima delle modifiche.
3. Classificare il delta: PHP, XMLDB, AMD, UI browser, lingue, privacy/backup, documentazione o packaging.
4. Eseguire ogni gate capace di rilevare un difetto nel delta; motivare i gate omessi.

## Gate statici richiesti

Dalla root Moodle il wrapper del maintainer può eseguire:

```bash
videotrack-update
moodle-test -p mod_videotrack -m 50,51,52,53 -c canon,lint,phpunit,behat
```

Aggiungere `grunt` quando cambia `amd/src`. `phpcs.xml.dist` è il gate canonico completo `moodle-extra`
senza esclusioni specifiche VideoTrack. Ogni errore o warning PHPCS è bloccante.

Controlli diretti minimi quando i wrapper non sono disponibili:

```bash
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l
php admin/tool/phpunit/cli/util.php --buildcomponentconfigs
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
```

Per modifiche AMD eseguire il vero task Grunt di Moodle e distribuire ogni `.min.js` e `.map` modificato.
La sola sintassi JavaScript non sostituisce Grunt/ESLint.

## Gate comportamentali

- PHPUnit: tutti i test componente, senza failure, errori, warning, notice o deprecazioni inattese.
- Behat: tag `@mod_videotrack` su Moodle 5.0–5.3 per contratti visibili nel browser.
- Smoke provider: HTML5, YouTube e Vimeo pubblici quando conta il comportamento rete/provider.
- Accessibilità manuale: tastiera, focus, reflow, forced colours e screen reader per modifiche UI.
- Ciclo di vita: installazione, upgrade, backup/restore, reset e Privacy API per codice/schema correlato.

Le suite distribuite correnti contengono 268 test PHPUnit / 2400 asserzioni e 23 scenari Behat /
342 step per ramo Moodle supportato. I conteggi sono aspettative, non una dichiarazione di pass.

## Controlli schema e dati

- modificare `db/install.xml` con definizioni compatibili XMLDB;
- aggiungere step idempotente e savepoint in `db/upgrade.php` per i siti installati;
- verificare separatamente fresh install e upgrade;
- confrontare lo schema installato con `cli/validate.php --json`;
- cercare record orfani prima di aggiungere chiavi o cambiare null/default;
- validare backup/restore e Privacy API rispetto alle relazioni modificate.

## Validazione patch e pacchetto

Le patch nascono dalla root del plugin con path relativi:

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

GNU `patch` non applica i record binary-diff di Git. Quando il delta aggiunge o modifica un asset binario,
`git apply` è il percorso patch autorevole e il dry run GNU si arresta legittimamente su quel record; se Git non è
disponibile usare lo ZIP completo. Verificare anche applicazione, rollback, rifiuto della riapplicazione e identità di contenuti/permessi con la
candidata. Lo ZIP deve contenere una sola directory `videotrack/`, nessun path pericoloso/duplicato e nessun
file solo di sviluppo. Se il packaging è riproducibile, due build devono avere lo stesso SHA-256.

## Evidenze di release

Registrare SHA-256 pacchetto, commit/tag, versioni Moodle/PHP/database, comandi esatti, conteggi test,
failure/warning, smoke manuali e gate differiti. Non promuovere una candidata con risultati richiesti mancanti.

Il validatore in sola lettura `cli/validate.php` e il benchmark Analytics
`cli/benchmark_course_analytics.php` sono in [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md);
la copertura browser è in [`22_TEST_BROWSER_BEHAT.md`](22_TEST_BROWSER_BEHAT.md).
