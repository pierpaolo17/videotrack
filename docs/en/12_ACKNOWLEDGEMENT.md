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

## Confirmation availability and progress snapshot (1.6.20)

The acknowledgement has its own collapsed section in the activity form. The teacher chooses one of two policies:

- **At any time**: preserves the 1.6.19 behaviour and legacy statement hash, so existing confirmations remain current after upgrade.
- **Only after the final video second**: the server accepts confirmation only when persisted `videotrack_state` intervals or the last tracked position reach the final second, with a one-second media-end tolerance.

The reaction notice checkbox and editor belong to the Reactions section and are no longer displayed with the acknowledgement settings. All instance-form sections are collapsed by default except **Video source**.

Each new confirmation stores an immutable viewing snapshot: `viewedseconds` is the unique covered time and `viewedpercent` is its percentage of the effective duration at confirmation time. Teacher HTML and CSV reports display confirmation status, date, viewed seconds and viewed percentage. Later viewing does not rewrite this snapshot.

When end-gating is active, the form is initially disabled until the persisted state already proves the end was reached. During the current page view, the three player modules emit `videotrack:ended` only after the final segment save completes; `core/player/acknowledgement.js` then enables the controls. Server-side validation remains authoritative.
Confirmations created before 1.6.20 have no historical progress snapshot; reports label that value as unavailable rather than inferring zero or using later viewing data.
