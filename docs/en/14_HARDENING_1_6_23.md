# Hardening 1.6.23

VideoTrack 1.6.23 is a hardening-only release before the Moodle App phase.

## Trust boundary

- `durationseconds` is now explicitly teacher-configured and is the only authoritative duration for viewed percentage, percentage completion and end-of-video acknowledgement.
- Learner runtime metadata never becomes authoritative. From 1.6.27, the trusted teacher form may propose a value from YouTube, Vimeo or local-file metadata; the proposal becomes authoritative only after the teacher reviews and saves the activity and can be edited later. Provider or browser limitations may leave detection unavailable.
- The instance field still accepts zero: validated watched intervals are recorded, while watched percentage, percentage completion and end-of-video acknowledgement remain unavailable until the teacher saves a duration greater than zero.
- `save_segment` validates the reported playback rate against the effective activity/site speed list.
- Segment acceptance uses one cumulative server-time allowance persisted per user/activity. Client session-id rotation and request frequency cannot replenish grace; long idle gaps are capped.
- The server also enforces the no-forward-seek frontier when forward seeking is disabled.
- The state stores only bounded guard/session values (`serverlastactivity`, `serverplaybacksessionid`, `serverbudgetseconds`, `servercreditedseconds`). They are declared in the Privacy API but intentionally excluded from backup payloads and reset to zero on restore, so unused playback credit cannot cross course copies.

## Learner scope

- In 1.6.23 users with `mod/videotrack:viewreport` were excluded from learner telemetry. This heuristic was superseded in 1.6.29 by the explicit `mod/videotrack:participate` capability, which supports custom and dual-role learners while ordinary staff previews remain non-tracking.
- Instance reports, sensitive actions and note exports use canonical active-enrolment and Moodle group visibility rules.

## Privacy contract

Bookmarks remain owner-only. Personal note text is visible to the owner and may be visible/exportable to authorised report viewers when notes are enabled; note text is excluded from aggregate Analytics.

## Moodle App

The previous incomplete `CoreCourseModuleDelegate` declaration was removed. Native App integration is intentionally deferred until the dedicated App implementation and runtime-validation phase.

## Upgrade note

The first 1.6.23 package attempted to reset aggregate progress, retain raw historical segments and recalculate Moodle completion through runtime APIs. That upgrade path is superseded by the 1.6.24 correction below and must not be treated as the current procedure.

## Upgrade correction in 1.6.24

Release 1.6.24 replaces the original completion-runtime recalculation with a resumable database-only cleanup. Because the plugin had not been used in production, pre-guard learner runtime rows and VideoTrack course-module completion rows are intentionally removed. Activity configuration, uploaded files and configured reaction definitions are preserved. The 1.6.23 schema block is now idempotent and the 1.6.24 cleanup can safely resume after a partially completed upgrade.

## 1.7.101 playback-session binding and browser visibility semantics

The server-authoritative credit window is now bound to the browser `sessionid` that opened it with `start_playback`. A segment from another tab/session is retained with `servervalidated=0` but cannot consume, reset or steal the active session budget. Accepted terminal/lifecycle closes (`pause`, `ended`, `beforeunload`, `pagehide`, `tab`, `visibilitychange`) clear the authorised session and require a fresh handshake before more credit can be earned.

Browser focus is intentionally **not** a server completion condition. Page Visibility and keyboard/window focus express different facts:

- a background tab, minimised window or locked/suspended page becomes hidden; the tracker closes the open segment and stops earning credit;
- grouped tabs behave like ordinary tabs: only content the browser reports visible can continue tracking;
- side-by-side/split-view pages can remain visible while only one pane/window owns keyboard focus, so a plain `window.blur` is not evidence that the learner cannot see the video;
- the default site policy therefore remains `hiddenonly`; sustained visible-window blur is diagnostic, while the optional `strict` policy may pause after its grace period;
- Picture-in-Picture prevention is best-effort. If the source document becomes hidden, VideoTrack stops tracking even if the browser keeps media visible elsewhere.

For sites that deliberately enable `strict`, VideoTrack creates the hidden, non-participating course group `mod_videotrack_focus_exception`. A member receives the effective `hiddenonly` policy so visible split-view and assistive-tool workflows are not paused by window blur alone. The exception does not affect hidden-document handling or any server-authoritative playback control, and no accommodation reason is stored by VideoTrack.

This design avoids treating legitimate split-screen, accessibility or multi-window workflows as cheating while still preventing background/hidden playback and cross-tab credit sharing from becoming authoritative completion evidence.

## 1.7.102 interaction timestamp authority

Forward-seek permission allows a learner to navigate to a later point; it does not by itself prove that an interaction belongs to that point. Reaction, note, bookmark and Forum clients first flush the current segment and prefer the saved endpoint returned by `save_segment`. The server therefore accepts the interaction only when the requested timestamp is covered by server-validated watched evidence under the configured session policy.

A recent `playstart` row grants no watched time and can no longer authorise an unrelated timestamp. This closes AC-F02 without changing seek controls, playback credit, completion, AMD assets or the behaviour of interactions at already validated positions.
