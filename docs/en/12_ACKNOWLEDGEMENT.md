# Optional learner acknowledgement

VideoTrack 1.6.19 adds an optional, explicit acknowledgement statement for compliance-oriented courses. The feature is disabled by default and does not claim to verify comprehension.

## Configuration

The teacher enables the statement in the activity settings and enters formatted text. A separate custom-completion option can require confirmation of the current statement.

Changing the stored statement text or format creates a new statement hash. Earlier confirmations remain in the audit history but no longer satisfy the current statement or its completion rule.

## Learner flow

The statement is displayed after the video interface. The learner must select an explicit checkbox and submit the form. VideoTrack stores:

- activity, course module and user identifiers;
- a non-reversible SHA-256 statement hash;
- the activity modification timestamp at confirmation;
- the confirmation timestamp.

The full statement is not duplicated in the confirmation table.

## Completion, reports and export

When enabled as a completion condition, the current statement must be confirmed. Teacher reports and the standard CSV show whether the current version is confirmed and its date. Moodle Privacy API exports the learner's confirmation history.

## Privacy and lifecycle

The table `videotrack_acknowledge` is included in backup/restore only when user data is included. User, activity, course reset and Privacy API erasure remove confirmations. Retention cleanup deletes expired confirmation records rather than pseudonymising them.
