# VideoTrack changelog

## 1.7.105 - 2026-08-22

- Continue U-007 with a deterministic HTML5 browser scenario for stacked player notices.
- Verify that the persistent resume and forward-seek policy notices coexist with a transient validation error produced by the real learner-note form.
- Verify that dismissing the transient status message leaves both persistent notices in place.
- Keep production runtime, AMD sources/builds, schema, services, capability, privacy, completion and language packs unchanged.

## 1.7.104 - 2026-08-22

- Correct the two canonical PHPCS spacing errors in the 1.7.103 persisted-seek Behat assertion.
- Align the browser contract with the real server-authoritative ledger: the raw seek snapshot must end at the pre-seek browser boundary, while the aggregate state must never credit or resume inside the skipped gap whether the strict server budget accepts or retains that row as non-authoritative evidence.
- Preserve the 1.7.103 runtime, scenario count and 0.75-second media-event tolerance; this is a root-cause-only corrective release after the identical Moodle 5.0/5.3 gate failure.
- Keep production runtime, AMD, schema, capability, privacy, completion and language packs unchanged.

## 1.7.103 - 2026-08-22

- Continue U-007 with a deterministic HTML5 browser scenario that plays real media, performs a blocked forward seek and verifies the persisted `videotrack_seg` row end-to-end.
- Capture the browser timestamp immediately before the jump and require the latest `endreason = 'seek'` row to be server-validated, non-empty and aligned with that pre-seek boundary within the documented media-event tolerance.
- Prove that the skipped interval is not credited by querying the real Moodle database after the asynchronous segment write instead of relying only on a static JavaScript ordering contract.
- Keep production runtime, AMD sources/builds, schema, capability, privacy, completion and language packs unchanged; public YouTube/Vimeo availability remains outside this deterministic tranche.

## 1.7.102 - 2026-08-22

- Close U-007 acceptance condition AC-F02: reaction, note, bookmark and Forum timestamps now require server-validated watched evidence at the requested position, even when forward seeking is allowed.
- Remove the permissive fallback that treated any recent same-session `playstart` row as authority for an unrelated interaction timestamp.
- Preserve legitimate allowed-forward-seek interactions by relying on the existing client progress flush and its server-returned saved endpoint before each write.
- Replace the incomplete synthetic user in `csv_event_writer_test` with a real Moodle-generated user, addressing the single PHPUnit Notice localised at test 85 across Moodle 5.0–5.3.
- Add regression coverage for rejected `playstart`-only evidence, accepted validated same-session progress and rejected distant timestamps; no schema, capability, privacy, language-pack or AMD change is included.

## 1.7.101 - 2026-08-21

- Harden the server-authoritative playback ledger by binding each credit window to the browser session that opened it with `start_playback`.
- Reject cross-session or stale-session segment writes as `servervalidated=0` without allowing them to consume or reset another tab's budget.
- Close the authorised credit window on accepted pause/end/page-hide/tab-hide lifecycle endings so resumed playback requires a fresh explicit handshake.
- Document why hidden-page state is authoritative for stopping credit while plain visible-window blur remains diagnostic/default-accessibility-safe for split view, grouped tabs and side-by-side windows.
- Add schema/upgrade/privacy/retention and regression coverage for the active playback-session identifier.
- Add a hidden, non-participating Moodle course group with stable idnumber `mod_videotrack_focus_exception` for learners who need visible split-view or assistive-tool use under the optional strict window-focus policy.
- Limit the group exception to strict `window.blur` handling: hidden tabs, AC-01 session binding, server credit, seek/rate controls and completion validation remain unchanged.
- Ensure the group during add, update, restore, upgrade and activity view; add PHPUnit and browser-contract coverage and correct the PHPCS formatting of the AC-01 terminal-reason condition.

## 1.7.100 - 2026-08-21

- Correct the 1.7.99 Behat completion fixture after the real Moodle 5.0/5.3 gate showed that direct validated-state seeding did not execute the normal Moodle completion synchronisation performed by runtime writes.
- Make the existing validated-watched Behat seed call the canonical `tracker::refresh_completion()` and `tracker::update_moodle_completion_if_changed()` path only when the activity uses automatic completion.
- Keep production runtime, completion logic, player/AMD, schema, capability, privacy, Analytics and language packs unchanged; this is a corrective-only release and anti-cheat work remains paused until the completion browser gate is green.

## 1.7.99 - 2026-08-21

- Continue U-007 browser-automation closure with deterministic Moodle completion synchronisation coverage on the local HTML5 fixture.
- Verify persisted core completion transitions for viewing-only, acknowledgement-only and combined AND completion; viewing evidence is seeded as server-validated fixture data while acknowledgement transitions use real browser interactions.
- Add one Behat assertion step for `course_modules_completion`; keep production runtime, AMD, schema, capability, privacy, Analytics and language packs unchanged.
- Reserve the next U-007 tranche for the explicit anti-cheat browser/runtime review requested by the maintainer after this completion gate is green.

## 1.7.98 - 2026-08-21

- Continue U-007 browser-automation closure with deterministic HTML5 acknowledgement coverage for immediate confirmation, video-end gating and persistence after confirmation.
- Reuse the local MP4 fixture and existing validated-state seeding; add no production runtime, PHP step definition or AMD change.
- Keep completion-state browser assertions, stacked-alert coverage and deterministic YouTube/Vimeo harnesses for later U-007 tranches.

## 1.7.97 - 2026-08-21

- Close U-017 by stop condition after the final `mod_form.php`, player and PHP-hotspot audits found no further obvious low-risk extraction whose benefit exceeds added indirection or runtime risk.
- Start the U-007 browser-automation closure phase with a deterministic local HTML5 playback feature covering validated resume, allowed backward seek and real play/pause state.
- Add a Behat playback-state assertion step without changing production runtime, AMD sources, schema, capability, privacy, tracking or completion behaviour.
- Keep public-network YouTube/Vimeo harnesses out of this tranche; provider-specific deterministic coverage remains a later U-007 step.

## 1.7.96 - 2026-08-20

- Correct the stale completion static contract introduced by the 1.7.95 `mod_form.php` delegation: required-reaction inspection now follows the extracted `local\form_validation` policy instead of requiring the implementation to remain inline in the form.
- Keep the 1.7.95 completion-enablement behaviour unchanged; no U-017 expansion is included.
- Synchronise current release documentation markers to 1.7.96.
- No production runtime, database/schema, capability, report/Analytics, tracking, completion behaviour, player/AMD, Behat or language-pack changes.

## 1.7.95 - 2026-08-20

- Continue U-017 on `mod_form.php` by extracting the pure custom-completion enablement decision into `local\\form_validation`.
- Preserve completion-percent + duration, reaction-rule and acknowledgement completion semantics while keeping the Moodle form callback as a thin delegate.
- Add behavioural coverage for every completion-enablement branch and strengthen the delegation contract.
- No changes to schema, capability, privacy, report/Analytics, tracking, runtime completion, player, AMD, Behat or language packs.

## 1.7.94 - 2026-08-20

- Correct the single canonical-PHPCS `PSR2.Classes.ClassDeclaration.CloseBraceAfterBody` error in `tests/form_validation_test.php` by removing the blank line immediately before the class closing brace.
- Keep the 1.7.93 form-validation extraction and PHPUnit behaviour unchanged; no U-017 expansion is included.
- Synchronise current release documentation markers to 1.7.94.
- No production runtime, database/schema, capability, report/Analytics, tracking, completion, player/AMD, Behat or language-pack changes.

## 1.7.93 - 2026-08-20

- Continue U-017 by moving autonomous scalar/JSON activity-form validation policy out of `mod_form.php` into `local\form_validation`.
- Preserve completion-percent, player-width, rewind/fast-forward step, reaction-minimum/preset JSON, acknowledgement timing/text and duration validation semantics exactly.
- Keep upload/draft-file, VTT, reaction-icon and forum/context validation inside `mod_form.php`; add direct behavioural coverage plus a delegation contract.
- No database/schema, capability, report/Analytics, tracking, completion runtime, player/AMD, Behat or language-pack changes.

## 1.7.92 - 2026-08-20

- Continue U-017 by extracting Analytics highlight selection from `report.php` into `local\report_support::analytics_highlights()`.
- Preserve top-watched, top-replayed and largest-drop filtering, ordering, suppression handling and five-item limits exactly.
- Add direct behavioural coverage for suppressed/null bins, replay availability, tie ordering, discontinuities and top-five truncation, plus strengthen the controller-delegation/non-inline contract.
- No SQL, Analytics builders/privacy threshold, capability, export, schema, tracking, completion, player/AMD, Behat or language-pack changes.

## 1.7.91 - 2026-08-20

- Continue U-017 by extracting the raw-segment versus state-fallback Analytics selection from `report.php` into `local\report_support::analytics_prefers_state_fallback()`.
- Preserve the existing priority exactly: state data wins on a higher viewer count, or on equal viewers only when unique watched seconds exceed the raw result by more than `0.001`.
- Add direct behavioural coverage for viewer priority, strict epsilon handling and equal-data fallback, plus strengthen the controller-delegation/non-inline contract.
- No SQL, Analytics builders/privacy threshold, capability, export, schema, tracking, completion, player/AMD, Behat or language-pack changes.

## 1.7.90 - 2026-08-20

- Continue U-017 by extracting acknowledgement-Analytics timing bucket counting from `report.php` into `local\report_support::analytics_acknowledgement_timing_counts()`.
- Preserve the canonical acknowledgement timing semantics by delegating each enabled instance to `acknowledgement::requires_video_end()`, including its invalid/missing-value fallback to anytime.
- Add behavioural coverage for anytime/video-end/invalid timing buckets and strengthen the controller-delegation contract.
- No SQL, Analytics aggregation/privacy threshold, capability, export, schema, tracking, completion, player/AMD, Behat or language-pack changes.

## 1.7.89 - 2026-08-20

- Correct state-Analytics provider filtering so a selected YouTube/Vimeo `videoid` applies to the complete capability-safe multi-activity scope, rather than only the final `OR` branch under SQL operator precedence.
- Preserve the existing `analyticsstatevideoid` parameter and leave the no-provider state scope byte-for-byte unchanged.
- Add/adjust the behavioural contract with a multi-branch `OR` scope to lock the corrected parenthesisation.
- Correct current-release documentation metadata left stale in 1.7.88; no U-017 expansion, schema, capability, export, tracking, completion, player/AMD or Behat changes.

## 1.7.88 - 2026-08-20

- Continue U-017 by extracting state-Analytics optional provider filtering from `report.php` into `local\report_support::analytics_state_condition()`.
- Preserve the existing capability-safe state scope, provider parameter name `analyticsstatevideoid`, and existing SQL concatenation semantics exactly; no query-normalisation or precedence change is included in this refactor.
- Add behavioural and controller-delegation contracts for the extracted helper.
- No changes to Analytics aggregation/privacy thresholds, report capabilities, exports, schema, tracking, completion, player/AMD, Behat or language packs.

## 1.7.87 - 2026-08-19

- Continue U-017 maintainability from the server-green 1.7.86 baseline by moving validated-segment Analytics SQL/parameter decoration out of `report.php` into `local\report_support::analytics_segment_condition()`.
- Preserve the capability-safe Analytics scope, mandatory `servervalidated = 1` filter and optional provider `videoid` filter with the existing `analyticssegmentvideoid` named parameter under direct behavioural PHPUnit coverage plus the controller-delegation/non-inline contract.
- Keep state Analytics filtering, reaction/bookmark/integrity/acknowledgement Analytics, privacy thresholds, report capabilities, exports, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.86 - 2026-08-19

- Resume U-017 maintainability from the server-green 1.7.85 baseline by moving integrity-Analytics provider-filter SQL/parameter decoration out of `report.php` into `local\report_support::analytics_integrity_condition()`.
- Preserve the capability-safe Analytics scope exactly when no provider filter is selected and preserve the existing `analyticsintegrityvideoid` named parameter when a provider video id is selected, under direct behavioural PHPUnit coverage plus the controller-delegation contract.
- Keep integrity summarisation/query ordering, reaction/bookmark/acknowledgement Analytics, privacy thresholds, report capabilities, exports, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.85 - 2026-08-19

- Eliminated the remaining 36 `moodle.PHPUnit.TestCaseCovers.Missing` findings by assigning explicit PHPUnit coverage metadata according to test intent: `CoversNothing` for source/documentation contracts, `CoversClass` for completion and generator behavior, and `CoversFunction` for global helper callbacks.
- Migrated the remaining legacy `@covers` doc-comment metadata in `lib_test.php`, `locallib_test.php` and `timed_text_test.php` to PHPUnit attributes, targeting removal of the 20 known PHPUnit 12 coverage-metadata deprecations.
- Removed the final PHPCS exclusion from `phpcs.xml.dist`; the canonical release gate is now the full `moodle-extra` ruleset with no VideoTrack-specific warning exclusions.
- Added a release-hygiene contract that prevents legacy `@covers` annotations from returning and requires every top-level PHPUnit test class to declare coverage metadata through attributes.
- No runtime, database, capability, privacy, report, Analytics, completion behavior, tracking or AMD/player implementation changed in this tranche.

## 1.7.84 - 2026-08-19

- Quality cleanup: globally alphabetised all eight maintained VideoTrack language packs without changing any key, translated value or Moodle placeholder contract.
- Removed post-header language-file section comments that triggered `moodle.Files.LangFilesOrdering.UnexpectedComment`; language grouping remains discoverable from the alphabetic keys themselves.
- Tightened the canonical `phpcs.xml.dist`: language ordering/comment sniffs are release-blocking again, leaving only the known `moodle.PHPUnit.TestCaseCovers.Missing` baseline debt deferred.
- Extended release-hygiene contracts so language packs must remain alphabetically ordered and free of post-header `//` comments.
- Refreshed documentation-audit facts that were stale after the 1.7.83 multi-version run: current non-documentation inventory is 276/276 and the maintainer Behat environment is operational with the 7/7 scenario matrix recorded.
- No runtime, report, capability, privacy, schema, tracking, completion, Analytics, export, AMD or player behaviour changes.

## 1.7.83 - 2026-08-19

- Stabilize the two common Behat failures seen identically on Moodle 5.0, 5.1, 5.2 and 5.3 by clicking the native learner-history `<summary>` elements through their unique CSS selectors instead of Moodle's generic `text` selector; runtime markup and behaviour are unchanged.
- Add repository-level `phpcs.xml.dist` as the canonical release-gate configuration based on `moodle-extra`, deferring only the three exact warning codes exposed as pre-existing 1.7.82 debt: language-key ordering, unexpected language-file comments and missing PHPUnit coverage metadata.
- Keep a full unfiltered `moodle-extra` scan as an advisory quality/debt report; U-017 is intentionally paused for this corrective release.

## 1.7.82 - 2026-08-18

- Continue U-017 maintainability from the server-green 1.7.81 baseline by moving bookmark-Analytics SQL decoration out of `report.php` into `local\report_support::analytics_bookmark_condition()`.
- Preserve the capability-safe Analytics scope, deleted-row exclusion, bookmark-only selection and optional provider `videoid` filter with the existing `analyticsbookmarkvideoid` parameter under direct behavioural coverage plus the controller-delegation contract.
- Keep Analytics privacy thresholds/clustering, reaction/integrity/acknowledgement Analytics, report capabilities, export paths, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.81 - 2026-08-18

- Continue U-017 maintainability from the server-green 1.7.80 baseline by moving standard reaction-Analytics SQL decoration out of `report.php` into `local\report_support::analytics_reaction_condition()`.
- Preserve the capability-safe Analytics scope, deleted-row exclusion, standard-reaction selection and optional provider `videoid` filter with the existing `analyticsreactionvideoid` parameter under direct behavioural coverage plus the controller-delegation contract.
- Keep Analytics privacy thresholds/clustering, bookmark/integrity/acknowledgement Analytics, report capabilities, export paths, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.80 - 2026-08-18

- Continue U-017 maintainability from the server-green 1.7.79 baseline by moving per-student note-list SQL/parameter construction out of `report.php` into `local\report_support::note_event_condition()`.
- Preserve the canonical learner scope, personal-note/deleted-row selection, optional learner filter and inclusive note creation-date bounds under direct behavioural PHPUnit coverage plus the controller-delegation contract.
- Keep note export paths, note privacy, report capabilities, Analytics, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.79 - 2026-08-18

- Continue U-017 maintainability from the server-green 1.7.78 baseline by moving segment-user discovery SQL/parameter construction out of `report.php` into `local\report_support::segment_user_condition()`.
- Preserve the canonical learner scope and existing `vtid` named parameter exactly under direct behavioural PHPUnit coverage plus the controller-delegation contract; no learner filter is added because the original segment-user discovery query did not apply one.
- Keep segment loading/validation, state queries, report capabilities/privacy, export paths, Analytics, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.78 - 2026-08-18

- Continue U-017 maintainability from the server-green 1.7.77 baseline by moving `videotrack_state` report SQL/parameter construction out of `report.php` into `local\report_support::state_condition()`.
- Preserve the canonical learner scope, optional learner filter and existing `svtid`/`suid` named parameters under direct behavioural PHPUnit coverage plus the controller-delegation contract.
- Keep state queries/order, segment scope, report capabilities/privacy, export paths, Analytics, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.77 - 2026-08-18

- Resume U-017 maintainability from the server-green 1.7.76 baseline by moving personal-note user-discovery SQL/parameter construction out of `report.php` into `local\report_support::note_user_condition()`.
- Preserve the canonical learner scope, optional learner filter and existing `vtid`/`uid` named parameters under direct behavioural PHPUnit coverage plus the controller-delegation contract.
- Keep note content/date filtering, personal-note privacy, CSV exports, report capabilities, Analytics, schema, tracking, completion and AMD/player runtime unchanged.

## 1.7.76 - 2026-08-18

- Correct the PHPCS/Moodle Extra PSR-12 blocker in `tests/report_contract_test.php` reported against the otherwise-green 1.7.75 authorisation release by assigning the granular capability names to a local array before iterating them.
- Preserve the 1.7.75 four-capability report model, legacy `mod/videotrack:viewreport` compatibility, upgrade permission cloning, aggregate privacy thresholds, individual visibility and export gating without runtime changes.
- Keep U-017 paused for this root-cause-only corrective release; no report query, Analytics, schema, tracking, completion or AMD/player behaviour changes are included.

## 1.7.75 - 2026-08-17

- Separate activity-report authorisation into four granular capabilities: aggregate viewing, individual viewing, aggregate export and individual export, while retaining the historical `mod/videotrack:viewreport` capability as a backwards-compatible full-access grant. On upgrade, each new capability clones the site's existing `viewreport` role assignments so customised grants/revocations are preserved; fresh installs use the standard report-viewer archetypes.
- Keep `mode=cumulative` and instance Analytics exact for viewers who may inspect individual learner reports, but preserve the configured `analyticsminusers` masking for aggregate-only viewers; aggregate-only access cannot use the learner filter, and cross-course instance Analytics become exact only when individual-report access is present for every included activity.
- Gate aggregate and individual download paths independently, keep personal-note/detailed CSV exports behind individual permissions, retain report maintenance/reset/recalculation behind the historical full-report capability, and add behavioural/static coverage plus privacy documentation for delegated assistant roles. U-017 remains paused for this corrective authorisation release.

## 1.7.74 - 2026-08-17

- Continue U-017 maintainability from the server-green 1.7.73 baseline by moving standard integrity-event SQL/parameter construction out of `report.php` into `local\report_support::integrity_event_condition()`.
- Preserve learner scope, optional user selection and inclusive video-time bounds under direct behavioural PHPUnit coverage plus a controller-delegation contract, retaining the existing integrity table, parameter keys and aggregate queries.
- Reduce `report.php` from 2,881 to 2,875 lines without changing integrity aggregation semantics, exports, Analytics, schema, tracking, completion or AMD/player runtime.

## 1.7.73 - 2026-08-17

- Continue U-017 maintainability from the server-green 1.7.72 baseline by moving standard bookmark-event SQL/parameter construction out of `report.php` into `local\report_support::bookmark_event_condition()`.
- Preserve learner scope, deleted/bookmark selection, optional user filter and inclusive video-time bounds under direct behavioural PHPUnit coverage plus a controller-delegation contract.
- Reduce `report.php` from 2,887 to 2,881 lines without changing bookmark aggregation queries, report summaries, CSV export paths, Analytics, schema, tracking, completion or AMD/player runtime.

## 1.7.72 - 2026-08-17

- Continue U-017 maintainability from the server-green 1.7.71 baseline by moving standard reaction-event SQL/parameter construction out of `report.php` into `local\report_support::reaction_event_condition()`.
- Preserve learner scope, deleted/note/bookmark exclusion, optional user/reaction filters and inclusive video-time bounds under direct behavioural PHPUnit coverage plus a controller-delegation contract.
- Reduce `report.php` from 2,896 to 2,887 lines without changing database reads, event recordset fields/order, exports, Analytics, schema, tracking, completion or AMD/player runtime.

## 1.7.71 - 2026-08-17

- Correct the 1.7.70 U-017 CSV-writer test/style findings without changing runtime export semantics: replace constructor-promoted dependencies in `local\csv_event_writer` with explicitly documented readonly member variables required by Moodle PHPCS.
- Align the two direct writer assertions with the existing `csv_export::write_row()` one-character-delimiter contract, which delegates to `fputcsv()` and therefore emits a line-feed (`\n`) terminator rather than the CRLF literal assumed by the new tests.
- Keep `csv_event_writer::write()`, custom CSV column values/order, `report.php`, queries, Analytics, tracking, completion, schema and AMD/player runtime unchanged; this is a corrective-only release before U-017 continues.

## 1.7.70 - 2026-08-17

- Resume U-017 maintainability from the server-green 1.7.69 baseline by moving the custom teacher CSV event-row writer out of `report.php` into the autoloaded `local\csv_event_writer` service and moving custom-event header assembly into `local\csv_export`.
- Preserve detailed/per-student/overall row ordering, canonical video timestamps, identity-field formatting, unknown-user exclusion and aggregate student counts under direct behavioural PHPUnit coverage, while removing the fragile multi-variable `$writeeventrow` closure from the controller.
- Reduce `report.php` from 2,950 to 2,896 lines without changing report queries, filters, clustering, Analytics semantics, schema, tracking, completion or AMD/player runtime.

## 1.7.69 - 2026-08-17

- Fix the remaining teacher-dashboard course-discovery defect exposed by the 1.7.68 behavioural test: Moodle's `get_user_capability_course()` returns a numerically indexed list, so `teacher_analytics::accessible_courses()` now keys validated courses explicitly by each record's real `course->id` instead of treating the source array key as a course id.
- Preserve the 1.7.68 explicit `fullname,visible` field request and the exact one-learner Analytics semantics while keeping hidden-course checks, capability scopes and all report filters unchanged.
- Reuse the existing behavioural test as the regression gate for course-id keying and metadata discovery; no change to `report.php`, `reports_course.php`, `reports_teacher.php`, `course_analytics.php`, AMD, schema, tracking, completion or U-017 is included.

## 1.7.68 - 2026-08-17

- Fix teacher-dashboard course discovery exposed by the 1.7.67 behavioural test: `teacher_analytics::accessible_courses()` now explicitly requests the `fullname` and `visible` course fields from Moodle's `get_user_capability_course()` API instead of reading fields that were not requested.
- Keep the exact one-learner Analytics semantics introduced in 1.7.67 unchanged while preserving hidden-course filtering, course/activity/group/period scopes and report capabilities.
- Strengthen the existing behavioural test to assert the discovered course metadata before exercising the exact dashboard row; no AMD, schema, tracking, completion or U-017 change is included.

## 1.7.67 - 2026-08-17

- Make `reports_teacher.php` exact for users already authorised by `mod/videotrack:viewcoursereport`: the cross-course dashboard now requests `analytics::EXACT_REPORT_MIN_USERS` instead of the configured `analyticsminusers`, so one-learner courses/activities and event subgroups are no longer masked.
- Remove the teacher-dashboard privacy notice and masked-cell presentation while preserving accessible-course discovery, activity/module visibility, group filters, period filters and per-activity report capabilities.
- Keep `teacher_analytics` and `course_analytics` generic threshold-capable services, leave instance/course Analytics semantics unchanged, and retain `analyticsminusers` only for aggregate summaries outside the exact Analytics dashboards. U-017 remains paused until this teacher-dashboard correction is server-green.

## 1.7.66 - 2026-08-17

- Fix the course Analytics exact-report path introduced in 1.7.65: `course_analytics::summarise_states()` now honours `analytics::EXACT_REPORT_MIN_USERS` instead of silently clamping every requested threshold to at least two users.
- Preserve generic threshold behaviour for callers that pass values greater than one; only the explicit exact-report threshold now disables suppression as intended.
- Keep `reports_course.php`, `reports_teacher.php`, instance Analytics, schema, tracking, completion and AMD/player runtime otherwise unchanged; this is a corrective-only release before the dedicated `reports_teacher.php` follow-up.

## 1.7.65 - 2026-08-17

- Make `reports_course.php` exact for users already authorised by `mod/videotrack:viewcoursereport`: the page now requests `analytics::EXACT_REPORT_MIN_USERS` instead of the configured `analyticsminusers`, so one-learner activities and completion/reaction/note/bookmark subgroups are no longer masked.
- Remove the course-dashboard privacy-suppression notice and unreachable masked-cell presentation while preserving course/module visibility, group scopes and the underlying generic `course_analytics` threshold support for other callers.
- Keep `reports_teacher.php`, instance Analytics semantics, schema, tracking, completion and AMD/player runtime unchanged; the teacher-centric dashboard remains the next dedicated follow-up before U-017 resumes.

## 1.7.64 - 2026-08-17

- Make `report.php?mode=analytics` exact for authorised report viewers inside the existing Moodle capability, activity/course and group scopes: the configured minimum-user privacy threshold no longer masks instance Analytics viewing bins, replay metrics, reactions/clusters, bookmark counts, integrity indicators or acknowledgement aggregates.
- Keep personal note text excluded from aggregate Analytics and bookmark labels/timestamps owner-only; this changes aggregate visibility for an already-authorised teacher/report viewer, not Moodle access control.
- Keep CSV/Excel/ODS instance-Analytics exports aligned with the page by exporting the same exact aggregates, while leaving `reports_course.php`, `reports_teacher.php`, their current threshold behaviour, schema, tracking, completion and AMD/player runtime unchanged for dedicated follow-up releases.

## 1.7.63 - 2026-08-17

- Fix the custom CSV export row writer so its static closure explicitly captures the module `$context` required by `csv_export::identity_values()`; without that capture the real download path can pass an undefined/null context despite the surrounding report controller having a valid module context.
- Add a static regression contract that isolates the `$writeeventrow` closure signature and requires `$context` to remain captured.
- Keep U-017 decomposition, report queries, export formats/content, tracking, completion, schema and AMD/player runtime otherwise unchanged; this is a corrective-only release before maintainability work resumes.

## 1.7.62 - 2026-08-17

- Correct the 1.7.61 U-017 extraction formatting reported by Moodle PHPCS: format the two multi-line reaction-cluster `foreach` statements according to PSR-12 and remove the extra blank line before the `report_support_test` class closing brace.
- Keep report-support behaviour, Analytics queries/exports, tracking, completion, schema and AMD/player runtime unchanged; this is a corrective-only release before U-017 continues.
- Rebase current release/documentation markers to 1.7.62 while Behat remains explicitly not run in the maintainer environment.

## 1.7.61 - 2026-08-17

- Advance U-017 maintainability with a third mechanically equivalent `report.php` decomposition slice: move report user-option assembly and standard reaction clustering into `local\report_support`.
- Preserve source-priority user filtering, email-visibility behaviour, cluster windows, unique-student counts, sort semantics and the configured cluster safety cap under behavioural PHPUnit coverage and a controller-delegation contract.
- Reduce the report request controller further without changing report queries, exports, tracking, completion, schema or AMD/player runtime; P2 remains blocked only on real browser/Behat evidence.

## 1.7.60 - 2026-08-17

- Advance U-017 maintainability with a second mechanically equivalent `report.php` decomposition slice: move report request/filter/scope helpers into the autoloaded `local\report_support` class.
- Keep representative helper behaviour covered for user labels, date parsing, structured duration filters, deny-all empty scopes and capability-dependent tabs, plus a static controller-delegation contract, while reducing the request controller from 3,360 to 3,038 lines.
- Keep Analytics rendering, exports, tracking, completion, schema and AMD/player runtime unchanged; P2 remains blocked only on real browser/Behat evidence.

## 1.7.59 - 2026-08-17

- Correct release-documentation freshness after 1.7.58: rebase the distributed file/function inventories and current documentation markers to the actual 1.7.59 tree.
- Keep runtime, Analytics presentation helper, AMD/player code, schema and U-017 scope unchanged; this is a corrective-only release.
- Preserve Behat as explicitly not run while the maintainer endpoint remains unavailable.

## 1.7.58 - 2026-08-17

- Correct the U-017 extraction naming introduced in 1.7.57: rename `local\report_renderer` to the neutral `local\report_view` helper so Moodle PHPCS no longer treats the static presentation helper as a renderer that must use `$this->output`.
- Keep Analytics markup, privacy semantics and controller delegation unchanged; only the helper/test names and references change.
- Wrap the two 133-character Analytics interval delegation lines reported by PHPCS.
- Keep AMD/player runtime unchanged and do not advance U-017 further in this corrective release.

## 1.7.57 - 2026-08-16

- Advance U-017 maintainability without changing Analytics data, privacy or query semantics: move the teacher Analytics presentation helpers out of the 4,143-line `report.php` request controller into the autoloaded `local\report_renderer` class.
- Keep heatmap, retention, reaction/bookmark/acknowledgement/integrity summaries and analytics-download markup behaviourally covered in the dedicated renderer while the controller delegates presentation work.
- Reduce `report.php` by roughly 800 lines and add behavioural/static regression coverage for privacy suppression, canonical timestamp formatting and controller-to-renderer delegation.
- Keep AMD/runtime player code unchanged; P2 remains blocked only on real browser/Behat evidence.

## 1.7.56 - 2026-08-16

- Advance the roadmap outside the temporarily blocked Behat/P2 browser gate by completing the code-side U-020 chapter-focus accessibility gap.
- Give VTT chapter buttons the same explicit keyboard `:focus`/`:focus-visible` treatment as the other VideoTrack controls, including dark-mode focus parity.
- Preserve chapter visibility in Windows/high-contrast forced-colour mode and keep the active chapter distinguishable with a system-colour outline.
- Add a static accessibility contract for chapter button semantics, accessible names, keyboard focus and forced-colour active state; no AMD source/build change is required.

## 1.7.55 - 2026-08-16

- Correct the remaining 1.7.54 completion-contract interpolation bug: the static marker now searches for the literal `$videotrack` argument instead of interpolating an undefined PHPUnit variable.
- Replace part of the fragile source-text-only acknowledgement coverage with behavioural PHPUnit checks: acknowledgement text/timing changes must alter the canonical completion signature, and `current_record()` must reject confirmations for superseded statement versions.
- Keep production PHP/AMD behaviour unchanged while Behat remains unavailable; this release strengthens server-side regression evidence only.

## 1.7.54 - 2026-08-16

- Correct the 1.7.53 non-browser resume/completion/alert contract itself without changing production runtime: use Moodle-compliant lower-case test variable names and real newline delimiters when slicing AMD source sections.
- Preserve the existing persistent-notice runtime contract after confirming the reported PHPUnit failure was caused by the test's literal `\n` search, not by `core/status.js`.
- Strengthen completion-configuration regression coverage so acknowledgement statement/version changes remain part of the canonical completion signature and only the current statement hash can satisfy acknowledgement completion.
- Keep Behat explicitly not run while the maintainer endpoint is unavailable; no browser/provider result is inferred from static or PHPUnit contracts.

## 1.7.53 - 2026-08-15

- Advance P2/U-007 without changing production AMD while the maintainer Behat endpoint is unavailable: add static contracts for automatic resume, completion synchronisation and stacked player notices across the three providers.
- Lock automatic resume to the server-validated watched frontier when forward seeking is disabled, and lock explicit report/replay targets ahead of stored resume positions for YouTube, HTML5 and Vimeo.
- Lock completion-changing learner mutations to Moodle completion synchronisation, keep VideoTrack completion events transition-only and prevent redundant Moodle completion writes on repeated heartbeats/interactions.
- Lock persistent resume/forward-seek notices so transient status messages cannot clear them; browser/Behat validation of these contracts remains pending.
- Correct the current documentation audit callable count and regenerate file/function inventories for the 1.7.53 tree.

## 1.7.52 - 2026-08-15

- Fix sub-second watched-percentage loss at playback boundaries without generic percentage rounding: a tiny initial instrumentation gap (up to 0.25 s) is normalised to zero, and only a validated natural `ended` segment may recover a bounded provider-tail discrepancy (up to 1.25 s).
- Apply the same boundary normalisation during `rebuild_state_from_segments()`, so completion recalculation repairs already stored full-watch evidence such as YouTube sessions that remained at 99.97%.
- Preserve raw `videotrack_seg` timestamps unchanged; the correction affects only aggregate watched coverage and therefore keeps the original evidence available for audit/debugging.
- Add PHPUnit coverage for live aggregation, historical recalculation, bounded start recovery and end-reason-specific tail recovery; production AMD remains unchanged.
- Fix the distributed `cli/validate.php` release-documentation check so it validates README and public `CHANGELOG.md` markers instead of requiring the maintainer-only roadmap/lesson-history file that is intentionally excluded from the product package.

## 1.7.51 - 2026-08-15

- Add a provider seek-snapshot PHPUnit contract covering YouTube, HTML5 and Vimeo without changing production AMD runtime.
- Lock YouTube/HTML5 blocked-forward-seek ordering so the trusted pre-seek position is snapshotted before rollback and the rollback destination is not credited as fresh playback.
- Lock Vimeo user-seek capture/valid-seek rotation and require all three providers to route reaction, note, bookmark and Forum timestamps through their rollback-safe interaction-time resolver.
- Keep the existing Behat HTML5 suite unchanged; the new static contracts complement browser coverage and do not claim the outstanding deterministic YouTube/Vimeo harnesses are complete.

## 1.7.50 - 2026-08-14

- Keep maintainer-only consolidated roadmap/lesson-history artifacts outside the distributed plugin tree; public release history remains in `CHANGELOG.md`, and `.moodleignore` prevents accidental packaging of those private artifacts.
- Extend the deterministic HTML5 post-rollback Behat scenario through the Forum composer, asserting that a blocked forward seek still opens the linked Forum at a server-acceptable watched timestamp.
- Add the test-only `behatlinkedforum` generator field so browser fixtures can resolve a same-course Forum by name without runtime-only configuration shortcuts.
- Remove the remaining PHPCS line-length warning in the custom Behat context by shortening the watched-evidence step expression.

This is the canonical release-history file for the plugin tree. Detailed design notes and historical migration documents remain under `docs/`.

## 1.7.49 - 2026-08-13

- Refined the learner page hierarchy: reaction buttons now start below the Reactions heading; Student notes, Student bookmarks and Forum posting have explicit H3 headings and contextual help.
- Removed the visual card container from learner acknowledgement while preserving its statement, status, confirmation controls and privacy hint in the normal page flow.
- Extended the deterministic HTML5 Behat harness with watched-evidence seeding and a post-rollback regression for reaction, note and bookmark saves after a blocked forward seek.
- Fixed the remaining PHPCS class-closing whitespace issue in `tests/generator_test.php`.
- Updated all eight maintained language packs and EN/IT release/runtime/Behat documentation.

## 1.7.48 - 2026-08-13

### Changed

- Render the optional Forum action before the acknowledgement box on the learner page, preserving the established interaction order while keeping acknowledgement as the final learner confirmation block.
- Advance P2/U-007 with a deterministic local HTML5 Behat seek harness: the module generator can now upload a bundled 60-second fixture and browser steps can drive/assert media timestamps without public-network dependencies.
- Add browser scenarios for blocked and allowed HTML5 forward seeks; blocked seek returns to the watched frontier while allowed seek remains at the requested timestamp.

### Tests

- Extend the generator PHPUnit coverage to verify the local HTML5 fixture is stored in the module `videocontent` filearea.
- Extend the learner static order contract so the Forum action must precede the acknowledgement section.

## 1.7.47 - 2026-08-13

### Changed

- Reorder the learner page into one stable vertical flow: completion conditions → notices → player/watched bar → progress summary → reaction controls/history → note composer/history → bookmark composer/history → optional acknowledgement/transcript → Forum action.
- Keep the configured player width but remove the landscape two-column split so the interaction order remains visually stable on phones and tablets in landscape.
- Render Moodle automatic-completion condition badges without the rounded boxed treatment while preserving readable wrapping and contrast.
- Move the progress summary immediately below the watched-interval bar and move the optional Forum action to the end of the learner interaction flow.
- Extend learner Behat/static contracts so the watched bar, progress summary, reaction controls, personal histories and Forum action cannot silently drift out of order.

### Fixed

- Fix the PHPCS multi-line condition reported in `tests/generator/lib.php` on the 1.7.46 tree.
- Replace the brittle learner-order contract marker that caused the single PHPUnit failure reported against 1.7.46.

## 1.7.46 - 2026-08-13

- Learner page: note and bookmark composers remain always visible; only saved reaction/note/bookmark history is collapsed.
- Learner page order is now reaction controls → My reactions → note composer → My notes → bookmark composer → My bookmarks.
- Behat generator now seeds one deterministic reaction when reactions are enabled; browser contract verifies active controls and DOM order.
- Fix PHPCS findings in the learner-view contract test and test generator.

## 1.7.45 - 2026-08-13

### Added

- Start roadmap phase P2/U-007 with a Moodle-native `mod_videotrack_generator` and the first `@mod_videotrack` Behat browser scenario.
- Add English and Italian Behat setup/run documentation and an exact-tree browser release gate.
- Add PHPUnit contracts for the new test generator and native learner-section markup.

### Changed

- Render **My reactions**, **My notes** and **My bookmarks** as independent native `<details>/<summary>` sections, collapsed by default to reduce distraction and vertical scrolling while preserving the existing list IDs and interaction handlers.
- Keep the learner UI collapsible contract independent of VideoTrack AMD so keyboard/browser-native disclosure remains available even if JavaScript fails.
- Fix the 12 Moodle Extra coding-style errors reported against 1.7.44: CLI list/control-structure formatting and the final newline in `tests/report_contract_test.php`.
- Mark U-007 as in progress: browser infrastructure now exists, while the role/provider/seek/interaction matrix and pre-seek/rollback regressions remain open.

## 1.7.44 - 2026-08-13

### Added

- Ship `cli/validate.php`, a read-only installation/release diagnostic covering installed/file version alignment, Moodle support range, XMLDB tables/fields/indexes, AJAX external methods, maintained language contracts, AMD src/build/source-map parity, current documentation markers and selected critical configuration.
- Ship `cli/benchmark_course_analytics.php`, the read-only four-scenario Course Analytics benchmark used for the real-dataset U-016 performance gate, with configurable course/viewer/activity/group/privacy threshold/run count/period.
- Add English and Italian administrator/maintainer documentation for both CLI tools, including safety model, exit semantics, interpretation and the recorded real-course benchmark baseline.
- Add static CLI contracts that prevent accidental write operations and keep the tools tied to the real batched `course_analytics` path.

### Changed

- Mark the real-dataset U-016 benchmark gate complete based on the maintainer run with 40 configured activities: 86 median reads/queries for the all-activity scenarios, 37.913 ms median all-time wall time and 46.645 ms median seven-day wall time; not all activities contained learner logs, so this is a production-like baseline rather than a synthetic worst-case claim.
- Remove the eight recurring Moodle Extra warnings caused by literal backticks inside `release_hygiene_contract_test.php` strings while preserving the exact documentation-marker assertions.
- Advance the roadmap: P1 CLI diagnostics is implemented in-tree; P2 browser automation/U-007 is the next engineering phase.

## 1.7.43 - 2026-08-12

### Fixed

- Explain directly inside the “Retention along the timeline” SVG when the full retention series is hidden by the configured privacy threshold, instead of leaving a graph with axes but no line or points.
- Include the same privacy explanation in the SVG accessible description without changing Analytics calculations, thresholds or masked values.
- Fix the PHPCS close-brace formatting error in `tests/save_bookmark_test.php` reported against 1.7.42.
- Add the retention privacy message to all eight maintained language packs and extend the report contract test.

## 1.7.42 - 2026-08-12

### Fixed

- Allow a private bookmark after seeking backward to a timestamp already covered by server-validated watched progress, while continuing to reject genuinely unwatched positions.
- Keep the existing allowed-forward-seek fallback for newly reached bookmark timestamps; historical watched evidence is checked first so backward navigation does not depend on the current session policy.
- Render transient player information and error alerts with the same compact in-flow flex close control already used by resume, seek-policy and provider/privacy notices; remove Bootstrap `alert-dismissible` absolute positioning from these status alerts.
- Remove the two known trailing-whitespace lines from the consolidated changelog/lessons/roadmap document.

## 1.7.41 - 2026-08-12

### Changed

- Merge the Activity completion labels “Reaction logic” and “Reaction completion” into one visible heading, “Completion via reactions and viewed percentage”, while preserving the existing AND/OR selector and explanatory guidance.
- Complete the maintained German, Spanish, French, Hindi, Polish and Portuguese packs with the nine completion/seek strings that were already present in English and Italian; all eight packs now expose the same 977-key and placeholder contract.
- Refresh the current technical-documentation baseline, distributed-file/function/configuration inventories and documentation audit for the 1.7.41 tree.
- Add `VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_1.7.5_1.7.41.md` as the consolidated pre-production history, lessons-learned and future-roadmap document.
- Add regression contracts for the single completion heading and language-pack key/placeholder parity.
- No player, tracking, seek, Forum, reaction-write, note-write, bookmark-write, Analytics, database schema or upgrade behaviour changed in this release.

## 1.7.40 - 2026-08-12

### Fixed

- Render the combined external-provider privacy and learner integrity/focus notice with VideoTrack's compact in-flow flex alert instead of Moodle's dismissible notification wrapper.
- Keep the close button small, vertically centred and inside the alert flow so it cannot overlap adjacent notices.
- No player, tracking, seek, Forum, reaction, note, bookmark, completion, Analytics or database behaviour changed in this release.

## 1.7.39 - 2026-08-11

### Fixed

- Keep the automatic-resume notice compact by removing Bootstrap's `alert-dismissible` positioning from its close control, so the button stays aligned inside the notice and no longer overlaps adjacent alerts.
- Show learners a persistent, dismissible policy alert whenever forward seeking is disabled for the activity.
- When the configured blocked-seek recovery rate differs from 1x, include that configured rate in the persistent policy alert; the existing blocked-seek runtime alert continues to report the actual recovery rate used for each blocked attempt.
- Update the blocked-forward-seek contract test to match the 1.7.38 pre-seek snapshot semantics for YouTube, Vimeo and HTML5 without changing the server-authoritative tracking logic.

## 1.7.38 - 2026-08-11

### Fixed

- Restore the proven Vimeo seek-segmentation contract in YouTube and HTML5: a seek now persists viewing only up to the pre-seek position and opens a new segment at the destination, never crediting the skipped gap as continuous playback.
- Close HTML5 programmatic rewind, fast-forward, replay, chapter and resume segments before changing `currentTime`, preventing pause/interaction flushes from being rejected by the server-authoritative ledger after a seek.
- Preserve the original wall-clock start also for known-boundary seek snapshots.
- While a blocked forward seek is rolling back, use the last trusted player time for Forum, reaction, note and bookmark flushes instead of the transient forbidden provider timestamp.
- Keep the 1.6.23 server-authoritative anti-tampering ledger and the 1.7.12/1.7.32 interaction guards intact; this release fixes the player-side segment boundary that those guards exposed.

## 1.7.37 - 2026-08-11

### Fixed

- Clamp automatic resume to the server-validated watched frontier whenever forward seeking is disabled, preventing stale browser resume positions from reopening playback beyond authorised progress.
- Preserve the original segment wall-clock start when pause, seek or end closes a segment before the AJAX payload is built.
- Restore Forum, note and bookmark timestamp writes after normal viewing by preventing invalid resume positions from causing every subsequent segment flush to be rejected as suspicious.
- Fix PSR-12 formatting in the Forum timestamp guard and update root release documentation to the current plugin release.

## 1.7.36 - 2026-08-11

### Fixed

- Pass the current browser playback session into the Forum composer and apply the same policy-aware timestamp validation already used by note, bookmark and reaction writes, avoiding false `error:playbackpositionnotwatched` failures immediately after an allowed forward seek.
- Remove the duplicate visible close glyph from temporary Bootstrap status alerts so their dismiss controls no longer overlap.
- Keep the visible watched percentage monotonic across successful interaction flushes and rewind actions while still allowing an explicitly rejected segment response to restore the server-authoritative percentage.
- Show learners an informational alert when forward seeking is blocked by the teacher policy; when blocked-seek recovery continues at a rate other than 1x, include that playback rate in the same alert.

## 1.7.35 - 2026-08-11

### Fixed

- Restored readable contrast for Moodle automatic-completion badges on VideoTrack pages when a theme remaps Bootstrap light/dark utility colours.
- Allowed long completion descriptions to wrap inside the Moodle activity header and added a subtle border so incomplete conditions remain visually distinct.
- No completion logic, player, Analytics, database or upgrade behaviour changed in this release.

## 1.7.34 - 2026-08-11

### Changed

- Consolidated the external-provider privacy notice and learner integrity/focus notice into a single informational alert above the player.
- Kept the existing translated notice text and visibility conditions unchanged; only the presentation container was unified.
- No player, tracking, completion, Analytics, database or upgrade behaviour changed in this release.

## 1.7.33 - 2026-08-11

### Fixed

- Fixed PSR12 formatting in the interaction timestamp guard without changing its runtime behaviour.
- Isolated the gradebook duplicate-repair regression test from the runtime grading API by building its grade fixtures with DML only, removing the test-generated PHPUnit notice while preserving the repair scenario.
- No player, Analytics, completion, database schema or upgrade behaviour changed in this release.

## 1.7.32 - 2026-08-11

### Fixed

- Restore `completionlogic = or` as a true alternative across enabled VideoTrack completion conditions, so a configured reaction requirement can complete the activity instead of the viewing-percentage requirement when OR is selected.
- Keep the requested “Reaction logic” / “Logica delle reazioni” label while clarifying in the form guidance that OR makes the reaction requirement alternative to other enabled VideoTrack conditions such as viewed percentage or acknowledgement.
- Avoid false `error:playbackpositionnotwatched` responses immediately after a permitted forward seek: interaction writes now accept a newly reached timestamp only when forward seeking is enabled and recent server-side playback evidence exists for the same browser session.
- Add completion and interaction-policy regression coverage without changing player/provider seek state machines.

## 1.7.31 - 2026-08-11

### Fixed

- Stop creating a VideoTrack grade item from the module-specific restore `after_execute()` hook; Moodle's common activity-grade restore step already restores `grades.xml`, and the former double creation could leave duplicate canonical grade items after activity duplication.
- Add a DML-only 1.7.31 upgrade repair that keeps the newest canonical itemnumber-0 grade item per VideoTrack instance, migrates non-conflicting `grade_grades` rows from older duplicates, and removes stale/duplicate grade items so already-duplicated activities become editable again.
- Add restore/gradebook regression coverage and fix the remaining PHPCS class-closing layout in the completion contract test.

## 1.7.30 - 2026-08-11

### Fixed

- Fix Moodle 5.0 completion-detail rendering by listing the composite VideoTrack rule together with all standard automatic conditions in `custom_completion::get_sort_order()`.
- Scope `completionlogic` to reaction criteria only, so reaction OR logic cannot bypass an enabled viewing-percentage or acknowledgement requirement.
- Rename the completion-form selector to “Reaction logic” / “Logica delle reazioni” and add explicit guidance for the common “at least one reaction of any available type” configuration.
- Fix three PHPUnit warnings in the completion contract caused by unintended `$suffix` interpolation in assertion strings.

## 1.7.29 - 2026-08-11

- Fix Moodle 5.0 completion form suffix handling by using `get_suffix()` and explicit field-name concatenation.
- Fix PSR-12 formatting in the acknowledgement completion refresh path.
- Add regression coverage preventing the unsupported `get_suffixed_name()` call from returning.

## 1.7.28 - 2026-08-11

### Fixed

- Uses Moodle 4.3+/5.0 suffixed custom-completion form element names for completion-form and bulk/default-form compatibility.
- Aligns reaction-based completion with Moodle 5.0 custom completion by publishing one composite `videotrackconditions` rule through `cm_info`.
- Uses the same `tracker::completion_satisfied()` decision for Moodle custom completion and VideoTrack runtime state, preserving configured AND/OR semantics including individually required reactions.
- Makes individually required reactions activate automatic completion validation, while disabled reactions no longer leave stale reaction requirements blocking completion.
- Shows reaction-completion integration in the standard Activity completion section and moves the global AND/OR selector there; reaction-specific controls remain in the Reactions section.
- Locks reaction completion controls when the editor lacks `mod/videotrack:overridecompletionsettings`.
- Recalculates tracked learner states when duration or completion configuration changes, including acknowledgement statement/version and reaction-definition requirements; normal Moodle edits leave the final native completion reset to core after `update_instance()`.
- Centralises completion-rule activation and descriptions so runtime, `cm_info`, privacy/retention and restore use the same contract, excluding disabled/empty acknowledgement definitions from completion.
- Preloads the set of activities with individually required reactions once per course/request while rebuilding `cm_info`, avoiding a per-activity reaction lookup.

## 1.7.27 - 2026-08-10

- Stabilizes the U-016 phase-2 test contracts without changing Analytics runtime code.
- Aligns the period-loader contract with the aliased `seg.servervalidated` and `seg.timecreated` SQL used by batched segment reads.
- Fixes the PHPCS class-closing layout in `analytics_performance_contract_test.php`.

## 1.7.26 - 2026-08-10

### Changed

- Continued U-016 by batching all-time state reads and period segment reads across up to 20 activity scopes per query.
- Joined each period segment batch to the unique `(videotrackid, userid)` state row so current completion flags travel with active-period segments instead of requiring a second per-activity or course-wide state pass.
- Removed the remaining per-activity database reads from the explicit course Analytics aggregation loop while preserving independently capability-filtered learner SQL for every activity.
- Updated the code-level explicit query model: both all-time and period course rows now use `2 + 2*ceil(N/20)` explicit reads, down from 1.7.24's `2 + N + ceil(N/20)` all-time and `2 + 2N + ceil(N/20)` period shapes, excluding Moodle capability/group helper internals. For 20 activities this is 4 reads instead of 23/43; for 100 activities it is 12 instead of 107/207.
- Added regression coverage preventing state and segment loaders from reverting to one query per activity or a separate completion-state query.

## 1.7.25 - 2026-08-10

### Fixed

- Stabilised the release-hygiene contract to use Moodle-compliant `require(...)` syntax.
- Updated the learner-scope delegation contract to accept the parameterised batching call introduced by 1.7.24 without weakening the canonical-helper assertion.
- No Analytics runtime, database, player or AMD behaviour changed in this release.

## 1.7.24 - 2026-08-10

### Changed

- Started Analytics performance work for U-016 with a lightweight teacher activity-select path based on modinfo and report capability checks instead of full dashboard aggregation.
- Preloaded course-level group existence once for course Analytics and reused it while resolving per-activity learner scopes.
- Batched reaction, note and bookmark counts across up to 20 activity scopes per aggregate query instead of issuing three event queries for every activity.
- Documented the code-level explicit query model, excluding Moodle capability/group helper internals: all-time course rows move from `1 + 4N` reads to `2 + N + ceil(N/20)`; period rows move from `1 + 5N` to `2 + 2N + ceil(N/20)`. A selected-course teacher dashboard also removes the former extra `1 + 4N` aggregation used only to populate its activity select.
- Made the release-hygiene contract derive the current release from `version.php` and removed the PHPCS-warning backticks from its assertion strings.

## 1.7.23 - 2026-08-10

### Changed

- Added explicit Moodle formatting contexts to CSV export labels and course/activity values.
- Corrected the Italian `environment.xml` warning to native UTF-8 spelling and accents.
- Introduced this root changelog and reduced the README files to current product, installation and maintenance information.
- Re-audited EN/IT privacy-document structure and maintained-language findings; no additional privacy-content change was required.

## 1.7.22 - 2026-08-10

### Fixed

- Simplified Vimeo blocked-forward-seek recovery to one rollback followed by playback-only resume retries.
- Prevented transient Vimeo pause events during rollback from cancelling the playback-start handshake.
- Cleared forward-seek guard state immediately after a successful rollback, eliminating accumulated rewind loops across repeated seeks.

## 1.7.15 - 1.7.21

### Changed

- Made YouTube/Vimeo provider loading retry-safe and removed global `window.define` manipulation.
- Iterated on Vimeo blocked-seek recovery based on browser runtime evidence; superseded by the simplified 1.7.22 state machine.

## 1.7.12 - 1.7.14

### Changed

- Centralised learner participation semantics for UI and Web Services.
- Preserved the learner's own grade for dual-role users.
- Required learner Forum timestamps to reference server-validated watched positions while retaining an explicit report-viewer bypass.
- Flushed learner progress before opening the Forum composer so immediate post-seek actions validate against the current watched frontier.

## 1.7.7 - 1.7.11

### Changed

- Brought Analytics/export summaries and learner scope into parity.
- Added aggregate bookmark export without exposing private labels or timestamps.
- Hardened telemetry playback-speed validation and interaction-segment persistence.
- Fixed note/bookmark timestamps and server-frontier persistence after blocked forward seeks.

## 1.7.0 - 1.7.6

### Changed

- Reset the pre-production release/version baseline and stabilised the current schema/runtime branch.
- Completed temporal Analytics corrections, report parity and related regression coverage.

## 1.6.24 - 1.6.36

### Changed

- Reworked pre-production upgrade recovery to remain database-only and idempotent.
- Introduced the server-authoritative playback ledger and idempotent write identifiers.
- Moved GDPR retention to deletion-based expiry and state reconstruction from retained evidence.
- Repaired gradebook upgrade handling and protected modern schemas from replay of obsolete legacy migrations.

For older detailed implementation history, see the numbered documents under `docs/en/`, `docs/it/` and their historical archives.
