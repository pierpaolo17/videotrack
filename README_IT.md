# VideoTrack per Moodle

VideoTrack è un modulo attività Moodle per distribuire e tracciare video HTML5/caricati, YouTube e Vimeo. Combina progresso di visione validato dal server con strumenti di studio opzionali, regole di completamento, integrazione gradebook e report docente attenti alla privacy.

Release corrente documentata da questo albero: **1.7.98**. Rami Moodle dichiarati come supportati dal plugin: **5.0–5.3**.

Panoramica inglese: [`README.md`](README.md)
Cronologia release: [`CHANGELOG.md`](CHANGELOG.md)
Sintesi privacy: [`PRIVACY_IT.md`](PRIVACY_IT.md) / [`PRIVACY.md`](PRIVACY.md)
Documentazione tecnica: [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md) / [`docs/en/00_INDEX.md`](docs/en/00_INDEX.md)

## Funzionalità principali

- Riproduzione di video HTML5/caricati, YouTube e Vimeo.
- Tracciamento dei segmenti visti validato dal server e calcolo del tempo unico coperto.
- Ripresa della visione, regole di seek avanti/indietro, limiti di velocità e controlli da tastiera accessibili.
- Completamento basato su percentuale vista, reazioni richieste e presa visione opzionale.
- Reazioni configurabili, note personali temporizzate e segnalibri privati.
- Trascrizioni WebVTT ricercabili e capitoli forniti dal docente.
- Composer Forum opzionale con timestamp, pubblicato tramite il modulo Forum di Moodle.
- Controlli opzionali di focus/integrità con segnali diagnostici limitati.
- Report per studente, dashboard di corso, analytics dello stesso video tra corsi ed esportazioni CSV/Excel/ODS.
- Integrazione gradebook, completamento personalizzato, eventi Moodle, backup/restore, Privacy API e retention pianificata.
- Otto language pack mantenuti: tedesco, inglese, spagnolo, francese, hindi, italiano, polacco e portoghese.
- Validazione CLI locale e benchmark Course Analytics in sola lettura per amministratori e maintainer.

## Partecipazione, privacy e accessibilità

La partecipazione learner è controllata da `mod/videotrack:participate` ed è indipendente dall'accesso ai report. Un utente con ruolo doppio o personalizzato può quindi restare un learner tracciato pur possedendo capability di report. Docenti, manager e amministratori restano in anteprima non tracciata salvo assegnazione anche della capability di partecipazione.

VideoTrack registra soltanto i dati necessari alle funzioni abilitate. Le etichette dei segnalibri restano private al proprietario. I report di attività separano visualizzazione aggregata, visualizzazione individuale, export aggregato ed export individuale. Chi possiede soltanto l’accesso aggregato non può filtrare per studente e mantiene la soglia minima configurata; chi possiede l’accesso individuale vede aggregati esatti entro il proprio scope Moodle. Il testo delle note personali è escluso dagli Analytics aggregati e gli indicatori di integrità restano segnali diagnostici, non prove di comportamento scorretto.

La politica focus predefinita mette in pausa soltanto quando la pagina del video è realmente nascosta. Controlli del player, regioni di stato, navigazione della trascrizione e azioni sul poster sono progettati per tastiera e tecnologie assistive. I limiti di browser e provider esterni sono documentati come comportamento best effort, non come garanzie.

## Installazione

1. Posizionare la cartella in `mod/videotrack`.
2. Aprire **Amministrazione del sito → Notifiche** oppure eseguire l'upgrade Moodle da CLI.
3. Verificare le impostazioni sito di VideoTrack prima di abilitare retention, campi identificativi CSV o controlli focus.
4. Creare un'attività VideoTrack e abilitare soltanto le funzioni necessarie allo scenario didattico.

Non modificare direttamente i sorgenti installati. Utilizzare release o patch revisionate e mantenere sincronizzati `amd/src` e `amd/build`.

## Diagnostica CLI locale

VideoTrack distribuisce strumenti CLI in sola lettura per amministratori e maintainer. Dalla root Moodle, usare `php mod/videotrack/cli/validate.php --json` per la diagnostica installazione/release e `php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>` per il benchmark Course Analytics. Opzioni e interpretazione complete sono documentate in [`docs/it/21_CLI_DIAGNOSTICS.md`](docs/it/21_CLI_DIAGNOSTICS.md).

## Baseline di validazione

Ogni release deve essere verificata con la toolchain Moodle disponibile per il ramo target, includendo PHPUnit, Moodle Coding Style/Extra e generazione AMD quando cambiano i sorgenti JavaScript. I comandi tipici sono documentati in [`docs/it/07_BUILD_TEST_RELEASE.md`](docs/it/07_BUILD_TEST_RELEASE.md).

Regole principali di release:

- partire dall'ultimo archivio reale del plugin;
- ricostruire il percorso runtime effettivo prima di modificare player o Web Service;
- trattare HTML5, YouTube e Vimeo come adapter distinti dietro un contratto condiviso;
- validare PHP, XML, placeholder delle lingue, Privacy API, backup/restore e documentazione;
- se cambia `amd/src/*`, eseguire il vero `grunt amd` Moodle e distribuire minificati e source map generati;
- verificare le patch sia con `git apply --check` sia con `patch -p1 --dry-run`.

## Licenza

GNU GPL v3 o successiva, coerentemente con Moodle.

Reazioni personali, note e segnalibri learner sono presentati in sezioni native collassabili compatte; l’automazione browser inizia in `tests/behat/`.
