# Lessons learned

- Audit the real archive; patch history is not proof of installed state.
- Reconstruct the executed player path instead of fixing a guessed abstraction.
- HTML5, YouTube and Vimeo share goals but not callback semantics.
- User, programmatic, resume and replay seeks need separate state.
- Web Service declarations, parameter validation and actual JSON responses form one contract.
- Privacy thresholds must be applied independently to populations that can differ.
- Private text must never leak through aggregate reports or convenient debug output.
- Accessibility failures can be caused by a single ancestor `aria-hidden`, not only missing labels.
- Window blur is not equivalent to hidden content; strict focus controls need an accessible default and clear limits.
- Large Privacy API exports require buffer reset after every emitted chunk.
- Tests must assert the current export schema; stale column counts are real failures.
- Documentation with old release notes mixed into a README becomes misleading. Current contracts and historical records must be separated.
- Equal language key counts do not prove translation quality; copied English values and placeholder parity need separate audits.
- Generated AMD output is evidence only after the actual build completes.

- A retryable write needs an idempotency key generated before the retry layer; transport success is not proof that the first response reached the browser.
- A playback guard must start with zero credit. Provider/server drift tolerance must remain cumulative debt across requests and handshakes, otherwise repeated play/pause cycles can manufacture progress.
- Compact state and exact aggregates have different responsibilities: cap the stored interval list, but calculate authoritative unique coverage from validated raw rows and keep completion monotonic.
- When a privacy fix suppresses a denominator, tests must use a non-inferible fixture or assert suppression; weakening the privacy rule to satisfy an old expectation is incorrect.
