# Learner acknowledgement

The feature is disabled by default and has a dedicated activity-form section. The teacher supplies formatted text and selects “any time” or “after the final video second”. The current version hash includes text, format and timing.

Confirmation uses a normal POST form with `sesskey`. “Final second” mode is enforced twice: the player enables controls after the end segment is saved, and the server checks persisted progress with a one-second tolerance. Manual POST cannot bypass the server check.

`videotrack_acknowledge` stores the user/activity ids, statement hash, activity version, confirmation time and immutable viewed-seconds/percentage snapshot. Older records without snapshots are reported as unavailable, not zero. A text/timing change requires a new current confirmation.

Custom completion can require the current acknowledgement. Per-student reports show status, date and snapshot. Analytics/export show current-version confirmations, distinct students, average progress and legacy-missing count, with separate privacy suppression for counts and progress contributors.

Privacy API, retention, reset and user-data backup/restore cover acknowledgements. The full statement is not duplicated in each user row.
