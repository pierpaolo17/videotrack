# Personal bookmarks and analytics

Bookmarks are disabled by default and enabled per activity. A bookmark is owned by one user, has a teacher-limited private label and points to a timestamp the user has already watched. Creation/deletion use dedicated AJAX services and Moodle events. Replay passes through the active player adapter and cannot bypass seek policy.

The owner can list, replay, delete and CSV-export their bookmarks. Labels and exact timestamps are not shown to teachers. Per-student reports may show counts; course/teacher dashboards and Analytics show only aggregate event and distinct-user counts when the feature is enabled. `analyticsminusers` masks small populations.

Bookmarks reuse `videotrack_reactev` with `notetype='bookmark'`. Privacy export/erasure, retention anonymisation, reset and backup/restore include them. Standard reaction deletion cannot delete bookmark rows.
