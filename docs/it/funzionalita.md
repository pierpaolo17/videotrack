# Guida per utenti e amministratori

VideoTrack 1.7.133 è un'attività Moodle per distribuire video, registrare evidenze di visione validate dal
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

Il completamento può richiedere un numero minimo di reazioni distinte, tutti i tipi abilitati, tipi specifici
obbligatori o una combinazione. La logica `AND`/`OR` configurata opera dentro l'unica regola composita VideoTrack.

## Note personali e segnalibri

Quando abilitati, i learner possono creare note personali temporizzate e segnalibri nominati. Questi dati sono
privati del proprietario, salvo report/export individuali separatamente autorizzati. La pagina mantiene visibili
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
e i file VTT caricati in VideoTrack sono funzioni distinte.

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
layout responsivo e supporto forced-colours. Trascrizione, raggruppamento completion, cronologie personali e stati
del player sono stati verificati nel ciclo manuale WCAG corrente. Gli iframe dei provider pubblici restano soggetti
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
