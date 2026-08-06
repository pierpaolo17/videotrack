# Runtime flows

## Tracking segmenti

```text
player event -> core/tracker -> save_segment AJAX -> classes/external/save_segment -> tracker::save_segment -> videotrack_seg + videotrack_state
```

The backend merges intervals, calculates unique seconds and percentage, and must not increase coverage when the student replays an already-covered section.

## Reazioni

```text
reaction button -> player reaction handler -> save_reaction AJAX -> classes/external/save_reaction -> videotrack_reactev -> table refresh
```

Important rules:

- only one reaction for the same displayed video second;
- too-close duplicate reactions are ignored without showing an error;
- the immediate UI row must use server-side data when available.

## Note

```text
note form -> save_note AJAX -> classes/external/save_note -> videotrack_reactev with notetype
```

## Replay frammento

```text
button.videotrack-replay -> shared replay handler -> player-specific seek/play implementation
```

Replay is shared as a UI event, but seek behaviour is specific to HTML5, YouTube and Vimeo. The initial seek target must be the exact reaction timestamp; start/end remain fragment metadata. Direct report links (`replaystart`/`replayend`) must take precedence over the saved resume position on all three players and must request playback.

## Vimeo

The Vimeo SDK uses asynchronous promises for `setCurrentTime()`, `play()` and `pause()`. Do not chain aggressive `play()` calls after a seek: this can produce `PlayInterrupted`. Every Vimeo change must be manually tested on rewind, forward within viewed range, forward beyond limit and replay.

## CSV export flow (1.4.269)

The site default defines the CSV separator and optional course/activity/user fields. Each activity may inherit or override these choices. Available separators are comma, semicolon, `§`, `#` and `|`. Teachers see the same standard configuration choices as administrators; identity fields denied by Moodle core remain visible but disabled and cannot be exported. At runtime, `classes/local/csv_export.php` applies Moodle visibility rules, loads standard and visible custom profile fields through `core_user\fields`, optionally adds the video link, prevents spreadsheet-formula injection, and writes UTF-8 with a BOM for spreadsheet applications.

The **CSV export** tab uses one menu to select all students or one specific student, then lets teachers choose reactions and/or notes and detailed, summary or overall output. Detailed output writes one row per event. Summary output clusters reactions per student and type while keeping notes on individual rows. Overall output clusters both reactions and notes from all students with the configured cluster window. In overall output, notes from the same cluster are concatenated into one cell as `{note1}{note2}{note3}`, and the creation-time column is omitted because the content is aggregated. Names are exported in separate last-name and first-name columns. The personal-data notice remains informational; downloads are protected by Moodle capabilities and sesskey validation without an additional checkbox.

Report time filters use separate numeric hour, minute and second controls; videos shorter than one hour show only `MM:SS`. Existing `MM:SS` and `HH:MM:SS` links remain compatible.

## Completion recalculation (1.4.268)

The recalculation action rebuilds each aggregate state from raw `videotrack_seg` rows, rather than only reevaluating the completion boolean. It merges watched intervals, recalculates unique covered seconds and percentage, restores the latest raw-segment position, reevaluates reaction requirements, and synchronises Moodle completion only when custom VideoTrack completion rules are configured and the completion state changes. View-only completion remains managed by Moodle core.

The **Completion recalculation** tab rebuilds states for all tracked users or one selected user.


## Timestamped Forum post flow (1.5.0)

Player button → shared AMD timestamp read → `forum_post.php` → Moodle Form validation → `forum_bridge` revalidation → `mod_forum_external::add_discussion()` → Forum discussion. Tracking, seek, replay, reactions and private notes are not modified.

## Instance analytics flow (1.6.0)

```text
Analytics tab -> capability-safe course-group scope -> ordered videotrack_seg recordset
-> analytics::build() -> privacy threshold -> heatmap + retention + accessible table
```

The scope uses the activity effective group mode: no-groups activities are not restricted merely because course groups exist; visible-groups mode exposes the groups Moodle makes visible; separate-groups mode without `moodle/site:accessallgroups` limits the general selection to the viewer’s own groups. Raw segments are streamed in user order. For each user, raw overlap contributes to total viewing time, while merged intervals contribute to unique coverage. Their non-negative difference is repeated viewing time. Exact results are hidden when the whole selection is below `analyticsminusers`; positive individual bins below the same threshold are masked. Replay metrics are independently masked when the replaying subgroup is below the threshold, and totals that could reveal masked values are omitted. The optional reaction overlay uses separate privacy-safe clusters and never loads note text or user names. No player, tracking, completion or CSV flow is modified.

## Cross-course Analytics flow (1.6.7)

```text
analyticsallcourses checkbox
-> analytics_scope::technical_identity()
-> find instances with the same provider ID/content hash
-> check mod/videotrack:viewreport in every context_module
-> resolve permitted groups for each activity
-> OR query by videotrackid + permitted userid scope
-> order by userid
-> analytics::build()/build_from_states()
-> privacy threshold over the combined population
```

The same `userid` is treated as one viewer even when present in multiple courses. Duration uses the best persisted value across all accessible instances. For YouTube and Vimeo, queries exclude historical rows with a different `videoid`. Reactions are grouped by `reactionkey`; the local numeric id is only a fallback for legacy rows. Cross-course clustering uses the cluster window configured in the activity from which the report is opened.

## Runtime fixes 1.6.1

- Note saving now resolves asynchronous player timestamps and prefers the end of the segment just accepted by the server; this prevents a Promise from being sent by the Vimeo player.
- A Moodle log-event failure no longer turns an already stored note into a failed save; a visible warning is returned instead.
- Reaction clusters apply their own privacy threshold independently from viewing-segment availability or suppression. Privacy-safe clusters remain available in an aggregate table without names or private note text.

## Runtime fixes 1.6.2

- The notes module now accepts the status callbacks passed by the player facade; clicks no longer stop with `showStatusMessage is not a function` before the AJAX call.
- Analytics scope uses the activity effective group mode instead of the mere presence of course groups.
- Reactions are counted even when they do not form a time cluster; the overall summary is shown only when the distinct-student privacy threshold is met.
- Clusters still require the threshold within the same reaction type and time window, and the UI explicitly distinguishes detected reactions from visible clusters.

## Personal bookmark flow (1.6.14–1.6.16)

1. The teacher enables bookmarks in Personal study tools.
2. `view.php` loads active bookmarks for the current user only.
3. The shared AMD handler saves current progress and resolves an accepted watched timestamp.
4. `mod_videotrack_save_bookmark` validates the position and inserts `notetype='bookmark'`.
5. The UI inserts the bookmark in chronological order; replay uses the player-specific replay handler.
6. Deletion calls `mod_videotrack_delete_bookmark`, which verifies ownership and soft-deletes the row.
7. Owner export uses `bookmarks.php`.
8. Teacher reports count events and distinct users only. Instance Analytics always renders a bookmark section when enabled and masks exact values below `analyticsminusers`.

## Integrity and focus flow (1.6.17)

Visibility/player callback -> player-specific facade -> shared `focus_guard` -> optional pause and student status -> debounced AJAX signal -> `{videotrack_integrity}` -> capability/group/privacy-safe teacher report. The flow never mutates watched intervals, completion or grades. Random deadlines restart after learner interactions and run only while playback is active.
