# Documentation audit

Baseline: VideoTrack **1.7.259** (`2026111701`).

## Initial 1.7.259 CI and bounded correction

Run `44120652072`, commit `1b5b2ed1e941e9eb9edeea62516b364b9b8b2ba1`, failed all
six jobs on the same PHPUnit fixture. All 457 source blobs/modes and six supplied
artifact digests match the official run. Each suite executed **448 tests / 5054
assertions / 1 error**: core `persistent::update` rejects the fixture's userinfo URL
before the VideoTrack endpoint boundary is reached. This is not evidence of a
successful OAuth request or an endpoint bypass.

The corrected test injects deliberately corrupt stored configuration through the
test database, without asking core's validated write API to accept an illegal URL.
All seven destinations remain covered. Userinfo is rejected by local readiness
(`configuration_incomplete`); the other unsupported Microsoft/custom destinations
are rejected as `oauth_endpoints_unsupported`. Every case asserts no OAuth refresh
and no Graph attempt, and the factory still fails if called.

Initial Behat passed **54 scenarios / 1194 steps** without reruns in every job;
Grunt and strict **10/0/0** also passed all six. The selected Moodle 5.0 quality job
passed PHPCS/lint on 180 distributed PHP plus the repository lifecycle helper,
PHPDoc, metadata and 206 upgrade/savepoint contracts. Lifecycle execution was
**skipped** after the Unit failure; it is not a pass.

Initial PHPStan/Psalm report no configured errors. PHPMD has **33** advisory
findings, including two new complexity warnings in Graph `inspect` and
`video_metadata`. This correction extracts the same bounded decoder and video MIME
validation into focused private helpers; HTTP destinations/options, output fields
and classifications are retained. A fresh native report must confirm the counts.
Psalm reports **743/771** information items; Moodle 5.3 also prints 13 core-alias
deprecations during analyser reflection. These are recorded rather than suppressed
or called a zero-advisory result.

This patch is **incremental from the failed 1.7.259 commit** on
`release/v1.7.259`; it is not the original 258-to-259 patch. Release/version stay
`1.7.259 / 2026111701`. Counts remain 457 repository / 433 distributed files,
180 distributed PHP, 448 test methods and 19 new Graph methods; named PHP callables
become 1238 (1231 distributed + 7 repository helpers), with 654 AMD callables.

Corrected native CI, including Unit and the previously skipped lifecycle gates,
is still required before merge. Actual main/package/server acceptance and live
Microsoft access remain separate gates. No schema, credentials, scopes, player,
AMD, AJAX or demo data is changed by this correction.

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
