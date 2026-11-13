# Review linguistica basata sul codice

## 1.7.256: base delle connessioni ai provider

La255 reale/main36ab70ed è accettata:415 file corrispondenti, PHPCS/lint server168 puliti, Unit385/4540 su50/53 e strict10/0/0; CI candidata/main a sei job con52/1154 Behat e storie lifecycle selezionate superate. Il benchmark distribuito255 conferma current_userid10 e6/92 query mediane in40 campioni.

Questa candidata aggiunge il riferimento al servizio OAuth Microsoft nelle impostazioni sito (predefinito: Nessuno), un servizio di verifica locale in sola lettura e provider_status.php. Le credenziali restano gestite dal core. I controlli verificano tipo provider, abilitazione/uso per servizi, configurazione client, HTTPS degli endpoint/sito e stato locale dell’account di sistema/scopi core. Non scambiano o rinnovano token e non contattano Microsoft. OneDrive/SharePoint non è ancora una sorgente video selezionabile.

Il manutentore dà ora priorità alle sorgenti autenticate: OneDrive/SharePoint, Zoom Cloud Recording e poi Google Drive; l’App seguirà. La prova App → browser/HTTPS è esplicitamente saltata. L’estensione completa dei sottotitoli multilingua è rinviata perché non è circoscrivibile con sufficiente affidabilità a una sola release tra HTML5 e overlay dei provider. Trascrizioni e sottotitoli esistenti restano disponibili. Il manuale di accessibilità Moodle 5.3 resta in standby.

La validazione nativa della candidata256 resta da eseguire. Inventari attesi:442 file repository/418 distribuiti,170 PHP distribuiti più1 helper repository,1141 callable PHP nominati (1134 distribuiti+7 solo repository),654 callable AMD e8 pacchetti da1001 chiavi. Schema, AMD, AJAX/player/tracking e CI restano invariati. I nuovi11 metodi PHPUnit richiedono esecuzione nativa; nessun PASS App o provider remoto è dichiarato. Le sezioni precedenti in attesa di prove sono checkpoint storici.

Albero corrente: VideoTrack **1.7.256** (`2026111301`). Review assistita registrata 2026-11-11.

## Perimetro semantico completato e limiti

La review assistita cumulativa copre **tutte le 997 chiavi per pacchetto / 7976 valori** in DE, EN, ES, FR,
HI, IT, PL e PT. Questa tranche aggiunge le ultime **208 chiavi / 1664 valori**, senza riusare chiavi già
accettate. Il precedente perimetro accettato comprende 789 chiavi / 6312 valori. Non restano chiavi correnti
fuori dalla review dichiarata. Non equivale ad approvazione madrelingua, certificazione WCAG o prova di ogni deploy.

Il manifesto completo è l’insieme ordinato delle chiavi del [pacchetto canonico](../../lang/en/videotrack.php),
identico negli otto pacchetti. SHA-256 dei 997 nomi UTF-8 separati da newline, con newline finale:
`06d08c38e974996779f5ce9f6e05fce742c322b33c880c4f4091cc9f27983903`.
Restano conservati chiavi, ordine, molteplicità dei placeholder Moodle, token dei template Forum e numeri.
Le stringhe conservate senza rendering attivo non sono presentate come nuove funzioni.

## Baseline reale accettata

- 1.7.249 / `2026111102`, 412 file identici a main `667e678b52eaa6fe04e93a717c4bfed9efda535d`.
- SHA-256 ZIP: `d71f3c7e7b4c895cbd7fbbfbd1456f31bca2643f40f881263e4795edd223ae8c`.
- SHA-256 report server: `1210838cb96ad931452ac616ce2bc2aee8b1db67fdd4d367f918f4def0aa0789`.
- CI candidata/main `43014227736` / `43017694467`: sei job ciascuna superano al primo tentativo Behat 50/1074,
  PHPUnit 379/4076, Grunt e strict 10/0/0. I report reali Moodle 5.0/5.3 superano PHPCS/lint 166, Grunt e
  PHPUnit 379/4076; entrambi gli strict forniti sono 10/0/0. Hash AMD prima/dopo Grunt identici. Restano
  registrati avviso noto Browserslist, 24 finding PHPMD revisionati, deprecazioni alias Moodle e totali
  informativi Psalm 732/760. I gate configurati passano: non si dichiarano zero segnalazioni consultive.

## Rilievi correnti dal codice

- `report.php` mostrava il reset solo con accesso completo e `mod/videotrack:managereactions`, ma il POST
  diretto verificava soltanto il gate storico del report completo. La candidata aggiunge il controllo gestione
  prima di visibilità e cancellazione, conservando POST, sesskey e scope learner. Chiave capability e grant
  dei ruoli non cambiano. Il testo del permesso dal nome tecnico storico ora spiega il suo uso effettivo nel
  reset; modificare le definizioni delle reazioni segue il distinto permesso Moodle di modifica dell’attività.
- `tracker::update_state()` conserva segmenti rifiutati come righe di audit `servervalidated = 0` per sessioni
  non attive, intervalli avanti non consentiti o credito server insufficiente; anche il replay di una richiesta
  rifiutata ritorna `-1`. `save_segment` associa tutti questi casi a `suspicioussegment`. L’avviso ora indica
  che il tempo non è conteggiato, senza promettere assenza dal DB o attribuire ogni rifiuto alla sola finestra.
- `core/api::call()` restituisce null prima del trasporto con differimento offline: non crea da solo una coda
  automatica. Il log dice “non inviata”. Il retry ritenta la richiesta, non l’errore. Il testo gruppo eccezione
  conserva pausa a documento nascosto quando abilitata e validazione server invariata. Le descrizioni già
  salvate nei gruppi Moodle restano conservate: la modifica linguistica non le migra.
- Le correzioni mirate distinguono percentuale vista da completamento nell’outline polacco, spostamento video
  da ricerca testuale nell’override spagnolo e presa visione da ricezione/accettazione in ES/HI. L’help della
  chiave preset polacca non inventa il vincolo sulle sole minuscole. Allineati etichette, diacritici italiani
  e testi hindi. I permessi di valutazione parlano di progressi di visione, non consegne di compiti; il percorso
  reale di assegnazione voto dal report resta invariato.

Fonti: `report.php`, `report_access`, `learner_scope`, `save_segment`, `tracker`, `focus_policy`, `core/api`,
`presets.php`, `locallib.php`, integrazione voto e test Behat/report/interazioni correnti. Il guard reset è una
modifica intenzionale della logica di produzione: questa candidata non è definita soltanto testuale.

## Chiavi e valori modificati

- `acknowledgement:checkboxlabel`
- `debug:ajaxdeferredoffline`
- `debug:ajaxretry`
- `debug:timedtexttimefailed`
- `focus:exceptiongroupdescription`
- `integrity:settingsheader`
- `outline:nodata`
- `outline:percent`
- `presets:key_help`
- `presets:reactions_help`
- `reactioniconcategory:activity`
- `reactionpreset:none`
- `videotrack:grade`
- `videotrack:managereactions`
- `videotrack:managereactions_desc`
- `videotrack:overrideplayersettings_desc`
- `warning:notetruncated`
- `warning:suspicioussegment`

| Pacchetto | Valori modificati |
|---|---:|
| DE | 8 |
| EN | 7 |
| ES | 10 |
| FR | 8 |
| HI | 11 |
| IT | 9 |
| PL | 12 |
| PT | 8 |

Totale: **73 valori su 18 chiavi esistenti**. Sei stringhe cambiano in ogni pacchetto; le altre sono mirate.
Restano conservati nomi AJAX pubblici, codici warning, chiavi capability, campi DB, intestazioni CSV e dati salvati.

## Evidenze manuali accessibilità

Il manutentore ha fornito M01–M08 con esito PASS sulla **1.7.248 / Moodle 5.0 / Academi / Windows 11 / Chrome /
NVDA**. Sono osservazioni dichiarate eseguite, non più NON ESEGUITE. Report ricevuto 2026-11-11; versioni esatte
browser/tema/NVDA e data di esecuzione non sono state fornite. La copertura manuale Moodle 5.3 non è registrata.
Vedere il [verbale circoscritto](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020). Non dedurre esecuzioni
manuali su 249/250 dal DOM invariato o dai test automatici; i testi successivi hanno un delta proprio.
U-020 è coperto parzialmente; U-017 e la milestone essenziale U-007 restano chiusi.

## Aggiornamento accettazione e candidata corrente

Review assistita completa e guard reset sono stati accettati sulla 250 reale/main `7a5b5f391dcc1ef0fd63f8640ce325e93f9ada25`,
con CI candidata/dopo merge, due report server e strict 10/0/0. Le osservazioni sotto conservano la storia
della candidata 250 iniziale. CI corretta e nuovo job 5.3/MariaDB dopo merge sono passati; questo non prova
la causa dell'arresto iniziale del server di test. Nessun valore lingua cambia nella 251. I due nuovi test
sul ciclo di vita attendono la CI nativa; vedere [le evidenze correnti](28_USCITA_PRODUZIONE.md).

## Storia candidata 1.7.250 e roadmap

Restano 412 file, 166 file PHP, 31 documenti per guida EN/IT, 345 righe inventario e 654 callable AMD. I callable
PHP diventano 1103 per due helper solo test. XMLDB resta `2026110601`, con 7 tabelle / 151 campi / 22 indici /
22 chiavi esterne. Gli ER cambiano solo versione. AMD, implementazioni backup/privacy, nove servizi, grant
dei ruoli esistenti e regole CI/analisi statica non cambiano.

Due nuovi scenari browser inviano POST reali nella stessa sessione con sesskey valido. Un lettore del report
completo senza permesso gestione deve ricevere solo il rifiuto nopermissions pertinente, con snapshot plugin,
voti/completion nativi e altri utenti invariati. Un utente autorizzato deve resettare solo il target, cancellando
voto/completion e conservando gli altri studenti. Visione/completion sono ottenute dal browser; il voto in punti
è una fixture esplicita creata con la normale API Moodle. La pagina negata è verificata e ripristinata nello
step, senza disabilitare gli hook automatici sugli errori.

I sei artifact CI caricati corrispondono al commit `d75af28e890d12c7dab4edd507c404ac3f3f1ad9` del run
`43075665485`, tentativo 2. Ogni report fornito supera **379 test PHPUnit / 4076 asserzioni**,
**52 scenari Behat / 1154 passi** e strict **10/0/0**; nei report non compaiono riesecuzioni di scenari Behat.
Grunt termina nei sei ambienti. Sono evidenze CI reali, non esecuzioni native in questo workspace.

La CI complessiva resta bloccata da un warning PHPCS: la nuova asserzione sul reset negato ha 133 caratteri
alla riga 1636 dell'helper, oltre il limite di 132. La correzione della stessa versione spezza solo tale
espressione e aggiorna le posizioni degli helper nei due inventari callable. Asserzioni, runtime, traduzioni,
XMLDB, AMD, versione e regole CI restano invariati. Prima del merge serve un nuovo run su sei job riuscito,
poi CI dopo merge e accettazione di ZIP/report server/strict reali della versione unita.

PHPStan e gli ambiti Psalm configurati passano. Psalm riporta 733/761 segnalazioni consultive, contro 732/760
della 249 accettata: una segnalazione aggiuntiva per ramo Moodle analizzato. I report forniti non ne mostrano
il dettaglio, quindi non se ne deduce la causa. Le 24 segnalazioni PHPMD sono identiche byte per byte alla 249
accettata. Non si dichiarano zero segnalazioni o totali consultivi invariati.

Completare gate integrato web/ciclo vita/privacy/benchmark ed evidenze manuali residue, quindi decisione App e
fase concordata sottotitoli VTT multilingua prima di base autenticata, OneDrive/SharePoint, Zoom Cloud Recording
e Google Drive. Questa candidata non implementa l’estensione VTT runtime.

## Accettazione corrente sul ciclo di vita

La 251 reale accetta anche i casi backup/restore/privacy. La 252 aggiunge la scoperta del callback standard di reset
e casi nativi reset/delete; nessun valore lingua cambia. Vedere [le evidenze correnti](28_USCITA_PRODUZIONE.md).
