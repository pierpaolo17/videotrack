# Security and trust boundaries

VideoTrack treats the browser and public provider SDKs as untrusted clients. They may request operations and
report state, but only authenticated Moodle services and server-side evidence can advance authoritative progress.

## Request boundary

Every AJAX write is declared in `db/services.php` and implemented in `classes/external/`. The common sequence is:

1. validate external parameters and normalise scalar values;
2. load the course module as `videotrack` and its module context;
3. require login and `mod/videotrack:participate`;
4. verify activity, course, user and ownership scope;
5. acquire a scoped lock where concurrent writes could race;
6. apply domain validation and write with Moodle DML;
7. return only the declared response shape.

CSRF/session protection is provided by Moodle's authenticated AJAX framework. Direct page actions additionally
use `require_sesskey()` where appropriate.

## Playback authority

`start_playback` opens a credit window bound to the current browser playback session. `save_segment` compares
requested media time with elapsed server time, allowed playback rate, current session and existing credit.
Accepted terminal events close the window. Stale, cross-session or over-budget rows cannot increase unique
coverage even if retained for audit.

The server does not infer attention from focus, maximum position or client percentages. Allowed navigation is
not proof that skipped media was watched.

## Interaction timestamps

Reaction, note, bookmark and Forum timestamps must fall within trusted watched evidence or another explicitly
permitted server rule. Clients flush pending progress before an interaction and use the saved server endpoint,
but server validation remains mandatory.

## Permissions and ownership

- participation is independent from report access;
- personal delete actions can affect only the current user's records;
- aggregate and individual report/export permissions are separate;
- course/module context and Moodle group/cohort scope constrain reads;
- only compatible same-course Forums can be linked;
- teachers need dedicated override capabilities to bypass site player/completion defaults.

## Data minimisation and output safety

Output uses Moodle escaping/formatting APIs. File delivery uses Moodle file areas and contexts. SQL uses DML
parameters. Aggregate Analytics excludes personal note/bookmark text and applies privacy masking unless the
viewer has individual access. Integrity events are bounded and diagnostic.

## External limits

YouTube/Vimeo availability, cookies, consent and iframe behaviour are external. UI restrictions such as
Picture-in-Picture prevention are best effort. Security decisions therefore rely on server-side credit and Moodle
permissions, not on assuming complete control of an external player.
