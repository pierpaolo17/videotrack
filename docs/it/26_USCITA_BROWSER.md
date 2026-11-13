# Audit di uscita U-007: automazione browser

## 1.7.256: base delle connessioni ai provider

La255 reale/main36ab70ed è accettata:415 file corrispondenti, PHPCS/lint server168 puliti, Unit385/4540 su50/53 e strict10/0/0; CI candidata/main a sei job con52/1154 Behat e storie lifecycle selezionate superate. Il benchmark distribuito255 conferma current_userid10 e6/92 query mediane in40 campioni.

Questa candidata aggiunge il riferimento al servizio OAuth Microsoft nelle impostazioni sito (predefinito: Nessuno), un servizio di verifica locale in sola lettura e provider_status.php. Le credenziali restano gestite dal core. I controlli verificano tipo provider, abilitazione/uso per servizi, configurazione client, HTTPS degli endpoint/sito e stato locale dell’account di sistema/scopi core. Non scambiano o rinnovano token e non contattano Microsoft. OneDrive/SharePoint non è ancora una sorgente video selezionabile.

Il manutentore dà ora priorità alle sorgenti autenticate: OneDrive/SharePoint, Zoom Cloud Recording e poi Google Drive; l’App seguirà. La prova App → browser/HTTPS è esplicitamente saltata. L’estensione completa dei sottotitoli multilingua è rinviata perché non è circoscrivibile con sufficiente affidabilità a una sola release tra HTML5 e overlay dei provider. Trascrizioni e sottotitoli esistenti restano disponibili. Il manuale di accessibilità Moodle 5.3 resta in standby.

La validazione nativa della candidata256 resta da eseguire. Inventari attesi:442 file repository/418 distribuiti,170 PHP distribuiti più1 helper repository,1141 callable PHP nominati (1134 distribuiti+7 solo repository),654 callable AMD e8 pacchetti da1001 chiavi. Schema, AMD, AJAX/player/tracking e CI restano invariati. I nuovi11 metodi PHPUnit richiedono esecuzione nativa; nessun PASS App o provider remoto è dichiarato. Le sezioni precedenti in attesa di prove sono checkpoint storici.

Albero corrente: VideoTrack **1.7.256** (`2026111301`). Decisione registrata il 2026-11-09.

## Decisione

**U-007 è chiuso sulla baseline 1.7.240 accettata.** La matrice browser/provider/ruoli essenziale ha esecuzione
reale nei sei job Moodle/PHP/database indicati sotto. La 1.7.241 accettata ha consolidato evidenze e passaggio
all’audit manuale; ZIP reale/server/strict e sei job CI release/main sono superati. Reset e revisione assistita
completa delle lingue sono accettati sulla 1.7.250 reale; vedere la [review mirata](27_REVISIONE_LINGUE.md).
U-017 resta chiuso. U-020/accessibilità manuale e gate produzione restano separati. La candidata 251
aggiunge test di integrazione sul ciclo di vita in attesa di CI nativa. Questo documento non certifica
accessibilità o deployment con provider pubblici.

## Baseline accettata esatta

| Evidenza | Valore accettato |
|---|---|
| Release reale / versione | 1.7.240 / `2026110603` |
| File distribuiti nello ZIP | 408 |
| SHA-256 ZIP | `be83a2bbf806c6e95bc05d971b9a12d291575e8909b2b9a1f45af5930e71ae1e` |
| Commit main dopo merge | `2d047f5c59792e4c8798a31209f19cfa9ecc15f7` |
| Candidata corretta | `3447cdfbadcef64332f21655b17a58f5d27045ba` |
| Run CI main / candidata | `42634054361` / `42630177127`, sei job superati, tentativo 1 |
| Uguaglianza repository | Tutti i 430 blob Git coincidono tra candidata e main; coincidono i 408 file distribuiti |
| Browser per job | 50 scenari / 1.074 step, tutti passati |
| PHPUnit per job | 379 test / 4.076 asserzioni, tutti passati |
| Validatore strict | 10 PASS / 0 WARN / 0 FAIL nei job e nelle due istanze server fornite |
| SHA-256 report server | `5a9f16b2c4bc89d0fa951855be8728cfceee3cc5abbe4fa4eb50b5a23660d577` |

Il report server reale esegue **canon, lint, grunt, phpunit** su Moodle 5.0/5.3, PHP 8.3.6: 166 file PHP
puliti, PHPCS zero errori/warning, PHPUnit 379/4076 e Grunt Done. Gli hash build AMD prima/dopo coincidono:
`325cbe9919b087c4d4e1e01cebd7108530e943c212c4699aa6525adcd3f7db51`. Il report non esegue Behat né il comando
locale `--preset=full`. Evidenze browser e milestone multiversione provengono dalla CI registrata, senza
dedurre esecuzioni server. PHPMD conserva 24 segnalazioni revisionate; i gate analisi configurati passano,
con informativi Psalm 732/760 invariati. Il debito consultivo revisionato non viene dichiarato nullo.

## Milestone browser multiversione

| Moodle | PHP | Database | Browser | Behat | PHPUnit |
|---|---|---|---|---|---|
| 5.0 | 8.2 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.0 | 8.2 | PostgreSQL | Chrome | 50/1074 passati | 379/4076 passati |
| 5.1 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.2 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.3 | 8.3 | MariaDB | Chrome | 50/1074 passati | 379/4076 passati |
| 5.3 | 8.3 | PostgreSQL | Chrome | 50/1074 passati | 379/4076 passati |

Sono coperti entrambi i rami di confine e tutti i quattro rami Moodle supportati. I due database sui rami
di confine completano i job intermedi; sei ambienti rappresentano comunque un solo profilo browser.

## Criteri di uscita originari ed evidenze

| Criterio originario | Evidenza registrata | Decisione |
|---|---|---|
| Flussi essenziali playback/interazioni/integrità/ruoli coperti dal browser | Matrice esatta file/contratti nella [guida browser](22_TEST_BROWSER_BEHAT.md); tutti i 50 casi eseguiti | Soddisfatto |
| HTML5/YouTube/Vimeo conservano contratti seek/resume/tracking | Adapter produzione, media/SDK double locali e scritture server accettate per completamento, gap, rollback, fine/replay/velocità, attenzione e ruoli | Soddisfatto nello scope adapter |
| Fixture e locator stabili | Idnumber gruppi, rifiuto atteso e distinzione selettore/dati corretti; entrambi i run finali superano i 50 casi in ogni job | Soddisfatto sull’albero registrato |
| Matrice completa passa su Moodle 5.0 e 5.3 | Quattro job di confine con due database sopra | Soddisfatto |
| Milestone completa 5.0–5.3 senza regressioni specifiche | Milestone candidata/main su sei job, ZIP esatto corrispondente e controlli server forniti | Soddisfatto |

La roadmap originaria preferisce harness locali deterministiche e verifica i **contratti degli adapter**
esterni, evitando la disponibilità della rete pubblica come dipendenza automatica. Firefox/Safari/altri
motori e disponibilità degli iframe pubblici non erano prerequisiti aggiuntivi dei criteri di uscita.
Questo audit supera il testo recente che teneva U-007 aperto solo per tali controlli. Nessun test, guardia
o job CI viene eliminato o indebolito.

Il replay prova che la copertura già vista non viene contata due volte. Retry request-id ed effetti
completamento una sola volta hanno anche casi PHPUnit comportamentali in `tracker_test.php` e
`tracking_effects_test.php`. Non si dichiara un esperimento browser con timeout trasporto forzato né il
prodotto cartesiano di ogni ruolo/provider/azione. Casi UI/resume con seed e visione guadagnata senza seed
restano distinti esplicitamente.

## Lavoro residuo e condizioni di riapertura

- Completare le [osservazioni manuali](19_ACCESSIBILITY.md#protocollo-evidenze-manuali-u-020) Moodle 5.3 non registrate quando possibile; conservare gli otto PASS Moodle 5.0 già dichiarati nel loro scope.
- Registrare esiti provider reali, cookie/consenso e accessibilità contenuti nell’accettazione deploy/contenuti.
- Conservare la review assistita completa delle otto lingue sulla 250; l’approvazione madrelingua è distinta.
- Eseguire il gate integrato produzione/lifecycle/privacy/benchmark su uno ZIP esatto.
- Poi valutazione Moodle App, sottotitoli VTT multilingua concordati, base comune provider autenticati, OneDrive/SharePoint, Zoom Cloud Recording,
  Google Drive e consolidamento fra provider, nell’ordine concordato.

Una regressione adapter/UI comprovata riapre la cella U-007 interessata; un nuovo provider richiede contratti
propri. Altri motori si provano e registrano quando pertinenti al deploy, senza dedurli dalla milestone Chrome.
L’audit manuale non genera automaticamente una release: registrare un esito verde; produrre una correzione
mirata solo per un finding concreto. Non riaprire U-017 strutturale per le metriche consultive.

## Scope corrente reset e review

La review assistita di 997 chiavi / 7976 valori resta completa. La 251 reale accetta i due casi backup/
restore/privacy tra corsi con PHPUnit 381/4296, Behat 52/1154 e strict 10/0/0 nativi. La 252 collega il callback
standard di reset Moodle all'implementazione legacy invariata e aggiunge test reali reset corso/cancellazione
attività; attesi 383 metodi PHPUnit, asserzioni da misurare, Behat 52/1154 invariato. Gate nativi e pacchetto reale
sono in attesa. Restano otto PASS manuali sulla 248 / Moodle 5.0 / Academi / Windows 11 / Chrome / NVDA nel loro scope;
5.3 manuale non è registrato. Vedere [il registro integrato](28_USCITA_PRODUZIONE.md). U-017/U-007 restano chiusi;
completare web/App, poi VTT concordati, base autenticata, OneDrive/SharePoint → Zoom → Google Drive e consolidamento.
