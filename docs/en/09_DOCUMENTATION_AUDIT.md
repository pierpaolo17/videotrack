# Documentation audit

Baseline: VideoTrack **1.7.149** (`2026092202`).

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
- Documentation markers, root README files and ER artefacts identify 1.7.149 / 2026092202.
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
- Reaction reads have explicit scopes: the standard helper returns active definitions, while lifecycle code can
  request active and soft-deleted definitions without a boolean behaviour flag. Activity records are filtered to
  real table columns through one request-local metadata cache before insert or update.
- Timed-text reads have explicit canonical and compatibility contracts. Dedicated transcript/chapter files retain
  precedence, and the historical subtitle area is used only by named fallback methods selected for migrated
  uploaded-video activities.
- The GitHub Actions guide documents triggers, least-privilege permissions, the six-job Moodle/PHP/database matrix,
  deterministic classic/`public/` ordinary-site bootstrap, rejection of generated `.types` mirrors, the
  authoritative `moodle-plugin-ci grunt` comparison, strict/advisory checks, retained logs and faildumps.
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
  date-boundary, Analytics export-shape and course-percentage rendering APIs; `report_support` contains 25 methods,
  and reaction icons always include their visible label without a boolean presentation flag. Exact finding counts
  are read from the current candidate's CI artifact.
- The two AMD build pairs corrected in 1.7.123 are canonical Moodle Grunt output; their source maps have non-empty
  mappings and embed source content byte-identical to the distributed AMD sources.
- Rendered accessibility helpers use Moodle 5 / Bootstrap 5 `visually-hidden` classes; player live regions are
  present in the initial markup so normal activity views do not use the deprecated Bootstrap 4 `sr-only` fallback.
- `.github/workflows/ci.yml` and its pinned analyser-tool manifest are present in the repository but excluded from
  Moodle release archives. The distributed configs and bootstrap remain available to maintainers and server gates.

## Release checks

Before promotion, verify:

1. no `archive/` directory or link remains in the package;
2. no obsolete version narrative remains in current docs;
3. local Markdown links resolve and the EN/IT document sets are paired;
4. file/callable inventories match the exact tree;
5. XMLDB/ER counts and conditional-reference labels match `db/install.xml`;
6. release-hygiene, XMLDB relationship and uninstall/gradebook PHPUnit contracts plus the proportionate server gate pass;
7. GitHub workflow syntax, action versions, matrix selectors, permissions and links match the repository file.
