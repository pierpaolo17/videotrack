# Hardening 1.6.23

VideoTrack 1.6.23 è una release esclusivamente di hardening prima della fase Moodle App.

## Confine di fiducia

- `durationseconds` è ora configurata esplicitamente dal docente ed è l’unica durata autorevole per percentuale vista, completamento percentuale e presa visione a fine video.
- I metadati runtime learner non diventano mai autorevoli. Dalla 1.6.27 il form docente attendibile può proporre un valore dai metadati YouTube, Vimeo o del file locale; la proposta diventa autorevole soltanto dopo la verifica e il salvataggio del docente e può essere modificata in seguito. Limiti del provider o del browser possono rendere il rilevamento non disponibile.
- Il campo dell’istanza continua ad accettare zero: gli intervalli validati vengono registrati, mentre percentuale vista, completamento percentuale e presa visione dopo l’ultimo secondo restano indisponibili finché il docente non salva una durata maggiore di zero.
- `save_segment` verifica la velocità dichiarata rispetto all’elenco effettivamente consentito dall’attività/sito.
- L’accettazione dei segmenti usa un budget cumulativo basato sul tempo server persistito per utente/attività. La rotazione del `sessionid` client e l’aumento della frequenza delle richieste non rigenerano credito; i lunghi periodi inattivi sono limitati.
- Il server applica anche la frontiera già vista quando il seek in avanti è disabilitato.
- Lo stato conserva soltanto valori limitati del guard/sessione (`serverlastactivity`, `serverplaybacksessionid`, `serverbudgetseconds`, `servercreditedseconds`). Sono dichiarati nella Privacy API ma intenzionalmente esclusi dal payload di backup e azzerati al restore, così il credito di riproduzione residuo non può trasferirsi tra copie del corso.

## Scope learner

- Nella 1.6.23 gli utenti con `mod/videotrack:viewreport` erano esclusi dalla telemetria learner. Dalla 1.6.29 questa euristica è sostituita dalla capability esplicita `mod/videotrack:participate`, che supporta learner con ruoli personalizzati o multipli mantenendo non tracciate le normali anteprime staff.
- Report di istanza, azioni sensibili ed export note usano regole canoniche di iscrizione attiva e visibilità gruppi Moodle.

## Contratto privacy

I segnalibri restano visibili solo al proprietario. Il testo delle note personali è visibile al proprietario e può essere consultato/esportato dai docenti autorizzati quando le note sono abilitate; il testo resta escluso dagli Analytics aggregati.

## Moodle App

La precedente dichiarazione `CoreCourseModuleDelegate` incompleta è stata rimossa. L’integrazione App nativa è intenzionalmente rinviata alla fase dedicata di implementazione e validazione runtime.

## Nota di upgrade

Il primo pacchetto 1.6.23 tentava di azzerare il progresso aggregato, conservare i segmenti storici e ricalcolare la completion Moodle tramite API runtime. Quel percorso di upgrade è sostituito dalla correzione 1.6.24 descritta di seguito e non deve essere considerato la procedura corrente.

## Correzione upgrade nella 1.6.24

La release 1.6.24 sostituisce il ricalcolo runtime della completion originario con un cleanup idempotente basato esclusivamente sul database. Poiché il plugin non era mai stato usato in produzione, vengono eliminati intenzionalmente i dati runtime learner precedenti al guard e le righe di completamento dei moduli VideoTrack. Restano conservati configurazione delle attività, file caricati e definizioni delle reazioni configurate. Il blocco schema 1.6.23 è ora idempotente e il cleanup 1.6.24 può riprendere in sicurezza dopo un upgrade completato solo parzialmente.

## 1.7.101 binding della sessione di playback e semantica di visibilità del browser

La finestra di credito server-authoritative è ora legata alla `sessionid` del browser che l'ha aperta tramite `start_playback`. Un segmento proveniente da un'altra scheda/sessione viene conservato con `servervalidated=0`, ma non può consumare, azzerare o sottrarre il budget della sessione attiva. Le chiusure terminali/lifecycle accettate (`pause`, `ended`, `beforeunload`, `pagehide`, `tab`, `visibilitychange`) rimuovono la sessione autorizzata e richiedono un nuovo handshake prima di poter maturare altro credito.

Il focus del browser **non** è intenzionalmente una condizione server di completion. Page Visibility e focus di tastiera/finestra descrivono fatti diversi:

- una scheda in background, una finestra minimizzata o una pagina sospesa/bloccata diventa hidden; il tracker chiude il segmento aperto e interrompe il credito;
- i gruppi di schede si comportano come normali schede: continua il tracking solo il contenuto che il browser dichiara visibile;
- pagine affiancate/split view possono restare visibili mentre una sola finestra/pane possiede il focus di tastiera, quindi un semplice `window.blur` non dimostra che lo studente non possa vedere il video;
- la policy sito predefinita resta quindi `hiddenonly`; il blur persistente con pagina visibile è diagnostico, mentre la policy opzionale `strict` può mettere in pausa dopo il grace period;
- il blocco Picture-in-Picture è best-effort. Se il documento sorgente diventa hidden, VideoTrack interrompe comunque il tracking anche se il browser mantiene il media visibile altrove.

Per i siti che abilitano intenzionalmente `strict`, VideoTrack crea nel corso il gruppo nascosto e non partecipante `mod_videotrack_focus_exception`. Un membro riceve la policy effettiva `hiddenonly`, così split-view visibile e strumenti assistivi non vengono messi in pausa dal solo blur della finestra. L’eccezione non modifica il trattamento del documento hidden né alcun controllo server-authoritative, e VideoTrack non memorizza la motivazione dell’accomodamento.

Questa scelta evita di classificare come cheating l’uso legittimo di split-screen, strumenti di accessibilità o finestre multiple, mantenendo però non autorevoli il playback hidden/background e la condivisione del credito fra schede diverse.
