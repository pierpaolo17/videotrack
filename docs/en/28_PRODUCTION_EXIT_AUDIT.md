# Integrated lifecycle and production evidence

Current candidate: VideoTrack **1.7.254** (`2026111204`). Audit recorded 2026-11-12.

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

The new CI helper is PHP-linted before execution and excluded only from the production PHPMD directory
filter (`*/.github/lifecycle/*`). Every previously analysed production file and all PHPMD rules remain in scope.

## Evidence and release gate

The candidate's native lifecycle execution is **pending**. No PASS for the new paths is inferred from local
patch checks or the previous unit uninstall-hook test. The six normal CI jobs must remain green, and both
selected jobs must finish `Historical upgrade and populated uninstall` successfully. Inspect `lifecycle.txt`
and the three before/upgrade/repeat/uninstall sets under `lifecycle/` in each selected artifact.

Existing PHPUnit remains **384 tests / 4516 assertions** and Behat **52/1154**; the new CLI assertions are
separate. The release keeps **415 distributed files**, **167 distributed PHP files**, **1114 distributed
PHP callables**, **654 AMD callables** and **32 documents per language**. Two repository-only helpers add
six PHP functions: **1120** named PHP callables overall and **348** file-inventory rows, including five
documented repository-only files. The full repository has 439 files; `.github/**` remains export-ignored.
XMLDB stays `2026110601`, **7/151/22/22**; nine AJAX services, eight packs of 997 keys and 48 AMD sets remain.
Production implementations, all existing tests, schema, language, AMD and grants are byte-identical to 253;
only the release marker, repository CI helpers/workflow, documentation/inventories and ER label change.

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
