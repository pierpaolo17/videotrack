# Review linguistica basata sul codice

Albero corrente: VideoTrack **1.7.247** (`2026111006`). Review registrata 2026-11-10.

## Perimetro attuale e limiti

La review cumulativa copre **352 chiavi univoche per pacchetto / 2816 valori distinti** in DE, EN, ES, FR,
HI, IT, PL e PT. Le altre **645 chiavi per pacchetto sono fuori dalla review semantica dichiarata**.
Questa verifica assistita di codice e lingue non equivale a un’approvazione madrelingua. Le 997 chiavi e i
placeholder restano allineati; la parità strutturale non prova correttezza semantica o accessibilità.

U-017/U-007 restano chiusi. U-020 è aperto/rinviato su richiesta del manutentore: tutte le otto osservazioni
manuali restano NON ESEGUITE. Conservare il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
per il gate finale produzione. Non si deducono esiti manuali, screen reader o provider dal vivo.

## Punto di partenza accettato

- Versione reale 1.7.246 / `2026111005`: 412 file distribuiti identici a main
  `c5527eb68e1d58b78999dc24c8f99e14b5872eb8`.
- SHA-256 ZIP: `c7c65c1933c370c885f44caf413bee2200c8fd6b1dfa4d6527aa26b8191e83b1`.
- SHA-256 ZIP report server: `804d98775c7096eff316dad5eec8c66dbacc5e89064f69469553a9626b899169`.
- CI candidata/main `42840354028` / `42864476627`: tutti i sei job superano PHPUnit 379/4076, Behat 50/1074
  e strict 10/0/0. Report server Moodle 5.0/5.3: PHPCS/lint 166, Grunt e PHPUnit 379/4076 superati; entrambi
  gli strict forniti sono 10/0/0. Hash AMD prima/dopo Grunt identici. Resta l’avviso noto sull’età dei dati
  Browserslist; i report server non eseguono Behat o controlli assistivi manuali. Le 24 segnalazioni PHPMD già
  revisionate e i totali informativi Psalm 732/760 sono distinti dai gate configurati superati.

## Manifesto completo delle chiavi revisionate

I quattro gruppi per prefisso sono esaustivi nei pacchetti correnti; seguono le restanti chiavi esatte.

| Prefisso chiave | Chiavi revisionate per pacchetto |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| `report:analytics_` | 121 |
| Chiavi esatte aggiuntive elencate sotto | 33 |
| Totale chiavi univoche | 352 |

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
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Tranche completa della famiglia Analytics

La tranche copre tutte le **121 chiavi `report:analytics_` / 968 valori**. Esamina 99 nuove chiavi / 792 valori
e riusa evidenze accettate invariate per 22 chiavi: `report:analytics_noduration` e le 21 chiavi Analytics delle
reazioni revisionate nella 1.7.246. Il cumulativo elimina le sovrapposizioni delle tranche precedenti. La famiglia
di chiavi Analytics è ora interamente coperta; le altre famiglie report, comprese etichette dashboard corso/
docente, sono fuori da questa tranche salvo inclusione esplicita nel manifesto cumulativo. Le definizioni senza
riferimento diretto di rendering sono conservate: la review non implica nuovi elementi UI o visualizzazione
corrente. I rilievi precedenti restano nel changelog principale.

Sono inclusi snapshot/medie presa visione, identità video e gruppi tra corsi, privacy dei segnalibri, intervalli
ed export, legende heatmap/retention, oscuramento, fallback dello stato conservato ed etichette dei riepiloghi.
Riferimenti: `report.php`, `analytics_scope`/`analytics_scope_matcher`, `acknowledgement_analytics`,
`analytics_timeline`, `analytics`, `report_support`, `analytics_table_export` e i servizi di presentazione,
riepilogo e timeline dei report in `classes/local`.

## Rilievi dal codice e correzioni

- `report.php` costruisce Analytics da segmenti grezzi e stato conservato. `report_support::analytics_prefers_state_fallback()`
  sceglie lo stato se conserva più spettatori o copertura unica. La ricostruzione dallo stato fornisce intervalli
  di visione unica, senza metriche di revisione. La descrizione generale conservata ora spiega entrambe le
  fonti e il limite; la modifica non aggiunge chiamate di rendering. Avviso e comportamento fallback esistenti
  non cambiano.
- `analytics::mask_timeline_bins()` oscura sottogruppi positivi di spettatori/revisione sotto soglia. Gli intervalli
  a zero possono restare visibili; popolazioni di intervallo oscurate nascondono anche percentuali di retention
  che potrebbero rivelare il denominatore protetto. La metodologia ora descrive sottogruppi non vuoti, conteggi
  zero potenzialmente visibili e percentuali protette. `report_view::analytics_methodology()` continua a omettere
  questo testo per report esatti autorizzati individualmente. Nessuna regola o soglia privacy cambia.
- `report.php` mostra riepiloghi reazioni/segnalibri/prese visione/integrità prima del ramo senza visioni,
  mentre la tabella indipendente dei cluster può seguirlo. Prese visione o dati di integrità possono attivare
  quel ramo senza dati reazioni/segnalibri. L’avviso ora parla di intervalli validi e riepiloghi indipendenti
  eventualmente disponibili, soggetti a permessi/privacy. Non promette una posizione sotto l’avviso o un
  riepilogo in assenza della relativa funzione o dei dati.
- La legenda del colore intenso DE/HI poteva suggerire conteggi superiori al massimo della selezione.
  La heatmap normalizza rispetto al massimo visibile: le due traduzioni ora esprimono un’intensità relativa.
  La metodologia visione unica/revisione FR sostituisce l’ambiguo `revision` senza accento con il termine
  `relecture` già usato nelle etichette. Scala cromatica e metriche non cambiano.

I test esistenti coprono visibilità degli intervalli a zero, oscuramento sottogruppi, sola revisione sotto
soglia, metodologia esatta, scelta fallback e markup heatmap, inclusi
`analytics_test::test_privacy_threshold_keeps_zero_intervals_visible()` e
`report_view_test::test_exact_analytics_methodology_omits_privacy_threshold_text()`.
Tutti i corpi dei test sono invariati. Gli esiti della baseline accettata non sono nuove esecuzioni locali candidata.

## Chiavi esatte modificate nella candidata

- `report:analytics_description`
- `report:analytics_heatmap_legend_high`
- `report:analytics_method_privacy`
- `report:analytics_method_unique`
- `report:analytics_noviewingdata_events`

Le tre descrizioni sono corrette in tutti i pacchetti; la legenda intensa soltanto in DE/HI e il termine
visione unica/revisione soltanto in FR. Ogni pacchetto cambia solo i propri rilievi fondati sul codice.

| Pacchetto | Valori modificati |
|---|---:|
| DE | 4 |
| EN | 3 |
| ES | 3 |
| FR | 4 |
| HI | 4 |
| IT | 3 |
| PL | 3 |
| PT | 3 |

Totale: **27 valori modificati su cinque chiavi esistenti**. Chiavi, molteplicità dei placeholder e numeri
restano invariati in ogni pacchetto. Nessuna nuova chiave lingua o campo dati.

## Gate candidata e lavoro residuo

La 1.7.247 contiene 412 file distribuiti / 31 documenti per lingua, 166 file PHP, 1101 callable PHP/654 AMD
e 345 righe inventario non documentale. Schema invariato: XMLDB `2026110601`, 7 tabelle / 151 campi / 22 indici /
22 foreign key. ER cambia soltanto le etichette versione correnti. Runtime, permessi, dati, servizi, DB,
AMD, test, fixture e regole CI/analisi sono identici byte per byte alla 1.7.246 accettata.

I controlli locali di contratto/delta/link e applicazione/inversione esatta della patch sono distinti dalle
esecuzioni native. Servono ancora tutti i sei job CI candidata, poi ZIP reale dopo merge/report server/strict.
Suite attesa invariata: 379 test PHPUnit / 4076 asserzioni e 50 scenari Behat / 1074 passi. Sono aspettative
della baseline, non esiti di esecuzione candidata. Restano aperti review semantica residua, U-020 manuale,
gate integrato produzione e successive fasi App/provider autenticati.
