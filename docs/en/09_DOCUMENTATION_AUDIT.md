# Documentation audit

Current tree: VideoTrack **1.7.266** (`2026112001`). Accepted actual baseline:
**1.7.265** (`2026112000`). Initial native candidate266 failed the Code Checker;
the corrected candidate still requires a complete native run. Main266 and live
Microsoft preview tests remain unexecuted.

## Accepted265 source and CI

Approved candidate `9cbf6761e63875305ed7faa0134d041c0665c7bd` and main
`dba3cd1be9c9ee94ea2ff9622cdbc2d719d45915` share tree
`4b8b075dfb7c79b617a8a7789840668e55a77e81`. All485repository blobs and modes
were compared with the remote tree. The actual461file distribution matches those
blobs and the export-ignore set. No source changes occurred during merge.

Candidate [run44723999463](https://github.com/pierpaolo17/videotrack/actions/runs/44723999463)
finishes with six successful jobs at attempt2. Initial5.3 loses localhost:8000;
the full fresh5.3 job136259892993 passes67/1327 without an internal rerun and all
three lifecycle lineages. Initial5.1 fails the post-rollback note visibility
assertion and passes an internal rerun of one scenario/43steps. Its first-failure
dump is retained by265. Neither the outage cause nor a product fix for the note
is established. The successful rerun does not erase the original evidence.

Main [run44743669261](https://github.com/pierpaolo17/videotrack/actions/runs/44743669261)
passes all six jobs at attempt1, each with Unit530/5612, Behat67/1327 without
internal reruns, strict10/0/0 and Grunt. All six official report digests match.
Canonical191distributedPHP+1helper, PHPDoc, metadata,206savepoints and Mustache pass.
PHPMD retains31reviewed baseline findings. PHPStan/Psalm have no configured errors;
Psalm750/778information remain unclassified. No new source correction is inferred
from the clean main5.1 run. On50/53MariaDB all three historical/partial/previous
lifecycle paths pass, with six independently reconstructed snapshot hashes and
126actual shared-file content checks.

## Actual265 package and server acceptance

Actual `videotrack-1.7.265.zip`:1,953,818bytes,461files/191PHP, archive comment
accepted maindba3cd1b. SHA256:
`692451bf677f2f7a6f7f4d2868f1964651431f6625947ad098761c8623533f24`.
Server `report_completo_videotrack_1.7.265_20261120_151810.zip` SHA256:
`4630a0b67cb7f5b096cc0f18a20ed4a72951b87782c49c719a648793e00295a1`.

All eight requested command results pass. Shared identical-source canonical/lint
is one execution over192PHP files, zero PHPCS errors/warnings and empty stderr.
Separate Unit530/5612 runs pass on Moodle5.0.11 and5.3+, PHP8.3.6/MariaDB11.4.14.
Both Grunt tasks complete; recorded before/after build hash
`ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb` is unchanged.
All48maps in the actual ZIP independently match their source content. No independent
reconstruction of the server aggregate build hash is claimed.

Maintainer strict is10pass/0warn/0fail each, installed/file2026112000, grade items/
rows0/0 on5.0 and34/34 on5.3. XMLDB7tables/151fields/22explicit indexes/22foreign keys,
9AJAX endpoints,8×1040language keys and48AMD sets agree. This completes265 acceptance.

## Current266 implementation

An explicit administrator POST/sesskey action requests a native Microsoft preview
with the current user's connected work/school identity. The normal GET remains
local; system identity is refused for preview. Existing user guard/client creation
is shared with metadata and original-page actions. An internal dispatch returns
diagnostic/URL pairs that each public method wraps in its own filtered result type.
The original metadata/page contracts and system CLI are retained.

Preview requires a valid video metadata GET before one fixed-host Graphv1 JSON
POST with the same drive/item ids. Both requests preserve TLS, bounded timeouts and
no redirects/retries. Preview parsing uses the same1MiB/depth16 limits after receipt.
Only a supported HTTPS getUrl is retained privately; POST-only previews, unsafe
URIs, unsupported hosts and non-embed paths cannot become frames. JSON omits the
temporary URL and raw provider fields. The protected non-cacheable response uses
escaped iframe attributes, a title, responsive width, no-referrer and a bounded
sandbox. No activity reference or preview URL is persisted, no content is imported
or proxied, and no student viewing/completion/grade evidence is created.

The Privacy API now declares Microsoft diagnostic/preview transfers using resource
ids, core-managed authorisation, temporary URL and browser technical-data fields.
Paired privacy summaries and all eight language packs describe the boundary. The
feature is documented in the [private-preview guide](37_MICROSOFT_PRIVATE_PREVIEW.md),
including supported destination shapes, caller-access implications and live checks.

Thirty new native unit methods and three browser scenarios are authored.
One existing original-page controller regression is deliberately scoped to its
link-rendering branch; its no-iframe boundary is retained while the separately
guarded preview branch gains a frame. The other529previous native test methods
and all67previous expanded browser scenarios remain unchanged. No assertion or
CI rule is removed to permit the new action.

Counts:490repository/466distributed files,193distributedPHP,1426namedPHPcallables
(1419distributed+7helper),654AMDcallables,560unit methods,70expanded scenarios,
8×1050language keys and48AMD source/build/map sets. Ten keys per language are added:
six preview strings and four Privacy metadata strings; the diagnostic notice is revised.

The preview guard recognises non-empty embedded credential keys; plain/unfamiliar
authentication addresses yield preview_authentication_unverified with no iframe.
The announced1April2027 Entra-header transition is documented, not implemented.

## Initial266 CI and incremental correction

[Run44889284032](https://github.com/pierpaolo17/videotrack/actions/runs/44889284032),
attempt1 at candidate `0edd6d231004536e5ab18346cca94a527eb0e19e`, uses expected tree
`998dcf720f44ff9dce35c55a131baf4a12933fbc`. All six received ZIPs match official
sizes and digests. Every configuration passes Unit560tests/6074assertions,
Behat70scenarios/1358steps without retries, strict10/0/0 and Grunt.

The50MariaDB job fails28PHPCS formatting errors across19lines of the new preview
test: eight multiline foreach controls and three argument lists. The other five
jobs pass. Distributed/helper lint, PHPDoc, metadata,206savepoints and Mustache
complete;50lifecycle is skipped and is not a pass. On53MariaDB all three historical/
partial/previous lineages pass: three independently reconstructed JSON snapshot
hashes match both verification stages,31/0 each; uninstall26/0 per lineage and63
actual shared-file content checks appear in the complete lifecycle log.

PHPMD records32findings:31baseline plus a new Graph class-complexity55 advisory.
PHPStan/Psalm report no configured errors;751/779Psalm information findings remain
unclassified. The informational total increases by one from265; a green tool exit
does not classify that finding or establish a fix.

The correction changes only test formatting, retaining all tokens and assertions.
Move the unchanged preview-response parser body into the
`microsoft_preview_result::from_response` factory, separating Graph transport from
result parsing. Preserve1MiB/depth16 limits, error reduction inside the same Graph
try/catch, URL guards and diagnostics. No PHPMD suppression or threshold changes;
the corrected CI must confirm removal of the new complexity advisory. File,
callable and test counts remain unchanged.

## Evidence and remaining gates

The initial CI does not approve266 for merge. Its native results belong to the
original candidate, not the corrected source. Local checks cover unchanged test
tokens and moved parser body, inventories and patch application/reversal. PHP and
Moodle are unavailable here: corrected PHPCS, lint, PHPUnit, Behat and static
analysis require the fresh native CI. Unit HTTP responses are synthetic; browser
scenarios never request a real tenant. Corrected candidate six jobs, main six jobs,
actual ZIP/server and both strict outputs remain required before266 acceptance.
Remote main remains at accepted265.

No real connected Microsoft browser round trip or successful native iframe has
been demonstrated. Preview readiness means a validated address, not actual browser
playback, learner resource rights or supported time/play/pause/seek telemetry.
Those live/provider contracts and stable activity launch references remain gates
before tracked Microsoft playback. Keep Microsoft first, then Zoom (optional
separate Moodle repository), Google Drive and finally App.

Schema/upgrade, AJAX, AMD, source availability, OAuth connection handshake, CI rules,
production Analytics and demo are retained. Manual5.3 accessibility stays **STANDBY**.
Historical Node harness debt and the intermittent note assertion are not declared fixed.
