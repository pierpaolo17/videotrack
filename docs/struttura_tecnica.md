# mod_videotrack — Guida alla struttura del codice

**Versione**: 1.0.40 (build 2026060900)
**Prerequisito di lettura**: conoscenza base di Moodle (plugin system, `$DB`, `$USER`, `cm_info`) e PHP/JavaScript.

---

## 1. Mappa della directory radice

```
videotrack/
├── version.php            # Metadati del plugin (versione, compatibilità)
├── lib.php                # Funzioni API obbligatorie Moodle
├── locallib.php           # Funzioni helper non-API
├── mod_form.php           # Form di creazione/modifica istanza
├── view.php               # Pagina vista dallo studente
├── report.php             # Report docente per singola attività
├── reports_course.php     # Report aggregato a livello corso
├── settings.php           # Impostazioni amministratore
├── presets.php            # CRUD preset reazioni
├── index.php              # Lista attività nel corso (standard Moodle)
├── styles.css             # Stili CSS del modulo
├── environment.xml        # Requisiti ambiente: GD PHP extension (optional)
│
├── amd/                   # Moduli JavaScript AMD (RequireJS/Moodle AMD)
│   ├── src/               # Sorgenti leggibili da sviluppatori
│   │   ├── player.js          # Player YouTube IFrame API (~937 righe)
│   │   ├── vimeo_player.js    # Player Vimeo SDK (~915 righe)
│   │   ├── html5_player.js    # Player HTML5 nativo (~1327 righe)
│   │   ├── presets.js         # UI gestione preset (~80 righe)
│   │   └── report.js          # Modulo AMD report: conferma reset studente
│   └── build/             # File .min.js compatti generati dai sorgenti AMD
│
├── backup/moodle2/        # Backup e ripristino (API Moodle 2)
│   ├── backup_videotrack_activity_task.class.php
│   ├── backup_videotrack_stepslib.php
│   ├── restore_videotrack_activity_task.class.php
│   └── restore_videotrack_stepslib.php
│
├── classes/               # Classi PHP con autoloading PSR-4
│   ├── completion/
│   │   └── custom_completion.php   # Regole di completamento personalizzate
│   ├── event/
│   │   ├── activity_completed.php
│   │   ├── course_module_viewed.php
│   │   ├── reaction_deleted.php
│   │   ├── reaction_saved.php
│   │   └── segment_saved.php
│   ├── external/
│   │   ├── save_segment.php    # Web service: salva segmento di visione
│   │   ├── save_reaction.php   # Web service: salva una reazione
│   │   ├── save_note.php       # Web service: salva una nota personale
│   │   └── delete_reaction.php # Web service: elimina reazione/nota
│   ├── local/
│   │   ├── tracker.php         # Core logic: calcolo progresso e completamento
│   │   └── privacy_manager.php # Retention GDPR e anonimizzazione
│   ├── privacy/
│   │   └── provider.php        # Privacy API Moodle
│   └── task/
│       └── cleanup_task.php    # Scheduled task retention/anonimizzazione
│
├── db/
│   ├── install.xml    # Schema DB (tabelle, campi e indici)
│   ├── upgrade.php    # Script di migrazione tra versioni
│   ├── access.php     # Definizione capabilities
│   ├── services.php   # Dichiarazione web services
│   ├── tasks.php      # Registrazione scheduled task
│   └── mobile.php     # Dichiarazione supporto app mobile
│
└── lang/
    ├── en/videotrack.php   # Stringhe in inglese
    ├── it/videotrack.php   # Italiano
    ├── de/videotrack.php   # Tedesco
    ├── es/videotrack.php   # Spagnolo
    ├── fr/videotrack.php   # Francese
    ├── pt/videotrack.php   # Portoghese
    ├── hi/videotrack.php   # Hindi
    └── pl/videotrack.php   # Polacco
```

---

## 2. Schema del database

Il modulo usa 5 tabelle, tutte con prefisso `{videotrack_}`.

### 2.1 `{videotrack}` — Istanza attività

Un record per ogni istanza dell'attività creata dal docente.

| Campo | Tipo | Descrizione |
|---|---|---|
| `id` | INT | PK autoincrement |
| `course` | INT | FK → `{course}.id` |
| `name` | VARCHAR(255) | Titolo dell'attività |
| `intro`, `introformat` | TEXT, INT | Descrizione (editor Moodle) |
| `youtubeurl` | TEXT | URL YouTube originale |
| `videoid` | VARCHAR(32) | ID estratto dall'URL (es. `dQw4w9WgXcQ`) |
| `videosource` | VARCHAR(20) | `youtube` \| `vimeo` \| `upload` |
| `videourl` | TEXT | URL Vimeo o riferimento file caricato |
| `durationseconds` | DECIMAL(10,3) | Durata video in secondi. Non viene aggiornata dalle chiamate AJAX dello studente; deve provenire da configurazione/metadata lato server o da processi attendibili. |
| `playbackspeeds` | VARCHAR(100) | Velocità consentite, es. `"0.75,1,1.25,1.5"` |
| `autoplay` | TINYINT | 0/1 |
| `loop` | TINYINT | 0/1 |
| `startmuted` | TINYINT | 0/1 |
| `allowdownload` | TINYINT | Solo upload: mostra bottone download |
| `html5controls` | VARCHAR(255) | Controlli HTML5 visibili, es. `"play,progress,mute"` |
| `playerwidth` | INT | Larghezza max in px (0 = usa default sito) |
| `rewindstep` | INT | Secondi per click ⏪ (0 = usa default sito) |
| `fastforwardstep` | INT | Secondi per click ⏩ (0 = usa default sito) |
| `showcontrols` | TINYINT | Mostra barra controlli |
| `disablekeyboard` | TINYINT | Disabilita shortcut tastiera |
| `showfullscreen` | TINYINT | Mostra bottone fullscreen |
| `allowseekforward` | TINYINT | Lo studente può saltare avanti |
| `allowseekbackward` | TINYINT | Lo studente può tornare indietro |
| `allowplaybackratechange` | TINYINT | Lo studente può cambiare velocità |
| `resumeplayback` | TINYINT | Resume automatico abilitato |
| `maxplaybackrate` | INT | Limite velocità in centesimi (0=nessuno, 150=1.5×) |
| `captions` | TINYINT | Sottotitoli abilitati |
| `captionslang` | VARCHAR(10) | Codice lingua sottotitoli (es. `it`) |
| `showtranscript` | TINYINT | Mostra transcript VTT interattivo |
| `showchapters` | TINYINT | Mostra barra capitoli VTT |
| `studentnotesenabled` | TINYINT | Note personali studente abilitate |
| `reactionsenabled` | TINYINT | Reazioni abilitate |
| `reactionsrequired` | TINYINT | Reazioni necessarie per completamento |
| `minreactions` | INT | Numero minimo di reazioni richieste |
| `requireallreactiontypes` | TINYINT | Tutte le reazioni devono essere usate |
| `completionlogic` | VARCHAR(10) | `and` \| `or` |
| `completionpercent` | INT | Percentuale minima per completamento |
| `grade` | INT | 0=no voto, >0=max punti, <0=id scala negativo |
| `gradepass` | DECIMAL(10,5) | Voto minimo di sufficienza |
| `showgradeto` | TINYINT | Mostra voto allo studente |
| `showstudentreport` | TINYINT | Mostra tabella reazioni allo studente |
| `showreactionnotice` | TINYINT | Mostra avviso reazioni |
| `reactionnotice` | TEXT | Testo avviso (HTML) |
| `clusterwindow` | INT | Finestra clustering heatmap in secondi |

### 2.2 `{videotrack_seg}` — Segmenti grezzi

Un record per ogni frammento di video guardato continuamente da uno studente.

| Campo | Tipo | Descrizione |
|---|---|---|
| `videotrackid` | INT | FK → `{videotrack}.id` |
| `courseid`, `cmid` | INT | Per query efficienti |
| `userid` | INT | FK → `{user}.id` |
| `videoid` | VARCHAR(32) | ID video al momento della visione |
| `sessionid` | VARCHAR(64) | UUID generato lato client per la sessione browser |
| `videotimestart` | DECIMAL(10,3) | Inizio segmento in secondi |
| `videotimeend` | DECIMAL(10,3) | Fine segmento in secondi |
| `wallclockstart` | INT | Timestamp UNIX inizio reale |
| `wallclockend` | INT | Timestamp UNIX fine reale |
| `playbackrate` | DECIMAL(5,3) | Velocità di riproduzione (es. `1.500`) |
| `endreason` | VARCHAR(32) | Allowlist server-side: `pause`, `seek`, `ended`, `heartbeat`, `beforeunload`, `pagehide`, `tab`; valori non ammessi diventano `unknown` |
| `timecreated` | INT | Timestamp UNIX creazione record |

### 2.3 `{videotrack_state}` — Stato aggregato per studente

Un solo record per coppia (istanza, studente). Viene aggiornato ad ogni `save_segment` con i valori ricalcolati.

| Campo | Tipo | Descrizione |
|---|---|---|
| `videotrackid` | INT | FK → `{videotrack}.id` |
| `userid` | INT | FK → `{user}.id` |
| `lastposition` | DECIMAL(10,3) | Ultimo punto raggiunto in secondi (per resume) |
| `durationseconds` | DECIMAL(10,3) | Durata video nota al momento dell'ultimo aggiornamento |
| `uniquecoveredseconds` | DECIMAL(10,3) | Secondi unici guardati (dopo merge intervalli) |
| `completionpercent` | DECIMAL(5,2) | `uniquecoveredseconds / durationseconds × 100` |
| `intervaljson` | LONGTEXT | JSON con tutti gli intervalli guardati: `[[0.5,30.2],[45.1,60.0]]` |
| `iscompleted` | TINYINT | 1 se tutte le regole di completamento sono soddisfatte |
| `timecreated`, `timemodified` | INT | Timestamp UNIX |

### 2.4 `{videotrack_react}` — Definizioni reazioni

Un record per ogni tipo di reazione definito dal docente.

| Campo | Tipo | Descrizione |
|---|---|---|
| `videotrackid` | INT | FK → `{videotrack}.id` |
| `reactionkey` | VARCHAR(100) | Slug unico generato dall'etichetta + sortorder |
| `label` | VARCHAR(255) | Testo bottone |
| `description` | TEXT | Tooltip/descrizione |
| `icontype` | VARCHAR(20) | `emoji` \| `fa` \| `file` |
| `iconvalue` | VARCHAR(255) | Emoji, classe FA, o vuoto se file |
| `requiredforcompletion` | TINYINT | Questa reazione è richiesta per completare |
| `sortorder` | INT | Ordine di visualizzazione |
| `isdeleted` | TINYINT | Soft-delete: 1 = rimossa, 0 = attiva |

L'icona file viene salvata nella filearea `reactionicon` con `itemid = {videotrack_react}.id`.

### 2.5 `{videotrack_reactev}` — Eventi reazione e note

Un record per ogni click su un bottone reazione o per ogni nota salvata.

| Campo | Tipo | Descrizione |
|---|---|---|
| `videotrackid`, `courseid`, `cmid` | INT | Riferimenti per query |
| `userid` | INT | Studente |
| `videoid` | VARCHAR(32) | ID video al momento dell'evento |
| `sessionid` | VARCHAR(64) | UUID sessione browser |
| `reactionid` | INT | FK → `{videotrack_react}.id` (0 per le note) |
| `reactionkey`, `reactionlabel`, `reactiondesc` | VARCHAR/TEXT | Snapshot dati reazione al momento del click |
| `notetext` | TEXT | Testo della nota (solo se `notetype='note'`) |
| `notetype` | VARCHAR(20) | Vuoto per reazioni standard, `'note'` per note personali |
| `videotime` | DECIMAL(10,3) | Timestamp nel video al momento dell'evento |
| `playbackrate` | DECIMAL(5,3) | Velocità di riproduzione al momento dell'evento |
| `isdeleted` | TINYINT | Soft delete (0/1) |
| `timecreated`, `timemodified` | INT | Timestamp UNIX |

---

## 3. File PHP principali

### 3.1 `version.php`

```php
$plugin->component = 'mod_videotrack';
$plugin->version   = 2026060900;
$plugin->requires  = 2025041400; // Moodle 5.0.
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '1.0.40';
```

È il file letto da Moodle per decidere se mostrare l'upgrade dialog. `version` è un intero in formato `YYYYMMDDnn`. `requires` è la build minima di Moodle supportata.

---

### 3.2 `lib.php` — API obbligatorie Moodle

Questo file contiene tutte le funzioni che Moodle chiama direttamente per gestire il ciclo di vita del plugin. Ogni funzione inizia con `videotrack_`.

**Utility DB:**
- `videotrack_whitelist_record($data)` — restituisce un oggetto con soli i campi della tabella `{videotrack}`, interrogando `$DB->get_columns()` con cache statica per request. Evita che campi extra del form (es. `videofile`, `posterimage`, `reactionlabel_*`) vengano passati a `insert/update_record`, che causerebbe eccezioni DB su colonne inesistenti.

**Lifecycle istanza:**
- `videotrack_add_instance($data)` — creazione istanza; normalizza i campi, inserisce il record, salva i file (video, poster, reazioni), aggiorna il gradebook.
- `videotrack_update_instance($data)` — modifica istanza; stessa logica.
- `videotrack_delete_instance($id)` — eliminazione; cancella tutti i record DB e i file nelle filearea.

**Normalizzazione campi (chiamate da add/update):**
- `videotrack_process_video_fields($data)` — estrae `videoid` dall'URL, imposta `videosource`/`videourl`.
- `videotrack_process_playbackspeeds_field($data)` — aggrega i checkbox `playbackspeed_N` in una stringa CSV.
- `videotrack_process_player_behavior_fields($data)` — normalizza i booleani `autoplay`, `loop`, ecc.
- `videotrack_process_html5controls_field($data)` — aggrega i checkbox `html5ctrl_*` in una stringa CSV.
- `videotrack_process_captions_fields($data)` — salva il file VTT nella filearea `subtitles`.
- `videotrack_process_grade_fields($data)` — normalizza `grade` e `gradepass`.

**Accesso ai file:**
- `videotrack_get_upload_url($instanceid, $cmid)` — URL del file video caricato, o `null`.
- `videotrack_get_vtt_url($cmid)` — URL del file VTT, o `null`.
- `videotrack_get_poster_url($cmid)` — URL dell'immagine poster, o `null`.
- `videotrack_save_uploaded_video($instanceid, $data)` — salva il draft filepicker `videofile` nella filearea.
- `videotrack_save_poster_image($instanceid, $data)` — salva il draft filepicker `posterimage`. Ha un guard su `draftitemid <= 0` per non cancellare il poster esistente quando il docente non tocca il campo.

**Configurazione player:**
- `videotrack_get_html5controls($videotrack)` — restituisce array dei controlli HTML5 attivi.
- `videotrack_get_player_width($videotrack)` — larghezza effettiva (istanza → sito → 960px).
- `videotrack_get_rewind_step($videotrack)` — secondi rewind effettivi (istanza → sito → 10).
- `videotrack_get_fastforward_step($videotrack)` — secondi fastforward effettivi.

**Gradebook:**
- `videotrack_grade_item_update($videotrack, $grades)` — crea/aggiorna voce nel gradebook.
- `videotrack_set_user_grade($videotrack, $userid, $gradevalue)` — assegna voto a un utente.
- `videotrack_get_user_grade($videotrack, $userid)` — legge voto corrente.
- `videotrack_grade_item_delete($videotrack)` — rimuove la voce dal gradebook.

**Report studente nel profilo:**
- `videotrack_user_outline($course, $user, $mod, $videotrack)` — riassunto in una riga (pagina profilo).
- `videotrack_user_complete($course, $user, $mod, $videotrack)` — tabella dettagliata.

**Navigazione:**
- `videotrack_extend_settings_navigation()` — aggiunge link "Report" al menu dell'attività.
- `videotrack_extend_navigation_course()` — aggiunge link ai "Report del corso".

**Completamento:**
- `videotrack_get_completion_active_rule_descriptions($cm)` — testi descrittivi delle regole per la scheda completamento.
- `videotrack_update_completion_for_user($videotrack, $cm, $userid)` — ricalcola e aggiorna lo stato Moodle.
- `videotrack_recalculate_all_states($videotrackid, $cm)` — ricalcola tutti gli studenti e aggiorna la `completion_info` Moodle.

**Reset e pulizia:**
- `videotrack_reset_course_userdata($data)` — resetta i dati utente quando si resetta un corso.
- `videotrack_reset_course_form_definition($mform)` — aggiunge checkbox al form di reset corso.
- `videotrack_delete_user_progress($videotrack, $userid)` — cancella state + segmenti di un utente.

**File serving:**
- `videotrack_pluginfile($course, $cm, $context, $filearea, $args, ...)` — serve i file delle filearea `reactionicon`, `videocontent`, `subtitles`, `posterimage`. Verifica capability `mod/videotrack:view` prima di servire.

**Utility:**
- `videotrack_resize_reaction_icon($context, $reactionid, $fs)` — ridimensiona l'icona reazione a 64×64px con crop centrato, usando la libreria GD. Usa `try/catch` invece di `@imagecreatefromstring` per compatibilità PHP 8+. Se GD non è disponibile, emette un messaggio `debugging()` con `DEBUG_NORMAL` (visibile agli admin con debug attivo) e lascia il file invariato. Vedere anche `environment.xml` e `settings.php` per gli avvisi all'amministratore.
- `videotrack_save_reaction_definitions($videotrackid, $data)` — sincronizza le reazioni (insert/update/delete) confrontando i dati del form con quelli esistenti nel DB.

---

### 3.3 `locallib.php` — Helper non-API

Funzioni usate internamente dal plugin ma non richieste dall'API Moodle:

- `videotrack_extract_videoid($url)` — estrae l'ID YouTube da qualsiasi formato URL (regex su youtu.be, youtube.com/watch, youtube.com/embed, ecc.)
- `videotrack_extract_vimeo_id($url)` — estrae l'ID Vimeo.
- `videotrack_get_playback_speeds($videotrack)` — restituisce array di float con le velocità consentite (istanza → sito).
- `videotrack_get_site_playback_speeds()` — velocità configurate a livello sito.
- `videotrack_get_reactions($videotrackid, $includedeleted = false)` — array di oggetti `{videotrack_react}` ordinati per `sortorder`. Per default filtra `isdeleted = 0` (solo reazioni attive). Passare `true` come secondo parametro solo per operazioni di backup/restore che devono includere le reazioni soft-deleted referenziate da eventi storici. Usa cache statica per request — chiavi separate per `active` e `all`.
- `videotrack_get_all_presets_for_js()` — array di preset serializzati per il form JS.
- `videotrack_render_reaction_icon($reaction, $context, $withLabel)` — HTML dell'icona reazione (emoji/FA/img da filearea).
- `videotrack_format_seconds($seconds)` — converte `63.5` in `"01:03"` o `"1:03:00"`.
- `videotrack_build_required_reaction_notice($videotrack, $reactions)` — costruisce il testo dell'avviso reazioni se non configurato manualmente.
- `videotrack_get_module_context_from_data($data, $instanceid)` — risolve il contesto modulo da `$data->coursemodule` o `$data->cmid` (fallback su `get_coursemodule_from_instance`).

---

### 3.4 `mod_form.php` — Form di creazione/modifica

Classe `mod_videotrack_mod_form extends moodleform_mod`.

Il form è organizzato in sezioni (header Moodle):

1. **Nome e descrizione** (`name`, `intro`)
2. **Sorgente video** — selector `videosource` + campi condizionali (`youtubeurl`, `vimeourl`, `videofile`, `posterimage`)
3. **Impostazioni player** — controlli, seek, velocità, resume (bloccabili con capability)
4. **Comportamento aggiuntivo** — autoplay, loop, mute, larghezza, skip buttons
5. **Sottotitoli** — `captions`, `captionslang`, `vttfile`, `showtranscript`, `showchapters`
6. **Controlli HTML5** (solo source=upload) — checkbox per ogni controllo disponibile
7. **Velocità di riproduzione** — checkbox per ogni velocità abilitata dal sito
8. **Reazioni** — enabled, required, minreactions, allreactiontypes, logic, lista reazioni con editor inline
9. **Note studente** — toggle con default da settings
10. **Poster** — filepicker
11. **Completamento** (sezione standard Moodle + custom)
12. **Valutazione** (sezione standard Moodle)

`definition_after_data()` abilita il repeat element per le reazioni (permette di aggiungere/rimuovere righe dinamicamente).

`data_preprocessing(&$defaultvalues)` prepara i draft area per:
- File video caricato (`videofile` → filearea `videocontent`)
- File VTT (`vttfile` → filearea `subtitles`)
- Poster (`posterimage` → filearea `posterimage`)
- Reazioni esistenti con icone file
- Editor del testo avviso reazioni

`validation($data, $files)` verifica che l'URL YouTube/Vimeo sia valido chiamando le funzioni `extract_*` di `locallib.php`.

---

### 3.5 `view.php` — Vista studente

Flusso di esecuzione:

1. Risolve il CM (da `id` o `n`), chiama `require_login()`, `require_capability()`
2. Chiama `videotrack_view()` per registrare l'evento `course_module_viewed` e il completamento da visita
3. Carica `$reactions`, `$state` e solo le prime reazioni utente mostrate nella pagina; lo storico completo rimane nel report docente/studente dedicato
4. Calcola `$playerconfig` — un grande array associativo con tutti i parametri per il modulo AMD:
   - Configurazione player (videoid, controls, seek, speed...)
   - URL dei file (vtturl, posterurl, videourl, beaconurl)
   - Stringhe localizzate per il JS (removelabel, dismisslabel, rewindlabel...)
   - Stato corrente (intervaljson, durationseconds, resumeposition)
5. Carica il modulo AMD corretto con `$PAGE->requires->js_call_amd()`
6. Renderizza HTML: player wrap, barra canvas, sidebar (transcript, note, progresso, reazioni, tabella eventi)

**Variabili chiave:**

- `$playerconfig` — passato come primo argomento a `init()` del modulo AMD; è la fonte di ogni impostazione lato JS
- `$safeintervals` — JSON validato degli intervalli guardati (fallback `[]` se corrotto nel DB)
- `$reactionmap_view` — array keyed `[reaction_id => reaction_obj]` per lookup O(1) nel foreach degli eventi

---

### 3.6 `report.php` — Report docente

Report con due modalità (`$mode`):

**`student`**: tabella per studente con stato, voto, azioni. Export CSV degli stati.

**`cumulative`**: tabella clustered delle reazioni per finestra temporale (`$window` secondi). Heatmap SVG accessibile. Export CSV degli eventi tramite POST + sesskey.

La funzione closure `$clusterize($events, $window, $aggregation)` aggrega gli eventi in cluster: due eventi dello stesso tipo entro `$window` secondi vengono fusi in un cluster con conteggio, timestamp medio, studenti distinti.

Azioni pre-header (redirect dopo sesskey):
- `savegrade` — assegna voto al singolo studente
- `recalculate` — ricalcola stati di tutti gli studenti
- `resetstudent` — azzera segmenti, stato, reazioni e voto di un singolo studente

Sezione note: query separata su `{videotrack_reactev}` con `notetype='note'`, raggruppata per utente con rowspan HTML per accessibilità. Export CSV note separato.

---

### 3.7 `settings.php` — Impostazioni amministratore

Usa l'API `admin_settingpage` di Moodle. Definisce tutti i default configurabili dall'amministratore (heartbeat, velocità, controlli, ecc.) e il sistema di preset di reazioni.

**Avviso GD**: All'inizio della pagina impostazioni, se `imagecreatefromstring()` non esiste (GD non installata), viene mostrato un `admin_setting_heading` con `class="alert alert-warning"` che spiega il problema e suggerisce la soluzione (`php-gd`). L'avviso scompare automaticamente se GD viene installata senza toccare il codice.

---

### 3.8 `presets.php` — Gestione preset reazioni

Pagina CRUD per i preset. Un preset è un insieme di reazioni predefinite che il docente può applicare con un click nel form. I preset vengono salvati nel DB come `{config_plugins}` di Moodle (con `get_config`/`set_config`).

---

### 3.9 `environment.xml` — Requisiti di ambiente

File XML nella root del plugin, letto da Moodle durante installazione/upgrade. Appare in **Amministrazione del sito → Server → Ambiente**.

Dichiara la libreria GD come dipendenza **opzionale** (`level="optional"`): produce un avviso giallo ma non blocca l'installazione. I messaggi `<MESSAGE lang="en">` e `<MESSAGE lang="it">` spiegano il problema e la soluzione all'amministratore. Supplementare all'avviso in `settings.php`.

---

## 4. Classi PHP (`classes/`)

### 4.1 `classes/local/tracker.php`

La classe statica più importante del plugin. Contiene tutta la logica di calcolo del progresso.

**Metodi:**

`simplify_intervals(array $intervals, int $target): array`
Riduce gli intervalli al numero target **senza mai inglobare gap non visti**. Ordina per lunghezza decrescente, mantiene i `$target` più lunghi, ri-ordina per posizione temporale. Non inventa copertura: la perdita di precisione (frammenti brevi scartati) è accettabile e semanticamente corretta.

`normalise_interval(float $start, float $end, float $duration): ?array`
Valida e normalizza un segmento `[start, end]`. Clamp a `[0, duration]`. Restituisce `null` se `end <= start` (segmento vuoto).

`merge_intervals(array $intervals): array`
Ordina e fonde gli intervalli sovrapposti o adiacenti. Es.: `[[0,30],[20,50]]` → `[[0,50]]`. Algoritmo classico O(n log n).

`cap_intervals(array $intervals): array`
Se ci sono più di `MAX_INTERVALS` (500) intervalli, chiama `simplify_intervals` per ridurli unendo le coppie con gap minore.

`simplify_intervals(array $intervals, int $target): array`
Riduce gli intervalli al numero target unendo iterativamente la coppia con gap minore. O(n×k) con k = numero fusioni necessarie. Sostituisce il precedente approccio con SplMinHeap che aveva bug.

`covered_seconds(array $intervals): float`
Somma la lunghezza di tutti gli intervalli → secondi unici guardati.

`has_recent_playback(int $videotrackid, int $userid, string $sessionid, float $videotime, int $recentseconds = 20, float $timetolerance = 8.0): bool`
Verifica server-side che esista un segmento recente nella `{videotrack_seg}` per la stessa sessione browser, vicino al `$videotime` (con tolleranza `±timetolerance` secondi), creato negli ultimi `$recentseconds` secondi. Usata da `save_reaction` e `save_note` per impedire che richieste AJAX dirette (senza video in riproduzione) possano creare reazioni/note a timestamp arbitrari. Nota implementativa: usa placeholder named distinti per ogni valore anche quando i valori coincidono (`vt`, `vt2`, `tol1`, `tol2`) per compatibilità con adodb che non ammette lo stesso placeholder due volte.

`reaction_counts(int $videotrackid, int $userid): array`
Due query SQL separate: la prima calcola `COUNT(*)` e `COUNT(DISTINCT reactionid)`; la seconda recupera gli ID distinti con `SELECT DISTINCT reactionid`. Questo evita `GROUP_CONCAT`, che su MySQL può essere troncato silenziosamente, ed esclude note personali e record soft-deleted. Restituisce:
```php
['eventcount' => N, 'uniquecount' => M, 'uniqueids' => [id1, id2, ...]]
```
`eventcount` = totale click reazioni; `uniquecount` = tipi distinti; `uniqueids` = lista ID reazioni usate.

`completion_satisfied($videotrack, $state, $reactionsummary, $requiredreactionids): bool`
Valuta tutte le regole di completamento (percentuale, minreazioni, reazioni specifiche, tutti i tipi) e le combina con logica AND o OR.

`update_state($videotrack, $cm, $userid, $interval, $lastposition, ?$segment = null, ?&$segmentid = null): stdClass`
Il metodo centrale. Viene chiamato da `save_segment.php` ad ogni segmento salvato.
Accetta opzionalmente `$segment` (il record grezzo da inserire) e `&$segmentid` (passato per reference):
l'insert avviene **dentro** la transazione delegata per garantire atomicità totale — nessun segmento orfano nel caso di rollback.
1. Apre una transazione Moodle (`start_delegated_transaction`)
2. Legge o crea il record `{videotrack_state}` per questo utente
3. Aggiunge il nuovo intervallo, fonde, cappa
4. Ricalcola `uniquecoveredseconds` e `completionpercent`
5. Chiama `reaction_counts` e `completion_satisfied`
6. Aggiorna `lastposition` (solo se > 2s)
7. Salva il record con `update_record` o `insert_record`
8. Fa commit della transazione
9. Se il completamento è passato da 0 a 1, triggera l'evento `activity_completed`
10. Restituisce `$state` aggiornato

`refresh_completion($videotrack, $cm, $userid): stdClass`
Versione leggera: ricalcola solo `iscompleted` senza ricalcolare gli intervalli. Usata quando cambiano le reazioni (non il progresso video).

---

### 4.2 `classes/external/save_segment.php`

Web service chiamato dal JS ad ogni fine segmento.

**Flusso:**
1. `validate_parameters` → `require_login` → `validate_context` → `require_capability`
2. Ignora `durationseconds` fornita dal client per normalizzazione e completion. La durata lato client può servire solo alla UI; il server usa esclusivamente una durata già presente in configurazione/metadati attendibili.
3. `tracker::normalise_interval` — se restituisce `null`, risponde con `accepted=false`
4. Clampa i wallclock al `now + 5s` (tolleranza clock skew)
5. Validazione anti-cheat: usa tempi server-side, ultimo segmento/sessione recente e tolleranze di heartbeat; i wallclock client non determinano da soli la validità
6. Inserisce il record in `{videotrack_seg}`
7. Chiama `tracker::update_state` (transazione atomica)
8. Triggera evento `segment_saved`
9. Aggiorna `completion_info` Moodle
10. Restituisce stato aggiornato: `uniquecoveredseconds`, `completionpercent`, `iscompleted`, `intervaljson`; `durationseconds` non viene aggiornato da AJAX studente

---

### 4.3 `classes/external/save_reaction.php`

Web service per il click su un bottone reazione.

**Flusso:**
1. Auth standard
2. Throttle: `count_records_select` per reazioni identiche negli ultimi 3 secondi. Se > 0, restituisce lo stato corrente senza salvare.
3. Inserisce record in `{videotrack_reactev}`
4. Triggera evento `reaction_saved`
5. Verifica `tracker::has_recent_playback()` — lancia `moodle_exception('error:playbackrequired')` se nessun segmento recente nella sessione
6. `tracker::reaction_counts()` — una sola chiamata prima di `refresh_completion` per evitare la query tripla
7. `tracker::refresh_completion` — aggiorna `iscompleted`
8. Aggiorna `completion_info` Moodle
9. Restituisce `reactioneventid`, `uniquereactions`, `iscompleted`

---

### 4.4 `classes/external/save_note.php`

Web service per il salvataggio di una nota personale.

Analogo a `save_reaction`, ma:
- Verifica `tracker::has_recent_playback()` — impedisce note a timestamp arbitrari senza video in play
- Verifica che `studentnotesenabled == 1` sull'istanza
- Clamp di `videotime` a `[0, durationseconds]`
- Salva con `notetype='note'` e `reactionid=0`
- Non chiama `refresh_completion` (le note non contribuiscono al completamento)

---

### 4.5 `classes/external/delete_reaction.php`

Web service per l'eliminazione di una reazione o nota.

- Verifica che il record appartenga all'utente corrente (IDOR protection)
- Soft delete: imposta `isdeleted=1`
- Se `notetype != 'note'`: ricalcola completamento e `completion_info`
- Se `notetype == 'note'`: aggiorna solo lo stato existente senza ricalcolo (le note non impattano il completamento)

---

### 4.6 `classes/completion/custom_completion.php`

Classe `custom_completion extends core_completion\activity_custom_completion`.

Implementa le tre regole personalizzate dichiarate in `get_defined_custom_rules()`:
- `completionpercent` — delegata a `{videotrack_state}.completionpercent`
- `minreactions` — delegata a `tracker::reaction_counts().uniquecount`
- `allreactiontypes` — intersezione tra reazioni definite e reazioni usate

`get_custom_rule_descriptions()` restituisce le stringhe localizzate mostrate nella scheda completamento.

---

### 4.7 `classes/privacy/provider.php`

Implementa:
- `get_metadata()` — documenta tutte le tabelle e i campi che contengono dati personali.
- `get_contexts_for_userid()` — trova tutti i contesti modulo dove l'utente ha dati.
- `get_users_in_context()` — lista solo utenti reali (`userid > 0`) con dati in un contesto; i record anonimizzati non vengono esposti come utenti Moodle.
- `export_user_data()` — esporta segmenti, stato, reazioni e note in formato leggibile; note e reazioni sono esportate separatamente; le icone di reazione (`reactionicon`) sono esportate come file, escluse quelle con `reactionid=0` (note).
- `delete_data_for_all_users_in_context()` — anonimizza i dati personali nel contesto preservando aggregati anonimi/pseudonimi.
- `delete_data_for_user()` e `delete_data_for_users()` — anonimizzano i dati dell'utente richiesto invece di cancellarli fisicamente.

La logica condivisa è in `classes/local/privacy_manager.php`: il salt locale è creato con lock Moodle, l'identificativo anonimo è negativo, salted e scoped per attività, e la retention automatica usa batch per evitare scansioni troppo pesanti.

---

### 4.8 `classes/local/privacy_manager.php`

Contiene gli helper privacy usati dal provider e dal task schedulato:
- `retention_period_seconds()` — converte l'impostazione amministrativa `retentionperioddays`; `0` significa conservazione illimitata.
- `anonymous_userid()` — genera un identificativo negativo, salted e scoped per attività per conservare statistiche aggregate senza riferimento all'utente reale.
- `anonymise_user_in_context()` — anonimizza segmenti, stato, note e reazioni per un utente in un'attività.
- `anonymise_all_users_in_context()` — anonimizza tutti gli utenti reali del contesto in batch sicuri.
- `anonymise_expired_records()` — applica la retention automatica fino al limite batch per esecuzione.

I record anonimizzati vengono preservati nei backup/restore come aggregati anonimi/pseudonimi e non sono rimappati su utenti reali.

### 4.9 `classes/task/cleanup_task.php`

Scheduled task registrato in `db/tasks.php`. Esegue la retention GDPR: se il periodo è `0` scrive un log informativo e non modifica dati; se è positivo anonimizza i dati più vecchi della soglia configurata.

## 5. Moduli JavaScript AMD (`amd/src/`)

Tutti e tre i player sono moduli AMD Moodle (`define(['core/ajax', 'core/log'], function(Ajax, Log) { ... })`). Ricevono `config` come argomento di `init()` e non usano variabili globali.

### 5.1 Struttura comune a tutti i player

**Variabili di stato locali al modulo:**

```javascript
var player = null;    // Istanza del player (YT.Player / Vimeo.Player / HTMLMediaElement)
var config = null;    // Oggetto playerconfig passato da PHP
var HEARTBEAT_INTERVAL = 30; // Sovrascritta da config.heartbeatinterval in init()

var state = {
    sessionid:            null,    // UUID generato in init()
    playing:              false,   // Video in riproduzione?
    segmentstart:         null,    // Inizio segmento corrente (float secondi)
    wallclockstart:       null,    // Timestamp UNIX inizio segmento
    lasttime:             0,       // Ultima posizione nota (per seek detection)
    duration:             0,       // Durata video in secondi
    playbackrate:         1,       // Velocità corrente
    heartbeatid:          null,    // ID del setInterval heartbeat (null = fermo)
    lastHeartbeatWallclock: 0,
    currentReplayEnd:     null,    // Fine del replay corrente (pause automatica)
    seekblocked:          false,   // Flag anti-loop per seek bloccati
    isSeeking:            false,   // True durante l'evento seeking
    isProgrammaticSeek:   false,   // True per seek lanciati dal codice (html5 only)
    _posterRemoved:       false    // Flag per rimuovere il poster solo una volta
};
```

**Funzioni comuni (nomi identici in tutti e tre):**

| Funzione | Descrizione |
|---|---|
| `uuid()` | Genera UUID v4 o fallback con Date+random |
| `ajax(methodname, args)` | Wrapper per `Ajax.call` con catch e log |
| `formatSeconds(s)` | Converte float secondi in `"MM:SS"` o `"H:MM:SS"` |
| `updateProgress(response)` | Aggiorna DOM con nuovi dati progresso (% percento, secondi, reazioni, barra canvas) |
| `updateIntervalBar(json, duration)` | Disegna il canvas verde degli intervalli |
| `setReactionButtons(playing)` | Abilita/disabilita bottoni reazione via `aria-disabled`; emette `CustomEvent('videotrack:playstate')` |
| `saveSegment(start, end, reason)` | Chiama il web service `mod_videotrack_save_segment` |
| `installGlobalListeners()` | Registra: `visibilitychange`, `pagehide`, `beforeunload` (sendBeacon) |
| `installReactionHandler()` | Click handler per bottoni reazione e delete |
| `installNoteHandler()` | Bottone salva nota, delete nota, contatore caratteri |
| `installNotesToggle()` | Toggle show/hide pannello note con sessionStorage |
| `installPosterHandler()` | Rimuove overlay poster al primo play |
| `appendReactionRow(eventid, reaction, time)` | Aggiunge riga alla tabella reazioni studente |
| `appendNoteRow(noteid, time, text)` | Aggiunge riga alla lista note |
| `showResumeNotice(seconds)` | Banner "Riprendendo da MM:SS" con auto-dismiss |
| `appendIconSafe(target, html)` | Inserisce HTML icona con whitelist esplicita: copia solo tag `img`/`i`/`span` e attributi `class`/`src`/`alt`/`aria-hidden` — nessun handler o attributo arbitrario può passare |

### 5.2 `player.js` — YouTube IFrame API

Carica l'API YouTube (`youtube.com/iframe_api`) in modo non-bloccante con `window.onYouTubeIframeAPIReady`.

`buildPlayer()` crea `new YT.Player('mod-videotrack-player', { playerVars, events })` con eventi:
- `onReady` — inizializza `state.duration`, gestisce `replaystart` (priorità su resume) e `resumeposition`
- `onStateChange` — `PLAYING` → `startCurrentSegment()`; `PAUSED` → `closeCurrentSegment('pause')`; `ENDED` → `closeCurrentSegment('ended')`
- `onAutoplayBlocked` — mostra avviso visivo
- `onError` — log

`handleSeekByPolling()` — invece degli eventi seek (YouTube non ha `seeked`), il polling avviene ogni 2s durante la riproduzione e confronta `currentTime` con `lasttime`. Se la differenza supera la soglia, è un seek utente.

`buildYouTubeSkipButtons()` — crea i bottoni ⏪/⏩ overlay sopra il player. Usa DOM API (`createTextNode`, `span[aria-hidden]`) invece di `innerHTML`.

`installReactionHandler()` — in player.js è un no-op documentato: le reazioni YouTube sono già gestite da `installGlobalListeners()` via event delegation.

### 5.3 `vimeo_player.js` — Vimeo Player SDK

Carica il Vimeo SDK da `player.vimeo.com/api/player.js` con `crossOrigin='anonymous'`. In caso di errore (`script.onerror`) mostra un avviso all'utente.

`buildPlayer()` crea `new window.Vimeo.Player(container, options)` con `dnt: true` (do-not-track).

Gestione eventi Vimeo:
- `player.on('play', fn)` — chiama `startSegment(t)` dove `t` è da `getCurrentTime().then()`
- `player.on('pause', fn)` — `closeSegment('pause')`
- `player.on('ended', fn)` — `closeSegment('ended')` + `setReactionButtons(false)`
- `player.on('seeked', fn)` — gestione seek con `isProgrammaticSeek` e blocco anti-skip
- `player.on('timeupdate', fn)` — aggiorna `state.lasttime` e gestisce replay stop

`closeSegment()` è **asincrono** in Vimeo (deve chiamare `player.getCurrentTime().then()`). Imposta `state.playing = false` e `state.segmentstart = null` subito all'interno della `.then()` per evitare race condition.

`isProgrammaticSeek` — flag impostato a `true` prima di ogni `player.setCurrentTime()` lanciato dal codice (replay, resume). Viene resettato nella `.then()`. L'handler `seeked` controlla questo flag per ignorare seek programmatici.

### 5.5 `report.js` — Modulo AMD report

Modulo AMD leggero, caricato da `report.php` tramite `$PAGE->requires->js_call_amd()`. Inizializza i form di reset/ricalcolo con conferme basate su `core/notification`, evitando azioni distruttive via GET e JS inline.

### 5.4 `html5_player.js` — Player HTML5 nativo

Il più complesso dei tre (1327 righe) perché gestisce anche transcript VTT, capitoli e controlli personalizzati.

`buildPlayer()`:
1. Check `config.videourl` — se vuoto mostra avviso e return
2. Crea `<video>` o `<audio>` in base all'estensione del file
3. Imposta attributi (`controls`, `muted`, `autoplay`, `loop`, `playsinline`)
4. Aggiunge `<source>` e `<track>` (sottotitoli)
5. Inserisce nell'elemento `#mod-videotrack-player`
6. Se configurato, chiama `buildCustomControls()`
7. Chiama `attachTrackingEvents()`
8. Gestisce `loadedmetadata` per resume e enforce maxplaybackrate

`attachTrackingEvents()` registra tutti gli event listener HTML5:
- `play` — avvia segmento solo se `!state.playing` (evita doppio start dopo replay)
- `pause`, `ended` — chiude segmento
- `seeking` — se `isProgrammaticSeek`, chiude segmento corrente (per salvare progresso); altrimenti applica regole allowseek
- `seeked` — resetta `isSeeking` e `isProgrammaticSeek`; riapre segmento se `state.playing`
- `timeupdate` — aggiorna `lasttime`, gestisce fine replay
- `ratechange` — enforce maxplaybackrate

`buildCustomControls()` — crea una barra controlli custom HTML (play/pause, skip, progresso, velocità, volume, fullscreen) usando DOM API, accessibile con `aria-label` e `role`.

`loadTranscript()` — fetch del VTT, parsing con `parseVTT()`, rendering con `renderTranscript()`, sincronizzazione con `syncTranscript()`.

`parseVTT(text)` — parser WebVTT puro in JavaScript. Supporta timestamp in formato `HH:MM:SS.mmm` e `MM:SS.mmm`. Stripping tag HTML nelle cue. Restituisce array `[{start, end, text}]`.

`buildChaptersBar()` — fetch dello stesso VTT, filtra le cue con testo ≤ 80 caratteri (titoli capitolo), crea la barra con bottoni. Sync capitolo attivo su `timeupdate`.

`isProgrammaticSeek` — flag specifico di html5_player (analogo al Vimeo), necessario perché `seeking` è asincrono rispetto all'assegnazione di `media.currentTime`. Impostato a `true` prima di ogni seek programmatico (replay, capitoli, resume), resettato nell'handler `seeked`.

---

## 6. Web Services (`db/services.php`)

Quattro web service, tutti con `ajax: true` e `loginrequired: true`:

| Nome | Classe | Descrizione |
|---|---|---|
| `mod_videotrack_save_segment` | `save_segment::execute` | Salva segmento di visione |
| `mod_videotrack_save_reaction` | `save_reaction::execute` | Salva click reazione |
| `mod_videotrack_save_note` | `save_note::execute` | Salva nota personale |
| `mod_videotrack_delete_reaction` | `delete_reaction::execute` | Soft delete reazione o nota |

Tutti seguono lo stesso pattern di autenticazione:
```
get_coursemodule_from_id → get_course → get_record(videotrack)
→ require_login → cm_info::create → context_module → validate_context
→ require_capability('mod/videotrack:view')
```

---

## 7. Capabilities (`db/access.php`)

| Capability | Default | Descrizione |
|---|---|---|
| `mod/videotrack:view` | studente | Accede all'attività |
| `mod/videotrack:viewreport` | docente | Vede il report docente |
| `mod/videotrack:viewownreport` | studente | Vede la propria tabella reazioni |
| `mod/videotrack:managereactions` | docente | Modifica/cancella reazioni altrui |
| `mod/videotrack:overrideplayersettings` | manager | Sblocca le impostazioni player |
| `mod/videotrack:overridecompletionsettings` | manager | Sblocca le impostazioni completamento |

---

## 8. Filearea Moodle

| Filearea | itemid | Contenuto |
|---|---|---|
| `intro` | 0 | Descrizione attività (standard Moodle) |
| `videocontent` | 0 | File video/audio caricato |
| `subtitles` | 0 | File VTT (sottotitoli/capitoli) |
| `posterimage` | 0 | Immagine anteprima pre-play |
| `reactionicon` | `{videotrack_react}.id` | Icona immagine della reazione |

Tutte servite da `videotrack_pluginfile()` con verifica capability. `videocontent` ha cache 1 ora, `posterimage` 5 minuti, le altre nessuna cache.

---

## 9. Pattern architetturali

**Flusso dati in tempo reale:**
```
Browser (JS) → AJAX → web service (PHP) → tracker::update_state() → DB
                                         → completion_info::update_state() → Moodle
                                         → response JSON → updateProgress() → DOM
```

**Evento custom `videotrack:playstate`:**
Tutti i componenti JS che devono reagire al cambio stato play/pausa ascoltano questo evento (emesso da `setReactionButtons(playing)`), senza monkey-patching di funzioni esistenti. Usato da:
- `installNoteHandler` → `setNoteButtonState(playing)`
- `installPosterHandler` → rimozione overlay al primo play

**Transazione atomica in tracker:**
`update_state` usa `start_delegated_transaction()`. Se un'eccezione viene lanciata, il rollback è automatico. L'evento `activity_completed` viene triggerato **fuori** dalla transazione per non bloccare il commit.

**Soft delete reazioni:**
Sia i **click evento** (`videotrack_reactev`) che le **definizioni reazione** (`videotrack_react`) non vengono mai cancellati fisicamente: `isdeleted = 1`. Questo preserva la coerenza dei report storici, dell'export GDPR e dei restore da backup: gli eventi continuano a puntare a reactionid validi anche dopo che la reazione viene rimossa dalla configurazione.

**sendBeacon su beforeunload:**
Il payload usa il formato bulk API di Moodle: `JSON.stringify([{methodname, args}])` inviato come `Blob` con `Content-Type: application/json` all'endpoint `/lib/ajax/service.php?sesskey=...`.

---

## 10. Suggerimenti per chi modifica il codice

1. **Aggiungere un campo DB**: modificare `install.xml`, aggiungere il blocco `if ($oldversion < N)` in `upgrade.php`, aggiungere a `backup_videotrack_stepslib.php` (lista campi del `backup_nested_element`), aggiornare `lib.php` se serve normalizzazione.

2. **Aggiungere un web service**: creare la classe in `classes/external/`, aggiungere la dichiarazione in `db/services.php`, eventualmente aggiungere una capability in `db/access.php`.

3. **Aggiungere una stringa**: aggiungerla in tutti i 7 file `lang/*/videotrack.php`. Le stringhe usate nel JS vanno anche aggiunte al `$playerconfig` in `view.php`.

4. **Aggiungere logica di tracciamento**: modificare `tracker::update_state()` e `tracker::completion_satisfied()`. Attenzione alla transazione: tutto dentro `try/catch`.

5. **Aggiungere funzionalità al form**: aggiungere il campo in `mod_form.php`, normalizzarlo in `lib.php` (tipicamente in una funzione `videotrack_process_*`), assicurarsi che `data_preprocessing` lo prepari correttamente per la modifica.

6. **Aggiungere logica JS al report**: creare o estendere `amd/src/report.js` e chiamare il metodo da `report.php` con `$PAGE->requires->js_call_amd('mod_videotrack/report', 'nomeMetodo', [$config])`. Copiare in `amd/build/report.min.js`.

7. **Modificare `whitelist_record`**: se si aggiunge un nuovo campo alla tabella `{videotrack}`, non è necessario aggiornare `whitelist_record` — la funzione interroga dinamicamente `$DB->get_columns()` e include automaticamente il nuovo campo. La cache statica si aggiorna alla prossima request.

8. **Aggiungere un requisito di ambiente** (es. nuova extension PHP): aggiungere un blocco `<PHP_EXTENSION>` in `environment.xml`. Usare `level="optional"` per avvisi non bloccanti, `level="required"` per bloccare l'installazione. Aggiungere le stringhe del messaggio direttamente nell'XML (non in `lang/`), su una sola riga per evitare whitespace extra.


### Storico aggiornamenti: 0.9.9

- Il report cumulativo evita il caricamento completo degli eventi grezzi in memoria: usa conteggi/distinct per i filtri, recordset per CSV e clustering, un algoritmo lineare basato sugli ultimi cluster attivi per reazione e una soglia di sicurezza sui cluster prodotti per evitare crescita non controllata su corsi molto grandi.
- La sezione note studente viene renderizzata tramite recordset, senza raggruppare tutte le note in array PHP.
- La heatmap SVG include una legenda testuale associata alla tabella dati (`aria-describedby`) per migliorare la fruibilità non basata solo sul colore.
- Le capability di override restano deliberate a livello `CONTEXT_COURSE`, perché l'override è una policy di corso e non una singola eccezione di istanza. Il report aggregato `reports_course.php` usa anch'esso il contesto corso; i report della singola attività continuano invece a usare `CONTEXT_MODULE`.

### Aggiornamento v1.0.0

- Il backup delle definizioni reazione include anche `isdeleted`, così le reazioni soft-deleted restano storicamente coerenti durante restore con dati utente.
- Il controllo anti-cheat dei segmenti combina sanity check sul wallclock client con un limite server-side basato sull'ultimo segmento salvato nella stessa sessione.
- Le azioni di grading nel report richiedono esplicitamente POST oltre al sesskey.
- Le regole CSS del pannello trascrizione sono consolidate per evitare override duplicati.

## Aggiornamento v1.0.1

- Export CSV dei report e delle note effettuato via POST per evitare esposizione del sesskey negli URL.
- La validazione dei segmenti non usa più `durationseconds` inviata dal client.
- Il controllo anti-cheat lato server si basa sul tempo server e sull'ultimo segmento salvato nella sessione.
- I bottoni reazione sono collegati al testo informativo tramite `aria-describedby`.
- Il report mostra un avviso quando viene raggiunta la soglia massima di cluster visualizzati.

### Aggiornamento 1.0.2

- L'export GDPR separa stato, segmenti, reazioni attive, reazioni eliminate e note in sezioni/blocchi per ridurre il picco di memoria e rendere più chiaro lo stato dei dati soft-deleted.
- Il report per studente usa recordset per gli stati e include nel filtro anche utenti che hanno solo note personali.
- Il report cumulativo segnala anche nel CSV quando viene raggiunta la soglia massima di cluster.
- La heatmap SVG include pattern e label numeriche sui cluster principali, in modo da non basarsi solo sul colore.
- I bottoni reazione mantengono `aria-disabled` ma annunciano l'hint associato quando vengono attivati fuori dalla riproduzione.


## Aggiornamento 1.0.3

- Le icone renderizzate lato client sono sanificate anche sul valore `src`, accettando solo URL locali/pluginfile.
- La view studente limita le note personali mostrate nella pagina principale e rimanda al report completo.
- L'export privacy separa note attive e note eliminate.
- La heatmap del report include un link di salto alla tabella dati e un riepilogo testuale.
- I savepoint senza modifiche schema sono documentati come passaggi di upgrade intenzionali.

### Aggiornamento 1.0.4

- Le icone associate a reazioni eliminate logicamente non vengono più cancellate dalla file area, così i report storici mantengono la rappresentazione originale quando disponibile.
- La sanificazione client-side delle icone caricate accetta solo URL Moodle `pluginfile.php` o `webservice/pluginfile.php` same-origin.
- L'avviso configurabile sulle reazioni usa opzioni editor esplicite e viene renderizzato con il contesto del modulo.
- Il controllo di playback recente per reazioni/note usa una singola query server-side.
- La sezione note del report docente è paginata.

### Aggiornamento 1.0.5

- Il salvataggio delle icone di reazione preserva il file esistente quando il docente non invia un nuovo draft file nella modifica dell'attività.
- Il salvataggio delle reazioni applica un rate limit globale per sessione, oltre al controllo anti-duplicato per singola reazione.
- Il reset completo dei dati studente dal report docente genera un evento Moodle dedicato per accountability e audit GDPR.
- Il report cumulativo supporta filtri espliciti sul tempo del video (`timefrom`/`timeto`) per restringere dataset molto grandi e recuperare analisi oltre la soglia massima di cluster visualizzati.
- La heatmap è preceduta da un riepilogo testuale dei cluster più rilevanti.
- I savepoint senza modifica schema restano intenzionali e documentati, per mantenere tracciabilità tra versioni rilasciate senza alterare il database.

### Aggiornamento 1.0.7

- I pulsanti reazione dei player HTML5 e Vimeo sono allineati al player YouTube: fuori dalla riproduzione sono realmente disabilitati e rimossi dal tab order, mantenendo `aria-disabled` come stato semantico.
- Il report cumulativo mostra un avviso specifico quando viene raggiunta la soglia cluster senza filtri temporali sul video; anche il CSV mette l'avviso prima dell'intestazione dati.
- L'export privacy usa una stringa localizzata anche per la sezione dello stato di completamento.
- La versione numerica Moodle è stata riallineata alla release pubblica 1.0.7.


### Report cumulativo e limiti dataset

Il report cumulativo usa un limite di sicurezza sul numero di cluster da mantenere in memoria.
Se il limite viene raggiunto senza un filtro temporale sul video, la visualizzazione e l'export cumulativo vengono bloccati in modo esplicito e viene richiesto al docente di restringere il periodo/segmento di analisi.
Questa scelta evita esportazioni parziali interpretate come complete e mantiene prevedibile il consumo di memoria.


## Versione 1.0.10

- Aggiunti indici compositi per validazione playback recente, rate limit reazioni e rate limit note.
- File AMD build riallineati in forma compatta rispetto ai sorgenti.


## Versione 1.0.32

- Documentazione aggiornata alla struttura reale del plugin e alla release corrente.
- Documentata la retention GDPR: `0` indica conservazione illimitata, mentre i valori positivi attivano anonimizzazione automatica dei dati scaduti.
- Documentato il comportamento di backup/restore dei record anonimizzati con `userid` negativo.
- Rafforzata la creazione del salt di anonimizzazione: senza lock Moodle non viene creato un nuovo salt concorrente.
- Completate le stringhe privacy/retention nelle lingue incluse.

### Storico aggiornamenti: 1.0.40

- Ripristinato il pacchetto lingua polacco e riallineate tutte le lingue incluse.
- Aggiornata la documentazione alla build 2026060900.
- Chiarito che la retention automatica opera per coppia utente/attività e che gli identificativi negativi sono pseudonimi tecnici, salted e scoped per attività.
- Ridotto il logging debug dei segmenti sospetti per evitare rumore e dati comportamentali non necessari.
- Migliorata l'usabilità anti-cheat: con la validazione di sessione non stretta, note e reazioni possono essere salvate anche dopo pause o refresh purché il timestamp risulti già guardato.
