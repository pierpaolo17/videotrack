# Build, test and release

## Required checks

From the Moodle root, adapt paths to the local installation:

```bash
# PHP syntax
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l

# PHPUnit
vendor/bin/phpunit --testsuite mod_videotrack_testsuite

# Moodle coding style / extra rules as configured by the project
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack

# AMD only when amd/src changes
npx grunt amd --root=mod/videotrack
```

Also parse `db/install.xml` and `environment.xml`, run `node --check` on source/build JavaScript, validate every source map as JSON, compare language key sets and placeholders, verify every static `get_string` reference, and compare XMLDB fields with backup/restore declarations.

## PHPUnit interpretation

A line ending in “OK, but there were issues” is not a clean pass if failures/errors exist. Known PHPUnit 11 deprecations for DocBlock metadata must be reported separately from failures. Keep `@covers` DocBlocks where required by Moodle PHPCS Extra until the toolchain supports attributes consistently.

## Patch validation

```bash
git diff --check
git diff --binary BASELINE..WORKTREE > videotrack-x.y.z.patch
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

Apply the patch to a separate fresh extraction and compare its complete tree with the worktree. New/untracked files must be included explicitly.

## Release evidence

Record baseline checksum, changed files, version/schema decisions, executed checks, unexecuted checks and patch checksum. Do not label PHPUnit, PHPCS, browser, upgrade or backup/restore as successful unless that exact release was actually tested.
