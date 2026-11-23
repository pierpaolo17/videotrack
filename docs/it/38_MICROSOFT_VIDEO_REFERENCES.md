# Riferimenti video Microsoft riutilizzabili

VideoTrack **1.7.269** (`2026112300`).

## Ripetere una diagnostica video personale

La verifica connessione Microsoft può riusare l’identità del video al posto di un
URL anteprima temporaneo. È una diagnostica amministrativa, non una sorgente
selezionabile nell’attività. Non salva ancora riferimenti Microsoft nelle attività
e non risolve l’avvio del player per gli studenti.

1. Configura e collega il tuo account aziendale/scolastico personale seguendo la
   [guida alla connessione](35_MICROSOFT_USER_CONNECTION.md).
2. Apri **Verifica connessione Microsoft**, scegli il tuo account Microsoft collegato
   e mantieni **Selezione del video → Inserisci gli ID di drive e video**. Inserisci
   gli ID espliciti, scegli metadati, pagina originale o anteprima e invia.
3. Dopo un risultato video personale riuscito, copia il **Riferimento video
   riutilizzabile** dal campo separato di sola lettura. Contiene identificatori,
   non un URL anteprima o una concessione di accesso. Mantienilo privato; non
   incollare URL anteprima privati o credenziali nei report, nei materiali didattici
   o nel riferimento.
4. In seguito seleziona **Usa un riferimento video riutilizzabile**, incolla
   l’identità, scegli il tuo account personale e l’azione desiderata e invia.
   Il POST esplicito ripete gli stessi controlli OAuth/accesso/video e ottiene un
   risultato nuovo. Aprire normalmente la pagina non effettua richieste Graph.

La modalità manuale resta predefinita e mantiene i metadati del solo drive e la
scelta esplicita dell’account di sistema. La modalità riferimento richiede sempre
un video e l’account personale corrente. I campi manuali inattivi non prevalgono
sul riferimento; tornando alla modalità manuale valgono gli ID inseriti esplicitamente.

## Formato e limiti dell’identità

Esempio con identificatori sintetici:

```json
{
    "version": 1,
    "provider": "microsoft",
    "issuerid": 7,
    "driveid": "b!ExampleDrive",
    "itemid": "ExampleVideo"
}
```

La versione del formato è distinta dalla release del plugin. L’input è una stringa
JSON di massimo4096byte e profondità4. Sono accettate solo queste cinque proprietà:
versione1, provider `microsoft`, ID intero positivo del servizio OAuth locale e due
ID opachi non vuoti con alfabeto Graph e limite256caratteri già adottati. Campi
aggiuntivi, URL, credenziali, percorsi, altri provider e versioni non supportate
sono rifiutati localmente. Spazi JSON ordinari e ordine delle proprietà non cambiano
l’identità.

Il numero del servizio indica un issuer Moodle core su questo sito; non è un ID
tenant né una configurazione connessione portabile. Se cambia l’issuer selezionato,
il riferimento viene rifiutato prima di OAuth/Graph. Ripristina il servizio voluto
oppure verifica gli ID nel nuovo servizio. Modificare un issuer sul posto conserva
il suo ID locale: il riferimento non vincola tenant, endpoint, scope o account
personale corrente. Ogni verifica applica comunque i controlli esistenti su
configurazione, endpoint e account.

Microsoft documenta indirizzamento per ID conservato nelle rinomine e negli
spostamenti dell’elemento. Questa implementazione mantiene la coppia drive/elemento
esplicita; non cerca file cancellati/ricreati, copie o risorse trasferite in un altro
drive. Non deduce un video sostitutivo dal nome del file o da un URL fallito.

## Risoluzione nuova e accesso

Il controller richiede `moodle/site:config` di sistema, POST validato e sesskey
prima di risolvere il riferimento. Il form ricontrolla l’issuer selezionato nella
conversione ai campi del probe esistente. Possedere il riferimento non sostituisce
capability amministrativa, account personale collegato o permessi del provider.
Il riferimento non può scegliere metodo HTTP, identità condivisa o endpoint.

Ogni richiesta anteprima segue GET metadati e POST JSON anteprima esistenti tramite
il client core dell’utente corrente. Il riferimento non contiene un URL precedente:
non esistono cache URL, rinnovo in background o retry automatico. Accesso revocato,
elementi cancellati, metadati invalidi ed errori trasporto restituiscono il consueto
fallimento redatto; non recuperano un precedente indirizzo anteprima riuscito.

L’export compare solo dopo `video_accessible`, `provider_link_ready` o `preview_ready`
su un video personale. È escapato in un campo separato di sola lettura; il JSON
del risultato probe continua a omettere ID drive/elemento, URL e credenziali. La serializzazione
JSON ordinaria dell’oggetto riferimento non espone i suoi campi privati:
`encode()` è il confine esplicito dell’export amministrativo.

Nessuna nuova impostazione, tabella, archivio token o dato attività viene scritto.
Core può aggiornare i propri token OAuth quando gira il probe esistente. Microsoft
riceve gli stessi ID risorsa nella dichiarazione Privacy esistente; non si aggiunge
un nuovo trasporto provider. Il form mantiene il testo inviato solo nella propria
risposta; VideoTrack non persiste il riferimento in database o sessione.

## Prove e roadmap

Il pacchetto reale266 è accettato: CI candidato/main a sei job, canonici/lint server
194PHP, Unit560/6074 su50/53, entrambi Grunt e strict10/0/0 ciascuno. Non dimostra
esecuzione nativa267 o successo browser Microsoft reale. La267 aggiunge23metodi
unitari e tre scenari Behat locali; esecuzione nativa ancora da effettuare.
Le prove coprono parsing identità, servizio, account personale/form/CSRF, anteprime
nuove a ogni riuso, accesso negato dopo un successo e dati attività conservati.

Persistenza/backup nell’attività e risoluzione all’avvio per studenti sono lavoro
successivo. Verifica browser/iframe delegata reale, transizione Entra-header
annunciata e telemetria supportata tempo/play/pausa/seek restano gate prima del player
Microsoft tracciato. Restano i [limiti dell’anteprima privata](37_MICROSOFT_PRIVATE_PREVIEW.md).
Prima Microsoft, poi Zoom, Google Drive e App; dataset demo conservato e accessibilità
manuale5.3 **STANDBY**.

## Riferimenti primari

- [Indirizzamento driveItem per ID in Microsoft Graph](https://learn.microsoft.com/en-us/graph/onedrive-addressing-driveitems)
- [Anteprima temporanea Graphv1 e identità del richiedente](https://learn.microsoft.com/en-us/graph/api/driveitem-preview?view=graph-rest-1.0)
- [Riuso OAuth core e diagnostica personale](33_MICROSOFT_USER_ACCESS.md)
