# Build, test e release

## Controlli richiesti

Dalla root Moodle, adattando i path:

```bash
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra mod/videotrack
# Solo se cambia amd/src
node node_modules/grunt/bin/grunt amd --root=mod/videotrack
```

Analizzare anche `db/install.xml` ed `environment.xml`, eseguire `node --check` su sorgenti/build, validare le source map JSON, confrontare chiavi e placeholder delle lingue, verificare ogni `get_string` statico e confrontare XMLDB con backup/restore.

## Interpretazione PHPUnit

“OK, but there were issues” non è un pass pulito se esistono failure/error. Le deprecazioni note PHPUnit 11 dei metadata DocBlock vanno distinte dai failure. Conservare i DocBlock `@covers` quando richiesti da Moodle PHPCS Extra finché la toolchain non supporta coerentemente gli attributi.

La suite PHPUnit include anche `provider_seek_snapshot_contract_test.php`, che protegge gli invarianti provider di seek/rollback senza sostituire artificialmente l'esecuzione browser. Deve restare verde quando cambia il codice seek dei provider; Behat/test manuali provider restano necessari come evidenza runtime.

## Validazione patch

```bash
git diff --check
git diff --binary BASELINE..WORKTREE > videotrack-x.y.z.patch
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

Applicare la patch a un’estrazione separata e confrontare l’intero albero. I file nuovi/untracked devono essere inclusi esplicitamente.

## Evidenze release

Registrare checksum baseline, file modificati, decisioni versione/schema, controlli eseguiti/non eseguiti e checksum patch. Non dichiarare riusciti PHPUnit, PHPCS, browser, upgrade o backup/restore se non eseguiti su quella release esatta.


## Controlli del ledger di riproduzione 1.6.32

Quando cambia il ledger di riproduzione o lo schema `videotrack_seg`, verificare anche che:

- `mod_videotrack_start_playback` sia dichiarato in `db/services.php`, ammesso dal validator AMD e protetto dallo stesso contratto sesskey/contesto/capability delle altre scritture learner;
- un segmento privo di handshake riuscito non riceva credito;
- gli identificativi richiesta siano univoci per attività/utente e i retry restituiscano il risultato persistito senza duplicare righe, eventi o scritture completion;
- la tolleranza tra clock provider e server resti un debito cumulativo e non possa essere azzerata da pausa, rifiuto o nuovo handshake;
- `requestid` sia coerente fra XMLDB, upgrade, Privacy API, backup e restore;
- la copertura unica esatta resti monotona al raggiungimento dei 500 intervalli compatti.

## Diagnostica CLI distribuita

VideoTrack include diagnostica locale in sola lettura. Eseguirla dalla root Moodle dopo installazione/upgrade:

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> --runs=5 --perioddays=7
```

Il validatore è utile per ogni release. Rieseguire il benchmark Analytics quando cambiano aggregazione di corso, learner/group scope, forma SQL Analytics o indici correlati. Opzioni complete, interpretazione e baseline U-016 registrata sono documentate in [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md).

## Gate browser Behat

Quando cambiano markup learner, interazioni browser, adapter player o stato dei seek, inizializzare Moodle Behat ed eseguire il tag VideoTrack:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Vedere [`22_TEST_BROWSER_BEHAT.md`](22_TEST_BROWSER_BEHAT.md). Behat è un gate relativo al tree esatto e non sostituisce gli smoke test manuali dei provider finché la matrice U-007 non è completa.
