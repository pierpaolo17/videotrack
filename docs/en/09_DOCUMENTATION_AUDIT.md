# Documentation audit

Current candidate: VideoTrack **1.7.273** (`2026112501`), incremental correction
on published commit `ca91e4a0ce64e6b5f49954b355096cbf66c32c76`,
tree `5ced30d5fd65d29341304517531f7d3dc9d05c12`.

## Initial 273 CI evidence

Run 45778182389 attempt 1 has five successful jobs. The sole failed step is Moodle
Code Checker in 50 MariaDB: 9 PHPCS formatting errors on 6 lines in
tests/form_duration_validation_test.php. The correction changes whitespace only
in that test; its executable tokens and all assertions are preserved.

All six matrices pass PHPUnit 645/6663, Behat 82/1541 without inner retries,
38 JavaScript groups plus the tracker harness, strict 10/0/0 and native Grunt.
All six native manifests identify ca91e4a0; 48 source and 96 build/map hashes match
the delivered code, maps match sources, and wrapper payloads match report payloads.
53 MariaDB passes historical/partial/previous upgrade, repeat and populated uninstall;
three preserved snapshot hashes were independently reconstructed. 50 MariaDB lifecycle
did not run after the quality failure and remains a gate for the correction.

PHPStan is clean in the reported 50/53 jobs. The two Psalm annotations are the same
ParadoxicalCondition at db/services.php:25, with 765/793 information entries.
PHPMD reports 43 advisories in this initial 273 scan; these are not called zero or
unchanged from 272. The corrected static-analysis results remain pending.

## Corrective scope

- Fix the 9 reported formatting violations in the duration test.
- Keep the mandatory MOODLE_INTERNAL guard and the complete service registry
  byte-identical. Psalm configuration narrowly suppresses ParadoxicalCondition
  only in db/services.php, whose bootstrap-known guard triggers the false positive.
  No global suppression or runtime guard removal is introduced.
- Clarify ALL / AT LEAST ONE completion instructions in all 8 language packs and
  update the existing completion-layout browser assertion to the new label.
- Make dismissible player notices match the provider privacy banner in width,
  typography and spacing. Keep media/layout/study-tool width via the existing
  configured width passed as a CSS custom property. ARIA, dismissal and timeouts stay.

No AMD source, build or map changes; no native rebuild is needed. Schema, upgrade,
privacy, backup, credentials, retained evidence, tracking/replay and duration policy
remain unchanged. Version/release stay 273/2026112501 while the candidate is pending.
Counts remain 523 repository / 496 distributed files, 207 distributed PHP, 1591 named PHP,
668 named AMD, 645 unit methods, 82 Behat / 1541 steps, 8×1078 keys and 48 AMD sets;
7 tables / 152 fields / 22 indexes / 22 foreign keys and 9 AJAX methods.

## Gates

Local token/asset/language/XML/patch checks are distinct from native CI. Corrected
PHPCS, Psalm, all six CI jobs, both lifecycle matrices, server checks and visual
acceptance remain pending. The initial functional passes do not replace these gates.
Microsoft HTTPS/account acceptance and the provider/App roadmap remain separate.
