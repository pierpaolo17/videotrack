# Indicatori di integrità e controlli focus

## Controlli

Per istanza il docente può abilitare registrazione indicatori, pausa per scheda/focus, prevenzione Picture-in-Picture best effort e pause casuali. Le impostazioni sito definiscono intervallo casuale (default 300–1800 secondi), politica focus e tolleranza. Tutti i controlli sono disabilitati per default nell’attività.

## Politica focus accessibile

La modalità consigliata mette in pausa quando `document.visibilityState` diventa hidden. Il blur avvia una tolleranza e può essere registrato, ma non mette in pausa salvo modalità rigida. Il ritorno del focus o l’interazione con iframe provider annulla l’azione pendente. Così screen reader, password manager, controlli browser e dialoghi OS non vengono trattati automaticamente come comportamento scorretto.

Quando è richiesta la modalità rigida, ogni corso contenente VideoTrack riceve un gruppo core nascosto e non partecipante con idnumber stabile `mod_videotrack_focus_exception`. L’appartenenza modifica soltanto la policy effettiva del blur rigido in `hiddenonly`: non consente playback con scheda nascosta e non aggira validazione server, seek, velocità, completion o regole delle interazioni. VideoTrack non registra la motivazione dell’appartenenza e legge direttamente la relazione gruppi core perché i normali helper visibility-aware nascondono intenzionalmente le membership non visibili.

## Credito di riproduzione server-authoritative

Il playback tracciato inizia soltanto dopo che `start_playback` apre una finestra di credito vincolata alla sessione browser corrente. Le scritture di segmenti provenienti da una sessione diversa o obsoleta restano disponibili come evidenza di audit non autorevole, ma non possono avanzare la copertura vista. Una pausa, fine o chiusura lifecycle per pagina nascosta accettata svuota sessione attiva e timestamp attività server, quindi la ripresa deve eseguire un nuovo handshake. Il contratto browser HTML5 deterministico fa coincidere gli identificatori di sessione `playstart` e `pause` accettato e verifica la chiusura della finestra server.

## Segnali

I tipi ammessi includono avanzamento bloccato, scheda nascosta, blur finestra, player fuori viewport, tentativo PiP, pausa casuale, velocità non autorizzata, callback provider mancante e tracking incoerente. Il server valida tipo, contesto, abilitazione e rate limit. Non accetta testo libero o acquisizioni del dispositivo.

## Interpretazione

Sono indicatori diagnostici, non misure dirette dell’attenzione. Limiti provider/browser e cause accessibili legittime possono produrre assenze o falsi positivi. Report e Analytics mostrano quindi conteggi/aggregati privacy-safe; non devono modificare automaticamente voto, completamento o disciplina.

## Ciclo di vita

`videotrack_integrity` è incluso in Privacy API, retention, reset, eliminazione attività e backup/restore con dati utente.
