# VideoTrack database and Entity–Relationship reference

- **Current tree / albero corrente:** VideoTrack 1.7.159 / 2026092603
- **XMLDB source / sorgente XMLDB:** `db/install.xml` (`2026082901`)
- **Supported Moodle / Moodle supportato:** 5.0–5.3

Visual artefacts / artefatti visuali: [Mermaid source](VIDEOTRACK_DB_ER_SCHEMA.mmd) ·
[accessible SVG](VIDEOTRACK_DB_ER_SCHEMA.svg)

Full field dictionaries, indexes, settings, capabilities, services and file areas are maintained in
[`en/05_VARIABLE_INVENTORY.md`](en/05_VARIABLE_INVENTORY.md) and
[`it/05_VARIABLE_INVENTORY.md`](it/05_VARIABLE_INVENTORY.md). They are not duplicated here.

I dizionari completi di campi, indici, impostazioni, capability, servizi e file area sono mantenuti in
[`en/05_VARIABLE_INVENTORY.md`](en/05_VARIABLE_INVENTORY.md) e
[`it/05_VARIABLE_INVENTORY.md`](it/05_VARIABLE_INVENTORY.md), senza duplicarli in questo documento.

## Relationship legend / Legenda delle relazioni

`db/install.xml` declares 22 stable `TYPE="foreign"` XMLDB keys. Moodle uses these declarations as relationship
metadata and generates an exact non-unique backing index for every key; Moodle's DDL layer does not create physical
database foreign-key constraints or cascades. Data lifecycle integrity therefore continues to depend on the plugin's
transactional deletion, reset, Privacy API and backup/restore paths, while `cli/validate.php` detects orphans.

`db/install.xml` dichiara 22 chiavi XMLDB stabili `TYPE="foreign"`. Moodle usa queste dichiarazioni come metadata di
relazione e genera per ciascuna un indice non univoco esatto; il livello DDL Moodle non crea vincoli fisici di
foreign key né cascade nel database. L'integrità del ciclo di vita continua quindi a dipendere da cancellazione
transazionale, reset, Privacy API e backup/restore del plugin, mentre `cli/validate.php` rileva gli orfani.

`REF(C)` identifies a conditional application reference. `videotrack.linkedforumid = 0` disables Forum integration;
`videotrack_reactev.reactionid = 0` identifies a note or bookmark. Declaring either as a normal XMLDB foreign key
would misclassify valid rows as orphans, so the validator checks only their positive/reference-bearing states.

`REF(C)` identifica un riferimento applicativo condizionale. `videotrack.linkedforumid = 0` disabilita l'integrazione
Forum; `videotrack_reactev.reactionid = 0` identifica una nota o un bookmark. Dichiararli come normali foreign key
XMLDB classificherebbe come orfani record validi: il validatore controlla quindi solo gli stati con riferimento.

## Plugin tables / Tabelle del plugin

| Table | Responsibility / Responsabilità | Key cardinality / Cardinalità chiave |
|---|---|---|
| `videotrack` | Activity configuration / configurazione attività | One row per Moodle activity instance. |
| `videotrack_seg` | Playback requests and validated segments / richieste e segmenti validati | Many rows per activity/user; request id unique in that scope. |
| `videotrack_state` | Derived watched state / stato visto derivato | At most one row per activity/user. |
| `videotrack_integrity` | Optional bounded diagnostics / diagnostica opzionale limitata | Many events per activity/user. |
| `videotrack_react` | Configured reaction definitions / definizioni reazioni | Many definitions per activity. |
| `videotrack_reactev` | Reactions, notes and bookmarks / reazioni, note e bookmark | Many personal events per activity/user. |
| `videotrack_acknowledge` | Versioned acknowledgements / prese visione versionate | Unique per activity/user/statement hash. |

## Moodle core integrations / Integrazioni Moodle core

| VideoTrack reference | Core target | Nature / Natura |
|---|---|---|
| `videotrack.course`, `*.courseid` | `course.id` | Stable XMLDB foreign keys / foreign key XMLDB stabili. |
| `course_modules.instance` | `videotrack.id` | Standard activity-module link when `modules.name = videotrack`. |
| `*.cmid` | `course_modules.id` | Stable XMLDB foreign keys plus semantic context check / foreign key stabili e controllo semantico del contesto. |
| `*.userid` | `user.id` | Stable XMLDB foreign keys; users are soft-deleted by Moodle / foreign key stabili; Moodle elimina gli utenti logicamente. |
| `videotrack.linkedforumid` | `forum.id` | `REF(C)`: optional same-course relation; `0` means disabled. |
| `files.contextid` + file area/itemid | `context` and plugin records | Moodle File API integration. |
| `grade_items.iteminstance` | `videotrack.id` | Gradebook link with `itemmodule = videotrack`. |
| `course_modules_completion` | course module + user | Core completion state managed through Completion API. |
| `videotrack_reactev.reactionid` | `videotrack_react.id` | `REF(C)`: `0` is valid for notes/bookmarks. |

## Ownership and cleanup / Proprietà e pulizia

Activity deletion and course reset delete plugin-owned child rows through Moodle DML/API paths. Privacy deletion
uses user/context scope; scheduled retention removes expired granular data and rebuilds derived state. Backup and
restore remap user, Forum and reaction identifiers. These application guarantees reduce orphan risk but do not
replace validation. XMLDB keys document and index stable relations; the CLI validator also checks orphans,
course/course-module consistency and conditional references without modifying data.

Cancellazione attività e reset eliminano i record figli del plugin tramite DML/API Moodle. La Privacy API opera
per utente/contesto; la retention elimina dati granulari scaduti e ricostruisce lo stato derivato. Backup e restore
rimappano utenti, Forum e reazioni. Le chiavi XMLDB documentano e indicizzano le relazioni stabili; il validatore
CLI verifica inoltre orfani, coerenza corso/course module e riferimenti condizionali senza modificare dati.
