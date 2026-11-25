# Ricerca video Microsoft personale

VideoTrack **1.7.270** (`2026112400`).

## Trovare un riferimento riutilizzabile

Apri **Verifica connessione Microsoft → Cerca video Microsoft**. Inserisci un ID
drive esplicito e un testo di2–100caratteri. L’azione richiede `moodle/site:config`
di sistema, POST/sesskey validati e l’account Microsoft aziendale/scolastico personale
collegato dell’amministratore corrente. Collega prima l’account nella diagnostica
esistente. Il GET normale resta locale; nessun fallback all’account condiviso.

Ogni risultato supportato mostra nome file escapato, durata e un
[riferimento riutilizzabile](38_MICROSOFT_VIDEO_REFERENCES.md) di sola lettura.
Copia l’identità nella verifica connessione, scegli il tuo account e invia l’azione
desiderata. Metadati/pagina/anteprima ricontrollano l’accesso. Il risultato di ricerca
è un metadato provvisorio, non una concessione di accesso o una prova di riproduzione.

## Ambito e limiti

Una richiesta cerca nella gerarchia del drive indicato. Microsoft documenta
corrispondenze su nomi, metadati e contenuti e il minimo permesso delegato `Files.Read`.
VideoTrack non aggiunge scope o concessioni. Non cerca in tutti i drive, non
predispone `/me/drive` e non segue collegamenti condivisi verso altri drive.

L’URL metadati Graphv1 a host fisso seleziona i facet necessari e chiede20elementi
del provider. Diventano scelte solo elementi con ID opachi sicuri, nomi UTF-8
circoscritti, MIME video, durata positiva, dimensione intera non negativa e drive
padre atteso. Cartelle, elementi cancellati/remoti, righe malformate e ID duplicati
sono esclusi. Venti elementi non sono necessariamente venti video. La pagina può
essere vuota anche se esistono altri risultati. Affina il testo; questa pagina non
è un inventario completo del drive.

L’indirizzo di continuazione non viene seguito o restituito. Una stringa non vuota
diventa solo `more_results: true`, con avviso visibile. Nessuna paginazione, ricerca
in background, retry automatico o cache risultati. Risposte oltre20elementi sono
rifiutate. Parsing JSON massimo1MiB/profondità16 dopo ricezione; nessun limite
streaming della risposta in core curl. Restano timeout connessione5secondi/richiesta
15secondi, verifiche TLS e rifiuto dei redirect.

L’input è UTF-8 valido e circoscritto, senza controlli o spazi esterni; gli apostrofi
OData sono raddoppiati e il contenuto stringa codificato per URL. Il drive usa la
grammatica ID esistente. Nessun valore può fornire endpoint, metodo HTTP, percorso
contenuti o URL continuazione. La ricerca effettua un GET e nessun POST anteprima.

## Metadati privati e riuso core

Nomi, ID e riferimenti provvisori compaiono solo nella risposta protetta. Il JSON
ordinario del risultato contiene solo stato, conteggio e flag continuazione
allowlist; non righe candidate, query, URL, credenziali o corpi provider grezzi.
Nomi e riferimenti sono escapati; risposta non memorizzabile in cache e no-referrer.

Configurazione personale, verifiche endpoint e acquisizione client sono condivise
con metadati/pagina/anteprima. Factory OAuth core e pagina ritorno fissa conservate.
Core gestisce i token e può aggiornarli. Non si creano riferimenti File API, draft,
import media, cache file o proxy streaming. Le credenziali private dei repository
Moodle non vengono lette.

La Privacy API dichiara esplicitamente la query inviata a Microsoft. VideoTrack
non aggiunge persistenza query/candidati, stato sessione o logging applicativo.
Il logging provider/infrastruttura resta fuori da questa dichiarazione. Il testo
può identificare una persona: usa solo contenuti adatti al servizio configurato.
Vedere il [riepilogo privacy](../../PRIVACY_IT.md).

## Prove e gate rimanenti

La269 accettata è il pacchetto effettivo caricato, SHA256
94200cf7c2d8da43478abd44e39f3c31b6ecae23af2487e28add19f61d7dd45e,
mainad9dc8ec/albero823ae1cc. Candidato45251028653/main45260313398 superano sei job
ciascuno, Unit595/6280, Behat75/1428 senza retry, strict10/0/0 e Grunt nativo.
Entrambe le matrici lifecycle superano aggiornamento/ripetizione/disinstallazione
popolata, con snapshot di conservazione verificati indipendentemente. Il server269
supera canonici/lint198condivisi, Unit595/6280 su entrambe le istanze e Grunt nativi
con hash invariati; entrambi i validator danno10/0/0. Il manutentore riferisce
installazione e ricalcolo riusciti il2026-11-24.

La270 riprende la ricerca268 sospesa sulla269 accettata. Aggiunge25metodi unitari e
3scenari locali; conserva tutti595metodi precedenti, estendendo solo l’asserzione
esatta Privacy con searchquery. Conserva tutti75scenari, tracciamento, recupero e AMD.
CI/server nativi270 e ricerca tenant reale restano da eseguire. I controlli sorgente
locali non equivalgono a esecuzione nativa.

La ricerca non aggiunge sorgenti selezionabili nelle attività, visione o avvio studente.
Persistenza/backup attività, OAuth/anteprima browser reale, transizione Entra-header
annunciata e telemetria supportata tempo/play/pausa/seek restano gate. Prima Microsoft,
poi Zoom, Google Drive e App. Il dataset demo riprende separatamente su5.3; accessibilità manuale5.3 STANDBY.

## Riferimenti primari

- [Ricerca driveItem Graphv1, permessi, ambiti e continuazione](https://learn.microsoft.com/en-us/graph/api/driveitem-search?view=graph-rest-1.0)
- [Escaping stringhe OData e codifica URL Graph](https://learn.microsoft.com/en-us/graph/query-parameters)
- [Confine anteprima privata esistente](37_MICROSOFT_PRIVATE_PREVIEW.md)

[Riferimenti Microsoft facoltativi e backup portabile](41_MICROSOFT_ACTIVITY_REFERENCES.md).
