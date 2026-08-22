# Documentation audit

Baseline: VideoTrack **1.7.102** (`2026082201`).

## Coverage

- Non-documentation files inventoried: **284/284**.
- Named PHP functions/methods inventoried: **734**.
- Named AMD callables detected and inventoried: **647**.
- XMLDB tables documented: **7**.
- Site-setting keys documented: **57**.
- Player configuration keys documented: **133**.
- AJAX services documented: **9**.
- Language packs: eight packs with the same **990-key** contract; operational strings are translated while technical and proper terms may legitimately remain identical.
- Root overviews: `README.md` (English) and `README_IT.md` (Italian).
- Root privacy summaries: `PRIVACY.md` and `PRIVACY_IT.md`.
- Distributed CLI diagnostics documented in `21_CLI_DIAGNOSTICS.md` and covered by static read-only contracts.
- Behat browser automation is documented in `22_BEHAT_BROWSER_TESTS.md`, including current deterministic coverage and explicit provider coverage limits.
- Static resume/completion/stacked-alert contracts complement the operational Behat environment; the real 1.7.98 browser gate passed 13/13 scenarios and 195/195 steps on Moodle 5.0 and 5.3, while broader provider smoke coverage remains explicitly separate.
- Maintainer-only consolidated roadmap/lessons-history files are intentionally excluded from the distributed plugin tree and protected by `.moodleignore`.
- Chapter navigation now has an explicit focus-visible/forced-colour contract; a manual keyboard/high-contrast matrix remains a release gate for final U-020 closure.

## 1.7.102 interaction-timestamp hardening verification

- The real 1.7.101 gate passed PHPCS, extra rules, PHP lint, Grunt, PHPUnit and all 18 Behat scenarios / 256 steps on Moodle 5.0–5.3; its four PHPUnit runs each reported one Notice at test 85.
- U-007 acceptance condition AC-F02 is closed by removing the recent-playback fallback from `interaction_timestamp_allowed()`: only server-validated watched evidence can authorise the requested timestamp.
- Forward-seek permission remains a navigation policy, not timestamp authority. The existing reaction, note, bookmark and Forum clients flush current progress before the interaction and prefer the server-returned saved endpoint.
- Regression coverage rejects a recent unvalidated `playstart`, accepts validated same-session progress at the requested position and rejects a distant timestamp.
- The test-85 CSV fixture now uses a complete Moodle-generated user instead of invoking `fullname()` with a partial synthetic record.
- Current inventory remains **284/284** non-documentation files and **734 PHP / 647 AMD** named callables; production AMD, schema, capability, privacy and language packs are unchanged.

## 1.7.101 anti-cheat/session-binding documentation verification

- Playback credit is bound to the active browser `sessionid`; stale/cross-tab writes are persisted only as non-authoritative evidence.
- Accepted pause/end/hidden-page lifecycle closes clear the active credit window and require a fresh `start_playback` handshake.
- Browser focus is not treated as proof of attention: hidden-page state stops tracking, while visible-window blur remains diagnostic by default so split view and side-by-side workflows are not falsely penalised.
- The optional strict policy is resolved per learner through the hidden, non-participating course group `mod_videotrack_focus_exception`; membership changes only strict blur handling to `hiddenonly`.
- Group creation is idempotently wired to add, update, restore, upgrade and view; no accommodation reason or VideoTrack learner attribute is stored.
- `videotrack_state.serverplaybacksessionid` is declared in XMLDB, upgrade logic, Privacy API and retention cleanup.
- Production AMD is unchanged; the hardening boundary is entirely server-side.


## 1.7.100 corrective documentation verification

- The real 1.7.99 gate passed canonical PHPCS but failed the same viewing-only completion scenario on Moodle 5.0 and 5.3: 15/16 scenarios and 235/236 steps passed on each branch.
- Root cause is test-fixture-only: direct validated-state seeding wrote `videotrack_seg`/`videotrack_state` but did not execute the canonical Moodle completion synchronisation that runtime `save_segment` performs.
- The 1.7.100 seed now refreshes VideoTrack completion and synchronises `course_modules_completion` through the existing tracker helpers only for automatic-completion activities.
- Current inventory remains **281/281** non-documentation files and **723 PHP / 647 AMD** named callables.
- Production runtime, completion rules, AMD, schema, capability, privacy, Analytics, tracking and language packs are unchanged.
- Anti-cheat remains the next planned tranche only after this corrected completion browser gate is green.

## 1.7.99 pre-production documentation verification

- Current non-documentation file inventory is **281/281** after adding the deterministic Moodle-completion Behat feature.
- Function inventory is **723 PHP / 647 AMD** named callables after adding one Behat assertion for persisted core completion state.
- The feature covers viewing-only, acknowledgement-only and combined AND completion; viewing evidence is a server-validated fixture seed, while acknowledgement transitions are real browser submits.
- Production runtime, AMD, schema, capability, privacy, Analytics, tracking and language packs are unchanged.
- The next U-007 tranche is reserved for the explicit anti-cheat browser/runtime review requested by the maintainer after the corrected completion gate is green.

## 1.7.98 browser-gate documentation verification

- The real 1.7.98 Behat gate passed **13/13 scenarios and 195/195 steps** on both Moodle 5.0 and 5.3 with Chrome 151/Selenium.
- Current non-documentation file inventory is **280/280** after adding the deterministic HTML5 acknowledgement Behat feature.
- Function inventory remains **722 PHP / 647 AMD** named callables; this test-only tranche adds no PHP step or production callable.
- U-007 now covers immediate acknowledgement confirmation/persistence, video-end gating before validated completion of playback, and unlock after validated evidence reaches the final second.
- Production runtime, AMD, schema, capability, privacy, Analytics, tracking and language packs are unchanged.
- Completion-state browser assertions, stacked-alert coverage and deterministic external-provider harnesses remain pending.

## 1.7.97 pre-production documentation verification

- Current non-documentation file inventory is **279/279** after adding the deterministic HTML5 playback Behat feature.
- Function inventory is **722 PHP / 647 AMD** named callables after adding one Behat playback-state assertion method.
- U-017 is closed by stop condition after final audits of `mod_form.php`, HTML5/Vimeo/YouTube entrypoints and the remaining PHP hotspots found no further obvious low-risk extraction with a favourable complexity/risk trade-off.
- U-007 browser closure is now active: 1.7.97 adds deterministic HTML5 resume, backward-seek and play/pause scenarios without changing production runtime or AMD sources.
- Public-network-independent YouTube/Vimeo harnesses, completion browser coverage and stacked-alert browser coverage remain pending.

## 1.7.96 corrective documentation verification
- Current non-documentation file inventory remains **278/278** entries.
- Function inventory remains **721 PHP / 647 AMD** named callables; this corrective tranche adds no callable.
- Release 1.7.96 corrects the stale `completion_contract_test` expectation exposed by the real 1.7.95 PHPUnit gate: required-reaction implementation is now asserted in `local\form_validation`, where the policy was intentionally extracted.
- The 1.7.95 completion-enablement behaviour and U-017 scope are unchanged.
- No production runtime, schema, capability, privacy, report/Analytics, tracking, player, AMD, Behat or language-pack changes are included.

## 1.7.95 pre-production documentation verification

- Current non-documentation file inventory remains **278/278** entries.
- Function inventory is **721 PHP / 647 AMD** named callables after adding the pure completion-enablement helper and its behavioural test.
- Release 1.7.95 continues U-017 on `mod_form.php` by moving the custom-completion enablement decision into `local\form_validation::completion_rule_enabled()`.
- The Moodle form callback remains a thin delegate; duration/percent, reaction-rule and acknowledgement completion semantics are preserved.
- No schema, capability, privacy, report/Analytics, tracking, runtime completion, player, AMD, Behat or language-pack changes are included.

## 1.7.94 corrective documentation verification

- Current non-documentation file inventory remains **278/278** entries.
- Function inventory remains **719 PHP / 647 AMD** named callables; this corrective tranche adds no production or test callable.
- Release 1.7.94 corrects the single PHPCS `PSR2.Classes.ClassDeclaration.CloseBraceAfterBody` error reported for `tests/form_validation_test.php` in the real 1.7.93 maintainer gate.
- PHPUnit behaviour is unchanged from 1.7.93; U-017 does not advance in this corrective release.
- Current release indexes, inventories and audit markers are synchronised to 1.7.94.

## 1.7.93 pre-production documentation verification

- Current non-documentation file inventory is **278/278** entries after adding `classes/local/form_validation.php` and `tests/form_validation_test.php`.
- Function inventory is **719 PHP / 647 AMD** named callables.
- Release 1.7.93 continues U-017 by moving only autonomous scalar/JSON validation policy out of `mod_form.php`; contextual file, VTT, reaction-icon and forum validation remain local to the form.
- No AMD or language-pack files change in this tranche.

## 1.7.92 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **712 PHP / 647 AMD** named callables after adding the Analytics-highlights helper and its behavioural test.
- Release 1.7.92 continues U-017 from the server-green 1.7.91 baseline by moving top-watched, top-replayed and largest-drop selection out of `report.php` into `local\report_support::analytics_highlights()`.
- The helper preserves privacy-processed bin filtering, replay availability, ordering, discontinuity resets and five-item limits exactly.
- No SQL, Analytics builders/privacy threshold, report capabilities, exports, schema, tracking, completion or AMD/player runtime change is included.

## 1.7.91 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **710 PHP / 647 AMD** named callables after adding the state-fallback selection helper and its behavioural test.
- Release 1.7.91 continues U-017 from the server-green 1.7.90 baseline by moving raw-segment versus state-fallback selection out of `report.php` into `local\report_support::analytics_prefers_state_fallback()`.
- The helper preserves viewer-count priority and the existing strict `0.001` unique-seconds epsilon exactly; privacy thresholding still happens afterwards in the controller.
- No SQL, Analytics builders/privacy threshold, report capabilities, exports, schema, tracking, completion or AMD/player runtime change is included.

## 1.7.90 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **708 PHP / 647 AMD** named callables after adding the acknowledgement timing-count helper and its behavioural test.
- Release 1.7.90 resumes U-017 from the server-green 1.7.89 baseline by moving acknowledgement Analytics timing bucket counting out of `report.php` into `local\report_support::analytics_acknowledgement_timing_counts()`.
- The helper preserves `acknowledgement::requires_video_end()` semantics, including the existing anytime fallback for invalid or missing timing values.
- No SQL, Analytics aggregation/privacy thresholds, report capabilities, exports, schema, tracking, completion or AMD/player runtime change is included.

## 1.7.89 corrective documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory remains **706 PHP / 647 AMD** named callables; this corrective tranche adds no production or test callable.
- State Analytics now wraps the complete capability-safe scope before appending `videoid = :analyticsstatevideoid`, so the provider filter applies to every `OR` branch.
- The no-provider state scope and named parameter contract remain unchanged.
- Current release indexes/inventory headers are synchronised after the 1.7.88 release-hygiene failure; U-017 does not advance in this corrective release.

## 1.7.88 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **706 PHP / 647 AMD** named callables after adding the state-Analytics helper and its behavioural test.
- Release 1.7.88 continues U-017 with a mechanically equivalent extraction of optional provider filtering for state Analytics from `report.php` into `local\report_support::analytics_state_condition()`.
- Capability-safe scope, parameter `analyticsstatevideoid` and the controller's existing SQL concatenation semantics are preserved exactly; no precedence normalisation is included in this refactor. Segment/reaction/bookmark/integrity/acknowledgement Analytics, privacy thresholds, report capabilities, exports, schema, tracking, completion and AMD/player runtime are unchanged.

## 1.7.87 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **704 PHP / 647 AMD** named callables after adding the segment-Analytics helper and its behavioural test.
- Release 1.7.87 continues U-017 with a mechanically equivalent extraction of mandatory `servervalidated = 1` and optional provider filtering for segment Analytics from `report.php` into `local\report_support::analytics_segment_condition()`.
- Capability-safe Analytics scope and the optional provider parameter `analyticssegmentvideoid` are preserved exactly; state Analytics filtering remains in the controller and reaction/bookmark/integrity/acknowledgement Analytics, privacy thresholds, report capabilities, exports, schema, tracking, completion and AMD/player runtime are unchanged.

## 1.7.86 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory is **702 PHP / 647 AMD** named callables after adding the integrity-Analytics helper and its behavioural test.
- Release 1.7.86 resumes U-017 with a mechanically equivalent extraction of optional provider filtering for integrity Analytics from `report.php` into `local\report_support::analytics_integrity_condition()`.
- Capability-safe Analytics scope and the optional provider `videoid` filter with parameter `analyticsintegrityvideoid` are preserved exactly; reaction/bookmark/acknowledgement Analytics, privacy thresholds, report capabilities, exports, schema, tracking, completion and AMD/player runtime are unchanged.
- Canonical `phpcs.xml.dist` remains the unmodified full `moodle-extra` gate introduced by 1.7.85.

## 1.7.85 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory remains **700 PHP / 647 AMD** named callables.
- PHPUnit coverage metadata is attribute-based across every top-level test class; no legacy `@covers` annotations remain.
- Canonical `phpcs.xml.dist` now contains the full `moodle-extra` rule with no VideoTrack-specific exclusions.
- This tranche changes tests, release metadata and documentation only; runtime implementation and AMD assets are unchanged.

## 1.7.84 pre-production documentation verification

- Current non-documentation file inventory remains **276/276** entries.
- Function inventory remains **699 PHP / 647 AMD** named callables.
- All eight maintained language packs retain **987 keys** and identical placeholder contracts; assignments are now globally alphabetised and post-header section comments removed.
- Canonical `phpcs.xml.dist` re-enables both `moodle.Files.LangFilesOrdering` checks and defers only `moodle.PHPUnit.TestCaseCovers.Missing`.
- No runtime, schema, capability, privacy, export, Analytics, completion, tracking or AMD/player behaviour changed.

## 1.7.83 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.83 tree: **276/276** entries; the added file is the repository-level `phpcs.xml.dist`.
- Function inventory remains **699** named PHP functions/methods and **647** detected named AMD callables; changed source positions were refreshed.
- The two cross-version Behat failures are stabilized by targeting the unique native learner-history `<summary>` CSS selectors instead of the generic Moodle `text` selector; learner markup/runtime is unchanged.
- Canonical PHPCS release gate is now `moodle-extra` through `phpcs.xml.dist`, excluding only the three exact 1.7.82 baseline warning codes; a full unfiltered `moodle-extra` scan remains advisory debt evidence.
- U-017 is paused for this corrective release.

## 1.7.82 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.82 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **699** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.82 continues U-017 with a mechanically equivalent extraction of bookmark-Analytics SQL decoration from `report.php` into `local\report_support::analytics_bookmark_condition()`, with behavioural and controller-delegation coverage.
- Capability-safe Analytics scope, deleted-row exclusion, bookmark-only event selection and the optional provider `videoid` filter with parameter `analyticsbookmarkvideoid` remain unchanged. Privacy thresholds/clustering, reaction/integrity/acknowledgement Analytics, report capabilities, exports, schema, tracking, completion and AMD/player runtime are intentionally outside this tranche.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue only after maintainer PHPUnit/PHPCS gates for 1.7.82 are green.

## 1.7.81 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.81 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **697** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.81 continues U-017 with a mechanically equivalent extraction of standard reaction-Analytics SQL decoration from `report.php` into `local\report_support::analytics_reaction_condition()`, with behavioural and controller-delegation coverage.
- Capability-safe Analytics scope, deleted-row exclusion, standard reaction-event selection and the optional provider `videoid` filter with parameter `analyticsreactionvideoid` remain unchanged. Privacy thresholds/clustering, bookmark/integrity/acknowledgement Analytics, report capabilities, exports, schema, tracking, completion and AMD/player runtime are intentionally outside this tranche.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue only after maintainer PHPUnit/PHPCS gates for 1.7.81 are green.

## 1.7.80 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.80 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **695** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.80 continues U-017 with a mechanically equivalent extraction of per-student personal-note SQL/parameter construction from `report.php` into `local\report_support::note_event_condition()`, with behavioural and controller-delegation coverage.
- Canonical learner scope, personal-note/non-deleted-row selection, optional learner filter and inclusive `timecreated` lower/upper bounds remain unchanged. Note exports, note privacy, report capabilities, Analytics, schema, tracking, completion and AMD/player runtime are intentionally outside this tranche.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue only after maintainer PHPUnit/PHPCS gates for 1.7.80 are green.

## 1.7.79 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.79 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **693** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.79 continues U-017 with a mechanically equivalent extraction of segment-user discovery SQL/parameter construction from `report.php` into `local\report_support::segment_user_condition()`, with behavioural and controller-delegation coverage.
- Canonical learner scope and the existing `vtid` named parameter remain unchanged; no optional learner filter is introduced because the 1.7.78 segment-user discovery query intentionally did not apply one. Segment loading/validation, state queries, report capabilities/privacy, exports, Analytics, schema, tracking, completion and AMD/player runtime are intentionally outside this tranche.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue with another small autonomous server-side extraction only after maintainer PHPUnit/PHPCS gates are green.

## 1.7.78 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.78 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **691** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.78 continues U-017 with a mechanically equivalent extraction of `videotrack_state` SQL/parameter construction from `report.php` into `local\report_support::state_condition()`, with behavioural and controller-delegation coverage.
- Canonical learner scope, optional learner selection and the existing `svtid`/`suid` parameter keys remain unchanged; state query ordering, segment scope, report capabilities/privacy, exports, Analytics, schema, tracking, completion and AMD/player runtime are intentionally outside this tranche.
- The current Italian function-inventory/audit headline count is reconciled with the generated inventory while preserving historical per-release counts.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue with another small autonomous server-side extraction only after maintainer PHPUnit/PHPCS gates are green.

## 1.7.77 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.77 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **689** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.77 resumes U-017 with a mechanically equivalent extraction of personal-note user-discovery SQL/parameter construction from `report.php` into `local\report_support::note_user_condition()`, with behavioural and controller-delegation coverage.
- Learner scope, optional learner selection and the existing `vtid`/`uid` parameter keys remain unchanged; note content/date filtering, note privacy and export paths are intentionally outside this tranche.
- XMLDB tables remain **7**, site settings **57**, AJAX services **9** and browser/player configuration keys **133**.
- All eight maintained language packs remain aligned at **987** keys with matching Moodle placeholders.
- Relative Markdown links were rechecked against the candidate tree with no missing target found.
- U-017 remains in progress; the next tranche should continue with another small autonomous server-side extraction only after maintainer PHPUnit/PHPCS gates are green.

## 1.7.76 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.76 tree: **275/275** entries.
- Current function inventory regenerated from source locations: **687** named PHP functions/methods and **647** detected named AMD callables.
- Activity reporting now separates aggregate view, individual view, aggregate export and individual export through four module-context capabilities, while historical `mod/videotrack:viewreport` remains a backwards-compatible full-access grant; upgrades clone its existing role assignments into all four new capabilities before later administrator customisation.
- Aggregate-only viewers cannot use learner filtering and retain configured `analyticsminusers` masking; individual-report viewers receive exact cumulative/instance-Analytics values inside their Moodle learner/group scope. Cross-course same-video Analytics are exact only when individual-report access covers every included activity.
- Detailed/personal CSV paths require individual view plus individual export; aggregate downloads require aggregate export and preserve the same privacy threshold as the page. Reset/recalculate/report-maintenance actions remain behind historical full report access.
- XMLDB inventory still matches all **7** tables; site settings remain **57**, AJAX services **9**, browser/player configuration keys **133**.
- All eight maintained language packs expose **987** identical keys, no duplicates and matching Moodle placeholders.
- Relative Markdown links across the plugin documentation were checked against the tree with no missing target found.
- This is a corrective authorisation/privacy release; U-017 maintainability work is intentionally paused until maintainer PHPUnit/PHPCS gates are green.

## 1.7.74 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.74 tree: **273/273** entries.
- Current function inventory regenerated from source locations: **677** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.74 advances U-017 with a mechanically equivalent extraction of standard integrity-event SQL/parameter construction from `report.php` into `local\report_support::integrity_event_condition()`, with behavioural and controller-delegation coverage.
- Learner scope, optional user selection, inclusive video-time bounds and the existing `integrityvtid`/`integrityuserid`/`integritytimefrom`/`integritytimeto` parameter keys remain unchanged from the 1.7.73 controller.
- XMLDB inventory matches all **7** tables and every declared field in `db/install.xml`.
- Site-setting inventory matches all **57** `mod_videotrack` settings.
- AJAX service inventory matches all **9** declared services.
- Browser player configuration inventory matches all **133** keys.
- All eight maintained language packs expose **982** identical keys, no duplicates and matching Moodle placeholders.
- Relative Markdown links across the plugin documentation were checked against the tree with no missing target found.
- Historical technical documents under `archive/` remain historical; current indexes/inventories are rebased to 1.7.74. Internal maintainer roadmap/lesson artifacts are not part of the distributed tree.

## 1.7.73 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.73 tree: **273/273** entries.
- Current function inventory regenerated from source locations: **675** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.73 advances U-017 with a mechanically equivalent extraction of standard bookmark-event SQL/parameter construction from `report.php` into `local\report_support::bookmark_event_condition()`, with behavioural and controller-delegation coverage.
- Learner scope, bookmark/deleted filtering, optional user selection and inclusive video-time bounds retain the same SQL fragments and named-parameter keys used by the 1.7.72 controller.
- XMLDB inventory matches all **7** tables and every declared field in `db/install.xml`.
- Site-setting inventory matches all **57** `mod_videotrack` settings.
- AJAX service inventory matches all **9** declared services.
- Browser player configuration inventory matches all **133** keys.
- All eight maintained language packs expose **982** identical keys, no duplicates and matching Moodle placeholders.
- Relative Markdown links across the plugin documentation were checked against the tree with no missing target found.
- Historical technical documents under `archive/` remain historical; current indexes/inventories are rebased to 1.7.73. Internal maintainer roadmap/lesson artifacts are not part of the distributed tree.

## 1.7.72 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.72 tree: **273/273** entries.
- Current function inventory regenerated from source locations: **673** named PHP functions/methods and **647** detected named AMD callables.
- Release 1.7.72 advances U-017 with a mechanically equivalent extraction of standard reaction-event SQL/parameter construction from `report.php` into `local\report_support::reaction_event_condition()`, with behavioural and controller-delegation coverage.
- Direct writer assertions now follow the existing one-character `csv_export::write_row()` / `fputcsv()` line-feed contract; event values, column order and controller delegation remain unchanged.
- XMLDB inventory matches all **7** tables and every declared field in `db/install.xml`.
- Site-setting inventory matches all **57** `mod_videotrack` settings.
- AJAX service inventory matches all **9** declared services.
- Browser player configuration inventory matches all **133** keys, including the four forward-seek policy/runtime labels introduced after 1.6.33.
- All eight maintained language packs expose **982** identical keys, no duplicates and matching Moodle placeholders.
- Relative Markdown links across the plugin documentation were checked against the tree with no missing target found.
- Historical technical documents under `archive/` remain historical; current indexes/inventories are rebased to 1.7.72. Internal maintainer roadmap/lesson artifacts are not part of the distributed tree.

## Freshness rules

Current documents must not include release-specific assertions without a version/status label. Historical 1.4.x audits are isolated in `archive/`. Any change to schema, services, File API, player configuration, reports, privacy, accessibility or translations requires updating the corresponding numbered documents.

## Automated audit expectations

A release audit must compare file inventory to the tree, function inventory to source, language key sets/placeholders, static `get_string` references, XMLDB to backup/restore, services to executable classes, AMD sources to generated assets and Markdown links to existing files.

## 1.6.33 deletion-based retention coverage

- Scheduled retention permanently deletes expired playback segments, interactions, integrity signals and acknowledgements; it no longer retains deterministic negative-user pseudonyms or a mapping key.
- `videotrack_state` is treated as derived personal data, rebuilt from retained server-validated segments and retained completion inputs, and removed when no such input remains.
- Stale playback-credit counters are cleared, active bounded guards are preserved and Moodle custom completion is synchronised with the rebuilt state.
- User-data backups include only positive-user records inside the source retention window and omit derived state; restore applies the destination retention policy and rebuilds state after course-module completion restore.
- Privacy API erasure removes learner rows while shared activity files remain governed by the activity lifecycle.
- Regression tests cover mixed old/recent data, state rebuilding/deletion, active guards, legacy-pseudonym removal and post-retention user erasure.

## 1.6.32 playback-ledger coverage

- `mod_videotrack_start_playback` establishes a zero-credit server timestamp before a tracked segment opens.
- Every handshake and segment has a persistent request identifier protected by a unique activity/user/request index.
- Transport retries reuse an identical stored result and cannot duplicate rows, completion transitions or events.
- Playback credit is cumulative, derived from elapsed server time and an allowed rate, with a bounded clock-drift tolerance that remains cumulative debt across requests and handshakes.
- Exact covered seconds remain monotonic when the compact interval list reaches 500 entries.
- XMLDB, upgrade, backup/restore, Privacy API, language metadata, AMD sources/builds and regression tests document the same ledger contract.

## 1.6.31 runtime-contract and privacy coverage

- The client AJAX allowlist is checked against every declared Moodle AJAX service; integrity indicators are no longer rejected as `invalid-method`.
- Every learner mutation endpoint validates the Moodle sesskey before loading course-module or database context.
- Privacy masking propagates from a hidden timeline bin to the total-viewer value and to retention percentages that use that total as their denominator.
- Reaction responses expose structured icon fields only; the HTML5 player no longer parses a raw `iconhtml` field.
- Releases 1.6.25–1.6.31 are documented as code-only with respect to XMLDB and intentionally have no no-op upgrade savepoints.

## 1.6.29 explicit-participation coverage

- Activity UI, all learner mutation services and learner report populations use `mod/videotrack:participate`.
- Report access no longer disables a genuine dual-role participant.
- Standard teachers/managers remain non-tracking because the capability is granted to the Student archetype and admin do-anything is ignored for this decision.
- Role administrators can grant or revoke the capability for custom participation roles.

## 1.6.28 duration-configuration transport coverage

- Localised detector configuration is stored in a JSON script element in the activity-form DOM.
- `js_call_amd()` receives only the configuration element id, keeping its serialised argument far below Moodle’s 1024-character developer-warning threshold.
- The AMD module validates and parses that DOM configuration before installing the detector.

## 1.6.27 automatic-duration coverage

- The trusted activity form can suggest duration from YouTube, Vimeo or local-file metadata.
- The suggestion is teacher-editable, announced through an accessible live region and becomes authoritative only when the teacher saves.
- Learner runtime duration remains unable to update the stored value; provider or browser failure leaves the field manual.

## 1.6.26 player-tool reliability coverage

- A verified duration of `0` explicitly disables watched-percentage calculation and percentage-based completion while preserving validated interval tracking and enabled study tools.
- The complete HTML5-controls fieldset is hidden for YouTube and Vimeo and restored when the source changes to a local upload.
- Learner reaction controls remain available while playing or paused, but the server accepts only already-watched validated timestamps.
- Enabled notes and bookmarks are rendered for explicit participants; users without `mod/videotrack:participate` receive a disabled preview and cannot create learner telemetry.
- The PHPCS findings reported against 1.6.25 in `view.php`, `report.php` and `tests/tracker_test.php` are corrected in the 1.6.26 source tree.

## 1.6.25 interface regression coverage

- The acknowledgement Analytics renderer initialises both count and progress suppression state.
- Reaction definitions remain inside the main Reactions form section and are not modelled as a fixed two-item set.
- Activity-view ordering is reactions, optional Forum action, then personal reaction history/bookmarks.
- Report-capable preview and learner persistence remain separate trust states.

## 1.6.30 bookmark persistence coverage

The current documentation records the restored parity between the AMD `bookmark` segment reason and the PHP validation whitelist. It also preserves the security contract that a bookmark timestamp must belong to server-validated watched progress.

- 1.7.45 adds Moodle Behat/generator documentation and records U-007 as in progress rather than complete.
