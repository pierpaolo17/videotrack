# Documentation audit

Current candidate: VideoTrack **1.7.272** (`2026112500`), based on the actual accepted 271 ZIP.

## Accepted baseline

Actual 271 ZIP SHA256 3bb6462e50fcfb4d59b0947322ab64a405b26c6b778f1bd8124111585b51f3f2,
490 distributed files match main 99d4195f1202cf630102c252841b0ed75372b6b3,
tree ee7f092ed9f8e75460060f8e8d7e386b16e832e4. Candidate 45497048064 attempt 2 and
main 45578816355 attempt 1 pass six jobs each. Unit 640/6618, Behat 80/1484 without inner
reruns in the accepted reports, strict 10/0/0, native Grunt and both 50/53 historical
upgrade/repeat/populated-uninstall matrices pass. Six snapshot hashes per accepted
run are independently reconstructed. The initial candidate's 5.3 MariaDB server failure
was not reproduced on identical code in the fresh accepted attempt; its initiating
cause remains unproven and is not called an application fix.

Server report 20261125_105411 passes eight outcomes: canon/lint 207 shared clean,
Unit 640/6618 separately on 50/53, and native Grunt without build changes on both.
Both user strict outputs pass 10/0/0. PHPMD 40 advisories, Psalm one standard guard
finding plus 763/791 information entries remain visible; PHPStan is clean.

## Current fix and gates

The [replay guide](42_REPORT_REPLAY_STATE.md) traces report URL→programmatic seek→
new provider PLAYING→late seek consumption→false rollback/recovery speed.
A single guard in the shared time helper preserves a settled newer provider state.
Database/upgrade, privacy, capabilities, source policy, server tracking and all PHP except
the release metadata remain byte-identical to accepted 271. All 640 unit methods and
80 old browser scenarios remain. Existing Node assertions remain, with three groups
added and two actual report-link browser scenarios using browser-earned reactions.

Counts:519 repository/493 distributed files, 206 distributed PHP, 1582 named PHP, 660 named AMD,
640 unit methods, 82 expanded Behat scenarios/1541 steps, 8 x 1076 keys and 48 AMD sets.
Schema stays 7 tables/152 fields/22 explicit indexes/22 foreign keys; 9 AJAX methods unchanged.
Inventories use actual positions. Source Node regressions pass all nine groups and
the tracker harness; accepted old source/build reproduces all three new failures.

Native AMD outputs are verified from the maintainer Moodle 5.0 test server, archive
SHA256 a6ce3d08275774f01fb2cd4b350c2f07c405f0336533f445705f894ff3175eb2.
Actual npx --no-install grunt amd exits 0. The exact source/fixture, 48 source/96 build
hashes, canonical maps, log digest and original-tree restoration marker match.
Only the helper and its two native outputs change; 47 sources/94 outputs are identical
to 271. All nine Node groups pass on source and compiled modules in the artifact and
after local incorporation, plus the unchanged tracker harness. No GitHub provenance
or exact Node patch version is invented for this maintainer-origin build.

The initial builder attempt used unsupported Node 26 and stopped before compilation;
its 144 file backup matches accepted 271. The supported Node 22 environment satisfies
Moodle 5.0 Grunt's >=22.11/<23 requirement. Original-site restoration is verified by the
runner. No DB/bootstrap/credential operation is introduced. PHPMD/Psalm debt stays
visible. Native 272 PHP/PHPCS/PHPUnit/Behat, full CI/lifecycle and test-server checks
remain pending; no new PHP/Moodle execution is claimed locally. Live YouTube review
and Microsoft HTTPS/account/provider/App roadmap gates retain their previous state.
