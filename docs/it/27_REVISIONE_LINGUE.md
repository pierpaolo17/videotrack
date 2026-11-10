# Review linguistica mirata

Albero corrente: VideoTrack **1.7.244** (`2026111003`). Review registrata 2026-11-10.

Perimetro cumulativo corrente: **134 chiavi univoche per pacchetto / 1072 valori distinti**. L’ultima
tranche copre tutte le 88 chiavi privacy/completamento e ne corregge cinque per pacchetto. Le altre 863
chiavi restano fuori dalla review dichiarata. U-020 resta aperto/rinviato; qualità madrelingua non certificata.

## Perimetro prima tranche (1.7.242 accettata)

Questa attività indipendente della roadmap prosegue mentre U-020 è rinviato. La prima tranche ha confrontato 19 chiavi critiche in
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
esistenti per igiene release, lint, lingue, PHPUnit, browser e strict devono essere eseguiti sulla 1.7.244.
379 test / 50 scenari / 1074 passi sono conteggi attesi, non nuovi risultati di esecuzione della candidata.

Proseguire la review semantica oltre le 134 chiavi univoche cumulative e acquisire review linguistica umana dove necessario.
U-017/U-007 restano chiusi. U-020 è aperto/rinviato con tutte le osservazioni M01–M08 NON ESEGUITE; conservare
il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020) prima del gate finale integrato
di produzione. Valutazione App e implementazione provider autenticati restano attività distinte della roadmap.

## Seconda tranche: policy e help nella 1.7.243

La baseline reale 1.7.242 è accettata: 412 file coincidono con main
`b6d1afef4af7a1ea7835968586a9f6f7e329bf93`. SHA-256 ZIP:
`e61dfd7547a53306303da9ec91c8cb31b245de3a94fe3238a8f9c301839c2501`.
SHA-256 ZIP report server: `ca74efd7b900bdcbd357fec0f719a98e0bfbc941cf547f72b4b2ca9d4fa311f3`.
Le CI candidata/main `42777321912` / `42784475404` superano sei job con PHPUnit 379/4076,
Behat 50/1074 e strict 10/0/0. I report dei due server superano PHPCS/lint 166, Grunt e PHPUnit 379/4076;
entrambi gli strict forniti sono 10/0/0. Quel report non esegue Behat server né accessibilità manuale.

Questa tranche rivede 31 chiavi negli otto pacchetti (248 valori), riprendendo due chiavi sulla presa visione.
Il perimetro cumulativo è di 48 chiavi univoche per pacchetto (384 valori distinti); le altre 949 chiavi non
sono dichiarate revisionate semanticamente. Sono corrette quattro chiavi per pacchetto (32 valori).
La struttura conserva parità chiavi/placeholder, 412 file, 31 documenti per lingua, runtime, DB, test, AMD e CI.
Non si deducono qualità madrelingua né osservazioni U-020 completate.

| Chiave corretta | Comportamento già presente descritto dal testo |
|---|---|
| `integrity:enable_help` | La registrazione conserva solo campi limitati; la mascheratura dei report segue capability/perimetro, non una promessa incondizionata di soglia privacy |
| `integrity:pauseonfocusloss_desc` | `core/player/focus_guard.js` registra il blur solo se è abilitata la registrazione; la pausa dipende anche dalla politica focus rigida |
| `setting:strictsessionvalidation_desc` | Tracker controlla prima l’evidenza nella stessa sessione; la modalità stretta interrompe il fallback; gli storici segnalibri/Forum restano separati |
| `setting:validationfallbackdays_desc` | I chiamanti note/segnalibri/Forum passano un’età; il chiamante reazioni no. Lo storico può usare la data del segmento o dello stato aggregato conservati; i dati eliminati non tornano |

Vedere il [perimetro delle policy](13_SECURITY_TRUST_BOUNDARIES.md#impostazioni-dellevidenza-vista-e-perimetro-effettivo).
Nessuna modifica runtime uniforma queste policy esistenti diverse. Un eventuale cambiamento funzionale
richiederebbe progettazione e test dedicati. I gate nativi CI/server/strict della 1.7.243 sono accettati; quelli della candidata 1.7.244 restano pendenti;
379 test PHPUnit, 50 scenari Behat / 1074 passi sono conteggi sorgente attesi, non nuovi esiti di esecuzione.
U-020 resta aperto/rinviato su richiesta del maintainer prima del gate finale di produzione.

### Chiavi revisionate nella seconda tranche

- `integrity:enable_help`
- `integrity:pauseonfocusloss_desc`
- `integrity:methodology`
- `integrity:reportintro`
- `integrity:settingsintro`
- `integrity:studentnotice`
- `integrity:analytics_recording_disabled_controls`
- `integrity:randompauseautomatic`
- `integrity:randompauses_desc`
- `integrity:preventpip_desc`
- `setting:strictsessionvalidation`
- `setting:strictsessionvalidation_desc`
- `setting:validationfallbackdays`
- `setting:validationfallbackdays_desc`
- `setting:validationfallbackdays_privacywarning`
- `forum:enable_help`
- `forum:studenthelp`
- `forum:composerprivacy`
- `forum:privacyinfo`
- `error:learnertrackingstaff`
- `error:playbackpositionnotwatched`
- `error:playbackrequired`
- `error:notesratelimit`
- `error:reactionratelimit`
- `error:bookmarksratelimit`
- `acknowledgement:enabled_desc`
- `acknowledgement:text_help`
- `acknowledgement:videoendrequired`
- `acknowledgement:pendingvideoend`
- `acknowledgement:timing_help`
- `acknowledgement:privacyhint`

## Terza tranche: privacy e completamento nella 1.7.244

La 1.7.243 reale accettata contiene 412 file identici a main
`942bf5965d7209673dfa8da2772928179c952052`. SHA-256 ZIP:
`b42316ad2be7bdef46b9c39ed88c431bd6329426add8ee47a96660107c1e94d3`.
SHA-256 ZIP report server: `d89f8965ad236358e9fea2cc3f78105a82e78dfc903ac8a46a190ee253e6916f`.
CI candidata/main `42792298746` / `42795878952`: sei job superano PHPUnit 379/4076, Behat 50/1074 e strict
10/0/0. I server Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e PHPUnit 379/4076; entrambi gli strict
forniti sono 10/0/0. Quel report non esegue Behat server né prove manuali con tecnologie assistive.

La tranche rivede tutte le chiavi `privacy:` e `completion`: 88 per pacchetto / 704 valori. Due erano già
state viste: il perimetro cumulativo diventa 134 chiavi univoche per pacchetto / 1072 valori distinti.
Cinque chiavi per pacchetto sono corrette (40 valori). Le altre 863 chiavi per pacchetto non sono dichiarate
revisionate; non si certifica qualità madrelingua.

| Chiavi corrette | Implementazione già presente descritta dal testo |
|---|---|
| `completionacknowledgement_desc`, `completionlogic` | `tracker::completion_satisfied()` include la presa visione nella regola AND/OR configurata. `mod_form.php` usa lo stesso selettore per percentuale, reazioni e presa visione |
| `privacy:metadata:videotrack_seg:wallclockstart`, `privacy:metadata:videotrack_seg:wallclockend` | `save_segment` limita tempi browser diagnostici; `tracker::begin_playback()` crea record playstart con tempo server. Il credito autorevole usa un registro del tempo server separato |
| `privacy:metadata:videotrack_state:iscompleted` | Tracker memorizza l’esito della regola custom. Moodle riceve un’unica regola composita e può imporre ulteriori requisiti nativi |

Comportamento, valori salvati, completamento custom/nativo, gradebook, permessi, payload export, retention e
cancellazione sono invariati. La candidata conserva 412 file / 31 documenti per lingua, 997 chiavi per
pacchetto, 166 file PHP, 1101 callable PHP/654 AMD e XMLDB 7 tabelle / 151 campi / 22 indici / 22 foreign key.
Corpi dei test/feature e regole CI sono invariati. I gate nativi candidata restano da eseguire; 379 test PHPUnit
e 50 scenari Behat / 1074 passi sono conteggi attesi, non nuovi esiti di esecuzione.
U-020 resta aperto/rinviato prima dell’accettazione finale per la produzione.

### Chiavi revisionate nella terza tranche

- `completionacknowledgement`
- `completionacknowledgement_desc`
- `completiondetail:acknowledgement`
- `completiondetail:allreactiontypes`
- `completiondetail:logicand`
- `completiondetail:logicor`
- `completiondetail:minreactions`
- `completiondetail:percent`
- `completiondetail:requiredreactions`
- `completiondetail:videotrackconditions`
- `completionlogic`
- `completionpercent`
- `completionreactionrules`
- `completionreactionrules_desc`
- `privacy:bookmarksactivechunk`
- `privacy:bookmarksdeletedchunk`
- `privacy:integritychunk`
- `privacy:intervals_none`
- `privacy:intervals_unavailable`
- `privacy:metadata:common:cmid`
- `privacy:metadata:common:courseid`
- `privacy:metadata:common:timecreated`
- `privacy:metadata:common:timemodified`
- `privacy:metadata:common:videoid`
- `privacy:metadata:common:videotrackid`
- `privacy:metadata:external:cookies`
- `privacy:metadata:external:ipaddress`
- `privacy:metadata:external:useragent`
- `privacy:metadata:videotrack_acknowledge`
- `privacy:metadata:videotrack_acknowledge:instanceversion`
- `privacy:metadata:videotrack_acknowledge:statementhash`
- `privacy:metadata:videotrack_acknowledge:timeconfirmed`
- `privacy:metadata:videotrack_acknowledge:userid`
- `privacy:metadata:videotrack_acknowledge:viewedpercent`
- `privacy:metadata:videotrack_acknowledge:viewedseconds`
- `privacy:metadata:videotrack_integrity`
- `privacy:metadata:videotrack_integrity:eventtype`
- `privacy:metadata:videotrack_integrity:sessionid`
- `privacy:metadata:videotrack_integrity:userid`
- `privacy:metadata:videotrack_integrity:videotime`
- `privacy:metadata:videotrack_reactev`
- `privacy:metadata:videotrack_reactev:isdeleted`
- `privacy:metadata:videotrack_reactev:notetext`
- `privacy:metadata:videotrack_reactev:notetype`
- `privacy:metadata:videotrack_reactev:playbackrate`
- `privacy:metadata:videotrack_reactev:reactiondesc`
- `privacy:metadata:videotrack_reactev:reactionid`
- `privacy:metadata:videotrack_reactev:reactionkey`
- `privacy:metadata:videotrack_reactev:reactionlabel`
- `privacy:metadata:videotrack_reactev:sessionid`
- `privacy:metadata:videotrack_reactev:userid`
- `privacy:metadata:videotrack_reactev:videotime`
- `privacy:metadata:videotrack_seg`
- `privacy:metadata:videotrack_seg:endreason`
- `privacy:metadata:videotrack_seg:playbackrate`
- `privacy:metadata:videotrack_seg:requestid`
- `privacy:metadata:videotrack_seg:servervalidated`
- `privacy:metadata:videotrack_seg:sessionid`
- `privacy:metadata:videotrack_seg:userid`
- `privacy:metadata:videotrack_seg:videotimeend`
- `privacy:metadata:videotrack_seg:videotimestart`
- `privacy:metadata:videotrack_seg:wallclockend`
- `privacy:metadata:videotrack_seg:wallclockstart`
- `privacy:metadata:videotrack_state`
- `privacy:metadata:videotrack_state:completionpercent`
- `privacy:metadata:videotrack_state:durationseconds`
- `privacy:metadata:videotrack_state:intervaljson`
- `privacy:metadata:videotrack_state:iscompleted`
- `privacy:metadata:videotrack_state:lastposition`
- `privacy:metadata:videotrack_state:serverbudgetseconds`
- `privacy:metadata:videotrack_state:servercreditedseconds`
- `privacy:metadata:videotrack_state:serverlastactivity`
- `privacy:metadata:videotrack_state:serverplaybacksessionid`
- `privacy:metadata:videotrack_state:uniquecoveredseconds`
- `privacy:metadata:videotrack_state:userid`
- `privacy:metadata:vimeo`
- `privacy:metadata:vimeo:url`
- `privacy:metadata:vimeo:videoid`
- `privacy:metadata:youtube`
- `privacy:metadata:youtube:url`
- `privacy:metadata:youtube:videoid`
- `privacy:notesactivechunk`
- `privacy:notesdeletedchunk`
- `privacy:reactionsactivechunk`
- `privacy:reactionsdeletedchunk`
- `privacy:segmentschunk`
- `privacy:state`
- `privacy:videoid_export_note`
