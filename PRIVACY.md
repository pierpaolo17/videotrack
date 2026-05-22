# VideoTrack privacy notes

VideoTrack stores per-user viewing segments, personal notes and reactions so that Moodle can calculate completion, grade-related progress and learning analytics for the activity.

When Moodle privacy erasure is requested for a user, VideoTrack removes the direct link to the real user and anonymises user-authored text while preserving aggregate analytics needed for course reporting. This means teachers can still see anonymised aggregate activity, but not the original learner identity or personal note text.

Site administrators can configure the retention period in the activity settings. A value of `0` keeps data indefinitely until a Moodle privacy erasure or context deletion request is processed.
