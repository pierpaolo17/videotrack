# Documentation audit

Current tree: VideoTrack **1.7.265** (`2026112000`). Accepted release baseline:
**1.7.264** (`2026111901`). Candidate265 has not yet executed native Moodle CI.

## Accepted264 source and native CI

Corrected candidate `f77cd0c2897bcce5d3ecf3529af03f5e3b87a120` and main
`247bfb4b3791b8bb32fc66992b10319d4ac67ded` share tree
`bea744d925670e857ea486f2c0d2a5831de57414`. The complete483file repository was
reconstructed from the actual distribution and24repository-only blobs; its Git
tree matches exactly, including modes. All459distributed files match that tree
and the export-ignore set. Source equivalence is independent of the native tests.

Corrected candidate [run44572433050](https://github.com/pierpaolo17/videotrack/actions/runs/44572433050)
passes all six jobs: Unit530/5612, Behat67/1327 without internal reruns, strict10/0/0
and Grunt. Main [run44702501925](https://github.com/pierpaolo17/videotrack/actions/runs/44702501925)
also finishes with six successful jobs, at run attempt2. Its six latest successful
report digests match the official artifact digests. Each has Unit530/5612 and
strict10/0/0, but main Behat history is explicitly retained:

- 5.1 initially fails a post-rollback YouTube note visibility assertion. The internal
  rerun passes one scenario/43steps. No first-failure browser dump was published by
  the old success-dependent upload condition. A product root cause is not established.
- Initial5.2 loses localhost:8000 during Behat; its internal retries cannot reach the
  service. Fresh complete job136212821667 passes67/1327 without an internal rerun,
  using the same source. The outage cause is not established; no OOM cause is inferred.
- The other four main jobs pass67/1327 without internal reruns. Thus five final
  successful reports are clean full browser runs, while5.1 retains its retry history.

Canonical lint/PHPCS191distributedPHP+1helper, PHPDoc, metadata and206savepoints pass.
PHPMD retains31reviewed baseline findings. PHPStan/Psalm report no configured errors;
Psalm750/778information remain, four more than263, with types unprinted. No resolution
or cause of that information delta is claimed. All three historical/partial/previous
lifecycle lineages pass on50/53MariaDB, with six independently reconstructed snapshot
hashes and126actual shared-file content checks in main acceptance.

Initial264 candidate `e07044204677edd6aba4d79714aa6f6ed41fd810` passed functional
tests but failed four boilerplate spacing errors and added a PHPMD boolean-argument
finding. Same-version corrections restore the headers, move callback error handling
inside nonce-only completion after request validation, and strengthen denial/replay.
The corrected candidate evidence above supersedes that incomplete acceptance; the
initial50lifecycle skip is not described as a pass.

## Actual264 package and maintainer server

`videotrack-1.7.264.zip`:1,946,922bytes,459files/191PHP; archive comment is accepted
main247bfb4b. SHA256:
`9a4809bd0731c2019a2dc09591bc448df0c33e73528cb38199324d4f2c96aceb`.
`report_completo_videotrack_1.7.264_20261120_113541.zip` SHA256:
`b844fc9132bab20cc22d586c9e20bcff371e842428c5c9a8b13b4835a5a7cff4`.

All eight requested command results pass. PHPCS/lint are one shared identical-source
execution:192PHP files, zero errors/warnings; empty PHPCS stderr. Unit530/5612 passes
separately on Moodle5.0.11 and5.3+, PHP8.3.6/MariaDB11.4.14. Both Grunt tasks complete,
with recorded before/after build hash
`ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb` unchanged.
All48source maps in the actual ZIP independently match their source content. No
independent reconstruction of the server's aggregate build hash is claimed.

Maintainer strict:10pass/0warn/0fail on each server; grade items/rows0/0 on5.0 and
34/34 on5.3. Installed/file2026111901, schema7tables/151fields/22explicit indexes/
22foreign keys,9AJAX endpoints,8packs×1040keys and48AMD sets agree. This completes264
acceptance; local265 checks do not extend those native results to a new candidate.

## Current265 changes and evidence boundary

The workflow attempts browser faildump upload on every outcome, including a successful
internal retry. Missing dumps remain ignored; names and14day retention are retained.
Immediately after a completed Behat step and before lifecycle, a separate step records
its outcome, UTC time, memory and process names/RSS, without process arguments or
environment. No HTTP probe is run after the tool's normal server teardown. The
snapshot cannot diagnose an earlier outage. See [evidence guide](36_CI_BEHAT_EVIDENCE.md).

The original Behat command, pipefail/exit propagation, rerun defaults, six-entry matrix,
all assertions/quality rules and lifecycle condition are retained. There is no production
behavior change apart from release metadata; production PHP, tests, AMD, languages,
schema/upgrades, services and demo are byte-identical to accepted264. Two paired guide
files are added. Expected counts:485repository/461distributed files,191distributedPHP,
1376namedPHPcallables (1369distributed+7helper),654AMDcallables,530unit methods,
67expanded scenarios,8×1040keys and48AMD source/build/map sets.

Local verification covers YAML structure and unchanged gates, shell syntax/execution
of the snapshot with success/failure outcomes and unavailable resource utilities,
source equivalence, documentation links/counts and clean patch apply/reverse. These
are local checks, not native265 PHP/Moodle/Actions results. Candidate six jobs, main
six jobs, actual ZIP/server and both strict outputs remain required before265 acceptance.

## Roadmap and retained limits

The real personal Microsoft OAuth browser round trip and live delegated Graph/video
access are unexecuted. An administrator diagnostic or connection-success notice does
not prove learner rights or player telemetry. Stable activity references/launch
resolution and supported provider authentication plus time/play/pause/seek telemetry
remain gates before tracked Microsoft playback. Keep Microsoft first, then Zoom
(optional separate Moodle repository), Google Drive and finally App. No import or
Moodle streaming proxy is introduced. Production Analytics is not optimised here.

Manual Moodle5.3 accessibility stays **STANDBY**. Retain the demo dataset and the
existing historical Node harness debt; neither is declared fixed or cleared.
English/Italian documentation and ER release labels describe this same current tree.
