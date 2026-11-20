# Microsoft check from site administration

Release 1.7.264 adds [Connect my Microsoft account](35_MICROSOFT_USER_CONNECTION.md)
to the existing administrator page. It starts core OAuth only after an explicit
protected POST; ordinary diagnostics GET remains local. Native264/live tenant pending.

Release 1.7.263 also offers an explicit [original Microsoft page action](34_MICROSOFT_PROVIDER_PAGE.md).
It requires the personal account and a video id. The provider page opens separately;
VideoTrack does not credit viewing in that browser tab.

Release 1.7.262 adds an explicit account selector: the existing system account
or the administrator’s own connected Microsoft account. Missing personal access
never selects the shared account. [Setup and limits](33_MICROSOFT_USER_ACCESS.md).

VideoTrack **1.7.264** (`2026111901`).

## Open and configure

From **Site administration → Plugins → Activity modules → VideoTrack**, save the
selected **Microsoft OAuth service**, then follow **Microsoft connection check**.
The equivalent route is `/mod/videotrack/provider_check.php`. Only users with
`moodle/site:config` in system context can open or submit this page. A course
teacher's editing permission does not grant access.

Opening the page reads local readiness, with no OAuth client creation or Microsoft
request. The diagnostic JSON shows site HTTPS, service type/enabled/service usage,
client setup, endpoint HTTPS and stored system-account checks. A complete local
configuration does not prove current token validity or remote resource access.
An HTTP test site will show `site_https: false` and cannot run the remote check.

Follow [the existing Microsoft setup guide](30_MICROSOFT_GRAPH.md) for core OAuth,
scopes, account access and supported public-cloud endpoints. Credentials remain
in Moodle core. There are no new VideoTrack secrets or cloud-player switches.

## Check explicit metadata

1. Enter an explicit **Drive ID**. Optionally supply **Video item ID (optional)**
   to inspect a video within that drive; leaving it empty checks the drive only.
2. Use case-sensitive opaque ids obtained from trusted Microsoft administration/API
   tools. IDs accept 1–256 ASCII letters, digits, `!`, `_` or `-`. They are not sharing
   URLs, paths, email addresses or numeric Moodle ids. This page does not browse or
   enumerate the tenant. Surrounding whitespace is trimmed; other invalid characters
   cause a field error rather than being silently removed.
3. Press **Check Microsoft access**. This is an explicit POST with Moodle's sesskey
   protection. GET query parameters cannot submit it. Invalid fields are rejected
   before the probe. With incomplete setup, the probe returns a local configuration
   status without attempting Graph.
4. Read the result. `drive_accessible` or `video_accessible` means metadata was read
   using the **configured system account**. It does not prove an individual learner
   is authorised. Other statuses, including missing/forbidden/throttled resources,
   use the [CLI diagnostic definitions](30_MICROSOFT_GRAPH.md).

The web page reuses the exact existing probe: core can refresh and persist OAuth
credentials, followed by one bounded metadata GET. It never requests file content,
imports video, streams via Moodle, starts background work or changes VideoTrack
activity/demo records. No automatic retry is performed. Browser POST resubmission
can explicitly repeat the check. The page is non-cacheable; results are shown only
in the response and are not saved by VideoTrack.

Output is escaped and uses the existing redacted diagnostic contract: status,
phase, HTTP code and safe MIME/size/duration/drive type, without file names, resource
ids, private URLs, tokens or raw provider errors. The submitted fields remain
visible to the administrator in the form. The local configuration includes the
Moodle OAuth service id; it contains no client secrets or account tokens.

## Scope and validation

This is a metadata diagnostic, **not Microsoft video playback**. No source is added
to the activity selector. Learner authorisation, short-lived link renewal and a
supported player bridge for time/play/pause/seek must still be implemented and
validated before watched progress can be credited. Zoom and Google remain later
gates; the optional Zoom repository is still an architectural assessment.

The diagnostic foundation through262 is accepted (candidate/main all6CI, server
Unit488/5267 and both strict10/0/0). Native263 is pending; see the
[documentation audit](09_DOCUMENTATION_AUDIT.md). No connected tenant or official-player
telemetry is claimed. Retain the demo; manual53 accessibility remains on standby.
