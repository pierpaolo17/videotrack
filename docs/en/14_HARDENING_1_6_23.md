# Hardening 1.6.23

VideoTrack 1.6.23 is a hardening-only release before the Moodle App phase.

## Trust boundary

- `durationseconds` is now explicitly teacher-configured and is the only authoritative duration for viewed percentage, percentage completion and end-of-video acknowledgement.
- Learner runtime metadata never becomes authoritative. From 1.6.27, the trusted teacher form may propose a value from YouTube, Vimeo or local-file metadata; the proposal becomes authoritative only after the teacher reviews and saves the activity and can be edited later. Provider or browser limitations may leave detection unavailable.
- The instance field still accepts zero: validated watched intervals are recorded, while watched percentage, percentage completion and end-of-video acknowledgement remain unavailable until the teacher saves a duration greater than zero.
- `save_segment` validates the reported playback rate against the effective activity/site speed list.
- Segment acceptance uses one cumulative server-time allowance persisted per user/activity. Client session-id rotation and request frequency cannot replenish grace; long idle gaps are capped.
- The server also enforces the no-forward-seek frontier when forward seeking is disabled.
- The state stores only bounded guard counters/timestamps (`serverlastactivity`, `serverbudgetseconds`, `servercreditedseconds`). They are declared in the Privacy API but intentionally excluded from backup payloads and reset to zero on restore, so unused playback credit cannot cross course copies.

## Learner scope

- Users with `mod/videotrack:viewreport` are not treated as learner telemetry subjects. Teacher previews therefore do not write learner tracking/interactions.
- Instance reports, sensitive actions and note exports use canonical active-enrolment and Moodle group visibility rules.

## Privacy contract

Bookmarks remain owner-only. Personal note text is visible to the owner and may be visible/exportable to authorised report viewers when notes are enabled; note text is excluded from aggregate Analytics.

## Moodle App

The previous incomplete `CoreCourseModuleDelegate` declaration was removed. Native App integration is intentionally deferred until the dedicated App implementation and runtime-validation phase.

## Upgrade note

The first 1.6.23 package attempted to reset aggregate progress, retain raw historical segments and recalculate Moodle completion through runtime APIs. That upgrade path is superseded by the 1.6.24 correction below and must not be treated as the current procedure.

## Upgrade correction in 1.6.24

Release 1.6.24 replaces the original completion-runtime recalculation with a resumable database-only cleanup. Because the plugin had not been used in production, pre-guard learner runtime rows and VideoTrack course-module completion rows are intentionally removed. Activity configuration, uploaded files and configured reaction definitions are preserved. The 1.6.23 schema block is now idempotent and the 1.6.24 cleanup can safely resume after a partially completed upgrade.
