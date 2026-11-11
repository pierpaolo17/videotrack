# Review linguistica basata sul codice

Albero corrente: VideoTrack **1.7.248** (`2026111101`). Review registrata 2026-11-11.

## Perimetro attuale e limiti

La review cumulativa copre **530 chiavi univoche per pacchetto / 4240 valori distinti** in DE, EN, ES, FR,
HI, IT, PL e PT. Le altre **467 chiavi per pacchetto sono fuori dalla review semantica dichiarata**. Tutte
le 997 chiavi e le molteplicità dei placeholder restano allineate. Questa review assistita non equivale
ad approvazione madrelingua, accessibilità manuale o prova di ogni combinazione browser/provider distribuita.

U-017/U-007 restano chiusi. U-020 è aperto/rinviato su richiesta del manutentore; tutte le otto osservazioni
manuali restano NON ESEGUITE. Conservare il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
per il gate finale produzione. Il lavoro linguistico indipendente non cambia tale decisione.

## Punto di partenza accettato

- Versione reale 1.7.247 / `2026111006`, 412 file identici a main `68754eb1ddf76a0566a36e93526cc00d2137e4d2`.
- SHA-256 ZIP: `6d120c597c97e2d9b245044daea22a212c3f203f152bb2253c31fcf7cdfa8abd`.
- SHA-256 ZIP report server: `3f3e23dd4c9d065dc1b3c6517100c8f482bd2bdef23deda8c20ae806dcd82b35`.
- CI candidata/main `42912958780` / `42982684717`: tutti i sei job superano PHPUnit 379/4076, Behat 50/1074
  e strict 10/0/0. I report server reali Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e PHPUnit 379/4076;
  entrambi gli strict forniti sono 10/0/0. Hash AMD prima/dopo Grunt identici. Avviso noto Browserslist,
  24 finding PHPMD revisionati e totali informativi Psalm 732/760 restano invariati. I gate configurati
  sono superati: non si dichiarano zero segnalazioni consultive. I report server non eseguono Behat o
  controlli assistivi manuali. Non si dichiara una nuova esecuzione nativa in questo workspace.

## Manifesto completo delle chiavi revisionate

I sei gruppi per prefisso sono esaustivi nei pacchetti correnti; seguono le altre chiavi esatte.

| Prefisso chiave | Chiavi revisionate per pacchetto |
|---|---:|
| `privacy:` | 74 |
| `completion` | 14 |
| `setting:` | 110 |
| `report:` | 260 |
| `coursereport:` | 25 |
| `teacherdashboard:` | 15 |
| Altre chiavi esatte | 32 |
| Totale chiavi univoche | 530 |

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
- `studentnotesenabled_help`

## Tranche corrente report/export/dashboard

La tranche verifica **179 chiavi / 1432 valori**: tutte le 139 chiavi `report:` non Analytics, 25 chiavi
`coursereport:` e 15 `teacherdashboard:`. Sono esaminate 178 nuove chiavi / 1424 valori e riusata una chiave
accettata invariata: `report:csvexport_bookmarks_help`. Resta accettata la review precedente delle 121 chiavi
Analytics. Sono ora coperte tutte le 260 chiavi report e le due famiglie dashboard. Chiavi conservate senza
chiamata diretta di rendering attiva non sono presentate come nuove funzioni visibili; gli avvisi privacy
dashboard inutilizzati non vengono reintrodotti. L’accesso ai conteggi esatti delle dashboard non cambia.

## Rilievi dal codice

- `report_support::cluster_reaction_events()` continua a leggere eventi ordinati dopo il limite configurato,
  scartando nuovi cluster ma potendo aggiungere eventi a cluster già attivi. `report.php` avvisa; in assenza
  di filtro sul tempo video nasconde la tabella limitata e ferma il CSV cumulativo prima delle righe cluster.
  Con un filtro presente, righe limitate possono ancora essere mostrate/esportate con avvisi. Restringere
  l’intervallo non garantisce completezza. Quattro avvisi/help ora spiegano questi confini; il testo heatmap
  descrive il sottoinsieme conservato senza promettere dati completi nella tabella.
- Il CSV personalizzato in `report.php` conserva il filtro learner consentito. La modalità complessiva azzera
  soltanto la selezione del singolo studente, non i permessi. Dettagliata/sintetica conservano le rispettive
  righe reazioni/note; i bookmark sono conteggi per studente o complessivi, senza etichette/timestamp privati.
  L’help ora comprende questi fatti. Esempio di concatenazione note e relativi numeri restano invariati.
- Il ricalcolo passa da `videotrack_recalculate_all_states()` e `tracker::rebuild_state_from_segments()`:
  segmenti conservati con `servervalidated = 1`, durata salvata autorevole e condizioni completion correnti.
  Il testo distingue il ricalcolo dal recupero di evidenze eliminate. Questa patch testuale non crea segmenti,
  credito o budget di sessione; la sincronizzazione completion esistente è preservata.
- Correzioni mirate riguardano studenti che hanno iniziato, attività/assenza visioni, azione polacca di ricalcolo
  completion e ambito utenti export hindi. `report:timestamp` e la chiave conservata `report:sorttime` italiana
  diventano «Tempo del video»: celle e CSV di produzione usano offset video, con date di creazione separate.
  L’intestazione CSV italiana cambia quindi intenzionalmente; ordine colonne, valori e codice di formattazione
  non cambiano. Le intestazioni timestamp delle altre lingue sono conservate.

Evidenze: `report.php`, `report_support`, `csv_export`/`csv_event_writer`/`csv_event_timestamps`,
`course_analytics`/`teacher_analytics`, `reports_course.php`, `reports_teacher.php`, `lib.php` e `tracker`.
Test esistenti cluster/export/corso/ricalcolo invariati; gli esiti della baseline non sono riattribuiti alla
candidata. Nessun nuovo test duplica queste sole correzioni di valori letterali.

## Chiavi esatte modificate nella candidata

- `coursereport:col_students_started`
- `coursereport:intro`
- `coursereport:nodata`
- `report:clusterlimitreached`
- `report:clusterlimitreached_help`
- `report:clusterlimitrequiresfilters`
- `report:clusterlimitrequiresfilters_csv`
- `report:csvexport_format_help`
- `report:csvexport_scope`
- `report:heatmap_desc`
- `report:heatmap_supplementary`
- `report:noattempts`
- `report:recalculate`
- `report:recalculate_description`
- `report:sorttime`
- `report:timestamp`

Sette descrizioni cambiano in ogni pacchetto; le altre modifiche sono rilievi mirati delle traduzioni.

| Pacchetto | Valori modificati |
|---|---:|
| DE | 11 |
| EN | 7 |
| ES | 8 |
| FR | 8 |
| HI | 9 |
| IT | 11 |
| PL | 9 |
| PT | 8 |

Totale: **71 valori su 16 chiavi esistenti**. Nessuna chiave aggiunta o rimossa. Placeholder, numeri ed esempi
originali delle note per lingua sono conservati. I rilievi precedenti restano nel changelog principale.

## Gate candidata e sequenza concordata

La candidata conserva 412 file, 31 documenti EN/IT ciascuno, 166 file PHP, 1101 callable PHP/654 AMD e 345 righe
inventario. XMLDB resta `2026110601`: 7 tabelle / 151 campi / 22 indici / 22 foreign key. ER cambia soltanto
etichette versione. Logica runtime, permessi, dati salvati, DB, servizi, AMD, test, fixture e regole CI sono
identici byte per byte alla 1.7.247 accettata. Cambiano soltanto testi, guide e metadati versione.

I controlli locali contratto/delta/link e applicazione/inversione esatta non sostituiscono sei job CI candidata,
poi ZIP reale dopo merge/report server/strict. Suite attesa invariata: 379 test PHPUnit / 4076 asserzioni,
50 scenari Behat / 1074 passi. Sono attese, non esiti candidata.

Ordine concordato col manutentore: terminare lavoro web precedente e decisione App, poi aggiungere sottotitoli
VTT multilingue prima della foundation autenticata e di OneDrive/SharePoint, Zoom Cloud e Google Drive.
Il pannello di trascrizione multilingue esistente resta distinto dai sottotitoli nel video. Qui non cambia
il runtime VTT. Controlli HTML5/lifecycle file e fattibilità provider esterni/fullscreen richiedono evidenze
dedicate; la pubblicazione automatica su account terzi non è inclusa nel piano. U-020 resta rinviato;
U-017/U-007 non vengono riaperti per metriche cosmetiche.
