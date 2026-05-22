# VideoTrack privacy notes

VideoTrack stores per-user viewing segments, personal notes and reactions so that Moodle can calculate completion, grade-related progress and learning analytics for the activity.

When Moodle privacy erasure is requested for a user, VideoTrack permanently deletes that user's tracking segments, aggregate state, reactions and personal notes for the selected activity context. The plugin does not keep pseudonymised rows as a response to Moodle Privacy API erasure requests.

Site administrators can configure the retention period in the activity settings. A value of `0` keeps data indefinitely until a Moodle privacy erasure or context deletion request is processed. Automated retention cleanup may anonymise old tracking records to preserve aggregate analytics; this is separate from user-initiated erasure and should be covered by the site's privacy notice and lawful-basis assessment.
