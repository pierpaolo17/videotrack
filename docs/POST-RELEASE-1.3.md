# Videotrack 1.3 post-release maintenance notes

This document records the low-risk checks that should remain in place after the
1.3 stable release line has been opened.

## Scope

The post-release maintenance line must remain conservative:

- keep `MATURITY_STABLE` in `version.php`;
- avoid schema changes unless a dedicated upgrade step is reviewed;
- keep committed AMD build files aligned with `amd/src` files;
- keep smoke/static tests runnable with plain Node.js where possible;
- document any manual Moodle runtime check that cannot be executed statically.

## Recommended local checks

From the plugin root:

```bash
node tests/smoke_amd.js
node tests/tracker_segment_test.js
node tests/adapter_test.js
node tests/backup_restore_static_test.js
node tests/privacy_static_test.js
node tests/deprecation_static_test.js
node tests/performance_static_test.js
node tests/release_candidate_static_test.js
node tests/final_static_test.js
node tests/stable_release_static_test.js
node tests/maintenance_static_test.js
node tests/postrelease_static_test.js
```

Also run syntax checks on all PHP and AMD JavaScript files before packaging.

## Manual checks still required

Static checks do not replace Moodle runtime validation. Before tagging a public
maintenance release, manually verify at least:

- module creation and update in a Moodle course;
- YouTube, Vimeo and HTML5 playback tracking;
- progress persistence after pause, seek, reload and completion;
- backup and restore of a course containing a Videotrack activity;
- privacy export/delete flows where the Moodle environment supports them.

## 1.3.86 maintenance hardening

This maintenance checkpoint resolves static review findings on video source validation, course report aggregation, grade controls accessibility and notes export confirmation. Manual Moodle runtime, theme and assistive-technology checks are still required.

## 1.3.87

Maintenance review fixes after the 1.3.86 audit: language-string completion, export hardening, accessible status dismissal, course-report visibility filtering, and safer subtitle serving. Static regression gate: `node tests/review_fixes_static_test.js`.
