# Replay da report e ordine degli eventi del player

VideoTrack **1.7.272** (`2026112500`) corregge il ripristino tardivo di uno stato,
osservato nei frammenti YouTube della 1.7.270. È una correzione della riproduzione:
schema, dati studente, regole di completamento, permessi e sorgenti non cambiano.
Il fix non richiede bonifica del database o ricalcolo del completamento.

## Difetto riprodotto

Il report apre view.php con replaystart/replayend espliciti. In onReady YouTube va
al frammento mentre il tracker è ancora in pausa, poi richiede la riproduzione.
L’evento PLAYING del provider apre un segmento attivo. Un polling successivo consuma
il flag del seek programmatico e ripristina senza condizioni la vecchia fotografia
in pausa. Con avanzamento vietato, il normale trascorrere del video viene così
interpretato come salto non autorizzato: scattano riavvolgimento e velocità configurata
in blockedseekplaybackrate, spiegando la ripetizione e il rallentamento ritardato.

Un test deterministico riproduce il difetto sui sorgenti 270 accettati e sui build AMD
nativi effettivi: dopo PLAYING il tracker torna in pausa e il polling riavvolge con
0, 5 x. Nel replay docente non viene emesso AJAX studente: il difetto non richiede
scritture DB per manifestarsi. Lo stesso ordine può interrompere il replay studente.

## Correzione e confini

consumeProgrammaticSeek ripristina lo stato precedente soltanto finché isSeeking
resta vero. Un evento successivo di play, pausa, fine o distruzione ha già azzerato
il flag e mantiene autorità. Se non arriva alcun evento del provider, il fallback
precedente resta valido. Il flag viene consumato una volta sola.

La modifica riguarda il tracker comune. Adapter e policy dei seek restano invariati:
un vero salto in avanti vietato torna alla frontiera affidabile, applica la velocità
di recupero e non accredita l’intervallo saltato. Accedere al report non concede
partecipazione studente. L’anteprima docente continua a non creare tracking, voti o
completamenti studente. Il progresso già salvato non viene riscritto.

## Verifiche

Tre gruppi Node nuovi coprono eventi successivi play/pausa/fine/distruzione, replay
docente/studente con recupero 0, 5/0, 75/1 e un salto realmente vietato dopo il replay.
Conservati i sei gruppi lifecycle e il tracker harness precedenti. I nove gruppi sui
sorgenti passano; i build precedenti falliscono i tre nuovi gruppi come atteso.
Sono moduli AMD reali con provider/AJAX/tempo simulati: non prove browser/provider reali.

Due nuovi scenari Behat creano una reazione mediante riproduzione effettiva della
fixture e cliccano il vero link del report. Il docente mantiene velocità normale
senza record studente; lo studente mantiene visione accettata e pausa reale.
Riutilizzano gli step esistenti e la fixture YouTube, senza creare evidenza di
visione direttamente nel DB. L’esecuzione Behat nativa resta da completare.

La compilazione Grunt nativa è necessaria per il rilascio. Un runner del manutentore
controlla gli hash 271 accettati, salva gli originali AMD del sito test, applica
provvisoriamente la sola correzione del tracker, esegue npx --no-install grunt amd
e i nove gruppi Node su sorgenti e compilati, poi ripristina gli originali. L’archivio
nativo viene prodotto soltanto dopo compilazione/test riusciti e ripristino verificato.
Non carica config.php e non esegue comandi DB. Il manifest indica provenienza dal
server del manutentore, digest, comando e log; non viene dichiarato come build GitHub.
Non si accettano minificazione manuale o modifiche manuali alle source map.

L’archivio nativo del manutentore è ora verificato: SHA256
a6ce3d08275774f01fb2cd4b350c2f07c405f0336533f445705f894ff3175eb2.
Il vero comando Grunt termina con 0; i test passano tutti i nove gruppi sui sorgenti
e tutti i nove sui compilati. Verificati byte sorgente/fixture, 48 digest sorgenti,
96 digest build, mappe canoniche, hash del log e indicazione di ripristino degli
originali. Cambiano soltanto questo helper e i suoi due output nativi, inclusi
senza minificazione manuale. I nove gruppi e il tracker harness passano anche
localmente dopo l’incorporazione dei build.

Il primo tentativo si è fermato perché la shell usava Node 26; Grunt di Moodle 5.0
richiede >=22.11 e <23. La selezione del Node 22 supportato ha consentito il build
riuscito documentato. L’artefatto non registra la versione patch esatta di Node:
non viene quindi attribuita. La scelta vale per la shell; nvm use default permette
di tornare al default configurato dopo la compilazione, senza cambiare il runtime del plugin.

Restano CI/server 272(compresi 82 scenari Behat) e prova docente YouTube reale.
HTTPS/account Microsoft e roadmap provider/App restano percorsi distinti.
