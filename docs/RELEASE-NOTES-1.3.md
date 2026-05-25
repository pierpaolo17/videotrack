# Videotrack 1.3 release notes draft

## Status

The 1.3 line is in release-candidate validation. It is not yet a final stable release.

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
- `tests/deprecation_static_test.js`
- `tests/performance_static_test.js`
- `tests/release_candidate_static_test.js`
- `tests/rc_freeze_static_test.js`
- `tests/rc2_freeze_static_test.js`
- `tests/rc3_freeze_static_test.js`

## Manual checks still required before release candidate

- Moodle install and upgrade flow.
- Provider playback tests for YouTube, Vimeo and HTML5.
- Backup and restore of representative activities.
- Privacy export/delete behaviour with real user data.
- Accessibility review for keyboard and screen-reader workflows.
- Performance review on long videos and repeated seek/reload sessions.

## Compatibility notes

The plugin metadata currently targets Moodle 5.0+ according to `version.php`. Confirm supported versions again before tagging a stable release.


## Release candidate checkpoints

- `1.3.76-rc1`: first release-candidate freeze.
- `1.3.77-rc2`: release-gate hardening and rc2 validation.
- `1.3.78-rc3`: final planned release-candidate checkpoint before final static verification.

- `1.3.80`: stable checkpoint after RC/final static verification; no new runtime behaviour beyond the freeze line.

- `1.3.81`: stable maintenance packaging checkpoint; restores/validates stable release documentation and static gate evidence.
