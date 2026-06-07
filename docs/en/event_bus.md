# Videotrack Event Bus

The internal event bus accepts only small provider-neutral event names matching:

```text
^[a-z0-9:_-]{1,100}$
```

The pattern is deliberately limited to ASCII identifiers and separators. It is
not intended as a public extension API.

## Valid event families

Current Videotrack modules should use only these event families:

- `player:*` for player shell lifecycle and UI updates;
- `tracker:*` for tracking lifecycle notifications;
- `notes:*` for personal-note UI events;
- `reactions:*` for reaction UI events;
- `status:*` for non-blocking status announcements.

New event families must be documented here before use.


## Supported events

Current event names are internal and must stay provider-neutral. The supported
namespaces are:

| Namespace | Purpose | Examples |
| --- | --- | --- |
| `player:*` | Player shell lifecycle and UI updates. | `player:ready`, `player:state` |
| `tracker:*` | Segment and heartbeat lifecycle notifications. | `tracker:start`, `tracker:stop` |
| `notes:*` | Personal-note UI events. | `notes:open`, `notes:changed` |
| `reactions:*` | Reaction button and persistence events. | `reactions:ready`, `reactions:saved` |
| `status:*` | Non-blocking status announcements. | `status:message`, `status:error` |

Provider names may be appended only as short suffixes when the event still
represents the same internal action, for example `player:ready:youtube`.
Free-form user input, selectors, URLs and translated strings must never be used
as event names.

## Review note

The pattern is permissive enough to support provider-specific suffixes while
still rejecting whitespace, markup and long arbitrary strings. Event handlers are
also capped internally to avoid accidental unbounded fan-out.
