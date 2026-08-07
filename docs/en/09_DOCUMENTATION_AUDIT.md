# Documentation audit

Baseline: VideoTrack **1.6.26** (`2026060441`).

## Coverage

- Non-documentation files inventoried: **234/234**.
- Named PHP functions/methods inventoried: **470**.
- Named AMD callables detected and inventoried: **573**.
- XMLDB tables documented: **7**.
- Site-setting keys documented: **57**.
- Player configuration keys documented: **128**.
- AJAX services documented: **8**.
- Language packs: eight packs with the same **954-key** contract; operational strings are translated while technical and proper terms may legitimately remain identical.
- Root overviews: `README.md` (English) and `README_IT.md` (Italian).
- Root privacy summaries: `PRIVACY.md` and `PRIVACY_IT.md`.

## Freshness rules

Current documents must not include release-specific assertions without a version/status label. Historical 1.4.x audits are isolated in `archive/`. Any change to schema, services, File API, player configuration, reports, privacy, accessibility or translations requires updating the corresponding numbered documents.

## Automated audit expectations

A release audit must compare file inventory to the tree, function inventory to source, language key sets/placeholders, static `get_string` references, XMLDB to backup/restore, services to executable classes, AMD sources to generated assets and Markdown links to existing files.

## 1.6.26 player-tool reliability coverage

- A verified duration of `0` explicitly disables watched-percentage calculation and percentage-based completion while preserving validated interval tracking and enabled study tools.
- Provider/browser duration metadata is not promoted automatically to the authoritative anti-tampering value; the teacher may enter a reviewed duration when percentage or end-gated acknowledgement is required.
- The complete HTML5-controls fieldset is hidden for YouTube and Vimeo and restored when the source changes to a local upload.
- Learner reaction controls remain available while playing or paused, but the server accepts only already-watched validated timestamps.
- Enabled notes and bookmarks are rendered for learners; report-capable staff receive a disabled preview and cannot create learner telemetry.
- The PHPCS findings reported against 1.6.25 in `view.php`, `report.php` and `tests/tracker_test.php` are corrected in the 1.6.26 source tree.

## 1.6.25 interface regression coverage

- The acknowledgement Analytics renderer initialises both count and progress suppression state.
- Reaction definitions remain inside the main Reactions form section and are not modelled as a fixed two-item set.
- Activity-view ordering is reactions, optional Forum action, then personal reaction history/bookmarks.
- Report-capable preview and learner persistence remain separate trust states.
