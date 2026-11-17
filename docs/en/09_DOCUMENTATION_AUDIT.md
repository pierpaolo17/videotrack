# Documentation audit

Baseline: VideoTrack **1.7.258** (`2026111700`).



## Initial258 CI rejection and bounded correction

The initial 1.7.258 candidate e70102d6 (run44084042387) failed all six jobs. All451
repository blobs/modes and12 uploaded CI/faildump archives match the official GitHub
source and digests. PHPUnit429/4823 reported2errors in the new backup tests (Moodle
rejects TEXT columns in get_record conditions) and1failure from stale README release
markers. Strict reported9/0/1 and PHPCS6 boilerplate errors in3 new files.
The correction reads restored text with a scoped fieldset and compares it in PHP,
updates both exact current-release markers and restores the canonical header spacing.

Behat initially passed52/54 scenarios and1188/1192 steps, then both automatic reruns
still failed the two new resume examples. Available HTML shows Second Learner with
server resume0 and a correctly scoped current-user key; localStorage values are absent,
so the aggregate assertion does not establish which condition failed or prove a leak.
The fixture now injects known foreign/legacy hints after login and before player init,
preserving Moodle's JS-revision sentinel. All original isolation, absent-current-hint,
zero-server-resume and actual provider-time assertions remain. Per-condition diagnostics
replace the ambiguous message. No player code, storage key or build changes here.

PHPStan/Psalm had no configured errors. Initial258 PHPMD31 has3 additional restore
complexity advisories versus257's28; Psalm741/769 has1 additional information item per
branch, with types unprinted. These are recorded, not silently treated as unchanged.
The selected lifecycle gates were skipped after failures. Fresh corrected native
canonical/strict/PHPUnit/Behat/lifecycle CI remains mandatory before merge. Local
fixture-JS syntax/logic checks pass, including four invalid states; this is not Behat.

This replacement is incremental from e70102d6 on release/v1.7.258. Version remains
2026111700. Current inventory has1203 PHP callables (1196 distributed+7 repository-only)
after adding one Behat preparation step; file counts,15 new PHPUnit methods and two
new browser scenarios remain unchanged. Accepted actual baseline stays1.7.257.

## 1.7.258: security follow-up

Removal physically erases the owned note/bookmark row. AJAX parameters and responses
remain compatible. Login/context/participation/sesskey checks precede retries: missing ids
are successful no-ops, existing foreign or wrong-type rows are rejected. A Moodle lock
serialises removals and the transaction includes one event without private text. An old
soft-deleted row is erased without duplicating its event.

User backups and Privacy exports exclude removed personal items; restore rejects personal
rows marked deleted in older XML. Active tools and reaction history retain their contracts.
Backup files created before deletion may contain then-active copies: their retention is
separate, and restore cannot infer a later deletion from an older active copy. No general
migration or demo cleanup runs.

Schema recovery moves to upgrade-only db/upgradelib.php. Install/uninstall retain DML
grade repair. Valid uninstall completion contexts use completion_info::delete_all_state;
only orphan module references need scoped completion DML. Ambiguous/orphan pre-production
grade rows retain tested transactional DML: those malformed rows can break the Grade API's
context resolution, so replacing that recovery with runtime grading calls is unsafe.

Controllers use data_submitted() with existing sesskey/capability checks. Analytics downloads
use POST with the selected filters and sesskey in the body; GET downloads are rejected before
their export event/output. The server generates opaque resume keys for site, participating
user, module, source and video identity. YouTube/Vimeo ignore old shared keys and disable
storage with an incomplete identity; HTML5 retains server resume. Browser hints never
become validated viewing evidence. The activity index cleans course-format section HTML
without applying filters twice; this is defence in depth, not a reproduced Tiles XSS claim.

Accepted actual baseline257/main d6e8705e:422 matching package files, server PHPCS/lint173
clean, PHPUnit414/4695 and strict10/0/0 on50/53, unchanged AMD builds, six-job candidate/main
CI, Behat52/1154 without reruns and three lifecycle lineages on selected50/53MariaDB.
Package SHA-256:59fff2d7214a6828c1544b896d489ecebb8bbefb1821e72c6a3384ca845cc504.

Candidate258 adds15 PHPUnit methods and two provider browser scenarios, including real
backup/restore with injected legacy XML. PHP/Moodle are unavailable locally: native
PHPCS/lint/static/PHPUnit/Behat/lifecycle and actual package/server acceptance remain pending.
Actual Moodle Grunt AMD Node22 passed; only two sources/four build-map outputs changed.
AMD smoke and adapter tests pass. Three historical Node scripts fail identically on257/258:
privacy_static_test and backup_restore_static_test expect obsolete pre-refactor contracts;
tracker_segment_test lacks a core/str harness dependency. They are existing tooling debt,
not native passes. A fresh external review is required before claiming a new security grade.
Native257 PHPMD28 and Psalm740/768 advisories remain recorded; no zero-warning claim.

No schema/savepoint/service/retention-task/OAuth change. No general legacy-data migration
per maintainer; retain the synthetic demo. Manual5.3 accessibility remains standby; Tiles
visual checking is pending. After full final acceptance proceed directly to the next roadmap
patch without reconfirmation. New sources precede the App; App-to-browser trial stays skipped.

## 1.7.258: security review corrections

This release addresses the reviewed settings-authorisation and dashboard privacy gaps.
The save callbacks enforce the same course-level override capabilities as the form before
normalisation, database writes or caption-file changes. Existing locked values are preserved;
new activities use site defaults, including explicit zero and inherited empty choices.
Completion dependencies cannot be disabled with zero duration, disabled acknowledgement,
or removal/reordering of required reaction definitions. Rate limit and resume controls that
the existing form leaves editable retain that contract.

Course and teacher rows require activity aggregate-report access. The effective contributor
floor is resolved per activity: legacy/individual access may request exact values; aggregate-only
access uses at least the configured site threshold, even when a caller requests one. All-time,
period, reaction, note and bookmark summaries use that floor. Suppressed counts display as
hidden rather than zero. Activity SQL batching is retained; new benchmark timings are pending.

Empty manual grades now use a null raw grade through the Grade API for points and scales.
HTML names/options are escaped separately from plain CSV labels and HTML attribute values.
Reaction warnings and the student-facing note visibility notice use all eight language packs.
The note notice explains that authorised individual-report users may read and export notes.

Video page GET no longer creates or repairs the focus exception group. Creation remains in
add/update/restore/upgrade. Full uninstall deletes only groups with the plugin's reserved
idnumber, including when no grade items exist. Grade repair uses a delegated DML transaction
so an interrupted merge/delete rolls back; DDL is not wrapped in that transaction.

The accepted baseline is actual 1.7.256/main 26cf86ed: 418 matching files,
server PHPCS/lint 171 clean, PHPUnit 396/4581 on Moodle 5.0/5.3, unchanged Grunt build digest,
strict 10/0/0 on both sites, and six-job candidate/main CI with the selected lifecycle paths.
Candidate 1.7.257 has 18 additional native regression methods; they have not been run in this
workspace. Native canonical/static/PHPUnit/Behat/lifecycle and actual package/server acceptance
remain pending. A new external scan is required before claiming a new review grade.

Permanent note/bookmark erasure and legacy backup handling are still open. Their existing
soft-delete/retention semantics are unchanged. DDL-helper separation, general request-controller
refactoring, per-user browser resume keys and POST-only Analytics downloads remain separate work.
Microsoft OAuth configuration remains local-only, with no remote access or new media playback.
New sources follow this security work; the App remains afterwards. The App-to-browser trial is
skipped, multilingual subtitle expansion is deferred, manual Moodle 5.3 remains on standby,
and the synthetic demo data is retained.

## Initial candidate CI and correction

The first 1.7.257 candidate, commit `68ca29de`, run `44049397832`, failed all six jobs.
All six attached artifacts match GitHub's official SHA-256 digests and the 446 repository
files/modes match that commit. Each PHPUnit run reported 414 tests, 4687 assertions,
one error, one failure and one warning. The reordered-reaction regression exposed an
existing missing-icon default: validation accepted the fallback emoji but then read a
missing array entry, producing null for a non-null database column. The correction
normalises the fallback first and asserts the persisted default without filling the
omitted fixture field. A source contract now tolerates indentation around the same
course_modules recordset call; anonymous-class spacing follows the native PHPCS finding.

Strict validation passed 10/0/0 and Grunt completed in all six jobs. Behat passed 52/1154
on five jobs; Moodle 5.1 first failed the post-rollback note assertion (51/52 scenarios),
then the automatic isolated rerun passed 1 scenario/43 steps. This is not a clean initial
pass. The historical upgrade/populated-uninstall gates were skipped on the selected
5.0/5.3 MariaDB jobs after PHPUnit failed; no lifecycle result is inferred from lint.

PHPStan and Psalm reported no errors on the configured 5.0/5.3 jobs. Psalm reported
739/767 informational issues, four more per branch than accepted 1.7.256; individual
issue types are not printed. PHPMD reported 28 findings versus 24: four new complexity
advisories at the per-activity privacy checks (10 each), reaction requirement restoration
(12) and transactional grade repair (10). These guards remain explicit in their shared
entry points; no suppressions or threshold changes are added. Informational follow-up
remains open. These are initial-candidate observations, not proof for the correction.

The replacement patch is incremental from `68ca29de` on `release/v1.7.257`.
The release/version stay 1.7.257/2026111600. Corrected native canonical/static/PHPUnit,
Behat and lifecycle checks must be rerun before merge; actual ZIP/server acceptance
comes afterwards. PHP/Moodle are unavailable in this workspace, so local source and
patch-application checks do not count as native passes.

## First 256 candidate CI rejection and bounded correction

Candidate `81933c95`, run `43490372802`, fails all six jobs: each PHPUnit run has
396 tests, 4496 assertions, 9 errors from a null OAuth fixture image and 1 failure
from language key order. Canonical PHPCS reports 13 method-docblock errors and
16 language warnings; PHPStan reports 3 CLI variable-definition errors, and Psalm
reports 1 CFG error. PHPMD remains at 24 findings, byte-identical to accepted 255.
Psalm informational counts are 737/765 (previously 732/760); the logs do not print
the issue kinds.

Behat 52/1154, Grunt and strict 10/0/0 pass in all six jobs. Lifecycle is skipped
after failed prerequisites, so none of its paths has a 256 pass. This correction stays at
1.7.256/2026111301: supply a non-null fixture image; use canonical multiline method
PHPDoc; insert the same four new strings at their sorted positions; annotate CFG
and explicitly terminate the CLI catch. Preserve every assertion and CI rule.
The provider runtime, schema and credentials handling are unchanged. New native
candidate/main and package/server/strict results are required; local checks are
archive/source checks and a SQLite constraint model, not a Moodle test execution.

## 1.7.256: provider connection foundation

Actual255/main36ab70ed is accepted:415 matching files, server PHPCS/lint168 clean, Unit385/4540 on50/53 and strict10/0/0; six-job candidate/main CI passes52/1154 Behat and selected lifecycle paths. The shipped255 benchmark confirms current_userid10 and6/92 median queries in40 samples.

This candidate adds a Microsoft OAuth service reference in site settings (default: None), a read-only configuration service and provider_status.php. Credentials remain owned by core. Checks inspect provider type, enabled/service usage, client configuration, HTTPS endpoints/site and the core system-account/scope state. They do not exchange or refresh tokens or contact Microsoft. OneDrive/SharePoint is not yet a selectable video source.

The maintainer now prioritises authenticated sources: OneDrive/SharePoint, Zoom Cloud Recording, then Google Drive; the App comes afterwards. The App-to-browser/HTTPS trial is explicitly skipped. The full multilingual subtitle extension is deferred because it is not bounded reliably to one release across HTML5 and provider overlays. Existing transcripts and captions remain supported. Manual Moodle 5.3 accessibility stays on standby.

Candidate256 native validation remains pending. Expected inventories:442 repository/418 distributed files,170 distributed PHP plus1 repository helper,1141 named PHP callables (1134 distributed+7 repository-only),654 AMD callables and8 packs of1001 keys. Schema, AMD, AJAX/player/tracking and CI are unchanged. New11 PHPUnit methods need native execution; no App or remote-provider pass is claimed. Older candidate-pending sections below are historical checkpoints.


## Candidate 1.7.256: benchmark viewer context

The accepted baseline is actual 1.7.254/main a42432a (415 matching package files, clean 168-file server PHPCS/lint,
385/4540 Unit on 5.0/5.3, six-job candidate/main Behat 52/1154 and selected lifecycle paths). Its latest helper
correction is accepted; earlier correction-pending statements below are historical checkpoints.

Candidate 255 changes only the benchmark CLI and release documentation/metadata: select the active full viewer
record, set the in-process current user before measurement and expose current_userid. The source-controlled
Analytics, capability/group/privacy rules, schema, tracking, lifecycle and AMD are unchanged. Both CLI guides
remove the unsupported benchmark --json example and document the independent privacy default 2.

Native 254 profiler evidence with minusers 5 preserves all 40 results when changing current-user 0 to viewer 10,
with 218-to-92 course queries and 9-to-6 single queries. This calibrates the CLI context rather than demonstrating
a product performance optimisation. See [the CLI guide](21_CLI_DIAGNOSTICS.md) for density and timing limits.
New 255 CI/server/strict results and the shipped CLI rerun are pending. File/callable inventories are unchanged.
Manual Moodle 5.3 remains on standby; worst-case/concurrent benchmarking and the explicit MoodleApp decision remain open.


## Scope

The documentation has been checked against `version.php`, `db/install.xml`, `db/services.php`, `db/access.php`,
`settings.php`, `mod_form.php`, `classes/local`, `classes/privacy`, backup/restore, AMD source/build pairs and the
distributed test suites.

Current coverage includes:

- complete user/admin feature guide and operational limits;
- architecture, runtime flows and trust boundaries;
- all seven plugin tables, settings, capabilities, nine AJAX services and file areas;
- HTML5, YouTube and Vimeo provider behaviour;
- tracking, completion, gradebook, reports, Analytics and exports;
- reactions, notes, bookmarks, Forum bridge and acknowledgement;
- privacy, retention, reset, backup/restore and CLI diagnostics;
- accessibility contract, troubleshooting, build/release gates, repository CI and static-analysis policy;
- exhaustive non-documentation file and named-callable inventories;
- Markdown, Mermaid and accessible SVG database/ER artefacts.

## Freshness and history policy

`docs/en/archive` and `docs/it/archive` are not distributed. Release-by-release implementation narratives have
been removed from current guides. The root `CHANGELOG.md` gives only release summaries; exact older behaviour is
read from the corresponding source tag and its bundled documentation.

Root README files provide a complete feature map and route readers to the detailed guides. They intentionally do
not duplicate implementation procedures, field dictionaries or long security/privacy explanations.

## Current-tree findings

- U-017/U-007 remain closed. Actual253 is accepted, including registered-task post-restore retention.
  Candidate/main254 CI passes historical upgrade/full-uninstall paths; actual server acceptance is blocked
  by three helper PHPCS errors. The second same254 helper/CI correction awaits native evidence.
  Assisted review of997 keys and scoped manual5.0 evidence remain; manual5.3 is pending.

- `form_reaction_settings` owns general reaction settings, context-safe notice/preset fields and individual row
  fields followed by their icon visibility rules. The form retains row count, bounds, upload-filter registration
  and no-submit Add reaction control, then delegates completion locks with the actual count. Course completion
  permission remains independent of player override permission. Runtime tests cover field order, types, unique
  picker targets, original file options, saved values, exact dependencies, real form counts and real row locks.
- `form_playback_settings` owns the site-controlled playback group with one public entry point and a private
  rate-choice stage. False/empty fallback preserves explicit zero, the course lock still freezes exactly seven
  fields, and resume/maximum rate retain their existing editable state. `form_definition` keeps nine public methods.
- Integrity fields retain their disabled defaults and their place between study tools and acknowledgement;
  the displayed policy uses canonical integrity APIs for fallback, bounds and grace. The activity form no longer
  has a direct integrity-class dependency.
- CSV form fields use the effective module/course context and the existing identity-permission APIs;
  delimiter inheritance, flat checkbox names, field order and unavailable labels remain explicit.
- HTML5 control and playback-speed groups use the original course override capability, site-permitted choices,
  flat checkbox names/values and canonical order. Upload-only visibility and frozen groups remain explicit;
  empty allowed-choice sets omit their groups and missing site values use the original API defaults.
- Player behaviour preserves boolean site defaults, zero-valued numeric inheritance, saved editable values,
  upload-only download visibility and the seven-field course override lock. The width-description association
  remains conditional on edit permission. Missing/empty boolean defaults still normalise to zero.
- Captions retain their site boolean/language defaults, including the literal language value "0", upload-only
  VTT dependencies and Vimeo notices. Transcript/chapter construction keeps provider-independent fields and
  canonical file options between the caption fields and Vimeo notice; the course lock affects only captions/language.
- Duration construction keeps the numeric field, accessible status regions, field identifiers, 86,400-second
  limit, message tokens and hex-escaped DOM JSON. AMD init receives only the matching config identifier on
  the page supplied by the form; the existing provider-loader contract follows this explicit delegation.
- `form_definition` owns duration, player/timed-text, CSV, Forum, integrity and acknowledgement field construction in their existing positions;
  course-scoped choices, saved placeholders and the editor context/options remain explicit. The activity form
  retains submitted validation and section expansion without a direct acknowledgement-class dependency.
- English and Italian indexes have matching scope and ordering.
- Documentation markers, root README files and ER artefacts identify 1.7.255 / 2026111300.
- The Moodle privacy export delegates state, segment, event, integrity and acknowledgement sections in order;
  six event categories preserve the original paths, sanitisation, 500-row streaming and final flush order.
- Activity-form source, timed-text, reaction-file and forum checks remain in their original order behind focused helpers;
  reaction-row checks retain error ordering and configured-row requirements; pure reaction icon-value and
  required-field policy resides in `reaction_form_validation`.
- Reaction, completion, playback, CSV and site defaults retain their edit-form order in `form_preprocessing`;
  timed-text and poster drafts remain in the activity form after Moodle's parent preprocessing call.
- The reaction notice editor and preset JSON are prepared after timed-text drafts, before the poster draft;
  an existing editor is kept when a saved notice is present.
- `videotrack_pluginfile()` retains the Moodle callback signature and guard order; focused helpers check
  activity areas, stored paths, extension/MIME/WebVTT content and upload download/cache rules.
- Retention pairs are processed by `privacy_retention_pair` using the existing state lock, transaction and
  post-commit completion sequence; `privacy_manager` retains the public erasure and batch APIs.
- The stable instance Analytics facade delegates duration, bin selection and segment/state timeline
  aggregation to `analytics_timeline`; the original privacy masking and reaction clustering remain in the facade.
- Cross-course Analytics documents the stable `analytics_scope` facade and the focused matcher that owns candidate
  selection, technical-identity predicates, capability filtering, current-instance fallback and ordering.
- Course dashboard Analytics delegates visible activity scopes and batched state, period segment and event reads
  to `course_analytics_loader`, while `course_analytics` retains summaries and privacy-safe row assembly.
- Custom completion reads enabled reaction conditions through `completion_config::reaction_checks()`;
  both reaction-level and top-level decisions use `completion_config::combine_checks()`.
- The CSV export facade keeps its public methods and delegates context-aware field selection to
  `csv_export_fields` and identity formatting to `csv_export_identity`.
- The custom CSV event writer receives a typed activity and learner context; row formatting and user filtering
  stay in the writer. The exporter separates activity-field formatting from user-field sanitisation; acknowledgement
  confirmation checks availability before looking up or creating a learner record.
- Custom CSV event rows carry three nullable video timestamps in a typed value; overall bookmark counts retain
  blank timestamp cells and the same student-count column.
- The segment service validates the original ten-field request before the sesskey and context checks,
  persists via the same tracker transaction and emits events and completion only for accepted new segments.
- Moodle privacy export remains reachable through `provider::export_user_data()`, which delegates to the
  dedicated `exporter`; interval formatting, 500-row chunks and data sanitisation remain byte-for-byte unchanged.
- Privacy metadata lists external locations after the database tables; retention deletes expired personal rows
  before rebuilding state, within the same state lock and delegated transaction.
- Recalculation discovers tracked learner ids from the same four tables; retention rebuilds retained state
  under its existing lock and transaction before native completion synchronisation.
- Tracker reuses its existing state snapshot on rebuild and persists inside the delegated transaction;
  retention synchronises native completion after commit while still holding its state lock.
- Standalone state rebuilds acquire and release the canonical lock; retention passes its actual held lock to
  the same rebuild method and retains ownership through the transaction and native completion sync.
- The activity identity includes a 1024-pixel `pix/icon.png` and an accessible native `pix/icon.svg` derived from
  the same maintainer-supplied artwork.
- `db/install.xml` declares seven primary keys, 22 stable foreign keys and 22 explicit indexes. XMLDB generates an
  additional exact backing index for every foreign key but no physical database constraint or cascade.
- `linkedforumid` and `reactionid` remain documented conditional references because their `0` sentinel is valid.
- The read-only CLI validator checks declared relations, backing indexes, orphans, denormalised context consistency
  and gradebook integrity.
- Lifecycle documentation covers the pre-core gradebook uninstall hook, malformed-row fallback, CLI preflight and
  zero-residue uninstall verification. CRUD, AJAX, privacy and completion runtime behaviour is unchanged.
- The activity and media guides document opt-in reaction configuration, effective learner-section visibility and
  the live `HH:MM:SS` duration equivalent.
- Uploaded-video and poster writes resolve only module contexts tied to submitted form identifiers or the persisted
  activity/course pair. Uploaded-media URL lookup rejects a course-module id that belongs to another VideoTrack
  instance, and the video-field helper validates every non-null Moodle-form collaborator.
- Reaction reads have explicit scopes: the standard helper returns active definitions, while lifecycle code can
  request active and soft-deleted definitions without a boolean behaviour flag. Activity records are filtered to
  real table columns through one request-local metadata cache before insert or update.
- Timed-text reads have explicit canonical and compatibility contracts. Dedicated transcript/chapter files retain
  precedence, and the historical subtitle area is used only by named fallback methods selected for migrated
  uploaded-video activities.
- The GitHub Actions guide documents triggers, least-privilege permissions, the six-job Moodle/PHP/database matrix,
  deterministic classic/`public/` ordinary-site bootstrap, rejection of generated `.types` mirrors, the
  authoritative `moodle-plugin-ci grunt` comparison, strict/advisory checks, retained logs and faildumps.
- The upper-bound MariaDB and PostgreSQL jobs select `MOODLE_503_STABLE` explicitly. They never use upstream
  `main`, which can move beyond VideoTrack's declared branch 500–503 support interval.
- PHPStan/Psalm have production-only scopes and a shared installed-Moodle bootstrap. The bootstrap loads canonical
  backup/restore include graphs before the Moodle 2 step libraries. A narrow Psalm stub models only Moodle's global
  `renderable` compatibility name and the upstream `xmlddb_field` DocBlock typo. Five entry points document the
  globals created by `config.php` under narrowly scoped PHPCS exceptions; no analyser category or production path
  is ignored.
- Psalm explicitly disables only `ensureOverrideAttribute`: its native fix requires PHP 8.3, but the supported
  Moodle 5.0 matrix includes PHP 8.2. Inheritance and signature checks remain enabled.
- Production files that consume globals or containers supplied by Moodle loaders have narrow analysis contracts.
  Existing declarations conform directly to Moodle PHPCS; the five direct entry points scope an exception only to
  the inline-DocBlock sniff around their 20 PHPStan declarations. Psalm also has a typed `$DB` global plus the loader
  variables used by `settings.php` and `version.php`. Two PHPStan path/name-specific rules keep unmatched-ignore
  reporting enabled.
- The active `phpmd.xml` removes reviewed Moodle naming/framework noise, keeps selected runtime rules and
  distinguishes advisory findings from analyser/configuration errors. Production code uses explicit delimiter,
  Analytics export-shape and course-percentage rendering APIs. `report_analytics_query`, `report_event_query` and
  `report_support` each own seven cohesive public contracts for Analytics SQL, event SQL and remaining presentation/
  option/highlight/clustering work respectively. `report_time_filter` owns four cohesive date/request/rendering
  contracts, and `report_aggregation` owns viewer-drop discovery plus reaction-cluster ordering. Teacher Analytics
  rendering is divided among shared-control, timeline, aggregate-summary and integrity-state view services, each
  below the public-method threshold and isolated from query or aggregation responsibilities. Timeline chart stages
  preserve privacy gaps and SVG order, while acknowledgement stages preserve full and progress-only suppression.
  Combined export construction separately owns timeline padding, ordered event summaries, integrity fallback and
  acknowledgement values while preserving the public row and privacy contracts.
  Same-video candidate discovery is isolated in `analytics_scope_matcher`; its focused stages preserve provider-id,
  upload-hash and canonical-URL identity plus per-module aggregate-report capability checks.
  The Moodle feature callback is
  table-driven, Font Awesome values contain exactly one validated icon name, and rendered reaction icons always
  include their visible label without a boolean presentation flag. Exact finding counts are read from the current
  candidate's CI artifact.
- The two AMD build pairs corrected in 1.7.123 are canonical Moodle Grunt output; their source maps have non-empty
  mappings and embed source content byte-identical to the distributed AMD sources.
- Rendered accessibility helpers use Moodle 5 / Bootstrap 5 `visually-hidden` classes; player live regions are
  present in the initial markup so normal activity views do not use the deprecated Bootstrap 4 `sr-only` fallback.
- `.github/workflows/ci.yml` and its pinned analyser-tool manifest are present in the repository but excluded from
  Moodle release archives. The distributed configs and bootstrap remain available to maintainers and server gates.
- Generated CI/server evidence is not part of the plugin tree. Repository `.gitignore`, Git archive
  `export-ignore` rules, Moodle `.moodleignore` rules and a PHPUnit release-hygiene contract jointly prevent
  `reports-*`, `videotrack-ci-*.zip` and `report_completo_videotrack_*.zip` from entering future releases. The
  canonical release archive must therefore contain the 415 legitimate plugin files only.

## Release checks

Before promotion, verify:

1. no `archive/` or `reports-*` directory, generated CI/server ZIP or corresponding link remains in the package;
2. no obsolete version narrative remains in current docs;
3. local Markdown links resolve and the EN/IT document sets are paired;
4. file/callable inventories match the exact tree;
5. XMLDB/ER counts and conditional-reference labels match `db/install.xml`;
6. release-hygiene, XMLDB relationship and uninstall/gradebook PHPUnit contracts plus the proportionate server gate pass;
7. GitHub workflow syntax, action versions, matrix selectors, permissions and links match the repository file.

## Current review and agreed next stages

Actual 253/main `511234197069f8f83e23f0da9444fa1d6a5f8030` is accepted: 415-file ZIP, both server reports,
384/4516 PHPUnit, 52/1154 Behat, strict10/0/0 and stable Grunt outputs. Candidate254 adds repository-only
historical/partial/previous install-upgrade-uninstall gates in two existing CI jobs; execution is pending.
The completion correction passes candidate/main CI385/4540 and all six lineage paths. The server
report passes Unit/Grunt/lint and both supplied strict outputs pass10/0/0, but fails canonical PHPCS
on the hidden helper. Correct boilerplate/bootstrap/comment and explicitly check helper lint/PHPCS
in CI, retaining all existing gates. This second same254 correction awaits fresh native evidence. There are 1116 distributed PHP callables plus
7 CI helper functions, 1123 overall; file inventory348 contains343 distributed and5 repository-only entries.
See [production evidence](28_PRODUCTION_EXIT_AUDIT.md) for exact histories, limits and remaining manual/benchmark/App gates.

Initial254 CI passes384/4516 Unit and52/1154 Behat in all six jobs; both lifecycle jobs fail on native
completion after full uninstall. Corrected254 expects385 Unit methods, assertions to be measured, plus
all three lifecycle paths on both selected jobs. File metadata passed initially; physical-content checks
were vacuous and now require one actual regular file in each named fixture area.
