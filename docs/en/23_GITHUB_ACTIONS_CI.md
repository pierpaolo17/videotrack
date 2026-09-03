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

Every matrix entry runs installation, Moodle Grunt, the VideoTrack read-only installation validator, PHPUnit and
Behat. The canonical Moodle 5.0/MariaDB entry also runs PHP lint, PHPCS with zero warnings, PHPDoc with zero
warnings, plugin validation, upgrade-savepoint validation and Mustache lint.

The workflow uses `set -o pipefail` before piping output through `tee`, so a failing checker remains a failing
step. Console logs are also collected in a downloadable `videotrack-ci-<matrix-id>` artifact for 14 days. A Behat
failure uploads the browser faildump separately.

## Advisory checks

PHPMD is initially `continue-on-error`. Its generic rules flag required Moodle names such as `$DB`, prescribed
backup/restore class names and static Moodle APIs; the output is evidence for refactoring, not a safe mechanical
rewrite list. It may become blocking only after a reviewed VideoTrack ruleset and baseline exist.

PHPStan and Psalm are not enabled by this workflow yet. The 1.7.120 fallback run did not load a complete Moodle
analysis environment: PHPStan stopped after more than 1,000 unresolved Moodle symbols and Psalm analysed Moodle
core/vendor files as well as the plugin, returning 1,712 errors. Adding either tool requires a committed,
reproducible bootstrap/stub configuration that limits findings to VideoTrack and is verified across supported
branches.

## Generated AMD evidence

After Grunt, the workflow records `git diff -- amd/build` in `amd-build.diff`. A non-empty diff emits a workflow
warning and remains available in the report artifact. This is advisory during CI bootstrap; once every tracked
build and source map is canonical, the check can be promoted to a blocking clean-tree assertion.

## Reading a result

1. Open **Actions → Moodle Plugin CI** and select the commit or pull request.
2. Check all six matrix jobs; a green summary with a failed advisory PHPMD step is expected only while the PHPMD
   baseline is explicitly documented.
3. Download the `videotrack-ci-*` artifacts for complete command output and inspect `amd-build.diff` warnings.
4. For Behat failures, download the corresponding faildump and inspect screenshots, HTML and browser diagnostics.
5. Associate every result with the tested commit SHA. A later commit requires a new run.

GitHub CI complements the maintainer's real installation, upgrade, backup/restore, privacy, provider and manual
accessibility checks. It does not turn a green isolated runner into evidence about production data or external
provider availability.
