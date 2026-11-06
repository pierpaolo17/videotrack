# Automazione browser Behat

La 1.7.237 accettata supera **33 scenari / 618 step** e 369 test PHPUnit / 3.988 asserzioni nei sei ambienti CI release/main. ZIP esatto e report server/strict sono accettati; il server non ha eseguito Behat. La candidata **1.7.238** contiene **41 scenari / 722 step attesi dal sorgente**, da confermare in CI reale. I sei ambienti usano un profilo Chrome, non sei browser.

## Setup ed esecuzione

Configurare il sito Behat isolato Moodle (`behat_wwwroot`, `behat_dataroot`, `behat_prefix`) e reinizializzarlo
dopo modifiche a generator, step o feature:

```bash
php admin/tool/behat/cli/init.php
php admin/tool/behat/cli/run.php --tags='@mod_videotrack'
```

Non puntare mai Behat a dati di produzione.

## Fixture deterministiche

- `behathtml5fixture=1` crea un'attività upload con fixture MP4 locale di 60 secondi.
- `behatproviderfixture=youtube` usa id riservato e doppio SDK YouTube locale soltanto in Behat.
- `behatproviderfixture=vimeo` usa id riservato e doppio SDK Vimeo locale soltanto in Behat.
- `behatlinkedforum=<nome>` mappa una fixture Forum nello stesso corso.

I percorsi di produzione degli adapter restano in uso; viene sostituito soltanto il comportamento SDK/rete esterno,
rendendo gli scenari ripetibili senza disponibilità dei provider pubblici.

## Copertura corrente

La suite verifica:

- comportamento learner, solo docente e doppio ruolo;
- composer reazione/nota/bookmark visibili e cronologie comprimibili indipendenti;
- assenza delle sezioni reazioni, note, bookmark e Forum quando la configurazione effettiva non è disponibile;
- lifecycle play/pause HTML5 e scrittura terminale ledger accettata;
- resume attendibile, seek indietro e seek avanti consentito/bloccato;
- persistenza snapshot pre-seek ed esclusione dei gap da copertura/resume;
- timestamp reazione, nota, bookmark e Forum dopo rollback;
- presa visione sempre disponibile o dopo copertura validata dell'ultimo secondo;
- persistenza completion Moodle per visione, interazioni e presa visione;
- avvisi persistenti/transitori impilati;
- policy focus strict e risoluzione gruppo di eccezione;
- contratti deterministici YouTube/Vimeo per resume, seek, recupero e pausa terminale;
- requisiti completion raggruppati e layout verticale nelle varianti lista native/ARIA di Moodle.

## Completamento da riproduzione browser

`browser_tracking_completion.feature` aggiunge un caso HTML5 e uno per ciascun adapter di produzione YouTube/Vimeo
con double SDK locali. Ogni fixture disabilita autoplay/resume e completamento per sola vista/presa visione, usa
durata di 60 secondi e soglia visione del 5%, e parte senza righe validate nel registro e con completamento nativo
incompleto. Il browser riproduce circa cinque secondi e mette in pausa dal player/provider reale; le asserzioni
richiedono pausa accettata nella stessa sessione, stato aggregato completato e riga `course_modules_completion`
persistita. Il reload deve conservare il completamento. La durata breve mantiene il test pratico ma richiede una
soglia positiva di tre secondi validati dal server: nessun seek o progresso predisposto attraversa quella soglia.

Le due nuove asserzioni leggono soltanto: non inseriscono/aggiornano registro, stato o completamento, non invocano
direttamente l'endpoint segmenti, non simulano risposte AJAX e non impostano righe complete. Il polling database
attende al massimo cinque secondi con brevi tentativi per le scritture asincrone, seguendo l'asserzione pausa esistente.

Il caso di visione in `html5_completion_contract.feature` predispone deliberatamente evidenze per verificare la
sincronizzazione da uno stato esistente. Il nome dello scenario ora lo dichiara. Resta utile accanto ai nuovi casi
browser → AJAX → database e da solo non dimostra una visione generata dal browser.

## Seek avanti consentito senza credito sul gap

`browser_allowed_seek_tracking.feature` aggiunge un caso senza seed per provider. La fixture abilita
esplicitamente il seek avanti, usa durata di 60 secondi e completamento automatico per il 50% di visione,
disabilitando autoplay/resume e completamento per sola vista/presa visione. Il browser parte senza evidenze
validate, riproduce fino alla finestra 4-10 secondi, salta a 30 mentre riproduce, prosegue nella finestra
33-39 secondi e mette in pausa. HTML5 usa il media locale reale; YouTube e Vimeo gli adapter di produzione
con i double SDK invariati.

La nuova asserzione in sola lettura attende il database reale per al massimo cinque secondi. Richiede una
riga seek validata positiva prima di 12 secondi e una successiva pausa validata positiva che inizi da almeno
29,5 secondi, entrambe della stessa sessione. Tutti gli intervalli validati del registro e quelli aggregati
persistiti devono lasciare scoperto il tratto centrale deliberatamente non visto 12-29,5 secondi. I secondi
unici aggregati devono coincidere con la somma degli intervalli e restare sotto 30; la posizione di resume
deve avanzare oltre 33 secondi senza soddisfare la soglia. Il completamento nativo resta incompleto anche al reload.

Il confine di 12 secondi ammette la normale tolleranza polling/seek della prima finestra di riproduzione;
29,5 secondi ammette la tolleranza provider attorno al target di 30 secondi. Questi casi non predispongono
progresso, registro, completamento o risposte AJAX. Binding browser e fixture SDK esistenti restano invariati.

## Interazioni dopo rollback e completamento conservato

`browser_rollback_interactions.feature` aggiunge un caso senza seed per provider, con media HTML5 locale e
double SDK YouTube/Vimeo locali invariati. Ogni attività disabilita autoplay/resume, seek avanti, completamento
da reazioni e presa visione; la soglia automatica del 5% richiede tre secondi validati della fixture da 60 secondi.
Play/pausa deve ottenere completamento aggregato e nativo reale prima del tentativo di salto a 30 secondi.
Lo stesso caso combina quindi completamento guadagnato nel browser, rollback e contratti delle interazioni.

La prima nuova asserzione in sola lettura richiede player in pausa entro 1,25 secondi dalla frontiera persistita
per 750ms, con attesa massima di sette secondi. Reazione, nota e bookmark vengono salvati dai controlli visibili
e verificati nelle rispettive cronologie. Il browser apre il composer Forum collegato: il test verifica il form
visibile e il suo URL reale, senza pubblicare una discussione.

La seconda asserzione attende al massimo cinque secondi. Legge esattamente una reazione attiva, una nota e un
bookmark, controllandone etichette/tipi, timestamp, scope del modulo e sessione della pausa accettata. Timestamp
degli eventi e timestamp arrotondato nell'URL Forum devono coincidere con la frontiera entro 1,25 secondi;
l'URL deve riferirsi a questa attività e alla stessa sessione. Frontiera e secondi unici restano sotto 22 secondi,
sotto il target rifiutato di 30. Completamento aggregato e nativo devono restare completi, anche al rientro
nell'attività. Non vengono inseriti registro, stato, interazioni, completamento o risposte AJAX dal test.

Il vecchio caso HTML5 con seed resta invariato e conserva il proprio scopo basato su fixture. I nuovi casi
esercitano evidenze generate dal browser per i tre provider. Ended/replay/velocità e combinazioni pertinenti
ruoli/gruppi/browser restano lavoro U-007 esplicito; questi casi da soli non chiudono la fase.

## Correzione della prima CI 1.7.237

Il commit iniziale `04b2fc284b62752a4a3bdeb113031049d46a060e` fallisce la nuova asserzione di frontiera
in pausa per HTML5 e Vimeo in ogni job CI. Cinque job terminano con 31/33 scenari passati; Moodle 5.2 perde anche
il servizio HTTP locale con `ERR_CONNECTION_REFUSED` più avanti nella suite. PHPUnit resta a 369 test / 3.988
asserzioni, strict a 10/0/0 e i gate statici/build configurati passano. Sono evidenze di una candidata non approvata.

Il test presumeva che il recupero conservasse la pausa. Il rilevamento HTML5 della riproduzione recente e il
recupero Vimeo del seek utente possono invece riavviare il video. La nuova azione di pausa esplicita osserva stato
e tempo tramite API native o double SDK invariati: una fase in pausa o in avanzamento deve restare stabile per
6,5 secondi, oltre le guardie di recupero esistenti di sei secondi. Una fase in riproduzione deve avanzare anche
di almeno sei secondi video. Cambio di stato o salto indietro riavviano l'osservazione; il limite totale è 15 secondi
e il media deve restare sotto 25 secondi. L'azione chiama poi una volta la vera API di pausa. È osservazione media
limitata, senza sleep fisso o scritture ai dati di tracking. Seguono frontiera in sola lettura e pausa accettata.

Il limite superiore delle evidenze diventa 22 secondi per includere la riproduzione reale durante il recupero,
restando sotto il target rifiutato di 30. Restano tolleranza relativa timestamp (1,25 secondi), sessione/scope,
conteggio/tipi eventi e completamento nativo. Sei step aggiunti portano l'attesa sorgente a **33 scenari / 618 step**.
Quella prima correzione lasciava adapter, AMD e workflow invariati; la correzione runtime HTML5 sotto aggiorna
sorgente e build Grunt corrispondenti, mantenendo double SDK e workflow. Servono tutti i sei job CI della candidata
corretta; anche l'interruzione HTTP Moodle 5.2 deve essere assente prima del merge.

## Autorizzazione della riproduzione HTML5 dopo il recupero

La candidata successiva, `52a371c0ce821b508f3107477f0c158273164cfb`, supera 32/33 scenari in tutti i sei
job. Vimeo e la precedente interruzione HTTP Moodle 5.2 risultano risolti in quel run. Resta HTML5: dopo la pausa
esplicita il media è circa a 11,8 secondi, ma la frontiera persistita resta circa a 5,3. È un difetto reale di
autorizzazione esposto dall'asserzione invariata, non un motivo per allargarne la tolleranza.

Il recupero HTML5 della riproduzione recente può riavviare il media dopo una pausa che ha chiuso la finestra
server. Prima, `blockForwardSeek()` apriva un segmento client sulla sola intenzione di recupero; `state.playing`
faceva poi saltare `Api.beginPlayback()` al vero gestore play. La correzione rileva se un segmento tracciato era
già aperto e salva/ruota soltanto quello. Un recupero da pausa deve ottenere una nuova finestra server prima di
aprire il segmento o avviare l'heartbeat. Una finestra già attiva conserva il proprio percorso. L'id di sessione
browser resta invariato e un'autorizzazione negata non può creare un segmento tracciato.

Le vere funzioni di recupero e gestione eventi sono state provate localmente con i confini media/server
strumentati: il vecchio sorgente riproduce l'handshake mancante, quello corretto attende l'autorizzazione e
conserva snapshot del seek attivo e pausa inattiva. È un controllo JavaScript isolato, non runtime Moodle completo.
Il vero Grunt AMD Moodle con Node 22.23.4 / Grunt 1.6.1 riproduce identici tutti i 96 vecchi build, poi supera
ESLint/Rollup sulla correzione: cambiano soltanto minificato/mappa HTML5, con sorgente esatto nella mappa.
Suite da 33 scenari / 618 step e asserzioni pausa, timestamp relativi, sessione/scope e completamento restano
invariati. I sei job CI corretti 1.7.237 sono passati prima del merge e su main. U-007 resta aperto.

## Matrice di copertura U-007

| Contratto | HTML5 | YouTube | Vimeo |
|---|---|---|---|
| Play/pausa e scrittura terminale accettata | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Resume, seek indietro, seek avanti bloccato/frontiera | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Riproduzione senza seed fino al completamento nativo | Caso 1.7.235 accettato | Caso 1.7.235 accettato | Caso 1.7.235 accettato |
| Seek avanti consentito, gap escluso e completamento incompleto | Caso 1.7.236 accettato | Caso 1.7.236 accettato | Caso 1.7.236 accettato |
| Interazioni dopo rollback e completamento conservato | Caso senza seed 1.7.237 accettato | Caso SDK double 1.7.237 accettato | Caso SDK double 1.7.237 accettato |
| Ended/replay/velocità e scope gruppi pertinenti | Revisione matrice residua | Revisione matrice residua | Revisione matrice residua |

Controlli learner, solo docente e doppio ruolo hanno già scenari condivisi sulla pagina. Questo non copre ogni
combinazione provider/lifecycle/gruppo. U-007 resta aperto fino alle evidenze sulle celle essenziali e sui rami
Moodle di confine; questa tranche non dichiara copertura completa dei provider o dei browser.

Il workflow attuale invoca il profilo Behat `chrome` su sei ambienti Moodle/PHP/database: sono sei job di ambiente
con un solo profilo browser. Risultati su altri browser e smoke provider pubblici vanno registrati separatamente,
senza dedurli da quel conteggio. U-020 manuale e gate finali di produzione restano anch'essi distinti.

## Regole per selector e asserzioni

Preferire marker CSS/data stabili del componente al testo tradotto per i target interattivi; verificare separatamente
le etichette visibili. Leggere stato persistito Moodle/plugin quando il markup varia tra rami. Le asserzioni media
usano tolleranze limitate e attendono le scritture asincrone invece di sleep fissi.

## Limiti

I doppi SDK locali verificano il contratto adapter ma non disponibilità iframe pubblici, consenso, cookie o UI del
provider. Behat non sostituisce test percettivi con tastiera/screen reader né smoke su provider reale. I risultati
valgono soltanto per albero plugin, ramo Moodle, browser e driver esatti registrati.

## Casi intervallo di attenzione 1.7.238

Cinque casi browser in sola lettura coprono terzi automatici, solo minimo, solo massimo, controllo spento e riserva sito con durata zero. Tre ulteriori scenari provider usano 60–60 secondi espliciti e riproducono a 0,5x le fixture media/SDK da 60 secondi invariate, così la fine non anticipa la vera scadenza di un minuto. Nessun timer o orologio server viene accelerato. Ogni caso osserva messaggio di pausa, pausa accettata con chiusura del credito server e frontiera stabile; poi riprende fino a 31–38 secondi video e mette nuovamente in pausa dal provider, richiedendo un nuovo ciclo play/pausa accettato. Scenari e asserzioni precedenti restano integri. La CI candidata deve confermare tutti i casi; smoke con provider pubblici e altri browser restano lavoro U-007 distinto.

I casi 1.7.238 corretti selezionano esplicitamente 0,5x e 1x tramite `playbackspeed_0.5` / `playbackspeed_1`. Il driver rifiuta di forzare 0,5x se la lista effettiva del player non la contiene. La CI iniziale metteva in pausa i tre provider ma rifiutava correttamente le scritture a velocità non autorizzata. Attesa di un minuto, pausa accettata, frontiera stabile e ripresa restano invariati.
