# Documentation audit

Documented baseline: Videotrack 1.4.249, generated from the real 1.4.248 plugin ZIP and updated only in documentation/version metadata.

## Purpose

This document records how the documentation set was checked against the actual plugin source. It is intended to help future maintainers verify that the numbered documentation remains aligned with the codebase before making functional changes.

## Audit scope

The audit covered:

- the root README;
- the English and Italian documentation sets under `docs/en` and `docs/it`;
- every non-documentation file shipped by the plugin;
- PHP classes, functions and methods;
- AMD source modules and generated AMD build artifacts;
- statically detected PHP and JavaScript variables;
- runtime flows for tracking, notes, reactions, replay, completion and reports;
- known maintenance rules learned during the 1.4.x development cycle.

## Current documentation map

The active source-of-truth documentation is the numbered set:

| File | Role |
| --- | --- |
| `00_INDEX.md` | Entry point and navigation. |
| `01_DEVELOPER_GUIDE.md` | Workflow, patch discipline and validation checklist. |
| `02_ARCHITECTURE.md` | High-level architecture and component responsibilities. |
| `03_FILE_INVENTORY.md` | Complete file inventory for the plugin. |
| `04_FUNCTION_INVENTORY.md` | PHP and AMD function inventory. |
| `05_VARIABLE_INVENTORY.md` | Statically detected PHP and AMD variables. |
| `06_RUNTIME_FLOWS.md` | Tracking, reactions, notes, replay and player flows. |
| `07_BUILD_TEST_RELEASE.md` | Build, test and release commands. |
| `08_LESSONS_LEARNED.md` | Maintainer rules learned during recent development. |
| `09_DOCUMENTATION_AUDIT.md` | This audit file and future documentation checklist. |
| `10_BOOKMARKS_AND_ANALYTICS.md` | Complete bookmark model, runtime, privacy and reporting contract. |

Historical audit files are kept in the same directories for traceability, but the numbered set is the current maintainer guide.

## Verification result

### File coverage

`docs/en/03_FILE_INVENTORY.md` and `docs/it/03_FILE_INVENTORY.md` cover all non-documentation files present in the plugin package.

The inventory includes:

- Moodle entry points (`view.php`, `lib.php`, `locallib.php`, reports, presets, settings);
- form and settings code;
- backup and restore classes;
- external AJAX service classes;
- event classes;
- privacy and cleanup classes;
- database definitions and upgrade files;
- language packs;
- AMD source files;
- generated AMD build files and sourcemaps;
- tests and support files.

### PHP function coverage

`docs/en/04_FUNCTION_INVENTORY.md` and `docs/it/04_FUNCTION_INVENTORY.md` include the PHP class/function inventory detected from the shipped source.

The inventory includes:

- Moodle module callbacks;
- form methods;
- external API methods;
- tracker and privacy helper methods;
- event methods;
- backup/restore methods;
- PHPUnit tests.

### AMD function coverage

The AMD section of `04_FUNCTION_INVENTORY.md` lists the detected functions and local helpers by source module. This is especially important for:

- `amd/src/html5_player.js`;
- `amd/src/player.js`;
- `amd/src/vimeo_player.js`;
- `amd/src/core/*` shared modules.

Maintainers should treat AMD source files as the editable source of truth and `amd/build/*.min.js` plus `.map` files as generated artifacts, unless a patch intentionally corrects a distributed build artifact.

### Variable coverage

`docs/en/05_VARIABLE_INVENTORY.md` and `docs/it/05_VARIABLE_INVENTORY.md` include statically detected PHP and AMD variables. The variable inventory is a navigation aid, not a behavioural contract.

Use it to find likely state variables quickly, then verify behaviour in the runtime flow and source code.

## Documentation quality notes

The documentation is now sufficiently detailed for a new maintainer to locate the main responsibilities of each file, follow the main runtime flows and understand the validation workflow.

However, three rules remain important:

1. The inventories are generated from static analysis and must be refreshed whenever files/functions/variables change.
2. Runtime behaviour, especially AMD player behaviour, must be validated manually in the browser.
3. Web service contracts must be checked against both `execute_returns()` and actual JSON responses.

## Required process for future documentation updates

Whenever a future patch changes PHP, JavaScript, DB structures or runtime behaviour:

1. update the relevant numbered documentation file in both English and Italian;
2. update file/function/variable inventories if new symbols were added or removed;
3. update runtime flow documentation if behaviour changes;
4. update lessons learned if the change reveals a new maintenance rule;
5. keep historical files intact unless they are explicitly superseded;
6. bump plugin version only if the documentation patch is intended to be installed as a new plugin release.

## Quick maintainer checklist

Before releasing a documentation patch, verify:

```text
- README describes the current plugin scope.
- docs/en and docs/it are both updated.
- 03_FILE_INVENTORY.md contains every shipped non-doc file.
- 04_FUNCTION_INVENTORY.md contains new/removed PHP and AMD functions.
- 05_VARIABLE_INVENTORY.md contains important new/removed variables.
- 06_RUNTIME_FLOWS.md reflects changed runtime behaviour.
- 07_BUILD_TEST_RELEASE.md reflects current commands.
- 08_LESSONS_LEARNED.md includes any newly discovered rule.
- git apply --check passes from the plugin root.
- patch -p1 --dry-run passes from the plugin root.
```

## 1.6.16 audit result

The bookmark implementation is now covered by the architecture, file, function, variable and runtime inventories and by the dedicated document `10_BOOKMARKS_AND_ANALYTICS.md`. Language-key parity is complete across all eight packs; recent bookmark and teacher-dashboard strings no longer fall back to English in the six non-English packs that previously contained placeholders.

## 1.6.19 audit result

The optional acknowledgement is covered by architecture, file/function/variable inventories, runtime flow, release regression checklist and the dedicated `12_ACKNOWLEDGEMENT.md`. The documentation records the current-hash rule, explicit POST confirmation, completion integration, teacher output, Privacy API, retention and backup/restore boundaries.
