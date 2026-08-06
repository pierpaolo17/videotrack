# Audit della documentazione

Baseline documentata: Videotrack 1.6.16, verificata dallo ZIP reale 1.6.15 e aggiornata con analytics dei segnalibri, traduzioni e documentazione completa.

## Scopo

Questo documento registra come la documentazione è stata verificata rispetto al sorgente effettivo del plugin. Serve ai manutentori futuri per controllare che la documentazione numerata resti allineata al codice prima di fare modifiche funzionali.

## Ambito dell'audit

L'audit ha coperto:

- README principale;
- documentazione inglese e italiana in `docs/en` e `docs/it`;
- tutti i file non documentali inclusi nel plugin;
- classi, funzioni e metodi PHP;
- moduli sorgente AMD e artifact AMD generati;
- variabili PHP e JavaScript rilevate staticamente;
- flussi runtime di tracking, note, reazioni, replay, completamento e report;
- regole operative apprese durante il ciclo di sviluppo 1.4.x.

## Mappa documentale corrente

La fonte aggiornata è la documentazione numerata:

| File | Ruolo |
| --- | --- |
| `00_INDEX.md` | Punto di ingresso e navigazione. |
| `01_DEVELOPER_GUIDE.md` | Workflow, disciplina patch e checklist di validazione. |
| `02_ARCHITECTURE.md` | Architettura generale e responsabilità dei componenti. |
| `03_FILE_INVENTORY.md` | Inventario completo dei file del plugin. |
| `04_FUNCTION_INVENTORY.md` | Inventario delle funzioni PHP e AMD. |
| `05_VARIABLE_INVENTORY.md` | Variabili PHP e AMD rilevate staticamente. |
| `06_RUNTIME_FLOWS.md` | Flussi di tracking, reazioni, note, replay e player. |
| `07_BUILD_TEST_RELEASE.md` | Comandi di build, test e rilascio. |
| `08_LESSONS_LEARNED.md` | Regole di manutenzione apprese durante lo sviluppo recente. |
| `09_DOCUMENTATION_AUDIT.md` | Questo file di audit e checklist futura. |
| `10_BOOKMARKS_AND_ANALYTICS.md` | Contratto completo di modello, runtime, privacy e report dei segnalibri. |

I file storici di audit restano nella stessa cartella per tracciabilità, ma la documentazione numerata è la guida corrente per il maintainer.

## Esito della verifica

### Copertura file

`docs/en/03_FILE_INVENTORY.md` e `docs/it/03_FILE_INVENTORY.md` coprono tutti i file non documentali presenti nel pacchetto del plugin.

L'inventario include:

- entry point Moodle (`view.php`, `lib.php`, `locallib.php`, report, preset, settings);
- form e impostazioni;
- classi backup e restore;
- classi dei servizi AJAX esterni;
- classi evento;
- privacy e cleanup;
- definizioni DB e upgrade;
- language pack;
- sorgenti AMD;
- build AMD generati e sourcemap;
- test e file di supporto.

### Copertura funzioni PHP

`docs/en/04_FUNCTION_INVENTORY.md` e `docs/it/04_FUNCTION_INVENTORY.md` includono l'inventario di classi/funzioni PHP rilevato dal sorgente distribuito.

L'inventario include:

- callback del modulo Moodle;
- metodi del form;
- metodi delle API esterne;
- metodi tracker e privacy;
- metodi evento;
- metodi backup/restore;
- test PHPUnit.

### Copertura funzioni AMD

La sezione AMD di `04_FUNCTION_INVENTORY.md` elenca funzioni e helper locali rilevati per modulo sorgente. È particolarmente importante per:

- `amd/src/html5_player.js`;
- `amd/src/player.js`;
- `amd/src/vimeo_player.js`;
- moduli condivisi `amd/src/core/*`.

I manutentori devono considerare i file AMD sorgente come fonte modificabile e `amd/build/*.min.js` più `.map` come artifact generati, salvo patch intenzionali che correggono un artifact distribuito.

### Copertura variabili

`docs/en/05_VARIABLE_INVENTORY.md` e `docs/it/05_VARIABLE_INVENTORY.md` includono le variabili PHP e AMD rilevate staticamente. L'inventario variabili è una mappa di navigazione, non un contratto comportamentale.

Serve per trovare rapidamente probabili variabili di stato; poi il comportamento deve essere verificato nel flusso runtime e nel sorgente.

## Note sulla qualità della documentazione

La documentazione è ora sufficientemente dettagliata per permettere a un nuovo manutentore di individuare le responsabilità principali dei file, seguire i flussi runtime e comprendere il workflow di validazione.

Restano però tre regole importanti:

1. gli inventari derivano da analisi statica e devono essere aggiornati quando cambiano file/funzioni/variabili;
2. il comportamento runtime, soprattutto dei player AMD, deve essere validato manualmente nel browser;
3. i contratti dei web service devono essere verificati sia in `execute_returns()` sia nelle risposte JSON reali.

## Processo richiesto per futuri aggiornamenti documentali

Quando una patch futura modifica PHP, JavaScript, strutture DB o comportamento runtime:

1. aggiornare il documento numerato pertinente sia in inglese sia in italiano;
2. aggiornare inventari file/funzioni/variabili se sono stati aggiunti o rimossi simboli;
3. aggiornare la documentazione dei flussi runtime se cambia il comportamento;
4. aggiornare le lezioni apprese se emerge una nuova regola di manutenzione;
5. mantenere i file storici, salvo esplicita sostituzione;
6. incrementare la versione plugin solo se la patch documentale deve essere installata come nuova release.

## Checklist rapida per il maintainer

Prima di rilasciare una patch documentale, verificare:

```text
- README descrive lo stato corrente del plugin.
- docs/en e docs/it sono entrambi aggiornati.
- 03_FILE_INVENTORY.md contiene ogni file non documentale distribuito.
- 04_FUNCTION_INVENTORY.md contiene funzioni PHP e AMD aggiunte/rimosse.
- 05_VARIABLE_INVENTORY.md contiene variabili importanti aggiunte/rimosse.
- 06_RUNTIME_FLOWS.md riflette eventuali cambi di comportamento runtime.
- 07_BUILD_TEST_RELEASE.md riflette i comandi correnti.
- 08_LESSONS_LEARNED.md contiene eventuali nuove regole scoperte.
- git apply --check passa dalla root del plugin.
- patch -p1 --dry-run passa dalla root del plugin.
```

## Esito audit 1.6.16

L'implementazione dei segnalibri è ora coperta dagli inventari di architettura, file, funzioni, variabili e flussi runtime e dal documento dedicato `10_BOOKMARKS_AND_ANALYTICS.md`. La parità delle chiavi è completa negli otto language pack; le stringhe recenti di segnalibri e dashboard docente non ricadono più sull'inglese nei sei pacchetti che contenevano placeholder.

## Esito audit 1.6.19

La presa visione opzionale è coperta da architettura, inventari di file/funzioni/variabili, flusso runtime, checklist di regressione e documento dedicato `12_ACKNOWLEDGEMENT.md`. La documentazione descrive regola dell’hash corrente, conferma POST esplicita, completamento, output docente, Analytics ed esportazione nei formati dati, Privacy API, retention e limiti di backup/restore.
