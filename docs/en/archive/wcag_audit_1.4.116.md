# Videotrack WCAG Audit - 1.4.116

## Scope

This audit documents the final static WCAG pass after the AMD refactor series up to 1.4.115.

The audit focuses on code-level accessibility signals that can be verified without a browser/screen-reader session. It does not replace manual keyboard, visual, or assistive-technology testing.

## Baseline

- Plugin: mod_videotrack
- Release audited: 1.4.115
- Release documenting the audit: 1.4.116
- Moodle target: 5.0

## Static checks performed

### Tables

All PHP `html_table` instances inspected in:

- `index.php`
- `lib.php`
- `presets.php`
- `report.php`
- `reports_course.php`

Result: every detected table has an explicit caption.

### Status and live regions

Status/error messaging was inspected across PHP and AMD sources. The code uses:

- `role="status"` for non-critical updates
- `role="alert"` for errors or fallback failures
- `aria-live="polite"` or `aria-live="assertive"` according to severity

Result: no static blocker found.

### Form and control descriptions

The following areas were inspected for descriptive labels or descriptions:

- player width setting
- note textarea
- note export confirmation
- interval bar/progress
- reaction buttons
- report grade controls
- hidden activity links

Result: no static blocker found.

### Focus management

The code includes focus restoration or managed focus for:

- confirmation modal fallback
- notes table updates
- player headings/status notifications

Result: no static blocker found, but manual keyboard testing is still required.

### Unsafe HTML insertion

`innerHTML` usage was reviewed in AMD sources. Remaining uses are limited to clearing existing containers before rebuilding content through DOM APIs.

Result: no WCAG/XSS blocker identified from static inspection.

## Manual checks still required

These checks require a real browser and assistive technology session:

1. Keyboard-only playback workflow.
2. Keyboard-only notes save/delete workflow.
3. Keyboard-only reactions workflow.
4. Screen-reader announcement of status messages.
5. Screen-reader announcement of interval/progress updates.
6. Focus restoration after modal cancel and fallback paths.
7. WCAG 2.2 focus appearance and target-size visual checks.

## Conclusion

The static WCAG audit did not identify remaining code-level blockers.

Status: ready for manual WCAG validation before candidate release.

## Release impact

This release is documentation-only and does not change runtime behaviour, tracking, segmentation, notes, reactions, analytics, or resume logic.
