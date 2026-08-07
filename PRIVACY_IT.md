# VideoTrack: sintesi privacy e trattamento dei dati

Questo documento descrive il modello dati corrente di VideoTrack e i relativi confini operativi. Integra, ma non sostituisce, l’informativa privacy del sito, la valutazione della base giuridica e la politica di conservazione dell’organizzazione.

Versione inglese: [`PRIVACY.md`](PRIVACY.md).

## Dati memorizzati per funzione

Quando viene utilizzata la funzione corrispondente, VideoTrack può registrare:

- **Segmenti di visione:** utente, attività/corso, sessione, provider/video, inizio/fine nel video, inizio/fine temporale reale, velocità e data di creazione.
- **Stato aggregato dello studente:** durata autorevole configurata dal docente, ultima posizione, intervalli visti uniti, secondi unici, stato di completamento, contatori/timestamp limitati del guard server e normali timestamp del record.
- **Reazioni:** chiave configurata, tempo del video, velocità, stato attivo/eliminato e timestamp.
- **Note personali:** proprietario, testo privato, tempo già visto, velocità, stato attivo/eliminato e timestamp.
- **Segnalibri privati:** proprietario, etichetta privata, tempo già visto, velocità, stato attivo/eliminato e timestamp.
- **Indicatori di integrità:** tipo di segnale limitato, tempo approssimativo, sessione/attività e data. Non vengono acquisiti webcam, microfono, biometria, screenshot, keylogging o contenuti di altre schede.
- **Prese visione:** proprietario, hash/versione della dichiarazione corrente, data di conferma e, per i record correnti, secondi unici e percentuale fotografati al momento della conferma.

Video sorgente, poster, sottotitoli, trascrizioni e capitoli caricati sono conservati nelle aree File API di Moodle. I post creati dal composer Forum opzionale appartengono a `mod_forum` e non vengono duplicati da VideoTrack.

## Limiti di visibilità

- Lo studente accede soltanto alle proprie note e alle proprie etichette dei segnalibri.
- Il docente vede dati per studente solo con la capability di report e nel rispetto dei gruppi.
- Gli Analytics sono aggregati e applicano `analyticsminusers` separatamente alle popolazioni di visione, reazioni, segnalibri, indicatori e prese visione quando previsto.
- Il testo delle note e le etichette dei segnalibri non compaiono mai negli Analytics docente.
- Gli Analytics tra corsi includono solo attività per cui il docente possiede `mod/videotrack:viewreport`; le regole dei gruppi vengono risolte separatamente in ogni corso.
- Gli indicatori di integrità sono diagnostici: non sono una prova conclusiva e non devono determinare da soli voti, completamento, sanzioni o accesso.

## Accessibilità e controlli focus

La politica predefinita mette in pausa quando il documento diventa nascosto. La perdita di focus della finestra viene registrata solo dopo una tolleranza configurabile e causa pausa esclusivamente se l’amministratore sceglie la modalità rigida. Questa distinzione riduce falsi positivi dovuti a tecnologie assistive, controlli del browser, password manager e finestre del sistema operativo.

La prevenzione Picture-in-Picture è best effort. HTML5 offre controlli più forti rispetto agli iframe YouTube/Vimeo e browser o estensioni possono ignorare alcune policy. Questi limiti devono essere considerati nell’interpretazione degli indicatori.

## Esportazione, cancellazione e reset

Il provider Privacy API di Moodle:

- dichiara tutte le tabelle VideoTrack e i collegamenti ai provider esterni;
- individua i contesti contenenti dati dell’utente;
- esporta visioni, stato, reazioni, note, segnalibri, indicatori e prese visione in blocchi limitati;
- elimina i dati per richieste approvate utente/contesto;
- supporta la cancellazione di elenchi di utenti in un contesto.

Eliminazione dell’attività, reset corso/attività e reset del progresso studente rimuovono i record di competenza del plugin. I dati nel registro valutatore sono gestiti dal provider privacy del gradebook Moodle.

## Conservazione

Il task pianificato applica il periodo configurato ai record utente del plugin e ai contenuti identificativi associati. Il valore `0` disabilita la pulizia automatica per età e richiede quindi una politica esplicita, documentata e riesaminata periodicamente. Le richieste privacy e la cancellazione del contesto continuano a rimuovere i dati anche con valore `0`.

Il sito dovrebbe scegliere il periodo più breve compatibile con lo scopo didattico e giuridico, comunicarlo agli studenti e controllare l’accesso alle esportazioni contenenti identità.

## Backup e ripristino

Il backup Moodle include configurazione e file dell’attività. I record degli utenti sono inclusi soltanto quando viene richiesto il backup dei dati utente. Il restore rimappa attività, modulo e utenti. I backup con dati utente devono essere protetti come il database Moodle in esercizio.

## Esportazioni CSV e data format

I campi identificativi dei report individuali sono configurabili a livello sito/istanza e limitati ai dati visibili a chi esporta. Gli export con reazioni o commenti individuali richiedono conferma esplicita e generano eventi Moodle. Gli export Analytics CSV/Excel/ODS contengono righe aggregate privacy-safe; i valori mascherati restano mascherati in ogni formato.

## Memoria del browser

VideoTrack può usare memoria browser limitata alla sessione per stato temporaneo dell’interfaccia e della riproduzione. Non è destinata al tracciamento tra siti e non sostituisce autorizzazione o persistenza lato server.

I riferimenti implementativi dettagliati sono in `docs/it/02_ARCHITECTURE.md`, `06_RUNTIME_FLOWS.md`, `11_INTEGRITY_AND_FOCUS.md` e `12_ACKNOWLEDGEMENT.md`.
