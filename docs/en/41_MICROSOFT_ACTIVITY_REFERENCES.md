# Microsoft activity references

VideoTrack **1.7.271** (`2026112401`) prepares an optional stable Microsoft target in an
activity. Microsoft is still unavailable as a tracked player source. HTTPS/OAuth,
real tenant permissions, browser events and learner progress need live acceptance.
The active HTML5, YouTube or Vimeo video remains the playable source.

## Preparing and changing a reference

An activity editor with `moodle/course:manageactivities` may open **Microsoft video
preparation** and paste the reference produced by the protected Microsoft access
check. The optional field can remain empty on every existing activity. This release
validates the reference locally; saving does not call Graph or acquire a token.

A reference has exactly these scalar fields:

```json
{"version":1,"provider":"microsoft","issuerid":17,"driveid":"b!Drive","itemid":"Video"}
```

The positive issuer must name the site's selected, existing, enabled Microsoft core
OAuth service. Drive/item ids use the existing bounded opaque-id grammar. URLs,
paths, temporary preview addresses, secrets and additional fields are rejected.
Storage is canonical JSON in nullable `videotrack.microsoftreference`; the 4096-byte
input bound and strict types apply before the activity write. An unchanged reference
survives a service configuration change; sparse callbacks preserve the previous field.
An authorised editor can clear even an obsolete reference without restoring the service.

The explicit **Bind to this site's selected Microsoft service** checkbox changes only
the local service association for those target ids. It is never stored as a setting,
never grants access and does not prove that the same drive exists in the new tenant.
The editor must check the resource again through the existing provider diagnostic
when the destination service/account is available. Reference changes do not switch
the active video, change duration or initiate completion recalculation.

## Backup and restore

Course backup includes a `microsoft_identity` child containing only `version`,
`provider`, `driveid` and `itemid`, with or without learner data. It excludes the local
issuer id and all OAuth credentials. It never exports the raw stored JSON field.
Restore validates those exact four fields and stores `issuerid:0` (unbound), even on
the original site. It ignores any raw parent `microsoftreference` field; backups
without the portable child leave the new column null. No issuer is created, looked
up by name, mapped by numeric coincidence or selected automatically during restore.

A normal edit preserves an unbound reference. An authorised editor must deliberately
bind it to the destination's selected enabled Microsoft service, or replace it with
a newly verified local reference. Rebacking up an unbound activity remains portable.
Learner data still follows Moodle's users setting, mapping and existing retention.

## Upgrade and privacy

The additive upgrade to `2026112401` adds one nullable TEXT column, guarded by
`field_exists`. Existing activities get null. The seven tables now have 152 fields;
22 explicit indexes, 22 foreign keys and nine AJAX services are unchanged. The upgrade
contains no learner data rewrite, cleanup or completion recalculation. An already
present column is preserved. The lifecycle CI snapshot expects only this new null
field in addition to the existing historical defaults.

Privacy metadata declares the optional activity configuration identifiers. This is
not a new learner identity or token store. The field is absent from learner player
configuration and learner-owned exports. Course backup intentionally carries its
portable resource ids. Core continues to own OAuth credentials and revocation.

## Verification and next gate

New native tests cover strict parsing, permission failures, absent/disabled/wrong
services, sparse edits, explicit destination binding, real activity callbacks and
actual course backup/restore with and without learner data, legacy raw-field rejection
and repeated portable backups. Two local browser scenarios cover empty saves and
invalid input. All 620 prior unit methods and 78 browser scenarios are retained.

Local source/contracts, language parity, preserved AMD maps and tracking regressions
are checked separately from native PHP/Moodle execution. Native 271 CI and both test
server installations are pending when the patch is delivered. No live Microsoft
OAuth, playback or telemetry result is claimed. The next step requires HTTPS and the
configured personal Microsoft account; provider iframe events must support trusted
tracking before any Microsoft player source can be enabled.
