# Documentation audit

Current candidate: **1.7.271** (`2026112401`), based on the actual accepted270 ZIP.

## Accepted270

483 distributed files match main63325ff0923543bda9ae3548a2f713cb80bae4b9,
tree61bc66351d2a1cb24508049aa123011a9544bbe8; ZIP SHA256
3927f8ec40f199f52fe5d5f92d490f648244cdbcb59fea17569bc06e1711aa2d.
Corrected candidate45392734528/main45424709649 pass six jobs each, Unit620/6483,
Behat78/1458 without retries, native Grunt, strict10/0/0, JS regressions and both
historical upgrade/repeat/populated-uninstall matrices with verified snapshots.
Server report20261124_173403 passes eight commands: canonical/lint203shared,
Unit620/6483 separately on50/53 and both unchanged native Grunt outputs. Both user
strict reports pass10/0/0. The user confirms demo data and graphics are good; retain them.

PHPMD36 advisories remain visible. Psalm retains the standard MOODLE_INTERNAL guard
ParadoxicalCondition at db/services.php25 and758/786information entries; PHPStan is
clean. These are accepted270 results, not proof of current271 execution.

## Current changes and gates

The [activity reference guide](41_MICROSOFT_ACTIVITY_REFERENCES.md) documents metadata,
permissions, explicit destination binding, portable backups and no automatic access
grant. XMLDB adds one nullable field (152 total); nine AJAX methods and48AMDsets remain.
The lifecycle fixture expects the new null field while preserving every prior snapshot
assertion. The urgent tracking/recovery implementation and its temporary option remain.

Counts: 516 repository/490 distributed files, 206 distributed PHP,
1582 named PHP including seven CI helpers,660AMDcallables,640 unit methods,
80expanded scenarios/1484steps and8x1076language keys. All620prior unit methods,
78scenarios and their assets are byte-identical. Paired inventories use actual positions.

Local checks cover source/lexical/upgrade contracts, preserved learner-facing code,
language/placeholder parity, exact existing maps, JS tracking regressions and reversible
patch application. PHP/Moodle/MariaDB are unavailable locally: native271 lint/PHPCS,
PHPUnit, Behat, Grunt, static analysis, lifecycle and server checks remain pending.
Live OAuth/search/preview/browser events and trustworthy learner telemetry still need
HTTPS and the real account. Microsoft stays unavailable as a tracked source. No new
plugin ZIP is generated: the maintainer builds it after accepted main CI. App/provider
roadmap and manual5.3accessibility retain their previous ordering/standby status.

Four auxiliary legacy Node scripts (backup_restore_static, privacy_static, performance_static,
deprecation_static) still fail on both accepted270 and271: they expect earlier source
shapes or docs/struttura_tecnica.md. They are unchanged and are not native CI results.
The tracker harness, six source/six compiled lifecycle groups and adapter checks pass.
