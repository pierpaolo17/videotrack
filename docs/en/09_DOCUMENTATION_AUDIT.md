# Documentation audit

Baseline: VideoTrack **1.7.259** (`2026111701`).

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

## Documentation and release contracts

English/Italian indexes, provider/CLI guides, privacy summaries, architecture, file and
named-callable inventories describe the candidate. The database/ER version markers
track it without changing the seven-table schema. All eight language packs retain
1006 keys; AMD source/build/map bytes are unchanged from accepted 258.

Inventory and patch checks are source evidence, not native PHPCS/lint/PHPUnit passes.
Candidate CI, main CI, actual ZIP identity, server canonical/Unit/Grunt and strict
validation are separate acceptance gates. Real Microsoft access is an additional
provider gate. Earlier release narratives remain in CHANGELOG and their source tags.
