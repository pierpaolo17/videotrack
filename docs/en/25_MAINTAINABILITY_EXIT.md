# U-017 maintainability exit audit

Current tree: VideoTrack **1.7.234** (`2026110504`).

## Decision and evidence status

This tree is the U-017 exit candidate. Stop the planned structural extraction sequence after this consolidation;
retain the residual findings below with explicit ownership and reasons. No production implementation, AMD asset,
public service signature, schema or analysis rule changes in this release. Four additional persisted-effect tests
close specific coverage gaps in the existing tracker and real segment endpoint.

The accepted baseline is the real 396-file 1.7.233 ZIP, SHA-256
`5d23832480f34395e233fcfaaf1a7a1c5054d3cab3b668ad320640b56c7ac7e7`, identical to merged commit
`6554576e60651cdd295b826eb00ccf2bd3b97adf`. Its six initial and six post-merge CI jobs pass 365 PHPUnit tests,
3,920 assertions, 24 Behat scenarios and ten strict checks each. The server report for Moodle 5.0/5.3 passes
PHPCS, PHP lint, Grunt and the same PHPUnit suite. Both supplied server strict results are 10 PASS / 0 WARN / 0 FAIL.

The new 1.7.234 tests have not been executed in the patch-generation environment because PHP is unavailable there.
They are acceptance requirements, not claimed passing evidence. Mark U-017 closed only after the exact candidate's
six CI jobs, post-merge artifacts, real ZIP and proportionate server/strict gates have passed. A failure keeps this
exit candidate open and requires a bounded correction. No fixed number of further refactoring releases is required.

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

New tests use real activities, enrolled learners, ledger/state tables and Moodle's event sink. They use a supplied
server clock for tracker cases; the real endpoint case uses its production clock after an explicit older handshake.
No sleep or provider network request is needed. Rejected rows remain audit evidence and do not enter validated
coverage. The cap case checks the live database rebuild, not only the pure aggregation helper.

## Reviewed PHPMD residuals

The actual 1.7.233 report contains **24** findings. Candidate 1.7.234 leaves every analysed production body and
ruleset unchanged, so the expected total remains 24; verify the full report in CI, including absence of additions.
This is an explicit debt register, not a suppression, zero-finding claim or blanket acceptance of future findings.

| File/owner | Findings | Reason to retain and condition for reopening |
|---|---:|---|
| `classes/external/save_segment.php` | 1 parameter-list finding | The ten-field public AJAX signature matches its declaration and callers. Reopen only with a deliberate service-contract change. |
| `classes/local/csv_export.php` | 1 public-method-count finding | Stable facade with focused implementation owners. Reopen for a real export API change, preserving context and identity checks. |
| `classes/local/tracker.php` | 6 | Class size/public API/complexity and `update_state()` complexity, NPath and length. Keep ledger, state and transaction effects together; any future split must preserve the effect matrix above. |
| `db/upgrade.php` | 3 | Complexity, NPath and length reflect ordered historical migrations. Do not rewrite deployed upgrade history for a metric. Reopen for an evidenced upgrade defect. |
| `lib.php` | 11 | Nine unused formal-parameter findings on callback/public signatures and two established boolean arguments. Preserve Moodle/lifecycle callers; revisit only with a concrete compatibility-safe API change. |
| `mod_form.php` | 2 | Class complexity and `definition()` length remain after field, validation and preprocessing separation. The form has 23 methods and retains framework orchestration. Reopen for a functional form change with a useful new boundary. |
| **Total** | **24** | No analyser rule, scope or baseline file is weakened. |

## Promotion and next phase

1. Candidate CI must pass the complete six-job matrix and compare all PHPMD findings; PHPStan/Psalm retain their
   current production scope and reviewed advisory/deprecation output.
2. Check the merged tree and all six post-merge artifact digests against the approved candidate.
3. Accept the real release ZIP, server PHPCS/lint/Grunt/PHPUnit results on Moodle 5.0/5.3 and both strict outputs.
4. If these gates pass, close U-017 with this debt register and stop structural micro-releases until new evidence
   warrants one. A production defect always overrides the stop decision.

Then continue the web roadmap: U-007 browser/provider/role matrix, U-020 manual accessibility, semantic translation
review and the final production/lifecycle/privacy/benchmark audit on one exact ZIP. Automation does not certify
manual accessibility, every browser/SDK or production operations. Evaluate Moodle App after those web gates;
OneDrive/SharePoint, Zoom Cloud Recording and Google Drive remain later phases in that order.

See [static-analysis policy](24_STATIC_ANALYSIS.md), [browser coverage](22_BEHAT_BROWSER_TESTS.md),
[release gates](07_BUILD_TEST_RELEASE.md) and [accessibility](19_ACCESSIBILITY.md).
