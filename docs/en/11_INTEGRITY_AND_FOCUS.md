# Integrity indicators and learner focus controls

## Scope and safeguards

Release 1.6.17 adds optional, per-activity focus controls and bounded diagnostic indicators. Every option is disabled by default. The feature is designed to support review of unusual playback conditions; it is not a surveillance system and does not establish whether a learner was attentive or acted dishonestly.

VideoTrack does not use a webcam, microphone, eye tracking, biometrics, screen capture, key logging, content from other tabs or free-text behavioural notes. A recorded signal contains only the activity identifiers, Moodle user id, playback-session id, signal type, approximate video time and creation time.

Signals must be interpreted with the learning context. They must not be used as conclusive evidence or as the sole basis for an automatic grade, completion change, disciplinary action or access restriction.

## Instance settings

The activity form exposes four independent options:

- **Record integrity indicators**: stores the diagnostic signal types listed below.
- **Pause when the page loses focus**: pauses playback when the document becomes hidden or the browser window loses focus.
- **Prevent Picture-in-Picture**: applies a best-effort browser/provider policy.
- **Enable random attention pauses**: while playback is active, pauses after a random delay of 301–1799 seconds from the latest learner interaction. The learner resumes manually.

Window focus can be lost for legitimate reasons, including browser chrome, password-manager prompts, accessibility tools and operating-system dialogs. Picture-in-Picture cannot be blocked absolutely when a browser extension or third-party provider ignores iframe/media policy controls.

## Signals

`videotrack_integrity.eventtype` is restricted to this allowlist:

- `forwardseek`: a disallowed forward seek was blocked;
- `tabhidden`: the video document became hidden during playback;
- `windowblur`: the browser window lost focus during playback;
- `outofviewport`: less than 25% of the player was visible while playing;
- `pipattempt`: the HTML5 media element entered Picture-in-Picture and VideoTrack attempted to exit it;
- `randompause`: an enabled random attention pause fired;
- `ratechange`: the player attempted to exceed the configured playback-rate policy;
- `callbackmissing`: expected progress callbacks stopped while the player appeared to be playing;
- `trackinggap`: video-time movement was inconsistent with wall-clock progress and recent learner actions.

Provider behaviour differs. HTML5 can expose a direct Picture-in-Picture event; YouTube and Vimeo are protected by removing iframe permission where possible, but the provider may not expose an attempted entry event.

## Runtime flow

```text
player action / browser visibility signal
-> core/player/focus_guard
-> optional provider pause
-> mod_videotrack_save_integrity_event
-> capability + sesskey + allowlist + debounce validation
-> videotrack_integrity
```

The shared `focus_guard` controller is instantiated separately by HTML5, YouTube and Vimeo. Player-specific pause, current-time and status callbacks remain distinct. Any click or keyboard activation in the player shell, together with play, pause, seek, rewind, fast-forward, reaction, note and bookmark controls, resets the random-pause deadline.

The external service applies a second same-user/session/type debounce window before insertion. This bounds duplicate browser callbacks without pretending that the signal is unique or definitive.

## Reports and analytics

Teacher reports can show a per-student total indicator count when recording is enabled. The cumulative report and the Analytics tab show counts by signal type. Analytics and cumulative aggregates apply `analyticsminusers` independently to every type; exact event and distinct-student totals are hidden below the threshold.

Reports deliberately omit browser details, URLs, free text and data from other tabs. The report introduction states that the values are diagnostic and cannot be treated as proof of misconduct.

## Privacy, retention and lifecycle

The table is declared in the Privacy API. Subject-access exports include localized signal types and formatted video time. User/context erasure deletes the relevant rows. Scheduled retention anonymises user/session/video-time fields while retaining only non-personal aggregate signal types. Activity deletion, student-progress reset and course reset remove matching rows.

Backup and restore include the new instance settings. Integrity rows are included only when user data are requested, user ids are remapped, and unknown signal types are discarded during restore.

## Required regression matrix

- settings disabled: no focus control and no stored rows;
- recording enabled without focus controls: signals can be stored but playback is unchanged;
- focus-loss pause on HTML5, YouTube and Vimeo, including return and manual resume;
- legitimate browser-dialog focus loss documented as a possible signal;
- HTML5 Picture-in-Picture entry exits where the browser supports the API;
- YouTube/Vimeo iframe permission removed without breaking playback or fullscreen;
- random delay always remains within 301–1799 seconds and resets after player interactions;
- forward seek, rate policy, tracking, resume, replay, notes, reactions and bookmarks remain functional;
- group/capability scope and `analyticsminusers` suppression in teacher reports;
- Privacy API export/erasure, retention, backup/restore and all reset paths;
- PHPUnit, PHPCS Moodle + Extra and real `grunt amd` with generated files included.
