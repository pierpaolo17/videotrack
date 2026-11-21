# Microsoft metadata under the current user's identity

Release266 adds a [private diagnostic preview](37_MICROSOFT_PRIVATE_PREVIEW.md) for the current administrator’s connected work/school account.
The metadata CLI and original-page action retain their own contracts; tracked learner playback
and live Microsoft browser validation remain separate gates.

Release 1.7.264 adds [Connect my Microsoft account](35_MICROSOFT_USER_CONNECTION.md)
to the existing administrator page. It starts core OAuth only after an explicit
protected POST; ordinary diagnostics GET remains local. Native264/265 are accepted; live tenant testing remains pending.

Release 1.7.263 also offers an explicit [original Microsoft page action](34_MICROSOFT_PROVIDER_PAGE.md).
It requires the personal account and a video id. The provider page opens separately;
VideoTrack does not credit viewing in that browser tab.

VideoTrack **1.7.267** (`2026112100`).

## Choose the identity explicitly

Open **Site administration → Plugins → Activity modules → VideoTrack → Microsoft
connection check**. The page remains restricted to `moodle/site:config` in system
context. Teachers/learners do not gain a new diagnostic endpoint or playback right.

**Account to check** offers:

| Choice | OAuth identity | Local prerequisite |
|---|---|---|
| Moodle system account | The selected service's existing core system client | Complete existing system readiness, including a stored system account |
| My connected Microsoft account | The current Moodle user's core user client for the same service | HTTPS/service/client/endpoints ready; no system account required |

System is the explicit default, preserving the existing workflow. The two readiness
objects shown on GET are local-only; neither checks user login or starts a client.
The CLI `provider_status.php`/`provider_probe.php` retain their system-account meaning.
No new CLI option or stored VideoTrack setting is added for the account choice.

## Reuse the existing Moodle connection

Configure the service as in [the Graph guide](30_MICROSOFT_GRAPH.md). In264, the
explicit [personal connection action](35_MICROSOFT_USER_CONNECTION.md) can use the same
core issuer directly from this diagnostic. A connection already established by a
Moodle repository remains reusable. Core owns login/consent, configured scopes and
tokens; VideoTrack adds no extra scopes, credential store or other-user selector.

Only an explicit valid POST/sesskey invokes the selected probe. Opaque drive/item
validation is unchanged. The personal path validates configuration and the supported
Microsoft identity endpoints before calling `core\oauth2\api::get_user_oauth_client`.
Its return URL is a fixed local diagnostic page; extra scopes are empty. Core user
autorefresh is enabled and core owns session/refresh tokens. The code does not read
another plugin's private token tables. Login checking may refresh, rotate or invalidate
core user credentials; it is not a database-wide read-only operation.

If the core client is not logged in, the result is `user_oauth_not_connected` and
Graph is not attempted. A valid client gets the existing single bounded metadata GET.
A 403 stays `graph_forbidden`; it is never retried with the system identity. Missing,
expired or denied personal access never authorises a shared-account fallback.

Results retain `account_context: current_user` (or `system` for the shared web probe),
configuration/OAuth/Graph phase and existing redacted status/metadata. No names, resource
ids, tokens, download URLs or raw exception text are returned. Both configuration and
result JSON are escaped; the page remains non-cacheable. VideoTrack records, demo data,
content files, grades and watched progress are not changed by either check.

## What this establishes and what remains open

A successful personal result proves only that **this current administrator's Microsoft
identity** can read metadata at that time. It does not test a different student's
identity, grant access, add OneDrive/SharePoint to the activity selector, renew a
playback URL or validate native player telemetry. A Graph preview URL acts as its
caller's identity; a shared account's success must not be used as learner authorisation.

Future learner access requires its own authenticated activity/participation checks,
provider identity, resource authorisation and measurable player contract. The preferred
provider-to-browser delivery path remains; no file is imported or streamed by Moodle.
Microsoft remains before Zoom and Google, and the App remains later in the roadmap.

The personal metadata/page/connection foundation through265 is accepted, with main
all six jobs, Unit530/5612, Behat67/1327 and actual server/strict gates. New266
preview and live-provider validation remain pending. Retain the demo; manual53standby.

## Primary core contracts

- [Moodle 5.0 core user OAuth factory](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/api.php)
- [Moodle 5.3 core user OAuth factory](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/classes/oauth2/api.php)
- [Core client token identity and user refresh](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/client.php)
- [Graph preview caller identity warning](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
