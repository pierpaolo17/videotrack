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
`public/mod/videotrack` layout. It then loads the stable upgrade, CLI, group, Forum external, admin and form APIs
plus Moodle's canonical backup and restore include graphs before the Moodle 2 step libraries used by the production
scope. The order matters because the step libraries declare subclasses immediately. This explicit bootstrap is
necessary because the analysers do not reliably follow every variable-based legacy require. It never searches the
complete filesystem and never selects generated `.types` mirrors. The selected site must already be installed and
its database must be reachable.

`psalm.xml` also loads `tools/static-analysis/moodle-legacy-aliases.phpstub`. The stub statically declares the global
`renderable` compatibility name as extending `core\output\renderable`. This represents Moodle 5.0's runtime alias in
a form Psalm can index; stub files are parsed for declarations, so an executable `class_alias()` call is ineffective.

Psalm's optional `ensureOverrideAttribute` policy is explicitly disabled. The native `#[\Override]` attribute is a
PHP 8.3 feature, while the supported Moodle 5.0 matrix includes PHP 8.2. Inheritance and signature analysis remain
enabled; only the incompatible attribute-presence style rule is disabled.

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

During the initial baseline, Psalm exit 2 is accepted as its documented completed-analysis-with-findings result;
exit 1 and every other non-zero status remain blocking failures. PHPStan exit 1 is advisory only when its normal
error-count summary is present and no internal/incomplete-analysis marker occurs. PHPMD exit 2 is likewise converted
to an advisory finding result. This keeps code findings visible without accepting crashes or partial reports as
valid evidence.

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
The first 1.7.125 GitHub reports are rejected as remediation baselines. On Moodle 5.0, PHPStan reported unresolved
legacy parent classes and Psalm stopped with an internal `renderable` storage error; Moodle PHPCS also identified
the standalone bootstrap's intentional pre-Moodle global state. On Moodle 5.3, Psalm completed with findings but
the workflow misclassified its documented exit 2 as a tool failure. Release 1.7.126 corrected the PHPCS and exit
classification defects, but its analyser bootstrap loaded `backup_stepslib.php` before `backup_execution_step` and
both static jobs stopped at startup. Release 1.7.127 then allowed PHPStan to complete with 366 findings on both
branches and Psalm to complete with 468 findings on Moodle 5.3. Psalm 6.16.1 still aborted on Moodle 5.0 because
Moodle's runtime `renderable` class alias had no Psalm class storage. Release 1.7.128 placed the executable
`class_alias()` mapping in a configured stub, but the 1.7.128 report proved that Psalm does not process that
statement as a class declaration there and repeated the same exception. Release 1.7.129 replaces it with a static
global interface declaration that Psalm can index. The 1.7.129 matrix then completed successfully: PHPStan reported
366 findings and Psalm 468 findings on both Moodle 5.0 and 5.3, while the reviewed PHPMD ruleset reported 161
findings. Release 1.7.130 begins remediation by loading the real Moodle upgrade, CLI, group and Forum external API
definitions needed by the production scope; no runtime file or analysis scope is changed.
Release 1.7.131 then disables the optional override-attribute policy because applying its suggested native attribute
would violate the supported PHP 8.2 minimum. The 104 resulting style findings are not accepted as code defects.
The verified 1.7.131 matrix reports 137 PHPStan findings, all with the `variable.undefined` identifier, and 136
Psalm findings on each analysed Moodle branch. Release 1.7.132 documents Moodle's loader-injected global variables
with narrow `@var` declarations in the ten affected production files. These declarations are analysis-neutral at
runtime and replace neither framework values nor executable initialisation.
The 1.7.132 matrix confirms zero PHPStan findings and 57 Psalm findings on both branches. Release 1.7.133 changes
the three annotations that Moodle PHPCS rejected in `settings.php` and `version.php`: Psalm uses its native typed
`globals` configuration, while PHPStan has two path-specific rules matching only the framework-injected names.
Unmatched-ignore reporting remains active, and all analyser scopes are unchanged.
The 1.7.133 matrix confirms PHPStan remains clean and Psalm completes with 56 findings on Moodle 5.0, but rejects
the Moodle 5.3 Psalm run because the runtime `admin_root` compatibility alias has no Psalm class storage. Release
1.7.134 keeps the globals typed through two minimal analysis-only contracts in the existing stub, covering only
`$ADMIN->fulltree` and `$settings->add()` without changing production code or using `mixed`.
The verified 1.7.134 matrix completes with zero PHPStan findings and 56 identical Psalm findings on both branches.
Release 1.7.135 qualifies twelve global PHPDoc type names in `tracker`, targeting thirteen direct and sixteen
propagated `UndefinedDocblockClass` findings. The remaining `xmlddb_field` report originates in Moodle's
`xmldb_table::add_field()` return DocBlock and remains visible rather than being hidden by a plugin suppression.
The verified 1.7.135 matrix reports zero PHPStan findings and 27 identical Psalm findings on both branches.
Release 1.7.136 adds Moodle's stable `CONTEXT_MODULE = 70` value to the Psalm-only stub because the analyser does
not index the runtime `define()` reached through `accesslib.php`. This targets the six remaining
`UndefinedConstant` findings without modifying production code or suppressing other issue types.

The verified 1.7.136 matrix showed that the global stub declaration did not resolve unqualified constant lookup
inside VideoTrack namespaces: Psalm remained at 27 findings, including all six `UndefinedConstant` results. Release
1.7.137 replaces it with equivalent analysis-only declarations in `mod_videotrack\local` and
`mod_videotrack\privacy`, the two namespaces that contain the affected uses. Runtime resolution remains unchanged.

The verified 1.7.137 matrix showed that Psalm 6.16.1 also ignored the namespaced constant declarations: both Moodle
branches remained at the same 27 findings. Release 1.7.138 uses Moodle's public `\core\context\module::LEVEL` class
constant for the six affected references and removes the ineffective stub declarations. Moodle documents this
autoloadable constant as the numeric context level matching legacy `CONTEXT_MODULE`, so runtime semantics remain
unchanged while the analyser receives a normal class-constant symbol.
