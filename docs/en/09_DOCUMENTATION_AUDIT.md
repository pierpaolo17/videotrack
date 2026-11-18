# Documentation audit

Baseline: VideoTrack **1.7.261** (`2026111801`).

## Corrected 261 run and PHPDoc-only follow-up

Run `44395198345`, commit `f834ffd16269662389acbf87ab59d3bf48e3bef7`,
tree `a658e256119dd0971b96c604708d2d354496ead9`, has five successful jobs and
one failed 5.0/MariaDB job. All **seven official artifact digests**, including the
faildump, match the uploaded ZIPs; **468 blobs/modes** match the delivered correction.
All six PHPUnit suites pass **472/5183**, strict **10/0/0** and Grunt. PHPStan/Psalm
report **no configured errors** on both selected branches, removing the previous
three findings; Psalm informational counts remain **746/774**, types not printed.
PHPMD retains exactly the same **31** reviewed baseline identities.

The canonical job reports **five PHPCS errors** in the first bootstrap variable
comment: its first-docblock position lacks file description/package/copyright/license,
and its trailing prose disqualifies the inline type hint. Lint185+1, helperPHPCS,
PHPDoc checker, metadata and 206 savepoint contracts still pass. This follow-up adds
a complete file PHPDoc before the namespace and makes the existing type hint end
with its variable. This follows the [actual Moodle CS3.7.0 rule](https://github.com/moodlehq/moodle-cs/blob/v3.7.0/moodle/Sniffs/Commenting/InlineCommentSniff.php).
Every executable PHP token, existing test, setting, schema, language, AMD, version
and CI gate is retained. No suppression is introduced. Local checks are lexical
and source comparisons, not native PHPCS/Psalm execution of this follow-up.

Five jobs pass Behat **61/1268**, without reruns. The 5.0/MariaDB log has **33 passed /
28 failed scenarios**, **877 passed /1 failed /390 skipped steps**, then two reruns
that cannot reach `http://localhost:8000`. Both faildump files show the HTML5-ready
step; the HTML is Chromium's `ERR_CONNECTION_REFUSED` page, not a Moodle exception.
The local HTTP service is unavailable; the reason it stopped is **undetermined**
from these artifacts. No server/OOM/root-cause claim or workaround is inferred.
A fresh CI run must re-execute the browser suite; the failed job is not accepted.

All three lifecycle lineages pass on **5.3/MariaDB**, with three independently
reconstructed snapshot hashes and 63 actual file-content checks. **5.0/MariaDB
lifecycle is skipped**, not passed. Historical success from the earlier run is
separate evidence and does not fill this gap.

This is an **incremental PHPDoc correction from f834ffd1**, retaining **1.7.261 /
2026111801**, 472 unit methods, 61 scenarios, 468/444 files and 8×1023 language keys.
Fresh six-job candidate CI, then main, actual package/server and both strict outputs
remain required. The actual accepted baseline remains260. Retain the demo; manual53
accessibility stays on standby. The assistant makes no remote Git changes.

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
