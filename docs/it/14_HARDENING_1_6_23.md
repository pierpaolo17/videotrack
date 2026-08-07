# Hardening 1.6.23

VideoTrack 1.6.23 è una release esclusivamente di hardening prima della fase Moodle App.

## Confine di fiducia

- `durationseconds` è ora configurata esplicitamente dal docente ed è l’unica durata autorevole per percentuale vista, completamento percentuale e presa visione a fine video.
- La durata dichiarata dal client non diventa mai autorevole. Con durata verificata pari a zero gli intervalli possono essere registrati, ma il completamento percentuale resta indisponibile e non è possibile configurare la presa visione dopo l’ultimo secondo.
- `save_segment` verifica la velocità dichiarata rispetto all’elenco effettivamente consentito dall’attività/sito.
- L’accettazione dei segmenti usa un budget cumulativo basato sul tempo server persistito per utente/attività. La rotazione del `sessionid` client e l’aumento della frequenza delle richieste non rigenerano credito; i lunghi periodi inattivi sono limitati.
- Il server applica anche la frontiera già vista quando il seek in avanti è disabilitato.
- Lo stato conserva soltanto contatori e timestamp limitati del guard (`serverlastactivity`, `serverbudgetseconds`, `servercreditedseconds`). Sono dichiarati nella Privacy API ma intenzionalmente esclusi dal payload di backup e azzerati al restore, così il credito di riproduzione residuo non può trasferirsi tra copie del corso.

## Scope learner

- Gli utenti con `mod/videotrack:viewreport` non sono soggetti learner del tracking. Le anteprime dei docenti non contaminano quindi tracking e interazioni learner.
- Report di istanza, azioni sensibili ed export note usano regole canoniche di iscrizione attiva e visibilità gruppi Moodle.

## Contratto privacy

I segnalibri restano visibili solo al proprietario. Il testo delle note personali è visibile al proprietario e può essere consultato/esportato dai docenti autorizzati quando le note sono abilitate; il testo resta escluso dagli Analytics aggregati.

## Moodle App

La precedente dichiarazione `CoreCourseModuleDelegate` incompleta è stata rimossa. L’integrazione App nativa è intenzionalmente rinviata alla fase dedicata di implementazione e validazione runtime.

## Nota di upgrade

Durante l’upgrade da release precedenti alla 1.6.23, il progresso aggregato di visione viene azzerato per tutti gli stati learner esistenti perché i segmenti storici non erano protetti dal nuovo guard autorevole lato server. I segmenti grezzi storici restano conservati per audit/privacy e sono esplicitamente non validati. Il completamento automatico Moodle viene quindi ricalcolato tramite l’API core, preservando le condizioni ancora valide basate su reazioni/presa visione e gli override manuali. Prima di rendere nuovamente autorevoli completamento percentuale o presa visione a fine video va configurata la durata reale verificata dal docente.
