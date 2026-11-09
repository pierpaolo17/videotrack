# Accessibilità

VideoTrack mira a WCAG 2.2 AA nelle parti dell'interfaccia controllate dal plugin. Gli iframe dei provider esterni
restano in parte governati da YouTube/Vimeo e vanno valutati nel contesto di deploy.

## Tastiera e focus

- tutte le azioni usano pulsanti, link, input, summary nativi o semantica equivalente da tastiera;
- gli indicatori di focus restano visibili in modalità normale e forced-colours;
- apertura/chiusura delle regioni interattive conserva una destinazione focus logica;
- i controlli player personalizzati hanno nomi accessibili e non dipendono solo dal colore;
- cue trascrizione, capitoli, reazioni, note, bookmark e presa visione sono operabili da tastiera.

La policy focus predefinita mette in pausa soltanto quando il documento è nascosto. Il blur rigido della finestra
visibile è opzionale e dispone di un gruppo corso di eccezione per split view o tecnologie assistive.

## Struttura e stati

Le intestazioni seguono la gerarchia della pagina. Le cronologie personali usano `<details>/<summary>` nativi.
Progresso e messaggi transitori usano regioni status/live senza spostare inaspettatamente il focus. L'enhancement
della completion conserva la semantica list/listitem Moodle e sposta soltanto l'etichetta logica VideoTrack prima
degli elenchi con più elementi.

## Reflow, zoom e contrasto

Controlli player e item completion vanno a capo/in colonna senza imporre overflow orizzontale. L'interfaccia deve
restare utilizzabile al 400% e con viewport da 320 CSS pixel. Le regole forced-colours mantengono focus, confini e
stato dei controlli. Contrasto testo/icone va controllato in Boost e nei temi personalizzati supportati.

## Media temporizzati

Il docente può fornire sottotitoli WebVTT, trascrizioni ricercabili e capitoli. VideoTrack non genera sottotitoli
né ne garantisce l'accuratezza: l'autore deve fornire testi sincronizzati e descrizioni previste dalla propria policy.

## Checklist audit manuale

Testare come learner e docente sulle versioni Moodle 5.0–5.3 in cui cambia il markup:

1. ordine Tab/Shift+Tab, attivazione Invio/Spazio e assenza di keyboard trap;
2. focus visibile e non occultato al 100% e 400%;
3. reflow a 320 CSS px senza scrolling bidimensionale per il contenuto ordinario;
4. visibilità controlli in Windows High Contrast/forced-colours;
5. nomi, stati, annunci e raggruppamento completion con screen reader;
6. controlli e testo temporizzato HTML5, YouTube e Vimeo;
7. errori, timing presa visione e ripristino focus dopo le azioni.

I test automatici proteggono la struttura ma non sostituiscono percezione e tecnologie assistive.

## Protocollo evidenze manuali U-020

**Stato: APERTO / NON ESEGUITO.** L’accettazione automatica U-007 è nell’[audit uscita browser](26_USCITA_BROWSER.md).
Questo protocollo prepara osservazioni reali: non contiene esiti manuali completati né certificazione accessibilità.

### Registrare l’ambiente provato

Usare un corso dedicato e account di test. Annotare release/versione plugin, SHA-256 pacchetto o commit,
versioni Moodle/PHP, tema/versione, sistema operativo, browser/versione, screen reader/versione, data ed
esecutore. Registrare provider, timed text, policy tastiera/seek, policy focus/gruppo eccezione, interruttore/
limiti attenzione, modalità presa visione e regole completamento. Usare i normali controlli sito/player.

Coprire viste studente e docente su Moodle 5.0 e 5.3, aggiungendo il tema di deploy se personalizzato.
Esercitare i tre provider configurati e registrare separatamente iframe/consensi esterni bloccati. Per la
percezione serve almeno una combinazione screen reader/browser reale. Le altre combinazioni restano non
provate finché non registrate; non dedurle dai sei ambienti CI Chrome.

### Matrice di osservazione

Ripetere le righe applicabili per ogni ambiente registrato. Usare PASS, FAIL, BLOCCATO o N/A con motivo ed
evidenza. Lo stato iniziale di tutte le righe sotto è **NON ESEGUITO**.

| Id | Azione su VideoTrack | Osservazione attesa | Stato iniziale |
|---|---|---|---|
| M01 | Percorrere player, timed text, interazioni e report con Tab/Shift+Tab; attivare con Invio/Spazio | Ordine logico, nomi significativi, una sola azione prevista e nessuna trappola tastiera | NON ESEGUITO |
| M02 | Aggiungere/rimuovere nota o bookmark, aprire/chiudere storici ed entrare/uscire dal composer Forum | Focus identificabile e ritorno a un controllo sensato; stato salvato ed errori reperibili | NON ESEGUITO |
| M03 | Ascoltare screen reader su play/pausa, completamento, errori form e avvisi transitori/persistenti | Nomi, ruoli e stati coerenti; cambi di stato pertinenti annunciati senza forzare il focus | NON ESEGUITO |
| M04 | Verificare contenuti ordinari a zoom 100%/400% e larghezza 320 pixel CSS | Testo/form a capo senza controlli persi o normale scrolling su due assi; tabelle realmente bidimensionali possono avere area di scroll propria usabile | NON ESEGUITO |
| M05 | Attivare alto contrasto/colori forzati del sistema operativo | Focus, bordi, stati pulsanti e testo distinguibili e usabili | NON ESEGUITO |
| M06 | Usare controlli HTML5 e iframe pubblici YouTube/Vimeo configurati, sottotitoli, trascrizione e capitoli | Entrata/uscita da tastiera; testi forniti coerenti col contenuto; descrizioni contenuto mancanti e restrizioni provider annotate separatamente | NON ESEGUITO |
| M07 | Provare presa visione libera/solo fine e intervallo attenzione abilitato 60–60 secondi | Stati disabilitati/disponibili e motivi comprensibili; messaggio pausa percepibile e ripresa manuale operabile | NON ESEGUITO |
| M08 | Confrontare studente, solo docente e doppio ruolo; usare report gruppo consentito e selettore studente | Stati di sola lettura/abilitati e selezione/dati comprensibili con tastiera e screen reader | NON ESEGUITO |

Per M07 impostare i due campi attenzione facoltativi dell’attività a 60 secondi, abilitare l’interruttore
esistente ed evitare interazioni riconosciute durante l’intero minuto di riproduzione. Usare durata/velocità
che non faccia terminare prima il video. Ripristinare la configurazione precedente dopo la prova. La pausa
non dimostra attenzione o comportamento scorretto. La presa visione solo a fine richiede visione reale
validata dell’ultimo secondo in questo corso manuale.

L’eccezione reflow per contenuti realmente bidimensionali non giustifica testo tagliato nelle singole celle
o controlli inaccessibili. Il controllo stato registra la percezione nella combinazione assistiva reale:
soli ruoli DOM o Behat verde non bastano. Riferimento normativo: [WCAG 2.2](https://www.w3.org/TR/WCAG22/).
Spiegazioni operative: [Reflow](https://www.w3.org/WAI/WCAG22/Understanding/reflow.html) e
[Messaggi di stato](https://www.w3.org/WAI/WCAG22/Understanding/status-messages.html).

### Verbale e decisione

Copiare una riga per ciascun risultato osservato:

| Pacchetto/commit | Moodle/tema | OS/browser/screen reader | Provider/ruolo | Caso | Esito | Osservazione reale / passi riproducibili | Riferimento evidenza |
|---|---|---|---|---|---|---|---|
| Da registrare | Da registrare | Da registrare | Da registrare | M01–M08 | NON ESEGUITO | Da registrare | Da registrare |

Per un finding annotare comportamento atteso/effettivo, impatto, configurazione esatta e riproduzione minima.
Indicare se appartiene al plugin, Moodle/tema, contenuto o provider esterno; conservare i confini non risolti.
L’audit resta APERTO se una riga applicabile è NON ESEGUITA/BLOCCATA o resta un finding materiale irrisolto.
Motivare scope/N/A e conclusione dell’esecutore. Chiudere U-020 solo da osservazioni reali revisionate.
Un audit interamente verde richiede il verbale, non una release funzionale automatica. Un finding concreto
richiede correzione mirata e relativi nuovi controlli statici/build/browser/manuali.
