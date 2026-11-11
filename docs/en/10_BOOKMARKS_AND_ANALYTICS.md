# Personal bookmarks and analytics

Bookmarks are disabled by default and enabled per activity. A bookmark is owned by one user, has a teacher-limited private label and points to a timestamp the user has already watched. Creation/deletion use dedicated AJAX services and Moodle events. Replay passes through the active player adapter and cannot bypass seek policy.

The save service validates its external parameters and AJAX sesskey before loading the activity context and learner scope. It then applies feature enablement, bounded plain-text label normalisation, duration clamping, previously watched or current-session evidence, and a user/activity-wide limit of ten bookmarks in ten seconds across all browser sessions. Only after those guards does it insert the canonical row and trigger `bookmark_saved`.

The owner can list, replay, delete and CSV-export their bookmarks. Labels and exact timestamps are not shown to teachers. Per-student reports may show counts. In `report.php`, aggregate-only viewers retain the configured `analyticsminusers` masking, while viewers with individual-report access receive exact aggregate values inside the same Moodle activity/group scope. Bookmark labels and exact bookmark timestamps remain private to their owner. Course and teacher-centric dashboards keep their existing capability/privacy behaviour.

Bookmarks reuse `videotrack_reactev` with `notetype='bookmark'`. Privacy export/erasure, deletion-based retention, reset and backup/restore include them. Standard reaction deletion cannot delete bookmark rows.

Cross-course Analytics groups activities by a stable technical media identity. YouTube and Vimeo use the exact
provider identifier, uploaded files use the Moodle content hash, and external media use a canonical URL produced by
`analytics_scope::normalise_external_url()`. Canonical URL identity requires a host, defaults an omitted scheme to
HTTPS, lowercases scheme and host, removes HTTP port 80 and HTTPS port 443, preserves other ports, collapses repeated
path separators, ignores fragments and sorts top-level query keys with RFC 3986 encoding. Authority, path and query
normalisation are isolated private helpers; callers continue to use the single public normalisation contract.

## Reaction-cluster display limit and effective threshold

The reaction-cluster table uses the distinct-user privacy threshold applicable to the selected report scope.
Individual-report access to every included activity permits exact aggregates; otherwise the configured
aggregate-only threshold applies. The display contains at most 2000 eligible clusters. When the limit warning
appears, additional eligible clusters have been omitted. Narrowing the report scope may avoid the cap; it
does not guarantee a complete view. Disabling the overlay hides clusters and does not recover omitted ones.
Neither the earliest subset across reaction types nor a complete export is promised. Summary counts and
privacy/access filters retain their existing behaviour. These wording corrections change no report logic.

## Viewing sources, zero counts and independent summaries

Analytics can use raw segments or reconstruct unique coverage from retained aggregate state intervals when
that state preserves more viewing evidence. State reconstruction does not supply replay metrics. Privacy
masking protects non-empty subgroups below the effective threshold; zero counts can remain visible. When
protected interval populations would expose the denominator, retention percentages are masked as well.

If no valid viewing interval is available, event summaries remain independent of viewing coverage. Reaction,
bookmark, acknowledgement and integrity summaries depend on their own data/features, permissions and privacy
rules. Several summaries precede the no-viewing notice, while a reaction-cluster table can follow it. The
notice therefore does not promise their page position or availability. These are existing behaviours; the
language patch changes neither source selection nor masking, metrics, exports or report layout.

## Cumulative reports, exports and recalculation wording

Cumulative reaction reports use `reportclusterlimit`, separately from the fixed Analytics cluster cap.
When capped without a video-time range filter, the table is hidden and cumulative CSV stops before cluster
rows. A range filter permits capped rows with a warning; narrowing does not guarantee complete data. The
supplementary heatmap and its table share the same retained subset.

Custom CSV Overall mode includes all permitted learners, regardless of a single learner selection, while
retaining access filters. Bookmarks remain counts only; private labels/timestamps are absent in every format.
Recalculation rebuilds from retained server-validated segments and saved duration; it cannot restore deleted
evidence. Italian video-time headings now say “Tempo del video”; CSV values/column order are unchanged.

After the earlier web work and App decision, the maintainer has requested multilingual VTT subtitles before
the authenticated providers. Existing multilingual transcripts remain separate. This is sequencing only;
HTML5 selection/file lifecycle and external-provider feasibility will be implemented/tested in their own phase.
