# Videotrack

Videotrack is a Moodle activity module for tracking video viewing, timestamped notes, and contextual student reactions across HTML5/uploaded videos, YouTube and Vimeo.

This repository contains the activity source code and a maintained documentation set under `docs/`. The documentation is intended for future maintainers: it explains the runtime flows, Moodle integration points, AJAX services, AMD modules, data model, build process and known development rules.

## Main capabilities

- Video playback with HTML5/uploaded files, YouTube and Vimeo.
- Watched-segment tracking with unique-covered-seconds calculation.
- Completion rules based on viewing percentage and reactions.
- Student reactions using emoji, Font Awesome or uploaded image icons.
- Timestamped personal notes.
- Teacher reports and course-level reports.
- Gradebook integration and privacy/retention support.
- Moodle backup/restore support.

## Documentation map

- `docs/en/00_INDEX.md` and `docs/it/00_INDEX.md`: entry point for the full technical documentation.
- `docs/en/01_DEVELOPER_GUIDE.md`: development workflow, patch rules and validation checklist.
- `docs/en/02_ARCHITECTURE.md`: component architecture and runtime flows.
- `docs/en/03_FILE_INVENTORY.md`: every file in the plugin and its responsibility.
- `docs/en/04_FUNCTION_INVENTORY.md`: PHP and AMD function inventory.
- `docs/en/05_VARIABLE_INVENTORY.md`: PHP and AMD variable inventory.
- `docs/en/06_RUNTIME_FLOWS.md`: detailed runtime flows for tracking, reactions, notes and replay.
- `docs/en/07_BUILD_TEST_RELEASE.md`: build, test and release commands.
- `docs/en/08_LESSONS_LEARNED.md`: lessons learned during recent maintenance.
- `docs/en/09_DOCUMENTATION_AUDIT.md`: documentation coverage audit and checklist for future updates.

Italian equivalents are available under `docs/it/`.

## Development rule

Before changing code, always start from the latest real ZIP provided by the maintainer, audit the actual files, create a focused patch from the plugin root, and verify `git apply --check` plus `patch -p1 --dry-run` before delivery.

---

# Videotrack

Videotrack è un modulo attività Moodle per tracciare la visualizzazione dei video, le note temporizzate e le reazioni contestuali degli studenti sui player HTML5/upload, YouTube e Vimeo.

Il repository contiene il codice del plugin e una documentazione mantenuta in `docs/`. La documentazione è pensata per chi dovrà manutenere il plugin: descrive flussi runtime, punti di integrazione Moodle, servizi AJAX, moduli AMD, modello dati, build e regole operative.

## Funzionalità principali

- Riproduzione video con HTML5/file caricati, YouTube e Vimeo.
- Tracciamento dei segmenti visti con calcolo dei secondi unici coperti.
- Completamento basato su percentuale vista e reazioni.
- Reazioni studente con emoji, Font Awesome o immagini caricate.
- Note personali temporizzate.
- Report docente e report aggregati di corso.
- Integrazione gradebook e supporto privacy/retention.
- Supporto backup/restore Moodle.

## Mappa della documentazione

- `docs/it/00_INDEX.md` e `docs/en/00_INDEX.md`: punto di ingresso della documentazione tecnica.
- `docs/it/01_DEVELOPER_GUIDE.md`: workflow, regole patch e checklist di validazione.
- `docs/it/02_ARCHITECTURE.md`: architettura componenti e flussi runtime.
- `docs/it/03_FILE_INVENTORY.md`: tutti i file del plugin e relative responsabilità.
- `docs/it/04_FUNCTION_INVENTORY.md`: inventario funzioni PHP e AMD.
- `docs/it/05_VARIABLE_INVENTORY.md`: inventario variabili PHP e AMD.
- `docs/it/06_RUNTIME_FLOWS.md`: flussi dettagliati di tracking, reazioni, note e replay.
- `docs/it/07_BUILD_TEST_RELEASE.md`: comandi build, test e release.
- `docs/it/08_LESSONS_LEARNED.md`: lezioni apprese durante la manutenzione recente.
- `docs/it/09_DOCUMENTATION_AUDIT.md`: audit della copertura documentale e checklist per aggiornamenti futuri.

Gli equivalenti inglesi sono disponibili in `docs/en/`.

## Regola di sviluppo

Prima di modificare codice, partire sempre dall'ultimo ZIP reale fornito dal maintainer, fare audit dei file effettivi, generare una patch focalizzata dalla root del plugin e verificare `git apply --check` più `patch -p1 --dry-run` prima della consegna.


## Integrazione Forum (1.5.0)

Videotrack può collegare opzionalmente un’istanza a un Forum compatibile dello stesso corso. Il pulsante nel player apre un composer separato al timestamp corrente; la bozza contiene un link temporale e viene pubblicata soltanto dopo la conferma. Note personali e reazioni non vengono mai copiate. Dalla 1.5.1 il docente può personalizzare l’oggetto precompilato con i segnaposto `{timestamp}` e `{activity}`.

## Analytics per istanza (1.6.0)

La scheda **Analytics** del report docente calcola dai segmenti una heatmap degli spettatori distinti, la retention lungo la timeline, il tempo di visione unico, il tempo rivisto e i principali cali tra intervalli. I dati sono aggregati, filtrabili per gruppo del corso e protetti da una soglia minima configurabile; non vengono mostrati nominativi. I cluster di reazioni possono essere sovrapposti solo quando rispettano la stessa soglia privacy.

## Instance analytics (1.6.0)

The teacher report **Analytics** tab derives a distinct-viewer heatmap, timeline retention, unique viewing time, repeated viewing time and the main adjacent decreases from saved segments. Data is aggregate, can be filtered by course group and is protected by a configurable minimum-user threshold; no student names are displayed. Reaction clusters can be overlaid only when they meet the same privacy threshold.
