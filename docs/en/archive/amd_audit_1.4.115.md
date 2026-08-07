# Videotrack AMD Audit - 1.4.115

## Scope

Final AMD coherence audit after the API, tracker and player micro-refactors.

## Findings

The 1.4.114 baseline referenced `mod_videotrack/core/player/notes/row` from `amd/src/core/player/notes.js`, but the corresponding source and build artifacts were missing from the ZIP.

## Fix

Restored:

- `amd/src/core/player/notes/row.js`
- `amd/build/core/player/notes/row.min.js`
- `amd/build/core/player/notes/row.min.js.map`

## Verification

The AMD dependency audit checks that every internal `mod_videotrack/*` dependency declared by a source module maps to an existing `amd/src` file.

The Moodle AMD build was executed with the validated Moodle 5.0 environment.

## Functional impact

None. This release only restores a missing AMD module and records the final AMD coherence audit.
