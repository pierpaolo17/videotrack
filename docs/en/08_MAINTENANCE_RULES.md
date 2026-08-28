# Maintenance rules

These rules protect the current runtime and are mandatory for all changes.

1. **Use the real baseline.** Audit the last real ZIP/tree; never infer its contents from a proposed patch.
2. **Reconstruct the executed path.** For runtime defects trace page, AMD entrypoint, provider adapter, AJAX
   method, server service, persistence and returned payload before editing.
3. **Keep providers separate.** HTML5, YouTube and Vimeo implement one contract with different SDK timing.
4. **Keep the server authoritative.** Browser timestamps, progress and focus are requests or diagnostics.
5. **Preserve idempotency.** Retryable writes need stable request identifiers and safe duplicate handling.
6. **Respect Moodle scope.** Validate login, context, course module, capability, group/cohort and ownership.
7. **Separate participation from reporting.** Do not use report access to infer learner status.
8. **Minimise personal data.** Do not expose note/bookmark text to aggregates or broaden export fields silently.
9. **Treat completion as one composite rule.** Presentation changes must not split or change AND/OR semantics.
10. **Use Moodle APIs.** File, grade, completion, privacy, backup, event and lock operations use core APIs.
11. **Keep AMD source/build paired.** Edit `amd/src`, run Moodle Grunt and ship generated builds/maps.
12. **Make upgrades resumable.** Schema steps are idempotent, ordered and end in a plugin savepoint.
13. **Test lifecycle effects.** Data changes require privacy, retention, reset and backup/restore review.
14. **Maintain both languages.** English and Italian documentation must describe the same current contract.
15. **Do not embed release archaeology.** Current docs explain the current tree; older detail lives in old tags.
16. **Record exact evidence.** A result from another tree, Moodle branch or browser is not transferable.

## Definition of done

A change is complete only when source, generated assets, schema/upgrade, language placeholders, documentation,
tests, patch/package verification and required manual checks agree. Deferred checks are stated explicitly and
remain release blockers when they cover the changed behaviour.
