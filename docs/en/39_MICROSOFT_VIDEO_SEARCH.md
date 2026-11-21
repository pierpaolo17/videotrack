# Personal Microsoft video search

VideoTrack **1.7.268** (`2026112101`).

## Find a reusable reference

Open **Microsoft connection check → Find Microsoft videos**. Supply an explicit
Drive ID and search text of2–100characters. The action requires system
`moodle/site:config`, a validated POST/sesskey and the current administrator's
connected personal Microsoft work/school account. Connect that account on the
existing diagnostic first. Normal GET remains local; no shared-account fallback.

Each supported result shows its escaped filename, duration and a read-only
[reusable reference](38_MICROSOFT_VIDEO_REFERENCES.md). Copy that identity into
the connection check, choose your personal account and submit the desired action.
The subsequent metadata/page/preview action checks access again. A search result
is provisional metadata, not a permission grant or proof that a browser can play it.

## Scope and bounds

One request searches the root hierarchy of the specified drive. Microsoft documents
matches across names, metadata and content, with delegated `Files.Read` as the
least permission. VideoTrack adds no scopes or consent grants. It does not search
all drives, provision `/me/drive` or follow shared shortcuts into another drive.

The fixed Graphv1 metadata URL selects only the required facets and asks for20
provider entries. Only entries with safe opaque ids, bounded UTF-8 names, video
MIME, positive duration, nonnegative integer size and the expected parent drive
become choices. Folders, deleted/remote entries, malformed rows and duplicate ids
are excluded. Twenty entries are not necessarily twenty videos. The page can be
empty while more provider matches exist. Refine the query rather than treating
this page as an exhaustive drive inventory.

The result's continuation address is never followed or returned. A non-empty
string continuation becomes only `more_results: true`, with a visible limitation
notice. There is no pagination, background search, automatic retry or result cache.
An over20entry response is rejected. JSON parsing is bounded at1MiB/depth16 after
receipt; core curl is not given a streaming response-size cap. The existing5second
connection/15second request timeout, TLS checks and refusal of redirects remain.

Input is bounded valid UTF-8 without controls or surrounding whitespace; OData
apostrophes are doubled and the string content percent-encoded. The drive uses the
existing explicit-id grammar. Neither value can supply an endpoint, HTTP method,
content path or continuation URL. Search performs one GET and no preview POST.

## Private metadata and core reuse

Names, ids and provisional references appear only in the protected response.
Ordinary result JSON contains allowlisted status/count/continuation fields, never
candidate rows, query text, URLs, credentials or raw provider bodies. The output
escapes names and reference text; it has no-referrer policy and is non-cacheable.

Current-user configuration, endpoint checks and client acquisition are shared
with the existing metadata/page/preview methods. The core OAuth factory and fixed
return page remain the same. Core owns tokens and may refresh them. No File API
reference, draft, media import, file-cache or streaming proxy is created. Existing
Moodle repository credentials are not read from private storage.

Privacy metadata explicitly declares the query sent to Microsoft. VideoTrack adds
no query/candidate persistence, session state or application logging. Provider and
infrastructure logging remain outside this claim. Search text may itself identify
a person; use only text appropriate for the configured Microsoft service. The
[privacy summary](../../PRIVACY.md) describes this transfer.

## Evidence and remaining gates

Actual267 is accepted: candidate/main six-job CI, server canonical/lint196clean,
Unit583/6243 on50/53, both Grunt builds and maintainer strict10/0/0 each. New268
adds25unit methods and3local Behat scenarios. All other582old unit methods remain
unchanged; the existing exact Privacy-field assertion is extended to include
searchquery without dropping any previous field. All73old browser scenarios remain.
Native268 execution and a live Microsoft tenant search are pending.

Search adds no selectable activity source, watched progress or learner launch.
Stable activity persistence/backup, live OAuth/browser preview, the announced
Entra-header authentication transition and supported player time/play/pause/seek
remain gates. Microsoft first, then Zoom, Google Drive and App. Keep the demo and
manual5.3 accessibility STANDBY.

## Primary references

- [Graphv1 driveItem search, permissions, scopes and continuation](https://learn.microsoft.com/en-us/graph/api/driveitem-search?view=graph-rest-1.0)
- [Graph OData string escaping and URL encoding](https://learn.microsoft.com/en-us/graph/query-parameters)
- [Existing private preview boundary](37_MICROSOFT_PRIVATE_PREVIEW.md)
