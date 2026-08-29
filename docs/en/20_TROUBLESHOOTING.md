# Troubleshooting

Start with browser Console/Network, Moodle developer debugging and the read-only validator. Always confirm the
installed code/version before investigating cached behaviour.

| Symptom | Checks | Likely boundary |
|---|---|---|
| Activity record not found | Verify URL `id`, `course_modules` row, module name and instance; navigate from course page. | Stale URL or deleted/recreated activity. |
| AMD change not visible | Confirm deployed `amd/src` and `amd/build`, purge Moodle caches, hard reload, inspect page `require()` bootstrap. | Stale code/cache or missing generated build. |
| YouTube duration not proposed | Validate URL/id, public embeddability, network/consent, console and timeout; enter duration manually. | Provider metadata readiness or access. |
| Vimeo duration not proposed | Validate numeric id/URL, SDK request, privacy/embedding policy and promise rejection. | Provider access or loader. |
| Percentage stays zero | Confirm positive saved duration, `participate`, `start_playback`, accepted segments and scheduled cleanup. | Configuration or server credit. |
| Forward seek counted | Inspect latest `videotrack_seg`, `servervalidated`, interval JSON and trusted frontier. | Tracker/provider regression. |
| Resume wrong | Compare `videotrack_state.lastposition`, duration, retained validated intervals and direct replay parameter. | Derived state or provider readiness. |
| Completion label/layout wrong | Confirm current `completion_requirements` AMD is loaded and grouping marker is `1`; inspect Moodle completion region. | Deployment/cache or cross-version markup. |
| Activity icon is blank or monochrome | Confirm both `pix/icon.png` and `pix/icon.svg`, purge caches, then inspect theme rules applying `filter` to `.activityicon`; keep theme-specific colour overrides in the theme/custom CSS. | Theme presentation, not plugin image data. |
| Reaction/note/bookmark rejected | Check capability, ownership, trusted timestamp, duplicate/burst limit and AJAX JSON response. | Scope or interaction validation. |
| Forum action unavailable | Check same-course Forum, availability, groups, permissions and saved `linkedforumid`. | Moodle Forum integration. |
| Report values masked | Check aggregate vs individual capability and `analyticsminusers`; verify learner/group scope. | Intentional privacy policy. |
| Backup omits learner data | Confirm backup `userinfo`, retention cutoff and valid user mappings. | Backup policy/retention. |
| Plugin uninstall stops while deleting grades | Stop retries, back up the database and measure `modules`, `course_modules`, `videotrack`, `grade_items`, `grade_grades` and `config_plugins`; do not reinstall over partial state. | Interrupted core lifecycle or pre-`2026082901` code without early grade cleanup. |
| Retention does not run | Check scheduled tasks, setting/confirmation, task log, locks and error log. | Cron/task configuration. |
| Focus pauses unexpectedly | Check site `focuslosspolicy`, activity flag, document visibility and exception-group membership. | Focus policy or membership. |

## Diagnostic commands

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/validate.php --strict
php admin/cli/purge_caches.php
php admin/cli/scheduled_task.php --execute='\mod_videotrack\task\cleanup_retention'
```

Run destructive/privacy tasks only in the intended environment and after confirming scope. For code defects,
capture the exact release, Moodle branch, source/provider, steps, Console, Network response and relevant database
rows with personal data redacted.
