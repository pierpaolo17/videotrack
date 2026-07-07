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

Replay is shared as a UI event, but seek behaviour is specific to HTML5, YouTube and Vimeo. The initial seek target must be the exact reaction timestamp; start/end remain fragment metadata.

## Vimeo

The Vimeo SDK uses asynchronous promises for `setCurrentTime()`, `play()` and `pause()`. Do not chain aggressive `play()` calls after a seek: this can produce `PlayInterrupted`. Every Vimeo change must be manually tested on rewind, forward within viewed range, forward beyond limit and replay.
