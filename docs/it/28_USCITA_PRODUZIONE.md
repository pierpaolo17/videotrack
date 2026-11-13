# Evidenze integrate sul ciclo di vita e sul gate di produzione

Candidata corrente: VideoTrack **1.7.255** (`2026111300`). Audit registrato 2026-11-13.

## Candidata 1.7.255: contesto viewer del benchmark

La baseline accettata e la254 reale/main a42432a (415 file package coincidenti, PHPCS/lint server puliti su 168 PHP,
Unit 385/4540 su 5.0/5.3, Behat 52/1154 nei sei job candidata/main e percorsi lifecycle selezionati). L'ultima
correzione helper e accettata; le precedenti attese di quella correzione sotto sono checkpoint storici.

La255 cambia solo CLI benchmark e documentazione/metadati release: carica il viewer attivo completo, lo imposta
come utente corrente in memoria prima delle misure ed espone current_userid. Analytics, regole capability/gruppi/
privacy, schema, tracking, lifecycle e AMD restano invariati. Le due guide CLI eliminano l'esempio --json non
supportato dal benchmark e documentano il suo default privacy indipendente 2.

L'evidenza nativa254 del profiler con minusers 5 conserva tutti i 40 risultati passando dall'utente corrente 0 al
viewer 10, con 218-a-92 query del corso e 9-a-6 della singola attivita. Questo calibra il contesto CLI, non dimostra
una ottimizzazione prestazionale del prodotto. Vedere [la guida CLI](21_CLI_DIAGNOSTICS.md) per densita e limiti.
Nuovi esiti 255 CI/server/strict e riesecuzione del CLI distribuito sono in attesa. Inventari file/callable invariati.
Il manuale Moodle 5.3 resta in standby; benchmark worst-case/concorrenti e decisione esplicita MoodleApp restano aperti.


## Baseline accettata

La **1.7.253 / 2026111203** reale è accettata: tutti i 415 file dello ZIP coincidono con main
`511234197069f8f83e23f0da9444fa1d6a5f8030` e con la candidata approvata.
SHA-256 pacchetto: `b7a1643ba5502424af714302bba9d99d61dc29b3820cbcc751b84ac16187522d`.
Il report server di 10 file ha SHA-256
`d2e8591de33b511a8343e86532d4ef49df34773d6a262daf5eac3e86a2e06322`.
CI candidata/main su sei job e report server Moodle 5.0/5.3 superano PHPUnit **384/4516**.
Behat supera **52 scenari / 1154 passi** nei sei ambienti CI. PHPCS/lint coprono 167 PHP distribuiti puliti;
Grunt conserva gli hash build AMD ed entrambi gli strict forniti sono **10/0/0**. La retention tramite task
registrato dopo il restore è ora accettata nel suo scope: policy illimitata/finita, pulizia ripetuta, scadenza
totale e conservazione configurazione/file condivisi/voti core. Restano accettate le integrazioni backup/
restore/privacy, reset corso standard e cancellazione sincrona attività. Restano 24 rilievi PHPMD rivisti
e conteggi Psalm 732/760 consultivi; i gate configurati passano.

## Evidenze della candidata iniziale e correzione nella stessa versione

I sei artifact coincidono col commit `22466b7cb7c56decb27ba9154e1f7b158e5daebf`,
run **43297863869**, tentativo **1**. Tutti superano PHPUnit **384/4516**, Behat **52/1154**,
strict **10/0/0** e Grunt. PHPCS/lint coprono 167 file puliti. La run complessiva ha quattro
job riusciti e due step lifecycle falliti, su Moodle 5.0/MariaDB e 5.3/MariaDB.

Entrambi i job selezionati installano realmente la 237, popolano la fixture, aggiornano alla 254 e superano
strict. L’upgrade ripetuto conserva l’hash completo della fotografia canonica in ciascun job. L’uninstall
completo rimuove tabelle plugin, righe modulo/course module/contesto, metadata file e voti attivi, poi il
gate fallisce su **`course_modules_completion`**. I controlli successivi e i percorsi partial/previous
non sono stati eseguiti e non vengono dichiarati superati.

L’uninstall completo Moodle invoca l’hook plugin prima di `core\plugininfo\mod::uninstall_cleanup()`.
Nei due rami ispezionati quella pulizia elimina direttamente course module e contesti senza cancellarne
la completion nativa, diversamente dalla cancellazione sincrona di un’attività. L’hook anticipato VideoTrack
aggiunge quindi la pulizia circoscritta della completion dopo il gradebook e anche nell’uscita senza voti.
L’helper richiede un identificatore modulo registrato positivo e cancella solo righe collegate a course
module ancora appartenenti a VideoTrack. Non indovina lo scope senza registro modulo, non ripara orfani
storici non attribuibili e non modifica la completion di altri moduli.

Una nuova regressione dell’hook usa due VideoTrack in due corsi e un Forum vicino. Verifica percorsi con e
senza voti, ripetizione, conservazione della completion estranea e guardia con identificatore zero. Restano
intatti algoritmo di riparazione voti e fallback sui voti malformati. La correzione conserva release254 e
versione interna; non cambia schema, lingue, AMD, tracking/player, ruoli o regole CI.

La fotografia iniziale confronta **14 righe metadata file**, di cui sette file regolari, ma il ciclo sui
contenuti passava `false` come area. Moodle lo tratta come valore di area, non come selezione di tutte le
aree: il ciclo non visitava file. Non attribuire alla run iniziale letture dei contenuti. L’helper corretto
enumera le sette aree della fixture, richiede un file regolare per area e legge ogni contenuto per verificarne
l’hash. Chiude una verifica vacua senza indebolire alcuna asserzione sui residui.

Quella correzione ha poi superato i sei job e tutte e tre le storie nei due job selezionati.
La suite PHPUnit corretta misura **385 test / 4540 asserzioni**; Behat resta **52/1154**.
Il risultato iniziale384/4516 appartiene solo al codice iniziale, non alla candidata corretta.

## CI confermata e rifiuto del report server reale

La candidata corretta `7561ed4dc9fdb73766c8d824be8ddbf6b606d547`, run **43304629270**, e
main integrato `7145a2fdf03941f4f71a8f0d38b1912d9be620d2`, run **43315619158**, superano tutti i sei job.
Entrambe misurano PHPUnit **385/4540**, Behat **52/1154**, strict **10/0/0** e tutte e tre le
storie lifecycle sui due job selezionati. Coincidono tutte le sei coppie indipendenti di snapshot
upgrade/ripetizione. Ogni job selezionato verifica **63** hash fisici dei file condivisi tra seed,
upgrade e ripetizione delle tre storie.

Lo ZIP reale di 415 file coincide esattamente con quel main approvato. SHA-256 pacchetto:
`1a78bf8360034f425ba86549bc78c9bd96613e822f161bff226f9678ad14dd5a`.
Il report server `20261113_091431`, SHA-256
`56b19571d624cffe10b05395ce9e4cfa8f3c275de44ed2ee6537631eb2ba8143`, supera lint su **168** PHP,
Grunt con hash AMD invariati e PHPUnit **385/4540** sui due siti PHP 8.3.6, Moodle 5.0/5.3.
Entrambi gli strict forniti sono **10 PASS / 0 WARN / 0 FAIL**; grade item/righe sono rispettivamente
**0/0** e **33/33**. Gli strict sono evidenze CLI fornite, non esecuzioni locali.

Il risultato complessivo del report è **FAIL**: PHPCS canonico controlla 168 file e segnala tre errori,
tutti in `.github/lifecycle/site.php`: boilerplate GPL incompleto, assegnazioni globali prima
dell’inclusione config e docblock inline senza annotazione di tipo per una variabile. La CI precedente
controllava i soli 167 file distribuiti e saltava questo helper nella directory nascosta, solo repository.
La253 reale resta la baseline accettata.

Correggere nella stessa release: completare il boilerplate, conservare il controllo ambiente/percorso
identico in `videotrack_ci_root()` chiamato dalla prima inclusione config.php, e usare un commento
normale. Il rifiuto resta prima del bootstrap. Il job qualità esegue esplicitamente lint e PHPCS
sull’helper col ruleset canonico `moodle-extra` del progetto; conserva `phplint-lifecycle.txt` e
`phpcs-lifecycle.txt` nello stesso artifact. Nessuna soppressione di regole o modifica al PHP distribuito.

## Nuovo gate CI: storia installazione reale e uninstall completo

Dopo PHPUnit e Behat riusciti, i job Moodle 5.0/MariaDB e 5.3/MariaDB riusano il proprio database standard
temporaneo del validatore. L'helper controlla workspace Actions, dataroot, identità sito e prefisso DB.
Rifiuta macchine ordinarie, database PHPUnit/Behat e un sito iniziale con attività VideoTrack già popolate.
L'installer normale copia il checkout. Il runner gestisce anche un eventuale symlink della directory plugin
prima di sostituire il codice; checkout e versioni installate non vengono riscritti per simulare un upgrade.

| Percorso | Sorgente realmente installato | Copertura aggiunta |
|---|---|---|
| historical | 1.7.237 / `2026110507` accettata, commit `baa07ab580c15f8924ecb812a5832231e981bed2` | Upgrade dallo schema reale 7 tabelle/149 campi agli attuali 151, conservando record e nuovi default zero. I 402 file dello ZIP reale coincidono col commit immutabile. |
| partial | La stessa 1.7.237 reale | Simula soltanto il DDL del primo campo dello step `2026110601`, lasciando assente il secondo e intatta la versione installata. Poi esegue l'upgrade normale. È uno stato parziale costruito, non un processo terminato o una prova di interruzione elettrica. |
| previous | 1.7.253 / `2026111203` accettata, commit `511234197069f8f83e23f0da9444fa1d6a5f8030` | Upgrade dalla versione reale immediatamente precedente; conserva intervalli attenzione espliciti 120/600 e dati studente/configurazione. |

Per ciascun percorso il runner:

1. Installa il vecchio sorgente esatto tramite il vero `admin/cli/upgrade.php` Moodle e verifica la versione reale.
2. Crea corso, studente, Forum vicino, VideoTrack configurato, tutte le sette famiglie plugin popolate,
   eventi reazione/nota/bookmark, file condivisi, voto attivo e completion nativa. I dati sono sintetici.
3. Registra una fotografia canonica, comprendendo hash dei contenuti file reali e impostazioni di sito esplicite.
4. Distribuisce il sorgente corrente committato, esegue upgrade Moodle e strict reali e confronta i dati conservati.
   Solo i due campi legittimamente aggiunti con default zero possono estendere la vecchia riga attività.
5. Ripete l'upgrade: le due fotografie complete devono avere lo stesso SHA-256. Un errore ferma prima dell'uninstall.
6. Invoca il vero `admin/cli/uninstall_plugins.php --plugins=mod_videotrack --run` completo di Moodle.
7. Verifica assenza di tabelle plugin, righe modulo/course module/contesto, voti/completion attivi, metadata file,
   configurazione, capability/grant, metodi esterni/associazioni servizi e task registrati. Forum vicino/file,
   identità corso e studente devono sopravvivere esattamente entro lo scope delle asserzioni.

L'uninstall iniziale del plugin corrente vuoto serve solo a preparare il sito. Tre percorsi popolati su due job
selezionati producono sei nuove prove, nei sei artifact esistenti; nessuna nuova famiglia di artifact.
Il codice target viene ripristinato all'uscita, ma non si dichiara né tenta un rollback del database dopo un errore.
Storico voti core, audit configurazione/eventi e retention di cestino/blob della File API seguono Moodle.
Il controllo dei residui operativi non promette cancellazione fisica immediata dei blob indirizzati per contenuto.

Il nuovo helper CI passa dal lint PHP prima dell’esecuzione ed è escluso solo dal filtro PHPMD di produzione
(`*/.github/lifecycle/*`). Ogni file operativo già analizzato e tutte le regole PHPMD restano nel perimetro.

## Evidenze e gate release

La seconda correzione nella stessa versione è **in attesa di validazione nativa**. Le CI su sei job
e le prove server sopra descrivono main7145a2f, non questo helper/workflow corretto. Non si dichiara
un’esecuzione PHP/PHPCS nativa locale. Richiedere nuova CI candidata/main su sei job, report helper
espliciti puliti e tutte e tre le storie lifecycle sui due job, poi ZIP reale/report server/strict
prima di accettare254.

Attesi PHPUnit **385 test / 4540 asserzioni** e Behat **52/1154**; le asserzioni CLI sono separate.
Restano **415 file distribuiti**, **167 PHP distribuiti**, **1116 callable PHP distribuiti**, **654 callable AMD**
e **32 documenti per lingua**. Due helper solo repository aggiungono sette funzioni PHP: **1123** callable PHP
nominati complessivi e **348** righe inventario file, compresi cinque file documentati solo repository.
Il repository completo conta 439 file; `.github/**` resta escluso dagli archivi. XMLDB resta `2026110601`,
**7/151/22/22**; nove AJAX, otto pacchetti da 997 chiavi e 48 terne AMD restano invariati. L’unica correzione
operativa rispetto alla254 iniziale è la pulizia circoscritta della completion nativa nell’hook uninstall.
Restano test precedenti e schema/lingue/AMD/grant/regole CI; si aggiungono una regressione dell’hook e i
controlli non vacui dei contenuti file. La versione resta1.7.254/2026111204.

## Gate di produzione residuo e roadmap

Queste storie circoscritte non certificano ogni vecchia installazione, migrazioni MBZ tra siti, vera interruzione
processo, guasti concorrenti o pianificazione cron. Restano da documentare benchmark Analytics su dataset ampi
e prove manuali di accessibilità Moodle 5.3. Conservare gli otto PASS dichiarati sulla 248/Moodle 5.0/Academi/
Windows 11/Chrome/NVDA nel loro scope. Il supporto Moodle App dedicato resta non dichiarato.
U-017/U-007 restano chiusi; completare gate produzione/manuale/benchmark e decisione App esplicita, poi
VTT multilingua concordati, autenticazione provider, OneDrive/SharePoint → Zoom Cloud Recording → Google Drive.
Accettare la 254 solo dopo CI candidata/main e ZIP reale/report server/strict.

Vedere [ciclo di vita](14_INSTALL_UPGRADE_BACKUP_RESTORE.md), [CI](23_GITHUB_ACTIONS_CI.md),
[privacy](18_PRIVACY_RETENTION.md), [benchmark](21_CLI_DIAGNOSTICS.md) e [prove manuali](19_ACCESSIBILITY.md).
