# Documentation audit

Baseline: VideoTrack **1.7.243** (`2026111002`).

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

- [U-017](25_MAINTAINABILITY_EXIT.md) and [U-007](26_BROWSER_EXIT_AUDIT.md) are closed on accepted
  evidence. Actual 1.7.240 ZIP/main/server/strict match; six release/main jobs pass Behat 50/1074
  and PHPUnit 379/4076. Actual 1.7.241 ZIP/server/strict and release/main CI are accepted.
  Actual 1.7.242 ZIP/server/strict and release/main CI are accepted. Candidate 1.7.243 corrects four policy
  keys per pack and related guides; runtime logic, tests, schema, AMD and CI/analysis rules are unchanged.
  Manual U-020 is deferred and remains open.

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
- Documentation markers, root README files and ER artefacts identify 1.7.243 / 2026111002.
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
  canonical release archive must therefore contain the 400 legitimate plugin files only.

## Release checks

Before promotion, verify:

1. no `archive/` or `reports-*` directory, generated CI/server ZIP or corresponding link remains in the package;
2. no obsolete version narrative remains in current docs;
3. local Markdown links resolve and the EN/IT document sets are paired;
4. file/callable inventories match the exact tree;
5. XMLDB/ER counts and conditional-reference labels match `db/install.xml`;
6. release-hygiene, XMLDB relationship and uninstall/gradebook PHPUnit contracts plus the proportionate server gate pass;
7. GitHub workflow syntax, action versions, matrix selectors, permissions and links match the repository file.

## 1.7.241 U-007 evidence consolidation

Accepted baseline is the actual 408-file 1.7.240 ZIP matching main `2d047f5c59792e4c8798a31209f19cfa9ecc15f7`.
The paired browser exit audits record exact hashes, candidate/main provenance, all five original exit
criteria and real six-environment 50/1074 and 379/4076 results. The server report has no Behat/full-preset run.
Current browser guides retain technical contracts and remove obsolete failed-candidate narratives. Release
guides now state the actual 379/50/1074 suite counts. U-017 stays closed with 24 reviewed PHPMD findings.

Candidate 1.7.241 adds only two Markdown audit files and updates current documentation/version metadata.
There are 410 distributed files, 345 non-documentary inventory rows, 1,101 PHP callables and 654 AMD callables.
All runtime PHP except version metadata, existing test/fixture/AMD/language/schema/service/ruleset bytes are
preserved. ER changes are current-tree labels only: XMLDB remains 2026110601, seven tables/151 fields/22
indexes/22 foreign keys. Eight language packs retain 997 keys. U-020's eight manual rows are explicitly
NOT RUN; semantic language review, live deployment checks and the final integrated gate remain separate.
Actual 1.7.241 CI/package acceptance is still required; no manual pass is inferred from automation.

## Targeted language review in 1.7.242

19 keys / 152 values reviewed; six keys per pack / 48 values corrected. Keys/placeholders and runtime
logic/tests/AMD/schema/rules are preserved. The candidate has 412 distributed files and 31 documents per
language. See [review evidence](27_LANGUAGE_REVIEW.md). U-020 is deferred; every manual row remains
NOT RUN. Native runtime gates for 1.7.242 remain pending.

## Policy-help review in 1.7.243

31 keys / 248 values reviewed in this pass; four keys per pack / 32 values corrected. The cumulative
semantic scope is 48 unique keys per pack; 949 remain outside the claimed review. The 412-file candidate
preserves runtime/tests/AMD/schema/CI and 997 keys/placeholders in each pack. U-020 stays deferred.
See [review evidence](27_LANGUAGE_REVIEW.md). Candidate runtime gates remain pending.
