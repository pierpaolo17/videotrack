# Documentation audit

Baseline: VideoTrack **1.7.67** (`2026081709`).

## Coverage

- Non-documentation files inventoried: **271/271**.
- Named PHP functions/methods inventoried: **663**.
- Named AMD callables detected and inventoried: **647**.
- XMLDB tables documented: **7**.
- Site-setting keys documented: **57**.
- Player configuration keys documented: **133**.
- AJAX services documented: **9**.
- Language packs: eight packs with the same **982-key** contract; operational strings are translated while technical and proper terms may legitimately remain identical.
- Root overviews: `README.md` (English) and `README_IT.md` (Italian).
- Root privacy summaries: `PRIVACY.md` and `PRIVACY_IT.md`.
- Distributed CLI diagnostics documented in `21_CLI_DIAGNOSTICS.md` and covered by static read-only contracts.
- Behat browser automation is documented in `22_BEHAT_BROWSER_TESTS.md`, including current deterministic coverage and explicit provider coverage limits.
- Static resume/completion/stacked-alert contracts cover the three providers while Behat remains unavailable in the maintainer environment; browser evidence is still explicitly pending.
- Maintainer-only consolidated roadmap/lessons-history files are intentionally excluded from the distributed plugin tree and protected by `.moodleignore`.
- Chapter navigation now has an explicit focus-visible/forced-colour contract; a manual keyboard/high-contrast matrix remains a release gate for final U-020 closure.

## 1.7.67 pre-production documentation verification

- Current non-documentation file inventory regenerated against the candidate 1.7.67 tree: **271/271** entries.
- Current function inventory regenerated from source locations: **663** named PHP functions/methods and **647** detected named AMD callables.
- `reports_course.php` and `reports_teacher.php` both request the exact authorised-report threshold and no longer render privacy-suppression notices or masked cells; instance, course and teacher Analytics now share the same exact-viewer semantics.
- XMLDB inventory matches all **7** tables and every declared field in `db/install.xml`.
- Site-setting inventory matches all **57** `mod_videotrack` settings.
- AJAX service inventory matches all **9** declared services.
- Browser player configuration inventory matches all **133** keys, including the four forward-seek policy/runtime labels introduced after 1.6.33.
- All eight maintained language packs expose **982** identical keys, no duplicates and matching Moodle placeholders.
- Relative Markdown links across the plugin documentation were checked against the tree with no missing target found.
- Historical technical documents under `archive/` remain historical; current indexes/inventories are rebased to 1.7.67. Internal maintainer roadmap/lesson artifacts are not part of the distributed tree.

## Freshness rules

Current documents must not include release-specific assertions without a version/status label. Historical 1.4.x audits are isolated in `archive/`. Any change to schema, services, File API, player configuration, reports, privacy, accessibility or translations requires updating the corresponding numbered documents.

## Automated audit expectations

A release audit must compare file inventory to the tree, function inventory to source, language key sets/placeholders, static `get_string` references, XMLDB to backup/restore, services to executable classes, AMD sources to generated assets and Markdown links to existing files.

## 1.6.33 deletion-based retention coverage

- Scheduled retention permanently deletes expired playback segments, interactions, integrity signals and acknowledgements; it no longer retains deterministic negative-user pseudonyms or a mapping key.
- `videotrack_state` is treated as derived personal data, rebuilt from retained server-validated segments and retained completion inputs, and removed when no such input remains.
- Stale playback-credit counters are cleared, active bounded guards are preserved and Moodle custom completion is synchronised with the rebuilt state.
- User-data backups include only positive-user records inside the source retention window and omit derived state; restore applies the destination retention policy and rebuilds state after course-module completion restore.
- Privacy API erasure removes learner rows while shared activity files remain governed by the activity lifecycle.
- Regression tests cover mixed old/recent data, state rebuilding/deletion, active guards, legacy-pseudonym removal and post-retention user erasure.

## 1.6.32 playback-ledger coverage

- `mod_videotrack_start_playback` establishes a zero-credit server timestamp before a tracked segment opens.
- Every handshake and segment has a persistent request identifier protected by a unique activity/user/request index.
- Transport retries reuse an identical stored result and cannot duplicate rows, completion transitions or events.
- Playback credit is cumulative, derived from elapsed server time and an allowed rate, with a bounded clock-drift tolerance that remains cumulative debt across requests and handshakes.
- Exact covered seconds remain monotonic when the compact interval list reaches 500 entries.
- XMLDB, upgrade, backup/restore, Privacy API, language metadata, AMD sources/builds and regression tests document the same ledger contract.

## 1.6.31 runtime-contract and privacy coverage

- The client AJAX allowlist is checked against every declared Moodle AJAX service; integrity indicators are no longer rejected as `invalid-method`.
- Every learner mutation endpoint validates the Moodle sesskey before loading course-module or database context.
- Privacy masking propagates from a hidden timeline bin to the total-viewer value and to retention percentages that use that total as their denominator.
- Reaction responses expose structured icon fields only; the HTML5 player no longer parses a raw `iconhtml` field.
- Releases 1.6.25–1.6.31 are documented as code-only with respect to XMLDB and intentionally have no no-op upgrade savepoints.

## 1.6.29 explicit-participation coverage

- Activity UI, all learner mutation services and learner report populations use `mod/videotrack:participate`.
- Report access no longer disables a genuine dual-role participant.
- Standard teachers/managers remain non-tracking because the capability is granted to the Student archetype and admin do-anything is ignored for this decision.
- Role administrators can grant or revoke the capability for custom participation roles.

## 1.6.28 duration-configuration transport coverage

- Localised detector configuration is stored in a JSON script element in the activity-form DOM.
- `js_call_amd()` receives only the configuration element id, keeping its serialised argument far below Moodle’s 1024-character developer-warning threshold.
- The AMD module validates and parses that DOM configuration before installing the detector.

## 1.6.27 automatic-duration coverage

- The trusted activity form can suggest duration from YouTube, Vimeo or local-file metadata.
- The suggestion is teacher-editable, announced through an accessible live region and becomes authoritative only when the teacher saves.
- Learner runtime duration remains unable to update the stored value; provider or browser failure leaves the field manual.

## 1.6.26 player-tool reliability coverage

- A verified duration of `0` explicitly disables watched-percentage calculation and percentage-based completion while preserving validated interval tracking and enabled study tools.
- The complete HTML5-controls fieldset is hidden for YouTube and Vimeo and restored when the source changes to a local upload.
- Learner reaction controls remain available while playing or paused, but the server accepts only already-watched validated timestamps.
- Enabled notes and bookmarks are rendered for explicit participants; users without `mod/videotrack:participate` receive a disabled preview and cannot create learner telemetry.
- The PHPCS findings reported against 1.6.25 in `view.php`, `report.php` and `tests/tracker_test.php` are corrected in the 1.6.26 source tree.

## 1.6.25 interface regression coverage

- The acknowledgement Analytics renderer initialises both count and progress suppression state.
- Reaction definitions remain inside the main Reactions form section and are not modelled as a fixed two-item set.
- Activity-view ordering is reactions, optional Forum action, then personal reaction history/bookmarks.
- Report-capable preview and learner persistence remain separate trust states.

## 1.6.30 bookmark persistence coverage

The current documentation records the restored parity between the AMD `bookmark` segment reason and the PHP validation whitelist. It also preserves the security contract that a bookmark timestamp must belong to server-validated watched progress.

- 1.7.45 adds Moodle Behat/generator documentation and records U-007 as in progress rather than complete.
