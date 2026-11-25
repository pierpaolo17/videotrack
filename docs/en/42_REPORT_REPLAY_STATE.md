# Report replay and provider-state ordering

VideoTrack **1.7.272** (`2026112500`) fixes a stale state restoration observed with
YouTube report fragments in 1.7.270. It is a playback correction; the database schema,
learner records, completion rules, permissions and source availability are unchanged.
No upgrade data repair or completion recalculation is required for this fix.

## Reproduced failure

The report opens view.php with an explicit replaystart/replayend. YouTube onReady
seeks to the fragment while the tracker is still paused, then requests playback.
The provider's PLAYING notification opens a playing segment. A later polling cycle
consumes the pending programmatic-seek flag and unconditionally restores the earlier
paused snapshot. With forward seeking disabled, subsequent normal time progression
is interpreted as an unauthorised jump. The guard rewinds and applies the configured
blockedseekplaybackrate, producing the reported repeat and delayed speed change.

A deterministic test reproduces this on both the accepted 270 source and its actual
native compiled AMD: after PLAYING the tracker becomes paused and the next poll
seeks back and applies 0.5 x. Staff replay emits no learner AJAX, so a database write
is not needed to trigger the defect. The same ordering can interrupt learner replay.

## Correction and boundaries

consumeProgrammaticSeek restores its captured fallback only while isSeeking remains
true. A newer provider play, pause, end or destruction event has already cleared
that flag and must remain authoritative. If no provider state event has arrived,
the previous playing/paused fallback is retained. The flag is consumed exactly once.

The change is in the shared tracker helper. Provider adapters and seek policy remain
unchanged. A real forbidden forward jump still returns to the trusted frontier,
applies the configured recovery rate and never credits the skipped interval. Report
access never grants learner participation. Staff preview still creates no learner
tracking, grade or completion records. Existing saved progress is not rewritten.

## Verification

Three added Node groups cover later play/pause/end/destruction events, report replay
for staff and learners at each recovery rate 0.5/0.75/1, and a genuine forbidden seek
after replay. They retain all six existing lifecycle groups and the tracker harness.
The source suite passes nine groups; the old compiled modules fail all three new
groups as expected. This is production AMD exercised with deterministic provider,
AJAX and time doubles, not real browser/provider acceptance.

Two new Behat scenarios create a reaction through actual fixture playback and click
the real report replay link. The teacher must keep normal speed without learner
records; the learner must retain accepted viewing and a real pause. These scenarios
use existing steps and deterministic YouTube fixtures, never seeded watch evidence.
Native Behat execution remains pending.

The native Grunt build is a release gate. A maintainer-side runner checks the exact
accepted 271 source/build hashes, backs up the test site's AMD tree, temporarily
applies this one helper correction, runs npx --no-install grunt amd and all nine
Node groups on sources and compiled modules, then restores the originals. Only
successful compilation/regressions plus exact restoration produce a native archive.
It loads no Moodle config.php and issues no database command. Its artifact records
maintainer-server provenance, source/build digests, command and log; it is not labelled
as a GitHub build. No manual minification or source-map editing is accepted.

The maintainer native archive is now verified: SHA256
a6ce3d08275774f01fb2cd4b350c2f07c405f0336533f445705f894ff3175eb2.
The actual Grunt command exits 0; its source and compiled tests each pass all nine
groups. The source/fixture bytes, 48 source/96 build digests, canonical maps, log digest
and original-tree restoration marker match. Only this helper and its two native
outputs change. The supplied outputs are included without manual minification.
The same nine groups plus the tracker harness also pass after local incorporation.

The first builder attempt stopped because the shell used Node 26; Moodle 5.0 Grunt
requires >=22.11 and <23. Selecting its supported Node 22 environment allowed the
recorded successful build. This artifact does not record an exact patch-level Node
version, so none is claimed from it. Node selection is per shell; nvm use default
can restore the configured default after compilation. It does not alter plugin runtime.

Native 272 CI/server (including 82 Behat scenarios) and live teacher replay checks remain
pending. Microsoft HTTPS/account acceptance and the provider/App roadmap stay separate.
