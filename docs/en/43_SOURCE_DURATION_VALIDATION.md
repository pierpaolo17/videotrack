# Source duration and every-save validation

VideoTrack **1.7.273** (`2026112501`) checks the configured duration against the
selected source when opening the settings and again on Save and display, Save and return to course, and Cancel.
This applies when editing only reactions, notes, bookmarks or other settings,
including a duplicated activity whose video link changes. A positive existing
duration no longer skips detection. A completed earlier result is never reused
as the save-time check.

## Policy

| Source metadata | Configured duration | Result |
|---|---|---|
| Positive valid duration | Greater than source | Show both durations, block this save and return to the duration field. |
| Positive valid duration | Equal or shorter | Preserve the configured value and run normal Moodle validation/submission. |
| Unavailable, zero, non-finite or timed out | Any otherwise valid manual value | Warn and offer manual saving or cancellation; do not replace or reset the value. |
| Different source/input while a check is pending | Any | Discard the stale result and require a current check before saving. |

Comparison uses the saved field's millisecond precision, without an arbitrary
seconds/percentage allowance. The rule is configured <= detected. A deliberately
shorter duration remains allowed, such as 5280 seconds for a longer demo video.
Zero retains its existing meaning; video-end acknowledgement still requires a
positive duration. Cancel also checks and shows any mismatch/unavailability warning, then discards
changes without saving; it is never blocked by an invalid duration. Repeat-reaction
fields and other intermediate form actions remain immediate. A double save while checking cannot create two posts.

YouTube uses the existing IFrame getDuration metadata probe, Vimeo its existing
Player SDK getDuration promise, and uploaded HTML5 media loadedmetadata. These are
teacher-form probes, not learner playback. Temporary players are destroyed after
settlement; no video is imported and no new server-side streaming is introduced.
An unavailable provider is retried on the next save. Existing bounded loader and
metadata timeouts remain; absence is never interpreted as a known zero-length video.

## Form and persistence boundary

The asynchronous capture listener checks both save buttons and Cancel, then
uses requestSubmit with the original button. Moodle's ordinary client/server form
validation, submit-button values and editors still participate. On unavailability,
the warning's explicit manual-save choice applies only to unchanged source and
duration inputs. It cannot approve a later changed form.

The transient durationprobe JSON carries source, current URL/draft item and detected
seconds to PHP form validation. PHP ignores mismatched, invalid or oversized reports
and also rejects an excessive configured duration for matching reports. This is
browser-reported form metadata, not an authenticated server API response or a
security assertion. No JavaScript/provider result means the documented manual path;
without JavaScript automatic verification is unavailable. No API credential is added.

The probe is not a database column, activity setting, backup field or learner export.
The patch adds no schema migration, rewrites no historical watching evidence and
does not automatically change existing durations or recalculate all activities.
For an already incorrect saved duration, edit and correct it and use the existing
completion recalculation tool as appropriate; retained segments remain available.
The accepted 272 replay/tracking fix and temporary historical-recovery option remain.

## Evidence and remaining gates

Ten deterministic Node groups exercise the actual AMD source: pre-existing values,
fresh unrelated saves, duplicated/new links, all three providers, equality/shorter
values, unavailable/manual/retry, stale pending results, duplicate submission,
manual edits while checking, timeouts, cancel/repeat/Enter and millisecond precision.
They fail all ten groups on accepted 272 as expected. Five PHPUnit methods cover the
source-bound PHP policy; existing form-config coverage includes the new transient
field and translated configuration. Existing 82 Behat scenarios are retained; two settings-save steps use deterministic source metadata.

The maintainer's actual Moodle 5.0 Grunt archive is verified, SHA256
`b5c4292a4d9362e4ec95ad3e009273de75d1503c6853c67272351926cb650fb3`.
It records Node 22.23.4 / npm 10.9.9 and a successful npx --no-install grunt amd.
All 48 source and 96 build/map hashes match; only form/duration.js and its two native
outputs change. The exact test fixture and log digest match. All 10 source and 10
compiled groups pass in the native log and again after local incorporation.
The runner reports exact original-tree restoration; no Moodle bootstrap or DB
command was used. This is maintainer-server output, not GitHub Actions output.

The transient hidden input has an explicit DOM id, asserted by form-definition
coverage, so PHP-rendered markup and the AMD configuration share one identifier.
PHP/PHPCS/PHPUnit, 273 CI/lifecycle/server and live duration-form acceptance remain
pending. No local PHP/Moodle execution or browser/live-provider acceptance is
claimed. The 82 Behat scenarios remain; the two existing settings-save steps use a reserved
sixty-second SDK double and the real submit handler, without live YouTube requests.

Primary references: [YouTube duration](https://developers.google.com/youtube/iframe_api_reference#getDuration),
[Vimeo Player SDK](https://github.com/vimeo/player.js/#getduration-promisenumber-error),
[Moodle Forms API](https://moodledev.io/docs/5.0/apis/subsystems/form).
