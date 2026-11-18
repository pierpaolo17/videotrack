# Documentation audit

Baseline: VideoTrack **1.7.261** (`2026111801`).

## Initial 1.7.261 CI and same-version correction

Run `44388798465`, commit `d8ab79681ecbb5c4b9e30dba3bbbd7f12625b62a`,
source tree `003728bd6ab13fd638a6c5295bdf5b500ba3231f`, reports all six jobs green.
The six uploaded ZIP digests match the official artifacts; all 468 blobs/modes match
the delivered candidate. Each job passes Unit **472/5183**, Behat **61/1268** without
retries, strict **10/0/0** and Grunt. Canonical checks pass **185 distributed PHP +1
repository helper**, PHPDoc/metadata and 206 savepoint contracts. All three selected
lifecycle lineages pass on 5.0/5.3 MariaDB; six snapshot hashes were reconstructed
and 126 actual file-content checks verified. PHPMD remains the same 31 reviewed
baseline findings.

The advisory static-analysis steps report one PHPStan finding and two Psalm errors:
`settings.php` depends on the externally supplied `$hassiteconfig`; the autoloaded
form declares `global $CFG` at file level. Psalm also reports 746/774 informational
items (types not printed). These new errors are not accepted merely because the
workflow is green. The correction uses a direct system capability check and a typed
local reference to `$GLOBALS['CFG']` for loading the core forms library. It adds no
suppression, new feature, test, language key, schema change or CI exception. Existing
form/session/access regressions are retained in full.

This is an **incremental patch from d8ab7968 on release/v1.7.261**; it is not the full
260-to261 patch. Keep **1.7.261 / 2026111801**, 472 test methods and 61 browser
scenarios. Source checks and patch comparisons can run locally; the corrected
candidate's native CI, then main/package/server acceptance, are still pending.
No connected Microsoft tenant or player telemetry is claimed. Retain the demo.

## Accepted 1.7.260 technical baseline

Accepted main `4d9fe8b0eb8b4a6587992f4ac86b2bec9d1dcb65` has tree
`6b4041b583a66db1ebbe5d623b7b1e0ab0156a12`, identical to candidate
`a98ef230429373259943b7bc9634dafe5512ea2c`. Candidate run `44365252939`
and main run `44374062516` each pass all six jobs. All six official artifact digests
and 462 source blobs/modes match each run. PHPUnit passes **463/5144** on both
branches; Behat passes **58/1241**, without retries. Strict passes **10/0/0**;
Grunt completes. Canonical checks pass 182 distributed PHP files plus the repository
helper. All three selected lifecycle lineages pass on 5.0/5.3 MariaDB, including
real upgrade, repeated upgrade and populated uninstall. Each run reproduces six
snapshot hashes and 126 actual file-content checks.

The actual 260 archive has **438 files**, all matching accepted main, archive
comment matching that commit, SHA-256
`f2557bf69f15af618d4a4c1a20045f3b44ddad32c59ac354a889fc87fee1b9d5`.
The maintainer report `report_completo_videotrack_1.7.260_20261118_203639.zip`
has SHA-256 `9ef587aa5bca60361fa066a4c34efdf8d4c98447ca23a2caf3a8dd7c5bdc072e`,
ten logs and eight requested commands passed. Server Unit is **463/5144** on both
sites; PHPCS/lint reports **183 clean** in a shared identical-source run, not two
separate canonical executions. Recorded Grunt before/after hashes agree; source
maps also match source content. A fresh reconstruction of the server aggregate
build hash is not claimed. Maintainer strict outputs pass **10/0/0** on both sites,
with grade items/rows **0/0** and **34/34**. Schema remains 7 tables/151 fields/22
explicit indexes/22 foreign keys; nine AJAX, eight packs of 1013 keys and 48 AMD sets.

PHPMD has 31 reviewed baseline advisories; PHPStan/Psalm report no configured errors.
Psalm has 743/771 informational items. Historical standalone Node harness debt and
the Browserslist advisory remain recorded; none is claimed fixed. The manual
Moodle 5.3 accessibility assessment stays **STANDBY**. Retain the synthetic demo.

## Current 1.7.261 scope and evidence boundary

The new [Microsoft web check](32_MICROSOFT_WEB_CHECK.md) is protected by system
site-configuration capability and uses a POST/sesskey form. GET only reads local
readiness. The existing Graph/OAuth diagnostic is reused; it can refresh core
credentials, but never imports/streams a file or changes activity/demo data.
Escaped redacted diagnostics are non-cacheable. IDs are validated before the probe.
No cloud source, player bridge, link renewal, new schema, service or AMD is added.

Nine native unit methods and three deterministic browser scenarios are authored;
**native execution of the corrected 261 candidate is pending**, as is live connected-tenant access.
The initial 261 execution above passed the new regressions; it does not execute this correction.
Source/lexical/style, language parity, inventories, unchanged schema/AMD and patch
apply/reverse comparisons can be checked locally; they do not replace PHP/Moodle.
Candidate six-job CI must pass before merge, followed by main six-job CI, actual
package/server report and both strict outputs. No remote Git changes are made by
the assistant. Zoom repository assessment and Google/App work remain later gates.
