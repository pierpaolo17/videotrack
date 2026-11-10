# Accessibility

VideoTrack targets WCAG 2.2 AA within the parts of the interface controlled by the plugin. External-provider
iframes remain partly controlled by YouTube/Vimeo and must be evaluated in the deployment context.

## Keyboard and focus

- all VideoTrack actions use native buttons, links, inputs, summaries or equivalent keyboard semantics;
- focus indicators remain visible in normal and forced-colours modes;
- opening/closing interactive regions preserves a logical focus destination;
- custom player controls expose accessible names and do not rely on colour alone;
- transcript cues, chapters, reactions, notes, bookmarks and acknowledgement are keyboard operable.

The default focus-loss policy pauses only when the document is hidden. Strict visible-window blur is optional and
has a course-group exception for learners who need split view or assistive workflows.

## Structure and status

Headings follow page hierarchy. Personal histories use native `<details>/<summary>`. Progress and transient
messages use appropriate status/live regions without moving focus unexpectedly. The completion enhancement keeps
Moodle's list/listitem semantics and moves only the composite VideoTrack logic label before multi-item lists.

## Reflow, zoom and contrast

Player controls and completion items wrap/stack rather than forcing horizontal overflow. The interface must remain
usable at 400% zoom and a 320 CSS-pixel viewport. Forced-colours rules preserve focus, boundaries and control state.
Text/icon contrast must be checked in Boost and any supported custom theme.

## Timed media

Teachers can provide WebVTT subtitles, searchable transcripts and chapters. VideoTrack does not generate captions
or guarantee their accuracy; content authors must provide synchronised, meaningful text and descriptions required
by their accessibility policy.

## Manual audit checklist

Test as learner and teacher in Moodle 5.0–5.3 where markup differs:

1. Tab/Shift+Tab order, Enter/Space activation and no keyboard trap.
2. Visible, non-obscured focus at 100% and 400% zoom.
3. Reflow at 320 CSS px without two-dimensional scrolling for ordinary content.
4. Windows High Contrast/forced-colours control visibility.
5. Screen-reader names, states, status announcements and completion grouping.
6. HTML5, YouTube and Vimeo controls and timed-text access.
7. Error messages, acknowledgement timing and focus restoration after actions.

Automated tests protect structural contracts but do not replace perception and assistive-technology testing.

## Manual evidence protocol U-020

**Status: OPEN / NOT RUN.** Automated U-007 acceptance is recorded in the [browser exit audit](26_BROWSER_EXIT_AUDIT.md).
This protocol prepares real observation; it contains no completed manual result or accessibility certification.

U-020 remains open and is **deferred at the maintainer’s request (2026-11-10)**. This change executes
or approves no manual case. Independent language work may continue; the final production gate still
requires the manual audit and any resulting fixes.

### Record the tested environment

Use a dedicated course and test accounts. Record the exact plugin release/version, package SHA-256 or commit,
Moodle/PHP versions, theme/version, operating system, browser/version, screen reader/version, date and tester.
Record relevant settings: provider, timed text, keyboard/seek policy, focus policy/exception membership,
attention switch/bounds, acknowledgement mode and completion rules. Use ordinary site/player controls.

Cover learner and teacher views on Moodle 5.0 and 5.3, adding the deployed custom theme when applicable.
Exercise all three configured providers and record any blocked external iframe/consent separately. At least
one actual screen-reader/browser setup is needed for perception checks. Other combinations stay untested
unless recorded; do not extrapolate from the six Chrome CI environments.

### Observation matrix

Repeat applicable rows for each recorded setup. Mark PASS, FAIL, BLOCKED or N/A with a reason and evidence.
The initial status of every row below is **NOT RUN**.

| Id | Action on VideoTrack | Expected observation | Initial status |
|---|---|---|---|
| M01 | Tab/Shift+Tab through player, timed text, interactions and report controls; activate with Enter/Space | Logical order, meaningful names, one intended action and no keyboard trap | NOT RUN |
| M02 | Add/remove a note or bookmark, open/close histories and enter/leave the Forum composer | Focus stays identifiable and returns to a sensible control; saved state and errors remain discoverable | NOT RUN |
| M03 | Listen to a screen reader while changing play/pause, completion, form errors and transient/persistent notices | Names, roles and states agree with the UI; relevant status changes are announced without forced focus movement | NOT RUN |
| M04 | Check ordinary content at 100%/400% zoom and 320 CSS-pixel width | Text/forms wrap without lost controls or ordinary two-axis scrolling; meaningful two-dimensional data tables may keep their own usable scroll area | NOT RUN |
| M05 | Enable operating-system high contrast/forced colours | Focus, boundaries, button states and text remain distinguishable and usable | NOT RUN |
| M06 | Use HTML5 and the configured public YouTube/Vimeo iframe controls, captions, transcript and chapters | Keyboard entry/exit works; supplied captions/transcript match the content; record missing content descriptions and provider restrictions separately | NOT RUN |
| M07 | Test anytime/end-only acknowledgement and an enabled 60–60-second attention interval | Disabled/available states and reasons are understandable; the pause notice is perceivable and manual resume remains operable | NOT RUN |
| M08 | Compare learner, teacher-only and dual-role pages; use a permitted group report and its learner selector | Read-only/enabled states and selected/report data are understandable with keyboard and screen reader | NOT RUN |

For M07 configure the test activity's two optional attention fields to 60 seconds, enable the existing switch
and avoid recognised interactions while it plays for the full minute. Use content/rate long enough not to
finish first. Restore the previous configuration after observation. Do not infer attention or misconduct
from the pause. End-only acknowledgement needs actual validated final-second viewing in this manual course.

The reflow exception for meaningful two-dimensional content does not excuse clipped individual cell text or
inaccessible controls. The status check records what is perceived with the actual assistive setup; DOM roles
or green Behat assertions alone are insufficient. Normative reference: [WCAG 2.2](https://www.w3.org/TR/WCAG22/).
Operational explanations: [Reflow](https://www.w3.org/WAI/WCAG22/Understanding/reflow.html) and
[Status messages](https://www.w3.org/WAI/WCAG22/Understanding/status-messages.html).

### Evidence record and decision

Copy one row per observed result:

| Package/commit | Moodle/theme | OS/browser/screen reader | Provider/role | Case | Result | Actual observation / reproducible steps | Evidence reference |
|---|---|---|---|---|---|---|---|
| To record | To record | To record | To record | M01–M08 | NOT RUN | To record | To record |

For a finding record expected/actual behaviour, impact, exact configuration and a minimal reproduction.
State whether it belongs to the plugin, Moodle/theme, content or external provider; preserve any unresolved
boundary. Keep the audit OPEN while an applicable row is NOT RUN/BLOCKED or a material finding is unresolved.
Record explicit scope/reasons for N/A and the tester's final conclusion. Close U-020 only from actual reviewed
observations. A wholly green audit needs its evidence record, not an automatic functional release. A concrete
finding warrants a focused correction and the matching static/build/browser/manual recheck.
