# Audit della documentazione

Albero corrente: VideoTrack **1.7.268** (`2026112101`). Baseline reale accettata:
**1.7.267** (`2026112100`). CI/server nativi268 e prove tenant/browser Microsoft reali
restano da eseguire.

## Sorgenti267 accettati e CI

Candidato70194429e55a6f95036edfdd3258d33cb3c2745b e main
418475d361cfd65b06b35875288a0b1b68aa3e12 condividono l’albero
87b084b6a85fc39eaed2a70db608f05ea2331ab5; il tag annotatov1.7.267 punta a main.
Tutti495blob/modi repository e471file export coincidono col pacchetto reale.

[Candidato44908095172](https://github.com/pierpaolo17/videotrack/actions/runs/44908095172)
e [main44910680696](https://github.com/pierpaolo17/videotrack/actions/runs/44910680696)
superano sei job ciascuno al tentativo1: Unit583test/6243asserzioni, Behat73scenari/
1390step senza tracce di retry, strict10/0/0 e Grunt in ogni voce matrice. I dodici
digest artifact coincidono con quelli ufficiali. Completati canonici195PHPdistribuiti
più helper, PHPDoc, metadati,206savepoint e Mustache. PHPMD31rilievi identici alla
baseline; PHPStan/Psalm senza errori configurati. Psalm751/779informazioni ancora
non classificate: conteggi uguali non dimostrano identità dei rilievi.

I job50/53MariaDB completano genealogie storica/parziale/precedente. Per ciascun
run sei hash snapshot ricostruiti autonomamente coincidono in entrambe le verifiche
31/0; disinstallazione26/0 per genealogia e126controlli effettivi contenuti file
condivisi. Lifecycle delle altre voci saltati, non superati. Restano accettate le
correzioni266PHPCS28/Graph55; gli esiti verdi non dimostrano correzioni prodotto
per precedenti instabilità della nota browser o indisponibilità localhost.

## Pacchetto267 reale e server maintainer

ZIP `videotrack-1.7.267.zip`:2.006.796byte,471file/195PHP, commento main418475d3.
SHA256: `3fbc345220877f1449e3f9a1dd03341b4b9c29ff002637b0eeb7ff94813278dd`.
Report `report_completo_videotrack_1.7.267_20261121_153041.zip`:14.739byte,
SHA256: `ee2c03f699a0b031769981120c62c9755eeae1ab21c6540b5ac3045946034c86`.

Superati gli otto esiti richiesti. Canonici/lint condivisi su codice identico196PHP
una volta, non due esecuzioni indipendenti: PHPCS0errori/0warning, stderr vuoto e196
successi lint. PHPUnit583/6243 separatamente su Moodle5.0.11 e5.3+, PHP8.3.6/
MariaDB11.4.14. Entrambi Grunt completati; hash build registrato prima/dopo invariato:
ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb. Tutte48mappe ZIP
coincidono autonomamente coi sorgenti. Nessuna ricostruzione autonoma dell’hash
aggregato build server dichiarata.

Entrambi strict maintainer10/0/0, installato/file2026112100. Elementi/righe voto0/0
su50 e34/34 su53; XMLDB7tabelle/151campi/22indici espliciti/22chiavi esterne,9AJAX,
8×1058chiavi lingua e48gruppiAMD coerenti. La267 reale è accettata.

## Implementazione268 corrente e test

La [ricerca metadati personale](39_MICROSOFT_VIDEO_SEARCH.md) è collegata alla
diagnostica protetta esistente. POST/sesskey e capability sito precedono OAuth
personale. Drive esplicito e query UTF-8 circoscritta costruiscono un GET Graphv1
a host fisso, con escaping separato apostrofi OData e contenuto URL. Nessuna
paginazione/retry automatico o richiesta contenuti; parsing1MiB/profondità16 e
massimo20elementi provider.

Sono mantenuti solo video validi, non cancellati/cartelle/remoti, del drive atteso,
con ID sicuri unici e nomi/facet circoscritti. Candidati privati mostrati escapati
come nomi/durate/riferimenti nella risposta protetta. Il JSON diagnostico omette
candidati, query, ID, URL, corpi grezzi e credenziali; continuazione solo flag/avviso.
La verifica video successiva ricontrolla l’accesso. Core gestisce le credenziali;
non vengono creati alias/cache/import/proxy File API.

Configurazione, endpoint identità e acquisizione client personale sono condivisi
tramite prepare_client. Restano contratti pubblici metadati/pagina/anteprima, ordine
target/capability, factory/callback core e CLI di sistema. Privacy aggiunge il
trasferimento searchquery; la regressione esatta aggiunge il campo conservando
tutte le asserzioni precedenti. Altri582metodi e tutti73scenari precedenti invariati.
Nuovi25metodi unitari e3scenari Behat locali coprono query/URI/righe/pagina/redazione,
errori trasporto, guardie personali/no fallback, dati conservati, form/CSRF e output
controller. HTTP/OAuth sono simulati, non prove di integrazione reale.

Conteggi:503file repository/479distribuiti,200PHPdistribuiti,1519funzioni PHP nominate
(1512distribuite+7helper),654elementiAMD,608metodi unitari,76scenari espansi,
8×1066chiavi lingua e48gruppiAMD invariati. Otto nuove stringhe tradotte per lingua;
nessun valore precedente cambiato. Indici/inventari/Privacy/guide ed etichette ER
abbinati descrivono lo stesso albero; XMLDB/upgrade,9AJAX,AMD/policy sorgenti/demo
e Analytics di produzione invariati.

## Limiti delle prove e roadmap

Controlli locali sorgenti/lessicali/contratti, lingue/inventari/link e applicazione/
inversione patch sono distinti dall’esecuzione PHP. PHP/Moodle non disponibili
localmente; PHPCS/lint, PHPUnit, Behat, analisi e server268 nativi da eseguire.
Accettazione: sei job candidato, sei main, ZIP/server reale e strict maintainer50/53.

Ricerca tenant reale, OAuth/anteprima browser, autenticazione Entra-header e telemetria
tempo/play/pausa/seek restano gate non verificati/implementati. Nessuna sorgente/
visione studente o persistenza/backup riferimenti attività aggiunta. Prima Microsoft,
poi Zoom (repository separato opzionale), Google Drive e App. Accessibilità manuale5.3
STANDBY; conservare demo e debito harness Node storico.
