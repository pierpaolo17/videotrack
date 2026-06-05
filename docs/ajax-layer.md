# Videotrack AJAX Layer

## Purpose

Videotrack tracks pedagogically significant viewing activity: watched segments,
heartbeat saves, notes, reactions and resume state. The AJAX layer is modular so
these operations share the same validation, retry, timeout and stale-response
rules without duplicating logic in each player provider.


## Design goals

The layer is intentionally defensive and conservative. Its goals are to:

- reject malformed browser payloads before they enter retry handling;
- avoid retrying logical failures such as permission or validation errors;
- retry only transient failures such as timeouts, network interruption or server-side transient errors;
- prevent stale asynchronous responses from overwriting newer player state;
- keep progress, notes and reaction saves consistent across long-running playback sessions.

These checks do not replace Moodle external-function validation. They reduce noisy client-side failure modes before the authoritative server-side checks run.

## Modules

### `core/api.js`

Public facade used by the player modules. It preserves the historical public API
and delegates implementation details to smaller modules.

### `core/api/validator.js`

Performs defensive client-side validation before `core/ajax` is called. Server
`PARAM_*` validation remains authoritative; client validation only prevents
malformed or oversized browser payloads from entering retry paths.

Current safety bounds are intentionally conservative:

- maximum object depth: 4;
- maximum array length: 100;
- maximum object keys: 50;
- maximum string argument length: 10000 characters;
- maximum payload size: 65536 bytes, unless a caller supplies a stricter limit.

These values are not teacher-facing configuration because changing them can
increase server load or allow accidentally oversized tracking payloads. They are
kept as code-level safety rails and documented here for Moodle HQ review.

### `core/api/transport.js`

Wraps `core/ajax` and applies request timeouts. This keeps transport behaviour
separate from validation and retry decisions.

### `core/api/retry.js`

Normalises retry counts and adds a small jittered delay for transient failures.
The jitter avoids multiple browser tabs retrying at exactly the same moment after
a short network interruption.

### `core/api/error.js`

Classifies AJAX failures into validation, network, server and unknown categories.
Only transient categories are retried.

### `core/api/scope.js`

Provides token-based stale-response protection. When a newer request supersedes
an older one, the older continuation resolves without overwriting newer state.

## Why this is not reduced to direct `Ajax.call()` usage

Direct calls would be smaller but would duplicate:

- payload validation;
- transient retry handling;
- browser offline handling;
- timeout handling;
- stale continuation guards;
- diagnostic logging.

Centralising this behaviour reduces inconsistent tracking behaviour across
YouTube, HTML5 and Vimeo providers.


## Request flow

```text
player module
  -> core/api.js public facade
  -> api/validator.js method and payload safety checks
  -> api/scope.js request token creation
  -> api/transport.js core/ajax call with timeout
  -> api/retry.js transient retry decision
  -> api/error.js failure classification
  -> caller-specific progress/status handling
```

The flow is deliberately linear: validation happens before transport, stale
response protection is attached before the first request attempt, and retry
decisions are made only after Moodle/core/ajax returns a classified failure.

## Example: watched segment save

A concrete player closes a segment on pause, seek, heartbeat or visibility
change. It calls `Api.saveSegment()` with provider-neutral times. The API layer
clamps the segment, validates required Moodle arguments, sends the request and
returns either the server progress payload or `null` when a configured
last-chance/handled failure is intentionally swallowed.

## Rationale for conservative validator limits

The limits are code-level guard rails rather than administrator settings. Making
them configurable would increase review surface and could let a local setting
turn a small tracking request into a large retrying payload. The server remains
the trust boundary; the browser checks are only a cheap early rejection step.

## SendBeacon fallback policy

`navigator.sendBeacon()` is used only during unload/visibility transitions where
normal promises are unreliable. If the browser cannot accept the beacon, the
function returns `false` to the caller. Normal heartbeat and lifecycle saves
remain active during regular playback, so the beacon helper is a last-chance
persistence path rather than the primary tracking mechanism.

## Review assessment after proposed 1.4.124 patch

A proposed follow-up patch suggested adding a second unlimited-retention confirmation setting, replacing this document with a new AJAX overview and introducing browser JavaScript tests. Only the documentation clarifications were accepted for this release.

The extra retention confirmation was not added because the plugin already contains the reviewed global confirmation setting and the associated warning. Adding a separate activity-form validation field would mix global retention policy with per-activity configuration and would not match the existing form fields.

The JavaScript test examples were not added because they were not accompanied by a Moodle JavaScript test harness configuration. Adding unregistered test files would create apparent coverage without a verifiable execution path, which conflicts with the project rule that reports must distinguish executed checks from future candidates.
