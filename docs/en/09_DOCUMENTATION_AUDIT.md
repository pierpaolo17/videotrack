# Documentation audit

Current tree: VideoTrack **1.7.268** (`2026112101`). Accepted actual baseline:
**1.7.267** (`2026112100`). New268 native CI/server and live Microsoft tenant/browser
evidence are pending.

## Accepted267 source and CI

Candidate70194429e55a6f95036edfdd3258d33cb3c2745b and main
418475d361cfd65b06b35875288a0b1b68aa3e12 share tree
87b084b6a85fc39eaed2a70db608f05ea2331ab5; annotated tagv1.7.267 points to main.
All495repository blob identities/modes and471export files match the actual package.

[Candidate44908095172](https://github.com/pierpaolo17/videotrack/actions/runs/44908095172)
and [main44910680696](https://github.com/pierpaolo17/videotrack/actions/runs/44910680696)
each pass six jobs at attempt1: Unit583tests/6243assertions, Behat73scenarios/
1390steps without retry markers, strict10/0/0 and Grunt on every matrix entry.
All twelve artifact digests match official records. Canonical195distributedPHP plus
helper, PHPDoc, metadata,206savepoints and Mustache complete. PHPMD31findings match
the prior baseline exactly; PHPStan/Psalm have no configured errors. Psalm751/779
information findings remain unclassified; equal counts do not prove equal identities.

Both50/53MariaDB jobs complete historical/partial/previous lifecycle lineages.
Per run six snapshot hashes are independently reconstructed and match both31/0
verification stages; uninstall26/0 per lineage and126actual shared-file content
checks. Other matrix lifecycle entries are skipped, not passes. Earlier266PHPCS28/
Graph55 corrections stay accepted; passing runs do not establish a product fix for
older transient browser note visibility or localhost availability.

## Actual267 and maintainer server

ZIP `videotrack-1.7.267.zip`:2,006,796bytes,471files/195PHP, comment main418475d3.
SHA256: `3fbc345220877f1449e3f9a1dd03341b4b9c29ff002637b0eeb7ff94813278dd`.
Report `report_completo_videotrack_1.7.267_20261121_153041.zip`:14,739bytes,
SHA256: `ee2c03f699a0b031769981120c62c9755eeae1ab21c6540b5ac3045946034c86`.

All eight requested command outcomes pass. Shared canonical/lint cover identical
code196PHP once, not two independent executions: PHPCS0errors/0warnings, stderr
empty and196lint successes. PHPUnit separately passes583/6243 on Moodle5.0.11 and
5.3+, PHP8.3.6/MariaDB11.4.14. Both Grunt commands complete; recorded before/after
build hash unchanged: ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb.
All48ZIP source maps independently match sources. No independent reconstruction
of the server aggregate build hash is claimed.

Both maintainer strict outputs are10/0/0 at installed/file2026112100. Grade items/
rows0/0 on50 and34/34 on53; XMLDB7tables/151fields/22explicit indexes/22foreign keys,
9AJAX,8×1058language keys and48AMD sets. Actual267 is accepted.

## Current268 implementation and tests

[Personal metadata search](39_MICROSOFT_VIDEO_SEARCH.md) is linked from the existing
protected diagnostic. POST/sesskey and site capability precede current-user OAuth.
One explicit drive and bounded UTF-8 query create a fixed-host Graphv1 GET; OData
apostrophes and URL content are escaped separately. No automatic paging, retry or
content requests; response parsing1MiB/depth16 and20provider entries maximum.

Only valid nondeleted, nonfolder, nonremote video entries in the expected drive
are retained, with unique safe ids and bounded names/facets. Private candidates
appear escaped as names/durations/reusable references in the protected response.
Diagnostic JSON omits candidates, query, ids, URLs, raw bodies and credentials;
continuation becomes only a boolean notice. The next explicit video check verifies
access again. Core owns credentials; no File API alias/cache/import/proxy is created.

Configuration, identity-endpoint checks and personal-client acquisition are shared
through prepare_client. Existing public metadata/page/preview contracts, target/
capability order, core factory/callback and system CLI are retained. Privacy adds
the searchquery transfer; the exact-field regression adds that field while retaining
every old assertion. Other582old methods and all73old scenarios are unchanged.
New25unit methods and3local Behat scenarios cover query/URI/row/page/redaction,
transport failures, personal guards/no fallback, data preservation, form/CSRF and
controller output. Provider HTTP and OAuth are doubles, not live integration proof.

Counts:503repository/479distributed files,200distributedPHP,1519namedPHPcallables
(1512distributed+7helper),654AMDcallables,608unit methods,76expanded scenarios,
8×1066language keys and48unchanged AMD sets. Eight new translated strings per pack;
no prior language value changed. Paired index/inventories/Privacy/guides and ER
release labels describe the same tree; XMLDB/upgrade,9AJAX,AMD/source policy/demo
and production Analytics remain unchanged.

## Evidence limits and roadmap

Local source/lexical/contract, language/inventory/link and patch application/reversal
checks are distinct from PHP execution. PHP/Moodle are unavailable locally; native
268 PHPCS/lint, PHPUnit, Behat, analysis and server checks are pending. Acceptance
requires six candidate jobs, six main jobs, actual ZIP/server and maintainer strict50/53.

Live tenant search, OAuth browser/preview, Entra-header authentication and supported
time/play/pause/seek are unverified/unimplemented gates. No learner source/tracking
or activity-reference persistence/backup is introduced. Microsoft first, then Zoom
(optional separate repository), Google Drive and App. Manual5.3 accessibility remains
STANDBY; preserve demo and historical Node harness debt.
