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
node tests/bug_report_1391_static_test.js
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

## 1.3.88

Review follow-up after the 1.3.87 static audit: restored the review-fix static gate, completed contextual export accessibility labels, aligned CSV hardening, improved visible average-coverage wording, and widened stable static gates for the maintenance line.

## 1.3.90

Bug-report follow-up after the 1.3.89 audit: modal API deprecation cleanup, per-instance status timers, progress fallback locale handling, preset table accessibility, invalid-method hardening, and report badge ARIA cleanup. The version number remains monotonic with the existing 1.3 maintenance line; do not downgrade Moodle version numbers when normalising future schemes.

## 1.3.91

Bug-report validation after the 1.3.90 audit: the reported syntax-corruption items were checked against the real AMD sources and were already fixed in the committed code. Confirmed maintenance improvements add ARIA live-region relevance metadata, less aggressive transient retry backoff, note-input counter debounce, stronger sendBeacon diagnostics, and a Web-Crypto-first session fallback without Math.random.

## 1.3.92

Bug-report follow-up after the 1.3.91 audit: legacy session fallback now always satisfies server validation, status messages use scoped containers without duplicate DOM ids or redundant live-region attributes, retry jitter is wider for classroom reconnection scenarios, completion refresh can reuse precomputed reaction summaries, stable static gates no longer hard-code 1.3.8x/9x micro releases, and preset table captions are translated in non-English language packs.
