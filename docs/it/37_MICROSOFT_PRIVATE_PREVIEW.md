# Anteprima diagnostica Microsoft privata

VideoTrack **1.7.267** (`2026112100`).

## Scopo e accesso

La verifica connessione Microsoft offre ora **Anteprima con il player Microsoft**.
È una diagnostica amministrativa con l’account Microsoft aziendale o scolastico
dell’utente corrente, per SharePoint/OneDrive for Business. Non è una sorgente
selezionabile nell’attività né un player studente tracciato. L’indirizzo temporaneo
può portare l’accesso del richiedente: mantenerlo privato e non inserirlo in URL
delle attività, materiali didattici o segnalazioni di problemi.

La pagina richiede `moodle/site:config` a livello di sistema. Il modulo richiede
POST, sesskey, **Il mio account Microsoft collegato**, ID drive e ID elemento video.
Non sostituisce mai l’identità personale con l’account di sistema. Il normale GET
della diagnostica resta locale e non produce richieste Graph né iframe di anteprima.

## Procedura

1. Configurare HTTPS e il servizio OAuth Microsoft esistente di core, quindi
   selezionarlo in VideoTrack. Collegare il proprio account tramite l’[azione
   dedicata](35_MICROSOFT_USER_CONNECTION.md). Core conserva credenziali, scope e
   rinnovo. Il permesso delegato minimo documentato è `Files.Read`; VideoTrack
   non aggiunge scope né concede accessi.
2. Aprire **Amministrazione del sito → Plugin → Moduli attività → VideoTrack →
   Verifica connessione Microsoft**. Selezionare l’anteprima e l’account collegato;
   inserire gli ID espliciti di drive/video, non URL di condivisione o percorsi.
3. Inviare. Il successo indica un indirizzo temporaneo supportato, non dimostra
   che il browser abbia caricato o riprodotto il video. Provare l’esperienza reale
   usando i controlli incorporati del provider.
4. Se l’indirizzo scade, inviare nuovamente gli stessi ID. Ogni richiesta genera
   un nuovo indirizzo; nessun rinnovo in background. L’azione per la pagina originale
   rimane disponibile separatamente.

Microsoft documenta il supporto per SharePoint/OneDrive for Business, non per gli
account Microsoft consumer. Regole tenant, cookie del browser, policy frame e
sandbox possono impedire riproduzione o login. La prova con tenant/browser reale
è ancora da svolgere. Un iframe vuoto o bloccato non autorizza un ripiego su identità
condivisa, download o proxy streaming Moodle.

## Confini di richiesta e credenziali

Il controllo personale verifica capability, ID, configurazione locale, endpoint
identità supportati e login prima di Graph. Metadati e pagina originale conservano
i contratti pubblici dei risultati. Il dispatch condiviso riceve un’azione interna
scelta da un metodo del servizio, mai un metodo HTTP arbitrario dalla richiesta.

L’anteprima esegue prima il GET metadati video esistente: elemento non eliminato,
video con durata/dimensione utilizzabili. Solo il successo consente un POST JSON
con corpo `{}` a `https://graph.microsoft.com/v1.0/drives/{driveId}/items/{itemId}/preview`,
con stessi ID e client autenticato. Nessun retry Graph, redirect, richiesta contenuto,
concessione di condivisione o enumerazione drive. Ogni richiesta Graph mantiene TLS
verificato e timeout5secondi connessione/15secondi richiesta. Il rinnovo OAuth segue
le regole core e può aggiornare le sue credenziali.

Il parsing JSON anteprima ha limite1MiB e profondità16 dopo la ricezione; non è un
limite streaming alla risposta. Supportato solo `getUrl`. Un risultato solo POST
produce `preview_get_unavailable`, senza inviarne modulo o parametri. Corpo provider,
errori grezzi, token e URL non vengono copiati nel JSON diagnostico.

Il risultato privato accetta URL HTTPS di massimo8192byte, senza credenziali URI,
fragment, spazi, caratteri di controllo, backslash o porte diverse da443. Destinazioni
supportate: tenant `*.sharepoint.com` con percorso finale `/embed.aspx`, oppure
esattamente `www.onedrive.com/embed`. Altre forme producono `preview_url_unsupported`.
Questa prima implementazione circoscritta non promette compatibilità con ogni futuro
formato URL Microsoft. Byte opachi e ordine query accettati sono conservati; parametri
query assimilabili a credenziali sono ammessi solo in questo risultato privato.
Il risultato della pagina originale conserva il rifiuto esistente di query bearer.

L’URL compare solo nella risposta iframe dell’amministratore richiedente. Pagina
non memorizzabile in cache e policy no-referrer. L’iframe usa attributi escapati,
titolo descrittivo, larghezza responsiva e sandbox con script, propria origine,
moduli e popup; nessuna autorizzazione alla navigazione superiore. Nessun token
Graph viene inviato manualmente a codice browser. Questa implementazione non salva
URL anteprima in VideoTrack, impostazioni core, marker di sessione, export o log.
Non dichiara eliminati traffico browser/provider o log del server. La serializzazione
JSON del risultato privato restituisce solo diagnostica allowlist, senza il suo URL.

La Privacy API dichiara i trasferimenti Microsoft diagnostici/browser: vedere il
[riepilogo privacy](../../PRIVACY_IT.md). Core continua a gestire le credenziali OAuth.

## Transizione dell’autenticazione anteprima

Microsoft ha annunciato modifiche all’autenticazione URL anteprima dal
**1aprile2027**. Gli indirizzi restituiti possono richiedere un header Authorization
Entra con audience corretta. Questo iframe non invia tale header. Mostra solo URL
con credenziale incorporata riconosciuta; gli altri producono
`preview_authentication_unverified` senza frame. Una chiave riconosciuta non prova
che la credenziale funzioni. La diagnostica non implementa né certifica il nuovo
modello: per quei casi usare la pagina Microsoft originale.

Il riconoscimento verifica valori query non vuoti di `tempauth`, `access_token`,
`authkey`, `token`, `sig` o `signature`, senza ricostruire URL o mostrare valori.
Forme sconosciute o vuote non sono considerate utilizzabili anonimamente. La nuova
autenticazione browser con audience corretta rimane un gate esplicito.

## Esiti e gate rimanenti

| Esito | Significato |
|---|---|
| `preview_ready` | Indirizzo GET temporaneo supportato disponibile; riproduzione browser da verificare. |
| `preview_authentication_unverified` | Destinazione supportata, autenticazione non riconosciuta; nessun iframe reso. |
| `preview_get_unavailable` | Nessuna anteprima GET utilizzabile; quelle solo POST non sono supportate. |
| `preview_url_unsupported` | L’URL restituito non supera i controlli circoscritti di destinazione/sintassi. |
| Errori configurazione/OAuth/Graph esistenti | Correggere account/risorsa/servizio; nessun ripiego sull’account di sistema. |

`preview_attempted` indica il tentativo del POST Graph. Il campo conservato
`playback_implemented: false` si riferisce al player Microsoft tracciato di VideoTrack.
L’iframe non aggiunge prove di segmenti, avanzamento, completamento, voti o interazioni.
Analytics di produzione, demo,48gruppiAMD,9AJAX e XMLDB sono conservati.

Trenta nuovi metodi unitari nativi coprono confini URI, redazione, trasporto con
metadati prima dell’anteprima, errori HTTP/parsing/soloPOST, permessi personali,
assenza ripiego condiviso, conservazione dati, modulo/CSRF e controller. Il test
precedente della pagina originale verifica ora il proprio ramo di rendering link,
conservandone il contratto senza iframe. Tre nuovi scenari Behat coprono GET locale,
rifiuto modulo e configurazione assente senza traffico provider reale. Non dimostrano
un iframe reale riuscito. CI candidato/main e test server nativi266 accettati;
il successo tenant/browser reale resta da verificare.

Seguono prova delegata reale browser/anteprima, riferimenti stabili nell’attività e
risoluzione all’avvio, quindi telemetria provider supportata di tempo/play/pausa/seek
prima del tracciamento studenti. Prima Microsoft, poi Zoom, Google Drive e App.
Accessibilità manuale5.3 in standby; conservare il dataset demo.

## Riferimenti primari

- [API preview Graph v1.0, account supportati e identità richiedente](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
- [URL opachi itemPreviewInfo e alternative GET/POST](https://learn.microsoft.com/en-us/graph/api/resources/itempreviewinfo?view=graph-rest-1.0)
- [Esempio SharePoint Embedded e indicazioni di sicurezza](https://learn.microsoft.com/en-us/sharepoint/dev/embedded/build/preview-files) — esempio specifico Embedded; qui non si creano container Embedded.
- [POST curl core50 fissato](https://github.com/moodle/moodle/blob/744cc0c19013e7be831dca24fafd6501d7eb8348/lib/filelib.php)
- [POST curl core53 fissato](https://github.com/moodle/moodle/blob/aa930e26fbdd08dd8ba70f53298838a8d306ee57/public/lib/filelib.php)

- [Annuncio Microsoft transizione autenticazione preview,8ottobre2026](https://devblogs.microsoft.com/microsoft365dev/pre-authenticated-tempauth-urls-are-retiring-move-to-microsoft-entra-id-tokens-before-april-1-2027/)

La267 può [riusare un’identità diagnostica stabile](38_MICROSOFT_VIDEO_REFERENCES.md)
per richiedere risultati nuovi. Persistenza attività e risoluzione avvio studente restano gate successivi.
