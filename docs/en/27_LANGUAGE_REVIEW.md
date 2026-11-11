# Source-grounded language review

Current tree: VideoTrack **1.7.247** (`2026111006`). Review recorded 2026-11-10.

## Current scope and limits

The cumulative review covers **352 unique keys per pack / 2816 distinct values** in DE, EN, ES, FR, HI,
IT, PL and PT. The other **645 keys per pack are outside the claimed semantic review**. This assisted
source/language review is not native linguistic sign-off. All 997 keys/placeholders remain structurally
aligned; structural parity does not establish semantic or accessibility correctness.

U-017/U-007 remain closed. U-020 is open/deferred at the maintainer’s request; all eight manual observations
remain NOT RUN. Retain the [manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) for the
final production gate. No manual, screen-reader or live-provider result is inferred here.

## Accepted starting point

- Actual 1.7.246 / `2026111005`: 412 distributed files identical to main
  `c5527eb68e1d58b78999dc24c8f99e14b5872eb8`.
- ZIP SHA-256: `c7c65c1933c370c885f44caf413bee2200c8fd6b1dfa4d6527aa26b8191e83b1`.
- Server-report ZIP SHA-256: `804d98775c7096eff316dad5eec8c66dbacc5e89064f69469553a9626b899169`.
- Candidate/main CI `42840354028` / `42864476627`: all six jobs pass PHPUnit 379/4076, Behat 50/1074 and
  strict 10/0/0. Moodle 5.0/5.3 server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076; both supplied
  strict outputs are 10/0/0. AMD hashes before/after Grunt match. The known Browserslist data-age advisory
  remains; server reports did not run Behat/manual assistive checks. The 24 reviewed PHPMD findings and
  Psalm informative totals 732/760 remain separate from passing configured analysis gates.

## Complete cumulative reviewed-key manifest

These four prefix groups are exhaustive in the current packs; the remaining exact keys are listed below.

| Key prefix | Reviewed keys per pack |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| `report:analytics_` | 121 |
| Additional exact keys listed below | 33 |
| Total unique keys | 352 |

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
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Complete Analytics-family pass

This pass covers all **121 `report:analytics_` keys / 968 values**. It newly examines 99 keys / 792 values
and reuses unchanged accepted evidence for 22 keys: `report:analytics_noduration` and the 21 reaction-related
Analytics keys reviewed in 1.7.246. The cumulative count deduplicates previous passes. The whole Analytics
key family is now covered; other report families, including course/teacher dashboard labels, are outside
this pass unless explicitly included by the cumulative manifest. Definitions without a direct rendering
reference are retained; their review does not imply a new UI element or current display. Previous findings
remain in the root changelog.

The pass covers acknowledgement snapshots/averages, cross-course matching/group scope, bookmark privacy,
intervals and exports, heatmap/retention legends, suppression, retained-state fallback and highlight labels.
Source references include `report.php`, `analytics_scope`/`analytics_scope_matcher`,
`acknowledgement_analytics`, `analytics_timeline`, `analytics`, `report_support`, `analytics_table_export`
and the report view/summary/timeline services under `classes/local`.

## Source findings and corrections

- `report.php` builds raw-segment and retained-state Analytics. `report_support::analytics_prefers_state_fallback()`
  selects state when it retains more viewers or unique coverage. State reconstruction supplies unique-view
  intervals but no repeat metrics. The retained general description now explains both sources and that
  limitation; this edit adds no rendering call. The existing fallback notice/behaviour is unchanged.
- `analytics::mask_timeline_bins()` masks positive viewer/replay subgroups below the threshold. Zero intervals
  can remain visible; masked interval populations also suppress retention percentages that could expose
  the protected denominator. Methodology now describes non-empty subgroups, potentially visible zero
  counts and protected percentages. `report_view::analytics_methodology()` still omits this threshold text
  for exact individual-authorised reports. No masking rule or threshold is changed.
- `report.php` renders reaction/bookmark/acknowledgement/integrity summaries before the no-viewing branch,
  while the independent cluster table can follow it. Acknowledgements or integrity data can trigger that
  branch without reaction/bookmark data. The notice now refers to valid viewing intervals and independently
  available event summaries, subject to permissions/privacy. It no longer promises a position below the
  notice or a summary when its data/feature is absent.
- The DE/HI high-colour legend could imply viewer counts exceeding the selection maximum. The heatmap
  normalises against its visible maximum; these two translations now express relative intensity. The FR
  unique/repeat methodology replaces the ambiguous unaccented `revision` with the existing `relecture`
  terminology. No colour scale or metric is changed.

Existing tests cover zero-interval visibility, small subgroup masking, replay-only suppression, exact-report
methodology, fallback selection and heatmap markup, including
`analytics_test::test_privacy_threshold_keeps_zero_intervals_visible()` and
`report_view_test::test_exact_analytics_methodology_omits_privacy_threshold_text()`.
All test bodies remain unchanged. Their accepted baseline results are not new local candidate execution.

## Exact keys changed in the candidate

- `report:analytics_description`
- `report:analytics_heatmap_legend_high`
- `report:analytics_method_privacy`
- `report:analytics_method_unique`
- `report:analytics_noviewingdata_events`

The general viewing description, privacy methodology and no-viewing notice are corrected in every pack;
the high-intensity legend only in DE/HI and the
unique/repeat terminology only in FR. Each pack changes only its source-proven findings.

| Pack | Values changed |
|---|---:|
| DE | 4 |
| EN | 3 |
| ES | 3 |
| FR | 4 |
| HI | 4 |
| IT | 3 |
| PL | 3 |
| PT | 3 |

Total: **27 changed values across five existing keys**. Keys, placeholder multiplicities and numeric tokens
are preserved in every pack. No language key or data field is added.

## Candidate gates and remaining work

1.7.247 contains 412 distributed files / 31 documents per language, 166 PHP files, 1101 PHP/654 AMD callables
and 345 non-documentary inventory rows. Schema remains XMLDB `2026110601`: 7 tables / 151 fields / 22 indexes /
22 foreign keys. ER changes only current-tree version labels. Runtime, access rules, data, services, DB,
AMD, tests, fixtures and CI/analysis rules are byte-identical to accepted 1.7.246.

Local contract/delta/link and exact patch apply/reverse checks are separate from native execution. Candidate
CI still needs all six jobs, followed by actual merged ZIP/server reports/strict. Expected unchanged suite:
379 PHPUnit tests / 4076 assertions and 50 Behat scenarios / 1074 steps. These are baseline expectations,
not candidate execution results. Remaining semantic review, manual U-020, the integrated production gate
and subsequent App/authenticated-provider stages remain open.
