# mod_videotrack — Funzionalità e potenzialità

**Versione**: 1.0.53 (build 2026062200)
**Compatibilità**: Moodle 5.0+  
**Lingue incluse**: Italiano, Inglese, Tedesco, Spagnolo, Francese, Portoghese, Hindi, Polacco, Ucraino

---

## Cos'è mod_videotrack

`mod_videotrack` è un modulo attività per Moodle che permette ai docenti di inserire video nei corsi (da YouTube, Vimeo o file caricati direttamente) e di **tracciare con precisione quanto ogni studente ha guardato**, quante parti ha saltato, a quale velocità e quante volte ha interagito con i contenuti.

A differenza dei semplici link video, mod_videotrack raccoglie dati comportamentali precisi: sa quando uno studente ha messo in pausa, saltato avanti, cambiato velocità o interagito con i contenuti. Non è possibile — con nessuno strumento lato browser — sapere se gli occhi dello studente erano effettivamente sullo schermo; il modulo misura l'**attenzione dichiarata dal comportamento** (visione continua, assenza di skip, uso delle reazioni), non l'attenzione cognitiva reale.

---

## 1. Sorgenti video supportate

Il docente sceglie una delle tre sorgenti al momento della creazione dell'attività:

**YouTube** — incolla l'URL del video (qualsiasi formato: `youtu.be`, `youtube.com/watch`, `youtube.com/embed`). Il player è l'IFrame API ufficiale di YouTube, con supporto a sottotitoli, qualità automatica e modalità schermo intero.

**Vimeo** — incolla l'URL del video Vimeo pubblico o unlisted. Il player usa l'SDK ufficiale `player.vimeo.com/api/player.js` con attributo `dnt=true` (do-not-track) per rispettare la privacy degli studenti.

**File caricato** — il docente carica direttamente un file video (`.mp4`, `.webm`, `.m4v`, `.mov`) o audio (`.mp3`, `.aac`, `.m4a`) tramite il file picker di Moodle. Il player è l'elemento HTML5 nativo del browser, personalizzabile con controlli su misura.

La sorgente può essere cambiata anche dopo la creazione dell'attività.

---

## 2. Tracciamento della visione

Il cuore del modulo è il sistema di tracciamento, che funziona così:

### 2.1 Segmenti di visione

Ogni volta che uno studente guarda un frammento continuo di video, il modulo salva un **segmento**: un record con `videotimestart`, `videotimeend`, `playbackrate`, `wallclockstart` e `wallclockend`. I segmenti vengono inviati al server:

- **all'evento pause** (studente pausa manualmente)
- **all'evento seek** (studente salta avanti o indietro)
- **all'evento ended** (video finito)
- **ogni N secondi** tramite heartbeat (configurabile, default 30s): salva il progresso periodicamente per evitare perdita di dati in caso di crash del browser
- **su `beforeunload`** (chiusura tab/finestra) tramite `navigator.sendBeacon()`, che funziona anche quando il browser sta per chiudere la pagina

### 2.2 Calcolo della copertura unica

I segmenti grezzi vengono processati da `tracker::update_state()`:

1. I segmenti si **fondono** tra loro (`merge_intervals`): se lo studente ha guardato 0–30s, poi torna a 20s e guarda 20–50s, il risultato è un unico intervallo 0–50s
2. Si calcola il **totale di secondi unici** guardati (`uniquecoveredseconds`)
3. Si calcola la **percentuale di completamento** rispetto alla durata totale del video
4. Il risultato viene aggiornato in tempo reale nella sidebar dello studente

La barra di progresso visiva (canvas verde) mostra esattamente quali parti del video sono state guardate.

### 2.3 Protezione validazione di integrità accademica

Il server valida ogni segmento ricevuto con un controllo server-side basato sull'ultimo segmento accettato nella sessione, sull'heartbeat configurato e sulla velocità di riproduzione. Se un client prova a inviare un intervallo video troppo lungo rispetto al tempo realmente trascorso lato server, il segmento viene rigettato senza salvare dati comportamentali nel log.

Questo riduce il rischio che script automatici (bot o `fetch` manuali) possano inviare segmenti falsi per simulare la visione del video. Il controllo è intenzionalmente conservativo: assorbe piccoli ritardi di rete, ma non considera mai affidabili i timestamp wallclock inviati dal browser.

### 2.4 Blocco seek

Il docente può bloccare il fast-forward o il rewind:

- **`allowseekforward = false`**: lo studente non può saltare avanti — se ci prova, viene riportato automaticamente al punto dove stava guardando
- **`allowseekbackward = false`**: lo studente non può tornare indietro
- Entrambi funzionano su tutti e tre i player (YouTube usa polling ogni 2s, Vimeo e HTML5 usano gli eventi nativi `seeking`/`seeked`)

---

## 3. Reazioni contestuali

Le reazioni sono bottoni personalizzabili (emoji, icone Font Awesome o immagini caricate) che lo studente può premere **mentre il video è in riproduzione** per segnalare una risposta emotiva, una domanda, un momento di difficoltà o qualsiasi altro segnale pedagogicamente utile.

Ogni reazione viene salvata con il **timestamp preciso nel video** in cui è stata premuta.

### 3.1 Configurazione delle reazioni

Il docente può definire un numero illimitato di reazioni. Per ogni reazione specifica:

- **Etichetta**: nome breve (es. "Non capisco")
- **Descrizione**: testo esteso visualizzato nel tooltip
- **Icona**: emoji Unicode, classe Font Awesome o immagine caricata (ridimensionata automaticamente a 64×64px)
- **Richiesta per il completamento**: se spuntata, lo studente deve aver usato questa specifica reazione per completare l'attività

### 3.2 Preset di reazioni

Il modulo include un sistema di preset: l'amministratore può definire insiemi di reazioni predefinite (es. "Domande frequenti", "Analisi emotiva", "Feedback rapido") che il docente può applicare con un click al posto di configurare le reazioni manualmente.

### 3.3 Throttle anti-spam

Il sistema impedisce il salvataggio di reazioni duplicate: se lo studente clicca lo stesso bottone più volte in 3 secondi, solo la prima viene salvata.

---

## 4. Note personali studente

Quando il docente abilita le note (configurable anche come default a livello di piattaforma), nella sidebar compare un pannello collassabile con una textarea.

Lo studente può:
- Scrivere testo libero (max 2000 caratteri)
- Salvare la nota al **timestamp corrente del video** (il video continua a girare durante la digitazione)
- Vedere l'elenco di tutte le note scritte, con timestamp e testo
- Eliminare singole note

Il pannello è collassabile con toggle show/hide; la preferenza viene salvata in `sessionStorage` per la durata della sessione browser.

Le note sono **private**: solo lo studente che le ha scritte può vederle (e il docente nel report dedicato). Non vengono mai mostrate ad altri studenti.

---

## 5. Transcript VTT interattivo

Disponibile solo per video caricati direttamente, quando il docente ha caricato un file VTT (sottotitoli WebVTT).

Il transcript appare nella sidebar come lista di cue (sottotitoli) cliccabili:

- **Sincronizzazione automatica**: la cue corrente viene evidenziata mentre il video avanza
- **Click per navigare**: cliccando una cue, il video salta al punto corrispondente
- **Scroll automatico**: il pannello scorri per mantenere la cue attiva sempre visibile
- Accessibile da screen reader con `aria-live="polite"` per gli aggiornamenti dinamici

---

## 6. Capitoli VTT navigabili

Se il file VTT contiene cue con testo breve (meno di 80 caratteri), queste vengono interpretate come **titoli di capitolo** e viene mostrata una barra di navigazione sopra i controlli del player.

Ogni capitolo è un bottone: cliccandolo il video salta all'inizio di quel capitolo. Il capitolo attivo viene evidenziato visivamente e comunicato agli screen reader con `aria-current="true"`.

---

## 7. Poster/anteprima pre-play

Il docente può caricare un'immagine di anteprima (JPG, PNG, WebP, GIF) che viene mostrata sopra il player prima che lo studente clicchi play.

Sopra il poster è visibile un grande bottone play circolare. Appena lo studente avvia il video, il poster scompare con un'animazione fade-out (0.3s). Funziona su tutte e tre le sorgenti video.

---

## 8. Resume automatico

Se il docente abilita questa opzione, quando uno studente riapre un'attività che aveva già iniziato, il video parte automaticamente dal punto in cui si era fermato (se superiore a 5 secondi).

Viene mostrato un banner informativo con il tempo di ripresa (es. "Riprendendo da 14:32"), con un bottone per chiuderlo manualmente. Il banner scompare automaticamente dopo 6 secondi.

Il punto di resume corrisponde a dove lo studente **ha smesso di guardare**, non al punto massimo raggiunto: se lo studente è arrivato a 50:00 e poi è tornato indietro a 10:00, riprenderà da 10:00.

---

## 9. Limiti di velocità

Il docente può impostare una **velocità massima di riproduzione** (1.25×, 1.5×, 1.75×, 2×, 3× o 4×). Se lo studente prova ad alzare la velocità oltre il limite, il sistema la riporta automaticamente al massimo consentito — su tutti e tre i player.

I valori sono memorizzati in centesimi (150 = 1.5×) per evitare imprecisioni floating-point.

---

## 10. Completamento attività

Il modulo supporta regole di completamento personalizzate che il docente può combinare:

| Regola | Significato |
|---|---|
| **Percentuale minima** | Lo studente deve aver guardato almeno X% del video (secondi unici / durata totale) |
| **Numero minimo di reazioni** | Lo studente deve aver cliccato almeno N reazioni diverse |
| **Tutti i tipi di reazione** | Lo studente deve aver usato almeno una volta ogni tipo di reazione configurato |
| **Reazioni specifiche richieste** | Alcune reazioni singole possono essere marcate come obbligatorie |

Le regole si possono combinare in logica AND (tutte devono essere soddisfatte) oppure OR (basta una).

Lo stato di completamento viene aggiornato in tempo reale ad ogni segmento salvato e ad ogni reazione.

---

## 11. Valutazione (voto)

Il docente può associare un voto all'attività (numerico o su scala Moodle). Il voto viene assegnato manualmente dal docente tramite il report dell'attività, che mostra un campo input per ogni studente.

Il voto opzionalmente appare allo studente nella sua vista dell'attività, con indicatore visivo di sufficienza/insufficienza (✓/✗) se è stata configurata una soglia minima.

---

## 12. Report docente

Il docente accede a un report completo con due modalità:

**Per studente**: tabella con tutti gli studenti iscritti, mostrando per ciascuno:
- Secondi unici guardati (formato MM:SS)
- Percentuale di completamento
- Ultima posizione raggiunta
- Stato di completamento (sì/no)
- Voto (se configurato) con form di assegnazione inline
- Pulsante "Azzera progresso" per resettare tutti i dati di uno studente

**Cumulativo (heatmap)**: tabella aggregata che mostra per ogni cluster temporale (es. al minuto 3:30) quante reazioni sono state ricevute, da quanti studenti distinti e in quale intervallo esatto. Una heatmap SVG accessibile mostra la distribuzione visiva sul timeline del video.

Entrambe le viste supportano filtri per studente e per tipo di reazione, ed export CSV.

**Report note**: nella modalità per-studente, sotto la tabella principale, il docente vede tutte le note personali degli studenti (timestamp, testo, data di scrittura), raggruppate per studente con rowspan accessibile. Il docente può esportare le note in CSV separato.

---

## 13. Report a livello di corso

Un report separato (`reports_course.php`) aggrega i dati di tutte le istanze videotrack del corso: media di completamento, studenti che hanno completato, totale di secondi guardati, numero di reazioni per istanza. Accessibile dalla navigazione "Report del corso".

---

## 14. Accessibilità (WCAG 2.2)

Il modulo è stato progettato con l'accessibilità come requisito non negoziabile:

- Tutti i bottoni hanno `aria-label` contestuale (es. "Replay — 01:03", non solo "Replay")
- I bottoni reazione usano `aria-disabled` invece di `disabled` HTML per restare nel tab order
- La barra canvas degli intervalli ha `role="img"` e `aria-label` aggiornato dinamicamente con la percentuale
- Il pannello transcript usa `aria-live="polite"` per aggiornamenti dinamici
- Le tabelle hanno `<caption>` (non duplicata con `aria-label`)
- Il bottone "Chiudi" del banner resume usa `dismisslabel` localizzato
- I bottoni skip ⏪/⏩ hanno `aria-label` nel formato "Indietro 10 secondi" (localizzato)
- Il focus viene gestito correttamente dopo eliminazione di righe (reazioni e note)
- Heading gerarchici coerenti (H2 → H3 → H4) senza salti

---

## 15. Privacy, GDPR e conservazione dati

Il modulo implementa la Privacy API di Moodle e adotta una policy di minimizzazione orientata alla conservazione delle statistiche aggregate:

- Tutti i dati personali sono documentati in `classes/privacy/provider.php::get_metadata()`.
- `export_user_data()` esporta segmenti, stato aggregato, reazioni e note personali in formato leggibile.
- Le note personali sono esportate separatamente dalle reazioni e l'esportazione CSV del report mostra un avviso perché può contenere dati personali.
- Il poster immagine è caricato dal **docente** e non è un dato personale dello studente; viene escluso dall'export privacy dello studente.
- L'impostazione amministrativa `retentionperioddays` controlla la conservazione automatica: `0` significa conservazione illimitata; un valore positivo indica dopo quanti giorni i dati personali di tracking, note e reazioni vengono anonimizzati.
- Le richieste di cancellazione/oblio dell'utente non eliminano fisicamente le statistiche aggregate: i dati vengono anonimizzati con identificativi pseudonimi negativi, salted e scoped per attività, in modo da rimuovere il collegamento all'utente reale preservando le analisi aggregate.
- I record anonimizzati sono esclusi dalle liste utenti della Privacy API e nei report vengono visualizzati come "Utente anonimizzato".
- La retention automatica opera per coppia utente/attività: quando un record personale supera la soglia configurata, tutti i dati personali di quell'utente in quell'attività vengono anonimizzati insieme. Non viene eseguita una cancellazione parziale dei singoli intervalli storici dentro uno stato aggregato.
- Gli identificativi anonimi sono pseudonimi tecnici irreversibili per gli operatori ordinari del sito: sono negativi, salted e scoped per attività, quindi non corrispondono ad account Moodle reali e non vanno usati per re-identificare gli utenti.
- Il salt locale di anonimizzazione viene creato una sola volta e protetto da lock Moodle per evitare race condition.

---

## 16. Backup e ripristino

Il modulo supporta il sistema backup/restore di Moodle 2:

- Tutti i campi di configurazione vengono salvati.
- Le filearea `videocontent`, `subtitles`, `posterimage`, `reactionicon` vengono incluse nel backup.
- Le reazioni definite, gli eventi, i segmenti, gli stati e le note vengono salvati e ripristinati quando il backup include i dati utente.
- Le note personali (`notetext`, `notetype`) vengono incluse nei dati utente e restano soggette alla Privacy API.
- I record già anonimizzati, riconoscibili da `userid` negativo, non sono rimappati su utenti Moodle reali durante il restore: vengono preservati come dati aggregati anonimi/pseudonimi, così i report storici restano coerenti senza re-identificare utenti cancellati.

---

## 17. Configurazione amministratore

L'amministratore di piattaforma controlla i default e i limiti attraverso la pagina Amministrazione → Plugin → Moduli attività → Video track:

- Default per autoplay, loop, mute, download, controlli player, velocità consentite
- Velocità di heartbeat (default 30s, minimo 5s)
- Limite massimo velocità di default
- Resume automatico abilitato di default
- Note personali abilitate di default
- Modalità "distraction-free" (layout embedded senza menu Moodle)
- Preset di reazioni predefiniti

Le singole impostazioni possono essere **bloccate**: se il docente non ha la capability `mod/videotrack:overrideplayersettings`, i campi del form vengono mostrati come readonly e i valori admin vengono usati sempre.

---

## 18. Modalità mobile

Il file `db/mobile.php` dichiara il supporto all'app mobile Moodle. Le funzionalità principali (visione del video, reazioni, note) sono accessibili anche da dispositivi mobili tramite il player HTML5 nativo.

---

## Tabella riepilogativa feature

| Feature | YouTube | Vimeo | Upload HTML5 |
|---|---|---|---|
| Tracciamento segmenti | ✓ | ✓ | ✓ |
| Heartbeat | ✓ | ✓ | ✓ |
| sendBeacon | ✓ | ✓ | ✓ |
| Blocco seek | ✓ (polling) | ✓ (nativo) | ✓ (nativo) |
| Reazioni | ✓ | ✓ | ✓ |
| Note studente | ✓ | ✓ | ✓ |
| Resume automatico | ✓ | ✓ | ✓ |
| Limite velocità | ✓ | ✓ | ✓ |
| Sottotitoli | ✓ (YouTube CC) | ✓ (Vimeo tracks) | ✓ (VTT upload) |
| Transcript interattivo | ✗ | ✗ | ✓ |
| Capitoli VTT | ✗ | ✗ | ✓ |
| Poster pre-play | ✓ | ✓ | ✓ |
| Bottoni skip ⏪⏩ | ✓ | ✓ | ✓ |
| Download file | ✗ | ✗ | ✓ |


## 17. Servizi esterni e CDN

VideoTrack non include librerie di terze parti nel pacchetto. Per i video YouTube e Vimeo il browser dello studente carica le API ufficiali dai rispettivi provider a runtime. Gli amministratori devono valutare policy privacy, cookie e Content Security Policy dell'istituto. Quando il trasferimento verso provider terzi non è consentito, è consigliato usare file video caricati direttamente in Moodle.


### Note sicurezza validazione di integrità accademica 1.0.42

- Il fallback storico per note e reazioni è limitato dal setting `validationfallbackdays` (default 30 giorni). Impostare `0` significa usare qualsiasi segmento storico già guardato: è utile in ambienti con riprese frequenti da dispositivi diversi, ma rende la validazione validazione di integrità accademica più permissiva.
- Gli identificativi anonimi negativi sono pseudonimi tecnici salted e scoped per attività. La probabilità di collisione è trascurabile nel range intero usato; in caso di collisione teorica i dati restano comunque non collegati ad account Moodle reali.


### Storico aggiornamenti: 1.0.45
- Ripristinati i diacritici nelle stringhe multilingua relative al troncamento delle reazioni.
- Migliorato il contrasto del transcript attivo, dello slider volume e del pulsante velocità attivo in dark mode.
- Spostata la live region delle reazioni nel markup e reso configurabile via playerconfig l'intervallo minimo degli annunci di indisponibilità.
- Aggiunta validazione difensiva delle classi Font Awesome per le icone reazione.

### Storico aggiornamenti: 1.0.44

- Rigenerati i build AMD minificati.
- Migliorati gli annunci per tecnologie assistive al replay del video.
- Aggiunti colori basati su variabili CSS per barre di avanzamento in dark mode.
- Ripulite stringhe non più usate e documentato il limite degli eventi mostrati.
