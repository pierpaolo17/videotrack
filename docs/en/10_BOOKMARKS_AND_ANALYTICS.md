# Personal bookmarks and analytics

Bookmarks are disabled by default and enabled per activity. A bookmark is owned by one user, has a teacher-limited private label and points to a timestamp the user has already watched. Creation/deletion use dedicated AJAX services and Moodle events. Replay passes through the active player adapter and cannot bypass seek policy.

The owner can list, replay, delete and CSV-export their bookmarks. Labels and exact timestamps are not shown to teachers. Per-student reports may show counts. In `report.php`, aggregate-only viewers retain the configured `analyticsminusers` masking, while viewers with individual-report access receive exact aggregate values inside the same Moodle activity/group scope. Bookmark labels and exact bookmark timestamps remain private to their owner. Course and teacher-centric dashboards keep their existing capability/privacy behaviour.

Bookmarks reuse `videotrack_reactev` with `notetype='bookmark'`. Privacy export/erasure, deletion-based retention, reset and backup/restore include them. Standard reaction deletion cannot delete bookmark rows.

Cross-course Analytics groups activities by a stable technical media identity. YouTube and Vimeo use the exact
provider identifier, uploaded files use the Moodle content hash, and external media use a canonical URL produced by
`analytics_scope::normalise_external_url()`. Canonical URL identity requires a host, defaults an omitted scheme to
HTTPS, lowercases scheme and host, removes HTTP port 80 and HTTPS port 443, preserves other ports, collapses repeated
path separators, ignores fragments and sorts top-level query keys with RFC 3986 encoding. Authority, path and query
normalisation are isolated private helpers; callers continue to use the single public normalisation contract.
