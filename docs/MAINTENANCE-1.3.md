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
```

Also run syntax checks for committed JavaScript and PHP files.

## Stable branch expectations

- Keep `MATURITY_STABLE` unless a later development cycle explicitly reopens the branch.
- Keep committed AMD build files aligned with `amd/src`.
- Prefer small patches with isolated documentation, tests, or low-risk fixes.
- Do not introduce schema changes without a dedicated upgrade review.
- Do not change privacy or backup/restore behaviour without dedicated tests.
