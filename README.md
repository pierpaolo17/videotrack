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

## Analytics per istanza (1.6.1)

La scheda **Analytics** del report docente calcola dai segmenti una heatmap degli spettatori distinti, la retention lungo la timeline, il tempo di visione unico, il tempo rivisto e i principali cali tra intervalli. I dati sono aggregati, filtrabili per gruppo del corso e protetti da una soglia minima configurabile; non vengono mostrati nominativi. I cluster di reazioni possono essere sovrapposti solo quando rispettano la stessa soglia privacy. Dalla 1.6.1 la loro valutazione è indipendente dalla disponibilità dei segmenti di visione: i cluster conformi restano consultabili in forma aggregata anche quando heatmap e retention sono nascoste.

## Instance analytics (1.6.1)

The teacher report **Analytics** tab derives a distinct-viewer heatmap, timeline retention, unique viewing time, repeated viewing time and the main adjacent decreases from saved segments. Data is aggregate, can be filtered by course group and is protected by a configurable minimum-user threshold; no student names are displayed. Reaction clusters can be overlaid only when they meet the same privacy threshold. Since 1.6.1, reaction privacy is evaluated independently from viewing-segment availability, so compliant aggregate clusters remain available when heatmap and retention are hidden.

## Correzioni 1.6.2 / Fixes 1.6.2

La release corregge il collegamento delle callback di stato delle note, usa la modalità gruppi effettiva dell’attività per gli Analytics e aggiunge un riepilogo privacy-safe delle reazioni anche quando non esiste un cluster temporale visibile.

This release fixes note status callback wiring, uses the activity effective group mode for Analytics, and adds a privacy-safe reaction summary even when no time cluster is visible.


## Instance analytics and personal notes (1.6.3)

La release consente di salvare note personali anche con il player in pausa, mantiene la validazione sul timestamp già visualizzato, usa lo stato aggregato come fallback degli Analytics quando i segmenti grezzi sono incompleti e rimuove il collegamento al report docente dalla pagina studente.

This release allows personal notes to be saved while the player is paused, keeps watched-timestamp validation, uses aggregate state as an Analytics fallback when raw segments are incomplete, and removes the teacher-report link from the student page.


## Student reactions, CSV notes and Analytics duration (1.6.4)

La cronologia delle reazioni personali viene mostrata direttamente allo studente quando le reazioni sono abilitate, senza dipendere dal report studente. Nell'esportazione CSV personalizzata le note sono selezionate per impostazione predefinita. Gli Analytics recuperano la durata anche dagli stati utente e dai segmenti esistenti, senza modificare i flussi dei player o il contratto di tracciamento.

The student's personal reaction history is displayed whenever reactions are enabled, independently of the student-report toggle. Personal notes are selected by default in custom CSV exports. Analytics recover duration from user states and existing segments without changing player flows or the tracking contract.


## Note saving compatibility fix (1.6.5)

La release corregge il tipo di parametro Moodle usato dal Web Service delle note personali: `PARAM_RAW_TRIMMED` sostituisce il nome inesistente `PARAM_RAW_TRIM`. Lo stesso refuso viene corretto nel campo nascosto dei preset reazioni. La modifica evita l'errore PHP lato server che veniva mostrato allo studente come messaggio generico di mancato salvataggio.

This release fixes the Moodle parameter type used by the personal-note Web Service: `PARAM_RAW_TRIMMED` replaces the non-existent `PARAM_RAW_TRIM` name. The same typo is corrected in the hidden reaction-preset field. This prevents the server-side PHP error previously shown to students as a generic note-save failure.

## Analytics dello stesso video tra corsi (1.6.7)

Nella scheda **Analytics** il docente può attivare **Includi i dati dello stesso video negli altri miei corsi**. Il filtro aggrega temporaneamente le attività Videotrack che usano lo stesso video tecnico e per le quali il docente possiede `mod/videotrack:viewreport`. YouTube e Vimeo sono riconosciuti tramite ID del provider; i file caricati tramite content hash Moodle, quindi il solo nome del file non è sufficiente. I nomi delle attività possono essere diversi. Lo stesso utente Moodle viene contato una sola volta tra corsi e istanze. La soglia privacy viene ricalcolata sull’insieme aggregato.

Il filtro dei gruppi del corso corrente non è disponibile nella vista tra corsi, perché gruppi appartenenti a corsi diversi non sono confrontabili. Ogni attività inclusa applica autonomamente la modalità gruppi effettiva, la capability `moodle/site:accessallgroups` e i gruppi consentiti al docente. Le reazioni con la stessa chiave salvata vengono aggregate; configurazioni di reazione differenti restano separate. La finestra temporale dei cluster resta quella configurata nell’attività da cui si apre il report. L’opzione non viene salvata nell’attività e non modifica tracking, completion o dati degli studenti.

## Same-video analytics across courses (1.6.7)

In the **Analytics** tab, teachers can enable **Include data for the same video from my other courses**. The temporary filter aggregates Videotrack activities that use the same technical video and for which the teacher has `mod/videotrack:viewreport`. YouTube and Vimeo are identified by provider ID; uploaded files by Moodle content hash, so a matching filename alone is not sufficient. Activity names may differ. The same Moodle user is counted once across courses and instances, and the privacy threshold is recalculated over the combined population.

The current-course group selector is unavailable in cross-course scope because groups from different courses are not comparable. Each included activity independently applies its effective group mode, the `moodle/site:accessallgroups` capability and the teacher’s permitted groups. Reactions with the same saved key are aggregated; different reaction configurations remain separate. The reaction-cluster time window remains the one configured in the activity from which the report is opened. The option is not stored in the activity and does not change tracking, completion or student data.

## Correzione ambito gruppi Analytics (1.6.8)

La release corregge il descrittore del modulo usato per calcolare la modalità gruppi nelle istanze Analytics tra corsi. L'oggetto passato a `groups_get_activity_groupmode()` include ora anche l'identificativo del corso richiesto dal contratto Moodle, evitando l'errore `Undefined property: stdClass::$course` all'apertura del report. La correzione non modifica l'aggregazione cross-course, le autorizzazioni, la privacy o i dati di tracking.

## Analytics group-scope fix (1.6.8)

This release fixes the course-module descriptor used to calculate group mode for cross-course Analytics instances. The object passed to `groups_get_activity_groupmode()` now includes the course identifier required by Moodle's contract, preventing the `Undefined property: stdClass::$course` error when opening the report. The fix does not change cross-course aggregation, permissions, privacy or tracking data.

## Consolidamento dashboard di corso (1.6.9)

La dashboard di corso riusa ora il servizio Analytics per calcolare copertura media e mediana, studenti non completati e calo principale di retention per ogni attivita. Reazioni e note sono conteggiate separatamente. Tutte le metriche aggregate applicano `analyticsminusers`, inclusi i sottogruppi di completamento, reazione e nota. Gli utenti sono classificati tramite capability: vengono inclusi solo gli iscritti attivi che possono visualizzare l attivita e non possiedono `mod/videotrack:viewreport`, evitando che dati di docenti o manager contaminino le statistiche. Ogni attivita applica inoltre i gruppi accessibili al docente e il link al report di dettaglio compare solo quando la capability di modulo e presente.

## Course dashboard consolidation (1.6.9)

The course dashboard now reuses the Analytics service to calculate average and median coverage, non-completing learners and the largest retention decrease for each activity. Reactions and personal notes are counted separately. Every aggregate metric applies `analyticsminusers`, including completion, reaction and note subgroups. Users are classified through capabilities: only active enrolled users who can view the activity and do not hold `mod/videotrack:viewreport` are included, preventing teacher or manager test data from contaminating learner statistics. Each activity also applies the report viewer s permitted groups, and the detailed-report link is rendered only when the module capability is present.

## Dashboard personale docente (1.6.10)

La dashboard personale riunisce, senza dipendere dal nome del ruolo, le sole attività VideoTrack dei corsi in cui l'utente possiede `mod/videotrack:viewcoursereport`. Ogni modulo viene poi verificato con `mod/videotrack:viewreport`. La vista riusa il servizio della dashboard di corso e applica filtri per corso, attività, gruppo accessibile e periodo (7, 30, 90 giorni o tutto il periodo). Le soglie privacy restano attive su ogni attività e sottogruppo.

## Personal teacher dashboard (1.6.10)

The personal dashboard combines only VideoTrack activities from courses where the user holds `mod/videotrack:viewcoursereport`, without relying on role names. Every module is then checked for `mod/videotrack:viewreport`. The view reuses the course-dashboard service and provides filters for course, activity, accessible group and period (7, 30, 90 days or all time). Privacy thresholds remain active for every activity and subgroup.

## Chiarezza ed export degli analytics (1.6.11)

La scheda Analytics presenta ora un unico pannello espandibile che spiega calcolo e soglia privacy, mostra avvisi soltanto quando visualizzazioni o reazioni non possono essere pubblicate e rende il riepilogo delle reazioni come testo compatto. La heatmap include una legenda per intervalli, intensità, valori mascherati e cluster di reazioni. La tabella accessibile equivalente può essere scaricata tramite i dataformat Moodle CSV, Microsoft Excel (`.xlsx`) e OpenDocument (`.ods`); i valori mascherati restano tali anche nei file esportati.

## Analytics clarity and export (1.6.11)

The Analytics tab now provides one expandable explanation of calculations and privacy thresholds, shows warnings only when viewing or reaction data cannot be published, and renders the reaction summary as compact text. The heatmap includes a legend for intervals, intensity, masked values and reaction clusters. Its equivalent accessible table can be downloaded through Moodle CSV, Microsoft Excel (`.xlsx`) and OpenDocument (`.ods`) data formats; masked values remain masked in every export.


## Interactive transcripts and chapters (1.6.12)

Teacher-provided WebVTT transcripts now work with HTML5 uploads, YouTube and Vimeo. Up to ten language files can be uploaded using language-code filenames such as `en.vtt`, `it.vtt` or `pt-BR.vtt`; students can switch language, search cue text and follow the active cue. Chapters use a dedicated WebVTT file where each cue is one chapter. Transcript and chapter navigation obey the same forward and backward seek restrictions as the selected player. Existing upload activities can continue using their subtitle file as a legacy fallback.

## Trascrizioni interattive e capitoli (1.6.12)

Le trascrizioni WebVTT fornite dal docente funzionano ora con file HTML5, YouTube e Vimeo. È possibile caricare fino a dieci lingue usando nomi come `en.vtt`, `it.vtt` o `pt-BR.vtt`; gli studenti possono cambiare lingua, cercare nel testo e seguire il cue attivo. I capitoli usano un file WebVTT dedicato nel quale ogni cue rappresenta un capitolo. La navigazione rispetta le stesse limitazioni di seek avanti e indietro del player. Le attività upload esistenti possono continuare a usare il vecchio file sottotitoli come fallback.


## Searchable transcript clarification (1.6.13)

YouTube and Vimeo captions remain native to their provider players and cannot be imported or searched automatically by VideoTrack. Teachers who want searchable provider captions must upload a WebVTT copy in the dedicated searchable-transcript area. The activity form now explains this separation explicitly, and the student transcript panel identifies the searchable transcript as a teacher-provided resource that may differ from captions shown inside the player. Uploaded-media activities may continue using their subtitle file as the legacy transcript fallback.

## Chiarimento sulla trascrizione ricercabile (1.6.13)

I sottotitoli nativi di YouTube e Vimeo restano nei player dei rispettivi provider e VideoTrack non può importarli o cercarli automaticamente. Per rendere ricercabili quei contenuti, il docente deve caricare una copia WebVTT nell’area dedicata alla trascrizione ricercabile. Il modulo di configurazione ora chiarisce esplicitamente questa separazione e il pannello studente identifica la trascrizione come risorsa fornita dal docente, che può differire dai sottotitoli mostrati nel player. Le attività con media caricati possono continuare a usare il file sottotitoli come fallback storico.

### Personal bookmarks

Activities can optionally let students save private named bookmarks at watched video timestamps. Bookmarks can be reopened, removed and exported by their owner. They are kept separate from notes and reactions and are not displayed in teacher reports.
