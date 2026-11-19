# Diagnostica autenticata dei metadati Microsoft Graph

VideoTrack **1.7.264** (`2026111901`).

Il servizio Graph/OAuth introdotto nella 1.7.259 accettata è conservato. La 1.7.261 aggiunge un modulo amministrativo che riutilizza la stessa diagnostica; la riproduzione Microsoft resta un gate futuro.


È la prima diagnostica remota per preparare OneDrive/SharePoint. Verifica metadati
espliciti di drive/video con l'account OAuth Microsoft di sistema del core.
Non rende OneDrive/SharePoint selezionabile nel modulo, non consegna video agli
studenti e non dimostra streaming/range o i futuri permessi di riproduzione.

## Preparare la connessione core

1. Usare un sito Moodle HTTPS con callback pubblica funzionante e certificato valido.
   Un URL del sito in semplice HTTP fallisce prima del rinnovo del token.
2. Configurare in Moodle un servizio OAuth 2 Microsoft abilitato per servizi,
   collegare l'account di sistema e selezionarlo nelle impostazioni VideoTrack.
   Credenziali e token restano nel core, senza copie nelle tabelle VideoTrack.
3. L'account collegato deve poter leggere drive/file. Il permesso delegato minimo
   indicato da Microsoft per queste API è `Files.Read`. Core e altri plugin possono
   richiedere scopi aggiuntivi. VideoTrack non modifica scopi o consenso: dopo
   modifiche agli scopi core, ricollegare l'account attraverso Moodle.
4. Ottenere `driveId` e `id` del video mediante strumenti amministrativi/API Microsoft
   affidabili. URL del browser/condivisione e percorsi file non sono identificatori.
   Questa prima versione accetta 1–256 lettere/cifre ASCII e `!_-`, distinguendo
   maiuscole/minuscole. Non enumera drive, librerie, cartelle, utenti o link condivisi.

Il probe supporta il cloud pubblico: Graph `graph.microsoft.com` ed endpoint OAuth
`https://login.microsoftonline.com/<tenant>/oauth2/[v2.0/]authorize` e `.../token`.
Endpoint personalizzati/cloud nazionali, userinfo, query, frammenti e porte alternative
vengono rifiutati prima di ottenere il client core. Il comando di stato locale resta
disponibile per diagnosticare e correggere configurazioni incomplete.

## Esecuzione dalla root Moodle

Usare l'eseguibile PHP e l'utente di servizio abituali del server. Gli apici singoli
evitano l'espansione della cronologia Bash per `!` presente negli ID drive OneDrive.

```bash
php mod/videotrack/cli/provider_status.php --provider=microsoft --strict
php mod/videotrack/cli/provider_probe.php --help
php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID'
php mod/videotrack/cli/provider_probe.php --driveid='DRIVE_ID' --itemid='ITEM_ID'
```

`provider_status.php` legge soltanto la configurazione locale. `provider_probe.php` è
un'operazione di rete esplicita: il core può rinnovare token, salvare il refresh token
ruotato e aggiornare la cache. Non è quindi un comando in sola lettura per l'intero DB.
Non inserisce, cambia o elimina attività, stati, segmenti, reazioni, note, segnalibri
o record demo VideoTrack. Non viene aggiunto un probe pianificato/in background.

La risorsa riceve un solo GET Graph v1.0 dei metadati, senza retry né redirect:
`/drives/{driveId}?$select=id,driveType` oppure
`/drives/{driveId}/items/{itemId}?$select=id,file,video,size,deleted`.
Il drive esplicito evita `GET /me/drive`, che può creare il drive di un utente con licenza.
Non vengono richiesti `/content` o endpoint di condivisione. Il GET verifica TLS, con
timeout di connessione di 5 secondi e complessivo di 15. Il rinnovo OAuth usa la politica
di trasporto del core. Il limite di 1 MiB vincola il parsing JSON dopo la ricezione,
non la quantità scaricata in streaming.

## Interpretare il JSON

Esempio illustrativo, senza esecuzione su tenant reale:

```json
{
    "provider": "microsoft",
    "phase": "graph",
    "status": "video_accessible",
    "graph_attempted": true,
    "oauth_refresh_possible": true,
    "playback_implemented": false,
    "http_status": 200,
    "mime_type": "video/mp4",
    "duration_seconds": 5280.123,
    "size_bytes": 123456
}
```

La durata Microsoft è in millisecondi e viene convertita in secondi. La durata
configurata/forzata dell'attività non viene modificata. Per un drive si restituisce
solo `drive_type` (personal, business o documentLibrary) oltre ai campi comuni.

| Risultato | Significato / azione |
|---|---|
| `invalid_target` | ID assente/non supportato; configurazione e OAuth non sono stati interrogati. |
| `not_configured` / `issuer_missing` / `configuration_incomplete` | Correggere selezione locale, servizio/account di sistema o HTTPS del sito. Nessun rinnovo OAuth o GET Graph. |
| `oauth_endpoints_unsupported` | Gli endpoint configurati escono dal formato cloud pubblico supportato. |
| `configuration_failed` | Errore locale; i dettagli vengono omessi dall'output. |
| `oauth_unavailable` / `oauth_failed` | Il core non fornisce un client autenticato. Graph non è stato tentato; riparare/ricollegare l'account core. |
| `drive_accessible` / `video_accessible` | Metadati accessibili all'account di sistema in quel momento. Uscita 0. |
| `graph_unauthorised` / `graph_forbidden` | HTTP 401/403: verificare token, consenso e accesso dell'account alla risorsa. |
| `graph_not_found` | HTTP 404: risorsa assente o inaccessibile; non dimostra una cancellazione. |
| `graph_throttled` / `graph_unavailable` | HTTP 429/5xx: riprovare manualmente più tardi, senza retry automatici. |
| `graph_redirect_refused` | HTTP 3xx: nessuna richiesta successiva alla Location. |
| `graph_transport_failed` / `graph_request_failed` | Errore/eccezione curl o altro stato HTTP non riuscito. |
| `graph_invalid_response` | JSON, tipo drive o identità assenti/non validi; profondità o dimensione eccessive per il parsing. |
| `graph_item_deleted` / `graph_not_video` / `graph_video_metadata_incomplete` | Elemento eliminato, cartella/file non video o durata/dimensione intere assenti/non valide. |

Ogni diagnostica non riuscita termina con 2. Anche le opzioni sconosciute terminano con
2 senza ripeterne i valori. `--help` termina con 0 senza eseguire il probe. L'output
scarta nomi file/drive, ID risorse, identità dei proprietari/utenti, URL web/download,
corpi delle risposte e testi delle eccezioni. Non allegare token o errori provider grezzi.

## Validazione e passaggio successivo

Il sorgente aggiunge 19 metodi PHPUnit simulati: trasporto, classificazione errori,
redazione dell'output, ID/endpoint ammessi, configurazione e unità dei metadati.
Devono essere eseguiti nativamente sulla candidata. Un mock non dimostra consenso
Microsoft o riproduzione reale; qui non è stata effettuata alcuna richiesta a un tenant.

Dopo accettazione nativa, l'amministratore può eseguire il probe su drive e video di
test scelti. Conservare il JSON sicuro tra le evidenze; non servono credenziali nei
report. La riproduzione richiede ancora autorizzazione attività/utente, scadenza e
rinnovo, gestione range, seek/completion e test browser dedicati.

Fonti primarie:
- [Get drive](https://learn.microsoft.com/en-us/graph/api/drive-get?view=graph-rest-1.0)
- [Get driveItem](https://learn.microsoft.com/en-us/graph/api/driveitem-get?view=graph-rest-1.0)
- [Risorsa video e durata in millisecondi](https://learn.microsoft.com/en-us/graph/api/resources/video?view=graph-rest-1.0)
- [API OAuth Moodle 5.0 del ramo accettato](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/classes/oauth2/api.php)
- [API OAuth Moodle 5.3 del ramo accettato](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/classes/oauth2/api.php)

La 1.7.261 aggiunge una [pagina amministrativa](32_MICROSOFT_WEB_CHECK.md)
che riutilizza la stessa diagnostica. La riproduzione Microsoft resta da implementare.

Il modulo web offre anche la [verifica utente corrente](33_MICROSOFT_USER_ACCESS.md).
La CLI resta dedicata all’account di sistema: non viene introdotta un’opzione di contesto account.

L’[azione pagina provider](34_MICROSOFT_PROVIDER_PAGE.md) seleziona separatamente webUrl.
La diagnostica metadati sistema/utente e la CLI di sistema omettono quell’URL.
