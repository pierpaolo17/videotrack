# Build, test and release

## Environment

- Moodle 5.0+
- PHP 8.2+
- Node 22.x according to Moodle requirement
- Moodle Grunt toolchain installed in the Moodle root

## PHPUnit

```bash
cd /var/www/default-ssl/m45/moodle
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
```

PHPUnit 11 docblock `@covers` deprecations are known: they satisfy the Moodle `TestCaseCovers` sniff until moodle-cs fully supports attributes for this plugin use case.

## PHPCS

```bash
/root/.config/composer/vendor/bin/phpcs --standard=moodle-extra /var/www/default-ssl/m45/moodle/mod/videotrack > phpcs_extra.json
```

## AMD

```bash
cd /var/www/default-ssl/m45/moodle
ulimit -n 65535
export UV_THREADPOOL_SIZE=4
export NODE_OPTIONS="--max-old-space-size=4096"
node node_modules/grunt/bin/grunt amd --root=mod/videotrack --force > ../grunt_amd.txt 2>&1
```

## Patches

```bash
git apply --check videotrack-x.y.z.patch
patch -p1 --dry-run < videotrack-x.y.z.patch
```

The patch must be generated from the plugin root. Absolute or temporary paths inside `/mnt/data` are not acceptable.

## Documentation-only release checklist

For documentation-only patches, the minimum checks are:

```bash
php -l version.php
git apply --check <patchfile>
patch -p1 --dry-run < <patchfile>
```

If inventories are changed, regenerate or manually verify:

- file inventory against the real ZIP contents;
- PHP function inventory against shipped PHP files;
- AMD function inventory against `amd/src` files;
- variable inventory as a navigation aid.

A documentation-only patch does not require `grunt amd`, PHPUnit or PHPCS unless it changes executable code. If `version.php` is bumped, run Moodle upgrade in the target environment.

## Forum integration regression checks (1.5.0)

- create and edit an activity with the feature disabled;
- configure each supported Forum type (`general`, `qanda`, `blog`);
- verify hidden and restricted Forum behaviour for teacher and student;
- verify no-groups, separate-groups and multiple-membership posting;
- verify cutoff and posting-threshold failures remain controlled;
- verify cancel returns to the timestamped video fragment;
- verify the published discussion contains only the student-confirmed text and replay link;
- restore with and without the linked Forum and confirm safe disablement when the mapping is absent;
- repeat player regression tests for YouTube, Vimeo and HTML5 tracking, seek, resume and replay.

## Instance analytics regression checks (1.6.0)

- verify that the reaction overlay reports its safety limit without unbounded memory growth;

- verify no data and unknown-duration states;
- verify datasets below and above `analyticsminusers`;
- verify masked timeline bins and reaction clusters below threshold;
- compare unique coverage and repeated time with overlapping segments;
- verify group filtering with permitted and unavailable groups;
- verify accessible SVG descriptions and equivalent table values;
- confirm existing student, cumulative, CSV and completion-recalculation tabs are unchanged;
- run `analytics_test.php`, PHPCS Moodle + Extra and PHP lint. No AMD build is required for this phase.

## Runtime fixes 1.6.1

- Note saving now resolves asynchronous player timestamps and prefers the end of the segment just accepted by the server; this prevents a Promise from being sent by the Vimeo player.
- A Moodle log-event failure no longer turns an already stored note into a failed save; a visible warning is returned instead.
- Reaction clusters apply their own privacy threshold independently from viewing-segment availability or suppression. Privacy-safe clusters remain available in an aggregate table without names or private note text.

## Bookmark release checks

For bookmark changes, add to the normal release matrix: enabled/disabled form persistence, save/delete ownership, already-watched timestamp enforcement, CSV formula-injection protection, Privacy API export/erasure, backup/restore, all three player replay paths, teacher aggregate counts and privacy suppression. Language-key parity must be checked across all eight shipped packs.

## Integrity and focus release checks

Run the three-player runtime matrix in `11_INTEGRITY_AND_FOCUS.md`. Because player AMD sources are modified, a real Moodle `grunt amd` is mandatory and the generated `player`, `html5_player`, `vimeo_player` and `core/player/focus_guard` build files and source maps must be included. Also run PHPUnit, PHPCS Moodle + Extra, XMLDB upgrade/new-install checks, Privacy API export/erasure and backup/restore with and without user data.

## Acknowledgement regression checks (1.6.19)

- Verify new installation and upgrade create `{videotrack_acknowledge}` and the four instance fields.
- Confirm that disabled statements render no learner form.
- Confirm that changing text or format makes the previous confirmation non-current.
- Verify POST without sesskey or without the checkbox is rejected.
- Verify acknowledgement-only and combined completion logic.
- Verify report/CSV, Privacy API, backup/restore, retention and user/course/activity reset.
- Verify both acknowledgement timing policies and confirm that reports preserve the viewed seconds and percentage captured at confirmation time.
- Verify single-activity and cross-course acknowledgement Analytics, group restrictions, legacy snapshot handling and privacy suppression.
- Verify CSV, Excel and ODS contain one acknowledgement-summary row and remain downloadable when only acknowledgement data exists.
