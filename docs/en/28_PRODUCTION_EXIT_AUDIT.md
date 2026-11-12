# Integrated lifecycle and production evidence

Current candidate: VideoTrack **1.7.253** (`2026111203`). Audit recorded 2026-11-12.

## Accepted baseline

Actual **1.7.252 / 2026111202** is accepted: all 415 ZIP files match main
`3f9e62ab6485f36e4088b1c76b79aa644bc49fa3` and the corrected approved candidate.
Package SHA-256: `659f60d7e48de2d198ab070a71b6ca3ee98506553545d09d29e712443fde61f9`.
The 10-file server report has SHA-256
`34ce4e5d1bfecd3fd874154fc7818797c43dcf2db14089328c82363bd40a21eb`.
The corrected candidate CI (run 43246418318, commit `5b7ffd3`) and main CI
(run 43252265858) pass all six environments. Each passes PHPUnit **383/4350**,
Behat **52 scenarios / 1154 steps**, strict **10/0/0** and Grunt. PHPCS/lint are clean on
167 files. The two Moodle 5.0/5.3 server reports pass the same PHPUnit suite and Grunt;
their AMD build hashes are unchanged. Both maintainer-supplied strict outputs are **10/0/0**.
No native Moodle execution occurred in this workspace. PHPMD's 24 reviewed findings and
Psalm's 732/760 advisories remain unchanged from the corrected candidate; configured scopes pass.
The initial two PHPCS argument-layout errors were corrected before merge, not ignored.

## Accepted integration scope

| Case | Evidence and scope |
|---|---|
| Backup/restore without learners | Real course controllers, shared configuration, Forum/reaction/file mappings and canonical grade item; learner rows/grades/completion excluded, source plugin rows preserved. |
| Backup/restore with learners and post-restore privacy | Destination retention excludes expired inputs; unvalidated segments and stale aggregate/native completion cannot restore credit; approved-user export, user/context erasure and preservation of other learners/source/files/core grade ownership are checked. |
| Standard course reset, option off/on | The actual Moodle reset entry discovers `videotrack_reset_userdata()`, which delegates to the retained helper. Two activities in one course are cleared while configuration/definitions/files and another course's learner rows survive. Core completion reset is selected separately and global gradebook reset is off. |
| Synchronous activity deletion | Public Moodle deletion removes the selected activity, six plugin child tables, native completion, files/context and owned grade items/rows. The linked Forum and a populated neighbouring activity survive. |

The standard reset callback and its opt-in/default-off behaviour are accepted on 252. The existing
helper remains available to historical direct callers. Moodle's reset page owns `moodle/course:reset`,
login and session/form checks; per-student report reset has its separate management/sesskey/scope gate.
The deletion test selects `core_courseformat\formatactions::cm()->delete()` when supported and the
older branch's supported helper otherwise; no deprecation suppression is used.

Backup tests use `MODE_IMPORT` with real XML/files, without archive compression. This does not certify
MBZ download/extraction, cross-site migration or historical-backup compatibility. Fixtures populate
framework/DB records directly; they are not evidence that a browser generated those records.

## New candidate: registered retention task after restore

One new integration test obtains `mod_videotrack\task\cleanup_task` through
`core\task\manager::get_scheduled_task()`, checks its component and invokes its real `execute()`.
It restores an activity with learner data while retention is unlimited, then exercises five invocations:

1. Unlimited retention preserves exact learner records in both source and restored activities.
2. A ten-day policy removes expired segment/event/integrity/acknowledgement rows from both activities.
   Only retained validated segments credit viewing; custom and native completion become incomplete.
3. Repeating that policy preserves all retained plugin records exactly and reports zero processed pairs.
4. A one-day policy expires every fixture input and removes all five learner-data families, including state.
5. Repeating the fully expired cleanup leaves no plugin learner rows and reports zero processed pairs.

Shared activity configuration, reaction definitions, files and core-owned grade rows remain unchanged.
Exact task output covers four user/activity pairs and distinguishes rebuilt from removed states. The site
policy applies to both courses; source expired data is deliberately cleaned too. The fixture's timestamps
are two and twenty days old, well away from the chosen cutoffs; no sleep or production clock seam is added.

This is a direct invocation of the task registered in Moodle, not a test of cron wake-up times, scheduler
locks, backlog limits, failure retry or injected faults. Those observations are not inferred from this test.
The new test is pending native CI. All production PHP except the release marker, schema, AMD, language values,
roles, services, task registration, privacy/backup implementations and CI rules remain byte-identical to 252.

Expected candidate suite: **384 PHPUnit test methods**; assertion totals must be measured by native CI.
Behat remains **52 scenarios / 1154 steps**. The tree keeps **415 files**, **167 PHP files**, **32 documents
per language**, **346 file-inventory rows** and **654 AMD callables**. Named PHP callables become **1114**.
XMLDB stays `2026110601` and **7/151/22/22** for tables/fields/indexes/foreign keys, with nine AJAX services,
eight language packs of 997 keys and 48 AMD source/build/map sets. ER changes only its version label.

## Remaining production gate and roadmap

- Historical/interrupted upgrades and full populated uninstall with zero-residue checks remain distinct evidence.
- Real cron scheduling, contention/failure recovery and fault injection have not been certified by these cases.
- Analytics benchmarks on large datasets, multiple activities/groups and 7/30/90-day periods need recorded
  versions and data/cache conditions; no benchmark is inferred from test or query counts.
- Eight maintainer-reported manual PASS on 248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA remain scoped.
  Moodle 5.3 manual observations remain unrecorded; no new manual pass or WCAG certification is inferred.
- Dedicated Moodle App support remains undeclared (`db/mobile.php` has an empty addon list).

U-017 and essential U-007 remain closed. Complete the integrated web/lifecycle/privacy/benchmark/manual
gate and explicit App decision, then agreed multilingual VTT subtitles, authenticated-provider foundation,
OneDrive/SharePoint → Zoom Cloud Recording → Google Drive and cross-provider hardening.
Native candidate/main CI and actual ZIP/server/strict remain required before accepting 253.

See [lifecycle](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [privacy](18_PRIVACY_RETENTION.md),
[CLI/benchmarks](21_CLI_DIAGNOSTICS.md), [manual evidence](19_ACCESSIBILITY.md)
and [language review](27_LANGUAGE_REVIEW.md).
