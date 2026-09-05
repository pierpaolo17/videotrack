# PHPStan, Psalm and PHPMD

Static analysis is a staged hardening process, not a substitute for Moodle runtime tests. VideoTrack keeps the
configuration in the plugin so local, server and GitHub runs use the same production scope.

## Configuration and ownership

| Tool | Configuration | Initial policy | Purpose |
|---|---|---|---|
| PHPStan | `phpstan.neon.dist` | level 1, advisory baseline | Type and call-contract analysis. |
| Psalm | `psalm.xml` | error level 8, advisory baseline | Independent type/data-flow analysis. |
| PHPMD | `phpmd.xml` | reviewed findings advisory | Complexity, size, design, boolean flags and unused code. |
| Moodle PHPCS | `phpcs.xml.dist` | zero warnings, blocking | Moodle coding style, naming and compatibility rules. |

PHPStan and Psalm cover production directories and entry points only. They exclude tests, dependencies and Moodle
core from the project scan, but load core definitions from the installed site. PHPMD excludes tests, language
packs, documentation and tooling in its own ruleset. A tool crash, invalid configuration or missing report is not
an advisory finding: it is an infrastructure failure.

## Moodle bootstrap

`tools/static-analysis/bootstrap.php` loads the installed Moodle `config.php` before analysis. It uses
`MOODLE_ROOT` when set, then checks the deterministic roots implied by classic `mod/videotrack` and Moodle's
`public/mod/videotrack` layout. It never searches the complete filesystem and never selects generated `.types`
mirrors. The selected site must already be installed and its database must be reachable.

## Maintainer command

From the Moodle root, after synchronising the exact candidate:

```bash
moodle-test -p mod_videotrack -m 50,53 -c phpstan,phpdoc,phpmd,psalm
```

The runner uses the committed configurations. PHPStan advances from level 1 to 8 and Psalm from error level 8 to
1, stopping at the first failing level. PHPMD and PHPDoc use their real exit status. Retain the complete report and
each per-level log; do not report only the highest successful level.

For direct PHPStan/Psalm runs outside an installed plugin path, export the real root first:

```bash
export MOODLE_ROOT=/absolute/path/to/moodle
vendor/bin/phpstan analyse --configuration=/absolute/path/to/videotrack/phpstan.neon.dist
vendor/bin/psalm --config=/absolute/path/to/videotrack/psalm.xml --no-cache
```

## GitHub baseline jobs

The repository workflow installs the exact direct analyser versions declared in
`.github/static-analysis/composer.json`. PHPStan and Psalm run after ordinary-site installation on the Moodle 5.0
and 5.3 MariaDB jobs. Their full output and installed versions are retained as `phpstan.txt`, `psalm.txt` and
`static-tools.txt`. PHPMD runs in the canonical Moodle 5.0 quality job.

During the initial baseline, a PHPStan/Psalm non-zero exit is accepted as advisory only when its output contains a
recognisable completed-analysis summary. PHPMD exit 2 is likewise converted to an advisory finding result. Missing
summaries and every other tool/configuration failure remain blocking, so a green workflow proves that the analysers
executed and produced readable evidence even before their code findings reach zero.

## Reading and fixing findings

1. Reject results dominated by unresolved Moodle symbols, core/vendor paths or analyser setup errors.
2. Group valid findings by rule and common root cause; record counts by production file.
3. Fix one bounded group without changing public Moodle signatures or weakening runtime validation.
4. Run PHPCS, PHPDoc, lint and the affected PHPUnit/Behat/lifecycle gates after every code tranche.
5. Re-run all three analysers on Moodle 5.0 and 5.3 and compare exact reports, not only totals.
6. Make a tool blocking only when its accepted baseline is zero or a reviewed baseline policy is committed.

Suppressions are exceptional. Each suppression must be the narrowest supported form, adjacent to the unavoidable
framework contract, explain why code cannot safely change and have a regression test where behaviour matters.
Generated baselines, global ignore patterns and reduced analysis scopes must never be used merely to obtain green
output.

## Current status

The generic PHPMD run preceding this configuration found 215 issues in 42 files and is retained only as a
pre-ruleset reference. Earlier standalone PHPStan/Psalm outputs did not resolve Moodle and are invalid baselines.
The first 1.7.125 GitHub/server reports are therefore the starting point for code remediation and must be audited
before any finding is declared fixed.
