# Videotrack 1.3 upgrade notes

This document summarizes the incremental 1.3 development line for maintainers upgrading from the stabilized 1.2.x branch or from earlier 1.3-dev snapshots.

## Scope of the 1.3 line

The 1.3 series is an incremental refactor of `mod_videotrack`. It keeps the existing Moodle plugin boundaries and avoids database schema changes unless explicitly introduced by a future migration.

Main goals completed so far:

- centralize provider-specific player operations behind the AMD adapter layer;
- consolidate tracker lifecycle, heartbeat, seek handling and stale async guards;
- harden AJAX/API calls with safer retry, cleanup and error classification;
- improve status, focus handling, live regions and non-invasive error feedback;
- add smoke/static tests to make the refactor easier to validate before release candidates.

## Upgrade expectations

Administrators should treat the 1.3 development builds as pre-release packages until a release candidate is tagged. The current line is still marked `MATURITY_ALPHA` in `version.php`.

No manual database step is expected for the changes covered by the 1.3.54-dev to 1.3.72-dev refactor window. Standard Moodle plugin upgrade flow is sufficient.

## JavaScript and AMD notes

The refactor keeps committed AMD build files. When changing AMD sources, maintainers should update the corresponding files under `amd/build/` and run the smoke/static checks before packaging.

Recommended local checks:

```bash
node tests/smoke_amd.js
node tests/tracker_segment_test.js
node tests/adapter_test.js
node tests/backup_restore_static_test.js
node tests/privacy_static_test.js
```

In addition, run `node --check` on AMD JavaScript files when available in the local environment.

## Backup, restore and privacy

The 1.3.70-dev and 1.3.71-dev steps added static review checks for backup/restore and privacy provider coverage. These checks do not replace manual Moodle restore/privacy verification, but they make accidental omissions easier to catch during development.

Before release candidate builds, still perform manual Moodle tests for:

- backup and restore of an activity instance with tracked data;
- privacy export for a user with notes/progress/reactions;
- privacy delete/export behaviour in a course context.

## Player compatibility

The adapter layer targets YouTube, Vimeo and HTML5 playback paths. Before release candidate builds, run manual playback checks for all supported providers, including:

- start/pause/resume;
- seek handling;
- playback rate where supported;
- completion/progress updates;
- reload/unload behaviour;
- slow or interrupted network conditions.

## Accessibility and UX checks

The 1.3.61-dev to 1.3.65-dev range improved status messages, live regions, focus restoration and error feedback. Before release candidate builds, manually check keyboard-only workflows and screen-reader announcements around confirm dialogs, progress/status updates and AJAX failures.

## Release preparation checklist

Before promoting a build to RC:

1. Run all committed Node tests.
2. Run PHP lint on plugin PHP files.
3. Run PHPUnit in a Moodle test environment.
4. Test install/upgrade in a clean Moodle instance.
5. Test backup/restore manually.
6. Test privacy export/delete manually.
7. Test YouTube, Vimeo and HTML5 playback manually.
8. Review release notes and supported Moodle versions.

## Known limitations

- Static tests are not a substitute for a Moodle runtime test environment.
- Network resilience is intentionally conservative and does not introduce offline batching.
- No CSP hardening is included in this line because external video providers require dedicated manual validation.
