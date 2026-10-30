# Documentation audit

Baseline: VideoTrack **1.7.207** (`2026103003`).

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

- English and Italian indexes have matching scope and ordering.
- Documentation markers, root README files and ER artefacts identify 1.7.207 / 2026103003.
- Cross-course Analytics documents the stable `analytics_scope` facade and the focused matcher that owns candidate
  selection, technical-identity predicates, capability filtering, current-instance fallback and ordering.
- Course dashboard Analytics delegates visible activity scopes and batched state, period segment and event reads
  to `course_analytics_loader`, while `course_analytics` retains summaries and privacy-safe row assembly.
- Custom completion reads enabled reaction conditions through `completion_config::reaction_checks()`;
  both reaction-level and top-level decisions use `completion_config::combine_checks()`.
- The custom CSV exporter separates activity-field formatting from user-field sanitisation; acknowledgement
  confirmation checks availability before looking up or creating a learner record.
- Privacy metadata lists external locations after the database tables; retention deletes expired personal rows
  before rebuilding state, within the same state lock and delegated transaction.
- Recalculation discovers tracked learner ids from the same four tables; retention rebuilds retained state
  under its existing lock and transaction before native completion synchronisation.
- Tracker reuses its existing state snapshot on rebuild and persists inside the delegated transaction;
  retention synchronises native completion after commit while still holding its state lock.
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
  canonical release archive must therefore contain the 376 legitimate plugin files only.

## Release checks

Before promotion, verify:

1. no `archive/` or `reports-*` directory, generated CI/server ZIP or corresponding link remains in the package;
2. no obsolete version narrative remains in current docs;
3. local Markdown links resolve and the EN/IT document sets are paired;
4. file/callable inventories match the exact tree;
5. XMLDB/ER counts and conditional-reference labels match `db/install.xml`;
6. release-hygiene, XMLDB relationship and uninstall/gradebook PHPUnit contracts plus the proportionate server gate pass;
7. GitHub workflow syntax, action versions, matrix selectors, permissions and links match the repository file.
