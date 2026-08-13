# VideoTrack for Moodle

VideoTrack is a Moodle activity module for delivering and tracking HTML5/uploaded, YouTube and Vimeo videos. It combines server-validated viewing progress with optional study tools, completion rules, gradebook integration and privacy-aware teacher reporting.

Current release documented by this tree: **1.7.45**. Supported Moodle branches declared by the plugin: **5.0–5.3**.

Italian overview: [`README_IT.md`](README_IT.md)
Release history: [`CHANGELOG.md`](CHANGELOG.md)
Consolidated 1.7.5–1.7.45 history/lessons/roadmap: [`VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_1.7.5_1.7.45.md`](VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_1.7.5_1.7.45.md)
Privacy summary: [`PRIVACY.md`](PRIVACY.md) / [`PRIVACY_IT.md`](PRIVACY_IT.md)
Technical documentation: [`docs/en/00_INDEX.md`](docs/en/00_INDEX.md) / [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md)

## Main capabilities

- HTML5/uploaded video, YouTube and Vimeo playback.
- Server-validated watched-segment tracking and unique-viewed-time calculation.
- Resume playback, forward/backward seek policies, playback-rate limits and accessible keyboard controls.
- Completion rules based on viewed percentage, required reactions and optional acknowledgement.
- Configurable reactions, personal timestamped notes and private bookmarks.
- Searchable WebVTT transcripts and chapter navigation supplied by the teacher.
- Optional timestamped Forum composer that publishes through Moodle Forum.
- Optional focus/integrity controls with bounded diagnostic signals.
- Per-student reports, course dashboards, cross-course same-video analytics and CSV/Excel/ODS exports.
- Gradebook integration, custom completion, Moodle events, backup/restore, Privacy API and scheduled retention.
- Eight maintained language packs: German, English, Spanish, French, Hindi, Italian, Polish and Portuguese.
- Read-only local CLI validation and Course Analytics benchmarking for administrators and maintainers.

## Participation, privacy and accessibility

Learner participation is controlled by `mod/videotrack:participate` and is independent from report access. A user with a dual/custom role can therefore remain a tracked learner while also holding reporting capabilities. Teachers, managers and administrators remain in non-tracking preview mode unless they also receive the participation capability.

VideoTrack records only data required by enabled features. Bookmark labels remain private to their owner. Personal note text is visible to its owner and may be viewed/exported by authorised report viewers; aggregate Analytics exclude note text. Analytics apply the configured minimum-user privacy threshold. Integrity signals are diagnostic and must not be treated as proof of misconduct.

The default focus policy pauses only when the video page is genuinely hidden. Player controls, status regions, transcript navigation and poster actions are designed for keyboard and assistive-technology use. External-provider and browser limitations are documented as best-effort behaviour rather than guarantees.

## Installation

1. Place the directory at `mod/videotrack`.
2. Visit **Site administration → Notifications** or run the Moodle CLI upgrade.
3. Review VideoTrack site settings before enabling retention, CSV identity fields or focus controls.
4. Create a VideoTrack activity and enable only the features required for the teaching scenario.

Do not edit installed source files directly. Use a reviewed release or patch and keep `amd/src` and `amd/build` in sync.

## Local CLI diagnostics

VideoTrack ships read-only CLI tools for administrators and maintainers. From the Moodle root, use `php mod/videotrack/cli/validate.php --json` for installation/release diagnostics and `php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>` for the course Analytics benchmark. Full options and interpretation are documented in [`docs/en/21_CLI_DIAGNOSTICS.md`](docs/en/21_CLI_DIAGNOSTICS.md).

## Validation baseline

A release should be checked with the Moodle toolchain available for the target branch, including PHPUnit, Moodle Coding Style/Extra checks and AMD generation when JavaScript sources change. Typical commands are documented in [`docs/en/07_BUILD_TEST_RELEASE.md`](docs/en/07_BUILD_TEST_RELEASE.md).

Core release rules:

- start from the latest real plugin archive;
- reconstruct the actual runtime path before changing player or Web Service behaviour;
- treat HTML5, YouTube and Vimeo as distinct adapters behind a shared contract;
- validate PHP, XML, language placeholders, Privacy API, backup/restore and documentation;
- when `amd/src/*` changes, run real Moodle `grunt amd` and ship the generated minified files and source maps;
- verify patches with both `git apply --check` and `patch -p1 --dry-run`.

## License

GNU GPL v3 or later, consistently with Moodle.

Learner personal reactions, notes and bookmarks are presented as compact native collapsible sections; browser automation starts under `tests/behat/`.
