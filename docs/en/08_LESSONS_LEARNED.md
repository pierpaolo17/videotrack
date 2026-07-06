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
