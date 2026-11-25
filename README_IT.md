# VideoTrack per Moodle

[![Moodle Plugin CI](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml/badge.svg)](https://github.com/pierpaolo17/videotrack/actions/workflows/ci.yml)

VideoTrack è un modulo attività Moodle per video HTML5/caricati, YouTube e Vimeo. Integra progresso di
visione validato dal server, strumenti di studio, completamento personalizzato, gradebook e report attenti
alla privacy.

Release corrente documentata da questo albero: **1.7.272**. Rami Moodle supportati: **5.0–5.3**.

- Panoramica inglese: [`README.md`](README.md)
- Guida italiana completa: [`docs/it/funzionalita.md`](docs/it/funzionalita.md)
- Indice tecnico: [`docs/it/00_INDEX.md`](docs/it/00_INDEX.md)
- Sintesi privacy: [`PRIVACY_IT.md`](PRIVACY_IT.md)
- Riferimento database/ER: [`docs/VIDEOTRACK_DB_ER_SCHEMA.md`](docs/VIDEOTRACK_DB_ER_SCHEMA.md)
- Cronologia release: [`CHANGELOG.md`](CHANGELOG.md)

È possibile preparare riferimenti Microsoft facoltativi nelle impostazioni e conservarli
nel backup corso senza associazioni OAuth locali. La riproduzione Microsoft tracciata
attende le prove reali HTTPS/account. Vedere la [guida riferimenti](docs/it/41_MICROSOFT_ACTIVITY_REFERENCES.md).

## Mappa delle funzionalità

| Area | Funzionalità disponibili |
|---|---|
| Media | Video caricati/HTML5, YouTube e Vimeo; proposta durata nel form con equivalente `HH:MM:SS`; poster; download opzionale dei file caricati. |
| Player | Autoplay, loop, avvio muto, larghezza responsiva, controlli configurabili, policy tastiera/fullscreen, passi avanti/indietro. |
| Navigazione | Policy seek avanti/indietro indipendenti, resume attendibile, replay limitato dai report e recupero dai salti bloccati. |
| Velocità | Elenco velocità, modifica learner, limite massimo e velocità di fallback dopo un salto bloccato. |
| Evidenze viste | Sessioni autorizzate dal server, richieste segmento idempotenti, copertura di intervalli unici e stato derivato. |
| Testo temporizzato | Sottotitoli WebVTT, trascrizione ricercabile, capitoli e navigazione per timestamp. |
| Reazioni | Preset opt-in e icone emoji, testuali, Font Awesome o caricate; controllo configurazione, aggiunta/rimozione, deduplica e limiti raffica. |
| Strumenti di studio | Note personali temporizzate, segnalibri privati nominati, replay/export del proprietario e cronologie comprimibili. |
| Forum | Composer temporizzato opzionale collegato a un Forum compatibile dello stesso corso. |
| Presa visione | Dichiarazione learner versionata, conferma immediata/fine video e integrazione completion. |
| Completamento | Percentuale vista, reazioni minime/distinte/obbligatorie e presa visione con logica AND/OR. |
| Voti | Punti/scale Moodle, sufficienza, visualizzazione voto learner e condizioni core basate sul voto. |
| Report | Viste learner, attività, corso e docente; permessi aggregati/individuali; timeline e cluster reazioni. |
| Analytics | Analisi cross-corso dello stesso video, scope cohort/gruppi, finestre 7/30/90 giorni e soglia privacy. |
| Export | Report CSV, Excel e ODS, campi/delimitatore CSV, export individuali e CSV segnalibri del proprietario. |
| Integrità | Diagnostica opzionale visibilità/focus, Picture-in-Picture e pause casuali; guard server del playback. |
| Accessibilità | Tastiera, focus visibile, regioni di stato, layout responsivo/reflow, forced-colours e completion accessibile. |
| Privacy | Privacy API Moodle, export/cancellazione proprietario, cancellazione context/user-list, retention a eliminazione e conferma retention illimitata. |
| Ciclo di vita | Backup/restore con o senza dati utente, reset, cancellazione istanza, riparazione gradebook ed eventi Moodle. |
| Amministrazione | Default sito, policy player/completion imponibili, preset reazioni, otto lingue, validatore/benchmark Analytics in sola lettura e diagnostica Graph autenticata esplicita. |
| Qualità | GitHub Actions integrato, PHPUnit/Behat, PHPCS/PHPDoc/validazione Moodle, gate AMD canonici, PHPMD revisionato e baseline PHPStan/Psalm Moodle-aware. |

La tabella è una mappa completa, non la specifica operativa. Comportamento, permessi, confini dati e limiti dei
provider sono descritti una sola volta nella [guida completa](docs/it/funzionalita.md) e nei riferimenti tecnici.

## Disponibilità delle sorgenti nel sito

L'amministratore può abilitare separatamente YouTube, Vimeo e HTML5/file caricati.
Le tre sorgenti sono abilitate per impostazione predefinita, anche dopo aggiornamento
senza valori già salvati. Una sorgente disabilitata non è selezionabile per nuove
attività e non consente riproduzione/nuovo tracciamento. Dati e report sono conservati.
Il modulo identifica la sorgente disabilitata esistente senza sostituirla.
Vedere [policy sorgenti e integrazioni opzionali](docs/it/31_SOURCE_POLICY.md).

## Requisiti e installazione

- Moodle 5.0–5.3 e stack PHP/database supportato dal ramo Moodle scelto.
- Accesso di rete a YouTube/Vimeo quando si usano i provider pubblici.
- Task pianificati Moodle attivi quando è richiesta la pulizia per retention.

Installare dall'interfaccia Moodle oppure copiare l'unica directory `videotrack` in `mod/`, quindi aprire
**Amministrazione del sito → Notifiche** o eseguire l'upgrade CLI Moodle. Prima della diffusione verificare
default di sito, retention, campi identificativi dei report e policy focus.

Non modificare direttamente i file installati. Usare una release revisionata e svuotare le cache Moodle dopo
il deploy.

## Ruoli e privacy in sintesi

`mod/videotrack:participate` governa il tracking learner indipendentemente dai permessi di report. Docenti e
amministratori restano in anteprima non tracciata salvo assegnazione esplicita della capability. Permessi di
report/export aggregati e individuali sono separati per governare mascheramento privacy e dettaglio learner.

La diagnostica opzionale supporta l'analisi ma non dimostra l'intenzione del learner. Note personali ed etichette
dei segnalibri sono escluse dagli Analytics aggregati. Consultare [`PRIVACY_IT.md`](PRIVACY_IT.md) prima di
modificare retention o campi identificativi.

## Amministrazione e validazione

Dalla root Moodle:

```bash
php mod/videotrack/cli/validate.php --json
php mod/videotrack/cli/benchmark_course_analytics.php --courseid=<id> --userid=<id>
```

Gli strumenti sono in sola lettura. Opzioni e interpretazione sono in
[`docs/it/21_CLI_DIAGNOSTICS.md`](docs/it/21_CLI_DIAGNOSTICS.md). I maintainer devono seguire
[`docs/it/07_BUILD_TEST_RELEASE.md`](docs/it/07_BUILD_TEST_RELEASE.md); quando cambia `amd/src`, build Moodle
Grunt e source map corrispondenti sono obbligatori.

La CI del repository, la matrice Moodle/PHP/database, i controlli bloccanti/consultivi e gli artefatti scaricabili
sono descritti in [`docs/it/23_GITHUB_ACTIONS_CI.md`](docs/it/23_GITHUB_ACTIONS_CI.md).

## Milestone del progetto

U-017 manutenibilità e U-007 automazione browser essenziale sono chiusi su evidenze release accettate.
L’[audit uscita browser](docs/it/26_USCITA_BROWSER.md) registra scope e provenienza. U-020 accessibilità
manuale registra otto PASS dichiarati su Moodle 5.0/Academi con Windows 11/Chrome/NVDA; manca la copertura 5.3: conservare il [protocollo manuale](docs/it/19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020)
per il gate finale di produzione. La [review linguistica mirata](docs/it/27_REVISIONE_LINGUE.md) registra
correzioni dei testi delle policy in tutti gli otto pacchetti e il perimetro semantico ancora da revisionare.

## Licenza

GNU GPL v3 o successiva, coerentemente con Moodle.

## Preparazione dei provider autenticati

Il comando esistente `provider_status.php` verifica la configurazione OAuth locale
senza contattare Microsoft. La 1.7.259 aggiunge `provider_probe.php` per un GET autenticato
esplicito dei metadati drive/video attraverso l'account di sistema core. Il core può
rinnovare/salvare token OAuth; i record VideoTrack/demo restano intatti. La riproduzione
OneDrive/SharePoint richiede una fase successiva. [Guida Graph](docs/it/30_MICROSOFT_GRAPH.md).

Il manutentore dà ora priorità alle sorgenti autenticate: OneDrive/SharePoint, Zoom Cloud Recording e poi Google Drive; l’App seguirà. La prova App → browser/HTTPS è esplicitamente saltata. L’estensione completa dei sottotitoli multilingua è rinviata perché non è circoscrivibile con sufficiente affidabilità a una sola release tra HTML5 e overlay dei provider. Trascrizioni e sottotitoli esistenti restano disponibili. Il manuale di accessibilità Moodle 5.3 resta in standby.

## 1.7.257: correzioni delle revisioni di sicurezza

Questa release corregge i problemi verificati nei permessi delle impostazioni e nella privacy
delle dashboard. I callback di salvataggio applicano le capability di override del corso prima
di normalizzazione, scritture e modifiche ai sottotitoli. In modifica conservano i valori bloccati;
in creazione usano i default sito, compresi zero esplicito e scelte vuote che ereditano dal sito.
Le dipendenze del completamento non possono essere disabilitate con durata zero, presa visione
disattivata o rimozione/riordino delle reazioni richieste. Limite di velocità e ripresa, lasciati
modificabili dal form esistente, mantengono quel contratto.

Le dashboard corso/docente richiedono accesso aggregato alla singola attività. La soglia viene
risolta per attività: accesso legacy/individuale consente valori esatti; accesso solo aggregato
impone almeno la soglia sito, anche se un chiamante richiede uno. Stati, periodi, reazioni, note
e segnalibri applicano la stessa soglia. I conteggi soppressi risultano nascosti, anziché zero.
Le query per attività restano a blocchi; le nuove misure di benchmark sono da eseguire.

Il voto manuale vuoto usa rawgrade nullo attraverso la Grade API, sia per punti sia per scale.
I nomi HTML sono escapati separatamente dai CSV testuali e dai valori degli attributi HTML.
Warning delle reazioni e avviso agli studenti sulle note sono presenti nelle otto lingue.
L’avviso spiega che gli utenti autorizzati ai report individuali possono leggere ed esportare le note.

Il GET della pagina video non crea né ripara il gruppo eccezioni di focus. La creazione resta
in add/update/restore/upgrade. La disinstallazione completa elimina solo i gruppi con idnumber
riservato al plugin, anche senza grade item. Il recupero voti usa una transazione DML delegata
per annullare merge/eliminazioni interrotti; la transazione non comprende DDL.

[Correzioni delle revisioni di sicurezza](docs/it/29_REVISIONE_SICUREZZA.md).

## 1.7.258: completamento delle correzioni di sicurezza

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

[Completamento delle correzioni di sicurezza](docs/it/29_REVISIONE_SICUREZZA.md).

La [verifica connessione Microsoft](docs/it/32_MICROSOFT_WEB_CHECK.md) è disponibile
dalle impostazioni di sito. Verifica l’accesso ai metadati con il servizio OAuth
di core; la riproduzione OneDrive/SharePoint resta un passaggio distinto della roadmap.

La 1.7.262 aggiunge la [verifica Microsoft personale](docs/it/33_MICROSOFT_USER_ACCESS.md)
con l’account OAuth core dell’amministratore corrente, senza ripiego sull’account
di sistema. Non autorizza la riproduzione degli studenti né aggiunge un player cloud.

La 1.7.263 può [recuperare la pagina originale del video Microsoft](docs/it/34_MICROSOFT_PROVIDER_PAGE.md)
con l’account personale collegato dell’amministratore corrente. Si apre sul provider
in una nuova scheda; non aggiunge un player Microsoft tracciato alle attività.

La 1.7.264 aggiunge il [collegamento Microsoft personale](docs/it/35_MICROSOFT_USER_CONNECTION.md)
dalla diagnostica amministrativa, tramite OAuth core di Moodle. La connessione non
dimostra accesso a uno specifico video né abilita un player Microsoft tracciato.

La 1.7.265 conserva i dump browser Behat disponibili anche dopo un rerun riuscito
e registra lo stato del runner al termine dello step. È manutenzione CI; riproduzione
e Analytics di produzione restano invariati. [Prove e limiti](docs/it/36_CI_BEHAT_EVIDENCE.md).

La 1.7.266 aggiunge un’[anteprima diagnostica Microsoft privata](docs/it/37_MICROSOFT_PRIVATE_PREVIEW.md).
L’amministratore corrente può richiedere un’anteprima nativa temporanea con il proprio account
aziendale/scolastico collegato. L’indirizzo resta privato; non accredita visione o completamento studenti.

La 1.7.267 aggiunge [riferimenti video Microsoft riutilizzabili](docs/it/38_MICROSOFT_VIDEO_REFERENCES.md)
nella diagnostica amministrativa. Copia l’identità risorsa validata e riusala per richiedere
nuovi metadati, pagina provider o anteprima personali; URL temporanei e credenziali sono
esclusi. Non persiste ancora sorgenti Microsoft nelle attività e non traccia gli studenti.

La1.7.269 corregge la continuità della visione tra salvataggi, pause e cambio scheda e offre recupero storico esplicito e tracciabile senza cancellare le prove originali. Vedi [recupero tracciamento](docs/it/39_TRACKING_RECOVERY_HOTFIX.md). Il lavoro Microsoft268è in pausa.

## Ricerca video Microsoft personale

La diagnostica amministrativa cerca identità video nel drive indicato usando
la connessione OAuth personale core. Mostra risultati privati circoscritti e
riferimenti riutilizzabili. [Guida ricerca](docs/it/40_MICROSOFT_VIDEO_SEARCH.md).
Conservate tutte le correzioni di tracciamento269 e il recupero storico esplicito.

Questa release corregge una race del replay da report che poteva riavvolgere frammenti
YouTube e applicare la velocità di recupero durante la riproduzione normale. Vedere la [guida replay](docs/it/42_REPORT_REPLAY_STATE.md).
