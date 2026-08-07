# Proposta automatica della durata 1.6.27

## Scopo

Il form dell’attività prova ora a precompilare `durationseconds` usando i metadati disponibili per la sorgente selezionata. Questo riduce il lavoro manuale senza indebolire il modello di tracking autorevole lato server.

## Sorgenti supportate

- **YouTube:** il form valida l’URL HTTPS supportato e interroga la durata tramite un’istanza YouTube IFrame API fuori schermo e senza riproduzione.
- **Vimeo:** il form conserva l’eventuale hash di privacy e interroga il Vimeo Player SDK tramite un iframe fuori schermo, senza riproduzione e con Do Not Track.
- **File locale Moodle:** quando il file picker espone l’URL draft same-origin, un elemento HTML media fuori schermo legge i metadati senza avviare la riproduzione.

Il rilevamento è best effort. Contenuti privati o non incorporabili, restrizioni CSP/rete, policy del browser, metadati non supportati o un upload non ancora concluso possono impedire il risultato.

## Fiducia e persistenza

- Il detector opera soltanto nel form attività attendibile del docente.
- Scrive una proposta nel normale campo modificabile; non scrive direttamente nel database.
- Una modifica manuale del docente non viene mai sovrascritta da una risposta tardiva del detector.
- Il cambio di sorgente consente una nuova proposta per la nuova sorgente.
- Il valore diventa autorevole soltanto dopo l’invio del form e il salvataggio dell’attività da parte di Moodle.
- La durata del player learner resta non autorevole e non può sovrascrivere il campo salvato.
- `0` resta una scelta esplicita valida: disabilita percentuale vista, completamento dipendente dalla percentuale e presa visione a fine video, mantenendo il tracking degli intervalli validati.

## Accessibilità e privacy

Gli aggiornamenti di stato sono annunciati tramite una regione live non invasiva associata al campo durata. Le sonde dei provider non avviano la riproduzione, sono fuori schermo e nascoste alle tecnologie assistive. Inserire un URL di un provider esterno può contattare il provider per ottenere i metadati; continuano ad applicarsi gli avvisi già presenti per le sorgenti esterne.

## Trasporto della configurazione Moodle

Dalla 1.6.28 le impostazioni localizzate del detector sono serializzate in un elemento `application/json` nel DOM del form attività. Il bootstrap AMD riceve soltanto l’id di tale elemento e analizza il JSON prima dell’installazione. Questo segue le indicazioni Moodle per i payload estesi ed evita l’avviso developer generato quando gli argomenti di `js_call_amd()` superano 1024 caratteri.

## Contratto di build

Il sorgente canonico è `amd/src/form/duration.js`. `grunt amd --root=mod/videotrack` deve generare e distribuire `amd/build/form/duration.min.js` e la relativa source map.
