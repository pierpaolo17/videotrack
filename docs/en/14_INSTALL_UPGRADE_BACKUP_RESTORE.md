# Installation, upgrade, backup and restore

## Fresh installation

Install the single `videotrack/` directory under `mod/` or through Moodle's plugin installer. Moodle reads
`db/install.xml`, capabilities, services, events and scheduled tasks, then records the plugin version.

After installation:

1. run `php mod/videotrack/cli/validate.php --json` from the Moodle root;
2. confirm 7 tables, 149 fields, 22 explicit indexes, 22 foreign keys/backing indexes, 9 AJAX services,
   8 language packs and 48 AMD sources;
3. review retention, privacy, performance, player, focus and export settings;
4. purge caches and create a test activity for each source used by the site.

## Upgrade

`db/upgrade.php` is an ordered, resumable sequence. Every schema operation checks current state where retry is
possible and ends with `upgrade_mod_savepoint()`. Upgrade code must use database/DDL operations appropriate to the
upgrade phase; runtime course-module, completion or browser assumptions do not belong in schema migration.

Never edit only `db/install.xml` for a released plugin. Fresh installations use `install.xml`; existing sites need
an explicit upgrade step and a larger `$plugin->version`.

Release 1.7.119 adds the stable relationship metadata through `add_key()`. Moodle creates or reuses an exact backing
index for each key but does not create physical database foreign-key constraints or cascades. The upgrade is
idempotent at index level and does not delete or rewrite data. After it completes, run the validator with `--strict`:
any orphan or course/course-module mismatch is a data-quality failure to investigate, never an automatic repair.
The conditional `linkedforumid` and `reactionid` fields are validated separately only when they carry a reference.

## Backup content

Without user data, backup includes activity configuration, configured reactions and Moodle-managed files. With
user data enabled, it additionally includes retained playback segments, reaction/note/bookmark events, integrity
events and acknowledgements. The derived `videotrack_state` table is intentionally not backed up.

All configured reactions, including soft-deleted definitions referenced by historical events, are backed up so
reaction identifiers can be remapped consistently. User records older than the retention cutoff are excluded.

## Restore behaviour

Restore creates the activity, remaps the optional Forum, reactions and users, restores files and retained granular
events, then rebuilds derived state and completion inputs. If a linked Forum is unavailable, Forum posting is
disabled with a warning. Missing reaction mappings are handled defensively with a hidden placeholder rather than
silently converting a historical reaction into a note/bookmark sentinel.

Gradebook repair runs after core grade restore so duplicate or stale items are normalised without losing grades.

## Reset and deletion

Course reset and instance deletion remove scoped state, segments, reaction events, integrity events,
acknowledgements and configured reactions in an order that avoids plugin-owned orphans. Moodle file areas and
grade items are removed through their APIs. Privacy deletion uses its own context/user-scoped operations.

## Required tests for lifecycle changes

- fresh install and validator;
- upgrade from the oldest supported real data baseline and from the immediately previous release;
- interrupted/retried upgrade where relevant;
- backup/restore with and without user data, linked Forum and uploaded files;
- reset, activity deletion, gradebook and completion consistency;
- Privacy API export/delete and scheduled retention after restore.
