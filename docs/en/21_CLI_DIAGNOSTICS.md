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
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id> \
    --activityid=<instanceid> --minusers=5 --runs=10 --perioddays=7 > benchmark.json
```

The benchmark always emits JSON; `--json` belongs to the validator and is not a benchmark option.
Use the installed plugin path; Moodle's `public/` layout may put it under `public/mod/videotrack/`.

`courseid` selects an existing course. `userid` selects an authorised active viewer. Before selecting activities
or measuring queries, the CLI loads that user's complete record and makes them the current in-process Moodle
user. JSON records both `userid` and `current_userid`. This aligns the capability-check context with the selected
viewer; it does not change roles, enrolments, settings or learner data. It remains a CLI measurement, not a browser
page-load measurement.

`activityid` is the VideoTrack instance id, not the course-module id from `view.php?id=...`. If omitted, the tool
prefers the activity with the most state rows. The four scenarios cover all-time/period and single/all activities.
`runs` defaults to5 and allows1..50; `perioddays` defaults to7. Privacy threshold `minusers` defaults independently
to2; pass the site's value explicitly when comparing site behaviour. The example uses5 without changing the
site setting. `groupid` optionally restricts the already-authorised group scope.

Each scenario reports raw runs and min/median/max wall time, database reads/query count/query time, and returned
activity rows. `all_vs_naive_ratio` compares the all-activity reads with the arithmetic estimate of single reads
multiplied by configured activities. Check returned rows against configured activities and dataset density before
interpreting that estimate; it is not a measured run of repeated single calls.

### Scoped Moodle 5.3 evidence and interpretation

On 2026-11-13, the maintainer supplied real baseline 1.7.254 diagnostics: 42 visible activities, 28 synthetic target
states, 28776 target segments and 14388 target segments in the seven-day window. Other activities contributed 21
additional all-time states. The standalone profiler used threshold 5 and 10 runs per scenario. Its public-component
replay matched canonical summaries, event counts and permissions in all 40runs. A controlled change from CLI
current-user 0 to selected-viewer 10 also preserved all 40 outputs, while queries changed9-to-6 for one activity
and218-to-92 for the course. The normal CLI's separate-user context added 126 queries in this course measurement;
no production Analytics optimisation had been applied.

With the viewer current, the course replay used 86 queries to prepare scopes, 3 for event counts, 3 for states or
period segments, and 0 for final row-permission checks. The observed source still resolves two learner SQL scopes
per activity; this evidence does not establish a constant total query count. Reading the single activity's 14388
period segments and rebuilding its summaries remained a separate cost from preparing permissions.

These results validate the diagnostic comparison on the 254 source, not native execution of candidate 255. Re-run
the shipped 255 CLI after upgrade and retain its JSON with the new `current_userid`. The profiler explicitly warms
caches; its replay phases are separate executions, so their median times are not additive parts of the canonical
total. A warm-up or larger first-run latency does not establish a cold-cache measurement.

The benchmark is read-only but can be expensive on a large course. Record database/cache state and host load,
compare like-for-like datasets, and retain raw JSON. This sample is not a fully populated 42-activity worst case,
a concurrent-load test, an accessibility test or an end-to-end player validation. The retained synthetic dataset
can support dashboard screenshots; manual Moodle 5.3 accessibility remains on standby.

## Evidence handling

Archive JSON output with release tests, recording plugin/Moodle/PHP/database versions and critical configuration.
The validator complements PHPCS, lint, PHPUnit, Grunt and Behat; it does not replace them. Re-run the benchmark when
course Analytics SQL, indexes, group/cohort scope or aggregation logic changes.
