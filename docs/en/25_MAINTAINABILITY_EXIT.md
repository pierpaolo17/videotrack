# U-017 maintainability exit audit

Current tree: VideoTrack **1.7.251** (`2026111201`).

## Decision and accepted evidence

**U-017 is closed.** The planned structural extraction sequence ended with accepted 1.7.234. Retain the 24 reviewed
findings below with explicit owners and reopening conditions. Further structural work requires a concrete defect
or functional benefit; it is not an automatic prerequisite for the next roadmap phase.

The accepted exit baseline is the real 399-file 1.7.234 ZIP, SHA-256
`91c06840b8f832887e44f4ae737c59bb095d36233afecbfd00aa99a6aa870c54`, identical to merged commit
`4fa98cf9bcfabc4a411515af3678b3981f181538`. Corrected candidate and post-merge CI pass all six jobs with 369
PHPUnit tests, 3,988 assertions, 24 Behat scenarios / 357 steps, ten strict checks and 24 unchanged PHPMD findings.
PHPCS and PHP lint are clean on 164 files. The exact server report on Moodle 5.0/5.3 passes PHPCS, lint, Grunt and
the same PHPUnit suite. Both supplied strict outputs are 10 PASS / 0 WARN / 0 FAIL. All exit gates are satisfied.

Current accepted runtime evidence is 1.7.240: six release/main jobs pass Behat 50/1074,
PHPUnit 379/4076 and strict 10/0/0; actual ZIP/server/strict are accepted. U-007 is closed in its
[own exit audit](26_BROWSER_EXIT_AUDIT.md). Candidate 1.7.241 is documentation/version metadata only.

## Ownership review

| Area | Current owners and retained boundary | Exit decision |
|---|---|---|
| Reports and Analytics | `report_access`, query/time-filter/aggregation services, Analytics loaders and presentation/export services. Controllers retain request routing and explicit access checks. | Retain existing scope and privacy boundaries; no further small extraction selected. |
| Activity page | `view.php` retains context/bootstrap, configuration and rendering. Its acknowledgement POST route requires participation, POST and sesskey, then delegates confirmation and completion. | Keep this explicit route; do not duplicate acknowledgement policy inside presentation helpers. |
| Activity form | `form_definition`, `form_playback_settings`, `form_reaction_settings`, preprocessing and validation owners. The form retains Moodle hooks, actual row count and file-draft lifecycle. | The planned field-construction tranche is complete. Remaining form orchestration is accepted. |
| PHP tracker | Interval algebra, ledger guards, atomic state update and completion stay in `tracker`; completion configuration has its own owner. | Retain the cohesive transaction owner. Moving guard branches across services would change the surface that must be proved. |
| Shared browser tracking | `core/tracker` delegates events, state/time, heartbeat, lifecycle and segment queue to focused modules. `core/api` centralises validation, transport, retry and request scope. | Shared responsibilities are already explicit. Keep provider-neutral policy here. |
| Concrete providers | `player.js` is YouTube; `html5_player.js` owns native media/controls; `vimeo_player.js` owns the SDK lifecycle, recovery and seek callbacks. All use shared tracker/player/API/adapter services. | Size alone does not justify unifying asynchronous provider behaviour. Small wrappers/configuration adapters are accepted duplication. |
| Exports | `csv_export` remains the facade; field selection, identity formatting, event writer and timestamp values have focused owners. | Preserve callers and privacy/context boundaries. |
| Moodle lifecycle | Callbacks, historical upgrades, privacy retention, backup/restore and gradebook contracts retain their framework entry points. | Keep compatibility and chronological migration history. |

The provider review follows the actual production imports/delegations and seek, resume, save and lifecycle paths.
Existing Behat includes HTML5 playback/seek/completion/acknowledgement and YouTube/Vimeo adapter contracts. The
external SDKs in deterministic provider tests are doubles; this is not a complete live-provider/browser certification.
Further cosmetic alias or configuration-wrapper extraction would add a build/provider validation cycle without
isolating a new domain responsibility. Reopen a boundary when a concrete defect or feature provides that benefit.

## Tracker effect review

`update_state()` keeps this order: acquire the canonical activity/user lock, start the delegated transaction,
load/create state, resolve request replay, apply active-session and forward-frontier guards, apply cumulative
server credit, persist accepted evidence, aggregate validated coverage and completion, then commit and release.
The completion event follows the successful commit. The endpoint emits significant segment events and synchronises
native Moodle completion only for a new accepted request. Existing replay and rejection returns remain separate.

The code review confirms the exception path requests transaction rollback and lock release. This release does not
claim a parallel-process lock-contention or injected database-failure test; those limits remain explicit in the
production/lifecycle audit. No mutation order is rewritten merely to reduce complexity metrics.

| Contract | Evidence |
|---|---|
| Server elapsed-time credit, bounded rate and cumulative tolerance debt | Existing behavioural tests in `tracker_test.php`. |
| Active-session ownership, stale-tab rejection, pause/page lifecycle closure | Existing persisted-state tests in `tracker_test.php`; shared lifecycle/provider contracts. |
| Accepted request replay and unchanged unique coverage | Existing persisted retry test; strengthened by the new endpoint completion test. |
| Rejected budget request remains rejected when retried later | New `tracking_effects_test::test_budget_rejection_retry_preserves_state_and_audit_row()`. |
| Rejected forward seek preserves the watched frontier; its replay cannot reset a later budget | New `tracking_effects_test::test_forward_rejection_keeps_frontier_and_does_not_replay_its_budget_reset()`. |
| Interval-cap fallback recomputes exact coverage from validated raw rows only | New `tracking_effects_test::test_live_interval_cap_rebuild_preserves_exact_validated_coverage()`. |
| Authenticated segment retry preserves the native completion row and emits significant/completion events once | New `tracking_effects_test::test_external_completion_retry_keeps_persisted_effects_and_events_once()`. |
| Lock, commit, rollback and side-effect boundaries | Source review; retained source contracts and existing lifecycle/privacy tests. No new concurrency/fault-injection claim. |

The exit tests use real activities, enrolled learners, ledger/state tables and Moodle's event sink. They use a supplied
server clock for tracker cases; the real endpoint case uses its production clock after an explicit older handshake.
No sleep or provider network request is needed. Rejected rows remain audit evidence and do not enter validated
coverage. The cap case checks the live database rebuild, not only the pure aggregation helper.

## Reviewed PHPMD residuals

Accepted 1.7.240 release/main CI confirms **24** reviewed findings, with the same report identities and
configured analysis scopes. Candidate 1.7.241 preserves all production bodies and rulesets. This is an explicit debt register, not a suppression, zero-finding claim or blanket acceptance of future findings.

| File/owner | Findings | Reason to retain and condition for reopening |
|---|---:|---|
| `classes/external/save_segment.php` | 1 parameter-list finding | The ten-field public AJAX signature matches its declaration and callers. Reopen only with a deliberate service-contract change. |
| `classes/local/csv_export.php` | 1 public-method-count finding | Stable facade with focused implementation owners. Reopen for a real export API change, preserving context and identity checks. |
| `classes/local/tracker.php` | 6 | Class size/public API/complexity and `update_state()` complexity, NPath and length. Keep ledger, state and transaction effects together; any future split must preserve the effect matrix above. |
| `db/upgrade.php` | 3 | Complexity, NPath and length reflect ordered historical migrations. Do not rewrite deployed upgrade history for a metric. Reopen for an evidenced upgrade defect. |
| `lib.php` | 11 | Nine unused formal-parameter findings on callback/public signatures and two established boolean arguments. Preserve Moodle/lifecycle callers; revisit only with a concrete compatibility-safe API change. |
| `mod_form.php` | 2 | Class complexity and `definition()` length remain after field, validation and preprocessing separation. The form has 23 methods and retains framework orchestration. Reopen for a functional form change with a useful new boundary. |
| **Total** | **24** | No analyser rule, scope or baseline file is weakened. |

## Current reset and review scope

Per-student reset and complete assisted review of 997 keys / 7976 values are accepted on actual 250.
Eight manual PASS are reported on 248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA; 5.3 remains unrecorded.
Candidate 251 adds real course backup/restore/privacy tests: expected 381 PHPUnit methods (assertions to
measure), Behat 52/1154 unchanged; its native CI and actual-package gates remain pending.
See the [integrated lifecycle evidence](28_PRODUCTION_EXIT_AUDIT.md). U-017/U-007 remain closed. Finish web/App work before
the agreed VTT subtitle phase, then authenticated foundation and OneDrive/SharePoint, Zoom and Google Drive.
