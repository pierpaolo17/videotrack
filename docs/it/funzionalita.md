# Guida per utenti e amministratori

La266 aggiunge un’[anteprima diagnostica privata](37_MICROSOFT_PRIVATE_PREVIEW.md) con l’account aziendale/scolastico collegato dell’amministratore corrente.
CLI metadati e azione pagina originale conservano i propri contratti; player studente tracciato
e validazione Microsoft reale con browser restano gate distinti.

La 1.7.264 aggiunge [Collega il mio account Microsoft](35_MICROSOFT_USER_CONNECTION.md)
alla pagina amministrativa esistente. Avvia OAuth core solo con POST protetto esplicito;
il normale GET diagnostico resta locale. Prove native264 e tenant reale da svolgere.

La 1.7.263 offre anche un’azione esplicita per [la pagina originale Microsoft](34_MICROSOFT_PROVIDER_PAGE.md).
Richiede account personale e ID video. La pagina provider si apre separatamente;
VideoTrack non accredita la visione in quella scheda.

La 1.7.262 aggiunge la scelta esplicita tra account di sistema e account Microsoft
personale collegato dell’amministratore. L’assenza di accesso personale non attiva
il ripiego sull’account condiviso. [Configurazione e limiti](33_MICROSOFT_USER_ACCESS.md).

La verifica Microsoft è disponibile anche dalle impostazioni di sito di VideoTrack.
La pagina legge la configurazione locale all’apertura e avvia la diagnostica remota
solo dopo l’invio del modulo protetto da parte dell’amministratore.
[Procedura dalla GUI](32_MICROSOFT_WEB_CHECK.md).

## Disponibilità delle sorgenti nel sito

L'amministratore può abilitare separatamente YouTube, Vimeo e HTML5/file caricati.
Le tre sorgenti sono abilitate per impostazione predefinita, anche dopo aggiornamento
senza valori già salvati. Una sorgente disabilitata non è selezionabile per nuove
attività e non consente riproduzione/nuovo tracciamento. Dati e report sono conservati.
Il modulo identifica la sorgente disabilitata esistente senza sostituirla.
Vedere [policy sorgenti e integrazioni opzionali](31_SOURCE_POLICY.md).

## Diagnostica autenticata dei metadati Microsoft

Gli amministratori possono verificare esplicitamente i metadati drive/video con
l'account OAuth core di sistema tramite `provider_probe.php`. Può rinnovare credenziali
core senza scrivere dati attività/demo. Non aggiunge la riproduzione OneDrive/SharePoint.
[Configurazione, comandi e interpretazione](30_MICROSOFT_GRAPH.md).

Le sezioni di sicurezza seguenti descrivono il comportamento accettato nella 257/258.
Le evidenze correnti sono nell'[audit documentale](09_DOCUMENTATION_AUDIT.md).


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

Baseline reale accettata257/main d6e8705e:422 file corrispondenti, PHPCS/lint server173
puliti, PHPUnit414/4695 e strict10/0/0 su50/53, build AMD invariati, CI candidata/main a sei
job, Behat52/1154 senza rerun e tre percorsi lifecycle nei job50/53MariaDB previsti.
SHA-256 pacchetto:59fff2d7214a6828c1544b896d489ecebb8bbefb1821e72c6a3384ca845cc504.

La candidata258 aggiunge15 metodi PHPUnit e due scenari browser, compreso backup/ripristino
reale con vecchio XML modificato. PHP/Moodle non disponibili localmente: prove native
PHPCS/lint/statiche/PHPUnit/Behat/lifecycle e accettazione di ZIP/server restano da eseguire.
Grunt AMD Moodle reale Node22 superato: solo due sorgenti e quattro build/mappe cambiano.
Smoke AMD e test adapter passano. Tre script Node storici falliscono ugualmente su257/258:
privacy_static_test e backup_restore_static_test hanno contratti precedenti ai refactor;
tracker_segment_test non ha la dipendenza core/str nel suo harness. È debito preesistente,
non test nativi superati. Un nuovo punteggio di sicurezza richiede una nuova scansione.
I rilievi nativi257 PHPMD28 e Psalm740/768 restano registrati, senza dichiarare zero warning.

Nessuna modifica a schema/savepoint/servizi/task retention/OAuth. Nessuna bonifica generale
dei dati pregressi, come richiesto; dataset demo conservato. Accessibilità manuale5.3 ancora
in standby; verifica visiva Tiles da eseguire. Dopo l'accettazione finale completa si passa
direttamente alla patch successiva, senza riconferma. Nuove sorgenti prima dell'App; prova
App → browser saltata.

## 1.7.258: correzioni delle revisioni di sicurezza

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

La baseline accettata è la 1.7.256 reale/main 26cf86ed: 418 file corrispondenti,
PHPCS/lint server puliti su 171 file, PHPUnit 396/4581 su Moodle 5.0/5.3, build Grunt invariati,
strict 10/0/0 su entrambe le istanze e CI candidata/main a sei job con i percorsi lifecycle previsti.
La candidata 1.7.257 aggiunge 18 metodi di regressione nativi, non eseguiti in questo workspace.
Restano da eseguire controlli canonici/statici/PHPUnit/Behat/lifecycle e accettazione di ZIP/server.
Una nuova scansione esterna è necessaria prima di attribuire un nuovo punteggio alla release.

Restano aperte la cancellazione definitiva di note/segnalibri e la gestione dei backup precedenti.
Il loro comportamento di soft-delete/retention non cambia. Separazione degli helper DDL,
riordino generale dei controller, chiavi browser per utente e download Analytics solo POST restano
lotti distinti. La configurazione OAuth Microsoft resta locale: nessun accesso remoto o nuovo player.
Le nuove sorgenti seguiranno questo lotto di sicurezza e l’App verrà dopo. La prova App → browser
è saltata, l’estensione sottotitoli multilingua è rinviata, il manuale Moodle 5.3 resta in standby
e i dati sintetici della demo sono conservati.

## 1.7.257: base delle connessioni ai provider

Questa candidata aggiunge il riferimento al servizio OAuth Microsoft nelle impostazioni sito (predefinito: Nessuno), un servizio di verifica locale in sola lettura e provider_status.php. Le credenziali restano gestite dal core. I controlli verificano tipo provider, abilitazione/uso per servizi, configurazione client, HTTPS degli endpoint/sito e stato locale dell’account di sistema/scopi core. Non scambiano o rinnovano token e non contattano Microsoft. OneDrive/SharePoint non è ancora una sorgente video selezionabile.

Il manutentore dà ora priorità alle sorgenti autenticate: OneDrive/SharePoint, Zoom Cloud Recording e poi Google Drive; l’App seguirà. La prova App → browser/HTTPS è esplicitamente saltata. L’estensione completa dei sottotitoli multilingua è rinviata perché non è circoscrivibile con sufficiente affidabilità a una sola release tra HTML5 e overlay dei provider. Trascrizioni e sottotitoli esistenti restano disponibili. Il manuale di accessibilità Moodle 5.3 resta in standby.

VideoTrack 1.7.256 è un'attività Moodle per distribuire video, registrare evidenze di visione validate dal
server, offrire strumenti di studio e valutare il completamento. Ogni raccolta dati opzionale deve essere
abilitata esplicitamente. Le politiche di sito possono limitare le impostazioni modificabili dal docente.

## Sorgenti multimediali supportate

| Sorgente | Input docente | Player | Note |
|---|---|---|---|
| Upload/HTML5 | File video nell'attività | Controlli HTML5 nativi/personalizzati | Supporta download opzionale, poster e durata dai metadati locali. |
| YouTube | URL pubblico o identificatore YouTube | YouTube IFrame API | Durata proposta da un probe limitato, muto e usato soltanto nel form. |
| Vimeo | URL pubblico o identificatore Vimeo | Vimeo Player SDK | Durata proposta in modo asincrono; restano i limiti di rete e privacy del provider. |

La durata salvata dal docente è autorevole per le percentuali. Il rilevamento automatico è soltanto una
proposta: va verificato prima del salvataggio, inserito manualmente se non disponibile oppure lasciato a `0`
per disabilitare il calcolo percentuale. Gli adapter condividono un contratto ma hanno implementazioni e limiti
del provider distinti.
Il form mostra lo stesso valore anche come `HH:MM:SS` e aggiorna l'equivalente dopo il rilevamento automatico o
l'inserimento manuale.

## Configurazione dell'attività

Il docente può configurare:

- nome, descrizione e impostazioni Moodle standard di disponibilità e gruppi;
- sorgente, URL/file, poster e download opzionale per i file caricati;
- autoplay, loop, avvio muto, larghezza player e controlli visibili;
- passi avanti/indietro, policy di seek e ripresa della visione;
- velocità disponibili, modifica da parte del learner, limite massimo e velocità di fallback dopo un salto bloccato;
- sottotitoli, trascrizioni ricercabili e capitoli tramite file WebVTT;
- reazioni, note personali, segnalibri privati e azione Forum temporizzata opzionale;
- diagnostica focus/integrità e presa visione;
- visibilità del report learner, finestra di aggregazione delle reazioni e preferenze export;
- voto, sufficienza e condizioni di completamento.

Gli amministratori definiscono i default e possono revocare `mod/videotrack:overrideplayersettings` o
`mod/videotrack:overridecompletionsettings` per imporre una politica centrale.

## Progresso di visione

VideoTrack registra le richieste di playback in `videotrack_seg`. Il play del browser apre una finestra di
credito autorizzata dal server. Le scritture successive avanzano il progresso soltanto quando identità della
sessione, tempo server trascorso, intervallo video richiesto e velocità restano entro il budget server. Le righe
rifiutate possono essere conservate come evidenza non autorevole, ma non aumentano la copertura vista.

Il progresso è l'unione degli intervalli temporali validati, non il tempo di orologio né la posizione massima
raggiunta. Rivedere un intervallo non lo conta due volte. Un salto avanti non riempie il tratto saltato.
`videotrack_state` memorizza copertura unica, ultima posizione attendibile e completamento per letture efficienti.

## Seek, velocità, resume e replay

- Seek avanti e indietro possono essere autorizzati separatamente.
- Un salto avanti vietato torna alla frontiera vista e applica la velocità di fallback configurata.
- Un salto consentito cambia la navigazione ma non segna retroattivamente come visti i secondi saltati.
- Il resume usa la posizione attendibile salvata ed è limitato da durata e copertura valide.
- I link di replay nei report aprono un frammento limitato senza indebolire le regole di tracking.
- La validazione pesata per velocità impedisce di ottenere più tempo video del budget autorizzato dal server.

I controlli del provider sono best effort quando l'SDK esterno espone funzioni fuori dall'interfaccia del plugin.
Il registro server resta l'autorità sul progresso acquisito.

## Reazioni

Il docente può partire da un preset o definire reazioni personalizzate con etichetta, descrizione e icona emoji,
Font Awesome, testuale o caricata. Una reazione può essere obbligatoria per il completamento. Il learner può
aggiungere o rimuovere le proprie reazioni in timestamp attendibili. Controlli su duplicati e raffiche riducono
invii accidentali o automatizzati.

Le reazioni sono disabilitate per impostazione predefinita. Per abilitarle serve almeno una definizione attiva e
completa: il form avvisa e rifiuta una configurazione vuota. Controlli learner, integrazione col player, avviso e
cronologia **Le mie reazioni** compaiono soltanto se sono presenti sia l'abilitazione sia una definizione attiva.
Note, segnalibri e azione Forum restano governati indipendentemente dai rispettivi flag e dalla destinazione.

Un valore Font Awesome viene accettato soltanto se contiene esattamente un nome icona `fa-*` minuscolo e non più
di tre decoratori revisionati di stile, famiglia, dimensione, rotazione, ribaltamento o animazione. Il valore
completo è limitato a quattro token e 160 caratteri e può contenere soltanto lettere ASCII, cifre, spazi e trattini;
ogni token sconosciuto produce un rifiuto fail-closed. Form e persistenza usano lo stesso contratto.

Il completamento può richiedere un numero minimo di reazioni distinte, tutti i tipi abilitati, tipi specifici
obbligatori o una combinazione. La logica `AND`/`OR` configurata opera dentro l'unica regola composita VideoTrack.

## Note personali e segnalibri

Quando abilitati, i learner possono creare note personali temporizzate e segnalibri nominati. Il testo delle
note personali è leggibile nei report individuali autorizzati ed esportabile con permessi individuali separati,
entro il perimetro degli studenti consentito. Etichette e timestamp esatti dei segnalibri restano privati del
proprietario; i report docente espongono solo i conteggi dei segnalibri. La pagina mantiene visibili
i composer e colloca le cronologie salvate in sezioni native comprimibili.

I segnalibri possono essere riaperti ed esportati dal proprietario. Testi delle note ed etichette dei segnalibri
sono esclusi dagli Analytics aggregati. La cancellazione è soft a livello di interazione per mantenere coerenti
report, retention e restore nel rispetto della visibilità corrente.

## Strumenti testuali WebVTT

- sottotitoli serviti dalle file area Moodle;
- trascrizioni con ricerca, cue temporizzati e navigazione seek;
- capitoli con punti di navigazione nominati;
- poster mostrabile prima della riproduzione.

Il docente è responsabile di validità, sincronizzazione e licenza dei contenuti. I sottotitoli nativi del provider
e i file VTT caricati in VideoTrack sono funzioni distinte. I file dedicati a trascrizione e capitoli hanno la
precedenza; il vecchio file sottotitoli caricato viene usato come fallback esplicito soltanto per attività migrate.

## Collegamento Forum temporizzato

L'attività può collegarsi a un Forum dello stesso corso. L'azione learner apre un composer con timestamp validato
e oggetto configurabile. VideoTrack verifica accesso e autorevolezza del timestamp; il modulo Forum effettua la
scrittura finale e applica permessi, gruppi e disponibilità propri.

## Presa visione

Il docente può pubblicare una dichiarazione da confermare in qualsiasi momento oppure soltanto dopo l'ultimo
secondo validato. La conferma conserva hash della dichiarazione corrente, versione dell'istanza, data e snapshot
della copertura. Modificare la dichiarazione crea una nuova versione corrente: una conferma precedente non la
soddisfa. La presa visione può partecipare al completamento.

## Completamento e gradebook

La regola personalizzata VideoTrack può usare:

- percentuale minima vista;
- criteri di reazione;
- conferma della presa visione corrente;
- composizione `AND` o `OR` dei criteri VideoTrack abilitati.

Moodle può inoltre richiedere apertura dell'attività, ricezione di un voto o sufficienza. Queste restano condizioni
core separate. Nell'intestazione dell'attività l'etichetta logica VideoTrack precede l'elenco senza modificarne
ordine, stati o semantica. Il gradebook supporta punti, scale e voto minimo di sufficienza.

## Report, Analytics ed esportazioni

In base alle capability VideoTrack offre:

- report personale learner;
- report di attività aggregati e individuali;
- dashboard di corso tra attività VideoTrack;
- vista docente sui corsi autorizzati;
- Analytics dello stesso video tra corsi e attività;
- timeline e cluster delle reazioni;
- export aggregati CSV, Excel e ODS;
- CSV individuali ed export dei segnalibri del proprietario.

L'accesso soltanto aggregato mantiene la soglia minima di privacy configurata e non consente filtri learner.
L'accesso individuale restituisce valori esatti soltanto entro lo scope Moodle dell'utente. Campi identificativi
e delimitatore CSV opzionali possono essere governati globalmente o per attività.

## Controlli focus e integrità

La diagnostica opzionale include variazioni di focus/visibilità, tentativi Picture-in-Picture e pause casuali di
attenzione. La policy predefinita mette in pausa solo quando il documento è nascosto. Il blur rigido della finestra
visibile può essere abilitato a livello sito; il gruppo corso nascosto `mod_videotrack_focus_exception` lo riduce
a hidden-only per i learner autorizzati, senza bypassare scheda nascosta o validazione server.

Gli eventi di integrità sono segnali diagnostici limitati, non prove di comportamento scorretto. Vanno interpretati
considerando accessibilità, browser, tecnologie assistive e resto delle evidenze.

## Privacy, retention e ciclo di vita dati

La Privacy API Moodle dichiara, esporta e cancella i dati learner. La retention pianificata elimina i record granulari
scaduti e ricostruisce lo stato derivato dalle evidenze validate conservate. La retention illimitata richiede conferma
esplicita dell'amministratore. Il backup include configurazione e definizioni delle reazioni; i dati utente entrano
solo se richiesti e ancora conservabili. Il restore rimappa utenti, Forum e reazioni e ricostruisce lo stato derivato.
Reset e cancellazione dell'attività rimuovono i record VideoTrack nel relativo scope.

Vedere [`18_PRIVACY_RETENTION.md`](18_PRIVACY_RETENTION.md) e `PRIVACY_IT.md` nella root per il contratto esatto.

## Accessibilità

L'interfaccia usa controlli nativi quando possibile, focus visibile, regioni di stato, nomi accessibili, tastiera,
layout responsivo e supporto forced-colours. L’audit U-020 corrente con tastiera e screen reader è rinviato e
resta non eseguito; seguire il [protocollo manuale](19_ACCESSIBILITY.md) prima del gate finale di produzione.
I controlli automatici non dimostrano l’accessibilità manuale. Gli iframe dei provider pubblici restano soggetti
al comportamento di accessibilità del provider.

## Lingue e diagnostica

Sono mantenuti otto language pack con stesse chiavi e placeholder: tedesco, inglese, spagnolo, francese, hindi,
italiano, polacco e portoghese. Gli amministratori possono usare validatore e benchmark Analytics in sola lettura
documentati in [`21_CLI_DIAGNOSTICS.md`](21_CLI_DIAGNOSTICS.md).

## Limiti operativi

- Disponibilità, consenso, cookie e SDK pubblici YouTube/Vimeo sono dipendenze esterne.
- Durata `0` disabilita la percentuale; non rappresenta una durata illimitata dedotta.
- Blocco Picture-in-Picture e controlli provider esterni sono best effort.
- La diagnostica focus non dimostra attenzione o intenzione.
- I test browser usano doppi provider locali deterministici; eseguire uno smoke test reale quando cambiano
  politiche del provider o controlli di rete del sito.

Per controlli guidati dal sintomo vedere [`20_TROUBLESHOOTING.md`](20_TROUBLESHOOTING.md).

## Intervalli di attenzione scelti dal docente

Le pause di attenzione dell’attività usano per default 1/3–2/3 della durata salvata. Il docente può sostituire ciascun limite in secondi; vuoto/0 lo mantiene automatico. Vedere le [regole complete](11_INTEGRITY_AND_FOCUS.md#intervalli-di-attenzione-scelti-dal-docente), inclusi durata zero e limiti operativi.

## Perimetro delle policy di validazione delle interazioni

L’impostazione stessa sessione governa note/reazioni. I limiti di età dello storico sono usati da note,
segnalibri e collegamenti Forum degli studenti; le reazioni non applicano quella finestra temporale.
Queste distinzioni già esistenti sono descritte nei [confini di sicurezza](13_SECURITY_TRUST_BOUNDARIES.md).

## Scope corrente reset e review

Il reset del singolo studente ora verifica il permesso gestione anche sul server, oltre che nell’interfaccia:
vedere i [permessi](16_ROLES_CAPABILITIES.md#autorizzazione-reset-del-singolo-studente-nel-report). La review
assistita copre tutte le 997 chiavi negli otto pacchetti, senza equivalere ad approvazione madrelingua.
Sono dichiarati otto PASS manuali su 1.7.248/Moodle 5.0/Academi/Windows 11/Chrome/NVDA; manca copertura 5.3.
Gate candidata pendenti: attesi PHPUnit 379/4076 e Behat 52/1154. Lavoro web/App prima della fase concordata
VTT multilingua, poi base autenticata e OneDrive/SharePoint, Zoom e Google Drive.
