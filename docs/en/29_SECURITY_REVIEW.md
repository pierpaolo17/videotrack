# Security follow-up

VideoTrack **1.7.258** (`2026111700`).

## Initial258 CI rejection and bounded correction

The initial 1.7.258 candidate e70102d6 (run44084042387) failed all six jobs. All451
repository blobs/modes and12 uploaded CI/faildump archives match the official GitHub
source and digests. PHPUnit429/4823 reported2errors in the new backup tests (Moodle
rejects TEXT columns in get_record conditions) and1failure from stale README release
markers. Strict reported9/0/1 and PHPCS6 boilerplate errors in3 new files.
The correction reads restored text with a scoped fieldset and compares it in PHP,
updates both exact current-release markers and restores the canonical header spacing.

Behat initially passed52/54 scenarios and1188/1192 steps, then both automatic reruns
still failed the two new resume examples. Available HTML shows Second Learner with
server resume0 and a correctly scoped current-user key; localStorage values are absent,
so the aggregate assertion does not establish which condition failed or prove a leak.
The fixture now injects known foreign/legacy hints after login and before player init,
preserving Moodle's JS-revision sentinel. All original isolation, absent-current-hint,
zero-server-resume and actual provider-time assertions remain. Per-condition diagnostics
replace the ambiguous message. No player code, storage key or build changes here.

PHPStan/Psalm had no configured errors. Initial258 PHPMD31 has3 additional restore
complexity advisories versus257's28; Psalm741/769 has1 additional information item per
branch, with types unprinted. These are recorded, not silently treated as unchanged.
The selected lifecycle gates were skipped after failures. Fresh corrected native
canonical/strict/PHPUnit/Behat/lifecycle CI remains mandatory before merge. Local
fixture-JS syntax/logic checks pass, including four invalid states; this is not Behat.

This replacement is incremental from e70102d6 on release/v1.7.258. Version remains
2026111700. Current inventory has1203 PHP callables (1196 distributed+7 repository-only)
after adding one Behat preparation step; file counts,15 new PHPUnit methods and two
new browser scenarios remain unchanged. Accepted actual baseline stays1.7.257.


Removal physically erases the owned note/bookmark row. AJAX parameters and responses
remain compatible. Login/context/participation/sesskey checks precede retries: missing ids
are successful no-ops, existing foreign or wrong-type rows are rejected. A Moodle lock
serialises removals and the transaction includes one event without private text. An old
soft-deleted row is erased without duplicating its event.

User backups and Privacy exports exclude removed personal items; restore rejects personal
rows marked deleted in older XML. Active tools and reaction history retain their contracts.
Backup files created before deletion may contain then-active copies: their retention is
separate, and restore cannot infer a later deletion from an older active copy. No general
migration or demo cleanup runs.

Schema recovery moves to upgrade-only db/upgradelib.php. Install/uninstall retain DML
grade repair. Valid uninstall completion contexts use completion_info::delete_all_state;
only orphan module references need scoped completion DML. Ambiguous/orphan pre-production
grade rows retain tested transactional DML: those malformed rows can break the Grade API's
context resolution, so replacing that recovery with runtime grading calls is unsafe.

Controllers use data_submitted() with existing sesskey/capability checks. Analytics downloads
use POST with the selected filters and sesskey in the body; GET downloads are rejected before
their export event/output. The server generates opaque resume keys for site, participating
user, module, source and video identity. YouTube/Vimeo ignore old shared keys and disable
storage with an incomplete identity; HTML5 retains server resume. Browser hints never
become validated viewing evidence. The activity index cleans course-format section HTML
without applying filters twice; this is defence in depth, not a reproduced Tiles XSS claim.

| ID | Status / Stato |
|---|---|
| SH-01/02/03/05/08/09 | Accepted257 with native regressions. |
| SH-04 | Permanent erasure/retry/Privacy/backup implemented; native258 pending. |
| SH-06 | DDL upgrade-only; completion API for valid contexts; justified orphan/ambiguous DML retained. |
| SH-07 | Moodle submitted-data API and existing sesskey guards. |
| SH-A1/A2 | Scoped resume and POST Analytics implemented; native258 pending. |
| MS-14 | Course-format output cleaned; no reproduced Tiles XSS claimed. |
| MS-38 | Transactional recovery regression passed257; DML bodies preserved. |

Accepted actual baseline257/main d6e8705e:422 matching package files, server PHPCS/lint173
clean, PHPUnit414/4695 and strict10/0/0 on50/53, unchanged AMD builds, six-job candidate/main
CI, Behat52/1154 without reruns and three lifecycle lineages on selected50/53MariaDB.
Package SHA-256:59fff2d7214a6828c1544b896d489ecebb8bbefb1821e72c6a3384ca845cc504.

Candidate258 adds15 PHPUnit methods and two provider browser scenarios, including real
backup/restore with injected legacy XML. PHP/Moodle are unavailable locally: native
PHPCS/lint/static/PHPUnit/Behat/lifecycle and actual package/server acceptance remain pending.
Actual Moodle Grunt AMD Node22 passed; only two sources/four build-map outputs changed.
AMD smoke and adapter tests pass. Three historical Node scripts fail identically on257/258:
privacy_static_test and backup_restore_static_test expect obsolete pre-refactor contracts;
tracker_segment_test lacks a core/str harness dependency. They are existing tooling debt,
not native passes. A fresh external review is required before claiming a new security grade.
Native257 PHPMD28 and Psalm740/768 advisories remain recorded; no zero-warning claim.

No schema/savepoint/service/retention-task/OAuth change. No general legacy-data migration
per maintainer; retain the synthetic demo. Manual5.3 accessibility remains standby; Tiles
visual checking is pending. After full final acceptance proceed directly to the next roadmap
patch without reconfirmation. New sources precede the App; App-to-browser trial stays skipped.
