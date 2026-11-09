# Behat browser automation

Accepted **1.7.239** passes **44 scenarios / 830 steps** and **379 PHPUnit tests / 4,076 assertions** in all six corrected release/main CI jobs. Its actual ZIP, server report and both strict outputs are accepted. Server PHPCS/lint, Grunt and PHPUnit pass; the report does not execute Behat. Candidate **1.7.240** expects **50 scenarios / 1,074 steps** from source, pending actual candidate CI. Six environments use one Chrome profile, not six browsers.

## Setup and execution

Configure Moodle's isolated Behat site (`behat_wwwroot`, `behat_dataroot`, `behat_prefix`) and initialise it after
changes to generators, steps or feature files:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Never point Behat at production data.

## Deterministic fixtures

- `behathtml5fixture=1` creates an upload activity backed by the local 60-second MP4 fixture.
- `behatproviderfixture=youtube` uses a reserved id and local YouTube SDK double only under Behat.
- `behatproviderfixture=vimeo` uses a reserved id and local Vimeo SDK double only under Behat.
- `behatlinkedforum=<name>` maps a Forum fixture in the same course.

Production provider code paths remain in use; only external SDK/network behaviour is replaced, keeping scenarios
repeatable without public-provider availability.

## Current coverage

The suite verifies:

- learner, teacher-only and dual-role participation behaviour;
- visible reaction/note/bookmark composers and independently collapsible saved histories;
- absence of reaction, note, bookmark and Forum sections when their effective configuration is unavailable;
- HTML5 play/pause session lifecycle and accepted terminal ledger write;
- trusted resume, backward seek and allowed/blocked forward seek;
- pre-seek snapshot persistence and exclusion of skipped gaps from coverage/resume;
- reaction, note, bookmark and linked-Forum timestamps after rollback;
- acknowledgement at any time and after validated final-second coverage;
- Moodle completion persistence for viewing, interactions and acknowledgement;
- stacked persistent/transient status notices;
- strict focus policy and course exception-group resolution;
- deterministic YouTube and Vimeo resume/seek/recovery/terminal pause contracts;
- grouped completion requirements and vertical layout across Moodle's native/ARIA list variants.

## Browser-driven viewing completion

`browser_tracking_completion.feature` adds one case for HTML5 and one each for the production YouTube/Vimeo
adapters with local SDK doubles. Each fixture disables autoplay/resume and view-only/acknowledgement completion,
uses a 60-second duration and a 5% viewing threshold, and starts with no server-validated ledger row and incomplete
native completion. The browser plays for about five seconds, pauses through the actual player/provider surface,
then the assertions require an accepted same-session pause, completed aggregate viewing state and the persisted
`course_modules_completion` row. Reload must retain completion. Short playback makes the case practical while
still requiring a positive three-second server-validated threshold; no seek or progress seed crosses that threshold.

The two new assertions read only. They cannot insert/update ledger, state or completion records, invoke the segment
endpoint directly, fabricate AJAX responses or mark a row complete. Database polling waits at most five seconds
with short retries for asynchronous writes, following the existing pause-lifecycle assertion.

The older `html5_completion_contract.feature` viewing case deliberately seeds evidence to test synchronisation
from an existing state. Its scenario name now states that purpose. It remains useful alongside the new live
browser-to-AJAX-to-database checks and is not evidence of browser-generated viewing by itself.

## Allowed forward seek without gap credit

`browser_allowed_seek_tracking.feature` adds one unseeded case for each provider. The fixture explicitly
enables forward seeking, uses a 60-second duration and automatic 50% viewing completion, and disables
autoplay/resume and view-only/acknowledgement completion. The browser starts from no validated evidence,
plays into the 4-10 second range, seeks to 30 while playing, continues into the 33-39 second range and pauses.
HTML5 uses the real local media; YouTube and Vimeo use production adapters against the unchanged SDK doubles.

The new read-only assertion polls the actual database for at most five seconds. It requires a positive
validated seek row before 12 seconds and a later positive validated pause starting at or after 29.5 seconds,
both from the same session. Every validated ledger interval and persisted aggregate interval must leave the
deliberately unwatched 12-29.5 second middle uncovered. Aggregate unique seconds must equal the sum of its
intervals and remain below 30 seconds; the resume position must advance beyond 33 seconds without completing
the viewing requirement. Native Moodle completion remains incomplete, including after reload.

The 12-second boundary allows the normal polling/seek tolerance around the first playback window; 29.5 seconds
allows the provider seek tolerance around the 30-second target. No progress, ledger row, completion state or
AJAX response is seeded by these cases. Existing browser bindings and SDK fixtures are unchanged.

## Post-rollback interactions and preserved completion

`browser_rollback_interactions.feature` adds one unseeded case per provider, with local HTML5 media and the
unchanged local YouTube/Vimeo SDK doubles. Each activity disables autoplay/resume, forward seeking, reaction-based
completion and acknowledgement completion; its automatic 5% viewing threshold requires three validated seconds
of the 60-second fixture. Play/pause must earn actual aggregate and native completion before the attempted
30-second seek. This combines browser-earned completion with rollback and interaction contracts in one case.

The first new read-only assertion requires the player to remain paused within 1.25 seconds of the persisted
frontier for 750ms, with a seven-second polling bound. Reaction, note and bookmark are then saved through their
visible controls and verified in the corresponding history lists. The browser opens the linked Forum composer;
the test verifies the visible form and its actual URL rather than submitting a discussion.

The second assertion polls for at most five seconds. It reads exactly one active reaction, one note and one
bookmark, checks their labels/types and timestamps, the course-module scope and the session of the accepted
pause. Event timestamps and the rounded Forum URL timestamp must match the actual frontier within 1.25 seconds;
the composer URL must target this activity and the same session. Frontier and unique coverage remain below
22 seconds, below the refused 30-second target. Aggregate and native completion must still be complete,
including after returning to the activity. No ledger, state, interaction, completion or AJAX result is injected.

The older seeded HTML5 rollback case remains unchanged and still covers its original fixture-based purpose.
The new cases exercise browser-generated evidence for all three providers. The 1.7.239 candidate adds ended/replay/rate coverage below. Relevant role/group/browser combinations remain
explicit U-007 work; these cases do not close the phase by themselves.

## Initial 1.7.237 CI correction

Initial commit `04b2fc284b62752a4a3bdeb113031049d46a060e` fails the new paused-frontier assertion for
HTML5 and Vimeo on every CI job. Five jobs finish with 31/33 scenarios passed; Moodle 5.2 also loses the local
HTTP service with `ERR_CONNECTION_REFUSED` later in the suite. PHPUnit remains 369 tests / 3,988 assertions,
strict validation is 10/0/0 and the configured static/build gates pass. This is failed candidate evidence.

The test assumed recovery would retain a pause. Existing HTML5 recent-playback detection and Vimeo user-seek
recovery may instead resume playback. The new explicit pause action observes media state and time through native
or unchanged SDK-double APIs: a paused or advancing phase must remain stable for 6.5 seconds, past the existing
six-second recovery guards. An advancing phase must also advance by at least six media seconds. A playback-state
change or backward jump resets the observation; the overall bound is 15 seconds and the media must stay below
25 seconds. The action then calls the real pause surface once. This is bounded media observation, not a fixed sleep
or a write to tracking data. The existing read-only frontier assertion and an accepted-pause assertion follow.

The upper evidence bound becomes 22 seconds to include real recovery playback, still below the refused target
of 30 seconds. Relative timestamp tolerance (1.25 seconds), session/scope, event count/types and native completion
checks are retained. Six added assertion/action steps make the source expectation **33 scenarios / 618 steps**.
That first correction left production adapters, AMD and workflow unchanged; the HTML5 runtime correction below
updates its source and matching Grunt builds while preserving SDK doubles and workflow. The corrected candidate requires all six CI jobs;
the Moodle 5.2 HTTP interruption must also be absent before merge.

## HTML5 recovery playback authorisation

The next candidate, `52a371c0ce821b508f3107477f0c158273164cfb`, passes 32/33 scenarios on all six jobs.
Vimeo and the prior Moodle 5.2 HTTP interruption are resolved in that run. Only HTML5 remains: after explicit
pause the media is near 11.8 seconds while the persisted frontier stays near 5.3 seconds. This is a real
playback-authorisation defect exposed by the unchanged frontier assertion, not a reason to relax its tolerance.

HTML5 recent-playback recovery may resume media after a pause has closed the server window. Previously,
`blockForwardSeek()` opened a client segment based on recovery intent alone; `state.playing` then caused the
actual play handler to skip `Api.beginPlayback()`. The fix captures whether a tracked segment was already open
and snapshots/rotates only that segment. A paused recovery must obtain a new server playback window before
opening a segment or starting the heartbeat. An already-active playback window retains its original path.
The browser tracking session id is preserved, and declined authorisation cannot create a tracked segment.

The actual recovery and tracking-event functions were tested locally with media/server boundaries instrumented:
the old source reproduces the missing handshake, while the fixed source waits for authorisation and retains
active-seek snapshot and idle-pause behaviour. This is an isolated JavaScript check, not full Moodle runtime.
Actual Moodle Grunt AMD with Node 22.23.4 / Grunt 1.6.1 reproduces all 96 old build files byte-for-byte, then
passes ESLint/Rollup for the fix; only the HTML5 minified file/map change, with exact source content in the map.
The 33-scenario / 618-step suite, terminal-pause, relative timestamp, session/scope and completion assertions are
unchanged. All six corrected 1.7.237 CI jobs passed before merge and again on main. U-007 remains open.

## U-007 coverage matrix

| Contract | HTML5 | YouTube | Vimeo |
|---|---|---|---|
| Play/pause and accepted terminal ledger write | Existing | Existing SDK-double case | Existing SDK-double case |
| Resume, backward seek, blocked forward seek/frontier | Existing | Existing SDK-double case | Existing SDK-double case |
| Unseeded playback to native viewing completion | Accepted 1.7.235 case | Accepted 1.7.235 case | Accepted 1.7.235 case |
| Allowed forward seek, gap exclusion and incomplete completion | Accepted 1.7.236 case | Accepted 1.7.236 case | Accepted 1.7.236 case |
| Post-rollback interactions and preserved viewing completion | Accepted unseeded 1.7.237 case | Accepted SDK-double 1.7.237 case | Accepted SDK-double 1.7.237 case |
| Attention configuration and real automatic pause/resume | Accepted 1.7.238 case | Accepted 1.7.238 SDK-double case | Accepted 1.7.238 SDK-double case |
| Natural end, watched replay, allowed rate and above-cap fallback | Accepted 1.7.239 case | Accepted 1.7.239 SDK-double case | Accepted 1.7.239 SDK-double case |
| Staff preview without tracking and dual-role earned completion | New 1.7.240 case, CI pending | New 1.7.240 SDK-double case, CI pending | New 1.7.240 SDK-double case, CI pending |
| Separate-group report selector and direct learner-id boundaries | New shared-endpoint 1.7.240 cases, HTML5 data | Same PHP policy; see shared cases | Same PHP policy; see shared cases |

Learner, teacher-only and dual-role page controls already have shared scenarios. Their existence does not fill
every provider/lifecycle/group combination. U-007 remains open until the roadmap's essential cells and boundary
Moodle runs are evidenced; this tranche does not declare complete provider or browser coverage.

The current workflow invokes the `chrome` Behat profile on six Moodle/PHP/database environments. These are six
environment jobs using one browser profile. Other-browser and live-public-provider smoke results must be recorded
separately, not inferred from that count. U-020 manual accessibility and final production gates also remain separate.

## Selector and assertion rules

Prefer stable component CSS/data markers over translated visible text for interaction targets; assert visible labels
separately. Read persisted Moodle/plugin state when markup varies across supported branches. Media assertions use
bounded timing tolerances and wait for asynchronous writes rather than fixed sleeps.

## Limits

Local SDK doubles verify adapter contracts but not public iframe availability, consent, cookies or provider UI.
Behat does not replace manual keyboard/screen-reader perception tests or real-provider smoke tests. Results apply
only to the exact plugin tree, Moodle branch, browser and driver recorded by the run.

## 1.7.238 attention interval cases

Five read-only browser configuration cases cover automatic thirds, minimum-only, maximum-only, disabled control and zero-duration site fallback. Three additional provider scenarios use explicit 60–60 seconds and play the unchanged 60-second media/SDK fixtures at 0.5x so their end cannot pre-empt the real one-minute attention deadline. No timer or server-clock acceleration is used. Each observes the student pause notice, an accepted pause closing server credit and a stable paused frontier; then it resumes to 31–38 video seconds and pauses again through the provider, requiring a fresh accepted play/pause lifecycle. Existing scenarios and assertions remain intact. All cases passed the accepted 1.7.238 main CI. Real public-provider and multi-browser smoke remain separate U-007 work.

The corrected 1.7.238 cases explicitly select 0.5x and 1x through `playbackspeed_0.5` / `playbackspeed_1`. The driver refuses to force half speed unless the effective player allowlist contains it. Initial CI paused all three providers but correctly rejected writes because half speed was not authorised. The one-minute wait, accepted-pause, stable-frontier and resume checks are unchanged.

## 1.7.239 natural end, replay and rate cases

`browser_ended_replay_rate.feature` adds three unseeded cases. The teacher fixture explicitly selects 1x/2x,
sets the cap to 2x and its authorised fallback to 1x, and disables autoplay/resume, loop, attention pauses
and acknowledgement completion. Automatic viewed completion requires 95% of the real 60-second duration.
After playing at 1x, the browser requests 4x and observes the existing cap restore 1x; it then requests and
observes authorised 2x. A bounded 45-second media observation waits for natural end, without a seek to the
end or altered runtime clocks. The Vimeo rate assertion waits for a numeric asynchronous result rather
than accepting a Promise as truthy.

Read-only database polling requires the actual same-session `playstart` and accepted `ended` rows, positive
terminal playback, cleared server credit, end/resume positions within 59–60.001 seconds, unique coverage
within 58–60.001 and complete aggregate/native Moodle state. One persisted interval must cover seconds
5–20. The driver snapshots only unique coverage, percent and terminal/session identifiers in test memory.
It then selects 1x, seeks back to second five, plays to seconds 8–12 and pauses through the real surface.
The fresh handshake must follow the accepted end, and a positive accepted pause must close credit in the
same browser session. Unique seconds and percent must match the snapshot within 0.001, with completion
still complete after reload. Replay is deliberately inside validated coverage: an initially uncredited
handshake fraction at second zero must not be confused with double-counting. Resume position may correctly
move backwards; it is not asserted to remain at the end after replay.

The shipped cases use real time and real AJAX writes; assertions cannot seed ledger/state/completion or
forge a response. Existing scenarios, adapters, SDK doubles and guards are unchanged. Local extracted-driver
checks cover controls, cap functions and async observation only; the initial Moodle 44/830 run passes on all six jobs; the formatting correction passed all six corrected release/main jobs.
These SDK contracts do not establish live public iframe, cookie/consent or other-browser availability.

## Initial 1.7.239 CI formatting correction

All six uploaded artifacts match the official digests for commit
`79b7b5bac5c6680f760c67595780b7b1c7f11857` (workflow 42270208398). The uploaded Moodle 5.0 MariaDB file is
from the second attempt; it still reports the same six CodeSniffer errors. Each environment passes the
44/830 Behat suite, 379/4076 PHPUnit, ten strict checks and Grunt. Five jobs are green; Moodle 5.0 MariaDB
fails only the Moodle Code Checker step. This is functional evidence, not an accepted overall candidate.

The three new multiline `if` conditions must put the first expression below `if (` and the closing `)`
on its own line, using the existing Moodle/PSR-12 indentation. The incremental correction changes only
whitespace in those conditions and updates documentation/callable positions. Every PHP token, JavaScript
string, assertion, timeout, fixture and scenario is identical. No production code, analysis rule or version
changes. All six corrected candidate and post-merge jobs passed; this historical formatting defect is resolved in the accepted 1.7.239 baseline.

## 1.7.240 staff preview and dual-role playback

`browser_role_tracking.feature` adds one unseeded case per provider. A teacher-only user plays to 5–10
seconds and pauses, while the actual page JSON must expose `trackingenabled=false`. The read-only assertion
requires no plugin `videotrack_seg` row (including a start handshake), no `videotrack_state` row and no
completed native Moodle state for that user/activity/course-module scope. An absent or incomplete native
row is permitted. Assertions repeat after reload and after another user plays. Moodle's normal activity
view event remains legitimate and is outside this learner-tracking assertion.

A student enrolment plus an editing-teacher role assignment must instead expose `trackingenabled=true`.
Starting without validated evidence/incomplete completion, this dual-role user plays to 5–10 seconds and
pauses through the real surface. Existing assertions require an accepted same-session pause closing credit,
a positive three-second viewing threshold and persisted native completion, retained after reload. Activity
fixtures explicitly disable autoplay/resume, loop, attention/focus pauses and view-only/acknowledgement
completion, with one authorised 1x rate. Existing SDK doubles and all previous scenarios remain unchanged.

## 1.7.240 separate-group report boundaries

`browser_report_groups.feature` uses Moodle's standard enrolment, role, permission-override, group and
activity generators. The course forces separate groups; two students belong to different groups. For the
teacher role, `moodle/site:accessallgroups` is prevented at course context; an editing teacher explicitly
retains it. One restricted reviewer shares Group A and a second has no group membership. Report capability
alone therefore cannot substitute for group access.

Each of the three cases earns accepted viewing/native completion for both students through actual local
HTML5 playback before opening reports. No aggregate/ledger/completion data is seeded. The navigation helper
resolves fixture ids but delegates every permission decision to the real HTTP report controller; it never
pre-filters or fakes the response. The choice assertion compares all positive `#id_userid` option values
with the exact expected user ids, ignoring only the standard All option.

The Group A reviewer can select/request its learner but sees no Group B option or name; directly requesting
that learner returns Moodle's `Invalid user` page with no selector or learner name. The ungrouped reviewer
has an empty selector and the same direct-request denial. All-groups permission permits both actual learners
and their individual reports, without adding report-only staff. These are shared PHP report/Moodle ACL
contracts using HTML5-generated data; they do not claim separate SDK or CSV-download group smoke coverage.

Source expectations are 50 scenarios / 1,074 steps and 379 PHPUnit methods. Local isolated checks cover the
extracted tracking-switch observer and unchanged API preview short-circuits with frozen state/no transport;
actual enabled playback, database assertions and group enforcement still require all six CI jobs. U-007
remains open for evidence review and explicit additional-browser/public-provider checks; U-020/manual WCAG,
semantic translations and final integrated production/lifecycle/privacy/benchmark gates remain distinct.

## Initial 1.7.240 group-fixture correction

All six reports and six faildumps match official artifact digests for commit
`d427dd9de1312cc421f41fb48ce670546311d5a9` (workflow 42301083539). The initial full suite reports 47 passed
and three failed scenarios; 962 steps pass, three fail and 107 are skipped. The three role/provider cases
and all 44 existing cases pass. All failures occur while creating the shared group-member fixture, before
any report/browser assertions in the three new group cases; those report contracts are not yet evidenced.
PHPUnit remains 379/4076, strict is 10/0/0, PHPCS is clean and Grunt/configured analysis gates pass.

Moodle's standard `group members` generator resolves `group` by `groups.idnumber`. The groups are named
`Group A` / `Group B`, with explicit idnumbers `A` / `B`. The three membership rows must therefore use
`A` for student1/reviewer1 and `B` for student2. The correction changes only these three cell values and
documentation. Names, role/capability overrides, actual playback, every action/assertion/time bound and
50/1072 source counts remain unchanged. No production, PHP helper, schema, language or version change.
Corrected six-environment CI must execute the previously skipped report assertions before merge.

## 1.7.240 expected-denial and filtered-row correction

Commit `ccbe192f5b746dc5e12b6e8675e39caf9fd9f9e1` (workflow 42358541350) fixes fixture ids. All six full runs
reach 1,061 passed steps, three failed and eight skipped, with 47/50 scenarios passed. PHPUnit 379/4076,
strict 10/0/0, PHPCS/lint, Grunt and configured analysis gates pass. Twelve official report/faildump digests
match. Offline inspection of all 54 HTML snapshots confirms 36 expected denial pages and 18 positive
filtered pages, including framework retries; these are observations, not 54 independent browser scenarios.

The two forbidden learner requests actually return the intended `invaliduser` error without a selector,
progress table or forbidden learner name. Moodle's after-step exception hook fails immediately on that
page, before the following generic text assertion. The dedicated atomic step now visits the same real
HTTP endpoint, requires exactly one fatal box with the expected message and an `invaliduser` documentation
code, checks absence of selector/progress table/learner name and PHP/debugging notices, then revisits the
normal authorised All report before returning. No hook, tag, exception policy or production guard is
changed or disabled. A wrong error, successful data response or extra notice still fails the step.
The following exact selector/hidden-name assertions check that the restored report keeps the same scope.

For the all-groups viewer, the available selector correctly retains both authorised learners after filtering.
The captured page has the requested learner selected and only that learner in its progress table. Keep
exact selector-scope assertions for both ids, and separately require the selected id, exactly one table
with the localised Per student caption, exactly one body row and the matching learner name (with only its
own optional email suffix). Both filtered learners are tested; the restricted viewer's positive detail also
uses this row check. Names appearing only as available options are not mistaken for leaked progress rows.

The correction adds two read-only/navigation methods while preserving all prior method bodies, real
playback and permission fixtures. Fifty scenarios remain; two added row assertions yield 1,074 steps.
The offline HTML/source checks do not execute PHP/Moodle or the atomic restore flow; fresh six-job CI
must confirm the complete correction before merge. Production, schema, language packs, AMD and version
1.7.240 / 2026110603 remain unchanged.
