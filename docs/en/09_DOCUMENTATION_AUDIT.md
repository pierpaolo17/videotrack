# Documentation audit

Baseline: VideoTrack **1.7.118** (`2026082802`).

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
- accessibility contract, troubleshooting and build/release gates;
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
- Documentation markers, root README files and ER artefacts identify 1.7.118 / 2026082802.
- The activity identity includes the full-colour `pix/icon.png` and a transparent `pix/icon.svg` for course formats.
- `db/install.xml` currently declares seven primary keys and indexes but no foreign-key metadata. The ER documents
  label those references accurately and do not claim physical constraints. This is recorded for a separate
  data/schema corrective cycle because installed sites require orphan checks and an explicit XMLDB upgrade.
- No runtime, AJAX, privacy, completion, gradebook or database behaviour is changed by this documentation/branding
  release.

## Release checks

Before promotion, verify:

1. no `archive/` directory or link remains in the package;
2. no obsolete version narrative remains in current docs;
3. local Markdown links resolve and the EN/IT document sets are paired;
4. file/callable inventories match the exact tree;
5. PNG icon validity and package path are correct;
6. release-hygiene PHPUnit contracts and the proportionate server gate pass.
