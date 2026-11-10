# Indicatori di integrità e controlli focus

## Controlli

Per istanza il docente può abilitare registrazione indicatori, pausa per scheda/focus, prevenzione Picture-in-Picture best effort e pause casuali. Le impostazioni sito definiscono i limiti di riserva con durata non definita (default 300–1800 secondi), politica focus e tolleranza. Default dell’attività e limiti docente sono descritti sotto. Tutti i controlli sono disabilitati per default nell’attività.

## Politica focus accessibile

La modalità consigliata mette in pausa quando `document.visibilityState` diventa hidden. Il blur avvia una tolleranza e può essere registrato, ma non mette in pausa salvo modalità rigida. Il ritorno del focus o l’interazione con iframe provider annulla l’azione pendente. Così screen reader, password manager, controlli browser e dialoghi OS non vengono trattati automaticamente come comportamento scorretto.

Quando è richiesta la modalità rigida, ogni corso contenente VideoTrack riceve un gruppo core nascosto e non partecipante con idnumber stabile `mod_videotrack_focus_exception`. L’appartenenza modifica soltanto la policy effettiva del blur rigido in `hiddenonly`: non consente playback con scheda nascosta e non aggira validazione server, seek, velocità, completion o regole delle interazioni. VideoTrack non registra la motivazione dell’appartenenza e legge direttamente la relazione gruppi core perché i normali helper visibility-aware nascondono intenzionalmente le membership non visibili.

## Credito di riproduzione server-authoritative

Il playback tracciato inizia soltanto dopo che `start_playback` apre una finestra di credito vincolata alla sessione browser corrente. Le scritture di segmenti provenienti da una sessione diversa o obsoleta restano disponibili come evidenza di audit non autorevole, ma non possono avanzare la copertura vista. Una pausa, fine o chiusura lifecycle per pagina nascosta accettata svuota sessione attiva e timestamp attività server, quindi la ripresa deve eseguire un nuovo handshake. Il contratto browser HTML5 deterministico fa coincidere gli identificatori di sessione `playstart` e `pause` accettato e verifica la chiusura della finestra server.

## Segnali

I tipi ammessi includono avanzamento bloccato, scheda nascosta, blur finestra, player fuori viewport, tentativo PiP, pausa casuale, velocità non autorizzata, callback provider mancante e tracking incoerente. Il server valida tipo, contesto, abilitazione e rate limit. Non accetta testo libero o acquisizioni del dispositivo.

## Interpretazione

Sono indicatori diagnostici, non misure dirette dell’attenzione. Limiti provider/browser e cause accessibili legittime possono produrre assenze o falsi positivi. Report e Analytics mostrano quindi conteggi/aggregati privacy-safe; non devono modificare automaticamente voto, completamento o disciplina.

## Stati di presentazione nel report

Il controller seleziona un contratto di presentazione esplicito prima di renderizzare la sezione integrità:

- `report_integrity_view::integrity_summary()` mostra i conteggi privacy-safe quando la registrazione è attiva;
- `report_integrity_view::integrity_disabled_summary()` spiega che registrazione e controlli focus sono disattivati;
- `report_integrity_view::integrity_controls_without_recording_summary()` avvisa che i controlli focus sono attivi ma non
  vengono registrati indicatori di integrità.

Questi entry point modificano soltanto la presentazione. Non abilitano la registrazione, non deducono comportamenti
scorretti e non aggirano la soglia privacy per utenti distinti. Helper privati condivisi mantengono identici titolo,
testo esplicativo e landmark accessibile della sezione in ogni stato.

## Ciclo di vita

`videotrack_integrity` è incluso in Privacy API, retention, reset, eliminazione attività e backup/restore con dati utente.

## Intervalli di attenzione scelti dal docente

L’interruttore **Abilita pause casuali di attenzione** resta disattivato per default. L’attività riusa le etichette
minimo/massimo del sito, ma i due campi facoltativi sono espressi in **secondi interi**, non minuti. Vuoto o `0`
significa automatico. I limiti espliciti devono essere tra **60 e 7200 secondi** e, se entrambi specificati,
rispettare minimo ≤ massimo. Valori uguali producono un intervallo fisso. Spegnere il controllo conserva i valori.

I valori automatici sono un terzo e due terzi di `durationseconds` salvato, arrotondati al secondo più vicino
e limitati a 60–7200. Con durata `0` o durata importata non valida si usano i limiti canonici del sito come
riserva. Un’attività da 900 secondi usa quindi 300–600 secondi; una da 30 secondi usa 60–60 e può terminare prima
della pausa. La durata usata per la percentuale vista non viene mai modificata per regolare questa funzione.

I due valori sono indipendenti. Se il solo minimo esplicito supera il massimo automatico, quest’ultimo viene
alzato al minimo esplicito. Se il solo massimo esplicito è inferiore al minimo automatico, quest’ultimo viene
abbassato al massimo esplicito. Modificare la durata ricalcola solo i limiti automatici: nel database resta `0`,
non una fotografia dell’intervallo calcolato. Il docente può riportare ciascun campo in automatico.

Si tratta di **tempo reale dall’ultima interazione riconosciuta durante la riproduzione**, non di posizioni a
1/3 e 2/3 della barra video. Il `focus_guard` condiviso, invariato, riavvia il timer sulle azioni riconosciute e
lo annulla su pausa/fine/distruzione. I soli callback di avanzamento non lo azzerano. La registrazione degli
indicatori è indipendente dall’abilitazione della pausa. La pausa del provider deve chiudere normalmente il
credito server; la ripresa richiede un nuovo handshake. Nessun tempo viene attribuito alla pausa; seek,
velocità, completamento e controllo scheda nascosta conservano i propri contratti. Le pause non provano
direttamente attenzione o comportamento scorretto.

## Registrazione e visibilità dei report

Il controllo focus registra un segnale solo se è abilitata la registrazione degli indicatori. I controlli
playback possono operare senza registrazione. Un blur prolungato mette in pausa solo se l’attività abilita
la pausa per focus e il sito sceglie la modalità rigida. Visibilità/mascheratura seguono capability e perimetro
studenti/gruppi effettivi: chi ha soltanto accesso aggregato conserva la soglia privacy; chi ha accesso ai
report individuali può vedere valori esatti nel perimetro autorizzato. I segnali restano contestuali e non
sono prova di comportamento scorretto.

## Descrizioni sito e interruttori indipendenti dell’attività

I limiti pausa del sito sono valori di riserva se la durata salvata dell’attività non è positiva. Una durata
positiva fornisce i limiti automatici, salvo override del docente nell’attività. Le impostazioni focus/
tolleranza del sito non abilitano controlli o registrazione nell’attività. Il segnale è registrato solo con
registrazione abilitata; un blur prolungato può mettere in pausa solo con policy rigida e controllo focus
attivo nell’attività. Anche la pausa per scheda nascosta richiede quel controllo. Le eccezioni focus già
previste restano valide. I testi sito descrivono questi interruttori esistenti; default e comportamento non cambiano.
