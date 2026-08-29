# Tracking, completion e gradebook

## Registro di riproduzione

`videotrack_seg` è un registro append-oriented delle richieste playback e del loro esito di validazione server.
Un `requestid` stabile rende i retry idempotenti per attività/utente. `sessionid` lega ogni richiesta a una finestra
di playback autorizzata. `servervalidated=1` indica che la riga può contribuire alle evidenze viste; le altre righe
restano soltanto diagnostiche.

Il budget server dipende da tempo server trascorso e velocità consentita. Gli intervalli richiesti sono limitati
ai confini validi e non consumano più credito del disponibile. Le cause terminali accettate chiudono la finestra.

## Stato derivato

`videotrack_state` conserva una riga per attività/utente con intervalli uniti in JSON, secondi unici, ultima
posizione attendibile, percentuale e cache completion. È derivato e ricostruibile dai segmenti validati conservati
e dalle regole correnti di interazione/presa visione.

La copertura è monotona nelle evidenze conservate: il replay non conta due volte e i salti avanti non riempiono
gap. Cambiare durata richiede ricalcolo perché la percentuale dipende dal denominatore salvato dal docente.

## Completion composita VideoTrack

I criteri abilitati possono includere percentuale vista, reazioni e presa visione corrente. `completionlogic` li
combina con `and` o `or`. Il plugin li espone come un'unica regola personalizzata perché una OR interna non venga
rappresentata erroneamente come più regole Moodle obbligatorie.

Provider custom completion e tracker usano la stessa configurazione/firma. Ogni mutazione che cambia un criterio
aggiorna lo stato derivato e sincronizza la completion Moodle soltanto al cambio effettivo di stato.

Le condizioni core Moodle come apertura attività, voto ricevuto e sufficienza restano separate. Il layout browser
può spostare l'etichetta AND/OR prima dell'elenco, senza cambiare stato o ordinamento.

## Gradebook

L'attività supporta nessun voto, punteggio massimo o scala Moodle. `gradepass` viene inviato al grade item e può
guidare la condizione core di sufficienza. `showgradeto` governa la visualizzazione nella pagina attività; i permessi
Moodle sui voti restano autorevoli.

Creazione/modifica/cancellazione e restore usano le API grade. La riparazione normalizza item legacy duplicati e
sposta i voti utente nell'item canonico conservato quando necessario. Un item canonico deve avere `itemnumber` uguale
a `0`, appartenere allo stesso corso dell'istanza VideoTrack e risolvere un solo course module VideoTrack.

La disinstallazione completa del plugin non richiama il normale callback di cancellazione per ogni istanza. L'hook
anticipato elimina quindi tramite Grade API i grade item validi prima che il core rimuova i contesti modulo. Solo i
record che non possono risolvere un contesto univoco passano dalla pulizia DML.

## Invarianti di verifica

- lo stesso request id non crea un secondo segmento logico;
- scritture obsolete/cross-session non avanzano la copertura;
- intervalli saltati non entrano in copertura o resume;
- cancellazione reazioni e nuova versione della presa visione aggiornano completion;
- refresh ripetuto senza transizione non duplica eventi/scritture completion;
- backup/restore ricostruisce lo stato e mantiene coerenza voti/completion.
- `gradebook_integrity` del validatore CLI non segnala contesti invalidi o item canonici duplicati prima dell'uninstall.
