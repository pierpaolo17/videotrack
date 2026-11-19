# Authenticated Microsoft Graph metadata diagnostics

VideoTrack **1.7.264** (`2026111901`).

The Graph/OAuth implementation introduced in accepted 1.7.259 is retained.
Release 1.7.261 adds an [administrator web form](32_MICROSOFT_WEB_CHECK.md)
using the same diagnostic. Microsoft playback remains a future gate.


This is the first remote diagnostic for OneDrive/SharePoint preparation. It checks
explicit drive/video metadata using the existing core Microsoft system OAuth account.
It does not make OneDrive/SharePoint selectable in the activity form, deliver a video
to learners, prove streaming/range support or enforce future playback permissions.

## Prepare the core connection

1. Use an HTTPS Moodle site with a working public callback and trusted certificate.
   A site URL using plain HTTP intentionally fails readiness before token refresh.
2. Configure a Microsoft OAuth 2 service in Moodle core, enable it for services,
   connect its system account and select it in VideoTrack's Microsoft service setting.
   Credentials and system-account tokens remain in core; none are copied into VideoTrack.
3. Ensure the connected account can read the target drive/file. Microsoft's least
   delegated permission for these metadata APIs is `Files.Read`. The core service or
   other plugins may require additional scopes. VideoTrack does not add scopes or
   grant consent; after core scope changes, reconnect the account through Moodle.
4. Obtain the explicit `driveId` and, for a video check, the item's `id` from trusted
   Microsoft administration/API tools. A browser/share URL or file path is not an id.
   Identifiers are case-sensitive, 1–256 ASCII letters/digits/`!_-` in this first probe.
   This command does not enumerate drives, libraries, folders, users or shared links.

The public cloud is supported: Graph host `graph.microsoft.com` and OAuth endpoints
`https://login.microsoftonline.com/<tenant>/oauth2/[v2.0/]authorize` and `.../token`.
Custom/national-cloud endpoints, userinfo, query strings, fragments and alternate
ports are refused before core obtains a client. Ordinary incomplete services remain
diagnosable through the existing local status command.

## Run from the Moodle root

Use the server's usual PHP executable and Moodle service account. Single quotes
around ids prevent Bash history expansion of the `!` found in OneDrive drive ids.

```bash
php mod/videotrack/cli/provider_status.php --provider=microsoft --strict
php mod/videotrack/cli/provider_probe.php --help
php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID'
php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID' --itemid='ITEM_ID'
```

`provider_status.php` reads local configuration only. `provider_probe.php` is an
explicit network operation: core may refresh tokens, persist a rotated refresh token
and update its token cache. It is therefore not a database-wide read-only command.
No VideoTrack activity, learner state, segment, reaction, note, bookmark or demo
record is inserted, changed or deleted. There is no scheduled/background probe.

The resource request is one Graph v1.0 metadata GET, with no retry or redirect:
`/drives/{driveId}?$select=id,driveType` or
`/drives/{driveId}/items/{itemId}?$select=id,file,video,size,deleted`.
An explicit drive avoids `GET /me/drive`, which can provision a licensed user's drive.
No `/content` or share-link endpoint is requested. Graph GET verifies TLS, has a
5-second connection timeout and 15-second request timeout. OAuth refresh uses core's
own transport policy. The 1 MiB response limit bounds JSON parsing after receipt;
it is not a streaming body-download limit.

## Interpret the JSON

A successful video result is similar to:

```json
{
    "provider": "microsoft",
    "phase": "graph",
    "status": "video_accessible",
    "graph_attempted": true,
    "oauth_refresh_possible": true,
    "playback_implemented": false,
    "http_status": 200,
    "mime_type": "video/mp4",
    "duration_seconds": 5280.123,
    "size_bytes": 123456
}
```

This is an illustrative result, not evidence of a real tenant test. Microsoft reports
video duration in milliseconds; the probe converts it to seconds without changing
an activity's configured/forced duration. A drive result contains only `drive_type`
(personal, business or documentLibrary) in addition to common status fields.

| Result | Meaning / action |
|---|---|
| `invalid_target` | Missing/unsupported id; configuration and OAuth have not been queried. |
| `not_configured` / `issuer_missing` / `configuration_incomplete` | Repair local selection, service/system-account readiness or site HTTPS. No OAuth refresh or Graph GET occurs. |
| `oauth_endpoints_unsupported` | The configured identity endpoints are outside this probe's supported public-cloud format. |
| `configuration_failed` | Local lookup failed; error details are deliberately omitted. |
| `oauth_unavailable` / `oauth_failed` | Core could not supply an authenticated client. Graph has not been attempted. Reconnect/repair the core account. |
| `drive_accessible` / `video_accessible` | Target metadata was accessible to the system account at this time. Exit 0. |
| `graph_unauthorised` / `graph_forbidden` | HTTP 401/403: review token/consent and the account's resource access. |
| `graph_not_found` | HTTP 404: target may be absent or inaccessible; this is not proof of deletion. |
| `graph_throttled` / `graph_unavailable` | HTTP 429/5xx: retry manually later; no automatic retry occurs. |
| `graph_redirect_refused` | HTTP 3xx: no follow-up request is made to its Location. |
| `graph_transport_failed` / `graph_request_failed` | Curl failure/exception or another unsuccessful HTTP status. |
| `graph_invalid_response` | Missing/unexpected JSON, drive type or identity, excessive depth or parsing-size limit. |
| `graph_item_deleted` / `graph_not_video` / `graph_video_metadata_incomplete` | Deleted item, non-video/folder, or missing/invalid integer duration/size. |

Every unsuccessful diagnostic exits 2. Unknown options also exit 2 without echoing their
values. `--help` exits 0 without running the probe. Status output discards file/drive names,
resource ids, owner/user identities, web/download URLs, response bodies and exception text.
Never paste raw tokens or provider error bodies into issue reports.

## Validation and next gate

The source adds 19 mocked PHPUnit regression methods for transport policy, error
classification, redaction, input/endpoint restrictions, readiness and metadata units.
They need native CI/server execution on the candidate; a mock is not real Microsoft
consent or playback evidence. No real tenant request has been executed here.

After native acceptance, an administrator can run the explicit probe against a chosen
test drive and video. Keep the safe JSON with the release evidence; no credentials are
needed in reports. Full playback still requires a separate activity/user access design,
expiry/refresh handling, range delivery, seek/completion checks and browser testing.

Primary references:
- [Get drive](https://learn.microsoft.com/en-us/graph/api/drive-get?view=graph-rest-1.0)
- [Get driveItem](https://learn.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0)
- [Video resource and millisecond duration](https://learn.microsoft.com/en-us/graph/api/resources/video?view=graph-rest-1.0)
- [Moodle 5.0 OAuth API used for the accepted branch](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/api.php)
- [Moodle 5.3 OAuth API used for the accepted branch](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/classes/oauth2/api.php)

The web form also offers [current-user diagnostics](33_MICROSOFT_USER_ACCESS.md).
The CLI remains system-account-only; no account-context CLI flag is introduced.

The [provider-page action](34_MICROSOFT_PROVIDER_PAGE.md) separately selects webUrl.
Existing system/user metadata diagnostics and system-only CLI output omit that URL.
