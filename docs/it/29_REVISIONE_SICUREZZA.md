# Correzioni delle revisioni di sicurezza

VideoTrack **1.7.257** (`2026111600`).

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

## Prima CI della candidata e correzione

La prima candidata 1.7.257, commit `68ca29de`, run `44049397832`, fallisce tutti e sei i job.
I sei archivi allegati corrispondono agli SHA-256 ufficiali GitHub e i 446 file con i relativi
permessi Git corrispondono al commit. Ogni PHPUnit riporta 414 test, 4687 asserzioni,
un errore, un fallimento e un warning. Il test di riordino reazioni scopre un difetto
preesistente nel default icona: la validazione accetta emoji come ripiego ma legge poi
un indice assente, passando null a una colonna non nulla. La correzione normalizza prima
il default e ne verifica il salvataggio, lasciando il campo omesso nella fixture.
Il contratto sul recordset course_modules ora tollera l'indentazione; lo spazio della
classe anonima segue la segnalazione PHPCS nativa.

Strict passa 10/0/0 e Grunt termina in tutti i job. Behat passa 52/1154 su cinque job;
su Moodle 5.1 fallisce inizialmente la nota dopo rollback (51/52 scenari), poi il rerun
automatico isolato passa 1 scenario/43 passi. Questo non equivale a un primo passaggio
pulito. Le prove di aggiornamento storico/disinstallazione popolata sono saltate nei
job selezionati 5.0/5.3 MariaDB dopo il fallimento PHPUnit; il lint non le sostituisce.

PHPStan e Psalm non riportano errori nei job configurati 5.0/5.3. Psalm segnala
739/767 rilievi informativi, quattro in più per ramo rispetto alla 1.7.256 accettata;
i singoli tipi non sono stampati. PHPMD riporta 28 rilievi contro 24: quattro nuove
segnalazioni di complessità nei controlli di privacy per attività (10 ciascuno), nel
ripristino requisiti reazioni (12) e nel recupero voti transazionale (10). Le guardie
restano esplicite nei punti di ingresso condivisi, senza soppressioni o cambi soglia.
L'approfondimento dei rilievi informativi resta aperto. Sono risultati della prima
candidata, non prove di superamento della correzione.

La patch sostitutiva è incrementale da `68ca29de` sul ramo `release/v1.7.257`.
Release/versione restano 1.7.257/2026111600. Prima del merge vanno rieseguiti controlli
canonici/statici/PHPUnit, Behat e lifecycle; l'accettazione di ZIP/server seguirà.
PHP/Moodle non sono disponibili in questo workspace: le verifiche locali dei sorgenti
e di applicazione della patch non valgono come prove native.

## Stato delle segnalazioni

| ID fonte | Stato dell’implementazione |
|---|---|
| SH-01 | Policy server, alias e guardie sulle dipendenze implementati; prove native da eseguire. |
| SH-02 | Permessi e soglia per attività implementati nei servizi a blocchi e nel rendering. |
| SH-03 | Il voto vuoto passa null; zero conserva il significato di voto. |
| SH-04 | Avviso sulle note implementato; cancellazione definitiva e backup precedenti restano aperti. |
| SH-05 | GET video in sola lettura per i gruppi e pulizia gruppi del plugin in uninstall. |
| SH-06 | Grade API già usata in uninstall per contesti validi; DML orfani conservato. Separazione DDL rinviata. |
| SH-07 | Guardie POST/sesskey conservate; riordino generale delle API di richiesta rinviato. |
| SH-08 | Etichette HTML escapate separatamente da CSV e attributi testuali. |
| SH-09 | Entrambi i warning localizzati, senza modificare codici e struttura AJAX. |
| MS-38 | Transazione DML implementata, con test nativo di errore iniettato da eseguire. |
| SH-A1 / A2 | Isolamento ripresa browser e download Analytics POST rinviati. |

## Validazione e lavoro residuo

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

MDL Shield è un servizio indipendente di LMSCloud, non affiliato a Moodle Pty Ltd. La priorità deriva dalla richiesta del manutentore. Le revisioni esaminavano 1.7.255/36ab70ed. Dei 38 casi incerti espliciti di mdlscan, 36 risultavano falsi positivi nei percorsi verificati; l’output del nome sezione dipende dal formato corso (Tiles richiede verifica nativa); l’atomicità del recupero voti è il miglioramento concreto. I riepiloghi raw non descrivono tutti i rilievi.

[MDL Shield](https://mdlshield.com/about) · [Baseline commit](https://github.com/pierpaolo17/videotrack/commit/26cf86ed6c275e5e6e9a23c81659be5c5aea7790)
