# VideoTrack privacy and data-handling summary

This document describes the current VideoTrack data model and operational privacy boundaries. It complements, but does not replace, the site’s own privacy notice, lawful-basis assessment and retention policy.

Italian version: [`PRIVACY_IT.md`](PRIVACY_IT.md).

## Data stored by feature

VideoTrack may store the following records when the corresponding feature is used:

- **Playback ledger rows:** user, activity/course identifiers, session and idempotency identifiers, provider/video identifier, video start/end, diagnostic client wall-clock start/end, validated playback rate, close reason, server-validation flag and creation time. Zero-length `playstart` rows establish a server-time window and do not count as watched progress.
- **Aggregated learner state:** teacher-authoritative duration, last position, compact merged watched intervals, exact monotonic unique-viewed seconds, completion state, bounded cumulative server credit counters, the latest handshake/request timestamp in milliseconds and normal record timestamps.
- **Reactions:** configured reaction key, video time, playback rate, active/deleted state and timestamps.
- **Personal notes:** owner, private text, watched video time, playback rate, active/deleted state and timestamps.
- **Private bookmarks:** owner, private label, watched video time, playback rate, active/deleted state and timestamps.
- **Integrity signals:** bounded signal type, approximate video time, session/activity identifiers and creation time. No webcam, microphone, biometric, screenshot, keylogging or other-tab content is collected.
- **Acknowledgements:** owner, current statement hash/version, confirmation time and, for current records, the unique viewed seconds and percentage captured at confirmation.

Uploaded source video, poster, captions, transcript and chapter files are stored in Moodle File API areas. Posts created through the optional Forum composer are owned and governed by `mod_forum`, not duplicated by VideoTrack.

## Visibility boundaries

- A learner can access only their own notes and bookmark labels.
- Personal note text in activity reports requires individual-report visibility and individual-export permission for downloads; bookmark labels remain owner-only.
- Per-student progress requires `mod/videotrack:viewindividualreport` (or the historical full `mod/videotrack:viewreport`) and remains constrained by Moodle enrolment/group scope.
- `mod/videotrack:viewaggregatereport` permits cumulative and instance Analytics without learner filtering. Aggregate-only viewers remain protected by `analyticsminusers`; viewers who also have individual-report access receive exact aggregate values inside the same Moodle scope.
- Private note text and bookmark labels are never exposed in aggregate Analytics.
- Cross-course instance Analytics include only activities where aggregate report access is available. Exact values are used only when individual-report access is available for every included activity; otherwise the configured minimum-user threshold remains active.
- Integrity indicators are diagnostic. They must not be treated as conclusive proof or used alone for automatic grading, completion, discipline or access decisions.

## Accessibility and focus controls

The default focus policy pauses playback when the document becomes hidden. Window blur is recorded only after a configurable grace period and pauses playback only if the site administrator enables the strict policy. This distinction reduces false positives caused by assistive technologies, browser chrome, password managers and operating-system dialogs.

Picture-in-Picture prevention is best effort. HTML5 exposes stronger controls than embedded YouTube or Vimeo, and browsers/extensions may override provider policy. These limitations are displayed in configuration help and must be considered when interpreting integrity data.

## Export, erasure and reset

The Moodle Privacy API provider:

- declares all VideoTrack tables and external provider links;
- finds contexts containing a user’s data;
- exports viewing, state, reaction, note, bookmark, integrity and acknowledgement records in bounded chunks;
- deletes data for an approved user/context request;
- supports bulk user deletion for a context.

Activity deletion, course/activity reset and dedicated student-progress reset remove the corresponding plugin-owned records. Gradebook data is handled by Moodle core’s gradebook privacy provider.

## Retention

The scheduled cleanup task permanently deletes plugin-owned learner records that fall outside the configured retention window. VideoTrack does not keep deterministic negative-user pseudonyms or a mapping key. After deletion, the derived `videotrack_state` row is rebuilt from retained server-validated segments and retained completion inputs; stale playback credit is cleared and Moodle custom completion is synchronised. Progress and completion may therefore decrease when their supporting evidence expires.

A value of `0` disables age-based automated cleanup and therefore requires an explicit, documented site policy and periodic review. Approved Privacy API erasure still removes the selected learner records regardless of that value. Shared activity files are configuration data and remain until the activity itself is deleted.

Sites should choose the shortest retention period compatible with their educational and legal purpose, publish it to learners and review access to exports containing identities.

## Backup and restore

Moodle backup includes activity configuration and files. When user data is requested, VideoTrack includes only positive-user records still inside the source site's current retention window; derived `videotrack_state` rows are not included. Restore remaps identifiers, rejects legacy pseudonymous and destination-expired records, and rebuilds state after Moodle restores course-module completion. A restored custom-completion row without retained VideoTrack evidence is reset to incomplete. Runtime playback-credit counters are not transferred as trusted progress. Backups containing user data must be protected according to the same policy as the live Moodle database.

## CSV and data-format exports

Identity fields in individual reports are configurable by site and activity and are limited to fields visible to the exporter. Individual downloads require `mod/videotrack:exportindividualreport` (or the historical full report capability); exports containing individual reactions or comments also require explicit confirmation and generate Moodle events. Aggregate downloads require `mod/videotrack:exportaggregatereport` and preserve the same exact-or-thresholded values shown on the authorised aggregate page. Personal note text and private bookmark labels/timestamps remain excluded from aggregate exports.

## Browser storage

VideoTrack may use session-scoped browser storage for transient UI/session state. It is not intended for cross-site tracking and is not a substitute for server-side authorisation or persistence.

Detailed implementation references are in `docs/en/02_ARCHITECTURE.md`, `06_RUNTIME_FLOWS.md`, `11_INTEGRITY_AND_FOCUS.md` and `12_ACKNOWLEDGEMENT.md`.
