# Tracking continuity and historical recovery hotfix

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

PHP, Moodle and MariaDB are not installed in the local workspace. Native PHP lint,
PHPCS, PHPUnit, Behat, static analysis and Moodle Grunt are pending CI/server checks.
The shipped 12 changed AMD sets were compiled with Babel and maps containing the
exact sources; local tests run against both sources and shipped builds. This is not
a claim of native Moodle/browser acceptance or a live external-provider test.

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
