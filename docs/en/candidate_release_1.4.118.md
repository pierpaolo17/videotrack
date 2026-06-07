# Videotrack 1.4.118 - Candidate Release Checkpoint

## Scope

Videotrack 1.4.118 is a candidate release checkpoint after the AMD, WCAG and Moodle HQ static audit passes.

This release does not change runtime behaviour and does not alter:

- tracking;
- segmentation;
- notes;
- reactions;
- analytics;
- resume logic;
- report calculations.

## Closed Areas

- Concurrency hardening.
- Localisation alignment.
- GDPR strict confirmation for unlimited retention.
- Accessibility safe improvements.
- Report scalability safe improvements.
- AJAX layer documentation and decomposition.
- Tracker layer decomposition.
- Player layer decomposition to the agreed maintainability target.
- AMD dependency audit.
- WCAG static audit.
- Moodle HQ static audit.

## Mandatory Verification Before Submission

The following checks are expected before Moodle Plugins Directory submission:

1. Apply the final package on a clean Moodle 5.0 installation.
2. Run Moodle strict/codechecker review.
3. Run PHPUnit in a configured Moodle test environment.
4. Perform a manual WCAG screen-reader and keyboard navigation pass.
5. Confirm that no AMD files are dirty after `grunt amd`.

## Environment Notes

The project build environment uses Moodle 5.0 sources and Moodle node_modules. AMD build validation is performed with:

```bash
node node_modules/grunt/bin/grunt amd --root=mod/videotrack
```

If Rollup/Terser worker instability appears in the local environment, use the previously documented `numWorkers = 1` workaround in the Moodle build task. This is an environment workaround, not plugin runtime behaviour.

## PHPUnit Status

PHPUnit has not been executed in the container environment because a complete Moodle installation with database, PHP extensions and PHPUnit bootstrap is required.

## Candidate Status

This checkpoint is suitable for local strict Moodle HQ validation. Findings from that validation should drive any subsequent 1.4.119+ patches.
