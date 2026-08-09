# VideoTrack for Moodle

VideoTrack is a Moodle activity module for delivering and tracking HTML5/uploaded, YouTube and Vimeo videos. It combines privacy-aware viewing analytics with optional study tools, completion rules and teacher reporting.

Current release documented by this tree: **1.6.36**. Supported Moodle branches: **5.0–5.3**.

Italian overview: [`README_IT.md`](README_IT.md)
Privacy summary: [`PRIVACY.md`](PRIVACY.md) / [`PRIVACY_IT.md`](PRIVACY_IT.md)

### Pre-production upgrade baseline recovery in 1.6.36

Release 1.6.36 detects a complete modern 1.6.x database schema before replaying historical upgrade steps. If the current ledger tables and fields are already present but the stored plugin version is unexpectedly old after interrupted development upgrades, VideoTrack fast-forwards the logical upgrade baseline to 1.6.32. This prevents obsolete 1.4.x/1.5.x migrations from touching tables that no longer belong to the current schema. The recovery is intentionally limited to this pre-production development history and does not fabricate missing modern tables.

### Gradebook upgrade recovery in 1.6.36

Release 1.6.36 supersedes the failed 1.6.34 gradebook upgrade step. During upgrade it uses Moodle DML only to remove pre-production VideoTrack grade items and their current grade rows; it does not call runtime gradebook APIs while the upgrade is running. Existing graded VideoTrack activities recreate one canonical Moodle grade item (`itemnumber = 0`) the next time the activity is saved. This intentionally discards pre-production VideoTrack grade values and removes ambiguous duplicate grade items.

### Deletion-based GDPR retention in 1.6.33

Release 1.6.33 removes the former deterministic negative-user pseudonyms. Expired playback segments, interactions, notes, bookmarks, integrity signals and acknowledgements are deleted; `videotrack_state` is rebuilt from the retained server-validated evidence and current completion inputs. State and Moodle custom completion can therefore return to incomplete when their evidence expires. User-data backups exclude expired and legacy pseudonymous rows, do not carry derived state, and apply the destination retention policy again on restore before rebuilding state after Moodle completion has been restored. See [`docs/en/20_GDPR_RETENTION_1_6_33.md`](docs/en/20_GDPR_RETENTION_1_6_33.md).

### Server-authoritative playback ledger in 1.6.32

Release 1.6.32 requires a zero-credit playback handshake before tracked segments can earn progress. Every handshake and segment receives a persistent idempotency identifier, so a lost response followed by the shared retry path cannot duplicate raw rows, events or Analytics. Credit is earned only from cumulative server elapsed time at an allowed playback rate, compact interval storage no longer reduces exact unique coverage, and the new schema/backup/privacy contracts are documented in [`docs/en/19_TRACKING_LEDGER_1_6_32.md`](docs/en/19_TRACKING_LEDGER_1_6_32.md).

### Runtime-contract and Analytics privacy hotfix in 1.6.31

Release 1.6.31 restores integrity-indicator delivery by aligning the client AJAX allowlist with the declared Moodle services. It also prevents inferential disclosure when timeline bins are privacy-masked, removes the dormant raw-HTML reaction field, validates sesskeys before database/context loading, and keeps all generated AMD files aligned with their sources. Releases 1.6.25–1.6.31 are code-only with respect to XMLDB and therefore do not add no-op upgrade savepoints.

### Bookmark persistence correction in 1.6.30

Release 1.6.30 aligns the server segment-end-reason whitelist with the shared player contract. The `bookmark` reason is now accepted by `mod_videotrack_save_segment`, so the current watched interval is persisted before a private bookmark is validated. The watched-position check remains unchanged: bookmarks can only be created at server-validated video positions.

Technical documentation: [`docs/en/00_INDEX.md`](docs/en/00_INDEX.md) / [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md)

## Main capabilities

- HTML5/uploaded video, YouTube and Vimeo playback.
- Server-validated watched-segment tracking and unique-viewed-time calculation.
- Resume playback, forward/backward seek policies, playback-rate limits and accessible keyboard controls.
- Completion rules based on viewed percentage, required reactions and an optional current acknowledgement.
- Configurable reactions, personal timestamped notes and private bookmarks.
- Searchable WebVTT transcripts and chapter navigation supplied by the teacher.
- Optional timestamped Forum composer that publishes through Moodle Forum.
- Optional focus/integrity controls: hidden-tab pause, best-effort Picture-in-Picture prevention, random attention pauses and bounded diagnostic signals.
- Optional versioned learner acknowledgement, either at any time or only after the final video second.
- Per-student reports, course dashboards, cross-course same-video analytics and CSV/Excel/ODS exports.
- Gradebook integration, custom completion, Moodle events, backup/restore, Privacy API and scheduled retention.
- Eight maintained language packs: German, English, Spanish, French, Hindi, Italian, Polish and Portuguese.

## Privacy and accessibility principles

VideoTrack records only the data required by enabled features. Bookmark labels remain visible only to their owner. Personal note text is visible to its owner and may be viewed/exported by authorised report viewers; note text is excluded from aggregate Analytics. Teacher analytics use aggregates and apply the configured minimum-user threshold. Integrity signals are diagnostic, not proof of misconduct, and must not be used as the sole basis for grading or disciplinary action.

The default focus policy pauses only when the video page is genuinely hidden. Window-focus loss is treated more cautiously to reduce false positives for screen readers, password managers, browser chrome and operating-system dialogs. Player controls, status regions, transcript navigation and poster actions are designed for keyboard and assistive-technology use. Browser and external-provider limitations are documented rather than presented as guarantees.

### Explicit learner participation in 1.6.29

Release 1.6.29 separates learner participation from report access. Tracking, reactions, notes, bookmarks, integrity signals and acknowledgements now require `mod/videotrack:participate`, which is granted to the standard Student archetype and cloned from Moodle’s completion-report participant capability. A user may therefore participate even when a custom or dual role also grants report access. Ordinary teachers, managers and site administrators remain in non-tracking preview mode unless they switch to, or are explicitly granted, a participating role. Analytics and learner reports use the same participation capability, so UI state, Web Services and report populations share one contract.

## Installation

1. Place the directory at `mod/videotrack`.
2. Visit **Site administration → Notifications** or run the Moodle CLI upgrade.
3. Review VideoTrack site settings before enabling retention, CSV identity fields or focus controls.
4. Create a VideoTrack activity and configure the video source and only the features required for the teaching scenario.

No source code files should be edited inside Moodle after installation. Use a reviewed release or patch and keep `amd/src` and `amd/build` in sync.

### Duration configuration transport correction in 1.6.28

Release 1.6.28 keeps the complete localised detector configuration in a JSON element in the activity-form DOM. `js_call_amd()` receives only the short configuration element identifier, avoiding Moodle's developer warning for payloads longer than 1024 characters. The detector behaviour, teacher-editable value and server-authoritative persistence contract are unchanged.

### Automatic duration suggestion in 1.6.27

When the teacher enters a supported YouTube or Vimeo URL, or selects a local media file, VideoTrack attempts to read the duration from the metadata exposed by that source and pre-fills the duration field. The form announces the result accessibly and the proposed value remains editable before saving and on later edits. Detection is best effort: provider restrictions, content privacy, browser policy or unavailable local metadata can prevent it, in which case the teacher can enter the value manually or leave `0`. The detected value becomes authoritative only after the teacher saves the activity; learner-player metadata can never overwrite it.

### Playback-tool reliability correction in 1.6.26

Release 1.6.26 keeps reaction buttons available while the player is playing or paused, while the server still requires a validated watched timestamp. Enabled personal notes and bookmarks are rendered consistently; users without the explicit participation capability see a disabled preview and cannot create learner telemetry. The complete HTML5-controls section is hidden for YouTube and Vimeo sources. The verified-duration field defaults to `0`, explicitly meaning that watched percentage, percentage completion and acknowledgement after the final second are not used; validated watched intervals continue to be collected.

### Interface and Analytics correction in 1.6.25

Release 1.6.25 fixes the acknowledgement summary in Analytics, keeps all reaction definitions inside the main Reactions section, restores the reaction controls above Forum posting and bookmarks, and preserves a variable reaction set through the existing **Add reaction** control (up to the established safety cap of 30). Users without the explicit participation capability see a disabled preview of reaction controls, while participants may record reactions even when a separate role also grants report access.

### Upgrade recovery in 1.6.24

Release 1.6.24 replaces the failed 1.6.23 completion-recalculation step with an idempotent database-only recovery. Because this project had not been used in production, the one-time upgrade removes existing learner runtime data and VideoTrack course-module completion rows while preserving activity configuration and configured reactions. See [`docs/en/15_UPGRADE_RECOVERY_1_6_24.md`](docs/en/15_UPGRADE_RECOVERY_1_6_24.md).

## Validation baseline

The distributed tree is intended to be checked with:

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra mod/videotrack
node node_modules/grunt/bin/grunt amd --root=mod/videotrack
```

Exact commands depend on the Moodle checkout and installed development dependencies. See [`docs/en/07_BUILD_TEST_RELEASE.md`](docs/en/07_BUILD_TEST_RELEASE.md).

## Maintenance contract

- Start every change from the latest real plugin archive.
- Audit the actual runtime path before changing player or Web Service behaviour.
- Treat HTML5, YouTube and Vimeo as separate adapters with a shared contract.
- Validate PHP, XMLDB, language placeholders, JavaScript sources/builds, Privacy API, backup/restore and documentation.
- When `amd/src/*` changes, run the real Moodle AMD build and distribute the resulting minified files and source maps.
- Generate patches from the plugin root and verify both `git apply --check` and `patch -p1 --dry-run`.

The numbered documentation set is the current source of truth. Files under `docs/*/archive/` are historical records only.

## Licence

GNU GPL v3 or later, consistently with Moodle.
