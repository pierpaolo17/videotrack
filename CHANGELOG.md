# VideoTrack changelog

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
