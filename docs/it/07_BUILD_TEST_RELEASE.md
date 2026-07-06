# Build, test e release

## Ambiente

- Moodle 5.0+
- PHP 8.2+
- Node 22.x according to Moodle requirement
- Moodle Grunt toolchain installed in the Moodle root

## PHPUnit

```bash
cd /var/www/default-ssl/m45/moodle
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
```

Le deprecazioni PHPUnit 11 sui docblock `@covers` sono note: servono a soddisfare lo sniff Moodle `TestCaseCovers` finché moodle-cs non supporterà pienamente gli attributi nel caso d’uso del plugin.

## PHPCS

```bash
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra /var/www/default-ssl/m45/moodle/mod/videotrack > phpcs_extra.json
```

## AMD

```bash
cd /var/www/default-ssl/m45/moodle
ulimit -n 65535
export UV_THREADPOOL_SIZE=4
export NODE_OPTIONS="--max-old-space-size=4096"
node node_modules/grunt/bin/grunt amd --root=mod/videotrack --force > ../grunt_amd.txt 2>&1
```

## Patch

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

La patch deve essere generata dalla root del plugin. Percorsi assoluti o temporanei dentro `/mnt/data` non sono accettabili.

## Checklist per release solo documentale

Per patch esclusivamente documentali, i controlli minimi sono:

```bash
php -l version.php
git apply --check <patchfile>
patch -p1 --dry-run < <patchfile>
```

Se cambiano gli inventari, rigenerare o verificare manualmente:

- inventario file rispetto al contenuto reale dello ZIP;
- inventario funzioni PHP rispetto ai file PHP distribuiti;
- inventario funzioni AMD rispetto ai file `amd/src`;
- inventario variabili come supporto di navigazione.

Una patch solo documentale non richiede `grunt amd`, PHPUnit o PHPCS, salvo modifiche a codice eseguibile. Se viene incrementato `version.php`, eseguire l'upgrade Moodle nell'ambiente target.
