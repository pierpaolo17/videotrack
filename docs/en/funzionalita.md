# mod_videotrack - Features and capabilities

**Documented version**: 1.6.7
**Compatibility**: Moodle 5.0+
**Included languages**: Italian, English, German, Spanish, French, Portuguese, Hindi, Polish

## What mod_videotrack is

`mod_videotrack` is a Moodle activity module for delivering educational videos and tracking student viewing in a verifiable way. It supports YouTube, Vimeo and locally uploaded files, and records watched segments, reactions, personal notes and completion state.

The module measures player behaviour validated server-side. It cannot prove real cognitive attention, but it documents which video portions were played, when reactions or notes were added, and whether configured completion rules were satisfied.

## Video sources

- **YouTube** through the IFrame API.
- **Vimeo** through the Vimeo Player SDK with privacy-friendly configuration.
- **Upload/HTML5** through Moodle file areas and native/custom browser playback.

Providers are handled by separate AMD modules, with shared logic for tracking, reactions, notes and interaction tables.

## Tracking

Tracking stores viewing segments with video time, wall-clock time, playback rate and close reason. The backend merges overlapping intervals and calculates unique covered seconds.

Core rule: replaying an already-covered portion must neither increase nor decrease unique seconds or completion percentage.

## Reactions

Teachers can configure reactions using:

- emoji;
- Font Awesome classes;
- uploaded image files.

Each reaction has a label and a description. The student UI records timestamped reactions and updates the "My reactions" table in real time. The server enforces anti-duplication controls to avoid statistical overload and non-useful reaction spam.

## Personal notes

When enabled, students can save personal notes attached to video time. Notes are separate from reactions but share part of the event structure.

## Completion and gradebook

Completion can depend on viewed percentage, number of reactions, required reactions or logical combinations. The plugin integrates with the Moodle gradebook when configured.

## Reports

Teachers can view activity-level and course-level reports. Reports use normalised data from segments, user state, reactions and notes.

## Privacy, retention and backup

The plugin implements the Moodle Privacy API provider, cleanup/anonymisation tasks and Moodle backup/restore support.

## Technical documentation

For code maintenance use the numbered documents in this directory, especially:

- `02_ARCHITECTURE.md`
- `03_FILE_INVENTORY.md`
- `04_FUNCTION_INVENTORY.md`
- `05_VARIABLE_INVENTORY.md`
- `06_RUNTIME_FLOWS.md`


## Optional timestamped Forum posting (1.5.0)

A teacher may link the activity to a compatible Forum in the same course. The student button reads the current player time, opens a separate Moodle form, and pre-fills a descriptive replay link. Publication is voluntary and uses the official Forum API. Notes remain private. Since 1.5.1, the teacher can customise the pre-filled subject using the `{timestamp}` and `{activity}` placeholders; the student may edit it before publishing.

## Instance heatmap and retention (1.6.0)

Teachers with report access have an aggregate Analytics tab for each activity. It shows distinct viewers along the timeline, retention, unique viewing time, repeated viewing time, most-viewed and most-replayed intervals, and the largest decreases between adjacent intervals. Results can be filtered by a course group available to the teacher. When the course has groups, users without the access-all-groups capability are restricted to the union of groups to which they belong, including when “All permitted users” is selected. A configurable minimum-user threshold suppresses small selections and masks small positive bins. Replay metrics are masked separately when the replaying subgroup is below the threshold, and totals that could reveal hidden values are omitted. The optional reaction overlay uses only clusters that meet the same threshold. No identities or private note text are displayed.

## Same-video analytics across courses (1.6.7)

The **Include data for the same video from my other courses** filter temporarily extends Analytics to every accessible activity using the same technical video. Identity does not depend on the activity name: it uses the YouTube/Vimeo provider ID or the Moodle content hash of an uploaded file. A candidate activity is included only when the teacher has `mod/videotrack:viewreport` in its module context. The same Moodle user is merged across activities and courses before viewer, retention, unique-coverage, replay and privacy-threshold calculations.

The course-group selector is disabled in cross-course scope. Each activity independently applies its effective group mode, `moodle/site:accessallgroups` and the teacher’s permitted groups. Reaction clusters are combined by the saved reaction key rather than local reaction-definition ids; their time window is the one configured in the activity from which the report is opened. The filter is report-only, is not persisted and does not modify source data.

## Runtime fixes 1.6.1

- Note saving now resolves asynchronous player timestamps and prefers the end of the segment just accepted by the server; this prevents a Promise from being sent by the Vimeo player.
- A Moodle log-event failure no longer turns an already stored note into a failed save; a visible warning is returned instead.
- Reaction clusters apply their own privacy threshold independently from viewing-segment availability or suppression. Privacy-safe clusters remain available in an aggregate table without names or private note text.
