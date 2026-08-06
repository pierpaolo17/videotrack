# Personal bookmarks and bookmark analytics

**Documented version:** 1.6.16

## Scope

Personal bookmarks are an optional per-activity study tool. The feature is disabled by default. A teacher enables it in the expanded **Personal study tools** section of the activity form. The site default is controlled by `setting:bookmarksenabled`.

A bookmark belongs to one user and one VideoTrack activity. It stores a private label and a watched video timestamp. The label and timestamp are visible only to the owner.

## Data model

Bookmarks reuse `{videotrack_reactev}` with:

- `notetype = 'bookmark'`;
- `reactionkey = 'bookmark'`;
- `notetext` containing the private label;
- `videotime` containing the watched position;
- `isdeleted` supporting soft deletion.

No dedicated bookmark table is introduced. Backup, restore, retention and Privacy API handling reuse the existing event-data lifecycle.

## Student runtime

`view.php` loads only the current user's active bookmarks and passes privacy-safe configuration to the player. `amd/src/core/player/bookmarks.js` is shared by HTML5, YouTube and Vimeo. It:

1. saves current progress before creating a bookmark;
2. resolves the accepted watched timestamp;
3. calls `mod_videotrack_save_bookmark`;
4. inserts the new row in timestamp order;
5. delegates replay to the player-specific replay handler;
6. calls `mod_videotrack_delete_bookmark` for owner deletion.

The server verifies capability, session, activity setting, label length, rate limits and that the target timestamp has already been watched.

## Owner export

`bookmarks.php` exports only the current user's bookmarks as CSV. The request requires login, capability, POST and sesskey. CSV fields are protected against formula injection. The export triggers `bookmark_exported`.

## Teacher reports and analytics

Teacher-facing data are aggregate-only:

- student report: bookmark count for each learner;
- course dashboard: privacy-safe bookmark event count;
- personal teacher dashboard: privacy-safe bookmark event count;
- instance Analytics: a dedicated **Bookmark usage** section with cards for saved bookmarks and distinct students using bookmarks.

The Analytics section is rendered whenever bookmarks are enabled, including when no bookmark exists. It displays zero values explicitly. When fewer than `analyticsminusers` distinct learners used bookmarks, exact values are replaced by the standard privacy-hidden label and a warning.

Cross-course Analytics includes only matching activities where `bookmarksenabled` is true. Labels and individual timestamps are never exposed in teacher reports, charts or exports.

## Privacy and retention

The Privacy API exports active and deleted bookmarks to the owner in separate chunks. Erasure and scheduled retention process bookmark rows together with the other user-event rows. Teacher analytics uses only counts and distinct-user totals after capability and group filtering.

## Backup and restore

`bookmarksenabled` is included in activity backup and restore. Bookmark user data are included with the existing reaction-event structure when user data are requested.

## Main files

- `bookmarks.php`
- `classes/external/save_bookmark.php`
- `classes/external/delete_bookmark.php`
- `classes/event/bookmark_saved.php`
- `classes/event/bookmark_deleted.php`
- `classes/event/bookmark_exported.php`
- `amd/src/core/player/bookmarks.js`
- `classes/local/analytics.php`
- `classes/local/course_analytics.php`
- `classes/local/teacher_analytics.php`
- `report.php`
- `reports_course.php`
- `reports_teacher.php`
- `tests/save_bookmark_test.php`
- `tests/lib_test.php`

## Validation

A bookmark release must verify:

- activity form persistence for enabled and disabled values;
- save, replay, delete and owner export;
- separate runtime behaviour for HTML5, YouTube and Vimeo;
- aggregate counts in student, course, teacher and instance reports;
- suppression below `analyticsminusers`;
- no labels or individual timestamps in teacher output;
- Privacy API export/erasure;
- backup/restore with and without user data;
- PHPUnit, PHPCS Moodle Extra and Grunt AMD when AMD sources change.
