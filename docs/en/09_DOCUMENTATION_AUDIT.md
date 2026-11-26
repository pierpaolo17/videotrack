# Documentation audit

Current candidate: VideoTrack **1.7.273** (`2026112501`), CI-only follow-up based on
main `65dd8183f683ae6c0ceec74597eb7c3ed5820f94`, tree
`9a528128a3f4ae3865531cc08662b14ff06ffffe`.

## Current main gate and server evidence

The corrected candidate 906f0933 passes all six jobs (one 50pgsql Behat scenario
passes its automatic retry); PHPCS is clean and Psalm has no errors. Both populated
lifecycle matrices pass. Main65dd8183 has the exact same source tree.
Main run45812588108 attempt1 passes four matrices. Behat51/53 MariaDB loses HTTP
service during testing; both internal retries fail the localhost8000 prerequisite.
Attempt2 passes 51MariaDB82/1541; 53MariaDB fails at a different page, now58/82.
Its faildump is Chrome's localhost connection-error page. Both updated reports pass
Unit645/6663, JS38+harness, strict10/0/0 and exact48source/96native-build hashes.
PHPStan/Psalm53 are clean; the populated53 lifecycle has not run on main.

The observed fact is HTTP unavailability during the tests. Why PHP stops is still
unproven: post-cleanup RAM/process snapshots are not an in-test health check, and
no PHP fatal, OOM or signal evidence was retained by the previous launcher.
Upstream moodle-plugin-ci4.5.13 BehatCommand disables the PHP process's output.

The CI-only runner owns the same PHP/Selenium services and retains
behat-webserver.log, behat-selenium.log and behat-server-state.json. The state separates
an exit before cleanup (negative return code means a signal) from intentional
termination. It keeps the same Behat command/profile/SCSS checks/default tags and
retry count. Unexpected PHP exit fails the gate and stops the cascade; it never
restarts the server to turn a failing run green. Existing listeners are refused.
Nine Python process-fixture tests pass locally; native Moodle/PHP/Docker execution
of this runner is pending. All production code, AMD outputs, schema and version
are byte-identical to main. Counts are525 repository/496 distributed files; the two
new CI Python files are export-ignored. Future274 duplication/CSV work is separate.

## Initial 273 CI evidence

Run 45778182389 attempt 1 has five successful jobs. The sole failed step is Moodle
Code Checker in 50 MariaDB: 9 PHPCS formatting errors on 6 lines in
tests/form_duration_validation_test.php. The correction changes whitespace only
in that test; its executable tokens and all assertions are preserved.

All six matrices pass PHPUnit 645/6663, Behat 82/1541 without inner retries,
38 JavaScript groups plus the tracker harness, strict 10/0/0 and native Grunt.
All six native manifests identify ca91e4a0; 48 source and 96 build/map hashes match
the delivered code, maps match sources, and wrapper payloads match report payloads.
53 MariaDB passes historical/partial/previous upgrade, repeat and populated uninstall;
three preserved snapshot hashes were independently reconstructed. 50 MariaDB lifecycle
did not run after the quality failure and remains a gate for the correction.

PHPStan is clean in the reported 50/53 jobs. The two Psalm annotations are the same
ParadoxicalCondition at db/services.php:25, with 765/793 information entries.
PHPMD reports 43 advisories in this initial 273 scan; these are not called zero or
unchanged from 272. The corrected static-analysis results remain pending.

## Corrective scope

- Fix the 9 reported formatting violations in the duration test.
- Keep the mandatory MOODLE_INTERNAL guard and the complete service registry
  byte-identical. Psalm configuration narrowly suppresses ParadoxicalCondition
  only in db/services.php, whose bootstrap-known guard triggers the false positive.
  No global suppression or runtime guard removal is introduced.
- Clarify ALL / AT LEAST ONE completion instructions in all 8 language packs and
  update the existing completion-layout browser assertion to the new label.
- Make dismissible player notices match the provider privacy banner in width,
  typography and spacing. Keep media/layout/study-tool width via the existing
  configured width passed as a CSS custom property. ARIA, dismissal and timeouts stay.

No AMD source, build or map changes; no native rebuild is needed. Schema, upgrade,
privacy, backup, credentials, retained evidence, tracking/replay and duration policy
remain unchanged. Version/release stay 273/2026112501 while the candidate is pending.
Previous corrective tree:523 repository / 496 distributed files, 207 distributed PHP, 1591 named PHP,
668 named AMD, 645 unit methods, 82 Behat / 1541 steps, 8×1078 keys and 48 AMD sets;
7 tables / 152 fields / 22 indexes / 22 foreign keys and 9 AJAX methods.

## Gates

Nine local process-fixture checks and patch checks do not replace native CI.
All six jobs with the observable runner, both populated lifecycle matrices,
server checks and live duration/visual acceptance remain required before273release.
The previous successful functional runs do not prove the server interruption fixed.
Microsoft HTTPS/account acceptance and the provider/App roadmap remain separate.
