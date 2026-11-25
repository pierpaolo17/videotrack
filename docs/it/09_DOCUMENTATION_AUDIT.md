# Audit della documentazione

Candidata corrente: VideoTrack **1.7.272** (`2026112500`), basata sullo ZIP 271 effettivo accettato.

## Baseline accettata

ZIP 271 SHA256 3bb6462e50fcfb4d59b0947322ab64a405b26c6b778f1bd8124111585b51f3f2:
490 file distribuiti corrispondono a main 99d4195f1202cf630102c252841b0ed75372b6b3,
albero ee7f092ed9f8e75460060f8e8d7e386b16e832e4. Candidato 45497048064 tentativo 2 e
main 45578816355 tentativo 1 superano sei job ciascuno: Unit 640/6618, Behat 80/1484 senza
retry interni nei report accettati, strict 10/0/0, Grunt nativo e matrici lifecycle 50/53
con aggiornamento/ripetizione/disinstallazione popolata. Sei hash di conservazione
per run accettato sono ricostruiti indipendentemente. L’indisponibilità iniziale del
server 5.3 MariaDBnon si riproduce sullo stesso codice nel tentativo accettato; la causa
iniziale resta non provata e non viene dichiarata come correzione applicativa.

Report server 20261125_105411: otto esiti superati, canonici/lint 207 condivisi puliti,
Unit 640/6618 separati su 50/53 e Grunt nativi con build invariati. Entrambi i validator
utente superano 10/0/0. Restano PHPMD 40 avvisi, Psalm un rilievo sulla guardia standard
più 763/791 informazioni; PHPStan è pulito.

## Correzione corrente e gate

La [guida replay](42_REPORT_REPLAY_STATE.md) segue URL report→seek programmatico→nuovo
PLAYING→consumo tardivo→falso riavvolgimento/velocità di recupero. Un solo controllo
nel tracker comune conserva gli eventi provider successivi già consolidati.
DB/upgrade, privacy, capability, sorgenti, tracking server e tutti i PHP salvo metadati
release restano identici alla 271. Conservati 640 metodi unitari e 80 scenari precedenti.
Restano tutte le asserzioni Node; aggiunti tre gruppi e due scenari con link reale
del report dopo reazioni ottenute tramite effettiva riproduzione della fixture.

Conteggi:519 file repository/493 distribuiti, 206 PHP distribuiti, 1582 callable PHP, 660 AMD,
640 metodi unitari, 82 scenari espansi/1541 passi, 8 lingue con 1076 chiavi e 48 gruppi AMD.
Schema 7 tabelle/152 campi/22 indici/22 foreign key e 9 AJAX invariati. Inventari aggiornati
alle posizioni effettive. I nove gruppi Node sui sorgenti e il tracker harness passano;
sorgenti/build precedenti riproducono i tre nuovi fallimenti.

Output AMD nativi verificati dal server test Moodle 5.0 del manutentore, archivio SHA256
a6ce3d08275774f01fb2cd4b350c2f07c405f0336533f445705f894ff3175eb2.
Il vero npx --no-install grunt amd termina con 0. Coincidono sorgente/fixture esatti,
48 hash sorgenti, 96 hash build, mappe canoniche, digest log e indicazione di ripristino
degli originali. Cambiano l’helper e due output; 47 sorgenti/94 output restano identici
alla 271. I nove gruppi Node passano sui sorgenti e sui compilati nell’artefatto e
dopo l’incorporazione locale, insieme al tracker harness invariato. Non vengono
inventati provenienza GitHub o versione patch Node esatta per il build del manutentore.

Il primo runner si ferma con Node 26 non supportato prima di compilare; il backup 144 file
coincide con la 271 accettata. Node 22 soddisfa il requisito >=22.11/<23 di Grunt Moodle 5.0.
Il runner verifica il ripristino del sito originale. Non vengono introdotti accessi
DB/bootstrap/credenziali. Il debito PHPMD/Psalm resta visibile. Restano PHP/PHPCS/PHPUnit/
Behat nativi 272, CI/lifecycle completi e server; non si dichiara esecuzione PHP/Moodle
locale nuova. Prove YouTube reali, HTTPS/account Microsoft e roadmap provider/App
mantengono i gate precedenti.
