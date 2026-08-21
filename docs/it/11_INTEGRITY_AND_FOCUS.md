# Indicatori di integrità e controlli focus

## Controlli

Per istanza il docente può abilitare registrazione indicatori, pausa per scheda/focus, prevenzione Picture-in-Picture best effort e pause casuali. Le impostazioni sito definiscono intervallo casuale (default 300–1800 secondi), politica focus e tolleranza. Tutti i controlli sono disabilitati per default nell’attività.

## Politica focus accessibile

La modalità consigliata mette in pausa quando `document.visibilityState` diventa hidden. Il blur avvia una tolleranza e può essere registrato, ma non mette in pausa salvo modalità rigida. Il ritorno del focus o l’interazione con iframe provider annulla l’azione pendente. Così screen reader, password manager, controlli browser e dialoghi OS non vengono trattati automaticamente come comportamento scorretto.

Quando è richiesta la modalità rigida, ogni corso contenente VideoTrack riceve un gruppo core nascosto e non partecipante con idnumber stabile `mod_videotrack_focus_exception`. L’appartenenza modifica soltanto la policy effettiva del blur rigido in `hiddenonly`: non consente playback con scheda nascosta e non aggira validazione server, seek, velocità, completion o regole delle interazioni. VideoTrack non registra la motivazione dell’appartenenza e legge direttamente la relazione gruppi core perché i normali helper visibility-aware nascondono intenzionalmente le membership non visibili.

## Segnali

I tipi ammessi includono avanzamento bloccato, scheda nascosta, blur finestra, player fuori viewport, tentativo PiP, pausa casuale, velocità non autorizzata, callback provider mancante e tracking incoerente. Il server valida tipo, contesto, abilitazione e rate limit. Non accetta testo libero o acquisizioni del dispositivo.

## Interpretazione

Sono indicatori diagnostici, non misure dirette dell’attenzione. Limiti provider/browser e cause accessibili legittime possono produrre assenze o falsi positivi. Report e Analytics mostrano quindi conteggi/aggregati privacy-safe; non devono modificare automaticamente voto, completamento o disciplina.

## Ciclo di vita

`videotrack_integrity` è incluso in Privacy API, retention, reset, eliminazione attività e backup/restore con dati utente.
