# Persistenza del segmento per i segnalibri — 1.6.30

## Errore runtime corretto

Il contratto AMD condiviso dei segmenti usava già `bookmark` come motivo valido di chiusura per un’interazione. La whitelist PHP in `classes/external/helper.php` non conteneva però quel valore. Di conseguenza, il comando **Salva segnalibro** tentava di persistere l’intervallo visto corrente con `endreason = bookmark`, il Web Service dei segmenti lo rifiutava come parametro non valido e il player, intenzionalmente, assorbiva l’errore del salvataggio progressivo in background. La successiva richiesta del segnalibro falliva quindi con `error:playbackpositionnotwatched`, perché il punto corrente non era stato persistito.

## Contratto ripristinato

VideoTrack 1.6.30 aggiunge `bookmark` alla whitelist server e documenta che l’elenco PHP deve restare allineato a `SAVE_REASONS` in `amd/src/core/segment.js`. Nessuna protezione sulla posizione vista viene rimossa. Il Web Service dei segnalibri continua a richiedere che il timestamp selezionato appartenga a dati di visione validati dal server.

## Copertura di regressione

`tests/save_bookmark_test.php` verifica ora sia la dichiarazione dei parametri esterni Moodle sia l’accettazione del motivo segmento `bookmark`. In questo modo un futuro disallineamento tra motivo client e whitelist server viene intercettato automaticamente.
