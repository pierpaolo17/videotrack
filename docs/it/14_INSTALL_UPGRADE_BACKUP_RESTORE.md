# Installazione, upgrade, backup e restore

## Installazione pulita

Installare l'unica directory `videotrack/` sotto `mod/` oppure usare l'installer plugin Moodle. Moodle legge
`db/install.xml`, capability, servizi, eventi e task pianificati, quindi registra la versione del plugin.

Dopo l'installazione:

1. dalla root Moodle eseguire `php mod/videotrack/cli/validate.php --json`;
2. confermare 7 tabelle, 149 campi, 22 indici espliciti, 22 foreign key/indici generati, 9 servizi AJAX,
   8 lingue e 48 sorgenti AMD;
3. verificare impostazioni retention, privacy, prestazioni, player, focus ed export;
4. svuotare le cache e creare un'attività di prova per ogni sorgente usata dal sito.

## Upgrade

`db/upgrade.php` è una sequenza ordinata e riprendibile. Ogni operazione schema verifica lo stato corrente quando
può essere ritentata e termina con `upgrade_mod_savepoint()`. Il codice di upgrade usa operazioni database/DDL
adatte alla fase; assunzioni runtime su course module, completion o browser non appartengono alle migrazioni.

Non modificare soltanto `db/install.xml` per un plugin già rilasciato. Le installazioni nuove usano `install.xml`;
i siti esistenti richiedono uno step di upgrade esplicito e un `$plugin->version` maggiore.

La release 1.7.119 aggiunge i metadata delle relazioni stabili tramite `add_key()`. Moodle crea o riusa un indice
esatto per ogni chiave, ma non crea foreign key fisiche né cascade nel database. L'upgrade è idempotente a livello
di indici e non elimina o riscrive dati. Al termine va eseguito il validatore con `--strict`: ogni orfano o incoerenza
corso/course module è un failure di qualità dati da indagare, mai una riparazione automatica. I campi condizionali
`linkedforumid` e `reactionid` vengono controllati separatamente solo quando contengono un riferimento.

## Contenuto del backup

Senza dati utente il backup include configurazione attività, reazioni definite e file gestiti da Moodle. Con dati
utente include inoltre segmenti conservabili, eventi reazione/nota/bookmark, integrità e prese visione. La tabella
derivata `videotrack_state` non viene intenzionalmente salvata.

Tutte le definizioni di reazione, comprese quelle soft-deleted richiamate da eventi storici, entrano nel backup per
rimappare coerentemente gli identificatori. I record utente precedenti al cutoff di retention sono esclusi.

## Comportamento del restore

Il restore crea l'attività, rimappa Forum opzionale, reazioni e utenti, ripristina file ed eventi granulari
conservabili, quindi ricostruisce stato derivato e input di completion. Se il Forum non è disponibile, la funzione
viene disabilitata con warning. Una reazione non rimappabile genera difensivamente un placeholder nascosto invece
di trasformare tacitamente l'evento in sentinella nota/bookmark.

La riparazione gradebook avviene dopo il restore core dei voti per normalizzare item duplicati o obsoleti senza
perdere valutazioni.

## Reset e cancellazione

Reset corso e cancellazione istanza rimuovono stato, segmenti, eventi reazione, integrità, prese visione e reazioni
configurate nello scope, con un ordine che evita orfani del plugin. File area e grade item passano dalle API Moodle.
La cancellazione Privacy usa operazioni proprie per contesto/utente.

## Test richiesti per modifiche al ciclo di vita

- fresh install e validatore;
- upgrade dalla più vecchia baseline dati reale supportata e dalla release precedente;
- upgrade interrotto/ritentato quando rilevante;
- backup/restore con e senza dati utente, Forum collegato e file caricati;
- reset, cancellazione attività, coerenza gradebook e completion;
- export/delete Privacy API e retention pianificata dopo il restore.
