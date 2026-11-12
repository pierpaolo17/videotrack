# Source-grounded language review

Current tree: VideoTrack **1.7.251** (`2026111201`). Assisted review recorded 2026-11-11.

## Completed semantic scope and its limits

The cumulative assisted source review covers **all 997 keys per pack / 7976 values** in DE, EN, ES, FR,
HI, IT, PL and PT. This pass adds the last **208 keys / 1664 values**, without reusing a previous key.
The accepted earlier scope contains 789 keys / 6312 values. No current key is outside this review claim.
This is not native linguistic sign-off, WCAG certification or proof of every deployed provider/browser.

The complete manifest is the sorted key set in [the canonical pack](../../lang/en/videotrack.php), identical
in every pack. SHA-256 of those 997 UTF-8 key names, newline-separated with a final newline:
`06d08c38e974996779f5ce9f6e05fce742c322b33c880c4f4091cc9f27983903`.
All existing keys, order, Moodle placeholder multiplicities, literal Forum template tokens and numeric
tokens are preserved. Retained strings without an active rendering call do not imply new functionality.

## Accepted actual baseline

- 1.7.249 / `2026111102`, 412 files identical to main `667e678b52eaa6fe04e93a717c4bfed9efda535d`.
- ZIP SHA-256: `d71f3c7e7b4c895cbd7fbbfbd1456f31bca2643f40f881263e4795edd223ae8c`.
- Server report SHA-256: `1210838cb96ad931452ac616ce2bc2aee8b1db67fdd4d367f918f4def0aa0789`.
- Candidate/main CI `43014227736` / `43017694467`: six jobs each pass first-attempt Behat 50/1074,
  PHPUnit 379/4076, Grunt and strict 10/0/0. Actual Moodle 5.0/5.3 server reports pass PHPCS/lint 166,
  Grunt and PHPUnit 379/4076; both supplied strict outputs are 10/0/0. AMD hashes before/after Grunt match.
  The known Browserslist notice, 24 reviewed PHPMD findings, Moodle alias deprecations and Psalm informative
  totals 732/760 remain recorded. Configured gates pass; no zero-advisory claim is made.

## Current source findings

- `report.php` showed reset controls only with full report access and `mod/videotrack:managereactions`,
  but its direct reset POST required only the historical full-report gate. The candidate adds the missing
  management check before learner validation or deletion, preserving POST, sesskey and visibility checks.
  Technical capability keys/role grants are unchanged. The legacy-named permission now describes its actual
  reset use; editing reaction definitions remains governed by Moodle activity editing, not this reset guard.
- `tracker::update_state()` retains rejected segments as `servervalidated = 0` audit rows for stale sessions,
  blocked forward intervals or server-credit rejection; rejected request replay also returns `-1`.
  `save_segment` maps all these cases to `suspicioussegment`. The warning now states that the segment was not
  counted as viewed time, without falsely claiming absence from storage or only an elapsed-window cause.
- `core/api::call()` returns null before transport when offline deferral is requested; it does not itself
  install an automatic offline queue. The diagnostic says “not sent”. Its transient-error branch retries
  the request, not the error. Focus-exception wording retains hidden-document pause only when enabled,
  plus unchanged server validation. Existing Moodle group descriptions are preserved, not migrated by text.
- Targeted corrections distinguish viewed percentage from full activity completion in the Polish outline,
  video seeking from textual search in the Spanish override description, and acknowledgement of reading
  from receipt/acceptance in ES/HI. Polish preset help no longer invents a lowercase-only restriction;
  units/labels, Italian diacritics and Hindi wording are aligned. Grade labels refer to viewing progress,
  not assignment submissions; the existing real report grading path is unchanged.

Sources: `report.php`, `report_access`, `learner_scope`, `save_segment`, `tracker`, `focus_policy`, `core/api`,
`presets.php`, `locallib.php`, native grade integration and the current Behat/report/interaction tests.
The reset guard is a deliberate production-logic change; this candidate is not described as wording-only.

## Exact changed keys and values

- `acknowledgement:checkboxlabel`
- `debug:ajaxdeferredoffline`
- `debug:ajaxretry`
- `debug:timedtexttimefailed`
- `focus:exceptiongroupdescription`
- `integrity:settingsheader`
- `outline:nodata`
- `outline:percent`
- `presets:key_help`
- `presets:reactions_help`
- `reactioniconcategory:activity`
- `reactionpreset:none`
- `videotrack:grade`
- `videotrack:managereactions`
- `videotrack:managereactions_desc`
- `videotrack:overrideplayersettings_desc`
- `warning:notetruncated`
- `warning:suspicioussegment`

| Pack | Values changed |
|---|---:|
| DE | 8 |
| EN | 7 |
| ES | 10 |
| FR | 8 |
| HI | 11 |
| IT | 9 |
| PL | 12 |
| PT | 8 |

Total: **73 values across 18 existing keys**. Six strings change in every pack; other changes are targeted.
Public AJAX method names, warning codes, capability keys, DB fields, CSV headers and persisted data are preserved.

## Manual accessibility evidence

The maintainer supplied M01–M08 marked PASS on **1.7.248 / Moodle 5.0 / Academi / Windows 11 / Chrome / NVDA**.
These are reported executed observations, no longer NOT RUN. The report was received on 2026-11-11; exact
browser/theme/NVDA versions and execution date were not supplied. Moodle 5.3 manual coverage is unrecorded.
See the [scoped manual record](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020). Do not infer manual
249/250 execution from unchanged DOM or from automatic tests. Later wording changes have their own delta.
U-020 is partially covered; U-017 and the accepted essential U-007 milestone remain closed.

## Acceptance update and current candidate

The complete assisted review and reset guard were accepted on actual 250/main `7a5b5f391dcc1ef0fd63f8640ce325e93f9ada25`,
with candidate/merged CI, both server reports and strict 10/0/0. The observations below retain the initial
250 candidate history. Corrected CI and a fresh post-merge 5.3/MariaDB retry passed; this does not establish
why that job's test server stopped initially. No language value changes in 251. Its two new lifecycle tests
are pending native CI; see [current integration evidence](28_PRODUCTION_EXIT_AUDIT.md).

## 1.7.250 candidate history and roadmap

The candidate retains 412 files, 166 PHP files, 31 documents per EN/IT guide, 345 inventory rows and 654 AMD
callables. PHP callables become 1103 through two test-only helpers. XMLDB remains `2026110601`, with
7 tables / 151 fields / 22 indexes / 22 foreign keys. ER changes only version labels. AMD, backup/privacy
implementations, nine services, existing role grants and CI/static-analysis rules are unchanged.

Two new browser scenarios submit real same-session POSTs with a valid session key. A full-report viewer
without the management grant must receive only the matching nopermissions denial; all captured plugin,
native grade/completion and other-learner records must remain identical. An authorised viewer must reset
only the selected learner, clearing its grade/completion while preserving other learners. Playback and
completion evidence is browser-earned; the points grade is an explicit ordinary Moodle grade fixture.
The denied page is checked and restored inside the step, retaining Moodle's automatic exception hooks.

The six uploaded CI artifacts match commit `d75af28e890d12c7dab4edd507c404ac3f3f1ad9` in run
`43075665485`, attempt 2. Every supplied report passes **379 PHPUnit tests / 4076 assertions**,
**52 Behat scenarios / 1154 steps** and strict **10/0/0**; no Behat scenario rerun is present in those reports.
Grunt completes in all six environments. This is real CI evidence, not a native execution in this workspace.

Overall CI remains blocked by one PHPCS warning: the new denied-reset assertion has 133 characters at
helper line 1636, beyond the 132-character limit. The same-version correction wraps that expression only
and updates the helper locations in both callable inventories. Assertions, runtime, translations, XMLDB,
AMD, version and CI rules are unchanged. The corrected candidate needs a new successful six-job run before
merge, followed by post-merge CI and actual merged ZIP/server/strict acceptance.

PHPStan and the configured Psalm scopes pass. Psalm reports 733/761 advisory issues, versus 732/760 for
accepted 249: one additional advisory per analysed Moodle branch. Its detail is not displayed in the supplied
reports, so its cause is not inferred. The 24 PHPMD findings are byte-identical to accepted 249. No claim of
zero findings or unchanged advisory totals is made.

Finish the integrated web/lifecycle/privacy/benchmark gate and remaining scoped manual evidence; complete
the App decision, then implement agreed multilingual VTT subtitles before authenticated foundation,
OneDrive/SharePoint, Zoom Cloud Recording and Google Drive. No VTT runtime extension is included here.
