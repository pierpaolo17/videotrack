# Recovery upgrade 1.6.24

VideoTrack 1.6.24 corregge il fallimento dell’upgrade database introdotto dal primo pacchetto 1.6.23.

## Modalità del fallimento

L’upgrade 1.6.23 originario aggiungeva i campi del tracking autorevole lato server e poi richiamava API runtime di corso, modulo corso e completion prima di raggiungere il savepoint del plugin. Un errore in quella fase lasciava invariata la versione del plugin, quindi Moodle rieseguiva lo stesso blocco al tentativo successivo.

## Percorso corretto

- Il blocco 1.6.23 ora esegue soltanto le modifiche di schema e resta idempotente grazie ai controlli `field_exists()`.
- Il blocco 1.6.24 usa esclusivamente API database e può essere eseguito in sicurezza dopo un tentativo 1.6.23 completato solo parzialmente.
- Vengono eliminate le righe runtime learner da segmenti, stato aggregato, interazioni, indicatori di integrità e prese visione.
- Vengono eliminate le righe `course_modules_completion` relative ai moduli VideoTrack, così un completamento obsoleto non sopravvive al reset pulito.
- Restano conservate le istanze delle attività, le impostazioni, i file caricati e le definizioni delle reazioni configurate.
- `db/upgrade.php` non richiama API runtime di corso, modulo corso o completion.

## Politica dati

Il reset distruttivo una tantum è intenzionale perché VideoTrack non era mai stato usato in produzione. In questo modo viene stabilita una baseline pulita per il modello di tracking autorevole lato server. Dopo l’upgrade il nuovo progresso learner viene raccolto normalmente.

## Casi di recovery supportati

Il passaggio supporta sia:

1. l’upgrade diretto dalla 1.6.22 alla 1.6.24;
2. il recupero dopo un upgrade 1.6.23 interrotto dopo la creazione parziale o completa dei nuovi campi ma prima del savepoint.
