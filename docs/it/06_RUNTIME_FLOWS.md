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

Il replay è comune come evento UI, ma il comportamento del seek è specifico per HTML5, YouTube e Vimeo. Il target iniziale deve essere il timestamp esatto della reazione; i limiti start/end restano metadati del frammento. I link diretti dal report (`replaystart`/`replayend`) devono avere precedenza sul resume salvato in tutti e tre i player e devono richiedere l’avvio della riproduzione.

## Vimeo

Vimeo SDK usa promesse asincrone per `setCurrentTime()`, `play()` e `pause()`. Non concatenare chiamate `play()` aggressive dopo un seek: può generare `PlayInterrupted`. Ogni modifica a Vimeo va testata manualmente su rewind, forward dentro il visto, forward oltre limite e replay.

## Flusso di esportazione CSV (1.4.266)

Il valore predefinito di sito definisce il separatore CSV e i campi facoltativi di corso, attività e utente. Ogni attività può ereditare o sovrascrivere queste scelte. I separatori disponibili sono virgola, punto e virgola, `§`, `#` e `|`. Il docente vede nella configurazione dell'attività le stesse opzioni standard dell'amministratore; i campi identificativi non autorizzati dal core Moodle restano non esportabili. Durante l'esportazione, `classes/local/csv_export.php` filtra i campi utente configurati applicando le regole Moodle sulla visibilità, carica i campi standard e quelli personalizzati visibili tramite `core_user\fields`, aggiunge facoltativamente il link al video, protegge dalle formule nei fogli di calcolo e scrive UTF-8 con BOM per i programmi di foglio elettronico.

La scheda **Esportazione CSV** consente di scegliere un singolo studente o tutti gli studenti, reazioni e/o note, e formato dettagliato o sintetico. Il formato dettagliato produce una riga per evento; quello sintetico raggruppa le reazioni per studente e tipo usando la finestra cluster dell'attività, mantenendo le note come righe individuali. La conferma sui dati personali non assegna permessi: documenta una scelta consapevole e viene verificata anche lato server prima del download.

## Ricalcolo del completamento (1.4.264)

L'azione di ricalcolo ricostruisce lo stato aggregato di ogni utente dai record grezzi `videotrack_seg`, invece di rivalutare soltanto il valore booleano di completamento. Unisce gli intervalli visti, ricalcola secondi unici e percentuale, ripristina la posizione dell'ultimo segmento grezzo, rivaluta i requisiti sulle reazioni e sincronizza il completamento Moodle solo quando sono configurate regole di completamento personalizzate di VideoTrack e lo stato cambia. Il completamento basato sulla sola visualizzazione resta gestito dal core Moodle.
