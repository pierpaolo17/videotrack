# Videotrack 1.4.123 - sendBeacon review

## Scope

This note records the strict-review follow-up for the unload beacon path used by
Videotrack. The review focuses on whether a fallback should be added when
`navigator.sendBeacon()` is unavailable or refuses a payload.

## Current behaviour

The player uses the beacon path only as a last-chance persistence mechanism
during page lifecycle transitions where normal asynchronous requests are not
reliable. Regular playback progress is still saved by the heartbeat and segment
save flow while the page is active.

The helper returns `false` when:

- the browser does not expose `navigator.sendBeacon()`;
- the configured beacon URL is not considered safe by the shared URL guard;
- the segment arguments cannot be built;
- the JSON payload exceeds the conservative beacon payload limit;
- the browser refuses to queue the beacon;
- an exception is raised while preparing or queueing the beacon.

Those cases are intentionally treated as non-fatal. Throwing during unload would
not improve persistence and could interfere with the browser lifecycle.

## Fallback assessment

An automatic AJAX fallback from `beforeunload` or late `pagehide` was not added
in this release. At that point in the lifecycle, promise-based Moodle AJAX calls
are not guaranteed to complete, and a retry queue could change the tracking
semantics by replaying a segment after the page context that produced it has
already gone away.

A more aggressive fallback would need to prove all of the following before being
accepted:

- it does not duplicate a segment already saved by the heartbeat path;
- it preserves the existing clamping and save-reason semantics;
- it does not extend the unload handler with blocking work;
- it does not store tracking payloads beyond the current Moodle session context;
- it remains safe when multiple tabs or embedded players are active.

## Decision for 1.4.123

No runtime change is introduced. The current behaviour is documented as an
intentional conservative policy: sendBeacon is a best-effort final persistence
path, not the primary tracking transport.

If future testing identifies measurable data loss in browsers without reliable
beacon support, the candidate remediation should be designed around an earlier
`visibilitychange` save while the document is still active, rather than a late
unload-time AJAX fallback.
