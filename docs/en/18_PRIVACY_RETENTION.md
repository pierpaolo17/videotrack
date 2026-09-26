# Privacy and retention

The root `PRIVACY.md` is the user-facing summary. This document describes the current implementation contract.

## Personal data map

| Table/area | Personal data | Purpose |
|---|---|---|
| `videotrack_seg` | User, session, media intervals, rate, timestamps, validation outcome | Watched evidence and audit. |
| `videotrack_state` | User, merged intervals, position, percentage, completion | Derived efficient state. |
| `videotrack_reactev` | User, reaction/note/bookmark, private text, media time | Study interactions and completion. |
| `videotrack_integrity` | User, bounded diagnostic type, session/time | Optional integrity diagnostics. |
| `videotrack_acknowledge` | User, statement hash/version, viewed snapshot, confirmation time | Versioned acknowledgement. |
| Moodle files | Uploaded video, VTT, poster and reaction icons in module context | Activity content. |

Configured activity data and reaction definitions are not learner-owned records, though teacher-authored text may
still be personal in the ordinary Moodle course context.

## Moodle Privacy API

`classes/privacy/provider.php` declares metadata, contexts and user lists, exports current user data and delegates
deletion to the scoped privacy manager. Supported deletion includes all data for a user, all data in a context and
selected users in a context. Owner exports preserve private note/bookmark text only for the authorised subject.

## Retention

`retentionperioddays` defines the rolling deletion window. The scheduled task removes expired granular learner
records in bounded operations and rebuilds/removes derived state so it never retains progress unsupported by
remaining personal evidence. Unlimited retention (`0`) is accepted only with explicit
`retentionunlimitedconfirmed` administrator confirmation.

The admin setting processes that decision in three separate steps: resolve the previous value, reject an
unconfirmed unlimited submission, then record the transition only after Moodle has persisted the new value. A
finite value clears the confirmation marker; a newly enabled unlimited value writes the dedicated configuration
audit entry when the Moodle logging helper is available. Validation failure never records a transition.

`validationfallbackdays` is a separate bounded privacy/performance setting for legacy validation fallback; it is
not the general retention period.

## Reporting privacy

Aggregate report access applies `analyticsminusers` masking and does not expose learner filters. Individual report
access may show exact aggregates and learner detail only within Moodle scope. Aggregate and individual export
capabilities are separate. Optional CSV identity fields must be enabled deliberately.

Personal note text and bookmark labels are excluded from aggregate Analytics. Integrity signals are labelled as
diagnostic and must not be presented as automated misconduct findings.

## Backup, restore and deletion

User-data backups include only records inside the current retention window. Restore remaps user ids and rebuilds
derived state. Activity deletion, reset, Privacy API deletion and retention cleanup are separate supported paths
and must all remove or recompute the same data families consistently.

## Administration checklist

- document the lawful purpose and retention period before enabling optional collection;
- use the shortest period compatible with the teaching need;
- keep unlimited retention disabled unless explicitly justified and confirmed;
- minimise CSV identity fields and individual-report capability assignments;
- verify scheduled tasks, Privacy API exports/deletions and backup policy after configuration changes.
