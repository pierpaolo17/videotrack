# VideoTrack per Moodle

VideoTrack è un modulo attività Moodle per distribuire e tracciare video HTML5/caricati, YouTube e Vimeo. Integra analytics di visione attenti alla privacy, strumenti di studio opzionali, condizioni di completamento e report per il docente.

Release documentata da questo albero: **1.6.28**. Versioni Moodle supportate: **5.0–5.3**.

Panoramica inglese: [`README.md`](README.md)
Informativa privacy: [`PRIVACY_IT.md`](PRIVACY_IT.md) / [`PRIVACY.md`](PRIVACY.md)
Documentazione tecnica: [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md) / [`docs/en/00_INDEX.md`](docs/en/00_INDEX.md)

## Funzionalità principali

- Riproduzione di video HTML5/caricati, YouTube e Vimeo.
- Tracciamento dei segmenti visti validato dal server e calcolo del tempo unico effettivamente coperto.
- Ripresa della visione, regole di seek avanti/indietro, limiti di velocità e controlli da tastiera accessibili.
- Completamento basato su percentuale vista, reazioni richieste e presa visione corrente opzionale.
- Reazioni configurabili, note personali temporizzate e segnalibri privati.
- Trascrizioni WebVTT ricercabili e capitoli forniti dal docente.
- Composer Forum opzionale con collegamento temporale, pubblicato tramite il modulo Forum di Moodle.
- Controlli opzionali di focus/integrità: pausa quando la scheda è nascosta, prevenzione Picture-in-Picture best effort, pause casuali di attenzione e segnali diagnostici limitati.
- Dichiarazione di presa visione versionata, confermabile in qualsiasi momento oppure soltanto dopo l’ultimo secondo del video.
- Report per studente, dashboard di corso, analytics dello stesso video tra corsi ed esportazioni CSV/Excel/ODS.
- Integrazione gradebook, completamento personalizzato, eventi Moodle, backup/restore, Privacy API e retention pianificata.
- Otto language pack mantenuti: tedesco, inglese, spagnolo, francese, hindi, italiano, polacco e portoghese.

## Principi di privacy e accessibilità

VideoTrack registra soltanto i dati necessari alle funzioni abilitate. Le etichette dei segnalibri restano visibili esclusivamente al proprietario. Il testo delle note personali è visibile al proprietario e può essere consultato/esportato dai docenti autorizzati; il testo delle note è escluso dagli Analytics aggregati. Gli analytics docente sono aggregati e applicano la soglia minima configurata. Gli indicatori di integrità sono diagnostici, non costituiscono prova di comportamento scorretto e non devono essere l’unica base per voti o provvedimenti disciplinari.

La politica focus predefinita mette in pausa soltanto quando la pagina del video è realmente nascosta. La perdita di focus della finestra viene trattata con maggiore cautela per ridurre falsi positivi dovuti a screen reader, password manager, controlli del browser e finestre del sistema operativo. Controlli del player, regioni di stato, trascrizione e pulsante sul poster sono progettati per tastiera e tecnologie assistive. I limiti dei browser e dei provider esterni sono documentati senza promettere garanzie impossibili.

## Installazione

1. Posizionare la cartella in `mod/videotrack`.
2. Aprire **Amministrazione del sito → Notifiche** oppure eseguire l’upgrade Moodle da CLI.
3. Verificare le impostazioni sito di VideoTrack prima di abilitare retention, campi identificativi CSV o controlli focus.
4. Creare un’attività VideoTrack, scegliere la sorgente e abilitare solo le funzioni necessarie allo scenario didattico.

Non modificare direttamente i file installati nel server Moodle. Utilizzare release o patch revisionate e mantenere sempre sincronizzati `amd/src` e `amd/build`.

### Correzione del trasporto della configurazione durata nella 1.6.28

La release 1.6.28 mantiene l’intera configurazione localizzata del detector in un elemento JSON nel DOM del form attività. `js_call_amd()` riceve soltanto l’identificatore breve dell’elemento di configurazione, evitando l’avviso Moodle developer per payload superiori a 1024 caratteri. Il comportamento del detector, la modificabilità del valore e il contratto di persistenza autorevole lato server restano invariati.

### Proposta automatica della durata nella 1.6.27

Quando il docente inserisce un URL YouTube o Vimeo supportato oppure seleziona un file multimediale locale, VideoTrack prova a leggere la durata dai metadati resi disponibili dalla sorgente e precompila il campo. Il form comunica l’esito in modo accessibile e il valore proposto resta modificabile prima del salvataggio e nelle modifiche successive. Il rilevamento è best effort: limitazioni del provider, privacy del contenuto, policy del browser o metadati locali non disponibili possono impedirlo; in tal caso il docente può inserire il valore manualmente oppure lasciare `0`. Il valore rilevato diventa autorevole soltanto dopo il salvataggio dell’attività; i metadati del player learner non possono mai sovrascriverlo.

### Correzione affidabilità degli strumenti di visione nella 1.6.26

La release 1.6.26 mantiene i pulsanti delle reazioni disponibili sia durante la riproduzione sia durante la pausa, continuando a richiedere lato server un timestamp di visione validato. Note e segnalibri abilitati vengono mostrati in modo coerente; chi può consultare i report vede un’anteprima disabilitata e non può creare telemetria learner. L’intera sezione dei controlli HTML5 viene nascosta per le sorgenti YouTube e Vimeo. Il campo della durata verificata parte da `0`, che indica esplicitamente che percentuale vista, completamento percentuale e presa visione dopo l’ultimo secondo non vengono usati; gli intervalli di visione validati continuano comunque a essere raccolti.

### Correzione interfaccia e Analytics nella 1.6.25

La release 1.6.25 corregge il riepilogo delle prese visione negli Analytics, mantiene tutte le definizioni delle reazioni nella sezione principale **Reazioni**, ripristina i relativi pulsanti sopra la pubblicazione Forum e i segnalibri e conserva un insieme variabile di reazioni tramite il comando **Aggiungi reazione** (fino al limite di sicurezza già previsto di 30). Chi può consultare i report vede un’anteprima disabilitata dei controlli, mentre soltanto i learner canonici possono registrare reazioni.

### Recovery upgrade nella 1.6.24

La release 1.6.24 sostituisce il passaggio fallito della 1.6.23, che tentava di ricalcolare la completion, con un recovery idempotente basato solo sul database. Poiché il progetto non era mai stato usato in produzione, l’upgrade una tantum elimina i dati runtime learner esistenti e le righe di completamento dei moduli VideoTrack, preservando configurazione delle attività e reazioni configurate. Vedere [`docs/it/15_UPGRADE_RECOVERY_1_6_24.md`](docs/it/15_UPGRADE_RECOVERY_1_6_24.md).

## Baseline di validazione

L’albero distribuito è progettato per essere verificato con:

```bash
php admin/tool/phpunit/cli/init.php
vendor/bin/phpunit --testsuite mod_videotrack_testsuite
vendor/bin/phpcs --standard=moodle --extensions=php mod/videotrack
npx grunt amd --root=mod/videotrack
```

I comandi esatti dipendono dal checkout Moodle e dalle dipendenze di sviluppo installate. Consultare [`docs/it/07_BUILD_TEST_RELEASE.md`](docs/it/07_BUILD_TEST_RELEASE.md).

## Contratto di manutenzione

- Partire sempre dall’ultimo archivio reale del plugin.
- Ricostruire il flusso runtime effettivo prima di modificare player o Web Service.
- Trattare HTML5, YouTube e Vimeo come adapter distinti con contratto condiviso.
- Verificare PHP, XMLDB, placeholder delle lingue, sorgenti/build JavaScript, Privacy API, backup/restore e documentazione.
- Se cambia `amd/src/*`, eseguire la build AMD reale di Moodle e distribuire minificati e source map risultanti.
- Generare le patch dalla root del plugin e verificare sia `git apply --check` sia `patch -p1 --dry-run`.

La documentazione numerata è la fonte corrente. I file in `docs/*/archive/` sono esclusivamente storici.

## Licenza

GNU GPL v3 o successiva, coerentemente con Moodle.
