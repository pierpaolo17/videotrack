# Build, test and release

Every result belongs to the exact source tree on which it ran. A later documentation, generated-asset, schema
or version change requires a new package identity and a proportionate gate.

## Baseline and change classification

1. Start from the latest real archive supplied or published by the maintainer.
2. Record release/version, file list, modes and SHA-256 before editing.
3. Classify the delta: PHP, XMLDB, AMD, browser UI, language, privacy/backup, documentation or packaging.
4. Run every gate capable of detecting a defect in that delta; document why omitted gates are irrelevant.

## Required static gates

From the Moodle root, the maintainer wrapper may run:

```bash
videotrack-update
moodle-test -p mod_videotrack -m 50,51,52,53 -c canon,lint,phpunit,behat
```

Add `grunt` whenever `amd/src` changes. The repository `phpcs.xml.dist` is the canonical full
`moodle-extra` gate and has no VideoTrack-specific exclusions. Any PHPCS error or warning is blocking.

Minimum direct checks when wrappers are unavailable:

```bash
find mod/videotrack -name '*.php' -print0 | xargs -0 -n1 php -l
php admin/tool/phpunit/cli/util.php --buildcomponentconfigs
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
```

For AMD changes, run Moodle's real Grunt task from the Moodle tree and distribute every changed `.min.js`
and `.map`. Syntax-only JavaScript checks do not replace Grunt/ESLint.

PHPDoc Checker is a blocking repository-CI gate with `--max-warnings 0`. PHPMD is advisory until a reviewed
Moodle-aware ruleset and explicit baseline exist. PHPStan and Psalm require committed bootstrap/stub configurations
that resolve Moodle symbols and exclude unrelated core/vendor code before either result can become a plugin gate.
Record every tool version, configuration and complete output; warnings are not silently suppressed. These analyzers
complement, but do not replace, Moodle PHPCS, PHP lint, PHPUnit, Behat or Grunt.

## Behavioural gates

- PHPUnit: all component tests, no failure, error, warning, notice or unexpected deprecation.
- Behat: `@mod_videotrack` across Moodle 5.0–5.3 for changes affecting browser-visible contracts.
- Manual provider smoke: public HTML5, YouTube and Vimeo when provider/network behaviour is relevant.
- Manual accessibility: keyboard, focus, reflow, forced colours and screen reader for UI changes.
- Lifecycle: fresh install, upgrade, backup/restore, reset, populated-gradebook uninstall and Privacy API when
  related code/schema changes.

The current distributed suites contain 274 PHPUnit tests / 2534 assertions and 24 Behat scenarios /
357 steps per supported Moodle branch. These numbers are expectations, not a pass claim.

## Repository continuous integration

`.github/workflows/ci.yml` applies the maintained `moodle-plugin-ci` v4 workflow model to six explicit
Moodle/PHP/database environments. It runs on pushes to `main` and `release/**`, pull requests and manual dispatch.
The workflow uses read-only repository permissions, retains no checkout credential and uploads command logs plus
Behat faildumps. See [`23_GITHUB_ACTIONS_CI.md`](23_GITHUB_ACTIONS_CI.md) for the exact matrix, strict/advisory
classification and result interpretation.

The workflow limits Grunt to the AMD task. `moodle-plugin-ci grunt` removes the installed build directory,
regenerates it, compares the result with the backed-up plugin and fails when tracked artifacts are stale; it then
restores the installed copy. Its exit status and retained `grunt.txt` are therefore the authoritative build gate,
not a later Git diff against the untouched repository checkout. The workflow also locates the installed plugin and
Moodle's CLI installer independently, so the ordinary database used by the strict VideoTrack validator is installed
in both the classic Moodle tree and the Moodle 5.1+ `public/` layout.

## Schema and data checks

- edit `db/install.xml` with XMLDB-compatible definitions;
- add an idempotent `db/upgrade.php` step and savepoint for installed sites;
- verify fresh install and upgrade separately;
- compare installed schema through `cli/validate.php --json`;
- inspect data for orphans before adding keys or changing null/default semantics;
- validate backup/restore and Privacy API against the changed relations.

## Patch and package validation

Patch files are generated from the plugin root with relative paths:

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

GNU `patch` cannot apply Git binary-diff records. When a delta adds or changes a binary asset, `git apply` is the
authoritative patch path and the GNU dry run is expected to stop at that record; use the complete release ZIP when
Git is unavailable. Also verify application, rollback, reapplication rejection and byte/mode identity with the candidate. A release
ZIP must contain one top-level `videotrack/` directory, no unsafe/duplicate paths and no development-only files.
Rebuild twice and require identical SHA-256 when reproducible packaging is part of the gate.

## Release evidence

Record package SHA-256, commit/tag, Moodle/PHP/database versions, exact commands, test counts, failures/warnings,
manual smoke results and any deferred gate. Do not promote a candidate while a required result is missing.

The read-only `cli/validate.php` installation validator and
`cli/benchmark_course_analytics.php` Analytics benchmark are documented in
[`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md); browser coverage is in
[`22_BEHAT_BROWSER_TESTS.md`](22_BEHAT_BROWSER_TESTS.md), and repository automation is in
[`23_GITHUB_ACTIONS_CI.md`](23_GITHUB_ACTIONS_CI.md).
