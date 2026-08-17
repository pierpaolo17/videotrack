# Personal bookmarks and analytics

Bookmarks are disabled by default and enabled per activity. A bookmark is owned by one user, has a teacher-limited private label and points to a timestamp the user has already watched. Creation/deletion use dedicated AJAX services and Moodle events. Replay passes through the active player adapter and cannot bypass seek policy.

The owner can list, replay, delete and CSV-export their bookmarks. Labels and exact timestamps are not shown to teachers. Per-student reports may show counts. Instance, course and teacher-centric Analytics dashboards show exact aggregate values to authorised report viewers within their existing Moodle activity/course/group scope. Bookmark labels and exact bookmark timestamps remain private to their owner. `analyticsminusers` is not used by these Analytics dashboards; it remains available only for aggregate summaries outside the exact Analytics views.

Bookmarks reuse `videotrack_reactev` with `notetype='bookmark'`. Privacy export/erasure, deletion-based retention, reset and backup/restore include them. Standard reaction deletion cannot delete bookmark rows.
