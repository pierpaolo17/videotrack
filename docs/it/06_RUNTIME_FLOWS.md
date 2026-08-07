# Flussi runtime

## Apertura attività e avvio player

`view.php` carica l’attività, verifica `mod/videotrack:view`, legge lo stato dell’utente e i file del docente, risolve policy sito/istanza e scrive una configurazione JSON. Inizializza poi un solo adapter: HTML5, YouTube o Vimeo. I moduli condivisi collegano stato, progresso, barra intervalli, reazioni, note, segnalibri, trascrizione, capitoli, forum e controlli focus.

## Tracciamento segmenti

Le callback provider aggiornano un tracker condiviso. Contribuisce soltanto la riproduzione attiva. Il client chiude segmenti limitati e chiama `mod_videotrack_save_segment`. Il servizio valida contesto, capability, sessione e movimento; `local\tracker` salva il segmento, unisce gli intervalli in `videotrack_state`, ricalcola secondi/percentuale, aggiorna completamento e voto ed emette gli eventi. Il lifecycle esegue flush su pausa, fine, cambio visibilità e unload tramite AJAX o beacon limitato.

## Seek, resume e replay

Il seek utente rispetta le policy avanti/indietro. Un avanzamento bloccato torna all’ultimo punto consentito e può usare la velocità di recupero. Resume e replay da report sono seek programmatici distinti per evitare falsi indicatori. Trascrizione, capitoli, note e segnalibri usano la stessa policy dell’adapter.

## Reazioni, note e segnalibri

Le definizioni delle reazioni sono configurate in un’unica sezione richiudibile **Reazioni**: il form mostra almeno quattro righe modificabili, mantiene una riga libera dopo le definizioni attive esistenti e può essere esteso con **Aggiungi reazione** fino al limite già previsto di 30. Nella pagina dell’attività i controlli delle reazioni precedono la pubblicazione Forum e i segnalibri. I learner canonici possono attivarli sia durante la riproduzione sia durante la pausa; il server accetta una reazione soltanto a un timestamp già coperto da dati di visione validati. Chi può consultare i report riceve un’anteprima visibile ma disabilitata e non può salvare telemetria learner. Note e segnalibri sono mostrati quando abilitati, richiedono un timestamp già visto dal proprietario e restano disabilitati nell’anteprima staff. I servizi verificano abilitazione, scope learner, proprietà e lunghezze massime. I record eliminati mantengono una tombstone limitata fino a retention/cancellazione; il testo privato non entra negli aggregati docente.

## Testo temporizzato

Il WebVTT caricato è analizzato da `local\timed_text`. I sottotitoli nativi YouTube/Vimeo restano nel provider e non sono ricercabili da VideoTrack. Trascrizioni e capitoli sono risorse File API separate. Lingua, ricerca, cue attiva e navigazione capitoli sono gestite dal modulo condiviso.

## Focus e integrità

`focus_guard` applica i controlli abilitati. La pausa su documento nascosto è il default accessibile. Il blur finestra è ritardato e mette in pausa solo in modalità rigida. Le pause casuali usano limiti amministrativi e si azzerano dopo azioni studente/player. Gli eventi ammessi sono salvati da `save_integrity_event`, con validazione e rate limit server.

## Presa visione

La versione è l’hash di testo/formato/modalità correnti. Il form POST usa `sesskey`. In modalità “fine video”, i controlli si abilitano dopo il salvataggio dell’ultimo segmento e il server verifica autonomamente la posizione persistita. La conferma salva hash, data e fotografia immutabile del progresso, poi aggiorna completamento/stato. Cambiare dichiarazione o modalità richiede una nuova conferma.

## Report ed export

I report per studente possono includere identità, progresso, reazioni, data/fotografia della presa visione e conteggi diagnostici secondo capability. Le dashboard aggregano attività accessibili. Gli Analytics di istanza costruiscono bin privacy-safe e riepiloghi separati per reazioni, segnalibri, integrità e prese visione. CSV/Excel/ODS mantengono le mascherature; gli export individuali con dati personali richiedono conferma.

## Privacy, retention e cancellazione

L’export Privacy elabora ogni famiglia in blocchi limitati. Cancellazione utente/contesto usa `privacy_manager`; eliminazione attività e reset rimuovono i record corrispondenti. Il task applica retention/anonimizzazione. Il backup include tabelle utente solo con user data e il restore rimappa gli identificativi.
