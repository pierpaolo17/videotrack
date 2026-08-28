# Compact technical structure

This page is a navigation map. Use the linked documents and source files for the full contract.

| Layer | Main paths | Responsibility |
|---|---|---|
| Moodle entrypoints | `view.php`, `index.php`, `report.php`, `course_report.php`, `teacher_report.php` | Access checks, context setup, rendering and report routing. |
| Activity lifecycle | `lib.php`, `mod_form.php`, `db/install.xml`, `db/upgrade.php` | Create/update/delete, form validation, files, grades and schema lifecycle. |
| Domain services | `classes/local/` | Tracking, analytics, privacy operations, exports, report scope, forum bridge and configuration. |
| Completion | `classes/completion/custom_completion.php`, `classes/local/completion_config.php` | Composite VideoTrack rule and Moodle completion synchronisation. |
| AJAX boundary | `db/services.php`, `classes/external/` | Nine authenticated write methods with parameter, context and capability validation. |
| Browser core | `amd/src/core/` | Shared state, API transport, session, tracking lifecycle, status and interaction controllers. |
| Provider adapters | `amd/src/html5_player.js`, `player.js`, `vimeo_player.js` | HTML5, YouTube and Vimeo playback contracts. |
| Generated assets | `amd/build/` | Moodle Grunt output; never edit manually. |
| Privacy | `classes/privacy/provider.php`, `classes/local/privacy_manager.php`, `db/tasks.php` | Metadata, export/deletion and scheduled retention. |
| Backup/restore | `backup/moodle2/` | Configuration, files, retained user evidence and identifier remapping. |
| Diagnostics | `cli/`, `classes/local/environment_validator.php` | Read-only installation validation and Analytics benchmark. |
| Tests | `tests/`, `tests/behat/`, `tests/generator/` | PHPUnit contracts and deterministic browser scenarios. |
| Documentation | `docs/en/`, `docs/it/`, `docs/VIDEOTRACK_DB_ER_SCHEMA.*` | Current-version user and developer reference.

## Data flow

1. `view.php` validates module/context/capabilities and emits localised JSON configuration.
2. The provider adapter and shared browser controller open a playback session through `start_playback`.
3. Lifecycle events submit idempotent segment requests through `save_segment`.
4. `classes/local/tracker.php` validates server credit and refreshes unique coverage.
5. Completion state, grade/report data and personal interactions are derived or queried within Moodle scope.
6. Privacy, retention, backup/restore and reset operate on the same declared data boundaries.

## Non-negotiable boundaries

- Browser time and provider state are requests, not authoritative watched evidence.
- `mod/videotrack:participate` is independent from report access.
- Aggregate and individual reports/exports use separate capabilities.
- Notes and bookmark labels are private content and excluded from aggregate Analytics.
- `amd/src` is canonical; `amd/build` is generated.
- HTML5, YouTube and Vimeo must be tested as separate adapters.

See [`02_ARCHITECTURE.md`](02_ARCHITECTURE.md), [`06_RUNTIME_FLOWS.md`](06_RUNTIME_FLOWS.md) and the
[database/ER reference](../VIDEOTRACK_DB_ER_SCHEMA.md).
