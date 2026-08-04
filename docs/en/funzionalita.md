# mod_videotrack - Features and capabilities

**Documented version**: 1.4.248
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
