# Behat browser automation

VideoTrack started its browser-automation phase in release 1.7.45; release 1.7.103 adds persisted pre-seek boundary verification to the deterministic local HTML5 harness after release 1.7.100 corrected the completion fixture introduced in 1.7.99. The maintainer Behat environment is operational on Moodle 5.0–5.3, and the suite uses unique native `<summary>` CSS selectors instead of ambiguous text clicks. The plugin ships a Moodle module generator under `tests/generator/lib.php` and browser scenarios under `tests/behat/`.

## Purpose

PHPUnit, PHPCS and Grunt are necessary but cannot reproduce browser/provider timing. Behat is therefore used for deterministic learner-page contracts that need a real browser. Provider-specific playback timing remains a separate matrix and is added incrementally.

## Requirements

Use Moodle's normal Behat environment on a local or staging installation. Do not run destructive browser tests against production data. Configure `$CFG->behat_wwwroot`, `$CFG->behat_dataroot` and `$CFG->behat_prefix` according to Moodle's Behat documentation, then initialise the Behat site from the Moodle root:

```bash
php admin/tool/behat/cli/init.php
```

After adding or changing feature files, generators or Behat step definitions, initialise again before running the suite.

## Run VideoTrack scenarios

From the Moodle root:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

To run only the compact learner-section scenario:

```bash
php admin/tool/behat/cli/run.php --name='Active controls stay visible and saved personal history is collapsed by default'
```

## Current automated coverage

Current coverage, strengthened in 1.7.50:

- creates a VideoTrack activity through `mod_videotrack_generator`;
- enables reactions, personal notes and personal bookmarks;
- opens the activity as a learner;
- verifies that reaction buttons, the note composer and the bookmark composer remain visible outside `<details>`;
- verifies watched-interval bar → progress summary → reaction controls, then **My reactions** → note form → **My notes** → bookmark form → **My bookmarks**;
- the static learner-view contract additionally keeps the optional Forum action after the personal-history flow;
- verifies that all three history sections start collapsed and can be opened independently.
- adds learner/teacher/dual-role scope coverage: controls stay active for learner and dual-role users and read-only for teacher-only users.

The native `<details>/<summary>` contract intentionally needs no VideoTrack JavaScript, so the sections remain keyboard-accessible even if an AMD module fails.

Release 1.7.83 deliberately uses the unique `.videotrack-student-section-… > summary` CSS selectors for browser clicks. Do not revert these steps to the generic Moodle `text` selector: the visible label remains asserted separately, while the click must target the interactive `<summary>` node itself.


### Deterministic local HTML5 seek harness

The generator accepts the test-only field `behathtml5fixture=1`. It creates an upload-source VideoTrack instance backed by `tests/fixtures/behat-video.mp4.b64`, a tiny 60-second local fixture. `tests/behat/behat_mod_videotrack.php` provides browser steps that wait for metadata, seek the HTML5 media and assert the resulting timestamp.

Run only the seek-policy feature with:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack_html5_seek'
```

The deterministic assertions cover both policies: a blocked jump to 20 seconds must return to the watched frontier, while the same jump remains at 20 seconds when forward seeking is allowed. The scenario exercises the real HTML5 adapter and Moodle-local file delivery without depending on YouTube or Vimeo availability. From 1.7.50 the generator also accepts `behatlinkedforum=<name>` to resolve a same-course Forum fixture for the post-rollback composer scenario.

Release 1.7.51 added `tests/provider_seek_snapshot_contract_test.php`: it statically guards pre-seek snapshot ordering and rollback-safe interaction timestamps across YouTube, HTML5 and Vimeo. This is complementary coverage only; it does not make the outstanding YouTube/Vimeo browser harnesses complete.

Release 1.7.53 added `tests/player_resume_completion_alert_contract_test.php`; release 1.7.54 corrected that test without changing runtime. Release 1.7.55 removes the remaining fragile acknowledgement marker failure and adds behavioural PHPUnit coverage for completion-signature and current-statement versioning. Provider-specific resume parity and stacked-alert browser coverage remain pending; deterministic HTML5 resume is added in 1.7.97.

Release 1.7.97 adds `html5_playback_contract.feature`. Using the same local MP4 fixture and validated-state seeding, it verifies that HTML5 resumes near a trusted saved position, allows an explicit backward seek inside watched progress, and really transitions between playing and paused states through the custom control bar. These scenarios are deterministic and do not require a public video provider.

The real 1.7.98 Behat gate passed **13/13 scenarios and 195/195 steps** on both Moodle 5.0 and Moodle 5.3 with Chrome 151/Selenium.

Release 1.7.98 adds `html5_acknowledgement_contract.feature`. It verifies that an anytime acknowledgement is immediately confirmable and persists after submission, that a video-end acknowledgement remains disabled before validated playback reaches the end, and that validated evidence through the final second unlocks confirmation. The feature reuses existing browser steps and adds no production or Behat-PHP helper logic.

Release 1.7.99 adds `html5_completion_contract.feature`. The real 1.7.99 gate exposed a fixture gap: the direct validated-state seed did not call the same Moodle completion synchronisation used by runtime writes, so the viewing-only scenario stayed incomplete on both Moodle 5.0 and 5.3. Release 1.7.100 corrects only that Behat fixture. When an activity uses automatic completion, the seed now calls the existing `tracker::refresh_completion()` and `tracker::update_moodle_completion_if_changed()` helpers after writing server-validated evidence. Acknowledgement scenarios still exercise real browser submits. The assertion step continues to read persisted `course_modules_completion`, avoiding markup-version dependencies.

Release 1.7.101 adds `focus_exception_policy.feature`. Two candidate scenarios inspect the real player JSON: a learner outside the hidden course exception group retains `strict`, while a member receives `hiddenonly`. This verifies the server-resolved split-view/accessibility contract without attempting a non-portable window-manager blur simulation. The distributed suite now contains 7 features and 18 candidate scenarios; these two new scenarios are not declared green until the maintainer runs Behat on the exact patched tree.

Release 1.7.103 extends `html5_seek_policy.feature` with a real persisted pre-seek snapshot assertion. The scenario plays the local media before a blocked jump, captures `currentTime` immediately before assigning the forbidden target and waits for the resulting `endreason = 'seek'` database row. The assertion requires a non-empty server-validated interval whose endpoint matches the captured browser boundary within 0.75 seconds. This closes the deterministic HTML5 part of the former static-only snapshot gap without changing production JavaScript.

The real 1.7.103 gate exposed that the extra `servervalidated = 1` expectation was stricter than the persisted-snapshot contract. On both Moodle 5.0 and 5.3 the raw row ended within about 0.22 seconds of the captured pre-seek time, but the server guard retained it as non-authoritative evidence. Release 1.7.104 preserves the raw-endpoint assertion and additionally verifies that aggregate unique coverage and resume position do not cross into the skipped gap. This validates both accepted and conservatively rejected ledger outcomes without weakening the server-authoritative guard.


## Current browser-test coverage limits

The distributed suite intentionally records what is not yet deterministic. Remaining coverage gaps are:

1. deterministic YouTube / Vimeo provider harnesses;
2. backward-seek parity for YouTube/Vimeo beyond the deterministic HTML5 resume/backward-seek coverage;
3. stacked-alert browser scenarios; Moodle completion-state persistence is covered from 1.7.99, while provider-specific resume parity remains pending.
4. an explicit anti-cheat browser/runtime tranche before the final Moodle 5.0–5.3 milestone.

Provider scenarios should avoid depending on public third-party network availability when a deterministic local harness can exercise the same adapter contract.

## Release evidence

Behat results belong to the exact tree on which they ran. Record the Moodle version, browser/driver, scenario count and failures. A green PHPUnit/PHPCS/Grunt run is not a substitute for browser automation, and a Behat result from an older VideoTrack release must not be attributed to a newer tree.

The HTML5 seek feature seeds a bounded validated watched interval for a named learner; in 1.7.50 that scenario verifies reaction, note, bookmark and linked-Forum composer access after a blocked forward seek rolls the player back into watched progress. The Forum assertion also checks that the composer URL carries a timestamp inside the already validated interval.
