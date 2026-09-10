# Indice della documentazione VideoTrack

Questa è la documentazione italiana autorevole di VideoTrack **1.7.128** (`2026091003`), compatibile
con Moodle 5.0–5.3. Descrive esclusivamente l'albero corrente. La cronologia delle release appartiene
al `CHANGELOG.md` principale e al tag sorgente della versione interessata.

## Da dove iniziare

1. [`funzionalita.md`](funzionalita.md) — guida completa per utenti e amministratori.
2. [`01_DEVELOPER_GUIDE.md`](01_DEVELOPER_GUIDE.md) — workflow di sviluppo sicuro e confini di fiducia.
3. [`02_ARCHITECTURE.md`](02_ARCHITECTURE.md) — componenti, responsabilità e integrazioni.
4. [`struttura_tecnica.md`](struttura_tecnica.md) — mappa tecnica sintetica per orientarsi.

## Documentazione di riferimento

5. [`03_FILE_INVENTORY.md`](03_FILE_INVENTORY.md) — tutti i file non documentali distribuiti.
6. [`04_FUNCTION_INVENTORY.md`](04_FUNCTION_INVENTORY.md) — callable PHP e AMD nominati.
7. [`05_VARIABLE_INVENTORY.md`](05_VARIABLE_INVENTORY.md) — tabelle, impostazioni, capability, servizi e file area.
8. [`06_RUNTIME_FLOWS.md`](06_RUNTIME_FLOWS.md) — flussi correnti dal caricamento pagina alla persistenza.
9. [`07_BUILD_TEST_RELEASE.md`](07_BUILD_TEST_RELEASE.md) — build, validazione e gate di release.
10. [`08_MAINTENANCE_RULES.md`](08_MAINTENANCE_RULES.md) — regole obbligatorie di manutenzione.
11. [`09_DOCUMENTATION_AUDIT.md`](09_DOCUMENTATION_AUDIT.md) — audit di copertura, freschezza e parità.
12. [`10_BOOKMARKS_AND_ANALYTICS.md`](10_BOOKMARKS_AND_ANALYTICS.md) — dati di studio personali e analytics aggregati.
13. [`11_INTEGRITY_AND_FOCUS.md`](11_INTEGRITY_AND_FOCUS.md) — segnali diagnostici e politica focus.
14. [`12_ACKNOWLEDGEMENT.md`](12_ACKNOWLEDGEMENT.md) — presa visione learner versionata.
15. [`13_SECURITY_TRUST_BOUNDARIES.md`](13_SECURITY_TRUST_BOUNDARIES.md) — autorità server e modello di sicurezza.
16. [`14_INSTALL_UPGRADE_BACKUP_RESTORE.md`](14_INSTALL_UPGRADE_BACKUP_RESTORE.md) — operazioni sul ciclo di vita dati.
17. [`15_MEDIA_PROVIDERS_DURATION.md`](15_MEDIA_PROVIDERS_DURATION.md) — contratti HTML5, YouTube e Vimeo.
18. [`16_ROLES_CAPABILITIES.md`](16_ROLES_CAPABILITIES.md) — partecipazione e modello dei permessi.
19. [`17_TRACKING_COMPLETION_GRADEBOOK.md`](17_TRACKING_COMPLETION_GRADEBOOK.md) — evidenze viste e risultati.
20. [`18_PRIVACY_RETENTION.md`](18_PRIVACY_RETENTION.md) — dati personali, Privacy API e retention.
21. [`19_ACCESSIBILITY.md`](19_ACCESSIBILITY.md) — tastiera, focus, reflow e tecnologie assistive.
22. [`20_TROUBLESHOOTING.md`](20_TROUBLESHOOTING.md) — diagnostica operativa per sintomo.
23. [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md) — validatore in sola lettura e benchmark Analytics.
24. [`22_TEST_BROWSER_BEHAT.md`](22_TEST_BROWSER_BEHAT.md) — copertura browser deterministica.
25. [`23_GITHUB_ACTIONS_CI.md`](23_GITHUB_ACTIONS_CI.md) — matrice CI, gate, artefatti e diagnostica del repository.
26. [`24_ANALISI_STATICA.md`](24_ANALISI_STATICA.md) — perimetro, esecuzione e risanamento PHPStan, Psalm e PHPMD.

## Artefatti database ed ER

- [Riferimento database/ER](../VIDEOTRACK_DB_ER_SCHEMA.md)
- [Sorgente Mermaid](../VIDEOTRACK_DB_ER_SCHEMA.mmd)
- [SVG accessibile](../VIDEOTRACK_DB_ER_SCHEMA.svg)

Il riferimento ER distingue dichiarazioni XMLDB, collegamenti Moodle standard, riferimenti condizionali
e snapshot denormalizzati. Una linea del diagramma non implica da sola un vincolo fisico nel database.

## Parità linguistica

L'albero inglese in `docs/en/` ha lo stesso perimetro e la stessa struttura documentale. I nomi dei file
differiscono solo dove viene mantenuta una denominazione italiana consolidata. Una release non è completa
finché entrambi gli alberi e le README principali non concordano con il codice corrente.
