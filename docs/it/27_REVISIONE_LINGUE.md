# Review linguistica basata sul codice

Albero corrente: VideoTrack **1.7.246** (`2026111005`). Review registrata 2026-11-10.

## Perimetro attuale e limiti

La review cumulativa copre **253 chiavi univoche per pacchetto / 2024 valori distinti** in DE, EN, ES, FR,
HI, IT, PL e PT. Le altre **744 chiavi per pacchetto sono fuori dalla review semantica dichiarata**.
Questa verifica assistita di codice e lingue non equivale a un’approvazione madrelingua. Le 997 chiavi e i
placeholder restano allineati; la parità strutturale non prova correttezza semantica o accessibilità.

U-017/U-007 restano chiusi. U-020 è aperto/rinviato su richiesta del manutentore: tutte le otto osservazioni
manuali restano NON ESEGUITE. Conservare il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
per il gate finale produzione. Non si deducono esiti manuali, screen reader o provider dal vivo.

## Punto di partenza accettato

- Versione reale 1.7.245 / `2026111004`: 412 file distribuiti identici a main
  `0309db75c57e379828fa9e049928bc2533e0ed6a`.
- SHA-256 ZIP: `31b706f1bf47deb87036939fc1188668b7646490ee0fb2c3c5a53bb54fad9268`.
- SHA-256 ZIP report server: `306f0139a21d0bcc15d96068e178466789b8492bc734a25e6244fd69ca476c3e`.
- CI candidata/main `42826573352` / `42830814382`: tutti i sei job superano PHPUnit 379/4076, Behat 50/1074
  e strict 10/0/0. Report server Moodle 5.0/5.3: PHPCS/lint 166, Grunt e PHPUnit 379/4076 superati; entrambi
  gli strict forniti sono 10/0/0. Hash AMD prima/dopo Grunt identici. Resta l’avviso noto sull’età dei dati
  Browserslist; il report server non esegue Behat o controlli assistivi manuali. Le segnalazioni PHPMD già
  revisionate e i totali informativi Psalm sono distinti dai gate configurati superati.

## Manifesto completo delle chiavi revisionate

I tre gruppi per prefisso sono esaustivi nei pacchetti correnti; seguono le restanti chiavi esatte.

| Prefisso chiave | Chiavi revisionate per pacchetto |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| Chiavi esatte aggiuntive elencate sotto | 55 |
| Totale chiavi univoche | 253 |

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
- `report:analytics_export_row_reactions`
- `report:analytics_heatmap_legend_reaction`
- `report:analytics_method_reactions`
- `report:analytics_noduration`
- `report:analytics_noviewingdata_reactions`
- `report:analytics_privacy_unavailable_reactions`
- `report:analytics_reaction_events`
- `report:analytics_reaction_students`
- `report:analytics_reaction_time`
- `report:analytics_reaction_type`
- `report:analytics_reactionclusters`
- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionclusters_none`
- `report:analytics_reactionclusters_title`
- `report:analytics_reactionlimit`
- `report:analytics_reactionmarker`
- `report:analytics_reactions_available`
- `report:analytics_reactions_cell`
- `report:analytics_reactions_detected`
- `report:analytics_reactionsummary`
- `report:analytics_reactionsummary_suppressed`
- `report:analytics_showreactions`
- `report:csvexport_bookmarks_help`
- `studentnotesenabled_help`

## Tranche Analytics delle reazioni

Questa tranche verifica **21 chiavi esistenti / 168 valori**: tutte le chiavi `report:analytics_` contenenti
`reaction`. Non ci sono sovrapposizioni con le 232 chiavi già accettate. La verifica comprende tabelle dei
cluster, controlli/marker della sovrapposizione, conteggi, oscuramento privacy e avvisi senza dati. Le chiavi
esistenti senza chiamata di rendering attiva sono conservate: la review non implica che siano visualizzate.
Le altre famiglie report/Analytics non sono incluse. I rilievi precedenti restano nel changelog principale.

- `report:analytics_export_row_reactions`
- `report:analytics_heatmap_legend_reaction`
- `report:analytics_method_reactions`
- `report:analytics_noviewingdata_reactions`
- `report:analytics_privacy_unavailable_reactions`
- `report:analytics_reaction_events`
- `report:analytics_reaction_students`
- `report:analytics_reaction_time`
- `report:analytics_reaction_type`
- `report:analytics_reactionclusters`
- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionclusters_none`
- `report:analytics_reactionclusters_title`
- `report:analytics_reactionlimit`
- `report:analytics_reactionmarker`
- `report:analytics_reactions_available`
- `report:analytics_reactions_cell`
- `report:analytics_reactions_detected`
- `report:analytics_reactionsummary`
- `report:analytics_reactionsummary_suppressed`
- `report:analytics_showreactions`

## Rilievi dal codice e correzioni

`report.php` ricava la soglia effettiva dai permessi su tutte le attività dell’ambito selezionato: l’accesso
ai report individuali consente `analytics::EXACT_REPORT_MIN_USERS` (1), mentre l’accesso solo aggregato
conserva la soglia configurata `analyticsminusers`. `cluster_reactions()` riceve il valore effettivo. La
didascalia della tabella ora si riferisce alla soglia di questo report senza suggerire che valga sempre
il valore del sito.

`analytics::append_visible_reaction_cluster()` controlla l’idoneità per utenti distinti prima di applicare
`MAX_REACTION_CLUSTERS` (2000). Ulteriori cluster idonei impostano `truncated`; il sottoinsieme conservato
viene ordinato dopo la raccolta, quindi non si promette che sia il primo cronologicamente tra tutti i tipi
di reazione. `report.php` mostra l’avviso quando ci sono omissioni. Disabilitare la sovrapposizione salta
raccolta e visualizzazione dei cluster, senza recuperare quelli omessi. L’avviso corretto spiega limite e
omissioni e indica soltanto che un ambito ridotto può evitare il limite. Non garantisce l’esito restringendo
l’ambito né un export completo. Conteggi riepilogativi, filtri di accesso e oscuramento privacy non cambiano.

Evidenze: rami soglia/overlay/avviso di `report.php`; metodi di clustering/visibilità in
`classes/local/analytics.php`; `classes/local/report_timeline_view.php::reaction_clusters()`; spiegazioni
metodologiche/privacy di `classes/local/report_view.php`. Il test esistente
`analytics_test::test_reaction_cluster_limit_is_reported()` copre limite e indicatore di omissioni: il corpo
è invariato e non se ne dichiara una nuova esecuzione locale. Nessun nuovo test replica questi soli testi.

## Chiavi esatte modificate nella candidata

- `report:analytics_reactionclusters_caption`
- `report:analytics_reactionlimit`

| Pacchetto | Valori modificati |
|---|---:|
| DE | 2 |
| EN | 2 |
| ES | 2 |
| FR | 2 |
| HI | 2 |
| IT | 2 |
| PL | 2 |
| PT | 2 |

Totale: **16 valori modificati su due chiavi esistenti**. Chiavi, molteplicità dei placeholder e numeri
restano invariati in ogni pacchetto. Nessuna nuova chiave lingua o campo dati.

## Gate candidata e lavoro residuo

La 1.7.246 contiene 412 file distribuiti / 31 documenti per lingua, 166 file PHP, 1101 callable PHP/654 AMD
e 345 righe inventario non documentale. Schema invariato: XMLDB `2026110601`, 7 tabelle / 151 campi / 22 indici /
22 foreign key. ER cambia soltanto le etichette versione correnti. Runtime, permessi, dati, servizi, DB,
AMD, test, fixture e regole CI/analisi sono identici byte per byte alla 1.7.245 accettata.

I controlli locali di contratto/delta/link e applicazione/inversione esatta della patch sono distinti dalle
esecuzioni native. Servono ancora tutti i sei job CI candidata, poi ZIP reale dopo merge/report server/strict.
Suite attesa invariata: 379 test PHPUnit / 4076 asserzioni e 50 scenari Behat / 1074 passi. Sono aspettative
della baseline, non esiti di esecuzione candidata. Restano aperti review semantica residua, U-020 manuale,
gate integrato produzione e successive fasi App/provider autenticati.
