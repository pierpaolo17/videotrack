# Architecture

## Layers

1. **Moodle entry points** — `view.php`, report pages, forms and lifecycle callbacks perform access checks and prepare server-owned state.
2. **Domain services** — classes under `classes/local/` implement tracking, analytics, scope resolution, privacy-safe export, timed text, integrity and acknowledgement logic.
3. **External services** — `classes/external/` expose eight AJAX write functions through `db/services.php`; shared validation is centralised in `external\helper`.
4. **Player adapters** — `html5_player.js`, `player.js` (YouTube) and `vimeo_player.js` translate provider callbacks into the shared tracking/study-tool contract.
5. **Shared AMD core** — API transport/retry, tracker lifecycle, interval state, reactions, notes, bookmarks, transcript, acknowledgement, focus guard, status and UI modules.
6. **Persistence** — seven XMLDB tables plus Moodle File API areas and the core gradebook.
7. **Reporting** — per-student report, course dashboard, teacher dashboard and instance/cross-course Analytics with data-format exports.

## Player contract

Each adapter must provide reliable current time, duration, play/pause, seek, rate and end callbacks. Shared modules never assume identical provider behaviour. Programmatic resume, replay and blocked-seek correction are distinguished from user seek. YouTube and Vimeo SDK limitations are handled explicitly.

## Data model

- `videotrack`: activity configuration.
- `videotrack_seg`: append-only watched segments.
- `videotrack_state`: merged intervals, progress and completion per user/activity.
- `videotrack_react`: configured reaction definitions.
- `videotrack_reactev`: standard reactions, personal notes and private bookmarks, distinguished by `notetype`.
- `videotrack_integrity`: bounded diagnostic signals.
- `videotrack_acknowledge`: versioned acknowledgement confirmations and progress snapshots.

## Identity and scope

Course-module context and Moodle capabilities are authoritative. Group visibility is resolved with the effective activity group mode. Cross-course Analytics recomputes capability and group scope for every included activity and identifies the same technical video by provider id or uploaded-file content hash.

## Privacy architecture

Collection is feature-gated. Teacher Analytics consume aggregates and apply independent minimum-user thresholds. Bookmark labels are owner-only. Personal note text is visible to the owner and may be visible/exportable to authorised report viewers when notes are enabled; note text is excluded from aggregate Analytics. Privacy export streams large collections in bounded chunks. Erasure, reset, context deletion, retention and backup/restore cover every user-data table.

## Accessibility architecture

Controls have keyboard operation and accessible names; dynamic status uses live regions; reduced-motion and forced-colour modes are supported; the poster overlay exposes its Play control to assistive technology. The default focus policy uses document visibility rather than raw window blur for automatic pausing.

## Generated assets

`amd/src` is canonical. `amd/build` and source maps are generated artifacts and must only change after a real Moodle build.
