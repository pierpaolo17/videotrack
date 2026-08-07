# Videotrack 1.4.122 - WCAG edge-case audit

## Scope

This document records the manual accessibility review required after the strict review findings on focus restore, confirmation fallbacks, and keyboard navigation edge cases.

The release is intentionally documentation-only. It does not change runtime behaviour, tracking semantics, player lifecycle, notes, reactions, progress persistence, or Moodle data handling.

## Review objective

The objective is to document the accessibility assumptions that must be preserved during future maintenance and to make the remaining edge-case behaviour explicit for Moodle review.

## Areas reviewed

### Keyboard navigation

Interactive controls must remain reachable using the keyboard in a predictable order. Controls added around the player, notes, reactions, progress UI, and status messages must not create keyboard traps.

Expected behaviour:

1. focus enters the Videotrack activity using the normal page tab order;
2. player controls remain reachable without pointer input;
3. notes and reaction controls remain reachable only when they are visible and relevant;
4. closing a secondary UI returns the learner to a stable point in the activity flow;
5. disabled or unavailable actions are communicated without requiring pointer-only interaction.

### Focus restore

Focus restoration should prefer the control that opened the secondary UI. If that element is no longer available, focus should fall back to the nearest stable activity container or to the normal document flow.

The implementation must avoid forcing focus to hidden, disabled, detached, or provider-owned media elements.

### Confirmation fallback

Browser confirmation fallbacks are acceptable only for conservative flows where the alternative would be data loss or ambiguous destructive behaviour. Any future replacement with a custom modal must preserve:

- keyboard access;
- visible focus;
- screen-reader announcement;
- clear confirm and cancel actions;
- no change to the underlying educational action.

### Status messaging

Non-blocking status messages should continue to use the established status channel so that learners receive feedback without unexpected focus movement.

## Risk assessment

No blocking WCAG issue is documented in this release. The remaining strict-review concern is treated as an edge-case audit item rather than a runtime defect.

The safest remediation is to document the intended behaviour and avoid preventive code changes until a reproducible accessibility failure is identified.

## Maintenance rules

Future changes touching player UI, notes, reactions, modal-like flows, or status messages must preserve:

- keyboard reachability;
- visible focus;
- no keyboard traps;
- stable focus restoration;
- non-disruptive status announcements.

## Validation impact

This release does not modify files under `amd/src`, so an AMD rebuild is not required.

Required validation for this documentation-only patch:

- audit of the real baseline ZIP;
- `git apply --check` from the plugin root;
- PHP lint;
- XML parse;
- JavaScript syntax check;
- source map JSON validation;
- versioning check.

`grunt amd` is not required because no AMD source file is modified.
