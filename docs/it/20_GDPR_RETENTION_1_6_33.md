# Redesign GDPR e retention in VideoTrack 1.6.33

## Scopo

VideoTrack 1.6.33 sostituisce il precedente modello di pseudonimizzazione deterministica con una retention basata sulla cancellazione. I record granulari scaduti degli studenti vengono eliminati. La riga derivata `videotrack_state` viene quindi ricostruita soltanto dai dati personali ancora compresi nel periodo di conservazione configurato.

Questo modello allinea Privacy API Moodle, task pianificato, Analytics, completamento, backup e restore allo stesso confine dei dati.

## Confine dei dati

L'impostazione sito `retentionperioddays` si applica ai seguenti record learner di proprietà del plugin:

- richieste di riproduzione e segmenti validati dal server in `videotrack_seg`;
- reazioni, note personali, segnalibri privati e tombstone in `videotrack_reactev`;
- indicatori diagnostici di integrità in `videotrack_integrity`;
- prese visione versionate in `videotrack_acknowledge`.

`videotrack_state` è un dato derivato. Non viene conservato indipendentemente dai suoi input e non viene usato per mantenere cronologia scaduta.

Il valore `0` disabilita la cancellazione automatica per età. Le richieste approvate tramite Privacy API Moodle continuano comunque a eliminare i dati dell'utente selezionato.

## Task pianificato

Il task elabora al massimo 500 coppie utente/attività per esecuzione. Per ogni coppia:

1. acquisisce lo stesso lock canonico usato dal tracking;
2. elimina i record granulari precedenti al cutoff;
3. invalida la cache dei conteggi delle reazioni;
4. ricostruisce lo stato dai segmenti conservati con `servervalidated = 1` e dagli input di completamento ancora validi;
5. elimina lo stato quando nessun input conservato può contribuire a progresso o completamento;
6. azzera il credito server non più attivo, preservando soltanto un guard di riproduzione ancora entro una finestra heartbeat limitata;
7. sincronizza il completamento personalizzato Moodle con lo stato ricostruito.

Il campo `timecreated` dello stato viene impostato sul più antico record personale ancora conservato. In questo modo le esecuzioni successive possono continuare a far avanzare la finestra di retention anche se lo stato è stato ricalcolato di recente.

Il completamento può tornare incompleto quando scadono le evidenze richieste dalle regole configurate. È un comportamento intenzionale: il plugin non deve dichiarare evidenze che non conserva più.

## Rimozione degli pseudonimi legacy

Le versioni precedenti potevano sostituire l'utente reale con un id negativo deterministico e conservare un sale di sito. La 1.6.33 elimina:

- tutte le righe con utente negativo nelle cinque tabelle learner;
- la configurazione legacy `anonymisationsalt`;
- etichette report e stringhe lingua che presentavano tali righe come utenti anonimi.

Non viene mantenuta alcuna chiave di mapping né alcun record granulare pseudonimo.

## Cancellazione tramite Privacy API Moodle

La cancellazione approvata utente/contesto elimina definitivamente le righe VideoTrack dello studente. Non elimina i file condivisi di configurazione dell'attività, come video sorgente, poster, sottotitoli, trascrizioni, capitoli o icone delle reazioni. Questi file appartengono all'attività e vengono rimossi dal normale ciclo di eliminazione del modulo.

Moodle core resta responsabile delle operazioni privacy sul registro valutatore.

## Backup e restore

I backup con dati utente includono soltanto record con utente positivo e timestamp ancora compreso nella retention del sito sorgente. Le righe derivate `videotrack_state` non vengono inserite nel backup.

Il restore applica nuovamente la politica del sito destinazione e scarta:

- record legacy con utente negativo;
- record precedenti al cutoff del sito destinazione;
- riferimenti utente che non possono essere rimappati.

Dopo tutti gli step comuni Moodle dell'attività, compreso il ripristino del completamento del modulo, il task ricostruisce lo stato VideoTrack dai record conservati. Un completamento personalizzato ripristinato senza evidenze VideoTrack ancora valide viene riportato a incompleto. I contatori runtime del credito di riproduzione non vengono trasferiti come progresso attendibile.

## Concorrenza e gestione degli errori

Cancellazione e ricostruzione dello stato sono protette dal lock canonico per utente/attività. Se il lock non è disponibile, la coppia resta eleggibile per un'esecuzione successiva. La sincronizzazione del completamento avviene dopo il commit dei record del plugin; un errore della Completion API viene conteggiato e segnalato senza annullare la cancellazione privacy.

## Matrice di validazione

La validazione della release deve coprire:

- segmenti misti scaduti e conservati;
- reazioni, note, segnalibri, indicatori e prese visione scaduti e conservati;
- ricostruzione ed eliminazione dello stato;
- guard di riproduzione scaduto e ancora attivo;
- retention illimitata con rimozione degli pseudonimi legacy;
- cancellazione utente dopo la retention;
- backup/restore con dati utente e politiche finite o illimitate;
- ricalcolo del completamento dopo il restore;
- export e cancellazione Privacy API dopo il cleanup.

## Limiti operativi

VideoTrack non considera anonimo un aggregato che rimane collegato a un utente Moodle. Lo stato viene quindi trattato come dato personale ed è esposto alla Privacy API finché esiste. Il sito dovrebbe scegliere il periodo più breve giustificato, comunicarlo agli studenti e proteggere i file di backup come dati personali.
