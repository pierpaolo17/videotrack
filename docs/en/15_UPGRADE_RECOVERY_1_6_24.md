# Upgrade recovery 1.6.24

VideoTrack 1.6.24 corrects the database-upgrade failure introduced by the first 1.6.23 package.

## Failure mode

The original 1.6.23 upgrade added the server-authoritative tracking fields and then invoked Moodle course-module and completion runtime APIs before reaching the plugin savepoint. A failure in that phase left the plugin version unchanged, so Moodle retried the same block during the next upgrade attempt.

## Corrected path

- The 1.6.23 block is now schema-only and remains idempotent through `field_exists()` checks.
- The 1.6.24 block uses database APIs only and can safely run after a partially completed 1.6.23 attempt.
- Existing learner runtime rows are deleted from segments, aggregate state, interactions, integrity signals and acknowledgements.
- Existing `course_modules_completion` rows for VideoTrack course modules are deleted so stale completion cannot survive the clean reset.
- Activity instances, settings, uploaded files and configured reaction definitions are preserved.
- No course, course-module or completion runtime API is called from `db/upgrade.php`.

## Data policy

This one-time destructive reset is intentional because VideoTrack had not been used in production. It establishes a clean baseline for the server-authoritative tracking model. New learner progress is collected normally after the upgrade.

## Supported recovery cases

The step supports both:

1. a direct upgrade from 1.6.22 to 1.6.24;
2. recovery after a 1.6.23 upgrade stopped after creating some or all of the new fields but before its savepoint.
