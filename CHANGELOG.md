# VideoTrack changelog

This is the canonical release-history file for the plugin tree. Detailed design notes and historical migration documents remain under `docs/`.

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
