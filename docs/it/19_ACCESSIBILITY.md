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
