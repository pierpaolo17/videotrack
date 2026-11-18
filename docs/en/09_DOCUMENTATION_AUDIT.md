# Documentation audit

Baseline: VideoTrack **1.7.260** (`2026111800`).

## Accepted 1.7.259 baseline

The baseline is main `243983ae3cc93af7c8a702014d4e58cad0d0f53f`, tree
`4aaddc578a96e73680853d7edb1768fd36124246`. The actual maintainer ZIP has 433 files
matching the corresponding blobs; all 457 repository blobs/modes match the isolated
baseline. Package SHA-256:
`30b8c99c21fc01be2e4e51ddef40fa9458e3ccf908d54ec7ff465d03903e82c8`.

Accepted corrected candidate/main CI passed all six jobs, PHPUnit 448/5071,
Behat 54/1194, strict 10/0/0 and selected historical/interrupted/previous-release
upgrade, repeat and populated-uninstall paths. The actual server report confirms
PHPCS/lint 181 clean, Unit 448/5071 on Moodle 5.0/5.3 and unchanged Grunt builds.
The maintainer's strict outputs pass 10/0/0 on both sites. These are baseline
results, not execution of the new source-policy regressions.

PHPMD 31 findings and Psalm 742/770 information items remain advisory;
PHPStan/Psalm report no configured errors. Three old Node harness failures remain:
obsolete Privacy/backup assertions and a missing core/str tracker stub. Manual
Moodle 5.3 accessibility remains on standby. Retain the synthetic demo.

A broader local run of the 19 legacy Node harnesses produced two passes and 17
failures identical to accepted 1.7.259 after normalising paths. These historical
harness failures are not native Moodle test results or newly introduced failures.

## Current candidate scope

Independent default-on switches for the three implemented sources apply in the
form, save callbacks, view/completion entry, new telemetry and direct local media.
Existing disabled edit values are explicit. All-off has no creation fallback.
Reports/data are retained, owner erasure retains its access checks and the validator
records effective flags. See [source policy](31_SOURCE_POLICY.md).

The optional separate Zoom repository is an architectural assessment. Native
player timing/control, authorisation and provider-to-browser delivery remain
future provider gates. Existing Microsoft diagnostics, AMD, schema, upgrades
and service contracts are unchanged.

## Inventory and evidence boundary

The tree has 462 repository / 438 distributed files, 182 distributed PHP files plus
one repository lifecycle helper, 1261 named PHP callables (1254 distributed + 7 helper
callables), 654 AMD callables and eight packs of 1013 matching keys/placeholders.
The curated inventory has 371 entries (366 distributed + five repository-only).
There are 463 PHPUnit methods (15 new) and four new browser scenarios; the expected
expanded Behat suite is 58 scenarios, pending native execution.

Local source/patch/inventory checks do not establish native PHP behaviour.
Native PHPCS, lint, PHPUnit, Behat, lifecycle and configured analysers must run in
candidate CI, followed by main CI, actual-package identity, server checks and strict
validation. No current-candidate native result or live provider access is claimed.
Paired guides/inventories describe this candidate. Older release history belongs
in CHANGELOG and the relevant source tag.
