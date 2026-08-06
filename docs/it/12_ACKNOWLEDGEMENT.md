# Presa visione opzionale dello studente

VideoTrack 1.6.19 introduce una dichiarazione opzionale di presa visione per corsi orientati alla compliance. La funzione è disattivata per impostazione predefinita e non pretende di verificare la comprensione.

## Configurazione

Il docente abilita la dichiarazione nelle impostazioni dell'attività e inserisce un testo formattato. Una condizione di completamento separata può richiedere la conferma della dichiarazione corrente.

La modifica del testo o del formato produce un nuovo hash della dichiarazione. Le conferme precedenti restano nello storico, ma non soddisfano più la versione corrente né la relativa condizione di completamento.

## Flusso dello studente

La dichiarazione compare dopo l'interfaccia del video. Lo studente deve selezionare una casella esplicita e inviare il modulo. VideoTrack registra:

- identificativi di attività, modulo e utente;
- hash SHA-256 non reversibile della dichiarazione;
- data di modifica dell'attività al momento della conferma;
- data e ora della conferma.

Il testo completo non viene duplicato nella tabella delle conferme.

## Completamento, report ed esportazione

Quando usata come condizione di completamento, la dichiarazione corrente deve essere confermata. I report docente e il CSV standard mostrano stato e data della conferma corrente. La Privacy API di Moodle esporta lo storico delle conferme dell'utente.

## Privacy e ciclo di vita

La tabella `videotrack_acknowledge` entra nel backup/restore solo quando sono inclusi i dati utente. Cancellazione utente, eliminazione attività, reset del corso e richieste Privacy API eliminano le conferme. La retention cancella le conferme scadute invece di pseudonimizzarle.
