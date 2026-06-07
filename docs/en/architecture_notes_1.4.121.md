# Videotrack 1.4.121 - Complexity documentation

## Scope

This document records the architectural rationale for the remaining dense areas of the plugin after the AMD refactor series. It is intentionally documentation-only and does not change runtime behaviour.

The goal of this release is to support strict Moodle review by explaining why some orchestration code remains concentrated in a small number of files instead of being split further.

## Design principle

Videotrack prioritises stable educational behaviour over architectural elegance. Refactoring is only accepted when it does not alter tracking semantics, resume behaviour, note handling, reactions, progress persistence, or accessibility feedback.

## Remaining orchestration areas

### Adapter layer

The adapter layer sits between Moodle page data, player state, AJAX transport, and UI updates. Some density remains because the adapter must keep the order of operations explicit:

1. read Moodle-provided configuration;
2. initialise the player and tracker modules;
3. connect UI controls and event handlers;
4. preserve existing resume and progress semantics;
5. expose failures through the established debug and status channels.

Splitting this sequence further would make the ordering less visible and would increase the risk of subtle regressions in tracking and resume behaviour.

### API layer

The API layer has already been decomposed into validator, error, retry, transport, and scope helpers. The remaining entry points intentionally keep the call flow close to Moodle external-service usage:

1. validate request payloads;
2. attach scope information;
3. execute the transport call;
4. normalise transient and permanent failures;
5. return predictable results to tracker and player modules.

The central entry point remains useful because it provides a single reviewable boundary between UI code and Moodle web service calls.

## Flow notes

### Save progress

Progress persistence should remain conservative. The tracker prepares validated timing data, the API layer applies request validation and transport rules, and the player receives only the resulting status. This prevents UI modules from depending on transport-specific details.

### Notes and reactions

Notes and reactions are educational interactions. Their modules are separated from the player core, but they still depend on the same player lifecycle and permission context. The current structure avoids duplicating permission and state checks in multiple modules.

### Debug messages

Debug and warning output should go through the central debug helper so that messages can be localised and reviewed consistently. Direct console strings should not be introduced in future changes.

## Reviewer rationale

The current structure is a deliberate compromise:

- small helper modules contain reusable logic;
- orchestration modules keep user-visible behaviour explicit;
- no additional abstraction is introduced unless it reduces risk;
- documentation is preferred over invasive refactor when the existing behaviour is stable.

## Validation impact

This document does not require AMD rebuilds because no file under `amd/src` is modified.

Expected validation for this release:

- versioning check;
- `git apply --check` from the plugin root;
- PHP lint;
- XML parse;
- JavaScript syntax check;
- source map JSON validation.
