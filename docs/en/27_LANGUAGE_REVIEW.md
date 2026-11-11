# Source-grounded language review

Current tree: VideoTrack **1.7.248** (`2026111101`). Review recorded 2026-11-11.

## Current scope and limits

The cumulative review covers **530 unique keys per pack / 4240 distinct values** in DE, EN, ES, FR, HI,
IT, PL and PT. The other **467 keys per pack are outside the claimed semantic review**. All 997 keys and
placeholder multiplicities remain structurally aligned. This assisted review is not native linguistic
sign-off, manual accessibility approval or proof of every deployed browser/provider combination.

U-017/U-007 remain closed. U-020 is open/deferred at the maintainer’s request; all eight manual observations
remain NOT RUN. Retain the [manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) for the
final production gate. Independent language work does not change that decision.

## Accepted starting point

- Actual 1.7.247 / `2026111006`, 412 files identical to main `68754eb1ddf76a0566a36e93526cc00d2137e4d2`.
- ZIP SHA-256: `6d120c597c97e2d9b245044daea22a212c3f203f152bb2253c31fcf7cdfa8abd`.
- Server-report ZIP SHA-256: `3f3e23dd4c9d065dc1b3c6517100c8f482bd2bdef23deda8c20ae806dcd82b35`.
- Candidate/main CI `42912958780` / `42982684717`: all six jobs pass PHPUnit 379/4076, Behat 50/1074 and
  strict 10/0/0. Actual Moodle 5.0/5.3 server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076;
  both supplied strict outputs are 10/0/0. AMD hashes before/after Grunt match. The known Browserslist
  data-age notice, 24 reviewed PHPMD findings and Psalm informative totals 732/760 are unchanged.
  Configured analysis gates pass; this is not a zero-advisory claim. Server reports did not run Behat
  or manual assistive checks. No new native execution in this workspace is claimed.

## Complete cumulative reviewed-key manifest

These six prefix groups are exhaustive in the current packs; additional exact keys follow.

| Key prefix | Reviewed keys per pack |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| `report:` | 260 |
| `coursereport:` | 25 |
| `teacherdashboard:` | 15 |
| Additional exact keys | 32 |
| Total unique keys | 530 |

- `acknowledgement:enabled_desc`
- `acknowledgement:pendingvideoend`
- `acknowledgement:privacyhint`
- `acknowledgement:text_help`
- `acknowledgement:timing_help`
- `acknowledgement:videoendrequired`
- `bookmarks_private_notice`
- `durationseconds_help`
- `error:bookmarksratelimit`
- `error:learnertrackingstaff`
- `error:notesratelimit`
- `error:playbackpositionnotwatched`
- `error:playbackrequired`
- `error:reactionratelimit`
- `forum:composerprivacy`
- `forum:enable_help`
- `forum:privacyinfo`
- `forum:studenthelp`
- `integrity:analytics_recording_disabled_controls`
- `integrity:enable_help`
- `integrity:methodology`
- `integrity:pauseonfocusloss_desc`
- `integrity:preventpip_desc`
- `integrity:randompauseautomatic`
- `integrity:randompauseinvalid`
- `integrity:randompauseorder`
- `integrity:randompauses_desc`
- `integrity:reportintro`
- `integrity:settingsintro`
- `integrity:studentnotice`
- `personalstudytoolsintro`
- `studentnotesenabled_help`

## Current report/export/dashboard pass

This pass reviews **179 keys / 1432 values**: all 139 non-Analytics `report:` keys, 25 `coursereport:` keys
and 15 `teacherdashboard:` keys. There are 178 newly examined keys / 1424 values, and one reused accepted
unchanged key: `report:csvexport_bookmarks_help`. The earlier 121-key Analytics review remains accepted.
All 260 report keys and both dashboard families are now covered. Retained keys without a direct active
rendering call are not presented as newly displayed functionality; existing unused dashboard privacy
notices are not reintroduced. Exact-count dashboard access remains unchanged.

## Source findings

- `report_support::cluster_reaction_events()` continues reading ordered events after its configured cap,
  skipping new clusters while existing active clusters can still collect events. `report.php` warns; when
  there is no video-time filter it hides the capped table and stops the cumulative CSV before cluster rows.
  With a filter present, capped rows can still be shown/exported with warnings. Narrowing a range is not
  a completeness guarantee. Four warnings/help texts now state those boundaries; the heatmap explanation
  describes the retained cluster subset instead of promising complete data in its table.
- Custom CSV in `report.php` retains the permitted learner condition. Overall mode clears only the single
  learner selection, not the permission scope. Detailed/summary modes preserve their reaction/note rows;
  bookmarks are counts per learner or overall, with private labels/timestamps excluded. Format help now
  covers these facts. The note concatenation example and its numeric tokens remain unchanged in every pack.
- Recalculation routes through `videotrack_recalculate_all_states()` and `tracker::rebuild_state_from_segments()`:
  retained `servervalidated = 1` segments, saved authoritative duration and current completion conditions.
  Text now distinguishes this from reconstructing deleted evidence. No new segment, credit or session
  budget is created by this wording patch; existing completion synchronisation is preserved.
- Targeted translations correct the started-learner label, activity/no-viewing messages, the Polish
  completion-recalculation action and the Hindi export-user scope. Italian `report:timestamp` and retained
  `report:sorttime` become “Tempo del video”: production table/CSV cells hold video offsets, while creation
  dates have separate columns. Thus the Italian visible CSV header changes deliberately; column order,
  data values and formatter code do not. Other languages’ timestamp headers are preserved.

Evidence: `report.php`, `report_support`, `csv_export`/`csv_event_writer`/`csv_event_timestamps`,
`course_analytics`/`teacher_analytics`, `reports_course.php`, `reports_teacher.php`, `lib.php` and `tracker`.
Existing cluster/export/course/recalculation tests are unchanged; accepted baseline execution is not
relabelled as candidate execution. No new tests duplicate these literal-only corrections.

## Exact keys changed in the candidate

- `coursereport:col_students_started`
- `coursereport:intro`
- `coursereport:nodata`
- `report:clusterlimitreached`
- `report:clusterlimitreached_help`
- `report:clusterlimitrequiresfilters`
- `report:clusterlimitrequiresfilters_csv`
- `report:csvexport_format_help`
- `report:csvexport_scope`
- `report:heatmap_desc`
- `report:heatmap_supplementary`
- `report:noattempts`
- `report:recalculate`
- `report:recalculate_description`
- `report:sorttime`
- `report:timestamp`

Seven descriptions change in each pack; other changes are targeted translation findings.

| Pack | Values changed |
|---|---:|
| DE | 11 |
| EN | 7 |
| ES | 8 |
| FR | 8 |
| HI | 9 |
| IT | 11 |
| PL | 9 |
| PT | 8 |

Total: **71 values across 16 existing keys**. No key is added or removed. Placeholder counts, numeric tokens
and the original per-language note examples are preserved. Earlier findings remain in the root changelog.

## Candidate gates and agreed sequence

The candidate retains 412 files, 31 EN/IT documents each, 166 PHP files, 1101 PHP/654 AMD callables and 345
inventory rows. XMLDB stays `2026110601`: 7 tables / 151 fields / 22 indexes / 22 foreign keys. ER changes
only version labels. Runtime logic, access rules, stored data, DB, services, AMD, tests, fixtures and CI
rules are byte-identical to accepted 1.7.247. Only wording, guides and version metadata differ.

Local contract/delta/link and exact apply/reverse checks do not replace six candidate CI jobs and then
actual merged ZIP/server reports/strict. Expected unchanged suite: 379 PHPUnit tests / 4076 assertions,
50 Behat scenarios / 1074 steps. These are expectations, not candidate results.

The maintainer’s agreed order is: finish the existing web work and App decision, then add multilingual VTT
subtitles before authenticated-provider foundation and OneDrive/SharePoint, Zoom Cloud and Google Drive.
The existing multilingual transcript panel remains distinct from subtitles over the video. No VTT runtime
change is included here. HTML5 controls/file lifecycle and external-provider/fullscreen feasibility need
their own evidence; automatic publication to third-party accounts is not included by this plan. U-020
remains deferred. U-017/U-007 are not reopened for cosmetic metrics.
