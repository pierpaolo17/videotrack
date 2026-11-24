# VideoTrack privacy and data-handling summary

## 1.7.260: source availability

Site switches block playback and new tracking while retaining saved personal data.
Retention, Privacy exports and authorised erasure keep their existing rules.
No cloud credential, connection or personal field is introduced.
See [source policy](docs/en/31_SOURCE_POLICY.md).

## 1.7.259: explicit Microsoft metadata diagnostics

The optional CLI probe uses Moodle's Microsoft system OAuth account. Core owns token
refresh/storage; VideoTrack stores no new credential or activity record. The command
makes a metadata request to Microsoft and discards resource/user identities, private
URLs and provider error bodies from its JSON. It never downloads file content or
sends learner tracking data. Existing local status diagnostics remain network-free.
[Protocol, output and limits](docs/en/30_MICROSOFT_GRAPH.md).

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

This document describes the current VideoTrack data model and operational privacy boundaries. It complements, but does not replace, the site’s own privacy notice, lawful-basis assessment and retention policy.

Italian version: [`PRIVACY_IT.md`](PRIVACY_IT.md).

## Data stored by feature

VideoTrack may store the following records when the corresponding feature is used:

- **Playback ledger rows:** user, activity/course identifiers, session and idempotency identifiers, provider/video identifier, video start/end, diagnostic client wall-clock start/end, validated playback rate, close reason, server-validation flag and creation time. Zero-length `playstart` rows establish a server-time window and do not count as watched progress.
- **Aggregated learner state:** teacher-authoritative duration, last position, compact merged watched intervals, exact monotonic unique-viewed seconds, completion state, bounded cumulative server credit counters, the latest handshake/request timestamp in milliseconds and normal record timestamps.
- **Reactions:** configured reaction key, video time, playback rate, active/deleted state and timestamps.
- **Personal notes:** owner, personal note text, watched video time, playback rate, active/deleted state and timestamps.
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

Courses containing VideoTrack may use a hidden, non-participating Moodle core group to exempt authorised learners from strict window-blur pausing. VideoTrack reads only core group membership and stores neither an accommodation reason nor a new learner attribute. The exception does not weaken hidden-tab or server-side playback validation.

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

## Administrator Microsoft diagnostics

Explicit administrator diagnostics send the selected drive/video identifiers to
Microsoft Graph using the selected Moodle core OAuth identity. A requested private
preview uses the current administrator’s connected work/school account. Its iframe
makes browser requests to Microsoft, which may receive IP address, user agent and
cookies. The Privacy API declares this external transfer. Core manages OAuth credentials.

The temporary preview address can carry the caller’s access. It appears only in that
administrator’s requested preview and is not saved as activity data, exported or
shared with learners by VideoTrack. Diagnostic JSON omits it. This action does not
create watched progress, grades or completion. See the [preview guide](docs/en/37_MICROSOFT_PRIVATE_PREVIEW.md).

## Personal Microsoft search

An explicit administrator POST sends the bounded search text to the selected Microsoft
service under the current personal account. Query text is declared in Privacy metadata.
Private names/ids remain in the protected result response; general diagnostic JSON omits
them. VideoTrack adds no query/candidate storage, media import or application logging.
Core retains token management; provider and infrastructure logs have their own policies.

## Optional activity reference

An editor may explicitly save drive/video ids and a local Microsoft OAuth service id
in activity configuration. This contains no tokens, secrets, temporary URLs or learner
identity. Course backup exports only the portable resource ids/version/provider,
including without user data. Restore requires explicit destination service binding.
The active player and learner-owned Privacy exports are unchanged. See the
[reference guide](docs/en/41_MICROSOFT_ACTIVITY_REFERENCES.md).
