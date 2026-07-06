# Lezioni apprese

## Patch e baseline

La baseline è sempre lo ZIP reale caricato dal maintainer. Non assumere che una patch precedente sia stata applicata come previsto.

## Contratti AJAX

Ogni campo dichiarato in `execute_returns()` deve essere sempre restituito. Errori `invalidresponse` possono comparire anche quando il database è già stato modificato.

## Runtime player

HTML5, YouTube e Vimeo condividono parti del rendering, ma il seek/play/pause è specifico. Vimeo richiede particolare cautela per le promesse interrotte (`PlayInterrupted`).

## Rendering immediato delle reazioni

La tabella “Le mie reazioni” deve essere aggiornata usando i dati ritornati dal server o, in fallback, i `data-*` del bottone reazione. L’HTML dopo refresh è il riferimento per il DOM corretto.

## Controlli anti-duplicazione

Il controllo deve stare server-side. La UI può rimuovere righe ottimistiche quando il server restituisce `reactioneventid = 0`, ma non deve essere l’unica difesa.
