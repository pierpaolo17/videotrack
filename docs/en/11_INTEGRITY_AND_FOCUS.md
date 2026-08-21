# Integrity and focus controls

## Controls

Per activity, a teacher may enable integrity recording, hidden/focus pause, best-effort Picture-in-Picture prevention and random attention pauses. Site settings define random-pause bounds (default 300–1800 seconds), focus policy and grace period. All controls are disabled by default at activity level.

## Accessible focus policy

The recommended policy pauses when `document.visibilityState` becomes hidden. A window blur starts a grace timer and may be recorded, but it does not pause unless the administrator enables strict mode. Returning focus or interacting with a provider iframe cancels the pending action. This avoids treating screen readers, password managers, browser controls and operating-system dialogs as automatic misconduct.

When strict mode is required, each course containing VideoTrack receives a hidden, non-participating core group whose stable idnumber is `mod_videotrack_focus_exception`. Membership changes only the effective strict blur policy to `hiddenonly`; it never permits hidden-tab playback or bypasses server validation, seek, rate, completion or interaction rules. VideoTrack stores no reason for membership and reads the Moodle core group relation directly because hidden membership is intentionally not exposed by normal visibility-aware helpers.

## Signals

Allowed bounded types include blocked forward seek, hidden tab, window blur, player outside viewport, Picture-in-Picture attempt, random pause, unauthorised rate, missing provider callback and inconsistent tracking. The server validates the type, context, enabled state and rate limit. No free text or device capture is accepted.

## Interpretation

Signals are visibility/integrity diagnostics, not direct attention measurements. Provider/browser limitations and legitimate accessibility causes can produce missing or false-positive signals. Reports and Analytics therefore present counts and privacy-safe aggregates; they must not automatically alter grades, completion or discipline.

## Lifecycle

`videotrack_integrity` is included in Privacy API export/erasure, retention, reset, activity deletion and user-data backup/restore.
