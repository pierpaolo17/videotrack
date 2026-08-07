# Runtime flows

## Activity view and player boot

`view.php` requires the activity, verifies `mod/videotrack:view`, loads the user state and teacher-supplied files, resolves site/instance policies and writes a JSON configuration element. It then initialises exactly one adapter: HTML5, YouTube or Vimeo. Shared modules attach status, progress, interval bar, reactions, notes, bookmarks, transcript, chapters, forum and focus controls.

## Segment tracking

Provider callbacks update a shared tracker. Only active playback contributes. The client periodically closes bounded segments and calls `mod_videotrack_save_segment`. The service validates context, capability, session and movement rules; `local\tracker` stores the raw segment, merges intervals into `videotrack_state`, recalculates unique seconds/percentage, updates completion and grade, and emits events where applicable. Lifecycle hooks flush on pause, end, visibility changes and unload using AJAX or a constrained beacon fallback.

## Seek, resume and replay

User seek is checked against forward/backward policy. A blocked forward seek returns to the latest allowed point and may use the configured recovery speed. Resume and report replay are programmatic seeks and are tagged separately to avoid false integrity events. Transcript, chapter, note and bookmark replay use the same adapter seek policy.

## Reactions, notes and bookmarks

Reaction definitions are configured inside one collapsible Reactions section; the form renders at least four editable rows, keeps one spare row after existing active definitions and can be expanded with **Add reaction** up to the established cap of 30. In the activity view, reaction controls precede Forum posting and bookmarks. Canonical learners can activate them while the player is playing or paused; the server accepts a reaction only at a timestamp already covered by validated viewing data. Report-capable staff receive a visible but disabled preview and cannot persist learner telemetry. Notes and bookmarks are shown whenever enabled, require a timestamp already viewed by the owner and remain disabled in staff preview mode. External services enforce feature enablement, learner scope, ownership and maximum lengths. Deleted records retain a bounded tombstone until retention/erasure; private text is never copied into teacher aggregates.

## Timed text

Teacher-uploaded WebVTT is parsed by `local\timed_text`. Native YouTube/Vimeo captions stay inside the provider and are not searchable by VideoTrack. Searchable transcript tracks and chapter VTT are separate File API resources. Language choice, search, active-cue highlighting and chapter navigation run in the shared timed-text module.

## Focus and integrity

`focus_guard` applies enabled controls. Hidden-document pause is the accessibility-oriented default. Window blur is delayed and only pauses in strict policy. Random pauses use administrator-defined minimum/maximum bounds and reset after learner/player actions. Allowed diagnostic events are written through `save_integrity_event`; the server rate-limits and validates their type.

## Acknowledgement

The statement version is a hash of current text/format/timing. The POST form is protected by `sesskey`. In “video end” mode, client controls enable only after the final segment is saved and the server independently verifies the persisted end position. The confirmation stores the current hash, timestamp and immutable progress snapshot, then refreshes completion/state. Changing the statement or timing requires a new confirmation.

## Reports and exports

Per-student reports may include identity, progress, reactions, acknowledgement date/snapshot and diagnostic counts according to capability. Course and teacher dashboards aggregate accessible activities. Instance Analytics builds privacy-safe bins and separate summaries for reactions, bookmarks, integrity and acknowledgements. CSV/Excel/ODS use the same masked aggregates; individual CSV exports require explicit confirmation where personal data is present.

## Privacy, retention and deletion

Privacy export streams each record family in bounded chunks. User/context deletion calls `privacy_manager`; activity deletion and reset remove all corresponding records. The scheduled task anonymises or deletes expired data according to policy. Backup includes user tables only with user data and restore remaps identifiers.

## Activity-form duration suggestion

1. The teacher selects YouTube, Vimeo or a local upload and supplies the source.
2. `mod_videotrack/form/duration` validates the source and performs a best-effort metadata probe: YouTube IFrame API, Vimeo Player SDK, or same-origin HTML media metadata for the Moodle draft file.
3. The proposed seconds are written only into the teacher-editable form field and announced through a polite live status region. A manual edit is preserved; changing the source starts a new proposal.
4. `videotrack_add_instance()` or `videotrack_update_instance()` stores the reviewed form value. Only this saved value is authoritative for percentage, completion and end-gated acknowledgement. Learner player metadata cannot update it.
5. When metadata is unavailable, the field remains manual and `0` keeps percentage-dependent functions disabled.
