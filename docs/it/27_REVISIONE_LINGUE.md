# Review linguistica verificata sul codice

Albero corrente: VideoTrack **1.7.245** (`2026111004`). Review registrata 2026-11-10.

## Perimetro corrente e limiti

La review cumulativa copre **232 chiavi univoche per pacchetto / 1856 valori distinti** in DE, EN, ES, FR,
HI, IT, PL e PT. Le altre **765 chiavi per pacchetto restano fuori dalla review semantica dichiarata**.
Questa review assistita sul codice e sui testi non certifica qualità madrelingua. Le 997 chiavi/placeholder
restano allineati strutturalmente; la parità non dimostra correttezza semantica o accessibilità.

U-017/U-007 restano chiusi. U-020 è aperto/rinviato su richiesta del maintainer; tutte le osservazioni manuali
restano NON ESEGUITE. Conservare il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
per il gate finale produzione. Nessun esito browser, screen reader o provider pubblico è dedotto da questa review.

## Punto di partenza accettato

- ZIP reale 1.7.244 / `2026111003`: 412 file distribuiti identici a main
  `b6963a5beda4871f4239482a0eb0919466c7fcd4`.
- SHA-256 ZIP: `6590b0bc76c447f950b00c0847673af0f2610fde73c171750ebe48f2baa41dae`.
- SHA-256 ZIP report server: `103f35fa8efb386e5a5faa4329abb797c62915c2e1e4ba5e026d7308364a4ba1`.
- CI candidata/main `42810143144` / `42814365796`: sei job superano PHPUnit 379/4076, Behat 50/1074 e strict
  10/0/0. I server Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e PHPUnit 379/4076; entrambi gli strict forniti
  sono 10/0/0. Hash AMD prima/dopo Grunt identici; resta il noto avviso sull’età dei dati Browserslist.
  Quel report server non esegue Behat o prove manuali con tecnologie assistive.

## Manifesto completo delle chiavi revisionate cumulative

I tre gruppi per prefisso sono completi nei pacchetti correnti; seguono le chiavi esatte aggiuntive.

| Prefisso chiave | Chiavi revisionate per pacchetto |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| Chiavi esatte aggiuntive elencate sotto | 34 |
| Totale chiavi univoche | 232 |

- `acknowledgement:enabled_desc`
- `acknowledgement:pendingvideoend`
- `acknowledgement:privacyhint`
- `acknowledgement:text_help`
- `acknowledgement:timing_help`
- `acknowledgement:videoendrequired`
- `bookmarks_private_notice`
- `durationseconds_help`
- `error:bookmarksratelimit`
- `error:learnertrackingstaff`
- `error:notesratelimit`
- `error:playbackpositionnotwatched`
- `error:playbackrequired`
- `error:reactionratelimit`
- `forum:composerprivacy`
- `forum:enable_help`
- `forum:privacyinfo`
- `forum:studenthelp`
- `integrity:analytics_recording_disabled_controls`
- `integrity:enable_help`
- `integrity:methodology`
- `integrity:pauseonfocusloss_desc`
- `integrity:preventpip_desc`
- `integrity:randompauseautomatic`
- `integrity:randompauseinvalid`
- `integrity:randompauseorder`
- `integrity:randompauses_desc`
- `integrity:reportintro`
- `integrity:settingsintro`
- `integrity:studentnotice`
- `personalstudytoolsintro`
- `report:analytics_noduration`
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Tranche corrente e rilievi sul codice

La tranche copre tutte le 110 chiavi `setting:` e ricontrolla otto descrizioni privacy: **118 chiavi / 944 valori**.
Conserva le evidenze accettate di 12 impostazioni già revisionate dove il contenuto è invariato; 98 impostazioni
sono esaminate per la prima volta in ogni lingua. Il cumulativo deduplica le tranche precedenti. I rilievi storici
restano nel changelog principale, evitando di ripetere cronache obsolete delle candidate.

Sono corrette quattro descrizioni di policy in tutte le otto lingue:

- `setting:default_completionpercent_desc`
- `setting:focuslossgraceseconds_desc`
- `setting:focuslosspolicy_desc`
- `setting:heading_integrity_desc`

- La percentuale predefinita è una condizione VideoTrack AND/OR. Zero disabilita solo quella condizione;
  `tracker::completion_satisfied()` conserva la regola composita accettata.
- `core/player/focus_guard.js` abilita separatamente registrazione e pausa. Il blur rigido dipende dal controllo
  focus dell’attività; tolleranza/policy del sito non abilitano da sole registrazione o controlli nell’attività.
- `random_pause_policy::activity_bounds()` deriva valori automatici dalla durata salvata positiva, applica
  gli override del docente e usa i limiti del sito solo come fallback in assenza di una durata positiva.

Ci sono 32 correzioni dei testi di policy, 59 correzioni ortografiche in DE/ES/FR/IT/PL/PT per privacy e report
note, e sette traduzioni hindi degli avvisi di conservazione: **98 valori modificati su 21 chiavi esistenti**.
I testi latini recuperano i segni diacritici; i testi hindi conservano il significato accettato della retention,
compresi 0/senza scadenza, esempio 730 giorni e cancellazione Privacy API. Non vengono introdotti obblighi privacy,
nuove raccolte dati o policy di conservazione. Numeri/placeholder restano invariati in queste correzioni non di policy.

| Pacchetto | Valori modificati |
|---|---:|
| DE | 13 |
| EN | 4 |
| ES | 13 |
| FR | 15 |
| HI | 11 |
| IT | 14 |
| PL | 14 |
| PT | 14 |

## Chiavi esatte modificate nella candidata

Questo è l’insieme unione; ogni pacchetto modifica soltanto le chiavi richieste dai suoi rilievi effettivi.

- `privacy:metadata:common:cmid`
- `privacy:metadata:common:courseid`
- `privacy:metadata:common:videoid`
- `privacy:metadata:common:videotrackid`
- `privacy:metadata:external:cookies`
- `privacy:metadata:external:ipaddress`
- `privacy:metadata:external:useragent`
- `privacy:metadata:videotrack_reactev:reactionid`
- `setting:default_completionpercent_desc`
- `setting:focuslossgraceseconds_desc`
- `setting:focuslosspolicy_desc`
- `setting:heading_integrity_desc`
- `setting:reportnotespagesize`
- `setting:reportnotespagesize_desc`
- `setting:retentionperioddays_desc`
- `setting:retentionprivacynotice`
- `setting:retentionunlimitedconfirm`
- `setting:retentionunlimitedconfirm_desc`
- `setting:retentionunlimitedconfirm_required`
- `setting:retentionunlimitedwarning_desc`
- `setting:retentionunlimitedwarning_title`

## Gate candidata e lavoro residuo

La 1.7.245 contiene 412 file distribuiti / 31 documenti per lingua, con 166 file PHP, 1101 callable PHP/654 AMD
e 345 righe inventario non documentale. Schema invariato: XMLDB `2026110601`, 7 tabelle / 151 campi / 22 indici /
22 foreign key; ER cambia solo le etichette versione. Logica runtime, permessi, servizi, dati, test, fixture,
AMD e regole CI sono invariati.

I controlli locali di contratto/delta/link e l’applicazione/inversione esatta della patch sono separati dalle
esecuzioni native. La candidata deve ancora superare sei job CI e poi i gate ZIP reale/server/strict dopo merge.
Restano attesi 379 test PHPUnit e 50 scenari Behat / 1074 passi; non sono nuovi risultati di esecuzione candidata.
Proseguire la review semantica residua; gate finale produzione, U-020 manuale e successive fasi App/provider
autenticati non vengono chiusi da questa review linguistica.
