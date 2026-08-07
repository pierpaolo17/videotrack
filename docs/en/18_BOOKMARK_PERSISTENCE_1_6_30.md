# Bookmark segment persistence — 1.6.30

## Runtime failure corrected

The shared AMD segment contract already used `bookmark` as a valid interaction close reason. The PHP validation whitelist in `classes/external/helper.php` did not contain that value. As a result, clicking **Save bookmark** attempted to persist the current watched segment with `endreason = bookmark`, the segment Web Service rejected it as an invalid parameter, and the player deliberately swallowed that background progress error. The following bookmark request then failed with `error:playbackpositionnotwatched` because the current position had not been persisted.

## Contract restored

VideoTrack 1.6.30 adds `bookmark` to the server whitelist and documents that the PHP list must remain aligned with `SAVE_REASONS` in `amd/src/core/segment.js`. No watched-position protection is removed. The bookmark Web Service still requires the selected timestamp to be contained in server-validated viewing data.

## Regression coverage

`tests/save_bookmark_test.php` now verifies both the Moodle external-parameter declaration and acceptance of the `bookmark` segment end reason. This catches any future drift between the client interaction reason and the server whitelist.
