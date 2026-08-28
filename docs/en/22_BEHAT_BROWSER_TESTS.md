# Behat browser automation

The plugin ships a Moodle activity generator in `tests/generator/lib.php`, component steps in
`tests/behat/behat_mod_videotrack.php` and feature files in `tests/behat/`. The current suite contains
23 scenarios and 342 steps.

## Setup and execution

Configure Moodle's isolated Behat site (`behat_wwwroot`, `behat_dataroot`, `behat_prefix`) and initialise it after
changes to generators, steps or feature files:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Never point Behat at production data.

## Deterministic fixtures

- `behathtml5fixture=1` creates an upload activity backed by the local 60-second MP4 fixture.
- `behatproviderfixture=youtube` uses a reserved id and local YouTube SDK double only under Behat.
- `behatproviderfixture=vimeo` uses a reserved id and local Vimeo SDK double only under Behat.
- `behatlinkedforum=<name>` maps a Forum fixture in the same course.

Production provider code paths remain in use; only external SDK/network behaviour is replaced, keeping scenarios
repeatable without public-provider availability.

## Current coverage

The suite verifies:

- learner, teacher-only and dual-role participation behaviour;
- visible reaction/note/bookmark composers and independently collapsible saved histories;
- HTML5 play/pause session lifecycle and accepted terminal ledger write;
- trusted resume, backward seek and allowed/blocked forward seek;
- pre-seek snapshot persistence and exclusion of skipped gaps from coverage/resume;
- reaction, note, bookmark and linked-Forum timestamps after rollback;
- acknowledgement at any time and after validated final-second coverage;
- Moodle completion persistence for viewing, interactions and acknowledgement;
- stacked persistent/transient status notices;
- strict focus policy and course exception-group resolution;
- deterministic YouTube and Vimeo resume/seek/recovery/terminal pause contracts;
- grouped completion requirements and vertical layout across Moodle's native/ARIA list variants.

## Selector and assertion rules

Prefer stable component CSS/data markers over translated visible text for interaction targets; assert visible labels
separately. Read persisted Moodle/plugin state when markup varies across supported branches. Media assertions use
bounded timing tolerances and wait for asynchronous writes rather than fixed sleeps.

## Limits

Local SDK doubles verify adapter contracts but not public iframe availability, consent, cookies or provider UI.
Behat does not replace manual keyboard/screen-reader perception tests or real-provider smoke tests. Results apply
only to the exact plugin tree, Moodle branch, browser and driver recorded by the run.
