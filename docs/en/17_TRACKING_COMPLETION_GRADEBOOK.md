# Tracking, completion and gradebook

## Playback ledger

`videotrack_seg` is an append-oriented ledger of playback requests and their server-validation outcome. A stable
`requestid` makes retries idempotent per activity/user. `sessionid` binds each request to an authorised playback
window. `servervalidated=1` means the row may contribute watched evidence; other rows remain diagnostic only.

The server budget is based on elapsed server time and allowed playback rate. Requested media intervals are clamped
to valid bounds and cannot consume more credit than available. Accepted terminal reasons close the active window.

## Derived state

`videotrack_state` stores one row per activity/user. It contains merged interval JSON, unique covered seconds,
trusted last position, percentage and completion cache. It is derived and can be rebuilt from retained validated
segments plus current interaction/acknowledgement rules.

Coverage is monotonic within retained evidence: replay does not double count and forward seeks do not fill gaps.
Duration changes require recalculation because percentage depends on the teacher-saved denominator.

## Composite VideoTrack completion

Enabled criteria can include viewed percentage, reaction rules and current acknowledgement. `completionlogic`
combines the enabled VideoTrack criteria as `and` or `or`. The plugin exposes this as one custom completion rule so
internal OR semantics are not misrepresented as separate mandatory Moodle rules.

The custom completion provider and tracker use the same configuration/signature. Every mutation that can change a
criterion refreshes derived state and synchronises Moodle completion only when the state actually changes.

`completion_config::active_condition_descriptions()` assembles the public list in this order: viewed percentage,
the grouped reaction conditions, then acknowledgement. Its internal reaction-description routine returns nothing
when reactions are disabled; otherwise it adds the minimum count, active individually required reactions ordered by
definition sort order/id, and the all-types condition. Reaction labels are formatted in the activity context before
the group is joined with the configured AND/OR label.

Moodle core conditions such as activity view, grade received and pass grade remain separate. The browser layout
may move the VideoTrack AND/OR label before a multi-item requirement list but never changes state or ordering.

## Gradebook

The activity supports no grade, numeric maximum points or a Moodle scale. `gradepass` is sent to the grade item and
can drive the core pass-grade completion condition. `showgradeto` controls activity-page display; Moodle's grade
permissions still govern access.

Create/update/delete and restore use Moodle grade APIs. Repair logic normalises duplicate legacy items and moves
user grades to the retained canonical item when necessary. A canonical item must identify item number `0`, the
same course as its VideoTrack instance and exactly one corresponding VideoTrack course module.

Full plugin uninstall does not call the normal instance-deletion callback for every activity. The custom early
uninstall hook therefore removes valid grade items through the Grade API before Moodle core deletes module contexts.
Only rows that cannot resolve a unique context use the DML-only cleanup path.

## Verification invariants

- same request id cannot create a second logical segment;
- stale/cross-session writes cannot advance coverage;
- skipped intervals never enter unique coverage or resume position;
- reaction deletion and acknowledgement version changes refresh completion;
- repeated refresh without a transition does not emit duplicate completion events/writes;
- backup/restore rebuilds derived state and preserves grade/completion consistency.
- CLI `gradebook_integrity` reports no invalid contexts or duplicate canonical items before uninstall.
