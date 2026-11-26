# Documentation audit

Current candidate: VideoTrack **1.7.273** (`2026112501`).

## Accepted baseline

Actual 272 ZIP SHA256 `4776fb1dda089a8e1ed701a477c751d44d5b78b5f4ba0c47399671879a5a2e64`.
All 493 distributed files match main `18305f5e3c62fdd25a128d9bc99c421323658767`,
tree `789f590de9d0f90a3a21eee1135d0704881b6d0b`. Main run 45658185075 attempt 1
passes six jobs, PHPUnit 640/6618, Behat 82/1541, strict 10/0/0, native builds and
six independently reconstructed lifecycle snapshot hashes. Server report 20261125_184426
passes canon/lint 207 shared PHP and native Grunt/Unit 640/6618 on 50/53. User strict
passes 10/0/0 on both sites; real YouTube report replay is accepted. Existing PHPMD 40
advisories and Psalm one standard-guard finding plus 763/791 info remain visible;
PHPStan is clean. No 273 static-analysis outcome is inferred from 272.

## Current change

The [duration guide](43_SOURCE_DURATION_VALIDATION.md) covers opening settings and
both save buttons plus Cancel, regardless of which fields changed. Excessive
known durations block saves; equal/shorter values remain valid. Unavailable
metadata permits an explicit manual save. Cancel checks and warns, then discards
without saving. Repeat-field actions remain immediate. Source-stale results and
duplicate submissions are guarded. requestSubmit preserves normal Moodle handling.

The transient metadata input has an explicit id and a matching form-config test.
PHP validates only a small report matching the submitted source. Browser-reported
metadata is not presented as authenticated server metadata. There is no persisted
setting, DB migration, tracking rewrite, API credential or automatic recalculation.
The accepted tracking/replay fixes, demo data and historical recovery option remain.

Counts: 523 repository / 496 distributed files, 207 distributed PHP, 1591 named PHP,
668 named AMD, 645 unit methods, 82 Behat scenarios / 1541 steps, 8×1078 keys and 48 AMD sets.
Schema stays 7 tables / 152 fields / 22 explicit indexes / 22 foreign keys; 9 AJAX services.
Inventories preserve actual locations, including duplicate AMD callable names.

## Native build and remaining gates

Verified maintainer Moodle 5.0 archive SHA256 `b5c4292a4d9362e4ec95ad3e009273de75d1503c6853c67272351926cb650fb3`.
Node 22.23.4 / npm 10.9.9; npx --no-install grunt amd exits 0. Exact 48 source / 96 build
hashes, maps, fixture and log match. Only the form module and its two outputs change.
All 10 source + 10 compiled duration groups pass in the actual log and after incorporation.
Accepted 272 source/build fail all 10 groups as expected. Existing tracker harness and
9 source + 9 compiled tracking groups pass. The runner reports verified restoration.
The two existing settings-save browser steps use a fixed-duration SDK double
restricted to the reserved video id, then click the real form button in the same
JavaScript task. Existing 82 scenarios / 1541 steps remain; no live metadata is needed.

PHP/Moodle execution, PHPCS, PHPUnit, 273 CI/lifecycle/server and live duration UI
remain pending. Five new PHP unit methods and updated form-config assertions are
prepared; no new PHP/Moodle or real browser/provider execution is claimed here.
Microsoft HTTPS/personal-account validation and the provider/App roadmap stay open.
