# mod_videotrack — Features and capabilities

Document updated for release **1.4.125**. This is the English companion to `docs/funzionalita.md` and describes the functional behaviour of the plugin for administrators, teachers, and reviewers.

## What mod_videotrack is

`mod_videotrack` is a Moodle activity module for delivering educational videos with progress tracking, contextual reactions, personal notes, completion rules, reporting, privacy controls, and accessibility-oriented user interface behaviour.

The plugin is designed for teaching scenarios where the important metric is not only whether a video has been opened, but which parts were actually watched, how the student interacted with the resource, and whether completion rules have been satisfied in a transparent and auditable way.

## 1. Supported video sources

The activity supports multiple video providers and local files:

- YouTube videos through the YouTube IFrame API.
- Vimeo videos through the Vimeo Player SDK.
- HTML5/local videos served through Moodle file areas.

The server-side helper functions normalise YouTube and Vimeo identifiers, while the client-side AMD modules keep provider-specific player integrations separate from shared tracking and UI logic.

## 2. Viewing tracking

### 2.1 Viewing segments

The plugin records viewing as time intervals rather than as a single counter. Each segment stores the relevant start/end positions and contextual metadata such as session id, reason for closing the segment, and provider-specific information when available.

### 2.2 Unique coverage calculation

Server-side tracker helpers merge overlapping intervals and calculate unique covered seconds. This prevents simple over-counting when the same part of a video is watched multiple times.

### 2.3 Academic-integrity validation

The plugin validates segment duration, playback rate limits, seek behaviour, and interval structure to reduce inflated progress caused by invalid or manipulated client data. Client checks are treated as usability support; server-side validation remains authoritative.

### 2.4 Seek control

When configured, seek blocking prevents students from jumping forward in ways that would invalidate the learning path. Programmatic seeks required by resume or replay logic are distinguished from user-driven seeks.

## 3. Contextual reactions

Teachers may configure contextual reaction buttons such as “confusing”, “important”, or other course-specific labels. Reactions are stored with video time and can be reviewed in reports.

### 3.1 Reaction configuration

Reaction labels and icons are configured from the activity form or through presets. Validation prevents unsupported icon classes and unsafe definitions.

### 3.2 Reaction presets

Administrators can define reusable reaction presets so that teachers do not need to recreate common reaction sets for every activity.

### 3.3 Anti-spam throttle

Client-side and server-side controls limit excessive reaction submissions and preserve the usefulness of the recorded data.

## 4. Student personal notes

Students can create notes linked to specific video times. Notes are personal to the student unless exported or displayed through authorised reporting flows. Notes can be saved and deleted through dedicated AJAX services.

## 5. Interactive VTT transcript

For HTML5 videos, VTT captions can be rendered as an interactive transcript. Clicking transcript cues may navigate the video when the activity configuration allows it.

## 6. Navigable VTT chapters

VTT chapters can be displayed as a chapter bar. Chapter navigation respects the same educational constraints as the rest of the player, including seek restrictions where applicable.

## 7. Poster / pre-play preview

Teachers can provide a poster image for the video. The player removes or hides the poster when playback starts, while preserving accessible status feedback.

## 8. Automatic resume

The plugin can offer or perform resume from the last known progress point. Resume behaviour is careful not to create fake viewing progress and uses programmatic seek markers where needed.

## 9. Playback speed limits

The plugin supports configured playback rates and site-level caps. Tracking validation accounts for playback speed so that completion is not inflated by unsupported rates.

## 10. Activity completion

Completion can be based on watching thresholds and required contextual reactions. The custom completion class reports rule descriptions to Moodle and computes the state using server-side progress snapshots.

## 11. Grading

When grading is enabled, the plugin can update the Moodle gradebook using calculated progress. Grade updates are kept separate from raw segment persistence.

## 12. Teacher report

The activity report shows student progress, viewing coverage, reactions, notes where allowed, and related status information. Reporting uses pagination and stable ordering to remain scalable.

## 13. Course-level report

The course report aggregates Videotrack activity data across course instances, allowing teachers to review engagement at a broader level.

## 14. Accessibility

The interface uses status messages, ARIA relationships, captions, keyboard-aware controls, and focus-safe behaviours. Additional WCAG edge-case audit documentation is available in `docs/wcag_edge_audit_1.4.122.md`.

## 15. Privacy, GDPR, and retention

The plugin implements Moodle Privacy API support, exports user data, and supports deletion/anonymisation. Unlimited retention requires explicit administrative confirmation and is documented as an intentional configuration choice.

## 16. Backup and restore

Backup and restore support preserves the activity configuration and related Videotrack data according to Moodle backup conventions. Restore code normalises interval JSON and remaps contextual identifiers where needed.

## 17. Administrator configuration

Site administrators can configure default limits, retention behaviour, playback constraints, reporting limits, and reusable reaction presets. Custom admin setting classes validate integer ranges and explicit retention confirmation.

## 18. Mobile mode

The plugin declares mobile service integration through Moodle service configuration. Behaviour depends on Moodle mobile support and the configured external services.

## 19. External services and CDNs

YouTube and Vimeo integrations use their respective external player APIs. HTML5/local-video use cases can run without third-party video providers, subject to the selected video source.

## 20. Feature summary

| Area | Capability |
|---|---|
| Video sources | YouTube, Vimeo, HTML5/local files |
| Tracking | Segment-based tracking and unique coverage calculation |
| Integrity | Server-side validation of intervals, rates, and limits |
| Reactions | Configurable contextual reactions and presets |
| Notes | Student personal notes linked to video time |
| Transcript | VTT transcript rendering for supported sources |
| Chapters | VTT chapter navigation with activity constraints |
| Resume | Last-position resume without artificial progress inflation |
| Completion | Custom completion rules based on viewing and reactions |
| Grading | Optional Moodle gradebook integration |
| Reports | Activity and course-level reporting |
| Accessibility | ARIA/status/focus-safe interface behaviours |
| Privacy | Privacy API, export, deletion, retention and anonymisation |
| Backup | Moodle backup/restore integration |

## 21. Related documentation

- `docs/technical_structure.md`: English technical structure and function inventory.
- `docs/struttura_tecnica.md`: Italian technical structure and function inventory.
- `docs/ajax-layer.md`: AJAX layer rationale and validation flow.
- `docs/event_bus.md`: AMD event bus namespaces and supported events.
- `docs/sendbeacon_review_1.4.123.md`: sendBeacon behaviour and fallback assessment.
- `docs/wcag_edge_audit_1.4.122.md`: final accessibility edge-case review.
