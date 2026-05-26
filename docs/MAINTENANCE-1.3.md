# Videotrack 1.3 Maintenance Notes

This document records the post-stable maintenance checks for the 1.3.x line.

## Scope

The 1.3.84 maintenance checkpoint is intentionally conservative. It does not change database schema, capabilities, privacy API, backup/restore mappings, or runtime player behaviour.

## Maintainer checklist

Before packaging a maintenance release, run the static test suite from the plugin root:

```bash
node tests/smoke_amd.js
node tests/tracker_segment_test.js
node tests/adapter_test.js
node tests/backup_restore_static_test.js
node tests/privacy_static_test.js
node tests/deprecation_static_test.js
node tests/performance_static_test.js
node tests/release_candidate_static_test.js
node tests/rc_freeze_static_test.js
node tests/rc2_freeze_static_test.js
node tests/rc3_freeze_static_test.js
node tests/final_static_test.js
node tests/maintenance_static_test.js
node tests/postrelease_static_test.js
node tests/bug_report_1391_static_test.js
node tests/bug_report_1392_static_test.js
node tests/bug_report_1393_static_test.js
```

Also run syntax checks for committed JavaScript and PHP files.

## Stable branch expectations

- Keep `MATURITY_STABLE` unless a later development cycle explicitly reopens the branch.
- Keep committed AMD build files aligned with `amd/src`.
- Prefer small patches with isolated documentation, tests, or low-risk fixes.
- Do not introduce schema changes without a dedicated upgrade review.
- Do not change privacy or backup/restore behaviour without dedicated tests.

## 1.3.86

Maintenance hardening after the 1.3.85 static review. The package remains MATURITY_STABLE and must be verified with node tests/maintenance_static_test.js and node tests/postrelease_static_test.js.

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

## 1.3.93

Bug-report follow-up after the 1.3.92 audit: restored the missing 1.3.91 regression gate, added the 1.3.92 regression gate, reduced duplicate ARIA live-region metadata, documented status container targeting, clarified legacy session identifiers, and hardened privacy interval exports. AMD build files are committed with the source changes.

## 1.3.94

Bug-report follow-up after the 1.3.93 audit: restored packaged bug-report regression tests, removed duplicate maintenance test gates, optimised delete-reaction completion refresh inputs, clarified privacy export interval semantics, replaced retry Math.random jitter with a Web-Crypto-first helper, and completed AMD build synchronisation. The long Moodle version integer remains monotonic; future major branches should adopt a conventional YYYYMMDDNN scheme without decrementing this stable line.
