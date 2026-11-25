# Documentation audit

Current candidate: VideoTrack **1.7.271** (`2026112401`), based on the actual accepted270 ZIP.

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
patch application. Initial native271 results are recorded below. PHP/Moodle/MariaDB
remain unavailable locally; corrected-candidate CI, lifecycle and server checks are pending.
Live OAuth/search/preview/browser events and trustworthy learner telemetry still need
HTTPS and the real account. Microsoft stays unavailable as a tracked source. No new
plugin ZIP is generated: the maintainer builds it after accepted main CI. App/provider
roadmap and manual5.3accessibility retain their previous ordering/standby status.

Four auxiliary legacy Node scripts (backup_restore_static, privacy_static, performance_static,
deprecation_static) still fail on both accepted270 and271: they expect earlier source
shapes or docs/struttura_tecnica.md. They are unchanged and are not native CI results.
The tracker harness, six source/six compiled lifecycle groups and adapter checks pass.

## Initial271 CI and corrective follow-up

Run45485426732 tests commit9a08e4111f6bd6f1ddb25e6f81010108cc06dcae,
tree751c68247bf7aaf8adcd6ec91429141c6b0f12f1. All six matrices run640unit methods/
6610assertions with exactly one failure: the existing release-documentation test
requires the literal `VideoTrack **1.7.271** (` + version marker in both audit headers.
The headers omitted the product name. All20new functional methods pass, including
four native course backup/restore cases. Correct both document markers, retaining
the existing test and every assertion unchanged.

The quality job reports15PHPCS formatting findings: seven multiline-foreach spacing
findings in microsoft_activity_reference_test.php and eight multiline-constructor
argument findings in microsoft_reference_restore_test.php. Correct only whitespace;
the complete executable token streams remain identical. Refresh both callable
inventories to the resulting line positions. No runtime, schema, language, AMD or
workflow change belongs to this correction.

All six matrices pass Behat80scenarios/1484steps without reruns, strict10/0/0,
native Grunt and the existing tracker plus six source/six compiled lifecycle groups.
The six embedded native archives and six separately uploaded wrappers agree exactly:
48source hashes,96build/map hashes, the submitted commit id, actual capture-command
provenance and capture-log digest all match. Their AMD bytes equal submitted271.
Lint206PHP and PHPDoc pass; the separate lifecycle-helper lint/PHPCS passes. PHPStan
is clean on50/53. Psalm retains one standard guard finding plus763/791information
entries. PHPMD reports40advisories; these remain visible and are not suppressed.

Every job fails overall because of the documentation assertion, and the quality
job additionally fails PHPCS. Both conditional historical lifecycle stages are
skipped, so no271upgrade/uninstall success is claimed. The corrected candidate
requires a fresh complete CI run before merge and the usual main/server checks.
The separately reproduced YouTube report-replay race is queued for the next release;
its source fix and tests are kept outside this271correction.
