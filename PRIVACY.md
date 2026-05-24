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

Le esportazioni CSV dei report possono contenere dati personali degli studenti e devono essere usate solo da utenti con capability di reportistica nel rispetto dell'informativa locale e delle policy di conservazione dell'istituzione.


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
