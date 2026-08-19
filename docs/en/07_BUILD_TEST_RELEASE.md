# Build, test and release

## Required checks

From the Moodle root, adapt paths to the local installation:

```bash
# PHP syntax
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l

# PHPUnit
vendor/bin/phpunit --testsuite mod_videotrack_testsuite

# Canonical PHPCS release gate (full Moodle Extra, no VideoTrack exclusions)
/root/.config/composer/vendor/bin/phpcs --standard=mod/videotrack/phpcs.xml.dist mod/videotrack


# AMD only when amd/src changes
node node_modules/grunt/bin/grunt amd --root=mod/videotrack
```

The repository-level `phpcs.xml.dist` is the canonical PHPCS release gate. Since 1.7.85 it is the full `moodle-extra` ruleset with no VideoTrack-specific warning exclusions: every PHPCS warning or error is release-blocking. Record both the PHP_CodeSniffer and `moodlehq/moodle-cs` versions with the evidence when the toolchain changes.

Also parse `db/install.xml` and `environment.xml`, run `node --check` on source/build JavaScript, validate every source map as JSON, compare language key sets and placeholders, verify every static `get_string` reference, and compare XMLDB fields with backup/restore declarations.

## PHPUnit interpretation

A line ending in “OK, but there were issues” is not a clean pass if failures/errors exist. Since 1.7.85 VideoTrack coverage metadata is expressed with PHPUnit attributes; any return of coverage-metadata deprecations must be treated as a regression and investigated separately from functional failures.

The PHPUnit suite also includes `provider_seek_snapshot_contract_test.php`, which protects provider seek/rollback invariants without pretending to replace browser execution. It must remain green when provider seek code changes; Behat/manual provider tests are still required for runtime evidence.

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


## 1.6.32 playback-ledger checks

When the playback ledger or `videotrack_seg` schema changes, also verify:

- `mod_videotrack_start_playback` is declared in `db/services.php`, accepted by the AMD validator and protected by the same sesskey/context/capability contract as other learner writes;
- a segment without a successful handshake receives no credit;
- request identifiers are unique per activity/user and retries return the persisted result without duplicate rows, events or completion writes;
- provider/server drift tolerance remains a cumulative debt and cannot be reset by pause, rejection or a new handshake;
- `requestid` is aligned across XMLDB, upgrade, Privacy API, backup and restore;
- exact unique coverage remains monotonic when the compact interval list reaches 500 entries.

## Distributed CLI diagnostics

VideoTrack ships read-only local diagnostics. Run them from the Moodle root after installation/upgrade:

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> --runs=5 --perioddays=7
```

The validator is useful for every release. Re-run the Analytics benchmark when course aggregation, learner/group scope, Analytics SQL or related indexes change. Full options, interpretation and the recorded U-016 baseline are documented in [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md).

## Behat browser gate

When learner-page markup, browser interactions, player adapters or seek state changes, initialise Moodle Behat and run the VideoTrack tag:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

See [`22_BEHAT_BROWSER_TESTS.md`](22_BEHAT_BROWSER_TESTS.md). Behat is an exact-tree release gate and does not replace manual provider smoke tests until the U-007 matrix is complete.
