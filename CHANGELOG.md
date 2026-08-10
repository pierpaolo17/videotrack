# VideoTrack changelog

This is the canonical release-history file for the plugin tree. Detailed design notes and historical migration documents remain under `docs/`.

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
