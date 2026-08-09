# GDPR retention redesign in VideoTrack 1.6.33

## Purpose

VideoTrack 1.6.33 replaces the former deterministic pseudonymisation model with deletion-based retention. Expired granular learner records are removed. The derived `videotrack_state` row is then rebuilt from the personal data that remains inside the configured retention window.

This design keeps the Moodle Privacy API, scheduled cleanup, Analytics, completion, backup and restore aligned with the same data boundary.

## Data boundary

The configured site value `retentionperioddays` applies to these plugin-owned learner records:

- playback requests and server-validated segments in `videotrack_seg`;
- reactions, personal notes, private bookmarks and tombstones in `videotrack_reactev`;
- diagnostic integrity signals in `videotrack_integrity`;
- versioned acknowledgements in `videotrack_acknowledge`.

`videotrack_state` is derived data. It is not retained independently of its inputs and is not used to preserve expired history.

A value of `0` disables age-based deletion. Approved Moodle Privacy API erasure requests still delete the selected user data.

## Scheduled cleanup

The scheduled task processes at most 500 user/activity pairs per run. For each pair it:

1. obtains the same canonical state lock used by playback tracking;
2. deletes granular rows older than the cutoff;
3. invalidates the reaction-count cache;
4. rebuilds aggregate state from retained `servervalidated = 1` segments and retained completion inputs;
5. removes the state row when no retained input can contribute to progress or completion;
6. resets stale server-credit counters while preserving a playback guard that is still inside one bounded heartbeat window;
7. synchronises Moodle custom completion with the rebuilt state.

The state `timecreated` value is set to the earliest retained personal record, so future task runs can continue moving the retention window forward even when the state was recently recalculated.

Completion can return to incomplete when the evidence required by the configured completion rules expires. This is intentional: the completion state must not claim evidence that the plugin no longer retains.

## Removal of legacy pseudonyms

Earlier releases could replace a real user id with a deterministic negative id and retain a site salt. Release 1.6.33 removes:

- all negative-user rows from the five learner-data tables;
- the legacy `anonymisationsalt` configuration value;
- report labels and language strings that presented these rows as anonymous users.

No mapping key or pseudonymous granular record is preserved.

## Moodle Privacy API erasure

Approved user/context erasure permanently removes the learner's VideoTrack rows. It does not delete shared activity configuration files such as the source video, poster, captions, transcripts, chapters or reaction icons. Those files belong to the activity and are removed through the normal activity-deletion lifecycle.

Moodle core remains responsible for gradebook privacy operations.

## Backup and restore

Backups created with user data include only positive-user records whose timestamps are still inside the source site's current retention window. Derived `videotrack_state` rows are not backed up.

Restore applies the destination site's current retention policy again. It rejects:

- legacy negative-user records;
- records older than the destination cutoff;
- user references that cannot be mapped.

After all common Moodle activity steps, including course-module completion, have finished, the restore task rebuilds VideoTrack state from retained rows. Any restored custom-completion value that has no retained VideoTrack evidence is reset to incomplete. Runtime playback-credit counters are not transferred as trusted progress.

## Concurrency and failure handling

Deletion and state rebuilding are protected by the canonical per-user/activity state lock. If the lock cannot be acquired, the pair remains eligible for the next task run. Completion synchronisation happens after plugin rows are committed; a completion API failure is counted and reported without rolling back the privacy deletion.

## Validation matrix

Release validation must cover:

- mixed expired and retained segments;
- expired and retained reactions, notes, bookmarks, integrity events and acknowledgements;
- state rebuilding and state deletion;
- stale and active playback guards;
- unlimited retention plus legacy-pseudonym removal;
- user erasure after retention;
- user-data backup/restore under finite and unlimited policies;
- completion recalculation after restore;
- Privacy API export and deletion after cleanup.

## Operational limits

VideoTrack does not claim that retained aggregates are anonymous if they remain linked to a Moodle user. State is therefore treated as personal data and is exposed to the Privacy API while it exists. Sites should choose the shortest justified retention period, disclose it to learners and protect backup files as personal data.
