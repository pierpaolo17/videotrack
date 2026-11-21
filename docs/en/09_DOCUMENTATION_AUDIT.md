# Documentation audit

Current tree: VideoTrack **1.7.267** (`2026112100`). Accepted actual baseline:
**1.7.266** (`2026112001`). New267 native CI/server and live Microsoft browser
preview are pending; completed266 evidence is scoped below.

## Accepted266 source and CI

Corrected candidate `eb60c8ec60ab4a41fe9bdcb37fad4528089c0706` and main
`b9a981797bede2bbabf2c58ec096dc29840ddc0c` share tree
`5b59ea93ee62edf1a0b621a0f8196053caa04cfa`. Annotated tagv1.7.266 points to that
main. All490repository blob identities/modes and the466file export set match the
actual archive. The merge introduces no source difference.

Initial candidate0edd6d23 / [run44889284032](https://github.com/pierpaolo17/videotrack/actions/runs/44889284032)
passes all functional tests but fails28PHPCS formatting errors in the new preview
test. Its50lifecycle is skipped;53passes. PHPMD also adds Graph class complexity55
to31baseline findings. The correction preserves test tokens/assertions and moves
the identical preview parser body into the result factory, retaining limits and
the Graph exception boundary. No CI rule or analysis threshold is weakened.

Corrected [candidate44894561003](https://github.com/pierpaolo17/videotrack/actions/runs/44894561003)
and [main44897318831](https://github.com/pierpaolo17/videotrack/actions/runs/44897318831)
each pass all six jobs on attempt1. Each configuration passes Unit560tests/
6074assertions, Behat70scenarios/1358steps without retry markers, strict10/0/0
and Grunt. All twelve received artifact digests match official records. Canonical
193distributedPHP plus lifecycle helper, PHPDoc, metadata,206savepoints and Mustache
complete. The28PHPCS errors and Graph PHPMD finding are gone;31exact baseline
findings remain. PHPStan/Psalm report no configured errors;751/779Psalm information
findings remain unclassified, one above265. No source fix for the older transient
note assertion or localhost outage is inferred from a passing run.

Both50/53MariaDB jobs complete historical/partial/previous lifecycle lineages.
For each run, six JSON snapshot hashes are independently reconstructed and match
both verification stages31/0. Each uninstall reports26/0; the logs contain126actual
shared-file content checks per run. Skipped lifecycle jobs on other matrix entries
are not relabelled as passes.

## Actual266 package and maintainer server

Actual `videotrack-1.7.266(1).zip`:1,986,549bytes,466files/193PHP, archive comment
mainb9a98179. SHA256:
`73b0d52f1ea0802e2f410b36feb6e335f2a7936a286a6778867b07c621130142`.
Report `report_completo_videotrack_1.7.266_20261121_124248.zip`:14,641bytes, SHA256:
`ba0345e936e2173ef35c1c8ea0e430177e311237bed2a6a87b63107f4e7c241f`.

All eight requested command outcomes pass. Canonical/lint are shared executions
over identical code194PHP, not two independent runs; PHPCS0errors/0warnings,
stderr empty and194lint successes. PHPUnit separately passes560/6074 on Moodle5.0.11
and5.3+, PHP8.3.6/MariaDB11.4.14. Both Grunt commands complete with recorded before/
after build hash unchanged:
`ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb`.
All48archive source maps independently match source content. No independent server
aggregate-build hash reconstruction is claimed.

Both maintainer strict reports pass10/0/0 at installed/file2026112001. Grade items/
rows are0/0 on50 and34/34 on53. XMLDB7tables/151fields/22explicit indexes/22foreign
keys,9AJAX,8×1050language keys and48AMD sets are consistent. Actual266 is accepted.

## Current267 implementation

[Reusable Microsoft references](38_MICROSOFT_VIDEO_REFERENCES.md) encode a versioned
local issuer/drive/video identity. Only the five documented fields/types and existing
opaque-id grammar are accepted in a4KiB/depth4 JSON string. No temporary URL, token,
path, scope or caller identity is accepted. The current selected issuer id is checked
locally and again when the validated POST target is resolved after sesskey validation.
The issuer number is local, not a tenant pin or a portable connection configuration.

Manual input remains the default; reference mode requires a personal video target.
Existing probe actions perform all actual OAuth/access/video checks and return fresh
results; no shared fallback, URL cache, automatic retry or background refresh is
added. A separate escaped read-only field offers identity only after a successful
personal video result. Probe results omit resource ids/URLs; ordinary reference
object JSON does not expose private fields. Existing core OAuth may refresh its own
tokens. No new VideoTrack setting, schema or activity/session persistence is added.

All560old unit methods and70expanded browser scenarios remain byte-identical in
their original files. New23unit methods cover parsing/type/field/URL boundaries,
issuer changes, personal-account/form/CSRF guards, fresh previews, denial after
success and unchanged activity data. Three new browser scenarios are local-only:
default GET, invalid URL input and wrong-service/shared-account rejection.

Counts:495repository/471distributed files,195distributedPHP,1467namedPHPcallables
(1460distributed+7helper),654AMDcallables,583unit methods,73expanded scenarios,
8×1058language keys and48unchanged AMD source/build/map sets. No prior language
value is changed; eight translated identity strings are added per pack. Paired
inventories, index, guides and ER release labels describe the same current tree.

## Evidence limits and next gates

Local lexical/source, preserved-contract, inventory/link/language and clean patch
application/reversal checks are distinct from running PHP. PHP/Moodle are unavailable
in this workspace; native267 PHPCS/lint, PHPUnit, Behat, analysis and server checks
remain pending. Native267 candidate six jobs, main six jobs, actual ZIP/server and
both maintainer strict outputs are required before acceptance.

No live Microsoft tenant/OAuth browser round trip, native iframe playback or
supported time/play/pause/seek telemetry is established. Reference reuse does not
implement the announced Entra-header preview authentication transition. Stable
activity persistence/backup and learner launch resolution remain later work before
tracked Microsoft playback. Microsoft first, then Zoom (optional separate Moodle
repository), Google Drive and App. XMLDB/upgrade,9AJAX,48AMD, source availability,
OAuth handshake, Privacy transport, production Analytics and demo stay unchanged.
Manual5.3 accessibility remains **STANDBY**; historical Node harness debt remains.
