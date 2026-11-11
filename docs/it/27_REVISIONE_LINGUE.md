# Review linguistica basata sul codice

Albero corrente: VideoTrack **1.7.249** (`2026111102`). Review registrata 2026-11-11.

## Perimetro e limiti

La review cumulativa copre **789 chiavi univoche per pacchetto / 6312 valori** in DE, EN, ES, FR, HI, IT, PL e PT.
Le altre **208 chiavi per pacchetto restano fuori dalla review semantica dichiarata**. Tutte le 997 chiavi e le
molteplicità dei placeholder sono allineate strutturalmente. Questa review assistita sul codice non equivale
ad approvazione madrelingua, accessibilità manuale o certificazione di ogni combinazione browser/provider.

U-017 e U-007 restano chiusi. U-020 resta aperto e rinviato su richiesta del manutentore; tutte le otto
osservazioni manuali sono NON ESEGUITE. Il [protocollo manuale](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
resta parte del gate finale produzione. Il lavoro linguistico indipendente non approva tali osservazioni.

## Punto di partenza accettato

- Versione reale 1.7.248 / `2026111101`, 412 file identici a main `9e53a6f1eb042b57612aca0bae7e1b263f5771e6`.
- SHA-256 ZIP: `05f0c797dca4fef7cd629300b20bc00d501ea6d58e23fa67d39415e6715b6daa`.
- SHA-256 ZIP report server: `43144759d020f0eedac37dbf2a30cfe05cc0a89fff4d695a82a1bc896302669d`.
- CI candidata `42995227950`: sei job superano PHPUnit 379/4076, Behat 50/1074 e strict 10/0/0 al primo
  tentativo degli scenari. CI dopo merge `42999353066`: tutti i sei job terminano con successo; cinque
  superano Behat al primo tentativo. Moodle 5.1 fallisce inizialmente un’asserzione sulla visibilità della
  reazione dopo rollback Vimeo: l’etichetta è presente ma non visibile. La riesecuzione completa supera
  1 scenario / 43 passi. Resta invariata la policy di riesecuzione dello strumento. La causa esatta non è
  provata: conservare l’osservazione intermittente per eventuali ricorrenze, senza dichiarare sei primi
  tentativi puliti o una regressione funzionale dimostrata.
- I report server reali Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e PHPUnit 379/4076. Entrambi gli strict
  forniti sono 10/0/0; gli hash AMD prima/dopo Grunt coincidono. I report server non eseguono Behat o controlli
  assistivi manuali. Restano registrati l’avviso noto Browserslist, 24 finding PHPMD revisionati e i totali
  informativi Psalm 732/760. I gate configurati passano: non si dichiarano zero segnalazioni consultive
  né una nuova esecuzione nativa nel workspace.

## Manifesto completo delle chiavi cumulative

I selettori seguenti sono esaustivi e disgiunti. “Senza due punti” include completion e le chiavi senza
prefisso di metadati, attività, player, strumenti di studio e ciclo di vita. Le chiavi conservate senza
rendering attivo sono revisionate come testo conservato: non si introducono o certificano nuove funzioni.

| Selettore | Chiavi revisionate per pacchetto |
|---|---:|
| `privacy:` | 74 |
| `setting:` | 110 |
| `report:` | 260 |
| `coursereport:` | 25 |
| `teacherdashboard:` | 15 |
| `ctrl:` | 12 |
| `html5:` | 12 |
| `source:` | 3 |
| `err:` | 14 |
| `completiondetail:` | 8 |
| Tutte le chiavi senza due punti | 228 |
| Altre chiavi esatte elencate sotto | 28 |
| Totale chiavi univoche | 789 |

- `acknowledgement:enabled_desc`
- `acknowledgement:pendingvideoend`
- `acknowledgement:privacyhint`
- `acknowledgement:text_help`
- `acknowledgement:timing_help`
- `acknowledgement:videoendrequired`
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

## Tranche corrente attività/player/strumenti di studio

Questa tranche esamina **259 nuove chiavi / 2072 valori**, senza riusare chiavi accettate: le restanti 218
chiavi senza prefisso e tutte le 41 chiavi `ctrl:`, `html5:`, `source:` e `err:`. Resta accettata la precedente
evidenza su 530 chiavi. Il cumulativo è 789; ne restano 208. I rilievi precedenti sono conservati nel changelog.

### Rilievi basati sui percorsi effettivi del codice

- Sottotitoli: YouTube riceve flag di preferenza; Vimeo richiede una traccia esistente corrispondente e gestisce
  l’assenza della traccia. Nessuno dei due crea tracce sul provider. HTML5 allega un singolo `<track>` VTT e usa
  la lingua come `srclang`/etichetta. Gli help distinguono disponibilità, metadati e file separati di trascrizione.
  Non si introducono sottotitoli nativi multipli o un overlay Moodle sopra gli iframe esterni.
- Le reazioni si possono salvare in pausa in un punto già visto, secondo le regole esistenti di partecipazione,
  feature, sessione e validazione. L’avviso di storico vuoto non implica più che il video debba essere in corso.
  Le etichette mirate distinguono il minimo di tipi distinti da un requisito generico sulle reazioni.
- `view.php` fornisce la posizione salvata sul server soltanto se la ripresa è abilitata e supera 5 secondi.
  Il ritorno manuale all’indietro rispetta comunque la policy. Il recupero della posizione dal browser nei singoli
  provider è un percorso esistente separato: il testo non promette che la soglia governi ogni cache degli adapter
  o che disabilitare la ripresa server la cancelli. Nessun percorso di ripresa cambia o viene ricertificato qui.
- `videotrack_pluginfile()` applica accesso Moodle a corso/modulo e `mod/videotrack:view`, più controlli su area
  e contenuto. L’avviso non dice più “solo studenti iscritti”: esistono anche staff e altri accessi consentiti.
- `reaction_file_icon_errors()` accetta icone nuove o esistenti e ne richiede una per una reazione configurata
  abilitata di tipo file. L’help non la definisce facoltativa in quel caso. `videotrack_resize_reaction_icon()`
  conserva l’originale se l’elaborazione non è disponibile o fallisce: l’avviso 64×64 ora indica tale fallback.
- Il poster scompare all’avvio, anche automatico: il clic su Play non è l’unico trigger. Lo slider HTML5 riceve
  un’etichetta sulla posizione video invece di traduzioni che significano “Cerca”. Gli avvisi capitoli/trascrizione
  DE/PT usano termini di avanzamento e riavvolgimento; la ricerca testuale conserva le proprie etichette.
- Allineati unità/media/larghezza e istruzione autoplay polacchi. Gli avvisi restore recuperano diacritici e
  scrittura hindi senza cambiare placeholder, mapping o reazione segnaposto.

Fonti: `view.php`, i tre adapter, `core/player/timed_text` e helper poster, `form_definition`, `mod_form.php`,
`lib.php`, `reaction_form_validation` e test esistenti media/interazioni/timed-text/restore. Le esecuzioni
accettate precedenti non sono presentate come test della candidata.

## Chiavi esatte modificate nella candidata

- `allowdownload`
- `autoblockedlabel`
- `captions_help`
- `captionslang_help`
- `chapterfile_notice`
- `fastforwardstep`
- `html5:seek`
- `noreactionsyet`
- `playerwidth`
- `posterimage_help`
- `reactioniconfile_help`
- `reactioniconfile_notice`
- `reactionsrequired`
- `restore_missing_reaction_mapping`
- `restore_placeholder_reaction`
- `resumeplayback_desc`
- `resumeplayback_help`
- `rewindstep`
- `showtranscript_help`
- `timedtext_seek_blocked`
- `videofile`
- `videofile_notice`

Nove stringhe cambiano in ogni pacchetto; le altre modifiche risolvono rilievi mirati di traduzione.

| Pacchetto | Valori modificati |
|---|---:|
| DE | 14 |
| EN | 9 |
| ES | 12 |
| FR | 12 |
| HI | 11 |
| IT | 9 |
| PL | 19 |
| PT | 14 |

Totale: **100 valori su 22 chiavi esistenti**. Nessuna chiave aggiunta o rimossa. Conservati molteplicità dei
placeholder, token numerici, identificatori tecnici, intestazioni CSV e logica di formattazione. Cambia
intenzionalmente il nome accessibile dello slider HTML5. Non si aggiungono test che duplicano solo i testi.

## Gate candidata e sequenza concordata

L’albero conserva 412 file, 31 documenti per guida EN/IT, 166 file PHP, 1101 callable PHP/654 AMD e 345 righe
d’inventario. XMLDB resta `2026110601`: 7 tabelle / 151 campi / 22 indici / 22 chiavi esterne. Gli ER cambiano
solo etichetta di versione. Logica runtime, permessi, dati, DB, servizi, AMD, test, fixture e regole CI/analisi
statica sono identici byte per byte alla 1.7.248 reale accettata.

I controlli locali su contratti/delta/link e applicazione/inversione non sostituiscono CI candidata su sei job,
CI dopo merge e ZIP/report server/strict reali. Suite attesa invariata: 379 test PHPUnit / 4076 asserzioni e
50 scenari Behat / 1074 passi. Sono attese, non risultati della candidata.

Completare lavoro web e decisione App, poi la fase concordata sui sottotitoli VTT multilingua prima della base
autenticata, OneDrive/SharePoint, Zoom Cloud e Google Drive. Il pannello trascrizione resta distinto. Controlli
HTML5/ciclo file e fattibilità provider esterni/fullscreen richiedono evidenze proprie. Non è selezionata la
pubblicazione automatica sugli account provider. U-020 resta rinviato.
