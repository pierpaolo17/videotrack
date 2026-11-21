# Security follow-up

VideoTrack **1.7.268** (`2026112101`).

## Accepted 1.7.258 baseline

The actual package from main `2df5bb3bee4b77e75a94acd61436071cc2026181` has 427 files;
every file matches the accepted repository tree. Package SHA-256:
`695166a99eb2b0b13ddc6526fa2333358d295cf3c1af6ca0d2ff8cb559878521`.

Corrected candidate/main CI passed all six matrix jobs: PHPUnit **429 tests / 4843
assertions**, Behat **54 scenarios / 1194 steps**, strict validation **10/0/0** and
canonical AMD builds. The selected Moodle 5.0/5.3 MariaDB jobs passed historical,
interrupted and previous-release upgrade/repeat/populated-uninstall paths with
preservation checks. The actual server report confirms clean PHPCS/lint on 177 files
(176 distributed PHP plus one repository helper), PHPUnit 429/4843 on both branches,
and unchanged Grunt builds. The maintainer's strict outputs pass 10/0/0 on both sites.

PHPStan/Psalm had no configured errors. PHPMD **31** findings and Psalm **741/769**
information items remain advisory; their types are not printed in the reports.
This is not a zero-advisory result. Three old Node scripts still fail identically on
accepted 257/258: obsolete privacy/backup source assertions and a missing core/str
tracker harness stub. Native gates above passed; those scripts are not counted as passes.

Manual Moodle 5.3 accessibility remains on standby, and Tiles visual checking and
a fresh external security scan remain open. The synthetic demo is retained.

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

| ID | Accepted implementation / remaining limit |
|---|---|
| SH-01/02/03/05/08/09 | Accepted 257 with native authorisation/privacy regressions. |
| SH-04 | Permanent owned erasure, serialised retry, Privacy/backup/legacy restore; native 258 passed. |
| SH-06 | Upgrade-only DDL and core completion cleanup for valid contexts; justified scoped orphan/ambiguous DML retained. |
| SH-07 | Submitted-data API with existing sesskey/capability checks; native 258 passed. |
| SH-A1/A2 | Scoped resume and POST Analytics; native 258 Unit/Behat passed. |
| MS-14 | Course-format output cleaned; no reproduced Tiles XSS or manual visual pass claimed. |
| MS-38 | Transactional recovery regression passed; DML bodies preserved in 258. |

## 1.7.259 candidate scope

This release adds an explicit authenticated Microsoft Graph metadata probe through
Moodle's existing Microsoft OAuth system account. It does not add a selectable source
or player adapter. The existing local status command stays inert. Only the new probe
can refresh core OAuth credentials and issue a fixed-host metadata GET; it never
downloads video content or writes VideoTrack/demo records.

Native PHP/Moodle execution and real tenant access are pending for this candidate.
The new mocked regressions require no credentials or network. See
[Microsoft Graph diagnostics](30_MICROSOFT_GRAPH.md) for configuration, output and limits.
After complete candidate/main and actual-package acceptance, continue with provider
playback and its activity/user access controls before Zoom, Google Drive and the App.
The App-to-browser trial remains skipped; multilingual subtitle expansion is deferred.
