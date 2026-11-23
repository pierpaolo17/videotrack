# Correzione urgente del tracciamento e recupero storico

VideoTrack **1.7.269** (`2026112300`), basata sulla **1.7.267** accettata.
La precedente268 dedicata alla ricerca Microsoft è esclusa e resta in pausa.

## Comportamento corretto

Il polling YouTube raggiunge sempre il salvataggio periodico, anche nei rami di
riproduzione naturale con avanzamento libero disabilitato. Prima alcune uscite
anticipate accumulavano un segmento troppo lungo: il limite server lo respingeva
e il successivo controllo sull’avanzamento poteva mantenere il buco.

Pausa manuale, cambio scheda e pausa casuale catturano subito il punto raggiunto.
Un cursore condiviso col salvataggio già in corso evita di inviare due volte la
stessa porzione. La ripresa attende il salvataggio precedente prima di aprire la
nuova sessione server. Risposte tardive non riaprono segmenti chiusi e non modificano
una diversa riproduzione; salvataggi respinti/nulli non avanzano il cursore.

La ripresa mantiene gli intervalli aggiornati e non li sostituisce coi dati iniziali
della pagina. Le risposte riuscite fuori ordine mantengono l’unione degli intervalli
confermati. Un callback vuoto restituisce i dati già salvati, senza azzerarli.
La durata configurata resta il denominatore della percentuale anche nel frontend.
Il numeratore è il tempo unico visto:96 secondi distinti su1172 sono 8,19%; rivedere
lo stesso tratto non aumenta la copertura unica.

Lo scroll orizzontale o verticale nella stessa pagina non genera osservazioni del
viewport, segnalazioni di integrità o chiusure del tracciamento. Le vecchie righe
`outofviewport` restano conservate. Nascondere davvero il documento chiude il tratto
visto fino a quel momento e mantiene tutti i progressi precedenti. Al ritorno, se il
player continua, serve un nuovo avvio server; con avanzamento disabilitato, eventuale
riproduzione in background torna in pausa al punto visto senza penalità di seek.
Anche il polling di recupero Vimeo richiede una nuova autorizzazione server dopo
una chiusura della sessione.

## Dati già presenti e ricalcolo

L’installazione non cambia schema, tabelle, indici o chiavi e non esegue migrazioni
massive né cancellazioni. Conserva note, segnalibri, reazioni e segmenti originali.
Restano attivi limite temporale server, sessione proprietaria, velocità consentita,
idempotenza, controllo avanzamento e verifica della posizione vista. Sono mantenute
le correzioni precedenti: confini 1.7.52, sessioni 1.7.101 e interazioni 1.7.102.

Dopo un backup completo verificato, apri **Report docente → Ricalcola**. Il ricalcolo
ordinario continua a usare soltanto prove validate; i processi Privacy/retention non
attivano alcun recupero. La nuova casella **Recupera i segmenti lunghi interessati
dai salvataggi periodici mancanti** è inizialmente deselezionata: abilita la
ricostruzione solo su richiesta esplicita del docente autorizzato. Provala prima su
uno studente interessato, confronta il risultato e poi seleziona eventualmente tutti.

Un segmento è recuperabile soltanto con avvio registrato dal server nello stesso
contesto/video/utente, sessione corrispondente, velocità consentita, tratto maggiore
del vecchio limite heartbeat, nessun salto avanti vietato e tempo cumulativo
compatibile con le date di ricezione server. L’orologio del browser non autorizza
il recupero. Un altro avvio sostituisce la scheda precedente; pausa, scheda nascosta,
uscita e seek chiudono la finestra. Non sono ricostruibili righe mancanti, casi
ambigui, piccoli rifiuti estranei al difetto o durata sconosciuta. Modifiche successive
a video, heartbeat o velocità consentite possono limitare il recupero.

Il recupero opera sotto lock e transazione per utente, conserva byte per byte le
righe originali e aggiunge righe derivate identificabili con motivo `recovery269`
e requestid deterministico `recovery269_<sha1(id originale)>`. Sono evidenze
ricostruite su richiesta amministrativa, non nuova visione osservata. Analytics e
interazioni possono usare le righe risultanti; stato aggregato e completamento
Moodle passano dal normale percorso autorizzato. Ripetere l’operazione non duplica
le righe. Non ripristina automaticamente il database e non cancella il dataset demo.

## Verifiche e limiti

Superati localmente test AMD sui sorgenti e sui build distribuiti: visione YouTube
continuativa di440 secondi con avanzamento bloccato, concorrenza heartbeat/pausa,
cambio scheda, pausa casuale, scroll, risposte tardive, salvataggi respinti e durata
configurata. Cinque regressioni mirate falliscono sui sorgenti267 e passano con la
patch. L’harness tracker storico viene reso eseguibile mantenendo le asserzioni,
con stub debug, payload wallclock corretto e attesa asincrona deterministica.

Aggiunti12 metodi PHPUnit per recupero esplicito/idempotente, conservazione originali,
autorizzazione interazioni, orologi falsi, sessioni, confini, doppia spesa, limiti e
risposte vuote. Due nuovi scenari browser; tre scenari di pausa casuale ora verificano
anche che la copertura precedente resti contigua. La CI esegue i test Node.

PHP/Moodle/MariaDB non sono disponibili nel workspace locale: lint/PHPCS, PHPUnit,
Behat, analisi e Grunt Moodle nativi restano da eseguire in CI/server. I 12 gruppi AMD
modificati sono compilati con Babel e mappe allineate ai sorgenti esatti. I test
locali non equivalgono a una certificazione Moodle/browser/provider reale.

Non creare il tag prima dei gate consueti e non unire il vecchio ramo Microsoft 268.
Se268 è già installata, aggiorna direttamente a269: non modificare la versione nel
DB, non disinstallare il plugin e non ripristinare un vecchio dump sopra i dati nuovi
per forzare un downgrade a267. Le istruzioni Git esterne accompagnano la patch.

## Lezioni e roadmap

Servono prove prolungate con avanzamento bloccato, oltre alle brevi prove play/pausa.
Il salvataggio periodico deve essere indipendente dalle uscite del controllo seek.
Configurazione iniziale e progresso confermato hanno durate diverse. Una pausa salva
un confine e non cancella progresso. Lo scroll serve ai controlli di studio e non
indica perdita di attenzione.

La ricerca Microsoft 268 sarà ripresa e ribasata dopo l’accettazione dell’hotfix; poi
Microsoft, Zoom con repository separato opzionale, Google Drive e infine App.
Conservare dataset per screenshot e standby dell’accessibilità manuale 5.3.
