# Videotrack 1.3 release notes draft

## Status

The 1.3 line is still a development/refactor line. It is not yet a final stable release.

## Highlights

- Adapter layer for YouTube, Vimeo and HTML5 player operations.
- Centralized tracker lifecycle, segment handling and heartbeat flow.
- Safer state transitions and guards against stale asynchronous work.
- Hardened AJAX/API handling with classification, retry and cleanup.
- Improved accessible status messages, focus handling and non-invasive error feedback.
- Added smoke/static tests for AMD modules, tracker/segment logic, adapter behaviour, backup/restore coverage and privacy coverage.

## Validation added during the refactor

Current development checks include:

- `tests/smoke_amd.js`
- `tests/tracker_segment_test.js`
- `tests/adapter_test.js`
- `tests/backup_restore_static_test.js`
- `tests/privacy_static_test.js`
- `tests/lib_test.php`

## Manual checks still required before release candidate

- Moodle install and upgrade flow.
- Provider playback tests for YouTube, Vimeo and HTML5.
- Backup and restore of representative activities.
- Privacy export/delete behaviour with real user data.
- Accessibility review for keyboard and screen-reader workflows.
- Performance review on long videos and repeated seek/reload sessions.

## Compatibility notes

The plugin metadata currently targets Moodle 5.0+ according to `version.php`. Confirm supported versions again before tagging a stable release.
