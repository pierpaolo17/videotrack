# Hardening 1.6.23

VideoTrack 1.6.23 is a hardening-only release before the Moodle App phase.

## Trust boundary

- `durationseconds` is now explicitly teacher-configured and is the only authoritative duration for viewed percentage, percentage completion and end-of-video acknowledgement.
- Client-reported duration never becomes authoritative. If verified duration is zero, watched intervals can still be recorded but percentage completion remains unavailable and end-of-video acknowledgement cannot be configured.
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

When upgrading from pre-1.6.23 releases, aggregate viewed progress is reset for all existing learner states because historical segments were not protected by the server-authoritative guard. Raw historical segment rows are retained for audit/privacy purposes and remain explicitly unvalidated. Moodle automatic completion is then recalculated through the core completion API, preserving still-valid reaction/acknowledgement conditions and manual overrides. Configure the real teacher-verified duration before percentage completion or end-gated acknowledgement can become authoritative again.
