# Lessons learned

## Patches and baseline

The baseline is always the real ZIP uploaded by the maintainer. Do not assume that a previous patch was applied as expected.

## AJAX contracts

Every field declared in `execute_returns()` must always be returned. `invalidresponse` errors can occur even when the database has already been changed.

## Player runtime

HTML5, YouTube and Vimeo share parts of rendering, but seek/play/pause is player-specific. Vimeo requires particular care for interrupted promises (`PlayInterrupted`).

## Immediate reaction rendering

The “My reactions” table must be updated using server-returned data or, as fallback, the reaction button `data-*` attributes. The HTML after page refresh is the reference for correct DOM.

## Anti-duplication controls

The control must be server-side. The UI can remove optimistic rows when the server returns `reactioneventid = 0`, but it must not be the only defence.

## Bookmark privacy boundary

A private study tool can still contribute to aggregate teaching analytics, but the privacy boundary must be explicit: teacher output may contain only threshold-protected event and distinct-user counts. Labels, individual timestamps and owner lists must remain outside teacher queries, charts and exports.

## Version compliance acknowledgements by content, not by a mutable flag

A confirmation must be bound to the exact teacher-authored statement. Store a non-reversible content/version hash with the timestamp, keep confirmation idempotent and never treat a confirmation for old text as satisfying the current statement. Do not duplicate the full statement in every user record.
