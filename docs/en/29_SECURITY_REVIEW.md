# Security review corrections

VideoTrack **1.7.257** (`2026111600`).

This release addresses the reviewed settings-authorisation and dashboard privacy gaps.
The save callbacks enforce the same course-level override capabilities as the form before
normalisation, database writes or caption-file changes. Existing locked values are preserved;
new activities use site defaults, including explicit zero and inherited empty choices.
Completion dependencies cannot be disabled with zero duration, disabled acknowledgement,
or removal/reordering of required reaction definitions. Rate limit and resume controls that
the existing form leaves editable retain that contract.

Course and teacher rows require activity aggregate-report access. The effective contributor
floor is resolved per activity: legacy/individual access may request exact values; aggregate-only
access uses at least the configured site threshold, even when a caller requests one. All-time,
period, reaction, note and bookmark summaries use that floor. Suppressed counts display as
hidden rather than zero. Activity SQL batching is retained; new benchmark timings are pending.

Empty manual grades now use a null raw grade through the Grade API for points and scales.
HTML names/options are escaped separately from plain CSV labels and HTML attribute values.
Reaction warnings and the student-facing note visibility notice use all eight language packs.
The note notice explains that authorised individual-report users may read and export notes.

Video page GET no longer creates or repairs the focus exception group. Creation remains in
add/update/restore/upgrade. Full uninstall deletes only groups with the plugin's reserved
idnumber, including when no grade items exist. Grade repair uses a delegated DML transaction
so an interrupted merge/delete rolls back; DDL is not wrapped in that transaction.

## Finding disposition

| Source ID | Current implementation status |
|---|---|
| SH-01 | Server-side settings policy, aliases and dependency guards implemented; native proof pending. |
| SH-02 | Per-activity access and privacy floor implemented in shared batched services and rendering. |
| SH-03 | Empty grade passes null; zero retains its grading meaning. |
| SH-04 | Student note visibility notice implemented; permanent deletion/legacy backups remain open. |
| SH-05 | Read-only video GET and owned-group uninstall cleanup implemented. |
| SH-06 | Uninstall already uses Grade API for valid contexts; orphan DML retained. DDL split deferred. |
| SH-07 | Existing POST and sesskey guards retained; general request API refactor deferred. |
| SH-08 | HTML labels escaped separately from CSV/plain attributes. |
| SH-09 | Both response warnings localised; warning codes and AJAX structure retained. |
| MS-38 | DML repair transaction implemented with native injected-failure regression supplied. |
| SH-A1 / A2 | Browser resume isolation and POST Analytics downloads deferred. |

## Validation and remaining work

The accepted baseline is actual 1.7.256/main 26cf86ed: 418 matching files,
server PHPCS/lint 171 clean, PHPUnit 396/4581 on Moodle 5.0/5.3, unchanged Grunt build digest,
strict 10/0/0 on both sites, and six-job candidate/main CI with the selected lifecycle paths.
Candidate 1.7.257 has 18 additional native regression methods; they have not been run in this
workspace. Native canonical/static/PHPUnit/Behat/lifecycle and actual package/server acceptance
remain pending. A new external scan is required before claiming a new review grade.

Permanent note/bookmark erasure and legacy backup handling are still open. Their existing
soft-delete/retention semantics are unchanged. DDL-helper separation, general request-controller
refactoring, per-user browser resume keys and POST-only Analytics downloads remain separate work.
Microsoft OAuth configuration remains local-only, with no remote access or new media playback.
New sources follow this security work; the App remains afterwards. The App-to-browser trial is
skipped, multilingual subtitle expansion is deferred, manual Moodle 5.3 remains on standby,
and the synthetic demo data is retained.

MDL Shield is an independent LMSCloud service, not affiliated with Moodle Pty Ltd. Its findings take priority at the maintainer’s request. The reviews examined 1.7.255/36ab70ed. Of mdlscan’s 38 explicit uncertain findings, 36 were false positives in the verified paths; section-name output is bounded by the course-format contract (Tiles needs native checking); grade-repair atomicity is the concrete improvement. Raw tool summaries are not a complete finding list.

[MDL Shield](https://mdlshield.com/about) · [Baseline commit](https://github.com/pierpaolo17/videotrack/commit/26cf86ed6c275e5e6e9a23c81659be5c5aea7790)
