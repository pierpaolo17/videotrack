# VideoTrack privacy notes

VideoTrack stores per-user viewing segments, personal notes and reactions so that Moodle can calculate completion, grade-related progress and learning analytics for the activity.

When Moodle privacy erasure is requested for a user, VideoTrack permanently deletes that user's tracking segments, aggregate state, reactions and personal notes for the selected activity context. The plugin does not keep pseudonymised rows as a response to Moodle Privacy API erasure requests.

Site administrators can configure the retention period in the activity settings. A value of `0` keeps data indefinitely until a Moodle privacy erasure or context deletion request is processed. Automated retention cleanup may anonymise old tracking records to preserve aggregate analytics; this is separate from user-initiated erasure and should be covered by the site's privacy notice and lawful-basis assessment.


## Retention anonymisation details

Automatic retention cleanup is distinct from Moodle Privacy API erasure requests.
When retention cleanup anonymises old tracking rows, it replaces user/session
identifiers and clears behavioural details, free-text notes, reaction descriptions
and reaction labels. Internal reaction keys may remain only as non-personal
definition identifiers for aggregate reporting.

Context-level erasure also removes plugin-owned files in the module context,
including uploaded videos, poster images, subtitles and reaction icons.


## Browser session data and unload beacons

VideoTrack creates a short browser-session identifier in JavaScript to associate playback heartbeats, reactions and notes with the current activity session. The identifier is not an authentication token and is validated server-side together with the Moodle session, capability checks and recent playback evidence.

When the page is closed, the player may use `navigator.sendBeacon()` to submit the final watched segment to Moodle's own AJAX endpoint using the current Moodle sesskey. This avoids losing progress on page unload; it does not send data to external services. The notes-panel collapsed/expanded preference is stored only in the browser `sessionStorage` and is not exported to the server.

## Informazioni aggiuntive per esportazioni, session storage e sottotitoli

Il modulo usa `sessionStorage` solo per stato temporaneo del player nel browser; i valori sono limitati alla sessione della scheda/browser e non sono condivisi tra dispositivi. Il fallback `sendBeacon` puo inviare l'ultimo segmento di visione durante la chiusura della pagina per evitare perdita di progresso. Gli URL opzionali di sottotitoli/transcript caricati via JavaScript sono accettati solo da origini locali Moodle/pluginfile.

CSV report exports may contain student personal data and must be used only by users with reporting capabilities, according to the site's privacy notice and institutional retention policies.


## Uploaded files and gradebook records

Teacher-managed files such as uploaded videos, poster images, subtitles and
reaction icons are activity content rather than per-student tracking records.
They are therefore not included in a single student's Subject Access Request
export and are not deleted by single-user erasure. They are deleted when Moodle
processes context-level erasure for the activity.

Gradebook rows for this activity are stored in Moodle core gradebook tables.
VideoTrack updates and deletes its grade item as part of activity lifecycle
operations, but privacy export and erasure for individual gradebook records are
handled by Moodle core's Privacy API provider.


## Version 1.2.21 clarification

The browser `sessionStorage` value used to remember whether the personal-notes
panel is collapsed is a UI preference scoped to the current course module. It is
not sent to the server and is not used to identify a person across activities or
browser sessions.

When configuring a retention period of `0` days, site administrators should treat
it as unlimited automated retention: the scheduled cleanup task does not
anonymise records solely because of age. Moodle Privacy API erasure requests and
context deletion still permanently remove the relevant VideoTrack records and
plugin-owned files as described above.

## Unlimited retention notice

If the retention period is configured as 0 days, VideoTrack keeps tracking data until it is removed by Moodle privacy tools or by an administrator. Sites should document this policy for students and teachers before enabling long-term tracking. Administrators should avoid this setting unless there is a documented lawful basis, a published retention notice, and a periodic manual review process. Unlimited retention should also be recorded in local audit or governance documentation so reviewers can verify why automated cleanup is disabled.


## VideoTrack retention default

The default retention period is 730 days for tracking data, notes and reactions. Administrators may set `retentionperioddays` to any non-negative integer; `0` means unlimited retention and the settings page displays a GDPR/privacy warning when it is active.

## Configurable CSV exports

VideoTrack CSV exports can include configured course, activity and user identity fields. Site administrators define defaults and teachers may override them per activity. Only identity fields and custom profile fields visible to the exporting user in the activity context are offered and loaded. Exports containing individual reactions or personal notes require an explicit confirmation and are recorded as Moodle events. Video timestamps are exported as `MM:SS` or `HH:MM:SS`; the separator can be configured as comma or semicolon.


## Optional Forum posting

When enabled by a teacher, VideoTrack opens a separate Forum composer with a timestamped replay link. VideoTrack does not store the post subject, message, author or discussion identifier. The published content is controlled, retained and exported by `mod_forum`; private VideoTrack notes are not copied.

## Aggregate analytics and reaction clusters

Instance analytics use saved viewing segments only for heatmap, retention and viewing-time metrics. Reaction clusters are evaluated separately and are displayed only when the configured number of distinct students contributed to the same privacy-safe cluster. This separation prevents the absence or suppression of viewing segments from hiding compliant reaction aggregates. The reaction-cluster table contains no student names and never includes private note text.

## Cross-course same-video analytics

The optional cross-course Analytics filter performs a read-only aggregation over activities that use the same technical video. It includes an activity only after checking `mod/videotrack:viewreport` in that activity’s module context. Course-group restrictions are resolved independently for every included activity; groups from different courses are never treated as a shared group. The filter does not reveal inaccessible course names or activity names.

YouTube and Vimeo activities are matched by provider video id. Uploaded files are matched by Moodle content hash rather than filename. The same Moodle user id is merged across included activities before distinct-user counts and the configured privacy threshold are calculated. No student identity or private note text is rendered. Reaction events are grouped by their stored reaction key and remain subject to the distinct-user privacy threshold. The filter is temporary, is not persisted and creates no new personal-data table or field.

## Personal bookmarks

When enabled, a bookmark stores the user id, activity identifiers, a private label, the watched video timestamp, playback rate and creation/modification times. Labels and timestamps are visible and exportable only by the owner. Teacher reports and analytics may show privacy-safe aggregate counts of bookmark events and distinct bookmark users, but never the individual label or timestamp. Bookmarks are handled by Moodle Privacy API export and erasure together with other VideoTrack user data.

### Bookmark analytics boundary

When bookmarks are enabled, teacher reports and Analytics may display only aggregate bookmark event counts and distinct-user counts after capability, group and minimum-user filtering. The Analytics page displays explicit zero totals when no bookmarks exist and masks exact values below the configured threshold. Labels, owner lists and individual video timestamps are never included in teacher output.


## Integrity and visibility indicators

When enabled for an activity, VideoTrack may store bounded diagnostic signals about playback conditions: blocked forward seek, hidden tab, browser-window focus loss, player mostly outside the viewport, an HTML5 Picture-in-Picture event, random attention pause, unauthorised rate change, missing provider callback or inconsistent tracking movement. Each row stores activity/user/session identifiers, the signal type, approximate video time and creation time. VideoTrack does not collect webcam, microphone, biometric, screen-capture, key-logging, free-text behavioural or other-tab content.

Focus-loss and Picture-in-Picture controls are best-effort browser mechanisms. The default accessibility-oriented policy pauses only when the video tab becomes hidden. A browser-window blur is recorded after a configurable grace period but pauses playback only when the site administrator selects the strict policy. Focus loss can have legitimate causes and external providers/extensions may prevent absolute Picture-in-Picture blocking. Signals are diagnostic indicators, not direct measurements of attention or conclusive evidence of misconduct. Sites must not use a signal as the sole basis for automatic grading, completion, discipline or access decisions. Aggregate teacher analytics apply the configured minimum-user privacy threshold. Privacy API export/erasure, retention anonymisation, backup/restore and activity/course reset cover the indicator table.

## Learner acknowledgements

When enabled by a teacher, VideoTrack stores an explicit acknowledgement with the user id, activity and course-module ids, a non-reversible hash of the statement version, the activity modification timestamp, the unique viewed seconds and percentage at confirmation time, and the confirmation timestamp. The full statement is not duplicated in the acknowledgement record. Confirmations are exported and erased through Moodle Privacy API, included in backups only with user data and deleted when the configured retention period expires. Teacher Analytics exposes only current-version aggregate counts and average progress, subject to the configured minimum-user privacy threshold; individual dates and progress remain in the per-student report.
