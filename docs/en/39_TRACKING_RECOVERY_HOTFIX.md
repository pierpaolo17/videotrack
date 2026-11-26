# Tracking continuity and historical recovery hotfix

> Historical 269recovery procedure: from 1.7.274 the browser checkbox and request path are retired. Ordinary completion recalculation remains available and continues to count accepted `recovery269` rows. The legacy repair engine remains only as an internal compatibility API; ordinary recalculation does not invoke it. No original or repaired evidence is erased.


VideoTrack **1.7.269** (`2026112300`), built from accepted **1.7.267**.
The abandoned Microsoft-search268 patch is excluded. Its work remains paused.

## Corrected behaviour

YouTube polls always reach the heartbeat scheduler, including the natural-playback
branches used when forward seeking is disabled. Previously their early returns
could leave a long final span that the strict server heartbeat cap rejected.
A later span beyond the server's watched frontier was then also rejected.

Pause, tab hide and random attention pause capture the current boundary immediately.
A cursor shared with an in-flight heartbeat advances only after acknowledged
persistence. The queued close saves its remaining tail once, without double charging
an overlapping range. Resume handshakes wait for earlier final saves. Late replies
cannot reopen a paused segment or overwrite a different seek/rewatch segment.
A rejected/null heartbeat does not advance the cursor as if it were persisted.

Resuming never copies the initial page intervals over newer acknowledged progress.
Successful out-of-order responses retain their acknowledged interval union. Empty
segment responses return the user's existing state instead of zero/empty defaults.
The configured duration is used consistently for live percentages across all three
players. Unique watched seconds, rather than the furthest playhead position, remain
the numerator: 96 unique seconds of 1172 seconds is 8.19%, while repeated viewing of
already covered seconds adds no unique coverage.

Scrolling inside the same document creates no viewport observation, integrity event
or tracking close. Historical `outofviewport` rows remain available. Actual document
hide still closes the visible watched tail and may trigger the configured pause;
it never removes earlier credit. Returning to a still-playing visible document uses
a fresh handshake. If background playback passed the watched frontier with forward
seeking disabled, it pauses and returns to that frontier without a seek penalty.
Vimeo fallback polling also requires a fresh server handshake after a closed window.

## Preserve and repair existing evidence

Installing this release changes no XMLDB tables, fields, indexes or foreign keys and
performs no bulk data migration/deletion. Notes, bookmarks, reactions and historical
rows remain. The live server heartbeat cap, session owner, rate ceiling, idempotency,
seek frontier and watched-position validation are retained. The1.7.52boundary repair,
1.7.101 session binding and1.7.102 interaction-evidence rules remain in force.

After a verified full database backup, use the teacher report's **Recalculate** page.
Ordinary recalculation remains validated-evidence-only, including privacy-retention
callers. The new unchecked **Recover long segments affected by missing heartbeats**
option explicitly enables historical reconstruction for the selected learner(s).
Start with one affected learner and inspect the result before selecting everyone.

Repair requires a same-activity/course/module/video/user server-recorded `playstart`,
matching active session, supported rate, a starvation-sized raw span, no forbidden
forward jump, and cumulative playback time fitting server receipt timestamps. Client
wallclock timestamps grant no authority. New handshakes supersede earlier tabs;
terminal/seek rows end a window. It cannot recover missing rows, ambiguous races,
short unrelated rejections or an unknown duration. Current activity restrictions
apply; changes in the configured heartbeat/rate/video may limit recovery.

Under the canonical per-user lock and transaction, qualifying original rows are
preserved byte-for-byte and identifiable derived rows are appended with reason
`recovery269` and deterministic `recovery269_<sha1(original id)>` request ids. These
are explicitly administrator-reconstructed evidence, not newly observed playback.
Normal Analytics/notes use the resulting validated rows; aggregate state and native
Moodle completion are then recalculated through the existing authorised pathway.
Repeating repair does not duplicate rows. There is no automatic database restore or
cleanup; repairs are not a substitute for a backup.

## Tests and remaining execution

Local source and compiled AMD tests cover a 440 second no-forward-seek YouTube watch,
heartbeat/pause concurrency, immediate tab boundaries, random pauses, scroll policy,
late replies, rejected saves and configured-duration percentages. Five targeted
regressions fail against the accepted267 sources and pass with this patch. The older
standalone tracker harness is made runnable with a debug stub, accurate wallclock
payload expectations and deterministic async waiting; its assertions are retained.

Twelve additional PHPUnit methods cover explicit/idempotent repair, original-row
preservation, interaction authorisation, forged clocks, session switches, lifecycle
boundaries, double spending, rate/duration bounds and empty endpoint snapshots.
Two browser scenarios are added; three existing random-pause scenarios now also
assert retained contiguous coverage. CI runs the standalone source/build tests.

PHP, Moodle and MariaDB are not installed in the local workspace. Native CI results
and remaining acceptance checks are recorded below. The initial Babel output for
12 changed AMD sets has been replaced with verified native Grunt output. Local tests
run against both sources and shipped builds; live external-provider acceptance
remains separate from automated browser fixtures.

The release gate must retain existing schema/privacy/security/lifecycle checks.
Do not tag/package unreviewed main or merge the abandoned Microsoft 268 branch.
Because an installed268 has a higher database version than267, upgrade directly to
269; do not edit `config_plugins.version`, uninstall VideoTrack or restore an old
full database over learners' new work to manufacture a Moodle downgrade.

## Lessons and next work

Long no-forward-seek viewing must be tested, rather than relying only on short
play/pause fixtures. Heartbeats must run independently of seek-classification early
returns. Persisted progress and page-initial configuration have different lifetimes.
A pause is a boundary save, never a command to erase acknowledged progress. Scroll
is necessary for study controls and carries no integrity inference.

Microsoft search 268 remains paused and must be rebased/tested after this hotfix is
accepted. Then continue the existing provider roadmap: Microsoft, optional separate
Zoom repository, Google Drive, and finally App. Keep the screenshot/demo dataset and
manual 5.3 accessibility standby unchanged.

## Initial 269 CI failure and corrective candidate

[Run 45227903298](https://github.com/pierpaolo17/videotrack/actions/runs/45227903298)
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
search remain paused. At this preparatory stage, corrected native execution was pending.

## Corrected CI and canonical builds

[Run 45240781127](https://github.com/pierpaolo17/videotrack/actions/runs/45240781127)
tests correction1 commit `b0814083eb44e83598bb20406e7549d8e006b536`.
All six matrices pass PHPUnit595/6280, Behat75/1428 without reruns, standalone JS
and strict10/0/0. The quality job passes lint, PHPCS and PHPDoc. Only AMD freshness
fails: native compilation succeeds but the submitted initial build files differ.

All six independently captured native outputs have matching hashes for all96files,
48 matching source hashes and exact-source maps. Replace the24differing build/map
files with this output, without changing PHP, AMD sources, tests or recovery policy.
Keep the original freshness gate. Rerun full candidate CI, including previously
skipped historical upgrade/populated uninstall, then main CI and server acceptance.
No production repair or local native Grunt execution is claimed by this import.

## Acceptance and resumed work

Accepted269 matches the uploaded ZIP; candidate/main each pass six jobs, server
canonical/lint198, both Unit595/6280 and native Grunt pass, both strict10/0/0.
The maintainer reports installation/recalculation success on2026-11-24.
New270 resumes Microsoft search while preserving all tracking/recovery code.
