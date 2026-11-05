# Behat browser automation

The plugin ships a Moodle activity generator in `tests/generator/lib.php`, component steps in
`tests/behat/behat_mod_videotrack.php` and feature files in `tests/behat/`. The current suite contains
**27 scenarios and 417 steps expected from source** for 1.7.235. The accepted 1.7.234 baseline passed 24 scenarios
and 357 steps on all six CI jobs. The three additional cases require actual candidate CI; source counts are not
a claim that those new scenarios have passed.

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
- absence of reaction, note, bookmark and Forum sections when their effective configuration is unavailable;
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

## Browser-driven viewing completion

`browser_tracking_completion.feature` adds one case for HTML5 and one each for the production YouTube/Vimeo
adapters with local SDK doubles. Each fixture disables autoplay/resume and view-only/acknowledgement completion,
uses a 60-second duration and a 5% viewing threshold, and starts with no server-validated ledger row and incomplete
native completion. The browser plays for about five seconds, pauses through the actual player/provider surface,
then the assertions require an accepted same-session pause, completed aggregate viewing state and the persisted
`course_modules_completion` row. Reload must retain completion. Short playback makes the case practical while
still requiring a positive three-second server-validated threshold; no seek or progress seed crosses that threshold.

The two new assertions read only. They cannot insert/update ledger, state or completion records, invoke the segment
endpoint directly, fabricate AJAX responses or mark a row complete. Database polling waits at most five seconds
with short retries for asynchronous writes, following the existing pause-lifecycle assertion.

The older `html5_completion_contract.feature` viewing case deliberately seeds evidence to test synchronisation
from an existing state. Its scenario name now states that purpose. It remains useful alongside the new live
browser-to-AJAX-to-database checks and is not evidence of browser-generated viewing by itself.

## U-007 coverage matrix

| Contract | HTML5 | YouTube | Vimeo |
|---|---|---|---|
| Play/pause and accepted terminal ledger write | Existing | Existing SDK-double case | Existing SDK-double case |
| Resume, backward seek, blocked forward seek/frontier | Existing | Existing SDK-double case | Existing SDK-double case |
| Unseeded playback to native viewing completion | New 1.7.235 case, CI pending | New 1.7.235 case, CI pending | New 1.7.235 case, CI pending |
| Allowed forward seek and post-rollback interactions | Existing HTML5 cases | Remaining parity review | Remaining parity review |
| Ended/replay/rate changes and relevant group scopes | Remaining matrix review | Remaining matrix review | Remaining matrix review |

Learner, teacher-only and dual-role page controls already have shared scenarios. Their existence does not fill
every provider/lifecycle/group combination. U-007 remains open until the roadmap's essential cells and boundary
Moodle runs are evidenced; this tranche does not declare complete provider or browser coverage.

The current workflow invokes the `chrome` Behat profile on six Moodle/PHP/database environments. These are six
environment jobs using one browser profile. Other-browser and live-public-provider smoke results must be recorded
separately, not inferred from that count. U-020 manual accessibility and final production gates also remain separate.

## Selector and assertion rules

Prefer stable component CSS/data markers over translated visible text for interaction targets; assert visible labels
separately. Read persisted Moodle/plugin state when markup varies across supported branches. Media assertions use
bounded timing tolerances and wait for asynchronous writes rather than fixed sleeps.

## Limits

Local SDK doubles verify adapter contracts but not public iframe availability, consent, cookies or provider UI.
Behat does not replace manual keyboard/screen-reader perception tests or real-provider smoke tests. Results apply
only to the exact plugin tree, Moodle branch, browser and driver recorded by the run.
