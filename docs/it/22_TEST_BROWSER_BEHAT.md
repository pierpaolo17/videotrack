# Automazione browser Behat

Il plugin distribuisce un generator attività Moodle in `tests/generator/lib.php`, step componente in
`tests/behat/behat_mod_videotrack.php` e feature in `tests/behat/`. Il sorgente 1.7.237 contiene
**33 scenari e 612 step attesi dal sorgente**, da confermare con la CI reale della candidata. La 1.7.236
accettata supera 30 scenari / 489 step nei sei job CI della release e di main, con 369 test PHPUnit / 3.988
asserzioni. Il report server reale Moodle 5.0/5.3 supera PHPCS, lint, Grunt e PHPUnit; i due strict superano
tutti i dieci controlli. Quel report non esegue Behat. La CI precedente non certifica i nuovi casi.

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
l'URL deve riferirsi a questa attività e alla stessa sessione. Frontiera e secondi unici restano sotto 12 secondi,
lontani dal target rifiutato di 30. Completamento aggregato e nativo devono restare completi, anche al rientro
nell'attività. Non vengono inseriti registro, stato, interazioni, completamento o risposte AJAX dal test.

Il vecchio caso HTML5 con seed resta invariato e conserva il proprio scopo basato su fixture. I nuovi casi
esercitano evidenze generate dal browser per i tre provider. Ended/replay/velocità e combinazioni pertinenti
ruoli/gruppi/browser restano lavoro U-007 esplicito; questi casi da soli non chiudono la fase.

## Matrice di copertura U-007

| Contratto | HTML5 | YouTube | Vimeo |
|---|---|---|---|
| Play/pausa e scrittura terminale accettata | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Resume, seek indietro, seek avanti bloccato/frontiera | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Riproduzione senza seed fino al completamento nativo | Caso 1.7.235 accettato | Caso 1.7.235 accettato | Caso 1.7.235 accettato |
| Seek avanti consentito, gap escluso e completamento incompleto | Caso 1.7.236 accettato | Caso 1.7.236 accettato | Caso 1.7.236 accettato |
| Interazioni dopo rollback e completamento conservato | Nuovo caso senza seed 1.7.237, CI attesa | Nuovo caso SDK double 1.7.237, CI attesa | Nuovo caso SDK double 1.7.237, CI attesa |
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
