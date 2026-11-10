# Targeted language review

Current tree: VideoTrack **1.7.242** (`2026111001`). Review recorded 2026-11-10.

## Scope and status

This independent roadmap task proceeds while U-020 is deferred. It compares 19 critical keys in EN, IT,
DE, ES, FR, HI, PL and PT: 152 string values. Six keys are corrected in every pack, for 48 changed values.
The remaining 978 keys per pack are not claimed to have received semantic review. This source-based review
does not certify native linguistic quality or replace human linguistic review. Structural parity of all
997 keys/placeholders is a separate release check. No manual accessibility observation is implied.

## Accepted starting point

- Actual 1.7.241 / `2026110901`: 410 distributed files identical to main
  `2f8bd011471f2ca983c235a4ed35b36d2a5c56b4`.
- ZIP SHA-256: `65b04a926559ba782a5ef88a14671911e9566b0725738e66120cd1f99f1b3ebd`.
- Server-report ZIP SHA-256: `3bf6cccfba47ab393de64daa81f3f6c5e7692a52a9678abba77deb124b5aaac2`.
- Release/main CI runs `42644023227` / `42648245448`: all six jobs passed, with 379 PHPUnit tests /
  4,076 assertions, 50 Behat scenarios / 1,074 steps and strict 10/0/0 per job.
- Moodle 5.0/5.3 server reports: PHPCS/lint 166 clean, Grunt completed with identical before/after AMD
  hashes and PHPUnit 379/4076. Both supplied strict outputs are 10/0/0. The known Browserslist data-age
  advisory remains; server Behat and manual assistive testing were not run by that report.

## Source-grounded findings

| Finding | Corrected keys | Unchanged runtime evidence |
|---|---|---|
| Playing the video does not save authoritative duration | `report:analytics_noduration` | `report.php` uses saved activity duration; `classes/external/save_segment.php` ignores learner duration as authority |
| Personal notes and private bookmark labels have different report boundaries | `studentnotesenabled_help`, `personalstudytoolsintro`, `privacy:metadata:videotrack_reactev:notetext` | `classes/local/report_access.php` and `report.php` permit personal notes in scoped individual reports; teacher reports exclude bookmark labels |
| Aggregate-only activity viewers still use the minimum-user threshold | `setting:analyticsminusers_desc` | `report.php` checks individual access to every included activity; course/teacher dashboards retain their existing exact-aggregate policy |
| One segment-save request does not imply exactly two database queries | `setting:heartbeatinterval_desc` | Context/session/request validation and transactional tracker persistence have conditional reads/writes; no benchmark count is asserted |

The note wording describes existing permissions, including delegated roles; it grants no access. The duration
warning describes an existing teacher-controlled setting. Masking, export permissions, learner/group scope,
tracking, completion, request idempotency and data storage retain the accepted implementation.

## Reviewed keys in every pack

- `durationseconds_help`
- `report:analytics_noduration`
- `studentnotesenabled_help`
- `personalstudytoolsintro`
- `privacy:metadata:videotrack_reactev:notetext`
- `bookmarks_private_notice`
- `report:csvexport_bookmarks_help`
- `setting:analyticsminusers_desc`
- `setting:retentionperioddays_desc`
- `setting:heartbeatinterval_desc`
- `setting:randompauseminseconds_help`
- `setting:randompausemaxseconds_help`
- `setting:randompauseminseconds_desc`
- `setting:randompausemaxseconds_desc`
- `integrity:randompauseinvalid`
- `integrity:randompauseorder`
- `acknowledgement:privacyhint`
- `acknowledgement:timing_help`
- `completionreactionrules_desc`

## Candidate checks and remaining work

The candidate contains 412 distributed files and 31 documents per language. Non-documentary inventory rows
(345), PHP files (166), named PHP/AMD callables (1101/654), test/feature bodies and the 7-table/151-field schema
are unchanged. XMLDB remains `2026110601`; ER artefacts update only their current-tree version label.
All eight packs retain 997 keys in canonical order, with identical placeholder contracts. Existing release
hygiene, lint, language-contract, PHPUnit, browser and strict gates must run on 1.7.242 itself. Counts of
379 tests / 50 scenarios / 1074 steps are expected, not new execution results for this candidate.

Continue semantic review outside these 19 keys and obtain human linguistic review where needed. U-017/U-007
stay closed. U-020 is open/deferred with every M01–M08 observation NOT RUN; retain its
[manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) before the final integrated production
gate. App evaluation and authenticated-provider implementation remain separate roadmap tasks.
