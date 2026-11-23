# Private Microsoft diagnostic preview

VideoTrack **1.7.269** (`2026112300`).

## Purpose and access

The Microsoft connection check now offers **Preview with Microsoft player**. It is
an administrator diagnostic using the current user's connected Microsoft work or
school account, for SharePoint/OneDrive for Business. It is not a selectable activity
source or a tracked student player. A preview address can carry the caller's access;
keep it private and do not put it in activity URLs, teaching materials or issue reports.

The page requires system `moodle/site:config`. The preview form requires POST,
sesskey, **My connected Microsoft account**, drive id and video item id. It never
uses the system account as a substitute. Opening the diagnostic normally remains
local and produces no Graph request or preview frame.

## Procedure

1. Configure HTTPS and the existing Microsoft core OAuth service, then select it
   in VideoTrack. Connect your own account using the [connection action](35_MICROSOFT_USER_CONNECTION.md).
   Core owns credentials, configured scopes and refresh. The documented least
   delegated permission is `Files.Read`; VideoTrack adds no scopes or access grants.
2. Open **Site administration → Plugins → Activity modules → VideoTrack → Microsoft
   connection check**. Select **Preview with Microsoft player** and your connected
   account; supply explicit drive/video ids, not sharing URLs or paths.
3. Submit. A successful result means a supported temporary preview address was
   returned, not that the browser has successfully loaded or played the video.
   Use the embedded provider controls to check the actual experience.
4. If the temporary address expires, submit the form again with the same ids.
   Every preview request obtains a fresh address; nothing is refreshed in the
   background. The original-page action remains available separately.

Microsoft documents preview support for SharePoint/OneDrive for Business, not
consumer Microsoft accounts. Tenant rules, browser cookies, frame policy and
sandbox restrictions can still prevent playback or sign-in. A real tenant/browser
test is pending. Do not interpret a blank or blocked frame as permission to fall
back to a shared identity, a download or a Moodle streaming proxy.

## Request and credential boundaries

The current-user guard checks capability, target ids, local readiness, supported
identity endpoints and login before Graph. Existing metadata and original-page
actions keep their public result contracts. Shared execution dispatch uses an
internal action selected by a public service method, never an arbitrary request method.

Preview first performs the existing video metadata GET, requiring a non-deleted
video with usable duration/size. Only success permits one JSON POST with body `{}`
to `https://graph.microsoft.com/v1.0/drives/{driveId}/items/{itemId}/preview`, using
the same ids and authenticated client. There are no Graph retries, redirects,
content requests, sharing grants or drive enumeration. Each Graph request keeps
the accepted TLS checks and5second connection/15second request timeout. Core OAuth
refresh uses its own transport policy and can update core credentials.

Preview JSON parsing is bounded at1MiB and depth16 after receipt; this is not a
streaming response-size cap. Only `getUrl` is supported. A POST-only provider
preview yields `preview_get_unavailable`, without submitting its form or parameters.
No response body, provider error, token or URL is copied into diagnostic JSON.

The private result accepts HTTPS embed URLs up to8192bytes, without URI credentials,
fragment, whitespace, control characters, backslashes or non443ports. Supported
destinations are tenant `*.sharepoint.com` paths ending `/embed.aspx`, or exactly
`www.onedrive.com/embed`. Other origins/path shapes yield `preview_url_unsupported`.
This intentionally bounded first implementation does not promise every future
Microsoft preview URL shape. Opaque accepted URL bytes and query order are retained;
credential-like query parameters are permitted only in this private preview result.
The original-page result keeps its existing bearer-query rejection.

The URL appears only in the requesting administrator's iframe response. The page
remains non-cacheable with no-referrer policy. The iframe uses escaped attributes,
a descriptive title, responsive width and a sandbox allowing scripts, its own
origin, forms and popups; top navigation is not granted. No Graph access token is
manually sent to browser code. No preview URL is stored in VideoTrack or core settings,
session markers, exports or logs by this implementation. Browser/provider traffic
and server logging are not claimed to disappear. JSON serialisation of the private
result returns only allowlisted diagnostics, not its URL.

The Privacy API declares Microsoft diagnostic/browser transfers; see the
[privacy summary](../../PRIVACY.md). Core continues to manage OAuth credentials.

## Preview authentication transition

Microsoft announced preview URL authentication changes starting **1April2027**.
Returned URLs can require an audience-correct Entra Authorization header. This
iframe implementation supplies no such header. It renders only currently recognised
credential-bearing URLs; other returned addresses produce
`preview_authentication_unverified` with no frame. A recognised credential key
does not prove the credential works. This diagnostic does not implement or certify
the new authentication model; use the original Microsoft page for those cases.

Recognition checks non-empty `tempauth`, `access_token`, `authkey`, `token`, `sig`
or `signature` query values without rewriting the URL or exposing their values.
Unknown or empty forms are not assumed to be anonymously redeemable. New
audience-correct browser authentication remains an explicit integration gate.

## Status and remaining gates

| Status | Meaning |
|---|---|
| `preview_ready` | A supported temporary GET URL is available; browser playback remains to be checked. |
| `preview_authentication_unverified` | Supported destination, but its authentication is not recognised; no iframe is rendered. |
| `preview_get_unavailable` | The provider returned no usable GET preview; POST-only previews are unsupported. |
| `preview_url_unsupported` | The returned URL fails the bounded destination/syntax checks. |
| Existing configuration/OAuth/Graph errors | Repair the selected account/resource/service; there is no system fallback. |

`preview_attempted` indicates whether the Graph POST was attempted. The retained
`playback_implemented: false` refers to VideoTrack's tracked Microsoft playback.
The frame adds no segment, progress, completion, grade or interaction evidence.
Production Analytics, the demo,48AMD sets,9AJAX endpoints and XMLDB are retained.

Thirty new native unit methods cover URI boundaries, redaction, metadata-before-
preview transport, HTTP/parse/POST-only failures, current-user permissions, no shared
fallback, data preservation, form/CSRF and controller contracts. The previous
original-page controller test now checks its own link-rendering branch, preserving
that branch's no-iframe contract. Three new Behat scenarios exercise local GET,
form rejection and missing configuration with no real provider traffic. They do
not prove a successful live iframe. Native266 candidate/main/server checks are accepted; live tenant/browser success is unverified.

Next gates are the real delegated browser/preview test, stable activity references
and launch resolution, and supported provider time/play/pause/seek telemetry before
learner tracking. Microsoft remains first, followed by Zoom, Google Drive and App.
Manual5.3 accessibility remains on standby; retain the demo dataset.

## Primary references

- [Graph v1.0 preview API, supported accounts and caller identity](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
- [Opaque itemPreviewInfo URLs and GET/POST alternatives](https://learn.microsoft.com/en-us/graph/api/resources/itempreviewinfo?view=graph-rest-1.0)
- [SharePoint Embedded example and preview security guidance](https://learn.microsoft.com/en-us/sharepoint/dev/embedded/build/preview-files) — the example is Embedded-specific; no Embedded container provisioning is added here.
- [Pinned50 core curl POST](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/filelib.php)
- [Pinned53 core curl POST](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/filelib.php)

- [Microsoft preview-auth transition announcement,8October2026](https://devblogs.microsoft.com/microsoft365dev/pre-authenticated-tempauth-urls-are-retiring-move-to-microsoft-entra-id-tokens-before-april-1-2027/)

Release267 can [reuse a stable diagnostic identity](38_MICROSOFT_VIDEO_REFERENCES.md)
to request fresh results. Activity persistence and learner launch resolution remain later gates.
