# Audit della documentazione

Baseline: VideoTrack **1.7.43** (`2026081204`).

## Copertura

- File non documentali inventariati: **253/253**.
- Funzioni/metodi PHP nominati inventariati: **604**.
- Callable AMD nominati rilevati e inventariati: **647**.
- Tabelle XMLDB documentate: **7**.
- Chiavi impostazioni sito documentate: **57**.
- Chiavi configurazione player documentate: **133**.
- Servizi AJAX documentati: **9**.
- Language pack: otto pacchetti con lo stesso contratto di **977 chiavi**; i testi operativi sono tradotti, mentre termini tecnici e nomi propri possono legittimamente coincidere.
- Panoramiche root: `README.md` (inglese) e `README_IT.md` (italiano).
- Sintesi privacy root: `PRIVACY.md` e `PRIVACY_IT.md`.

## Verifica documentazione pre-produzione 1.7.43

- Inventario corrente dei file non documentali rigenerato sull'albero reale 1.7.43: **253/253** voci.
- Inventario funzioni rigenerato sulle posizioni sorgente correnti: **604** funzioni/metodi PHP nominati e **647** callable AMD nominati rilevati.
- L'inventario XMLDB coincide con tutte le **7** tabelle e con ogni campo dichiarato in `db/install.xml`.
- L'inventario impostazioni coincide con tutte le **57** impostazioni `mod_videotrack`.
- L'inventario servizi AJAX coincide con tutti i **9** servizi dichiarati.
- L'inventario configurazione browser/player coincide con tutte le **133** chiavi, incluse le quattro label seek FW introdotte dopo la 1.6.33.
- Tutti gli otto language pack mantenuti espongono **978** chiavi identiche, nessun duplicato e placeholder Moodle coerenti.
- I link Markdown relativi della documentazione sono stati verificati contro l'albero senza target mancanti.
- I documenti storici specifici di release restano storici; soltanto indici e inventari correnti sono ribasati alla 1.7.41.

## Regole di aggiornamento

I documenti correnti non devono contenere affermazioni di release senza indicare versione/stato. Gli audit 1.4.x sono isolati in `archive/`. Ogni modifica a schema, servizi, File API, configurazione player, report, privacy, accessibilità o traduzioni richiede l’aggiornamento dei documenti numerati pertinenti.

## Audit automatici attesi

Ogni release deve confrontare inventario file/albero, inventario funzioni/sorgente, chiavi e placeholder, `get_string` statici, XMLDB/backup-restore, servizi/classi, sorgenti/build AMD e link Markdown/file esistenti.

## Copertura retention basata sulla cancellazione 1.6.33

- La retention pianificata elimina definitivamente segmenti, interazioni, indicatori e prese visione scaduti; non conserva più pseudonimi deterministici con utente negativo né una chiave di mapping.
- `videotrack_state` è trattato come dato personale derivato, ricostruito dai segmenti validati e dagli input di completamento ancora conservati, e rimosso quando tali input non esistono più.
- I contatori di credito obsoleti vengono azzerati, i guard attivi e limitati vengono preservati e il completamento personalizzato Moodle viene sincronizzato con lo stato ricostruito.
- I backup con dati utente includono soltanto record con utente positivo entro la retention del sito sorgente e omettono lo stato derivato; il restore applica la retention del sito destinazione e ricostruisce lo stato dopo il ripristino della completion del modulo.
- La cancellazione Privacy API elimina i record learner, mentre i file condivisi dell’attività restano governati dal ciclo di vita dell’attività.
- I test di regressione coprono dati vecchi/recenti misti, ricostruzione/rimozione dello stato, guard attivi, rimozione dei pseudonimi legacy e cancellazione utente dopo la retention.

## Copertura registro di riproduzione 1.6.32

- `mod_videotrack_start_playback` stabilisce un timestamp server a credito zero prima dell’apertura di un segmento tracciato.
- Ogni handshake e segmento possiede un identificativo persistente protetto da indice univoco attività/utente/richiesta.
- I retry riutilizzano un risultato identico già salvato e non possono duplicare righe, transizioni di completamento o eventi.
- Il credito è cumulativo, deriva dal tempo server trascorso e da una velocità consentita, con una tolleranza limitata per il clock che resta debito cumulativo fra richieste e handshake.
- I secondi coperti esatti restano monotoni quando la lista compatta raggiunge 500 intervalli.
- XMLDB, upgrade, backup/restore, Privacy API, metadati lingua, sorgenti/build AMD e test di regressione descrivono lo stesso contratto di ledger.

## Copertura contratto runtime e privacy 1.6.31

- La whitelist AJAX client viene confrontata con tutti i servizi AJAX Moodle dichiarati; gli indicatori di integrità non vengono più rifiutati come `invalid-method`.
- Ogni endpoint di mutazione learner verifica il sesskey Moodle prima di caricare contesto del modulo o dati dal database.
- La soppressione privacy di un intervallo si propaga al totale spettatori e alle percentuali di retention che usano quel totale come denominatore.
- Le risposte delle reazioni espongono soltanto campi icona strutturati; il player HTML5 non analizza più il campo HTML grezzo `iconhtml`.
- Le release 1.6.25–1.6.31 sono documentate come code-only rispetto a XMLDB e non introducono savepoint no-op.

## Copertura partecipazione esplicita 1.6.29

- UI attività, servizi di scrittura learner e popolazioni dei report usano `mod/videotrack:participate`.
- L’accesso ai report non disabilita più un partecipante con ruolo multiplo.
- Docenti e manager standard restano non tracciati perché la capability è assegnata all’archetipo Studente e il privilegio amministrativo do-anything viene ignorato per questa decisione.
- Gli amministratori dei ruoli possono assegnare o revocare la capability ai ruoli personalizzati.

## Copertura trasporto configurazione durata 1.6.28

- La configurazione localizzata del detector è conservata in un elemento script JSON nel DOM del form attività.
- `js_call_amd()` riceve soltanto l’id dell’elemento di configurazione, mantenendo l’argomento serializzato molto sotto la soglia Moodle developer di 1024 caratteri.
- Il modulo AMD verifica e analizza la configurazione DOM prima di installare il detector.

## Copertura durata automatica 1.6.27

- Il form docente può proporre la durata dai metadati YouTube, Vimeo o del file locale.
- La proposta è modificabile, annunciata tramite una regione live accessibile e diventa autorevole soltanto quando il docente salva.
- La durata runtime learner non può aggiornare il valore memorizzato; se provider o browser non espongono i metadati, il campo resta manuale.

## Copertura affidabilità strumenti player 1.6.26

- Una durata verificata pari a `0` disabilita esplicitamente percentuale vista e completamento percentuale, mantenendo il tracking degli intervalli validati e gli strumenti di studio abilitati.
- L’intero fieldset dei controlli HTML5 è nascosto per YouTube e Vimeo e ricompare quando la sorgente diventa un file locale.
- I controlli delle reazioni learner restano disponibili in riproduzione e in pausa, ma il server accetta solo timestamp già visualizzati e validati.
- Note e segnalibri abilitati sono mostrati ai partecipanti espliciti; chi non possiede `mod/videotrack:participate` riceve un’anteprima disabilitata e non può creare telemetria learner.
- Le segnalazioni PHPCS rilevate sulla 1.6.25 in `view.php`, `report.php` e `tests/tracker_test.php` sono corrette nell’albero 1.6.26.

## Copertura regressioni interfaccia 1.6.25

- Il renderer Analytics delle prese visione inizializza sia la soppressione dei conteggi sia quella del progresso.
- Le definizioni delle reazioni restano nella sezione principale Reazioni e non sono modellate come un insieme fisso di due elementi.
- L’ordine nella pagina attività è: reazioni, azione Forum opzionale, quindi storico personale delle reazioni/segnalibri.
- Anteprima per chi consulta i report e persistenza learner restano stati di fiducia distinti.

## Copertura persistenza segnalibri 1.6.30

La documentazione corrente registra la parità ripristinata tra il motivo segmento AMD `bookmark` e la whitelist di validazione PHP. Conserva inoltre il contratto di sicurezza secondo cui il timestamp di un segnalibro deve appartenere a progresso visto e validato dal server.
