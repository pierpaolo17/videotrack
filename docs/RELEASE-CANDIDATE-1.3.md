# Videotrack 1.3 Release Candidate Preparation

This checklist records the release-candidate preparation status for the 1.3.x refactor line. It is intentionally conservative: it documents readiness checks without changing database schema, capabilities, privacy behaviour or backup/restore contracts.

## Static checks required before rc1

Run these checks from the plugin root before preparing an rc branch or tag:

```bash
node tests/smoke_amd.js
node tests/tracker_segment_test.js
node tests/adapter_test.js
node tests/backup_restore_static_test.js
node tests/privacy_static_test.js
node tests/deprecation_static_test.js
node tests/performance_static_test.js
node tests/release_candidate_static_test.js
find . -name "*.php" -print0 | xargs -0 -n1 php -l
find amd/src tests -name "*.js" -print0 | xargs -0 -n1 node --check
```

## Manual runtime checks still required

The static checks do not replace Moodle runtime validation. Before rc1, verify at least:

- install and upgrade on a supported Moodle 5.x site;
- activity creation, edit and deletion;
- YouTube, Vimeo and HTML5 playback;
- progress tracking, seek tracking and heartbeat persistence;
- page reload/unload behaviour;
- backup and restore of a course containing videotrack activities;
- privacy export/delete flows in a real Moodle privacy run;
- keyboard navigation and status announcements for the main player controls.

## Release constraints

- Do not introduce database changes during rc preparation unless a dedicated upgrade review is performed.
- Do not change capabilities, privacy provider contracts or backup/restore mapping without matching tests and documentation.
- Keep AMD build files committed whenever `amd/src` files change.
- Keep runtime player checks manual until a Moodle/browser automation harness exists.

## Current readiness

The 1.3.75-dev checkpoint is intended as the final pre-rc cleanup gate. If all static checks and manual runtime checks pass, the next planned version can move to `1.3.76-rc1`.
