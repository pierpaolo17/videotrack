# GitHub Actions continuous integration

VideoTrack runs repository-level continuous integration through `.github/workflows/ci.yml`. GitHub provisions a
fresh Ubuntu runner for every matrix entry; the workflow starts only the selected database service and
`moodle-plugin-ci` installs the requested Moodle branch and VideoTrack into that temporary environment. Nothing is
installed on the production Moodle site and no repository secret is required.

## Triggers and permissions

The workflow runs for pushes to `main` and `release/**`, for pull requests and through manual
`workflow_dispatch`. Concurrent runs for the same workflow and ref are cancelled when superseded. The workflow
uses the minimum repository permission `contents: read`, and checkout does not retain Git credentials.

GitHub Actions must be enabled for the repository. A pull request from a fork receives no privileged token or
deployment credential from this workflow.

## Test matrix

| Moodle | Upstream branch | PHP | Database | Purpose |
|---|---|---:|---|---|
| 5.0 | `MOODLE_500_STABLE` | 8.2 | MariaDB | oldest supported branch and canonical quality job |
| 5.0 | `MOODLE_500_STABLE` | 8.2 | PostgreSQL | lower-bound database portability |
| 5.1 | `MOODLE_501_STABLE` | 8.3 | MariaDB | intermediate compatibility |
| 5.2 | `MOODLE_502_STABLE` | 8.3 | MariaDB | intermediate compatibility |
| 5.3 development | `main` | 8.3 | MariaDB | upper supported branch before a stable branch exists |
| 5.3 development | `main` | 8.3 | PostgreSQL | upper-bound database portability |

`fail-fast` is disabled, so one failure does not hide the outcome of the other environments. Moodle 5.3 uses
upstream `main` until Moodle publishes `MOODLE_503_STABLE`; changing that selector is an explicit maintenance
operation.

## Blocking checks

Every matrix entry installs the PHPUnit and Behat environments, runs the Moodle AMD Grunt task and its canonical
artifact comparison, installs the ordinary Moodle database, runs the VideoTrack read-only installation validator,
and then executes PHPUnit and Behat. The normal, PHPUnit and Behat schemas use their configured independent
prefixes. The canonical Moodle 5.0/MariaDB entry also runs PHP lint, PHPCS with zero warnings, PHPDoc with zero
warnings, plugin validation, upgrade-savepoint validation and Mustache lint.

The workflow uses `set -o pipefail` before piping output through `tee`, so a failing checker remains a failing
step. Console logs are also collected in a downloadable `videotrack-ci-<matrix-id>` artifact for 14 days. A Behat
failure uploads the browser faildump separately.

## Advisory static analysis

The versioned `phpmd.xml` selects reviewed clean-code, size, design and unused-code rules for production PHP and
excludes tests, language packs, documentation and tooling. Moodle PHPCS remains authoritative for naming and
framework conventions. Before this ruleset existed, the 1.7.123 generic run reported 215 findings in 42 files:
56 cyclomatic-complexity, 35 NPath, 34 long-variable, 23 excessive-method-length, 14 boolean-flag, 13
unused-parameter, 13 too-many-public-methods, eight unused-local-variable, three excessive-parameter-list and 16
other findings. That result is a pre-ruleset reference, not the current baseline. PHPMD exit 2 (findings) is
advisory; any different non-zero status is treated as a tool/configuration failure and blocks the job.

PHPStan 2.2.13 and Psalm 6.16.1 are pinned by `.github/static-analysis/composer.json`. On the Moodle 5.0 and 5.3
MariaDB entries, they run after ordinary-site installation against production VideoTrack paths only. Their
versioned configurations load `tools/static-analysis/bootstrap.php`; the explicit `MOODLE_ROOT` selects the exact
tested tree, with deterministic classic/`public/` fallback available to maintainer runners. PHPStan starts at level
1 and Psalm at level 8. Findings are temporarily
advisory while the first valid cross-branch baselines are collected; the retained `static-tools.txt`,
`phpstan.txt` and `psalm.txt` make tool identity and complete output auditable.

The former 1.7.122 fallback outputs are invalid baselines: PHPStan produced more than 1,000 predominantly unresolved
Moodle symbols, while Psalm mixed 1,712 plugin, core, vendor and tool errors. They must not be compared numerically
with the scoped Moodle-aware reports. Psalm exit 2 is its documented completed-with-findings result and is advisory;
exit 1 or any other non-zero status blocks the job. PHPStan exit 1 is advisory only with its normal error-count
summary and without internal or incomplete-analysis markers. Static findings are reviewed by root cause and
remediated in bounded tranches; the gates become blocking only after the accepted baseline is zero or an explicit
reviewed baseline policy exists.

## Generated AMD evidence

Grunt is explicitly limited to the `amd` task. `moodle-plugin-ci grunt` backs up the installed plugin, removes its
build directory, regenerates AMD artifacts, compares their content with the backup and fails when a tracked file is
missing or stale. It restores the installed plugin after the comparison, so a subsequent diff of the separate
repository checkout cannot validate the generated result. The retained `grunt.txt` log and step exit status are the
authoritative evidence. The broader Grunt lint groups are not mislabeled as the AMD build gate: CSS and JavaScript
lint debt must be handled by dedicated reviewed gates.

## Installation validator bootstrap

`moodle-plugin-ci install` prepares the isolated PHPUnit and Behat databases but does not install the ordinary site
schema used by `cli/validate.php`. Immediately after installation, the workflow resolves paths from the known Moodle
root in a deterministic order: `moodle/public/mod/videotrack` first and the classic
`moodle/mod/videotrack` second. Moodle's installer is always checked separately at
`moodle/admin/cli/install_database.php`.

The resolver never scans the complete workspace. This is important on Moodle branches whose Grunt `amdtypes` task
creates a generated `.types/amd/public/mod/videotrack` mirror: that directory resembles a plugin path but does not
contain an installable activity. A defensive check rejects any `.types` result. The validated paths are written to
the job environment once and reused by both ordinary-site installation and the strict validator. `paths.txt`,
`site-install.txt` and `videotrack-validate.txt` retain the path contract and command evidence. The administrator
password is generated inside the disposable job and is neither committed nor retained. The validator must report
zero failures in strict mode.

## Reading a result

1. Open **Actions → Moodle Plugin CI** and select the commit or pull request.
2. Check all six matrix jobs. Recognised PHPStan/Psalm/PHPMD finding reports are advisory during the declared
   baseline phase; a tool/configuration failure still fails the job.
3. Download the `videotrack-ci-*` artifacts for complete command output; verify `paths.txt`, the authoritative
   `grunt.txt`, `site-install.txt`, `videotrack-validate.txt` and, where present, `static-tools.txt`, `phpstan.txt`
   and `psalm.txt`.
4. For Behat failures, download the corresponding faildump and inspect screenshots, HTML and browser diagnostics.
5. Associate every result with the tested commit SHA. A later commit requires a new run.

GitHub CI complements the maintainer's real installation, upgrade, backup/restore, privacy, provider and manual
accessibility checks. It does not turn a green isolated runner into evidence about production data or external
provider availability.
