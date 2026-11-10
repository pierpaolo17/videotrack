# Integrity and focus controls

## Controls

Per activity, a teacher may enable integrity recording, hidden/focus pause, best-effort Picture-in-Picture prevention and random attention pauses. Site settings define fallback bounds for unknown duration (default 300–1800 seconds), focus policy and grace period. Activity defaults and teacher overrides are described below. All controls are disabled by default at activity level.

## Accessible focus policy

The recommended policy pauses when `document.visibilityState` becomes hidden. A window blur starts a grace timer and may be recorded, but it does not pause unless the administrator enables strict mode. Returning focus or interacting with a provider iframe cancels the pending action. This avoids treating screen readers, password managers, browser controls and operating-system dialogs as automatic misconduct.

When strict mode is required, each course containing VideoTrack receives a hidden, non-participating core group whose stable idnumber is `mod_videotrack_focus_exception`. Membership changes only the effective strict blur policy to `hiddenonly`; it never permits hidden-tab playback or bypasses server validation, seek, rate, completion or interaction rules. VideoTrack stores no reason for membership and reads the Moodle core group relation directly because hidden membership is intentionally not exposed by normal visibility-aware helpers.

## Server-authoritative playback credit

Tracked playback starts only after `start_playback` opens a credit window bound to the current browser session. Segment writes from another or stale session remain available as non-authoritative audit evidence but cannot advance watched coverage. An accepted pause, end or hidden-page lifecycle save clears the active session and server activity timestamp, so resumed playback must perform a new handshake. The deterministic HTML5 browser contract matches `playstart` and accepted `pause` session identifiers and verifies that the server window is closed.

## Signals

Allowed bounded types include blocked forward seek, hidden tab, window blur, player outside viewport, Picture-in-Picture attempt, random pause, unauthorised rate, missing provider callback and inconsistent tracking. The server validates the type, context, enabled state and rate limit. No free text or device capture is accepted.

## Interpretation

Signals are visibility/integrity diagnostics, not direct attention measurements. Provider/browser limitations and legitimate accessibility causes can produce missing or false-positive signals. Reports and Analytics therefore present counts and privacy-safe aggregates; they must not automatically alter grades, completion or discipline.

## Report presentation states

The controller selects one explicit presentation contract before rendering the integrity section:

- `report_integrity_view::integrity_summary()` renders privacy-safe counts when recording is enabled;
- `report_integrity_view::integrity_disabled_summary()` explains that both recording and focus controls are disabled;
- `report_integrity_view::integrity_controls_without_recording_summary()` warns that focus controls are active but no
  integrity signals are being recorded.

These entry points change presentation only. They do not enable recording, infer misconduct or bypass the
distinct-user privacy threshold. Shared private helpers keep the heading, explanatory text and accessible section
landmark identical in every state.

## Lifecycle

`videotrack_integrity` is included in Privacy API export/erasure, retention, reset, activity deletion and user-data backup/restore.

## Teacher attention intervals

The existing **Enable random attention pauses** switch remains off by default. The activity reuses the site
minimum/maximum labels, but its two optional fields are **whole seconds**, not minutes. Empty or `0` means
automatic. Explicit bounds must be within **60–7200 seconds**; two explicit bounds must satisfy minimum ≤ maximum.
Equal values give a fixed interval. The settings stay saved when the switch is off.

Automatic values are one third and two thirds of the saved `durationseconds`, rounded to the nearest whole
second and clamped to 60–7200. With duration `0` or an invalid imported duration, the canonical site interval is
the fallback. A 900-second activity therefore defaults to 300–600 seconds; a 30-second activity uses 60–60 and
may finish before a pause occurs. The duration used for watched percentage is never changed to tune this feature.

Each override is independent. If only the minimum is explicit and exceeds the automatic maximum, that maximum
is raised to the explicit minimum. If only the maximum is explicit and is below the automatic minimum, that
minimum is lowered to the explicit maximum. Editing duration recalculates only automatic values: the database
stores `0`, not a snapshot of the calculated interval. A teacher may return either field to automatic.

These are **wall-clock delays since a recognised interaction while playing**, not 1/3 and 2/3 timeline markers.
The unchanged shared `focus_guard` restarts its timer on recognised actions and cancels it on pause/end/destroy.
Progress callbacks alone do not reset it. Recording integrity signals is independent of enabling the pause.
The provider pause must close server credit normally; resuming must obtain a fresh server handshake. No time
is awarded for the pause, and seek/rate guards, completion and hidden-tab controls retain their own contracts.
Pauses are diagnostic prompts and do not prove attention or misconduct.

## Recording and report visibility

The focus guard records a signal only when indicator recording is enabled. Playback controls can still
operate without recording. A sustained window blur pauses only when the activity enables focus pausing
and the site chooses strict mode. Report visibility/masking follows actual capabilities and learner/group
scope: aggregate-only activity viewers retain the privacy threshold; authorised individual-report viewers
may see exact scoped values. Diagnostic signals remain contextual and are not proof of misconduct.
