# Audit della documentazione

Candidata corrente: VideoTrack **1.7.273** (`2026112501`), correzione incrementale
del commit pubblicato `ca91e4a0ce64e6b5f49954b355096cbf66c32c76`,
albero `5ced30d5fd65d29341304517531f7d3dc9d05c12`.

## Evidenze della prima CI 273

Run 45778182389, tentativo 1: cinque job riusciti. L’unico passaggio fallito è Moodle
Code Checker su 50 MariaDB: 9 errori PHPCS di formattazione su 6 righe del file
tests/form_duration_validation_test.php. In quel test cambiano solo spazi e
andate a capo; token eseguibili e tutte le asserzioni restano identici.

Tutte le sei matrici superano PHPUnit 645/6663, Behat 82/1541 senza retry interni,
38 gruppi JavaScript e tracker harness, strict 10/0/0 e Grunt nativo. I sei manifest
identificano ca91e4a0: coincidono 48 hash sorgenti, 96 hash build/mappe, contenuto
delle mappe e payload degli archivi report/AMD separati.
53 MariaDB supera upgrade storico/parziale/precedente, ripetizione e disinstallazione
popolata; ricostruiti indipendentemente tre hash di conservazione. Il lifecycle
50 MariaDB non è partito dopo l’errore qualità e resta un gate della correzione.

PHPStan è pulito nei report 50/53. Le due annotazioni Psalm ripetono lo stesso
ParadoxicalCondition a db/services.php:25; restano 765/793 informazioni.
PHPMD riporta 43 avvisi nella prima scansione 273: non si dichiarano azzerati o
invariati rispetto alla 272. Gli esiti statici della correzione restano da verificare.

## Ambito della correzione

- Corregge i 9 rilievi di formattazione del test durata.
- Mantiene identici guardia MOODLE_INTERNAL e registro servizi. La configurazione
  Psalm limita l’eccezione ParadoxicalCondition al solo db/services.php: la guardia
  è già nota al bootstrap di analisi e genera questo falso positivo. Non si
  disattiva l’analisi globale e non si rimuove la protezione runtime.
- Rende esplicite le istruzioni TUTTE / ALMENO UNA in 8 lingue e aggiorna
  l’asserzione browser del layout al nuovo testo.
- Uniforma larghezza, tipografia e spaziatura degli avvisi richiudibili al banner
  privacy. Video, layout e strumenti mantengono il limite configurato, passato
  tramite proprietà CSS. Restano ARIA, chiusura e timeout.

Sorgenti, compilati e mappe AMD restano identici: non serve Grunt aggiuntivo.
Schema, upgrade, privacy, backup, credenziali, evidenze pregresse, tracking/replay e
policy durata sono invariati. Release/versione restano 273/2026112501 in attesa del
rilascio. Conteggi: 523 file repository / 496 distribuiti, 207 PHP distribuiti, 1591 callable
PHP, 668 callable AMD, 645 metodi unitari, 82 scenari Behat / 1541 passi, 8×1078 chiavi e 48 set AMD;
7 tabelle / 152 campi / 22 indici / 22 chiavi esterne e 9 metodi AJAX.

## Gate

Le verifiche locali su token, asset, lingue, XML e patch non sostituiscono la CI
nativa. Restano PHPCS/Psalm corretti, sei job CI, entrambi i lifecycle, server e
verifica visiva. I controlli funzionali della prima candidata non li sostituiscono.
HTTPS/account Microsoft e roadmap provider/App restano percorsi separati.
