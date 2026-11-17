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

## Initial candidate CI and correction

The first 1.7.257 candidate, commit `68ca29de`, run `44049397832`, failed all six jobs.
All six attached artifacts match GitHub's official SHA-256 digests and the 446 repository
files/modes match that commit. Each PHPUnit run reported 414 tests, 4687 assertions,
one error, one failure and one warning. The reordered-reaction regression exposed an
existing missing-icon default: validation accepted the fallback emoji but then read a
missing array entry, producing null for a non-null database column. The correction
normalises the fallback first and asserts the persisted default without filling the
omitted fixture field. A source contract now tolerates indentation around the same
course_modules recordset call; anonymous-class spacing follows the native PHPCS finding.

Strict validation passed 10/0/0 and Grunt completed in all six jobs. Behat passed 52/1154
on five jobs; Moodle 5.1 first failed the post-rollback note assertion (51/52 scenarios),
then the automatic isolated rerun passed 1 scenario/43 steps. This is not a clean initial
pass. The historical upgrade/populated-uninstall gates were skipped on the selected
5.0/5.3 MariaDB jobs after PHPUnit failed; no lifecycle result is inferred from lint.

PHPStan and Psalm reported no errors on the configured 5.0/5.3 jobs. Psalm reported
739/767 informational issues, four more per branch than accepted 1.7.256; individual
issue types are not printed. PHPMD reported 28 findings versus 24: four new complexity
advisories at the per-activity privacy checks (10 each), reaction requirement restoration
(12) and transactional grade repair (10). These guards remain explicit in their shared
entry points; no suppressions or threshold changes are added. Informational follow-up
remains open. These are initial-candidate observations, not proof for the correction.

The replacement patch is incremental from `68ca29de` on `release/v1.7.257`.
The release/version stay 1.7.257/2026111600. Corrected native canonical/static/PHPUnit,
Behat and lifecycle checks must be rerun before merge; actual ZIP/server acceptance
comes afterwards. PHP/Moodle are unavailable in this workspace, so local source and
patch-application checks do not count as native passes.

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
