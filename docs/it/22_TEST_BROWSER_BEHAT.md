# Automazione browser Behat

Il plugin distribuisce un generator attività Moodle in `tests/generator/lib.php`, step componente in
`tests/behat/behat_mod_videotrack.php` e feature in `tests/behat/`. La suite corrente contiene
**27 scenari e 417 step attesi dal sorgente** per la 1.7.235. La baseline 1.7.234 accettata supera 24 scenari e
357 step su tutti i sei job CI. I tre casi aggiuntivi richiedono la CI reale della candidata: i conteggi sorgente
non dichiarano che i nuovi scenari siano già passati.

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

## Matrice di copertura U-007

| Contratto | HTML5 | YouTube | Vimeo |
|---|---|---|---|
| Play/pausa e scrittura terminale accettata | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Resume, seek indietro, seek avanti bloccato/frontiera | Esistente | Caso SDK double esistente | Caso SDK double esistente |
| Riproduzione senza seed fino al completamento nativo | Nuovo caso 1.7.235, CI attesa | Nuovo caso 1.7.235, CI attesa | Nuovo caso 1.7.235, CI attesa |
| Seek avanti consentito e interazioni dopo rollback | Casi HTML5 esistenti | Revisione parità residua | Revisione parità residua |
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
