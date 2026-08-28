# Automazione browser Behat

Il plugin distribuisce un generator attività Moodle in `tests/generator/lib.php`, step componente in
`tests/behat/behat_mod_videotrack.php` e feature in `tests/behat/`. La suite corrente contiene
23 scenari e 342 step.

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

## Regole per selector e asserzioni

Preferire marker CSS/data stabili del componente al testo tradotto per i target interattivi; verificare separatamente
le etichette visibili. Leggere stato persistito Moodle/plugin quando il markup varia tra rami. Le asserzioni media
usano tolleranze limitate e attendono le scritture asincrone invece di sleep fissi.

## Limiti

I doppi SDK locali verificano il contratto adapter ma non disponibilità iframe pubblici, consenso, cookie o UI del
provider. Behat non sostituisce test percettivi con tastiera/screen reader né smoke su provider reale. I risultati
valgono soltanto per albero plugin, ramo Moodle, browser e driver esatti registrati.
