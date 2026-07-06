# Guida sviluppatore

## Scopo

Questa guida permette a un nuovo sviluppatore di intervenire sul plugin senza reverse engineering iniziale.

## Regole inderogabili per le patch

1. Partire sempre dallo ZIP reale più recente fornito dal maintainer.
2. Eseguire un audit reale dei file prima di modificare.
3. Generare patch dalla root del plugin, mai da directory temporanee interne.
4. Verificare realmente `git apply --check` e `patch -p1 --dry-run` sul file patch consegnato.
5. Se si modifica `amd/src`, includere anche `amd/build` e source map oppure dichiarare chiaramente che va eseguito `grunt amd`.
6. Non dichiarare OK controlli non eseguiti.

## Comandi minimi di validazione

```bash
php -l lib.php
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra mod/videotrack
ulimit -n 65535
node node_modules/grunt/bin/grunt amd --root=mod/videotrack --force
```

## Quando modificare AMD

I problemi runtime dei player non vanno corretti per deduzione. Prima individuare la funzione realmente eseguita, poi modificare il punto minimo.

## Patch documentali

Le patch documentali devono aggiornare entrambe le lingue, mantenere gli inventari coerenti con il codice e incrementare la release se richiesto dal maintainer.
