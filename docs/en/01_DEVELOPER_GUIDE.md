# Developer guide

## Purpose

This guide helps a new developer work on the plugin without an initial reverse-engineering phase.

## Mandatory patch rules

1. Always start from the latest real ZIP provided by the maintainer.
2. Audit the actual files before editing.
3. Generate patches from the plugin root, never from internal temporary paths.
4. Really verify `git apply --check` and `patch -p1 --dry-run` on the delivered patch file.
5. If `amd/src` changes, include `amd/build` and source maps or clearly state that `grunt amd` must be run.
6. Do not claim checks that were not actually run.

## Minimum validation commands

```bash
php -l lib.php
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra mod/videotrack
ulimit -n 65535
node node_modules/grunt/bin/grunt amd --root=mod/videotrack --force
```

## When editing AMD

Player runtime issues must not be fixed by deduction. First identify the function actually executed, then change the minimal point.

## Documentation patches

Documentation patches must update both languages, keep inventories aligned with code and bump the release when requested by the maintainer.
