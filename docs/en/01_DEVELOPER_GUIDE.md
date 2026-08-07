# Developer guide

## Baseline rule

Start from the latest real ZIP supplied by the maintainer. Record its checksum and inspect `version.php`, XMLDB, generated AMD assets and local validation logs before editing. Never infer that a previous patch is installed.

## Change workflow

1. Extract the archive into a clean worktree and create a baseline commit.
2. Reconstruct the real request/runtime path. For player defects, analyse HTML5, YouTube and Vimeo separately.
3. Make the smallest coherent change. Keep privacy, accessibility, backup/restore, completion, reports and translations in the same scope when the data contract changes.
4. Update English plus all seven translated language packs. Preserve Moodle placeholders exactly.
5. Update the numbered documentation and both root README/privacy pairs.
6. If `amd/src/*` changes, run the actual Moodle `grunt amd` task and include the matching `.min.js` and `.map` files.
7. Run static checks, PHPUnit and PHPCS where available. Report failures honestly.
8. Generate the patch from the plugin root with `a/` and `b/` paths.
9. Verify `git apply --check`, actual application to a fresh baseline, tree equality and `patch -p1 --dry-run`.

## Trust boundaries

- The browser is not trusted for ownership, watched-position or completion decisions.
- Every write service validates parameters, login, module context, capability and ownership/feature state.
- Private text is never copied into aggregate reports.
- Browser focus and Picture-in-Picture controls are best effort; do not promise enforcement unavailable from a browser/provider.
- Integrity signals are diagnostic and cannot independently prove cheating.

## Coding conventions

Follow Moodle coding style, XMLDB rules and Moodle 5.0 APIs. Keep large configuration payloads in a DOM JSON script element instead of `js_call_amd()` arguments. Keep source and generated AMD assets synchronised. Use namespaced classes for reusable logic and explicit Moodle events for auditable actions.

## Definition of done

A release is not complete until code, schema, services, language keys/placeholders, generated assets, Privacy API, reset/deletion, backup/restore, reports/exports and documentation agree with the same current contract.
