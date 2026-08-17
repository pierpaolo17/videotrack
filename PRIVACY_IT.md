# VideoTrack: sintesi privacy e trattamento dei dati

Questo documento descrive il modello dati corrente di VideoTrack e i relativi confini operativi. Integra, ma non sostituisce, l’informativa privacy del sito, la valutazione della base giuridica e la politica di conservazione dell’organizzazione.

Versione inglese: [`PRIVACY.md`](PRIVACY.md).

## Dati memorizzati per funzione

Quando viene utilizzata la funzione corrispondente, VideoTrack può registrare:

- **Righe del registro di riproduzione:** utente, attività/corso, identificativi di sessione e idempotenza, provider/video, inizio/fine nel video, wall-clock diagnostico, velocità validata, motivo di chiusura, stato di validazione server e data. Le righe `playstart` a durata zero stabiliscono la finestra temporale server e non producono progresso visto.
- **Stato aggregato dello studente:** durata autorevole configurata dal docente, ultima posizione, intervalli visti compatti, secondi unici esatti e monotoni, stato di completamento, contatori cumulativi limitati del credito server, timestamp in millisecondi dell’ultimo handshake/richiesta e normali timestamp del record.
- **Reazioni:** chiave configurata, tempo del video, velocità, stato attivo/eliminato e timestamp.
- **Note personali:** proprietario, testo privato, tempo già visto, velocità, stato attivo/eliminato e timestamp.
- **Segnalibri privati:** proprietario, etichetta privata, tempo già visto, velocità, stato attivo/eliminato e timestamp.
- **Indicatori di integrità:** tipo di segnale limitato, tempo approssimativo, sessione/attività e data. Non vengono acquisiti webcam, microfono, biometria, screenshot, keylogging o contenuti di altre schede.
- **Prese visione:** proprietario, hash/versione della dichiarazione corrente, data di conferma e, per i record correnti, secondi unici e percentuale fotografati al momento della conferma.

Video sorgente, poster, sottotitoli, trascrizioni e capitoli caricati sono conservati nelle aree File API di Moodle. I post creati dal composer Forum opzionale appartengono a `mod_forum` e non vengono duplicati da VideoTrack.

## Limiti di visibilità

- Lo studente accede soltanto alle proprie note e alle proprie etichette dei segnalibri.
- I docenti autorizzati possono consultare/esportare il testo delle note personali quando la funzione è abilitata e lo scope iscrizione/gruppi lo permette; le etichette dei segnalibri restano visibili soltanto al proprietario.
- Il docente vede dati per studente solo con la capability di report e nel rispetto dei gruppi.
- Gli Analytics di istanza, la dashboard di corso e la dashboard docente trasversale usano aggregati e mostrano valori aggregati esatti ai report viewer autorizzati entro lo scope Moodle già consentito per attività/corso/gruppi. `analyticsminusers` non si applica a queste dashboard Analytics esatte e resta disponibile soltanto per altri riepiloghi aggregati del report.
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

Il task pianificato elimina definitivamente i record learner di proprietà del plugin che superano il periodo configurato. VideoTrack non mantiene pseudonimi deterministici con utente negativo né una chiave di mapping. Dopo la cancellazione, la riga derivata `videotrack_state` viene ricostruita dai segmenti validati e dagli input di completamento ancora conservati; il credito di riproduzione non più attivo viene azzerato e il completamento personalizzato Moodle viene sincronizzato. Progresso e completamento possono quindi diminuire quando scadono le evidenze che li sostenevano.

Il valore `0` disabilita la pulizia automatica per età e richiede una politica esplicita, documentata e riesaminata periodicamente. Le richieste approvate tramite Privacy API continuano a eliminare i record learner selezionati anche con valore `0`. I file condivisi dell’attività sono dati di configurazione e restano fino all’eliminazione dell’attività stessa.

Il sito dovrebbe scegliere il periodo più breve compatibile con lo scopo didattico e giuridico, comunicarlo agli studenti e controllare l’accesso alle esportazioni contenenti identità.

## Backup e ripristino

Il backup Moodle include configurazione e file dell’attività. Quando sono richiesti i dati utente, VideoTrack include soltanto record con utente positivo ancora compresi nella retention del sito sorgente; le righe derivate `videotrack_state` non vengono incluse. Il restore rimappa gli identificativi, scarta pseudonimi legacy e record già scaduti secondo il sito destinazione e ricostruisce lo stato dopo il ripristino della completion del modulo Moodle. Un completamento personalizzato ripristinato senza evidenze VideoTrack ancora valide viene riportato a incompleto. I contatori runtime del credito non vengono trasferiti come progresso attendibile. I backup con dati utente devono essere protetti come il database Moodle in esercizio.

## Esportazioni CSV e data format

I campi identificativi dei report individuali sono configurabili a livello sito/istanza e limitati ai dati visibili a chi esporta. Gli export con reazioni o commenti individuali richiedono conferma esplicita e generano eventi Moodle. Gli export CSV/Excel/ODS degli Analytics di istanza contengono gli stessi aggregati esatti mostrati al report viewer autorizzato. Il testo delle note personali e le etichette/timestamp privati dei segnalibri restano esclusi; le dashboard corso/docente mantengono la soglia minima corrente fino alla revisione dedicata.

## Memoria del browser

VideoTrack può usare memoria browser limitata alla sessione per stato temporaneo dell’interfaccia e della riproduzione. Non è destinata al tracciamento tra siti e non sostituisce autorizzazione o persistenza lato server.

I riferimenti implementativi dettagliati sono in `docs/it/02_ARCHITECTURE.md`, `06_RUNTIME_FLOWS.md`, `11_INTEGRITY_AND_FOCUS.md` e `12_ACKNOWLEDGEMENT.md`.
