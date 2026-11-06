# U-017 maintainability exit audit

Current tree: VideoTrack **1.7.239** (`2026110602`).

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

Accepted 1.7.237 includes viewing completion, allowed-seek gaps and post-rollback learner interactions
for all three providers. All six release/main CI jobs pass 33 Behat scenarios / 618 steps and
369 PHPUnit tests / 3,988 assertions. Its actual ZIP matches the main commit and server/strict gates pass.
Accepted 1.7.238 adds the requested teacher attention configuration. All six main CI jobs pass
379 PHPUnit tests / 4,076 assertions and 41 Behat scenarios / 722 steps; actual ZIP/server/strict are accepted.
Candidate 1.7.239 resumes U-007 with natural end, replay and rates; U-017 remains closed.

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

Accepted 1.7.236 CI confirms **24** unchanged findings. Candidate 1.7.237 preserves production PHP bodies
and rulesets, so compare the full report again in candidate CI. This is an explicit debt register, not a suppression, zero-finding claim or blanket acceptance of future findings.

| File/owner | Findings | Reason to retain and condition for reopening |
|---|---:|---|
| `classes/external/save_segment.php` | 1 parameter-list finding | The ten-field public AJAX signature matches its declaration and callers. Reopen only with a deliberate service-contract change. |
| `classes/local/csv_export.php` | 1 public-method-count finding | Stable facade with focused implementation owners. Reopen for a real export API change, preserving context and identity checks. |
| `classes/local/tracker.php` | 6 | Class size/public API/complexity and `update_state()` complexity, NPath and length. Keep ledger, state and transaction effects together; any future split must preserve the effect matrix above. |
| `db/upgrade.php` | 3 | Complexity, NPath and length reflect ordered historical migrations. Do not rewrite deployed upgrade history for a metric. Reopen for an evidenced upgrade defect. |
| `lib.php` | 11 | Nine unused formal-parameter findings on callback/public signatures and two established boolean arguments. Preserve Moodle/lifecycle callers; revisit only with a concrete compatibility-safe API change. |
| `mod_form.php` | 2 | Class complexity and `definition()` length remain after field, validation and preprocessing separation. The form has 23 methods and retains framework orchestration. Reopen for a functional form change with a useful new boundary. |
| **Total** | **24** | No analyser rule, scope or baseline file is weakened. |

## Current roadmap phase

U-007 remains open. Accepted 1.7.236 confirms viewing-completion and allowed-seek gap cases for all three providers.
The 1.7.237 post-rollback interaction cases are accepted; the 1.7.238 attention cases are accepted; the new 1.7.239 end/replay/rate cases require candidate CI. Review the remaining playback, interaction, role/group and provider-parity cells in
[browser coverage](22_BEHAT_BROWSER_TESTS.md). Chrome CI is a browser execution on six Moodle/PHP/database jobs;
it is not a six-browser matrix. Public SDK/network smoke and other browser coverage remain explicit later checks.

After U-007, continue U-020 manual accessibility, semantic translation review and the final production/lifecycle/
privacy/benchmark audit on one exact ZIP. Evaluate Moodle App after the web gates. OneDrive/SharePoint, Zoom Cloud
Recording and Google Drive remain later phases in that order. A real production defect can reopen U-017.

See [static-analysis policy](24_STATIC_ANALYSIS.md), [release gates](07_BUILD_TEST_RELEASE.md) and
[accessibility](19_ACCESSIBILITY.md).

## Current acceptance and remaining roadmap

The actual 405-file 1.7.238 ZIP matches main `27299bc43e5d63dd6e18dda7325412551fa95a14`.
Six main jobs pass Behat 41/722 and PHPUnit 379/4076. Server PHPCS/lint/Grunt/PHPUnit and both strict outputs
pass; the server report does not execute Behat. Initial candidate defects were corrected in 1.7.238 and are
superseded by this accepted baseline. No structural gate is reopened.

Candidate 1.7.239 adds three end/replay/rate browser cases (44/830 expected), with production code unchanged.
After candidate CI and actual release acceptance, assess relevant roles/group boundaries and the explicit
additional-browser/public-provider checks. U-007 remains open. Then follow U-020/manual WCAG, semantic
translations and the integrated production/lifecycle/privacy/benchmark gate on the exact release ZIP.
Subsequent order: Moodle App evaluation, shared authenticated-provider foundation, OneDrive/SharePoint,
Zoom Cloud Recording, Google Drive and cross-provider hardening.
