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

## Server URL and timestamp parsing

`videotrack_parse_https_media_url()` is the provider-neutral boundary for submitted external media URLs. It rejects
empty, malformed, non-HTTPS or line-broken values and returns a lowercase host without a trailing dot, a path with
repeated separators collapsed and a string query. `videotrack_extract_videoid()` and
`videotrack_extract_vimeo_id()` then apply separate host/path allowlists. YouTube query values also pass through
`videotrack_normalise_youtube_video_id()`, which accepts only a scalar 11-character provider identifier.

`videotrack_parse_video_timestamp()` accepts numeric seconds or two/three colon-separated digit components. The
colon grammar bounds minutes and seconds to 0–59 while allowing accumulated hours or minutes. Empty and malformed
values return `null`; numeric negatives retain the established clamp to zero. Report filters first require a colon
form through `videotrack_parse_report_timestamp()`.

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

## Playback-rate configuration pipeline

`videotrack_get_playback_speeds()` is the public policy entry point. It delegates four explicit stages: parse and
normalise the configured list, calculate the strictest positive site/activity cap, apply that cap and finally
guarantee normal `1.0` playback. Values must be finite, greater than zero and no greater than `4.0`; duplicates are
removed and the result is sorted. An empty or legacy literal `0` setting uses `0.75,1,1.25,1.5,2`, while a list
containing no valid value falls back to `1.0`. Disabling rate changes bypasses the pipeline and exposes only `1.0`.

Telemetry uses `videotrack_get_tracking_playback_speeds()`, which starts from that learner-visible list and may add
only the configured blocked-seek recovery rate. This keeps internal recovery valid without presenting it as an
ordinary learner choice.

## Verification

- test URL/id parsing and invalid/private inputs;
- test uppercase/trailing-dot hosts, line breaks, non-scalar query values and malformed timestamp components;
- test immediate and delayed duration metadata;
- ensure probes are muted, bounded and destroyed;
- test resume, backward seek, blocked-forward recovery, rate policy and terminal pause separately per provider;
- compare `amd/src` with generated build/map and run a public-provider smoke test when external policy changes.
