# Source-grounded language review

Current tree: VideoTrack **1.7.249** (`2026111102`). Review recorded 2026-11-11.

## Scope and limits

The cumulative review covers **789 unique keys per pack / 6312 values** in DE, EN, ES, FR, HI, IT, PL and PT.
The other **208 keys per pack remain outside the claimed semantic review**. All 997 keys and placeholder
multiplicities are structurally aligned. This assisted source review is not native linguistic sign-off,
manual accessibility acceptance or certification of every deployed browser/provider combination.

U-017 and U-007 remain closed. U-020 remains open and deferred at the maintainer’s request; all eight
manual observations are NOT RUN. The [manual protocol](19_ACCESSIBILITY.md#manual-evidence-protocol-u-020)
remains part of the final production gate. Independent language work does not approve those observations.

## Accepted starting point

- Actual 1.7.248 / `2026111101`, 412 files identical to main `9e53a6f1eb042b57612aca0bae7e1b263f5771e6`.
- ZIP SHA-256: `05f0c797dca4fef7cd629300b20bc00d501ea6d58e23fa67d39415e6715b6daa`.
- Server-report ZIP SHA-256: `43144759d020f0eedac37dbf2a30cfe05cc0a89fff4d695a82a1bc896302669d`.
- Candidate CI `42995227950`: six jobs pass PHPUnit 379/4076, Behat 50/1074 and strict 10/0/0 on the first
  scenario attempt. Post-merge CI `42999353066`: all six jobs finish successfully; five pass Behat on the
  first attempt. Moodle 5.1 initially fails one Vimeo rollback/reaction-visibility assertion: the reaction
  label is present but not visible. Its complete rerun passes 1 scenario / 43 steps. The tool’s existing
  automatic rerun policy is unchanged. The exact root cause is unproven; retain this intermittent observation
  for recurrence review, rather than claiming six clean first attempts or a proven functional regression.
- Actual Moodle 5.0/5.3 server reports pass PHPCS/lint 166, Grunt and PHPUnit 379/4076. Both supplied strict
  outputs are 10/0/0; AMD hashes before/after Grunt match. Server reports did not run Behat or manual assistive
  checks. The known Browserslist data-age notice, 24 reviewed PHPMD findings and Psalm informative totals
  732/760 remain recorded. Configured analysis gates pass; no zero-advisory or new native execution is claimed.

## Complete cumulative key manifest

The following selectors are exhaustive and disjoint. “Without a colon” includes completion keys and the
unprefixed metadata, activity, player, study-tool and lifecycle labels. Retained keys without an active
rendering call are reviewed as retained text; this does not introduce or certify new functionality.

| Selector | Reviewed keys per pack |
|---|---:|
| `privacy:` | 74 |
| `setting:` | 110 |
| `report:` | 260 |
| `coursereport:` | 25 |
| `teacherdashboard:` | 15 |
| `ctrl:` | 12 |
| `html5:` | 12 |
| `source:` | 3 |
| `err:` | 14 |
| `completiondetail:` | 8 |
| All keys without a colon | 228 |
| Additional exact keys below | 28 |
| Total unique keys | 789 |

- `acknowledgement:enabled_desc`
- `acknowledgement:pendingvideoend`
- `acknowledgement:privacyhint`
- `acknowledgement:text_help`
- `acknowledgement:timing_help`
- `acknowledgement:videoendrequired`
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

## Current activity/player/study-tool pass

This pass reviews **259 additional keys / 2072 values**, with no reused accepted key: the remaining 218
unprefixed keys and all 41 keys under `ctrl:`, `html5:`, `source:` and `err:`. The previous 530-key evidence
remains accepted. The cumulative total is 789; 208 remain. Earlier findings are preserved in the changelog.

### Findings grounded in the executed code paths

- Captions: YouTube receives preference flags; Vimeo requests an existing matching track and catches a
  missing-track failure. Neither creates a provider track. HTML5 attaches one VTT `<track>` and uses the
  configured language as `srclang`/label. Help now distinguishes availability, metadata and separate transcript
  files. This does not introduce multiple native subtitle files or a Moodle overlay over external iframes.
- Reactions can be saved while paused at an already-watched point, subject to existing participation,
  feature, session and validation rules. The empty-history message no longer implies playing is mandatory.
  Targeted labels distinguish a minimum of distinct types from a generic reaction requirement.
- `view.php` supplies the saved server resume position only when enabled and beyond 5 seconds. Manual
  backward navigation still follows the activity policy. Provider-specific browser-position recovery is a
  separate existing path; this wording does not promise that a 5-second threshold governs every adapter cache
  or that disabling server resume clears it. No resume path is changed or newly certified by this patch.
- `videotrack_pluginfile()` uses Moodle course/module access plus `mod/videotrack:view`, file-area and content
  checks. The file notice no longer says “only enrolled students”: staff and other permitted access also exist.
- `reaction_file_icon_errors()` accepts a new or existing icon and requires one for an enabled configured
  file-icon reaction. The helper no longer calls that image optional in this case. `videotrack_resize_reaction_icon()`
  keeps the original if image processing is unavailable or fails; the 64×64 notice now states that fallback.
- Poster removal follows playback start, including autoplay; clicking Play is not the only trigger. The HTML5
  range control receives a video-position label instead of translations meaning textual “Search”. Chapter/
  transcript notices use seek/navigation terminology in DE/PT. Transcript search labels remain search labels.
- Polish media/width/step units and the blocked-autoplay instruction are aligned. Restore notices regain
  diacritics and Hindi script without changing placeholders or the restore mapping/fallback itself.

Sources: `view.php`, the three provider adapters, `core/player/timed_text` and poster helpers,
`form_definition`, `mod_form.php`, `lib.php`, `reaction_form_validation` and the existing media/interaction/
timed-text/restore tests. Existing passing test evidence is not relabelled as candidate execution.

## Exact keys changed in this candidate

- `allowdownload`
- `autoblockedlabel`
- `captions_help`
- `captionslang_help`
- `chapterfile_notice`
- `fastforwardstep`
- `html5:seek`
- `noreactionsyet`
- `playerwidth`
- `posterimage_help`
- `reactioniconfile_help`
- `reactioniconfile_notice`
- `reactionsrequired`
- `restore_missing_reaction_mapping`
- `restore_placeholder_reaction`
- `resumeplayback_desc`
- `resumeplayback_help`
- `rewindstep`
- `showtranscript_help`
- `timedtext_seek_blocked`
- `videofile`
- `videofile_notice`

Nine strings change in each pack; the other changes address targeted translation findings.

| Pack | Values changed |
|---|---:|
| DE | 14 |
| EN | 9 |
| ES | 12 |
| FR | 12 |
| HI | 11 |
| IT | 9 |
| PL | 19 |
| PT | 14 |

Total: **100 values across 22 existing keys**. No key is added or removed. Placeholder multiplicities,
numeric tokens, machine identifiers, CSV headers and formatting logic are preserved. The HTML5 position
slider’s accessible name changes deliberately. No new test merely duplicates these literal corrections.

## Candidate gates and agreed sequence

The tree retains 412 files, 31 documents per EN/IT guide, 166 PHP files, 1101 PHP/654 AMD callables and 345
inventory rows. XMLDB remains `2026110601`: 7 tables / 151 fields / 22 indexes / 22 foreign keys. ER changes
only current version labels. Runtime logic, access rules, stored data, DB, services, AMD, tests, fixtures
and CI/static-analysis rules are byte-identical to accepted 1.7.248.

Local contract/delta/link and exact apply/reverse checks do not replace native six-job candidate CI, post-merge
CI and actual merged ZIP/server/strict gates. The unchanged expected suite is 379 PHPUnit tests / 4076 assertions
and 50 Behat scenarios / 1074 steps. Those are expectations, not results for this candidate.

Finish the existing web work and App decision, then implement the agreed multilingual VTT subtitle phase
before authenticated foundation, OneDrive/SharePoint, Zoom Cloud and Google Drive. The existing transcript
panel remains distinct. HTML5 controls/file lifecycle and external-provider/fullscreen feasibility need
their own evidence. Automatic publication to provider accounts is not selected. U-020 remains deferred.
