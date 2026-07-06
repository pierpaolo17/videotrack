# Build, test and release

## Environment

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

PHPUnit 11 docblock `@covers` deprecations are known: they satisfy the Moodle `TestCaseCovers` sniff until moodle-cs fully supports attributes for this plugin use case.

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

## Patches

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

The patch must be generated from the plugin root. Absolute or temporary paths inside `/mnt/data` are not acceptable.

## Documentation-only release checklist

For documentation-only patches, the minimum checks are:

```bash
php -l version.php
git apply --check <patchfile>
patch -p1 --dry-run < <patchfile>
```

If inventories are changed, regenerate or manually verify:

- file inventory against the real ZIP contents;
- PHP function inventory against shipped PHP files;
- AMD function inventory against `amd/src` files;
- variable inventory as a navigation aid.

A documentation-only patch does not require `grunt amd`, PHPUnit or PHPCS unless it changes executable code. If `version.php` is bumped, run Moodle upgrade in the target environment.
