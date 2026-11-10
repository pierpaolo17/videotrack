# Review linguistica mirata

Albero corrente: VideoTrack **1.7.242** (`2026111001`). Review registrata 2026-11-10.

## Perimetro e stato

Questa attività indipendente della roadmap prosegue mentre U-020 è rinviato. Confronta 19 chiavi critiche in
EN, IT, DE, ES, FR, HI, PL e PT: 152 valori testuali. Sei chiavi sono corrette in ogni pacchetto, per 48 valori
modificati. Non si dichiara revisionata la semantica delle altre 978 chiavi per pacchetto. Questa review sul
codice non certifica qualità madrelingua e non sostituisce una review linguistica umana. La parità strutturale
di tutte le 997 chiavi/placeholder è un controllo release separato. Nessun esito manuale WCAG è implicito.

## Punto di partenza accettato

- ZIP reale 1.7.241 / `2026110901`: 410 file distribuiti identici a main
  `2f8bd011471f2ca983c235a4ed35b36d2a5c56b4`.
- SHA-256 ZIP: `65b04a926559ba782a5ef88a14671911e9566b0725738e66120cd1f99f1b3ebd`.
- SHA-256 ZIP report server: `3bf6cccfba47ab393de64daa81f3f6c5e7692a52a9678abba77deb124b5aaac2`.
- CI release/main `42644023227` / `42648245448`: sei job superati, con PHPUnit 379 test / 4.076 assertion,
  Behat 50 scenari / 1.074 passi e strict 10/0/0 per job.
- Report server Moodle 5.0/5.3: PHPCS/lint 166 file puliti, Grunt completato con hash AMD identici prima/dopo
  e PHPUnit 379/4076. Entrambi gli strict forniti sono 10/0/0. Resta l’avviso già noto sull’età dei dati
  Browserslist; quel report non esegue Behat server né prove manuali con tecnologie assistive.

## Rilievi confermati dal codice

| Rilievo | Chiavi corrette | Evidenza runtime invariata |
|---|---|---|
| Riprodurre il video non salva la durata autorevole | `report:analytics_noduration` | `report.php` legge la durata salvata; `classes/external/save_segment.php` non usa la durata dello studente come autorità |
| Note personali ed etichette private dei segnalibri hanno visibilità diversa nei report | `studentnotesenabled_help`, `personalstudytoolsintro`, `privacy:metadata:videotrack_reactev:notetext` | `classes/local/report_access.php` e `report.php` consentono note nei report individuali autorizzati; etichette segnalibro escluse dai report docente |
| Chi ha soltanto accesso aggregato all’attività conserva la soglia minima utenti | `setting:analyticsminusers_desc` | `report.php` controlla l’accesso individuale a ogni attività inclusa; le dashboard corso/docente conservano la politica esistente dei valori esatti |
| Una richiesta di salvataggio non equivale a due query database fisse | `setting:heartbeatinterval_desc` | Validazione contesto/sessione/richiesta e persistenza transazionale prevedono letture/scritture condizionali; nessun numero di query viene dichiarato misurato |

I testi sulle note descrivono permessi già esistenti, anche per ruoli delegati; non concedono accesso. L’avviso
durata descrive l’impostazione già controllata dal docente. Mascheratura, permessi export, perimetro studenti/
gruppi, tracciamento, completamento, idempotenza e dati conservano l’implementazione accettata.

## Chiavi revisionate in ogni pacchetto

- `durationseconds_help`
- `report:analytics_noduration`
- `studentnotesenabled_help`
- `personalstudytoolsintro`
- `privacy:metadata:videotrack_reactev:notetext`
- `bookmarks_private_notice`
- `report:csvexport_bookmarks_help`
- `setting:analyticsminusers_desc`
- `setting:retentionperioddays_desc`
- `setting:heartbeatinterval_desc`
- `setting:randompauseminseconds_help`
- `setting:randompausemaxseconds_help`
- `setting:randompauseminseconds_desc`
- `setting:randompausemaxseconds_desc`
- `integrity:randompauseinvalid`
- `integrity:randompauseorder`
- `acknowledgement:privacyhint`
- `acknowledgement:timing_help`
- `completionreactionrules_desc`

## Controlli candidata e lavoro residuo

La candidata contiene 412 file distribuiti e 31 documenti per lingua. Righe inventario non documentale (345),
file PHP (166), callable PHP/AMD (1101/654), corpi dei test/feature e schema 7 tabelle/151 campi sono invariati.
XMLDB resta `2026110601`; gli artefatti ER aggiornano soltanto l’etichetta versione dell’albero corrente.
Gli otto pacchetti mantengono 997 chiavi nell’ordine canonico e gli stessi contratti placeholder. I gate
esistenti per igiene release, lint, lingue, PHPUnit, browser e strict devono essere eseguiti sulla 1.7.242.
379 test / 50 scenari / 1074 passi sono conteggi attesi, non nuovi risultati di esecuzione della candidata.

Proseguire la review semantica oltre queste 19 chiavi e acquisire review linguistica umana dove necessario.
U-017/U-007 restano chiusi. U-020 è aperto/rinviato con tutte le osservazioni M01–M08 NON ESEGUITE; conservare
il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020) prima del gate finale integrato
di produzione. Valutazione App e implementazione provider autenticati restano attività distinte della roadmap.
