# Connect the administrator's Microsoft account

VideoTrack **1.7.264** (`2026111901`).

## Procedure

Configure the existing core Microsoft OAuth service and select it in VideoTrack.
The Moodle site must use HTTPS. The personal readiness object must be complete;
it does not require a shared system account. Register/use Moodle's existing core
OAuth callback, not a new VideoTrack callback or a new application secret.

Open **Site administration → Plugins → Activity modules → VideoTrack → Microsoft
connection check**, then **Connect my Microsoft account**. The button appears only
when local personal readiness is complete. It submits a separate POST with sesskey;
opening the page never constructs a client or starts authentication.

Core checks the current user's existing connection. If it is usable, no external
redirect is needed. Otherwise the browser goes to the configured Microsoft login/
consent screen and returns through core's callback. The request uses the selected
issuer's configured scopes and core user autorefresh; VideoTrack requests no extra
scopes and never falls back to the system account.

After return, choose **Account to check → My connected Microsoft account**, enter
the drive/item ids and run the metadata or original-page action. The default account
choice remains system; the connection does not automatically submit either probe.
Targets/form inputs are not retained across the external login round trip.

A connection-success notice means core has a usable personal client at that time.
It is not a Graph access check: a particular video can still be denied, missing or
subject to tenant policy. The browser identity used by the original Microsoft page
may differ; see [the provider-page guide](34_MICROSOFT_PROVIDER_PAGE.md).

## Request and return boundary

Only a user with system `moodle/site:config` may begin or complete. Start requires
POST/sesskey and rejects an injected OAuth code. Local readiness and the existing
Microsoft endpoint guard run before core receives the issuer. Core creates the
login URL; critical state/callback/client/response fields and its destination are
checked before redirect, including configured login-parameter overrides.

A private session marker contains exactly user id, issuer id, expiry and a random
64hex nonce. It is usable for ten minutes, tied to the current Moodle session and
passed through core's fixed local return state. A later attempt replaces it. A
callback needs sesskey and a matching marker before any client/token work; a valid
marker is consumed before core processes the response. Wrong, expired, different-user
or replayed requests never invoke the client. The selected service and endpoints
are rechecked on return; a changed issuer cannot receive the prior response.

Provider error text is ignored. A matching denial consumes the marker without token
work; other failures become reduced local outcomes. Notifications show no codes,
nonce, credentials, URLs or exception details. A clean local redirect removes OAuth
response parameters before rendering diagnostics. The page remains non-cacheable
and sets no-referrer policy. Standard core/browser callback traffic still carries
OAuth protocol fields; VideoTrack does not claim to remove web-server access logs.

Core retains its own session/refresh credentials and can refresh, rotate or invalidate
them. The personal connection can also be reused by other Moodle tools using the
same issuer/current user. VideoTrack stores no token copy and never logs in a different
Moodle user. No logout, disconnection or issuer-setting changes are added.

## Scope and validation

This action does not call Graph, import/proxy media, fetch a player, credit viewing,
change grades/completion, or enable a selectable cloud source. Existing diagnostic
JSON, page links, system CLI,9AJAX contracts,48AMD sets and database schema are retained.

Twenty-two new native unit methods cover real core URL construction with fake local
configuration, permission/POST/sesskey/code guards, readiness and endpoint failures,
one-use/user/issuer/expiry matching, denial, replay, protocol overrides and reduced
exceptions. HTTP clients used for completion are mocked; no provider token exchange
is executed by those tests. One new browser scenario has no configured issuer or
external request. All508 prior unit methods and66 scenarios remain byte-identical.

Initial264 runtime tests passed530/5611 and67/1327, but PHPCS failed4header errors
and PHPMD reported a new boolean-argument finding. The incremental correction uses
a nonce-only completion API with callback error handling after request validation.
Corrected native candidate/main/server tests and the complete connected Microsoft
browser round trip remain pending; see the [audit](09_DOCUMENTATION_AUDIT.md). Local source/style/patch checks are not native PHP/
Moodle passes. Accepted263: Unit508/5490, Behat66/1318, all6candidate/main jobs, actual
454file ZIP, server and both strict10/0/0. Retain the demo; manual53 stays on standby.

Next gates remain real delegated access, stable activity references/launch resolution,
and supported native-player authentication and time/play/pause/seek telemetry before
tracked Microsoft playback. Zoom, Google Drive and the App remain later in that order.

## Primary core references

- [Core OAuth API](https://docs.moodle.org/dev/OAuth_2_API)
- [Pinned50 current-user client](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/client.php)
- [Pinned50 login URL and code processing](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/oauthlib.php)
- [Pinned53 standard callback](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/admin/oauth2callback.php)
