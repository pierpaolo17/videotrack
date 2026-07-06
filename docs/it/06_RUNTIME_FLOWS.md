# Flussi runtime

## Tracking segmenti

```text
player event -> core/tracker -> save_segment AJAX -> classes/external/save_segment -> tracker::save_segment -> videotrack_seg + videotrack_state
```

Il backend fonde gli intervalli, calcola secondi unici e percentuale, e non deve aumentare la copertura quando lo studente rivede una porzione già coperta.

## Reazioni

```text
reaction button -> player reaction handler -> save_reaction AJAX -> classes/external/save_reaction -> videotrack_reactev -> table refresh
```

Regole importanti:

- una sola reazione per lo stesso secondo video;
- stessa reazione troppo vicina viene ignorata senza errore;
- la riga UI immediata deve usare i dati server-side quando disponibili.

## Note

```text
note form -> save_note AJAX -> classes/external/save_note -> videotrack_reactev with notetype
```

## Replay frammento

```text
button.videotrack-replay -> shared replay handler -> player-specific seek/play implementation
```

Il replay è comune come evento UI, ma il comportamento del seek è specifico per HTML5, YouTube e Vimeo.

## Vimeo

Vimeo SDK usa promesse asincrone per `setCurrentTime()`, `play()` e `pause()`. Non concatenare chiamate `play()` aggressive dopo un seek: può generare `PlayInterrupted`. Ogni modifica a Vimeo va testata manualmente su rewind, forward dentro il visto, forward oltre limite e replay.
