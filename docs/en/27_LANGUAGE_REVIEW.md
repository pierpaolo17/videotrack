# Source-grounded language review

Current tree: VideoTrack **1.7.245** (`2026111004`). Review recorded 2026-11-10.

## Current scope and limits

The cumulative review covers **232 unique keys per pack / 1856 distinct values** in DE, EN, ES, FR, HI,
IT, PL and PT. The other **765 keys per pack are outside the claimed semantic review**. This assisted
source/language review is not native linguistic sign-off. All 997 keys/placeholders remain structurally
aligned, but structural parity does not establish semantic or accessibility correctness.

U-017/U-007 remain closed. U-020 is open/deferred at the maintainer’s request; every manual observation
remains NOT RUN. Keep the [manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) for the
final production gate. No browser, screen-reader or live-provider result is inferred from this record.

## Accepted starting point

- Actual 1.7.244 / `2026111003`: 412 distributed files identical to main
  `b6963a5beda4871f4239482a0eb0919466c7fcd4`.
- ZIP SHA-256: `6590b0bc76c447f950b00c0847673af0f2610fde73c171750ebe48f2baa41dae`.
- Server-report ZIP SHA-256: `103f35fa8efb386e5a5faa4329abb797c62915c2e1e4ba5e026d7308364a4ba1`.
- Candidate/main CI `42810143144` / `42814365796`: all six jobs pass PHPUnit 379/4076, Behat 50/1074 and
  strict 10/0/0. Moodle 5.0/5.3 server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076; both supplied
  strict outputs are 10/0/0. AMD hashes before/after Grunt match; the known Browserslist data-age advisory
  remains. The server report did not run Behat/manual assistive tests.

## Complete cumulative reviewed-key manifest

These three prefix groups are exhaustive in the current source packs, with the additional exact keys below.

| Key prefix | Reviewed keys per pack |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| Additional exact keys listed below | 34 |
| Total unique keys | 232 |

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
- `report:analytics_noduration`
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Current pass and source findings

This pass covers all 110 `setting:` keys and rechecks eight privacy descriptions: **118 keys / 944 values**.
It retains accepted evidence for 12 previously reviewed settings where content is unchanged; 98 settings
are newly examined in every language. The cumulative count deduplicates all previous passes. Earlier
release findings remain traceable in the root changelog rather than repeated as obsolete candidate narratives.

Four policy descriptions are corrected in all eight languages:

- `setting:default_completionpercent_desc`
- `setting:focuslossgraceseconds_desc`
- `setting:focuslosspolicy_desc`
- `setting:heading_integrity_desc`

- The default percentage is one VideoTrack AND/OR condition. Zero disables that condition only;
  `tracker::completion_satisfied()` retains the accepted composite rule.
- `core/player/focus_guard.js` gates signal recording separately from pausing. Strict blur handling depends
  on activity focus control; the grace/site policy does not itself enable recording or activity controls.
- `random_pause_policy::activity_bounds()` derives automatic values from saved positive duration, applies
  teacher overrides and uses site fallback bounds only without a positive duration.

There are 32 policy wording corrections, 59 orthographic corrections in DE/ES/FR/IT/PL/PT privacy and
note-report text, and seven Hindi retention-notice translations: **98 changed values across 21 existing keys**.
Latin text restores diacritics; Hindi text preserves the accepted retention meaning, including 0/unlimited,
730-day example and Privacy API erasure. No new privacy obligation, data collection or retention policy is
introduced. Numbers/placeholders remain unchanged in these non-policy edits.

| Pack | Changed values |
|---|---:|
| DE | 13 |
| EN | 4 |
| ES | 13 |
| FR | 15 |
| HI | 11 |
| IT | 14 |
| PL | 14 |
| PT | 14 |

## Exact keys changed in this candidate

This is the union; each pack changes only the keys required by its actual findings.

- `privacy:metadata:common:cmid`
- `privacy:metadata:common:courseid`
- `privacy:metadata:common:videoid`
- `privacy:metadata:common:videotrackid`
- `privacy:metadata:external:cookies`
- `privacy:metadata:external:ipaddress`
- `privacy:metadata:external:useragent`
- `privacy:metadata:videotrack_reactev:reactionid`
- `setting:default_completionpercent_desc`
- `setting:focuslossgraceseconds_desc`
- `setting:focuslosspolicy_desc`
- `setting:heading_integrity_desc`
- `setting:reportnotespagesize`
- `setting:reportnotespagesize_desc`
- `setting:retentionperioddays_desc`
- `setting:retentionprivacynotice`
- `setting:retentionunlimitedconfirm`
- `setting:retentionunlimitedconfirm_desc`
- `setting:retentionunlimitedconfirm_required`
- `setting:retentionunlimitedwarning_desc`
- `setting:retentionunlimitedwarning_title`

## Candidate gates and remaining work

1.7.245 contains 412 distributed files / 31 documents per language, with 166 PHP files, 1101 PHP/654 AMD
callables and 345 non-documentary inventory rows. Schema remains XMLDB `2026110601`, 7 tables / 151 fields /
22 indexes / 22 foreign keys; ER changes only current-tree labels. Runtime logic, permissions, services,
data values, tests, fixtures, AMD and CI rules are unchanged.

Local contract/delta/link checks and exact patch application/reversal are separate from native execution.
The candidate must still pass its six CI jobs and later actual merged ZIP/server/strict gates. Expected source
counts remain 379 PHPUnit tests and 50 Behat scenarios / 1074 steps; they are not new candidate test results.
Continue the remaining semantic review; the final integrated production gate, manual U-020 and later App/
authenticated-provider roadmap tasks are not closed by this language pass.
