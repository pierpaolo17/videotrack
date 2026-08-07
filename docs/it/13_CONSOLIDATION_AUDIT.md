# Audit di consolidamento — VideoTrack 1.6.23

## Scopo

La release consolida tutto il lavoro prima dell’ultimo punto della roadmap. Non aggiunge una funzione didattica: allinea implementazione, test, privacy, accessibilità, traduzioni e documentazione.

## Difetti corretti

- Test export Analytics aggiornato da 12 allo schema reale di 15 colonne e fixture completata con i conteggi attività mancanti.
- Rimossi tre errori PHPCS dovuti alla riga vuota prima della chiusura classe.
- Preservate le impostazioni transcript e capitoli durante la normalizzazione dei sottotitoli; YouTube e Vimeo non perdono più i controlli timed-text dedicati.
- Rimossa un’assegnazione duplicata della larghezza player e aggiornati commenti obsoleti su reset/eliminazione.
- Azzerato il buffer segmenti Privacy API dopo ogni blocco da 500, evitando duplicazioni e crescita inutile.
- Aggiunto il conteggio esplicito delle prese visione scadute eliminate dalla retention e allineate le etichette di reset a tutte le famiglie di dati realmente cancellate.
- Centralizzato il reset per studente del report tramite il callback condiviso di eliminazione.
- Rimossi `aria-hidden` e semantica presentazionale dall’overlay poster per esporre il pulsante Play alle tecnologie assistive.
- Aggiornati commenti XMLDB fuorvianti per timed text e focus.
- Rimossa la source map orfana `amd/build/core/tracker/tracker.min.js.map`, ripristinando la parità uno-a-uno sorgente/build/map.
- Sostituito il README root misto/cronologico con panoramiche inglese e italiana correnti.
- Sostituito il documento privacy misto/vecchio con sintesi inglese e italiana correnti.
- Tradotti i testi operativi ancora copiati dall’inglese in tedesco, spagnolo, francese, hindi, italiano, polacco e portoghese, preservando i placeholder.
- Rigenerati inventari file/funzioni/dati e isolati i documenti storici in `archive/`.

## Evidenze baseline

Nella 1.6.21 il Grunt AMD del maintainer era completato, ma PHPUnit aveva un failure (12 colonne attese contro 15 reali) e PHPCS tre errori fixable. Sono trattati come difetti di release e corretti qui.

## Limite di validazione

Sulla baseline 1.6.22 Grunt, PHPCS e PHPUnit lato maintainer sono stati completati con esito positivo prima del ciclo di hardening 1.6.23. Browser, upgrade DB reale e backup/restore restano validazioni runtime e non vengono dichiarati superati da questo audit statico.
