# Documentation audit

Current candidate: VideoTrack **1.7.270** (`2026112400`), based on accepted269.

## Accepted baseline and evidence

Actual uploaded269 ZIP SHA25694200cf7c2d8da43478abd44e39f3c31b6ecae23af2487e28add19f61d7dd45e;
475distribution files match mainad9dc8ecc0f963f0f6e7bbcc41906e6e734c5471,
tree823ae1cce5c5f29522b0b7ee316718009e224552. Candidate45251028653 and main45260313398
each pass six jobs: Unit595/6280, Behat75/1428 without reruns, strict10/0/0, native
Grunt, standalone JS and both upgrade/repeat/populated-uninstall matrices. The six
preservation hashes per run are independently reconstructed.

Server report20261123_230428 passes eight command outcomes: canonical/lint198shared
without errors/warnings, Unit595/6280 separately on50/53, both Grunt commands with
unchanged native output hashes. Both maintainer strict outputs are10/0/0. Successful
installation/recalculation is reported by the maintainer on2026-11-24, not directly
executed by this assistant. PHPMD35 and Psalm1guard advisory per run plus757/785info
remain open; PHPStan is clean. Prior wording claiming zero Psalm errors was incorrect.

## Current changes and remaining gates

The [personal search](40_MICROSOFT_VIDEO_SEARCH.md) resumes suspended268 on accepted269.
All tracking/recovery code,48native AMD sets,9AJAX and XMLDB are unchanged. Keep the
temporary recovery option until the extraordinary repair workflow is retired in a
dedicated release. Demo data generation remains external to the plugin and targets5.3.

Add25unit methods and3local Behat scenarios; all595old unit methods and75scenarios
remain, with only the exact Privacy fields assertion extended by searchquery.
Counts:509repository/483distribution files,202distributedPHP plus7named CI helper
functions,1542namedPHP callables,660namedAMD,620unit methods,78expanded scenarios/
1458steps,8x1068language keys and48AMDsets. Bilingual inventories use actual locations.

Local checks cover source/lexical contracts, language/placeholder parity, unchanged
hotfix files, exact maps, Node source/compiled regressions, patch application/reversal
and documentation links. PHP/Moodle/MariaDB are unavailable locally: native270PHP
lint/PHPCS, PHPUnit, Behat, Grunt, static analysis, lifecycle and server checks remain
pending. Live Microsoft OAuth/search/browser playback and learner telemetry remain
separate gates. Next provider work remains Microsoft, Zoom, Google Drive, then App.
Manual5.3accessibility remains STANDBY.
