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

Counts: 500repository/475distributed files; 197distributedPHP plus lifecycle helper;
1489namedPHP callables, 660namedAMD callables, 595unit methods,
75expanded Behat scenarios,8×1060language keys and48AMD sets. Paired inventories reflect
source locations; unchanged XMLDB remains7tables/151fields/22indexes/22foreign keys.

Existing267 static-analysis information is baseline evidence only:31PHPMD findings and
751/779unclassified Psalm entries; no claim of unchanged269findings without native execution.
Preserve demo data, manual5.3accessibility STANDBY and pending provider/App roadmap.
