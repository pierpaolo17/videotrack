# Server-authoritative playback ledger in 1.6.32

## Purpose

Release 1.6.32 closes two trust-boundary weaknesses in watched-segment persistence: the first segment no longer receives unearned playback credit, and a transient transport retry can no longer insert the same logical segment twice.

## Playback handshake

Before a player opens a tracked segment it calls `mod_videotrack_start_playback`. The service:

- validates the Moodle session key, activity context and `mod/videotrack:participate` capability;
- stores a zero-length `playstart` ledger row with `servervalidated = 0`;
- records the current server timestamp in milliseconds;
- grants no watched time and no completion progress.

A provider PLAY event starts tracking only after this handshake succeeds. Pause/end transitions invalidate a pending client continuation, so a late response cannot open a stale segment. The handshake has its own lifecycle serial and is deliberately independent from the shared AJAX response scope used by other controls. Duplicate Vimeo `play` notifications received while a segment is already open are ignored by the ledger, so they cannot reset the server-credit window.

## Server-time credit

Every candidate watched segment is charged against cumulative server-authorised credit:

1. elapsed server time since the latest handshake or request is measured;
2. elapsed time is capped to one heartbeat window plus the existing bounded network allowance;
3. the validated activity playback rate converts wall-clock time into maximum video-time credit;
4. the candidate is accepted only when cumulative credited video time remains within that cumulative budget, with strictly less than one second of total provider/server clock drift;
5. tolerated drift remains debt across requests and new handshakes; positive unused headroom is discarded, so pause/replay and rejected requests cannot convert tolerance into repeatable credit;
6. a segment request cannot implicitly open a credit window: only the explicit playback handshake can do so.

The teacher-saved duration, allowed playback speeds and forward-seek policy remain authoritative. Client wall-clock and duration values remain diagnostic only.

## Idempotent segment requests

Each handshake and segment request carries a 32-character random request identifier generated once before the shared retry layer runs. `videotrack_seg.requestid` is protected by a unique index over activity, user and request identifier.

When a retry reuses the same identifier:

- an identical persisted payload returns the current persisted result without inserting another row;
- a different payload using the same identifier is rejected;
- completion and Moodle events are not emitted twice;
- raw-seconds and review Analytics are not inflated by a lost response followed by a retry.

Legacy rows receive deterministic `legacy…` identifiers during upgrade. Restore preserves valid identifiers and generates deterministic `restore…` identifiers for older backups. Playback-credit counters are still excluded from backup and reset on restore.

## Interval cap and monotonic progress

`intervaljson` remains bounded to 500 stored intervals for payload and database safety. When the cap is reached, exact unique coverage is recalculated from all `servervalidated = 1` raw segments before the compact interval list is persisted. `uniquecoveredseconds` is monotonic and cannot decrease merely because short fragments are omitted from the compact representation.

## Data protection

The request identifier is an operational idempotency token, not an authentication credential. It is included in Moodle Privacy API metadata/export and user-data backup because it belongs to an identifiable playback record. It is never used to authorise a request; Moodle login, sesskey, context and capability checks remain authoritative.

## Validation expectations

A release containing this contract must verify:

- new installation and upgrade from 1.6.31;
- first segment without handshake is rejected;
- a short video cannot gain initial credit;
- validated maximum playback rate is bounded by real server elapsed time;
- retrying an accepted or rejected request does not add rows or progress;
- more than 500 disjoint segments preserve exact cumulative coverage;
- all three adapters wait for the handshake and cancel stale handshakes;
- PHP, XMLDB, Privacy API, backup/restore, PHPUnit, PHPCS and real Moodle AMD build remain aligned.
