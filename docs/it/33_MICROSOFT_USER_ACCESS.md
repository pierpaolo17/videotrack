# Metadati Microsoft con l’identità dell’utente corrente

La 1.7.263 offre anche un’azione esplicita per [la pagina originale Microsoft](34_MICROSOFT_PROVIDER_PAGE.md).
Richiede account personale e ID video. La pagina provider si apre separatamente;
VideoTrack non accredita la visione in quella scheda.

VideoTrack **1.7.263** (`2026111900`).

## Scegliere esplicitamente l’identità

Aprire **Amministrazione del sito → Plugin → Moduli attività → VideoTrack → Verifica
connessione Microsoft**. La pagina resta riservata a `moodle/site:config` nel contesto
di sistema. Docenti e studenti non ricevono un nuovo endpoint diagnostico o diritto di riproduzione.

**Account da verificare** offre:

| Scelta | Identità OAuth | Prerequisito locale |
|---|---|---|
| Account di sistema Moodle | Client di sistema core del servizio selezionato | Configurazione di sistema completa, incluso account di sistema memorizzato |
| Il mio account Microsoft collegato | Client core dell’utente Moodle corrente per lo stesso servizio | HTTPS/servizio/client/endpoint pronti; account di sistema non richiesto |

Il default esplicito resta sistema. I due oggetti di stato mostrati su GET sono solo
locali: non verificano il login personale e non creano client. Le CLI `provider_status.php`
e `provider_probe.php` conservano il significato di account di sistema. Nessuna nuova
opzione CLI o impostazione persistente VideoTrack per la scelta dell’account.

## Riutilizzare la connessione Moodle esistente

Configurare il servizio secondo la [guida Graph](30_MICROSOFT_GRAPH.md). Se l’account
personale non è collegato, usare il flusso repository Microsoft/OAuth di Moodle con
lo **stesso issuer**, poi tornare alla pagina. La connessione deve già possedere i
permessi necessari sulla risorsa. VideoTrack non aggiunge scope, schermate di consenso,
URL di login o archivi di token e non collega l’account di un altro utente Moodle.

Solo un POST esplicito valido con sesskey avvia la verifica scelta. La validazione
degli ID opachi drive/file resta invariata. Il percorso personale controlla configurazione
ed endpoint Microsoft supportati prima di chiamare `core\oauth2\api::get_user_oauth_client`.
L’URL di ritorno è la pagina locale fissa; gli scope aggiuntivi sono vuoti. Il rinnovo
utente core è abilitato e core gestisce sessione e refresh token. Il codice non legge
archivi privati di token di altri plugin. Il controllo login può rinnovare, ruotare o
invalidare credenziali utente core: non è un’operazione globalmente di sola lettura sul DB.

Se il client non è collegato, l’esito è `user_oauth_not_connected` senza tentare Graph.
Con client valido si usa il singolo GET limitato ai metadati già esistente. Un403 resta
`graph_forbidden`: non viene ritentato con l’identità di sistema. Accesso personale
assente, scaduto o negato non autorizza mai il ripiego sull’account condiviso.

Gli esiti mantengono `account_context: current_user` (oppure `system` per la verifica
web condivisa), fase configurazione/OAuth/Graph e metadati/stati ridotti esistenti.
Non restituiscono nomi, ID risorsa, token, URL di download o testo grezzo delle eccezioni.
JSON di stato ed esito con escape e pagina non memorizzabile in cache. Nessuna modifica
a record VideoTrack, demo, file dei contenuti, voti o progresso di visione.

## Cosa prova e cosa resta aperto

Un esito personale positivo prova soltanto che **l’identità Microsoft di questo
amministratore corrente** legge i metadati in quel momento. Non verifica un altro
studente, non concede accesso, non aggiunge OneDrive/SharePoint al selettore, non rinnova
un URL di riproduzione e non valida la telemetria del player nativo. Una preview Graph
agisce con l’identità del chiamante: il successo condiviso non autorizza gli studenti.

Il futuro accesso studente richiede propri controlli autenticati su attività e
partecipazione, identità provider, autorizzazione risorsa e contratto player misurabile.
Resta preferita la distribuzione provider → browser; Moodle non importa né trasmette il
file. Microsoft precede Zoom e Google; l’App resta successiva nella roadmap.

La diagnostica personale262 ha superato6job candidato/main, Unit488/5267, Behat63/1287
e i gate reali server/strict. La gestione del link263 è distinta, con prove native e
provider reale da svolgere. Conservare la demo; manuale53 in standby.

## Contratti core primari

- [Factory OAuth utente Moodle5.0](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/api.php)
- [Factory OAuth utente Moodle5.3](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/classes/oauth2/api.php)
- [Identità token e rinnovo utente nel client core](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/client.php)
- [Avvertenza identità del chiamante nella preview Graph](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
