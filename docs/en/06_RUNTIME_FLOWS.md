# Runtime flows

## Activity view and player boot

`view.php` requires the activity, verifies `mod/videotrack:view`, loads the user state and teacher-supplied files, resolves site/instance policies and writes a JSON configuration element. It then initialises exactly one adapter: HTML5, YouTube or Vimeo. Shared modules attach status, progress, interval bar, reactions, notes, bookmarks, transcript, chapters, forum and focus controls.

## Segment tracking

Provider callbacks update a shared tracker. A PLAY event first calls `mod_videotrack_start_playback`, which establishes a server timestamp without granting watched time. After the handshake succeeds, active playback opens a bounded segment. Each segment has one request identifier generated before transport retries; `mod_videotrack_save_segment` reuses an identical persisted result and rejects identifier reuse with different data. The service validates context, `mod/videotrack:participate`, allowed speed, forward frontier and cumulative server-time credit. `local\tracker` stores the raw request, merges validated intervals into `videotrack_state`, preserves exact monotonic unique coverage beyond the compact 500-interval representation, recalculates percentage/completion and emits events only once. Lifecycle hooks flush on pause, end, visibility changes and unload using AJAX or a constrained beacon fallback.

## Seek, resume and replay

User seek is checked against forward/backward policy. Before any accepted or blocked seek, the adapter snapshots only the segment that was actually played up to the trusted pre-seek position; the skipped gap is never credited as continuous viewing. A blocked forward seek returns to the latest allowed point and may use the configured recovery speed. While rollback is settling, Forum/reaction/note/bookmark interactions use the last trusted timestamp rather than the transient forbidden provider position. Resume and report replay are programmatic seeks and are tagged separately to avoid false integrity events. Transcript, chapter, note and bookmark replay use the same adapter seek policy.

## Reactions, notes and bookmarks

Reaction definitions are configured inside one collapsible Reactions section; the form renders at least four editable rows, keeps one spare row after existing active definitions and can be expanded with **Add reaction** up to the established cap of 30. In the activity view, reaction controls precede Forum posting and bookmarks. Users with `mod/videotrack:participate` can activate them while the player is playing or paused; the server accepts a reaction only at a timestamp already covered by validated viewing data. Report access is independent: dual-role participants remain interactive, while ordinary staff without the participation capability receive a disabled preview and cannot persist learner telemetry. Notes and bookmarks are shown whenever enabled, require a timestamp already viewed by the owner and remain disabled in staff preview mode. External services enforce feature enablement, learner scope, ownership and maximum lengths. Deleted records retain a bounded tombstone until retention/erasure; private text is never copied into teacher aggregates.

## Timed text

Teacher-uploaded WebVTT is parsed by `local\timed_text`. Native YouTube/Vimeo captions stay inside the provider and are not searchable by VideoTrack. Searchable transcript tracks and chapter VTT are separate File API resources. Language choice, search, active-cue highlighting and chapter navigation run in the shared timed-text module.

## Focus and integrity

`focus_guard` applies enabled controls. Hidden-document pause is the accessibility-oriented default. Window blur is delayed and only pauses in strict policy. Random pauses use administrator-defined minimum/maximum bounds and reset after learner/player actions. Allowed diagnostic events are written through `save_integrity_event`; the server rate-limits and validates their type.

## Acknowledgement

The statement version is a hash of current text/format/timing. The POST form is protected by `sesskey`. In “video end” mode, client controls enable only after the final segment is saved and the server independently verifies the persisted end position. The confirmation stores the current hash, timestamp and immutable progress snapshot, then refreshes completion/state. Changing the statement or timing requires a new confirmation.

## Reports and exports

Per-student reports may include identity, progress, reactions, acknowledgement date/snapshot and diagnostic counts according to capability. Course and teacher dashboards aggregate accessible activities. Instance Analytics builds privacy-safe bins and separate summaries for reactions, bookmarks, integrity and acknowledgements. CSV/Excel/ODS use the same masked aggregates; individual CSV exports require explicit confirmation where personal data is present.

## Privacy, retention and deletion

Privacy export streams each record family in bounded chunks. User/context erasure calls `privacy_manager`; activity deletion and reset remove the corresponding records. The scheduled task deletes expired granular rows, rebuilds `videotrack_state` from retained trusted evidence, clears stale playback credit and synchronises custom completion. No deterministic pseudonymous copy is kept. User-data backup includes only positive-user rows inside the source retention window and omits derived state. Restore applies the destination retention policy, remaps users and rebuilds state after Moodle course-module completion has been restored.

## Activity-form duration suggestion

1. The teacher selects YouTube, Vimeo or a local upload and supplies the source.
2. `mod_videotrack/form/duration` validates the source and performs a best-effort metadata probe: YouTube IFrame API, Vimeo Player SDK, or same-origin HTML media metadata for the Moodle draft file.
3. The proposed seconds are written only into the teacher-editable form field and announced through a polite live status region. A manual edit is preserved; changing the source starts a new proposal.
4. `videotrack_add_instance()` or `videotrack_update_instance()` stores the reviewed form value. Only this saved value is authoritative for percentage, completion and end-gated acknowledgement. Learner player metadata cannot update it.
5. When metadata is unavailable, the field remains manual and `0` keeps percentage-dependent functions disabled.
