# PHPStan, Psalm and PHPMD

Static analysis is a staged hardening process, not a substitute for Moodle runtime tests. VideoTrack keeps the
configuration in the plugin so local, server and GitHub runs use the same production scope.

## Configuration and ownership

| Tool | Configuration | Initial policy | Purpose |
|---|---|---|---|
| PHPStan | `phpstan.neon.dist` | level 1, advisory baseline | Type and call-contract analysis. |
| Psalm | `psalm.xml` | error level 8, advisory baseline | Independent type/data-flow analysis. |
| PHPMD | `phpmd.xml` | reviewed findings advisory | Complexity, size, design, boolean flags and unused code. |
| Moodle PHPCS | `phpcs.xml.dist` | zero warnings, blocking | Moodle coding style, naming and compatibility rules. |

PHPStan and Psalm cover production directories and entry points only. They exclude tests, dependencies and Moodle
core from the project scan, but load core definitions from the installed site. PHPMD excludes tests, language
packs, documentation and tooling in its own ruleset. A tool crash, invalid configuration or missing report is not
an advisory finding: it is an infrastructure failure.

## Moodle bootstrap

`tools/static-analysis/bootstrap.php` loads the installed Moodle `config.php` before analysis. It uses
`MOODLE_ROOT` when set, then checks the deterministic roots implied by classic `mod/videotrack` and Moodle's
`public/mod/videotrack` layout. It then loads the stable upgrade, CLI, group, Forum external, admin and form APIs
plus Moodle's canonical backup and restore include graphs before the Moodle 2 step libraries used by the production
scope. The order matters because the step libraries declare subclasses immediately. This explicit bootstrap is
necessary because the analysers do not reliably follow every variable-based legacy require. It never searches the
complete filesystem and never selects generated `.types` mirrors. The selected site must already be installed and
its database must be reachable.

`psalm.xml` also loads `tools/static-analysis/moodle-legacy-aliases.phpstub`. The stub statically declares the global
`renderable` compatibility name as extending `core\output\renderable`. This represents Moodle 5.0's runtime alias in
a form Psalm can index; stub files are parsed for declarations, so an executable `class_alias()` call is ineffective.

Psalm's optional `ensureOverrideAttribute` policy is explicitly disabled. The native `#[\Override]` attribute is a
PHP 8.3 feature, while the supported Moodle 5.0 matrix includes PHP 8.2. Inheritance and signature analysis remain
enabled; only the incompatible attribute-presence style rule is disabled.

## Maintainer command

From the Moodle root, after synchronising the exact candidate:

```bash
moodle-test -p mod_videotrack -m 50,53 -c phpstan,phpdoc,phpmd,psalm
```

The runner uses the committed configurations. PHPStan advances from level 1 to 8 and Psalm from error level 8 to
1, stopping at the first failing level. PHPMD and PHPDoc use their real exit status. Retain the complete report and
each per-level log; do not report only the highest successful level.

For direct PHPStan/Psalm runs outside an installed plugin path, export the real root first:

```bash
export MOODLE_ROOT=/absolute/path/to/moodle
vendor/bin/phpstan analyse --configuration=/absolute/path/to/videotrack/phpstan.neon.dist
vendor/bin/psalm --config=/absolute/path/to/videotrack/psalm.xml --no-cache
```

## GitHub baseline jobs

The repository workflow installs the exact direct analyser versions declared in
`.github/static-analysis/composer.json`. PHPStan and Psalm run after ordinary-site installation on the Moodle 5.0
and 5.3 MariaDB jobs. Their full output and installed versions are retained as `phpstan.txt`, `psalm.txt` and
`static-tools.txt`. PHPMD runs in the canonical Moodle 5.0 quality job.

Psalm exit 2 is accepted only as its documented completed-analysis-with-findings result; exit 1 and every other
non-zero status remain blocking failures. PHPStan exit 1 is advisory only when its normal error-count summary is
present and no internal/incomplete-analysis marker occurs. PHPMD exit 2 is likewise converted to an advisory
finding result. This keeps code findings visible without accepting crashes or partial reports as valid evidence.

## Reading and fixing findings

1. Reject results dominated by unresolved Moodle symbols, core/vendor paths or analyser setup errors.
2. Group valid findings by rule and common root cause; record counts by production file.
3. Fix one bounded group without changing public Moodle signatures or weakening runtime validation.
4. Run PHPCS, PHPDoc, lint and the affected PHPUnit/Behat/lifecycle gates after every code tranche.
5. Re-run all three analysers on Moodle 5.0 and 5.3 and compare exact reports, not only totals.
6. Make a tool blocking only when its accepted baseline is zero or a reviewed baseline policy is committed.

Suppressions are exceptional. Each suppression must be the narrowest supported form, adjacent to the unavoidable
framework contract, explain why code cannot safely change and have a regression test where behaviour matters.
Generated baselines, global ignore patterns and reduced analysis scopes must never be used merely to obtain green
output.

Private helper signatures and their canonical DocBlocks must express the same contract. Use native `stdClass` for
Moodle DML records when the record shape is an intentional precondition, and pass a primitive value instead of a
framework object when the helper needs only that value. Keep a genuine generic `object` contract where the helper
does not inspect implementation-specific members. Do not add parameter-only inline `@var` blocks or analyser-only
`@phpstan-param`/`@psalm-param` tags: Moodle Code Checker rejects those forms. For validated arrays, keep the
canonical `array` contract and cast values at the point where a concrete scalar is persisted or passed to a typed
API. An inline `@var` is reserved for a real assignment whose variable name matches the immediately following code
line, not for redeclaring a method parameter.

## Current status

- PHPStan and Psalm complete on both supported boundary branches. Their blocking/error summaries are clean; full
  advisory output remains available in each CI artifact rather than being copied into this guide.
- Moodle loader-provided globals have narrow type contracts. Five direct entry points keep only the PHPCS exception
  required around their inline PHPStan declarations, and unmatched-ignore reporting remains enabled.
- The Psalm-only legacy stub models the global `renderable` alias and Moodle's misspelled `xmlddb_field` return type.
  It does not execute runtime code, suppress issue categories or reduce the analysed production scope.
- `analytics_scope::matching_accessible_instances()` remains the public facade while `analytics_scope_matcher`
  separates candidate SQL, source predicates, external-URL filtering, capability filtering, current fallback and
  ordering. The split preserves every trust boundary while removing three PHPMD findings from the facade class.
- The backup structure hook delegates root, reaction and user-data node creation, canonical attachment, source
  configuration and file-area annotation to named private stages. Source contracts preserve XML nesting, retention
  and positive-user filters, identifier annotations and file areas independently of the method-length reduction.
- `report_support`, `report_analytics_query` and `report_event_query` each expose seven cohesive public contracts.
  Analytics/provider/acknowledgement SQL and event/learner-discovery SQL are isolated from presentation, option,
  highlight and clustering support. The focused `report_time_filter` service owns four cohesive public contracts
  for date boundaries, request parsing and duration-control rendering;
  its structured parser delegates presence, digit, normalisation and range decisions to named private stages. The
  focused `report_aggregation` service owns pure viewer-drop discovery and reaction-cluster ordering. These
  boundaries preserve the controller contract while removing the method-level complexity previously assigned to
  `report_support::optional_time_param()`. CSV delimiter selection likewise uses separate site and activity entry
  points; neither API uses a boolean switch.
- `videotrack_render_reaction_icon()` always returns the icon together with its visible accessible label. Its callers
  no longer pass an unused boolean presentation flag.
- Reaction lookup uses distinct active-only and complete-lifecycle functions. Record whitelisting has one stable
  request-cached contract; neither API uses an optional boolean behaviour switch.
- Timed-text lookup uses canonical transcript/chapter methods plus explicitly named legacy-fallback methods. The
  compatibility paths preserve canonical precedence without boolean behaviour switches.
- Playback-rate policy exposes one public contract backed by named parsing, effective-cap, filtering and mandatory
  normal-speed stages. TinyMCE emoji parsing and reaction-picker header/tab/body rendering are likewise isolated,
  while retention-setting persistence separates confirmation from post-save transition auditing.
- Teacher Analytics presentation is split into four focused owners. `report_view` has three shared control methods,
  `report_timeline_view` has five interval/chart methods, `report_summary_view` has three aggregate-summary methods,
  and `report_integrity_view` has three explicit public states plus private markup helpers. Heatmap and retention
  geometry/markup and acknowledgement value/notice decisions now use named private stages. Public signatures,
  suppression boundaries and rendered contracts are unchanged.
- Combined Analytics export rows use named private stages for timeline padding, event-summary ordering, integrity
  fallback and acknowledgement values. The public signature, selected columns, privacy masking and row order remain
  unchanged, with a combined-shape regression contract covering all three row families.
- Personal-note writes expose one public external-service entry point backed by named text, timestamp,
  watched-evidence, cross-session rate-limit, persistence and warning stages. Guard-order and rate-scope contracts
  prevent the complexity refactoring from weakening the write boundary. Private DML record contracts use native
  `stdClass`, while persistence receives the scalar course-module id it actually stores.
- Personal-bookmark writes expose the corresponding named label, timestamp, watched-evidence, cross-session
  rate-limit, persistence and event stages. Source contracts preserve validation and security order and prevent the
  ten-in-ten-seconds user/activity limit from being narrowed to one browser session.
- Reaction writes expose named request-validation, active-definition, timestamp, watched-evidence, burst-limit,
  duplicate and soft-ignore stages. The external endpoint owns every pre-insert security and concurrency decision;
  the focused `reaction_write_service` owns cache/event/count/completion work after persistence. Source contracts
  preserve both orders, stable response mapping and the export helper's generic context contract. The cross-service
  completion contract inspects this current owner rather than requiring the extracted calls to remain in the public
  endpoint. Its explicit `MOODLE_INTERNAL` guard retains one line-scoped PHPCS exception because the autoloaded
  class has no top-level side effects.
- Four internal media/file helpers consume their complete signatures through runtime contracts: Moodle-form
  validation, instance-aware module-context resolution and course-module/instance consistency checks. Required
  Moodle callback parameters remain unchanged even where the framework owns their invocation.
- The Moodle feature callback is a direct feature-to-value table, while Font Awesome reaction validation partitions
  the reviewed decorator subset from exactly one icon name. Their public contracts, limits and fail-closed defaults
  are covered independently of the complexity reduction.
- PHPMD remains advisory. The 1.7.217 CI confirmed 38 findings, down from 39 in
  1.7.216 without new findings: the CSV facade and its two focused services
  remain below the configured class-complexity threshold. The 1.7.218
  candidate separates Moodle file-serving area eligibility, stored-file
  lookup, content validation and download/cache policy while preserving the
  callback's public signature and access-check order. Candidate CI must
  confirm removal of its two complexity findings without new findings.
  The first candidate CI found 37 total, including one new complexity finding
  in the area helper; the follow-up divides area and timed-text checks and
  requires CI confirmation of the expected 36 without new findings.
  Other reviewed findings concern complexity, size, broad public classes,
  required Moodle callback parameters and boolean switches.
- The final 1.7.218 CI confirmed 36 PHPMD findings without new findings. The 1.7.219 candidate splits the long
  activity-form validation method into source, timed-text, stored-icon and forum checks while retaining existing
  Moodle entry points. Its first CI passed all blocking checks but increased PHPMD to 38: the source helper
  acquired cyclomatic complexity and timed-text checking acquired cyclomatic and NPath findings. The incremental
  correction split URL/upload checks, draft validation and required-file checks; the corrected CI and six
  post-merge jobs confirmed 35 findings without new ones. The 1.7.220 candidate separates reaction-row,
  icon-value and required-field validation while preserving error order. The first CI passed all six jobs
  and reported 34 findings, including a new `TooManyMethods` on the form. An incremental correction moves
  both pure checks into a focused `reaction_form_validation` class. Its six CI jobs confirmed 33 findings
  with no new ones, but the Moodle 5.0/MariaDB quality job found nine PHPCS formatting errors. A further
  incremental patch fixed multiline call formatting and the class closing brace. Six corrected and six
  post-merge CI jobs passed with the same 33 findings and no new ones. The 1.7.221 candidate moves form
  edit-default preparation into a focused class while retaining Moodle's parent call and draft order;
  CI must confirm the method-length, cyclomatic and NPath results without new findings.
- The first 1.7.221 CI found 32 PHPMD findings: two form-method complexity findings disappeared, while a new
  cyclomatic finding appeared in the extracted completion helper. A follow-up separated grade and Vimeo defaults
  from completion rules and corrected two runtime-test problems. Six corrected and six post-merge jobs confirmed
  31 findings with no new ones. The 1.7.222 candidate moves reaction notice and preset defaults into the existing
  helper after timed-text drafts; CI must confirm removal of the 101-line method-length finding.
- Static-analysis configuration, production scope, database schema and AMD assets are unchanged by these API
  cleanups. Candidate counts remain authoritative only after the complete CI artifacts are retained and reviewed.
- The merged 1.7.222 server and CI confirmed 30 PHPMD findings. Candidate 1.7.223 splits the
  343-line privacy exporter and six event categories while retaining the writer paths, field transformations,
  bounded 500-row chunks and final category order. Two PHPUnit cases check the actual export and boundary;
  the PHPMD result and all runtime gates await candidate CI.
- The first 1.7.223 CI passed all six blocking jobs and reduced PHPMD to 28 findings,
  but introduced one cyclomatic finding in `format_intervals()`. The follow-up extracts
  the unchanged numeric-bound guard; the corrected PHPMD count awaits CI.
- The corrected 1.7.223 post-merge CI confirms 27 advisory PHPMD findings; no finding remains in either privacy
  exporter. Candidate 1.7.224 extracts the course-scoped Forum form group into `form_definition` without
  adding methods to the activity form. This is a testability boundary, with an unchanged expected count of 27;
  the three new runtime tests and the complete finding list require candidate CI confirmation.
- Initial 1.7.224 CI passes the six runtime jobs (340 PHPUnit tests, 3,058 assertions and 24 Behat scenarios)
  but reports 28 PHPMD findings: form object coupling reaches 13 after adding the Forum builder. The corrected
  `form_definition` also owns acknowledgement field construction, removing the form's direct dependency on
  `acknowledgement` while retaining both groups' statements and positions. Four behavioural tests cover these
  groups. The expected 27 findings and the added editor-context test require corrected candidate CI.
- Corrected and post-merge 1.7.224 CI confirm 27 findings without new findings; object coupling is no longer
  reported for the activity form. Candidate 1.7.225 moves CSV field construction into the existing
  `form_definition` owner and removes the form's direct CSV-class dependency. The expected count remains 27;
  two new runtime cases cover field order, inheritance and actual context/hidden-identity permissions.
  The complete finding list and all runtime gates require candidate CI confirmation.
- Initial and post-merge 1.7.225 CI confirm the same 27 findings: `definition()` complexity falls from 29 to 26,
  NPath from 27,648,000 to 4,608,000 and length from 974 to 922 lines. Candidate 1.7.226 delegates integrity-field
  construction to the existing `form_definition` owner without adding activity-form methods. Two runtime cases
  cover disabled defaults and effective site-policy presentation. The expected count remains 27;
  the complete finding list and runtime behaviour require candidate CI confirmation.

- Initial and post-merge 1.7.226 CI confirm the same 27 findings without new findings. `definition()` complexity
  falls to 25, NPath to 2,304,000 and length to 860 lines. The real ZIP and Moodle 5.0/5.3 server checks confirm
  the 345-test, 3,109-assertion suite and both strict validators pass all ten checks. Candidate 1.7.227 delegates
  the site-permitted HTML5 controls and playback-speed groups to the existing `form_definition` owner. Each
  helper uses the original course context for override permissions; neither adds a boolean behaviour flag.
  Three runtime tests cover allowed-choice order and flat values, upload visibility, actual course-scoped
  permissions, missing configuration and empty choice sets. The activity form retains 25 methods. The expected
  PHPMD count remains 27; complete findings and runtime behaviour require candidate CI confirmation.
- Initial 1.7.227 CI confirms the same 27 findings with no new findings: `definition()` complexity is 19,
  NPath 92,160 and length 713 lines. All six environments pass 348 PHPUnit tests, 3,152 assertions, 24 Behat
  scenarios and ten strict validator checks; PHPStan and Psalm find no errors. Canonical PHPCS rejects the
  inline multiline array in the new empty-group test. The incremental correction uses a named local list
  without changing assertions or production code. Release/version remain 1.7.227 / 2026110402;
  corrected candidate CI must pass before merge.
- Corrected and post-merge 1.7.227 CI confirm all six jobs and the same 27 PHPMD findings. The real ZIP and
  Moodle 5.0/5.3 server checks pass PHPCS, lint, Grunt and 348 tests with 3,152 assertions; both strict validators
  report ten passes without warnings. Candidate 1.7.228 delegates the player-behaviour group to the existing
  `form_definition` owner using the original course context. Boolean site defaults retain their false/empty
  fallback after the existing boolean/integer conversion; numeric zero continues to mean inheritance. Three
  runtime tests cover defaults, saved values, inheritance, upload visibility, width-description accessibility
  and actual course override permissions. The activity form retains 25 methods. The expected PHPMD count
  remains 27; the complete findings and runtime gates require candidate CI confirmation.
- Initial and post-merge 1.7.228 CI confirm the same 27 findings without new findings. `definition()` complexity
  falls from 19 to 16, NPath from 92,160 to 15,360 and length from 713 to 632 lines. The real ZIP and Moodle
  5.0/5.3 server checks pass PHPCS, lint, Grunt and 351 tests with 3,219 assertions; both strict validators pass
  ten checks without warnings. Candidate 1.7.229 delegates the caption group to `form_definition`, with a focused
  private stage for provider-independent transcripts/chapters. The two stages retain the original field order,
  site-default fallback, provider dependencies, WebVTT options and course override lock. Three runtime tests
  cover defaults, dependencies, effective upload options for an ordinary user and actual course permissions.
  The activity form retains 25 methods. The expected PHPMD count remains 27; candidate CI must confirm the
  complete findings and all runtime gates.
- Initial and post-merge 1.7.229 CI confirm the same 27 findings without new findings. `definition()` complexity
  falls to 15, NPath to 7,680 and length to 490 lines. The real ZIP and Moodle 5.0/5.3 server checks pass PHPCS,
  lint, Grunt and 354 tests with 3,292 assertions; both strict validators pass ten checks without warnings.
  Candidate 1.7.230 delegates the duration field, accessible status markup, DOM JSON and AMD init to the existing
  `form_definition` owner, using the original page. Two runtime tests cover manual decimals, accessibility,
  decoded DOM configuration and registration on the supplied page. The provider-loader source contract follows
  the delegation while retaining its duration-message assertions. The activity form retains 25 methods. The
  expected PHPMD count remains 27; candidate CI must confirm the complete findings and all runtime gates.
- Initial and post-merge 1.7.230 CI confirm the same 27 findings without new findings. `definition()` complexity
  is 14, NPath 3,840 and length 403 lines. The real ZIP and Moodle 5.0/5.3 server checks pass PHPCS, lint, Grunt
  and 356 tests with 3,315 assertions; both strict validators pass ten checks without warnings. Candidate 1.7.231
  gives the site-controlled playback group its focused `form_playback_settings` owner with one public entry point.
  The existing builder stays at nine public methods; the activity form stays at 25. The private rate-choice stage
  preserves field order, hundredths-based values, defaults and the seven-field course lock. Three runtime tests
  cover false/empty/zero site values, actual course permissions, unlocked controls and saved rate selections.
  The expected PHPMD count remains 27; candidate CI must confirm the complete findings and all runtime gates.
- Initial and post-merge 1.7.231 CI confirm the same 27 findings without new findings. `definition()` complexity
  is 11, NPath 640 and length 305 lines. The real ZIP and Moodle 5.0/5.3 server checks pass PHPCS, lint, Grunt
  and 359 tests with 3,427 assertions; both strict validators pass ten checks without warnings. Candidate 1.7.232
  separates general reaction settings and the post-row completion lock into `form_reaction_settings`, leaving
  repeated-row construction and its actual count in the form. Three runtime tests cover defaults, editor context,
  preset guards and completion-only course/row permissions. Existing source contracts retain their assertions
  on the new owner and check delegation order. The expected PHPMD count is 25, targeting the remaining cyclomatic
  and NPath findings on `definition()`; candidate CI must confirm complete findings and all runtime gates.
- The initial and post-merge 1.7.232 CI jobs confirm 25 findings, down from 27 with no additions. Both
  complexity findings on `definition()` are gone; its reported length is 192 lines. The real ZIP and Moodle 5.0/5.3
  server gates pass PHPCS, lint, Grunt and 362 tests with 3,553 assertions; both strict validators pass ten checks
  without warnings. Candidate 1.7.233 moves row fields and icon visibility into the existing reaction owner, using
  one public row entry point and a private visibility stage. The form retains count selection, bounds, upload
  filtering, no-submit Add reaction and preset registration, then locks completion with the actual count.
  Three additional runtime tests cover row/picker/file contracts, isolated dependencies and saved values, plus
  real form count/add/cap orchestration. Existing permission cases now use real rows. The form has 23 methods and
  the owner three public methods. The expected PHPMD count is 24; candidate CI must confirm the full finding list
  and all runtime gates. No further form extraction is planned before the U-017 exit audit of tracker/player
  responsibilities, critical state effects and justified residual findings.
- The 1.7.233 CI/server acceptance confirms 24 findings, 365 tests and 3,920 assertions. The 1.7.234
  [exit audit](25_MAINTAINABILITY_EXIT.md) accepts the documented residual ownership and stops the planned structural sequence
  after consolidation, subject to real candidate acceptance. Four new persisted-effect tests cover budget/seek
  rejection replays, live interval-cap rebuilding and authenticated completion/event idempotence. All production
  implementations and analyser rules remain unchanged; the expected PHPMD count is 24, to be confirmed in CI.
- Initial 1.7.234 CI confirms the exact 24 findings and 369 tests with 3,988 assertions on all six configurations.
  One multiline tracker call in the new test causes six canonical PHPCS formatting errors. The same-version
  incremental correction changes only argument layout and corresponding documentation; corrected CI remains pending.
