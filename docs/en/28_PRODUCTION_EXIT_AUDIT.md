# Integrated lifecycle and production evidence

Current candidate: VideoTrack **1.7.251** (`2026111201`). Audit recorded 2026-11-12.

## Accepted baseline and purpose

Actual **1.7.250 / 2026111103** is accepted: 412 archive files match main
`7a5b5f391dcc1ef0fd63f8640ce325e93f9ada25`. Package SHA-256:
`69820512260e51382a463d3b0986e9dd89154a6d1774776483908b9ba63666e8`.
The 10-file server report has SHA-256
`34688bc88280d367e475f86e9a8345959939be4bb84460ff54884e474f0a80f2`.
Both Moodle 5.0/5.3 reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076; both supplied strict outputs
pass 10/0/0. AMD build hashes before and after Grunt are identical. These are maintainer/server and CI
observations; no native Moodle execution occurred in this workspace.

The corrected candidate and merged CI pass 52 Behat scenarios / 1154 steps in each selected successful
report. The first merged Moodle 5.3/MariaDB job lost its local test webserver and failed; a fresh job passed
the same source. Preserve that history and the earlier PHPCS correction. Do not claim six clean first attempts
or an established cause for the test server stopping. Configured static scopes pass; PHPMD has 24 reviewed
findings and Psalm has 733/761 advisory issues, unchanged from corrected 250 and one above 249 per branch.

Release 251 adds two meaningful integration tests. Existing real duplication tests cover attention settings;
the new cases connect real course backup/restore to learner evidence, shared files and Privacy API operations.
All existing production logic, role grants, services, languages, DB and AMD assets are byte-identical to 250.

## New test cases and their limits

`tests/lifecycle_integration_test.php` uses Moodle's real backup/restore controllers and a new destination
course. It follows the synchronous controller pattern used by Moodle's own backup tests on branches 500/503.
`MODE_IMPORT` keeps the real XML/file working tree and skips archive compression; this is not an independent
MBZ-download/extract, cross-site import or historical-backup compatibility certification.

| Case | Persisted boundaries checked |
|---|---|
| Without user data | Attention overrides/duration, linked Forum remapping, soft-deleted reaction and icon remapping, one canonical grade item, seven shared file areas including two transcript languages; no plugin learner data, grades or completion copied; original plugin learner records unchanged. |
| With user data | Source backup includes old and current rows under unlimited retention; destination retention is tightened before restore. Old rows are excluded, unvalidated rows cannot inflate viewed state, stale aggregate/native completion is rebuilt, records point to the new course/module, Privacy API exports only the approved learner, user erasure preserves the other learner, context erasure clears the remainder, shared configuration/files and core-owned grade rows survive, original plugin learner records remain unchanged. |

The fixture deliberately contains stale complete state and completion rows, retained validated segments,
unvalidated segments, reaction/note/bookmark events, integrity events and acknowledgement versions. These
records are test inputs, not a claim that a browser generated them. Real browser tracking evidence remains
the accepted U-007 suite. The two new cases require native candidate CI before their results can be accepted.

Expected candidate suite: **381 PHPUnit test methods**; assertion totals must be measured by native CI.
Behat remains **52 scenarios / 1154 steps**. The current tree contains **415 files**, **167 PHP files**,
**1110 named PHP callables**, **654 AMD callables**, **346 file-inventory rows** and **32 documents per
language**. XMLDB remains `2026110601`, 7 tables / 151 fields / 22 indexes / 22 foreign keys; nine AJAX
services, eight packs of 997 keys and 48 AMD source/build/map sets are unchanged. ER changes only its version label.

## Production gate status

| Area | Evidence and remaining boundary |
|---|---|
| Exact package, schema, services, language/build contracts | Actual 250 accepted. Candidate 251 still needs native CI, merged CI and actual package/server/strict acceptance. |
| Fresh install and supported Moodle matrix | Covered by ordinary CI installation/validation on 5.0–5.3; do not infer historical upgrade execution from this. |
| Historical/interrupted upgrade and full uninstall | Source contracts and existing targeted tests retained. A disposable-site historical upgrade/uninstall with zero-residue verification remains a separate production observation. |
| Backup/restore and post-restore privacy | Existing settings duplication and scoped privacy tests pass on 250. The two cross-course integration cases above are pending on 251. |
| Course/student reset, role/group scope, native completion | Accepted unit/browser contracts retained, including the two real direct-reset POST cases from 250. |
| Retention | Existing deletion/rebuild tests retained; destination-retention integration added here. No scheduler timing or fault-injection execution is claimed. |
| Analytics and benchmark | SQL/index/scope/aggregation code is unchanged. No new benchmark was run or inferred from test/query counts. Large-dataset, multi-activity/group and 7/30/90-day observations must be recorded with versions and dataset/cache conditions where required for the final deployment gate. |
| Manual accessibility U-020 | Eight maintainer-reported PASS on 248 / Moodle 5.0 / Academi / Windows 11 / Chrome / NVDA remain valid within that scope. Moodle 5.3 observations are unrecorded; no new manual execution or WCAG certification is inferred. |
| Moodle App | Dedicated support is not declared (`db/mobile.php` keeps an empty addon list). Scope/support decision follows the remaining web gate; no WebView/native/offline parity is claimed. |

U-017 and essential U-007 remain closed. This release advances the integrated lifecycle evidence; it does not
close the whole production gate, deferred manual observations or the App decision by documentation alone.
Do not add cosmetic refactors or artificial releases to manufacture gate completion.

## Release and roadmap order

Run the existing candidate CI and normal server PHPCS/lint/Grunt/PHPUnit checks; they include the new tests.
Keep the same release for any candidate correction. Accept a new baseline only from its actual merged ZIP,
reports and strict outputs. Historical upgrade/uninstall, benchmark and manual deployment observations remain
explicit evidence items, rather than expected results prefilled from source review.

Finish the earlier web/lifecycle/privacy/benchmark/manual work and the explicit App decision; then implement
the agreed multilingual VTT subtitle phase. Continue with authenticated-provider foundation,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive, followed by cross-provider hardening.

See [lifecycle](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [privacy](18_PRIVACY_RETENTION.md),
[CLI/benchmark](21_CLI_DIAGNOSTICS.md), [manual evidence](19_ACCESSIBILITY.md),
[language review](27_LANGUAGE_REVIEW.md) and [browser exit](26_BROWSER_EXIT_AUDIT.md).
