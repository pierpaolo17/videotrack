# Provider multimediali e durata

VideoTrack supporta media caricati/HTML5, YouTube e Vimeo tramite adapter distinti. Il core browser coordina il
comportamento condiviso; il timing specifico degli SDK resta dentro ogni adapter.

## Selezione della sorgente

| Sorgente | Identificatori salvati | Dipendenza runtime | Proposta durata |
|---|---|---|---|
| Upload | File area Moodle e identificatore normalizzato | HTMLMediaElement browser | `loadedmetadata` del file locale selezionato. |
| YouTube | URL e video id estratto | YouTube IFrame API | Probe nascosto e muto interroga `getDuration()` fino a metadata o timeout. |
| Vimeo | URL e id numerico estratto | Vimeo Player SDK | Promise `getDuration()` entro il timeout condiviso. |

Il detector opera soltanto nel form docente. Non scrive dati di tracking learner, non rende i metadata provider
autorevoli da soli e distrugge player temporanei/timer dopo successo, errore o timeout.

La configurazione localizzata è serializzata in un elemento DOM `application/json`. Il bootstrap AMD riceve solo
l'id dell'elemento, analizza il JSON e installa listener per sorgente. Si evitano argomenti `js_call_amd()` troppo
grandi e si mantengono testi/URL codificati in sicurezza.

## Durata autorevole

`durationseconds` salvato è il denominatore della percentuale vista e della completion. Il docente deve verificare
la proposta. L'inserimento manuale è necessario se privacy/consenso del provider, rete, media privato/eliminato o
metadati browser impediscono il rilevamento. Salvare `0` disabilita esplicitamente il calcolo percentuale. Lo stato
live adiacente converte il valore corrente in `HH:MM:SS`; è informativo e non introduce un secondo dato salvato.

## Contratto degli adapter

Ogni adapter espone play/pause, tempo corrente, durata, seek, velocità ed eventi lifecycle necessari al tracker.
Resume e replay cercano la posizione solo dopo la readiness. Il recupero da salto avanti bloccato cattura la
posizione pre-seek e non deve ripetere il seek durante i retry di play.

HTML5 offre il maggior controllo locale. YouTube e Vimeo restano soggetti a SDK, policy iframe e disponibilità del
servizio pubblico. Le promise dei loader si azzerano dopo un errore perché un tentativo successivo possa recuperare.

## Verifica

- testare parsing URL/id e input non validi/privati;
- testare metadata durata immediati e ritardati;
- verificare probe muti, limitati e distrutti;
- testare per ogni provider resume, seek indietro, recupero salto bloccato, velocità e pausa terminale;
- confrontare `amd/src` con build/map e fare smoke pubblico quando cambiano le policy esterne.
