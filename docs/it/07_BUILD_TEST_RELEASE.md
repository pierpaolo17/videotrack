# Build, test e release

## Controlli richiesti

Dalla root Moodle, adattando i path:

```bash
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack
# Solo se cambia amd/src
npx grunt amd --root=mod/videotrack
```

Analizzare anche `db/install.xml` ed `environment.xml`, eseguire `node --check` su sorgenti/build, validare le source map JSON, confrontare chiavi e placeholder delle lingue, verificare ogni `get_string` statico e confrontare XMLDB con backup/restore.

## Interpretazione PHPUnit

“OK, but there were issues” non è un pass pulito se esistono failure/error. Le deprecazioni note PHPUnit 11 dei metadata DocBlock vanno distinte dai failure. Conservare i DocBlock `@covers` quando richiesti da Moodle PHPCS Extra finché la toolchain non supporta coerentemente gli attributi.

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
