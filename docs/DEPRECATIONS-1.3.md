# Videotrack 1.3 deprecation and compatibility notes

This document records compatibility surfaces that remain intentionally available
while the 1.3 refactor moves shared logic into `amd/src/core/` modules.

## Current compatibility policy

The 1.3 development line keeps the historical Moodle AMD entry points stable:

- `mod_videotrack/player`
- `mod_videotrack/html5_player`
- `mod_videotrack/vimeo_player`

These entry points should continue to behave as thin integration layers. New
shared behaviour should be added to `mod_videotrack/core/*` modules first and
then exposed through a small facade only when an existing caller still needs it.

## Deprecated implementation patterns

Avoid introducing new direct provider-specific logic outside the adapter layer:

- direct YouTube/Vimeo/HTML5 playback commands should go through `core/adapter`
- transient status DOM should go through `core/status`
- segment clamping/open/close behaviour should go through `core/segment` and
  `core/tracker`
- AJAX calls should go through `core/api`
- confirmation modals should go through `core/confirm`

## Release preparation checklist

Before a release candidate, review whether each backwards-compatible facade in
`amd/src/core/player.js` is still required. Removing a facade should only happen
when all internal callers have migrated and the public AMD entry points remain
stable for Moodle pages that already load the plugin.
