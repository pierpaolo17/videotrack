# U-007 browser automation exit audit

Current tree: VideoTrack **1.7.247** (`2026111006`). Decision recorded 2026-11-09.

## Decision

**U-007 is closed on the accepted 1.7.240 baseline.** Its essential browser/provider/role matrix has real
execution on the six Moodle/PHP/database jobs below. Accepted 1.7.241 consolidated that evidence and the
manual-audit handoff; its actual ZIP/server/strict and six-job release/main CI have passed. The 1.7.247
language candidate is documented separately in the [targeted review](27_LANGUAGE_REVIEW.md).
U-017 remains closed. U-020 manual accessibility, semantic language review and integrated production gates
are separate and remain open. This document does not certify accessibility or a live provider deployment.

## Exact accepted baseline

| Evidence | Accepted value |
|---|---|
| Real release / version | 1.7.240 / `2026110603` |
| Distributed ZIP files | 408 |
| ZIP SHA-256 | `be83a2bbf806c6e95bc05d971b9a12d291575e8909b2b9a1f45af5930e71ae1e` |
| Merged main commit | `2d047f5c59792e4c8798a31209f19cfa9ecc15f7` |
| Corrected candidate | `3447cdfbadcef64332f21655b17a58f5d27045ba` |
| Main / candidate CI runs | `42634054361` / `42630177127`, all six jobs successful, attempt 1 |
| Repository equality | All 430 Git blobs agree between candidate and main; all 408 distributed files match |
| Per-job browser result | 50 scenarios / 1,074 steps, all passed |
| Per-job PHPUnit result | 379 tests / 4,076 assertions, all passed |
| Strict validator | 10 PASS / 0 WARN / 0 FAIL in all jobs and both supplied server instances |
| Server report SHA-256 | `5a9f16b2c4bc89d0fa951855be8728cfceee3cc5abbe4fa4eb50b5a23660d577` |

The actual server report runs **canon, lint, grunt, phpunit** on Moodle 5.0/5.3, PHP 8.3.6: 166 clean PHP
files, PHPCS zero errors/warnings, PHPUnit 379/4076 and Grunt Done. AMD build hashes before/after are identical:
`325cbe9919b087c4d4e1e01cebd7108530e943c212c4699aa6525adcd3f7db51`. The report does not run Behat or the local
`--preset=full` command. Browser evidence and the multi-version milestone come from the recorded CI, not
an inferred server execution. PHPMD retains 24 reviewed findings; configured analysis gates pass, with
Psalm informative totals 732/760 unchanged. Reviewed advisory debt is not a zero-finding claim.

## Multi-version browser milestone

| Moodle | PHP | Database | Browser | Behat | PHPUnit |
|---|---|---|---|---|---|
| 5.0 | 8.2 | MariaDB | Chrome | 50/1074 passed | 379/4076 passed |
| 5.0 | 8.2 | PostgreSQL | Chrome | 50/1074 passed | 379/4076 passed |
| 5.1 | 8.3 | MariaDB | Chrome | 50/1074 passed | 379/4076 passed |
| 5.2 | 8.3 | MariaDB | Chrome | 50/1074 passed | 379/4076 passed |
| 5.3 | 8.3 | MariaDB | Chrome | 50/1074 passed | 379/4076 passed |
| 5.3 | 8.3 | PostgreSQL | Chrome | 50/1074 passed | 379/4076 passed |

This covers both boundary versions and all four supported Moodle branches. The two databases on boundary
versions complement the intermediate-version jobs; six environments still represent one browser profile.

## Original exit criteria and evidence

| Original criterion | Recorded evidence | Decision |
|---|---|---|
| Essential playback/interactions/integrity/roles have browser coverage | Exact filename/contract matrix in [browser guide](22_BEHAT_BROWSER_TESTS.md); all 50 cases execute | Satisfied |
| HTML5/YouTube/Vimeo retain seek/resume/tracking contracts | Production adapters, local media/SDK doubles and accepted server writes across completion, seek gaps, rollback, end/replay/rate, attention and role cases | Satisfied within adapter scope |
| Browser fixtures and locators are stable | Corrected group idnumbers, expected-denial handling and selector/data separation; both final runs pass all 50 cases in every job | Satisfied on recorded tree |
| Complete matrix passes Moodle 5.0 and 5.3 | Four boundary-version database jobs above | Satisfied |
| Full 5.0–5.3 milestone has no version-specific regression | Six-job candidate/main milestone, matching exact ZIP and supplied server checks | Satisfied |

The original roadmap prefers deterministic local harnesses and tests external **adapter contracts**, avoiding
public-network availability as an automation dependency. Firefox/Safari/other-engine and public iframe
availability were not additional prerequisites in those exit criteria. Prior current-guide wording keeping
U-007 open solely for such checks is superseded by this audit. No test, guard or CI job is removed or weakened.

Replay proves that already watched coverage is not counted twice. Request-id retry and once-only completion
side effects additionally have behavioural PHPUnit cases in `tracker_test.php` and `tracking_effects_test.php`.
No forced transport-timeout browser experiment or full Cartesian combination of every role/provider/action
is claimed. Seeded UI/resume cases and unseeded earned-viewing cases remain explicitly distinguished.

## Remaining work and reopening rules

- Run the [U-020 manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) in the actual theme/browser/assistive setup.
- Record live provider, cookie/consent and content accessibility results during deployment/content acceptance.
- Complete semantic review of eight language packs; equal keys/placeholders are not semantic approval.
- Perform the integrated production/lifecycle/privacy/benchmark gate on one exact release ZIP.
- Then evaluate Moodle App, common authenticated-provider foundations, OneDrive/SharePoint, Zoom Cloud Recording,
  Google Drive and cross-provider hardening, in the agreed order.

An evidenced adapter/UI regression reopens its affected U-007 cell; a new provider needs its own contracts.
Additional engines are tested and recorded when relevant to the deployment. Do not infer that they passed
from this Chrome milestone. Manual audit does not automatically generate a release: record a green result;
create a focused correction only for a concrete finding. Do not reopen structural U-017 for advisory metrics.

U-020 remains open and is **deferred at the maintainer’s request (2026-11-10)**. This change executes
or approves no manual case. Independent language work may continue; the final production gate still
requires the manual audit and any resulting fixes.
