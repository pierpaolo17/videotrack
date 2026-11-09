# Automazione browser Behat

La **1.7.239 accettata** supera **44 scenari / 830 step** e **379 test PHPUnit / 4.076 asserzioni** nei sei job CI release/main corretti. ZIP reale, report server e due strict sono accettati. PHPCS/lint, Grunt e PHPUnit server passano; il report non esegue Behat. La candidata **1.7.240** prevede dal sorgente **50 scenari / 1.074 step**, da confermare nella CI candidata reale. I sei ambienti usano un profilo Chrome, non sei browser.

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
esercitano evidenze generate dal browser per i tre provider. La candidata 1.7.239 aggiunge fine/replay/velocità
come descritto sotto. Le combinazioni pertinenti ruoli/gruppi/browser restano lavoro U-007 esplicito;
questi casi da soli non chiudono la fase.

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
| Configurazione attenzione e pausa automatica/ripresa reale | Caso 1.7.238 accettato | Caso SDK double 1.7.238 accettato | Caso SDK double 1.7.238 accettato |
| Fine naturale, replay, velocità consentita e riserva oltre tetto | Caso 1.7.239 accettato | Caso SDK double 1.7.239 accettato | Caso SDK double 1.7.239 accettato |
| Anteprima docente senza tracking e completamento con doppio ruolo | Nuovo caso 1.7.240, CI attesa | Nuovo caso SDK double 1.7.240, CI attesa | Nuovo caso SDK double 1.7.240, CI attesa |
| Confini gruppi separati su selettore e id studente diretto nei report | Nuovi casi endpoint comune 1.7.240, dati HTML5 | Stessa policy PHP; vedere casi comuni | Stessa policy PHP; vedere casi comuni |

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

Cinque casi browser in sola lettura coprono terzi automatici, solo minimo, solo massimo, controllo spento e riserva sito con durata zero. Tre ulteriori scenari provider usano 60–60 secondi espliciti e riproducono a 0,5x le fixture media/SDK da 60 secondi invariate, così la fine non anticipa la vera scadenza di un minuto. Nessun timer o orologio server viene accelerato. Ogni caso osserva messaggio di pausa, pausa accettata con chiusura del credito server e frontiera stabile; poi riprende fino a 31–38 secondi video e mette nuovamente in pausa dal provider, richiedendo un nuovo ciclo play/pausa accettato. Scenari e asserzioni precedenti restano integri. Tutti i casi sono passati nella CI main 1.7.238 accettata. Smoke con provider pubblici e altri browser restano lavoro U-007 distinto.

I casi 1.7.238 corretti selezionano esplicitamente 0,5x e 1x tramite `playbackspeed_0.5` / `playbackspeed_1`. Il driver rifiuta di forzare 0,5x se la lista effettiva del player non la contiene. La CI iniziale metteva in pausa i tre provider ma rifiutava correttamente le scritture a velocità non autorizzata. Attesa di un minuto, pausa accettata, frontiera stabile e ripresa restano invariati.

## Casi fine naturale, replay e velocità 1.7.239

`browser_ended_replay_rate.feature` aggiunge tre casi senza seed. La fixture docente seleziona esplicitamente
1x/2x, tetto 2x e riserva autorizzata 1x; disabilita autoplay/resume, loop, pause di attenzione e completamento
per presa visione. Il completamento automatico richiede il 95% della durata reale di 60 secondi.
Dopo l’avvio a 1x, il browser richiede 4x e osserva il ritorno a 1x imposto dal tetto esistente; richiede poi
2x autorizzato e lo verifica. Un’osservazione media limitata a 45 secondi attende la fine naturale, senza
seek alla fine né orologi runtime alterati. Per Vimeo si attende il risultato numerico asincrono della
velocità, senza considerare una Promise come esito positivo.

Il polling database in sola lettura richiede righe reali `playstart` e `ended` accettato della stessa sessione,
intervallo terminale positivo, credito server chiuso, posizioni finali/resume tra 59 e 60,001 secondi, copertura
unica tra 58 e 60,001 e stato aggregato/completamento Moodle completo. Un intervallo persistito deve coprire
5–20 secondi. Il driver conserva solo copertura, percentuale e identificatori terminale/sessione nella
memoria del test. Seleziona poi 1x, torna al secondo cinque, riproduce fino a 8–12 secondi e mette in pausa
dalla superficie reale. Il nuovo handshake deve seguire la fine accettata; una pausa positiva accettata deve
chiudere il credito nella stessa sessione browser. Copertura unica e percentuale devono coincidere con la
fotografia entro 0,001, con completamento ancora completo dopo reload. Il replay è interno alla copertura
validata: una frazione iniziale non accreditata per l’handshake al secondo zero non va confusa con un doppio
conteggio. La posizione resume può correttamente arretrare; dopo replay non si richiede che resti alla fine.

I casi distribuiti usano tempo reale e scritture AJAX reali; le asserzioni non inseriscono ledger/stato/
completamento né risposte simulate. Scenari precedenti, adapter, SDK double e guardie restano invariati.
Le verifiche locali dei driver estratti coprono controlli, funzioni del tetto e osservazioni asincrone;
la prima esecuzione Moodle 44/830 passa nei sei job; la correzione di formato ha superato i sei job release/main corretti. Questi contratti SDK non provano disponibilità degli iframe
pubblici, cookie/consenso o compatibilità con altri browser.

## Correzione formattazione della prima CI 1.7.239

I sei artifact caricati corrispondono ai digest ufficiali del commit
`79b7b5bac5c6680f760c67595780b7b1c7f11857` (workflow 42270208398). Il file Moodle 5.0 MariaDB caricato proviene
dal secondo tentativo e segnala ancora gli stessi sei errori CodeSniffer. Ogni ambiente supera Behat 44/830,
PHPUnit 379/4076, dieci strict e Grunt. Cinque job sono verdi; Moodle 5.0 MariaDB fallisce solo il passo Moodle
Code Checker. È evidenza funzionale, non accettazione complessiva della candidata.

Le tre nuove condizioni `if` multilinea devono avere la prima espressione sotto `if (` e la `)` finale
su una riga propria, con l’indentazione Moodle/PSR-12 già usata. La correzione incrementale cambia solo
spazi nelle condizioni e aggiorna documentazione/posizioni dei callable. Ogni token PHP, stringa JavaScript,
asserzione, timeout, fixture e scenario resta identico. Nessun cambiamento a produzione, regole analisi o
versione. I sei job della candidata corretta e dopo merge sono passati; il difetto storico di formato è risolto nella baseline 1.7.239 accettata.

## Anteprima docente e riproduzione con doppio ruolo 1.7.240

`browser_role_tracking.feature` aggiunge un caso senza seed per provider. Un utente solo docente riproduce
fino a 5–10 secondi e mette in pausa; il JSON reale deve esporre `trackingenabled=false`. L’asserzione in sola
lettura richiede assenza di righe `videotrack_seg` (incluso handshake iniziale), assenza di `videotrack_state`
e nessuno stato nativo Moodle completato nello scope utente/attività/modulo corso. È ammessa una riga nativa
assente o incompleta. Si ripete dopo reload e dopo la riproduzione di un altro utente. Il normale evento
Moodle di apertura attività resta legittimo ed è esterno a questa verifica del tracking studente.

Iscrizione studente più assegnazione del ruolo docente con modifica devono invece esporre
`trackingenabled=true`. Senza evidenze validate iniziali e con completamento incompleto, l’utente con doppio
ruolo riproduce fino a 5–10 secondi e mette in pausa dalla superficie reale. Le asserzioni esistenti richiedono
pausa accettata della stessa sessione con credito chiuso, soglia positiva di tre secondi e completamento
nativo persistito, conservato dopo reload. Le fixture disabilitano esplicitamente autoplay/resume, loop,
pause attenzione/focus e completamento solo per apertura/presa visione, con velocità 1x autorizzata.
SDK double e tutti gli scenari precedenti restano invariati.

## Confini dei report con gruppi separati 1.7.240

`browser_report_groups.feature` usa i generatori Moodle standard per iscrizioni, ruoli, override permessi,
gruppi e attività. Il corso forza gruppi separati; due studenti appartengono a gruppi diversi. Per il ruolo
docente senza modifica si previene `moodle/site:accessallgroups` nel contesto corso; un docente con modifica
lo conserva esplicitamente. Un revisore limitato condivide il Gruppo A e un secondo non appartiene a gruppi.
La sola capability report non può quindi sostituire l’accesso al gruppo.

Ciascuno dei tre casi ottiene visione accettata/completamento nativo per entrambi gli studenti tramite
riproduzione HTML5 locale reale prima di aprire i report. Nessun dato aggregato/ledger/completamento viene
inserito dal test. L’helper di navigazione risolve gli id delle fixture ma delega ogni decisione di permesso
al controller HTTP reale: non prefiltra né simula la risposta. L’asserzione confronta tutti i valori positivi
delle opzioni `#id_userid` con gli id esatti attesi, ignorando solo la normale opzione Tutti.

Il revisore del Gruppo A seleziona/richiede il proprio studente ma non vede opzione o nome del Gruppo B;
la richiesta diretta di quest’ultimo restituisce `Invalid user`, senza selettore né nome studente. Il revisore
senza gruppi ha selettore vuoto e lo stesso rifiuto diretto. Il permesso su tutti i gruppi consente entrambi
gli studenti reali e i rispettivi report, senza includere lo staff che ha solo accesso report. Sono contratti
comuni del report PHP/ACL Moodle con dati ottenuti da HTML5; non attestano smoke separati SDK o download CSV.

Aspettative dal sorgente: 50 scenari / 1.074 step e 379 metodi PHPUnit. I controlli locali isolati coprono
l’osservatore dell’interruttore estratto e i rami anteprima API invariati, con stato congelato e senza trasporto;
riproduzione abilitata reale, asserzioni database e confini gruppi richiedono ancora tutti i sei job CI. U-007
resta aperto per revisione evidenze e controlli espliciti altri browser/provider pubblici; U-020/WCAG manuale,
traduzioni semantiche e gate finali integrati produzione/lifecycle/privacy/benchmark restano distinti.

## Correzione fixture gruppi della prima 1.7.240

I sei report e sei faildump coincidono con i digest ufficiali del commit
`d427dd9de1312cc421f41fb48ce670546311d5a9` (workflow 42301083539). La prima suite completa riporta 47 scenari
passati e tre falliti; 962 step passano, tre falliscono e 107 vengono saltati. Passano i tre casi ruoli/
provider e tutti i 44 casi precedenti. I fallimenti avvengono nella creazione condivisa delle appartenenze,
prima delle asserzioni report/browser dei tre nuovi casi gruppi: quei contratti non sono ancora verificati.
PHPUnit resta 379/4076, strict 10/0/0, PHPCS pulito e Grunt/gate analisi configurati superati.

Il generatore Moodle standard `group members` risolve `group` tramite `groups.idnumber`. I gruppi si chiamano
`Group A` / `Group B`, con identificativi espliciti `A` / `B`. Le tre righe di appartenenza devono quindi
usare `A` per student1/reviewer1 e `B` per student2. La correzione cambia solo queste tre celle e la
documentazione. Nomi, override ruoli/capability, riproduzione reale, ogni azione/asserzione/timeout e conteggi
50/1072 restano invariati. Nessuna modifica a produzione, helper PHP, schema, lingue o versione. La CI
corretta sui sei ambienti deve eseguire le asserzioni report prima saltate, prima del merge.

## Correzione rifiuto atteso e righe filtrate 1.7.240

Il commit `ccbe192f5b746dc5e12b6e8675e39caf9fd9f9e1` (workflow 42358541350) corregge gli id delle fixture.
I sei run completi arrivano a 1.061 step passati, tre falliti e otto saltati, con 47/50 scenari passati.
PHPUnit 379/4076, strict 10/0/0, PHPCS/lint, Grunt e gate analisi configurati passano. Coincidono dodici
digest ufficiali report/faildump. L’analisi offline dei 54 HTML conferma 36 pagine di rifiuto atteso e 18
pagine filtrate positive, inclusi i retry del framework: non sono 54 scenari browser indipendenti.

Le due richieste vietate restituiscono effettivamente `invaliduser`, senza selettore, tabella progressi o
nome dello studente escluso. Il controllo automatico Moodle dopo lo step fallisce subito su quella pagina,
prima della successiva asserzione testuale generica. Lo step atomico dedicato visita ora lo stesso endpoint
HTTP reale, richiede un solo box fatale con messaggio atteso e codice documentale `invaliduser`, verifica
assenza di selettore/tabella/nome studente e avvisi PHP/debug, poi riapre il normale report Tutti autorizzato
prima di terminare. Nessun hook, tag, criterio eccezioni o protezione produzione viene cambiato o disattivato.
Errore diverso, risposta con dati o avviso aggiuntivo fanno ancora fallire lo step. Le successive asserzioni
su selettore esatto e nomi esclusi verificano che il report ripristinato mantenga lo stesso scope.

Per il revisore con accesso a tutti i gruppi, il selettore mantiene correttamente entrambi gli studenti
autorizzati dopo il filtro. La pagina acquisita seleziona l’utente richiesto e mostra solo lui nella tabella.
Si mantengono le asserzioni esatte sulle due opzioni e si richiedono separatamente id selezionato, una sola
tabella con caption Per studente localizzata, una sola riga e nome corrispondente (con sola email propria
facoltativa). Si verificano entrambi gli studenti filtrati; anche il dettaglio positivo del revisore limitato
usa questo controllo di riga. Un nome presente solo fra le opzioni non viene scambiato per dati di visione.

La correzione aggiunge due metodi in sola lettura/navigazione conservando corpi dei metodi precedenti,
riproduzione e fixture permessi. Restano cinquanta scenari; due asserzioni aggiuntive portano a 1.074 step.
I controlli offline HTML/sorgente non eseguono PHP/Moodle né il ripristino atomico; servono sei job CI nuovi
per confermare la correzione completa prima del merge. Produzione, schema, lingue, AMD e versione
1.7.240 / 2026110603 restano invariati.
