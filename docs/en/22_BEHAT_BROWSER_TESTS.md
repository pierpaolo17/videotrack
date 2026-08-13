# Behat browser automation

VideoTrack started its browser-automation phase in release 1.7.45; release 1.7.47 extends the deterministic learner-page scenario with the full vertical ordering contract. The plugin ships a Moodle module generator under `tests/generator/lib.php` and browser scenarios under `tests/behat/`.

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

Current coverage, strengthened in 1.7.47:

- creates a VideoTrack activity through `mod_videotrack_generator`;
- enables reactions, personal notes and personal bookmarks;
- opens the activity as a learner;
- verifies that reaction buttons, the note composer and the bookmark composer remain visible outside `<details>`;
- verifies watched-interval bar → progress summary → reaction controls, then **My reactions** → note form → **My notes** → bookmark form → **My bookmarks**;
- the static learner-view contract additionally keeps the optional Forum action after the personal-history flow;
- verifies that all three history sections start collapsed and can be opened independently.
- adds learner/teacher/dual-role scope coverage: controls stay active for learner and dual-role users and read-only for teacher-only users.

The native `<details>/<summary>` contract intentionally needs no VideoTrack JavaScript, so the sections remain keyboard-accessible even if an AMD module fails.

## P2 roadmap still open

U-007 is **in progress**, not closed by this first scenario. The remaining browser matrix must add deterministic coverage for:

1. learner / dual-role / teacher;
2. HTML5 / YouTube / Vimeo;
3. forward seek allowed / blocked and backward seek;
4. pre-seek segment snapshot and rollback timestamp;
5. reaction / note / bookmark / Forum immediately after seek and rollback;
6. resume, completion and stacked alerts.

Provider scenarios should avoid depending on public third-party network availability when a deterministic local harness can exercise the same adapter contract.

## Release evidence

Behat results belong to the exact tree on which they ran. Record the Moodle version, browser/driver, scenario count and failures. A green PHPUnit/PHPCS/Grunt run is not a substitute for browser automation, and a Behat result from an older VideoTrack release must not be attributed to a newer tree.
