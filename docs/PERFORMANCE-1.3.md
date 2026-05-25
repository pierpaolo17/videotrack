# mod_videotrack 1.3 performance review

This note documents the conservative performance checks introduced during the
1.3 release-preparation phase. It is intentionally focused on static and smoke
checks that can run outside a Moodle site.

## Scope

The 1.3 refactor moved repeated provider logic into shared AMD modules. The
performance review keeps that direction measurable by checking that provider
entry points do not reintroduce direct AJAX calls, duplicated timers or heavy
browser work outside the core modules.

## Current expectations

- Network access remains centralised in `amd/src/core/api.js`.
- Heartbeat and segment lifecycle remain centralised in `amd/src/core/tracker.js`.
- Provider modules use shared core helpers instead of starting independent
  request/retry implementations.
- AMD source files stay paired with committed build files.
- Release-preparation tests remain runnable with plain Node.js where possible.

## Manual checks still required

Static checks cannot prove runtime performance in Moodle. Before a release
candidate, manually test at least:

- YouTube playback with seek, pause, resume and page unload.
- HTML5 playback with long videos and repeated seeks.
- Vimeo playback with slow network conditions.
- Browser tab background/foreground transitions.
- Mobile browser pause/resume behaviour.

## Related test

Run:

```bash
node tests/performance_static_test.js
```

This complements the AMD smoke tests and the tracker/adapter focused tests.
