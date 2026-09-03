# VideoTrack per Moodle

VideoTrack è un modulo attività Moodle per video HTML5/caricati, YouTube e Vimeo. Integra progresso di
visione validato dal server, strumenti di studio, completamento personalizzato, gradebook e report attenti
alla privacy.

Release corrente documentata da questo albero: **1.7.120**. Rami Moodle supportati: **5.0–5.3**.

- Panoramica inglese: [`README.md`](README.md)
- Guida italiana completa: [`docs/it/funzionalita.md`](docs/it/funzionalita.md)
- Indice tecnico: [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md)
- Sintesi privacy: [`PRIVACY_IT.md`](PRIVACY_IT.md)
- Riferimento database/ER: [`docs/VIDEOTRACK_DB_ER_SCHEMA.md`](docs/VIDEOTRACK_DB_ER_SCHEMA.md)
- Cronologia release: [`CHANGELOG.md`](CHANGELOG.md)

## Mappa delle funzionalità

| Area | Funzionalità disponibili |
|---|---|
| Media | Video caricati/HTML5, YouTube e Vimeo; proposta durata nel form con equivalente `HH:MM:SS`; poster; download opzionale dei file caricati. |
| Player | Autoplay, loop, avvio muto, larghezza responsiva, controlli configurabili, policy tastiera/fullscreen, passi avanti/indietro. |
| Navigazione | Policy seek avanti/indietro indipendenti, resume attendibile, replay limitato dai report e recupero dai salti bloccati. |
| Velocità | Elenco velocità, modifica learner, limite massimo e velocità di fallback dopo un salto bloccato. |
| Evidenze viste | Sessioni autorizzate dal server, richieste segmento idempotenti, copertura di intervalli unici e stato derivato. |
| Testo temporizzato | Sottotitoli WebVTT, trascrizione ricercabile, capitoli e navigazione per timestamp. |
| Reazioni | Preset opt-in e icone emoji, testuali, Font Awesome o caricate; controllo configurazione, aggiunta/rimozione, deduplica e limiti raffica. |
| Strumenti di studio | Note personali temporizzate, segnalibri privati nominati, replay/export del proprietario e cronologie comprimibili. |
| Forum | Composer temporizzato opzionale collegato a un Forum compatibile dello stesso corso. |
| Presa visione | Dichiarazione learner versionata, conferma immediata/fine video e integrazione completion. |
| Completamento | Percentuale vista, reazioni minime/distinte/obbligatorie e presa visione con logica AND/OR. |
| Voti | Punti/scale Moodle, sufficienza, visualizzazione voto learner e condizioni core basate sul voto. |
| Report | Viste learner, attività, corso e docente; permessi aggregati/individuali; timeline e cluster reazioni. |
| Analytics | Analisi cross-corso dello stesso video, scope cohort/gruppi, finestre 7/30/90 giorni e soglia privacy. |
| Export | Report CSV, Excel e ODS, campi/delimitatore CSV, export individuali e CSV segnalibri del proprietario. |
| Integrità | Diagnostica opzionale visibilità/focus, Picture-in-Picture e pause casuali; guard server del playback. |
| Accessibilità | Tastiera, focus visibile, regioni di stato, layout responsivo/reflow, forced-colours e completion accessibile. |
| Privacy | Privacy API Moodle, export/cancellazione proprietario, cancellazione context/user-list, retention a eliminazione e conferma retention illimitata. |
| Ciclo di vita | Backup/restore con o senza dati utente, reset, cancellazione istanza, riparazione gradebook ed eventi Moodle. |
| Amministrazione | Default sito, policy player/completion imponibili, preset reazioni, otto lingue, validatore e benchmark Analytics in sola lettura. |
| Qualità | Contratti PHPUnit, Behat deterministico HTML5/YouTube/Vimeo e gate Moodle PHPCS/AMD canonici. |

La tabella è una mappa completa, non la specifica operativa. Comportamento, permessi, confini dati e limiti dei
provider sono descritti una sola volta nella [guida completa](docs/it/funzionalita.md) e nei riferimenti tecnici.

## Requisiti e installazione

- Moodle 5.0–5.3 e stack PHP/database supportato dal ramo Moodle scelto.
- Accesso di rete a YouTube/Vimeo quando si usano i provider pubblici.
- Task pianificati Moodle attivi quando è richiesta la pulizia per retention.

Installare dall'interfaccia Moodle oppure copiare l'unica directory `videotrack` in `mod/`, quindi aprire
**Amministrazione del sito → Notifiche** o eseguire l'upgrade CLI Moodle. Prima della diffusione verificare
default di sito, retention, campi identificativi dei report e policy focus.

Non modificare direttamente i file installati. Usare una release revisionata e svuotare le cache Moodle dopo
il deploy.

## Ruoli e privacy in sintesi

`mod/videotrack:participate` governa il tracking learner indipendentemente dai permessi di report. Docenti e
amministratori restano in anteprima non tracciata salvo assegnazione esplicita della capability. Permessi di
report/export aggregati e individuali sono separati per governare mascheramento privacy e dettaglio learner.

La diagnostica opzionale supporta l'analisi ma non dimostra l'intenzione del learner. Note personali ed etichette
dei segnalibri sono escluse dagli Analytics aggregati. Consultare [`PRIVACY_IT.md`](PRIVACY_IT.md) prima di
modificare retention o campi identificativi.

## Amministrazione e validazione

Dalla root Moodle:

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
```

Gli strumenti sono in sola lettura. Opzioni e interpretazione sono in
[`docs/it/21_CLI_DIAGNOSTICS.md`](docs/it/21_CLI_DIAGNOSTICS.md). I maintainer devono seguire
[`docs/it/07_BUILD_TEST_RELEASE.md`](docs/it/07_BUILD_TEST_RELEASE.md); quando cambia `amd/src`, build Moodle
Grunt e source map corrispondenti sono obbligatori.

## Licenza

GNU GPL v3 o successiva, coerentemente con Moodle.
