# Browser coverage and U-007 exit

**U-007 is closed on accepted 1.7.240.** The actual package matches main
`2d047f5c59792e4c8798a31209f19cfa9ecc15f7`. All six corrected release/main jobs pass **50 scenarios /
1,074 steps** and **379 PHPUnit tests / 4,076 assertions**. Server PHPCS/lint, Grunt, PHPUnit and both strict
outputs pass; the server report does not execute Behat. See the [exit audit](26_BROWSER_EXIT_AUDIT.md).
Candidate 1.7.241 changes documentation/version metadata only; its own CI/package gates remain required.

## Setup and execution

Configure the isolated Moodle Behat site (`behat_wwwroot`, `behat_dataroot`, `behat_prefix`), then run from
Moodle root after changing generators, steps or features:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Use an isolated test database. The repository workflow runs the `chrome` profile on six Moodle/PHP/database
jobs. That is one browser profile across six environments. It is not evidence for other browser engines.

## Deterministic fixtures and evidence

- `behathtml5fixture=1`: uploaded local 60-second MP4.
- `behatproviderfixture=youtube` / `vimeo`: reserved ids and local SDK doubles, enabled only under Behat.
- `behatlinkedforum=<name>`: actual Forum fixture in the same course.
- Production adapters, normal AJAX endpoints, server credit and native Moodle completion stay in use.
- Unseeded viewing cases read actual persisted rows; they do not fabricate progress or responses.
- Seeded resume/UI cases deliberately test an existing state and remain distinct from earned-viewing evidence.

The roadmap's external-provider objective is the adapter contract. Public iframe/network availability is
outside the deterministic U-007 gate. Live-provider checks remain part of deployment/content acceptance;
manual keyboard/screen-reader perception belongs to [U-020](19_ACCESSIBILITY.md).

## Essential coverage matrix

| Contract | HTML5 evidence | YouTube/Vimeo evidence |
|---|---|---|
| Play/pause, saved resume and backward/blocked seek | `html5_playback_contract.feature`, `html5_seek_policy.feature` | `youtube_provider_contract.feature`, `vimeo_provider_contract.feature` |
| Browser-earned viewed completion and reload | `browser_tracking_completion.feature` | Same feature, one case per adapter |
| Allowed forward seek and excluded gap | `browser_allowed_seek_tracking.feature` | Same feature, one case per adapter |
| Post-rollback reactions/notes/bookmarks/Forum timestamps | `browser_rollback_interactions.feature` | Same feature, one case per adapter |
| Natural end, watched replay, authorised rate and above-cap fallback | `browser_ended_replay_rate.feature` | Same feature, one case per adapter |
| Effective attention policy, real pause and new resume handshake | `random_attention_policy.feature` | Same feature, one timed case per adapter |
| Staff preview and dual-role earned completion | `browser_role_tracking.feature` | Same feature, one case per adapter |
| Group-limited, ungrouped and all-groups reports | `browser_report_groups.feature`, browser-earned HTML5 data | Shared PHP report/Moodle ACL boundary; no repeated SDK claim |
| Acknowledgement, completion combinations and status notices | `html5_acknowledgement_contract.feature`, `html5_completion_contract.feature`, `html5_playback_contract.feature` | Shared UI/server contracts; no claim that every combination is repeated per adapter |
| Role controls, optional histories and completion layout | `learner_role_controls.feature`, `student_personal_sections.feature`, `completion_requirements_layout.feature` | Shared rendered controls |
| Focus exception-group resolution | `focus_exception_policy.feature` | Shared effective player policy |

Every row has real Chrome execution in the accepted six-environment milestone. The table states the actual
scope; it does not turn a shared PHP/UI case into separate public-provider or browser-engine evidence.

## Credit, completion and seek assertions

The unseeded completion fixtures disable autoplay/resume and view-only/acknowledgement completion, use a
60-second duration and 5% threshold, and begin without validated evidence/native completion. Play to 5–10
seconds and pause must persist an accepted same-session terminal row, closed server credit and native
completion, retained after reload. Read-only polling waits for asynchronous writes.

Allowed-seek cases play into 4–10 seconds, seek to 30, continue into 33–39 and pause. Validated ledger and
aggregate intervals must leave 12–29.5 seconds uncovered, unique coverage must stay below 30, and native
completion must remain incomplete after reload. Bounds account for polling and provider seek tolerance.

Post-rollback cases first earn the 5% completion threshold, then refuse a seek to 30. Recovery is observed
until a pause or advancing phase remains stable for 6.5 seconds; advancement must reach at least six media seconds,
with a 15-second observation bound and media below 25. The real pause follows. Persisted frontier/coverage
stay below 22; reaction, note, bookmark and Forum URL timestamps match the frontier within 1.25 seconds,
with exact activity/session scope. The Forum composer is opened, not submitted. Completion remains complete.

## End, replay and rates

The teacher fixture explicitly selects 1x/2x, cap 2x, authorised fallback 1x and a 95% viewed threshold. A 4x request must restore 1x;
a selected 2x request then reaches the natural 60-second end within a 45-second observation bound, without
seeking to the end or overriding clocks. Vimeo's numeric asynchronous rate result is awaited.

Actual `playstart`/accepted `ended` rows must close credit, end/resume positions must be within 59–60.001,
unique coverage within 58–60.001 and native completion complete. A merged interval must cover 5–20 seconds.
Replay from second five at 1x plays to 8–12 and pauses: a new start must follow the accepted end, with a
positive accepted pause in the same browser session. Unique seconds and percent must stay within 0.001 of
the end snapshot, including reload. Resume position may move backwards. Replaying inside known coverage
avoids confusing an initially uncredited handshake fraction with double-counting.

Request-id retries and completion effects also have behavioural PHPUnit coverage in `tracker_test.php`
and `tracking_effects_test.php`; no forced network-timeout browser test is claimed.

## Attention and participation

Five configuration cases cover automatic duration thirds, one-sided overrides, disabled control and zero-duration
site fallback. Three timed cases explicitly select 0.5x/1x and use unchanged 60-second fixtures at half speed,
so natural end cannot pre-empt the real 60–60 wall-second attention deadline. They observe the pause notice,
accepted pause/closed credit and stable frontier, then manually resume to 31–38 media seconds and pause with
a fresh handshake. No runtime clock is accelerated.

Staff preview must expose `trackingenabled=false`, create no plugin segment/state rows and no completed
native state after playback/reload. Absent or incomplete native rows are permitted; the normal Moodle view
event is legitimate. A student enrolment plus editing-teacher role instead exposes tracking enabled and
must earn accepted playback/native completion through the existing positive assertions.

## Group report and expected-denial contracts

The course forces separate groups. Standard Moodle generators create `Group A`/`Group B` with idnumbers
`A`/`B`; group-member references use those idnumbers. A teacher's `moodle/site:accessallgroups` is prevented
at course context; an editing teacher explicitly retains it. Two learners earn actual HTML5 progress.

The restricted reviewer sees only its group's exact learner ids; the ungrouped reviewer sees none; the
all-groups reviewer sees both actual learners and no report-only staff. Selector choices retain the full
authorised population after filtering. A separate assertion requires the selected id and exactly one
matching progress row in the table with the localised Per student caption, with only its own optional email.

Forbidden requests visit the real report endpoint. The atomic assertion requires one expected `invaliduser`
fatal box/documentation code, no selector/progress table/forbidden name and no extra PHP/debugging notices.
It then restores the authorised All report before Moodle's normal after-step hook runs. Hooks are never
disabled. The restored selector/hidden-name assertions retain the same scope. Unexpected errors or data fail.

## Maintenance and remaining work

Use stable CSS/data selectors, exact ids and localised captions where needed. Keep bounded polling rather
than fixed sleeps, preserve read-only assertions and keep SDK/network fixtures separate from production.
All prior cases remain in the suite. Run six-job CI after changes; retain exact commit/package provenance.

The original U-007 matrix and boundary-version milestone are complete on 1.7.240. Additional engines, public
iframes, consent/cookies and assistive-technology results are recorded separately when exercised; none are
inferred from Chrome/SDK doubles. U-020 manual audit, semantic translation review and the final integrated
production/lifecycle/privacy/benchmark gate remain open. An evidenced regression can reopen the affected cell.
