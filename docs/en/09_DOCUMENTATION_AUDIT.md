# Documentation audit

VideoTrack **1.7.269** (`2026112300`), based on accepted **1.7.267** (`2026112100`).

Accepted267: main418475d361cfd65b06b35875288a0b1b68aa3e12, tree87b084b6a85fc39eaed2a70db608f05ea2331ab5.
Candidate44908095172/main44910680696 each pass six CI jobs, Unit583/6243, Behat73/1390,
strict10/0/0 and Grunt. Actual ZIP SHA2563fbc345220877f1449e3f9a1dd03341b4b9c29ff002637b0eeb7ff94813278dd.
Server267 passes canonical/lint196shared, Unit583/6243 independently on50/53 and both Grunt builds.
These accepted267 results are not269execution results.

Current changes and limitations: [tracking hotfix and explicit recovery](39_TRACKING_RECOVERY_HOTFIX.md).
Microsoft-search268is paused/excluded; the diagnostic foundation through267 remains.
No schema,9AJAX,source-availability,token-store or Analytics-query optimisation changes.

Local standalone tests pass: old tracker harness plus six regression groups against both
source and shipped AMD. Five groups reproduce failures against accepted267. Source/build
maps, lexical/source contracts, inventories, language parity and patch application/reversal
are checked locally. Native269PHP lint/PHPCS, PHPUnit, Behat, Grunt, static analysis and
server acceptance remain pending. Local PHP/Moodle/MariaDB runtimes are unavailable.
12changed AMD sets were compiled using Babel; native Moodle Grunt is a separate gate.

Added12PHPUnit methods,2Behat scenarios and stronger contiguous-coverage assertions in
3existing random-pause scenarios. Normal and Privacy retention recalculation are unchanged;
historical repair requires explicit teacher selection, appends identifiable derived rows,
and preserves original rejected evidence. Missing or ambiguous rows cannot be reconstructed.

Counts: 501repository/475distributed files; 197distributedPHP plus lifecycle helper;
1490namedPHP callables, 660namedAMD callables, 595unit methods,
75expanded Behat scenarios,8×1060language keys and48AMD sets. Paired inventories reflect
source locations; unchanged XMLDB remains7tables/151fields/22indexes/22foreign keys.

Existing267 static-analysis information is baseline evidence only:31PHPMD findings and
751/779unclassified Psalm entries; no claim of unchanged269findings without native execution.
Preserve demo data, manual5.3accessibility STANDBY and pending provider/App roadmap.

## Initial 269 CI failure and corrective candidate

[Run 45227903298](https://github.com/pierpaolo 17/videotrack/actions/runs/45227903298)
executed commit `a8d58ffd21a7558b05e25bc5433344dca0aa1a16`, tree
`80a28a42cc2e3ff3002b8e7dd35b5bc5902aa53e`. All six jobs fail. Each executes
595 PHPUnit methods /6268 assertions with one failing old source-string assertion;
the 12 new methods pass. Browser results are 73/75 passing with the same two new
future-time waits failing on both automatic reruns. YouTube advances to about 7.2 s
inside a 7 s wait; HTML5 resumes near 3 s and advances to about 9.4 s inside a 6 s wait.
No stuck-provider claim follows from these observations. All 73 existing scenarios,
including stronger random-pause coverage checks, pass.

The quality job reports 51 PHPCS errors and one deprecated `$CFG->admin` warning.
The toolchain changed from PHPCS 3.13.6/moodle-cs 3.7.0 to 4.0.4/3.9.2; old header
spacing is now also flagged. Lint 197/197, PHPDoc, the lifecycle helper checks and
all six strict 10/0/0 validators pass where executed. Twelve standalone JS groups
and the tracker harness pass in every matrix entry. Lifecycle install/uninstall
is skipped after the failures, not accepted. PHPMD reports 35 advisories on 50 versus
31 previously; Psalm 757/785 information entries versus 751/779 are unclassified.

The correction expands PHP formatting/docblocks without changing recovery or
completion tokens, updates the old static wallclock assertion for the captured
cursor while retaining its other checks, and adds a dedicated bounded real-play
step before the two unchanged 12–17 s assertions. Old short-range waits and retries
are unchanged. The settings link now uses core navigation rather than a deprecated
admin-directory property. No AMD source, live ledger, DB schema or recovery policy
change is made by this corrective candidate. All 595 unit methods remain.

Grunt itself completes, but its freshness check reports 24 stale compiled files.
The initial Babel builds are not canonical Moodle Grunt output. Locally the npm
registry is denied (403), so no replacement build is fabricated. The original
freshness gate remains mandatory. Since plugin-CI 4.5.13 restores its input plugin
after comparing files, a separate guarded CI-only native Grunt invocation captures
its real output, source/commit hashes and exact-source maps. Early native artifacts
and the ordinary reports contain `native-amd.zip`. Compiled JS regressions use the
installed native build. A follow-up correction must install that verified output
and rerun every gate; this preparatory correction alone does not close Grunt.

The recovery checkbox is temporary: after production 5.0 historical repair has been
completed and checked, remove the checkbox and extraordinary repair path in a later
release, retaining repaired evidence and ordinary recalculation. Demo 5.3 and Microsoft
search remain paused. Native execution of the corrected candidate is still pending.
