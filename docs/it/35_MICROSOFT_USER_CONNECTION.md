# Collegare l’account Microsoft dell’amministratore

La266 aggiunge un’[anteprima diagnostica privata](37_MICROSOFT_PRIVATE_PREVIEW.md) con l’account aziendale/scolastico collegato dell’amministratore corrente.
CLI metadati e azione pagina originale conservano i propri contratti; player studente tracciato
e validazione Microsoft reale con browser restano gate distinti.

VideoTrack **1.7.267** (`2026112100`).

## Procedura

Configurare il servizio Microsoft OAuth esistente nel core e selezionarlo in VideoTrack.
Il sito Moodle deve usare HTTPS. Lo stato locale personale deve risultare completo;
non richiede un account di sistema condiviso. Usare il callback OAuth standard già
registrato per Moodle, senza nuovo callback VideoTrack o nuova chiave applicativa.

Aprire **Amministrazione del sito → Plugin → Moduli attività → VideoTrack → Verifica
connessione Microsoft**, poi **Collega il mio account Microsoft**. Il pulsante compare
solo con configurazione personale locale completa. Invia un POST separato con sesskey:
aprire la pagina non crea client e non avvia autenticazione.

Core controlla la connessione dell’utente corrente. Se già utilizzabile, non serve
un redirect esterno; altrimenti il browser apre login/consenso Microsoft configurato
e torna attraverso il callback core. Si usano gli scope configurati dell’issuer e
il rinnovo personale core, senza scope aggiuntivi né ripiego sull’account di sistema.

Al ritorno scegliere **Account da verificare → Il mio account Microsoft collegato**,
inserire gli ID drive/file e avviare verifica metadati o pagina originale. Il default
resta sistema; la connessione non avvia automaticamente nessuna verifica. ID e campi
del modulo non vengono conservati durante il passaggio sul login esterno.

L’avviso di connessione riuscita indica un client personale utilizzabile dal core in
quel momento; non è una verifica Graph. Uno specifico video può essere negato, assente
o soggetto a policy tenant. L’identità browser della pagina Microsoft può differire:
vedere la [guida alla pagina provider](34_MICROSOFT_PROVIDER_PAGE.md).

## Confine della richiesta e del ritorno

Avvio e completamento richiedono `moodle/site:config` nel sistema. L’avvio richiede
POST/sesskey e rifiuta un codice OAuth iniettato. Configurazione locale ed endpoint
Microsoft sono verificati prima di passare l’issuer al core. Il core costruisce l’URL
di login; destinazione e campi critici state/callback/client/response vengono controllati
prima del redirect, comprese eventuali sovrascritture nei parametri login configurati.

Il marker privato di sessione contiene solo ID utente, ID issuer, scadenza e nonce
casuale di64cifre esadecimali. È utilizzabile per dieci minuti, legato alla sessione
Moodle e passato nello state locale fisso del core. Un nuovo tentativo lo sostituisce.
Il ritorno richiede sesskey e marker corrispondente prima di usare client o token; un
marker valido viene consumato prima dell’elaborazione core. Richieste errate, scadute,
di altro utente o ripetute non creano client. Al ritorno servizio ed endpoint vengono
ricontrollati: un issuer diverso non può ricevere la risposta precedente.

Il testo di errore provider viene ignorato. Un rifiuto con marker valido lo consuma
senza operazioni sui token; gli altri errori diventano esiti locali ridotti. Gli avvisi
non mostrano codici, nonce, credenziali, URL o eccezioni. Un redirect locale pulito
rimuove i parametri OAuth prima di rendere la diagnostica. Pagina non memorizzabile in
cache e policy no-referrer. Il normale traffico browser/callback core trasporta comunque
i campi del protocollo: non viene dichiarata la rimozione dei log del server web.

Core conserva i propri token di sessione/refresh e può rinnovarli, ruotarli o invalidarli.
Altri strumenti Moodle con lo stesso issuer/utente possono riutilizzare la connessione.
VideoTrack non copia token e non autentica un diverso utente Moodle. Non aggiunge logout,
disconnessione o modifiche alle impostazioni dell’issuer.

## Perimetro e verifiche

L’azione non chiama Graph, importa/proxy media, recupera un player, accredita visione,
modifica voti/completamento o abilita una nuova sorgente selezionabile. Restano JSON
diagnostici, link provider, CLI sistema,9serviziAJAX,48gruppiAMD e schema esistenti.

Ventidue nuovi metodi nativi coprono URL core reali con configurazione locale fittizia,
permessi/POST/sesskey/codici, configurazione/endpoint, monouso/utente/issuer/scadenza,
rifiuto, replay, sovrascritture protocollo ed eccezioni ridotte. I client HTTP dei
completamenti sono simulati; nessuno scambio token provider viene eseguito dai test.
Un nuovo scenario browser non ha issuer configurato né richieste esterne. Tutti508i
metodi unitari e66scenari precedenti restano identici nei byte.

Candidato corretto264 e main superano tutti sei i job, Unit530/5612 e strict10/0/0.
Il main5.1 richiede un rerun interno di scenario; il primo job5.2 perde il servizio
HTTP locale e un nuovo job completo supera67/1327 senza rerun interno. Accettati
ZIP reale459file, server Unit530/5612 su50/53, canonici condivisi192puliti e due
strict10/0/0 del maintainer. Vedere l’[audit](09_DOCUMENTATION_AUDIT.md).
Il ritorno completo Microsoft con browser reale non è ancora eseguito. La265 modifica
solo prove CI; il codice OAuth è identico nei byte alla264 accettata. Conservare demo
e manuale53 in standby.

Restano accesso delegato reale, riferimenti stabili nell’attività/risoluzione all’avvio
e autenticazione/telemetria ufficiale di tempo/play/pausa/seek prima della riproduzione
Microsoft tracciata. Seguono Zoom, Google Drive e infine App.

## Riferimenti core primari

- [API OAuth core](https://docs.moodle.org/dev/OAuth_2_API)
- [Client personale50 fissato](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/client.php)
- [URL login e gestione codici50](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/oauthlib.php)
- [Callback standard53 fissato](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/admin/oauth2callback.php)
