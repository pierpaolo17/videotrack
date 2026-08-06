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

## Disponibilità della conferma e fotografia dell’avanzamento (1.6.20)

La presa visione dispone di una sezione autonoma e chiusa nel form dell’attività. Il docente sceglie tra due politiche:

- **In qualsiasi momento**: mantiene il comportamento 1.6.19 e l’hash storico della dichiarazione, così le conferme esistenti restano valide dopo l’upgrade.
- **Solo dopo l’ultimo secondo del video**: il server accetta la conferma soltanto quando gli intervalli persistiti in `videotrack_state` o l’ultima posizione tracciata raggiungono l’ultimo secondo, con una tolleranza di un secondo sulla fine del media.

La checkbox e l’editor dell’avviso sulle reazioni appartengono alla sezione Reazioni e non sono più visualizzati insieme alla presa visione. Tutte le sezioni del form dell’istanza sono chiuse per impostazione predefinita, tranne **Sorgente video**.

Ogni nuova conferma registra una fotografia immutabile dell’avanzamento: `viewedseconds` contiene il tempo unico coperto e `viewedpercent` la relativa percentuale sulla durata effettiva al momento della conferma. I report HTML e CSV del docente mostrano stato, data, secondi visti e percentuale vista. La visione successiva non modifica la fotografia salvata.

Quando è richiesto il termine del video, il form è inizialmente disabilitato finché lo stato persistito non dimostra che la fine è stata raggiunta. Durante la stessa visita, i tre player emettono `videotrack:ended` solo dopo il salvataggio del segmento finale; `core/player/acknowledgement.js` abilita quindi i controlli. La validazione server-side resta autoritativa.
Le conferme create prima della 1.6.20 non dispongono di una fotografia storica del progresso; i report indicano il dato come non disponibile invece di dedurre zero o usare una visione successiva.
