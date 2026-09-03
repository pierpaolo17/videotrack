# Media providers and duration

VideoTrack supports uploaded/HTML5 media, YouTube and Vimeo through separate adapters. Shared behaviour is
coordinated by the browser core; provider-specific SDK timing stays inside each adapter.

## Source selection

| Source | Stored identifiers | Runtime dependency | Duration proposal |
|---|---|---|---|
| Upload | Moodle file area and normalised media identifier | Browser HTMLMediaElement | `loadedmetadata` from the selected local file. |
| YouTube | URL and extracted video id | YouTube IFrame API | Hidden muted probe polls `getDuration()` until metadata or timeout. |
| Vimeo | URL and extracted numeric id | Vimeo Player SDK | `getDuration()` promise within the shared timeout. |

The detector runs only in the teacher form. It does not write learner tracking data, does not make provider
metadata authoritative by itself and destroys temporary players/timers after success, error or timeout.

Localised detector configuration is serialised in an `application/json` DOM element. The AMD bootstrap receives
only its element id, parses the JSON and installs source-specific listeners. This avoids oversized `js_call_amd()`
arguments and keeps text/URLs safely encoded.

## Authoritative duration

The saved `durationseconds` is the denominator for watched percentage and completion. The teacher must verify the
proposal. Manual input is required when provider privacy settings, consent, network policy, deleted/private media
or browser metadata prevent detection. Saving `0` explicitly disables percentage calculation. The adjacent live
status converts the current value to `HH:MM:SS`; it is informational and does not introduce a second stored value.

## Adapter contract

Each adapter exposes play/pause, current time, duration, seek, playback rate and lifecycle events needed by the
shared tracker. Resume and replay seek only after provider readiness. Blocked forward recovery captures the
pre-seek position and must not re-seek during playback retry.

HTML5 provides the strongest local control. YouTube and Vimeo remain subject to their SDKs, iframe policies and
public service availability. Provider loader promises reset after failure so a later attempt can recover.

## Verification

- test URL/id parsing and invalid/private inputs;
- test immediate and delayed duration metadata;
- ensure probes are muted, bounded and destroyed;
- test resume, backward seek, blocked-forward recovery, rate policy and terminal pause separately per provider;
- compare `amd/src` with generated build/map and run a public-provider smoke test when external policy changes.
