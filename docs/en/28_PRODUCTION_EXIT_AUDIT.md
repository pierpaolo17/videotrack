# Integrated lifecycle and production evidence

Current candidate: VideoTrack **1.7.255** (`2026111300`). Audit recorded 2026-11-13.

## Candidate 1.7.255: benchmark viewer context

The accepted baseline is actual 1.7.254/main a42432a (415 matching package files, clean 168-file server PHPCS/lint,
385/4540 Unit on 5.0/5.3, six-job candidate/main Behat 52/1154 and selected lifecycle paths). Its latest helper
correction is accepted; earlier correction-pending statements below are historical checkpoints.

Candidate 255 changes only the benchmark CLI and release documentation/metadata: select the active full viewer
record, set the in-process current user before measurement and expose current_userid. The source-controlled
Analytics, capability/group/privacy rules, schema, tracking, lifecycle and AMD are unchanged. Both CLI guides
remove the unsupported benchmark --json example and document the independent privacy default 2.

Native 254 profiler evidence with minusers 5 preserves all 40 results when changing current-user 0 to viewer 10,
with 218-to-92 course queries and 9-to-6 single queries. This calibrates the CLI context rather than demonstrating
a product performance optimisation. See [the CLI guide](21_CLI_DIAGNOSTICS.md) for density and timing limits.
New 255 CI/server/strict results and the shipped CLI rerun are pending. File/callable inventories are unchanged.
Manual Moodle 5.3 remains on standby; worst-case/concurrent benchmarking and the explicit MoodleApp decision remain open.


## Accepted baseline

Actual **1.7.253 / 2026111203** is accepted: all 415 ZIP files match main
`511234197069f8f83e23f0da9444fa1d6a5f8030` and the approved candidate.
Package SHA-256: `b7a1643ba5502424af714302bba9d99d61dc29b3820cbcc751b84ac16187522d`.
The 10-file server report has SHA-256
`d2e8591de33b511a8343e86532d4ef49df34773d6a262daf5eac3e86a2e06322`.
Candidate/main six-job CI and both Moodle 5.0/5.3 server reports pass PHPUnit **384/4516**.
Behat passes **52 scenarios / 1154 steps** in all six CI environments. PHPCS/lint cover 167 clean
distributed PHP files; Grunt preserves AMD build hashes and both supplied strict outputs are **10/0/0**.
The registered-task post-restore retention case is now accepted within its documented scope: unlimited/finite
policy, repeated cleanup, complete input expiry and preserved shared configuration/files/core grade rows.
Backup/restore/privacy, standard course reset and synchronous activity-deletion integration remain accepted.
PHPMD's 24 reviewed findings and Psalm's 732/760 advisory counts remain unchanged; configured scopes pass.

## Initial candidate evidence and same-version correction

All six artifacts match commit `22466b7cb7c56decb27ba9154e1f7b158e5daebf`,
run **43297863869**, attempt **1**. All six pass PHPUnit **384/4516**, Behat **52/1154**,
strict **10/0/0** and Grunt. PHPCS/lint cover 167 clean files. The overall run has four
successful jobs and two failed lifecycle steps, on Moodle 5.0/MariaDB and 5.3/MariaDB.

Both selected jobs really install 237, populate the fixture, upgrade to 254 and pass strict.
The repeated upgrade preserves the complete canonical snapshot hash in each job. The full uninstall
removes plugin tables, module/course-module/context rows, file metadata and active grades, then the
gate fails on **`course_modules_completion`**. Checks after that assertion, and the partial/previous
lineages, have not run. Those remaining checks are not declared passed.

The normal full Moodle uninstall calls the plugin hook before `core\plugininfo\mod::uninstall_cleanup()`.
On both inspected branches, that cleanup directly removes course modules and contexts without clearing
their native completion rows. This differs from synchronous activity deletion. Add scoped completion
cleanup to VideoTrack's early uninstall hook, after grade cleanup and also on its no-grade early return.
The dedicated helper requires a positive registered module id and deletes only completion rows whose
course module still belongs to VideoTrack. It does not guess ownership after a missing module registry,
repair unattributed historical orphans, or touch another module's completion.

A new hook regression uses two VideoTrack activities in two courses and a neighbouring Forum. It checks
graded and no-grade paths, repeated execution, unchanged foreign completion and the zero-identity guard.
The pre-existing grade-repair and malformed-grade fallback code stays intact. No schema, version code,
language, AMD, tracking/player, role or CI-rule change is made in this same-254 correction.

The initial snapshot compares **14 file metadata rows**, including seven regular files, but its content
loop passed `false` as the file area. Moodle treats that as an area value, not an all-areas selector;
the loop visited no files. Do not describe the initial run as having read their contents. Correct the
helper to enumerate all seven named fixture areas, require exactly one regular file per area and read
each file to verify its hash. This closes a vacuous-check gap without weakening any residue assertion.

That correction subsequently passed all six jobs and all three lineages in both selected jobs.
The corrected PHPUnit suite measures **385 tests / 4540 assertions**; Behat remains **52/1154**.
The initial 384/4516 result belongs only to the initial code, not to the corrected candidate.

## Confirmed CI and actual server rejection

Corrected candidate `7561ed4dc9fdb73766c8d824be8ddbf6b606d547`, run **43304629270**, and
merged main `7145a2fdf03941f4f71a8f0d38b1912d9be620d2`, run **43315619158**, each pass all six jobs.
Both measure PHPUnit **385/4540**, Behat **52/1154**, strict **10/0/0** and all three lifecycle
lineages on both selected jobs. All six independent upgrade/repeat snapshot pairs match. Each selected
job performs **63** physical shared-file hash checks across seed/upgrade/repeat and the three lineages.

The actual 415-file ZIP matches that approved main source exactly. Package SHA-256:
`1a78bf8360034f425ba86549bc78c9bd96613e822f161bff226f9678ad14dd5a`.
Server report `20261113_091431`, SHA-256
`56b19571d624cffe10b05395ce9e4cfa8f3c275de44ed2ee6537631eb2ba8143`, passes lint on **168** PHP files,
Grunt with unchanged AMD hashes and PHPUnit **385/4540** on both PHP 8.3.6 Moodle 5.0/5.3 sites.
Both supplied strict outputs are **10 PASS / 0 WARN / 0 FAIL**; grade item/row counts are **0/0**
and **33/33**, respectively. Strict is user-supplied CLI evidence, not a local execution.

The report overall is **FAIL**: canonical PHPCS checks 168 files and reports three errors, all in
`.github/lifecycle/site.php`: incomplete GPL boilerplate, global assignments before config inclusion,
and an inline docblock without a variable type annotation. Earlier CI PHPCS checked only the 167
distributed files and skipped this hidden repository-only helper. Actual253 remains the accepted baseline.

Correct within the same release: complete the boilerplate, retain the exact environment/path guard in
`videotrack_ci_root()` called by the first config.php inclusion, and replace the inline docblock with
a normal comment. Keep guard rejection before bootstrap. The quality job explicitly lints this helper
and checks it with the project canonical `moodle-extra` ruleset; retain `phplint-lifecycle.txt` and
`phpcs-lifecycle.txt` in the existing artifact. No rule suppression or production PHP change is added.

## New CI gate: actual installation lineage and full uninstall

After successful PHPUnit and Behat, the Moodle 5.0/MariaDB and 5.3/MariaDB jobs reuse their disposable
standard validator databases. The helper checks the Actions workspace, dataroot, site identity and database
prefix. It refuses ordinary machines, PHPUnit/Behat databases and a populated initial VideoTrack site.
The normal installer makes a copy of the checkout. The runner also detaches a possible plugin-directory
symlink before replacing code; neither the checkout nor database versions are rewritten to fake an upgrade.

| Lineage | Actual installed source | Added coverage |
|---|---|---|
| historical | Accepted 1.7.237 / `2026110507`, commit `baa07ab580c15f8924ecb812a5832231e981bed2` | Upgrade from the real 7-table/149-field schema to the current 151-field schema, keeping existing rows and new zero defaults. All 402 actual baseline ZIP files match that immutable commit. |
| partial | The same actual 1.7.237 | Simulate only the first field DDL of step `2026110601`, leaving the second absent and installed version untouched. Then run the normal upgrade. This is a constructed partial state, not a killed process or power-loss test. |
| previous | Accepted 1.7.253 / `2026111203`, commit `511234197069f8f83e23f0da9444fa1d6a5f8030` | Upgrade from the immediately preceding actual version; preserve explicit 120/600 attention settings and existing learner/configuration data. |

For each lineage, the runner:

1. Installs the exact old source using Moodle's real `admin/cli/upgrade.php`, then verifies the actual version.
2. Creates a course, learner, neighbouring Forum, configured VideoTrack, all seven populated plugin families,
   reaction/note/bookmark events, shared files, an active grade and native completion. Fixture rows are synthetic.
3. Records a canonical baseline snapshot, including actual shared-file content hashes and explicit site settings.
4. Deploys the current committed source, runs real Moodle upgrade and strict, and compares preserved data.
   Only the two legitimately added zero-default columns may extend the historical activity row.
5. Repeats the upgrade; both complete snapshots must have the same SHA-256. Failure stops before uninstall.
6. Calls Moodle's full `admin/cli/uninstall_plugins.php --plugins=mod_videotrack --run`.
7. Checks absence of plugin tables, module/course-module/context rows, active grades/completion, shared file
   metadata, plugin configuration, capabilities/grants, external methods/service bindings and registered tasks.
   The neighbouring Forum/files, course identity and learner must survive exactly within the asserted scope.

The initial empty current-plugin uninstall is preparation only. Three populated lineages on two selected jobs
mean six new lifecycle paths, recorded inside the existing six artifacts; there is no new artifact family.
The target code is restored on script exit, but database rollback after a failure is not claimed or attempted.
Core grade history, configuration/event audit history and File API trash/blob retention remain governed by
Moodle. Operational residue checks do not promise immediate physical erasure of content-addressed blobs.

The CI helper is PHP-linted before lifecycle execution. The quality job also explicitly runs helper lint
and canonical PHPCS, covering the hidden file omitted by the ordinary plugin scan. It is excluded only
from the production PHPMD directory filter (`*/.github/lifecycle/*`). Every previously analysed production
file and all PHPMD rules remain in scope.

## Evidence and release gate

The second same-version correction is **pending native validation**. Earlier six-job CI and server
results above describe main7145a2f, not this corrected helper/workflow. No local native PHP/PHPCS run
is claimed. Require new candidate/main six-job CI, clean explicit helper reports, all three lifecycle
paths on both selected jobs, then actual ZIP/server and strict evidence before accepting254.

Expected PHPUnit remains **385 tests / 4540 assertions**, Behat **52/1154**; CLI assertions are separate.
The release keeps **415 distributed files**, **167 distributed PHP files**, **1116 distributed
PHP callables**, **654 AMD callables** and **32 documents per language**. Two repository-only helpers add
seven PHP functions: **1123** named PHP callables overall and **348** file-inventory rows, including five
documented repository-only files. The full repository has 439 files; `.github/**` remains export-ignored.
XMLDB stays `2026110601`, **7/151/22/22**; nine AJAX services, eight packs of 997 keys and 48 AMD sets remain.
The only production correction since the initial 254 is scoped native-completion cleanup in the uninstall
hook. Pre-existing unit tests and schema/language/AMD/grants/CI rules are retained; one hook test and the
nonvacuous physical-file checks are added. Version remains 1.7.254 / 2026111204.

## Remaining production gate and roadmap

These bounded histories do not certify every old deployment lineage, cross-site MBZ migration, real process
interruption, concurrent faults or cron scheduling. Large-dataset Analytics benchmarks and manual Moodle 5.3
accessibility observations remain unrecorded. Retain the eight reported PASS on 248/Moodle 5.0/Academi/
Windows 11/Chrome/NVDA within their scope. Dedicated Moodle App support remains undeclared.
U-017/U-007 remain closed; finish the production/manual/benchmark gate and explicit App decision, then
agreed multilingual VTT, provider authentication, OneDrive/SharePoint → Zoom Cloud Recording → Google Drive.
Accept 254 only after candidate/main CI and actual ZIP/server/strict evidence.

See [lifecycle](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [CI](23_GITHUB_ACTIONS_CI.md),
[privacy](18_PRIVACY_RETENTION.md), [benchmarks](21_CLI_DIAGNOSTICS.md) and [manual evidence](19_ACCESSIBILITY.md).
