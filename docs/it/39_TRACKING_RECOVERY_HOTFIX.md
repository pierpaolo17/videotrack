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

PHP/Moodle/MariaDB non sono disponibili nel workspace locale. Gli esiti nativi CI
e i controlli ancora aperti sono riportati sotto. Le build Babel iniziali dei12gruppi
AMD modificati sono sostituite con output Grunt nativo verificato. I test locali
coprono sorgenti e compilati; la verifica con provider esterni reali resta distinta
dalle prove browser automatiche con fixture.

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

## Esito della prima CI 269 e candidata correttiva

Il [run 45227903298](https://github.com/pierpaolo17/videotrack/actions/runs/45227903298)
ha eseguito il commit `a8d58ffd21a7558b05e25bc5433344dca0aa1a16`, albero
`80a28a42cc2e3ff3002b8e7dd35b5bc5902aa53e`. Tutti i sei job falliscono. Ciascuno
esegue 595 metodi PHPUnit/6268 asserzioni con un solo fallimento in una vecchia
asserzione sul testo sorgente; i 12 nuovi metodi passano. Behat supera 73 scenari su 75;
i due scenari nuovi falliscono anche nei due retry automatici. YouTube raggiunge
circa 7,2 secondi in un’attesa di 7 secondi, HTML5 riparte vicino a 3 e raggiunge circa 9,4
in un’attesa di 6: il timeout non dimostra un player bloccato. Passano i 73 scenari
precedenti, incluse le verifiche rafforzate sulla conservazione dopo pausa casuale.

Il job qualità rileva 51 errori PHPCS e un warning per `$CFG->admin` deprecato.
Gli strumenti sono passati da PHPCS 3.13.6/moodle-cs 3.7.0 a 4.0.4/3.9.2, esponendo
anche spaziature di intestazione già presenti. Superati lint 197/197, PHPDoc e controlli
helper dove eseguiti; tutti i sei validator danno 10/0/0. Superati anche 12 gruppi Node
più harness tracker per matrice. Lifecycle saltato dopo i fallimenti, non accettato.
PHPMD su 50 riporta 35 avvisi rispetto ai 31 precedenti; le 757/785 informazioni Psalm
rispetto a 751/779 restano da classificare.

La correttiva sistema formattazione e DocBlock senza cambiare i token di recupero o
completamento. Aggiorna l’asserzione wallclock al cursore catturato conservando gli
altri controlli. Aggiunge un’attesa dedicata e limitata della riproduzione reale prima
delle due asserzioni 12–17 secondi, che restano invariate. Non modifica attese brevi
esistenti o retry. Il link impostazioni usa la navigazione core anziché la proprietà
admin deprecata. Nessun cambiamento a sorgenti AMD, ledger live, schema o politica
di recupero; restano tutti i 595 metodi unitari.

Grunt termina la compilazione, ma il controllo di freschezza respinge 24 file generati.
Le build Babel della prima patch non sono quelle canoniche Moodle. Il registry npm
locale risponde 403: non viene simulata una compilazione nativa. Il gate originale
resta obbligatorio. Poiché plugin-CI 4.5.13 ripristina i file iniziali dopo il confronto,
un secondo comando Grunt protetto e limitato alla CI raccoglie l’output reale, hash
sorgenti/commit e mappe esatte. Gli artifact immediati e i report normali includono
`native-amd.zip`; le regressioni sui compilati usano il vero output installato.
Servirà poi integrare quelle build verificate e rieseguire tutti i gate: questa
correttiva preparatoria da sola non chiude il problema Grunt.

La casella recupero è temporanea: dopo la bonifica verificata delle attività della
produzione 5.0 rimuovere interfaccia e procedura straordinaria in una release successiva,
conservando prove recuperate e ricalcolo normale. Demo 5.3 e ricerca Microsoft restano
in pausa. In questa fase preparatoria i controlli nativi corretti erano ancora da eseguire.

## CI corretta e compilati canonici

Il [run 45240781127](https://github.com/pierpaolo17/videotrack/actions/runs/45240781127)
verifica la prima correttiva, commit `b0814083eb44e83598bb20406e7549d8e006b536`.
Tutte le sei matrici superano PHPUnit595/6280, Behat75/1428 senza retry, test Node
e strict10/0/0. Il job qualità supera lint, PHPCS e PHPDoc. Fallisce solo il confronto
AMD: la compilazione nativa riesce ma i file inizialmente distribuiti differiscono.

I sei output nativi raccolti separatamente hanno gli stessi hash per tutti i96file,
48hash sorgenti corrispondenti e mappe esatte. Sostituiti i24file build/map differenti
senza modificare PHP, sorgenti AMD, test o politica di recupero. Il gate originale
resta attivo. Rieseguire la CI completa della candidata, incluso aggiornamento storico
e disinstallazione popolata prima saltati, poi CI main e accettazione server.
Questa integrazione non dichiara bonifica in produzione o Grunt nativo locale eseguiti.

## Accettazione e ripresa dello sviluppo

La269 accettata coincide con lo ZIP caricato: candidato/main a sei job verdi,
server canonici/lint198, Unit595/6280 su entrambe le istanze e Grunt nativi superati,
entrambi strict10/0/0. Il manutentore riferisce installazione/ricalcolo riusciti
il2026-11-24. La270 riprende la ricerca Microsoft conservando tracciamento e recupero.
