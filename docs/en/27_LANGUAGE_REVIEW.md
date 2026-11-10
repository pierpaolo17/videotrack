# Source-grounded language review

Current tree: VideoTrack **1.7.246** (`2026111005`). Review recorded 2026-11-10.

## Current scope and limits

The cumulative review covers **253 unique keys per pack / 2024 distinct values** in DE, EN, ES, FR, HI,
IT, PL and PT. The other **744 keys per pack are outside the claimed semantic review**. This assisted
source/language review is not native linguistic sign-off. All 997 keys/placeholders remain structurally
aligned; structural parity does not establish semantic or accessibility correctness.

U-017/U-007 remain closed. U-020 is open/deferred at the maintainer’s request; all eight manual observations
remain NOT RUN. Retain the [manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) for the
final production gate. No manual, screen-reader or live-provider result is inferred here.

## Accepted starting point

- Actual 1.7.245 / `2026111004`: 412 distributed files identical to main
  `0309db75c57e379828fa9e049928bc2533e0ed6a`.
- ZIP SHA-256: `31b706f1bf47deb87036939fc1188668b7646490ee0fb2c3c5a53bb54fad9268`.
- Server-report ZIP SHA-256: `306f0139a21d0bcc15d96068e178466789b8492bc734a25e6244fd69ca476c3e`.
- Candidate/main CI `42826573352` / `42830814382`: all six jobs pass PHPUnit 379/4076, Behat 50/1074 and
  strict 10/0/0. Moodle 5.0/5.3 server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076; both supplied
  strict outputs are 10/0/0. AMD hashes before/after Grunt match. The known Browserslist data-age advisory
  remains; the server report did not run Behat/manual assistive checks. Reviewed PHPMD findings and Psalm
  informative totals remain separate from passing configured analysis gates.

## Complete cumulative reviewed-key manifest

The three prefix groups below are exhaustive in the current packs; the remaining exact keys are listed below.

| Key prefix | Reviewed keys per pack |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| Additional exact keys listed below | 55 |
| Total unique keys | 253 |

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
- `report:analytics_export_row_reactions`
- `report:analytics_heatmap_legend_reaction`
- `report:analytics_method_reactions`
- `report:analytics_noduration`
- `report:analytics_noviewingdata_reactions`
- `report:analytics_privacy_unavailable_reactions`
- `report:analytics_reaction_events`
- `report:analytics_reaction_students`
- `report:analytics_reaction_time`
- `report:analytics_reaction_type`
- `report:analytics_reactionclusters`
- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionclusters_none`
- `report:analytics_reactionclusters_title`
- `report:analytics_reactionlimit`
- `report:analytics_reactionmarker`
- `report:analytics_reactions_available`
- `report:analytics_reactions_cell`
- `report:analytics_reactions_detected`
- `report:analytics_reactionsummary`
- `report:analytics_reactionsummary_suppressed`
- `report:analytics_showreactions`
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Reaction Analytics pass

This pass reviews **21 existing keys / 168 values**: every `report:analytics_` key containing `reaction`.
These keys do not overlap the 232 previously accepted keys. The review checks cluster tables, overlay
controls/markers, counts, privacy suppression and empty-data notices. Existing keys without an active
rendering call are retained; their review does not imply that they are currently displayed. The rest of
the report/Analytics key families are not included. Earlier findings remain in the root changelog.

- `report:analytics_export_row_reactions`
- `report:analytics_heatmap_legend_reaction`
- `report:analytics_method_reactions`
- `report:analytics_noviewingdata_reactions`
- `report:analytics_privacy_unavailable_reactions`
- `report:analytics_reaction_events`
- `report:analytics_reaction_students`
- `report:analytics_reaction_time`
- `report:analytics_reaction_type`
- `report:analytics_reactionclusters`
- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionclusters_none`
- `report:analytics_reactionclusters_title`
- `report:analytics_reactionlimit`
- `report:analytics_reactionmarker`
- `report:analytics_reactions_available`
- `report:analytics_reactions_cell`
- `report:analytics_reactions_detected`
- `report:analytics_reactionsummary`
- `report:analytics_reactionsummary_suppressed`
- `report:analytics_showreactions`

## Source findings and corrections

`report.php` obtains the effective threshold from access to every activity in the selected scope: individual
report access permits `analytics::EXACT_REPORT_MIN_USERS` (1), while aggregate-only access retains the
configured `analyticsminusers` threshold. `cluster_reactions()` receives that effective value. The table
caption now refers to this report’s threshold instead of implying that the site value always applies.

`analytics::append_visible_reaction_cluster()` checks distinct-user eligibility before applying
`MAX_REACTION_CLUSTERS` (2000). Additional eligible clusters set `truncated`; the retained subset is sorted
after collection, so it is not promised to be the earliest subset across reaction types. `report.php` raises
the limit warning on truncation. Disabling the overlay skips cluster collection and rendering; it does not
recover omitted clusters. The revised warning explains the display cap/omissions and says only that a
narrower scope may avoid it. It does not promise that narrowing always succeeds or that a complete export
is available. Existing summary counts, access filters and privacy masking are unchanged.

Source evidence: `report.php` threshold/overlay/warning branches; `classes/local/analytics.php`
clustering/visibility methods; `classes/local/report_timeline_view.php::reaction_clusters()`;
`classes/local/report_view.php` methodology/privacy explanations. The existing
`analytics_test::test_reaction_cluster_limit_is_reported()` covers the cap/truncation flag; its body is
unchanged and is not claimed newly executed locally. No additional test mirrors these literal-only edits.

## Exact keys changed in the candidate

- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionlimit`

| Pack | Values changed |
|---|---:|
| DE | 2 |
| EN | 2 |
| ES | 2 |
| FR | 2 |
| HI | 2 |
| IT | 2 |
| PL | 2 |
| PT | 2 |

Total: **16 changed values across two existing keys**. Keys, placeholder multiplicities and numeric tokens
are preserved in every pack. No new language key or data field is added.

## Candidate gates and remaining work

1.7.246 contains 412 distributed files / 31 documents per language, 166 PHP files, 1101 PHP/654 AMD callables
and 345 non-documentary inventory rows. Schema remains XMLDB `2026110601`: 7 tables / 151 fields / 22 indexes /
22 foreign keys. ER changes only current-tree version labels. Runtime, access rules, data, services, DB,
AMD, tests, fixtures and CI/analysis rules are byte-identical to accepted 1.7.245.

Local contract/delta/link and exact patch apply/reverse checks are separate from native execution. Candidate
CI still needs all six jobs, followed by actual merged ZIP/server reports/strict. Expected unchanged suite:
379 PHPUnit tests / 4076 assertions and 50 Behat scenarios / 1074 steps. These are baseline expectations,
not candidate execution results. Remaining semantic review, manual U-020, the integrated production gate
and subsequent App/authenticated-provider stages remain open.
