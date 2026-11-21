# Reusable Microsoft video references

VideoTrack **1.7.267** (`2026112100`).

## Repeat a personal video diagnostic

The Microsoft connection check can reuse a video identity instead of a temporary
preview URL. This is an administrator diagnostic, not a selectable activity source.
It does not yet store Microsoft references in activities or resolve learner launches.

1. Configure and connect your personal work/school account as described in the
   [connection guide](35_MICROSOFT_USER_CONNECTION.md).
2. Open **Microsoft connection check**, select **My connected Microsoft account**
   and keep **Target selection → Enter drive and video IDs**. Enter the explicit
   drive/video ids, choose a metadata, original-page or preview action and submit.
3. After a successful personal video result, copy the **Reusable video reference**
   from the separate read-only field. It contains resource identifiers, not a
   preview URL or a permission grant. Keep it private; do not paste private preview
   addresses or credentials into reports, teaching materials or this reference.
4. Later choose **Use a reusable video reference**, paste that identity, choose
   your personal account and the desired existing action, and submit again.
   This explicit POST repeats the same OAuth/access/video checks and obtains a
   fresh result. Opening the page normally performs no Graph request.

The original manual-id mode remains the default and retains drive-only metadata
and its explicit shared-account option. Reference mode always requires a video
and the current personal account. Inactive manual fields cannot override a
reference; switching back to manual mode uses the explicitly entered ids.

## Identity format and limits

An example with synthetic ids is:

```json
{
    "version": 1,
    "provider": "microsoft",
    "issuerid": 7,
    "driveid": "b!ExampleDrive",
    "itemid": "ExampleVideo"
}
```

The format version is separate from the plugin release. Input is a JSON string
of at most4096bytes and depth4. Only those five properties are accepted: version1,
provider `microsoft`, a positive integer local OAuth service id and two non-empty
opaque ids under the existing Graph alphabet/256character limits. Unknown fields,
URLs, credentials, paths, other providers and unsupported versions are rejected
locally. Plain JSON whitespace and property order do not change the identity.

The service number names a Moodle core issuer on this site; it is not a tenant id
or a portable connection configuration. If the selected issuer id changes, the
reference is refused before OAuth/Graph. Restore the intended service or validate
the video ids under the new service. Editing an issuer in place keeps its local id;
the reference does not pin its tenant, endpoints, scopes or current user's account.
Every actual check still applies the existing configuration/endpoint/account guards.

Microsoft documents id-based addressing as surviving renames and moves of an item.
This implementation retains the explicit drive/item pair; it does not search for
deleted/recreated files, copies or resources transferred to another drive. It does
not infer a replacement video from its filename or a failed URL.

## Fresh resolution and access

The controller requires system `moodle/site:config`, validated POST and sesskey
before resolving the reference. The form rechecks the selected issuer when it
converts the reference into existing probe fields. Reference possession never
substitutes for administration capability, a connected personal account or provider
permissions. The reference cannot select an HTTP method, shared identity or endpoint.

Each preview request follows the existing metadata GET then JSON preview POST
through the current user's core client. The reference holds no previous preview
URL and there is no URL cache, background renewal or automatic retry. Revoked
access, deleted items, invalid metadata or transport errors return the existing
redacted failure; they do not recover an earlier successful preview address.

An export is offered only after `video_accessible`, `provider_link_ready` or
`preview_ready` under a personal video target. It is escaped in a separate read-only
field; the probe-result JSON still excludes drive/item ids, URLs and credentials.
The reference object does not expose its private fields via ordinary object JSON
serialisation: `encode()` is the explicit administrator export boundary.

No new settings, table, user token store or activity data is written. Core may
refresh its own OAuth tokens when the existing probe runs. Microsoft already
receives the same resource ids under the existing Privacy declaration; this adds
no new provider transport. The form retains submitted text only in its response;
VideoTrack adds no database/session persistence of the reference.

## Evidence and roadmap

The actual266 package is accepted: main/candidate six-job CI, server canonical/lint
194PHP, Unit560/6074 on50/53, both Grunt builds and both strict10/0/0. That does not
establish native267 or live Microsoft browser success. New267 contains23unit
methods and three local-only Behat scenarios; native execution remains pending.
Tests cover identity parsing, service binding, personal-account/form/CSRF boundaries,
fresh repeated previews, denied access after success and preserved activity data.

Stable activity persistence/backup and learner launch resolution are later work.
Live delegated browser/iframe validation, the announced Entra-header authentication
transition and supported time/play/pause/seek telemetry remain gates before tracked
Microsoft playback. The [private-preview limits](37_MICROSOFT_PRIVATE_PREVIEW.md)
remain in force. Keep Microsoft first, then Zoom, Google Drive and App; retain the
demo dataset and manual5.3 accessibility **STANDBY**.

## Primary references

- [Microsoft Graph id-based driveItem addressing](https://learn.microsoft.com/en-us/graph/onedrive-addressing-driveitems)
- [Graph v1.0 temporary preview and caller-identity boundary](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
- [Core OAuth reuse and current-user diagnostic](33_MICROSOFT_USER_ACCESS.md)
