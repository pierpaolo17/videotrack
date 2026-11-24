# Sorgenti video del sito e integrazioni opzionali

La266 aggiunge un’[anteprima diagnostica privata](37_MICROSOFT_PRIVATE_PREVIEW.md) con l’account aziendale/scolastico collegato dell’amministratore corrente.
CLI metadati e azione pagina originale conservano i propri contratti; player studente tracciato
e validazione Microsoft reale con browser restano gate distinti.

VideoTrack **1.7.270** (`2026112400`).

## Configurare le sorgenti implementate

Aprire **Amministrazione del sito → Plugin → Moduli attività → VideoTrack → Sorgenti video**.
YouTube, Vimeo e Caricamento (player HTML5 locale) hanno ciascuna una casella
indipendente, attivata per impostazione predefinita. Anche su un sito aggiornato,
l'assenza della configurazione equivale ad abilitazione. La descrizione mostra
quante attività usano la sorgente, comprese quelle disabilitate. Un'unica query
raggruppata calcola i conteggi nella pagina amministrativa; durante la riproduzione
questa policy non conta le attività né contatta il fornitore.

Alla creazione compaiono soltanto le sorgenti abilitate. Se YouTube è disabilitato,
il modulo propone la prima alternativa abilitata. Con tutte le sorgenti disabilitate,
la selezione vuota spiega che deve intervenire l'amministratore; il salvataggio non
può ripiegare silenziosamente su YouTube.

In modifica la sorgente originale disabilitata rimane visibile con un'etichetta
esplicita, conservando selezione e identità del video. Per salvare, il docente deve
scegliere una sorgente abilitata oppure l'amministratore deve riabilitare quella
originale. Anche i callback diretti di creazione/aggiornamento impongono la policy
prima delle scritture su attività, valutazioni e file: alterare il modulo non basta
ad aggirarla.

## Effetti della disabilitazione

- La riproduzione delle attività esistenti viene bloccata con un messaggio tradotto
  prima di completamento su apertura, presa visione o generazione del player.
- Le nuove sessioni, i segmenti, le reazioni, le note, i segnalibri e gli eventi di
  integrità vengono respinti nel controllo autenticato delle funzioni esterne.
  Vale anche per gli invii da una scheda aperta prima della disabilitazione.
- Gli URL diretti del video locale e dei sottotitoli precedenti rispettano
  l'impostazione Caricamento.
- L'interruttore non elimina attività, file, avanzamento, interazioni, prese visioni
  o valutazioni. Report, esportazioni, Privacy API, retention e cancellazioni
  personali autorizzate mantengono le regole di accesso già previste.
- Riabilitare ripristina l'accesso alla stessa attività, identità del video e stato
  di avanzamento. Scegliere un'altra sorgente/video è una modifica esplicita.
- Backup/ripristino non abilita una sorgente nel sito: le attività ripristinate
  rispettano la policy del sito di destinazione.

Il JSON del validatore include nella configurazione critica i tre valori booleani
effettivi, anche quando l'abilitazione deriva dall'assenza di un valore salvato.
File già scaricati e siti esterni dei fornitori mantengono le proprie regole di
accesso e cache.

La policy si applica alle richieste successive di pagine, file e servizi. Questo
interruttore non interrompe da remoto i contenuti già caricati in una scheda aperta.

La vista di indisponibilità corretta usa un avviso normale con ritorno al corso,
senza pagina di errore fatale Moodle né avvio del player. L'accesso ai dati per le
cancellazioni personali resta separato dall'accesso alla riproduzione abilitata.
Vedere [rilievi della prima CI e correzione](09_DOCUMENTATION_AUDIT.md).

## Verifiche e stato della release

La candidata aggiunge 15 metodi PHPUnit e quattro scenari Behat: valori predefiniti,
interruttori indipendenti, configurazione alterata, opzioni filtrate, sorgenti
disabilitate in modifica, tutte le sorgenti disabilitate, callback diretti respinti,
cambio esplicito di sorgente, richieste da schede precedenti, conservazione di file
e stati e cancellazioni personali. La policy sorgenti fino alla266 è accettata, inclusi sei job main, pacchetto/server
reale e due strict. I nuovi riferimenti267 sono ancora da validare; i confronti locali
sorgenti/patch non sono esecuzione PHP nativa.

## Repository Zoom opzionale: valutazione architetturale

Un plugin repository separato è una soluzione da valutare per condividere una
connessione Zoom e la scelta delle registrazioni tra attività Moodle. Prima di
creare un nuovo plugin vanno esaminate le integrazioni esistenti. La separazione proposta è:

| Componente | Responsabilità |
|---|---|
| Repository Zoom opzionale | Configurazione dell'account, selezione autorizzata delle registrazioni, identità stabile istanza riunione/file, metadati e verifica disponibilità. |
| Adapter Zoom VideoTrack | Avvio del player, posizione corrente ed eventi di riproduzione, reazioni, intervalli guardati e controlli supportati. |

Il repository dovrebbe esporre una piccola interfaccia documentata lato server.
VideoTrack non deve leggere token privati di un altro plugin, duplicare credenziali
o imporre il repository a chi usa soltanto HTML5/YouTube/Vimeo. Una futura sorgente
diventa selezionabile quando dipendenza opzionale, configurazione, autorizzazioni
e contratto di riproduzione supportato sono pronti.

I riferimenti esterni di Moodle possono comportare cache o distribuzione locale
dei byte. Il riuso deve conservare il percorso fornitore → browser scelto, senza
importazione o proxy del video impliciti. Gli identificativi stabili devono
sopravvivere al rinnovo degli URL; le credenziali API restano nel backend e
l'accesso dello studente viene verificato separatamente da quello amministrativo.

Per l'account Zoom interno vanno valutati Server-to-Server OAuth e webhook firmati
sul ciclo di vita delle registrazioni. Credenziali e rinnovo dei token differiscono
dai flussi OAuth delegati Microsoft/Google già gestiti dal core Moodle. Un repository
non dimostra il supporto dei tempi e dei controlli del player nativo: resta una
verifica di fattibilità distinta. La 1.7.261 non consegna plugin Zoom, player cloud,
credenziali o endpoint webhook. Il servizio Graph/OAuth della 1.7.259 è conservato; la nuova
[verifica amministrativa dalla GUI](32_MICROSOFT_WEB_CHECK.md) lo riutilizza.

## Riferimenti primari

- [Servizi OAuth 2 Moodle](https://docs.moodle.org/500/en/OAuth2_Services)
- [API repository Moodle](https://moodledev.io/docs/5.2/apis/plugintypes/repository)
- [Zoom Server-to-Server OAuth](https://developers.zoom.us/docs/internal-apps/s2s-oauth/)
- [Webhook Zoom](https://developers.zoom.us/docs/api/webhooks/)
