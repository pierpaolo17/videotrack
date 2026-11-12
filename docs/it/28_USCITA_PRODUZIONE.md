# Evidenze integrate sul ciclo di vita e sul gate di produzione

Candidata corrente: VideoTrack **1.7.254** (`2026111204`). Audit registrato 2026-11-12.

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

L'esecuzione lifecycle nativa della candidata è **in attesa**. Non si deducono PASS dai controlli patch locali
o dal precedente test unitario dell'hook uninstall. I sei job normali devono essere verdi ed entrambi i job
selezionati devono concludere `Historical upgrade and populated uninstall`. Analizzare `lifecycle.txt` e le
tre serie prima/upgrade/ripetizione/uninstall sotto `lifecycle/` nei rispettivi artifact.

Restano PHPUnit **384 test / 4516 asserzioni** e Behat **52/1154**; le nuove asserzioni CLI sono separate.
Restano **415 file distribuiti**, **167 PHP distribuiti**, **1114 callable PHP distribuiti**, **654 callable AMD**
e **32 documenti per lingua**. Due helper solo repository aggiungono sei funzioni PHP: **1120** callable PHP
nominati complessivi e **348** righe inventario file, compresi cinque file documentati solo repository.
Il repository completo conta 439 file; `.github/**` resta escluso dagli archivi. XMLDB resta `2026110601`,
**7/151/22/22**; nove AJAX, otto pacchetti da 997 chiavi e 48 terne AMD restano invariati. Implementazioni
operative, test esistenti, schema, lingue, AMD e grant sono identici byte per byte alla 253; cambiano solo
marcatore release, helper/workflow CI del repository, documentazione/inventari ed etichetta ER.

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
