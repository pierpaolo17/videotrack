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
