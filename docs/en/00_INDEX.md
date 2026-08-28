# VideoTrack documentation index

This is the authoritative English documentation for VideoTrack **1.7.117** (`2026082801`),
supporting Moodle 5.0–5.3. It describes the current tree only. Release history belongs in the
root `CHANGELOG.md` and in the source tag for the relevant version.

## Start here

1. [`funzionalita.md`](funzionalita.md) — complete user and administrator guide.
2. [`01_DEVELOPER_GUIDE.md`](01_DEVELOPER_GUIDE.md) — safe development workflow and trust boundaries.
3. [`02_ARCHITECTURE.md`](02_ARCHITECTURE.md) — components, responsibilities and integrations.
4. [`struttura_tecnica.md`](struttura_tecnica.md) — compact technical map for orientation.

## Reference documentation

5. [`03_FILE_INVENTORY.md`](03_FILE_INVENTORY.md) — every distributed non-documentation file.
6. [`04_FUNCTION_INVENTORY.md`](04_FUNCTION_INVENTORY.md) — named PHP and AMD callables.
7. [`05_VARIABLE_INVENTORY.md`](05_VARIABLE_INVENTORY.md) — tables, settings, capabilities, services and file areas.
8. [`06_RUNTIME_FLOWS.md`](06_RUNTIME_FLOWS.md) — current runtime sequences from page load to persistence.
9. [`07_BUILD_TEST_RELEASE.md`](07_BUILD_TEST_RELEASE.md) — build, validation and release gates.
10. [`08_MAINTENANCE_RULES.md`](08_MAINTENANCE_RULES.md) — mandatory rules for safe maintenance.
11. [`09_DOCUMENTATION_AUDIT.md`](09_DOCUMENTATION_AUDIT.md) — scope, freshness and parity audit.
12. [`10_BOOKMARKS_AND_ANALYTICS.md`](10_BOOKMARKS_AND_ANALYTICS.md) — personal study data and aggregate analytics.
13. [`11_INTEGRITY_AND_FOCUS.md`](11_INTEGRITY_AND_FOCUS.md) — diagnostic signals and focus policy.
14. [`12_ACKNOWLEDGEMENT.md`](12_ACKNOWLEDGEMENT.md) — versioned learner acknowledgement.
15. [`13_SECURITY_TRUST_BOUNDARIES.md`](13_SECURITY_TRUST_BOUNDARIES.md) — server authority and security model.
16. [`14_INSTALL_UPGRADE_BACKUP_RESTORE.md`](14_INSTALL_UPGRADE_BACKUP_RESTORE.md) — data lifecycle operations.
17. [`15_MEDIA_PROVIDERS_DURATION.md`](15_MEDIA_PROVIDERS_DURATION.md) — HTML5, YouTube and Vimeo contracts.
18. [`16_ROLES_CAPABILITIES.md`](16_ROLES_CAPABILITIES.md) — participation and permission model.
19. [`17_TRACKING_COMPLETION_GRADEBOOK.md`](17_TRACKING_COMPLETION_GRADEBOOK.md) — watched evidence and outcomes.
20. [`18_PRIVACY_RETENTION.md`](18_PRIVACY_RETENTION.md) — personal data, Privacy API and retention.
21. [`19_ACCESSIBILITY.md`](19_ACCESSIBILITY.md) — keyboard, focus, reflow and assistive-technology contract.
22. [`20_TROUBLESHOOTING.md`](20_TROUBLESHOOTING.md) — symptom-led operational diagnostics.
23. [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md) — read-only validator and analytics benchmark.
24. [`22_BEHAT_BROWSER_TESTS.md`](22_BEHAT_BROWSER_TESTS.md) — deterministic browser coverage.

## Database and ER artefacts

- [Database/ER reference](../VIDEOTRACK_DB_ER_SCHEMA.md)
- [Mermaid source](../VIDEOTRACK_DB_ER_SCHEMA.mmd)
- [Accessible SVG](../VIDEOTRACK_DB_ER_SCHEMA.svg)

The ER reference distinguishes XMLDB declarations, standard Moodle links, conditional references and
denormalised snapshots. Do not infer a physical database constraint from a diagram line alone.

## Language parity

The Italian tree under `docs/it/` has the same scope and document structure. File names differ only
where an established Italian name is retained. A release is not documentation-complete until both
trees and the root README pair agree with the current code.
