# Targeted language review

Current tree: VideoTrack **1.7.244** (`2026111003`). Review recorded 2026-11-10.

Current cumulative scope: **134 unique keys per pack / 1072 distinct values**. The latest pass covers
all 88 privacy/completion keys and corrects five keys per pack. The remaining 863 keys are outside the
claimed semantic review. U-020 remains open/deferred; native linguistic quality is not certified.

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
hygiene, lint, language-contract, PHPUnit, browser and strict gates must run on 1.7.244 itself. Counts of
379 tests / 50 scenarios / 1074 steps are expected, not new execution results for this candidate.

Continue semantic review outside the cumulative 134 unique keys and obtain human linguistic review where needed. U-017/U-007
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
would require its own design and tests. Actual 1.7.243 native CI/server/strict gates are accepted; candidate 1.7.244 gates remain pending;
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

## Third pass: 1.7.244 privacy and completion

Accepted actual 1.7.243 contains 412 files identical to main
`942bf5965d7209673dfa8da2772928179c952052`. ZIP SHA-256:
`b42316ad2be7bdef46b9c39ed88c431bd6329426add8ee47a96660107c1e94d3`.
Server-report ZIP SHA-256: `d89f8965ad236358e9fea2cc3f78105a82e78dfc903ac8a46a190ee253e6916f`.
Candidate/main CI `42792298746` / `42795878952`: all six jobs pass PHPUnit 379/4076, Behat 50/1074 and
strict 10/0/0. Moodle 5.0/5.3 server PHPCS/lint 166, Grunt and PHPUnit 379/4076 pass; both supplied strict
outputs are 10/0/0. The report does not execute server Behat or manual assistive tests.

This pass reviews every `privacy:` and `completion` key: 88 per pack / 704 values. Two were in earlier passes;
the cumulative scope becomes 134 unique keys per pack / 1072 distinct values. Five keys per pack are corrected
(40 values). The other 863 keys per pack are not claimed reviewed; no native linguistic quality is certified.

| Corrected keys | Existing implementation reflected by the wording |
|---|---|
| `completionacknowledgement_desc`, `completionlogic` | `tracker::completion_satisfied()` includes acknowledgement in the configured AND/OR rule. `mod_form.php` uses the same selector for percentage, reactions and acknowledgement |
| `privacy:metadata:videotrack_seg:wallclockstart`, `privacy:metadata:videotrack_seg:wallclockend` | `save_segment` bounds diagnostic browser times; `tracker::begin_playback()` creates server-time playstart rows. Authoritative credit uses a separate server-time ledger |
| `privacy:metadata:videotrack_state:iscompleted` | Tracker persists the custom-rule result. Moodle consumes one composite custom rule and may impose additional native requirements |

Source behaviour, data values, custom/native completion, gradebook, permission boundaries, export payloads,
retention and deletion are unchanged. The candidate still has 412 files / 31 documents per language,
997 keys per pack, 166 PHP files, 1101 PHP/654 AMD callables and XMLDB 7 tables / 151 fields / 22 indexes /
22 foreign keys. Existing test/feature bodies and CI rules are unchanged. Native candidate gates must still
run; 379 PHPUnit tests and 50 Behat scenarios / 1074 steps are expected, not new execution results.
U-020 remains open/deferred before final production acceptance.

### Keys reviewed by the third pass

- `completionacknowledgement`
- `completionacknowledgement_desc`
- `completiondetail:acknowledgement`
- `completiondetail:allreactiontypes`
- `completiondetail:logicand`
- `completiondetail:logicor`
- `completiondetail:minreactions`
- `completiondetail:percent`
- `completiondetail:requiredreactions`
- `completiondetail:videotrackconditions`
- `completionlogic`
- `completionpercent`
- `completionreactionrules`
- `completionreactionrules_desc`
- `privacy:bookmarksactivechunk`
- `privacy:bookmarksdeletedchunk`
- `privacy:integritychunk`
- `privacy:intervals_none`
- `privacy:intervals_unavailable`
- `privacy:metadata:common:cmid`
- `privacy:metadata:common:courseid`
- `privacy:metadata:common:timecreated`
- `privacy:metadata:common:timemodified`
- `privacy:metadata:common:videoid`
- `privacy:metadata:common:videotrackid`
- `privacy:metadata:external:cookies`
- `privacy:metadata:external:ipaddress`
- `privacy:metadata:external:useragent`
- `privacy:metadata:videotrack_acknowledge`
- `privacy:metadata:videotrack_acknowledge:instanceversion`
- `privacy:metadata:videotrack_acknowledge:statementhash`
- `privacy:metadata:videotrack_acknowledge:timeconfirmed`
- `privacy:metadata:videotrack_acknowledge:userid`
- `privacy:metadata:videotrack_acknowledge:viewedpercent`
- `privacy:metadata:videotrack_acknowledge:viewedseconds`
- `privacy:metadata:videotrack_integrity`
- `privacy:metadata:videotrack_integrity:eventtype`
- `privacy:metadata:videotrack_integrity:sessionid`
- `privacy:metadata:videotrack_integrity:userid`
- `privacy:metadata:videotrack_integrity:videotime`
- `privacy:metadata:videotrack_reactev`
- `privacy:metadata:videotrack_reactev:isdeleted`
- `privacy:metadata:videotrack_reactev:notetext`
- `privacy:metadata:videotrack_reactev:notetype`
- `privacy:metadata:videotrack_reactev:playbackrate`
- `privacy:metadata:videotrack_reactev:reactiondesc`
- `privacy:metadata:videotrack_reactev:reactionid`
- `privacy:metadata:videotrack_reactev:reactionkey`
- `privacy:metadata:videotrack_reactev:reactionlabel`
- `privacy:metadata:videotrack_reactev:sessionid`
- `privacy:metadata:videotrack_reactev:userid`
- `privacy:metadata:videotrack_reactev:videotime`
- `privacy:metadata:videotrack_seg`
- `privacy:metadata:videotrack_seg:endreason`
- `privacy:metadata:videotrack_seg:playbackrate`
- `privacy:metadata:videotrack_seg:requestid`
- `privacy:metadata:videotrack_seg:servervalidated`
- `privacy:metadata:videotrack_seg:sessionid`
- `privacy:metadata:videotrack_seg:userid`
- `privacy:metadata:videotrack_seg:videotimeend`
- `privacy:metadata:videotrack_seg:videotimestart`
- `privacy:metadata:videotrack_seg:wallclockend`
- `privacy:metadata:videotrack_seg:wallclockstart`
- `privacy:metadata:videotrack_state`
- `privacy:metadata:videotrack_state:completionpercent`
- `privacy:metadata:videotrack_state:durationseconds`
- `privacy:metadata:videotrack_state:intervaljson`
- `privacy:metadata:videotrack_state:iscompleted`
- `privacy:metadata:videotrack_state:lastposition`
- `privacy:metadata:videotrack_state:serverbudgetseconds`
- `privacy:metadata:videotrack_state:servercreditedseconds`
- `privacy:metadata:videotrack_state:serverlastactivity`
- `privacy:metadata:videotrack_state:serverplaybacksessionid`
- `privacy:metadata:videotrack_state:uniquecoveredseconds`
- `privacy:metadata:videotrack_state:userid`
- `privacy:metadata:vimeo`
- `privacy:metadata:vimeo:url`
- `privacy:metadata:vimeo:videoid`
- `privacy:metadata:youtube`
- `privacy:metadata:youtube:url`
- `privacy:metadata:youtube:videoid`
- `privacy:notesactivechunk`
- `privacy:notesdeletedchunk`
- `privacy:reactionsactivechunk`
- `privacy:reactionsdeletedchunk`
- `privacy:segmentschunk`
- `privacy:state`
- `privacy:videoid_export_note`
