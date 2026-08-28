# VideoTrack database and Entity–Relationship reference

- **Current tree / albero corrente:** VideoTrack 1.7.118 / 2026082802
- **XMLDB source / sorgente XMLDB:** `db/install.xml` (`2026082301`)
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

`db/install.xml` currently declares primary keys and indexes, but no `TYPE="foreign"` XMLDB keys. In the diagram,
`FK(L)` therefore means a logical relation verified in current code, queries, Privacy API or backup/restore. It
does not claim that the installed database enforces a physical foreign-key constraint.

`db/install.xml` dichiara attualmente chiavi primarie e indici, ma nessuna chiave XMLDB `TYPE="foreign"`. Nel
diagramma `FK(L)` indica quindi una relazione logica verificata nel codice, nelle query, nella Privacy API o nel
backup/restore. Non attribuisce al database installato un vincolo fisico non dichiarato.

The omission of XMLDB foreign-key metadata is tracked as a schema-quality finding. Correcting it requires a
separate release with orphan-data checks, index comparison, an idempotent upgrade step and fresh-install/upgrade
validation. The documentation/icon release does not change the schema.

L'assenza dei metadata XMLDB delle foreign key è registrata come finding di qualità dello schema. La correzione
richiede una release separata con controllo dati orfani, confronto indici, upgrade idempotente e verifica distinta
di installazione/upgrade. La release documentale/grafica non modifica lo schema.

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
| `videotrack.course`, `*.courseid` | `course.id` | Persisted logical scope / scope logico persistito. |
| `course_modules.instance` | `videotrack.id` | Standard activity-module link when `modules.name = videotrack`. |
| `*.cmid` | `course_modules.id` | Denormalised module-context reference / riferimento denormalizzato. |
| `*.userid` | `user.id` | Personal-data owner / proprietario dei dati personali. |
| `videotrack.linkedforumid` | `forum.id` | Optional same-course relation; `0` means disabled. |
| `files.contextid` + file area/itemid | `context` and plugin records | Moodle File API integration. |
| `grade_items.iteminstance` | `videotrack.id` | Gradebook link with `itemmodule = videotrack`. |
| `course_modules_completion` | course module + user | Core completion state managed through Completion API. |
| `videotrack_reactev.reactionid` | `videotrack_react.id` | Conditional: `0` is valid for notes/bookmarks. |

## Ownership and cleanup / Proprietà e pulizia

Activity deletion and course reset delete plugin-owned child rows through Moodle DML/API paths. Privacy deletion
uses user/context scope; scheduled retention removes expired granular data and rebuilds derived state. Backup and
restore remap user, Forum and reaction identifiers. These application guarantees reduce orphan risk but do not
replace accurate XMLDB metadata or pre-migration data checks.

Cancellazione attività e reset eliminano i record figli del plugin tramite DML/API Moodle. La Privacy API opera
per utente/contesto; la retention elimina dati granulari scaduti e ricostruisce lo stato derivato. Backup e restore
rimappano utenti, Forum e reazioni. Queste garanzie applicative riducono il rischio di orfani, ma non sostituiscono
metadata XMLDB corretti né i controlli dati prima di una migrazione.
