# Check a saved Microsoft activity reference

VideoTrack **1.7.275** (`2026112700`) connects the saved optional activity reference
to the existing administrator diagnostic. It does not enable Microsoft as a
student source or implement playback tracking.

## Open the check

As a site administrator, edit an existing VideoTrack activity and expand
**Microsoft video preparation**. Use **Check the saved Microsoft reference**.
The diagnostic opens in another tab so unsaved settings are not discarded.
The notice explains that it uses the last saved reference; edits still present
in the activity form are not included. A new activity must be saved first.
Teachers without site-configuration permission do not see this shortcut.

The link carries only `activitycmid`, the Moodle course-module id. It carries no
drive/item ids, reference JSON, temporary URL, credential or selected account.
The controller checks site permission and permission to manage activities in the
owning course, then reads the current activity record. A module belonging to a
different plugin cannot be substituted through a coincident instance number.
Site permission is checked before resolving the module, including invalid ids.

## Submit an explicit operation

Opening the page is local: it does not acquire an OAuth client, contact Graph or
create a preview iframe. The bound form shows the escaped saved activity name
and the three existing operations: metadata, original provider page and private
native preview. It has no editable resource ids, pasted reference or account
selector. The server resolves only the saved reference and current user's account.

Every POST requires the ordinary Moodle form validation and sesskey. Each request
reloads the saved record before validation: a reference changed after the previous
page opened replaces the old browser snapshot. Forged extra fields cannot select
another resource or the shared account. Unsaved edits are never written back.
An empty, malformed, restored-unbound or differently bound reference stops locally
with the existing reference error. Configuration, selected issuer, OAuth endpoints
and access are checked by the existing personal probe before Graph requests.

This is administrator diagnostics. The system-wide `moodle/site:config` restriction
remains; course management alone does not authorise the endpoint. Ordinary generic
diagnostics still support their existing explicit manual/reference/account choices.
Connecting an account uses the existing core callback and returns to the general
diagnostic; reopen the activity shortcut after connecting. No new OAuth state or
return parameter is persisted.

## Identity, media and evidence

Every explicit operation reuses `microsoft_user_probe` and the current user's core
OAuth client. No fallback to the shared/system account is introduced. Core continues
to own token refresh. A reference is an identity, not a provider access grant.
Requests remain fixed-host Graph metadata and existing bounded preview operations;
there is no file download, Moodle import, proxy streaming, URL cache or auto retry.
Private provider/preview URLs remain confined to the existing administrator response.
Ordinary diagnostic JSON does not gain resource ids, names, reference data or URLs.
The existing no-cache/no-referrer policy and iframe sandbox remain.

The check does not change the active HTML5/YouTube/Vimeo source, configured duration,
watching segments, notes, reactions, bookmarks, grades or completion. It does not
bind/rebind the reference or repair data. No schema migration, scheduled task,
capability, service or new privacy transfer is added. The temporary historical
recovery checkbox remains retired; ordinary recalculation and accepted repair rows
retain their accepted behavior. All AMD sources/builds/maps are unchanged.

## Evidence and next gate

The accepted baseline is the actual 274 ZIP SHA256
31f6f085edfe3f1c82f4a6eb418bb3ce08c26b3ed934047741e9e61435bdb220,
501 files identical to main 5e939e64/tree ec807599. Candidate and main each pass
six jobs, Unit 656/6922, Behat 84/1571 first pass, both populated lifecycle matrices
and strict validators. Actual server reports pass all eight requested outcomes on
50/53, shared canon/lint 210 files and unchanged native builds. On 2026-11-27 the
maintainer confirms duplication/unrelated saves, CSV and ordinary recalculation.

Fifteen new PHPUnit methods cover context permissions, wrong module ids, real form
GET/POST/CSRF behavior, forged inputs, stale references, operation choices, shortcut
privacy and integration with fresh personal transport responses. Provider HTTP is
mocked, including denial after success; evidence/grade/completion rows are compared.
Two new browser scenarios cover the actual new-tab shortcut, local empty-reference
rejection and teacher exclusion. The generic-controller assertion keeps its POST
requirement while accepting the server-supplied optional form context.

Local source, inventory, language, asset and patch checks are separate from native
PHP/Moodle execution. No PHP runtime is available locally; 275 PHPCS/PHPUnit/Behat,
CI, populated lifecycle, server and manual reports are pending. HTTPS and a linked
Microsoft account are not yet available at preparation time. Real delegated access,
iframe/browser authentication and supported time/play/pause/seek telemetry remain
required before any Microsoft tracked source. No success is inferred from fixture
tests. Zoom, Google Drive and App follow the agreed provider order.

Primary references: [Graph preview and caller identity](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0),
[opaque preview information](https://learn.microsoft.com/en-us/graph/api/resources/itempreviewinfo?view=graph-rest-1.0),
[Moodle OAuth API](https://docs.moodle.org/dev/OAuth_2_API).
