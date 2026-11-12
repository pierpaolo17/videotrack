# Integrated lifecycle and production evidence

Current candidate: VideoTrack **1.7.252** (`2026111202`). Audit recorded 2026-11-12.

## Accepted baseline

Actual **1.7.251 / 2026111201** is accepted: 415 files match main
`823a6ac458c5cc8a2ed5a5fbea729dabfdac6330`. Package SHA-256:
`4954f75e11f146814e2d410c59bc2457e75f81eea93cfa26208311826717bbab`.
The 10-file server report has SHA-256
`c87760ceadc01bfbc24b47cabc78f2e512acedc3132313c04cbf3123b5bdf7e2`.
Candidate/main CI and both actual Moodle 5.0/5.3 server reports pass PHPUnit **381/4296**. The selected
six-job browser reports pass **52 scenarios / 1154 steps**, without scenario reruns. PHPCS/lint are clean on
167 files; Grunt and supplied strict outputs pass, with strict **10/0/0** on both sites and unchanged AMD build
hashes. Retain the initial nine PSR12 formatting errors corrected before merge. No native Moodle execution
occurred in this workspace. PHPMD has 24 reviewed findings and Psalm 733/761 advisories; configured scopes pass.

## Course-reset callback finding and focused correction

Both Moodle 5.0 and 5.3 `reset_course_userdata()` discover the activity function `$modname . '_reset_userdata'`
after populating `courseid` from the submitted course `id`. VideoTrack 251 defines only
`videotrack_reset_course_userdata()`. Its reset checkbox and deletion implementation exist, but the framework
cannot discover that implementation through the expected name. This is source-level evidence; a failed live
maintainer reset was not reproduced or claimed.

Release 252 adds **`videotrack_reset_userdata(stdClass $data): array`**, delegating directly to the retained
helper. The helper's executable body, `reset_videotrack_userdata` flag, default-off behaviour, table filters,
grade reset and returned status are unchanged. Historical direct callers can still use the old helper.
Moodle's course-reset page remains responsible for login, session/form checks and `moodle/course:reset`.
No new AJAX service or direct web endpoint is introduced. The separately authorised per-student report reset
retains its full-report/management/sesskey/scope requirements.

The VideoTrack option clears its five learner-data families and module grade values, while preserving
activity settings, reaction definitions and shared files. Native completion reset remains controlled by
Moodle's separate completion-reset option; the positive integration case explicitly selects it. Global
gradebook reset is left off so the test exercises VideoTrack's own grade reset path.

## Initial candidate CI and same-version formatting correction

All six uploaded artifacts match the official digests for commit
`df3fe608eabe24d4a8c1b1e650dc73ee3e73c79e`, run **43233266763**, attempt **2**.
Each passes **383 PHPUnit tests / 4350 assertions**, **52 Behat scenarios / 1154 steps**,
strict **10/0/0** and Grunt. PHP lint covers 167 files; PHPDoc is clean.
The overall run has five successful jobs and one failed Moodle 5.0/MariaDB job:
PHPCS reports **two errors on line 185** of `tests/lifecycle_integration_test.php`.
They concern multiple arguments on one line in a multiline `get_records()` call.

The incremental correction applies to that already committed 252 tree, keeps
release/version unchanged and places those arguments on separate lines. Test
executable tokens and literals are identical; production files are byte-identical.
Both callable inventories reflect the two added lines. Check function-call argument
layout as well as multiline control headers: the earlier control-header guard did
not cover this PHPCS rule. No CI rule is weakened. Native PHPCS and the complete
corrected six-job matrix remain pending before merge; initial functional passes
do not make the failed quality gate acceptable.

PHPMD's 24 findings match the accepted 251 findings semantically, ignoring shifted
line positions. Psalm's configured scopes pass and report **732/760 advisories**,
one fewer per branch than 251; no unobserved cause is inferred.

## Real integration cases

| Case | Evidence and scope |
|---|---|
| Backup/restore without learners | Accepted on 251: real course controllers, shared configuration, Forum/reaction/file mappings, canonical grade item; learner rows/grades/completion excluded and original plugin rows preserved. |
| Backup/restore with learners and post-restore privacy | Accepted on 251: destination retention excludes expired inputs; unvalidated segments and stale aggregate/native completion cannot restore credit; approved-user export, user/context erasure and preservation of other learners/source/files/core grade ownership are checked. |
| Standard course reset, option off/on | Passed on initial 252 in all six CI environments; corrected CI pending. Call Moodle's actual reset function, first with VideoTrack off, then on across two activities in one course. Check the module status, preserved configuration/definitions/files, cleared learner families/grades and no completed native rows when core completion reset is selected. A second course's plugin learner records remain identical. |
| Synchronous activity deletion | Passed on initial 252 in all six CI environments; corrected CI pending. Call Moodle's public activity-deletion API; assert removal of the target activity, six plugin-owned child tables, native completion, files/context and grade item/rows. The linked Forum and neighbouring activity in the same course, including its learner records/configuration/files, survive. |

The deletion test uses `core_courseformat\formatactions::cm()->delete()` when its `cmactions::delete()`
implementation exists; older branches use `course_delete_module()`. Moodle 5.2+ deprecates the global helper,
so no deprecation suppression is used. Existing backup cases retain their default fixture path; the helper
accepts an optional existing course only for the new same-course isolation cases.

These are actual framework/DB integration tests, not a claim that a browser generated the fixture records.
Backup tests use `MODE_IMPORT` to retain real XML/files without archive compression. They do not certify
download/extraction of MBZ files, cross-site migration or historical-backup compatibility. New reset/deletion
tests passed the initial native matrix; the corrected candidate must pass a complete new run.
Local contracts and patch checks do not substitute for that execution.

Expected corrected suite: **383 PHPUnit tests / 4350 assertions**, measured in the initial native CI.
Behat stays **52 scenarios / 1154 steps**. The tree retains **415 files**, **167 PHP files**, **32 documents
per language**, **346 file-inventory rows** and **654 AMD callables**. Named PHP callables become **1113**:
one standard reset callback and two test methods. XMLDB remains `2026110601`, **7/151/22/22** for tables,
fields, indexes and foreign keys. Nine AJAX services, eight packs of 997 keys and 48 AMD source/build/map sets
are unchanged; ER changes only its version label. No language value, role grant, tracking/player policy,
privacy implementation, backup/restore implementation, SQL aggregation or CI rule is changed.

## Remaining production gate and roadmap

- Fresh installs and the supported Moodle matrix use ordinary CI; this does not prove a historical upgrade.
- Historical/interrupted upgrades and full uninstall with populated data and zero-residue checks remain
  distinct production evidence; targeted upgrade/uninstall tests do not certify every deployment lineage.
- Existing retention tests remain accepted; no new scheduler timing or fault-injection execution is claimed.
- Analytics SQL/index/scope code is unchanged. No benchmark was run or inferred from test/query counts;
  required large-dataset/multi-activity/group/7/30/90-day observations need recorded versions and data/cache conditions.
- Eight maintainer-reported manual PASS on 248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA remain scoped.
  Moodle 5.3 manual observations are unrecorded. Do not infer new manual passes or WCAG certification.
- Dedicated Moodle App support is undeclared (`db/mobile.php` has an empty addon list); no native/WebView/offline parity is promised.

U-017 and essential U-007 remain closed. Finish the earlier integrated web/lifecycle/privacy/benchmark/manual
work and explicit App decision, then the agreed multilingual VTT subtitles, authenticated-provider foundation,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive and cross-provider hardening. This release advances
specific lifecycle evidence; it does not close the entire production gate by documentation.

See [lifecycle](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [permissions](13_SECURITY_TRUST_BOUNDARIES.md),
[privacy](18_PRIVACY_RETENTION.md), [CLI/benchmark](21_CLI_DIAGNOSTICS.md), [manual evidence](19_ACCESSIBILITY.md)
and [language review](27_LANGUAGE_REVIEW.md).
