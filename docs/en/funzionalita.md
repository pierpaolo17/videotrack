# User and administrator guide

VideoTrack 1.7.134 is a Moodle activity for delivering video, recording server-validated watched evidence,
supporting study interactions and evaluating completion. Every optional collection feature must be enabled
explicitly. Site policy can restrict which settings a teacher may override.

## Supported media sources

| Source | Teacher input | Player | Notes |
|---|---|---|---|
| Uploaded/HTML5 | Video file in the activity | Native/custom HTML5 controls | Supports optional download, poster and local metadata duration. |
| YouTube | Public YouTube URL or identifier | YouTube IFrame API | Duration is proposed by a bounded, muted form-only probe. Provider availability still applies. |
| Vimeo | Public Vimeo URL or identifier | Vimeo Player SDK | Duration is proposed asynchronously. Provider privacy/network policy still applies. |

The duration stored by the teacher is authoritative for percentages. Automatic detection is only a proposal:
review it before saving, enter it manually when detection is unavailable, or use `0` to disable percentage
calculation. Player adapters share a contract but have separate implementations and external-provider limits.
The form displays the same value as `HH:MM:SS` and updates that equivalent after automatic detection or manual input.

## Activity configuration

Teachers can configure:

- name, description and standard Moodle availability/group settings;
- source, URL/file, poster and optional download for uploaded media;
- autoplay, loop, initial mute, player width and visible controls;
- rewind and fast-forward steps, forward/backward seek policy and resume;
- available playback speeds, whether the learner can change speed, a maximum rate and the fallback rate
  after a blocked forward seek;
- subtitles, searchable transcripts and chapters using WebVTT files;
- reactions, personal notes, private bookmarks and an optional timestamped Forum action;
- focus/integrity diagnostics and learner acknowledgement;
- learner report visibility, reaction aggregation window and export preferences;
- grade, pass grade and completion conditions.

Site administrators define defaults and may revoke `mod/videotrack:overrideplayersettings` or
`mod/videotrack:overridecompletionsettings` to enforce central policy.

## Watched progress

VideoTrack records playback requests in `videotrack_seg`. A browser play event opens a server-authorised
credit window. Later segment writes are accepted for progress only when session identity, elapsed server time,
requested media interval and playback rate remain within the server budget. Rejected rows may be retained as
non-authoritative audit evidence but do not advance watched coverage.

Progress is based on the union of validated media-time intervals, not on wall-clock time or the furthest
position reached. Rewatching an interval does not count twice. A forward jump never fills the skipped gap.
`videotrack_state` caches unique coverage, last trusted position and completion state for efficient reads.

## Seek, speed, resume and replay

- Forward and backward seeking can be allowed independently.
- A blocked forward seek returns to the trusted watched frontier and applies the configured fallback speed.
- A permitted forward seek changes navigation but does not retroactively mark skipped seconds as watched.
- Resume uses the saved trusted position and is clamped to valid duration and coverage boundaries.
- Report replay links open a bounded fragment without weakening the normal tracking rules.
- Rate-weighted validation prevents accelerated playback from earning more media time than the server budget.

Provider controls are best effort when the external SDK exposes behaviour outside the plugin's UI. The server
ledger remains the authority for earned progress.

## Reactions

Teachers may start from a preset or define custom reactions with label, description and emoji, Font Awesome,
text or uploaded icon. A reaction can be marked as required for completion. Learners can add or remove their
own reactions at trusted timestamps. Duplicate and burst controls reduce accidental or automated spam.

Reactions are disabled by default. Enabling them requires at least one complete active definition; the form warns
and rejects an empty configuration. The learner controls, player reaction integration, notice and **My reactions**
history are rendered only when both the feature and an active definition are present. Notes, bookmarks and the
Forum action remain independently governed by their own enablement and destination settings.

Completion can require a minimum number of distinct reactions, every enabled reaction type, specific required
types, or a combination. The configured `AND`/`OR` logic applies inside the single composite VideoTrack rule.

## Personal notes and bookmarks

Learners can create timestamped personal notes and named bookmarks when enabled. These records are private to
their owner except where a separately authorised individual report/export explicitly includes learner data.
The learner page keeps the composers visible and places saved histories in native collapsible sections.

Bookmarks can be replayed and exported by their owner. Notes and bookmark labels are excluded from aggregate
Analytics. Deletion is soft at interaction level so reports, retention and restore can preserve consistent
history while respecting current visibility.

## WebVTT text tools

- subtitles/captions are served through Moodle file areas;
- transcripts provide searchable timed cues and seek navigation;
- chapters provide named navigation points;
- a poster can be displayed before playback.

The teacher is responsible for valid, correctly timed and licensed content. Provider-native captions and
uploaded VideoTrack VTT files are separate facilities.

## Timestamped Forum bridge

An activity can link to a Forum in the same course. The learner action opens a composer with a validated video
timestamp and a configurable subject template. VideoTrack validates access and timestamp authority, but the
Forum module performs the final discussion write and applies its own permissions, groups and availability.

## Acknowledgement

The teacher may publish a statement that the learner must confirm at any time or only after the validated final
video second. Confirmation stores a hash of the current statement, its instance version, confirmation time and
the viewed coverage snapshot. Editing the statement creates a new current version; an old confirmation does not
satisfy the new one. Acknowledgement can participate in completion.

## Completion and gradebook

The custom VideoTrack completion rule may use:

- minimum viewed percentage;
- reaction criteria;
- confirmation of the current acknowledgement;
- `AND` or `OR` composition of the enabled VideoTrack criteria.

Moodle can additionally require viewing the activity, receiving a grade or passing the grade. These remain
separate core conditions. The activity header groups the VideoTrack logic label before the completion list
without changing rule order, states or semantics. Gradebook supports points, scales and pass grade.

## Reports, Analytics and exports

Depending on capability, VideoTrack provides:

- learner self-report;
- activity-level aggregate and individual reports;
- course dashboard across VideoTrack activities;
- teacher view across authorised courses;
- same-video Analytics across course/activity contexts;
- reaction timelines and clusters;
- CSV, Excel and ODS aggregate exports;
- individual CSV exports and owner bookmark export.

Aggregate-only access preserves the configured minimum-user privacy threshold and does not permit learner
filtering. Individual access returns exact values only within the viewer's Moodle scope. Optional identity
fields and CSV delimiter can be governed globally or per activity.

## Focus and integrity controls

Optional diagnostics include focus/visibility changes, Picture-in-Picture attempts and random attention pauses.
The default focus policy pauses only when the document is hidden. Strict visible-window blur handling can be
enabled site-wide; membership in the hidden course group `mod_videotrack_focus_exception` relaxes strict blur
to hidden-only for authorised learners. It never bypasses hidden-tab handling or server validation.

Integrity events are bounded diagnostic signals, not proof of misconduct. They must be interpreted with
accessibility needs, browser behaviour, assistive technology and the surrounding evidence.

## Privacy, retention and data lifecycle

The Moodle Privacy API declares, exports and deletes learner data. Scheduled retention removes expired granular
records and rebuilds derived state from retained validated evidence. Unlimited retention requires an explicit
administrator confirmation. Backup includes activity configuration and reaction definitions; user data is
included only when requested and only while retained. Restore remaps users, forums and reactions and rebuilds
derived state. Course/activity reset and deletion remove the scoped VideoTrack records.

See [`18_PRIVACY_RETENTION.md`](18_PRIVACY_RETENTION.md) and the root `PRIVACY.md` for the exact data contract.

## Accessibility

The interface uses native controls where practical, visible focus indicators, live status regions, accessible
names, keyboard operation, responsive layout and forced-colours support. Transcript, completion grouping,
personal-history sections and player status were manually checked in the current WCAG closure cycle. Public
provider iframes remain subject to the provider's own accessibility behaviour.

## Languages and diagnostics

Eight language packs are maintained with the same keys and placeholders: German, English, Spanish, French,
Hindi, Italian, Polish and Portuguese. Administrators can run the read-only validator and course Analytics
benchmark documented in [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md).

## Operational limits

- Public YouTube/Vimeo availability, consent, cookies and SDK behaviour are external dependencies.
- A duration of `0` disables percentage calculation; it is not an inferred unlimited duration.
- Picture-in-Picture prevention and external-provider controls are best effort.
- Focus diagnostics cannot establish attention or intent.
- Browser tests use local provider doubles for determinism; perform a real-provider smoke test before a site-wide
  deployment when provider policy or network controls have changed.

For symptom-led checks, see [`20_TROUBLESHOOTING.md`](20_TROUBLESHOOTING.md).
