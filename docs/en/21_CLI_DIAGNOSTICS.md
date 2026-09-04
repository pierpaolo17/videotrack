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
- installed tables, fields, explicit indexes and foreign-key backing indexes against `db/install.xml`;
- orphan detection for all 22 declared stable relations;
- consistency of each denormalised `courseid`/`cmid` with the parent activity and the VideoTrack course module;
- conditional Forum and reaction references, respecting their legitimate `0` sentinels;
- VideoTrack grade-item count, active grade-row count, canonical item uniqueness and resolvable activity/course-module
  contexts;
- AJAX service declarations and external methods;
- key/placeholder parity across eight language packs;
- AMD source/build/source-map correspondence, including non-empty mappings and embedded-source identity;
- README and changelog release markers;
- selected privacy, performance and tracking configuration.

Exit status is non-zero when a check fails. Warnings require review even when no failure is present. The tool does
not install, upgrade, write settings or repair data.

Use `--strict` for release evidence. The JSON `xmldb` detail separates explicit index and foreign-key counts and
lists missing objects or orphan counts. `reference_integrity` reports semantic context and conditional-reference
findings. `gradebook_integrity` reports invalid contexts and duplicate canonical items; it must pass before plugin
uninstall. A failure must be investigated against a database backup; the validator deliberately has no repair mode.

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
