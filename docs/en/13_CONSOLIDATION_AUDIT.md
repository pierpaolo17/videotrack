# Consolidation audit — VideoTrack 1.6.22

## Purpose

The release consolidates all work before the final roadmap step. It does not add a teaching feature; it aligns implementation, tests, privacy, accessibility, translations and documentation.

## Defects corrected

- Updated a stale Analytics export test from 12 to the real 15-column acknowledgement schema and supplied the missing activity-count fixture values.
- Removed three PHPCS class-closing blank-line errors.
- Preserved provider transcript and chapter settings during caption normalisation; YouTube and Vimeo no longer lose dedicated timed-text switches.
- Removed a duplicated player-width assignment and refreshed stale reset/delete comments.
- Reset the Privacy API segment buffer after every 500-row chunk, preventing duplicate exports and unbounded buffer growth.
- Added explicit retention counts for deleted expired acknowledgements and aligned reset labels with every data family actually removed.
- Centralised the per-student report reset through the shared deletion callback.
- Removed `aria-hidden`/presentation semantics from the poster overlay so its Play button remains exposed to assistive technology.
- Updated misleading XMLDB comments for timed text and focus policy.
- Removed the orphan `amd/build/core/tracker/tracker.min.js.map`, restoring one-to-one source/build/map parity.
- Replaced the mixed release-log root README with separate current English and Italian overviews.
- Replaced the mixed/stale privacy document with current English and Italian summaries.
- Translated copied-English operational strings across German, Spanish, French, Hindi, Italian, Polish and Portuguese while preserving placeholders.
- Regenerated file/function/data inventories and isolated historical engineering notes under `archive/`.

## Baseline evidence

The maintainer’s 1.6.21 run completed Grunt AMD, but PHPUnit had one failure (expected 12 columns versus the actual 15) and PHPCS had three fixable errors. These are treated as release defects and are corrected here.

## Validation boundary

Static checks and patch-application checks are recorded in the release report. PHPUnit, PHPCS, browser, real database upgrade and backup/restore for 1.6.22 must be run in the maintainer’s Moodle environment before the consolidation is considered fully validated.
