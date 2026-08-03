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

## CSV export flow (1.4.264)

The site default defines the CSV separator and optional course/activity/user fields. Each activity may inherit or override these choices. At runtime, `classes/local/csv_export.php` filters the configured user fields through Moodle's identity-field visibility rules, loads standard and visible custom profile fields with `core_user\fields`, applies spreadsheet-formula protection, and writes localised headers. The all-events export includes active reactions and personal notes for every user after an explicit personal-data confirmation.

## Completion recalculation (1.4.264)

The recalculation action rebuilds each aggregate state from raw `videotrack_seg` rows, rather than only reevaluating the completion boolean. It merges watched intervals, recalculates unique covered seconds and percentage, restores the latest raw-segment position, reevaluates reaction requirements, and synchronises Moodle completion only when custom VideoTrack completion rules are configured and the completion state changes. View-only completion remains managed by Moodle core.
