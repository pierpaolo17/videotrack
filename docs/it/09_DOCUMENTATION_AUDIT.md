# Audit della documentazione

Albero corrente: VideoTrack **1.7.266** (`2026112001`). Baseline reale accettata:
**1.7.265** (`2026112000`). CI nativa candidato/main266 e anteprima Microsoft reale
non sono state eseguite qui.

## Sorgenti265 accettati e CI

Candidata approvata `9cbf6761e63875305ed7faa0134d041c0665c7bd` e main
`dba3cd1be9c9ee94ea2ff9622cdbc2d719d45915` condividono l’albero
`4b8b075dfb7c79b617a8a7789840668e55a77e81`. Tutti485blob e modi repository
confrontati con l’albero remoto. Il pacchetto reale461file coincide con quei blob
e l’insieme export-ignore. Nessuna modifica sorgenti durante il merge.

Il [run44723999463](https://github.com/pierpaolo17/videotrack/actions/runs/44723999463)
candidato termina con sei job superati al tentativo2. Il primo5.3 perde localhost:8000;
il nuovo job completo136259892993 supera67/1327 senza rerun interno e tutte tre
le genealogie lifecycle. Il primo5.1 fallisce la visibilità della nota dopo rollback
e supera il rerun interno di uno scenario/43step. La265 conserva il dump del primo
errore. Causa dell’interruzione e correzione prodotto della nota non dimostrate;
il rerun riuscito non cancella le prove iniziali.

Il [run44743669261](https://github.com/pierpaolo17/videotrack/actions/runs/44743669261)
main supera tutti sei i job al tentativo1: Unit530/5612, Behat67/1327 senza rerun
interni, strict10/0/0 e Grunt ciascuno. Coincidono i sei digest ufficiali dei report.
Superati canonici191PHPdistribuiti+1helper, PHPDoc, metadati,206savepoint e Mustache.
PHPMD conserva31rilievi revisionati di baseline. PHPStan/Psalm senza errori configurati;
restano750/778informazioni Psalm non classificate. Il main5.1 pulito non dimostra
una nuova correzione sorgenti. Su50/53MariaDB passano tre genealogie lifecycle
storica/parziale/precedente, con sei hash snapshot ricostruiti autonomamente e126prove
effettive dei contenuti dei file condivisi.

## Accettazione pacchetto265 reale e server

`videotrack-1.7.265.zip` reale:1.953.818byte,461file/191PHP, commento archivio main
dba3cd1b accettato. SHA256:
`692451bf677f2f7a6f7f4d2868f1964651431f6625947ad098761c8623533f24`.
Report `report_completo_videotrack_1.7.265_20261120_151810.zip` SHA256:
`4630a0b67cb7f5b096cc0f18a20ed4a72951b87782c49c719a648793e00295a1`.

Superati otto esiti richiesti. Canonici/lint condivisi su codice identico sono una
sola esecuzione su192PHP, zero errori/warning PHPCS e stderr vuoto. Unit530/5612
superati separatamente su Moodle5.0.11 e5.3+, PHP8.3.6/MariaDB11.4.14. Entrambi
Grunt completati; hash build registrato prima/dopo invariato:
`ac160febbcd2a2cacabe5ac1b95e5263d04db57d5aa298af2e6c59aef5ccb0cb`.
Tutte48mappe dello ZIP coincidono autonomamente coi sorgenti. Nessuna ricostruzione
autonoma dell’hash aggregato build server dichiarata.

Strict maintainer10pass/0warn/0fail ciascuno, installato/file2026112000, elementi/
righe voto0/0 su5.0 e34/34 su5.3. Coerenti XMLDB7tabelle/151campi/22indici espliciti/
22chiavi esterne,9AJAX,8×1040chiavi lingua e48gruppiAMD. Accettazione265 completata.

## Implementazione266 corrente

Un’azione amministrativa POST/sesskey esplicita richiede l’anteprima Microsoft
nativa con l’identità aziendale/scolastica collegata dell’utente corrente. GET
normale locale; identità di sistema rifiutata per l’anteprima. Controlli personali
e creazione client condivisi con metadati e pagina originale. Il dispatch interno
restituisce coppie diagnostica/URL, incapsulate da ogni metodo pubblico nel proprio
tipo filtrato. Contratti metadati/pagina e CLI di sistema conservati.

Serve un GET metadati video valido prima di un POST JSON Graphv1 a host fisso con
stessi ID drive/elemento. Entrambe le richieste mantengono TLS, timeout circoscritti,
assenza redirect/retry. Parsing anteprima con limiti1MiB/profondità16 dopo ricezione.
Solo getUrl HTTPS supportato conservato privatamente; risultati soloPOST, URI
insicuri, host non supportati e percorsi non embed non possono diventare iframe.
Il JSON omette URL temporaneo e campi provider grezzi. La risposta protetta non
memorizzabile in cache usa attributi iframe escapati, titolo, larghezza responsiva,
no-referrer e sandbox circoscritta. Nessun riferimento attività o URL anteprima
persistito, nessun import/proxy contenuti, nessuna prova studente di visione/voto/
completamento creata.

La Privacy API dichiara ora i trasferimenti diagnostica/anteprima Microsoft tramite
ID risorsa, autorizzazione core, URL temporaneo e dati tecnici browser. Riepiloghi
privacy abbinati e otto pacchetti lingua descrivono il confine. La [guida anteprima
privata](37_MICROSOFT_PRIVATE_PREVIEW.md) documenta forme URL supportate, accesso del
richiedente e verifiche reali.

Scritti trenta nuovi metodi unitari nativi e tre scenari browser. Un precedente
test controller della pagina originale viene circoscritto esplicitamente al proprio
ramo di rendering link: conserva il confine senza iframe mentre il ramo anteprima
separatamente protetto aggiunge il frame. Restano identici gli altri529metodi nativi
precedenti e tutti67scenari browser espansi. Nessuna asserzione o regola CI eliminata
per consentire la nuova azione.

Conteggi:490file repository/466distribuiti,193PHPdistribuiti,1426funzioni PHP nominate
(1419distribuite+7helper),654elementiAMD,560metodi unitari,70scenari espansi,
8×1050chiavi lingua e48gruppi sorgente/build/mapAMD. Dieci nuove chiavi per lingua:
sei anteprima e quattro metadati Privacy; avviso diagnostico aggiornato.

Il controllo anteprima riconosce credenziali query non vuote; indirizzi con
autenticazione semplice/sconosciuta producono preview_authentication_unverified
senza iframe. La transizione Entra-header dal1aprile2027 è documentata, non implementata.

## Prove e gate rimanenti

Controlli locali lessicali/sorgenti/API, inventari/link/lingue, contratti conservati
e applicazione/inversione pulita della patch sono distinti dall’esecuzione PHP.
Nessun interprete PHP o Moodle installato disponibile qui; PHPCS/lint, PHPUnit,
Behat, analisi statica e server266 nativi ancora da eseguire. Risposte HTTP dei nuovi
test sintetiche; scenari browser senza tenant reale. Prima dell’accettazione266
servono sei job candidato, sei main, ZIP/server reale e due strict.

Nessun ritorno Microsoft collegato con browser reale o iframe nativo riuscito
dimostrato. Anteprima pronta significa indirizzo validato, non riproduzione browser,
diritti studente sulla risorsa o telemetria supportata di tempo/play/pausa/seek.
Questi contratti reali/provider e riferimenti stabili all’avvio restano gate prima
del player Microsoft tracciato. Prima Microsoft, poi Zoom (repository Moodle
separato opzionale), Google Drive e infine App.

Schema/upgrade, AJAX, AMD, disponibilità sorgenti, collegamento OAuth, regole CI,
Analytics di produzione e demo conservati. Accessibilità manuale5.3 ancora **STANDBY**.
Debito degli harness Node storici e instabilità della nota non dichiarati risolti.
