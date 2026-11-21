# Open the original Microsoft video page

Release266 adds a [private diagnostic preview](37_MICROSOFT_PRIVATE_PREVIEW.md) for the current administrator’s connected work/school account.
The metadata CLI and original-page action retain their own contracts; tracked learner playback
and live Microsoft browser validation remain separate gates.

Release 1.7.264 adds [Connect my Microsoft account](35_MICROSOFT_USER_CONNECTION.md)
to the existing administrator page. It starts core OAuth only after an explicit
protected POST; ordinary diagnostics GET remains local. Native264/265 are accepted; live tenant testing remains pending.

VideoTrack **1.7.268** (`2026112101`).

## Administrator procedure

Open **Site administration → Plugins → Activity modules → VideoTrack → Microsoft
connection check**. Select **Action → Resolve Microsoft page link** and **Account to
check → My connected Microsoft account**. Enter both the explicit drive and video item
ids, then submit **Check Microsoft access**. The existing metadata action remains the
default and can still use either system or personal identity.

Only a valid POST/sesskey and system `moodle/site:config` capability can resolve a
page link. Both the form and controller require the personal account; the backend
also checks capability, target, local readiness, trusted OAuth endpoints and current
client login before Graph. No system-account fallback or other-user selector exists.
Use the same core OAuth issuer and connection as in [the personal guide](33_MICROSOFT_USER_ACCESS.md).
Core may refresh/invalidate its user credentials; VideoTrack owns no token store.

The action performs one fixed-host GET:
`/v1.0/drives/{driveId}/items/{itemId}?$select=id,file,video,size,deleted,webUrl`.
It reuses the existing transport, HTTP/JSON checks and video-facet validation. Redirects
and retries remain disabled; connection/total Graph timeouts are5/15seconds. The1MiB
limit bounds JSON parsing after receipt, not streaming body transfer. No content,
preview, download, sharing or permission-writing endpoint is called.

## Result and private link

| Status | Meaning |
|---|---|
| `provider_link_ready` | Valid video metadata and supported resource-page destination; the private link can be displayed |
| `provider_link_unavailable` | Video metadata is valid but webUrl is absent or not a string |
| `provider_link_unsupported` | The returned destination fails this first scope's URI restrictions |
| Existing configuration/OAuth/Graph errors | No provider link; retain their existing redacted diagnostic meanings |

A ready result shows **Open in Microsoft**, opening the page in a separate tab.
The URL is kept separate from the JSON diagnostic; serialising the result object also
omits it. It is rendered only in the authorised response, not stored in VideoTrack,
a session cache or a content-free event. No link is emailed, copied into an activity
or made public. The page remains non-cacheable. The link uses `noopener noreferrer`
and `referrerpolicy=no-referrer`; it is not an iframe or an automatic redirect.

This first scope accepts HTTPS subdomains of `sharepoint.com`, including OneDrive for
Business personal sites. It rejects userinfo, non443ports, fragments, raw whitespace/
controls/backslashes, URLs longer than8192bytes and familiar bearer query keys
`access_token`, `tempauth`, `authkey`, `token`, `sig` and `signature` (also encoded keys).
Personal OneDrive, national clouds and other hosts remain unsupported. URL bytes are
not reconstructed or rewritten. The query check is a conservative boundary, not a
proof of every tenant's authentication semantics.

The supplied stable drive/item ids are resolved afresh on each explicit submission;
a renamed item can return its current webUrl. Cross-drive moves or replaced items
may require new ids. This is an administrator handoff, not a stored activity reference
or the future automatic video-launch resolver.

## Identity and tracking boundary

The Graph check uses the current Moodle user's delegated Microsoft identity. The
new tab uses the browser's Microsoft sign-in, which may be another account, require
login, be denied or behave differently under tenant policy. This release does not
prove successful playback, the same browser identity or an individual learner's rights.
Microsoft renders its resource page; VideoTrack does not supply a player controller.

No watched seconds, progress, native completion, grades or VideoTrack activity-view
event are credited. No media is imported, streamed or proxied by Moodle. The three
implemented source switches and existing YouTube/Vimeo/HTML5 behaviour remain unchanged.
The nine AJAX contracts, database schema and AMD sources/builds/maps are untouched.

## Future native embedding

Microsoft announced preview URL authentication changes beginning1April2027; an iframe
must not assume a returned URL remains anonymously redeemable. That future integration
needs audience-correct authentication and supported time/play/pause/seek telemetry.
This original-page action only opens the resource page; the266 private preview
action is separate and still requires live authentication validation.

Twenty new native unit methods cover URI boundaries, serialization/redaction, one
bounded metadata GET, video validation, failures, personal/permission guards, retained
data and form/controller contracts. Three browser scenarios have no selected issuer
or external network. The corrected foundation through265 has passed candidate/main
CI, actual package/server and both strict outputs. New266 and live tenant/browser
checks remain pending; see the [audit](09_DOCUMENTATION_AUDIT.md). Retain demo;
manual53standby.

## Primary references

- [driveItem webUrl property](https://learn.microsoft.com/en-us/graph/api/resources/driveitem?view=graph-rest-1.0)
- [Graph item metadata GET](https://learn.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0)
- [Microsoft preview-auth transition announcement,8October2026](https://devblogs.microsoft.com/microsoft365dev/pre-authenticated-tempauth-urls-are-retiring-move-to-microsoft-entra-id-tokens-before-april-1-2027/)
