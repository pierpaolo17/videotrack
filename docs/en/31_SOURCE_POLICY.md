# Site video sources and optional integrations

VideoTrack **1.7.264** (`2026111901`).

## Configure implemented sources

Open **Site administration → Plugins → Activity modules → VideoTrack → Video sources**.
YouTube, Vimeo and Upload (the local HTML5 player) each have an independent enable
checkbox, enabled by default. Missing settings on an upgraded site also mean enabled.
The description shows how many activities currently use that source, including
disabled activities. The count uses one grouped query on the administration page;
ordinary playback does not count activities or contact a provider for this policy.

Only enabled sources appear when creating an activity. If YouTube is disabled,
the form defaults to the first enabled alternative. If all sources are disabled,
the empty selector explains that an administrator must enable one; submission
cannot fall back silently to YouTube.

An existing disabled source remains visible with a disabled label on the editing
form, preserving the original selection and media identity. To save, the teacher
must explicitly select an enabled source or an administrator must re-enable the
original source. Direct create/update callbacks enforce the same policy before
activity, grade or file writes; a forged form cannot bypass it.

## Effect of disabling a source

- Existing activity playback is blocked with a translated message before viewed
  completion, acknowledgement processing or player output.
- New playback sessions, segments, reactions, notes, bookmarks and integrity
  events are rejected at the authenticated external-function boundary. This also
  covers submissions from a tab opened before the source was disabled.
- Direct local video and legacy subtitle URLs obey the Upload setting.
- The toggle does not delete activity rows, files, learner progress, interactions,
  acknowledgements or grades. Reports, exports, Privacy API, retention and
  authorised owner deletion remain governed by their existing access rules.
- Re-enabling restores access to the same activity, video identity and progress.
  Selecting a different source/video is an explicit media change.
- Backup/restore does not enable a source at site level. Restored activities are
  subject to the destination site's source policy.

The validator's JSON critical configuration includes the three effective boolean
values, including the default enabled state when no setting has been stored.
An already downloaded file or an external provider's own website remains under
its existing access and caching rules.

The policy takes effect on subsequent page, file and server requests. Media already
loaded in an open browser tab is not remotely stopped by this setting.

The corrected unavailable view uses a normal warning with a return-to-course
button, without a Moodle fatal-error page or player initialisation. Learner data
access for owner deletion remains separate from enabled-source playback access.
See [initial CI findings and correction](09_DOCUMENTATION_AUDIT.md).

## Verification and release status

The candidate adds 15 PHPUnit regression methods and four Behat scenarios. They
cover defaults, independent switches, malformed configuration, filtered choices,
disabled edit values, all-off behaviour, direct callback rejection, explicit
source changes, stale playback requests, retained files/state and owner erasure.
Native candidate CI, main CI, actual-package identity, server checks and strict
validation are pending. Source/patch checks are not native PHP execution.

## Optional Zoom repository: architectural assessment

A separate repository plugin is a useful candidate for sharing one Zoom connection
and a recording picker across Moodle activities. Evaluate existing integrations
before creating another plugin. The proposed boundary is:

| Component | Responsibility |
|---|---|
| Optional Zoom repository | Account configuration, scoped recording selection, stable meeting-instance/file identity, metadata and availability resolution. |
| VideoTrack Zoom adapter | Player initialisation, current position and playback events, reactions, watched intervals and supported controls. |

The repository should expose a small documented server-side interface. VideoTrack
must not read another plugin's private token storage, copy credentials or make
the repository a mandatory dependency for HTML5/YouTube/Vimeo installations.
A future provider is selectable only when its optional dependency, configuration,
permissions and supported playback contract are ready.

Moodle's reference-file mechanisms may cache or serve remote bytes locally.
Any reuse must preserve the chosen provider-to-browser media path, rather than
silently importing or proxying the video. Stable resource ids must survive URL
renewal; API credentials stay in the backend and learner access is checked
separately from the administrator's access.

For the internal Zoom account model, assess Server-to-Server OAuth and signed
recording lifecycle webhooks. Their credential and token lifecycle differs from
Moodle's existing delegated Microsoft/Google OAuth flows. A repository does not
establish native player timing/control support: that remains a separate feasibility
test. No Zoom plugin, cloud player, credentials or webhook endpoint is delivered
in 1.7.261. The Graph/OAuth service from 1.7.259 is retained; the new
[administrator web check](32_MICROSOFT_WEB_CHECK.md) reuses it.

## Primary references

- [Moodle OAuth 2 services](https://docs.moodle.org/500/en/OAuth2_Services)
- [Moodle repository API](https://moodledev.io/docs/5.2/apis/plugintypes/repository)
- [Zoom Server-to-Server OAuth](https://developers.zoom.us/docs/internal-apps/s2s-oauth/)
- [Zoom webhooks](https://developers.zoom.us/docs/api/webhooks/)
