# Documentation audit

Baseline: VideoTrack **1.6.24** (`2026060439`).

## Coverage

- Non-documentation files inventoried: **234/234**.
- Named PHP functions/methods inventoried: **469**.
- Named AMD callables detected and inventoried: **572**.
- XMLDB tables documented: **7**.
- Site-setting keys documented: **57**.
- Player configuration keys documented: **128**.
- AJAX services documented: **8**.
- Language packs: eight packs with the same 952-key contract at consolidation start; translated copied-English operational strings were replaced while technical/proper terms may legitimately remain identical.
- Root overviews: `README.md` (English) and `README_IT.md` (Italian).
- Root privacy summaries: `PRIVACY.md` and `PRIVACY_IT.md`.

## Freshness rules

Current documents must not include release-specific assertions without a version/status label. Historical 1.4.x audits are isolated in `archive/`. Any change to schema, services, File API, player configuration, reports, privacy, accessibility or translations requires updating the corresponding numbered documents.

## Automated audit expectations

A release audit must compare file inventory to the tree, function inventory to source, language key sets/placeholders, static `get_string` references, XMLDB to backup/restore, services to executable classes, AMD sources to generated assets and Markdown links to existing files.
