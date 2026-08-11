# Flussi runtime

## Apertura attività e avvio player

`view.php` carica l’attività, verifica `mod/videotrack:view`, legge lo stato dell’utente e i file del docente, risolve policy sito/istanza e scrive una configurazione JSON. Inizializza poi un solo adapter: HTML5, YouTube o Vimeo. I moduli condivisi collegano stato, progresso, barra intervalli, reazioni, note, segnalibri, trascrizione, capitoli, forum e controlli focus.

## Tracciamento segmenti

Le callback provider aggiornano un tracker condiviso. Un evento PLAY chiama prima `mod_videotrack_start_playback`, che stabilisce un timestamp server senza assegnare tempo visto. Dopo il successo dell’handshake, la riproduzione attiva apre un segmento limitato. Ogni segmento riceve un identificativo generato prima dei retry di trasporto; `mod_videotrack_save_segment` riusa un risultato identico già persistito e rifiuta il riutilizzo dello stesso identificativo con dati differenti. Il servizio valida contesto, `mod/videotrack:participate`, velocità consentita, frontiera di seek e credito cumulativo basato sul tempo server. `local\tracker` salva la richiesta grezza, unisce gli intervalli validati in `videotrack_state`, mantiene la copertura unica esatta e monotona oltre la rappresentazione compatta di 500 intervalli, ricalcola percentuale/completamento ed emette gli eventi una sola volta. Il lifecycle esegue flush su pausa, fine, cambio visibilità e unload tramite AJAX o beacon limitato.

## Seek, resume e replay

Il seek utente rispetta le policy avanti/indietro. Prima di ogni seek accettato o bloccato, l’adapter salva soltanto il segmento realmente riprodotto fino alla posizione pre-seek affidabile; il tratto saltato non viene mai accreditato come visione continua. Un avanzamento bloccato torna all’ultimo punto consentito e può usare la velocità di recupero. Durante il rollback, Forum, reazioni, note e segnalibri usano l’ultimo timestamp affidabile invece della posizione provider transitoria e vietata. Resume e replay da report sono seek programmatici distinti per evitare falsi indicatori. Trascrizione, capitoli, note e segnalibri usano la stessa policy dell’adapter.

## Reazioni, note e segnalibri

Le definizioni delle reazioni sono configurate in un’unica sezione richiudibile **Reazioni**: il form mostra almeno quattro righe modificabili, mantiene una riga libera dopo le definizioni attive esistenti e può essere esteso con **Aggiungi reazione** fino al limite già previsto di 30. Nella pagina dell’attività i controlli delle reazioni precedono la pubblicazione Forum e i segnalibri. I learner canonici possono attivarli sia durante la riproduzione sia durante la pausa; il server accetta una reazione soltanto a un timestamp già coperto da dati di visione validati. Chi può consultare i report riceve un’anteprima visibile ma disabilitata e non può salvare telemetria learner. Note e segnalibri sono mostrati quando abilitati, richiedono un timestamp già visto dal proprietario e restano disabilitati nell’anteprima staff. I servizi verificano abilitazione, scope learner, proprietà e lunghezze massime. I record eliminati mantengono una tombstone limitata fino a retention/cancellazione; il testo privato non entra negli aggregati docente.

## Testo temporizzato

Il WebVTT caricato è analizzato da `local\timed_text`. I sottotitoli nativi YouTube/Vimeo restano nel provider e non sono ricercabili da VideoTrack. Trascrizioni e capitoli sono risorse File API separate. Lingua, ricerca, cue attiva e navigazione capitoli sono gestite dal modulo condiviso.

## Focus e integrità

`focus_guard` applica i controlli abilitati. La pausa su documento nascosto è il default accessibile. Il blur finestra è ritardato e mette in pausa solo in modalità rigida. Le pause casuali usano limiti amministrativi e si azzerano dopo azioni studente/player. Gli eventi ammessi sono salvati da `save_integrity_event`, con validazione e rate limit server.

## Presa visione

La versione è l’hash di testo/formato/modalità correnti. Il form POST usa `sesskey`. In modalità “fine video”, i controlli si abilitano dopo il salvataggio dell’ultimo segmento e il server verifica autonomamente la posizione persistita. La conferma salva hash, data e fotografia immutabile del progresso, poi aggiorna completamento/stato. Cambiare dichiarazione o modalità richiede una nuova conferma.

## Report ed export

I report per studente includono soltanto utenti con `mod/videotrack:participate` e possono mostrare identità, progresso, reazioni, data/fotografia della presa visione e conteggi diagnostici secondo capability. Le dashboard aggregano attività accessibili. Gli Analytics di istanza costruiscono bin privacy-safe e riepiloghi separati per reazioni, segnalibri, integrità e prese visione. CSV/Excel/ODS mantengono le mascherature; gli export individuali con dati personali richiedono conferma.

## Privacy, retention e cancellazione

L’export Privacy elabora ogni famiglia in blocchi limitati. La cancellazione utente/contesto usa `privacy_manager`; eliminazione attività e reset rimuovono i record corrispondenti. Il task elimina le righe granulari scadute, ricostruisce `videotrack_state` dalle evidenze attendibili ancora conservate, azzera il credito di riproduzione non più attivo e sincronizza il completamento personalizzato. Non viene mantenuta alcuna copia pseudonima deterministica. Il backup con dati utente include soltanto righe con utente positivo comprese nella retention del sito sorgente e omette lo stato derivato. Il restore applica la retention del sito destinazione, rimappa gli utenti e ricostruisce lo stato dopo il ripristino del completamento del modulo Moodle.

## Proposta della durata nel form attività

1. Il docente seleziona YouTube, Vimeo o un caricamento locale e indica la sorgente.
2. `mod_videotrack/form/duration` valida la sorgente ed esegue un rilevamento best effort dei metadati: YouTube IFrame API, Vimeo Player SDK oppure metadati HTML media same-origin del file draft Moodle.
3. I secondi proposti vengono scritti soltanto nel campo modificabile del form docente e annunciati tramite una regione live non invasiva. Una modifica manuale viene preservata; il cambio di sorgente avvia una nuova proposta.
4. `videotrack_add_instance()` o `videotrack_update_instance()` salva il valore revisionato nel form. Solo questo valore salvato è autorevole per percentuale, completamento e presa visione vincolata alla fine. I metadati del player learner non possono aggiornarlo.
5. Quando i metadati non sono disponibili, il campo resta manuale e `0` mantiene disabilitate le funzioni dipendenti dalla percentuale.
