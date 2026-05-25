# Videotrack 1.3 final static checks

This document records the last Moodle-independent verification checkpoint before
the 1.3 stable tag decision.

## Scope

The `1.3.79` checkpoint is intentionally conservative. It does not introduce new
runtime behaviour, database changes, capability changes or privacy-provider
changes. Its purpose is to make the accumulated release-candidate evidence easy
to verify from the plugin root.

## Static gates

Run the following commands from the plugin root:

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
```

Then run syntax checks:

```bash
find amd/src amd/build tests -name '*.js' -print -exec node --check {} \;
find . -name '*.php' -print -exec php -l {} \;
```

## Manual runtime checks still required

These static checks do not replace a real Moodle validation pass. Before a stable
release, manually verify at least:

- activity creation and editing
- YouTube playback tracking
- Vimeo playback tracking
- HTML5 playback tracking
- heartbeat/progress persistence
- seek behaviour and completion handling
- backup and restore in a course context
- privacy export/delete flows
- accessibility feedback in a browser with keyboard navigation

## Stable release readiness

If the static gates and manual Moodle checks pass, the next planned checkpoint
can become the final stable `1.3.80` package.
