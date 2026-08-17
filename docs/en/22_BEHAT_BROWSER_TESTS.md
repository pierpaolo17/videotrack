# Behat browser automation

VideoTrack started its browser-automation phase in release 1.7.45; release 1.7.73 keeps the deterministic HTML5 post-rollback Behat suite and the non-browser contracts for resume, completion synchronisation and stacked notices across YouTube, HTML5 and Vimeo while the maintainer Behat endpoint is unavailable; acknowledgement versioning is additionally covered by behavioural PHPUnit checks. The plugin ships a Moodle module generator under `tests/generator/lib.php` and browser scenarios under `tests/behat/`.

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


### Deterministic local HTML5 seek harness

The generator accepts the test-only field `behathtml5fixture=1`. It creates an upload-source VideoTrack instance backed by `tests/fixtures/behat-video.mp4.b64`, a tiny 60-second local fixture. `tests/behat/behat_mod_videotrack.php` provides browser steps that wait for metadata, seek the HTML5 media and assert the resulting timestamp.

Run only the seek-policy feature with:

```bash
php admin/tool/behat/cli/run.php --tags='@mod_videotrack_html5_seek'
```

The deterministic assertions cover both policies: a blocked jump to 20 seconds must return to the watched frontier, while the same jump remains at 20 seconds when forward seeking is allowed. The scenario exercises the real HTML5 adapter and Moodle-local file delivery without depending on YouTube or Vimeo availability. From 1.7.50 the generator also accepts `behatlinkedforum=<name>` to resolve a same-course Forum fixture for the post-rollback composer scenario.

Release 1.7.51 added `tests/provider_seek_snapshot_contract_test.php`: it statically guards pre-seek snapshot ordering and rollback-safe interaction timestamps across YouTube, HTML5 and Vimeo. This is complementary coverage only; it does not make the outstanding YouTube/Vimeo browser harnesses complete.

Release 1.7.53 added `tests/player_resume_completion_alert_contract_test.php`; release 1.7.54 corrected that test without changing runtime. Release 1.7.55 removes the remaining fragile acknowledgement marker failure and adds behavioural PHPUnit coverage for completion-signature and current-statement versioning. Resume/alert provider checks remain non-browser contracts; the corresponding Behat scenarios are still pending.

## Current browser-test coverage limits

The distributed suite intentionally records what is not yet deterministic. Remaining coverage gaps are:

1. deterministic YouTube / Vimeo provider harnesses;
2. backward-seek provider parity beyond the current HTML5 forward-seek scenarios;
3. end-to-end assertion of the exact pre-seek segment snapshot persisted before a jump;
4. resume, completion and stacked-alert browser scenarios (their non-browser contracts are covered from 1.7.53).

Provider scenarios should avoid depending on public third-party network availability when a deterministic local harness can exercise the same adapter contract.

## Release evidence

Behat results belong to the exact tree on which they ran. Record the Moodle version, browser/driver, scenario count and failures. A green PHPUnit/PHPCS/Grunt run is not a substitute for browser automation, and a Behat result from an older VideoTrack release must not be attributed to a newer tree.

The HTML5 seek feature seeds a bounded validated watched interval for a named learner; in 1.7.50 that scenario verifies reaction, note, bookmark and linked-Forum composer access after a blocked forward seek rolls the player back into watched progress. The Forum assertion also checks that the composer URL carries a timestamp inside the already validated interval.
