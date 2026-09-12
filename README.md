# VideoTrack for Moodle

[![Moodle Plugin CI](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml/badge.svg)](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml)

VideoTrack is a Moodle activity module for HTML5/uploaded, YouTube and Vimeo video. It combines
server-validated watched progress, study interactions, custom completion, gradebook integration and
privacy-aware reporting.

Current release documented by this tree: **1.7.134**. Supported Moodle branches: **5.0–5.3**.

- Italian overview: [`README_IT.md`](README_IT.md)
- Complete English guide: [`docs/en/funzionalita.md`](docs/en/funzionalita.md)
- Technical index: [`docs/en/00_INDEX.md`](docs/en/00_INDEX.md)
- Privacy summary: [`PRIVACY.md`](PRIVACY.md)
- Database/ER reference: [`docs/VIDEOTRACK_DB_ER_SCHEMA.md`](docs/VIDEOTRACK_DB_ER_SCHEMA.md)
- Release history: [`CHANGELOG.md`](CHANGELOG.md)

## Feature map

| Area | Available functionality |
|---|---|
| Media | Uploaded/HTML5 video, YouTube and Vimeo; form-only duration suggestion with `HH:MM:SS` equivalent; poster; optional uploaded-file download. |
| Player | Autoplay, loop, initial mute, responsive width, configurable controls, keyboard/fullscreen policy, rewind/forward steps. |
| Navigation | Independent forward/backward seek policy, trusted resume, bounded report replay and blocked-seek recovery. |
| Speed | Configurable speed list, learner rate-change policy, maximum rate and fallback rate after a blocked seek. |
| Watched evidence | Server-authorised playback sessions, idempotent segment requests, unique-interval coverage and derived progress state. |
| Timed text | WebVTT subtitles, searchable transcript, chapters and timestamp navigation. |
| Reactions | Opt-in presets and custom emoji, text, Font Awesome or uploaded icons; configuration guard, add/remove, deduplication and burst limits. |
| Study tools | Personal timestamped notes, private named bookmarks, owner bookmark replay/export and compact collapsible histories. |
| Forum | Optional timestamped composer linked to a compatible Forum in the same course. |
| Acknowledgement | Versioned learner statement, anytime/final-second timing and completion integration. |
| Completion | Viewed percentage, minimum/distinct/required reactions and acknowledgement combined with AND/OR logic. |
| Grades | Moodle gradebook points/scales, pass grade, learner grade display and standard grade completion conditions. |
| Reports | Learner, activity, course and teacher views; aggregate/individual permissions; reaction timelines and clusters. |
| Analytics | Same-video cross-course analysis, scoped cohorts/groups, 7/30/90-day windows and privacy thresholding. |
| Exports | CSV, Excel and ODS reports, configurable CSV fields/delimiter, individual data exports and owner bookmark CSV. |
| Integrity | Optional visibility/focus, Picture-in-Picture and random-pause diagnostics; server-side playback guard. |
| Accessibility | Keyboard operation, visible focus, status regions, responsive/reflow layout, forced-colours support and accessible completion grouping. |
| Privacy | Moodle Privacy API, owner export/deletion, context/user-list deletion, scheduled deletion-based retention and explicit unlimited-retention confirmation. |
| Lifecycle | Backup/restore with or without user data, reset, instance deletion, gradebook repair and Moodle events. |
| Administration | Site defaults, enforceable player/completion policies, reaction presets, eight language packs, read-only validator and Analytics benchmark. |
| Quality | Repository-native GitHub Actions, PHPUnit/Behat, Moodle PHPCS/PHPDoc/validation, canonical AMD gates, reviewed PHPMD and Moodle-aware PHPStan/Psalm baselines. |

This table is an exhaustive map, not the operational specification. Behaviour, permissions, data boundaries
and provider limits are documented once in the [complete guide](docs/en/funzionalita.md) and the linked
technical references.

## Requirements and installation

- Moodle 5.0–5.3 and a PHP/database stack supported by the selected Moodle branch.
- Network access to YouTube/Vimeo when those public providers are used.
- Moodle scheduled tasks enabled when retention cleanup is required.

Install through Moodle's plugin installer or place the single `videotrack` directory in `mod/`, then open
**Site administration → Notifications** or run Moodle's upgrade CLI. Review site defaults, retention,
report identity fields and focus policy before enabling the activity broadly.

Do not edit installed files directly. Use a reviewed release and purge Moodle caches after deployment.

## Roles and privacy at a glance

`mod/videotrack:participate` controls learner tracking independently from report permissions. Teachers and
administrators remain in non-tracking preview mode unless they also receive that capability. Aggregate and
individual report/export permissions are separate so privacy masking and learner-level access can be governed
independently.

Optional diagnostics are evidence for investigation, not proof of learner intent. Personal notes and bookmark
labels are excluded from aggregate Analytics. See [`PRIVACY.md`](PRIVACY.md) before changing retention or
identity-field settings.

## Administration and validation

From the Moodle root:

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
```

The tools are read-only. Options and interpretation are in
[`docs/en/21_CLI_DIAGNOSTICS.md`](docs/en/21_CLI_DIAGNOSTICS.md). Maintainers should follow
[`docs/en/07_BUILD_TEST_RELEASE.md`](docs/en/07_BUILD_TEST_RELEASE.md); when `amd/src` changes, the matching
Moodle Grunt build and source maps are mandatory.

Repository CI, its Moodle/PHP/database matrix, blocking/advisory checks and downloadable artifacts are documented
in [`docs/en/23_GITHUB_ACTIONS_CI.md`](docs/en/23_GITHUB_ACTIONS_CI.md).

## License

GNU GPL v3 or later, consistently with Moodle.
