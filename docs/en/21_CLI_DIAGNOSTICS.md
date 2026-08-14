# CLI validation and Analytics benchmark

VideoTrack distributes two read-only CLI tools for local/staging administration and future maintenance work. They live under `mod/videotrack/cli/` and load the normal Moodle configuration, so run them from a Moodle installation where the plugin is installed.

## Safety model

Both commands are diagnostic only. They do not insert, update or delete Moodle records and they do not provide a repair mode. Run them with a web-server account that can read the Moodle code/configuration and connect to the configured database. Keep their output with the release evidence when using them for deployment or performance comparison.

## Installation/release validator

```bash
php mod/videotrack/cli/validate.php
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/validate.php --verbose
php mod/videotrack/cli/validate.php --strict
```

The validator checks:

- file release/version against the installed `config_plugins` version;
- current Moodle branch against `$plugin->supported`;
- tables, fields and indexes declared by `db/install.xml` against the real database;
- AJAX functions declared by `db/services.php` and their external classes/methods;
- maintained language-pack key/placeholder parity;
- every AMD source against its minified build and source-map `sourcesContent`;
- current README/CHANGELOG public release markers;
- selected privacy, tracking and performance configuration values.

Exit status is `0` when there are no failures. Warnings do not fail the command unless `--strict` is used. `--json` is recommended for archived release evidence.

This validator complements, but does not replace, PHPUnit, PHPCS, Grunt, browser runtime tests, Privacy API checks or backup/restore tests.

## Course Analytics benchmark

```bash
php mod/videotrack/cli/benchmark_course_analytics.php \
    --courseid=4 \
    --userid=2 \
    --minusers=2 \
    --runs=5 \
    --perioddays=7
```

Optional parameters:

- `--activityid=<id>` chooses the VideoTrack instance used by the two single-activity scenarios. If omitted, the command prefers the activity with the most `videotrack_state` rows and then the lowest id.
- `--groupid=<id>` applies the same accessible-group filter used by the course dashboard.
- `--minusers=<n>` sets the Analytics privacy threshold for the benchmark; minimum `2`.
- `--runs=<n>` controls repetitions per scenario, from `1` to `50`.
- `--perioddays=<n>` controls the recent-period scenarios, from `1` to `3650` days.

The JSON output contains four scenarios:

1. `all_time_single` — one activity, all time;
2. `all_time_all` — all visible VideoTrack activities, all time;
3. `period_single` — one activity inside the requested period;
4. `period_all` — all visible activities inside the requested period.

Each run records returned row count, database reads, total queries, DB execution time and wall-clock time. The `scaling` section compares the real all-activity read count with the naive `single activity × configured activity count` estimate.

The benchmark is comparative evidence, not a universal pass/fail threshold. Dataset density, group/capability scope, DB caches, Moodle caches and host load all affect timings. Record the full JSON together with Moodle/PHP/DB versions and whether all configured activities actually contain learner logs.

## Maintainer benchmark recorded for U-016

A maintainer run on Moodle 5.0.9 / PHP 8.2.32 / MySQL-family DB used a real course with 40 configured VideoTrack activities, not all populated with learner logs, five runs per scenario and a seven-day period. The all-time all-activity scenario used a median 86 reads/queries, 24.602 ms DB time and 37.913 ms wall time; the seven-day all-activity scenario used the same 86 median reads/queries, 29.896 ms DB time and 46.645 ms wall time. The all-vs-naive read ratio was 0.3583 in both comparisons.

This closes the original U-016 real-dataset benchmark gate for the observed production-like course. It does not claim worst-case scaling for a synthetic course where every activity contains a large learner history; such stress fixtures remain useful future evidence, not a blocker for the recorded baseline.

## Recommended maintenance workflow

Before a release, archive `validate.php --json` output alongside PHPUnit/PHPCS/Grunt evidence. Run the course Analytics benchmark when `classes/local/course_analytics.php`, learner scoping, group scoping, Analytics SQL shape or relevant indexes change, and compare results against a previous run on the same dataset/environment whenever possible.
