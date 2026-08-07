# VideoTrack for Moodle

VideoTrack is a Moodle activity module for delivering and tracking HTML5/uploaded, YouTube and Vimeo videos. It combines privacy-aware viewing analytics with optional study tools, completion rules and teacher reporting.

Current release documented by this tree: **1.6.26**. Supported Moodle branches: **5.0–5.3**.

Italian overview: [`README_IT.md`](README_IT.md)
Privacy summary: [`PRIVACY.md`](PRIVACY.md) / [`PRIVACY_IT.md`](PRIVACY_IT.md)
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

## Installation

1. Place the directory at `mod/videotrack`.
2. Visit **Site administration → Notifications** or run the Moodle CLI upgrade.
3. Review VideoTrack site settings before enabling retention, CSV identity fields or focus controls.
4. Create a VideoTrack activity and configure the video source and only the features required for the teaching scenario.

No source code files should be edited inside Moodle after installation. Use a reviewed release or patch and keep `amd/src` and `amd/build` in sync.

### Playback-tool reliability correction in 1.6.26

Release 1.6.26 keeps reaction buttons available while the player is playing or paused, while the server still requires a validated watched timestamp. Enabled personal notes and bookmarks are rendered consistently; report-capable staff see a disabled preview and cannot create learner telemetry. The complete HTML5-controls section is hidden for YouTube and Vimeo sources. The verified-duration field defaults to `0`, explicitly meaning that watched percentage, percentage completion and acknowledgement after the final second are not used; validated watched intervals continue to be collected.

### Interface and Analytics correction in 1.6.25

Release 1.6.25 fixes the acknowledgement summary in Analytics, keeps all reaction definitions inside the main Reactions section, restores the reaction controls above Forum posting and bookmarks, and preserves a variable reaction set through the existing **Add reaction** control (up to the established safety cap of 30). Report-capable staff can see a disabled preview of reaction controls, while only canonical learners may record reactions.

### Upgrade recovery in 1.6.24

Release 1.6.24 replaces the failed 1.6.23 completion-recalculation step with an idempotent database-only recovery. Because this project had not been used in production, the one-time upgrade removes existing learner runtime data and VideoTrack course-module completion rows while preserving activity configuration and configured reactions. See [`docs/en/15_UPGRADE_RECOVERY_1_6_24.md`](docs/en/15_UPGRADE_RECOVERY_1_6_24.md).

## Validation baseline

The distributed tree is intended to be checked with:

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack
npx grunt amd --root=mod/videotrack
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
