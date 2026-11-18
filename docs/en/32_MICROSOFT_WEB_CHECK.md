# Microsoft check from site administration

VideoTrack **1.7.261** (`2026111801`).

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

Nine native unit methods cover form identifiers, POST/GET behaviour, sesskey,
administration-node access and controller guard ordering. Three browser scenarios
cover local status, invalid URLs and incomplete configuration without live network.
The initial six-job 261 CI passes these regressions (Unit472/5183, Behat61/1268).
The following run clears PHPStan/Psalm, but needs a PHPDoc correction and a fresh
CI run after the 5.0/MariaDB HTTP service became unavailable. See the
[documentation audit](09_DOCUMENTATION_AUDIT.md) for the exact evidence. No connected
Microsoft tenant or official-player telemetry has been tested in this release.
Retain the demo; the separate manual Moodle 5.3 accessibility check stays on standby.
