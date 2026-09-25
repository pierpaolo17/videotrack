# Sicurezza e confini di fiducia

VideoTrack considera browser e SDK dei provider pubblici client non attendibili. Possono richiedere operazioni e
comunicare stato, ma soltanto servizi Moodle autenticati ed evidenze lato server avanzano il progresso autorevole.

## Confine delle richieste

Ogni scrittura AJAX è dichiarata in `db/services.php` e implementata in `classes/external/`. La sequenza comune è:

1. validare i parametri external e normalizzare i valori scalari;
2. caricare il course module come `videotrack` e il contesto modulo;
3. richiedere login e `mod/videotrack:participate`;
4. verificare scope attività, corso, utente e proprietà;
5. acquisire un lock circoscritto quando scritture concorrenti possono collidere;
6. applicare la validazione di dominio e scrivere con DML Moodle;
7. restituire soltanto la forma di risposta dichiarata.

La protezione CSRF/sessione è fornita dal framework AJAX autenticato di Moodle. Le azioni pagina dirette usano
inoltre `require_sesskey()` quando necessario. I servizi di scrittura originati dal browser chiamano il confine
comune `external\helper::require_ajax_sesskey()` prima di ogni mutazione. I callback lifecycle Moodle (`lib.php`),
upgrade, installazione/disinstallazione, backup/ripristino e Privacy API sono invocati da orchestratori core fidati,
non come azioni browser dirette; aggiungere controlli sesskey di richiesta dentro tali callback ne violerebbe il
contratto core.

I file procedurali condivisi (`locallib.php` e `db/repairlib.php`) rifiutano l'esecuzione diretta tramite il guard
standard `MOODLE_INTERNAL`. Le pagine dirette inizializzano Moodle con `config.php` prima di caricare le librerie.

## Autorità sul playback

`start_playback` apre una finestra di credito legata alla sessione di riproduzione browser. `save_segment` confronta
tempo media richiesto, tempo server trascorso, velocità consentita, sessione corrente e credito esistente. Gli eventi
terminali accettati chiudono la finestra. Righe obsolete, cross-session o oltre budget non aumentano la copertura
unica anche se conservate per audit.

Il server non deduce attenzione da focus, posizione massima o percentuali client. La navigazione consentita non
dimostra che il tratto saltato sia stato visto.

## Timestamp delle interazioni

Timestamp di reazioni, note, bookmark e Forum devono ricadere in evidenze viste attendibili o in altra regola server
esplicitamente autorizzata. I client scaricano il progresso pendente prima dell'interazione e usano l'estremo salvato,
ma la validazione server resta obbligatoria.

## Permessi e proprietà

- partecipazione e accesso ai report sono indipendenti;
- le cancellazioni personali agiscono solo sui record dell'utente corrente;
- permessi report/export aggregati e individuali sono separati;
- contesto corso/modulo e scope gruppi/cohort Moodle limitano le letture;
- possono essere collegati soltanto Forum compatibili dello stesso corso;
- i docenti necessitano capability dedicate per superare i default sito di player/completion.

## Minimizzazione e sicurezza output

L'output usa API Moodle di escaping/formattazione. `html_writer` produce markup strutturale già sottoposto a escaping:
tabelle e link generati vengono emessi direttamente, senza filtri contenuto o un secondo escaping. I file sono
serviti tramite file area e contesti Moodle. Le query usano placeholder DML e array di parametri. Le clausole `IN`
dinamiche sono accettate soltanto come coppie frammento SQL/parametri restituite dagli helper DML Moodle come
`get_in_or_equal()`. Gli Analytics aggregati escludono testo note/bookmark e applicano il mascheramento privacy salvo
accesso individuale. Gli eventi di integrità sono limitati e diagnostici.

## Limiti esterni

Disponibilità, cookie, consenso e iframe YouTube/Vimeo sono esterni. Vincoli UI come il blocco Picture-in-Picture
sono best effort. Le decisioni di sicurezza si basano quindi su credito server e permessi Moodle, non sul presunto
controllo completo di un player esterno.
