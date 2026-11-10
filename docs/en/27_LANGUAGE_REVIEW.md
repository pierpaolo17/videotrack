# Targeted language review

Current tree: VideoTrack **1.7.243** (`2026111002`). Review recorded 2026-11-10.

## First-pass scope (accepted 1.7.242)

This independent roadmap task proceeds while U-020 is deferred. The first pass compared 19 critical keys in EN, IT,
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
hygiene, lint, language-contract, PHPUnit, browser and strict gates must run on 1.7.243 itself. Counts of
379 tests / 50 scenarios / 1074 steps are expected, not new execution results for this candidate.

Continue semantic review outside the cumulative 48 unique keys and obtain human linguistic review where needed. U-017/U-007
stay closed. U-020 is open/deferred with every M01–M08 observation NOT RUN; retain its
[manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020) before the final integrated production
gate. App evaluation and authenticated-provider implementation remain separate roadmap tasks.

## Second pass: 1.7.243 policy/help review

The actual 1.7.242 baseline is accepted: 412 files match main
`b6d1afef4af7a1ea7835968586a9f6f7e329bf93`. ZIP SHA-256:
`e61dfd7547a53306303da9ec91c8cb31b245de3a94fe3238a8f9c301839c2501`.
Server-report ZIP SHA-256: `ca74efd7b900bdcbd357fec0f719a98e0bfbc941cf547f72b4b2ca9d4fa311f3`.
Candidate/main runs `42777321912` / `42784475404` pass all six jobs with PHPUnit 379/4076,
Behat 50/1074 and strict 10/0/0. Both server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076;
both supplied strict outputs are 10/0/0. Server Behat/manual accessibility were not run by that report.

This pass reviews 31 keys in all eight packs (248 values), revisiting two first-pass acknowledgement keys.
The cumulative scope is 48 unique keys per pack (384 distinct values); the other 949 keys are not declared
semantically reviewed. Four keys are corrected per pack (32 values). Existing eight-pack key/placeholder
parity, 412 files, 31 documents per language, runtime, DB, tests, AMD and CI remain unchanged in structure.
No native-quality certification or completed U-020 observation is inferred.

| Corrected key | Existing source behaviour reflected by the wording |
|---|---|
| `integrity:enable_help` | Recording stores only bounded signal fields; report masking follows actual capabilities/scope, not an unconditional privacy-threshold promise |
| `integrity:pauseonfocusloss_desc` | `core/player/focus_guard.js` records blur only when indicator recording is enabled; pausing also depends on the strict focus policy |
| `setting:strictsessionvalidation_desc` | Tracker checks same-session evidence first; strict mode stops its fallback; bookmark/Forum historical lookups remain separate |
| `setting:validationfallbackdays_desc` | Note/bookmark/Forum callers supply an age; reaction caller does not. Historical lookup may use retained segment or aggregate-state timestamps; deleted data is not restored |

See [interaction-policy scope](13_SECURITY_TRUST_BOUNDARIES.md#watched-evidence-settings-and-their-actual-scope).
No runtime change is made to unify these different existing policies. A future functional policy change
would require its own design and tests. Candidate 1.7.243 native CI/server/strict gates remain pending;
379 PHPUnit tests, 50 Behat scenarios / 1074 steps are expected source counts, not new execution results.
U-020 remains open/deferred at the maintainer’s request before the final production gate.

### Keys reviewed by the second pass

- `integrity:enable_help`
- `integrity:pauseonfocusloss_desc`
- `integrity:methodology`
- `integrity:reportintro`
- `integrity:settingsintro`
- `integrity:studentnotice`
- `integrity:analytics_recording_disabled_controls`
- `integrity:randompauseautomatic`
- `integrity:randompauses_desc`
- `integrity:preventpip_desc`
- `setting:strictsessionvalidation`
- `setting:strictsessionvalidation_desc`
- `setting:validationfallbackdays`
- `setting:validationfallbackdays_desc`
- `setting:validationfallbackdays_privacywarning`
- `forum:enable_help`
- `forum:studenthelp`
- `forum:composerprivacy`
- `forum:privacyinfo`
- `error:learnertrackingstaff`
- `error:playbackpositionnotwatched`
- `error:playbackrequired`
- `error:notesratelimit`
- `error:reactionratelimit`
- `error:bookmarksratelimit`
- `acknowledgement:enabled_desc`
- `acknowledgement:text_help`
- `acknowledgement:videoendrequired`
- `acknowledgement:pendingvideoend`
- `acknowledgement:timing_help`
- `acknowledgement:privacyhint`
