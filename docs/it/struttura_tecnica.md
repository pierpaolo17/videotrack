# Struttura tecnica sintetica

Questa pagina è una mappa di navigazione. Per il contratto completo usare i documenti e i sorgenti collegati.

| Livello | Percorsi principali | Responsabilità |
|---|---|---|
| Entrypoint Moodle | `view.php`, `index.php`, `report.php`, `course_report.php`, `teacher_report.php` | Accesso, contesto, rendering e routing report. |
| Ciclo attività | `lib.php`, `mod_form.php`, `classes/local/form_validation.php`, `db/install.xml`, `db/upgrade.php` | Creazione/modifica/cancellazione, validazione contestuale e autonoma del form, file, voti e schema. |
| Helper input multimediali | `locallib.php`, `docs/it/15_MEDIA_PROVIDERS_DURATION.md` | Normalizzazione URL HTTPS, estrazione identificatori provider, parsing timestamp e helper condivisi di presentazione. |
| Servizi dominio | `classes/local/` | Tracking, analytics, privacy, export, scope report, Forum e configurazione. |
| Completion | `classes/completion/custom_completion.php`, `classes/local/completion_config.php` | Regola composita VideoTrack, descrizioni ordinate delle condizioni, firme di configurazione e sincronizzazione Moodle. |
| Confine AJAX | `db/services.php`, `classes/external/` | Nove metodi di scrittura autenticati con validazione di parametri, contesto e capability. |
| Core browser | `amd/src/core/` | Stato, trasporto API, sessione, lifecycle tracking, status e controller interazioni. |
| Adapter provider | `amd/src/html5_player.js`, `player.js`, `vimeo_player.js` | Contratti di riproduzione HTML5, YouTube e Vimeo. |
| Asset generati | `amd/build/` | Output Moodle Grunt; non modificare manualmente. |
| Privacy | `classes/privacy/provider.php`, `classes/local/privacy_manager.php`, `db/tasks.php` | Metadata, export/cancellazione e retention pianificata. |
| Backup/restore | `backup/moodle2/` | Configurazione, file, evidenze utente conservabili e rimappatura identificatori. |
| Diagnostica | `cli/`, `classes/local/environment_validator.php` | Validazione installazione e benchmark Analytics in sola lettura. |
| Test | `tests/`, `tests/behat/`, `tests/generator/` | Contratti PHPUnit e scenari browser deterministici. |
| Documentazione | `docs/en/`, `docs/it/`, `docs/VIDEOTRACK_DB_ER_SCHEMA.*` | Riferimento corrente per utenti e sviluppatori. |

## Flusso dati

1. `view.php` valida modulo, contesto e capability ed emette configurazione JSON localizzata.
2. L'adapter provider e il controller browser aprono una sessione tramite `start_playback`.
3. Gli eventi lifecycle inviano richieste segmento idempotenti tramite `save_segment`.
4. `classes/local/tracker.php` valida il credito server e aggiorna la copertura unica.
5. Completion, voto/report e interazioni personali sono derivati o interrogati nello scope Moodle.
6. Privacy, retention, backup/restore e reset operano sugli stessi confini dati dichiarati.

## Confini non negoziabili

- Tempo browser e stato provider sono richieste, non evidenze viste autorevoli.
- `mod/videotrack:participate` è indipendente dall'accesso ai report.
- Report/export aggregati e individuali usano capability separate.
- Testo note ed etichette bookmark sono contenuti privati esclusi dagli Analytics aggregati.
- `amd/src` è canonico; `amd/build` è generato.
- HTML5, YouTube e Vimeo vanno testati come adapter distinti.

Vedere [`02_ARCHITECTURE.md`](02_ARCHITECTURE.md), [`06_RUNTIME_FLOWS.md`](06_RUNTIME_FLOWS.md) e il
[riferimento database/ER](../VIDEOTRACK_DB_ER_SCHEMA.md).
