# Explicit learner participation scope — 1.6.29

## Problem corrected

Earlier releases treated the absence of `mod/videotrack:viewreport` as proof that a user was a learner. That heuristic disabled reactions, notes, bookmarks and tracking for custom or dual-role users who legitimately had both learner and report permissions. Because the same condition was enforced in `view.php` and the AJAX context helper, all personal controls appeared grey and every write was rejected.

## Canonical permission

VideoTrack 1.6.29 introduces `mod/videotrack:participate` as the canonical permission for learner telemetry and personal study tools. It is a module-context personal-data write capability. The standard Student archetype receives it by default and its initial permissions are cloned from Moodle’s `moodle/course:isincompletionreports` capability.

The permission controls:

- watched-segment tracking and completion data;
- reactions, notes and bookmarks;
- integrity indicators and acknowledgement confirmation;
- personal bookmark export;
- the learner population used by instance, course and teacher Analytics.

`mod/videotrack:viewreport` remains solely a report-viewing permission. A user may possess both permissions; such a user can participate and is included in learner scope because participation is explicit rather than inferred.

## Staff and administrator previews

Teachers and managers do not receive `mod/videotrack:participate` from their standard archetypes. Site-administrator do-anything privileges are deliberately ignored when deciding whether the current page may generate learner telemetry. Administrators who need a realistic test must switch to a participating role or use a test learner account.

## Custom roles and upgrade

The Moodle plugin upgrade synchronises the new capability. Sites with custom learner roles should verify that those roles receive `mod/videotrack:participate`; role administrators can grant or revoke it explicitly. This is preferable to inferring learner status from role names or unrelated report capabilities.

## Regression contract

The same capability must be used by server rendering, mutation Web Services, learner SQL scopes and exports. A future change must not reintroduce a separate “not a report viewer” gate because that would recreate the all-controls-disabled regression.
