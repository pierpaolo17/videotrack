# VideoTrack for Moodle

[![Moodle Plugin CI](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml/badge.svg)](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml)

VideoTrack is a Moodle activity module for HTML5/uploaded, YouTube and Vimeo video. It combines
server-validated watched progress, study interactions, custom completion, gradebook integration and
privacy-aware reporting.

Current release documented by this tree: **1.7.265**. Supported Moodle branches: **5.0–5.3**.

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
| Administration | Site defaults, enforceable player/completion policies, reaction presets, eight language packs, read-only validator, Analytics benchmark and explicit authenticated Graph metadata diagnostics. |
| Quality | Repository-native GitHub Actions, PHPUnit/Behat, Moodle PHPCS/PHPDoc/validation, canonical AMD gates, reviewed PHPMD and Moodle-aware PHPStan/Psalm baselines. |

This table is an exhaustive map, not the operational specification. Behaviour, permissions, data boundaries
and provider limits are documented once in the [complete guide](docs/en/funzionalita.md) and the linked
technical references.

## Site source availability

Administrators can independently enable YouTube, Vimeo and local HTML5/uploaded
media. All three default to enabled, including upgraded sites with no stored flags.
Disabled sources cannot be selected for new activities or used for playback/new
tracking. Existing records and reports are retained. The editing form identifies
an existing disabled source without silently replacing it.
See [site source policy and optional integrations](docs/en/31_SOURCE_POLICY.md).

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

## Project milestones

U-017 maintainability and U-007 essential browser automation are closed on accepted release evidence.
The [browser exit audit](docs/en/26_BROWSER_EXIT_AUDIT.md) records scope and provenance. U-020 manual
accessibility has eight reported PASS cases on Moodle 5.0/Academi with Windows 11/Chrome/NVDA; Moodle 5.3 remains unrecorded; retain the [manual protocol](docs/en/19_ACCESSIBILITY.md#manual-evidence-protocol-u-020)
for the final production gate. The [targeted language review](docs/en/27_LANGUAGE_REVIEW.md) records
source-grounded policy wording corrections in all eight packs and the remaining semantic-review scope.

## License

GNU GPL v3 or later, consistently with Moodle.

## Authenticated provider preparation

The existing `provider_status.php` inspects local OAuth readiness without contacting
Microsoft. Version 1.7.259 adds `provider_probe.php` for an explicit authenticated GET
of drive/video metadata through the core system account. Core may refresh/persist
OAuth tokens; VideoTrack/demo records are untouched. OneDrive/SharePoint playback
is a later gate. See [Microsoft Graph diagnostics](docs/en/30_MICROSOFT_GRAPH.md).

The maintainer now prioritises authenticated sources: OneDrive/SharePoint, Zoom Cloud Recording, then Google Drive; the App comes afterwards. The App-to-browser/HTTPS trial is explicitly skipped. The full multilingual subtitle extension is deferred because it is not bounded reliably to one release across HTML5 and provider overlays. Existing transcripts and captions remain supported. Manual Moodle 5.3 accessibility stays on standby.

## 1.7.257: security review corrections

This release addresses the reviewed settings-authorisation and dashboard privacy gaps.
The save callbacks enforce the same course-level override capabilities as the form before
normalisation, database writes or caption-file changes. Existing locked values are preserved;
new activities use site defaults, including explicit zero and inherited empty choices.
Completion dependencies cannot be disabled with zero duration, disabled acknowledgement,
or removal/reordering of required reaction definitions. Rate limit and resume controls that
the existing form leaves editable retain that contract.

Course and teacher rows require activity aggregate-report access. The effective contributor
floor is resolved per activity: legacy/individual access may request exact values; aggregate-only
access uses at least the configured site threshold, even when a caller requests one. All-time,
period, reaction, note and bookmark summaries use that floor. Suppressed counts display as
hidden rather than zero. Activity SQL batching is retained; new benchmark timings are pending.

Empty manual grades now use a null raw grade through the Grade API for points and scales.
HTML names/options are escaped separately from plain CSV labels and HTML attribute values.
Reaction warnings and the student-facing note visibility notice use all eight language packs.
The note notice explains that authorised individual-report users may read and export notes.

Video page GET no longer creates or repairs the focus exception group. Creation remains in
add/update/restore/upgrade. Full uninstall deletes only groups with the plugin's reserved
idnumber, including when no grade items exist. Grade repair uses a delegated DML transaction
so an interrupted merge/delete rolls back; DDL is not wrapped in that transaction.

[Security review corrections](docs/en/29_SECURITY_REVIEW.md).

## 1.7.258: security follow-up

Removal physically erases the owned note/bookmark row. AJAX parameters and responses
remain compatible. Login/context/participation/sesskey checks precede retries: missing ids
are successful no-ops, existing foreign or wrong-type rows are rejected. A Moodle lock
serialises removals and the transaction includes one event without private text. An old
soft-deleted row is erased without duplicating its event.

User backups and Privacy exports exclude removed personal items; restore rejects personal
rows marked deleted in older XML. Active tools and reaction history retain their contracts.
Backup files created before deletion may contain then-active copies: their retention is
separate, and restore cannot infer a later deletion from an older active copy. No general
migration or demo cleanup runs.

Schema recovery moves to upgrade-only db/upgradelib.php. Install/uninstall retain DML
grade repair. Valid uninstall completion contexts use completion_info::delete_all_state;
only orphan module references need scoped completion DML. Ambiguous/orphan pre-production
grade rows retain tested transactional DML: those malformed rows can break the Grade API's
context resolution, so replacing that recovery with runtime grading calls is unsafe.

Controllers use data_submitted() with existing sesskey/capability checks. Analytics downloads
use POST with the selected filters and sesskey in the body; GET downloads are rejected before
their export event/output. The server generates opaque resume keys for site, participating
user, module, source and video identity. YouTube/Vimeo ignore old shared keys and disable
storage with an incomplete identity; HTML5 retains server resume. Browser hints never
become validated viewing evidence. The activity index cleans course-format section HTML
without applying filters twice; this is defence in depth, not a reproduced Tiles XSS claim.

[Security follow-up](docs/en/29_SECURITY_REVIEW.md).

The [Microsoft connection check](docs/en/32_MICROSOFT_WEB_CHECK.md) is available
from the site settings. It verifies metadata access with the existing core OAuth
service; OneDrive/SharePoint playback remains a separate roadmap gate.

Release 1.7.262 adds a [personal Microsoft metadata check](docs/en/33_MICROSOFT_USER_ACCESS.md)
using the current administrator’s core OAuth account, without a system-account fallback.
It does not authorise learner playback or add a cloud player.

Release 1.7.263 can [resolve the original Microsoft video page](docs/en/34_MICROSOFT_PROVIDER_PAGE.md)
for the current administrator’s connected personal account. The page opens on the
provider in a new tab; this does not add a tracked Microsoft player to activities.

Release 1.7.264 adds an explicit [personal Microsoft connection](docs/en/35_MICROSOFT_USER_CONNECTION.md)
from the administrator diagnostic, using Moodle core OAuth. Connection does not
establish access to a particular video or enable a tracked Microsoft player.

Release 1.7.265 preserves available Behat browser dumps even after a passing retry
and records the completed test step's runner state. This is CI maintenance; production
playback and Analytics are unchanged. See [evidence and limits](docs/en/36_CI_BEHAT_EVIDENCE.md).
