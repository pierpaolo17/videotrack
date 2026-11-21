# Preserve and interpret Behat evidence

VideoTrack **1.7.267** (`2026112100`).

## Why this maintenance release exists

Accepted264 main [run44702501925](https://github.com/pierpaolo17/videotrack/actions/runs/44702501925)
finished with six successful jobs, but its retry history matters. Moodle5.1 initially
failed a post-rollback YouTube note visibility assertion; the internal rerun passed
one scenario/43steps. The workflow previously uploaded browser dumps only when the
final Behat step failed, so that successful rerun hid the available first-failure
browser evidence from the published artifacts.

The initial5.2 job lost access to localhost:8000 during Behat. A fresh complete job,
without source changes, passed67scenarios/1327steps without an internal rerun. The
available logs do not establish why the HTTP service became unavailable. Neither
a product fix for the note assertion nor an out-of-memory diagnosis is claimed.

## Artifacts from265

| Artifact | Contents and interpretation |
|---|---|
| `videotrack-ci-<matrix-id>` | Existing command logs, including the full `behat.txt`, plus `behat-runtime.txt` when the Behat step completed with success or failure and the job was not cancelled. |
| `videotrack-behat-faildump-<matrix-id>` | Available files below `moodledata/behat_dump`, even if an internal rerun later succeeds. Upload is attempted on every job outcome; an absent directory is ignored. No dump is invented or guaranteed. |

Retention remains14days. A clean run normally has no faildump artifact. Compare
the full command output with the dump, commit, matrix entry and Actions run attempt;
a green final step does not erase an earlier failure. Automatic internal reruns
and a fresh complete Actions job are different events and must be reported separately.

The completion snapshot records the final Behat step outcome, UTC time, `free -m`
and `ps -eo pid,ppid,stat,comm,rss`. It excludes process arguments and environment.
If either resource utility fails, the report says its snapshot is unavailable.
It runs immediately after Behat, before the historical lifecycle step can alter
the disposable site, and does not run when Behat was skipped or the job cancelled.

This is a runner snapshot after the command, not an HTTP availability test or a
continuous monitor. Upstream [BehatCommand](https://github.com/moodlehq/moodle-plugin-ci/blob/ab2928c7982a33f325a413a4dfc9ef4ad6a7e23e/src/Command/BehatCommand.php)
stops the servers it starts when the test command finishes. Consequently there is
no post-command HTTP probe: a refused connection then could be ordinary teardown.
The snapshot cannot prove the earlier state of a process, a memory spike or the
cause of a transient failure. Keep the original failure evidence when restarting
a job; a later passing attempt demonstrates that attempt's result only.

## Gates and remaining work

The Behat command, its pipefail/exit handling, automatic-rerun defaults, six-entry
matrix, assertions, quality rules and lifecycle success condition are retained.
No failing test is converted into success by the collection steps. Faildump upload
does not change the outcome of an earlier failed step. New266 native candidate/main
CI, actual package/server checks and both strict outputs are still required.

The265 maintenance changed CI evidence/documentation and release metadata only.
Its evidence collection has no production playback or data side effect. The separate
266 diagnostic preview is described in [its guide](37_MICROSOFT_PRIVATE_PREVIEW.md). The manual5.3 accessibility check remains on standby. A real delegated
Microsoft browser round trip is still unexecuted; supported provider authentication
and time/play/pause/seek telemetry remain gates before tracked Microsoft playback.
Next integration priorities remain Microsoft, Zoom, Google Drive and finally App.
