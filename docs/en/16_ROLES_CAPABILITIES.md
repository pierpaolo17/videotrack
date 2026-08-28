# Roles, capabilities and participation

VideoTrack never infers learner status from a role name or report permission. Moodle capabilities in the relevant
context are authoritative, and one user may legitimately hold learner and staff permissions at the same time.

## Capability map

| Capability | Context | Purpose |
|---|---|---|
| `mod/videotrack:addinstance` | Course | Add the activity. |
| `mod/videotrack:view` | Module | Open the activity. |
| `mod/videotrack:participate` | Module | Generate learner tracking and personal-study data. |
| `mod/videotrack:viewownreport` | Module | View own progress/interactions. |
| `mod/videotrack:viewreport` | Module | Legacy/general report entry permission. |
| `mod/videotrack:viewaggregatereport` | Module | View privacy-thresholded aggregates without learner filter. |
| `mod/videotrack:viewindividualreport` | Module | View learner-level and exact in-scope data. |
| `mod/videotrack:exportaggregatereport` | Module | Export aggregate report data. |
| `mod/videotrack:exportindividualreport` | Module | Export learner-level personal data. |
| `mod/videotrack:viewcoursereport` | Course | Open the course VideoTrack dashboard. |
| `mod/videotrack:managereactions` | Module | Manage reaction definitions. |
| `mod/videotrack:grade` | Module | Manage VideoTrack grades. |
| `mod/videotrack:overrideplayersettings` | Course | Override site-enforced player defaults. |
| `mod/videotrack:overridecompletionsettings` | Course | Override site-enforced VideoTrack completion defaults. |

## Participation contract

Users with `participate` are in learner runtime: playback sessions, progress, reactions, notes, bookmarks,
acknowledgement and related events may be written when the feature is enabled. Users without it see a non-tracking
preview even if they can view reports. A dual-role user with both participation and reporting remains a tracked
learner while also receiving authorised report controls.

Course reports use Moodle enrolment, group and cohort scope in addition to capabilities. Hidden/deleted users and
role changes must be handled through Moodle APIs, not hard-coded archetype assumptions.

## Focus exception group

`mod_videotrack_focus_exception` is a hidden, non-participating course group used only when the site focus policy is
strict. Membership changes visible-window blur handling from strict to hidden-only. It grants no capability and
does not bypass hidden-tab, playback-credit, completion or report rules.

## Administration guidance

- grant personal-data report/export capabilities only to roles that need them;
- do not grant `participate` to preview-only staff unless tracking them is intentional;
- revoke override capabilities when player/completion policy must be centrally enforced;
- test custom and dual roles explicitly after permission changes;
- review group/cohort scope before interpreting or exporting report data.
