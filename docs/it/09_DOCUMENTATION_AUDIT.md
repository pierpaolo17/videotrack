# Audit della documentazione

Candidata corrente: VideoTrack **1.7.274** (`2026112600`).

## Baseline accettata

Lo ZIP effettivo 273 SHA256 1f9f7fd2deeb01a7d374448e7fa80aabd33572229c9bee0cdb4c3d8e4327797f
contiene 496 file identici all’export Git di main e3097257, albero e413a886. Run candidata
45837567111 e main 45846097127 superano sei job: Unit 645/6663, Behat 82/1541 senza retry
interni, JS 38+harness, strict 10/0/0, 48 hash sorgenti/96 build nativi ed entrambi i
lifecycle popolati. Ricostruiti sei hash di conservazione. Il server PHP osservabile
resta attivo durante i test e termina soltanto nella pulizia voluta. Le cause dei
precedenti arresti HTTP restano da dimostrare: non si dichiara risolta la causa
soltanto perché le nuove esecuzioni passano.

Il report server effettivo SHA256 126163137969f410ba105dc1e1348d0a42d3a27ede7a70bfc836458aef9262c0
supera gli otto esiti richiesti: canon/lint condivisi 208 PHP, Unit 645/6663 su 50/53 e
Grunt nativo su entrambi senza variazioni dei build. Il totale 208 include l’helper
lifecycle solo repository; la baseline installabile ha 207 PHP. Strict 10/0/0 su entrambi.
Il maintainer conferma che tutte le prove manuali durata/pulsanti/banner funzionano
come previsto. La 273 è accettata. Resta non bloccante l’avviso Browserslist sui dati
datati; le informazioni e gli avvisi degli strumenti statici non sono dichiarati zero.

## Ambito e verifiche 274

Vedere [regole copie/CSV/ricalcolo](44_ACTIVITY_COPY_CSV_MAINTENANCE.md). Il Forum è
risolto dopo tutte le mappature; il riuso nello stesso corso richiede lo stesso sito.
I nomi delle velocità decimali sopravvivono al parsing POST PHP. Valori scalari e
preferenze CSV disabilitate sono conservati senza autorizzare dati al download.
Sono espliciti i permessi di profilo/identità/campi nascosti e il motivo per campo.
Casella e parametro del recupero storico sono ritirati; restano ricalcolo ordinario
e righe già accettate. Non si aggiungono migrazioni, riscritture storiche, bypass di
voti/completamento, cambi player o operazioni sulle credenziali. Le copie già errate
richiedono correzione manuale.

Aggiunti 11 metodi PHPUnit e 2 scenari Behat, mantenendo le precedenti asserzioni sui
permessi. Il test di recupero verifica anche le righe accettate nel ricalcolo normale.
Non c’è un runtime PHP/Moodle locale: PHP/PHPCS/PHPUnit e risultati browser 274 sono
ancora da eseguire, non dedotti dalla precedente release. I controlli locali coprono
struttura dei token, identità sorgenti/asset, lingue, schema, inventari e patch.
Tutti i 48 set AMD sorgenti/build/mappe sono identici allo ZIP accettato: non serve un
ulteriore artefatto Grunt per emettere questa candidata con modifiche solo PHP.

Conteggi: 530 file repository/501 distribuiti, 210 PHP repository/209 distribuiti,
1617 dichiarazioni PHP (1610 distribuite), 668 AMD, 656 metodi unitari, 84 scenari Behat
espansi/1571 passi, 8 lingue x 1080 chiavi, 48 set AMD. Schema 7 tabelle/152 campi/22 indici/
22 chiavi esterne e 9 metodi AJAX invariati. Prima dell’accettazione della 274 servono
esiti effettivi CI, entrambi i lifecycle, server e prove manuali. HTTPS/Microsoft
restano un percorso separato.

## Prima CI 274 e candidata correttiva

Il [run 45866268225](https://github.com/pierpaolo17/videotrack/actions/runs/45866268225) verifica commit 1773d14b, albero a69be2cd.
Tutte le sei matrici superano Behat 84/1571 senza retry, JavaScript 38+harness,
strict 10/0/0 e Grunt nativo. Gli hash sorgenti/build coincidono con la candidata
e i sei artefatti nativi separati abbinati. Il server PHP resta attivo fino alla
pulizia voluta. La suite di 656 test ha due failure su 50/51 e una su 52/53.
Code Checker segnala 12 errori di formattazione nei due nuovi file di test.
Entrambi i lifecycle sono saltati. PHPStan/Psalm non risolvono i due simboli legacy.

La correzione usa il separatore POST esplicito, sovrascrive il permesso già negato
nella fixture, corregge la formattazione e carica la vera API profili per entrambi
gli analizzatori. Nessuna soppressione aggiunta. La revisione del sorgente corregge
anche il confronto stretto intero/stringa dei campi completamente nascosti; il
test esteso verifica che un permesso sul solo corso non li renda disponibili.
Questa correzione non cambia tracciamento, completamento, recupero o UI browser.
Conteggi e versione invariati. Disponibili i controlli locali su token, inventari
e patch; risultati nativi corretti ed entrambi i lifecycle popolati ancora attesi.
