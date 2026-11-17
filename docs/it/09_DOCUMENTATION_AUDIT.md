# Audit della documentazione

Baseline: VideoTrack **1.7.259** (`2026111701`).

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
