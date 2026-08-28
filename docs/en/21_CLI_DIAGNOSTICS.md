# CLI validation and Analytics benchmark

VideoTrack ships two read-only CLI tools. Run them from the Moodle root as the same operating-system user normally
used for Moodle CLI maintenance.

## Installation/release validator

```bash
php mod/videotrack/cli/validate.php
php mod/videotrack/cli/validate.php --json
```

The validator checks:

- installed plugin version against `version.php`;
- supported Moodle branch;
- installed database schema against `db/install.xml`;
- AJAX service declarations and external methods;
- key/placeholder parity across eight language packs;
- AMD source/build/source-map correspondence;
- README and changelog release markers;
- selected privacy, performance and tracking configuration.

Exit status is non-zero when a check fails. Warnings require review even when no failure is present. The tool does
not install, upgrade, write settings or repair data.

## Course Analytics benchmark

```bash
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> --json
```

`courseid` selects an existing course. `userid` must identify an authorised user whose Moodle scope is used by the
real Analytics query. The benchmark runs the production batched aggregation for all-time and period views, with
single/all activity variants. It reports wall time, database reads/query count/query time, returned rows and an
all-versus-naive ratio where available.

It is read-only but can be expensive on a large course. Run it during an appropriate maintenance window, record
database/cache state and compare like-for-like datasets. Results from one course do not prove worst-case scaling.

## Evidence handling

Archive JSON output with release tests, recording plugin/Moodle/PHP/database versions and critical configuration.
The validator complements PHPCS, lint, PHPUnit, Grunt and Behat; it does not replace them. Re-run the benchmark when
course Analytics SQL, indexes, group/cohort scope or aggregation logic changes.
