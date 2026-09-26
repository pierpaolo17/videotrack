# Architecture

## Layers

1. **Moodle entry points** — `view.php`, report pages, forms and lifecycle callbacks perform access checks and prepare server-owned state.
2. **Domain services** — classes under `classes/local/` implement tracking, analytics, scope resolution, privacy-safe export, timed text, integrity and acknowledgement logic.
3. **External services** — `classes/external/` expose nine AJAX write functions through `db/services.php`; shared validation is centralised in `external\helper`.
4. **Player adapters** — `html5_player.js`, `player.js` (YouTube) and `vimeo_player.js` translate provider callbacks into the shared tracking/study-tool contract.
5. **Shared AMD core** — API transport/retry, tracker lifecycle, interval state, reactions, notes, bookmarks, transcript, acknowledgement, focus guard, status and UI modules.
6. **Persistence** — seven XMLDB tables plus Moodle File API areas and the core gradebook.
7. **Reporting** — per-student report, course dashboard, teacher dashboard and instance/cross-course Analytics with data-format exports.

## Form validation boundary

`mod_form.php` retains validation that depends on draft files, database state or course context. Pure submitted-value
policy belongs to `classes/local/form_validation.php`. Its scalar aggregator preserves error order while delegating
completion percentage, bounded integer fields, reaction-rule dependencies and optional preset JSON to focused
private validators. Custom completion enablement similarly combines three explicit predicates: percentage requires
a duration and positive percentage; reaction rules require reactions to be enabled; acknowledgement requires both
the feature and its completion flag. Suffixed Moodle completion fields fall back to their unsuffixed values.

## Media input parsing boundary

Server-side YouTube and Vimeo URL handling starts in `locallib.php` with one provider-neutral HTTPS normaliser.
It rejects line breaks, missing hosts and non-HTTPS schemes, then canonicalises host, path and query representation.
Provider extractors apply their own explicit host and path allowlists only after that step. YouTube identifiers pass
through a dedicated scalar/length/alphabet validator, including values decoded from query strings. Video timestamps
accept numeric seconds or an anchored two/three-component colon grammar; only minute and second components are
bounded to 0–59. Report filters remain stricter and require the colon form before using the shared timestamp parser.

## Configuration and reaction presentation helpers

Procedural helpers in `locallib.php` expose stable public contracts and delegate independent transformations to
named stages. Playback-speed policy separates parsing, cap selection, filtering and the mandatory `1.0` guarantee.
TinyMCE emoji loading separates file access from source parsing; unusable or HTML-image entries never enter the
catalogue and an empty result uses the local fallback. The reaction picker composes independent header, type-tab and
catalogue-body renderers. All markup continues through `html_writer`, so splitting presentation code does not create
a raw-HTML input path.

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

Ordinary presentation and participation code reads active reaction definitions through
`videotrack_get_reactions()`. Lifecycle operations that must preserve historical event references use the explicit
`videotrack_get_all_reactions()` contract, which also returns soft-deleted definitions. The two scopes are separate
APIs rather than a boolean mode on one function, so callers state their data-retention intent directly.

Timed-text storage likewise has explicit scopes. The standard `timed_text` lookups read only the dedicated
`transcripts` and `chapters` areas. Separately named compatibility methods first prefer those canonical resources
and then fall back to the historical `subtitles` area for migrated uploaded-video activities. `view.php` selects
that compatibility contract only when the legacy caption source exists.

## Identity and scope

Course-module context and Moodle capabilities are authoritative. `mod/videotrack:participate` explicitly identifies users whose learner telemetry and personal study tools may be written; report access is independent. Group visibility is resolved with the effective activity group mode. Cross-course Analytics recomputes participation, report and group scope for every included activity and identifies the same technical video by provider id, uploaded-file content hash or a canonical external URL. External URL identity has one public contract; private authority, path and query normalisers make its default-port, separator, fragment and query-order rules explicit.

## Playback ledger trust boundary

A provider PLAY event opens a zero-credit server handshake through `mod_videotrack_start_playback`. Segment requests then earn cumulative credit only from elapsed server time at an allowed playback rate. Each request has a persistent idempotency identifier protected by a unique database index; retries reuse the stored result instead of inserting another raw segment. `intervaljson` remains a bounded transport representation, while exact unique coverage is recalculated from validated raw rows when the cap is reached.

## Privacy architecture

Collection is feature-gated. Instance Analytics expose exact aggregates only to viewers with individual-report access inside Moodle capability/group scope; aggregate-only viewers retain the configured minimum-user threshold. The separate course and teacher dashboards keep their existing capability/privacy behaviour pending dedicated review. Bookmark labels are owner-only. Personal note text is visible to the owner and may be visible/exportable to authorised report viewers when notes are enabled; note text is excluded from aggregate Analytics. Privacy export streams large collections in bounded chunks. Erasure and scheduled retention delete personal rows rather than retaining deterministic pseudonyms. `videotrack_state` is derived personal data and is rebuilt only from retained server-validated segments and retained completion inputs. User-data backup excludes expired rows and derived state; restore reapplies the destination retention policy and rebuilds state after Moodle completion restore. Activity configuration files are removed by the normal activity-deletion lifecycle, not by learner-data erasure.

## Accessibility architecture

Controls have keyboard operation and accessible names; dynamic status uses live regions; reduced-motion and forced-colour modes are supported; the poster overlay exposes its Play control to assistive technology. The default focus policy uses document visibility rather than raw window blur for automatic pausing. VTT chapter buttons share the explicit focus-visible contract and retain an active-state outline in forced-colour mode.

## Generated assets

`amd/src` is canonical. `amd/build` and source maps are generated artifacts and must only change after a real Moodle build.
