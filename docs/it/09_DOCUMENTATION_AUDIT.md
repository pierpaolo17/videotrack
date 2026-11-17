# Audit della documentazione

Baseline: VideoTrack **1.7.259** (`2026111701`).

## Prima CI 1.7.259 e correzione circoscritta

Il run `44120652072`, commit `1b5b2ed1e941e9eb9edeea62516b364b9b8b2ba1`, fallisce
tutti i sei job sulla stessa fixture PHPUnit. I 457 blob sorgente/permessi e i digest
dei sei artifact allegati corrispondono al run ufficiale. Ogni suite esegue **448
test / 5054 asserzioni / 1 errore**: il core `persistent::update` rifiuta l'URL con
userinfo della fixture prima di raggiungere il controllo endpoint VideoTrack.
Questo non dimostra una richiesta OAuth riuscita o l'aggiramento del controllo.

Il test corretto inserisce volutamente la configurazione corrotta nel database di
test, senza chiedere all'API di scrittura validata del core di accettare un URL
illegale. Restano tutti i sette endpoint: userinfo viene respinto dalla verifica
locale (`configuration_incomplete`), gli altri endpoint personalizzati/Microsoft
non supportati come `oauth_endpoints_unsupported`. Ogni caso verifica assenza di
rinnovo OAuth e di tentativo Graph; la factory continua a far fallire il test se chiamata.

Behat iniziale supera **54 scenari / 1194 step** senza retry in ogni job; anche
Grunt e validazione rigorosa **10/0/0** passano nei sei ambienti. Il job qualità
Moodle 5.0 selezionato supera PHPCS/lint su 180 PHP distribuiti più l'helper lifecycle
repository, PHPDoc, metadati e 206 contratti upgrade/savepoint. I lifecycle sono
**saltati** dopo l'errore Unit, non superati.

PHPStan/Psalm iniziali non riportano errori nei perimetri configurati. PHPMD conta
**33** advisory, inclusi due nuovi rilievi di complessità in Graph `inspect` e
`video_metadata`. La correzione separa gli stessi controlli di decodifica limitata
e MIME video in helper privati; mantiene destinazioni/opzioni HTTP, campi e stati
dell'output. Il nuovo report nativo dovrà confermare i conteggi. Psalm riporta
**743/771** informazioni; Moodle 5.3 stampa inoltre 13 deprecazioni di alias core
durante la riflessione dell'analizzatore. Restano registrate, senza sopprimerle né
dichiarare un risultato privo di advisory.

Questa patch è **incrementale dal commit 1.7.259 fallito**, sul ramo
`release/v1.7.259`: non è la precedente patch completa 258→259. Release/versione
restano `1.7.259 / 2026111701`. Restano 457 file repository / 433 distribuiti,
180 PHP distribuiti, 448 metodi di test e 19 nuovi metodi Graph; le funzioni PHP
nominate diventano 1238 (1231 distribuite + 7 helper repository), con 654 callable AMD.

Prima del merge serve la nuova CI nativa, compresi Unit e lifecycle prima saltati.
Accettazione main/pacchetto/server e accesso Microsoft reale restano gate distinti.
La correzione non cambia schema, credenziali, scopi, player, AMD, AJAX o dati demo.

## Perimetro della candidata 1.7.259

La release aggiunge una verifica esplicita dei metadati Microsoft Graph mediante
l'account OAuth Microsoft di sistema già gestito da Moodle. Non aggiunge una sorgente
selezionabile o un adapter del player. Il comando di stato locale resta inerte. Solo
il nuovo probe può rinnovare credenziali OAuth del core ed effettuare un GET dei
metadati verso host fisso; non scarica il video e non scrive record VideoTrack/demo.

L'esecuzione nativa PHP/Moodle e l'accesso a un tenant reale restano da verificare
per questa candidata. I nuovi test simulati non richiedono credenziali né rete.
La [guida Microsoft Graph](30_MICROSOFT_GRAPH.md) spiega configurazione, output e limiti.
Dopo accettazione completa candidata/main e pacchetto reale si prosegue con riproduzione
e controlli di accesso attività/utente, poi Zoom, Google Drive e App. La prova App →
browser resta saltata; l'estensione multilingua dei sottotitoli rimane rinviata.

## Baseline 1.7.258 accettata

Il pacchetto reale da main `2df5bb3bee4b77e75a94acd61436071cc2026181` contiene 427 file;
ogni file coincide con l'albero repository accettato. SHA-256 del pacchetto:
`695166a99eb2b0b13ddc6526fa2333358d295cf3c1af6ca0d2ff8cb559878521`.

La CI corretta candidata/main supera tutti i sei job: PHPUnit **429 test / 4843
asserzioni**, Behat **54 scenari / 1194 step**, validazione rigorosa **10/0/0** e build
AMD canonici. I job MariaDB selezionati Moodle 5.0/5.3 superano upgrade/ripetizione e
uninstall popolato da installazioni storiche, interrotte e precedenti, con controlli di
conservazione. Il report server reale conferma PHPCS/lint puliti su 177 file (176 PHP
distribuiti più un helper repository), PHPUnit 429/4843 su entrambi i rami e build Grunt
invariati. Gli output rigorosi del maintainer sono 10/0/0 su entrambe le istanze.

PHPStan/Psalm non riportano errori nei perimetri configurati. Restano advisory **31**
rilievi PHPMD e **741/769** informazioni Psalm, di tipo non stampato nei report.
Non è un risultato privo di advisory. Tre vecchi script Node falliscono ugualmente
sulle 257/258 accettate: contratti sorgente Privacy/backup obsoleti e stub core/str
assente nell'harness tracker. I gate nativi passano; questi script non sono contati come PASS.

Il manuale di accessibilità Moodle 5.3 resta in standby; verifica visiva Tiles e nuova
scansione esterna di sicurezza restano aperte. Il dataset demo viene conservato.

## Contratti documentali e di release

Indici inglese/italiano, guide provider/CLI, privacy, architettura e inventari dei file
e delle funzioni descrivono la candidata. I marcatori DB/ER seguono la release senza
modificare lo schema a sette tabelle. Gli otto pacchetti lingua conservano 1006 chiavi;
i byte AMD sorgente/build/map restano identici alla 258 accettata.

Inventari e applicazione della patch sono prove sul sorgente, non PASS nativi
PHPCS/lint/PHPUnit. CI candidata, CI main, identità ZIP reale, canonici/Unit/Grunt server
e validazione rigorosa sono gate distinti. L'accesso reale Microsoft è un ulteriore gate
provider. Lo storico delle vecchie release resta in CHANGELOG e nei tag sorgente.
