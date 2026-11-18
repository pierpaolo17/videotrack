# Completamento delle correzioni di sicurezza

VideoTrack **1.7.260** (`2026111800`).

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

La rimozione elimina fisicamente la riga della nota/segnalibro del proprietario.
Parametri e risposte AJAX restano compatibili. Login, contesto, partecipazione e sesskey
precedono i retry: un id assente è un successo senza scritture; righe esistenti di altro
utente o tipo vengono rifiutate. Un lock Moodle serializza le rimozioni e la transazione
comprende un solo evento senza testo privato. Una vecchia riga già marcata eliminata viene
rimossa senza duplicare l'evento.

Backup utente ed export Privacy escludono gli elementi personali eliminati; il ripristino
scarta quelli marcati eliminati nei vecchi XML. Strumenti attivi e storico reazioni mantengono
i propri contratti. Backup creati prima della cancellazione possono contenere copie allora
attive: la loro retention è distinta e il ripristino non può dedurre una cancellazione
successiva da una vecchia copia attiva. Nessuna migrazione generale o pulizia della demo.

Il recupero dello schema passa in db/upgradelib.php, riservato all'upgrade. Install/uninstall
conservano il recupero voti DML. Per contesti validi la disinstallazione usa
completion_info::delete_all_state; solo i moduli orfani richiedono DML di completamento
circoscritto. Voti ambigui/orfani di pre-produzione conservano il DML transazionale verificato:
proprio quelle righe malformate possono impedire alla Grade API di risolvere il contesto.

I controller usano data_submitted() con i controlli sesskey/capability esistenti. Il download
Analytics usa POST, con filtri e sesskey nel corpo; GET viene rifiutato prima dell'evento
e dell'output. Il server genera chiavi opache di ripresa per sito, studente partecipante,
modulo, sorgente e video. YouTube/Vimeo ignorano le vecchie chiavi condivise e disabilitano
lo storage per identità incomplete; HTML5 conserva la ripresa server. I suggerimenti del
browser non diventano evidenza di visione validata. L'indice pulisce l'HTML dei nomi sezione
senza ripetere i filtri: difesa aggiuntiva, non una XSS Tiles dichiarata come riprodotta.

| ID | Implementazione accettata / limite residuo |
|---|---|
| SH-01/02/03/05/08/09 | Accettate nella 257 con regressioni native autorizzazione/privacy. |
| SH-04 | Cancellazione fisica, retry serializzato, Privacy/backup/restore legacy; prove native 258 superate. |
| SH-06 | DDL solo upgrade e API completion per contesti validi; resta DML circoscritto e motivato per orfani/ambigui. |
| SH-07 | API dei dati inviati con sesskey/capability; prove native 258 superate. |
| SH-A1/A2 | Ripresa isolata e Analytics POST; Unit/Behat nativi 258 superati. |
| MS-14 | Output course-format pulito; nessuna XSS Tiles dichiarata riprodotta o prova visiva manuale superata. |
| MS-38 | Regressione transazionale superata; corpi DML conservati nella 258. |

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
